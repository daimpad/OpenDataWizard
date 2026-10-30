<?php
/**
 * Kopfbereich und Cache-Hinweise des Frontend-Katalogs (Stufe 2).
 *
 * Die Detailansicht ?odw_ds=<ID> ist die einzige HTML-Adresse eines
 * Datensatzes. Damit Suchmaschinen sie als eigene Seite behandeln, setzt
 * diese Klasse je Zustand der Katalogseite:
 *
 * - ungültige ID: HTTP 404 und noindex — das Seitentemplate bleibt erhalten,
 *   die Meldung „Datensatz nicht gefunden." steht also weiter im Inhalt;
 * - gültige ID: Canonical <Seite>?odw_ds=<ID>, Titel „Datensatz – Seite",
 *   <link rel="alternate"> auf JSON-LD und Turtle des Datensatzes;
 * - Listenseite ab 2: Canonical mit odw_page, damit Crawler jede Seite der
 *   Liste (und damit jeden Titel-Link) erreichen;
 * - Filter, Suche oder Sortierung: noindex,follow — dieselben Datensätze in
 *   beliebig vielen Kombinationen sind Duplikate. Der Canonical bleibt der
 *   der Seite.
 *
 * Erkannt wird der Katalog nur im Inhalt der aufgerufenen Seite (Shortcode
 * oder Block). In Widgets und Templates greift nichts davon; die Ausgabe der
 * Seite selbst ändert sich dort nicht.
 *
 * Bewusst KEINE 301-Normalisierung: Browser speichern 301 dauerhaft, und
 * Canonical plus noindex reichen.
 *
 * Seiten-Caches: Ein Datensatz hat keine eigene URL, also leert kein
 * Cache-Plugin beim Speichern die Katalogseite. Die Action odw_catalog_changed
 * meldet jede Änderung, die den Katalog betrifft — zum Anbinden eines Purge.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Head integration and cache hints of the frontend catalogue.
 *
 * @package OpenDataWizard
 */
class ODW_Catalog_Seo {

	/**
	 * Zustand des aktuellen Requests, einmal ermittelt (null: noch nicht,
	 * false: keine Katalogseite).
	 *
	 * @var array<string, mixed>|false|null
	 */
	private static $head = null;

	/**
	 * Bereits gemeldete Datensätze dieses Requests.
	 *
	 * @var array<int, true>
	 */
	private static $announced = array();

	/**
	 * Registers the head hooks and the change notifications.
	 */
	public static function init(): void {
		// Die Filter hängen von Anfang an; was sie tun, ermittelt head() erst
		// beim ersten Aufruf — dann steht die Hauptabfrage fest. So spielt es
		// keine Rolle, wie früh ein SEO-Plugin seine Ausgabe berechnet.
		// Nach redirect_canonical (10): Eine fällige Weiterleitung ist dann
		// schon passiert, und der Status gilt für die Seite, die ausgeliefert wird.
		add_action( 'template_redirect', array( self::class, 'send_headers' ), 11 );
		add_filter( 'get_canonical_url', array( self::class, 'filter_canonical' ), 10, 2 );
		add_filter( 'document_title_parts', array( self::class, 'filter_title_parts' ) );
		add_filter( 'wp_robots', array( self::class, 'filter_robots' ) );
		add_action( 'wp_head', array( self::class, 'print_alternates' ) );

		// Yoast SEO entfernt rel_canonical und ersetzt den Titel (über
		// pre_get_document_title) — es braucht eigene Filter. robots kommt bei
		// Yoast über den Core-Filter wp_robots an, aber Yoast unterdrückt
		// Canonical und rel prev/next nur nach seinem eigenen robots-Wert.
		add_filter( 'Yoast\WP\SEO\allowlist_permalink_vars', array( self::class, 'allow_params' ) );
		add_filter( 'wpseo_robots_array', array( self::class, 'yoast_robots' ), 20, 2 );
		add_filter( 'wpseo_canonical', array( self::class, 'yoast_url' ), 20, 2 );
		add_filter( 'wpseo_opengraph_url', array( self::class, 'yoast_url' ), 20, 2 );
		add_filter( 'wpseo_title', array( self::class, 'yoast_title' ), 20, 2 );
		add_filter( 'wpseo_opengraph_title', array( self::class, 'yoast_title' ), 20, 2 );
		add_filter( 'wpseo_schema_webpage', array( self::class, 'yoast_schema_webpage' ), 20, 2 );

		// Rank Math ersetzt Titel, Canonical und robots (es leert am Hook wp
		// sogar alle wp_robots-Filter). Priorität 20, nach den eigenen
		// Integrationen von Rank Math (WooCommerce, Polylang …) auf 10.
		add_filter( 'rank_math/frontend/robots', array( self::class, 'rank_math_robots' ), 20 );
		add_filter( 'rank_math/frontend/canonical', array( self::class, 'rank_math_canonical' ), 20 );
		add_filter( 'rank_math/frontend/title', array( self::class, 'rank_math_title' ), 20 );
		add_filter( 'rank_math/opengraph/facebook/og_title', array( self::class, 'rank_math_social_title' ), 20 );
		add_filter( 'rank_math/opengraph/twitter/twitter_title', array( self::class, 'rank_math_social_title' ), 20 );

		// All in One SEO: dieselben drei Ausgaben über eigene Filter; es leert
		// vor wp_head ebenfalls alle wp_robots-Filter.
		add_filter( 'aioseo_canonical_url', array( self::class, 'aioseo_canonical' ), 20 );
		add_filter( 'aioseo_title', array( self::class, 'aioseo_title' ), 20 );
		add_filter( 'aioseo_robots_meta', array( self::class, 'aioseo_robots' ), 20 );

		add_action( 'wp_after_insert_post', array( self::class, 'after_insert' ), 10, 4 );
		add_action( 'deleted_post', array( self::class, 'after_delete' ), 10, 2 );
	}

	// -------------------------------------------------------------------------
	// Zustand
	// -------------------------------------------------------------------------

	/**
	 * Was gilt für die Katalogseite in diesem Zustand? Reine Funktion.
	 *
	 * @param array<string, int> $request Ergebnis von ODW_Catalog::parse_request().
	 * @param bool               $variant Filter, Suche oder Sortierung gesetzt.
	 * @param WP_Post|null       $dataset Öffentlicher Datensatz der Detailansicht, sonst null.
	 * @return array{status: int, noindex: bool, canonical_args: array<string, int>, title: string, dataset: int}
	 */
	public static function head_state( array $request, bool $variant, ?WP_Post $dataset ): array {
		$state = array(
			'status'         => 200,
			'noindex'        => false,
			'canonical_args' => array(),
			'title'          => '',
			'dataset'        => 0,
		);

		if ( (int) $request['dataset'] > 0 ) {
			if ( null === $dataset ) {
				$state['status']  = 404;
				$state['noindex'] = true;
				return $state;
			}

			// Der Listenzustand, den die Detailadresse für den Rückweg trägt,
			// gehört nicht in den Canonical: Es gibt genau eine Adresse.
			$state['canonical_args'] = array( ODW_Catalog::PARAM_DATASET => (int) $dataset->ID );
			$state['title']          = (string) get_the_title( $dataset );
			$state['dataset']        = (int) $dataset->ID;
			return $state;
		}

		if ( $variant ) {
			$state['noindex'] = true;
			return $state;
		}

		if ( (int) $request['page'] > 1 ) {
			$state['canonical_args'] = array( ODW_Catalog::PARAM_PAGE => (int) $request['page'] );
		}

		return $state;
	}

	/**
	 * Zustand des aktuellen Requests, oder false, wenn die Seite keinen
	 * Katalog enthält.
	 *
	 * @return array<string, mixed>|false
	 */
	private static function head() {
		if ( null !== self::$head ) {
			return self::$head;
		}

		// Vor der Hauptabfrage lässt sich nichts entscheiden — dann später.
		if ( ! did_action( 'wp' ) ) {
			return false;
		}

		self::$head = false;

		if ( ! is_singular() || is_preview() ) {
			return false;
		}

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post || ! self::has_catalog( $post ) ) {
			return false;
		}

		$get     = ODW_Catalog::request_values();
		$request = ODW_Catalog::parse_request( $get );
		$id      = (int) $request['dataset'];
		$dataset = $id > 0 && ODW_Catalog::is_public_dataset( $id ) ? get_post( $id ) : null;

		self::$head = self::head_state(
			$request,
			ODW_Catalog_Filters::is_active( ODW_Catalog_Filters::parse_get( $get ) ),
			$dataset instanceof WP_Post ? $dataset : null
		) + array( 'post_id' => (int) $post->ID );

		return self::$head;
	}

	/**
	 * Enthält der Beitrag einen Katalog (Shortcode oder Block)?
	 *
	 * @param WP_Post $post Beitrag.
	 * @return bool
	 */
	public static function has_catalog( WP_Post $post ): bool {
		$content = (string) $post->post_content;
		if ( function_exists( 'has_block' ) && has_block( ODW_Catalog::BLOCK, $post ) ) {
			return true;
		}
		return has_shortcode( $content, ODW_Catalog::SHORTCODE );
	}

	// -------------------------------------------------------------------------
	// Core
	// -------------------------------------------------------------------------

	/**
	 * Sendet 404 für eine ungültige Detail-ID — vor jeder Ausgabe, ohne
	 * set_404(): Das Seitentemplate mit der Meldung bleibt erhalten.
	 *
	 * Das noindex zusätzlich als HTTP-Header: Der wirkt auch dann, wenn ein
	 * SEO-Plugin das robots-Meta-Tag selbst schreibt und keinen der Filter
	 * unten kennt.
	 */
	public static function send_headers(): void {
		$head = self::head();
		if ( false === $head || headers_sent() ) {
			return;
		}

		if ( 404 === $head['status'] ) {
			status_header( 404 );
			nocache_headers();
		}
		if ( $head['noindex'] ) {
			header( 'X-Robots-Tag: noindex, follow' );
		}
	}

	/**
	 * Canonical für eine Adresse der Katalogseite. Reine Funktion.
	 *
	 * @param string               $url  Canonical, den Core oder ein SEO-Plugin ermittelt hat.
	 * @param array<string, mixed> $head Ergebnis von head_state().
	 * @return string Leer: kein Canonical (ungültige Detail-ID).
	 */
	public static function canonical_for( string $url, array $head ): string {
		if ( 404 === (int) $head['status'] ) {
			return '';
		}
		if ( '' === $url || empty( $head['canonical_args'] ) ) {
			return $url;
		}
		return (string) add_query_arg( $head['canonical_args'], remove_query_arg( ODW_Catalog::own_params(), $url ) );
	}

	/**
	 * Gilt ein Filteraufruf für die aufgerufene Katalogseite?
	 *
	 * Canonical- und Titel-Filter laufen auch für andere Beiträge (Schema-Graph,
	 * REST-Head, wp_get_canonical_url() für beliebige Posts).
	 *
	 * @param int $post_id ID des Beitrags, für den der Filter gerade läuft.
	 * @return array<string, mixed>|false Zustand oder false.
	 */
	private static function head_for( int $post_id ) {
		$head = self::head();
		return false !== $head && (int) $head['post_id'] === $post_id ? $head : false;
	}

	/**
	 * Canonical der Katalogseite um odw_ds bzw. odw_page ergänzen.
	 *
	 * @param string|false $url  Canonical, den Core ermittelt hat.
	 * @param WP_Post      $post Beitrag, für den er gilt.
	 * @return string|false
	 */
	public static function filter_canonical( $url, $post ) {
		$head = self::head_for( (int) $post->ID );
		if ( false === $head || ! is_string( $url ) ) {
			return $url;
		}

		// '' unterdrückt das Tag (rel_canonical() gibt nur Nicht-Leeres aus).
		return self::canonical_for( $url, $head );
	}

	/**
	 * Titel der Detailansicht: „Datensatz – Katalogseite – Website".
	 *
	 * @param array<string, string> $parts Teile des Dokumenttitels.
	 * @return array<string, string>
	 */
	public static function filter_title_parts( $parts ): array {
		$parts = (array) $parts;
		$head  = self::head();
		if ( false === $head || '' === $head['title'] ) {
			return $parts;
		}

		return self::title_parts( $parts, (string) $head['title'] );
	}

	/**
	 * Setzt den Datensatztitel vor den Seitentitel. Reine Funktion.
	 *
	 * @param array<string, string> $parts   Teile des Dokumenttitels.
	 * @param string                $dataset Datensatztitel.
	 * @return array<string, string>
	 */
	public static function title_parts( array $parts, string $dataset ): array {
		$page = (string) ( $parts['title'] ?? '' );
		unset( $parts['title'] );

		return array_merge(
			array(
				'title'       => $dataset,
				'odw_catalog' => $page,
			),
			$parts
		);
	}

	/**
	 * Robots „noindex,follow" für Fehler und gefilterte Varianten.
	 *
	 * @param array<string, bool|string> $robots Robots-Direktiven.
	 * @return array<string, bool|string>
	 */
	public static function filter_robots( $robots ): array {
		$robots = (array) $robots;
		$head   = self::head();
		if ( false === $head || ! $head['noindex'] ) {
			return $robots;
		}

		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
		return $robots;
	}

	/**
	 * <link rel="alternate"> auf die maschinenlesbaren Fassungen des
	 * Datensatzes — dieselben Adressen wie der Link „Metadaten JSON" der Karte.
	 */
	public static function print_alternates(): void {
		$head = self::head();
		if ( false === $head || $head['dataset'] <= 0 ) {
			return;
		}

		$url = rest_url( 'datenatlas/v1/datasets/' . (int) $head['dataset'] );
		printf( '<link rel="alternate" type="application/ld+json" href="%s">' . "\n", esc_url( $url ) );
		printf( '<link rel="alternate" type="text/turtle" href="%s">' . "\n", esc_url( add_query_arg( 'format', 'turtle', $url ) ) );
	}

	// -------------------------------------------------------------------------
	// Yoast SEO
	// -------------------------------------------------------------------------

	/**
	 * Die eigenen Parameter für Yoasts „Crawl-Bereinigung" freigeben.
	 *
	 * Ist dort „Permalinks bereinigen" an, leitet Yoast jeden unbekannten
	 * GET-Parameter einer Seite per 301 auf den Permalink um — schon auf
	 * template_redirect@1 und nur für Besucher ohne Anmeldung. Detailansicht,
	 * Blättern und Filter wären dann für genau die Besucher kaputt, die sie
	 * brauchen.
	 *
	 * @param array<int, string> $vars Erlaubte Parameter.
	 * @return array<int, string>
	 */
	public static function allow_params( $vars ): array {
		return array_values( array_unique( array_merge( (array) $vars, ODW_Catalog::own_params() ) ) );
	}

	/**
	 * Yoast-Objekt der aufgerufenen Katalogseite?
	 *
	 * @param mixed $presentation Indexable_Presentation.
	 * @return array<string, mixed>|false Zustand oder false.
	 */
	private static function yoast_head( $presentation ) {
		$model = is_object( $presentation ) && isset( $presentation->model ) ? $presentation->model : null;
		if ( ! is_object( $model ) || 'post' !== ( $model->object_type ?? '' ) ) {
			return false;
		}
		return self::head_for( (int) ( $model->object_id ?? 0 ) );
	}

	/**
	 * Robots in Yoasts eigener Form ('index' => 'noindex').
	 *
	 * @param array<string, string> $robots       Robots-Werte.
	 * @param mixed                 $presentation Indexable_Presentation.
	 * @return array<string, string>
	 */
	public static function yoast_robots( $robots, $presentation ): array {
		$robots = (array) $robots;
		$head   = self::yoast_head( $presentation );
		if ( false === $head || ! $head['noindex'] ) {
			return $robots;
		}

		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
		unset( $robots['max-snippet'], $robots['max-image-preview'], $robots['max-video-preview'], $robots['imageindex'], $robots['archive'], $robots['snippet'] );
		return $robots;
	}

	/**
	 * Canonical und og:url. Yoast ruft den Canonical-Filter bei noindex gar
	 * nicht erst auf.
	 *
	 * @param string $url          Adresse.
	 * @param mixed  $presentation Indexable_Presentation.
	 * @return string
	 */
	public static function yoast_url( $url, $presentation ): string {
		$head = self::yoast_head( $presentation );
		return false === $head ? (string) $url : self::canonical_for( (string) $url, $head );
	}

	/**
	 * Titel und og:title der Detailansicht. Läuft je Request mehrfach — und
	 * bekommt jedes Mal den frischen Yoast-Titel.
	 *
	 * @param string $title        Titel.
	 * @param mixed  $presentation Indexable_Presentation.
	 * @return string
	 */
	public static function yoast_title( $title, $presentation ): string {
		$head = self::yoast_head( $presentation );
		return false === $head ? (string) $title : self::title_for( (string) $title, (string) $head['title'] );
	}

	/**
	 * Der WebPage-Knoten im Schema-Graphen: url und name wie Canonical und
	 * Titel. Die @id bleibt der Permalink, andere Knoten verweisen darauf.
	 *
	 * @param array<string, mixed> $piece   Schema-Knoten.
	 * @param mixed                $context Meta_Tags_Context.
	 * @return array<string, mixed>
	 */
	public static function yoast_schema_webpage( $piece, $context ): array {
		$piece     = (array) $piece;
		$indexable = is_object( $context ) && isset( $context->indexable ) ? $context->indexable : null;
		$head      = is_object( $indexable ) ? self::head_for( (int) ( $indexable->object_id ?? 0 ) ) : false;
		if ( false === $head || $head['noindex'] ) {
			return $piece;
		}

		if ( ! empty( $piece['url'] ) && is_string( $piece['url'] ) ) {
			$piece['url'] = self::canonical_for( $piece['url'], $head );
		}
		if ( ! empty( $piece['name'] ) && is_string( $piece['name'] ) ) {
			$piece['name'] = self::title_for( $piece['name'], (string) $head['title'] );
		}
		return $piece;
	}

	// -------------------------------------------------------------------------
	// Rank Math
	// -------------------------------------------------------------------------

	/**
	 * Robots in Rank Maths Form ('index' => 'noindex'). Rank Math entfernt am
	 * Hook wp alle wp_robots-Filter und schreibt sein eigenes Tag; bei noindex
	 * lässt es den Canonical selbst weg.
	 *
	 * Ein vorhandenes 'nofollow' (etwa bei blog_public = 0) bleibt stehen.
	 *
	 * @param array<string, string> $robots Robots-Werte.
	 * @return array<string, string>
	 */
	public static function rank_math_robots( $robots ): array {
		$robots = (array) $robots;
		$head   = self::head();
		if ( false === $head || ! $head['noindex'] ) {
			return $robots;
		}

		$robots['index'] = 'noindex';
		if ( ! isset( $robots['follow'] ) ) {
			$robots['follow'] = 'follow';
		}
		return $robots;
	}

	/**
	 * Canonical (og:url und Schema folgen ihm bei Rank Math von selbst).
	 * Rank Math berechnet ihn nur für die aufgerufene Seite.
	 *
	 * @param string|false $url Canonical.
	 * @return string|false
	 */
	public static function rank_math_canonical( $url ) {
		$head = self::head();
		return false === $head || ! is_string( $url ) ? $url : self::canonical_for( $url, $head );
	}

	/**
	 * Titel der Detailansicht. Ruft bewusst nichts von Rank Math auf — eine
	 * Titel-Abfrage aus dem Titel-Filter heraus liefe in eine Endlosschleife.
	 *
	 * @param string $title Titel.
	 * @return string
	 */
	public static function rank_math_title( $title ): string {
		$head = self::head();
		return false === $head ? (string) $title : self::title_for( (string) $title, (string) $head['title'] );
	}

	/**
	 * Die Social-Titel og:title und twitter:title. Ohne eigenen Social-Titel der Seite fällt
	 * Rank Math auf den schon ergänzten Seitentitel zurück — dann nicht ein
	 * zweites Mal voranstellen.
	 *
	 * @param string $title Titel.
	 * @return string
	 */
	public static function rank_math_social_title( $title ): string {
		$title = (string) $title;
		$head  = self::head();
		if ( false === $head || '' === $head['title'] || 0 === strpos( $title, $head['title'] . ' – ' ) ) {
			return $title;
		}
		return self::title_for( $title, (string) $head['title'] );
	}

	// -------------------------------------------------------------------------
	// All in One SEO
	// -------------------------------------------------------------------------

	/**
	 * Canonical und og:url. AIOSEO wendet vorher auch get_canonical_url an —
	 * canonical_for() ersetzt die eigenen Parameter, statt sie zu verdoppeln.
	 * Ein '' unterdrückt Canonical und og:url.
	 *
	 * @param string $url Canonical.
	 * @return string
	 */
	public static function aioseo_canonical( $url ): string {
		$head = self::head();
		return false === $head ? (string) $url : self::canonical_for( (string) $url, $head );
	}

	/**
	 * Titel, og:title, twitter:title und Schema-Name — AIOSEO ruft den Filter
	 * für jeden davon einzeln auf, ohne Kontext. Deshalb nur einmal voranstellen.
	 *
	 * AIOSEO ersetzt danach Smart-Tags (#…) und führt Shortcodes aus; ein # oder
	 * [ im Datensatztitel wird deshalb als Entität übergeben.
	 *
	 * @param string $title Titel.
	 * @return string
	 */
	public static function aioseo_title( $title ): string {
		$title = (string) $title;
		$head  = self::head();
		if ( false === $head || '' === $head['title'] || '' === trim( $title ) ) {
			return $title;
		}

		$dataset = str_replace( array( '#', '[' ), array( '&#35;', '&#91;' ), (string) $head['title'] );
		return 0 === strpos( $title, $dataset . ' – ' ) ? $title : self::title_for( $title, $dataset );
	}

	/**
	 * Robots in AIOSEOs Form ('noindex' => 'noindex'). AIOSEO leert vor
	 * wp_head alle wp_robots-Filter. Ein gesetztes nofollow bleibt.
	 *
	 * @param array<string, string> $attributes Robots-Werte.
	 * @return array<string, string>
	 */
	public static function aioseo_robots( $attributes ): array {
		$attributes = (array) $attributes;
		$head       = self::head();
		if ( false === $head || ! $head['noindex'] ) {
			return $attributes;
		}

		$attributes['noindex'] = 'noindex';
		return $attributes;
	}

	/**
	 * Datensatztitel vor einen fertigen Seitentitel setzen. Reine Funktion.
	 *
	 * @param string $title   Seitentitel, wie das SEO-Plugin ihn gebildet hat.
	 * @param string $dataset Datensatztitel; leer: Titel unverändert.
	 * @return string
	 */
	public static function title_for( string $title, string $dataset ): string {
		if ( '' === $dataset || '' === trim( $title ) ) {
			return $title;
		}
		return $dataset . ' – ' . $title;
	}

	// -------------------------------------------------------------------------
	// Änderungen melden (Seiten-Caches)
	// -------------------------------------------------------------------------

	/**
	 * Betrifft ein Statuswechsel den öffentlichen Katalog? Reine Funktion.
	 *
	 * @param string $new_status Neuer Status.
	 * @param string $old_status Alter Status ('new' beim Anlegen).
	 * @return bool
	 */
	public static function affects_catalog( string $new_status, string $old_status ): bool {
		return 'publish' === $new_status || 'publish' === $old_status;
	}

	/**
	 * Nach dem Speichern — wp_after_insert_post läuft nach Carbon Fields und
	 * allen save_post-Callbacks, die Meta-Werte stehen also fest.
	 *
	 * @param int          $post_id     Post ID.
	 * @param WP_Post      $post        Beitrag.
	 * @param bool         $update      Aktualisierung statt Neuanlage.
	 * @param WP_Post|null $post_before Beitrag vor dem Speichern.
	 */
	public static function after_insert( $post_id, $post, $update, $post_before ): void {
		unset( $update );
		if ( self::should_announce( $post, $post_before ) ) {
			self::announce( (int) $post_id );
		}
	}

	/**
	 * Muss das Speichern gemeldet werden? Reine Funktion.
	 *
	 * @param mixed $post        Beitrag nach dem Speichern.
	 * @param mixed $post_before Beitrag davor (null beim Anlegen).
	 * @return bool
	 */
	public static function should_announce( $post, $post_before ): bool {
		if ( ! $post instanceof WP_Post || 'odw_dataset' !== $post->post_type ) {
			return false;
		}

		$old = $post_before instanceof WP_Post ? (string) $post_before->post_status : 'new';
		return self::affects_catalog( (string) $post->post_status, $old );
	}

	/**
	 * Endgültiges Löschen eines veröffentlichten Datensatzes (ohne Papierkorb).
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post|null $post    Gelöschter Beitrag.
	 */
	public static function after_delete( $post_id, $post = null ): void {
		if ( $post instanceof WP_Post && 'odw_dataset' === $post->post_type && 'publish' === $post->post_status ) {
			self::announce( (int) $post_id );
		}
	}

	/**
	 * Meldet eine Änderung — höchstens einmal je Datensatz und Request.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function announce( int $post_id ): void {
		if ( isset( self::$announced[ $post_id ] ) ) {
			return;
		}
		self::$announced[ $post_id ] = true;

		/**
		 * Ein Datensatz, der im öffentlichen Katalog steht oder stand, hat sich
		 * geändert. Zum Leeren von Seiten-Caches der Katalogseiten.
		 *
		 * @since 2.44.0
		 *
		 * @param int $post_id Post ID des Datensatzes.
		 */
		do_action( 'odw_catalog_changed', $post_id );
	}
}
