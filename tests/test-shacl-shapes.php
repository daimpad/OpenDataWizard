<?php
/**
 * Integritätstest für die gebündelten SHACL-Shapes (config/shacl/).
 *
 * Stellt sicher, dass die offiziellen DCAT-AP-Shapes vorhanden und nicht
 * versehentlich geleert/beschädigt wurden.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for the bundled SHACL shape files.
 */
class Test_ODW_Shacl_Shapes extends TestCase {

	/**
	 * Both official shape files exist and contain SHACL node shapes.
	 *
	 * @return void
	 */
	public function test_bundled_shapes_are_present_and_look_like_shacl(): void {
		$files = array(
			ODW_PLUGIN_DIR . 'config/shacl/dcat-ap-SHACL.ttl',
			ODW_PLUGIN_DIR . 'config/shacl/dcat-ap-SHACL-DE.ttl',
		);

		foreach ( $files as $file ) {
			$this->assertFileExists( $file, "Missing SHACL shape file: {$file}" );

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local bundled asset, not a remote request.
			$content = (string) file_get_contents( $file );

			$this->assertNotSame( '', trim( $content ), "Empty SHACL shape file: {$file}" );
			$this->assertStringContainsString( '@prefix', $content, "No Turtle prefixes in: {$file}" );
			// Both official files reference the SHACL namespace (the EU file via
			// NodeShape declarations, the DCAT-AP.de file via shape overrides).
			$this->assertMatchesRegularExpression( '/\b(sh|shacl):/', $content, "No SHACL namespace in: {$file}" );
		}
	}
}
