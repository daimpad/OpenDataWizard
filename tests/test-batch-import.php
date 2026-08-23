<?php
/**
 * Tests für ODW_Batch_Import — Parsing, Validierung und Anlage der Datensätze.
 *
 * Der Batch-Import ist die einzige Stelle im Plugin, an der fremde Dateien auf
 * den Code treffen: CSV und JSON kommen aus Excel, aus fremden Katalogen oder
 * von Hand. Die Tests decken deshalb vor allem die Ränder ab — BOM aus
 * Excel-Exporten, verrutschte Spalten, ungültige URLs, Formel-Injektion.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Batch_Import.
 */
class Test_ODW_Batch_Import extends TestCase {

	/** CSV header line containing all required columns. */
	private const CSV_HEADER = 'title,publisher,description,access_url,license';

	/** A complete, valid CSV data row matching CSV_HEADER. */
	private const CSV_ROW = 'Baumkataster,Stadt Musterstadt,Alle Straßenbäume,https://example.org/baeume.csv,cc-by';

	/**
	 * Temporary fixture files created during a test, removed in tearDown().
	 *
	 * @var string[]
	 */
	private array $fixtures = array();

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Tear down WP_Mock and remove fixture files after each test.
	 */
	protected function tearDown(): void {
		foreach ( $this->fixtures as $path ) {
			if ( file_exists( $path ) ) {
				unlink( $path );
			}
		}
		$this->fixtures = array();

		\WP_Mock::tearDown();
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Loads the classes under test once.
	 */
	private function load_classes(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );

		if ( ! class_exists( 'ODW_Fields' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
		}
		if ( ! class_exists( 'ODW_Batch_Import' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-batch-import.php';
		}
	}

	/**
	 * Registers the WordPress functions every parse path touches.
	 *
	 * `wp_parse_url` gets the real behaviour: die Schema-Prüfung in
	 * is_valid_url() ist der Grund, warum `javascript:` nicht durchkommt — ein
	 * Stub, der irgendetwas zurückgibt, würde genau das nicht mehr prüfen.
	 */
	private function mock_parse_environment(): void {
		$this->load_classes();

		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing(
			function ( $url, $component = -1 ) {
				return parse_url( (string) $url, (int) $component );
			}
		);
	}

	/**
	 * Writes a fixture file and returns its path.
	 *
	 * @param string $content   File content.
	 * @param string $extension File extension without the dot.
	 * @return string Absolute path to the fixture.
	 */
	private function fixture( string $content, string $extension = 'csv' ): string {
		$path = sys_get_temp_dir() . '/odw-import-' . uniqid( '', true ) . '.' . $extension;
		file_put_contents( $path, $content );
		$this->fixtures[] = $path;

		return $path;
	}

	/**
	 * Builds a CSV fixture from the standard header plus the given rows.
	 *
	 * @param string[] $rows Data rows (already comma-separated).
	 * @return string Absolute path to the fixture.
	 */
	private function csv_fixture( array $rows ): string {
		return $this->fixture( self::CSV_HEADER . "\n" . implode( "\n", $rows ) . "\n" );
	}

	/**
	 * Invokes a private static method of ODW_Batch_Import.
	 *
	 * @param string       $name Method name.
	 * @param array<mixed> $args Arguments.
	 * @return mixed Return value of the method.
	 */
	private function call_private( string $name, array $args ) {
		$method = new \ReflectionMethod( 'ODW_Batch_Import', $name );
		$method->setAccessible( true );

		return $method->invokeArgs( null, $args );
	}

	// -----------------------------------------------------------------------
	// parse_file() — Formaterkennung
	// -----------------------------------------------------------------------

	/**
	 * A missing file is reported instead of raising a PHP warning.
	 */
	public function test_parse_file_reports_missing_file(): void {
		$this->mock_parse_environment();

		$result = ODW_Batch_Import::parse_file( '/nicht/vorhanden.csv' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 0, $result['count'] );
		$this->assertStringContainsString( 'nicht gefunden', (string) $result['error'] );
	}

	/**
	 * Only CSV and JSON are accepted; anything else is rejected by extension.
	 */
	public function test_parse_file_rejects_unsupported_extension(): void {
		$this->mock_parse_environment();

		$path   = $this->fixture( self::CSV_HEADER . "\n" . self::CSV_ROW . "\n", 'txt' );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'nicht unterstützt', (string) $result['error'] );
	}

	/**
	 * Uploads arrive as temporary files without a meaningful extension. The
	 * original client filename decides the format in that case.
	 */
	public function test_parse_file_uses_original_name_for_extension(): void {
		$this->mock_parse_environment();

		$path   = $this->fixture( self::CSV_HEADER . "\n" . self::CSV_ROW . "\n", 'tmp' );
		$result = ODW_Batch_Import::parse_file( $path, 'datensaetze.csv' );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
	}

	// -----------------------------------------------------------------------
	// CSV
	// -----------------------------------------------------------------------

	/**
	 * A well-formed CSV row ends up as one record with trimmed values.
	 */
	public function test_csv_parses_valid_row(): void {
		$this->mock_parse_environment();

		$path   = $this->csv_fixture( array( '  Baumkataster  ,Stadt Musterstadt,Alle Straßenbäume,https://example.org/baeume.csv,cc-by' ) );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
		$this->assertSame( 'Baumkataster', $result['data'][0]['title'] );
		$this->assertSame( 'cc-by', $result['data'][0]['license'] );
	}

	/**
	 * Excel writes a UTF-8 BOM in front of the first header cell. Without
	 * stripping it the column would be called "\xEF\xBB\xBFtitle" and every row
	 * would fail with "Pflichtfeld title fehlt" — an error nobody can explain
	 * by looking at the file.
	 */
	public function test_csv_strips_utf8_bom_from_first_header(): void {
		$this->mock_parse_environment();

		$path   = $this->fixture( "\xEF\xBB\xBF" . self::CSV_HEADER . "\n" . self::CSV_ROW . "\n" );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
		$this->assertArrayHasKey( 'title', $result['data'][0] );
	}

	/**
	 * A row with a wrong number of columns is skipped with a numbered error —
	 * the remaining rows are still imported.
	 */
	public function test_csv_skips_row_with_column_mismatch(): void {
		$this->mock_parse_environment();

		$path = $this->csv_fixture(
			array(
				'Zu wenig,Spalten',
				self::CSV_ROW,
			)
		);

		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
		$this->assertCount( 1, $result['errors'] );
		$this->assertStringContainsString( 'Zeile 1', $result['errors'][0] );
	}

	/**
	 * A trailing newline must not produce a phantom error row.
	 */
	public function test_csv_ignores_trailing_empty_line(): void {
		$this->mock_parse_environment();

		// Zweite Form derselben Leerzeile: Tabellenprogramme schreiben am
		// Dateiende gern eine Zeile aus lauter Trennzeichen. Auch sie darf
		// keinen Fehler erzeugen — sie enthält ja keine Angaben.
		$path   = $this->fixture( self::CSV_HEADER . "\n" . self::CSV_ROW . "\n\n,,,,\n" );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
		$this->assertSame( array(), $result['errors'] );
	}

	/**
	 * An empty file has no header and cannot be interpreted.
	 */
	public function test_csv_rejects_empty_file(): void {
		$this->mock_parse_environment();

		$result = ODW_Batch_Import::parse_file( $this->fixture( '' ) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'leer', (string) $result['error'] );
	}

	/**
	 * If no row survives validation the import fails as a whole and the answer
	 * names the first concrete reasons instead of just "0 Datensätze".
	 */
	public function test_csv_fails_when_no_row_is_valid(): void {
		$this->mock_parse_environment();

		$path   = $this->csv_fixture( array( 'Nur ein Titel,,,,' ) );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'Keine gültigen Zeilen', (string) $result['error'] );
		$this->assertStringContainsString( 'Pflichtfeld', (string) $result['error'] );
	}

	// -----------------------------------------------------------------------
	// JSON
	// -----------------------------------------------------------------------

	/**
	 * A JSON array yields one record per element.
	 */
	public function test_json_parses_array_of_objects(): void {
		$this->mock_parse_environment();

		$records = array(
			$this->valid_record( 'Erster' ),
			$this->valid_record( 'Zweiter' ),
		);

		$path   = $this->fixture( $this->encode( $records ), 'json' );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 2, $result['count'] );
		$this->assertSame( 'Zweiter', $result['data'][1]['title'] );
	}

	/**
	 * A single object is wrapped instead of being iterated over its properties.
	 */
	public function test_json_wraps_single_object(): void {
		$this->mock_parse_environment();

		$path   = $this->fixture( $this->encode( $this->valid_record( 'Einzeln' ) ), 'json' );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
		$this->assertSame( 'Einzeln', $result['data'][0]['title'] );
	}

	/**
	 * Broken JSON is reported as such, not silently treated as zero records.
	 */
	public function test_json_rejects_invalid_syntax(): void {
		$this->mock_parse_environment();

		$path   = $this->fixture( '{ "title": ', 'json' );
		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'ungültig', (string) $result['error'] );
	}

	/**
	 * A scalar inside the array is reported with its position.
	 */
	public function test_json_reports_non_object_element(): void {
		$this->mock_parse_environment();

		$path = $this->fixture(
			$this->encode( array( $this->valid_record( 'Gut' ), 'kaputt' ) ),
			'json'
		);

		$result = ODW_Batch_Import::parse_file( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['count'] );
		$this->assertStringContainsString( 'Element 2', $result['errors'][0] );
	}

	// -----------------------------------------------------------------------
	// Validierung einzelner Felder
	// -----------------------------------------------------------------------

	/**
	 * Every required field is named individually when missing.
	 */
	public function test_validation_names_each_missing_required_field(): void {
		$this->mock_parse_environment();

		$result = $this->call_private( 'validate_row', array( array( 'title' => 'Nur ein Titel' ), 7 ) );

		$this->assertFalse( $result['valid'] );
		$this->assertCount( 4, $result['errors'] );
		$this->assertStringContainsString( 'Zeile 7', $result['errors'][0] );
	}

	/**
	 * URLs are restricted to network schemes. `javascript:` passes PHP's
	 * FILTER_VALIDATE_URL and is caught by the scheme check alone.
	 *
	 * @dataProvider provide_urls
	 *
	 * @param string $url      URL under test.
	 * @param bool   $expected Whether the URL should be accepted.
	 */
	public function test_validation_accepts_only_network_url_schemes( string $url, bool $expected ): void {
		$this->mock_parse_environment();

		$this->assertSame( $expected, $this->call_private( 'is_valid_url', array( $url ) ) );
	}

	/**
	 * URLs and their expected verdict.
	 *
	 * @return array<string, array{0: string, 1: bool}>
	 */
	public function provide_urls(): array {
		return array(
			'https'       => array( 'https://example.org/daten.csv', true ),
			'http'        => array( 'http://example.org/daten.csv', true ),
			'ftp'         => array( 'ftp://example.org/daten.csv', true ),
			'javascript'  => array( 'javascript:alert(1)', false ),
			'data-uri'    => array( 'data:text/csv;base64,QQ==', false ),
			'ohne Schema' => array( 'example.org/daten.csv', false ),
			'leer'        => array( '', false ),
		);
	}

	/**
	 * A byte size must be a plain integer — "1,5" would silently truncate to 1.
	 *
	 * @dataProvider provide_byte_sizes
	 *
	 * @param string $value    Value of the byte_size column.
	 * @param bool   $expected Whether the row should validate.
	 */
	public function test_validation_requires_integer_byte_size( string $value, bool $expected ): void {
		$this->mock_parse_environment();

		$record              = $this->valid_record( 'Mit Größe' );
		$record['byte_size'] = $value;

		$result = $this->call_private( 'validate_row', array( $record, 1 ) );

		$this->assertSame( $expected, $result['valid'] );
	}

	/**
	 * Byte size values and their expected verdict.
	 *
	 * @return array<string, array{0: string, 1: bool}>
	 */
	public function provide_byte_sizes(): array {
		return array(
			'ganze Zahl'   => array( '1024', true ),
			'leer'         => array( '', true ),
			'Dezimalkomma' => array( '1,5', false ),
			'Dezimalpunkt' => array( '1.5', false ),
			'mit Einheit'  => array( '12 KB', false ),
			'negativ'      => array( '-1', false ),
		);
	}

	/**
	 * Both the short code and the full URI are accepted for a license.
	 */
	public function test_validation_accepts_short_code_and_uri_licenses(): void {
		$this->mock_parse_environment();

		$this->assertTrue( $this->call_private( 'is_valid_license', array( 'cc-by' ) ) );
		$this->assertTrue( $this->call_private( 'is_valid_license', array( 'CC-BY' ) ) );
		$this->assertTrue( $this->call_private( 'is_valid_license', array( 'https://creativecommons.org/licenses/by/4.0/' ) ) );
	}

	// -----------------------------------------------------------------------
	// Formel-Injektion
	// -----------------------------------------------------------------------

	/**
	 * Cells starting with = + @ (or tab/CR) are formulas for Excel and
	 * LibreOffice. Prefixing them with an apostrophe makes them inert if the
	 * imported data is ever exported again.
	 *
	 * @dataProvider provide_formula_values
	 *
	 * @param string $value    Raw cell value.
	 * @param string $expected Expected value after neutralisation.
	 */
	public function test_formula_injection_is_neutralised( string $value, string $expected ): void {
		$this->mock_parse_environment();

		$this->assertSame( $expected, $this->call_private( 'neutralize_formula', array( $value ) ) );
	}

	/**
	 * Cell values and their expected neutralised form.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public function provide_formula_values(): array {
		return array(
			'Gleichheitszeichen' => array( '=SUM(A1:A9)', "'=SUM(A1:A9)" ),
			'Plus'               => array( '+49 30 123456', "'+49 30 123456" ),
			'At'                 => array( '@SUM(A1)', "'@SUM(A1)" ),
			'Tabulator'          => array( "\tcmd", "'\tcmd" ),
			'negative Zahl'      => array( '-42', '-42' ),
			'Bindestrich-Text'   => array( '-cmd|/c calc', "'-cmd|/c calc" ),
			'harmlos'            => array( 'Baumkataster', 'Baumkataster' ),
			'leer'               => array( '', '' ),
		);
	}

	// -----------------------------------------------------------------------
	// import_records()
	// -----------------------------------------------------------------------

	/**
	 * A record becomes a draft post plus the mapped meta fields.
	 */
	public function test_import_creates_draft_with_mapped_meta(): void {
		$meta = array();
		$this->mock_import_environment( $meta );

		$result = ODW_Batch_Import::import_records( array( $this->valid_record( 'Baumkataster' ) ) );

		$this->assertSame( 1, $result['created'] );
		$this->assertSame( 0, $result['failed'] );
		$this->assertSame( 'Stadt Musterstadt', $meta['_odw_publisher'] );
		$this->assertSame( 'https://example.org/daten.csv', $meta['_odw_access_url'] );
		$this->assertArrayHasKey( '_odw_imported', $meta );
	}

	/**
	 * The short code is stored as the https URI — the http variant would break
	 * catalogue filters, license labels and the MQA vocabulary metric.
	 */
	public function test_import_maps_license_short_code_to_https_uri(): void {
		$meta = array();
		$this->mock_import_environment( $meta );

		ODW_Batch_Import::import_records( array( $this->valid_record( 'Mit Lizenz' ) ) );

		$this->assertSame( 'https://creativecommons.org/licenses/by/4.0/', $meta['_odw_license'] );
	}

	/**
	 * Keywords may arrive comma- or newline-separated; internally they are
	 * stored one per line.
	 */
	public function test_import_normalises_keywords_to_one_per_line(): void {
		$meta = array();
		$this->mock_import_environment( $meta );

		$record             = $this->valid_record( 'Mit Schlagworten' );
		$record['keywords'] = 'Bäume, Umwelt ,, Klima';

		ODW_Batch_Import::import_records( array( $record ) );

		$this->assertSame( "Bäume\nUmwelt\nKlima", $meta['_odw_keywords'] );
	}

	/**
	 * A failing wp_insert_post() is counted and reported with its row number
	 * instead of aborting the whole import.
	 */
	public function test_import_reports_failed_row_and_continues(): void {
		$meta = array();
		$this->mock_import_environment( $meta, true );

		$result = ODW_Batch_Import::import_records(
			array(
				$this->valid_record( 'Scheitert' ),
				$this->valid_record( 'Scheitert auch' ),
			)
		);

		$this->assertSame( 0, $result['created'] );
		$this->assertSame( 2, $result['failed'] );
		$this->assertSame( 2, $result['errors'][1]['row'] );
	}

	// -----------------------------------------------------------------------
	// Fixtures
	// -----------------------------------------------------------------------

	/**
	 * Returns a record that passes validation.
	 *
	 * @param string $title Dataset title.
	 * @return array<string, string>
	 */
	private function valid_record( string $title ): array {
		return array(
			'title'       => $title,
			'publisher'   => 'Stadt Musterstadt',
			'description' => 'Ein Beispieldatensatz.',
			'access_url'  => 'https://example.org/daten.csv',
			'license'     => 'cc-by',
		);
	}

	/**
	 * Registers everything create_dataset_from_record() touches and returns the
	 * meta store the mocked update_post_meta() writes into.
	 *
	 * `get_post` deliberately returns null: ODW_Quality::calculate() bails out
	 * immediately for a post it cannot load, so the quality recalculation stays
	 * inert here — no matter whether another test file already loaded the class.
	 *
	 * @param array<string, mixed> $meta         Meta store, filled while the import runs.
	 * @param bool                 $insert_fails Whether wp_insert_post() should report an error.
	 */
	private function mock_import_environment( array &$meta, bool $insert_fails = false ): void {
		$this->mock_parse_environment();

		\WP_Mock::userFunction( 'wp_insert_post' )->andReturn( $insert_fails ? 0 : 4711 );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( $insert_fails );
		\WP_Mock::userFunction( 'get_post' )->andReturn( null );
		\WP_Mock::userFunction( 'current_time' )->andReturn( '2026-08-23 12:00:00' );
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'sanitize_textarea_field' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'absint' )->andReturnUsing(
			function ( $value ) {
				return abs( (int) $value );
			}
		);
		\WP_Mock::userFunction( 'update_post_meta' )->andReturnUsing(
			function ( $post_id, $key, $value ) use ( &$meta ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				$meta[ $key ] = $value;
				return true;
			}
		);
	}

	/**
	 * Encodes fixture data as JSON.
	 *
	 * Bewusst nicht über wp_json_encode(): Die Funktion wird an anderer Stelle
	 * der Suite gemockt, und eine Fixture-Hilfe sollte nicht davon abhängen,
	 * welcher Test zufällig vorher lief.
	 *
	 * @param mixed $data Data to encode.
	 * @return string JSON representation.
	 */
	private function encode( $data ): string {
		return (string) json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}
}
