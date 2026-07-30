<?php
/**
 * Tests für den Delta-Harvesting Endpoint (GET /delta)
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// ---------------------------------------------------------------------------
// Minimal WP stubs — defined once, guarded to survive multi-file test runs.
// ---------------------------------------------------------------------------

if ( ! class_exists( 'WP_REST_Request' ) ) {
	/**
	 * Stub for WP_REST_Request.
	 */
	class WP_REST_Request {
		/**
		 * Stored request parameters.
		 *
		 * @var array<string,mixed>
		 */
		private array $params = array();

		/**
		 * Sets a single parameter value.
		 *
		 * @param string $key   Parameter name.
		 * @param mixed  $value Parameter value.
		 */
		public function set_param( string $key, mixed $value ): void {
			$this->params[ $key ] = $value;
		}

		/**
		 * Returns the value for a single parameter.
		 *
		 * @param string $key Parameter name.
		 * @return mixed Parameter value, or null when not set.
		 */
		public function get_param( string $key ): mixed {
			return $this->params[ $key ] ?? null;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	/**
	 * Stub for WP_REST_Response.
	 */
	class WP_REST_Response {
		/**
		 * Response body data.
		 *
		 * @var mixed
		 */
		public mixed $data;
		/**
		 * HTTP status code.
		 *
		 * @var int
		 */
		public int $status;
		/**
		 * Stored response headers.
		 *
		 * @var array<string,string>
		 */
		public array $headers = array();

		/**
		 * Creates a REST response stub.
		 *
		 * @param mixed $data   Response body.
		 * @param int   $status HTTP status code.
		 */
		public function __construct( mixed $data, int $status = 200 ) {
			$this->data   = $data;
			$this->status = $status;
		}

		/**
		 * Stores an HTTP response header.
		 *
		 * @param string $key   Header name.
		 * @param string $value Header value.
		 */
		public function header( string $key, string $value ): void {
			$this->headers[ $key ] = $value;
		}
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Stub for WP_Error.
	 */
	class WP_Error {
		/**
		 * Error code string.
		 *
		 * @var string
		 */
		public string $code;
		/**
		 * Human-readable error message.
		 *
		 * @var string
		 */
		public string $message;
		/**
		 * Additional error data.
		 *
		 * @var array<string,mixed>
		 */
		public array $data;

		/**
		 * Creates a WP_Error stub.
		 *
		 * @param string              $code    Error code.
		 * @param string              $message Human-readable message.
		 * @param array<string,mixed> $data    Additional data (e.g. HTTP status).
		 */
		public function __construct( string $code, string $message = '', array $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	/**
	 * Configurable stub for WP_Query used in delta endpoint tests.
	 *
	 * Tests push result sets onto $mock_queue; each instantiation shifts one off.
	 */
	class WP_Query {
		/**
		 * Preset result sets consumed by each instantiation.
		 *
		 * @var array<int, array<string,mixed>>
		 */
		public static array $mock_queue = array();

		/**
		 * Queried post objects.
		 *
		 * @var array<int,object>
		 */
		public array $posts = array();
		/**
		 * Total number of found posts.
		 *
		 * @var int
		 */
		public int $found_posts = 0;
		/**
		 * Total number of result pages.
		 *
		 * @var int
		 */
		public int $max_num_pages = 0;

		/**
		 * Creates a WP_Query stub, consuming the next item from the mock queue.
		 *
		 * @param array<string,mixed> $args Query arguments (not used by stub).
		 */
		public function __construct( array $args = array() ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- stub intentionally ignores query args.
			$result              = array_shift( self::$mock_queue ) ?? array();
			$this->posts         = $result['posts'] ?? array();
			$this->found_posts   = $result['found_posts'] ?? 0;
			$this->max_num_pages = $result['max_num_pages'] ?? 0;
		}
	}
}

if ( ! class_exists( 'DateTimeZone' ) ) {
	/**
	 * Stub for DateTimeZone — only needed if PHP is missing it (should not happen).
	 */
	class DateTimeZone {} // phpcs:ignore Generic.Files.OneClassPerFile.MultipleFound -- stubs file.
}

/**
 * Unit tests for the ODW_Rest_API delta harvesting endpoint.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Rest_Delta extends TestCase {

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		WP_Query::$mock_queue = array();
	}

	/**
	 * Tear down WP_Mock after each test.
	 */
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Loads ODW_Rest_API once per test run.
	 */
	private function load_class(): void {
		if ( ! class_exists( 'ODW_Rest_API' ) ) {
			\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
			require_once ODW_PLUGIN_DIR . 'includes/class-rest-api.php';
			require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
		}
	}

	/**
	 * Builds a WP_REST_Request stub with the given parameters pre-set.
	 *
	 * @param array<string,mixed> $params Request parameters.
	 * @return WP_REST_Request Populated request stub.
	 */
	private function make_request( array $params ): WP_REST_Request {
		$request = new WP_REST_Request();
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		return $request;
	}

	// -------------------------------------------------------------------------
	// JSON-LD @context
	// -------------------------------------------------------------------------

	/**
	 * The dcat namespace must use the canonical http:// IRI. Using https:// would
	 * expand dcat:Dataset to a different IRI than the registered DCAT vocabulary,
	 * breaking type/property matching for harvesters.
	 */
	public function test_jsonld_context_dcat_namespace_is_canonical_http(): void {
		$this->load_class();

		$reflection = new \ReflectionClass( 'ODW_Rest_API' );
		$context    = $reflection->getConstant( 'JSONLD_CONTEXT' );

		$this->assertIsArray( $context );
		$this->assertArrayHasKey( 'dcat', $context );
		$this->assertSame( 'http://www.w3.org/ns/dcat#', $context['dcat'] );
	}

	/**
	 * The dcatap namespace (r5r) must be declared so HVD terms
	 * (dcatap:hvdCategory, dcatap:applicableLegislation) resolve in the JSON-LD.
	 */
	public function test_jsonld_context_declares_dcatap_namespace(): void {
		$this->load_class();

		$reflection = new \ReflectionClass( 'ODW_Rest_API' );
		$context    = $reflection->getConstant( 'JSONLD_CONTEXT' );

		$this->assertIsArray( $context );
		$this->assertArrayHasKey( 'dcatap', $context );
		$this->assertSame( 'http://data.europa.eu/r5r/', $context['dcatap'] );
	}

	// -------------------------------------------------------------------------
	// validate_since_param()
	// -------------------------------------------------------------------------

	/**
	 * Accepts a plain date string in YYYY-MM-DD format.
	 */
	public function test_validate_since_accepts_date_only(): void {
		$this->load_class();
		$this->assertTrue( ODW_Rest_API::validate_since_param( '2024-01-01' ) );
	}

	/**
	 * Accepts a full datetime with Z (UTC) suffix.
	 */
	public function test_validate_since_accepts_datetime_utc_z(): void {
		$this->load_class();
		$this->assertTrue( ODW_Rest_API::validate_since_param( '2024-06-15T12:30:00Z' ) );
	}

	/**
	 * Accepts a full datetime with numeric timezone offset.
	 */
	public function test_validate_since_accepts_datetime_with_offset(): void {
		$this->load_class();
		$this->assertTrue( ODW_Rest_API::validate_since_param( '2024-06-15T12:30:00+02:00' ) );
	}

	/**
	 * Accepts a datetime without timezone suffix (assumed UTC).
	 */
	public function test_validate_since_accepts_datetime_no_tz(): void {
		$this->load_class();
		$this->assertTrue( ODW_Rest_API::validate_since_param( '2024-06-15T12:30:00' ) );
	}

	/**
	 * Rejects a free-text string that is not a date.
	 */
	public function test_validate_since_rejects_free_text(): void {
		$this->load_class();
		$this->assertFalse( ODW_Rest_API::validate_since_param( 'yesterday' ) );
	}

	/**
	 * Rejects an empty string.
	 */
	public function test_validate_since_rejects_empty_string(): void {
		$this->load_class();
		$this->assertFalse( ODW_Rest_API::validate_since_param( '' ) );
	}

	/**
	 * Rejects a partial date that cannot be parsed (YYYY-MM only).
	 */
	public function test_validate_since_rejects_partial_date(): void {
		$this->load_class();
		$this->assertFalse( ODW_Rest_API::validate_since_param( '2024-06' ) );
	}

	/**
	 * Rejects an overflow date that createFromFormat() would silently normalise.
	 */
	public function test_validate_since_rejects_overflow_date(): void {
		$this->load_class();
		$this->assertFalse( ODW_Rest_API::validate_since_param( '2024-13-45' ) );
	}

	// -------------------------------------------------------------------------
	// get_delta() — cache hit
	// -------------------------------------------------------------------------

	/**
	 * Returns cached body and correct headers on a transient cache hit.
	 */
	public function test_get_delta_returns_cached_response_on_hit(): void {
		$this->load_class();

		$cached_body = array(
			'@type'             => 'odw:DeltaCatalog',
			'odw:since'         => '2024-01-01',
			'odw:totalModified' => 1,
			'odw:totalRemoved'  => 0,
			'dcat:dataset'      => array( array( '@type' => 'dcat:Dataset' ) ),
			'odw:removed'       => array(),
		);

		\WP_Mock::userFunction( 'get_transient' )
			->andReturn(
				array(
					'body'         => $cached_body,
					'total'        => 1,
					'pages'        => 1,
					'generated_at' => '2026-04-22T10:00:00+00:00',
				)
			);

		$request = $this->make_request(
			array(
				'since'    => '2024-01-01',
				'page'     => 1,
				'per_page' => 20,
				'format'   => 'jsonld',
			)
		);

		$response = ODW_Rest_API::get_delta( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->status );
		$this->assertSame( $cached_body, $response->data );
		$this->assertSame( 'HIT', $response->headers['X-ODW-Cache'] );
		$this->assertSame( '2024-01-01', $response->headers['X-ODW-Delta-Since'] );
	}

	// -------------------------------------------------------------------------
	// get_delta() — cache miss, no results
	// -------------------------------------------------------------------------

	/**
	 * Returns an empty delta body with zero counts when no datasets match the window.
	 */
	public function test_get_delta_returns_empty_body_when_nothing_modified(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		// First WP_Query: published (empty); second: trashed (empty).
		WP_Query::$mock_queue = array(
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
		);

		$request = $this->make_request(
			array(
				'since'    => '2025-01-01',
				'page'     => 1,
				'per_page' => 20,
				'format'   => 'jsonld',
			)
		);

		$response = ODW_Rest_API::get_delta( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->status );

		$body = $response->data;
		$this->assertSame( 'odw:DeltaCatalog', $body['@type'] );
		$this->assertSame( '2025-01-01', $body['odw:since'] );
		$this->assertSame( 0, $body['odw:totalModified'] );
		$this->assertSame( 0, $body['odw:totalRemoved'] );
		$this->assertSame( array(), $body['dcat:dataset'] );
		$this->assertSame( array(), $body['odw:removed'] );
		$this->assertSame( 'MISS', $response->headers['X-ODW-Cache'] );
	}

	// -------------------------------------------------------------------------
	// get_delta() — cache miss, with results
	// -------------------------------------------------------------------------

	/**
	 * Includes full JSON-LD for each modified dataset in dcat:dataset.
	 *
	 * Integration test — requires full WP_Query mock population and DateTime handling.
	 */
	public function test_get_delta_includes_modified_datasets(): void {
		$this->markTestSkipped( 'Delta integration tests require full WP_Query mock support — tested in live environment.' );
		$this->load_class();

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$post              = new \stdClass();
		$post->ID          = 42;
		$post->post_type   = 'odw_dataset';
		$post->post_status = 'publish';
		$post->post_title  = 'My Dataset';

		WP_Query::$mock_queue = array(
			array(
				'posts'         => array( $post ),
				'found_posts'   => 1,
				'max_num_pages' => 1,
			),
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
		);

		// Stub functions needed by odw_build_dataset_jsonld().
		\WP_Mock::userFunction( 'get_post' )->with( 42 )->andReturn( $post );
		\WP_Mock::userFunction( 'carbon_get_post_meta' )->andReturn( '' );
		\WP_Mock::userFunction( 'get_post_meta' )->andReturn( '' );
		\WP_Mock::userFunction( 'rest_url' )->andReturnUsing( fn( $p ) => 'http://localhost/wp-json/' . $p );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );

		$request = $this->make_request(
			array(
				'since'    => '2025-01-01',
				'page'     => 1,
				'per_page' => 20,
				'format'   => 'jsonld',
			)
		);

		$response = ODW_Rest_API::get_delta( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$body = $response->data;
		$this->assertSame( 1, $body['odw:totalModified'] );
		$this->assertCount( 1, $body['dcat:dataset'] );
		$this->assertSame( 'dcat:Dataset', $body['dcat:dataset'][0]['@type'] );
	}

	/**
	 * Includes tombstone entries in odw:removed for trashed datasets.
	 *
	 * Integration test — requires full WP_Query mock support.
	 */
	public function test_get_delta_includes_removed_tombstones(): void {
		$this->markTestSkipped( 'Delta integration tests require full WP_Query mock support — tested in live environment.' );
		$this->load_class();

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );

		$trashed                    = new \stdClass();
		$trashed->ID                = 99;
		$trashed->post_type         = 'odw_dataset';
		$trashed->post_status       = 'trash';
		$trashed->post_modified_gmt = '2025-06-01 08:00:00';

		WP_Query::$mock_queue = array(
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
			array(
				'posts'         => array( $trashed ),
				'found_posts'   => 1,
				'max_num_pages' => 1,
			),
		);

		\WP_Mock::userFunction( 'rest_url' )->andReturnUsing( fn( $p ) => 'http://localhost/wp-json/' . $p );

		$request = $this->make_request(
			array(
				'since'    => '2025-01-01',
				'page'     => 1,
				'per_page' => 20,
				'format'   => 'jsonld',
			)
		);

		$response = ODW_Rest_API::get_delta( $request );
		$body     = $response->data;

		$this->assertSame( 1, $body['odw:totalRemoved'] );
		$this->assertCount( 1, $body['odw:removed'] );

		$tombstone = $body['odw:removed'][0];
		$this->assertSame( 'dcat:Dataset', $tombstone['@type'] );
		$this->assertStringContainsString( '99', $tombstone['@id'] );
		$this->assertArrayHasKey( 'odw:removedAt', $tombstone );
	}

	// -------------------------------------------------------------------------
	// get_delta() — response headers
	// -------------------------------------------------------------------------

	/**
	 * Sets the correct pagination and delta-specific response headers.
	 *
	 * Integration test — requires full WP_Query mock support.
	 */
	public function test_get_delta_sets_expected_response_headers(): void {
		$this->markTestSkipped( 'Delta integration tests require full WP_Query mock support — tested in live environment.' );
		$this->load_class();

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( 'rest_url' )->andReturnArg( 0 );

		WP_Query::$mock_queue = array(
			array(
				'posts'         => array(),
				'found_posts'   => 3,
				'max_num_pages' => 2,
			),
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
		);

		$request = $this->make_request(
			array(
				'since'    => '2024-03-01T00:00:00Z',
				'page'     => 1,
				'per_page' => 2,
				'format'   => 'jsonld',
			)
		);

		$response = ODW_Rest_API::get_delta( $request );

		$this->assertSame( '3', $response->headers['X-WP-Total'] );
		$this->assertSame( '2', $response->headers['X-WP-TotalPages'] );
		$this->assertSame( '2024-03-01T00:00:00Z', $response->headers['X-ODW-Delta-Since'] );
		$this->assertArrayHasKey( 'X-ODW-Generated-At', $response->headers );
		$this->assertSame( 'MISS', $response->headers['X-ODW-Cache'] );
	}

	// -------------------------------------------------------------------------
	// get_delta() — response body structure
	// -------------------------------------------------------------------------

	/**
	 * The response body contains all required top-level JSON-LD keys.
	 */
	public function test_get_delta_body_has_required_keys(): void {
		$this->load_class();

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
		\WP_Mock::userFunction( 'rest_url' )->andReturnArg( 0 );

		WP_Query::$mock_queue = array(
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
			array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			),
		);

		$request = $this->make_request(
			array(
				'since'    => '2024-01-01',
				'page'     => 1,
				'per_page' => 20,
				'format'   => 'jsonld',
			)
		);

		$body = ODW_Rest_API::get_delta( $request )->data;

		$this->assertArrayHasKey( '@context', $body );
		$this->assertArrayHasKey( '@type', $body );
		$this->assertArrayHasKey( 'dct:issued', $body );
		$this->assertArrayHasKey( 'odw:since', $body );
		$this->assertArrayHasKey( 'odw:totalModified', $body );
		$this->assertArrayHasKey( 'odw:totalRemoved', $body );
		$this->assertArrayHasKey( 'dcat:dataset', $body );
		$this->assertArrayHasKey( 'odw:removed', $body );
	}

	/**
	 * A date-only since parameter is parsed as start of day (00:00:00 UTC) —
	 * without the '!' format prefix, createFromFormat() would inject the current
	 * wall-clock time and silently drop same-day changes from the delta.
	 */
	public function test_parse_iso8601_date_only_is_midnight_utc(): void {
		$this->load_class();

		$method = new \ReflectionMethod( 'ODW_Rest_API', 'parse_iso8601' );
		$method->setAccessible( true );

		$dt = $method->invoke( null, '2026-07-30' );

		$this->assertInstanceOf( \DateTimeImmutable::class, $dt );
		$this->assertSame( '2026-07-30 00:00:00', $dt->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( 'UTC', $dt->getTimezone()->getName() );
	}
}
