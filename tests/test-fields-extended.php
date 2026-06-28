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

		\WP_Mock::userFunction( 'esc_url_raw' )
			->andReturnArg( 0 );

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
	 * Title and description are emitted as language-tagged literals; without a
	 * language field the default tag 'de' is used.
	 */
	public function test_build_title_description_are_language_tagged_default_de(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			30,
			'odw_dataset',
			array( 'odw_description' => 'Eine Beschreibung' )
		);

		$result = odw_build_dataset_jsonld( 30 );

		$this->assertIsArray( $result );
		$this->assertSame(
			array(
				'@value'    => 'Test Dataset',
				'@language' => 'de',
			),
			$result['dct:title']
		);
		$this->assertSame(
			array(
				'@value'    => 'Eine Beschreibung',
				'@language' => 'de',
			),
			$result['dct:description']
		);
	}

	/**
	 * The language field drives the literal language tag (EU URI → BCP-47).
	 */
	public function test_build_language_field_sets_literal_tag(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			31,
			'odw_dataset',
			array(
				'odw_description' => 'A description',
				'odw_language'    => 'http://publications.europa.eu/resource/authority/language/ENG',
			)
		);

		$result = odw_build_dataset_jsonld( 31 );

		$this->assertSame( 'en', $result['dct:title']['@language'] );
		$this->assertSame( 'en', $result['dct:description']['@language'] );
	}

	/**
	 * Keywords are emitted as an array of language-tagged literals.
	 */
	public function test_build_keywords_are_language_tagged(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			32,
			'odw_dataset',
			array( 'odw_keywords' => "Umwelt\nWasser" )
		);

		$result = odw_build_dataset_jsonld( 32 );

		$this->assertSame(
			array(
				array(
					'@value'    => 'Umwelt',
					'@language' => 'de',
				),
				array(
					'@value'    => 'Wasser',
					'@language' => 'de',
				),
			),
			$result['dcat:keyword']
		);
	}

	/**
	 * The language-tag resolver maps EU URIs, bare codes and legacy tags; unknown
	 * values resolve to an empty string.
	 */
	public function test_resolve_language_tag(): void {
		$this->load_fields();

		$this->assertSame( 'de', odw_resolve_language_tag( 'http://publications.europa.eu/resource/authority/language/DEU' ) );
		$this->assertSame( 'en', odw_resolve_language_tag( 'ENG' ) );
		$this->assertSame( 'fr', odw_resolve_language_tag( 'fr' ) );
		$this->assertSame( '', odw_resolve_language_tag( '' ) );
		$this->assertSame( '', odw_resolve_language_tag( 'http://example.org/lang/XXX' ) );
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
	 * The CESSDA topic is emitted as DCAT-AP conformant dct:subject (with @id),
	 * never under an undeclared cessda: prefix that would invalidate the JSON-LD.
	 */
	public function test_build_emits_cessda_topic_as_dct_subject(): void {
		$this->load_fields();

		$uri = 'https://vocabularies.cessda.eu/urn/urn:ddi:int.cessda.cv:TopicClassification:4.2.3:de:1.0';

		$this->setup_jsonld_mocks(
			13,
			'odw_dataset',
			array(
				'odw_cessda_topic' => $uri,
			)
		);

		$result = odw_build_dataset_jsonld( 13 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dct:subject', $result );
		$this->assertSame( array( '@id' => $uri ), $result['dct:subject'] );
		$this->assertArrayNotHasKey( 'cessda:topic', $result );
	}

	/**
	 * When a dataset is flagged HVD with a category, both dcatap:hvdCategory and
	 * dcatap:applicableLegislation are emitted (the latter pinned to Reg 2023/138).
	 */
	public function test_build_includes_hvd_category_and_legislation_when_enabled(): void {
		$this->load_fields();

		$category = 'http://data.europa.eu/bna/c_ac64a52d';

		$this->setup_jsonld_mocks(
			14,
			'odw_dataset',
			array(
				'odw_is_hvd'       => 'yes',
				'odw_hvd_category' => $category,
			)
		);

		$result = odw_build_dataset_jsonld( 14 );

		$this->assertIsArray( $result );
		$this->assertSame( array( '@id' => $category ), $result['dcatap:hvdCategory'] );
		$this->assertSame(
			array( '@id' => 'http://data.europa.eu/eli/reg_impl/2023/138/oj' ),
			$result['dcatap:applicableLegislation']
		);
	}

	/**
	 * HVD keys are absent when the dataset is not flagged as HVD.
	 */
	public function test_build_omits_hvd_when_disabled(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			14,
			'odw_dataset',
			array(
				'odw_is_hvd'       => '',
				'odw_hvd_category' => 'http://data.europa.eu/bna/c_ac64a52d',
			)
		);

		$result = odw_build_dataset_jsonld( 14 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dcatap:hvdCategory', $result );
		$this->assertArrayNotHasKey( 'dcatap:applicableLegislation', $result );
	}

	/**
	 * HVD keys are absent when flagged HVD but no category is chosen — we never
	 * emit applicableLegislation without a category.
	 */
	public function test_build_omits_hvd_when_category_empty(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			14,
			'odw_dataset',
			array(
				'odw_is_hvd'       => 'yes',
				'odw_hvd_category' => '',
			)
		);

		$result = odw_build_dataset_jsonld( 14 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dcatap:hvdCategory', $result );
		$this->assertArrayNotHasKey( 'dcatap:applicableLegislation', $result );
	}

	/**
	 * The HVD category options expose the empty default plus the six EU categories.
	 */
	public function test_get_hvd_category_options_has_six_categories_and_default(): void {
		$this->load_fields();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$options = ODW_Fields::get_hvd_category_options();

		$this->assertArrayHasKey( '', $options );
		$this->assertArrayHasKey( 'http://data.europa.eu/bna/c_ac64a52d', $options );
		$this->assertArrayHasKey( 'http://data.europa.eu/bna/c_b79e35eb', $options );
		// Six categories + one empty default.
		$this->assertCount( 7, $options );
	}

	/**
	 * The dcatap:availability term is attached to the distribution when set.
	 */
	public function test_build_includes_availability_in_distribution(): void {
		$this->load_fields();

		$uri = 'http://publications.europa.eu/resource/authority/planned-availability/STABLE';

		$this->setup_jsonld_mocks(
			15,
			'odw_dataset',
			array(
				'odw_access_url'   => 'https://example.com/data.csv',
				'odw_availability' => $uri,
			)
		);

		$result = odw_build_dataset_jsonld( 15 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dcat:distribution', $result );
		$this->assertSame( array( '@id' => $uri ), $result['dcat:distribution'][0]['dcatap:availability'] );
		// dcat:accessURL is an IRI resource ({@id}), consistent with other URI fields.
		$this->assertSame( array( '@id' => 'https://example.com/data.csv' ), $result['dcat:distribution'][0]['dcat:accessURL'] );
	}

	/**
	 * The dcatde:contributorID is emitted as an @id object when set.
	 */
	public function test_build_includes_contributor_id(): void {
		$this->load_fields();

		$uri = 'http://dcat-ap.de/def/contributors/openDataBayern';

		$this->setup_jsonld_mocks(
			16,
			'odw_dataset',
			array( 'odw_contributor_id' => $uri )
		);

		$result = odw_build_dataset_jsonld( 16 );

		$this->assertIsArray( $result );
		$this->assertSame( array( '@id' => $uri ), $result['dcatde:contributorID'] );
	}

	/**
	 * The dcatde:originator and dcatde:maintainer build foaf:Agent nodes; the
	 * mailbox is only present when an e-mail is given.
	 */
	public function test_build_includes_originator_and_maintainer_agents(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			17,
			'odw_dataset',
			array(
				'odw_originator_name'  => 'Statistisches Landesamt',
				'odw_originator_email' => 'urheber@example.com',
				'odw_maintainer_name'  => 'Open Data Team',
			)
		);

		$result = odw_build_dataset_jsonld( 17 );

		$this->assertIsArray( $result );
		$this->assertSame( 'foaf:Agent', $result['dcatde:originator']['@type'] );
		$this->assertSame( 'Statistisches Landesamt', $result['dcatde:originator']['foaf:name'] );
		$this->assertSame( array( '@id' => 'mailto:urheber@example.com' ), $result['dcatde:originator']['foaf:mbox'] );

		$this->assertSame( 'Open Data Team', $result['dcatde:maintainer']['foaf:name'] );
		$this->assertArrayNotHasKey( 'foaf:mbox', $result['dcatde:maintainer'] );
	}

	/**
	 * Agent roles are omitted entirely when no name is provided.
	 */
	public function test_build_omits_agents_without_name(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			17,
			'odw_dataset',
			array( 'odw_originator_email' => 'lonely@example.com' )
		);

		$result = odw_build_dataset_jsonld( 17 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dcatde:originator', $result );
		$this->assertArrayNotHasKey( 'dcatde:maintainer', $result );
	}

	/**
	 * The DCAT-AP.de coverage/legal fields are emitted with the correct shape:
	 * politicalGeocodingURI and qualityProcessURI as @id objects, legalBasis as
	 * a plain literal.
	 */
	public function test_build_includes_dcatde_coverage_and_legal_fields(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			18,
			'odw_dataset',
			array(
				'odw_political_geocoding_uri' => 'http://dcat-ap.de/def/politicalGeocoding/regionalKey/09',
				'odw_legal_basis'             => '§ 12a EGovG',
				'odw_quality_process_uri'     => 'https://example.com/qa',
			)
		);

		$result = odw_build_dataset_jsonld( 18 );

		$this->assertIsArray( $result );
		$this->assertSame(
			array( '@id' => 'http://dcat-ap.de/def/politicalGeocoding/regionalKey/09' ),
			$result['dcatde:politicalGeocodingURI']
		);
		$this->assertSame( '§ 12a EGovG', $result['dcatde:legalBasis'] );
		$this->assertSame( array( '@id' => 'https://example.com/qa' ), $result['dcatde:qualityProcessURI'] );
	}

	/**
	 * The DCAT-AP.de coverage/legal fields are absent when not set.
	 */
	public function test_build_omits_dcatde_coverage_and_legal_fields_when_empty(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks( 18, 'odw_dataset' );

		$result = odw_build_dataset_jsonld( 18 );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'dcatde:politicalGeocodingURI', $result );
		$this->assertArrayNotHasKey( 'dcatde:legalBasis', $result );
		$this->assertArrayNotHasKey( 'dcatde:qualityProcessURI', $result );
	}

	/**
	 * The planned-availability options expose the empty default plus four values.
	 */
	public function test_get_availability_options_structure(): void {
		$this->load_fields();

		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$options = ODW_Fields::get_availability_options();

		$this->assertArrayHasKey( '', $options );
		$this->assertArrayHasKey( 'http://publications.europa.eu/resource/authority/planned-availability/STABLE', $options );
		$this->assertCount( 5, $options );
	}

	/**
	 * The dct:accessRights is emitted as an @id object when set.
	 */
	public function test_build_includes_access_rights(): void {
		$this->load_fields();

		$uri = 'http://publications.europa.eu/resource/authority/access-right/PUBLIC';

		$this->setup_jsonld_mocks(
			19,
			'odw_dataset',
			array( 'odw_access_rights' => $uri )
		);

		$result = odw_build_dataset_jsonld( 19 );

		$this->assertIsArray( $result );
		$this->assertSame( array( '@id' => $uri ), $result['dct:accessRights'] );
	}

	/**
	 * An additional theme URI is appended to the curated theme, producing a
	 * dcat:theme array with both entries.
	 */
	public function test_build_appends_additional_theme_uri(): void {
		$this->load_fields();

		$extra = 'http://publications.europa.eu/resource/authority/data-theme/ENER';

		$this->setup_jsonld_mocks(
			20,
			'odw_dataset',
			array(
				'odw_theme'     => 'Bildung',
				'odw_theme_uri' => $extra,
			)
		);

		$result = odw_build_dataset_jsonld( 20 );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result['dcat:theme'] );
		$this->assertSame( $extra, $result['dcat:theme'][1]['@id'] );
	}

	/**
	 * A theme URI on its own yields a single dcat:theme object (not an array).
	 */
	public function test_build_theme_uri_alone_is_single_object(): void {
		$this->load_fields();

		$extra = 'http://publications.europa.eu/resource/authority/data-theme/TECH';

		$this->setup_jsonld_mocks(
			20,
			'odw_dataset',
			array( 'odw_theme_uri' => $extra )
		);

		$result = odw_build_dataset_jsonld( 20 );

		$this->assertIsArray( $result );
		$this->assertSame( array( '@id' => $extra ), $result['dcat:theme'] );
	}

	/**
	 * The additional theme field stores a human-readable label (autosuggest);
	 * it is resolved to the official EU data-theme URI for the @id (mirrors the
	 * contributorID behaviour). Regression test for the label-as-@id bug.
	 */
	public function test_build_resolves_additional_theme_label_to_uri(): void {
		$this->load_fields();

		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}

		// load_vocabulary() is hit because the value is a label, not a URI.
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$this->setup_jsonld_mocks(
			21,
			'odw_dataset',
			array( 'odw_theme_uri' => 'Energie' )
		);

		$result = odw_build_dataset_jsonld( 21 );

		$this->assertIsArray( $result );
		$this->assertSame(
			array( '@id' => 'http://publications.europa.eu/resource/authority/data-theme/ENER' ),
			$result['dcat:theme']
		);
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
		$this->assertSame( array( '@id' => 'mailto:max@example.org' ), $contact['vcard:hasEmail'] );
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

	// -------------------------------------------------------------------------
	// DCAT-AP.de compliance tests
	// -------------------------------------------------------------------------

	/**
	 * The dcat:theme key is emitted as an @id object referencing the EU data-theme URI.
	 */
	public function test_build_theme_is_emitted_as_id_reference(): void {
		$this->load_fields();

		$uri = 'http://publications.europa.eu/resource/authority/data-theme/ENVI';
		$this->setup_jsonld_mocks( 20, 'odw_dataset', array( 'odw_theme' => $uri ) );

		$result = odw_build_dataset_jsonld( 20 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dcat:theme', $result );
		$this->assertSame( array( '@id' => $uri ), $result['dcat:theme'] );
	}

	/**
	 * Legacy text theme labels are mapped to their EU data-theme URI equivalents.
	 */
	public function test_build_theme_maps_legacy_text_to_eu_uri(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks( 21, 'odw_dataset', array( 'odw_theme' => 'Umwelt' ) );

		$result = odw_build_dataset_jsonld( 21 );

		$this->assertIsArray( $result );
		$this->assertSame(
			array( '@id' => 'http://publications.europa.eu/resource/authority/data-theme/ENVI' ),
			$result['dcat:theme']
		);
	}

	/**
	 * The dct:language key is emitted as an @id object referencing the EU language URI.
	 */
	public function test_build_language_is_emitted_as_id_reference(): void {
		$this->load_fields();

		$uri = 'http://publications.europa.eu/resource/authority/language/DEU';
		$this->setup_jsonld_mocks( 22, 'odw_dataset', array( 'odw_language' => $uri ) );

		$result = odw_build_dataset_jsonld( 22 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dct:language', $result );
		$this->assertSame( array( '@id' => $uri ), $result['dct:language'] );
	}

	/**
	 * Legacy 'de' language code is migrated to the EU language URI for German.
	 */
	public function test_build_language_maps_legacy_de_code(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks( 23, 'odw_dataset', array( 'odw_language' => 'de' ) );

		$result = odw_build_dataset_jsonld( 23 );

		$this->assertIsArray( $result );
		$this->assertSame(
			array( '@id' => 'http://publications.europa.eu/resource/authority/language/DEU' ),
			$result['dct:language']
		);
	}

	/**
	 * The dct:format in a distribution uses the EU file-type URI (not a MIME type).
	 */
	public function test_build_distribution_format_uses_eu_uri(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			24,
			'odw_dataset',
			array(
				'odw_access_url' => 'https://example.com/data.csv',
				'odw_format'     => 'CSV',
				'odw_byte_size'  => '',
			)
		);

		$result = odw_build_dataset_jsonld( 24 );

		$this->assertIsArray( $result );
		$dist = $result['dcat:distribution'][0];
		$this->assertSame(
			array( '@id' => 'http://publications.europa.eu/resource/authority/file-type/CSV' ),
			$dist['dct:format']
		);
	}

	/**
	 * Distribution carries a license as dct:license @id.
	 */
	public function test_build_distribution_has_own_license(): void {
		$this->load_fields();

		$license_uri = 'https://creativecommons.org/publicdomain/zero/1.0/';
		$this->setup_jsonld_mocks(
			25,
			'odw_dataset',
			array(
				'odw_access_url' => 'https://example.com/data.csv',
				'odw_format'     => 'CSV',
				'odw_byte_size'  => '',
				'odw_license'    => $license_uri,
			)
		);

		$result = odw_build_dataset_jsonld( 25 );

		$this->assertIsArray( $result );
		$dist = $result['dcat:distribution'][0];
		$this->assertSame( array( '@id' => $license_uri ), $dist['dct:license'] );
	}

	/**
	 * The dcatde:licenseAttributionByText is included in distribution when set.
	 */
	public function test_build_distribution_attribution_text(): void {
		$this->load_fields();

		$this->setup_jsonld_mocks(
			26,
			'odw_dataset',
			array(
				'odw_access_url'       => 'https://example.com/data.csv',
				'odw_format'           => 'CSV',
				'odw_byte_size'        => '',
				'odw_attribution_text' => 'Daten von Musterorganisation e.V.',
			)
		);

		$result = odw_build_dataset_jsonld( 26 );

		$this->assertIsArray( $result );
		$dist = $result['dcat:distribution'][0];
		$this->assertSame( 'Daten von Musterorganisation e.V.', $dist['dcatde:licenseAttributionByText'] );
	}

	/**
	 * The dcatde:politicalGeocodingLevelURI is emitted as an @id reference.
	 */
	public function test_build_includes_political_geocoding_level_uri(): void {
		$this->load_fields();

		$uri = 'http://dcat-ap.de/def/politicalGeocoding/Level/municipality';
		$this->setup_jsonld_mocks( 27, 'odw_dataset', array( 'odw_political_geocoding_level' => $uri ) );

		$result = odw_build_dataset_jsonld( 27 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'dcatde:politicalGeocodingLevelURI', $result );
		$this->assertSame( array( '@id' => $uri ), $result['dcatde:politicalGeocodingLevelURI'] );
	}

	/**
	 * The dct:license is emitted per distribution (not at dataset level).
	 * A distribution with license 'sonstige' + license_custom uses the custom URI.
	 */
	public function test_build_distribution_sonstige_license_uses_custom_uri(): void {
		$this->load_fields();

		$custom_uri = 'https://example.org/my-custom-license';
		$this->setup_jsonld_mocks(
			28,
			'odw_dataset',
			array(
				'odw_access_url'     => 'https://example.com/data.csv',
				'odw_format'         => 'CSV',
				'odw_byte_size'      => '',
				'odw_license'        => 'sonstige',
				'odw_license_custom' => $custom_uri,
			)
		);

		$result = odw_build_dataset_jsonld( 28 );

		$this->assertIsArray( $result );
		$dist = $result['dcat:distribution'][0];
		$this->assertSame( array( '@id' => $custom_uri ), $dist['dct:license'] );
	}

	/**
	 * Routes scheme-bearing values — including ones obfuscated with leading
	 * whitespace or embedded control chars — through esc_url_raw(), and leaves
	 * bare codes/labels untouched.
	 */
	public function test_sanitize_jsonld_id_routes_schemes_and_blocks_whitespace_bypass(): void {
		$this->load_fields();

		\WP_Mock::userFunction( 'esc_url_raw' )->andReturn( 'ESCAPED' );

		// Bare codes/labels have no scheme — returned unchanged.
		$this->assertSame( 'de', odw_sanitize_jsonld_id( 'de' ) );
		$this->assertSame( 'Soziales', odw_sanitize_jsonld_id( 'Soziales' ) );

		// Plain scheme-bearing values are routed through esc_url_raw().
		$this->assertSame( 'ESCAPED', odw_sanitize_jsonld_id( 'https://example.org/x' ) );

		// Leading whitespace must NOT bypass scheme detection.
		$this->assertSame( 'ESCAPED', odw_sanitize_jsonld_id( '   javascript:alert(1)' ) );

		// Embedded control characters must NOT bypass it either.
		$this->assertSame( 'ESCAPED', odw_sanitize_jsonld_id( "java\nscript:alert(1)" ) );
	}
}
