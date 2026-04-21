<?php
/**
 * Tests für ODW_Fields
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Test_ODW_Fields extends TestCase {

    public function test_get_required_fields_returns_expected_keys(): void {
        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

        $fields = ODW_Fields::get_required_fields();

        $this->assertIsArray( $fields );
        $this->assertCount( 3, $fields );

        $meta_keys = array_column( $fields, 'meta_key' );
        $this->assertContains( '_odw_description', $meta_keys );
        $this->assertContains( '_odw_publisher', $meta_keys );
        $this->assertContains( '_odw_license', $meta_keys );
    }

    public function test_get_license_options_has_empty_default(): void {
        \WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

        $options = ODW_Fields::get_license_options();
        $this->assertArrayHasKey( '', $options );
    }

    public function test_get_license_label_returns_label_for_known_uri(): void {
        \WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

        $label = ODW_Fields::get_license_label( 'https://creativecommons.org/licenses/by/4.0/' );
        $this->assertSame( 'CC-BY 4.0', $label );
    }

    public function test_get_license_label_returns_uri_for_unknown(): void {
        \WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

        $unknown = 'https://example.com/custom-license';
        $this->assertSame( $unknown, ODW_Fields::get_license_label( $unknown ) );
    }

    public function test_get_format_mime_maps_csv(): void {
        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

        $this->assertSame( 'text/csv', ODW_Fields::get_format_mime( 'CSV' ) );
    }

    public function test_get_format_mime_returns_format_for_unknown(): void {
        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

        $this->assertSame( 'CUSTOM', ODW_Fields::get_format_mime( 'CUSTOM' ) );
    }
}
