<?php
/**
 * Tests für ODW_Shortcode
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Shortcode.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Shortcode extends TestCase {

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
	 * Loads ODW_Shortcode and its dependencies once per test run.
	 */
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

	/**
	 * Invokes the private format_bytes() method via ReflectionClass.
	 *
	 * @param int $bytes Byte count.
	 * @return string Formatted size string.
	 */
	private function call_format_bytes( int $bytes ): string {
		$ref    = new \ReflectionClass( 'ODW_Shortcode' );
		$method = $ref->getMethod( 'format_bytes' );
		$method->setAccessible( true );
		return (string) $method->invoke( null, $bytes );
	}

	/**
	 * Values below 1 KB are shown as bytes.
	 */
	public function test_format_bytes_below_1kb(): void {
		$this->load_class();
		$this->assertSame( '512 B', $this->call_format_bytes( 512 ) );
	}

	/**
	 * Zero bytes produces "0 B".
	 */
	public function test_format_bytes_zero(): void {
		$this->load_class();
		$this->assertSame( '0 B', $this->call_format_bytes( 0 ) );
	}

	/**
	 * Exactly 1024 bytes formats to "1 KB".
	 */
	public function test_format_bytes_exactly_1kb(): void {
		$this->load_class();
		$this->assertSame( '1 KB', $this->call_format_bytes( 1024 ) );
	}

	/**
	 * 2048 bytes formats to "2 KB".
	 */
	public function test_format_bytes_kb_range(): void {
		$this->load_class();
		$this->assertSame( '2 KB', $this->call_format_bytes( 2048 ) );
	}

	/**
	 * 1.5 MB formats correctly.
	 */
	public function test_format_bytes_mb_range(): void {
		$this->load_class();
		$this->assertSame( '1.5 MB', $this->call_format_bytes( 1_048_576 + 524_288 ) );
	}

	/**
	 * 2 GB formats correctly.
	 */
	public function test_format_bytes_gb_range(): void {
		$this->load_class();
		$this->assertSame( '2 GB', $this->call_format_bytes( 2 * 1_073_741_824 ) );
	}

	// -------------------------------------------------------------------------
	// render() — edge cases without valid post
	// -------------------------------------------------------------------------

	/**
	 * A zero ID causes render() to return empty string immediately.
	 */
	public function test_render_returns_empty_when_id_is_zero(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'shortcode_atts' )
			->with( array( 'id' => '0' ), array(), 'odw_dataset' )
			->andReturn( array( 'id' => '0' ) );

		\WP_Mock::userFunction( 'absint' )
			->with( '0' )
			->andReturn( 0 );

		$result = ODW_Shortcode::render( array() );
		$this->assertSame( '', $result );
	}

	/**
	 * A non-existent post causes render() to return empty string.
	 */
	public function test_render_returns_empty_when_post_not_found(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'shortcode_atts' )
			->with( array( 'id' => '0' ), array( 'id' => '99' ), 'odw_dataset' )
			->andReturn( array( 'id' => '99' ) );

		\WP_Mock::userFunction( 'absint' )
			->with( '99' )
			->andReturn( 99 );

		\WP_Mock::userFunction( 'get_post' )
			->with( 99 )
			->andReturn( null );

		$result = ODW_Shortcode::render( array( 'id' => '99' ) );
		$this->assertSame( '', $result );
	}

	/**
	 * A post with a different post_type causes render() to return empty string.
	 */
	public function test_render_returns_empty_when_wrong_post_type(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'shortcode_atts' )
			->with( array( 'id' => '0' ), array( 'id' => '5' ), 'odw_dataset' )
			->andReturn( array( 'id' => '5' ) );

		\WP_Mock::userFunction( 'absint' )
			->with( '5' )
			->andReturn( 5 );

		$post              = new \stdClass();
		$post->ID          = 5;
		$post->post_type   = 'post';
		$post->post_status = 'publish';

		\WP_Mock::userFunction( 'get_post' )
			->with( 5 )
			->andReturn( $post );

		$result = ODW_Shortcode::render( array( 'id' => '5' ) );
		$this->assertSame( '', $result );
	}

	/**
	 * A draft post causes render() to return empty string.
	 */
	public function test_render_returns_empty_when_post_not_published(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'shortcode_atts' )
			->with( array( 'id' => '0' ), array( 'id' => '6' ), 'odw_dataset' )
			->andReturn( array( 'id' => '6' ) );

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

		$result = ODW_Shortcode::render( array( 'id' => '6' ) );
		$this->assertSame( '', $result );
	}
}
