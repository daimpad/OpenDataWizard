<?php
/**
 * Tests für ODW_Validation — Schwerpunkt: bedingte HVD-Pflichtprüfung.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Validation::validate(), focused on the conditional
 * HVD-category requirement (category mandatory when the dataset is flagged HVD).
 */
class Test_ODW_Validation extends TestCase {

	/**
	 * The exact (form-language) error label appended when an HVD dataset lacks a category.
	 */
	private const HVD_ERROR = 'HVD-Kategorie';

	/** Form-language label for the missing-distribution error. */
	private const DIST_ERROR = 'Link zur Datei oder Datei-Upload';

	/** Form-language label for the missing-license error. */
	private const LICENSE_ERROR = 'Lizenz';

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
	 * Loads the classes under test once.
	 */
	private function load_classes(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		if ( ! class_exists( 'ODW_Fields' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
		}
		if ( ! class_exists( 'ODW_Validation' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-validation.php';
		}
	}

	/**
	 * Invokes the private ODW_Validation::validate() via reflection.
	 *
	 * @param int                  $post_id  Post ID.
	 * @param array<string, mixed> $postarr  Raw post array (incl. carbon_fields_compact_input).
	 * @param array<string, mixed> $meta_map Optional get_post_meta fallback map (meta_key => value).
	 * @return array<int, array{label: string, dcat: string, tab: int, target: string, section: string}> Structured errors.
	 */
	private function run_validate( int $post_id, array $postarr, array $meta_map = array() ): array {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		// Distribution check falls back to carbon_get_post_meta; keep it empty so
		// no URL parsing runs (irrelevant to the HVD assertions).
		\WP_Mock::userFunction( 'carbon_get_post_meta' )->andReturn( '' );

		// get_field_value() falls back to get_post_meta when a key is absent
		// from the compact input — used to exercise the meta fallback path.
		\WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			function ( $id, $key, $single ) use ( $meta_map ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				return $meta_map[ $key ] ?? '';
			}
		);

		$method = new \ReflectionMethod( 'ODW_Validation', 'validate' );
		$method->setAccessible( true );

		return (array) $method->invoke( null, $post_id, $postarr );
	}

	/**
	 * Extracts the label column from structured validation errors.
	 *
	 * @param array<int, array{label: string}> $errors Structured errors.
	 * @return array<int, string>
	 */
	private function labels( array $errors ): array {
		return array_column( $errors, 'label' );
	}

	/**
	 * Builds a base post array with a title and the two required scalar fields
	 * filled, so only the HVD branch drives the assertions.
	 *
	 * @param array<string, mixed> $cf Extra compact-input overrides.
	 * @return array<string, mixed>
	 */
	private function make_postarr( array $cf ): array {
		return array(
			'post_title'                  => 'Test Dataset',
			'carbon_fields_compact_input' => array_merge(
				array(
					'_odw_description' => 'Some description',
					'_odw_publisher'   => 'Some publisher',
				),
				$cf
			),
		);
	}

	/**
	 * An additional distribution (repeater row) satisfies the distribution rule
	 * even when the primary access URL is empty.
	 */
	public function test_extra_distribution_counts_as_valid_distribution(): void {
		$this->load_classes();

		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing(
			static function ( $url, $component ) {
				return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			}
		);

		$errors = $this->run_validate(
			11,
			$this->make_postarr(
				array(
					'_odw_extra_distributions' => array(
						array(
							'_access_url' => 'https://example.org/extra.json',
							'_license'    => 'https://creativecommons.org/licenses/by/4.0/',
						),
					),
				)
			)
		);

		$labels = $this->labels( $errors );
		$this->assertNotContains( self::DIST_ERROR, $labels );
		$this->assertNotContains( self::LICENSE_ERROR, $labels );
	}

	/**
	 * A dataset whose only distribution is an uploaded media file must still
	 * carry a license.
	 */
	public function test_upload_only_distribution_requires_license(): void {
		$this->load_classes();

		$errors = $this->run_validate(
			12,
			$this->make_postarr( array() ),
			array( '_odw_file_id' => 7 )
		);

		$labels = $this->labels( $errors );
		$this->assertNotContains( self::DIST_ERROR, $labels );
		$this->assertContains( self::LICENSE_ERROR, $labels );
	}

	/**
	 * An extra distribution with "sonstige" license but no custom URI fails the
	 * license rule.
	 */
	public function test_extra_distribution_sonstige_without_custom_fails(): void {
		$this->load_classes();

		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing(
			static function ( $url, $component ) {
				return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			}
		);

		$errors = $this->run_validate(
			13,
			$this->make_postarr(
				array(
					'_odw_extra_distributions' => array(
						array(
							'_access_url' => 'https://example.org/extra.csv',
							'_license'    => 'sonstige',
						),
					),
				)
			)
		);

		$this->assertContains( self::LICENSE_ERROR, $this->labels( $errors ) );
	}

	/**
	 * HVD flagged with an empty category yields the HVD error.
	 */
	public function test_hvd_yes_without_category_adds_error(): void {
		$this->load_classes();

		$errors = $this->run_validate(
			1,
			$this->make_postarr(
				array(
					'_odw_is_hvd'       => 'yes',
					'_odw_hvd_category' => '',
				)
			)
		);

		$this->assertContains( self::HVD_ERROR, $this->labels( $errors ) );
	}

	/**
	 * HVD flagged with a category set does not add the HVD error.
	 */
	public function test_hvd_yes_with_category_passes(): void {
		$this->load_classes();

		$errors = $this->run_validate(
			1,
			$this->make_postarr(
				array(
					'_odw_is_hvd'       => 'yes',
					'_odw_hvd_category' => 'http://data.europa.eu/bna/c_ac64a52d',
				)
			)
		);

		$this->assertNotContains( self::HVD_ERROR, $this->labels( $errors ) );
	}

	/**
	 * Non-HVD datasets never require a category, even when empty.
	 */
	public function test_not_hvd_does_not_require_category(): void {
		$this->load_classes();

		$errors = $this->run_validate(
			1,
			$this->make_postarr(
				array(
					'_odw_is_hvd'       => '',
					'_odw_hvd_category' => '',
				)
			)
		);

		$this->assertNotContains( self::HVD_ERROR, $this->labels( $errors ) );
	}

	/**
	 * The HVD flag is read from saved meta when absent from the compact input
	 * (edit of an existing HVD dataset without re-touching the field).
	 */
	public function test_hvd_flag_falls_back_to_meta(): void {
		$this->load_classes();

		$errors = $this->run_validate(
			1,
			$this->make_postarr( array() ),
			array(
				'_odw_is_hvd'       => 'yes',
				'_odw_hvd_category' => '',
			)
		);

		$this->assertContains( self::HVD_ERROR, $this->labels( $errors ) );
	}

	/**
	 * Each structured error carries a tab number and DOM target so the admin
	 * notice can render a working "jump to field" link (B2).
	 */
	public function test_errors_carry_tab_and_target_metadata(): void {
		$this->load_classes();

		// Empty dataset: title, publisher, description, distribution and license all missing.
		$errors = $this->run_validate( 20, array( 'post_title' => '' ) );

		$by_label = array();
		foreach ( $errors as $error ) {
			$by_label[ $error['label'] ] = $error;
		}

		$this->assertArrayHasKey( 'Titel', $by_label );
		$this->assertSame( 0, $by_label['Titel']['tab'] );
		$this->assertSame( 'title', $by_label['Titel']['target'] );

		$this->assertArrayHasKey( 'Herausgebende Organisation', $by_label );
		$this->assertSame( 1, $by_label['Herausgebende Organisation']['tab'] );
		$this->assertSame( '_odw_publisher', $by_label['Herausgebende Organisation']['target'] );

		$this->assertArrayHasKey( self::DIST_ERROR, $by_label );
		$this->assertSame( 3, $by_label[ self::DIST_ERROR ]['tab'] );
		$this->assertSame( '_odw_access_url', $by_label[ self::DIST_ERROR ]['target'] );
	}
}
