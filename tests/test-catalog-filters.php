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
				'theme'     => '',
				'license'   => '',
				'format'    => '',
				'sort'      => '',
				'q'         => '',
				'publisher' => '',
				'keywords'  => array(),
				'from'      => '',
				'to'        => '',
				'open'      => '',
				'hvd'       => '',
			),
			$set
		);
	}

	/**
	 * Index-Werte eines Datensatzes mit Abweichungen.
	 *
	 * @param array<string, mixed> $set Abweichungen.
	 * @return array<string, mixed>
	 */
	private function index( array $set = array() ): array {
		return array_merge(
			array(
				'license'   => '',
				'format'    => '',
				'publisher' => '',
				'keywords'  => array(),
				'from'      => '',
				'to'        => '',
				'open'      => '',
				'hvd'       => '',
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
			'Format klein'           => array( array( 'odw_format' => 'csv' ), 'format', 'CSV' ),
			'Format als Endung'      => array( array( 'odw_format' => 'geojson' ), 'format', 'GeoJSON' ),
			'Format ohne Schlüssel'  => array( array( 'odw_format' => 'docx' ), 'format', 'DOCX' ),
			'Lizenz-URI kodiert'     => array( array( 'odw_license' => 'https://stadt.example/nutzung-f%C3%BCr-daten' ), 'license', 'https://stadt.example/nutzung-f%C3%BCr-daten' ),
			'Lizenz als Freitext'    => array( array( 'odw_license' => 'Nutzung nach Absprache' ), 'license', '' ),
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

		// modified weicht von der Standardsortierung ab — wirkt also nur, wenn
		// 'sort' freigeschaltet ist.
		$off = ODW_Catalog_Filters::state( ODW_Catalog::parse_atts( '' ), array_merge( $get, array( 'sort' => 'modified' ) ) );
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

		// Umgekehrt: Katalog nach Änderung sortiert, Besucher wählt Titel.
		$config   = ODW_Catalog::parse_atts(
			array(
				'orderby' => 'modified',
				'filters' => 'sort',
			)
		);
		$by_title = ODW_Catalog_Filters::state( $config, ODW_Catalog_Filters::parse_get( array( 'odw_sort' => 'title' ) ) );
		$this->assertSame( 'title', $by_title['sort'] );
		$this->assertSame(
			array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
			ODW_Catalog_Filters::apply_to_query( array(), $config, $by_title )['orderby']
		);
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
		$this->assertContains( self::THEME_BASE . 'ENVI', $values, 'Die Themen des Attributs gelten.' );
		$this->assertSame( 2, count( array_keys( $values, '_odw_theme_index', true ) ), 'Themen des Attributs und Wahl des Besuchers: zwei Bedingungen.' );
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
	 * Bedingungen der Facetten: Die Lizenz des Attributs geht der Wahl des
	 * Besuchers vor, die Themen des Attributs gelten immer.
	 */
	public function test_conditions(): void {
		$this->load();

		$config     = ODW_Catalog::parse_atts(
			array(
				'license' => 'cc-by',
				'theme'   => 'EDUC',
				'filters' => 'format',
			)
		);
		$conditions = ODW_Catalog_Filters::conditions( $config, $this->state( array( 'license' => self::CC0 ) ), 7 );

		$this->assertSame( self::CC_BY, $conditions['license'] );
		$this->assertSame( array( self::THEME_BASE . 'EDUC' ), $conditions['themes'] );
		$this->assertSame( 7, $conditions['exclude'] );
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

	/**
	 * Angeboten wird nur, was über das Formular unverändert zurückkommt:
	 * keine Lizenz als Freitext, kein Thema, das es nicht mehr gibt.
	 */
	public function test_facet_options_round_trip(): void {
		$this->load();

		$licenses = ODW_Catalog_Filters::facet_options(
			'license',
			array(
				self::CC_BY              => 3,
				'Nutzung nach Absprache' => 2,
				'CC-BY'                  => 1,
				'https://stadt.example/nutzung-f%C3%BCr-daten' => 1,
			),
			''
		);
		$this->assertSame( array( 'cc-by', 'https://stadt.example/nutzung-f%C3%BCr-daten' ), array_column( $licenses, 'param' ) );

		$themes = ODW_Catalog_Filters::facet_options(
			'theme',
			array(
				self::THEME_BASE . 'EDUC' => 1,
				self::THEME_BASE . 'XYZ'  => 4,
			),
			''
		);
		$this->assertSame( array( 'EDUC' ), array_column( $themes, 'param' ) );
	}

	/**
	 * Themen, die eine Website über odw_theme_options ergänzt, lassen sich
	 * wählen — als URI oder als eigener Schlüssel.
	 */
	public function test_custom_theme(): void {
		$this->load();
		$defaults = ODW_Fields::get_theme_options();
		\WP_Mock::onFilter( 'odw_theme_options' )->with( $defaults )->reply(
			array_merge(
				$defaults,
				array(
					'https://example.org/thema/sport' => 'Sport',
					'ehrenamt'                        => 'Ehrenamt',
				)
			)
		);

		$this->assertSame( 'https://example.org/thema/sport', ODW_Catalog_Filters::parse_get( array( 'odw_theme' => 'https://example.org/thema/sport' ) )['theme'] );
		$this->assertSame( 'ehrenamt', ODW_Catalog_Filters::parse_get( array( 'odw_theme' => 'ehrenamt' ) )['theme'] );

		$options = ODW_Catalog_Filters::facet_options(
			'theme',
			array(
				'https://example.org/thema/sport' => 2,
				'ehrenamt'                        => 1,
			),
			''
		);
		$this->assertSame( array( 'ehrenamt', 'https://example.org/thema/sport' ), array_column( $options, 'param' ) );
		$this->assertSame( array( 'Ehrenamt', 'Sport' ), array_column( $options, 'label' ) );
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
			$this->index(
				array(
					'license' => $license,
					'format'  => $format,
					'open'    => in_array( $license, array( self::CC_BY, self::CC0 ), true ) ? '1' : '',
				)
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
				'GeoJSON',
			),
			'Endung ohne Format-Schlüssel'       => array(
				array(
					'_odw_file_id'     => '9',
					'_odw_file_format' => 'DOCX',
				),
				'',
				'DOCX',
			),
			'eigene Lizenz als Freitext'         => array(
				array(
					'_odw_license'        => 'sonstige',
					'_odw_license_custom' => 'Nutzung nach Absprache',
				),
				'',
				'',
			),
			'eigene Lizenz wie eine Kurzform'    => array(
				array(
					'_odw_license'        => 'sonstige',
					'_odw_license_custom' => 'CC-BY',
				),
				'',
				'',
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
				'_odw_license'        => self::CC0,
				'_odw_publisher'      => 'Stadt  Musterstadt ',
				'_odw_keywords'       => "Schule\nSchule\n Bildung ",
				'_odw_temporal_start' => '2019-01-01',
				'_odw_is_hvd'         => 'yes',
			)
		);
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'odw_dataset' );
		\WP_Mock::userFunction( 'get_current_blog_id' )->andReturn( 1 );
		\WP_Mock::userFunction( 'get_post_field' )->with( 'post_modified_gmt', 41 )->andReturn( '2026-10-30 08:00:00' );
		foreach ( array(
			'_odw_license_index'   => self::CC0,
			'_odw_publisher_index' => 'Stadt Musterstadt',
			'_odw_period_from'     => '2019',
			'_odw_period_to'       => '9999',
			'_odw_open_index'      => '1',
			'_odw_hvd_index'       => '1',
			'_odw_index_state'     => '2|2026-10-30 08:00:00',
		) as $key => $value ) {
			\WP_Mock::userFunction( 'update_post_meta' )->once()->with( 41, $key, $value );
		}
		\WP_Mock::userFunction( 'delete_post_meta' )->once()->with( 41, '_odw_format_index' );
		\WP_Mock::userFunction( 'delete_post_meta' )->once()->with( 41, '_odw_keyword_index' );
		\WP_Mock::userFunction( 'add_post_meta' )->once()->with( 41, '_odw_keyword_index', 'Schule' );
		\WP_Mock::userFunction( 'add_post_meta' )->once()->with( 41, '_odw_keyword_index', 'Bildung' );
		$this->set_pending( array( 1 => array( 41 => true ) ) );

		ODW_Catalog_Filters::sync_index( 41 );
		$this->assertSame( array( 1 => array() ), $this->pending(), 'Abgeglichen: nicht mehr vorgemerkt.' );

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
	// -------------------------------------------------------------------------
	// Abgleich außerhalb des Speicherns
	// -------------------------------------------------------------------------

	/**
	 * Vorgemerkte Datensätze.
	 *
	 * @return array<int, true>
	 */
	private function pending(): array {
		$property = new ReflectionProperty( 'ODW_Catalog_Filters', 'pending' );
		$property->setAccessible( true );
		return (array) $property->getValue();
	}

	/**
	 * Setzt die vorgemerkten Datensätze.
	 *
	 * @param array<int, true> $pending Vormerkungen.
	 */
	private function set_pending( array $pending ): void {
		$property = new ReflectionProperty( 'ODW_Catalog_Filters', 'pending' );
		$property->setAccessible( true );
		$property->setValue( null, $pending );
	}

	/**
	 * Meta-Änderungen an Quellwerten (auch an den Carbon-Fields-Zeilen der
	 * Themen) merken den Datensatz vor — je Fall für sich geprüft. Andere
	 * Schlüssel, die Index-Felder selbst und Revisionen nicht.
	 *
	 * @dataProvider provide_meta_keys
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta-Schlüssel.
	 * @param bool   $queued   Wird vorgemerkt.
	 */
	public function test_meta_change_queues_dataset( int $post_id, string $meta_key, bool $queued ): void {
		$this->load();
		$this->set_pending( array() );
		\WP_Mock::userFunction( 'get_current_blog_id' )->andReturn( 3 );
		\WP_Mock::userFunction( 'add_action' )->andReturn( true );
		\WP_Mock::userFunction( 'get_post_type' )->andReturnUsing(
			static function ( $id ): string {
				return 51 === (int) $id ? 'revision' : 'odw_dataset';
			}
		);

		ODW_Catalog_Filters::note_meta_change( array( 1 ), $post_id, $meta_key );

		$this->assertSame( $queued ? array( 3 => array( $post_id => true ) ) : array(), $this->pending() );
		$this->set_pending( array() );
	}

	/**
	 * Meta-Schlüssel und ob sie vormerken.
	 *
	 * @return array<string, array{0: int, 1: string, 2: bool}>
	 */
	public function provide_meta_keys(): array {
		return array(
			'Lizenz'              => array( 50, '_odw_license', true ),
			'eigene Lizenz'       => array( 50, '_odw_license_custom', true ),
			'Format'              => array( 50, '_odw_format', true ),
			'Datei'               => array( 50, '_odw_file_id', true ),
			'Datei-Format'        => array( 50, '_odw_file_format', true ),
			'Thema (Carbon)'      => array( 52, '_odw_theme|||0|value', true ),
			'Index selbst'        => array( 53, '_odw_license_index', false ),
			'Themen-Index selbst' => array( 53, '_odw_theme_index', false ),
			'Abgleich-Stand'      => array( 53, '_odw_index_state', false ),
			'anderes Feld'        => array( 54, '_odw_description', false ),
			'Revision'            => array( 51, '_odw_license', false ),
		);
	}

	/**
	 * Die erste Vormerkung hängt den Abgleich an shutdown.
	 */
	public function test_queue_registers_shutdown_flush(): void {
		$this->load();
		$this->set_pending( array() );
		\WP_Mock::userFunction( 'get_current_blog_id' )->andReturn( 1 );
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'odw_dataset' );
		\WP_Mock::expectActionAdded( 'shutdown', array( 'ODW_Catalog_Filters', 'flush_pending' ) );

		ODW_Catalog_Filters::note_meta_change( 1, 50, '_odw_license' );
		$this->set_pending( array() );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	/**
	 * Die Methode init() hängt den Abgleich an alle Wege, auf denen sich Quellwerte ändern.
	 */
	public function test_init_registers_index_hooks(): void {
		$this->load();
		\WP_Mock::expectActionAdded( 'save_post', array( 'ODW_Catalog_Filters', 'sync_index' ), 30 );
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			\WP_Mock::expectActionAdded( $hook, array( 'ODW_Catalog_Filters', 'note_meta_change' ), 10, 3 );
		}
		\WP_Mock::expectActionAdded( 'deleted_post', array( 'ODW_Catalog_Filters', 'note_attachment_deleted' ), 10, 2 );
		\WP_Mock::expectActionAdded( 'wp_restore_post_revision', array( 'ODW_Catalog_Filters', 'after_restore' ), 20, 2 );

		ODW_Catalog_Filters::init();

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	/**
	 * Ein gelöschter Anhang merkt die Datensätze vor, die ihn als Datei
	 * führen; andere gelöschte Beiträge lösen keine Suche aus.
	 */
	public function test_attachment_deleted_queues_datasets(): void {
		$this->load();
		$this->set_pending( array() );
		\WP_Mock::userFunction( 'get_current_blog_id' )->andReturn( 1 );
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'odw_dataset' );
		\WP_Mock::userFunction( 'add_action' )->andReturn( true );
		\WP_Mock::userFunction( 'get_posts' )->once()->andReturnUsing(
			static function ( array $args ): array {
				return '_odw_file_id' === $args['meta_key'] && '90' === $args['meta_value'] && 'ids' === $args['fields'] ? array( 60, 61 ) : array();
			}
		);

		$page            = new WP_Post();
		$page->post_type = 'page';
		ODW_Catalog_Filters::note_attachment_deleted( 91, $page );

		$attachment            = new WP_Post();
		$attachment->post_type = 'attachment';
		ODW_Catalog_Filters::note_attachment_deleted( 90, $attachment );

		$this->assertSame(
			array(
				1 => array(
					60 => true,
					61 => true,
				),
			),
			$this->pending()
		);

		$this->set_pending( array() );
	}

	/**
	 * Am Request-Ende: jeder vorgemerkte Datensatz wird neu abgeglichen und —
	 * wenn veröffentlicht — an odw_catalog_changed gemeldet; in Multisite in
	 * seiner eigenen Website.
	 */
	public function test_flush_pending_resyncs_and_announces(): void {
		$this->load();
		require_once ODW_PLUGIN_DIR . 'includes/class-catalog-seo.php';
		$this->mock_meta( array( '_odw_license' => self::CC0 ) );
		\WP_Mock::userFunction( 'get_current_blog_id' )->andReturn( 1 );
		\WP_Mock::userFunction( 'is_multisite' )->andReturn( true );
		\WP_Mock::userFunction( 'switch_to_blog' )->once()->with( 2 )->andReturn( true );
		\WP_Mock::userFunction( 'restore_current_blog' )->once()->andReturn( true );
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'odw_dataset' );
		\WP_Mock::userFunction( 'get_post_field' )->andReturn( '2026-10-30 08:00:00' );
		\WP_Mock::userFunction( 'carbon_get_post_meta' )->andReturn( array() );
		\WP_Mock::userFunction( 'delete_post_meta' )->andReturn( true );
		\WP_Mock::userFunction( 'update_post_meta' )->times( 3 )->with( \Mockery::anyOf( 60, 61, 70 ), '_odw_license_index', self::CC0 );
		\WP_Mock::userFunction( 'update_post_meta' )->andReturn( true );
		\WP_Mock::userFunction( 'get_post_status' )->andReturnUsing(
			static function ( $id ): string {
				return 61 === (int) $id ? 'draft' : 'publish';
			}
		);
		$announced = array();
		foreach ( array( 60, 61, 70 ) as $id ) {
			\WP_Mock::onAction( 'odw_catalog_changed' )->with( $id )->perform(
				static function () use ( &$announced, $id ): void {
					$announced[] = $id;
				}
			);
		}

		$this->set_pending(
			array(
				1 => array(
					60 => true,
					61 => true,
				),
				2 => array( 70 => true ),
			)
		);
		ODW_Catalog_Filters::flush_pending();

		$this->assertSame( array( 60, 70 ), $announced, 'Nur Veröffentlichtes wird gemeldet.' );
		$this->assertSame( array(), $this->pending() );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	/**
	 * Nach dem Wiederherstellen einer Revision: zuerst den Meta-Cache
	 * verwerfen (Carbon Fields schreibt an der Meta-API vorbei), dann neu
	 * abgleichen.
	 */
	public function test_after_restore_drops_meta_cache(): void {
		$this->load();
		$order = array();
		\WP_Mock::userFunction( 'wp_cache_delete' )->once()->with( 44, 'post_meta' )->andReturnUsing(
			static function () use ( &$order ): bool {
				$order[] = 'cache';
				return true;
			}
		);
		\WP_Mock::userFunction( 'get_post_type' )->andReturnUsing(
			static function () use ( &$order ): string {
				$order[] = 'sync';
				return 'page';
			}
		);
		\WP_Mock::userFunction( 'get_post_status' )->andReturn( 'draft' );

		ODW_Catalog_Filters::after_restore( 44, 900 );

		$this->assertSame( 'cache', $order[0] );
		$this->assertContains( 'sync', $order );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	/**
	 * Ersatz für $wpdb mit prepare() und get_col().
	 *
	 * @return object
	 */
	private function wpdb_stub(): object {
		return new class() {
			/**
			 * Tabelle.
			 *
			 * @var string
			 */
			public $posts = 'wp_posts';

			/**
			 * Tabelle.
			 *
			 * @var string
			 */
			public $postmeta = 'wp_postmeta';

			/**
			 * Letzte Vorlage von prepare().
			 *
			 * @var string
			 */
			public $sql = '';

			/**
			 * Letzte Werte von prepare().
			 *
			 * @var array<int, mixed>
			 */
			public $args = array();

			/**
			 * Ersatz für wpdb::prepare().
			 *
			 * @param string $sql     Vorlage.
			 * @param mixed  ...$args Werte.
			 * @return string
			 */
			public function prepare( $sql, ...$args ) {
				$this->sql  = (string) $sql;
				$this->args = $args;
				return (string) $sql;
			}

			/**
			 * Ersatz für wpdb::get_col().
			 *
			 * @return array<int, string>
			 */
			public function get_col() {
				return array();
			}
		};
	}

	/**
	 * Nach einem Versionswechsel läuft die Migration erneut von vorn — und
	 * fragt nur Datensätze ab, deren Abgleich-Stand fehlt oder abweicht.
	 */
	public function test_migration_restarts_after_version_change(): void {
		$this->load();
		$GLOBALS['wpdb'] = $this->wpdb_stub();

		\WP_Mock::userFunction( 'get_option' )->with( 'odw_catalog_index' )->andReturn(
			array(
				'version' => 2,
				'plugin'  => '0.9.0',
				'after'   => 500,
				'done'    => true,
				'checked' => time(),
			)
		);
		\WP_Mock::userFunction( 'update_option' )->once()->with(
			'odw_catalog_index',
			\Mockery::on(
				static function ( $value ): bool {
					return 2 === $value['version'] && ODW_VERSION === $value['plugin'] && 0 === $value['after'] && true === $value['done'] && $value['checked'] > 0;
				}
			),
			true
		);

		ODW_Catalog_Filters::maybe_migrate_index();
		$this->assertSame( array( '_odw_index_state', 0, 2, 200 ), $GLOBALS['wpdb']->args, 'Von vorn: ID > 0, Index-Version 2.' );
		$this->assertStringContainsString( "CONCAT( %d, '|', p.post_modified_gmt )", $GLOBALS['wpdb']->sql );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );

		unset( $GLOBALS['wpdb'] );
	}

	/**
	 * Auch ohne Versionswechsel sieht sie alle 12 Stunden nach — so fallen
	 * Datensätze auf, die eine ältere Version (Rollback) geändert hat.
	 */
	public function test_migration_rechecks_periodically(): void {
		$this->load();
		$GLOBALS['wpdb'] = $this->wpdb_stub();
		\WP_Mock::userFunction( 'get_option' )->andReturn(
			array(
				'version' => 2,
				'plugin'  => ODW_VERSION,
				'after'   => 500,
				'done'    => true,
				'checked' => time() - 13 * 3600,
			)
		);
		\WP_Mock::userFunction( 'update_option' )->once();

		ODW_Catalog_Filters::maybe_migrate_index();
		$this->assertSame( 0, $GLOBALS['wpdb']->args[1], 'Nachprüfung von vorn.' );

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );

		unset( $GLOBALS['wpdb'] );
	}

	/**
	 * Für die aktuelle Version abgeschlossen und kürzlich geprüft: keine
	 * Abfrage, kein Schreiben.
	 */
	public function test_migration_done_for_current_version(): void {
		$this->load();
		\WP_Mock::userFunction( 'get_option' )->andReturn(
			array(
				'version' => 2,
				'plugin'  => ODW_VERSION,
				'after'   => 500,
				'done'    => true,
				'checked' => time(),
			)
		);
		\WP_Mock::userFunction( 'update_option' )->never();

		ODW_Catalog_Filters::maybe_migrate_index();

		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	// -------------------------------------------------------------------------
	// Formular und Rückweg
	// -------------------------------------------------------------------------

	/**
	 * Attributwerte kommen exakt zurück — auch mit „&amp;" im Wert.
	 */
	public function test_attr_exact(): void {
		$this->load();
		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing(
			static function ( $text ): string {
				// Wie _wp_specialchars() mit double_encode = false.
				return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
			}
		);

		foreach ( array( 'https://stadt.example/n?id=7&lang=de', 'https://stadt.example/n?id=7&amp;lang=de', 'a&#038;b "c" <d>' ) as $value ) {
			$this->assertSame( $value, html_entity_decode( ODW_Catalog_Filters::attr_exact( $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $value );
		}
	}

	/**
	 * Ein zu kurzer Suchbegriff wird für den Hinweis erkannt, ein leerer oder
	 * gültiger nicht.
	 */
	public function test_short_query(): void {
		$this->load();

		$this->assertSame( 'a', ODW_Catalog_Filters::short_query( array( 'odw_q' => ' a ' ) ) );
		$this->assertSame( '😀', ODW_Catalog_Filters::short_query( array( 'odw_q' => '😀' ) ) );
		$this->assertSame( '', ODW_Catalog_Filters::short_query( array( 'odw_q' => '   ' ) ) );
		$this->assertSame( '', ODW_Catalog_Filters::short_query( array( 'odw_q' => 'ab' ) ) );
		$this->assertSame( '', ODW_Catalog_Filters::short_query( array( 'odw_q' => array( 'a' ) ) ) );
	}

	/**
	 * Lizenzen der Auswahlliste in ihrer Schreibweise; das Auswahlfeld
	 * markiert eine Schreibvariante, statt sie mit 0 Treffern anzulegen.
	 */
	public function test_license_case(): void {
		$this->load();

		$this->assertSame( self::CC_BY, ODW_Catalog_Filters::parse_license( 'HTTPS://CreativeCommons.org/licenses/by/4.0/' ) );
		$this->assertSame( 'https://Stadt.example/Lizenz', ODW_Catalog_Filters::parse_license( 'https://Stadt.example/Lizenz' ), 'Eigene URIs bleiben, wie sie sind.' );

		$options = ODW_Catalog_Filters::facet_options( 'license', array( 'https://stadt.example/lizenz' => 2 ), 'https://stadt.example/Lizenz' );
		$this->assertCount( 1, $options );
		$this->assertTrue( $options[0]['selected'] );
		$this->assertSame( 2, $options[0]['count'] );
	}

	/**
	 * Facetten übernehmen die Wahl des Besuchers in allen übrigen Facetten
	 * und die Suche.
	 */
	public function test_conditions_take_visitor_choice(): void {
		$this->load();

		$config = ODW_Catalog::parse_atts(
			array(
				'filters' => 'theme,license,format',
				'search'  => '1',
			)
		);
		$state  = $this->state(
			array(
				'theme'   => self::THEME_BASE . 'EDUC',
				'license' => self::CC0,
				'format'  => 'CSV',
				'q'       => 'Schule',
			)
		);

		$this->assertSame(
			array(
				'exclude'   => 0,
				'themes'    => array(),
				'theme'     => self::THEME_BASE . 'EDUC',
				'license'   => self::CC0,
				'format'    => 'CSV',
				'q'         => 'Schule',
				'publisher' => '',
				'keywords'  => array(),
				'from'      => '',
				'to'        => '',
				'open'      => '',
				'hvd'       => '',
			),
			ODW_Catalog_Filters::conditions( $config, $state, 0 )
		);
	}
	// -------------------------------------------------------------------------
	// Stufe 3a: Herausgeber, Schlagworte, Zeitraum, Open Data, HVD
	// -------------------------------------------------------------------------

	/**
	 * Die neuen Einträge im Attribut filters, in Formularreihenfolge.
	 */
	public function test_parse_filters_stage3a(): void {
		$this->load();

		$this->assertSame(
			array( 'theme', 'publisher', 'keyword', 'sort', 'period', 'open', 'hvd' ),
			ODW_Catalog_Filters::parse_filters( 'hvd,open,period,sort,keyword,publisher,theme', false, false )
		);
	}

	/**
	 * GET-Werte der neuen Filter.
	 *
	 * @dataProvider provide_get_stage3a
	 *
	 * @param array<string, mixed> $get      GET.
	 * @param string               $key      Geprüfter Schlüssel.
	 * @param mixed                $expected Erwarteter Wert.
	 */
	public function test_parse_get_stage3a( array $get, string $key, $expected ): void {
		$this->load();

		$this->assertSame( $expected, ODW_Catalog_Filters::parse_get( $get )[ $key ] );
	}

	/**
	 * GET-Werte und das Ergebnis.
	 *
	 * @return array<string, array{0: array<string, mixed>, 1: string, 2: mixed}>
	 */
	public function provide_get_stage3a(): array {
		return array(
			'Herausgeber'                   => array( array( 'odw_publisher' => "  Stadt \t Musterstadt " ), 'publisher', 'Stadt Musterstadt' ),
			'Herausgeber zu lang'           => array( array( 'odw_publisher' => str_repeat( 'a', 201 ) ), 'publisher', '' ),
			'Herausgeber mit Steuerzeichen' => array( array( 'odw_publisher' => "A\x01B" ), 'publisher', '' ),
			'Herausgeber als Liste'         => array( array( 'odw_publisher' => array( 'x' ) ), 'publisher', '' ),
			'Schlagworte'                   => array( array( 'odw_keyword' => array( 'Sport', ' Vereine ', 'Sport', '', array( 'x' ) ) ), 'keywords', array( 'Sport', 'Vereine' ) ),
			'ein Schlagwort ohne Liste'     => array( array( 'odw_keyword' => 'Sport' ), 'keywords', array( 'Sport' ) ),
			'höchstens 20 Schlagworte'      => array( array( 'odw_keyword' => range( 1, 30 ) ), 'keywords', array_map( 'strval', range( 1, 20 ) ) ),
			'ohne Schlagworte'              => array( array(), 'keywords', array() ),
			'Jahr von'                      => array( array( 'odw_from' => '2020' ), 'from', '2020' ),
			'Jahr ungültig'                 => array( array( 'odw_from' => '20x0' ), 'from', '' ),
			'Jahr fünfstellig'              => array( array( 'odw_to' => '20201' ), 'to', '' ),
			'Zeitraum gedreht (von)'        => array(
				array(
					'odw_from' => '2025',
					'odw_to'   => '2020',
				),
				'from',
				'2020',
			),
			'Zeitraum gedreht (bis)'        => array(
				array(
					'odw_from' => '2025',
					'odw_to'   => '2020',
				),
				'to',
				'2025',
			),
			'Open Data'                     => array( array( 'odw_open' => '1' ), 'open', '1' ),
			'Open Data ungültig'            => array( array( 'odw_open' => 'ja' ), 'open', '' ),
			'HVD'                           => array( array( 'odw_hvd' => '1' ), 'hvd', '1' ),
		);
	}

	/**
	 * Die neuen Filter wirken nur, wenn der Katalog sie freischaltet.
	 */
	public function test_state_stage3a(): void {
		$this->load();

		$get = ODW_Catalog_Filters::parse_get(
			array(
				'odw_publisher' => 'Stadt Musterstadt',
				'odw_keyword'   => array( 'Sport' ),
				'odw_from'      => '2020',
				'odw_to'        => '2024',
				'odw_open'      => '1',
				'odw_hvd'       => '1',
			)
		);

		$off = ODW_Catalog_Filters::state( ODW_Catalog::parse_atts( array( 'filters' => 'theme' ) ), $get );
		$this->assertSame( $this->state(), $off );
		$this->assertFalse( ODW_Catalog_Filters::is_active( $off ) );

		$on = ODW_Catalog_Filters::state( ODW_Catalog::parse_atts( array( 'filters' => 'publisher,keyword,period,open,hvd' ) ), $get );
		$this->assertSame(
			$this->state(
				array(
					'publisher' => 'Stadt Musterstadt',
					'keywords'  => array( 'Sport' ),
					'from'      => '2020',
					'to'        => '2024',
					'open'      => '1',
					'hvd'       => '1',
				)
			),
			$on
		);
		$this->assertTrue( ODW_Catalog_Filters::is_active( $this->state( array( 'keywords' => array( 'Sport' ) ) ) ) );

		$this->assertSame(
			array(
				'odw_publisher' => 'Stadt Musterstadt',
				'odw_keyword'   => array( 'Sport' ),
				'odw_from'      => '2020',
				'odw_to'        => '2024',
				'odw_open'      => '1',
				'odw_hvd'       => '1',
			),
			ODW_Catalog_Filters::state_args( $on )
		);
	}

	/**
	 * Abfrage: Herausgeber gleich, Schlagworte IN (mindestens eines),
	 * Zeitraum als Überschneidung über numerische Vergleiche, Kästchen = '1'.
	 */
	public function test_apply_to_query_stage3a(): void {
		$this->load();

		$args = ODW_Catalog_Filters::apply_to_query(
			array(),
			ODW_Catalog::parse_atts( '' ),
			$this->state(
				array(
					'publisher' => 'Stadt Musterstadt',
					'keywords'  => array( 'Sport', 'Vereine' ),
					'from'      => '2020',
					'to'        => '2024',
					'open'      => '1',
					'hvd'       => '1',
				)
			)
		);

		$this->assertSame(
			array(
				array(
					'key'   => '_odw_publisher_index',
					'value' => 'Stadt Musterstadt',
				),
				array(
					'key'     => '_odw_keyword_index',
					'value'   => array( 'Sport', 'Vereine' ),
					'compare' => 'IN',
				),
				array(
					'key'     => '_odw_period_to',
					'value'   => 2020,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => '_odw_period_from',
					'value'   => 2024,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'   => '_odw_open_index',
					'value' => '1',
				),
				array(
					'key'   => '_odw_hvd_index',
					'value' => '1',
				),
			),
			$args['meta_query']
		);
	}

	/**
	 * Bedingungen eines Katalogs mit allen neuen Filtern.
	 *
	 * @return array<string, mixed>
	 */
	private function conditions_stage3a(): array {
		return array(
			'exclude'   => 0,
			'themes'    => array(),
			'theme'     => '',
			'license'   => '',
			'format'    => '',
			'q'         => '',
			'publisher' => 'Stadt Musterstadt',
			'keywords'  => array( 'Sport', 'Vereine' ),
			'from'      => '2020',
			'to'        => '2024',
			'open'      => '1',
			'hvd'       => '1',
		);
	}

	/**
	 * Facetten-SQL: Die Schlagwort-Facette zählt ohne die eigene Auswahl,
	 * aber mit Herausgeber, Zeitraum und Kästchen. Platzhalter und Werte
	 * passen zusammen.
	 */
	public function test_facet_sql_stage3a(): void {
		$this->load();

		list( $sql, $values ) = ODW_Catalog_Filters::facet_sql( 'keyword', $this->conditions_stage3a(), 'wp_posts', 'wp_postmeta' );

		$this->assertSame( '_odw_keyword_index', $values[0] );
		$this->assertNotContains( 'Sport', $values, 'Die eigene Auswahl zählt nicht mit.' );
		$this->assertContains( 'Stadt Musterstadt', $values );
		$this->assertContains( '_odw_open_index', $values );
		$this->assertContains( '_odw_hvd_index', $values );
		$this->assertStringContainsString( 'CAST( y.meta_value AS SIGNED ) >= %d', $sql );
		$this->assertStringContainsString( 'CAST( y.meta_value AS SIGNED ) <= %d', $sql );
		$this->assertSame( array( '_odw_period_to', 2020 ), array_slice( $values, array_search( '_odw_period_to', $values, true ), 2 ) );
		$this->assertSame(
			substr_count( $sql, '%s' ) + substr_count( $sql, '%d' ),
			count( $values ),
			'Jeder Platzhalter hat genau einen Wert.'
		);

		// Das HVD-Kästchen zählt ohne die eigene Auswahl, mit den Schlagworten.
		list( $hvd_sql, $hvd_values ) = ODW_Catalog_Filters::facet_sql( 'hvd', $this->conditions_stage3a(), 'wp_posts', 'wp_postmeta' );
		$this->assertSame( '_odw_hvd_index', $hvd_values[0] );
		$this->assertSame( 1, count( array_keys( $hvd_values, '_odw_hvd_index', true ) ), 'Nur als gezählte Facette, nicht als Bedingung.' );
		$this->assertContains( 'Vereine', $hvd_values );
		$this->assertSame( substr_count( $hvd_sql, '%s' ) + substr_count( $hvd_sql, '%d' ), count( $hvd_values ) );
	}

	/**
	 * Schlagworte: mehrere gewählt, ein gewähltes ohne Treffer bleibt stehen.
	 */
	public function test_facet_options_keywords(): void {
		$this->load();

		$options = ODW_Catalog_Filters::facet_options(
			'keyword',
			array(
				'Vereine' => 3,
				'Sport'   => 2,
				'2024'    => 1,
			),
			array( 'Sport', 'Quartier' )
		);

		$this->assertSame( array( '2024', 'Quartier', 'Sport', 'Vereine' ), array_column( $options, 'value' ) );
		$this->assertSame( array( false, true, true, false ), array_column( $options, 'selected' ) );
		$this->assertSame( array( 1, 0, 2, 3 ), array_column( $options, 'count' ) );
	}

	/**
	 * Zeitlicher Bezug als Jahre: offenes Ende, offener Anfang, vertauscht,
	 * ohne Angabe.
	 */
	public function test_period(): void {
		$this->load();

		$this->assertSame( array( '2020', '2025' ), ODW_Catalog_Filters::period( '2020-01-01', '2025-12-31' ) );
		$this->assertSame( array( '2019', '9999' ), ODW_Catalog_Filters::period( '2019-05-01', '' ), 'Nur Start: seit 2019.' );
		$this->assertSame( array( '0', '2024' ), ODW_Catalog_Filters::period( '', '2024-12-31' ), 'Nur Ende.' );
		$this->assertSame( array( '2020', '2025' ), ODW_Catalog_Filters::period( '2025-01-01', '2020-01-01' ), 'Vertauscht.' );
		$this->assertSame( array( '', '' ), ODW_Catalog_Filters::period( '', 'unbekannt' ) );
	}

	/**
	 * Jahresliste für den Zeitraum.
	 */
	public function test_year_range(): void {
		$this->load();

		$this->assertSame(
			array( 2025, 2024, 2023, 2022, 2021, 2020 ),
			ODW_Catalog_Filters::year_range(
				array(
					'lo_from' => '2020',
					'hi_from' => '2024',
					'lo_to'   => '2021',
					'hi_to'   => '2025',
					'hi_raw'  => '2025',
				),
				2026
			)
		);
		$this->assertSame(
			array( 2026, 2025, 2024 ),
			ODW_Catalog_Filters::year_range(
				array(
					'lo_from' => '2024',
					'hi_from' => '2024',
					'lo_to'   => '9999',
					'hi_to'   => null,
					'hi_raw'  => '9999',
				),
				2026
			),
			'Ein offener Zeitraum reicht bis zum laufenden Jahr.'
		);
		$this->assertCount(
			200,
			ODW_Catalog_Filters::year_range(
				array(
					'lo_from' => '201',
					'hi_to'   => '2025',
					'hi_raw'  => '2025',
				),
				2026
			),
			'Höchstens 200 Jahre.'
		);
		$this->assertSame( array(), ODW_Catalog_Filters::year_range( array(), 2026 ), 'Ohne Zeitangaben keine Jahre.' );
	}

	/**
	 * Aktive Filter: Beschriftung und der Zustand ohne den jeweiligen Filter;
	 * mehrere Schlagworte einzeln.
	 */
	public function test_active_filters(): void {
		$this->load();

		$state  = $this->state(
			array(
				'theme'     => self::THEME_BASE . 'EDUC',
				'q'         => 'Schule',
				'publisher' => 'Stadt Musterstadt',
				'keywords'  => array( 'Sport', 'Vereine' ),
				'from'      => '2020',
				'open'      => '1',
				'sort'      => 'modified',
			)
		);
		$active = ODW_Catalog_Filters::active_filters( $state );

		$this->assertSame(
			array(
				'Suche: „Schule“',
				'Thema: Bildung, Kultur & Sport',
				'Herausgeber: Stadt Musterstadt',
				'Schlagwort: Sport',
				'Schlagwort: Vereine',
				'Zeitraum: ab 2020',
				'Nur Open Data',
				'Sortierung: Zuletzt aktualisierte zuerst',
			),
			array_column( $active, 'label' )
		);
		$this->assertSame( array( 'Vereine' ), $active[3]['state']['keywords'], 'Ein Schlagwort entfernen, das andere bleibt.' );
		$this->assertSame( '', $active[2]['state']['publisher'] );
		$this->assertSame( 'Stadt Musterstadt', $active[3]['state']['publisher'], 'Die übrigen Filter bleiben.' );
		$this->assertSame( array(), ODW_Catalog_Filters::active_filters( $this->state() ) );
	}

	/**
	 * Beschriftung des Zeitraums.
	 */
	public function test_period_label(): void {
		$this->load();

		$this->assertSame( 'Zeitraum: 2020–2024', ODW_Catalog_Filters::period_label( '2020', '2024' ) );
		$this->assertSame( 'Zeitraum: 2020', ODW_Catalog_Filters::period_label( '2020', '2020' ) );
		$this->assertSame( 'Zeitraum: ab 2020', ODW_Catalog_Filters::period_label( '2020', '' ) );
		$this->assertSame( 'Zeitraum: bis 2024', ODW_Catalog_Filters::period_label( '', '2024' ) );
	}

	/**
	 * Index-Werte der neuen Felder: Herausgeber und Schlagworte wie aus der
	 * Adresse gelesen, Zeitraum in Jahren, Open Data aus der Lizenzliste, HVD.
	 */
	public function test_index_values_stage3a(): void {
		$this->load();
		$this->mock_meta(
			array(
				'_odw_license'        => 'sonstige',
				'_odw_license_custom' => 'https://creativecommons.org/licenses/by-nc/4.0/',
				'_odw_publisher'      => " Kreissportbund\nMusterkreis ",
				'_odw_keywords'       => "Sport\r\nVereine\n\nSport\n" . str_repeat( 'x', 201 ),
				'_odw_temporal_start' => '2015-01-01',
				'_odw_temporal_end'   => '2025-12-31',
				'_odw_is_hvd'         => '',
			)
		);

		$this->assertSame(
			$this->index(
				array(
					'license'   => 'https://creativecommons.org/licenses/by-nc/4.0/',
					'publisher' => 'Kreissportbund Musterkreis',
					'keywords'  => array( 'Sport', 'Vereine' ),
					'from'      => '2015',
					'to'        => '2025',
				)
			),
			ODW_Catalog_Filters::index_values( 40 ),
			'CC BY-NC ist keine freie Lizenz; zu lange Schlagworte fehlen im Index.'
		);
	}

	/**
	 * Herausgeber und Schlagworte, die die Facette anbietet, kommen über das
	 * Formular zurück.
	 */
	public function test_facet_options_publisher_round_trip(): void {
		$this->load();

		$options = ODW_Catalog_Filters::facet_options(
			'publisher',
			array(
				'Stadt Musterstadt'         => 2,
				'Bürgernetz e.V. & Partner' => 1,
			),
			''
		);
		$this->assertSame( array( 'Bürgernetz e.V. & Partner', 'Stadt Musterstadt' ), array_column( $options, 'param' ) );
	}
	/**
	 * Chips der aktiven Filter: je ein Link ohne diesen Filter, dazu „Alle
	 * entfernen"; ohne aktive Filter nichts.
	 */
	public function test_render_active(): void {
		$this->load();
		\WP_Mock::userFunction( 'remove_query_arg' )->andReturnArg( 1 );
		\WP_Mock::userFunction( 'add_query_arg' )->andReturnUsing(
			static function ( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);

		$html = ODW_Catalog_Filters::render_active(
			$this->state(
				array(
					'keywords' => array( 'Sport', 'Vereine' ),
					'hvd'      => '1',
				)
			),
			'https://example.org/daten/',
			'https://example.org/daten/#odw-catalog'
		);

		$this->assertSame( 4, substr_count( $html, '<li>' ), 'Zwei Schlagworte, HVD, „Alle entfernen".' );
		$this->assertStringContainsString( 'Schlagwort: Sport', $html );
		$this->assertStringContainsString( 'Nur hochwertige Datensätze (HVD)', $html );
		// Der Chip „Sport" führt zur Liste mit Vereine und HVD.
		$this->assertMatchesRegularExpression( '#href="[^"]*odw_keyword%5B0%5D=Vereine[^"]*odw_hvd=1[^"]*">Schlagwort: Sport#', $html );
		$this->assertStringContainsString( 'href="https://example.org/daten/#odw-catalog">Alle entfernen', $html );
		$this->assertSame( '', ODW_Catalog_Filters::render_active( $this->state(), 'https://example.org/daten/', '' ) );
	}
}
