<?php
/**
 * Tests für ODW_Quality
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Quality.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Quality extends TestCase {

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
	 * Loads ODW_Quality once per test run.
	 */
	private function load_class(): void {
		if ( ! class_exists( 'ODW_Quality' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-quality.php';
		}
	}

	// -------------------------------------------------------------------------
	// get_level()
	// -------------------------------------------------------------------------

	/**
	 * Score 80 maps to LEVEL_HIGH.
	 */
	public function test_get_level_returns_high_at_80(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_HIGH, ODW_Quality::get_level( 80 ) );
	}

	/**
	 * Score 100 maps to LEVEL_PERFECT.
	 */
	public function test_get_level_returns_perfect_at_100(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_PERFECT, ODW_Quality::get_level( 100 ) );
	}

	/**
	 * Score 56 maps to LEVEL_HIGH (some optional fields filled).
	 */
	public function test_get_level_returns_high_at_56(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_HIGH, ODW_Quality::get_level( 56 ) );
	}

	/**
	 * Score 55 maps to LEVEL_SUFFICIENT (exactly required fields only).
	 */
	public function test_get_level_returns_sufficient_at_55(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_SUFFICIENT, ODW_Quality::get_level( 55 ) );
	}

	/**
	 * Score 99 maps to LEVEL_HIGH (boundary check below perfect).
	 */
	public function test_get_level_returns_high_at_99(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_HIGH, ODW_Quality::get_level( 99 ) );
	}

	/**
	 * Score 0 maps to LEVEL_LOW.
	 */
	public function test_get_level_returns_low_at_0(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_LOW, ODW_Quality::get_level( 0 ) );
	}

	/**
	 * Score 49 is LEVEL_LOW (boundary check).
	 */
	public function test_get_level_returns_low_at_49(): void {
		$this->load_class();
		$this->assertSame( ODW_Quality::LEVEL_LOW, ODW_Quality::get_level( 49 ) );
	}

	// -------------------------------------------------------------------------
	// get_level_label()
	// -------------------------------------------------------------------------

	/**
	 * LEVEL_HIGH maps to label "Gut".
	 */
	public function test_get_level_label_high(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( 'Gut', ODW_Quality::get_level_label( ODW_Quality::LEVEL_HIGH ) );
	}

	/**
	 * LEVEL_PERFECT maps to label "Perfekt".
	 */
	public function test_get_level_label_perfect(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( 'Perfekt', ODW_Quality::get_level_label( ODW_Quality::LEVEL_PERFECT ) );
	}

	/**
	 * LEVEL_SUFFICIENT maps to label "Ausreichend".
	 */
	public function test_get_level_label_sufficient(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( 'Ausreichend', ODW_Quality::get_level_label( ODW_Quality::LEVEL_SUFFICIENT ) );
	}

	/**
	 * LEVEL_LOW maps to label "Verbesserungsbedarf".
	 */
	public function test_get_level_label_low(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( 'Verbesserungsbedarf', ODW_Quality::get_level_label( ODW_Quality::LEVEL_LOW ) );
	}

	/**
	 * An unknown level string returns the fallback label "Unbekannt".
	 */
	public function test_get_level_label_unknown_returns_fallback(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( 'Unbekannt', ODW_Quality::get_level_label( 'invalid' ) );
	}

	// -------------------------------------------------------------------------
	// get_indicators()
	// -------------------------------------------------------------------------

	/**
	 * The sum of all indicator points equals 100.
	 */
	public function test_get_indicators_sums_to_100(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$total = array_sum( array_column( ODW_Quality::get_indicators(), 'points' ) );
		$this->assertSame( 100, $total );
	}

	/**
	 * The 'title' indicator key is present in the indicators list.
	 */
	public function test_get_indicators_has_title_key(): void {
		$this->load_class();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$keys = array_column( ODW_Quality::get_indicators(), 'key' );
		$this->assertContains( 'title', $keys );
	}

	// -------------------------------------------------------------------------
	// get_rating() — MQA-Bewertungsstufen (proportional)
	// -------------------------------------------------------------------------

	/**
	 * A full assessable score maps to the excellent rating.
	 */
	public function test_get_rating_full_is_excellent(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( ODW_Quality::RATING_EXCELLENT, ODW_Quality::get_rating( 230, 230 ) );
		$this->assertSame( ODW_Quality::RATING_EXCELLENT, ODW_Quality::get_rating( 405, 405 ) );
	}

	/**
	 * The proportional thresholds (351/221/121 of 405) map to the correct bands.
	 */
	public function test_get_rating_bands_on_full_scale(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( ODW_Quality::RATING_EXCELLENT, ODW_Quality::get_rating( 351, 405 ) );
		$this->assertSame( ODW_Quality::RATING_GOOD, ODW_Quality::get_rating( 350, 405 ) );
		$this->assertSame( ODW_Quality::RATING_GOOD, ODW_Quality::get_rating( 221, 405 ) );
		$this->assertSame( ODW_Quality::RATING_SUFFICIENT, ODW_Quality::get_rating( 220, 405 ) );
		$this->assertSame( ODW_Quality::RATING_SUFFICIENT, ODW_Quality::get_rating( 121, 405 ) );
		$this->assertSame( ODW_Quality::RATING_BAD, ODW_Quality::get_rating( 120, 405 ) );
		$this->assertSame( ODW_Quality::RATING_BAD, ODW_Quality::get_rating( 0, 405 ) );
	}

	/**
	 * A zero assessable maximum yields the bad rating without division by zero.
	 */
	public function test_get_rating_zero_assessable_is_bad(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( ODW_Quality::RATING_BAD, ODW_Quality::get_rating( 0, 0 ) );
	}

	/**
	 * Each MQA rating constant has a non-empty label; unknown falls back.
	 */
	public function test_get_rating_label(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$this->assertSame( 'Ausgezeichnet', ODW_Quality::get_rating_label( ODW_Quality::RATING_EXCELLENT ) );
		$this->assertSame( 'Mangelhaft', ODW_Quality::get_rating_label( ODW_Quality::RATING_BAD ) );
		$this->assertSame( 'Unbekannt', ODW_Quality::get_rating_label( 'nope' ) );
	}

	// -------------------------------------------------------------------------
	// get_metrics() — MQA-Konfiguration
	// -------------------------------------------------------------------------

	/**
	 * The MQA metric points sum to 405 across the five dimensions.
	 */
	public function test_get_metrics_sum_to_405(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$total = array_sum( array_column( ODW_Quality::get_metrics(), 'points' ) );
		$this->assertSame( 405, $total );
	}

	/**
	 * Each dimension reaches its documented MQA maximum.
	 */
	public function test_get_metrics_dimension_maxima(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$by_dim = array();
		foreach ( ODW_Quality::get_metrics() as $m ) {
			$by_dim[ $m['dimension'] ] = ( $by_dim[ $m['dimension'] ] ?? 0 ) + (int) $m['points'];
		}

		$this->assertSame( 100, $by_dim['findability'] );
		$this->assertSame( 100, $by_dim['accessibility'] );
		$this->assertSame( 110, $by_dim['interoperability'] );
		$this->assertSame( 75, $by_dim['reusability'] );
		$this->assertSame( 20, $by_dim['contextuality'] );
	}

	// -------------------------------------------------------------------------
	// get() / store() — MQA-Persistierung
	// -------------------------------------------------------------------------

	/**
	 * Returns a zeroed result when no MQA data is stored in post meta.
	 */
	public function test_get_returns_empty_result_when_nothing_stored(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_mqa', true )
			->andReturn( '' );

		$result = ODW_Quality::get( 42 );

		$this->assertSame( 0, $result['score'] );
		$this->assertSame( '', $result['level'] );
		$this->assertSame( '', $result['rating'] );
	}

	/**
	 * Returns the stored MQA structure verbatim.
	 */
	public function test_get_returns_stored_mqa(): void {
		$this->load_class();

		$stored = array(
			'achieved'      => 200,
			'assessable'    => 230,
			'max'           => 405,
			'rating'        => 'good',
			'dimensions'    => array(),
			'metrics'       => array(),
			'calculated_at' => '2026-04-21 10:00:00',
			'score'         => 87,
			'level'         => 'high',
		);

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_mqa', true )
			->andReturn( $stored );

		$result = ODW_Quality::get( 42 );

		$this->assertSame( 'good', $result['rating'] );
		$this->assertSame( 200, $result['achieved'] );
		$this->assertSame( 87, $result['score'] );
	}

	/**
	 * Persists the full MQA array plus the backward-compatible scalar keys.
	 */
	public function test_store_persists_mqa_and_bc_keys(): void {
		$this->load_class();

		$result = array(
			'rating'        => 'good',
			'calculated_at' => '2026-04-21 12:00:00',
			'score'         => 75,
			'level'         => 'high',
		);

		\WP_Mock::userFunction( 'update_post_meta' )->with( 7, '_odw_mqa', $result )->once();
		\WP_Mock::userFunction( 'update_post_meta' )->with( 7, '_odw_quality_score', 75 )->once();
		\WP_Mock::userFunction( 'update_post_meta' )->with( 7, '_odw_quality_level', 'high' )->once();
		\WP_Mock::userFunction( 'update_post_meta' )->with( 7, '_odw_quality_calculated_at', '2026-04-21 12:00:00' )->once();

		ODW_Quality::store( 7, $result );

		$this->addToAssertionCount( 4 );
	}

	// -------------------------------------------------------------------------
	// append_to_jsonld()
	// -------------------------------------------------------------------------

	/**
	 * Adds odw:qualityScore (405-scale, rating, dimensions) when MQA data exists.
	 */
	public function test_append_to_jsonld_adds_mqa_data(): void {
		$this->load_class();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_mqa', true )
			->andReturn(
				array(
					'achieved'      => 200,
					'assessable'    => 230,
					'max'           => 405,
					'rating'        => 'good',
					'dimensions'    => array(
						'findability' => array(
							'achieved'   => 100,
							'assessable' => 100,
							'max'        => 100,
						),
					),
					'metrics'       => array(),
					'calculated_at' => '2026-04-21 09:00:00',
				)
			);

		$dataset = array( '@type' => 'dcat:Dataset' );
		$result  = ODW_Quality::append_to_jsonld( $dataset, 5 );

		$this->assertArrayHasKey( 'odw:qualityScore', $result );
		$this->assertSame( 200, $result['odw:qualityScore']['odw:score'] );
		$this->assertSame( 'good', $result['odw:qualityScore']['odw:rating'] );
		$this->assertSame( 405, $result['odw:qualityScore']['odw:maxScore'] );
		$this->assertArrayHasKey( 'findability', $result['odw:qualityScore']['odw:dimensions'] );
	}

	/**
	 * Leaves the dataset array unchanged when no MQA data is stored.
	 */
	public function test_append_to_jsonld_skips_when_no_data(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_mqa', true )
			->andReturn( '' );

		$dataset = array( '@type' => 'dcat:Dataset' );
		$result  = ODW_Quality::append_to_jsonld( $dataset, 5 );

		$this->assertArrayNotHasKey( 'odw:qualityScore', $result );
	}

	// -------------------------------------------------------------------------
	// url_is_reachable() — MQA Phase 3 (opt-in reachability)
	// -------------------------------------------------------------------------

	/**
	 * Invokes the private static url_is_reachable() helper.
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	private function call_url_is_reachable( string $url ): bool {
		$ref = new \ReflectionMethod( 'ODW_Quality', 'url_is_reachable' );
		$ref->setAccessible( true );
		return (bool) $ref->invoke( null, $url );
	}

	/**
	 * A cached negative result short-circuits without a network request.
	 */
	public function test_url_is_reachable_uses_cached_result(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_transient' )->andReturn( '0' );

		$this->assertFalse( $this->call_url_is_reachable( 'https://example.com/data.csv' ) );
	}

	/**
	 * A 200 HEAD response marks the URL reachable and caches the result.
	 */
	public function test_url_is_reachable_maps_2xx_to_true(): void {
		$this->load_class();

		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array( 'response' => array( 'code' => 200 ) ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		\WP_Mock::userFunction( 'set_transient' )->once()->andReturn( true );

		$this->assertTrue( $this->call_url_is_reachable( 'https://example.com/data.csv' ) );
	}
}
