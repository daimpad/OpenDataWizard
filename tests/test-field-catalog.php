<?php
/**
 * Feld-Katalog- und Generator-Tests.
 *
 * Prüft config/field-catalog.php auf Vollständigkeit und stellt sicher, dass
 * das committete docs/FELD-REFERENZ.md mit dem Generator übereinstimmt.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for the field catalog and the field-reference generator.
 */
class Test_ODW_Field_Catalog extends TestCase {

	/**
	 * Loaded catalog entries.
	 *
	 * @var array<int, array<string, string>>
	 */
	private array $catalog;

	/**
	 * Loads the catalog before each test.
	 */
	protected function setUp(): void {
		require_once ODW_PLUGIN_DIR . 'includes/class-field-reference.php';
		$this->catalog = ODW_Field_Reference::load_catalog();
	}

	/**
	 * Every entry carries all required keys with non-empty text.
	 */
	public function test_entries_are_complete(): void {
		$this->assertNotEmpty( $this->catalog );

		$text_keys  = array( 'q_dcat', 'q_human', 'desc_dcat', 'desc_human' );
		$meta_keys  = array( 'key', 'meta_key', 'dcat_prop', 'tab', 'tier', 'vocab' );
		$valid_tier = array( 'mandatory', 'recommended', 'optional', 'conditional' );

		foreach ( $this->catalog as $entry ) {
			$id = $entry['key'] ?? '(unknown)';

			foreach ( array_merge( $meta_keys, $text_keys ) as $required ) {
				$this->assertArrayHasKey( $required, $entry, "Missing '{$required}' in '{$id}'" );
			}

			foreach ( $text_keys as $text_key ) {
				$this->assertNotSame( '', trim( (string) $entry[ $text_key ] ), "Empty '{$text_key}' in '{$id}'" );
			}

			$this->assertContains( $entry['tier'], $valid_tier, "Invalid tier in '{$id}'" );
			$this->assertNotSame( '', trim( (string) $entry['meta_key'] ), "Empty meta_key in '{$id}'" );
			$this->assertStringContainsString( ':', $entry['dcat_prop'], "dcat_prop should be a prefixed term in '{$id}'" );
		}
	}

	/**
	 * Internal keys and meta keys are unique across the catalog.
	 */
	public function test_keys_and_meta_keys_are_unique(): void {
		$keys      = array_column( $this->catalog, 'key' );
		$meta_keys = array_column( $this->catalog, 'meta_key' );

		$this->assertSame( array_unique( $keys ), $keys, 'Duplicate field key in catalog' );
		$this->assertSame( array_unique( $meta_keys ), $meta_keys, 'Duplicate meta_key in catalog' );
	}

	/**
	 * Every data field in the wizard form has a catalog entry and vice versa.
	 *
	 * Guards against drift: adding or removing a form field without updating
	 * config/field-catalog.php fails this test.
	 */
	public function test_catalog_covers_all_form_fields(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads local source, not a remote request.
		$source = (string) file_get_contents( ODW_PLUGIN_DIR . 'includes/class-fields.php' );

		$this->assertNotSame( '', $source, 'class-fields.php could not be read' );

		// Collect the internal keys of all scalar data fields
		// (text/textarea/select/multiselect/date).
		preg_match_all(
			"/Field::make\\(\\s*'(?:text|textarea|select|multiselect|date)',\\s*'odw_([a-z_]+)'/",
			$source,
			$matches
		);
		$form_keys = array_values( array_unique( $matches[1] ) );
		sort( $form_keys );

		$catalog_keys = array_column( $this->catalog, 'key' );
		sort( $catalog_keys );

		// Einträge mit 'auto' => true beschreiben Eigenschaften, die das Plugin
		// selbst befüllt (z. B. dct:modified). Sie gehören in die Feld-Referenz,
		// haben aber bewusst kein Formularfeld. Der Schutz vor Drift bleibt:
		// Das Flag muss pro Eintrag ausdrücklich gesetzt werden, und die
		// Gegenrichtung — jedes Formularfeld braucht einen Katalogeintrag —
		// gilt weiterhin ausnahmslos.
		$auto_keys = array_column(
			array_filter(
				$this->catalog,
				static function ( array $entry ): bool {
					return ! empty( $entry['auto'] );
				}
			),
			'key'
		);

		$missing_in_catalog = array_diff( $form_keys, $catalog_keys );
		$missing_in_form    = array_diff( $catalog_keys, $form_keys, $auto_keys );

		$this->assertSame(
			array(),
			array_values( $missing_in_catalog ),
			'Form fields without a catalog entry: ' . implode( ', ', $missing_in_catalog )
		);
		$this->assertSame(
			array(),
			array_values( $missing_in_form ),
			'Catalog entries without a form field: ' . implode( ', ', $missing_in_form )
		);
	}

	/**
	 * No complex sub-field uses a name Carbon Fields reserves.
	 *
	 * Carbon Fields rejects "value" (and "_type") as complex sub-field names,
	 * which silently breaks the repeater's "Add entry" button. Guard against
	 * re-introducing such a name.
	 */
	public function test_no_reserved_complex_subfield_names(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads local source, not a remote request.
		$source = (string) file_get_contents( ODW_PLUGIN_DIR . 'includes/class-fields.php' );

		$this->assertDoesNotMatchRegularExpression(
			"/Field::make\\(\\s*'[a-z]+',\\s*'(value|_type)'/",
			$source,
			'Carbon Fields reserves "value"/"_type" as complex sub-field names — rename the field.'
		);
	}

	/**
	 * Terminology glossary (B5): the recurring concepts use one consistent term —
	 * "Thema" (not "Kategorie") for dcat:theme and "Schlagworte/Schlagwörter"
	 * (not "Stichworte/Schlüsselwörter") for dcat:keyword.
	 */
	public function test_terminology_glossary_is_consistent(): void {
		$by_key = array();
		foreach ( $this->catalog as $entry ) {
			$by_key[ $entry['key'] ] = $entry;
		}

		$this->assertArrayHasKey( 'theme', $by_key );
		// „Thema" bleibt der Begriff — seit das Feld eine Mehrfachauswahl ist,
		// steht er dort im Plural („Welchen Themen …"). Verboten bleibt der
		// Zweitbegriff „Kategorie".
		$this->assertMatchesRegularExpression( '/Them(a|en)/', $by_key['theme']['q_human'] );
		$this->assertStringNotContainsString( 'Kategorie', $by_key['theme']['q_human'] );

		$this->assertArrayHasKey( 'keywords', $by_key );
		$this->assertStringContainsString( 'Schlagworten', $by_key['keywords']['q_human'] );
		$this->assertStringNotContainsString( 'Stichwort', $by_key['keywords']['q_human'] );
		$this->assertStringNotContainsString( 'Stichwör', $by_key['keywords']['desc_human'] );
	}

	/**
	 * Writing creates a missing target directory.
	 *
	 * A plugin installed from the release ZIP has no docs/ directory (the build
	 * allowlist ships runtime files only), so `wp open-data-wizard docs` would
	 * otherwise fail there with a misleading "could not be written" error.
	 */
	public function test_write_creates_missing_directory(): void {
		$base   = sys_get_temp_dir() . '/odw-write-' . uniqid();
		$target = $base . '/docs/FELD-REFERENZ.md';

		$bytes = ODW_Field_Reference::write( $target );

		$this->assertGreaterThan( 0, $bytes, 'write() should report written bytes' );
		$this->assertFileExists( $target );

		// Aufräumen des Temp-Verzeichnisses. WP_Filesystem/wp_delete_file() stehen im
		// Unit-Test (kein WordPress geladen) nicht zur Verfügung, daher direkte
		// PHP-Aufrufe.
		// phpcs:disable WordPress.WP.AlternativeFunctions -- test teardown, no WP runtime available.
		unlink( $target );
		rmdir( dirname( $target ) );
		rmdir( $base );
		// phpcs:enable WordPress.WP.AlternativeFunctions
	}

	/**
	 * The committed docs/FELD-REFERENZ.md matches the generated output.
	 *
	 * Fails when the catalog changed but the doc was not regenerated
	 * (`php bin/generate-field-reference.php`).
	 */
	public function test_generated_doc_is_in_sync(): void {
		$path = ODW_PLUGIN_DIR . 'docs/FELD-REFERENZ.md';
		$this->assertFileExists( $path, 'docs/FELD-REFERENZ.md is missing — run php bin/generate-field-reference.php' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local test fixture, not a remote request.
		$committed = (string) file_get_contents( $path );
		$generated = ODW_Field_Reference::build();

		$this->assertSame(
			$generated,
			$committed,
			'docs/FELD-REFERENZ.md is out of date — run: php bin/generate-field-reference.php'
		);
	}

	/**
	 * Every entry carries an explicit multiplicity.
	 *
	 * Die Angabe steuert seit v2.41.0 die Merkmalsliste im „Mehr erfahren"-Panel.
	 * Fehlt sie, bleibt die Zeile dort still leer — deshalb hier eine harte Zusage.
	 */
	public function test_every_entry_has_a_cardinality(): void {
		foreach ( $this->catalog as $field ) {
			$this->assertMatchesRegularExpression(
				'/^[01]\.\.[1n]$/',
				(string) ( $field['cardinality'] ?? '' ),
				'Feld ' . $field['key'] . ' hat keine gültige Multiplizität.'
			);
		}
	}

	/**
	 * The structured multiplicity agrees with the one stated in the prose.
	 *
	 * Beide Angaben stehen im selben Eintrag und werden von Hand gepflegt. Ohne
	 * diese Prüfung laufen sie auseinander, sobald jemand nur eine der beiden
	 * anfasst — und das Panel zeigt dann etwas anderes als die Definition darunter.
	 */
	public function test_cardinality_matches_the_prose(): void {
		foreach ( $this->catalog as $field ) {
			$matched = preg_match( '/Multiplizität\s+([01]\.\.[1n])/u', (string) $field['desc_dcat'], $m );
			$this->assertSame( 1, $matched, 'Feld ' . $field['key'] . ' nennt keine Multiplizität im Definitionstext.' );
			$this->assertSame(
				(string) $field['cardinality'],
				$m[1],
				'Feld ' . $field['key'] . ': strukturierte Multiplizität und Definitionstext weichen ab.'
			);
		}
	}

	/**
	 * Every entry names the profile class it belongs to.
	 *
	 * Die Klasse steuert den Link auf den passenden Abschnitt der
	 * DCAT-AP.de-Spezifikation. Fehlt sie, verschwindet der Link stillschweigend.
	 */
	public function test_every_entry_has_an_entity(): void {
		foreach ( $this->catalog as $field ) {
			$this->assertContains(
				(string) ( $field['entity'] ?? '' ),
				array( 'dataset', 'distribution', 'catalog' ),
				'Feld ' . $field['key'] . ' hat keine gültige Profil-Klasse.'
			);
		}
	}

	/**
	 * Each entity resolves to a spec URL on the official domain.
	 */
	public function test_entity_resolves_to_a_spec_url(): void {
		foreach ( $this->catalog as $field ) {
			$url = ODW_Field_Reference::spec_url( (string) $field['entity'] );
			$this->assertStringStartsWith(
				'https://www.dcat-ap.de/def/dcatde/3.0/spec/#',
				$url,
				'Feld ' . $field['key'] . ' liefert keine Spezifikations-URL.'
			);
		}
	}

	/**
	 * Distribution-level fields are exactly those the distribution node builds.
	 *
	 * Die Zuordnung stammt aus odw_build_distribution_node(); läuft sie
	 * auseinander, verweist der Link auf den falschen Abschnitt.
	 */
	public function test_distribution_fields_match_the_builder(): void {
		$erwartet = array(
			'access_url',
			'attribution_text',
			'availability',
			'byte_size',
			'dist_description',
			'dist_rights',
			'dist_title',
			'download_url',
			'format',
			'license',
			'license_custom',
			'media_type',
		);

		$tatsaechlich = array();
		foreach ( $this->catalog as $field ) {
			if ( 'distribution' === ( $field['entity'] ?? '' ) ) {
				$tatsaechlich[] = (string) $field['key'];
			}
		}
		sort( $tatsaechlich );

		$this->assertSame( $erwartet, $tatsaechlich );
	}
}
