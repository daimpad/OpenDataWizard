<?php
/**
 * Tests für ODW_Shortcode
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Test_ODW_Shortcode extends TestCase {

    protected function setUp(): void {
        \WP_Mock::setUp();
    }

    protected function tearDown(): void {
        \WP_Mock::tearDown();
    }

    private function load_class(): void {
        if ( ! class_exists( 'ODW_Fields' ) ) {
            \WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
            \WP_Mock::userFunction( '__' )->andReturnArg( 0 );
            require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
        }

        if ( ! class_exists( 'ODW_Quality' ) ) {
            require_once ODW_PLUGIN_DIR . 'includes/class-quality.php';
        }

        if ( ! class_exists( 'ODW_Shortcode' ) ) {
            require_once ODW_PLUGIN_DIR . 'includes/class-shortcode.php';
        }
    }

    // -------------------------------------------------------------------------
    // format_bytes() — via Reflection (private method)
    // -------------------------------------------------------------------------

    private function call_format_bytes( int $bytes ): string {
        $ref    = new \ReflectionClass( 'ODW_Shortcode' );
        $method = $ref->getMethod( 'format_bytes' );
        $method->setAccessible( true );
        return (string) $method->invoke( null, $bytes );
    }

    public function test_format_bytes_below_1kb(): void {
        $this->load_class();
        $this->assertSame( '512 B', $this->call_format_bytes( 512 ) );
    }

    public function test_format_bytes_zero(): void {
        $this->load_class();
        $this->assertSame( '0 B', $this->call_format_bytes( 0 ) );
    }

    public function test_format_bytes_exactly_1kb(): void {
        $this->load_class();
        $this->assertSame( '1 KB', $this->call_format_bytes( 1024 ) );
    }

    public function test_format_bytes_kb_range(): void {
        $this->load_class();
        // 2048 bytes = 2.0 KB
        $this->assertSame( '2 KB', $this->call_format_bytes( 2048 ) );
    }

    public function test_format_bytes_mb_range(): void {
        $this->load_class();
        // 1.5 MB = 1573888 bytes
        $this->assertSame( '1.5 MB', $this->call_format_bytes( 1_048_576 + 524_288 ) );
    }

    public function test_format_bytes_gb_range(): void {
        $this->load_class();
        // 2 GB
        $this->assertSame( '2 GB', $this->call_format_bytes( 2 * 1_073_741_824 ) );
    }

    // -------------------------------------------------------------------------
    // render() — edge cases without valid post
    // -------------------------------------------------------------------------

    public function test_render_returns_empty_when_id_is_zero(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'shortcode_atts' )
            ->with( [ 'id' => '0' ], [], 'odw_dataset' )
            ->andReturn( [ 'id' => '0' ] );

        \WP_Mock::userFunction( 'absint' )
            ->with( '0' )
            ->andReturn( 0 );

        $result = ODW_Shortcode::render( [] );
        $this->assertSame( '', $result );
    }

    public function test_render_returns_empty_when_post_not_found(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'shortcode_atts' )
            ->with( [ 'id' => '0' ], [ 'id' => '99' ], 'odw_dataset' )
            ->andReturn( [ 'id' => '99' ] );

        \WP_Mock::userFunction( 'absint' )
            ->with( '99' )
            ->andReturn( 99 );

        \WP_Mock::userFunction( 'get_post' )
            ->with( 99 )
            ->andReturn( null );

        $result = ODW_Shortcode::render( [ 'id' => '99' ] );
        $this->assertSame( '', $result );
    }

    public function test_render_returns_empty_when_wrong_post_type(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'shortcode_atts' )
            ->with( [ 'id' => '0' ], [ 'id' => '5' ], 'odw_dataset' )
            ->andReturn( [ 'id' => '5' ] );

        \WP_Mock::userFunction( 'absint' )
            ->with( '5' )
            ->andReturn( 5 );

        $post            = new \stdClass();
        $post->ID        = 5;
        $post->post_type = 'post';
        $post->post_status = 'publish';

        \WP_Mock::userFunction( 'get_post' )
            ->with( 5 )
            ->andReturn( $post );

        $result = ODW_Shortcode::render( [ 'id' => '5' ] );
        $this->assertSame( '', $result );
    }

    public function test_render_returns_empty_when_post_not_published(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'shortcode_atts' )
            ->with( [ 'id' => '0' ], [ 'id' => '6' ], 'odw_dataset' )
            ->andReturn( [ 'id' => '6' ] );

        \WP_Mock::userFunction( 'absint' )
            ->with( '6' )
            ->andReturn( 6 );

        $post              = new \stdClass();
        $post->ID          = 6;
        $post->post_type   = 'odw_dataset';
        $post->post_status = 'draft';

        \WP_Mock::userFunction( 'get_post' )
            ->with( 6 )
            ->andReturn( $post );

        $result = ODW_Shortcode::render( [ 'id' => '6' ] );
        $this->assertSame( '', $result );
    }
}
