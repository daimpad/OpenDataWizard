<?php
/**
 * Tests für ODW_Quality
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Test_ODW_Quality extends TestCase {

    protected function setUp(): void {
        \WP_Mock::setUp();
    }

    protected function tearDown(): void {
        \WP_Mock::tearDown();
    }

    private function load_class(): void {
        if ( ! class_exists( 'ODW_Quality' ) ) {
            require_once ODW_PLUGIN_DIR . 'includes/class-quality.php';
        }
    }

    // -------------------------------------------------------------------------
    // get_level()
    // -------------------------------------------------------------------------

    public function test_get_level_returns_high_at_80(): void {
        $this->load_class();
        $this->assertSame( ODW_Quality::LEVEL_HIGH, ODW_Quality::get_level( 80 ) );
    }

    public function test_get_level_returns_high_at_100(): void {
        $this->load_class();
        $this->assertSame( ODW_Quality::LEVEL_HIGH, ODW_Quality::get_level( 100 ) );
    }

    public function test_get_level_returns_medium_at_50(): void {
        $this->load_class();
        $this->assertSame( ODW_Quality::LEVEL_MEDIUM, ODW_Quality::get_level( 50 ) );
    }

    public function test_get_level_returns_medium_at_79(): void {
        $this->load_class();
        $this->assertSame( ODW_Quality::LEVEL_MEDIUM, ODW_Quality::get_level( 79 ) );
    }

    public function test_get_level_returns_low_at_0(): void {
        $this->load_class();
        $this->assertSame( ODW_Quality::LEVEL_LOW, ODW_Quality::get_level( 0 ) );
    }

    public function test_get_level_returns_low_at_49(): void {
        $this->load_class();
        $this->assertSame( ODW_Quality::LEVEL_LOW, ODW_Quality::get_level( 49 ) );
    }

    // -------------------------------------------------------------------------
    // get_level_label()
    // -------------------------------------------------------------------------

    public function test_get_level_label_high(): void {
        $this->load_class();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $this->assertSame( 'Gut', ODW_Quality::get_level_label( ODW_Quality::LEVEL_HIGH ) );
    }

    public function test_get_level_label_medium(): void {
        $this->load_class();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $this->assertSame( 'Mittel', ODW_Quality::get_level_label( ODW_Quality::LEVEL_MEDIUM ) );
    }

    public function test_get_level_label_low(): void {
        $this->load_class();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $this->assertSame( 'Verbesserungsbedarf', ODW_Quality::get_level_label( ODW_Quality::LEVEL_LOW ) );
    }

    public function test_get_level_label_unknown_returns_fallback(): void {
        $this->load_class();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $this->assertSame( 'Unbekannt', ODW_Quality::get_level_label( 'invalid' ) );
    }

    // -------------------------------------------------------------------------
    // get_indicators()
    // -------------------------------------------------------------------------

    public function test_get_indicators_sums_to_100(): void {
        $this->load_class();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $total = array_sum( array_column( ODW_Quality::get_indicators(), 'points' ) );
        $this->assertSame( 100, $total );
    }

    public function test_get_indicators_has_title_key(): void {
        $this->load_class();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $keys = array_column( ODW_Quality::get_indicators(), 'key' );
        $this->assertContains( 'title', $keys );
    }

    // -------------------------------------------------------------------------
    // get() — liest aus Post-Meta
    // -------------------------------------------------------------------------

    public function test_get_returns_empty_result_when_no_level_stored(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_post_meta' )
            ->with( 42, '_odw_quality_level', true )
            ->andReturn( '' );

        $result = ODW_Quality::get( 42 );

        $this->assertSame( 0, $result['score'] );
        $this->assertSame( '', $result['level'] );
        $this->assertSame( [], $result['indicators'] );
        $this->assertSame( '', $result['calculated_at'] );
    }

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
            ->andReturn( [ 'title' => [ 'passed' => true ] ] );

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

    public function test_store_calls_update_post_meta_for_all_keys(): void {
        $this->load_class();

        $result = [
            'score'         => 75,
            'level'         => 'medium',
            'indicators'    => [],
            'calculated_at' => '2026-04-21 12:00:00',
        ];

        \WP_Mock::userFunction( 'update_post_meta' )
            ->with( 7, '_odw_quality_score', 75 )
            ->once();

        \WP_Mock::userFunction( 'update_post_meta' )
            ->with( 7, '_odw_quality_level', 'medium' )
            ->once();

        \WP_Mock::userFunction( 'update_post_meta' )
            ->with( 7, '_odw_quality_indicators', [] )
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
            ->andReturn( [] );

        \WP_Mock::userFunction( 'get_post_meta' )
            ->with( 5, '_odw_quality_calculated_at', true )
            ->andReturn( '2026-04-21 09:00:00' );

        $dataset = [ '@type' => 'dcat:Dataset' ];
        $result  = ODW_Quality::append_to_jsonld( $dataset, 5 );

        $this->assertArrayHasKey( 'odw:qualityScore', $result );
        $this->assertSame( 90, $result['odw:qualityScore']['odw:score'] );
        $this->assertSame( 'high', $result['odw:qualityScore']['odw:level'] );
        $this->assertSame( 100, $result['odw:qualityScore']['odw:maxScore'] );
    }

    public function test_append_to_jsonld_skips_when_no_level(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_post_meta' )
            ->with( 5, '_odw_quality_level', true )
            ->andReturn( '' );

        $dataset = [ '@type' => 'dcat:Dataset' ];
        $result  = ODW_Quality::append_to_jsonld( $dataset, 5 );

        $this->assertArrayNotHasKey( 'odw:qualityScore', $result );
    }
}
