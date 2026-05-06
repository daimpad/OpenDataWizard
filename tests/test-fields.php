<?php
/**
 * Tests für ODW_Fields
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Fields static helper methods.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Fields extends TestCase {

	/**
	 * Returns scalar required meta keys (description + publisher).
	 * License is now per-distribution and not included as a scalar field.
	 */
	public function test_get_required_fields_returns_expected_keys(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$fields = ODW_Fields::get_required_fields();

		$this->assertIsArray( $fields );

		$meta_keys = array_column( $fields, 'meta_key' );
		$this->assertContains( '_odw_description', $meta_keys );
		$this->assertContains( '_odw_publisher', $meta_keys );
		// License is now per-distribution; no longer a scalar dataset-level field.
		$this->assertNotContains( '_odw_license', $meta_keys );
	}

	/**
	 * The license options array includes an empty-string key as the placeholder option.
	 */
	public function test_get_license_options_has_empty_default(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$options = ODW_Fields::get_license_options();
		$this->assertArrayHasKey( '', $options );
	}

	/**
	 * Returns the human-readable label for a known license URI.
	 */
	public function test_get_license_label_returns_label_for_known_uri(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$label = ODW_Fields::get_license_label( 'https://creativecommons.org/licenses/by/4.0/' );
		$this->assertSame( 'CC-BY 4.0', $label );
	}

	/**
	 * Returns the URI itself when the URI is not in the license map.
	 */
	public function test_get_license_label_returns_uri_for_unknown(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$unknown = 'https://example.com/custom-license';
		$this->assertSame( $unknown, ODW_Fields::get_license_label( $unknown ) );
	}

	/**
	 * Maps "CSV" to the correct MIME type.
	 */
	public function test_get_format_mime_maps_csv(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$this->assertSame( 'text/csv', ODW_Fields::get_format_mime( 'CSV' ) );
	}

	/**
	 * Returns the input string unchanged for unknown formats.
	 */
	public function test_get_format_mime_returns_format_for_unknown(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$this->assertSame( 'CUSTOM', ODW_Fields::get_format_mime( 'CUSTOM' ) );
	}

	/**
	 * All non-empty theme option keys use EU Publications Office data-theme URIs.
	 */
	public function test_get_theme_options_uses_eu_vocabulary_uris(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$base    = 'http://publications.europa.eu/resource/authority/data-theme/';
		$options = ODW_Fields::get_theme_options();

		foreach ( array_keys( $options ) as $key ) {
			if ( '' === $key ) {
				continue;
			}
			$this->assertStringStartsWith( $base, $key );
		}
	}

	/**
	 * Maps "CSV" to the EU Publications Office file-type URI.
	 */
	public function test_get_format_eu_uri_maps_csv(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$this->assertSame(
			'http://publications.europa.eu/resource/authority/file-type/CSV',
			ODW_Fields::get_format_eu_uri( 'CSV' )
		);
	}

	/**
	 * Returns the format string unchanged when the format is not in the EU vocabulary.
	 */
	public function test_get_format_eu_uri_returns_format_for_unknown(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$this->assertSame( 'CUSTOM', ODW_Fields::get_format_eu_uri( 'CUSTOM' ) );
	}

	/**
	 * All non-empty language option keys use EU Publications Office language URIs.
	 */
	public function test_get_language_options_uses_eu_uris(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$base    = 'http://publications.europa.eu/resource/authority/language/';
		$options = ODW_Fields::get_language_options();

		foreach ( array_keys( $options ) as $key ) {
			if ( '' === $key ) {
				continue;
			}
			$this->assertStringStartsWith( $base, $key );
		}
	}

	/**
	 * The political geocoding level options include the federal-level URI.
	 */
	public function test_get_political_geocoding_level_options_has_federal(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$options = ODW_Fields::get_political_geocoding_level_options();
		$this->assertArrayHasKey( 'http://dcat-ap.de/def/politicalGeocoding/Level/federal', $options );
	}

	/**
	 * All non-empty political geocoding level keys use the dcatde geocoding URI base.
	 */
	public function test_get_political_geocoding_level_options_all_use_correct_base(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$base    = 'http://dcat-ap.de/def/politicalGeocoding/Level/';
		$options = ODW_Fields::get_political_geocoding_level_options();

		foreach ( array_keys( $options ) as $key ) {
			if ( '' === $key ) {
				continue;
			}
			$this->assertStringStartsWith( $base, $key );
		}
	}
}
