<?php
/**
 * Tests for ODW_CLI WP-CLI commands
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! class_exists( 'WP_Query' ) ) {
	/**
	 * Stub for WP_Query.
	 */
	class WP_Query {
		/**
		 * Preset result sets for testing (used by test-rest-delta.php).
		 *
		 * @var array<int, array<string,mixed>>
		 */
		public static array $mock_queue = array();

		/**
		 * Array of post IDs.
		 *
		 * @var array<int>
		 */
		public array $posts = array();

		/**
		 * Total number of found posts.
		 *
		 * @var int
		 */
		public int $found_posts = 0;

		/**
		 * Total number of result pages.
		 *
		 * @var int
		 */
		public int $max_num_pages = 0;

		/**
		 * Constructor.
		 *
		 * @param array<string,mixed> $args Query arguments.
		 */
		public function __construct( array $args = array() ) {
			$this->posts = array();
		}
	}
}

if ( ! class_exists( 'WP_CLI' ) ) {
	/**
	 * Stub for WP_CLI.
	 */
	class WP_CLI {
		/**
		 * Add a WP-CLI command.
		 *
		 * @param string $name     Command name.
		 * @param array  $callback Callback array.
		 */
		public static function add_command( string $name, array $callback ): void {
			// Stub implementation.
		}

		/**
		 * Print a success message.
		 *
		 * @param string $message Success message.
		 */
		public static function success( string $message ): void {
			// Stub implementation.
		}
	}
}

if ( ! class_exists( 'WP_CLI_Utils_Progress_Bar' ) ) {
	/**
	 * Stub for WP_CLI progress bar.
	 */
	class WP_CLI_Utils_Progress_Bar {
		/**
		 * Tick the progress bar.
		 */
		public function tick(): void {}

		/**
		 * Finish the progress bar.
		 */
		public function finish(): void {}
	}
}

/**
 * Test WP-CLI command registration and basic execution.
 *
 * @package OpenDataWizard
 */
class Test_ODW_CLI extends TestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		$this->load_class();
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Load ODW_CLI class.
	 */
	private function load_class(): void {
		if ( ! class_exists( 'ODW_CLI' ) ) {
			\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
			\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

			if ( ! defined( 'WP_CLI' ) ) {
				define( 'WP_CLI', true );
			}

			require_once dirname( __DIR__ ) . '/includes/class-cli.php';
		}
	}

	/**
	 * Test that the CLI class can be loaded.
	 */
	public function test_cli_class_loads(): void {
		$this->assertTrue( class_exists( 'ODW_CLI' ), 'ODW_CLI class should exist' );
	}

	/**
	 * Test that init method exists and is callable.
	 */
	public function test_init_method_exists(): void {
		$this->assertTrue(
			method_exists( 'ODW_CLI', 'init' ),
			'ODW_CLI should have init method'
		);
	}

	/**
	 * Test that quality_recalculate method exists and is callable.
	 */
	public function test_quality_recalculate_method_exists(): void {
		$this->assertTrue(
			method_exists( 'ODW_CLI', 'quality_recalculate' ),
			'ODW_CLI should have quality_recalculate method'
		);
	}

	/**
	 * Test that cache_clear method exists and is callable.
	 */
	public function test_cache_clear_method_exists(): void {
		$this->assertTrue(
			method_exists( 'ODW_CLI', 'cache_clear' ),
			'ODW_CLI should have cache_clear method'
		);
	}
}
