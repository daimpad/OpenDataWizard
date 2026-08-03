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
		$this->assertSame( 'CC BY 4.0 — Namensnennung', $label );
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
	 * Curated spatial options map region names to GeoNames URIs.
	 */
	public function test_get_spatial_options_links_geonames(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$options = ODW_Fields::get_spatial_options();
		$this->assertArrayHasKey( 'Deutschland', $options );
		$this->assertStringStartsWith( 'https://sws.geonames.org/', $options['Deutschland'] );
		$this->assertArrayHasKey( 'Bayern', $options );
	}

	/**
	 * Format metadata exposes the MQA machine-readable / non-proprietary flags.
	 */
	public function test_get_format_meta_exposes_mqa_flags(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$csv = ODW_Fields::get_format_meta( 'CSV' );
		$this->assertTrue( $csv['machine_readable'] );
		$this->assertTrue( $csv['non_proprietary'] );
		$this->assertNotSame( '', $csv['eu_uri'] );

		// XLSX is machine-readable but proprietary.
		$xlsx = ODW_Fields::get_format_meta( 'XLSX' );
		$this->assertTrue( $xlsx['machine_readable'] );
		$this->assertFalse( $xlsx['non_proprietary'] );

		// "Sonstiges" is neither and has no EU vocabulary URI.
		$other = ODW_Fields::get_format_meta( 'Sonstiges' );
		$this->assertFalse( $other['machine_readable'] );
		$this->assertFalse( $other['non_proprietary'] );
		$this->assertSame( '', $other['eu_uri'] );

		// Unknown format returns an empty array.
		$this->assertSame( array(), ODW_Fields::get_format_meta( 'NOPE' ) );
	}

	/**
	 * License descriptions provide plain-language text for the standard licenses.
	 */
	public function test_get_license_descriptions_has_known_licenses(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$descriptions = ODW_Fields::get_license_descriptions();
		$this->assertArrayHasKey( 'https://creativecommons.org/licenses/by/4.0/', $descriptions );
		$this->assertNotSame( '', $descriptions['https://creativecommons.org/licenses/by/4.0/'] );
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

	/**
	 * The live-preview field config exposes well-formed entries for the wizard.
	 */
	public function test_get_live_preview_fields_returns_wellformed_entries(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$fields = ODW_Fields::get_live_preview_fields();

		$this->assertIsArray( $fields );
		$this->assertNotEmpty( $fields );

		foreach ( $fields as $field ) {
			$this->assertArrayHasKey( 'key', $field );
			$this->assertArrayHasKey( 'label', $field );
			$this->assertArrayHasKey( 'required', $field );
			$this->assertArrayHasKey( 'card', $field );
			$this->assertIsString( $field['key'] );
			$this->assertIsBool( $field['required'] );
			$this->assertIsBool( $field['card'] );
		}
	}

	/**
	 * The live-preview required entries cover the core publishing requirements.
	 */
	public function test_get_live_preview_fields_marks_core_required(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$fields   = ODW_Fields::get_live_preview_fields();
		$required = array();
		foreach ( $fields as $field ) {
			if ( $field['required'] ) {
				$required[] = $field['key'];
			}
		}

		$this->assertContains( 'title', $required );
		$this->assertContains( 'odw_publisher', $required );
		$this->assertContains( 'odw_description', $required );
		$this->assertContains( 'odw_access_url', $required );
		$this->assertContains( 'odw_license', $required );
	}

	/**
	 * Maps a theme code, label or URI to the EU URI (resolve_theme_uri()).
	 */
	public function test_resolve_theme_uri_maps_code_label_and_passthrough(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$expected = 'http://publications.europa.eu/resource/authority/data-theme/SOCI';

		$this->assertSame( $expected, ODW_Fields::resolve_theme_uri( 'SOCI' ) );
		$this->assertSame( $expected, ODW_Fields::resolve_theme_uri( 'Bevölkerung & Gesellschaft' ) );
		$this->assertSame( $expected, ODW_Fields::resolve_theme_uri( $expected ) );
		// Unknown value passes through unchanged.
		$this->assertSame( 'Foobar', ODW_Fields::resolve_theme_uri( 'Foobar' ) );
	}

	/**
	 * Turns a stored EU theme URI back into its readable label (resolve_label()).
	 */
	public function test_resolve_label_theme_uri_to_label(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$this->assertSame(
			'Umwelt',
			ODW_Fields::resolve_label( 'theme', 'http://publications.europa.eu/resource/authority/data-theme/ENVI' )
		);
		// Freetext / unknown value is returned unchanged.
		$this->assertSame( 'Musterstadt', ODW_Fields::resolve_label( 'theme', 'Musterstadt' ) );
	}

	/**
	 * Normalises format keys case- and separator-insensitively (resolve_format_key()).
	 */
	public function test_resolve_format_key_normalises_case_and_separators(): void {
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';

		$this->assertSame( 'GeoJSON', ODW_Fields::resolve_format_key( 'GEOJSON' ) );
		$this->assertSame( 'JSON-LD', ODW_Fields::resolve_format_key( 'jsonld' ) );
		$this->assertSame( 'CSV', ODW_Fields::resolve_format_key( 'csv' ) );
		$this->assertSame( '', ODW_Fields::resolve_format_key( 'nope' ) );
	}

	/**
	 * Every set_attribute() call must use an attribute the target Carbon Fields
	 * field type accepts. A disallowed one does not throw in production (WP_DEBUG
	 * off) — it is silently dropped and surfaces as the "Your site seems to be
	 * slightly misconfigured" admin notice. This test parses the field
	 * definitions and compares each attribute against the allow-lists Carbon
	 * Fields declares for the respective field class.
	 */
	public function test_set_attribute_calls_use_allowed_attributes(): void {
		// Werte gespiegelt aus den allowed_attributes der jeweiligen
		// Carbon-Fields-Feldklassen (Text_Field, Textarea_Field, Date_Field).
		$allowed = array(
			'text'      => array( 'list', 'max', 'maxLength', 'min', 'pattern', 'placeholder', 'readOnly', 'step', 'type', 'is', 'inputmode', 'autocomplete' ),
			'textarea'  => array( 'maxLength', 'minLength', 'placeholder', 'readOnly', 'is', 'autocomplete' ),
			'date'      => array( 'placeholder', 'autocomplete' ),
			'date_time' => array( 'placeholder', 'autocomplete' ),
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads local source, not a remote request.
		$source = (string) file_get_contents( ODW_PLUGIN_DIR . 'includes/class-fields.php' );

		$matches = array();
		preg_match_all(
			"/Field::make\(\s*'([a-z_]+)'|->set_attribute\(\s*'([^']+)'/",
			$source,
			$matches,
			PREG_SET_ORDER
		);

		$type       = '';
		$violations = array();
		foreach ( $matches as $match ) {
			if ( '' !== ( $match[1] ?? '' ) ) {
				$type = $match[1];
				continue;
			}

			$attribute = $match[2] ?? '';
			// data-* is always allowed, regardless of field type.
			if ( 0 === strpos( strtolower( $attribute ), 'data-' ) ) {
				continue;
			}
			if ( ! isset( $allowed[ $type ] ) ) {
				$violations[] = sprintf( 'unbekannter Feldtyp "%s" mit Attribut "%s"', $type, $attribute );
				continue;
			}
			if ( ! in_array( $attribute, $allowed[ $type ], true ) ) {
				$violations[] = sprintf( '"%s" ist bei Feldtyp "%s" nicht erlaubt', $attribute, $type );
			}
		}

		$this->assertSame( array(), $violations, implode( '; ', $violations ) );
	}
}
