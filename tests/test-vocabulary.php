<?php
/**
 * Tests für ODW_Fields::load_vocabulary() und odw_resolve_vocab_uri().
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the bundled-vocabulary loader and the label→URI resolver
 * powering the generic autosuggest (Phase B).
 */
class Test_ODW_Vocabulary extends TestCase {

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}
		if ( ! class_exists( 'ODW_Fields' ) ) {
			\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
			require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
		}
	}

	/**
	 * Tear down WP_Mock after each test.
	 */
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * The bundled contributors vocabulary loads as { value, label } entries.
	 */
	public function test_load_vocabulary_returns_contributor_entries(): void {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$vocab = ODW_Fields::load_vocabulary( 'contributors' );

		$this->assertNotEmpty( $vocab );
		$this->assertArrayHasKey( 'value', $vocab[0] );
		$this->assertArrayHasKey( 'label', $vocab[0] );
		$this->assertStringStartsWith( 'http://dcat-ap.de/def/contributors/', $vocab[0]['value'] );
	}

	/**
	 * A malformed id is sanitised so no path traversal can occur; the resulting
	 * non-existent file yields an empty array (no arbitrary file is read).
	 */
	public function test_load_vocabulary_blocks_path_traversal(): void {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$this->assertSame( array(), ODW_Fields::load_vocabulary( '../../etc/passwd' ) );
	}

	/**
	 * An id that sanitises to an empty string returns an empty array.
	 */
	public function test_load_vocabulary_empty_id_returns_empty(): void {
		$this->assertSame( array(), ODW_Fields::load_vocabulary( '///' ) );
	}

	/**
	 * A transient cache hit short-circuits the file read.
	 */
	public function test_load_vocabulary_uses_transient_cache(): void {
		$cached = array(
			array(
				'value' => 'http://example.org/x',
				'label' => 'X',
			),
		);
		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached );

		$this->assertSame( $cached, ODW_Fields::load_vocabulary( 'contributors' ) );
	}

	/**
	 * A value that is already an http(s) URI is returned unchanged, without any
	 * vocabulary lookup.
	 */
	public function test_resolve_vocab_uri_passes_through_uri(): void {
		$uri = 'http://dcat-ap.de/def/contributors/openDataBayern';
		$this->assertSame( $uri, odw_resolve_vocab_uri( $uri, 'contributors' ) );
	}

	/**
	 * A known label resolves to its canonical URI (case-insensitive).
	 */
	public function test_resolve_vocab_uri_maps_label_to_uri(): void {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$this->assertSame(
			'http://dcat-ap.de/def/contributors/auswaertigesAmt',
			odw_resolve_vocab_uri( 'Auswärtiges Amt', 'contributors' )
		);
	}

	/**
	 * An unknown label resolves to an empty string (never emitted as a bogus @id).
	 */
	public function test_resolve_vocab_uri_unknown_label_returns_empty(): void {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$this->assertSame( '', odw_resolve_vocab_uri( 'Nicht existierende Stelle', 'contributors' ) );
	}

	/**
	 * An empty value resolves to an empty string without any lookup.
	 */
	public function test_resolve_vocab_uri_empty_value_returns_empty(): void {
		$this->assertSame( '', odw_resolve_vocab_uri( '   ', 'contributors' ) );
	}
}
