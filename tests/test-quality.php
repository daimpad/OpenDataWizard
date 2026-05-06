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
	// get() — liest aus Post-Meta
	// -------------------------------------------------------------------------

	/**
	 * Returns a zeroed result when no quality level is stored in post meta.
	 */
	public function test_get_returns_empty_result_when_no_level_stored(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_quality_level', true )
			->andReturn( '' );

		$result = ODW_Quality::get( 42 );

		$this->assertSame( 0, $result['score'] );
		$this->assertSame( '', $result['level'] );
		$this->assertSame( array(), $result['indicators'] );
		$this->assertSame( '', $result['calculated_at'] );
	}

	/**
	 * Returns score, level, and calculated_at from stored post meta.
	 */
	public function test_get_returns_stored_values(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_quality_level', true )
			->andReturn( 'high' );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_quality_score', true )
			->andReturn( '85' );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_quality_indicators', true )
			->andReturn( array( 'title' => array( 'passed' => true ) ) );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 42, '_odw_quality_calculated_at', true )
			->andReturn( '2026-04-21 10:00:00' );

		$result = ODW_Quality::get( 42 );

		$this->assertSame( 85, $result['score'] );
		$this->assertSame( 'high', $result['level'] );
		$this->assertSame( '2026-04-21 10:00:00', $result['calculated_at'] );
	}

	// -------------------------------------------------------------------------
	// store()
	// -------------------------------------------------------------------------

	/**
	 * Calls update_post_meta for all four quality meta keys.
	 */
	public function test_store_calls_update_post_meta_for_all_keys(): void {
		$this->load_class();

		$result = array(
			'score'         => 75,
			'level'         => 'medium',
			'indicators'    => array(),
			'calculated_at' => '2026-04-21 12:00:00',
		);

		\WP_Mock::userFunction( 'update_post_meta' )
			->with( 7, '_odw_quality_score', 75 )
			->once();

		\WP_Mock::userFunction( 'update_post_meta' )
			->with( 7, '_odw_quality_level', 'medium' )
			->once();

		\WP_Mock::userFunction( 'update_post_meta' )
			->with( 7, '_odw_quality_indicators', array() )
			->once();

		\WP_Mock::userFunction( 'update_post_meta' )
			->with( 7, '_odw_quality_calculated_at', '2026-04-21 12:00:00' )
			->once();

		ODW_Quality::store( 7, $result );

		// WP_Mock ->once() expectations verified in tearDown; count them explicitly.
		$this->addToAssertionCount( 4 );
	}

	// -------------------------------------------------------------------------
	// append_to_jsonld()
	// -------------------------------------------------------------------------

	/**
	 * Adds odw:qualityScore to the dataset array when a quality level is stored.
	 */
	public function test_append_to_jsonld_adds_quality_data(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_quality_level', true )
			->andReturn( 'high' );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_quality_score', true )
			->andReturn( '90' );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_quality_indicators', true )
			->andReturn( array() );

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_quality_calculated_at', true )
			->andReturn( '2026-04-21 09:00:00' );

		$dataset = array( '@type' => 'dcat:Dataset' );
		$result  = ODW_Quality::append_to_jsonld( $dataset, 5 );

		$this->assertArrayHasKey( 'odw:qualityScore', $result );
		$this->assertSame( 90, $result['odw:qualityScore']['odw:score'] );
		$this->assertSame( 'high', $result['odw:qualityScore']['odw:level'] );
		$this->assertSame( 100, $result['odw:qualityScore']['odw:maxScore'] );
	}

	/**
	 * Leaves the dataset array unchanged when no quality level is stored.
	 */
	public function test_append_to_jsonld_skips_when_no_level(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_post_meta' )
			->with( 5, '_odw_quality_level', true )
			->andReturn( '' );

		$dataset = array( '@type' => 'dcat:Dataset' );
		$result  = ODW_Quality::append_to_jsonld( $dataset, 5 );

		$this->assertArrayNotHasKey( 'odw:qualityScore', $result );
	}
}
