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

		register_block_type(
			ODW_PLUGIN_DIR . 'blocks/catalog',
			array( 'render_callback' => array( self::class, 'render_block' ) )
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

		wp_localize_script(
			'odw-catalog-editor',
			'odwCatalogBlock',
			array(
				'themes'   => $themes,
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
					'showDemoHelp' => __( 'Den bei der Aktivierung angelegten Beispieldatensatz mit auflisten.', 'open-data-wizard' ),
					'rendered'     => __( 'Wird im Frontend als Liste aller veröffentlichten Datensätze ausgegeben.', 'open-data-wizard' ),
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
		return array(
			'per_page'  => (string) ( $attributes['perPage'] ?? self::PER_PAGE_DEFAULT ),
			'orderby'   => (string) ( $attributes['orderby'] ?? 'title' ),
			'theme'     => (string) ( $attributes['theme'] ?? '' ),
			'title'     => (string) ( $attributes['title'] ?? '' ),
			'excerpt'   => (string) ( $attributes['excerpt'] ?? self::EXCERPT_DEFAULT ),
			'show_demo' => empty( $attributes['showDemo'] ) ? '0' : '1',
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
	 * @param array<string, string>|string $atts Rohe Attribute.
	 * @return array{per_page: int, orderby: string, themes: array<int, string>, themes_invalid: array<int, string>, title: string, excerpt: int, show_demo: bool}
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

		return array(
			'per_page'       => min( $per_page, self::PER_PAGE_MAX ),
			'orderby'        => 'modified' === strtolower( trim( (string) $atts['orderby'] ) ) ? 'modified' : 'title',
			'themes'         => $themes['valid'],
			'themes_invalid' => $themes['invalid'],
			'title'          => sanitize_text_field( (string) $atts['title'] ),
			'excerpt'        => $excerpt,
			'show_demo'      => in_array( strtolower( trim( (string) $atts['show_demo'] ) ), array( '1', 'true', 'yes', 'ja' ), true ),
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
	 * @param array<string, mixed> $config  Ergebnis von parse_atts().
	 * @param array<string, int>   $request Ergebnis von parse_request().
	 * @param int                  $demo_id ID des Beispieldatensatzes oder 0.
	 * @return array<string, mixed>
	 */
	public static function build_query_args( array $config, array $request, int $demo_id ): array {
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

		/**
		 * Filtert die WP_Query-Argumente des Frontend-Katalogs.
		 *
		 * @param array $args    Query arguments.
		 * @param array $config  Geprüfte Shortcode-Attribute.
		 * @param array $request Seite und Detail-ID.
		 */
		return (array) apply_filters( 'odw_catalog_query_args', $args, $config, $request );
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
		// Listenseite lohnen nicht, die Detailansicht ermittelt die Größe.
		$file = ODW_Shortcode::file_info( $id, false );

		$format = self::meta( $id, '_odw_format' );
		if ( '' === $format ) {
			$format = $file['format'];
		}

		$size = $file['size'];
		if ( '' === $size ) {
			$bytes = (int) self::meta( $id, '_odw_byte_size' );
			if ( $bytes > 0 ) {
				$size = ODW_Shortcode::format_bytes( $bytes );
			}
		}

		$license = self::meta( $id, '_odw_license' );
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
			'access'   => self::access_label( self::meta( $id, '_odw_access_rights' ) ),
			'target'   => ODW_Shortcode::download_target(
				self::meta( $id, '_odw_download_url' ),
				$file['url'],
				self::meta( $id, '_odw_access_url' )
			),
		);
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

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Rein lesender Seitenaufruf. Eine Nonce vertrüge sich weder mit Seiten-Caches noch mit Lesezeichen; beide Werte gehen nur als absint() weiter.
		$request = self::parse_request(
			array(
				self::PARAM_PAGE    => isset( $_GET[ self::PARAM_PAGE ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::PARAM_PAGE ] ) ) : '',
				self::PARAM_DATASET => isset( $_GET[ self::PARAM_DATASET ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::PARAM_DATASET ] ) ) : '',
			)
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$base = self::base_url();
		$view = $request['dataset'] > 0
			? self::render_detail( $request, $base )
			: self::render_list( $config, $request, $base );

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
	 * Die Liste: Trefferzeile, Einträge, Seitennavigation.
	 *
	 * @param array<string, mixed> $config  Geprüfte Attribute.
	 * @param array<string, int>   $request Seite und Detail-ID.
	 * @param string               $base    Basis-URL der Katalogseite.
	 * @return array{mode: string, labelledby: string, html: string}
	 */
	private static function render_list( array $config, array $request, string $base ): array {
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

		// Sind ALLE Themen-Angaben unbekannt, zeigt der Katalog nichts — und nicht
		// stillschweigend alles: Das wäre das Gegenteil dessen, was eingestellt ist.
		if ( ! empty( $config['themes_invalid'] ) && empty( $config['themes'] ) ) {
			$html .= self::empty_state();
			return array(
				'mode'       => 'list',
				'labelledby' => $labelledby,
				'html'       => $html,
			);
		}

		$query = new WP_Query( self::build_query_args( $config, $request, self::demo_id() ) );
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

		if ( 0 === $total ) {
			$html .= self::empty_state();
		} elseif ( empty( $posts ) ) {
			$html .= '<p class="odw-catalog__empty">' . esc_html__( 'Diese Seite gibt es nicht.', 'open-data-wizard' ) . ' '
				. '<a href="' . esc_url( self::page_url( $base, 1, 0, self::ANCHOR ) ) . '">' . esc_html__( 'Zur ersten Seite', 'open-data-wizard' ) . '</a></p>';
		} else {
			self::prime_attachments( $posts );

			$from = ( $request['page'] - 1 ) * (int) $config['per_page'] + 1;
			$to   = $from + count( $posts ) - 1;

			$html .= '<p class="odw-catalog__count">' . esc_html( self::count_label( $from, $to, $total ) ) . '</p>';
			$html .= '<ul class="odw-catalog__list">';
			foreach ( $posts as $post ) {
				$html .= self::render_entry( self::item_data( $post, (int) $config['excerpt'] ), $entry_tag, $base, $request['page'] );
			}
			$html .= '</ul>';
			$html .= self::pagination( $base, $request['page'], $pages );
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
	 * @param array<string, mixed> $item      Ergebnis von item_data().
	 * @param string               $title_tag h2 oder h3.
	 * @param string               $base      Basis-URL.
	 * @param int                  $page      Aktuelle Listenseite (für den Rückweg).
	 * @return string
	 */
	private static function render_entry( array $item, string $title_tag, string $base, int $page ): string {
		$id       = (int) $item['id'];
		$title_id = 'odw-ds-' . $id . '-title';
		$detail   = self::page_url( $base, $page, $id, self::ANCHOR );

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
	 * @param array<string, int> $request Seite und Detail-ID.
	 * @param string             $base    Basis-URL.
	 * @return array{mode: string, labelledby: string, html: string}
	 */
	private static function render_detail( array $request, string $base ): array {
		$id    = (int) $request['dataset'];
		$post  = get_post( $id );
		$valid = $post instanceof WP_Post
			&& 'odw_dataset' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password;

		$back = self::page_url( $base, $request['page'], 0, $valid ? 'odw-ds-' . $id : self::ANCHOR );
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
	 * Leerzustand der Liste.
	 *
	 * @return string
	 */
	private static function empty_state(): string {
		return '<p class="odw-catalog__empty">' . esc_html__( 'Keine Datensätze gefunden.', 'open-data-wizard' ) . '</p>';
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
	 * Eigene Seitennavigation.
	 *
	 * Nicht paginate_links(): Das übernimmt alle Parameter der aktuellen Anfrage
	 * in jeden Link und liest ohne ausdrückliche Angaben die globale Abfrage.
	 *
	 * @param string $base    Basis-URL.
	 * @param int    $current Aktuelle Seite.
	 * @param int    $pages   Seiten insgesamt.
	 * @return string
	 */
	private static function pagination( string $base, int $current, int $pages ): string {
		if ( $pages <= 1 ) {
			return '';
		}

		$items = array();

		if ( $current > 1 ) {
			$items[] = '<li class="odw-catalog__pagination-prev"><a href="' . esc_url( self::page_url( $base, $current - 1, 0, self::ANCHOR ) ) . '">'
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
				$items[] = '<li><a href="' . esc_url( self::page_url( $base, $n, 0, self::ANCHOR ) ) . '">' . (int) $n . '</a></li>';
			}

			$last_shown = $n;
		}

		if ( $current < $pages ) {
			$items[] = '<li class="odw-catalog__pagination-next"><a href="' . esc_url( self::page_url( $base, $current + 1, 0, self::ANCHOR ) ) . '">'
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
	 * @param string $base     Basis-URL der Katalogseite.
	 * @param int    $page     Listenseite.
	 * @param int    $dataset  Datensatz-ID für die Detailansicht, 0 für die Liste.
	 * @param string $fragment Sprungmarke ohne #.
	 * @return string
	 */
	public static function page_url( string $base, int $page, int $dataset, string $fragment ): string {
		$url  = remove_query_arg( array( self::PARAM_PAGE, self::PARAM_DATASET ), $base );
		$args = array();

		if ( $dataset > 0 ) {
			$args[ self::PARAM_DATASET ] = $dataset;
		}
		if ( $page > 1 ) {
			$args[ self::PARAM_PAGE ] = $page;
		}
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return '' === $fragment ? $url : $url . '#' . $fragment;
	}

	/**
	 * Adresse der Seite, auf der der Katalog steht.
	 *
	 * Auf einer Einzelseite deren Permalink — sauber, ohne fremde Parameter.
	 * Überall sonst (Widgets in Archiven, Blog-Startseite) die aktuelle Adresse
	 * ohne die eigenen Parameter, damit Links nie auf eine fremde Seite führen.
	 *
	 * @return string
	 */
	private static function base_url(): string {
		if ( is_singular() ) {
			$permalink = get_permalink( get_queried_object_id() );
			if ( is_string( $permalink ) && '' !== $permalink ) {
				return $permalink;
			}
		}

		return (string) remove_query_arg( array( self::PARAM_PAGE, self::PARAM_DATASET ) );
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

		if ( ! empty( $ids ) && function_exists( '_prime_post_caches' ) ) {
			_prime_post_caches( array_values( array_unique( $ids ) ), false, true );
		}
	}

	/**
	 * ID des Beispieldatensatzes oder 0.
	 *
	 * @return int
	 */
	private static function demo_id(): int {
		return class_exists( 'ODW_Setup' ) ? ODW_Setup::get_demo_post_id() : 0;
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
