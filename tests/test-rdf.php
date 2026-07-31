<?php
/**
 * Tests für den JSON-LD → Turtle Serializer (ODW_Rdf).
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Rdf::to_turtle().
 */
class Test_ODW_Rdf extends TestCase {

	/**
	 * Loads the class under test once.
	 */
	protected function setUp(): void {
		if ( ! class_exists( 'ODW_Rdf' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-rdf.php';
		}
	}

	/**
	 * A representative DCAT-AP.de catalog document.
	 *
	 * @return array<string, mixed>
	 */
	private function sample_catalog(): array {
		return array(
			'@context'      => array(
				'dcat' => 'http://www.w3.org/ns/dcat#',
				'dct'  => 'http://purl.org/dc/terms/',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'xsd'  => 'http://www.w3.org/2001/XMLSchema#',
			),
			'@id'           => 'https://example.org/wp-json/datenatlas/v1/catalog',
			'@type'         => 'dcat:Catalog',
			'dct:title'     => array(
				'@value'    => 'Offene Daten von Verein XY',
				'@language' => 'de',
			),
			'dct:publisher' => array(
				'@type'     => 'foaf:Organization',
				'foaf:name' => 'Verein XY e.V.',
			),
			'dcat:dataset'  => array(
				array(
					'@id'               => 'https://example.org/dataset/1',
					'@type'             => 'dcat:Dataset',
					'dct:title'         => array(
						'@value'    => 'Mitglieder 2025',
						'@language' => 'de',
					),
					'dct:modified'      => array(
						'@value' => '2025-03-01',
						'@type'  => 'xsd:date',
					),
					'dcat:theme'        => array(
						array( '@id' => 'http://publications.europa.eu/resource/authority/data-theme/SOCI' ),
					),
					'dcat:distribution' => array(
						'@type'          => 'dcat:Distribution',
						'dcat:accessURL' => array( '@id' => 'https://example.org/f.csv' ),
					),
				),
			),
		);
	}

	/**
	 * Prefix declarations are emitted from the @context.
	 */
	public function test_emits_prefix_declarations(): void {
		$ttl = ODW_Rdf::to_turtle( $this->sample_catalog() );

		$this->assertStringContainsString( '@prefix dcat: <http://www.w3.org/ns/dcat#> .', $ttl );
		$this->assertStringContainsString( '@prefix foaf: <http://xmlns.com/foaf/0.1/> .', $ttl );
	}

	/**
	 * The catalog is a named subject with a language-tagged title.
	 */
	public function test_named_subject_with_language_literal(): void {
		$ttl = ODW_Rdf::to_turtle( $this->sample_catalog() );

		$this->assertStringContainsString( '<https://example.org/wp-json/datenatlas/v1/catalog> a dcat:Catalog', $ttl );
		$this->assertStringContainsString( 'dct:title "Offene Daten von Verein XY"@de', $ttl );
	}

	/**
	 * A node without @id (publisher) becomes an inline blank node.
	 */
	public function test_node_without_id_becomes_blank_node(): void {
		$ttl = ODW_Rdf::to_turtle( $this->sample_catalog() );

		$this->assertStringContainsString( '[ a foaf:Organization ; foaf:name "Verein XY e.V." ]', $ttl );
	}

	/**
	 * A nested node with @id (the dataset) is referenced and rendered as its own subject.
	 */
	public function test_nested_node_with_id_becomes_own_subject(): void {
		$ttl = ODW_Rdf::to_turtle( $this->sample_catalog() );

		// Referenced from the catalog.
		$this->assertStringContainsString( 'dcat:dataset <https://example.org/dataset/1>', $ttl );
		// And emitted as its own subject.
		$this->assertStringContainsString( '<https://example.org/dataset/1> a dcat:Dataset', $ttl );
	}

	/**
	 * Typed literals and IRI references serialise correctly.
	 */
	public function test_typed_literal_and_iri_reference(): void {
		$ttl = ODW_Rdf::to_turtle( $this->sample_catalog() );

		$this->assertStringContainsString( 'dct:modified "2025-03-01"^^xsd:date', $ttl );
		$this->assertStringContainsString( 'dcat:theme <http://publications.europa.eu/resource/authority/data-theme/SOCI>', $ttl );
		$this->assertStringContainsString( 'dcat:accessURL <https://example.org/f.csv>', $ttl );
	}

	/**
	 * String literals with quotes are escaped.
	 */
	public function test_string_literal_escaping(): void {
		$doc = array(
			'@context'  => array( 'dct' => 'http://purl.org/dc/terms/' ),
			'@id'       => 'https://example.org/x',
			'@type'     => 'dct:BibliographicResource',
			'dct:title' => 'Ein "Zitat" mit Backslash \\ und Zeile',
		);

		$ttl = ODW_Rdf::to_turtle( $doc );

		$this->assertStringContainsString( 'dct:title "Ein \\"Zitat\\" mit Backslash \\\\ und Zeile"', $ttl );
	}

	/**
	 * An empty list must not emit a predicate without an object.
	 *
	 * A catalogue without published datasets carries `dcat:dataset => []`. Without
	 * this guard the serialiser produced "dcat:dataset ." — invalid Turtle that
	 * every RDF parser rejects, i.e. a fresh installation would serve a broken
	 * harvest document.
	 */
	public function test_empty_list_does_not_emit_dangling_predicate(): void {
		$doc = array(
			'@context'     => array( 'dcat' => 'http://www.w3.org/ns/dcat#' ),
			'@id'          => 'https://example.org/catalog',
			'@type'        => 'dcat:Catalog',
			'dcat:dataset' => array(),
		);

		$ttl = ODW_Rdf::to_turtle( $doc );

		$this->assertStringNotContainsString( 'dcat:dataset', $ttl, 'the empty predicate must be dropped entirely' );
		$this->assertStringContainsString( '<https://example.org/catalog> a dcat:Catalog .', $ttl );
	}

	/**
	 * Multiple @type values render as a comma-separated list.
	 */
	public function test_multiple_types(): void {
		$doc = array(
			'@context' => array( 'foaf' => 'http://xmlns.com/foaf/0.1/' ),
			'@id'      => 'https://example.org/agent',
			'@type'    => array( 'foaf:Agent', 'foaf:Organization' ),
		);

		$ttl = ODW_Rdf::to_turtle( $doc );

		$this->assertStringContainsString( '<https://example.org/agent> a foaf:Agent, foaf:Organization .', $ttl );
	}
}
