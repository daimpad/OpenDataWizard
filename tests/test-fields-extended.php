<?php
/**
 * Tests für erweiterte ODW_Fields-Methoden und odw_build_dataset_jsonld() (v1.7.0)
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Extended unit tests for ODW_Fields methods and odw_build_dataset_jsonld().
 *
 * @package OpenDataWizard
 */
class Test_ODW_Fields_Extended extends TestCase {

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
	 * Loads ODW_Fields (and its companion function) once per test run.
	 */
	private function load_fields(): void {
		if ( ! class_exists( 'ODW_Fields' ) ) {
			\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
			\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
			require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
		}
	}

	// -------------------------------------------------------------------------
	// get_periodicity_options()
	// -------------------------------------------------------------------------

	/**
	 * Includes an empty-string key as placeholder in periodicity options.
	 */
	public function test_get_periodicity_options_has_empty_key_default(): void {
		$this->load_fields();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$options = ODW_Fields::get_periodicity_options();
		$this->assertArrayHasKey( '', $options );
	}

	/**
	 * The DAILY frequency URI from the EU Publications Office is present.
	 */
	public function test_get_periodicity_options_contains_daily_uri(): void {
		$this->load_fields();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$options = ODW_Fields::get_periodicity_options();
		$this->assertArrayHasKey(
			'http://publications.europa.eu/resource/authority/frequency/DAILY',
			$options
		);
	}

	/**
	 * The ANNUAL frequency URI from the EU Publications Office is present.
	 */
	public function test_get_periodicity_options_contains_annual_uri(): void {
		$this->load_fields();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$options = ODW_Fields::get_periodicity_options();
		$this->assertArrayHasKey(
			'http://publications.europa.eu/resource/authority/frequency/ANNUAL',
			$options
		);
	}

	/**
	 * All non-empty periodicity option keys start with the correct EU Publications Office base URI.
	 */
	public function test_get_periodicity_options_all_uris_use_correct_base(): void {
		$this->load_fields();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$base    = 'http://publications.europa.eu/resource/authority/frequency/';
		$options = ODW_Fields::get_periodicity_options();

		foreach ( array_keys( $options ) as $key ) {
			if ( '' === $key ) {
				continue;
			}
			$this->assertStringStartsWith( $base, $key, "Key '$key' does not use the EU Publications Office base URI." );
		}
	}

	// -------------------------------------------------------------------------
	// odw_build_dataset_jsonld() — helper to build mock post & CF meta
	// -------------------------------------------------------------------------

	/**
	 * Sets up WP_Mock stubs needed by odw_build_dataset_jsonld() tests.
	 *
	 * @param int                  $post_id   Post ID to mock.
	 * @param string               $post_type Post type for the mock post object.
	 * @param array<string, mixed> $cf_meta   Carbon Fields key-value map (field slug → value).
	 * @param array<string, mixed> $post_meta WP meta key-value map (meta key → value).
	 */
	private function setup_jsonld_mocks(
		int $post_id,
		string $post_type,
		array $cf_meta = array(),
		array $post_meta = array()
	): void {
		$post              = new \stdClass();
		$post->ID          = $post_id;
		$post->post_type   = $post_type;
		$post->post_title  = 'Test Dataset';
		$post->post_status = 'publish';

		\WP_Mock::userFunction( 'get_post' )
			->with( $post_id )
			->andReturn( $post );

		// Default CF meta returns empty string unless overridden.
		\WP_Mock::userFunction( 'carbon_get_post_meta' )
			->andReturnUsing(
				function ( $id, $key ) use ( $cf_meta ) {
					return $cf_meta[ $key ] ?? '';
				}
			);

		// get_post_meta for _odw_modified and similar keys.
		\WP_Mock::userFunction( 'get_post_meta' )
			->andReturnUsing(
				function ( $id, $key, $_single ) use ( $post_meta ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
					return $post_meta[ $key ] ?? '';
				}
			);

		\WP_Mock::userFunction( 'rest_url' )
			->andReturnUsing(
				function ( $path ) {
					return 'http://localhost/wp-json/' . $path;
				}
			);

		\WP_Mock::userFunction( 'apply_filters' )
			->andReturnArg( 1 );
	}

	/**
	 * Returns null when the post type is not odw_dataset.
	 */
	public function test_build_returns_null_for_non_dataset_post_type(): void {
		$this->load_fields();

		$post            = new \stdClass();
		$post->ID        = 1;
		$post->post_type = 'post';

		\WP_Mock::userFunction( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		$this->assertNull( odw_build_dataset_jsonld( 1 ) );
	}

	/**
	 * Returns null when no post is found for the given ID.
	 */
	public function test_build_returns_null_when_post_not_found(): void {
		$this->load_fields();

		\WP_Mock::userFunction( 'get_post' )
			->with( 999 )
			->andReturn( null );

		$this->assertNull( odw_build_dataset_jsonld( 999 ) );
	}

	/**
	 * The dcat:landingPage key is present in JSON-LD when odw_landing_page is set.
	 */
	public function test_build_includes_landing_page_when_set(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			10,
			'odw_dataset',
			array(
				'odw_landing_page' => 'https://example.com/project',
			)
		);

		$result = odw_build_dataset_jsonld( 10 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dcat:landingPage', $result );
		$this->assertSame( array( '@id' => 'https://example.com/project' ), $result['dcat:landingPage'] );
	}

	/**
	 * The dcat:landingPage key is absent when odw_landing_page is empty.
	 */
	public function test_build_omits_landing_page_when_empty(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks( 10, 'odw_dataset' );

		$result = odw_build_dataset_jsonld( 10 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dcat:landingPage', $result );
	}

	/**
	 * The dct:accrualPeriodicity is included as an @id object when the field is set.
	 */
	public function test_build_includes_accrual_periodicity_when_set(): void {
		$this->load_fields();

		$uri = 'http://publications.europa.eu/resource/authority/frequency/MONTHLY';
		$this->setup_jsonld_mocks(
			11,
			'odw_dataset',
			array(
				'odw_accrual_periodicity' => $uri,
			)
		);

		$result = odw_build_dataset_jsonld( 11 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dct:accrualPeriodicity', $result );
		$this->assertSame( array( '@id' => $uri ), $result['dct:accrualPeriodicity'] );
	}

	/**
	 * The dct:spatial field uses @type dct:Location and skos:prefLabel for a text value.
	 */
	public function test_build_includes_spatial_with_correct_type(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			12,
			'odw_dataset',
			array(
				'odw_spatial' => 'Berlin',
			)
		);

		$result = odw_build_dataset_jsonld( 12 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dct:spatial', $result );
		$this->assertSame( 'dct:Location', $result['dct:spatial']['@type'] );
		$this->assertSame( 'Berlin', $result['dct:spatial']['skos:prefLabel'] );
	}

	/**
	 * The dct:temporal field includes both dcat:startDate and dcat:endDate as xsd:date typed values.
	 */
	public function test_build_includes_temporal_with_start_and_end(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			13,
			'odw_dataset',
			array(
				'odw_temporal_start' => '2024-01-01',
				'odw_temporal_end'   => '2024-12-31',
			)
		);

		$result = odw_build_dataset_jsonld( 13 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dct:temporal', $result );

		$temporal = $result['dct:temporal'];
		$this->assertSame( 'dct:PeriodOfTime', $temporal['@type'] );
		$this->assertSame(
			array(
				'@type'  => 'xsd:date',
				'@value' => '2024-01-01',
			),
			$temporal['dcat:startDate']
		);
		$this->assertSame(
			array(
				'@type'  => 'xsd:date',
				'@value' => '2024-12-31',
			),
			$temporal['dcat:endDate']
		);
	}

	/**
	 * The dct:temporal field contains only dcat:startDate when end date is empty.
	 */
	public function test_build_includes_temporal_with_start_only(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			14,
			'odw_dataset',
			array(
				'odw_temporal_start' => '2025-01-01',
			)
		);

		$result = odw_build_dataset_jsonld( 14 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dct:temporal', $result );
		$this->assertArrayHasKey( 'dcat:startDate', $result['dct:temporal'] );
		$this->assertArrayNotHasKey( 'dcat:endDate', $result['dct:temporal'] );
	}

	/**
	 * The dct:temporal field is absent when both start and end are empty.
	 */
	public function test_build_omits_temporal_when_both_empty(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks( 15, 'odw_dataset' );

		$result = odw_build_dataset_jsonld( 15 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dct:temporal', $result );
	}

	/**
	 * The dcat:contactPoint includes vcard:hasEmail with the mailto: prefix.
	 */
	public function test_build_includes_contact_point_with_mailto_prefix(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			16,
			'odw_dataset',
			array(
				'odw_contact_name'  => 'Max Mustermann',
				'odw_contact_email' => 'max@example.org',
			)
		);

		$result = odw_build_dataset_jsonld( 16 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dcat:contactPoint', $result );

		$contact = $result['dcat:contactPoint'];
		$this->assertSame( 'vcard:Organization', $contact['@type'] );
		$this->assertSame( 'Max Mustermann', $contact['vcard:fn'] );
		$this->assertSame( 'mailto:max@example.org', $contact['vcard:hasEmail'] );
	}

	/**
	 * The vcard:hasURL is emitted as an @id object when odw_contact_url is set.
	 */
	public function test_build_contact_point_includes_url_as_id(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			17,
			'odw_dataset',
			array(
				'odw_contact_name'  => 'Org',
				'odw_contact_email' => 'info@org.de',
				'odw_contact_url'   => 'https://org.de',
			)
		);

		$result = odw_build_dataset_jsonld( 17 );

		$this->assertIsArray( $result );
		$contact = $result['dcat:contactPoint'];
		$this->assertSame( array( '@id' => 'https://org.de' ), $contact['vcard:hasURL'] );
	}

	/**
	 * The dcat:contactPoint is absent when both contact name and email are empty.
	 */
	public function test_build_omits_contact_point_when_empty(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks( 18, 'odw_dataset' );

		$result = odw_build_dataset_jsonld( 18 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dcat:contactPoint', $result );
	}
}
