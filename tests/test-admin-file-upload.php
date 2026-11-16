<?php
/**
 * Tests für ODW_Admin::save_file_attachment() — Datei aus der Mediathek als Distribution.
 *
 * Geprüft wird, welche Metadaten das Plugin beim Speichern aus der hochgeladenen Datei
 * übernimmt: Zugriffs-URL, Format und (seit v2.47.6) die Dateigröße als dcat:byteSize.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! class_exists( 'WP_Post' ) ) {
	/**
	 * Minimaler WP_Post-Ersatz — dieselben Felder wie in test-catalog.php.
	 *
	 * @package OpenDataWizard
	 */
	class WP_Post {

		/**
		 * Post ID.
		 *
		 * @var int
		 */
		public $ID = 0;

		/**
		 * Post type.
		 *
		 * @var string
		 */
		public $post_type = 'odw_dataset';

		/**
		 * Post status.
		 *
		 * @var string
		 */
		public $post_status = 'draft';

		/**
		 * Creation date (GMT).
		 *
		 * @var string
		 */
		public $post_date_gmt = '';

		/**
		 * Last modification date (GMT).
		 *
		 * @var string
		 */
		public $post_modified_gmt = '';

		/**
		 * Last modification date (local time).
		 *
		 * @var string
		 */
		public $post_modified = '';

		/**
		 * Post content.
		 *
		 * @var string
		 */
		public $post_content = '';
	}
}

/**
 * Unit tests for ODW_Admin::save_file_attachment().
 *
 * @package OpenDataWizard
 */
class Test_ODW_Admin_File_Upload extends TestCase {

	/**
	 * Post-Meta des Datensatzes während eines Tests.
	 *
	 * @var array<string, mixed>
	 */
	private $meta = array();

	/**
	 * Temporäre Datei, die als Anhang dient.
	 *
	 * @var string
	 */
	private $datei = '';

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
		require_once ODW_PLUGIN_DIR . 'includes/class-admin.php';
	}

	/**
	 * Tear down WP_Mock after each test.
	 */
	protected function tearDown(): void {
		unset( $_POST['odw_file_upload_nonce'], $_POST['_odw_file_id'] );
		if ( '' !== $this->datei && file_exists( $this->datei ) ) {
			unlink( $this->datei ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		}
		\WP_Mock::tearDown();
	}

	/**
	 * Speichert den Datensatz mit einer hochgeladenen CSV-Datei der angegebenen Größe.
	 *
	 * @param int                  $bytes Größe der Datei in Bytes.
	 * @param array<string, mixed> $meta  Post-Meta vor dem Speichern.
	 * @return array<string, mixed> Post-Meta nach dem Speichern.
	 */
	private function speichere( int $bytes, array $meta ): array {
		$this->meta  = $meta;
		$this->datei = sys_get_temp_dir() . '/odw-test-' . uniqid() . '.csv';
		file_put_contents( $this->datei, str_repeat( 'x', $bytes ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		$_POST['odw_file_upload_nonce'] = 'nonce';
		$_POST['_odw_file_id']          = '42';

		\WP_Mock::userFunction( 'wp_is_post_revision' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_unslash' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'wp_verify_nonce' )->andReturn( 1 );
		\WP_Mock::userFunction( 'current_user_can' )->andReturn( true );
		\WP_Mock::userFunction( 'absint' )->andReturnUsing(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( $this->datei );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'https://example.org/wp-content/uploads/daten.csv' );
		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			function ( $post_id, $key ) {
				return $this->meta[ $key ] ?? '';
			}
		);
		\WP_Mock::userFunction( 'update_post_meta' )->andReturnUsing(
			function ( $post_id, $key, $value ) {
				$this->meta[ $key ] = $value;
				return true;
			}
		);

		$post     = new WP_Post();
		$post->ID = 7;
		ODW_Admin::save_file_attachment( 7, $post );

		return $this->meta;
	}

	/**
	 * Ohne eigene Angaben übernimmt das Plugin URL, Format und Größe aus der Datei.
	 */
	public function test_upload_fills_access_url_format_and_byte_size(): void {
		$meta = $this->speichere( 203, array() );

		$this->assertSame( 'https://example.org/wp-content/uploads/daten.csv', $meta['_odw_access_url'] );
		$this->assertSame( 'CSV', $meta['_odw_format'] );
		$this->assertSame( '203', $meta['_odw_byte_size'] );
		$this->assertSame( 203, $meta['_odw_file_size'] );
	}

	/**
	 * Eine von Hand eingetragene Größe bleibt stehen.
	 */
	public function test_manual_byte_size_is_kept(): void {
		$meta = $this->speichere( 203, array( '_odw_byte_size' => '5000' ) );

		$this->assertSame( '5000', $meta['_odw_byte_size'] );
	}

	/**
	 * Hat das Plugin die Größe aus der vorigen Datei übernommen, bringt eine ersetzte
	 * Datei ihre eigene Größe mit.
	 */
	public function test_replaced_file_updates_derived_byte_size(): void {
		$meta = $this->speichere(
			512,
			array(
				'_odw_byte_size' => '203',
				'_odw_file_size' => 203,
			)
		);

		$this->assertSame( '512', $meta['_odw_byte_size'] );
		$this->assertSame( 512, $meta['_odw_file_size'] );
	}
}
