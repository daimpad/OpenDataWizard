<?php
/**
 * Feld-Katalog- und Generator-Tests.
 *
 * Prüft config/field-catalog.php auf Vollständigkeit und stellt sicher, dass
 * das committete docs/FELD-REFERENZ.md mit dem Generator übereinstimmt.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for the field catalog and the field-reference generator.
 */
class Test_ODW_Field_Catalog extends TestCase {

	/**
	 * Loaded catalog entries.
	 *
	 * @var array<int, array<string, string>>
	 */
	private array $catalog;

	/**
	 * Loads the catalog before each test.
	 */
	protected function setUp(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-field-reference.php';
		$this->catalog = ODW_Field_Reference::load_catalog();
	}

	/**
	 * Every entry carries all required keys with non-empty text.
	 */
	public function test_entries_are_complete(): void {
		$this->assertNotEmpty( $this->catalog );

		$text_keys  = array( 'q_dcat', 'q_human', 'desc_dcat', 'desc_human' );
		$meta_keys  = array( 'key', 'meta_key', 'dcat_prop', 'tab', 'tier', 'vocab' );
		$valid_tier = array( 'mandatory', 'recommended', 'optional', 'conditional' );

		foreach ( $this->catalog as $entry ) {
			$id = $entry['key'] ?? '(unknown)';

			foreach ( array_merge( $meta_keys, $text_keys ) as $required ) {
				$this->assertArrayHasKey( $required, $entry, "Missing '{$required}' in '{$id}'" );
			}

			foreach ( $text_keys as $text_key ) {
				$this->assertNotSame( '', trim( (string) $entry[ $text_key ] ), "Empty '{$text_key}' in '{$id}'" );
			}

			$this->assertContains( $entry['tier'], $valid_tier, "Invalid tier in '{$id}'" );
			$this->assertNotSame( '', trim( (string) $entry['meta_key'] ), "Empty meta_key in '{$id}'" );
			$this->assertStringContainsString( ':', $entry['dcat_prop'], "dcat_prop should be a prefixed term in '{$id}'" );
		}
	}

	/**
	 * Internal keys and meta keys are unique across the catalog.
	 */
	public function test_keys_and_meta_keys_are_unique(): void {
		$keys      = array_column( $this->catalog, 'key' );
		$meta_keys = array_column( $this->catalog, 'meta_key' );

		$this->assertSame( array_unique( $keys ), $keys, 'Duplicate field key in catalog' );
		$this->assertSame( array_unique( $meta_keys ), $meta_keys, 'Duplicate meta_key in catalog' );
	}

	/**
	 * Every data field in the wizard form has a catalog entry and vice versa.
	 *
	 * Guards against drift: adding or removing a form field without updating
	 * config/field-catalog.php fails this test.
	 */
	public function test_catalog_covers_all_form_fields(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads local source, not a remote request.
		$source = (string) file_get_contents( ODW_PLUGIN_DIR . 'includes/class-fields.php' );

		$this->assertNotSame( '', $source, 'class-fields.php could not be read' );

		// Collect the internal keys of all scalar data fields (text/textarea/select/date).
		preg_match_all(
			"/Field::make\\(\\s*'(?:text|textarea|select|date)',\\s*'odw_([a-z_]+)'/",
			$source,
			$matches
		);
		$form_keys = array_values( array_unique( $matches[1] ) );
		sort( $form_keys );

		$catalog_keys = array_column( $this->catalog, 'key' );
		sort( $catalog_keys );

		$missing_in_catalog = array_diff( $form_keys, $catalog_keys );
		$missing_in_form    = array_diff( $catalog_keys, $form_keys );

		$this->assertSame(
			array(),
			array_values( $missing_in_catalog ),
			'Form fields without a catalog entry: ' . implode( ', ', $missing_in_catalog )
		);
		$this->assertSame(
			array(),
			array_values( $missing_in_form ),
			'Catalog entries without a form field: ' . implode( ', ', $missing_in_form )
		);
	}

	/**
	 * The committed docs/FELD-REFERENZ.md matches the generated output.
	 *
	 * Fails when the catalog changed but the doc was not regenerated
	 * (`php bin/generate-field-reference.php`).
	 */
	public function test_generated_doc_is_in_sync(): void {
		$path = ODW_PLUGIN_DIR . 'docs/FELD-REFERENZ.md';
		$this->assertFileExists( $path, 'docs/FELD-REFERENZ.md is missing — run php bin/generate-field-reference.php' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local test fixture, not a remote request.
		$committed = (string) file_get_contents( $path );
		$generated = ODW_Field_Reference::build();

		$this->assertSame(
			$generated,
			$committed,
			'docs/FELD-REFERENZ.md is out of date — run: php bin/generate-field-reference.php'
		);
	}
}
