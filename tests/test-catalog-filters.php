<?php
/**
 * Tests für ODW_Catalog_Filters (Stufe 2 des Frontend-Katalogs).
 *
 * Geprüft werden die reinen Funktionen: Attribute, GET-Zustand, Abfrage-
 * Argumente, Such- und Facetten-SQL, Auswahllisten, versteckte Felder und
 * die Werte der Index-Felder. Abfragen und Formular gegen ein echtes
 * WordPress prüft tests/e2e/catalog.spec.js.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Catalog_Filters.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Catalog_Filters extends TestCase {

	private const THEME_BASE = 'http://publications.europa.eu/resource/authority/data-theme/';
	private const CC_BY      = 'https://creativecommons.org/licenses/by/4.0/';
	private const CC0        = 'https://creativecommons.org/publicdomain/zero/1.0/';

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
	 * reinen Funktionen brauchen.
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
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing(
			static function ( $url, $component = -1 ) {
				return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Testersatz für wp_parse_url(), kein WordPress geladen.
			}
		);

		foreach ( array( 'class-fields.php', 'class-setup.php', 'class-shortcode.php', 'class-catalog.php', 'class-catalog-filters.php' ) as $file ) {
			require_once ODW_PLUGIN_DIR . 'includes/' . $file;
		}
	}

	/**
	 * Ein leerer Zustand mit einzelnen Abweichungen.
	 *
	 * @param array<string, string> $set Abweichungen.
	 * @return array<string, string>
	 */
	private function state( array $set = array() ): array {
		return array_merge(
			array(
				'theme'   => '',
				'license' => '',
				'format'  => '',
				'sort'    => '',
				'q'       => '',
			),
			$set
		);
	}

	// -------------------------------------------------------------------------
	// Attribute
	// -------------------------------------------------------------------------

	/**
	 * Attribut filters: nur bekannte Einträge, feste Reihenfolge, ohne Doppelte; fest
	 * eingestellte Facetten fallen weg.
	 */
	public function test_parse_filters(): void {
		$this->load();

		$this->assertSame( array( 'theme', 'license', 'format', 'sort' ), ODW_Catalog_Filters::parse_filters( 'sort, FORMAT,license,theme,theme', false, false ) );
		$this->assertSame( array( 'format' ), ODW_Catalog_Filters::parse_filters( 'format,keywords,<b>', false, false ) );
		$this->assertSame( array( 'license', 'format' ), ODW_Catalog_Filters::parse_filters( 'theme,license,format', true, false ) );
		$this->assertSame( array( 'theme' ), ODW_Catalog_Filters::parse_filters( 'theme,license', false, true ) );
		$this->assertSame( array(), ODW_Catalog_Filters::parse_filters( '', false, false ) );
	}

	/**
	 * Lizenz: Kurzform (Groß-/Kleinschreibung egal) oder http(s)-URI, sonst ''.
	 */
	public function test_parse_license(): void {
		$this->load();

		$this->assertSame( self::CC_BY, ODW_Catalog_Filters::parse_license( 'CC-BY' ) );
		$this->assertSame( self::CC_BY, ODW_Catalog_Filters::parse_license( 'cc-by-4.0' ) );
		$this->assertSame( 'https://example.org/meine-lizenz', ODW_Catalog_Filters::parse_license( ' https://example.org/meine-lizenz ' ) );
		$this->assertSame( '', ODW_Catalog_Filters::parse_license( 'cc-nc' ) );
		$this->assertSame( '', ODW_Catalog_Filters::parse_license( 'javascript:alert(1)' ) );
		$this->assertSame( '', ODW_Catalog_Filters::parse_license( 'https://example.org/' . str_repeat( 'x', 600 ) ) );
		$this->assertSame( '', ODW_Catalog_Filters::parse_license( '' ) );
	}

	/**
	 * Im Link steht die bevorzugte Kurzform, für eigene Lizenzen die URI.
	 */
	public function test_license_param(): void {
		$this->load();

		$this->assertSame( 'cc-by', ODW_Catalog_Filters::license_param( self::CC_BY ) );
		$this->assertSame( 'cc0', ODW_Catalog_Filters::license_param( self::CC0 ) );
		$this->assertSame( 'dl-de-by', ODW_Catalog_Filters::license_param( 'https://www.govdata.de/dl-de/by-2-0' ) );
		$this->assertSame( 'https://example.org/eigen', ODW_Catalog_Filters::license_param( 'https://example.org/eigen' ) );
	}

	/**
	 * Eine Liste der Kurzformen für REST, Import und Katalog — vorher kannte
	 * jede Stelle andere.
	 */
	public function test_rest_and_import_share_the_alias_list(): void {
		$this->load();
		require_once ODW_PLUGIN_DIR . 'includes/class-rest-api.php';
		require_once ODW_PLUGIN_DIR . 'includes/class-batch-import.php';

		$shared = ODW_Fields::license_aliases();
		foreach ( array( 'ODW_Rest_API', 'ODW_Batch_Import' ) as $class ) {
			$method = new ReflectionMethod( $class, 'get_license_alias_map' );
			$method->setAccessible( true );
			$this->assertSame( $shared, $method->invoke( null ), $class );
		}

		// Beide früheren Listen sind enthalten.
		foreach ( array( 'cc0', 'cc0-1.0', 'cc-by', 'cc-by-4.0', 'cc-by-sa', 'cc-by-sa-4.0', 'dl-de-by-2.0', 'odc-odbl', 'dl-de-by', 'dl-de-zero' ) as $alias ) {
			$this->assertArrayHasKey( $alias, $shared );
			$this->assertStringStartsWith( 'https://', $shared[ $alias ] );
		}
	}

	/**
	 * Shortcode-Attribute der Stufe 2: Standard aus, Lizenz aufgelöst,
	 * unbekannte Lizenz gemerkt.
	 */
	public function test_parse_atts_stage2(): void {
		$this->load();

		$default = ODW_Catalog::parse_atts( '' );
		$this->assertSame( array(), $default['filters'] );
		$this->assertFalse( $default['search'] );
		$this->assertSame( '', $default['license'] );
		$this->assertSame( '', $default['license_invalid'] );

		$config = ODW_Catalog::parse_atts(
			array(
				'filters' => 'theme,license,format,sort',
				'search'  => 'ja',
				'license' => 'cc-by',
			)
		);
		$this->assertSame( array( 'theme', 'format', 'sort' ), $config['filters'], 'Fest eingestellte Lizenz: kein Lizenzfilter.' );
		$this->assertTrue( $config['search'] );
		$this->assertSame( self::CC_BY, $config['license'] );

		$broken = ODW_Catalog::parse_atts( array( 'license' => '<b>quatsch</b>' ) );
		$this->assertSame( '', $broken['license'] );
		$this->assertSame( 'quatsch', $broken['license_invalid'] );
	}

	// -------------------------------------------------------------------------
	// Anfrage und Zustand
	// -------------------------------------------------------------------------

	/**
	 * GET-Werte: nur Gültiges, sonst „alle".
	 *
	 * @dataProvider provide_get
	 *
	 * @param array<string, mixed> $get      GET.
	 * @param string               $key      Geprüfter Schlüssel.
	 * @param string               $expected Erwarteter Wert.
	 */
	public function test_parse_get( array $get, string $key, string $expected ): void {
		$this->load();

		$this->assertSame( $expected, ODW_Catalog_Filters::parse_get( $get )[ $key ] );
	}

	/**
	 * GET-Werte und das Ergebnis.
	 *
	 * @return array<string, array{0: array<string, mixed>, 1: string, 2: string}>
	 */
	public function provide_get(): array {
		return array(
			'Thema als Code'         => array( array( 'odw_theme' => 'educ' ), 'theme', self::THEME_BASE . 'EDUC' ),
			'Thema unbekannt'        => array( array( 'odw_theme' => 'XYZ' ), 'theme', '' ),
			'Thema als URI'          => array( array( 'odw_theme' => self::THEME_BASE . 'EDUC' ), 'theme', '' ),
			'Lizenz als Kurzform'    => array( array( 'odw_license' => 'cc-by' ), 'license', self::CC_BY ),
			'Format'                 => array( array( 'odw_format' => 'GeoJSON' ), 'format', 'GeoJSON' ),
			'Format mit Leerzeichen' => array( array( 'odw_format' => 'C S V' ), 'format', '' ),
			'Sortierung'             => array( array( 'odw_sort' => 'Modified' ), 'sort', 'modified' ),
			'Sortierung unbekannt'   => array( array( 'odw_sort' => 'rand' ), 'sort', '' ),
			'Suche'                  => array( array( 'odw_q' => "  Schule \n  Bau " ), 'q', 'Schule Bau' ),
			'Suche zu kurz'          => array( array( 'odw_q' => 'a' ), 'q', '' ),
			'Suche gekürzt'          => array( array( 'odw_q' => str_repeat( 'ä', 150 ) ), 'q', str_repeat( 'ä', 100 ) ),
			'Array statt Wert'       => array( array( 'odw_q' => array( 'x' ) ), 'q', '' ),
		);
	}

	/**
	 * Nur freigeschaltete Filter wirken; die eingestellte Sortierung ist keine
	 * Abweichung.
	 */
	public function test_state_respects_config(): void {
		$this->load();

		$get = ODW_Catalog_Filters::parse_get(
			array(
				'odw_theme'   => 'EDUC',
				'odw_license' => 'cc0',
				'odw_format'  => 'CSV',
				'odw_sort'    => 'title',
				'odw_q'       => 'Schule',
			)
		);

		$off = ODW_Catalog_Filters::state( ODW_Catalog::parse_atts( '' ), $get );
		$this->assertSame( $this->state(), $off, 'Ohne filters/search ändert sich nichts.' );
		$this->assertFalse( ODW_Catalog_Filters::is_active( $off ) );

		$on = ODW_Catalog_Filters::state(
			ODW_Catalog::parse_atts(
				array(
					'filters' => 'theme,format,sort',
					'search'  => '1',
				)
			),
			$get
		);
		$this->assertSame(
			$this->state(
				array(
					'theme'  => self::THEME_BASE . 'EDUC',
					'format' => 'CSV',
					'q'      => 'Schule',
				)
			),
			$on,
			'Lizenz nicht freigeschaltet; title ist die eingestellte Sortierung.'
		);
		$this->assertTrue( ODW_Catalog_Filters::is_active( $on ) );

		$sorted = ODW_Catalog_Filters::state( ODW_Catalog::parse_atts( array( 'filters' => 'sort' ) ), ODW_Catalog_Filters::parse_get( array( 'odw_sort' => 'modified' ) ) );
		$this->assertSame( 'modified', $sorted['sort'] );
		$this->assertTrue( ODW_Catalog_Filters::is_active( $sorted ) );
	}

	/**
	 * Links tragen nur, was vom Standard abweicht — in Kurzform.
	 */
	public function test_state_args(): void {
		$this->load();

		$this->assertSame( array(), ODW_Catalog_Filters::state_args( $this->state() ) );
		$this->assertSame(
			array(
				'odw_theme'   => 'EDUC',
				'odw_license' => 'cc-by',
				'odw_format'  => 'CSV',
				'odw_sort'    => 'modified',
				'odw_q'       => 'Schule Bau',
			),
			ODW_Catalog_Filters::state_args(
				$this->state(
					array(
						'theme'   => self::THEME_BASE . 'EDUC',
						'license' => self::CC_BY,
						'format'  => 'CSV',
						'sort'    => 'modified',
						'q'       => 'Schule Bau',
					)
				)
			)
		);
	}

	// -------------------------------------------------------------------------
	// Abfrage
	// -------------------------------------------------------------------------

	/**
	 * Filter werden UND-verknüpfte Gleichheiten auf den Index-Feldern — neben
	 * einer vorhandenen Themen-Klausel, ohne ODER und ohne NOT EXISTS.
	 */
	public function test_apply_to_query(): void {
		$this->load();

		$config = ODW_Catalog::parse_atts(
			array(
				'theme'   => 'EDUC,ENVI',
				'license' => 'cc0',
				'filters' => 'format,sort',
				'search'  => '1',
			)
		);
		$args   = ODW_Catalog::build_query_args(
			$config,
			array(
				'page'    => 1,
				'dataset' => 0,
			),
			0,
			$this->state(
				array(
					'format' => 'CSV',
					'sort'   => 'modified',
					'q'      => 'Schule',
				)
			)
		);

		$this->assertSame(
			array(
				array(
					'key'     => '_odw_theme_index',
					'value'   => array( self::THEME_BASE . 'EDUC', self::THEME_BASE . 'ENVI' ),
					'compare' => 'IN',
				),
				array(
					'key'   => '_odw_license_index',
					'value' => self::CC0,
				),
				array(
					'key'   => '_odw_format_index',
					'value' => 'CSV',
				),
			),
			$args['meta_query']
		);
		$this->assertSame( 'Schule', $args['odw_search'] );
		$this->assertSame(
			array(
				'modified' => 'DESC',
				'ID'       => 'DESC',
			),
			$args['orderby']
		);
	}

	/**
	 * Ohne Stufe-2-Attribute und ohne Zustand bleiben die Argumente die von
	 * Stufe 1 — keine Meta-Klausel, keine Suche.
	 */
	public function test_query_args_unchanged_without_stage2(): void {
		$this->load();

		$args = ODW_Catalog::build_query_args(
			ODW_Catalog::parse_atts( '' ),
			array(
				'page'    => 1,
				'dataset' => 0,
			),
			0
		);
		$this->assertArrayNotHasKey( 'meta_query', $args );
		$this->assertArrayNotHasKey( 'odw_search', $args );
	}

	/**
	 * Suche: Titel, Beschreibung und Schlagworte; % und _ wörtlich.
	 */
	public function test_search_sql(): void {
		$this->load();

		list( $sql, $values ) = ODW_Catalog_Filters::search_sql( '50%_Anteil', 'wp_posts.ID', 'wp_posts.post_title', 'wp_postmeta' );

		$this->assertStringContainsString( 'wp_posts.post_title LIKE %s', $sql );
		$this->assertStringContainsString( "odw_s.meta_key IN ( '_odw_description', '_odw_keywords' )", $sql );
		$this->assertStringContainsString( 'odw_s.post_id = wp_posts.ID', $sql );
		$this->assertSame( array( '%50\\%\\_Anteil%', '%50\\%\\_Anteil%' ), $values );
		$this->assertSame( 2, substr_count( $sql, '%s' ) );
	}

	// -------------------------------------------------------------------------
	// Facetten
	// -------------------------------------------------------------------------

	/**
	 * Facetten-SQL: Die eigene Auswahl bleibt außen vor, alle anderen
	 * Bedingungen gelten. Platzhalter und Werte passen zusammen.
	 */
	public function test_facet_sql(): void {
		$this->load();

		$conditions = array(
			'exclude' => 7,
			'themes'  => array( self::THEME_BASE . 'EDUC', self::THEME_BASE . 'ENVI' ),
			'theme'   => self::THEME_BASE . 'EDUC',
			'license' => self::CC_BY,
			'format'  => 'CSV',
			'q'       => 'Schule',
		);

		list( $sql, $values ) = ODW_Catalog_Filters::facet_sql( 'license', $conditions, 'wp_posts', 'wp_postmeta' );

		$this->assertStringStartsWith( 'SELECT f.meta_value AS value, COUNT( DISTINCT p.ID ) AS n FROM wp_posts p INNER JOIN wp_postmeta f', $sql );
		$this->assertStringContainsString( "p.post_type = 'odw_dataset' AND p.post_status = 'publish' AND p.post_password = ''", $sql );
		$this->assertStringEndsWith( 'GROUP BY f.meta_value', $sql );
		$this->assertSame( '_odw_license_index', $values[0] );
		$this->assertSame( 7, $values[1] );
		$this->assertNotContains( self::CC_BY, $values, 'Die eigene Auswahl zählt nicht mit.' );
		$this->assertContains( 'CSV', $values );
		$this->assertContains( '_odw_format_index', $values );
		$this->assertContains( '%Schule%', $values );
		$this->assertSame(
			substr_count( $sql, '%s' ) + substr_count( $sql, '%d' ),
			count( $values ),
			'Jeder Platzhalter hat genau einen Wert.'
		);

		// Ohne Bedingungen: nur Schlüssel und Grundfilter.
		list( $plain, $plain_values ) = ODW_Catalog_Filters::facet_sql(
			'format',
			array(
				'exclude' => 0,
				'themes'  => array(),
				'theme'   => '',
				'license' => '',
				'format'  => '',
				'q'       => '',
			),
			'wp_posts',
			'wp_postmeta'
		);
		$this->assertSame( array( '_odw_format_index' ), $plain_values );
		$this->assertStringNotContainsString( 'EXISTS', $plain );
	}

	/**
	 * Auswahlliste: Beschriftung, Parameter, Sortierung; der gewählte Wert
	 * bleibt auch ohne Treffer.
	 */
	public function test_facet_options(): void {
		$this->load();

		$themes = ODW_Catalog_Filters::facet_options(
			'theme',
			array(
				self::THEME_BASE . 'TRAN' => 2,
				self::THEME_BASE . 'EDUC' => 5,
			),
			self::THEME_BASE . 'ENVI'
		);
		$this->assertSame( array( 'EDUC', 'ENVI', 'TRAN' ), array_column( $themes, 'param' ) );
		$this->assertSame( array( 5, 0, 2 ), array_column( $themes, 'count' ) );
		$this->assertSame( array( false, true, false ), array_column( $themes, 'selected' ) );
		$this->assertSame( 'Bildung, Kultur & Sport', $themes[0]['label'] );

		$licenses = ODW_Catalog_Filters::facet_options(
			'license',
			array(
				self::CC_BY                 => 3,
				'https://example.org/eigen' => 1,
			),
			''
		);
		$this->assertSame( array( 'cc-by', 'https://example.org/eigen' ), array_column( $licenses, 'param' ) );
		$this->assertSame( 'CC BY 4.0 — Namensnennung', $licenses[0]['label'] );
	}

	// -------------------------------------------------------------------------
	// Formular
	// -------------------------------------------------------------------------

	/**
	 * Versteckte Felder: der Query-String der Seite ohne eigene Parameter.
	 */
	public function test_hidden_inputs(): void {
		$this->load();

		$this->assertSame( array(), ODW_Catalog_Filters::hidden_inputs( 'https://example.org/daten/' ) );
		$this->assertSame(
			array(
				'page_id' => '7',
				'lang'    => 'en',
				's'       => 'grüne wiese',
			),
			ODW_Catalog_Filters::hidden_inputs( 'https://example.org/?page_id=7&odw_page=2&lang=en&odw_q=x&s=gr%C3%BCne+wiese&a[]=1&odw_ds=3' )
		);
	}

	// -------------------------------------------------------------------------
	// Index-Felder
	// -------------------------------------------------------------------------

	/**
	 * Stellt get_post_meta() und metadata_exists() mit einer festen
	 * Meta-Tabelle bereit.
	 *
	 * @param array<string, mixed> $meta Meta-Schlüssel => Wert.
	 */
	private function mock_meta( array $meta ): void {
		\WP_Mock::userFunction( 'metadata_exists' )->andReturnUsing(
			static function ( $type, $post_id, $key ) use ( $meta ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				return array_key_exists( $key, $meta );
			}
		);
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
	 * Index-Werte = was Liste und Karte zeigen: eigene Lizenz bei „sonstige",
	 * Format aus dem Formular, sonst aus der Datei, ungültige Auswahl leer.
	 *
	 * @dataProvider provide_index_meta
	 *
	 * @param array<string, string> $meta     Meta-Werte.
	 * @param string                $license  Erwartete Lizenz.
	 * @param string                $format   Erwartetes Format.
	 */
	public function test_index_values( array $meta, string $license, string $format ): void {
		$this->load();
		$this->mock_meta( $meta );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'https://example.org/up/karte.geojson' );

		$this->assertSame(
			array(
				'license' => $license,
				'format'  => $format,
			),
			ODW_Catalog_Filters::index_values( 40 )
		);
	}

	/**
	 * Meta-Zustände und die erwarteten Index-Werte.
	 *
	 * @return array<string, array{0: array<string, string>, 1: string, 2: string}>
	 */
	public function provide_index_meta(): array {
		return array(
			'Standardlizenz und Formular-Format' => array(
				array(
					'_odw_license' => self::CC_BY,
					'_odw_format'  => 'CSV',
				),
				self::CC_BY,
				'CSV',
			),
			'eigene Lizenz'                      => array(
				array(
					'_odw_license'        => 'sonstige',
					'_odw_license_custom' => ' https://example.org/eigen ',
				),
				'https://example.org/eigen',
				'',
			),
			'ungültige Auswahl, Datei-Format'    => array(
				array(
					'_odw_license' => 'cc-by',
					'_odw_format'  => 'csv',
					'_odw_file_id' => '9',
				),
				'',
				'GEOJSON',
			),
		);
	}

	/**
	 * Der Index-Abgleich schreibt beide Felder und löscht leere.
	 */
	public function test_sync_index_writes_and_clears(): void {
		$this->load();
		$this->mock_meta(
			array(
				'_odw_license' => self::CC0,
			)
		);
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'odw_dataset' );
		\WP_Mock::userFunction( 'update_post_meta' )->once()->with( 41, '_odw_license_index', self::CC0 );
		\WP_Mock::userFunction( 'delete_post_meta' )->once()->with( 41, '_odw_format_index' );

		ODW_Catalog_Filters::sync_index( 41 );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	/**
	 * Der Index-Abgleich rührt fremde Post Types nicht an.
	 */
	public function test_sync_index_ignores_other_types(): void {
		$this->load();
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'page' );
		\WP_Mock::userFunction( 'update_post_meta' )->never();
		\WP_Mock::userFunction( 'delete_post_meta' )->never();

		ODW_Catalog_Filters::sync_index( 42 );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}
}
