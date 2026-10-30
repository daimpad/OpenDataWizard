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
				'_odw_license' => self::CC0,
			)
		);
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'odw_dataset' );
		\WP_Mock::userFunction( 'get_current_blog_id' )->andReturn( 1 );
		\WP_Mock::userFunction( 'get_post_field' )->with( 'post_modified_gmt', 41 )->andReturn( '2026-10-30 08:00:00' );
		\WP_Mock::userFunction( 'update_post_meta' )->once()->with( 41, '_odw_license_index', self::CC0 );
		\WP_Mock::userFunction( 'update_post_meta' )->once()->with( 41, '_odw_index_state', '1|2026-10-30 08:00:00' );
		\WP_Mock::userFunction( 'delete_post_meta' )->once()->with( 41, '_odw_format_index' );
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
				'version' => 1,
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
					return 1 === $value['version'] && ODW_VERSION === $value['plugin'] && 0 === $value['after'] && true === $value['done'] && $value['checked'] > 0;
				}
			),
			true
		);

		ODW_Catalog_Filters::maybe_migrate_index();
		$this->assertSame( array( '_odw_index_state', 0, 1, 200 ), $GLOBALS['wpdb']->args, 'Von vorn: ID > 0, Index-Version 1.' );
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
				'version' => 1,
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
				'version' => 1,
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
				'exclude' => 0,
				'themes'  => array(),
				'theme'   => self::THEME_BASE . 'EDUC',
				'license' => self::CC0,
				'format'  => 'CSV',
				'q'       => 'Schule',
			),
			ODW_Catalog_Filters::conditions( $config, $state, 0 )
		);
	}
}
