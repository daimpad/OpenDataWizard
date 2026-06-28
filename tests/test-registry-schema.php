<?php
/**
 * Tests für das deklarative Schema der Feld-Registry (config/dcat-ap-fields.php).
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Validates the structure and invariants of the DCAT-AP field registry so the
 * declarative metadata (profile/tier/range/cardinality/entity/vocab) stays
 * consistent and backward-compatible with its consumers.
 */
class Test_ODW_Registry_Schema extends TestCase {

	/**
	 * Loaded registry definitions.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $defs;

	/**
	 * Load the registry once per test with __() stubbed.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		$this->defs = require ODW_PLUGIN_DIR . 'config/dcat-ap-fields.php';
	}

	/**
	 * Tear down WP_Mock.
	 */
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * The registry is a non-empty list of array entries.
	 */
	public function test_registry_is_non_empty_list(): void {
		$this->assertIsArray( $this->defs );
		$this->assertNotEmpty( $this->defs );
	}

	/**
	 * Every entry keeps the base keys consumed by ODW_Quality and ODW_Validation.
	 */
	public function test_every_entry_has_base_keys(): void {
		foreach ( $this->defs as $entry ) {
			foreach ( array( 'key', 'meta_key', 'dcat_prop', 'label', 'points', 'required' ) as $base ) {
				$this->assertArrayHasKey( $base, $entry, "Missing base key '$base'" );
			}
			$this->assertIsInt( $entry['points'] );
			$this->assertIsBool( $entry['required'] );
		}
	}

	/**
	 * Every entry carries the declarative schema keys with valid enum values.
	 */
	public function test_every_entry_has_valid_schema_metadata(): void {
		$profiles      = array( 'ap', 'ap.de', 'hvd' );
		$tiers         = array( 'mandatory', 'recommended', 'optional' );
		$ranges        = array( 'literal', 'literal-lang', 'uri', 'node' );
		$cardinalities = array( '0..1', '0..n', '1..1', '1..n' );
		$entities      = array( 'dataset', 'distribution', 'catalog' );

		foreach ( $this->defs as $entry ) {
			$this->assertContains( $entry['profile'], $profiles, "Bad profile for {$entry['key']}" );
			$this->assertContains( $entry['tier'], $tiers, "Bad tier for {$entry['key']}" );
			$this->assertContains( $entry['range'], $ranges, "Bad range for {$entry['key']}" );
			$this->assertContains( $entry['cardinality'], $cardinalities, "Bad cardinality for {$entry['key']}" );
			$this->assertContains( $entry['entity'], $entities, "Bad entity for {$entry['key']}" );
			$this->assertArrayHasKey( 'vocab', $entry );
			$this->assertIsString( $entry['vocab'] );
		}
	}

	/**
	 * The tier and required flag stay consistent: mandatory iff required.
	 */
	public function test_tier_matches_required_flag(): void {
		foreach ( $this->defs as $entry ) {
			if ( 'mandatory' === $entry['tier'] ) {
				$this->assertTrue( $entry['required'], "Mandatory tier must be required: {$entry['key']}" );
			} else {
				$this->assertFalse( $entry['required'], "Non-mandatory tier must not be required: {$entry['key']}" );
			}
		}
	}

	/**
	 * The total points still sum to 100 (scoring scale is unchanged).
	 */
	public function test_points_sum_to_100(): void {
		$sum = array_sum( array_column( $this->defs, 'points' ) );
		$this->assertSame( 100, $sum );
	}

	/**
	 * Keys are unique across the registry.
	 */
	public function test_keys_are_unique(): void {
		$keys = array_column( $this->defs, 'key' );
		$this->assertSame( count( $keys ), count( array_unique( $keys ) ) );
	}
}
