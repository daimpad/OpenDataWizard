<?php
/**
 * Filter, Facetten und Suche des Frontend-Katalogs (Stufe 2).
 *
 * Alles hier ist Opt-in über die Attribute filters, search und license: Ein
 * [odw_catalog] ohne diese Attribute gibt genau dasselbe aus wie in Stufe 1,
 * und GET-Parameter für nicht freigeschaltete Filter wirken nicht.
 *
 * Gefiltert wird ausschließlich über flache Index-Felder, eines pro Wert:
 * - _odw_theme_index   — gibt es seit v2.41 (ODW_Fields::sync_theme_index()),
 * - _odw_license_index — die wirksame Lizenz-URI (bei „sonstige" die eigene),
 * - _odw_format_index  — das wirksame Format (Formular, sonst die Datei).
 * Lizenz und Format als ODER-Abfrage über die Rohfelder zu bauen, erzeugte in
 * WP_Meta_Query ein Kreuzprodukt über wp_postmeta mit acht Aliassen. Die
 * Indexfelder schreibt sync_index() auf save_post@30, also nach Carbon Fields
 * (@10) und dem Datei-Widget (@20); Bestandsdaten holt maybe_migrate_index()
 * in Stapeln nach. Ändern sich die Quellwerte auf anderem Weg (Import,
 * WP-CLI, gelöschter Anhang, wiederhergestellte Revision), gleicht
 * flush_pending() am Ende des Requests ab.
 *
 * Die Facetten zählen je Wert, wie viele Datensätze die AKTUELLE Auswahl der
 * übrigen Filter und der Suche ergäbe — so führt kein angebotener Wert ins
 * Leere. Grundlage ist der Katalog, wie die Attribute ihn festlegen; was der
 * Filter odw_catalog_query_args zusätzlich ändert, sehen die Zahlen nicht.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters, facets and search of the frontend catalogue.
 *
 * @package OpenDataWizard
 */
class ODW_Catalog_Filters {

	/** GET-Parameter der Filter. */
	public const PARAM_THEME     = 'odw_theme';
	public const PARAM_LICENSE   = 'odw_license';
	public const PARAM_FORMAT    = 'odw_format';
	public const PARAM_SORT      = 'odw_sort';
	public const PARAM_QUERY     = 'odw_q';
	public const PARAM_PUBLISHER = 'odw_publisher';
	public const PARAM_KEYWORD   = 'odw_keyword';
	public const PARAM_FROM      = 'odw_from';
	public const PARAM_TO        = 'odw_to';
	public const PARAM_OPEN      = 'odw_open';
	public const PARAM_HVD       = 'odw_hvd';

	/** Erlaubte Einträge des Attributs filters, in Formularreihenfolge. */
	public const FACETS = array( 'theme', 'license', 'format', 'publisher', 'keyword', 'sort', 'period', 'open', 'hvd' );

	/** Flache Index-Felder. */
	public const LICENSE_INDEX   = '_odw_license_index';
	public const FORMAT_INDEX    = '_odw_format_index';
	public const THEME_INDEX     = '_odw_theme_index';
	public const PUBLISHER_INDEX = '_odw_publisher_index';
	public const KEYWORD_INDEX   = '_odw_keyword_index';
	public const PERIOD_FROM     = '_odw_period_from';
	public const PERIOD_TO       = '_odw_period_to';
	public const OPEN_INDEX      = '_odw_open_index';
	public const HVD_INDEX       = '_odw_hvd_index';

	/**
	 * Ende eines offenen Zeitraums (nur ein Startdatum: „seit 2019"). Ein
	 * fehlender Start steht als 0 im Index.
	 */
	public const PERIOD_OPEN_END = 9999;

	/** Höchstlänge von Herausgeber und Schlagwort im Filter, höchstens so viele Schlagworte. */
	private const TEXT_MAX     = 200;
	private const KEYWORDS_MAX = 20;

	/** Eigene Query-Variable, an der search_where() die Katalog-Abfrage erkennt. */
	public const SEARCH_VAR = 'odw_search';

	/** Länge des Suchbegriffs. */
	private const QUERY_MIN = 2;
	private const QUERY_MAX = 100;

	/** Fortschritt der Index-Migration (autoload: wird auf jedem Request gelesen). */
	private const INDEX_OPTION  = 'odw_catalog_index';
	private const INDEX_VERSION = 2;
	private const INDEX_BATCH   = 200;

	/**
	 * Je Datensatz: Index-Version und post_modified_gmt beim letzten Abgleich.
	 * Daran erkennt die Migration Datensätze, die ohne Abgleich geändert
	 * wurden (etwa unter einer älteren Plugin-Version).
	 */
	private const INDEX_STATE = '_odw_index_state';

	/** Abstand der Nachprüfung auf solche Datensätze, in Sekunden (12 Stunden). */
	private const INDEX_RECHECK = 43200;

	/** Basis der EU-Themen-URIs. */
	private const THEME_BASE = 'http://publications.europa.eu/resource/authority/data-theme/';

	/**
	 * Meta-Schlüssel, aus denen die Index-Felder entstehen (dazu die
	 * Carbon-Fields-Zeilen der Themen, _odw_theme|||N|value).
	 */
	private const SOURCE_KEYS = array( '_odw_license', '_odw_license_custom', '_odw_format', '_odw_file_id', '_odw_file_format', '_odw_publisher', '_odw_keywords', '_odw_temporal_start', '_odw_temporal_end', '_odw_is_hvd' );

	/**
	 * Datensätze, deren Index am Ende des Requests abzugleichen ist — je
	 * Website (Multisite: switch_to_blog()).
	 *
	 * @var array<int, array<int, true>>
	 */
	private static $pending = array();

	/**
	 * Registers the index sync, the migration and the search clause.
	 */
	public static function init(): void {
		// @30 wie sync_theme_index: nach Carbon Fields (@10) und dem
		// Datei-Widget (@20), das _odw_file_id/_odw_file_format schreibt.
		add_action( 'save_post', array( self::class, 'sync_index' ), 30 );
		add_action( 'init', array( self::class, 'maybe_migrate_index' ), 30 );
		add_filter( 'posts_where', array( self::class, 'search_where' ), 10, 2 );

		// Was die Index-Felder sonst noch verändert — Import, WP-CLI, eigener
		// Code, gelöschte Anhänge, wiederhergestellte Revisionen.
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			add_action( $hook, array( self::class, 'note_meta_change' ), 10, 3 );
		}
		add_action( 'deleted_post', array( self::class, 'note_attachment_deleted' ), 10, 2 );
		add_action( 'wp_restore_post_revision', array( self::class, 'after_restore' ), 20, 2 );
	}

	/**
	 * Alle GET-Parameter der Filter.
	 *
	 * @return array<int, string>
	 */
	public static function params(): array {
		return array(
			self::PARAM_THEME,
			self::PARAM_LICENSE,
			self::PARAM_FORMAT,
			self::PARAM_SORT,
			self::PARAM_QUERY,
			self::PARAM_PUBLISHER,
			self::PARAM_KEYWORD,
			self::PARAM_FROM,
			self::PARAM_TO,
			self::PARAM_OPEN,
			self::PARAM_HVD,
		);
	}

	// -------------------------------------------------------------------------
	// Attribute
	// -------------------------------------------------------------------------

	/**
	 * Das Attribut filters: Teilmenge von FACETS, in fester Reihenfolge.
	 *
	 * Per Attribut fest eingestellte Facetten fallen weg — ein Themenfilter in
	 * einem auf EDUC festgelegten Katalog böte nur EDUC an.
	 *
	 * @param string $raw           Kommagetrennt.
	 * @param bool   $theme_fixed   theme-Attribut gesetzt.
	 * @param bool   $license_fixed license-Attribut gesetzt.
	 * @return array<int, string>
	 */
	public static function parse_filters( string $raw, bool $theme_fixed, bool $license_fixed ): array {
		$wanted = array_map( 'trim', explode( ',', strtolower( $raw ) ) );
		$out    = array();
		foreach ( self::FACETS as $facet ) {
			if ( ! in_array( $facet, $wanted, true ) ) {
				continue;
			}
			if ( ( 'theme' === $facet && $theme_fixed ) || ( 'license' === $facet && $license_fixed ) ) {
				continue;
			}
			$out[] = $facet;
		}
		return $out;
	}

	/**
	 * Lizenz aus Kurzform oder URI. Unbekannte Kurzformen und alles, was keine
	 * http(s)-Adresse ist, ergeben ''.
	 *
	 * Eine URI muss NICHT in der Auswahlliste stehen: Eigene Lizenzen
	 * („sonstige") haben beliebige URIs und sollen filterbar sein. Eine URI,
	 * die kein Datensatz trägt, ergibt einfach eine leere Liste.
	 *
	 * @param string $raw Kurzform (cc-by) oder URI.
	 * @return string
	 */
	public static function parse_license( string $raw ): string {
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return '';
		}

		$aliases = class_exists( 'ODW_Fields' ) ? ODW_Fields::license_aliases() : array();
		$key     = strtolower( $raw );
		if ( isset( $aliases[ $key ] ) ) {
			return $aliases[ $key ];
		}

		if ( strlen( $raw ) <= 500 && preg_match( '#^https?://[^\s<>"\x00-\x1F\x7F]+$#i', $raw ) ) {
			// Eine Lizenz der Auswahlliste in ihrer Schreibweise: Die Liste
			// fände „https://CreativeCommons.org/…" (die Kollation von
			// wp_postmeta unterscheidet nicht), das Auswahlfeld nicht.
			foreach ( array_unique( $aliases ) as $uri ) {
				if ( 0 === strcasecmp( $uri, $raw ) ) {
					return $uri;
				}
			}
			return $raw;
		}

		return '';
	}

	/**
	 * Wert für den GET-Parameter einer Lizenz: die bevorzugte Kurzform, sonst
	 * die URI.
	 *
	 * @param string $uri Lizenz-URI.
	 * @return string
	 */
	public static function license_param( string $uri ): string {
		$aliases = class_exists( 'ODW_Fields' ) ? ODW_Fields::license_aliases() : array();
		$alias   = array_search( $uri, $aliases, true );
		return false === $alias ? $uri : (string) $alias;
	}

	/**
	 * Einheitliche Schreibweise eines Formats: der Schlüssel der Formatliste
	 * (csv → CSV, GEOJSON → GeoJSON), sonst in Großbuchstaben — so, wie das
	 * Datei-Widget Endungen speichert.
	 *
	 * Ohne sie fände die Liste „csv" (die Kollation von wp_postmeta
	 * unterscheidet nicht nach Groß-/Kleinschreibung), das Auswahlfeld aber
	 * nicht, und GROUP BY legte GeoJSON und GEOJSON zusammen.
	 *
	 * @param string $format Format oder Dateiendung.
	 * @return string
	 */
	public static function canonical_format( string $format ): string {
		$format = trim( $format );
		$key    = '' !== $format && class_exists( 'ODW_Fields' ) ? ODW_Fields::resolve_format_key( $format ) : '';
		return '' !== $key ? $key : strtoupper( $format );
	}

	// -------------------------------------------------------------------------
	// Anfrage
	// -------------------------------------------------------------------------

	/**
	 * Liest die Filter-Parameter. Ungültiges gilt als „alle" und wird nie
	 * zurückgespiegelt.
	 *
	 * Herausgeber und Schlagworte werden wie sanitize_text_field() beim
	 * Schreiben des Index gefasst (Leerraum zusammengezogen); Jahre sind
	 * vierstellig, ein vertauschter Zeitraum wird gedreht.
	 *
	 * @param array<string, mixed> $get Bereits entschärfte GET-Werte.
	 * @return array{theme: string, license: string, format: string, sort: string, q: string, publisher: string, keywords: array<int, string>, from: string, to: string, open: string, hvd: string}
	 */
	public static function parse_get( array $get ): array {
		$value = static function ( string $key ) use ( $get ): string {
			return isset( $get[ $key ] ) && is_scalar( $get[ $key ] ) ? trim( (string) $get[ $key ] ) : '';
		};

		// EU-Code (EDUC) oder — für Themen, die eine Website über den Filter
		// odw_theme_options ergänzt — der Wert der Option selbst.
		$theme = '';
		$raw   = $value( self::PARAM_THEME );
		if ( '' !== $raw && class_exists( 'ODW_Fields' ) ) {
			$known = ODW_Fields::get_theme_options_multi();
			if ( isset( $known[ $raw ] ) && 0 !== strpos( $raw, self::THEME_BASE ) ) {
				$theme = $raw;
			} elseif ( preg_match( '/^[A-Za-z_]{2,20}$/', $raw ) && isset( $known[ self::THEME_BASE . strtoupper( $raw ) ] ) ) {
				$theme = self::THEME_BASE . strtoupper( $raw );
			}
		}

		$format = $value( self::PARAM_FORMAT );
		$format = preg_match( '/^[A-Za-z0-9][A-Za-z0-9.+_-]{0,39}$/', $format ) ? self::canonical_format( $format ) : '';

		$sort = strtolower( $value( self::PARAM_SORT ) );
		if ( ! in_array( $sort, array( 'title', 'modified' ), true ) ) {
			$sort = '';
		}

		$q = preg_replace( '/\s+/u', ' ', $value( self::PARAM_QUERY ) );
		$q = trim( (string) $q );
		$q = function_exists( 'mb_substr' ) ? mb_substr( $q, 0, self::QUERY_MAX ) : substr( $q, 0, self::QUERY_MAX );
		$q = trim( $q );
		if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $q ) : strlen( $q ) ) < self::QUERY_MIN ) {
			$q = '';
		}

		$keywords = array();
		$raw      = $get[ self::PARAM_KEYWORD ] ?? array();
		foreach ( is_array( $raw ) ? $raw : array( $raw ) as $keyword ) {
			$keyword = is_scalar( $keyword ) ? self::filter_text( (string) $keyword ) : '';
			if ( '' !== $keyword && ! in_array( $keyword, $keywords, true ) ) {
				$keywords[] = $keyword;
			}
			if ( count( $keywords ) >= self::KEYWORDS_MAX ) {
				break;
			}
		}

		$year = static function ( string $raw ): string {
			return preg_match( '/^[0-9]{4}$/', $raw ) ? $raw : '';
		};
		$from = $year( $value( self::PARAM_FROM ) );
		$to   = $year( $value( self::PARAM_TO ) );
		if ( '' !== $from && '' !== $to && (int) $from > (int) $to ) {
			list( $from, $to ) = array( $to, $from );
		}

		return array(
			'theme'     => $theme,
			'license'   => self::parse_license( $value( self::PARAM_LICENSE ) ),
			'format'    => $format,
			'sort'      => $sort,
			'q'         => $q,
			'publisher' => self::filter_text( $value( self::PARAM_PUBLISHER ) ),
			'keywords'  => $keywords,
			'from'      => $from,
			'to'        => $to,
			'open'      => '1' === $value( self::PARAM_OPEN ) ? '1' : '',
			'hvd'       => '1' === $value( self::PARAM_HVD ) ? '1' : '',
		);
	}

	/**
	 * Herausgeber oder Schlagwort für Filter und Index: Leerraum zusammengezogen
	 * wie bei sanitize_text_field(), ohne Steuerzeichen, höchstens TEXT_MAX
	 * Zeichen. Sonst ''. Reine Funktion.
	 *
	 * @param string $raw Wert.
	 * @return string
	 */
	public static function filter_text( string $raw ): string {
		$text = trim( (string) preg_replace( '/[\r\n\t ]+/', ' ', $raw ) );
		if ( '' === $text || preg_match( '/[\x00-\x1F\x7F]/', $text ) ) {
			return '';
		}
		return ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text ) ) <= self::TEXT_MAX ? $text : '';
	}

	/**
	 * Wirksamer Zustand: nur, was der Katalog freischaltet.
	 *
	 * @param array<string, mixed> $config Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, mixed> $get    Ergebnis von parse_get().
	 * @return array{theme: string, license: string, format: string, sort: string, q: string, publisher: string, keywords: array<int, string>, from: string, to: string, open: string, hvd: string}
	 */
	public static function state( array $config, array $get ): array {
		$filters = (array) ( $config['filters'] ?? array() );
		$on      = static function ( string $facet ) use ( $filters ): bool {
			return in_array( $facet, $filters, true );
		};

		$sort    = $on( 'sort' ) ? (string) ( $get['sort'] ?? '' ) : '';
		$orderby = (string) ( $config['orderby'] ?? 'title' );
		if ( $orderby === $sort ) {
			// Die eingestellte Sortierung ist keine Abweichung — sonst gäbe es
			// dieselbe Liste unter zwei Adressen.
			$sort = '';
		}

		$text = static function ( string $facet, string $key ) use ( $on, $get ): string {
			return $on( $facet ) ? (string) ( $get[ $key ] ?? '' ) : '';
		};

		return array(
			'theme'     => $text( 'theme', 'theme' ),
			'license'   => $text( 'license', 'license' ),
			'format'    => $text( 'format', 'format' ),
			'sort'      => $sort,
			'q'         => ! empty( $config['search'] ) ? (string) ( $get['q'] ?? '' ) : '',
			'publisher' => $text( 'publisher', 'publisher' ),
			'keywords'  => $on( 'keyword' ) ? array_values( array_map( 'strval', (array) ( $get['keywords'] ?? array() ) ) ) : array(),
			'from'      => $text( 'period', 'from' ),
			'to'        => $text( 'period', 'to' ),
			'open'      => $text( 'open', 'open' ),
			'hvd'       => $text( 'hvd', 'hvd' ),
		);
	}

	/**
	 * Schränkt der Zustand die Liste ein oder ordnet sie um?
	 *
	 * @param array<string, mixed> $state Ergebnis von state().
	 * @return bool
	 */
	public static function is_active( array $state ): bool {
		foreach ( array( 'theme', 'license', 'format', 'sort', 'q', 'publisher', 'from', 'to', 'open', 'hvd' ) as $key ) {
			if ( '' !== (string) ( $state[ $key ] ?? '' ) ) {
				return true;
			}
		}
		return ! empty( $state['keywords'] );
	}

	/**
	 * GET-Parameter, die Links tragen müssen, damit der Zustand erhalten bleibt
	 * (Blättern, Detailansicht und Rückweg). Nur, was vom Standard abweicht.
	 *
	 * @param array<string, mixed> $state Ergebnis von state().
	 * @return array<string, string|array<int, string>>
	 */
	public static function state_args( array $state ): array {
		$args = array();
		if ( '' !== ( $state['theme'] ?? '' ) ) {
			$args[ self::PARAM_THEME ] = str_replace( self::THEME_BASE, '', $state['theme'] );
		}
		if ( '' !== ( $state['license'] ?? '' ) ) {
			$args[ self::PARAM_LICENSE ] = self::license_param( $state['license'] );
		}
		if ( '' !== ( $state['format'] ?? '' ) ) {
			$args[ self::PARAM_FORMAT ] = $state['format'];
		}
		if ( '' !== ( $state['sort'] ?? '' ) ) {
			$args[ self::PARAM_SORT ] = $state['sort'];
		}
		if ( '' !== ( $state['q'] ?? '' ) ) {
			$args[ self::PARAM_QUERY ] = $state['q'];
		}
		if ( '' !== ( $state['publisher'] ?? '' ) ) {
			$args[ self::PARAM_PUBLISHER ] = $state['publisher'];
		}
		if ( ! empty( $state['keywords'] ) ) {
			$args[ self::PARAM_KEYWORD ] = array_values( $state['keywords'] );
		}
		foreach ( array(
			'from' => self::PARAM_FROM,
			'to'   => self::PARAM_TO,
			'open' => self::PARAM_OPEN,
			'hvd'  => self::PARAM_HVD,
		) as $key => $param ) {
			if ( '' !== ( $state[ $key ] ?? '' ) ) {
				$args[ $param ] = $state[ $key ];
			}
		}
		return $args;
	}

	// -------------------------------------------------------------------------
	// Abfrage
	// -------------------------------------------------------------------------

	/**
	 * Ergänzt die WP_Query-Argumente der Liste um Lizenz, Filter, Suche und
	 * Sortierung. Alle Bedingungen sind UND-verknüpfte Gleichheiten auf den
	 * Index-Feldern — kein ODER, kein NOT EXISTS.
	 *
	 * Mehrere Schlagworte: mindestens eines davon (IN). Zeitraum: Überschneidung
	 * — der Datensatz beginnt spätestens im Bis-Jahr und endet frühestens im
	 * Von-Jahr; ohne Zeitangabe fehlen die Index-Felder, der Datensatz fällt
	 * heraus.
	 *
	 * @param array<string, mixed> $args   Argumente aus ODW_Catalog::build_query_args().
	 * @param array<string, mixed> $config Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, mixed> $state  Ergebnis von state().
	 * @return array<string, mixed>
	 */
	public static function apply_to_query( array $args, array $config, array $state ): array {
		$clauses = array();

		if ( '' !== $state['theme'] ) {
			$clauses[] = array(
				'key'   => self::THEME_INDEX,
				'value' => $state['theme'],
			);
		}

		$license = '' !== (string) ( $config['license'] ?? '' ) ? (string) $config['license'] : $state['license'];
		if ( '' !== $license ) {
			$clauses[] = array(
				'key'   => self::LICENSE_INDEX,
				'value' => $license,
			);
		}

		if ( '' !== $state['format'] ) {
			$clauses[] = array(
				'key'   => self::FORMAT_INDEX,
				'value' => $state['format'],
			);
		}

		if ( '' !== (string) ( $state['publisher'] ?? '' ) ) {
			$clauses[] = array(
				'key'   => self::PUBLISHER_INDEX,
				'value' => (string) $state['publisher'],
			);
		}

		if ( ! empty( $state['keywords'] ) ) {
			$clauses[] = array(
				'key'     => self::KEYWORD_INDEX,
				'value'   => array_values( (array) $state['keywords'] ),
				'compare' => 'IN',
			);
		}

		if ( '' !== (string) ( $state['from'] ?? '' ) ) {
			$clauses[] = array(
				'key'     => self::PERIOD_TO,
				'value'   => (int) $state['from'],
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}
		if ( '' !== (string) ( $state['to'] ?? '' ) ) {
			$clauses[] = array(
				'key'     => self::PERIOD_FROM,
				'value'   => (int) $state['to'],
				'compare' => '<=',
				'type'    => 'NUMERIC',
			);
		}

		foreach ( array(
			'open' => self::OPEN_INDEX,
			'hvd'  => self::HVD_INDEX,
		) as $flag => $key ) {
			if ( '' !== (string) ( $state[ $flag ] ?? '' ) ) {
				$clauses[] = array(
					'key'   => $key,
					'value' => '1',
				);
			}
		}

		if ( ! empty( $clauses ) ) {
			$existing           = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
			$args['meta_query'] = array_merge( $existing, $clauses );
		}

		if ( '' !== $state['q'] ) {
			$args[ self::SEARCH_VAR ] = $state['q'];
		}

		if ( 'modified' === $state['sort'] ) {
			$args['orderby'] = array(
				'modified' => 'DESC',
				'ID'       => 'DESC',
			);
		} elseif ( 'title' === $state['sort'] ) {
			$args['orderby'] = array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			);
		}

		return $args;
	}

	/**
	 * Suchbedingung für Titel, Beschreibung und Schlagworte als SQL-Vorlage.
	 *
	 * WP_Query 's' fände nur Titel: Der Post Type hat keinen Inhalt, die
	 * Beschreibung steht in der Meta. Eine LIKE-Suche ohne Relevanz.
	 *
	 * @param string $term      Suchbegriff.
	 * @param string $id_col    Spalte der Beitrags-ID (z. B. wp_posts.ID).
	 * @param string $title_col Spalte des Titels.
	 * @param string $meta      Tabelle wp_postmeta.
	 * @return array{0: string, 1: array<int, string>} Vorlage für $wpdb->prepare() und ihre Werte.
	 */
	public static function search_sql( string $term, string $id_col, string $title_col, string $meta ): array {
		// Wie $wpdb->esc_like(): %, _ und \ wörtlich nehmen.
		$like = '%' . addcslashes( $term, '_%\\' ) . '%';

		return array(
			"( {$title_col} LIKE %s OR EXISTS ( SELECT 1 FROM {$meta} odw_s WHERE odw_s.post_id = {$id_col} AND odw_s.meta_key IN ( '_odw_description', '_odw_keywords' ) AND odw_s.meta_value LIKE %s ) )",
			array( $like, $like ),
		);
	}

	/**
	 * Hängt die Suchbedingung an — nur an Abfragen, die die eigene
	 * Query-Variable tragen, also nur an die des Katalogs.
	 *
	 * @param string   $where WHERE-Teil.
	 * @param WP_Query $query Abfrage.
	 * @return string
	 */
	public static function search_where( $where, $query ): string {
		$where = (string) $where;
		$term  = (string) $query->get( self::SEARCH_VAR );
		if ( '' === $term ) {
			return $where;
		}

		global $wpdb;
		list( $sql, $values ) = self::search_sql( $term, "{$wpdb->posts}.ID", "{$wpdb->posts}.post_title", $wpdb->postmeta );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Die Vorlage enthält nur Tabellennamen aus $wpdb; die Werte laufen durch prepare().
		return $where . ' AND ' . $wpdb->prepare( $sql, $values );
	}

	// -------------------------------------------------------------------------
	// Facetten
	// -------------------------------------------------------------------------

	/**
	 * Alle Bedingungen der Liste, als einfache Daten für die Facetten-Abfragen.
	 *
	 * @param array<string, mixed> $config  Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, mixed> $state   Ergebnis von state().
	 * @param bool                 $hide_samples Beispieldaten ausblenden (ODW_Catalog::hide_samples()).
	 * @return array{hide_samples: bool, themes: array<int, string>, theme: string, license: string, format: string, q: string, publisher: string, keywords: array<int, string>, from: string, to: string, open: string, hvd: string}
	 */
	public static function conditions( array $config, array $state, bool $hide_samples ): array {
		return array(
			'hide_samples' => $hide_samples,
			'themes'       => array_values( (array) ( $config['themes'] ?? array() ) ),
			'theme'        => (string) $state['theme'],
			'license'      => '' !== (string) ( $config['license'] ?? '' ) ? (string) $config['license'] : (string) $state['license'],
			'format'       => (string) $state['format'],
			'q'            => (string) $state['q'],
			'publisher'    => (string) ( $state['publisher'] ?? '' ),
			'keywords'     => array_values( (array) ( $state['keywords'] ?? array() ) ),
			'from'         => (string) ( $state['from'] ?? '' ),
			'to'           => (string) ( $state['to'] ?? '' ),
			'open'         => (string) ( $state['open'] ?? '' ),
			'hvd'          => (string) ( $state['hvd'] ?? '' ),
		);
	}

	/**
	 * Abfrage der Trefferzahlen einer Facette als SQL-Vorlage.
	 *
	 * Die eigene Auswahl der Facette bleibt außen vor, alle übrigen
	 * Bedingungen gelten — „Umwelt (3)" heißt: Diese Wahl ergibt 3 Treffer.
	 * Bei den Kästchen open und hvd ist der einzige Wert '1'.
	 *
	 * @param string               $facet      theme | license | format | publisher | keyword | open | hvd.
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @param string               $posts      Tabelle wp_posts.
	 * @param string               $meta       Tabelle wp_postmeta.
	 * @return array{0: string, 1: array<int, int|string>}
	 */
	public static function facet_sql( string $facet, array $conditions, string $posts, string $meta ): array {
		$keys = self::facet_keys();

		$sql    = "SELECT f.meta_value AS value, COUNT( DISTINCT p.ID ) AS n FROM {$posts} p"
			. " INNER JOIN {$meta} f ON f.post_id = p.ID AND f.meta_key = %s"
			. " WHERE p.post_type = 'odw_dataset' AND p.post_status = 'publish' AND p.post_password = '' AND f.meta_value <> ''";
		$values = array( $keys[ $facet ] );

		if ( ! empty( $conditions['hide_samples'] ) ) {
			$sql     .= " AND NOT EXISTS ( SELECT 1 FROM {$meta} x WHERE x.post_id = p.ID AND x.meta_key = %s )";
			$values[] = ODW_Samples::META_KEY;
		}

		$exists = static function ( string $key, array $wanted ) use ( $meta, &$sql, &$values ): void {
			$sql   .= " AND EXISTS ( SELECT 1 FROM {$meta} c WHERE c.post_id = p.ID AND c.meta_key = %s AND c.meta_value IN ( "
				. implode( ', ', array_fill( 0, count( $wanted ), '%s' ) ) . ' ) )';
			$values = array_merge( $values, array( $key ), array_values( $wanted ) );
		};

		if ( ! empty( $conditions['themes'] ) ) {
			$exists( self::THEME_INDEX, $conditions['themes'] );
		}
		foreach ( self::selected_values( $conditions ) as $other => $wanted ) {
			if ( $other !== $facet && ! empty( $wanted ) ) {
				$exists( $keys[ $other ], $wanted );
			}
		}

		// Zeitraum: wie in apply_to_query() eine Überschneidung.
		foreach ( array(
			array( 'from', self::PERIOD_TO, '>=' ),
			array( 'to', self::PERIOD_FROM, '<=' ),
		) as $bound ) {
			if ( '' !== (string) ( $conditions[ $bound[0] ] ?? '' ) ) {
				$sql     .= " AND EXISTS ( SELECT 1 FROM {$meta} y WHERE y.post_id = p.ID AND y.meta_key = %s AND CAST( y.meta_value AS SIGNED ) {$bound[2]} %d )";
				$values[] = $bound[1];
				$values[] = (int) $conditions[ $bound[0] ];
			}
		}

		if ( '' !== (string) $conditions['q'] ) {
			list( $search, $search_values ) = self::search_sql( (string) $conditions['q'], 'p.ID', 'p.post_title', $meta );
			$sql                           .= ' AND ' . $search;
			$values                         = array_merge( $values, $search_values );
		}

		$sql .= ' GROUP BY f.meta_value';

		return array( $sql, $values );
	}

	/**
	 * Index-Feld je Facette.
	 *
	 * @return array<string, string>
	 */
	private static function facet_keys(): array {
		return array(
			'theme'     => self::THEME_INDEX,
			'license'   => self::LICENSE_INDEX,
			'format'    => self::FORMAT_INDEX,
			'publisher' => self::PUBLISHER_INDEX,
			'keyword'   => self::KEYWORD_INDEX,
			'open'      => self::OPEN_INDEX,
			'hvd'       => self::HVD_INDEX,
		);
	}

	/**
	 * Gewählte Werte je Facette aus den Bedingungen — als Liste, auch bei
	 * Facetten mit nur einem Wert.
	 *
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @return array<string, array<int, string>>
	 */
	private static function selected_values( array $conditions ): array {
		$one = static function ( string $key ) use ( $conditions ): array {
			$value = (string) ( $conditions[ $key ] ?? '' );
			return '' === $value ? array() : array( $value );
		};

		return array(
			'theme'     => $one( 'theme' ),
			'license'   => $one( 'license' ),
			'format'    => $one( 'format' ),
			'publisher' => $one( 'publisher' ),
			'keyword'   => array_values( array_map( 'strval', (array) ( $conditions['keywords'] ?? array() ) ) ),
			'open'      => $one( 'open' ),
			'hvd'       => $one( 'hvd' ),
		);
	}

	/**
	 * Trefferzahlen einer Facette.
	 *
	 * @param string               $facet      Facette (siehe facet_sql()).
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @return array<int|string, int> Wert => Anzahl (Zahlen als Wert werden zu int-Schlüsseln).
	 */
	public static function facet_counts( string $facet, array $conditions ): array {
		global $wpdb;
		list( $sql, $values ) = self::facet_sql( $facet, $conditions, $wpdb->posts, $wpdb->postmeta );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- GROUP BY über die Index-Felder hat kein WP-API-Gegenstück; die Vorlage enthält nur Tabellennamen aus $wpdb, die Werte laufen durch prepare(). Ohne Cache: Die Zahlen müssen nach jedem Speichern stimmen.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A );

		$counts = array();
		foreach ( (array) $rows as $row ) {
			$counts[ (string) $row['value'] ] = (int) $row['n'];
		}
		return $counts;
	}

	/**
	 * Auswahlliste einer Facette: Wert, Beschriftung, Anzahl, nach Beschriftung
	 * sortiert. Ein gewählter Wert bleibt auch mit 0 Treffern in der Liste,
	 * sonst sähe das Formular nach dem Absenden anders aus als die Auswahl.
	 *
	 * @param string                           $facet    theme | license | format | publisher | keyword.
	 * @param array<int|string, int>           $counts   Ergebnis von facet_counts().
	 * @param string|array<int|string, string> $selected Gewählter Wert, bei Schlagworten die gewählten.
	 * @return array<int, array{value: string, param: string, label: string, count: int, selected: bool}>
	 */
	public static function facet_options( string $facet, array $counts, $selected ): array {
		$chosen = array();
		foreach ( (array) $selected as $wanted ) {
			$wanted = (string) $wanted;
			if ( '' === $wanted ) {
				continue;
			}
			if ( ! isset( $counts[ $wanted ] ) ) {
				// GROUP BY liefert bei Schreibvarianten (…/Lizenz, …/lizenz) nur
				// einen Vertreter; die Liste trifft beide. Dann diesen markieren.
				foreach ( array_keys( $counts ) as $value ) {
					if ( 0 === strcasecmp( (string) $value, $wanted ) ) {
						$wanted = (string) $value;
						break;
					}
				}
				if ( ! isset( $counts[ $wanted ] ) ) {
					$counts[ $wanted ] = 0;
				}
			}
			$chosen[] = $wanted;
		}

		$themes = 'theme' === $facet && class_exists( 'ODW_Fields' ) ? ODW_Fields::get_theme_options_multi() : array();

		$options = array();
		foreach ( $counts as $value => $count ) {
			$value = (string) $value;
			switch ( $facet ) {
				case 'theme':
					$label = (string) ( $themes[ $value ] ?? $value );
					$param = str_replace( self::THEME_BASE, '', $value );
					break;
				case 'license':
					$label = class_exists( 'ODW_Shortcode' ) ? ODW_Shortcode::license_label( $value, '' ) : $value;
					$param = self::license_param( $value );
					break;
				default:
					$label = $value;
					$param = $value;
			}

			// Was über das Formular nicht unverändert zurückkommt, führte ins
			// Leere oder zu anderen Datensätzen — es wird gar nicht erst
			// angeboten. Der gewählte Wert stammt aus parse_get(), er kommt
			// immer zurück.
			$is_chosen = in_array( $value, $chosen, true );
			if ( ! $is_chosen && ! self::round_trips( $facet, $param, $value ) ) {
				continue;
			}

			$options[] = array(
				'value'    => $value,
				'param'    => $param,
				'label'    => '' !== $label ? $label : $value,
				'count'    => (int) $count,
				'selected' => $is_chosen,
			);
		}

		usort(
			$options,
			static function ( array $a, array $b ): int {
				return strnatcasecmp( $a['label'], $b['label'] );
			}
		);

		return $options;
	}

	/**
	 * Ergibt der GET-Wert einer Option nach dem Absenden wieder genau ihren
	 * Wert? Nicht bei einer eigenen Lizenz als Freitext oder bei einem Thema,
	 * das es nicht mehr in der Auswahl gibt. Den Weg durch das HTML-Attribut
	 * macht attr_exact() verlustfrei, auch für „&amp;" im Wert.
	 *
	 * @param string $facet theme | license | format | publisher | keyword.
	 * @param string $param GET-Wert der Option.
	 * @param string $value Index-Wert.
	 * @return bool
	 */
	private static function round_trips( string $facet, string $param, string $value ): bool {
		if ( 'keyword' === $facet ) {
			return array( $value ) === self::parse_get( array( self::PARAM_KEYWORD => array( $param ) ) )['keywords'];
		}

		$names = array(
			'theme'     => self::PARAM_THEME,
			'license'   => self::PARAM_LICENSE,
			'format'    => self::PARAM_FORMAT,
			'publisher' => self::PARAM_PUBLISHER,
		);
		$back  = self::parse_get( array( $names[ $facet ] => $param ) );
		return $back[ $facet ] === $value;
	}

	// -------------------------------------------------------------------------
	// Formular
	// -------------------------------------------------------------------------

	/**
	 * Das Filterformular — ein schlichtes GET-Formular, das ohne JavaScript
	 * vollständig funktioniert.
	 *
	 * Kein automatisches Absenden bei Änderung (WCAG 3.2.2). Ein neuer Filter
	 * beginnt immer auf Seite 1: odw_page und odw_ds werden nicht mitgeschickt.
	 *
	 * Das Formular steht in einem <details>, das offen ausgeliefert wird; auf
	 * breiten Bildschirmen blendet das CSS die Zusammenfassung aus.
	 * assets/js/odw-catalog.js klappt es auf schmalen Bildschirmen ein und
	 * macht aus der Schlagwort-Mehrfachauswahl ein Aufklappfeld mit Suche.
	 *
	 * @param array<string, mixed> $config     Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, mixed> $state      Ergebnis von state().
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @param string               $base       Basis-URL der Katalogseite.
	 * @param string               $reset      Adresse ohne Filter.
	 * @param string               $short_q    Zu kurzer Suchbegriff (short_query()), sonst ''.
	 * @return string
	 */
	public static function render_form( array $config, array $state, array $conditions, string $base, string $reset, string $short_q = '' ): string {
		$filters = (array) ( $config['filters'] ?? array() );
		$search  = ! empty( $config['search'] );
		if ( empty( $filters ) && ! $search ) {
			return '';
		}

		$action = (string) preg_replace( '/[?#].*$/s', '', $base );
		$html   = '<form class="odw-catalog__filters" method="get" action="' . esc_url( $action ) . '#' . esc_attr( ODW_Catalog::ANCHOR ) . '"'
			. ' role="search" aria-label="' . esc_attr__( 'Datensätze filtern', 'open-data-wizard' ) . '">';

		// Ein GET-Formular ersetzt den Query-String der action-Adresse. Was die
		// Seite selbst braucht (page_id bei einfachen Permalinks, lang bei
		// Sprach-Plugins), muss deshalb als verstecktes Feld mit.
		foreach ( self::hidden_inputs( $base ) as $name => $value ) {
			$html .= '<input type="hidden" name="' . self::attr_exact( $name ) . '" value="' . self::attr_exact( $value ) . '">';
		}

		if ( $search ) {
			// Zu kurz: Der Begriff bleibt stehen, und ein Hinweis sagt, warum die
			// Liste ungefiltert ist (WCAG 3.3.1). minlength zählt UTF-16-Einheiten
			// und Leerraum mit, das pattern wie der Server zwei Zeichen ohne ihn.
			$hint    = __( 'Bitte mindestens zwei Zeichen eingeben.', 'open-data-wizard' );
			$invalid = '' !== $short_q;
			$html   .= '<p class="odw-catalog__field odw-catalog__field--search">'
				. '<label for="odw-filter-q">' . esc_html__( 'Datensätze durchsuchen', 'open-data-wizard' ) . '</label>'
				. '<input type="search" id="odw-filter-q" name="' . esc_attr( self::PARAM_QUERY ) . '" value="' . self::attr_exact( $invalid ? $short_q : (string) $state['q'] ) . '"'
				. ' minlength="' . (int) self::QUERY_MIN . '" maxlength="' . (int) self::QUERY_MAX . '" pattern="' . esc_attr( '\s*\S.*\S\s*' ) . '" title="' . esc_attr( $hint ) . '"'
				. ( $invalid ? ' aria-invalid="true" aria-describedby="odw-filter-q-hint"' : '' ) . '>'
				. ( $invalid ? '<span class="odw-catalog__hint" id="odw-filter-q-hint">' . esc_html( $hint ) . '</span>' : '' )
				. '</p>';
		}

		$labels = array(
			'theme'     => array( __( 'Thema', 'open-data-wizard' ), __( 'Alle Themen', 'open-data-wizard' ), self::PARAM_THEME ),
			'license'   => array( __( 'Lizenz', 'open-data-wizard' ), __( 'Alle Lizenzen', 'open-data-wizard' ), self::PARAM_LICENSE ),
			'format'    => array( __( 'Format', 'open-data-wizard' ), __( 'Alle Formate', 'open-data-wizard' ), self::PARAM_FORMAT ),
			'publisher' => array( __( 'Herausgeber', 'open-data-wizard' ), __( 'Alle Herausgeber', 'open-data-wizard' ), self::PARAM_PUBLISHER ),
		);

		$flags = array();
		foreach ( $filters as $facet ) {
			switch ( $facet ) {
				case 'sort':
					$html .= self::sort_field( $config, $state );
					break;
				case 'keyword':
					$html .= self::keyword_field( $state, $conditions );
					break;
				case 'period':
					$html .= self::period_field( $state, ! empty( $conditions['hide_samples'] ) );
					break;
				case 'open':
				case 'hvd':
					$flags[] = $facet;
					break;
				default:
					list( $label, $all, $name ) = $labels[ $facet ];
					$html                      .= self::select_field( $facet, $label, $all, $name, self::facet_options( $facet, self::facet_counts( $facet, $conditions ), (string) $state[ $facet ] ) );
			}
		}
		$html .= self::flag_field( $flags, $state, $conditions );

		$html .= '<p class="odw-catalog__filter-actions">'
			. '<button type="submit" class="odw-catalog__submit">' . esc_html__( 'Anzeigen', 'open-data-wizard' ) . '</button>';
		if ( self::is_active( $state ) ) {
			$html .= ' <a class="odw-catalog__reset" href="' . esc_url( $reset ) . '">' . esc_html__( 'Filter zurücksetzen', 'open-data-wizard' ) . '</a>';
		}
		$html .= '</p></form>';

		if ( class_exists( 'ODW_Catalog' ) ) {
			ODW_Catalog::enqueue_script();
		}

		if ( empty( $filters ) ) {
			$summary = __( 'Suche', 'open-data-wizard' );
		} else {
			$summary = $search ? __( 'Filter und Suche', 'open-data-wizard' ) : __( 'Filter', 'open-data-wizard' );
		}
		$active = count( self::active_filters( $state ) );
		if ( $active > 0 ) {
			$summary = sprintf(
				/* translators: 1: "Filter und Suche", "Filter" or "Suche", 2: number of active filters */
				__( '%1$s (%2$d aktiv)', 'open-data-wizard' ),
				$summary,
				$active
			);
		}

		return '<details class="odw-catalog__toggle" open><summary class="odw-catalog__summary">' . esc_html( $summary ) . '</summary>' . $html . '</details>';
	}

	/**
	 * Ein Auswahlfeld mit Trefferzahlen (Thema, Lizenz, Format, Herausgeber).
	 *
	 * @param string                                                                                     $facet   Facette.
	 * @param string                                                                                     $label   Beschriftung.
	 * @param string                                                                                     $all     Text der leeren Option.
	 * @param string                                                                                     $name    GET-Parameter.
	 * @param array<int, array{value: string, param: string, label: string, count: int, selected: bool}> $options Ergebnis von facet_options().
	 * @return string
	 */
	private static function select_field( string $facet, string $label, string $all, string $name, array $options ): string {
		$html = '<p class="odw-catalog__field">'
			. '<label for="odw-filter-' . esc_attr( $facet ) . '">' . esc_html( $label ) . '</label>'
			. '<select id="odw-filter-' . esc_attr( $facet ) . '" name="' . esc_attr( $name ) . '">'
			. '<option value="">' . esc_html( $all ) . '</option>';
		foreach ( $options as $option ) {
			$html .= '<option value="' . self::attr_exact( $option['param'] ) . '"' . ( $option['selected'] ? ' selected' : '' ) . '>'
				. esc_html( self::option_text( $option['label'], $option['count'] ) ) . '</option>';
		}
		return $html . '</select></p>';
	}

	/**
	 * Text einer Option: Wert und Trefferzahl.
	 *
	 * @param string $label Beschriftung.
	 * @param int    $count Treffer.
	 * @return string
	 */
	private static function option_text( string $label, int $count ): string {
		return sprintf(
			/* translators: 1: filter value, e.g. a theme or licence name, 2: number of matching datasets */
			__( '%1$s (%2$d)', 'open-data-wizard' ),
			$label,
			$count
		);
	}

	/**
	 * Die Sortierung.
	 *
	 * @param array<string, mixed> $config Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, mixed> $state  Ergebnis von state().
	 * @return string
	 */
	private static function sort_field( array $config, array $state ): string {
		$current = '' !== (string) $state['sort'] ? (string) $state['sort'] : (string) $config['orderby'];
		return '<p class="odw-catalog__field">'
			. '<label for="odw-filter-sort">' . esc_html__( 'Sortierung', 'open-data-wizard' ) . '</label>'
			. '<select id="odw-filter-sort" name="' . esc_attr( self::PARAM_SORT ) . '">'
			. '<option value="title"' . selected( 'title', $current, false ) . '>' . esc_html__( 'Nach Titel (A–Z)', 'open-data-wizard' ) . '</option>'
			. '<option value="modified"' . selected( 'modified', $current, false ) . '>' . esc_html__( 'Zuletzt aktualisierte zuerst', 'open-data-wizard' ) . '</option>'
			. '</select></p>';
	}

	/**
	 * Schlagworte: ein natives Mehrfach-Auswahlfeld. assets/js/odw-catalog.js
	 * macht daraus ein Aufklappfeld mit Suche und Kästchen; die Beschriftungen
	 * dafür stehen in data-Attributen, damit sie übersetzt ankommen.
	 *
	 * @param array<string, mixed> $state      Ergebnis von state().
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @return string
	 */
	private static function keyword_field( array $state, array $conditions ): string {
		$options = self::facet_options( 'keyword', self::facet_counts( 'keyword', $conditions ), (array) $state['keywords'] );
		if ( empty( $options ) ) {
			return '';
		}

		$hint = __( 'Mehrere Schlagworte: Datensätze mit mindestens einem davon.', 'open-data-wizard' );
		$html = '<div class="odw-catalog__field odw-catalog__field--keywords">'
			. '<label for="odw-filter-keyword" id="odw-filter-keyword-label">' . esc_html__( 'Schlagworte', 'open-data-wizard' ) . '</label>'
			. '<select id="odw-filter-keyword" name="' . esc_attr( self::PARAM_KEYWORD ) . '[]" multiple size="' . (int) min( 6, max( 3, count( $options ) ) ) . '"'
			. ' aria-describedby="odw-filter-keyword-hint" data-odw-multiselect'
			. ' data-label-none="' . esc_attr__( 'Alle Schlagworte', 'open-data-wizard' ) . '"'
			/* translators: %d: number of selected keywords (3 or more) */
			. ' data-label-many="' . esc_attr__( '%d ausgewählt', 'open-data-wizard' ) . '"'
			. ' data-label-search="' . esc_attr__( 'Schlagwort suchen', 'open-data-wizard' ) . '"'
			. ' data-label-clear="' . esc_attr__( 'Auswahl aufheben', 'open-data-wizard' ) . '"'
			. ' data-label-done="' . esc_attr__( 'Fertig', 'open-data-wizard' ) . '"'
			. ' data-hint="' . esc_attr( $hint ) . '">';
		foreach ( $options as $option ) {
			$html .= '<option value="' . self::attr_exact( $option['param'] ) . '"' . ( $option['selected'] ? ' selected' : '' ) . '>'
				. esc_html( self::option_text( $option['label'], $option['count'] ) ) . '</option>';
		}
		return $html . '</select>'
			. '<span class="odw-catalog__hint" id="odw-filter-keyword-hint">' . esc_html( $hint ) . ' ' . esc_html__( 'Mehrfachauswahl mit gedrückter Strg- bzw. Cmd-Taste.', 'open-data-wizard' ) . '</span>'
			. '</div>';
	}

	/**
	 * Zeitraum der Daten: Jahr von/bis.
	 *
	 * @param array<string, mixed> $state        Ergebnis von state().
	 * @param bool                 $hide_samples Beispieldaten ausblenden.
	 * @return string
	 */
	private static function period_field( array $state, bool $hide_samples ): string {
		$from  = (string) $state['from'];
		$to    = (string) $state['to'];
		$years = self::period_years( $hide_samples );
		if ( empty( $years ) && '' === $from && '' === $to ) {
			return '';
		}

		$select = static function ( string $id, string $name, string $label, string $current ) use ( $years ): string {
			$list = $years;
			if ( '' !== $current && ! in_array( (int) $current, $list, true ) ) {
				$list[] = (int) $current;
				rsort( $list );
			}
			$html = '<span class="odw-catalog__year"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>'
				. '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">'
				. '<option value="">' . esc_html__( 'beliebig', 'open-data-wizard' ) . '</option>';
			foreach ( $list as $year ) {
				$html .= '<option value="' . (int) $year . '"' . selected( (string) $year, $current, false ) . '>' . (int) $year . '</option>';
			}
			return $html . '</select></span>';
		};

		return '<fieldset class="odw-catalog__group odw-catalog__group--period">'
			. '<legend>' . esc_html__( 'Zeitraum der Daten', 'open-data-wizard' ) . '</legend>'
			. '<span class="odw-catalog__years">'
			. $select( 'odw-filter-from', self::PARAM_FROM, __( 'ab', 'open-data-wizard' ), $from )
			. $select( 'odw-filter-to', self::PARAM_TO, __( 'bis', 'open-data-wizard' ), $to )
			. '</span></fieldset>';
	}

	/**
	 * „Nur Open Data" und „Nur hochwertige Datensätze (HVD)". Das HVD-Kästchen
	 * erscheint nur, wenn es unter den übrigen Bedingungen HVD-Datensätze gibt
	 * oder es schon angekreuzt ist.
	 *
	 * @param array<int, string>   $flags      open und/oder hvd.
	 * @param array<string, mixed> $state      Ergebnis von state().
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @return string
	 */
	private static function flag_field( array $flags, array $state, array $conditions ): string {
		$labels = array(
			'open' => array( __( 'Nur Open Data', 'open-data-wizard' ), self::PARAM_OPEN ),
			'hvd'  => array( __( 'Nur hochwertige Datensätze (HVD)', 'open-data-wizard' ), self::PARAM_HVD ),
		);

		$boxes = '';
		foreach ( $flags as $flag ) {
			$counts  = self::facet_counts( $flag, $conditions );
			$count   = (int) ( $counts[1] ?? 0 );
			$checked = '' !== (string) $state[ $flag ];
			if ( 'hvd' === $flag && 0 === $count && ! $checked ) {
				continue;
			}
			$boxes .= '<label class="odw-catalog__option" for="odw-filter-' . esc_attr( $flag ) . '">'
				. '<input type="checkbox" id="odw-filter-' . esc_attr( $flag ) . '" name="' . esc_attr( $labels[ $flag ][1] ) . '" value="1"' . ( $checked ? ' checked' : '' ) . '>'
				. '<span>' . esc_html( $labels[ $flag ][0] ) . ' <span class="odw-catalog__count-hint">(' . $count . ')</span></span></label>';
		}

		if ( '' === $boxes ) {
			return '';
		}
		return '<fieldset class="odw-catalog__group"><legend>' . esc_html__( 'Eingrenzen', 'open-data-wizard' ) . '</legend>' . $boxes . '</fieldset>';
	}

	/**
	 * Jahre für den Zeitraum: vom frühesten bis zum spätesten Jahr, das die
	 * veröffentlichten Datensätze abdecken, absteigend. Eine Abfrage.
	 *
	 * @param bool $hide_samples Beispieldaten nicht mitrechnen.
	 * @return array<int, int>
	 */
	public static function period_years( bool $hide_samples = false ): array {
		global $wpdb;
		$samples = $hide_samples ? $wpdb->prepare( " AND NOT EXISTS ( SELECT 1 FROM {$wpdb->postmeta} x WHERE x.post_id = p.ID AND x.meta_key = %s )", ODW_Samples::META_KEY ) : '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- MIN/MAX über die Index-Felder hat kein WP-API-Gegenstück; ohne Cache, damit das Jahr eines neuen Datensatzes sofort erscheint. $samples ist vorbereitet.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT MIN( NULLIF( CAST( a.meta_value AS SIGNED ), 0 ) ) AS lo_from, MAX( CAST( a.meta_value AS SIGNED ) ) AS hi_from, MIN( CAST( b.meta_value AS SIGNED ) ) AS lo_to, MAX( NULLIF( CAST( b.meta_value AS SIGNED ), %d ) ) AS hi_to, MAX( CAST( b.meta_value AS SIGNED ) ) AS hi_raw FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} a ON a.post_id = p.ID AND a.meta_key = %s INNER JOIN {$wpdb->postmeta} b ON b.post_id = p.ID AND b.meta_key = %s WHERE p.post_type = 'odw_dataset' AND p.post_status = 'publish' AND p.post_password = ''{$samples}", self::PERIOD_OPEN_END, self::PERIOD_FROM, self::PERIOD_TO ), ARRAY_A );

		return self::year_range( is_array( $row ) ? $row : array(), (int) current_time( 'Y' ) );
	}

	/**
	 * Jahresliste aus den Grenzen der Abfrage. Reine Funktion.
	 *
	 * Ein offener Zeitraum („seit 2019") reicht bis zum laufenden Jahr. Höchstens
	 * 200 Jahre, damit ein Tippfehler (Jahr 0201) die Liste nicht sprengt.
	 *
	 * @param array<string, mixed> $row     lo_from, hi_from, lo_to, hi_to, hi_raw.
	 * @param int                  $current Laufendes Jahr.
	 * @return array<int, int>
	 */
	public static function year_range( array $row, int $current ): array {
		$valid = static function ( array $years ): array {
			return array_values(
				array_filter(
					array_map( 'intval', $years ),
					static function ( int $year ): bool {
						return $year > 0 && $year < self::PERIOD_OPEN_END;
					}
				)
			);
		};

		$lows  = $valid( array( $row['lo_from'] ?? 0, $row['lo_to'] ?? 0 ) );
		$highs = $valid( array( $row['hi_from'] ?? 0, $row['hi_to'] ?? 0 ) );
		if ( self::PERIOD_OPEN_END === (int) ( $row['hi_raw'] ?? 0 ) ) {
			$highs[] = $current;
		}
		if ( empty( $lows ) || empty( $highs ) ) {
			return array();
		}

		$high = max( $highs );
		$low  = max( min( $lows ), $high - 199 );
		return $low > $high ? array() : range( $high, $low );
	}

	/**
	 * Die aktiven Filter einzeln — je mit dem Zustand, der ohne ihn bliebe.
	 *
	 * @param array<string, mixed> $state Ergebnis von state().
	 * @return array<int, array{label: string, state: array<string, mixed>}>
	 */
	public static function active_filters( array $state ): array {
		$out     = array();
		$without = static function ( array $changes ) use ( $state ): array {
			return array_merge( $state, $changes );
		};

		if ( '' !== (string) $state['q'] ) {
			/* translators: %s: search term */
			$out[] = array( sprintf( __( 'Suche: „%s“', 'open-data-wizard' ), $state['q'] ), $without( array( 'q' => '' ) ) );
		}
		if ( '' !== (string) $state['theme'] ) {
			$themes = class_exists( 'ODW_Fields' ) ? ODW_Fields::get_theme_options_multi() : array();
			/* translators: %s: theme name */
			$out[] = array( sprintf( __( 'Thema: %s', 'open-data-wizard' ), (string) ( $themes[ $state['theme'] ] ?? $state['theme'] ) ), $without( array( 'theme' => '' ) ) );
		}
		if ( '' !== (string) $state['license'] ) {
			$label = class_exists( 'ODW_Shortcode' ) ? ODW_Shortcode::license_label( (string) $state['license'], '' ) : (string) $state['license'];
			/* translators: %s: licence name */
			$out[] = array( sprintf( __( 'Lizenz: %s', 'open-data-wizard' ), '' !== $label ? $label : $state['license'] ), $without( array( 'license' => '' ) ) );
		}
		if ( '' !== (string) $state['format'] ) {
			/* translators: %s: file format, e.g. CSV */
			$out[] = array( sprintf( __( 'Format: %s', 'open-data-wizard' ), $state['format'] ), $without( array( 'format' => '' ) ) );
		}
		if ( '' !== (string) ( $state['publisher'] ?? '' ) ) {
			/* translators: %s: publishing organisation */
			$out[] = array( sprintf( __( 'Herausgeber: %s', 'open-data-wizard' ), $state['publisher'] ), $without( array( 'publisher' => '' ) ) );
		}
		foreach ( (array) ( $state['keywords'] ?? array() ) as $keyword ) {
			$rest = array_values( array_diff( (array) $state['keywords'], array( $keyword ) ) );
			/* translators: %s: keyword */
			$out[] = array( sprintf( __( 'Schlagwort: %s', 'open-data-wizard' ), $keyword ), $without( array( 'keywords' => $rest ) ) );
		}
		if ( '' !== (string) ( $state['from'] ?? '' ) || '' !== (string) ( $state['to'] ?? '' ) ) {
			$out[] = array(
				self::period_label( (string) $state['from'], (string) $state['to'] ),
				$without(
					array(
						'from' => '',
						'to'   => '',
					)
				),
			);
		}
		if ( '' !== (string) ( $state['open'] ?? '' ) ) {
			$out[] = array( __( 'Nur Open Data', 'open-data-wizard' ), $without( array( 'open' => '' ) ) );
		}
		if ( '' !== (string) ( $state['hvd'] ?? '' ) ) {
			$out[] = array( __( 'Nur hochwertige Datensätze (HVD)', 'open-data-wizard' ), $without( array( 'hvd' => '' ) ) );
		}
		if ( '' !== (string) $state['sort'] ) {
			$sort = 'modified' === $state['sort'] ? __( 'Zuletzt aktualisierte zuerst', 'open-data-wizard' ) : __( 'Nach Titel (A–Z)', 'open-data-wizard' );
			/* translators: %s: chosen sort order */
			$out[] = array( sprintf( __( 'Sortierung: %s', 'open-data-wizard' ), $sort ), $without( array( 'sort' => '' ) ) );
		}

		return array_map(
			static function ( array $item ): array {
				return array(
					'label' => (string) $item[0],
					'state' => $item[1],
				);
			},
			$out
		);
	}

	/**
	 * Beschriftung des Zeitraums. Reine Funktion.
	 *
	 * @param string $from Von-Jahr oder ''.
	 * @param string $to   Bis-Jahr oder ''.
	 * @return string
	 */
	public static function period_label( string $from, string $to ): string {
		if ( '' !== $from && '' !== $to ) {
			$range = $from === $to ? $from : sprintf(
				/* translators: 1: first year, 2: last year */
				__( '%1$s–%2$s', 'open-data-wizard' ),
				$from,
				$to
			);
		} elseif ( '' !== $from ) {
			/* translators: %s: year */
			$range = sprintf( __( 'ab %s', 'open-data-wizard' ), $from );
		} else {
			/* translators: %s: year */
			$range = sprintf( __( 'bis %s', 'open-data-wizard' ), $to );
		}
		/* translators: %s: year range, e.g. "2020–2025" or "ab 2020" */
		return sprintf( __( 'Zeitraum: %s', 'open-data-wizard' ), $range );
	}

	/**
	 * Die aktiven Filter als Chips, jeder ein Link ohne diesen Filter, dazu
	 * „Alle entfernen". Leer ohne aktive Filter.
	 *
	 * @param array<string, mixed> $state Ergebnis von state().
	 * @param string               $base  Basis-URL der Katalogseite.
	 * @param string               $reset Adresse ohne Filter.
	 * @return string
	 */
	public static function render_active( array $state, string $base, string $reset ): string {
		$active = self::active_filters( $state );
		if ( empty( $active ) ) {
			return '';
		}

		$html = '<div class="odw-catalog__active">'
			. '<span class="odw-catalog__active-label" id="odw-active-label">' . esc_html__( 'Aktive Filter:', 'open-data-wizard' ) . '</span>'
			. '<ul class="odw-catalog__chips" aria-labelledby="odw-active-label">';
		foreach ( $active as $item ) {
			$url   = ODW_Catalog::page_url( $base, 1, 0, ODW_Catalog::ANCHOR, self::state_args( $item['state'] ) );
			$html .= '<li><a class="odw-chip" href="' . esc_url( $url ) . '">' . esc_html( $item['label'] )
				. ' <span class="odw-chip__x" aria-hidden="true">×</span><span class="odw-sr-only"> – ' . esc_html__( 'Filter entfernen', 'open-data-wizard' ) . '</span></a></li>';
		}
		$html .= '<li><a class="odw-catalog__clear" href="' . esc_url( $reset ) . '">' . esc_html__( 'Alle entfernen', 'open-data-wizard' ) . '</a></li>';

		return $html . '</ul></div>';
	}

	/**
	 * Attributwert, den der Browser genau so zurückschickt.
	 *
	 * Denn esc_attr() kodiert vorhandene Zeichenreferenzen nicht noch einmal: Aus
	 * einer Lizenz-URI mit „&amp;" (aus HTML-Quelltext kopiert) würde im
	 * Browser „&" — und die Auswahl träfe nichts. Ein vorab kodiertes & hält
	 * jede Referenz wörtlich.
	 *
	 * @param string $value Wert.
	 * @return string Für ein Attribut in doppelten Anführungszeichen.
	 */
	public static function attr_exact( string $value ): string {
		return esc_attr( str_replace( '&', '&amp;', $value ) );
	}

	/**
	 * Ein eingegebener, aber zu kurzer Suchbegriff — für den Hinweis am
	 * Suchfeld. parse_get() verwirft ihn; ohne Hinweis sähe der Besucher nur
	 * die ungefilterte Liste und ein leeres Feld.
	 *
	 * @param array<string, mixed> $get Bereits entschärfte GET-Werte.
	 * @return string Der Begriff (1 Zeichen), sonst ''.
	 */
	public static function short_query( array $get ): string {
		$raw = isset( $get[ self::PARAM_QUERY ] ) && is_scalar( $get[ self::PARAM_QUERY ] ) ? (string) $get[ self::PARAM_QUERY ] : '';
		$q   = trim( (string) preg_replace( '/\s+/u', ' ', $raw ) );
		$len = function_exists( 'mb_strlen' ) ? mb_strlen( $q ) : strlen( $q );
		return $len > 0 && $len < self::QUERY_MIN ? $q : '';
	}

	/**
	 * Versteckte Felder aus dem Query-String der Basis-Adresse — ohne die
	 * eigenen Parameter und ohne Listen-Werte.
	 *
	 * @param string $base Basis-URL.
	 * @return array<string, string>
	 */
	public static function hidden_inputs( string $base ): array {
		$query = (string) wp_parse_url( $base, PHP_URL_QUERY );
		if ( '' === $query ) {
			return array();
		}

		$own = array_merge( array( ODW_Catalog::PARAM_PAGE, ODW_Catalog::PARAM_DATASET ), self::params() );
		$out = array();
		foreach ( explode( '&', $query ) as $pair ) {
			if ( '' === $pair ) {
				continue;
			}
			$parts = explode( '=', $pair, 2 );
			$name  = urldecode( $parts[0] );
			if ( '' === $name || in_array( $name, $own, true ) || false !== strpos( $name, '[' ) ) {
				continue;
			}
			$out[ $name ] = urldecode( $parts[1] ?? '' );
		}
		return $out;
	}

	// -------------------------------------------------------------------------
	// Index-Felder
	// -------------------------------------------------------------------------

	/**
	 * Werte der Index-Felder eines Datensatzes — genau das, was Liste und
	 * Karte anzeigen, in einer Form, die der Filter auch wieder annimmt.
	 *
	 * Eine eigene Lizenz, die weder Kurzform noch http(s)-Adresse ist
	 * („Nutzung nach Absprache", aber auch „CC-BY" als Freitext), kommt nicht
	 * in den Index: parse_license() verwürfe sie bzw. läse sie als die
	 * Standard-Lizenz CC BY — die Auswahl wirkte nicht oder zeigte andere
	 * Datensätze. Herausgeber und Schlagworte stehen so im Index, wie
	 * sanitize_text_field() sie aus der Adresse liest.
	 *
	 * @param int $post_id Post ID.
	 * @return array{license: string, format: string, publisher: string, keywords: array<int, string>, from: string, to: string, open: string, hvd: string}
	 */
	public static function index_values( int $post_id ): array {
		$file    = class_exists( 'ODW_Shortcode' ) ? ODW_Shortcode::file_info( $post_id, false ) : array( 'format' => '' );
		$license = ODW_Catalog::effective_license( $post_id );
		$format  = ODW_Catalog::effective_format( $post_id, (string) $file['format'] );
		$meta    = static function ( string $key ) use ( $post_id ): string {
			return (string) get_post_meta( $post_id, $key, true );
		};
		$period  = self::period( $meta( '_odw_temporal_start' ), $meta( '_odw_temporal_end' ) );

		$keywords = array();
		foreach ( explode( "\n", $meta( '_odw_keywords' ) ) as $keyword ) {
			$keyword = self::index_text( $keyword );
			if ( '' !== $keyword && ! in_array( $keyword, $keywords, true ) ) {
				$keywords[] = $keyword;
			}
		}

		return array(
			'license'   => self::parse_license( $license ) === $license ? $license : '',
			'format'    => '' !== $format ? self::canonical_format( $format ) : '',
			'publisher' => self::index_text( $meta( '_odw_publisher' ) ),
			'keywords'  => $keywords,
			'from'      => $period[0],
			'to'        => $period[1],
			'open'      => '' !== $license && class_exists( 'ODW_Fields' ) && ODW_Fields::is_open_license( $license ) ? '1' : '',
			'hvd'       => 'yes' === trim( $meta( '_odw_is_hvd' ) ) ? '1' : '',
		);
	}

	/**
	 * Herausgeber oder Schlagwort, wie er im Index steht: so, wie die Adresse
	 * ihn über sanitize_text_field() und filter_text() zurückliefert.
	 *
	 * @param string $raw Rohwert.
	 * @return string
	 */
	private static function index_text( string $raw ): string {
		return self::filter_text( function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $raw ) : $raw );
	}

	/**
	 * Zeitlicher Bezug als Jahre für den Index. Reine Funktion.
	 *
	 * Nur ein Start („seit 2019") reicht bis PERIOD_OPEN_END, nur ein Ende
	 * beginnt bei 0. Ohne beides: keine Index-Felder.
	 *
	 * @param string $start Startdatum (Y-m-d) oder ''.
	 * @param string $end   Enddatum (Y-m-d) oder ''.
	 * @return array{0: string, 1: string} Von- und Bis-Jahr.
	 */
	public static function period( string $start, string $end ): array {
		$year = static function ( string $date ): int {
			return preg_match( '/^\s*([0-9]{4})/', $date, $match ) ? (int) $match[1] : 0;
		};

		$from = $year( $start );
		$to   = $year( $end );
		if ( 0 === $from && 0 === $to ) {
			return array( '', '' );
		}
		if ( 0 === $to ) {
			$to = self::PERIOD_OPEN_END;
		}
		if ( $from > $to ) {
			list( $from, $to ) = array( $to, $from );
		}
		return array( (string) $from, (string) $to );
	}

	/**
	 * Schreibt die Index-Felder (ohne _odw_theme_index, das schreibt
	 * ODW_Fields::sync_theme_index()).
	 *
	 * @param int $post_id Post ID.
	 */
	public static function sync_index( int $post_id ): void {
		if ( 'odw_dataset' !== get_post_type( $post_id ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		unset( self::$pending[ get_current_blog_id() ][ $post_id ] );

		$values = self::index_values( $post_id );
		foreach ( array(
			self::LICENSE_INDEX   => $values['license'],
			self::FORMAT_INDEX    => $values['format'],
			self::PUBLISHER_INDEX => $values['publisher'],
			self::PERIOD_FROM     => $values['from'],
			self::PERIOD_TO       => $values['to'],
			self::OPEN_INDEX      => $values['open'],
			self::HVD_INDEX       => $values['hvd'],
		) as $key => $value ) {
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				// Die Meta-API entfernt Backslashes (wp_unslash) — wie wp_slash().
				update_post_meta( $post_id, $key, addslashes( $value ) );
			}
		}

		// Eine Zeile je Schlagwort; nur neu schreiben, wenn sich etwas ändert.
		$current = array_values( array_map( 'strval', (array) get_post_meta( $post_id, self::KEYWORD_INDEX, false ) ) );
		if ( $current !== $values['keywords'] ) {
			delete_post_meta( $post_id, self::KEYWORD_INDEX );
			foreach ( $values['keywords'] as $keyword ) {
				add_post_meta( $post_id, self::KEYWORD_INDEX, addslashes( $keyword ) );
			}
		}

		update_post_meta( $post_id, self::INDEX_STATE, self::index_state( (string) get_post_field( 'post_modified_gmt', $post_id ) ) );
	}

	/**
	 * Stand des Abgleichs, wie ihn die Migration vergleicht. Reine Funktion.
	 *
	 * @param string $modified_gmt post_modified_gmt des Datensatzes.
	 * @return string
	 */
	public static function index_state( string $modified_gmt ): string {
		return self::INDEX_VERSION . '|' . $modified_gmt;
	}

	/**
	 * Merkt einen Datensatz vor, wenn sich ein Quellwert der Index-Felder
	 * außerhalb des Speicherns ändert.
	 *
	 * Der WordPress-Importer etwa legt den Beitrag an (save_post läuft, noch
	 * ohne Meta) und schreibt die Meta erst danach mit add_post_meta(); ebenso
	 * „wp post meta update" oder eigener Code. Die Index-Schlüssel selbst
	 * stehen nicht in der Liste — kein Kreislauf.
	 *
	 * @param mixed  $meta_ids  Meta-ID(s), ungenutzt.
	 * @param int    $object_id Post ID.
	 * @param string $meta_key  Meta-Schlüssel.
	 */
	public static function note_meta_change( $meta_ids, $object_id, $meta_key ): void {
		unset( $meta_ids );
		$meta_key = (string) $meta_key;
		if ( ! in_array( $meta_key, self::SOURCE_KEYS, true ) && 0 !== strpos( $meta_key, '_odw_theme|' ) ) {
			return;
		}
		self::queue( (int) $object_id );
	}

	/**
	 * Ein gelöschter Anhang nimmt Datensätzen, die ihn als Datei führen, das
	 * Format aus der Dateiendung — Liste und Karte zeigen dann keines mehr.
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post|null $post    Gelöschter Beitrag.
	 */
	public static function note_attachment_deleted( $post_id, $post = null ): void {
		if ( ! $post instanceof WP_Post || 'attachment' !== $post->post_type ) {
			return;
		}

		$ids = get_posts(
			array(
				'post_type'      => 'odw_dataset',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'meta_key'       => '_odw_file_id',
				'meta_value'     => (string) (int) $post_id,
			)
		);
		foreach ( (array) $ids as $id ) {
			self::queue( (int) $id );
		}
	}

	/**
	 * Nach dem Wiederherstellen einer Revision: Carbon Fields kopiert auf
	 * Priorität 10 alle Meta-Zeilen der Revision zurück, deren Schlüssel mit
	 * einem Feldnamen beginnen — also auch die (zum Zeitpunkt der Revision
	 * veralteten) Index-Felder _odw_license_index, _odw_format_index und
	 * _odw_theme_index. Es schreibt per SQL an der Meta-API vorbei, ohne den
	 * Meta-Cache zu leeren; der Abgleich verwirft ihn deshalb zuerst.
	 *
	 * @param int $post_id     Post ID.
	 * @param int $revision_id Revision, ungenutzt.
	 */
	public static function after_restore( $post_id, $revision_id = 0 ): void {
		unset( $revision_id );
		wp_cache_delete( (int) $post_id, 'post_meta' );
		self::resync( (int) $post_id );
		self::announce( (int) $post_id );
	}

	/**
	 * Vorgemerkte Datensätze am Ende des Requests abgleichen — dann stehen
	 * alle Meta-Werte und Caches fest (wp_delete_attachment() leert den
	 * Cache des Anhangs erst nach deleted_post).
	 */
	public static function flush_pending(): void {
		$pending       = self::$pending;
		self::$pending = array();
		$current       = get_current_blog_id();

		foreach ( $pending as $blog_id => $ids ) {
			$switched = (int) $blog_id !== $current && is_multisite();
			if ( $switched ) {
				switch_to_blog( (int) $blog_id );
			}
			foreach ( array_keys( $ids ) as $post_id ) {
				self::resync( (int) $post_id );
				self::announce( (int) $post_id );
			}
			if ( $switched ) {
				restore_current_blog();
			}
		}
	}

	/**
	 * Merkt einen Datensatz für flush_pending() vor.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function queue( int $post_id ): void {
		if ( $post_id <= 0 || 'odw_dataset' !== get_post_type( $post_id ) ) {
			return;
		}
		if ( empty( self::$pending ) ) {
			add_action( 'shutdown', array( self::class, 'flush_pending' ) );
		}
		self::$pending[ get_current_blog_id() ][ $post_id ] = true;
	}

	/**
	 * Alle Index-Felder eines Datensatzes neu schreiben.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function resync( int $post_id ): void {
		if ( class_exists( 'ODW_Fields' ) ) {
			ODW_Fields::sync_theme_index( $post_id );
		}
		self::sync_index( $post_id );
	}

	/**
	 * Meldet einen veröffentlichten Datensatz an odw_catalog_changed — auch
	 * dann, wenn das Speichern ihn in diesem Request schon gemeldet hat: Der
	 * Abgleich kam danach.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function announce( int $post_id ): void {
		if ( class_exists( 'ODW_Catalog_Seo' ) && 'publish' === get_post_status( $post_id ) ) {
			ODW_Catalog_Seo::announce( $post_id, true );
		}
	}

	/**
	 * Gleicht Datensätze ab, deren Index nicht zum aktuellen Stand passt —
	 * 200 pro Request, bis alle durch sind.
	 *
	 * Welche das sind, sagt _odw_index_state (index_state()): Er fehlt bei
	 * Bestandsdaten aus der Zeit vor 2.44 und bei Datensätzen, die eine ältere
	 * Plugin-Version angelegt hat; er weicht ab, wenn ein Datensatz ohne
	 * Abgleich gespeichert wurde (etwa nach einem Rollback auf 2.43) oder sich
	 * die Index-Version ändert.
	 *
	 * Läuft auf init, also auch im Frontend: Sonst fehlten auf einer frisch
	 * aktualisierten Website, deren Backend noch niemand geöffnet hat, alle
	 * Bestandsdaten in Filtern und Facetten. Abgeschlossen kostet die Prüfung
	 * keine Abfrage (die Option ist autoload); nachgesehen wird nach einem
	 * Versionswechsel und sonst alle 12 Stunden, mit einer Abfrage.
	 */
	public static function maybe_migrate_index(): void {
		$plugin   = defined( 'ODW_VERSION' ) ? (string) ODW_VERSION : '';
		$now      = time();
		$progress = get_option( self::INDEX_OPTION );
		$progress = is_array( $progress ) ? $progress : array();
		$current  = (int) ( $progress['version'] ?? 0 ) === self::INDEX_VERSION && (string) ( $progress['plugin'] ?? '' ) === $plugin;
		$done     = ! empty( $progress['done'] );
		if ( $current && $done && $now - (int) ( $progress['checked'] ?? 0 ) < self::INDEX_RECHECK ) {
			return;
		}

		$after = $current && ! $done ? (int) ( $progress['after'] ?? 0 ) : 0;

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration in Stapeln nach ID über einen LEFT JOIN; get_posts() kennt weder „ID größer als" noch den Vergleich mit post_modified_gmt.
		$ids = array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = %s WHERE p.post_type = 'odw_dataset' AND p.post_status NOT IN ( 'auto-draft', 'trash', 'inherit' ) AND p.ID > %d AND ( s.meta_value IS NULL OR s.meta_value <> CONCAT( %d, '|', p.post_modified_gmt ) ) ORDER BY p.ID ASC LIMIT %d", self::INDEX_STATE, $after, self::INDEX_VERSION, self::INDEX_BATCH ) ) );

		if ( ! empty( $ids ) ) {
			update_meta_cache( 'post', $ids );
			foreach ( $ids as $post_id ) {
				self::sync_index( $post_id );
			}
		}

		update_option(
			self::INDEX_OPTION,
			array(
				'version' => self::INDEX_VERSION,
				'plugin'  => $plugin,
				'after'   => empty( $ids ) ? $after : (int) end( $ids ),
				'done'    => count( $ids ) < self::INDEX_BATCH,
				'checked' => $now,
			),
			true
		);
	}
}
