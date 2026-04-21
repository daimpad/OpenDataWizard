<?php
/**
 * Tests für erweiterte ODW_Fields-Methoden und odw_build_dataset_jsonld() (v1.7.0)
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Test_ODW_Fields_Extended extends TestCase {

    protected function setUp(): void {
        \WP_Mock::setUp();
    }

    protected function tearDown(): void {
        \WP_Mock::tearDown();
    }

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

    public function test_get_periodicity_options_has_empty_key_default(): void {
        $this->load_fields();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $options = ODW_Fields::get_periodicity_options();
        $this->assertArrayHasKey( '', $options );
    }

    public function test_get_periodicity_options_contains_daily_uri(): void {
        $this->load_fields();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $options = ODW_Fields::get_periodicity_options();
        $this->assertArrayHasKey(
            'http://publications.europa.eu/resource/authority/frequency/DAILY',
            $options
        );
    }

    public function test_get_periodicity_options_contains_annual_uri(): void {
        $this->load_fields();

        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );

        $options = ODW_Fields::get_periodicity_options();
        $this->assertArrayHasKey(
            'http://publications.europa.eu/resource/authority/frequency/ANNUAL',
            $options
        );
    }

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
     * @param array<string, mixed> $cf_meta   CF field key → value
     * @param array<string, mixed> $post_meta WP meta key → value
     */
    private function setup_jsonld_mocks(
        int $post_id,
        string $post_type,
        array $cf_meta = [],
        array $post_meta = []
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
            ->andReturnUsing( function ( $id, $key ) use ( $cf_meta ) {
                return $cf_meta[ $key ] ?? '';
            } );

        // get_post_meta for _odw_modified and similar keys.
        \WP_Mock::userFunction( 'get_post_meta' )
            ->andReturnUsing( function ( $id, $key, $single ) use ( $post_meta ) {
                return $post_meta[ $key ] ?? '';
            } );

        \WP_Mock::userFunction( 'rest_url' )
            ->andReturnUsing( function ( $path ) {
                return 'http://localhost/wp-json/' . $path;
            } );

        \WP_Mock::userFunction( 'apply_filters' )
            ->andReturnArg( 1 );
    }

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

    public function test_build_returns_null_when_post_not_found(): void {
        $this->load_fields();

        \WP_Mock::userFunction( 'get_post' )
            ->with( 999 )
            ->andReturn( null );

        $this->assertNull( odw_build_dataset_jsonld( 999 ) );
    }

    public function test_build_includes_landing_page_when_set(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 10, 'odw_dataset', [
            'odw_landing_page' => 'https://example.com/project',
        ] );

        $result = odw_build_dataset_jsonld( 10 );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'dcat:landingPage', $result );
        $this->assertSame( [ '@id' => 'https://example.com/project' ], $result['dcat:landingPage'] );
    }

    public function test_build_omits_landing_page_when_empty(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 10, 'odw_dataset' );

        $result = odw_build_dataset_jsonld( 10 );

        $this->assertIsArray( $result );
        $this->assertArrayNotHasKey( 'dcat:landingPage', $result );
    }

    public function test_build_includes_accrual_periodicity_when_set(): void {
        $this->load_fields();

        $uri = 'http://publications.europa.eu/resource/authority/frequency/MONTHLY';
        $this->setup_jsonld_mocks( 11, 'odw_dataset', [
            'odw_accrual_periodicity' => $uri,
        ] );

        $result = odw_build_dataset_jsonld( 11 );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'dct:accrualPeriodicity', $result );
        $this->assertSame( [ '@id' => $uri ], $result['dct:accrualPeriodicity'] );
    }

    public function test_build_includes_spatial_with_correct_type(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 12, 'odw_dataset', [
            'odw_spatial' => 'Berlin',
        ] );

        $result = odw_build_dataset_jsonld( 12 );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'dct:spatial', $result );
        $this->assertSame( 'dct:Location', $result['dct:spatial']['@type'] );
        $this->assertSame( 'Berlin', $result['dct:spatial']['skos:prefLabel'] );
    }

    public function test_build_includes_temporal_with_start_and_end(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 13, 'odw_dataset', [
            'odw_temporal_start' => '2024-01-01',
            'odw_temporal_end'   => '2024-12-31',
        ] );

        $result = odw_build_dataset_jsonld( 13 );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'dct:temporal', $result );

        $temporal = $result['dct:temporal'];
        $this->assertSame( 'dct:PeriodOfTime', $temporal['@type'] );
        $this->assertSame( [ '@type' => 'xsd:date', '@value' => '2024-01-01' ], $temporal['dcat:startDate'] );
        $this->assertSame( [ '@type' => 'xsd:date', '@value' => '2024-12-31' ], $temporal['dcat:endDate'] );
    }

    public function test_build_includes_temporal_with_start_only(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 14, 'odw_dataset', [
            'odw_temporal_start' => '2025-01-01',
        ] );

        $result = odw_build_dataset_jsonld( 14 );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'dct:temporal', $result );
        $this->assertArrayHasKey( 'dcat:startDate', $result['dct:temporal'] );
        $this->assertArrayNotHasKey( 'dcat:endDate', $result['dct:temporal'] );
    }

    public function test_build_omits_temporal_when_both_empty(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 15, 'odw_dataset' );

        $result = odw_build_dataset_jsonld( 15 );

        $this->assertIsArray( $result );
        $this->assertArrayNotHasKey( 'dct:temporal', $result );
    }

    public function test_build_includes_contact_point_with_mailto_prefix(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 16, 'odw_dataset', [
            'odw_contact_name'  => 'Max Mustermann',
            'odw_contact_email' => 'max@example.org',
        ] );

        $result = odw_build_dataset_jsonld( 16 );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'dcat:contactPoint', $result );

        $contact = $result['dcat:contactPoint'];
        $this->assertSame( 'vcard:Organization', $contact['@type'] );
        $this->assertSame( 'Max Mustermann', $contact['vcard:fn'] );
        $this->assertSame( 'mailto:max@example.org', $contact['vcard:hasEmail'] );
    }

    public function test_build_contact_point_includes_url_as_id(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 17, 'odw_dataset', [
            'odw_contact_name'  => 'Org',
            'odw_contact_email' => 'info@org.de',
            'odw_contact_url'   => 'https://org.de',
        ] );

        $result = odw_build_dataset_jsonld( 17 );

        $this->assertIsArray( $result );
        $contact = $result['dcat:contactPoint'];
        $this->assertSame( [ '@id' => 'https://org.de' ], $contact['vcard:hasURL'] );
    }

    public function test_build_omits_contact_point_when_empty(): void {
        $this->load_fields();

        $this->setup_jsonld_mocks( 18, 'odw_dataset' );

        $result = odw_build_dataset_jsonld( 18 );

        $this->assertIsArray( $result );
        $this->assertArrayNotHasKey( 'dcat:contactPoint', $result );
    }
}
