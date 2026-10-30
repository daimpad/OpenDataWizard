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
 * in Stapeln nach.
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
	public const PARAM_THEME   = 'odw_theme';
	public const PARAM_LICENSE = 'odw_license';
	public const PARAM_FORMAT  = 'odw_format';
	public const PARAM_SORT    = 'odw_sort';
	public const PARAM_QUERY   = 'odw_q';

	/** Erlaubte Einträge des Attributs filters, in Formularreihenfolge. */
	public const FACETS = array( 'theme', 'license', 'format', 'sort' );

	/** Flache Index-Felder. */
	public const LICENSE_INDEX = '_odw_license_index';
	public const FORMAT_INDEX  = '_odw_format_index';
	public const THEME_INDEX   = '_odw_theme_index';

	/** Eigene Query-Variable, an der search_where() die Katalog-Abfrage erkennt. */
	public const SEARCH_VAR = 'odw_search';

	/** Länge des Suchbegriffs. */
	private const QUERY_MIN = 2;
	private const QUERY_MAX = 100;

	/** Fortschritt der Index-Migration (autoload: wird auf jedem Request gelesen). */
	private const INDEX_OPTION  = 'odw_catalog_index';
	private const INDEX_VERSION = 1;
	private const INDEX_BATCH   = 200;

	/** Basis der EU-Themen-URIs. */
	private const THEME_BASE = 'http://publications.europa.eu/resource/authority/data-theme/';

	/**
	 * Registers the index sync, the migration and the search clause.
	 */
	public static function init(): void {
		// @30 wie sync_theme_index: nach Carbon Fields (@10) und dem
		// Datei-Widget (@20), das _odw_file_id/_odw_file_format schreibt.
		add_action( 'save_post', array( self::class, 'sync_index' ), 30 );
		add_action( 'init', array( self::class, 'maybe_migrate_index' ), 30 );
		add_filter( 'posts_where', array( self::class, 'search_where' ), 10, 2 );
	}

	/**
	 * Alle GET-Parameter der Filter.
	 *
	 * @return array<int, string>
	 */
	public static function params(): array {
		return array( self::PARAM_THEME, self::PARAM_LICENSE, self::PARAM_FORMAT, self::PARAM_SORT, self::PARAM_QUERY );
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

		if ( strlen( $raw ) <= 500 && preg_match( '#^https?://[^\s<>"]+$#i', $raw ) ) {
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

	// -------------------------------------------------------------------------
	// Anfrage
	// -------------------------------------------------------------------------

	/**
	 * Liest die Filter-Parameter. Ungültiges gilt als „alle" und wird nie
	 * zurückgespiegelt.
	 *
	 * @param array<string, mixed> $get Bereits entschärfte GET-Werte.
	 * @return array{theme: string, license: string, format: string, sort: string, q: string}
	 */
	public static function parse_get( array $get ): array {
		$value = static function ( string $key ) use ( $get ): string {
			return isset( $get[ $key ] ) && is_scalar( $get[ $key ] ) ? trim( (string) $get[ $key ] ) : '';
		};

		$theme = '';
		$code  = strtoupper( $value( self::PARAM_THEME ) );
		if ( '' !== $code && preg_match( '/^[A-Z_]{2,20}$/', $code ) && class_exists( 'ODW_Fields' ) ) {
			$uri = self::THEME_BASE . $code;
			if ( isset( ODW_Fields::get_theme_options_multi()[ $uri ] ) ) {
				$theme = $uri;
			}
		}

		$format = $value( self::PARAM_FORMAT );
		if ( ! preg_match( '/^[A-Za-z0-9][A-Za-z0-9.+_-]{0,39}$/', $format ) ) {
			$format = '';
		}

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

		return array(
			'theme'   => $theme,
			'license' => self::parse_license( $value( self::PARAM_LICENSE ) ),
			'format'  => $format,
			'sort'    => $sort,
			'q'       => $q,
		);
	}

	/**
	 * Wirksamer Zustand: nur, was der Katalog freischaltet.
	 *
	 * @param array<string, mixed>  $config Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, string> $get    Ergebnis von parse_get().
	 * @return array{theme: string, license: string, format: string, sort: string, q: string}
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

		return array(
			'theme'   => $on( 'theme' ) ? (string) ( $get['theme'] ?? '' ) : '',
			'license' => $on( 'license' ) ? (string) ( $get['license'] ?? '' ) : '',
			'format'  => $on( 'format' ) ? (string) ( $get['format'] ?? '' ) : '',
			'sort'    => $sort,
			'q'       => ! empty( $config['search'] ) ? (string) ( $get['q'] ?? '' ) : '',
		);
	}

	/**
	 * Schränkt der Zustand die Liste ein oder ordnet sie um?
	 *
	 * @param array<string, string> $state Ergebnis von state().
	 * @return bool
	 */
	public static function is_active( array $state ): bool {
		foreach ( array( 'theme', 'license', 'format', 'sort', 'q' ) as $key ) {
			if ( '' !== (string) ( $state[ $key ] ?? '' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * GET-Parameter, die Links tragen müssen, damit der Zustand erhalten bleibt
	 * (Blättern, Detailansicht und Rückweg). Nur, was vom Standard abweicht.
	 *
	 * @param array<string, string> $state Ergebnis von state().
	 * @return array<string, string>
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
	 * @param array<string, mixed>  $args   Argumente aus ODW_Catalog::build_query_args().
	 * @param array<string, mixed>  $config Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, string> $state  Ergebnis von state().
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
	 * @param array<string, mixed>  $config  Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, string> $state   Ergebnis von state().
	 * @param int                   $exclude Ausgeblendete ID (Beispieldatensatz) oder 0.
	 * @return array{exclude: int, themes: array<int, string>, theme: string, license: string, format: string, q: string}
	 */
	public static function conditions( array $config, array $state, int $exclude ): array {
		return array(
			'exclude' => $exclude,
			'themes'  => array_values( (array) ( $config['themes'] ?? array() ) ),
			'theme'   => $state['theme'],
			'license' => '' !== (string) ( $config['license'] ?? '' ) ? (string) $config['license'] : $state['license'],
			'format'  => $state['format'],
			'q'       => $state['q'],
		);
	}

	/**
	 * Abfrage der Trefferzahlen einer Facette als SQL-Vorlage.
	 *
	 * Die eigene Auswahl der Facette bleibt außen vor, alle übrigen
	 * Bedingungen gelten — „Umwelt (3)" heißt: Diese Wahl ergibt 3 Treffer.
	 *
	 * @param string               $facet      theme | license | format.
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @param string               $posts      Tabelle wp_posts.
	 * @param string               $meta       Tabelle wp_postmeta.
	 * @return array{0: string, 1: array<int, int|string>}
	 */
	public static function facet_sql( string $facet, array $conditions, string $posts, string $meta ): array {
		$keys = array(
			'theme'   => self::THEME_INDEX,
			'license' => self::LICENSE_INDEX,
			'format'  => self::FORMAT_INDEX,
		);

		$sql    = "SELECT f.meta_value AS value, COUNT( DISTINCT p.ID ) AS n FROM {$posts} p"
			. " INNER JOIN {$meta} f ON f.post_id = p.ID AND f.meta_key = %s"
			. " WHERE p.post_type = 'odw_dataset' AND p.post_status = 'publish' AND p.post_password = '' AND f.meta_value <> ''";
		$values = array( $keys[ $facet ] );

		if ( (int) $conditions['exclude'] > 0 ) {
			$sql     .= ' AND p.ID <> %d';
			$values[] = (int) $conditions['exclude'];
		}

		$exists = static function ( string $key, array $wanted ) use ( $meta, &$sql, &$values ): void {
			$sql   .= " AND EXISTS ( SELECT 1 FROM {$meta} c WHERE c.post_id = p.ID AND c.meta_key = %s AND c.meta_value IN ( "
				. implode( ', ', array_fill( 0, count( $wanted ), '%s' ) ) . ' ) )';
			$values = array_merge( $values, array( $key ), array_values( $wanted ) );
		};

		if ( ! empty( $conditions['themes'] ) ) {
			$exists( self::THEME_INDEX, $conditions['themes'] );
		}
		foreach ( $keys as $other => $key ) {
			if ( $other !== $facet && '' !== (string) $conditions[ $other ] ) {
				$exists( $key, array( (string) $conditions[ $other ] ) );
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
	 * Trefferzahlen einer Facette.
	 *
	 * @param string               $facet      theme | license | format.
	 * @param array<string, mixed> $conditions Ergebnis von conditions().
	 * @return array<string, int> Wert => Anzahl.
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
	 * @param string             $facet    theme | license | format.
	 * @param array<string, int> $counts   Ergebnis von facet_counts().
	 * @param string             $selected Gewählter Wert (URI bzw. Format).
	 * @return array<int, array{value: string, param: string, label: string, count: int, selected: bool}>
	 */
	public static function facet_options( string $facet, array $counts, string $selected ): array {
		if ( '' !== $selected && ! isset( $counts[ $selected ] ) ) {
			$counts[ $selected ] = 0;
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

			$options[] = array(
				'value'    => $value,
				'param'    => $param,
				'label'    => '' !== $label ? $label : $value,
				'count'    => (int) $count,
				'selected' => $value === $selected,
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

	// -------------------------------------------------------------------------
	// Formular
	// -------------------------------------------------------------------------

	/**
	 * Das Filterformular — ein schlichtes GET-Formular ohne JavaScript.
	 *
	 * Kein automatisches Absenden bei Änderung (WCAG 3.2.2). Ein neuer Filter
	 * beginnt immer auf Seite 1: odw_page und odw_ds werden nicht mitgeschickt.
	 *
	 * @param array<string, mixed>  $config     Ergebnis von ODW_Catalog::parse_atts().
	 * @param array<string, string> $state      Ergebnis von state().
	 * @param array<string, mixed>  $conditions Ergebnis von conditions().
	 * @param string                $base       Basis-URL der Katalogseite.
	 * @param string                $reset      Adresse ohne Filter.
	 * @return string
	 */
	public static function render_form( array $config, array $state, array $conditions, string $base, string $reset ): string {
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
			$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}

		if ( $search ) {
			$html .= '<p class="odw-catalog__field odw-catalog__field--search">'
				. '<label for="odw-filter-q">' . esc_html__( 'Datensätze durchsuchen', 'open-data-wizard' ) . '</label>'
				. '<input type="search" id="odw-filter-q" name="' . esc_attr( self::PARAM_QUERY ) . '" value="' . esc_attr( $state['q'] ) . '" maxlength="' . (int) self::QUERY_MAX . '">'
				. '</p>';
		}

		$labels = array(
			'theme'   => array( __( 'Thema', 'open-data-wizard' ), __( 'Alle Themen', 'open-data-wizard' ), self::PARAM_THEME ),
			'license' => array( __( 'Lizenz', 'open-data-wizard' ), __( 'Alle Lizenzen', 'open-data-wizard' ), self::PARAM_LICENSE ),
			'format'  => array( __( 'Format', 'open-data-wizard' ), __( 'Alle Formate', 'open-data-wizard' ), self::PARAM_FORMAT ),
		);

		foreach ( $filters as $facet ) {
			if ( 'sort' === $facet ) {
				$current = '' !== $state['sort'] ? $state['sort'] : (string) $config['orderby'];
				$html   .= '<p class="odw-catalog__field">'
					. '<label for="odw-filter-sort">' . esc_html__( 'Sortierung', 'open-data-wizard' ) . '</label>'
					. '<select id="odw-filter-sort" name="' . esc_attr( self::PARAM_SORT ) . '">'
					. '<option value="title"' . selected( 'title', $current, false ) . '>' . esc_html__( 'Nach Titel (A–Z)', 'open-data-wizard' ) . '</option>'
					. '<option value="modified"' . selected( 'modified', $current, false ) . '>' . esc_html__( 'Zuletzt aktualisierte zuerst', 'open-data-wizard' ) . '</option>'
					. '</select></p>';
				continue;
			}

			list( $label, $all, $name ) = $labels[ $facet ];
			$options                    = self::facet_options( $facet, self::facet_counts( $facet, $conditions ), (string) $state[ $facet ] );

			$html .= '<p class="odw-catalog__field">'
				. '<label for="odw-filter-' . esc_attr( $facet ) . '">' . esc_html( $label ) . '</label>'
				. '<select id="odw-filter-' . esc_attr( $facet ) . '" name="' . esc_attr( $name ) . '">'
				. '<option value="">' . esc_html( $all ) . '</option>';
			foreach ( $options as $option ) {
				$html .= '<option value="' . esc_attr( $option['param'] ) . '"' . ( $option['selected'] ? ' selected' : '' ) . '>'
					. esc_html(
						sprintf(
							/* translators: 1: filter value, e.g. a theme or licence name, 2: number of matching datasets */
							__( '%1$s (%2$d)', 'open-data-wizard' ),
							$option['label'],
							$option['count']
						)
					)
					. '</option>';
			}
			$html .= '</select></p>';
		}

		$html .= '<p class="odw-catalog__filter-actions">'
			. '<button type="submit" class="odw-catalog__submit">' . esc_html__( 'Anzeigen', 'open-data-wizard' ) . '</button>';
		if ( self::is_active( $state ) ) {
			$html .= ' <a class="odw-catalog__reset" href="' . esc_url( $reset ) . '">' . esc_html__( 'Filter zurücksetzen', 'open-data-wizard' ) . '</a>';
		}
		$html .= '</p></form>';

		return $html;
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
	 * Wirksame Lizenz und wirksames Format eines Datensatzes — genau das, was
	 * Liste und Karte anzeigen.
	 *
	 * @param int $post_id Post ID.
	 * @return array{license: string, format: string}
	 */
	public static function index_values( int $post_id ): array {
		$file = class_exists( 'ODW_Shortcode' ) ? ODW_Shortcode::file_info( $post_id, false ) : array( 'format' => '' );

		return array(
			'license' => ODW_Catalog::effective_license( $post_id ),
			'format'  => ODW_Catalog::effective_format( $post_id, (string) $file['format'] ),
		);
	}

	/**
	 * Schreibt _odw_license_index und _odw_format_index.
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

		$values = self::index_values( $post_id );
		foreach ( array(
			self::LICENSE_INDEX => $values['license'],
			self::FORMAT_INDEX  => $values['format'],
		) as $key => $value ) {
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Füllt die Index-Felder für Bestandsdaten — 200 Datensätze pro Request,
	 * bis alle durch sind.
	 *
	 * Läuft auf init, also auch im Frontend: Sonst fehlten auf einer frisch
	 * aktualisierten Website, deren Backend noch niemand geöffnet hat, alle
	 * Bestandsdaten in Filtern und Facetten. Die Option ist autoload — nach
	 * Abschluss kostet die Prüfung keine Abfrage.
	 */
	public static function maybe_migrate_index(): void {
		$progress = get_option( self::INDEX_OPTION );
		$progress = is_array( $progress ) ? $progress : array();
		if ( (int) ( $progress['version'] ?? 0 ) >= self::INDEX_VERSION && ! empty( $progress['done'] ) ) {
			return;
		}

		$after = (int) ( $progress['version'] ?? 0 ) === self::INDEX_VERSION ? (int) ( $progress['after'] ?? 0 ) : 0;

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Einmalige Migration in Stapeln nach ID; get_posts() kennt kein „ID größer als".
		$ids = array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'odw_dataset' AND post_status NOT IN ( 'auto-draft', 'trash', 'inherit' ) AND ID > %d ORDER BY ID ASC LIMIT %d", $after, self::INDEX_BATCH ) ) );

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
				'after'   => empty( $ids ) ? $after : (int) end( $ids ),
				'done'    => count( $ids ) < self::INDEX_BATCH,
			),
			true
		);
	}
}
