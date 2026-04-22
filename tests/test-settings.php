<?php
/**
 * Tests für ODW_Settings
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Settings.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Settings extends TestCase {

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
	 * Loads ODW_Settings once per test run.
	 */
	private function load_class(): void {
		if ( ! class_exists( 'ODW_Settings' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-settings.php';
		}
	}

	/**
	 * When the option is empty, get() returns the default values.
	 */
	public function test_get_returns_defaults_when_option_empty(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array() );

		$settings = ODW_Settings::get();

		$this->assertIsArray( $settings );
		$this->assertSame( '', $settings['catalog_title'] );
		$this->assertSame( '', $settings['default_license'] );
		$this->assertSame( '', $settings['default_language'] );
		$this->assertSame( 300, $settings['cache_ttl'] );
		$this->assertSame( '0', $settings['delete_on_uninstall'] );
	}

	/**
	 * A stored value is returned when get() is called with a specific key.
	 */
	public function test_get_single_key_returns_stored_value(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array( 'catalog_title' => 'Mein Datenkatalog' ) );

		$this->assertSame( 'Mein Datenkatalog', ODW_Settings::get( 'catalog_title' ) );
	}

	/**
	 * An unknown key returns null from get().
	 */
	public function test_get_single_key_returns_null_for_unknown_key(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array() );

		$this->assertNull( ODW_Settings::get( 'non_existent_key' ) );
	}

	/**
	 * Stored values are merged with defaults, overriding them where set.
	 */
	public function test_get_merges_stored_with_defaults(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array( 'cache_ttl' => 600 ) );

		$settings = ODW_Settings::get();

		// Gespeicherter Wert überschreibt Default.
		$this->assertSame( 600, $settings['cache_ttl'] );
		// Andere Defaults bleiben.
		$this->assertSame( '', $settings['catalog_title'] );
	}

	/**
	 * Returns the custom title when one is stored in settings.
	 */
	public function test_filter_catalog_title_returns_custom_when_set(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array( 'catalog_title' => 'Mein Katalog' ) );

		$result = ODW_Settings::filter_catalog_title( 'Standard-Katalog' );

		$this->assertSame( 'Mein Katalog', $result );
	}

	/**
	 * Returns the default title when no custom title is stored.
	 */
	public function test_filter_catalog_title_returns_default_when_empty(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array() );

		$result = ODW_Settings::filter_catalog_title( 'Standard-Katalog' );

		$this->assertSame( 'Standard-Katalog', $result );
	}

	/**
	 * A whitespace-only catalog title is treated as empty, returning the default.
	 */
	public function test_filter_catalog_title_ignores_whitespace_only(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_option' )
			->with( ODW_Settings::OPTION_KEY, array() )
			->andReturn( array( 'catalog_title' => '   ' ) );

		$result = ODW_Settings::filter_catalog_title( 'Fallback' );

		$this->assertSame( 'Fallback', $result );
	}
}
