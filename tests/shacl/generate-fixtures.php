#!/usr/bin/env php
<?php
/**
 * SHACL fixture generator — builds test datasets without WordPress.
 *
 * Generates minimal and maximal dataset fixtures plus a catalog wrapper,
 * each in both JSON-LD and Turtle. Run from the project root:
 *
 *   php tests/shacl/generate-fixtures.php
 *
 * Output lands in build/shacl/ (gitignored).
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

// Bootstrap test environment.
require_once __DIR__ . '/../bootstrap.php';

// ----------------------------------------------------------
// WordPress function stubs (plain functions, no WP_Mock)
// ----------------------------------------------------------

if ( ! function_exists( 'get_post' ) ) {
	/**
	 * Mock get_post — returns a stdClass with the required properties.
	 *
	 * @param int $post_id Post ID.
	 * @return object|null
	 */
	function get_post( int $post_id ): ?object {
		static $posts = array();

		if ( ! isset( $posts[ $post_id ] ) ) {
			$post              = new stdClass();
			$post->ID          = $post_id;
			$post->post_type   = 'odw_dataset';
			$post->post_title  = "Test Dataset #{$post_id}";
			$post->post_status = 'publish';
			$posts[ $post_id ] = $post;
		}

		return $posts[ $post_id ];
	}
}

if ( ! function_exists( 'carbon_get_post_meta' ) ) {
	/**
	 * Mock carbon_get_post_meta — returns fixture data.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return mixed
	 */
	function carbon_get_post_meta( int $post_id, string $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $fixture_meta;
		return $fixture_meta[ $key ] ?? '';
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	/**
	 * Mock get_post_meta — returns fixture data.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Whether to return a single value.
	 * @return mixed
	 */
	function get_post_meta( int $post_id, string $key, bool $single = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $fixture_meta;
		return $fixture_meta[ $key ] ?? '';
	}
}

if ( ! function_exists( 'rest_url' ) ) {
	/**
	 * Mock rest_url.
	 *
	 * @param string $path Path.
	 * @return string
	 */
	function rest_url( string $path = '' ): string {
		return 'https://example.org/wp-json/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * Mock esc_url_raw — strips dangerous schemes.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	function esc_url_raw( string $url ): string {
		return preg_match( '#^(javascript|data|vbscript):#i', $url ) ? '' : $url;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Mock apply_filters — returns the value unchanged.
	 *
	 * @param string $tag   Filter tag.
	 * @param mixed  $value Value.
	 * @return mixed
	 */
	function apply_filters( string $tag, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return $value;
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	/**
	 * Mock get_bloginfo.
	 *
	 * @param string $show What to show.
	 * @return string
	 */
	function get_bloginfo( string $show = '' ): string {
		if ( 'name' === $show ) {
			return 'Open Data Wizard Test Site';
		}
		if ( 'description' === $show ) {
			return 'Test catalog for SHACL validation';
		}
		return '';
	}
}

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Mock home_url.
	 *
	 * @param string $path Path.
	 * @return string
	 */
	function home_url( string $path = '' ): string {
		return 'https://example.org' . $path;
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Mock translation function — returns the text unchanged.
	 *
	 * @param string $text   Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function __( string $text, string $domain = 'default' ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return $text;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Mock get_transient — always returns false (no cache).
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	function get_transient( string $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Mock set_transient — no-op.
	 *
	 * @param string $key    Key.
	 * @param mixed  $value  Value.
	 * @param int    $expiry Expiry.
	 * @return bool
	 */
	function set_transient( string $key, $value, int $expiry = 0 ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

// Define DAY_IN_SECONDS if not already defined (used by vocabulary loading).
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

// ----------------------------------------------------------
// Load required classes
// ----------------------------------------------------------

require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
require_once ODW_PLUGIN_DIR . 'includes/class-rdf.php';

// ----------------------------------------------------------
// Fixture data definitions
// ----------------------------------------------------------

/**
 * Returns the minimal fixture data (required fields only).
 *
 * @return array<string, mixed>
 */
function fixture_minimal(): array {
	return array(
		'odw_publisher'   => 'Ministerium für Daten',
		'odw_description' => 'Minimaler Datensatz mit nur den Pflichtfeldern.',
		'odw_access_url'  => 'https://example.org/minimal.csv',
		'odw_license'     => 'https://creativecommons.org/publicdomain/zero/1.0/',
	);
}

/**
 * Returns the maximal fixture data (all fields populated).
 *
 * @return array<string, mixed>
 */
function fixture_maximal(): array {
	return array(
		// Required.
		'odw_publisher'   => 'Bundesamt für Statistik',
		'odw_description' => 'Maximaler Datensatz mit allen verfügbaren Feldern, einschließlich HVD, dcatde:, Mehrsprachigkeit und Mehrfach-Distributionen.',
		'odw_access_url'  => 'https://example.org/maximal.csv',
		'odw_license'     => 'https://creativecommons.org/licenses/by/4.0/',

		// Theme + additional theme.
		'odw_theme'     => 'http://publications.europa.eu/resource/authority/data-theme/SOCI',
		'odw_theme_uri' => 'Energie', // Resolved to EU URI.

		// Language + keywords.
		'odw_language' => 'http://publications.europa.eu/resource/authority/language/DEU',
		'odw_keywords' => "Soziales\nArbeit\nBeschäftigung",

		// Dates.
		'odw_issued'               => '2024-01-15',
		'_odw_modified'            => '2024-06-20',
		'odw_accrual_periodicity'  => 'http://publications.europa.eu/resource/authority/frequency/ANNUAL',

		// Spatial + Temporal.
		'odw_spatial'        => 'Bayern',
		'odw_temporal_start' => '2023-01-01',
		'odw_temporal_end'   => '2023-12-31',

		// Contact.
		'odw_contact_name'  => 'Open Data Team',
		'odw_contact_email' => 'opendata@example.org',
		'odw_contact_url'   => 'https://example.org/contact',

		// Landing page.
		'odw_landing_page' => 'https://example.org/datasets/maximal',

		// CESSDA topic.
		'odw_cessda_topic' => 'https://vocabularies.cessda.eu/urn/urn:ddi:int.cessda.cv:TopicClassification:4.2.3:de:1.0',

		// HVD.
		'odw_is_hvd'       => 'yes',
		'odw_hvd_category' => 'http://data.europa.eu/bna/c_ac64a52d',

		// DCAT-AP.de.
		'odw_contributor_id'          => 'http://dcat-ap.de/def/contributors/openDataBayern',
		'odw_originator_name'         => 'Statistisches Landesamt Bayern',
		'odw_originator_email'        => 'urheber@statistik-bayern.de',
		'odw_maintainer_name'         => 'Datenredaktion',
		'odw_maintainer_email'        => 'redaktion@example.org',
		'odw_political_geocoding_uri' => 'http://dcat-ap.de/def/politicalGeocoding/regionalKey/09',
		'odw_political_geocoding_level' => 'http://dcat-ap.de/def/politicalGeocoding/Level/state',
		'odw_legal_basis'             => '§ 12a EGovG',
		'odw_quality_process_uri'     => 'https://example.org/qa-process',
		'odw_geocoding_description'   => 'Gesamte Landesfläche Bayern',

		// Additional optional fields.
		'odw_identifier'          => 'urn:uuid:12345678-1234-1234-1234-123456789abc',
		'odw_type'                => 'http://purl.org/dc/dcmitype/Dataset',
		'odw_creator_name'        => 'Forschungsgruppe Soziales',
		'odw_creator_email'       => 'forschung@example.org',
		'odw_version'             => '2.1',
		'odw_version_notes'       => 'Zweite Hauptversion mit erweiterten Sozialstatistiken',
		'odw_spatial_resolution'  => '1000',
		'odw_temporal_resolution' => 'P1M',
		'odw_conforms_to'         => 'https://schema.org/Dataset',
		'odw_provenance'          => 'Daten aus amtlichen Erhebungen gemäß Bundesstatistikgesetz',
		'odw_access_rights'       => 'http://publications.europa.eu/resource/authority/access-right/PUBLIC',
		'odw_availability'        => 'http://publications.europa.eu/resource/authority/planned-availability/STABLE',

		// Distribution fields.
		'odw_format'           => 'CSV',
		'odw_byte_size'        => '1048576',
		'odw_attribution_text' => 'Daten: Bundesamt für Statistik, CC BY 4.0',
		'odw_dist_title'       => 'Sozialdaten 2023 (CSV)',
		'odw_dist_description' => 'Vollständige Sozialdaten für das Jahr 2023 im CSV-Format',
		'odw_download_url'     => 'https://example.org/maximal-download.csv',
		'odw_media_type'       => 'https://www.iana.org/assignments/media-types/text/csv',
		'odw_dist_rights'      => 'https://creativecommons.org/licenses/by/4.0/',

		// Multi-distribution (Phase E).
		'odw_extra_distributions' => array(
			array(
				'access_url'   => 'https://example.org/maximal.json',
				'format'       => 'JSON',
				'byte_size'    => '2097152',
				'license'      => 'https://creativecommons.org/licenses/by/4.0/',
				'title'        => 'Sozialdaten 2023 (JSON)',
				'description'  => 'Vollständige Sozialdaten als JSON',
				'download_url' => 'https://example.org/maximal-download.json',
				'media_type'   => 'application/json',
			),
			array(
				'access_url' => 'https://example.org/maximal.xml',
				'format'     => 'XML',
				'byte_size'  => '3145728',
				'license'    => 'https://creativecommons.org/licenses/by/4.0/',
				'title'      => 'Sozialdaten 2023 (XML)',
			),
		),

		// Multilingual (Phase D) — title/description/keyword translations.
		'odw_title_translations' => array(
			array(
				'language' => 'http://publications.europa.eu/resource/authority/language/ENG',
				'content'  => 'Social Statistics 2023',
			),
			array(
				'language' => 'http://publications.europa.eu/resource/authority/language/FRA',
				'content'  => 'Statistiques sociales 2023',
			),
		),
		'odw_description_translations' => array(
			array(
				'language' => 'http://publications.europa.eu/resource/authority/language/ENG',
				'content'  => 'Maximum dataset with all available fields, including HVD, dcatde:, multilingual support, and multiple distributions.',
			),
		),
		'odw_keyword_translations' => array(
			array(
				'language' => 'http://publications.europa.eu/resource/authority/language/ENG',
				'keywords' => "Social\nWork\nEmployment",
			),
		),
	);
}

/**
 * Builds a catalog document wrapping the given datasets.
 *
 * Mirrors ODW_Rest_API::build_catalog_document() logic but runs without WP.
 *
 * @param array<int, array<string, mixed>> $datasets Dataset JSON-LD nodes.
 * @return array<string, mixed>
 */
function build_catalog_fixture( array $datasets ): array {
	$catalog_title       = get_bloginfo( 'name' ) . ' — Datenkatalog';
	$catalog_description = get_bloginfo( 'description' );

	if ( '' === trim( $catalog_description ) ) {
		$catalog_description = sprintf(
			'Offene Daten, bereitgestellt von %s.',
			get_bloginfo( 'name' )
		);
	}

	// Mirror the @context from class-rest-api.php.
	$context = array(
		'dcat'    => 'http://www.w3.org/ns/dcat#',
		'dct'     => 'http://purl.org/dc/terms/',
		'dcatde'  => 'http://dcat-ap.de/def/dcatde/',
		'dcatap'  => 'http://data.europa.eu/r5r/',
		'foaf'    => 'http://xmlns.com/foaf/0.1/',
		'vcard'   => 'http://www.w3.org/2006/vcard/ns#',
		'xsd'     => 'http://www.w3.org/2001/XMLSchema#',
		'skos'    => 'http://www.w3.org/2004/02/skos/core#',
		'rdfs'    => 'http://www.w3.org/2000/01/rdf-schema#',
		'owl'     => 'http://www.w3.org/2002/07/owl#',
		'adms'    => 'http://www.w3.org/ns/adms#',
		'locn'    => 'http://www.w3.org/ns/locn#',
		'prov'    => 'http://www.w3.org/ns/prov#',
		'odrl'    => 'http://www.w3.org/ns/odrl/2/',
		'spdx'    => 'http://spdx.org/rdf/terms#',
	);

	$catalog = array(
		'@context'      => $context,
		'@id'           => rest_url( 'datenatlas/v1/catalog' ),
		'@type'         => 'dcat:Catalog',
		'dct:title'     => array(
			'@value'    => $catalog_title,
			'@language' => 'de',
		),
		'dct:publisher' => array(
			'@type'     => 'foaf:Organization',
			'foaf:name' => get_bloginfo( 'name' ),
		),
		'foaf:homepage' => array( '@id' => home_url( '/' ) ),
		'dcat:dataset'  => $datasets,
	);

	if ( '' !== $catalog_description ) {
		$catalog['dct:description'] = array(
			'@value'    => $catalog_description,
			'@language' => 'de',
		);
	}

	return $catalog;
}

// ----------------------------------------------------------
// Generator functions
// ----------------------------------------------------------

/**
 * Generates a fixture and writes both JSON-LD and Turtle.
 *
 * @param string               $name Fixture name (without extension).
 * @param array<string, mixed> $meta Fixture meta data.
 * @param bool                 $is_catalog Whether this is a catalog document.
 * @return void
 */
function generate_fixture( string $name, array $meta, bool $is_catalog = false ): void {
	global $fixture_meta;
	$fixture_meta = $meta;

	if ( $is_catalog ) {
		// Build datasets first.
		$datasets = array();
		if ( ! empty( $meta['datasets'] ) ) {
			foreach ( $meta['datasets'] as $idx => $dataset_meta ) {
				$fixture_meta = $dataset_meta;
				$dataset      = odw_build_dataset_jsonld( 100 + $idx );
				if ( null !== $dataset ) {
					$datasets[] = $dataset;
				}
			}
		}
		$doc = build_catalog_fixture( $datasets );
	} else {
		$doc = odw_build_dataset_jsonld( 1 );
	}

	if ( null === $doc ) {
		echo "SKIP: {$name} — odw_build_dataset_jsonld() returned null\n";
		return;
	}

	// Write JSON-LD.
	$jsonld_path = __DIR__ . "/../../build/shacl/{$name}.jsonld";
	file_put_contents(
		$jsonld_path,
		json_encode( $doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n"
	);
	echo "✓ {$jsonld_path}\n";

	// Write Turtle.
	$turtle = ODW_Rdf::to_turtle( $doc );
	if ( null === $turtle ) {
		echo "WARN: {$name} — ODW_Rdf::to_turtle() returned null\n";
		return;
	}

	$turtle_path = __DIR__ . "/../../build/shacl/{$name}.ttl";
	file_put_contents( $turtle_path, $turtle );
	echo "✓ {$turtle_path}\n";
}

// ----------------------------------------------------------
// Main
// ----------------------------------------------------------

echo "Generating SHACL validation fixtures...\n\n";

// make sure build/schacl exists, will warn if it exists, create if not
mkdir(__DIR__ . "/../../build/shacl", recursive:TRUE);

// Minimal dataset.
generate_fixture( 'dataset-minimal', fixture_minimal() );

// Maximal dataset.
generate_fixture( 'dataset-maximal', fixture_maximal() );

// Catalog wrapping both.
generate_fixture(
	'catalog',
	array(
		'datasets' => array(
			fixture_minimal(),
			fixture_maximal(),
		),
	),
	true
);

echo "\nDone. Fixtures written to build/shacl/\n";
