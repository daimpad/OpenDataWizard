<?php
/**
 * Tests für ODW_Catalog (Shortcode [odw_catalog]).
 *
 * Geprüft werden die reinen Funktionen: Attribute, Anfrage, Query-Argumente,
 * Trefferzeile, Seitenfenster und die Daten je Listeneintrag. Die Abfrage
 * selbst und das Zusammenspiel mit WordPress prüft tests/e2e/catalog.spec.js.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! class_exists( 'WP_Post' ) ) {
	/**
	 * Minimaler WP_Post-Ersatz — WP_Mock bringt die Klasse nicht mit.
	 *
	 * Dieselben Felder wie der Ersatz in test-quality.php, damit es egal ist,
	 * welche der beiden Dateien zuerst geladen wird.
	 *
	 * @package OpenDataWizard
	 */
	class WP_Post {

		/**
		 * Post ID.
		 *
		 * @var int
		 */
		public $ID = 0;

		/**
		 * Post type.
		 *
		 * @var string
		 */
		public $post_type = 'odw_dataset';

		/**
		 * Post status.
		 *
		 * @var string
		 */
		public $post_status = 'draft';
	}
}

/**
 * Unit tests for ODW_Catalog.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Catalog extends TestCase {

	private const THEME_BASE = 'http://publications.europa.eu/resource/authority/data-theme/';

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Tear down WP_Mock after each test.
	 */
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Lädt die Klassen und stellt die WordPress-Funktionen bereit, die die
	 * reinen Funktionen brauchen — mit echtem Verhalten statt fester Rückgaben.
	 */
	private function load(): void {
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'absint' )->andReturnUsing(
			static function ( $value ): int {
				return abs( (int) $value );
			}
		);
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing(
			static function ( $value ): string {
				return trim( strip_tags( (string) $value ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Testersatz für sanitize_text_field(), kein WordPress geladen.
			}
		);
		\WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			static function ( array $pairs, $atts ): array {
				$atts = (array) $atts;
				$out  = array();
				foreach ( $pairs as $name => $default ) {
					$out[ $name ] = array_key_exists( $name, $atts ) ? $atts[ $name ] : $default;
				}
				return $out;
			}
		);

		foreach ( array( 'class-fields.php', 'class-setup.php', 'class-shortcode.php', 'class-catalog.php' ) as $file ) {
			require_once ODW_PLUGIN_DIR . 'includes/' . $file;
		}
	}

	// -------------------------------------------------------------------------
	// parse_atts()
	// -------------------------------------------------------------------------

	/**
	 * Ohne Attribute: 20 pro Seite, nach Titel, Auszug 30, ohne Demo.
	 */
	public function test_parse_atts_defaults(): void {
		$this->load();

		$config = ODW_Catalog::parse_atts( '' );

		$this->assertSame( 20, $config['per_page'] );
		$this->assertSame( 'title', $config['orderby'] );
		$this->assertSame( array(), $config['themes'] );
		$this->assertSame( array(), $config['themes_invalid'] );
		$this->assertSame( '', $config['title'] );
		$this->assertSame( 30, $config['excerpt'] );
		$this->assertFalse( $config['show_demo'] );
	}

	/**
	 * Shortcodes setzen auch Mitwirkende — jede Grenze gilt hart.
	 *
	 * @dataProvider provide_clamped_atts
	 *
	 * @param array<string, string> $atts     Rohe Attribute.
	 * @param string                $key      Geprüfter Schlüssel.
	 * @param mixed                 $expected Erwarteter Wert.
	 */
	public function test_parse_atts_clamps_and_whitelists( array $atts, string $key, $expected ): void {
		$this->load();

		$this->assertSame( $expected, ODW_Catalog::parse_atts( $atts )[ $key ] );
	}

	/**
	 * Attribute und der Wert, auf den sie geklemmt werden.
	 *
	 * @return array<string, array{0: array<string, string>, 1: string, 2: mixed}>
	 */
	public function provide_clamped_atts(): array {
		return array(
			'per_page über Obergrenze' => array( array( 'per_page' => '500' ), 'per_page', 50 ),
			'per_page unlesbar'        => array( array( 'per_page' => 'viele' ), 'per_page', 20 ),
			'per_page null'            => array( array( 'per_page' => '0' ), 'per_page', 20 ),
			'excerpt aus'              => array( array( 'excerpt' => '0' ), 'excerpt', 0 ),
			'excerpt über Obergrenze'  => array( array( 'excerpt' => '500' ), 'excerpt', 100 ),
			'excerpt unlesbar'         => array( array( 'excerpt' => 'lang' ), 'excerpt', 30 ),
			'orderby modified, Groß'   => array( array( 'orderby' => 'MODIFIED' ), 'orderby', 'modified' ),
			'orderby unbekannt'        => array( array( 'orderby' => 'rand' ), 'orderby', 'title' ),
			'show_demo ja'             => array( array( 'show_demo' => 'ja' ), 'show_demo', true ),
			'show_demo 1'              => array( array( 'show_demo' => '1' ), 'show_demo', true ),
			'show_demo sonstiges'      => array( array( 'show_demo' => 'vielleicht' ), 'show_demo', false ),
			'title ohne HTML'          => array( array( 'title' => '<b>Unsere Daten</b>' ), 'title', 'Unsere Daten' ),
		);
	}

	// -------------------------------------------------------------------------
	// parse_themes()
	// -------------------------------------------------------------------------

	/**
	 * EU-Code, Altname und volle URI werden aufgelöst, Doppelte zusammengelegt,
	 * Unbekanntes landet in 'invalid'.
	 */
	public function test_parse_themes_resolves_codes_names_and_uris(): void {
		$this->load();

		$result = ODW_Catalog::parse_themes( 'EDUC, Bildung, xyz,  ,' . self::THEME_BASE . 'SOCI' );

		$this->assertSame( array( self::THEME_BASE . 'EDUC', self::THEME_BASE . 'SOCI' ), $result['valid'] );
		$this->assertSame( array( 'xyz' ), $result['invalid'] );
	}

	/**
	 * Eine leere Angabe bedeutet „alle Themen", nicht „ungültig".
	 */
	public function test_parse_themes_empty_is_no_restriction(): void {
		$this->load();

		$this->assertSame(
			array(
				'valid'   => array(),
				'invalid' => array(),
			),
			ODW_Catalog::parse_themes( '  ' )
		);
	}

	// -------------------------------------------------------------------------
	// parse_request()
	// -------------------------------------------------------------------------

	/**
	 * Seite und Detail-ID kommen nur als positive Ganzzahlen weiter, Arrays aus
	 * manipulierten Adressen werden ignoriert.
	 *
	 * @dataProvider provide_requests
	 *
	 * @param array<string, mixed> $get      GET-Werte.
	 * @param array<string, int>   $expected Erwartetes Ergebnis.
	 */
	public function test_parse_request( array $get, array $expected ): void {
		$this->load();

		$this->assertSame( $expected, ODW_Catalog::parse_request( $get ) );
	}

	/**
	 * GET-Werte und das erwartete Ergebnis.
	 *
	 * @return array<string, array{0: array<string, mixed>, 1: array<string, int>}>
	 */
	public function provide_requests(): array {
		return array(
			'leer'              => array(
				array(),
				array(
					'page'    => 1,
					'dataset' => 0,
				),
			),
			'Seite 3'           => array(
				array( 'odw_page' => '3' ),
				array(
					'page'    => 3,
					'dataset' => 0,
				),
			),
			'Seite 0'           => array(
				array( 'odw_page' => '0' ),
				array(
					'page'    => 1,
					'dataset' => 0,
				),
			),
			'Seite absurd groß' => array(
				array( 'odw_page' => '99999999' ),
				array(
					'page'    => 10000,
					'dataset' => 0,
				),
			),
			'Detail'            => array(
				array( 'odw_ds' => '42' ),
				array(
					'page'    => 1,
					'dataset' => 42,
				),
			),
			'Detail Text'       => array(
				array( 'odw_ds' => 'abc' ),
				array(
					'page'    => 1,
					'dataset' => 0,
				),
			),
			'Arrays ignoriert'  => array(
				array(
					'odw_page' => array( '2' ),
					'odw_ds'   => array( '5' ),
				),
				array(
					'page'    => 1,
					'dataset' => 0,
				),
			),
		);
	}

	// -------------------------------------------------------------------------
	// build_query_args()
	// -------------------------------------------------------------------------

	/**
	 * Typ, Status und Passwortschutz sind fest — nie aus Attribut oder Adresse.
	 */
	public function test_query_args_fix_type_status_and_password(): void {
		$this->load();

		$args = ODW_Catalog::build_query_args(
			ODW_Catalog::parse_atts( '' ),
			array(
				'page'    => 2,
				'dataset' => 0,
			),
			0
		);

		$this->assertSame( 'odw_dataset', $args['post_type'] );
		$this->assertSame( 'publish', $args['post_status'] );
		$this->assertFalse( $args['has_password'] );
		$this->assertSame( 20, $args['posts_per_page'] );
		$this->assertSame( 2, $args['paged'] );
		$this->assertFalse( $args['no_found_rows'] );
		$this->assertSame(
			array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
			$args['orderby']
		);
		$this->assertArrayNotHasKey( 'meta_query', $args );
		$this->assertArrayNotHasKey( 'post__not_in', $args );
	}

	/**
	 * Der Beispieldatensatz fehlt standardmäßig und erscheint mit show_demo.
	 */
	public function test_query_args_hide_demo_unless_requested(): void {
		$this->load();

		$request = array(
			'page'    => 1,
			'dataset' => 0,
		);

		$hidden = ODW_Catalog::build_query_args( ODW_Catalog::parse_atts( '' ), $request, 7 );
		$shown  = ODW_Catalog::build_query_args( ODW_Catalog::parse_atts( array( 'show_demo' => '1' ) ), $request, 7 );

		$this->assertSame( array( 7 ), $hidden['post__not_in'] );
		$this->assertArrayNotHasKey( 'post__not_in', $shown );
	}

	/**
	 * Themen filtern über den flachen Spiegel _odw_theme_index, mehrere per IN;
	 * „zuletzt aktualisiert" sortiert absteigend mit stabiler ID.
	 */
	public function test_query_args_theme_filter_and_modified_order(): void {
		$this->load();

		$config = ODW_Catalog::parse_atts(
			array(
				'theme'   => 'EDUC,ENVI',
				'orderby' => 'modified',
			)
		);
		$args   = ODW_Catalog::build_query_args(
			$config,
			array(
				'page'    => 1,
				'dataset' => 0,
			),
			0
		);

		$this->assertSame(
			array(
				array(
					'key'     => '_odw_theme_index',
					'value'   => array( self::THEME_BASE . 'EDUC', self::THEME_BASE . 'ENVI' ),
					'compare' => 'IN',
				),
			),
			$args['meta_query']
		);
		$this->assertSame(
			array(
				'modified' => 'DESC',
				'ID'       => 'DESC',
			),
			$args['orderby']
		);
	}

	// -------------------------------------------------------------------------
	// Trefferzeile, Seitenfenster und Block-Attribute
	// -------------------------------------------------------------------------

	/**
	 * Trefferzeile ohne „1 Datensätze" — alle vier Formulierungen.
	 *
	 * @dataProvider provide_counts
	 *
	 * @param int    $from     Erster Eintrag.
	 * @param int    $to       Letzter Eintrag.
	 * @param int    $total    Treffer insgesamt.
	 * @param string $expected Erwartete Zeile.
	 */
	public function test_count_label( int $from, int $to, int $total, string $expected ): void {
		$this->load();

		$this->assertSame( $expected, ODW_Catalog::count_label( $from, $to, $total ) );
	}

	/**
	 * Zählungen und die erwartete Zeile.
	 *
	 * @return array<string, array{0: int, 1: int, 2: int, 3: string}>
	 */
	public function provide_counts(): array {
		return array(
			'genau einer'          => array( 1, 1, 1, 'Ein Datensatz' ),
			'alle auf einer Seite' => array( 1, 7, 7, '7 Datensätze' ),
			'letzter allein'       => array( 41, 41, 41, 'Datensatz 41 von 41' ),
			'Ausschnitt'           => array( 21, 40, 57, 'Datensätze 21–40 von 57' ),
		);
	}

	/**
	 * Erste, letzte und ±2 um die aktuelle Seite.
	 *
	 * @dataProvider provide_windows
	 *
	 * @param int             $current  Aktuelle Seite.
	 * @param int             $pages    Seiten insgesamt.
	 * @param array<int, int> $expected Erwartete Seitenzahlen.
	 */
	public function test_page_window( int $current, int $pages, array $expected ): void {
		$this->load();

		$this->assertSame( $expected, ODW_Catalog::page_window( $current, $pages ) );
	}

	/**
	 * Seitenfenster.
	 *
	 * @return array<string, array{0: int, 1: int, 2: array<int, int>}>
	 */
	public function provide_windows(): array {
		return array(
			'eine Seite'  => array( 1, 1, array( 1 ) ),
			'Anfang'      => array( 1, 10, array( 1, 2, 3, 10 ) ),
			'Mitte'       => array( 5, 10, array( 1, 3, 4, 5, 6, 7, 10 ) ),
			'Ende'        => array( 10, 10, array( 1, 8, 9, 10 ) ),
			'kurze Liste' => array( 2, 3, array( 1, 2, 3 ) ),
		);
	}

	/**
	 * Block-Attribute laufen durch dieselbe Prüfung wie der Shortcode.
	 */
	public function test_block_attributes_map_to_shortcode_atts(): void {
		$this->load();

		$atts = ODW_Catalog::block_to_atts(
			array(
				'perPage'  => 10,
				'orderby'  => 'modified',
				'theme'    => 'EDUC',
				'title'    => 'Daten',
				'excerpt'  => 0,
				'showDemo' => true,
			)
		);

		$this->assertSame(
			array(
				'per_page'  => '10',
				'orderby'   => 'modified',
				'theme'     => 'EDUC',
				'title'     => 'Daten',
				'excerpt'   => '0',
				'show_demo' => '1',
			),
			$atts
		);
		$this->assertSame( '0', ODW_Catalog::block_to_atts( array() )['show_demo'] );
	}

	// -------------------------------------------------------------------------
	// item_data()
	// -------------------------------------------------------------------------

	/**
	 * Stellt get_post_meta() mit einer festen Meta-Tabelle bereit.
	 *
	 * @param array<string, mixed> $meta Meta-Schlüssel => Wert.
	 */
	private function mock_meta( array $meta ): void {
		\WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			static function ( $post_id, $key, $single = false ) use ( $meta ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				$value = $meta[ $key ] ?? null;
				if ( $single ) {
					return null === $value ? '' : $value;
				}
				return null === $value ? array() : (array) $value;
			}
		);
	}

	/**
	 * Ein Datensatz-Objekt.
	 *
	 * @param int $id Post ID.
	 * @return WP_Post
	 */
	private function post( int $id ): WP_Post {
		$post     = new WP_Post();
		$post->ID = $id;
		return $post;
	}

	/**
	 * Vollständiger Eintrag: Formular-Format vor Dateiendung, Größe aus
	 * byte_size, Lizenzlabel, Themen-Labels, Auszug, direkter Download.
	 */
	public function test_item_data_full_entry(): void {
		$this->load();
		$this->mock_meta(
			array(
				'_odw_format'       => 'CSV',
				'_odw_byte_size'    => '204800',
				'_odw_license'      => 'https://creativecommons.org/licenses/by/4.0/',
				'_odw_description'  => 'Eins zwei drei vier fünf',
				'_odw_download_url' => 'https://example.org/daten.csv',
				'_odw_access_url'   => 'https://example.org/portal',
				'_odw_theme_index'  => array( self::THEME_BASE . 'TRAN', self::THEME_BASE . 'ENVI' ),
				'_odw_modified'     => '2026-10-01',
			)
		);
		\WP_Mock::userFunction( 'get_the_title' )->andReturn( 'Zählstellen' );
		\WP_Mock::userFunction( 'get_option' )->andReturn( 'd.m.Y' );
		\WP_Mock::userFunction( 'mysql2date' )->andReturn( '01.10.2026' );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'wp_trim_words' )->andReturn( 'Eins zwei drei …' );

		$item = ODW_Catalog::item_data( $this->post( 21 ), 3 );

		$this->assertSame( 21, $item['id'] );
		$this->assertSame( 'Zählstellen', $item['title'] );
		$this->assertSame( '01.10.2026', $item['modified'] );
		$this->assertSame( 'Eins zwei drei …', $item['excerpt'] );
		$this->assertSame( array( 'Verkehr', 'Umwelt' ), $item['themes'] );
		$this->assertSame( 'CSV', $item['format'] );
		$this->assertSame( '200 KB', $item['size'] );
		$this->assertSame( 'CC BY 4.0 — Namensnennung', $item['license'] );
		$this->assertSame( '', $item['access'], 'Ohne Zugriffsrecht gilt der Datensatz als öffentlich.' );
		$this->assertSame(
			array(
				'url'  => 'https://example.org/daten.csv',
				'kind' => 'file',
			),
			$item['target']
		);
	}

	/**
	 * Nur eine accessURL: Ziel ist das Datenangebot, nicht eine Datei; ein
	 * eingeschränktes Zugriffsrecht wird als Label ausgewiesen; ohne Titel
	 * steht die ID da, ohne Auszug-Länge kein Auszug.
	 */
	public function test_item_data_access_only_restricted_untitled(): void {
		$this->load();
		$this->mock_meta(
			array(
				'_odw_access_url'    => 'https://example.org/portal',
				'_odw_access_rights' => 'http://publications.europa.eu/resource/authority/access-right/RESTRICTED',
				'_odw_description'   => 'Wird nicht gezeigt.',
			)
		);
		\WP_Mock::userFunction( 'get_the_title' )->andReturn( '' );
		\WP_Mock::userFunction( 'get_the_modified_date' )->andReturn( '3. Oktober 2026' );

		$item = ODW_Catalog::item_data( $this->post( 22 ), 0 );

		$this->assertSame( '(ohne Titel, ID 22)', $item['title'] );
		$this->assertSame( '3. Oktober 2026', $item['modified'], 'Ohne _odw_modified gilt das Änderungsdatum des Beitrags.' );
		$this->assertSame( '', $item['excerpt'] );
		$this->assertSame( 'Eingeschränkt', $item['access'] );
		$this->assertSame(
			array(
				'url'  => 'https://example.org/portal',
				'kind' => 'access',
			),
			$item['target']
		);
	}

	// -------------------------------------------------------------------------
	// Wächter: Die Liste liest nur flache Meta-Schlüssel
	// -------------------------------------------------------------------------

	/**
	 * Jeder Meta-Schlüssel, den der Katalog per get_post_meta() liest, muss ein
	 * flaches Feld sein.
	 *
	 * Der Katalog umgeht Carbon Fields und liest die Roh-Meta. Das geht nur für
	 * einfache Felder auf oberster Ebene (text, textarea, select): Mehrfachwerte
	 * und complex-Felder legt Carbon Fields unter anderen Schlüsseln ab
	 * (`_odw_theme|||0|value`), dort fände der Katalog nichts. Wird ein solches
	 * Feld später zur Mehrfachauswahl, schlägt dieser Test an — bevor die
	 * Liste still leere Badges zeigt. Interne Schlüssel ohne Formularfeld sind
	 * ausdrücklich aufgezählt.
	 */
	public function test_catalog_reads_only_flat_meta_keys(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads local source, not a remote request.
		$catalog = (string) file_get_contents( ODW_PLUGIN_DIR . 'includes/class-catalog.php' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads local source, not a remote request.
		$fields = (string) file_get_contents( ODW_PLUGIN_DIR . 'includes/class-fields.php' );

		preg_match_all( "/'(_odw_[a-z_]+)'/", $catalog, $matches );
		$keys = array_values( array_unique( $matches[1] ) );

		$this->assertNotEmpty( $keys, 'Keine Meta-Schlüssel gefunden — der Test misst sonst nichts.' );

		// Vom Plugin selbst geschrieben, ohne Formularfeld.
		$internal = array( '_odw_modified', '_odw_file_id', '_odw_file_format', '_odw_file_size', '_odw_theme_index' );

		$problems = array();
		foreach ( $keys as $key ) {
			if ( in_array( $key, $internal, true ) ) {
				continue;
			}
			$field = substr( $key, 1 );
			if ( ! preg_match( "/Field::make\\(\\s*'(?:text|textarea|select)',\\s*'" . preg_quote( $field, '/' ) . "'/", $fields ) ) {
				$problems[] = $key;
			}
		}

		$this->assertSame( array(), $problems, 'Kein flaches Formularfeld: ' . implode( ', ', $problems ) );
	}
}
