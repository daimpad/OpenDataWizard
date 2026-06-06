<?php
/**
 * Tests for ODW_Batch_Import class
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Batch_Import CSV/JSON parser.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Batch_Import extends TestCase {

	/**
	 * Test CSV parsing with valid data.
	 */
	public function test_parse_csv_with_valid_data(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license,theme
"Dataset 1","Org A","A test dataset","https://example.com/data1.csv","cc-by","SOCI"
"Dataset 2","Org B","Another dataset","https://example.com/data2.json","cc0","ECON"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
		$this->assertCount( 2, $result['data'] );
		$this->assertEquals( 2, $result['count'] );
		$this->assertNull( $result['error'] );
	}

	/**
	 * Test CSV parsing with missing required field.
	 */
	public function test_parse_csv_with_missing_required_field(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url
"Dataset 1","Org A","Description","https://example.com/data.csv"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertFalse( $result['success'] );
		$this->assertEquals( 0, $result['count'] );
		$this->assertNotNull( $result['error'] );
	}

	/**
	 * Test CSV parsing with invalid URL.
	 */
	public function test_parse_csv_with_invalid_url(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license
"Dataset 1","Org A","Description","not-a-url","cc-by"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertFalse( $result['success'] );
		$this->assertEquals( 0, $result['count'] );
	}

	/**
	 * Test CSV parsing blocks javascript: URLs.
	 */
	public function test_parse_csv_blocks_javascript_urls(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license
"Dataset 1","Org A","Description","javascript:alert('xss')","cc-by"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * Test JSON parsing with valid data.
	 */
	public function test_parse_json_with_valid_data(): void {
		$json_content = json_encode(
			array(
				array(
					'title'       => 'Dataset 1',
					'publisher'   => 'Org A',
					'description' => 'A test dataset',
					'access_url'  => 'https://example.com/data1.csv',
					'license'     => 'cc-by',
				),
				array(
					'title'       => 'Dataset 2',
					'publisher'   => 'Org B',
					'description' => 'Another dataset',
					'access_url'  => 'https://example.com/data2.json',
					'license'     => 'cc0',
				),
			)
		);

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $json_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
		$this->assertCount( 2, $result['data'] );
		$this->assertEquals( 2, $result['count'] );
	}

	/**
	 * Test JSON parsing with single object (should wrap in array).
	 */
	public function test_parse_json_with_single_object(): void {
		$json_content = json_encode(
			array(
				'title'       => 'Dataset 1',
				'publisher'   => 'Org A',
				'description' => 'A test dataset',
				'access_url'  => 'https://example.com/data.csv',
				'license'     => 'cc-by',
			)
		);

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $json_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
		$this->assertCount( 1, $result['data'] );
		$this->assertEquals( 1, $result['count'] );
	}

	/**
	 * Test JSON parsing with invalid format.
	 */
	public function test_parse_json_with_invalid_format(): void {
		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, 'not valid json {]' );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertFalse( $result['success'] );
		$this->assertEquals( 0, $result['count'] );
	}

	/**
	 * Test unsupported file format.
	 */
	public function test_unsupported_file_format(): void {
		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-") . '.txt';
		file_put_contents( $temp_file, 'some data' );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'Format nicht unterstützt', $result['error'] );
	}

	/**
	 * Test CSV whitespace trimming.
	 */
	public function test_csv_whitespace_trimming(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license
"  Dataset 1  ","  Org A  ","  Description  ","https://example.com/data.csv","  cc-by  "
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
		$this->assertCount( 1, $result['data'] );
		$this->assertEquals( 'Dataset 1', $result['data'][0]['title'] );
	}

	/**
	 * Test CSV with optional fields.
	 */
	public function test_csv_with_optional_fields(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license,theme,language,format,keywords
"Dataset 1","Org A","Description","https://example.com/data.csv","cc-by","SOCI","de","CSV","test,data"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
		$this->assertEquals( 'SOCI', $result['data'][0]['theme'] );
		$this->assertEquals( 'de', $result['data'][0]['language'] );
	}

	/**
	 * Test HTTP and HTTPS URLs are valid.
	 */
	public function test_http_https_urls_are_valid(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license
"Dataset 1","Org A","Description","http://example.com/data.csv","cc-by"
"Dataset 2","Org B","Description","https://example.com/data.csv","cc-by"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
		$this->assertCount( 2, $result['data'] );
	}

	/**
	 * Test FTP URLs are accepted.
	 */
	public function test_ftp_urls_are_valid(): void {
		$csv_content = <<<CSV
title,publisher,description,access_url,license
"Dataset 1","Org A","Description","ftp://example.com/data.csv","cc-by"
CSV;

		$temp_file = tempnam(sys_get_temp_dir(), "odw-test-");
		file_put_contents( $temp_file, $csv_content );

		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		$this->assertTrue( $result['success'] );
	}
}
