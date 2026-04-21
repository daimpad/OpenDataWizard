<?php
/**
 * Tests für ODW_Settings
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Test_ODW_Settings extends TestCase {

    protected function setUp(): void {
        \WP_Mock::setUp();
    }

    protected function tearDown(): void {
        \WP_Mock::tearDown();
    }

    private function load_class(): void {
        if ( ! class_exists( 'ODW_Settings' ) ) {
            require_once ODW_PLUGIN_DIR . 'includes/class-settings.php';
        }
    }

    public function test_get_returns_defaults_when_option_empty(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [] );

        $settings = ODW_Settings::get();

        $this->assertIsArray( $settings );
        $this->assertSame( '', $settings['catalog_title'] );
        $this->assertSame( '', $settings['default_license'] );
        $this->assertSame( '', $settings['default_language'] );
        $this->assertSame( 300, $settings['cache_ttl'] );
        $this->assertSame( '0', $settings['delete_on_uninstall'] );
    }

    public function test_get_single_key_returns_stored_value(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [ 'catalog_title' => 'Mein Datenkatalog' ] );

        $this->assertSame( 'Mein Datenkatalog', ODW_Settings::get( 'catalog_title' ) );
    }

    public function test_get_single_key_returns_null_for_unknown_key(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [] );

        $this->assertNull( ODW_Settings::get( 'non_existent_key' ) );
    }

    public function test_get_merges_stored_with_defaults(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [ 'cache_ttl' => 600 ] );

        $settings = ODW_Settings::get();

        // Gespeicherter Wert überschreibt Default.
        $this->assertSame( 600, $settings['cache_ttl'] );
        // Andere Defaults bleiben.
        $this->assertSame( '', $settings['catalog_title'] );
    }

    public function test_filter_catalog_title_returns_custom_when_set(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [ 'catalog_title' => 'Mein Katalog' ] );

        $result = ODW_Settings::filter_catalog_title( 'Standard-Katalog' );

        $this->assertSame( 'Mein Katalog', $result );
    }

    public function test_filter_catalog_title_returns_default_when_empty(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [] );

        $result = ODW_Settings::filter_catalog_title( 'Standard-Katalog' );

        $this->assertSame( 'Standard-Katalog', $result );
    }

    public function test_filter_catalog_title_ignores_whitespace_only(): void {
        $this->load_class();

        \WP_Mock::userFunction( 'get_option' )
            ->with( ODW_Settings::OPTION_KEY, [] )
            ->andReturn( [ 'catalog_title' => '   ' ] );

        $result = ODW_Settings::filter_catalog_title( 'Fallback' );

        $this->assertSame( 'Fallback', $result );
    }
}
