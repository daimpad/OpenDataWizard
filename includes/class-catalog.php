<?php
/**
 * Datenkatalog im Frontend: Shortcode [odw_catalog] und Block odw/catalog.
 *
 * Listet die veröffentlichten Datensätze auf einer Seite. Ein Klick auf einen
 * Titel öffnet die vollständige Download-Karte auf DERSELBEN Seite
 * (?odw_ds=<ID>): Der Custom Post Type hat bewusst keine öffentliche
 * Einzelseite (class-post-types.php), es gibt also keine andere Adresse, auf
 * die ein Katalog verlinken könnte.
 *
 * Die Liste liest ihre Felder per get_post_meta() aus dem Meta-Cache, den
 * WP_Query für alle Treffer in einer einzigen Abfrage vorlädt. Bequemer wäre
 * es, ODW_Shortcode::render() je Eintrag aufzurufen — das kostet aber 29–32
 * Datenbankabfragen pro Datensatz, weil Carbon Fields jeden Wert mit einer
 * eigenen SQL-Abfrage am Cache vorbei liest (Meta_Datastore::get_storage_array).
 * So bleibt eine Listenseite bei rund einem halben Dutzend Abfragen, egal wie
 * groß der Katalog ist. Die Karte und die Liste teilen sich die Logik über die
 * öffentlichen Helfer in ODW_Shortcode, damit beide nicht auseinanderlaufen.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend data catalogue: shortcode [odw_catalog] and block odw/catalog.
 *
 * @package OpenDataWizard
 */
class ODW_Catalog {

	/** Name des Shortcodes. */
	public const SHORTCODE = 'odw_catalog';

	/** Name des Blocks. */
	public const BLOCK = 'odw/catalog';

	/**
	 * GET-Parameter der Listenseite. Bewusst nicht 'page' oder 'paged': Das sind
	 * öffentliche WordPress-Query-Vars und kollidierten mit <!--nextpage--> und
	 * statischen Startseiten.
	 */
	public const PARAM_PAGE = 'odw_page';

	/** GET-Parameter der Detailansicht. */
	public const PARAM_DATASET = 'odw_ds';

	/** Sprungmarke der Katalogsektion. */
	public const ANCHOR = 'odw-catalog';

	private const PER_PAGE_DEFAULT = 20;
	private const PER_PAGE_MAX     = 50;
	private const EXCERPT_DEFAULT  = 30;
	private const EXCERPT_MAX      = 100;

	/** Schutz vor absurden OFFSETs über die Adresszeile. */
	private const PAGE_MAX = 10000;

	/** Zugriffsrecht „öffentlich". Alles andere bekommt in der Liste ein Badge. */
	private const ACCESS_PUBLIC = 'http://publications.europa.eu/resource/authority/access-right/PUBLIC';

	/**
	 * Anhang-IDs, die prime_attachments() nicht gefunden hat (in der Mediathek
	 * gelöscht, der Verweis _odw_file_id steht noch am Datensatz).
	 *
	 * @var array<int, true>
	 */
	private static $missing_files = array();

	/**
	 * Registers the shortcode, the block and the asset hooks.
	 */
	public static function init(): void {
		add_shortcode( self::SHORTCODE, array( self::class, 'render_shortcode' ) );
		add_action( 'init', array( self::class, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( self::class, 'localize_editor' ) );
		// Nach ODW_Shortcode::register_assets() auf Priorität 10, das das
		// Handle 'odw-frontend' erst registriert.
		add_action( 'wp_enqueue_scripts', array( self::class, 'maybe_enqueue_assets' ), 20 );
	}

	// -------------------------------------------------------------------------
	// Einbindung
	// -------------------------------------------------------------------------

	/**
	 * Bindet das Stylesheet schon im <head> ein, wenn die Seite den Katalog enthält.
	 *
	 * Ohne diesen Schritt käme das CSS erst beim Rendern des Shortcodes in den
	 * Footer, und klassische Themes zeigten die Liste kurz ungestylt. Erkennen
	 * lässt sich der Katalog nur im Inhalt der aufgerufenen Seite — in Widgets
	 * und Page-Buildern greift weiterhin das späte Einbinden in render().
	 */
	public static function maybe_enqueue_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$has_block = function_exists( 'has_block' ) && has_block( self::BLOCK, $post );
		if ( $has_block || has_shortcode( (string) $post->post_content, self::SHORTCODE ) ) {
			wp_enqueue_style( 'odw-frontend' );
		}
	}

	/**
	 * Registriert Editor-Skript und Block.
	 */
	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'odw-catalog-editor',
			ODW_PLUGIN_URL . 'blocks/catalog/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
			ODW_VERSION,
			true
		);

		// Titel, Beschreibung und Schlagworte als Argumente: Sie überschreiben
		// block.json (register_block_type_from_metadata() führt beides per
		// array_merge zusammen). WordPress übersetzt block.json nur über
		// gettext-Kontexte („block title" …), die die gebündelte .mo nicht kennt.
		register_block_type(
			ODW_PLUGIN_DIR . 'blocks/catalog',
			array(
				'title'           => __( 'Datenkatalog', 'open-data-wizard' ),
				'description'     => __( 'Listet alle veröffentlichten Datensätze; ein Klick öffnet die vollständige Download-Karte — die Alternative zum Shortcode [odw_catalog]. Ein Katalog pro Seite.', 'open-data-wizard' ),
				'keywords'        => array( __( 'Datenkatalog', 'open-data-wizard' ), 'Open Data', __( 'Datensätze', 'open-data-wizard' ), 'DCAT' ),
				'render_callback' => array( self::class, 'render_block' ),
			)
		);
	}

	/**
	 * Übergibt Themenliste und Beschriftungen an das Editor-Skript.
	 *
	 * Die Themen kommen aus den übersetzten Optionen, nicht aus der Datenbank —
	 * der Block braucht anders als die Datensatz-Karte keine Datensatzliste.
	 */
	public static function localize_editor(): void {
		$themes = array();
		if ( class_exists( 'ODW_Fields' ) ) {
			$base = 'http://publications.europa.eu/resource/authority/data-theme/';
			foreach ( ODW_Fields::get_theme_options_multi() as $uri => $label ) {
				$themes[] = array(
					'code'  => str_replace( $base, '', (string) $uri ),
					'label' => (string) $label,
				);
			}
		}

		// Lizenzen für die feste Vorauswahl: die Formular-Optionen ohne
		// Platzhalter und ohne „Sonstige" (eine eigene URI lässt sich nur im
		// Shortcode angeben). Wert ist die Kurzform bzw. die URI — dieselbe
		// Form, die der Shortcode annimmt.
		$licenses = array();
		if ( class_exists( 'ODW_Fields' ) ) {
			foreach ( ODW_Fields::get_license_options() as $uri => $label ) {
				if ( '' === (string) $uri || 'sonstige' === (string) $uri ) {
					continue;
				}
				$licenses[] = array(
					'value' => ODW_Catalog_Filters::license_param( (string) $uri ),
					'label' => (string) $label,
				);
			}
		}

		wp_localize_script(
			'odw-catalog-editor',
			'odwCatalogBlock',
			array(
				'themes'   => $themes,
				'licenses' => $licenses,
				'defaults' => array(
					'perPage' => self::PER_PAGE_DEFAULT,
					'excerpt' => self::EXCERPT_DEFAULT,
				),
				'limits'   => array(
					'perPage' => self::PER_PAGE_MAX,
					'excerpt' => self::EXCERPT_MAX,
				),
				'labels'   => array(
					'title'        => __( 'Datenkatalog', 'open-data-wizard' ),
					'panel'        => __( 'Katalog', 'open-data-wizard' ),
					'heading'      => __( 'Überschrift (optional)', 'open-data-wizard' ),
					'perPage'      => __( 'Datensätze pro Seite', 'open-data-wizard' ),
					'orderby'      => __( 'Sortierung', 'open-data-wizard' ),
					'byTitle'      => __( 'Nach Titel (A–Z)', 'open-data-wizard' ),
					'byModified'   => __( 'Zuletzt aktualisierte zuerst', 'open-data-wizard' ),
					'excerpt'      => __( 'Länge des Beschreibungsauszugs (Wörter, 0 = aus)', 'open-data-wizard' ),
					'themes'       => __( 'Nur diese Themen zeigen', 'open-data-wizard' ),
					'themesHelp'   => __( 'Keine Auswahl zeigt alle Themen.', 'open-data-wizard' ),
					'showDemo'     => __( 'Beispieldatensatz anzeigen', 'open-data-wizard' ),
					'showDemoHelp' => __( 'Den bei der Aktivierung angelegten Beispieldatensatz mit auflisten. Sobald er bearbeitet wurde, erscheint er ohnehin.', 'open-data-wizard' ),
					'rendered'     => __( 'Wird im Frontend als Liste aller veröffentlichten Datensätze ausgegeben.', 'open-data-wizard' ),
					'license'      => __( 'Nur diese Lizenz zeigen', 'open-data-wizard' ),
					'allLicenses'  => __( 'Alle Lizenzen', 'open-data-wizard' ),
					'visitor'      => __( 'Filter und Suche für Besucher', 'open-data-wizard' ),
					'visitorHelp'  => __( 'Zeigt über der Liste ein Formular. Fest eingestellte Themen oder Lizenzen werden dort nicht noch einmal angeboten.', 'open-data-wizard' ),
					'filterTheme'  => __( 'Thema', 'open-data-wizard' ),
					'filterLic'    => __( 'Lizenz', 'open-data-wizard' ),
					'filterFormat' => __( 'Format', 'open-data-wizard' ),
					'filterSort'   => __( 'Sortierung', 'open-data-wizard' ),
					'search'       => __( 'Suchfeld anzeigen', 'open-data-wizard' ),
					'filtersOn'    => __( 'Filter', 'open-data-wizard' ),
					'searchOn'     => __( 'mit Suche', 'open-data-wizard' ),
				),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Einstiegspunkte
	// -------------------------------------------------------------------------

	/**
	 * Shortcode-Handler — gibt HTML zurück, kein direktes echo.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_shortcode( $atts ): string {
		return self::render( self::parse_atts( $atts ), false );
	}

	/**
	 * Render-Callback des Blocks.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public static function render_block( array $attributes ): string {
		return self::render( self::parse_atts( self::block_to_atts( $attributes ) ), true );
	}

	/**
	 * Übersetzt die Block-Attribute (camelCase) in Shortcode-Attribute.
	 *
	 * Block und Shortcode laufen damit durch dieselbe Prüfung — es gibt nur
	 * eine Stelle, die Grenzen und Standardwerte kennt.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return array<string, string>
	 */
	public static function block_to_atts( array $attributes ): array {
		$text = static function ( string $key, string $fallback ) use ( $attributes ): string {
			$value = $attributes[ $key ] ?? $fallback;
			return is_scalar( $value ) ? (string) $value : $fallback;
		};

		return array(
			'per_page'  => $text( 'perPage', (string) self::PER_PAGE_DEFAULT ),
			'orderby'   => $text( 'orderby', 'title' ),
			'theme'     => $text( 'theme', '' ),
			'title'     => $text( 'title', '' ),
			'excerpt'   => $text( 'excerpt', (string) self::EXCERPT_DEFAULT ),
			'show_demo' => empty( $attributes['showDemo'] ) ? '0' : '1',
			'license'   => $text( 'license', '' ),
			'filters'   => implode( ',', array_filter( (array) ( $attributes['filters'] ?? array() ), 'is_string' ) ),
			'search'    => empty( $attributes['search'] ) ? '0' : '1',
		);
	}

	// -------------------------------------------------------------------------
	// Eingaben prüfen — reine Funktionen, ohne Datenbank testbar
	// -------------------------------------------------------------------------

	/**
	 * Prüft die Shortcode-Attribute und setzt Grenzen und Standardwerte.
	 *
	 * Shortcodes dürfen auch Mitwirkende setzen. Deshalb gilt jede Grenze hart,
	 * und nichts wird ungeprüft an WP_Query durchgereicht.
	 *
	 * Stufe 2 (alles Opt-in, der Standard ändert sich nie): filters schaltet
	 * Auswahlfelder frei, search das Suchfeld, license legt die Lizenz fest.
	 *
	 * @param array<string, string>|string $atts Rohe Attribute.
	 * @return array{per_page: int, orderby: string, themes: array<int, string>, themes_invalid: array<int, string>, title: string, excerpt: int, show_demo: bool, license: string, license_invalid: string, filters: array<int, string>, search: bool}
	 */
	public static function parse_atts( $atts ): array {
		$atts = shortcode_atts(
			array(
				'per_page'  => (string) self::PER_PAGE_DEFAULT,
				'orderby'   => 'title',
				'theme'     => '',
				'title'     => '',
				'excerpt'   => (string) self::EXCERPT_DEFAULT,
				'show_demo' => '0',
				'license'   => '',
				'filters'   => '',
				'search'    => '0',
			),
			$atts,
			self::SHORTCODE
		);

		$per_page = absint( $atts['per_page'] );
		if ( $per_page < 1 ) {
			$per_page = self::PER_PAGE_DEFAULT;
		}

		// 0 schaltet den Auszug ab; nur Unlesbares fällt auf den Standard zurück.
		$excerpt_raw = trim( (string) $atts['excerpt'] );
		$excerpt     = is_numeric( $excerpt_raw ) ? min( absint( $excerpt_raw ), self::EXCERPT_MAX ) : self::EXCERPT_DEFAULT;

		$themes = self::parse_themes( (string) $atts['theme'] );

		$license_raw = trim( (string) $atts['license'] );
		$license     = ODW_Catalog_Filters::parse_license( $license_raw );

		$theme_fixed   = '' !== trim( (string) $atts['theme'] );
		$license_fixed = '' !== $license_raw;

		$yes = static function ( $value ): bool {
			return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'ja' ), true );
		};

		return array(
			'per_page'        => min( $per_page, self::PER_PAGE_MAX ),
			'orderby'         => 'modified' === strtolower( trim( (string) $atts['orderby'] ) ) ? 'modified' : 'title',
			'themes'          => $themes['valid'],
			'themes_invalid'  => $themes['invalid'],
			'title'           => sanitize_text_field( (string) $atts['title'] ),
			'excerpt'         => $excerpt,
			'show_demo'       => $yes( $atts['show_demo'] ),
			'license'         => $license,
			'license_invalid' => '' !== $license_raw && '' === $license ? sanitize_text_field( $license_raw ) : '',
			'filters'         => ODW_Catalog_Filters::parse_filters( (string) $atts['filters'], $theme_fixed, $license_fixed ),
			'search'          => $yes( $atts['search'] ),
		);
	}

	/**
	 * Zerlegt die Themen-Angabe und löst jede einzeln zur EU-URI auf.
	 *
	 * Selbst an Kommas aufgeteilt: ODW_Fields::normalize_themes() behandelt einen
	 * String als einen einzigen Wert. Erlaubt sind EU-Code (EDUC), volle URI und
	 * die alten deutschen Kurznamen („Bildung"). Was sich nicht auflösen lässt,
	 * landet in 'invalid' — die Redaktion bekommt dazu einen Hinweis.
	 *
	 * @param string $raw Kommagetrennte Themen-Angabe.
	 * @return array{valid: array<int, string>, invalid: array<int, string>}
	 */
	public static function parse_themes( string $raw ): array {
		$out = array(
			'valid'   => array(),
			'invalid' => array(),
		);

		if ( '' === trim( $raw ) || ! class_exists( 'ODW_Fields' ) ) {
			return $out;
		}

		$known = ODW_Fields::get_theme_options_multi();

		foreach ( explode( ',', $raw ) as $part ) {
			$part = trim( $part );
			if ( '' === $part ) {
				continue;
			}

			$uris = ODW_Fields::normalize_themes( array( $part ) );
			$uri  = (string) ( $uris[0] ?? '' );

			if ( '' !== $uri && isset( $known[ $uri ] ) ) {
				if ( ! in_array( $uri, $out['valid'], true ) ) {
					$out['valid'][] = $uri;
				}
			} else {
				$out['invalid'][] = $part;
			}
		}

		return $out;
	}

	/**
	 * Liest Seite und Detail-ID aus den GET-Parametern.
	 *
	 * @param array<string, mixed> $get Bereits entschärfte GET-Werte.
	 * @return array{page: int, dataset: int}
	 */
	public static function parse_request( array $get ): array {
		$page = isset( $get[ self::PARAM_PAGE ] ) && is_scalar( $get[ self::PARAM_PAGE ] ) ? absint( $get[ self::PARAM_PAGE ] ) : 1;
		$ds   = isset( $get[ self::PARAM_DATASET ] ) && is_scalar( $get[ self::PARAM_DATASET ] ) ? absint( $get[ self::PARAM_DATASET ] ) : 0;

		return array(
			'page'    => max( 1, min( $page, self::PAGE_MAX ) ),
			'dataset' => $ds,
		);
	}

	/**
	 * Baut die WP_Query-Argumente der Liste.
	 *
	 * Typ und Status sind fest und kommen nie aus Attribut oder Adresse.
	 * Passwortgeschützte Datensätze fehlen ebenso wie (standardmäßig) der bei
	 * der Aktivierung angelegte Beispieldatensatz. update_post_meta_cache bleibt
	 * an: Genau dieser Vorab-Abruf aller Meta-Zeilen ist der Kern des Ansatzes.
	 *
	 * @param array<string, mixed>       $config  Ergebnis von parse_atts().
	 * @param array<string, int>         $request Ergebnis von parse_request().
	 * @param int                        $demo_id ID des Beispieldatensatzes oder 0.
	 * @param array<string, string>|null $state   Filter-Zustand (ODW_Catalog_Filters::state()), null = keiner.
	 * @return array<string, mixed>
	 */
	public static function build_query_args( array $config, array $request, int $demo_id, ?array $state = null ): array {
		$args = array(
			'post_type'              => 'odw_dataset',
			'post_status'            => 'publish',
			'has_password'           => false,
			'posts_per_page'         => (int) $config['per_page'],
			'paged'                  => (int) $request['page'],
			// Mit der ID als zweitem Kriterium bleibt die Reihenfolge bei gleichen
			// Titeln stabil — sonst könnte ein Eintrag beim Blättern doppelt
			// erscheinen und ein anderer gar nicht.
			'orderby'                => 'modified' === $config['orderby']
				? array(
					'modified' => 'DESC',
					'ID'       => 'DESC',
				)
				: array(
					'title' => 'ASC',
					'ID'    => 'ASC',
				),
			'no_found_rows'          => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
		);

		if ( empty( $config['show_demo'] ) && $demo_id > 0 ) {
			$args['post__not_in'] = array( $demo_id );
		}

		if ( ! empty( $config['themes'] ) ) {
			// Über den flachen Spiegel _odw_theme_index (sync_theme_index), nicht
			// über die Carbon-Fields-Schlüssel der Mehrfachauswahl.
			$args['meta_query'] = array(
				array(
					'key'     => '_odw_theme_index',
					'value'   => array_values( $config['themes'] ),
					'compare' => 'IN',
				),
			);
		}

		$state = $state ?? ODW_Catalog_Filters::state( $config, array() );
		$args  = ODW_Catalog_Filters::apply_to_query( $args, $config, $state );

		/**
		 * Filtert die WP_Query-Argumente des Frontend-Katalogs.
		 *
		 * @param array $args    Query arguments.
		 * @param array $config  Geprüfte Shortcode-Attribute.
		 * @param array $request Seite und Detail-ID.
		 * @param array $state   Filter, Suche und Sortierung der Besucher (seit 2.44.0).
		 */
		return (array) apply_filters( 'odw_catalog_query_args', $args, $config, $request, $state );
	}

	// -------------------------------------------------------------------------
	// Daten je Listeneintrag
	// -------------------------------------------------------------------------

	/**
	 * Sammelt die Anzeige-Daten eines Listeneintrags.
	 *
	 * Ausschließlich get_post_meta() auf die einfachen Felder — nach der
	 * WP_Query liegen sie im Meta-Cache, jeder Aufruf kostet keine Abfrage.
	 * Format, Größe, Lizenz und Ziel laufen über dieselben Helfer wie die Karte.
	 *
	 * @param WP_Post $post          Datensatz.
	 * @param int     $excerpt_words Länge des Auszugs in Wörtern, 0 = keiner.
	 * @return array{id: int, title: string, modified: string, excerpt: string, themes: array<int, string>, format: string, size: string, license: string, access: string, target: array{url: string, kind: string}}
	 */
	public static function item_data( WP_Post $post, int $excerpt_words ): array {
		$id = (int) $post->ID;

		$title = get_the_title( $post );
		if ( '' === trim( $title ) ) {
			$title = sprintf(
				/* translators: %d: post ID of an untitled dataset */
				__( '(ohne Titel, ID %d)', 'open-data-wizard' ),
				$id
			);
		}

		// Datei aus der Mediathek, ohne filesize(): N Dateisystemzugriffe je
		// Listenseite lohnen nicht, die Detailansicht ermittelt die Größe. Für
		// einen gelöschten Anhang liefert file_info() ohnehin leere Werte —
		// nur fragte wp_get_attachment_url() dafür jedes Mal einzeln nach.
		$file = isset( self::$missing_files[ (int) get_post_meta( $id, '_odw_file_id', true ) ] )
			? array(
				'url'    => '',
				'format' => '',
				'size'   => '',
			)
			: ODW_Shortcode::file_info( $id, false );

		$format = self::effective_format( $id, $file['format'] );

		$size = $file['size'];
		if ( '' === $size ) {
			$bytes = (int) self::meta( $id, '_odw_byte_size' );
			if ( $bytes > 0 ) {
				$size = ODW_Shortcode::format_bytes( $bytes );
			}
		}

		$license = self::select_meta( $id, '_odw_license', 'license' );
		$custom  = 'sonstige' === $license ? self::meta( $id, '_odw_license_custom' ) : '';

		$excerpt = '';
		if ( $excerpt_words > 0 ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( self::meta( $id, '_odw_description' ) ), $excerpt_words, ' …' );
		}

		return array(
			'id'       => $id,
			'title'    => $title,
			'modified' => self::modified_date( $post ),
			'excerpt'  => $excerpt,
			'themes'   => self::theme_labels( $id ),
			'format'   => $format,
			'size'     => $size,
			'license'  => self::license_label( $license, $custom ),
			'access'   => self::access_label( self::select_meta( $id, '_odw_access_rights', 'access_rights' ) ),
			'target'   => ODW_Shortcode::download_target(
				self::meta( $id, '_odw_download_url' ),
				$file['url'],
				self::meta( $id, '_odw_access_url' )
			),
		);
	}

	/**
	 * Wirksames Format: das im Formular gewählte, sonst das der Datei — wie in
	 * der Karte. Quelle auch für den Index _odw_format_index.
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $file_format Format aus ODW_Shortcode::file_info().
	 * @return string
	 */
	public static function effective_format( int $post_id, string $file_format ): string {
		$format = self::select_meta( $post_id, '_odw_format', 'format' );
		return '' !== $format ? $format : $file_format;
	}

	/**
	 * Wirksame Lizenz-URI: die gewählte, bei „sonstige" die eigene. Quelle für
	 * den Index _odw_license_index.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function effective_license( int $post_id ): string {
		$license = self::select_meta( $post_id, '_odw_license', 'license' );
		return 'sonstige' === $license ? self::meta( $post_id, '_odw_license_custom' ) : $license;
	}

	/**
	 * Einfacher Meta-Wert, getrimmt.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta-Schlüssel mit führendem Unterstrich.
	 * @return string
	 */
	private static function meta( int $post_id, string $key ): string {
		return trim( (string) get_post_meta( $post_id, $key, true ) );
	}

	/**
	 * Wert eines Auswahlfelds — so, wie Carbon Fields ihn der Karte liefert.
	 *
	 * Zwei Regeln von Carbon Fields gelten auch hier:
	 * - Fehlt die Meta-Zeile ganz, nimmt es den Standardwert des Felds
	 *   (Field::get_value_from_datastore()): bei der Lizenz die Standard-Lizenz
	 *   aus den Einstellungen, beim Zugriffsrecht „öffentlich".
	 * - Ist der Wert keine der Optionen (etwa „csv" klein aus einem Import),
	 *   nimmt es die erste Option, bei allen drei Feldern also ''
	 *   (Select_Field::get_formatted_value(), strikter Vergleich ohne Trimmen).
	 * Sonst zeigte die Liste ein Badge, das Karte und JSON-LD nicht haben —
	 * oder umgekehrt.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta-Schlüssel mit führendem Unterstrich.
	 * @param string $field   format | license | access_rights.
	 * @return string
	 */
	private static function select_meta( int $post_id, string $key, string $field ): string {
		if ( ! class_exists( 'ODW_Fields' ) ) {
			return self::meta( $post_id, $key );
		}

		static $valid = array();
		if ( ! isset( $valid[ $field ] ) ) {
			switch ( $field ) {
				case 'format':
					$options = ODW_Fields::get_format_options();
					break;
				case 'license':
					$options = ODW_Fields::get_license_options();
					break;
				default:
					$options = ODW_Fields::get_access_rights_options();
			}
			$valid[ $field ] = array_map( 'strval', array_keys( $options ) );
		}

		// metadata_exists() liest denselben vorgeladenen Meta-Cache — keine Abfrage.
		if ( metadata_exists( 'post', $post_id, $key ) ) {
			$raw = (string) get_post_meta( $post_id, $key, true );
		} elseif ( 'license' === $field ) {
			$raw = class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_license' ) : '';
		} elseif ( 'access_rights' === $field ) {
			$raw = self::ACCESS_PUBLIC;
		} else {
			$raw = '';
		}

		return in_array( $raw, $valid[ $field ], true ) ? trim( $raw ) : '';
	}

	/**
	 * Änderungsdatum im Datumsformat der Website.
	 *
	 * @param WP_Post $post Datensatz.
	 * @return string
	 */
	private static function modified_date( WP_Post $post ): string {
		$modified = self::meta( (int) $post->ID, '_odw_modified' );
		if ( '' !== $modified ) {
			$formatted = mysql2date( (string) get_option( 'date_format' ), $modified . ' 00:00:00' );
			if ( is_string( $formatted ) && '' !== $formatted ) {
				return $formatted;
			}
		}

		$fallback = get_the_modified_date( '', $post );
		return is_string( $fallback ) ? $fallback : '';
	}

	/**
	 * Labels der Themen eines Datensatzes, aus dem flachen Themen-Spiegel.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int, string>
	 */
	private static function theme_labels( int $post_id ): array {
		static $options = null;
		if ( null === $options ) {
			$options = class_exists( 'ODW_Fields' ) ? ODW_Fields::get_theme_options_multi() : array();
		}

		$labels = array();
		foreach ( (array) get_post_meta( $post_id, '_odw_theme_index', false ) as $uri ) {
			$uri = (string) $uri;
			if ( '' === $uri ) {
				continue;
			}
			$labels[] = isset( $options[ $uri ] ) ? (string) $options[ $uri ] : $uri;
		}

		return array_values( array_unique( $labels ) );
	}

	/**
	 * Lizenz-Label über den Helfer der Karte, je Request gemerkt.
	 *
	 * ODW_Fields::get_license_label() liest für Lizenzen außerhalb der
	 * Standardauswahl jedes Mal config/licenses.txt — in einer Liste mit vielen
	 * gleichen Lizenzen wäre das dieselbe Datei viele Male.
	 *
	 * @param string $license Gespeicherte Lizenz.
	 * @param string $custom  Eigene Lizenz-URI.
	 * @return string
	 */
	private static function license_label( string $license, string $custom ): string {
		static $memo = array();
		$key         = $license . '|' . $custom;
		if ( ! isset( $memo[ $key ] ) ) {
			$memo[ $key ] = ODW_Shortcode::license_label( $license, $custom );
		}
		return $memo[ $key ];
	}

	/**
	 * Label für ein nicht öffentliches Zugriffsrecht, sonst ''.
	 *
	 * Ein leerer Wert zählt als öffentlich: Der Standardwert des Formulars steht
	 * bei importierten Datensätzen nicht in der Post-Meta.
	 *
	 * @param string $uri Gespeichertes Zugriffsrecht.
	 * @return string
	 */
	private static function access_label( string $uri ): string {
		if ( '' === $uri || self::ACCESS_PUBLIC === $uri ) {
			return '';
		}

		static $memo = array();
		if ( ! isset( $memo[ $uri ] ) ) {
			$memo[ $uri ] = class_exists( 'ODW_Fields' ) ? ODW_Fields::resolve_label( 'access_rights', $uri ) : $uri;
		}
		return $memo[ $uri ];
	}

	// -------------------------------------------------------------------------
	// Ausgabe
	// -------------------------------------------------------------------------

	/**
	 * Rendert Liste oder Detailansicht samt umschließender Sektion.
	 *
	 * @param array<string, mixed> $config Ergebnis von parse_atts().
	 * @param bool                 $block  Aufruf aus dem Block (Wrapper-Attribute).
	 * @return string
	 */
	private static function render( array $config, bool $block ): string {
		wp_enqueue_style( 'odw-frontend' );

		$get     = self::request_values();
		$request = self::parse_request( $get );
		$state   = ODW_Catalog_Filters::state( $config, ODW_Catalog_Filters::parse_get( $get ) );

		$base = self::base_url();
		$view = $request['dataset'] > 0
			? self::render_detail( $request, $state, $base )
			: self::render_list( $config, $request, $state, $base );

		$attributes = array(
			'class' => 'odw-catalog odw-catalog--' . $view['mode'],
			'id'    => self::ANCHOR,
		);
		if ( '' !== $view['labelledby'] ) {
			$attributes['aria-labelledby'] = $view['labelledby'];
		} else {
			$attributes['aria-label'] = __( 'Datenkatalog', 'open-data-wizard' );
		}

		$wrapper = $block && function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes( $attributes )
			: self::html_attributes( $attributes );

		return '<section ' . $wrapper . '>' . $view['html'] . '</section>';
	}

	/**
	 * Die GET-Werte des Katalogs, entschärft. Nicht-skalare Werte (?odw_q[]=)
	 * kommen als '' an und gelten damit als nicht gesetzt.
	 *
	 * Lizenz und Thema laufen nicht durch sanitize_text_field(): Das entfernt
	 * %XX-Sequenzen, aus einer eigenen Lizenz-URI wie …-f%C3%BCr-daten würde
	 * eine andere. Beide prüft ODW_Catalog_Filters::parse_get() danach streng
	 * (http(s)-Adresse ohne Leerraum und <>" bzw. Liste der Themen).
	 *
	 * @return array<string, string>
	 */
	public static function request_values(): array {
		$raw = array( ODW_Catalog_Filters::PARAM_LICENSE, ODW_Catalog_Filters::PARAM_THEME );
		$out = array();
		foreach ( array_merge( array( self::PARAM_PAGE, self::PARAM_DATASET ), ODW_Catalog_Filters::params() ) as $key ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Rein lesender Seitenaufruf. Eine Nonce vertrüge sich weder mit Seiten-Caches noch mit Lesezeichen; jeder Wert wird danach gegen eine Whitelist, ein Muster bzw. per absint() geprüft.
			$value       = isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? wp_unslash( (string) $_GET[ $key ] ) : '';
			$out[ $key ] = in_array( $key, $raw, true ) ? trim( wp_check_invalid_utf8( $value ) ) : sanitize_text_field( $value );
			// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}
		return $out;
	}

	/**
	 * Die Liste: Formular, Trefferzeile, Einträge, Seitennavigation.
	 *
	 * @param array<string, mixed>  $config  Geprüfte Attribute.
	 * @param array<string, int>    $request Seite und Detail-ID.
	 * @param array<string, string> $state   Filter-Zustand.
	 * @param string                $base    Basis-URL der Katalogseite.
	 * @return array{mode: string, labelledby: string, html: string}
	 */
	private static function render_list( array $config, array $request, array $state, string $base ): array {
		$html       = '';
		$labelledby = '';
		$entry_tag  = 'h2';

		if ( '' !== $config['title'] ) {
			$labelledby = self::ANCHOR . '-title';
			$entry_tag  = 'h3';
			$html      .= '<h2 class="odw-catalog__title" id="' . esc_attr( $labelledby ) . '">' . esc_html( $config['title'] ) . '</h2>';
		}

		if ( ! empty( $config['themes_invalid'] ) && current_user_can( 'manage_open_data' ) ) {
			$html .= '<p class="odw-catalog__notice">' . esc_html(
				sprintf(
					/* translators: %s: comma-separated list of unrecognised theme values */
					__( 'Hinweis für die Redaktion, nur für Sie sichtbar: Diese Themen-Angaben wurden nicht erkannt und ignoriert: %s. Verwenden Sie EU-Codes wie EDUC oder SOCI.', 'open-data-wizard' ),
					implode( ', ', $config['themes_invalid'] )
				)
			) . '</p>';
		}

		if ( '' !== $config['license_invalid'] && current_user_can( 'manage_open_data' ) ) {
			$html .= '<p class="odw-catalog__notice">' . esc_html(
				sprintf(
					/* translators: %s: unrecognised licence value from the shortcode attribute */
					__( 'Hinweis für die Redaktion, nur für Sie sichtbar: Die Lizenz-Angabe „%s" wurde nicht erkannt. Verwenden Sie eine Kurzform wie cc-by oder die Lizenz-URI.', 'open-data-wizard' ),
					$config['license_invalid']
				)
			) . '</p>';
		}

		// Sind ALLE Themen-Angaben unbekannt oder ist die Lizenz unbekannt, zeigt
		// der Katalog nichts — und nicht stillschweigend alles: Das wäre das
		// Gegenteil dessen, was eingestellt ist.
		if ( ( ! empty( $config['themes_invalid'] ) && empty( $config['themes'] ) ) || '' !== $config['license_invalid'] ) {
			$html .= self::empty_state();
			return array(
				'mode'       => 'list',
				'labelledby' => $labelledby,
				'html'       => $html,
			);
		}

		$demo_id    = self::demo_id();
		$state_args = ODW_Catalog_Filters::state_args( $state );
		$reset      = self::page_url( $base, 1, 0, self::ANCHOR );

		// Die Facetten zählen genau die Datensätze, die auch die Liste zeigen
		// kann — mit show_demo also einschließlich des Beispiels.
		$exclude = empty( $config['show_demo'] ) ? $demo_id : 0;
		$html   .= ODW_Catalog_Filters::render_form( $config, $state, ODW_Catalog_Filters::conditions( $config, $state, $exclude ), $base, $reset, ODW_Catalog_Filters::short_query( self::request_values() ) );

		$query = new WP_Query( self::build_query_args( $config, $request, $demo_id, $state ) );
		$posts = array_values(
			array_filter(
				(array) $query->posts,
				static function ( $post ): bool {
					return $post instanceof WP_Post;
				}
			)
		);
		$total = (int) $query->found_posts;
		$pages = (int) $query->max_num_pages;

		// Seite und Position aus der Abfrage, wie sie tatsächlich lief — nicht
		// aus Attribut und Adresse: odw_catalog_query_args oder pre_get_posts
		// dürfen posts_per_page und paged ändern.
		$page = max( 1, absint( $query->get( 'paged' ) ) );

		// Eine leere Seite hinter Seite 1 liegt jenseits des Endes. found_posts
		// taugt hier nicht zur Unterscheidung: WP_Query zählt die Treffer nur,
		// wenn die angefragte Seite welche enthält (set_found_posts() bricht bei
		// leerem Ergebnis ab) — es bleibt dann immer 0.
		if ( empty( $posts ) && $page > 1 ) {
			$html .= '<p class="odw-catalog__empty">' . esc_html__( 'Diese Seite gibt es nicht.', 'open-data-wizard' ) . ' '
				. '<a href="' . esc_url( self::page_url( $base, 1, 0, self::ANCHOR, $state_args ) ) . '">' . esc_html__( 'Zur ersten Seite', 'open-data-wizard' ) . '</a></p>';
		} elseif ( empty( $posts ) ) {
			$html .= self::empty_state( ODW_Catalog_Filters::is_active( $state ) ? $reset : '' );
		} else {
			self::prime_attachments( $posts );

			$from = self::first_position( (array) $query->query_vars );
			$to   = $from + count( $posts ) - 1;

			$html .= '<p class="odw-catalog__count">' . esc_html( self::count_label( $from, $to, $total ) ) . '</p>';
			$html .= '<ul class="odw-catalog__list">';
			foreach ( $posts as $post ) {
				$html .= self::render_entry( self::item_data( $post, (int) $config['excerpt'] ), $entry_tag, $base, $page, $state_args );
			}
			$html .= '</ul>';
			$html .= self::pagination( $base, $page, $pages, $state_args );
		}

		return array(
			'mode'       => 'list',
			'labelledby' => $labelledby,
			'html'       => $html,
		);
	}

	/**
	 * Ein Listeneintrag.
	 *
	 * @param array<string, mixed>  $item       Ergebnis von item_data().
	 * @param string                $title_tag  h2 oder h3.
	 * @param string                $base       Basis-URL.
	 * @param int                   $page       Aktuelle Listenseite (für den Rückweg).
	 * @param array<string, string> $state_args Filter-Zustand für den Rückweg.
	 * @return string
	 */
	private static function render_entry( array $item, string $title_tag, string $base, int $page, array $state_args = array() ): string {
		$id       = (int) $item['id'];
		$title_id = 'odw-ds-' . $id . '-title';
		$detail   = self::page_url( $base, $page, $id, self::ANCHOR, $state_args );

		$out  = '<li class="odw-catalog__item" id="odw-ds-' . $id . '">';
		$out .= '<article class="odw-entry" aria-labelledby="' . esc_attr( $title_id ) . '">';
		$out .= '<' . $title_tag . ' class="odw-entry__title" id="' . esc_attr( $title_id ) . '">'
			. '<a href="' . esc_url( $detail ) . '">' . esc_html( $item['title'] ) . '</a>'
			. '</' . $title_tag . '>';

		if ( '' !== $item['modified'] ) {
			$out .= '<p class="odw-entry__meta">' . esc_html(
				sprintf(
					/* translators: %s: formatted date of the last update */
					__( 'Aktualisiert: %s', 'open-data-wizard' ),
					$item['modified']
				)
			) . '</p>';
		}

		if ( '' !== $item['excerpt'] ) {
			$out .= '<p class="odw-entry__excerpt">' . esc_html( $item['excerpt'] ) . '</p>';
		}

		$badges = array();
		foreach ( $item['themes'] as $theme ) {
			$badges[] = array( 'theme', $theme );
		}
		foreach ( array( 'format', 'size', 'license', 'access' ) as $kind ) {
			if ( '' !== $item[ $kind ] ) {
				$badges[] = array( $kind, $item[ $kind ] );
			}
		}
		if ( ! empty( $badges ) ) {
			$out .= '<p class="odw-entry__badges">';
			foreach ( $badges as $badge ) {
				$out .= '<span class="odw-badge odw-badge--' . esc_attr( $badge[0] ) . '">' . esc_html( $badge[1] ) . '</span> ';
			}
			$out .= '</p>';
		}

		$target = $item['target'];
		if ( '' !== $target['url'] ) {
			// Eine echte Datei bekommt „Datei herunterladen" samt download-Attribut.
			// Die accessURL darf in DCAT auch eine Webseite sein — dort wäre beides
			// irreführend. Der Titel im unsichtbaren Teil unterscheidet die vielen
			// gleichlautenden Links für Screenreader.
			$is_file = 'file' === $target['kind'];
			$out    .= '<p class="odw-entry__actions"><a class="odw-entry__action" href="' . esc_url( $target['url'] ) . '"' . ( $is_file ? ' download' : '' ) . '>'
				. ( $is_file ? esc_html__( 'Datei herunterladen', 'open-data-wizard' ) : esc_html__( 'Zum Datenangebot', 'open-data-wizard' ) )
				. '<span class="odw-sr-only">: ' . esc_html( $item['title'] ) . '</span></a></p>';
		}

		$out .= '</article></li>';

		return $out;
	}

	/**
	 * Die Detailansicht: die unveränderte Download-Karte mit Rückweg.
	 *
	 * Entwurf, Passwortschutz, Papierkorb, fremder Typ und unbekannte ID
	 * antworten gleich — Entwurfs-IDs lassen sich so nicht aufzählen.
	 *
	 * @param array<string, int>    $request Seite und Detail-ID.
	 * @param array<string, string> $state   Filter-Zustand (nur für den Rückweg).
	 * @param string                $base    Basis-URL.
	 * @return array{mode: string, labelledby: string, html: string}
	 */
	private static function render_detail( array $request, array $state, string $base ): array {
		$id    = (int) $request['dataset'];
		$valid = self::is_public_dataset( $id );

		$back = self::page_url( $base, $request['page'], 0, $valid ? 'odw-ds-' . $id : self::ANCHOR, ODW_Catalog_Filters::state_args( $state ) );
		$html = '<p class="odw-catalog__back"><a href="' . esc_url( $back ) . '"><span aria-hidden="true">←</span> '
			. esc_html__( 'Zurück zum Katalog', 'open-data-wizard' ) . '</a></p>';

		if ( $valid ) {
			$html .= ODW_Shortcode::render( array( 'id' => (string) $id ) );
		} else {
			$html .= '<p class="odw-catalog__empty">' . esc_html__( 'Datensatz nicht gefunden.', 'open-data-wizard' ) . '</p>';
		}

		return array(
			'mode'       => 'detail',
			'labelledby' => '',
			'html'       => $html,
		);
	}

	/**
	 * Darf ein Datensatz öffentlich gezeigt werden? Entwurf, Passwortschutz,
	 * Papierkorb, fremder Typ und unbekannte ID antworten gleich.
	 *
	 * @param int $id Post ID.
	 * @return bool
	 */
	public static function is_public_dataset( int $id ): bool {
		$post = $id > 0 ? get_post( $id ) : null;
		return $post instanceof WP_Post
			&& 'odw_dataset' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password;
	}

	/**
	 * Leerzustand der Liste.
	 *
	 * @param string $reset Adresse ohne Filter; '' ohne Link.
	 * @return string
	 */
	private static function empty_state( string $reset = '' ): string {
		$link = '' !== $reset
			? ' <a href="' . esc_url( $reset ) . '">' . esc_html__( 'Filter zurücksetzen', 'open-data-wizard' ) . '</a>'
			: '';
		return '<p class="odw-catalog__empty">' . esc_html__( 'Keine Datensätze gefunden.', 'open-data-wizard' ) . $link . '</p>';
	}

	/**
	 * Trefferzeile, grammatisch ohne Pluralformen der Übersetzungswerkzeuge.
	 *
	 * Die mitgelieferten i18n-Werkzeuge kennen kein _n() (bin/compile-mo.py).
	 * Vier feste Formulierungen decken alle Fälle ab, ohne „1 Datensätze".
	 *
	 * @param int $from  Erster Eintrag der Seite (1-basiert).
	 * @param int $to    Letzter Eintrag der Seite.
	 * @param int $total Treffer insgesamt.
	 * @return string
	 */
	public static function count_label( int $from, int $to, int $total ): string {
		if ( 1 === $total ) {
			return __( 'Ein Datensatz', 'open-data-wizard' );
		}

		if ( 1 === $from && $to === $total ) {
			/* translators: %d: number of datasets (always 2 or more) */
			return sprintf( __( '%d Datensätze', 'open-data-wizard' ), $total );
		}

		if ( $from === $to ) {
			/* translators: 1: position of the dataset, 2: total number of datasets */
			return sprintf( __( 'Datensatz %1$d von %2$d', 'open-data-wizard' ), $from, $total );
		}

		/* translators: 1: first dataset on this page, 2: last dataset on this page, 3: total number of datasets */
		return sprintf( __( 'Datensätze %1$d–%2$d von %3$d', 'open-data-wizard' ), $from, $to, $total );
	}

	/**
	 * Position des ersten Eintrags einer Seite (1-basiert), aus den
	 * Abfrage-Variablen — nach denselben Regeln wie WP_Query::get_posts():
	 * nopaging bzw. posts_per_page -1 zeigt alles ab 1, ein offset hat
	 * Vorrang vor paged.
	 *
	 * @param array<string, mixed> $vars WP_Query::$query_vars nach der Abfrage.
	 * @return int
	 */
	public static function first_position( array $vars ): int {
		$per_page = (int) ( $vars['posts_per_page'] ?? 0 );
		if ( ! empty( $vars['nopaging'] ) || $per_page < 1 ) {
			return 1;
		}

		if ( isset( $vars['offset'] ) && is_numeric( $vars['offset'] ) ) {
			return absint( $vars['offset'] ) + 1;
		}

		return ( max( 1, absint( $vars['paged'] ?? 1 ) ) - 1 ) * $per_page + 1;
	}

	/**
	 * Eigene Seitennavigation.
	 *
	 * Nicht paginate_links(): Das übernimmt alle Parameter der aktuellen Anfrage
	 * in jeden Link und liest ohne ausdrückliche Angaben die globale Abfrage.
	 *
	 * @param string                $base       Basis-URL.
	 * @param int                   $current    Aktuelle Seite.
	 * @param int                   $pages      Seiten insgesamt.
	 * @param array<string, string> $state_args Filter-Zustand.
	 * @return string
	 */
	private static function pagination( string $base, int $current, int $pages, array $state_args = array() ): string {
		if ( $pages <= 1 ) {
			return '';
		}

		$items = array();

		if ( $current > 1 ) {
			$items[] = '<li class="odw-catalog__pagination-prev"><a href="' . esc_url( self::page_url( $base, $current - 1, 0, self::ANCHOR, $state_args ) ) . '">'
				. '<span aria-hidden="true">‹</span> ' . esc_html__( 'Vorherige Seite', 'open-data-wizard' ) . '</a></li>';
		}

		$last_shown = 0;
		foreach ( self::page_window( $current, $pages ) as $n ) {
			if ( $last_shown > 0 && $n > $last_shown + 1 ) {
				$items[] = '<li class="odw-catalog__pagination-gap" aria-hidden="true">…</li>';
			}

			if ( $n === $current ) {
				$items[] = '<li><span aria-current="page">' . (int) $n . '</span></li>';
			} else {
				$items[] = '<li><a href="' . esc_url( self::page_url( $base, $n, 0, self::ANCHOR, $state_args ) ) . '">' . (int) $n . '</a></li>';
			}

			$last_shown = $n;
		}

		if ( $current < $pages ) {
			$items[] = '<li class="odw-catalog__pagination-next"><a href="' . esc_url( self::page_url( $base, $current + 1, 0, self::ANCHOR, $state_args ) ) . '">'
				. esc_html__( 'Nächste Seite', 'open-data-wizard' ) . ' <span aria-hidden="true">›</span></a></li>';
		}

		return '<nav class="odw-catalog__pagination" aria-label="' . esc_attr__( 'Seiten des Datenkatalogs', 'open-data-wizard' ) . '"><ul>'
			. implode( '', $items ) . '</ul></nav>';
	}

	/**
	 * Welche Seitenzahlen erscheinen: erste, letzte und ±2 um die aktuelle.
	 *
	 * @param int $current Aktuelle Seite.
	 * @param int $pages   Seiten insgesamt.
	 * @return array<int, int> Aufsteigend, ohne Doppelte.
	 */
	public static function page_window( int $current, int $pages ): array {
		$shown = array( 1, $pages );
		for ( $n = $current - 2; $n <= $current + 2; $n++ ) {
			if ( $n >= 1 && $n <= $pages ) {
				$shown[] = $n;
			}
		}

		$shown = array_values( array_unique( $shown ) );
		sort( $shown );

		return $shown;
	}

	/**
	 * Adresse einer Listenseite oder Detailansicht.
	 *
	 * Seite 1 kommt ohne odw_page aus, damit es genau eine Adresse für den
	 * Einstieg gibt. Vorhandene odw_-Parameter der Basis werden ersetzt, nicht
	 * verdoppelt.
	 *
	 * @param string                $base       Basis-URL der Katalogseite.
	 * @param int                   $page       Listenseite.
	 * @param int                   $dataset    Datensatz-ID für die Detailansicht, 0 für die Liste.
	 * @param string                $fragment   Sprungmarke ohne #.
	 * @param array<string, string> $state_args Filter-Zustand (ODW_Catalog_Filters::state_args()).
	 * @return string
	 */
	public static function page_url( string $base, int $page, int $dataset, string $fragment, array $state_args = array() ): string {
		$url  = remove_query_arg( self::own_params(), $base );
		$args = array();

		if ( $dataset > 0 ) {
			$args[ self::PARAM_DATASET ] = $dataset;
		}
		if ( $page > 1 ) {
			$args[ self::PARAM_PAGE ] = $page;
		}
		foreach ( $state_args as $key => $value ) {
			$args[ $key ] = rawurlencode( $value );
		}
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return '' === $fragment ? $url : $url . '#' . $fragment;
	}

	/**
	 * Adresse der Seite, auf der der Katalog steht.
	 *
	 * - Einzelseite: deren Permalink — sauber, ohne fremde Parameter. Nicht auf
	 *   einer Unterseite (<!--nextpage-->) und nicht in der Vorschau: Der
	 *   Permalink führte dort auf Seite 1 bzw. auf die veröffentlichte Fassung.
	 * - Feed und REST-Antwort: der Permalink des Beitrags, in dessen Inhalt der
	 *   Katalog steht — die aufgerufene Adresse ist dort der Feed bzw. der
	 *   Endpunkt. Feed-Schleife und REST-Controller setzen den globalen Beitrag.
	 * - Sonst (Widgets in Archiven, Blog-Startseite, Unterseite, Vorschau): die
	 *   aufgerufene Adresse ohne die eigenen Parameter (request_base()).
	 *
	 * @return string
	 */
	private static function base_url(): string {
		$subpage = max( (int) get_query_var( 'page' ), (int) get_query_var( 'paged' ) ) > 1;

		if ( is_singular() && ! $subpage && ! is_preview() ) {
			$permalink = get_permalink( get_queried_object_id() );
			if ( is_string( $permalink ) && '' !== $permalink ) {
				return $permalink;
			}
		}

		if ( is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			$permalink = get_permalink();
			if ( is_string( $permalink ) && '' !== $permalink ) {
				return $permalink;
			}
		}

		// Gesäubert in request_base(): erst nach remove_query_arg(), sonst
		// strichen esc_url_raw() Zeichen wie ^ { } aus fremden Parametern.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return self::request_base( (string) $request_uri );
	}

	/**
	 * Basis aus der aufgerufenen Adresse (REQUEST_URI): ohne die eigenen
	 * Parameter, wurzelrelativ, garantiert auf derselben Website.
	 *
	 * Die Reihenfolge zählt:
	 * 1. remove_query_arg() entfernt odw_page/odw_ds und kodiert die übrige
	 *    Query neu — ^ { } ` bleiben dabei als %5E … erhalten.
	 * 2. esc_url_raw() streicht nur noch, was nicht in eine URL gehört.
	 * 3. Zuletzt genau EIN führender Schrägstrich: REQUEST_URI kommt vom
	 *    Browser, ein Pfad wie //evil.example/ ergäbe sonst schemarelative
	 *    Links auf eine fremde Domain (redirect_canonical() räumt ihn etwa auf
	 *    Suchseiten nicht auf). Nach diesem Schritt entfernt esc_url() bei der
	 *    Ausgabe nichts mehr, was erneut // erzeugen könnte.
	 * Wurzelrelativ statt mit Schema und Host aus home_url(): Bei Sprachen auf
	 * eigenen (Sub-)Domains (etwa Polylang) bliebe der Besucher sonst nicht auf
	 * der Domain, die er aufgerufen hat.
	 *
	 * @param string $request_uri Pfad samt Query, wie im REQUEST_URI.
	 * @return string
	 */
	public static function request_base( string $request_uri ): string {
		$uri = esc_url_raw( (string) remove_query_arg( self::own_params(), $request_uri ) );

		return '/' . ltrim( $uri, '/\\' );
	}

	/**
	 * Alle eigenen GET-Parameter — Links ersetzen sie, statt sie zu verdoppeln.
	 *
	 * @return array<int, string>
	 */
	public static function own_params(): array {
		return array_merge( array( self::PARAM_PAGE, self::PARAM_DATASET ), ODW_Catalog_Filters::params() );
	}

	/**
	 * Attribute des ersten Katalogs im Inhalt, geprüft wie beim Rendern —
	 * damit der Kopfbereich weiß, was die Seite zeigen wird, bevor sie es tut.
	 *
	 * Block vor Shortcode (der Block ist nur einmal je Beitrag erlaubt). Ein
	 * maskierter [[odw_catalog]], ein auskommentierter und ein Katalog in
	 * einem synchronisierten Muster ergeben null — die Seite zeigt dann keinen
	 * (bzw. keinen erkennbaren) Katalog.
	 *
	 * @param string $content Beitragsinhalt.
	 * @return array<string, mixed>|null Ergebnis von parse_atts(), null ohne Katalog.
	 */
	public static function config_from_content( string $content ): ?array {
		if ( false !== strpos( $content, '<!-- wp:' . self::BLOCK ) && function_exists( 'parse_blocks' ) ) {
			$attributes = self::find_block_attributes( parse_blocks( $content ) );
			if ( null !== $attributes ) {
				// Wie vor dem render_callback: WordPress prüft die Attribute gegen
				// die Typen aus block.json und setzt Ungültiges auf den
				// Standardwert. Ohne diesen Schritt rechneten Kopfbereich und Liste
				// etwa bei "theme":["EDUC"] mit verschiedenen Einstellungen.
				$type = class_exists( 'WP_Block_Type_Registry' ) ? WP_Block_Type_Registry::get_instance()->get_registered( self::BLOCK ) : null;
				if ( $type instanceof WP_Block_Type ) {
					$attributes = $type->prepare_attributes_for_render( $attributes );
				}
				return self::parse_atts( self::block_to_atts( $attributes ) );
			}
		}

		// Ein auskommentierter Shortcode (<!-- [odw_catalog] -->, etwa zum
		// vorübergehenden Abschalten) gibt im Browser nichts aus.
		$scan = (string) preg_replace( '/<!--.*?-->/s', '', $content );
		if ( false === strpos( $scan, '[' . self::SHORTCODE )
			|| ! preg_match_all( '/' . get_shortcode_regex( array( self::SHORTCODE ) ) . '/', $scan, $matches, PREG_SET_ORDER )
		) {
			return null;
		}

		foreach ( $matches as $match ) {
			// [[odw_catalog]] ist maskiert und wird nicht ausgeführt (wie do_shortcode_tag()).
			if ( '[' === $match[1] && ']' === $match[6] ) {
				continue;
			}
			// Ohne Attribute liefert shortcode_parse_atts() '' — parse_atts() nimmt beides.
			return self::parse_atts( shortcode_parse_atts( $match[3] ) );
		}

		return null;
	}

	/**
	 * Attribute des Katalog-Blocks, auch verschachtelt (Gruppe, Spalten).
	 *
	 * @param array<int, array<string, mixed>> $blocks Ergebnis von parse_blocks().
	 * @return array<string, mixed>|null
	 */
	private static function find_block_attributes( array $blocks ): ?array {
		foreach ( $blocks as $block ) {
			if ( self::BLOCK === ( $block['blockName'] ?? null ) ) {
				return (array) ( $block['attrs'] ?? array() );
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$found = self::find_block_attributes( (array) $block['innerBlocks'] );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	/**
	 * Zeigt die Listenseite Einträge? Dieselbe Abfrage wie die Liste, nur nach
	 * IDs und ohne Zählung. Für Seiten hinter dem Ende (odw_page=99), die der
	 * Kopfbereich als 404 ausliefert.
	 *
	 * @param array<string, mixed>  $config  Ergebnis von parse_atts().
	 * @param array<string, int>    $request Ergebnis von parse_request().
	 * @param array<string, string> $state   Filter-Zustand.
	 * @return bool
	 */
	public static function page_has_entries( array $config, array $request, array $state ): bool {
		// Bei unbekannter Lizenz bzw. nur unbekannten Themen zeigt die Liste
		// nichts (render_list()).
		if ( '' !== $config['license_invalid'] || ( ! empty( $config['themes_invalid'] ) && empty( $config['themes'] ) ) ) {
			return false;
		}

		$args = self::build_query_args( $config, $request, self::demo_id(), $state );

		$args['fields']                 = 'ids';
		$args['no_found_rows']          = true;
		$args['update_post_meta_cache'] = false;

		$query = new WP_Query( $args );
		return ! empty( $query->posts );
	}

	/**
	 * Lädt die Anhänge aller Einträge in einer Abfrage vor.
	 *
	 * Sonst kostete wp_get_attachment_url() in file_info() pro Eintrag bis zu
	 * zwei Abfragen — die Anhänge lädt die WP_Query über die Datensätze nicht mit.
	 *
	 * @param array<int, WP_Post> $posts Datensätze der Seite.
	 */
	private static function prime_attachments( array $posts ): void {
		$ids = array();
		foreach ( $posts as $post ) {
			$file_id = (int) get_post_meta( (int) $post->ID, '_odw_file_id', true );
			if ( $file_id > 0 ) {
				$ids[] = $file_id;
			}
		}

		if ( empty( $ids ) || ! function_exists( '_prime_post_caches' ) ) {
			return;
		}

		$ids = array_values( array_unique( $ids ) );
		_prime_post_caches( $ids, false, true );

		// Was die Vorab-Abfrage nicht findet, merkt sich WordPress nicht:
		// wp_get_attachment_url() fragte für jeden verwaisten Verweis erneut
		// einzeln — bis zu per_page Abfragen pro Seitenaufruf. Nicht bei
		// ausgesetzter Cache-Aufnahme (etwa Widgets in der Customizer-Vorschau):
		// Dort fehlen auch vorhandene Anhänge im Cache.
		if ( function_exists( 'wp_cache_get_multiple' ) && ! wp_suspend_cache_addition() ) {
			foreach ( wp_cache_get_multiple( $ids, 'posts' ) as $file_id => $cached ) {
				if ( false === $cached ) {
					self::$missing_files[ (int) $file_id ] = true;
				}
			}
		}
	}

	/**
	 * ID des Beispieldatensatzes, solange er unverändert ist — sonst 0.
	 *
	 * Die Willkommensseite lädt ausdrücklich ein, das Beispiel als Vorlage zu
	 * nutzen. Wer daraus den ersten echten Datensatz macht, darf ihn nicht
	 * kommentarlos im Katalog vermissen. Die Option odw_demo_post_id bleibt
	 * dabei stehen: Sonst legte maybe_create_demo() beim nächsten Aktivieren
	 * ein neues Beispiel an.
	 *
	 * @return int
	 */
	private static function demo_id(): int {
		$id = class_exists( 'ODW_Setup' ) ? ODW_Setup::get_demo_post_id() : 0;
		if ( $id <= 0 ) {
			return 0;
		}

		$post = get_post( $id );

		return $post instanceof WP_Post && self::is_untouched( $post ) ? $id : 0;
	}

	/**
	 * Wurde der Beitrag seit dem Anlegen nie gespeichert?
	 *
	 * Beim Anlegen setzt wp_insert_post() post_modified gleich post_date;
	 * jedes spätere Speichern (wp_update_post, Schnellbearbeitung) hebt die
	 * Gleichheit auf. create_demo_dataset() schreibt danach nur noch Meta.
	 *
	 * @param WP_Post $post Beitrag.
	 * @return bool
	 */
	public static function is_untouched( WP_Post $post ): bool {
		return (string) $post->post_modified_gmt === (string) $post->post_date_gmt;
	}

	/**
	 * Baut eine Attributliste für ein HTML-Element.
	 *
	 * @param array<string, string> $attributes Name => Wert.
	 * @return string
	 */
	private static function html_attributes( array $attributes ): string {
		$out = array();
		foreach ( $attributes as $name => $value ) {
			$out[] = $name . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $out );
	}
}
