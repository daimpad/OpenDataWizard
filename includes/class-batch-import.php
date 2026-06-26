<?php
/**
 * Batch Import — CSV/JSON Parser und Importer
 *
 * Unterstützt:
 * - CSV & JSON Format
 * - Validation vor Import
 * - Error Tracking per Row
 * - Bulk Insert mit Meta
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Batch Import Handler for CSV and JSON files.
 *
 * @package OpenDataWizard
 */
class ODW_Batch_Import {

	/** Maximum file size: 10MB. */
	private const MAX_FILE_SIZE = 10 * 1024 * 1024;

	/**
	 * Required import columns.
	 *
	 * Mapped on insert to: title => post_title, publisher => _odw_publisher,
	 * description => _odw_description, access_url => _odw_access_url,
	 * license => _odw_license.
	 */
	private const REQUIRED_FIELDS = array(
		'title',
		'publisher',
		'description',
		'access_url',
		'license',
	);

	/** Optional field mapping (CSV column → meta key). */
	private const OPTIONAL_FIELD_MAP = array(
		'theme'       => '_odw_theme',
		'language'    => '_odw_language',
		'keywords'    => '_odw_keywords',
		'issued'      => '_odw_issued',
		'format'      => '_odw_format',
		'byte_size'   => '_odw_byte_size',
		'attribution' => '_odw_attribution_text',
	);

	/**
	 * Parse uploaded file and return parsed data.
	 *
	 * The format is determined by the file extension. When the file on disk has
	 * no meaningful extension (e.g. a temporary upload such as foo.tmp), the
	 * original client filename can be supplied via $original_name so the real
	 * extension is still detected.
	 *
	 * @param string $file_path     Path to the file on disk.
	 * @param string $original_name Optional original filename used for extension detection.
	 * @return array{
	 *   success: bool,
	 *   data: array<int, array<string, mixed>>,
	 *   error: string|null,
	 *   count: int
	 * }
	 */
	public static function parse_file( string $file_path, string $original_name = '' ): array {
		if ( ! file_exists( $file_path ) ) {
			return self::error_response( __( 'Datei nicht gefunden.', 'open-data-wizard' ) );
		}

		if ( filesize( $file_path ) > self::MAX_FILE_SIZE ) {
			return self::error_response( __( 'Datei zu groß (max. 10MB).', 'open-data-wizard' ) );
		}

		// Prefer the original filename for extension detection so temporary
		// upload files (e.g. foo.tmp) are still recognised as CSV or JSON.
		$name_for_extension = '' !== $original_name ? $original_name : $file_path;
		$extension          = strtolower( (string) pathinfo( $name_for_extension, PATHINFO_EXTENSION ) );

		if ( 'csv' === $extension ) {
			return self::parse_csv( $file_path );
		} elseif ( 'json' === $extension ) {
			return self::parse_json( $file_path );
		}

		return self::error_response( __( 'Format nicht unterstützt (nur CSV oder JSON).', 'open-data-wizard' ) );
	}

	/**
	 * Parse CSV file.
	 *
	 * @param string $file_path Path to CSV file.
	 * @return array{
	 *   success: bool,
	 *   data: array<int, array<string, mixed>>,
	 *   error: string|null,
	 *   count: int
	 * }
	 */
	private static function parse_csv( string $file_path ): array {
		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return self::error_response( __( 'CSV-Datei konnte nicht geöffnet werden.', 'open-data-wizard' ) );
		}

		$header = fgetcsv( $handle );
		if ( ! $header ) {
			fclose( $handle );
			return self::error_response( __( 'CSV-Datei ist leer oder ungültig.', 'open-data-wizard' ) );
		}

		// Remove UTF-8 BOM from first header if present (common in Excel-exported CSVs).
		if ( ! empty( $header[0] ) ) {
			$header[0] = ltrim( (string) $header[0], "\xEF\xBB\xBF" );
		}

		$data   = array();
		$row    = 0;
		$errors = array();

		while ( ( $row_data = fgetcsv( $handle ) ) !== false ) {
			++$row;

			if ( count( $row_data ) !== count( $header ) ) {
				$errors[] = sprintf(
					/* translators: %d: CSV row number. */
					__( 'Zeile %d: Spaltenanzahl stimmt nicht überein.', 'open-data-wizard' ),
					$row
				);
				continue;
			}

			$record = array_combine( $header, $row_data );

			// Trim values.
			$record = array_map( 'trim', $record );

			// Validate row.
			$validation = self::validate_row( $record, $row );
			if ( ! $validation['valid'] ) {
				$errors = array_merge( $errors, $validation['errors'] );
				continue;
			}

			$data[] = $record;
		}

		fclose( $handle );

		if ( empty( $data ) && ! empty( $errors ) ) {
			return self::error_response(
				__( 'Keine gültigen Zeilen gefunden.', 'open-data-wizard' ) . ' ' .
				implode( ' ', array_slice( $errors, 0, 3 ) )
			);
		}

		return array(
			'success' => true,
			'data'    => $data,
			'error'   => null,
			'count'   => count( $data ),
		);
	}

	/**
	 * Parse JSON file.
	 *
	 * @param string $file_path Path to JSON file.
	 * @return array{
	 *   success: bool,
	 *   data: array<int, array<string, mixed>>,
	 *   error: string|null,
	 *   count: int
	 * }
	 */
	private static function parse_json( string $file_path ): array {
		$content = file_get_contents( $file_path );
		if ( ! $content ) {
			return self::error_response( __( 'JSON-Datei konnte nicht gelesen werden.', 'open-data-wizard' ) );
		}

		$data = json_decode( $content, true );
		if ( ! is_array( $data ) ) {
			return self::error_response( __( 'JSON-Format ist ungültig.', 'open-data-wizard' ) );
		}

		// If single object, wrap in array.
		if ( isset( $data['title'] ) && ! isset( $data[0] ) ) {
			$data = array( $data );
		}

		$records = array();
		$errors  = array();

		foreach ( $data as $idx => $record ) {
			if ( ! is_array( $record ) ) {
				$errors[] = sprintf(
					/* translators: %d: element number in the JSON array. */
					__( 'Element %d: Erwarte Objekt.', 'open-data-wizard' ),
					$idx + 1
				);
				continue;
			}

			$validation = self::validate_row( $record, $idx + 1 );
			if ( ! $validation['valid'] ) {
				$errors = array_merge( $errors, $validation['errors'] );
				continue;
			}

			$records[] = $record;
		}

		if ( empty( $records ) && ! empty( $errors ) ) {
			return self::error_response(
				__( 'Keine gültigen Datensätze gefunden.', 'open-data-wizard' ) . ' ' .
				implode( ' ', array_slice( $errors, 0, 3 ) )
			);
		}

		return array(
			'success' => true,
			'data'    => $records,
			'error'   => null,
			'count'   => count( $records ),
		);
	}

	/**
	 * Validate a single row.
	 *
	 * @param array<string, mixed> $record    Data record.
	 * @param int                  $row_index Row number (for error reporting).
	 * @return array{valid: bool, errors: string[]}
	 */
	private static function validate_row( array $record, int $row_index ): array {
		$errors = array();

		// Check required fields.
		foreach ( self::REQUIRED_FIELDS as $field ) {
			$value = $record[ $field ] ?? '';
			if ( '' === trim( (string) $value ) ) {
				$errors[] = sprintf(
					/* translators: 1: row number, 2: required field name. */
					__( 'Zeile %1$d: Pflichtfeld "%2$s" fehlt.', 'open-data-wizard' ),
					$row_index,
					$field
				);
			}
		}

		// Validate URL format.
		if ( isset( $record['access_url'] ) && ! empty( $record['access_url'] ) ) {
			if ( ! self::is_valid_url( (string) $record['access_url'] ) ) {
				$errors[] = sprintf(
					/* translators: 1: row number, 2: invalid URL. */
					__( 'Zeile %1$d: Ungültige URL: %2$s', 'open-data-wizard' ),
					$row_index,
					$record['access_url']
				);
			}
		}

		// Validate license. Accept both short codes (cc-by) and full URIs.
		if ( isset( $record['license'] ) && ! empty( $record['license'] ) ) {
			$license_value = (string) $record['license'];
			if ( ! self::is_valid_license( $license_value ) ) {
				$errors[] = sprintf(
					/* translators: 1: row number, 2: invalid license value. */
					__( 'Zeile %1$d: Ungültige Lizenz: %2$s', 'open-data-wizard' ),
					$row_index,
					$license_value
				);
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	/**
	 * Validate URL format.
	 *
	 * @param string $url URL to validate.
	 * @return bool
	 */
	private static function is_valid_url( string $url ): bool {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
		return in_array( $scheme, array( 'http', 'https', 'ftp', 'ftps' ), true );
	}

	/**
	 * Validate license (short code or URI).
	 *
	 * @param string $license License short code or URI.
	 * @return bool
	 */
	private static function is_valid_license( string $license ): bool {
		// Get available licenses.
		$options = ODW_Fields::get_license_options();

		// Check if it's a known URI.
		if ( isset( $options[ $license ] ) ) {
			return true;
		}

		// Check if it's a short code that maps to a URI.
		$license_map = self::get_license_alias_map();
		if ( isset( $license_map[ strtolower( $license ) ] ) ) {
			return true;
		}

		// Accept any non-empty string for "sonstige" (custom).
		return ! empty( $license );
	}

	/**
	 * Get license short code → URI mapping.
	 *
	 * @return array<string, string>
	 */
	private static function get_license_alias_map(): array {
		return array(
			'cc0'      => 'http://creativecommons.org/publicdomain/zero/1.0/',
			'cc-by'    => 'http://creativecommons.org/licenses/by/4.0/',
			'cc-by-sa' => 'http://creativecommons.org/licenses/by-sa/4.0/',
			'odc-odbl' => 'http://opendatacommons.org/licenses/odbl/1.0/',
		);
	}

	/**
	 * Import validated data into WordPress.
	 *
	 * @param array<int, array<string, mixed>> $records Data records to import.
	 * @return array{
	 *   success: bool,
	 *   created: int,
	 *   failed: int,
	 *   errors: array<int, array{row: int, message: string}>
	 * }
	 */
	public static function import_records( array $records ): array {
		$created = 0;
		$failed  = 0;
		$errors  = array();

		foreach ( $records as $idx => $record ) {
			$result = self::create_dataset_from_record( $record, $idx + 1 );

			if ( $result['success'] ) {
				++$created;
			} else {
				++$failed;
				$errors[] = array(
					'row'     => $idx + 1,
					'message' => $result['error'],
				);
			}
		}

		return array(
			'success' => true,
			'created' => $created,
			'failed'  => $failed,
			'errors'  => $errors,
		);
	}

	/**
	 * Create a single dataset post from import record.
	 *
	 * @param array<string, mixed> $record     Data record.
	 * @param int                  $row_index  Row number.
	 * @return array{success: bool, error?: string, post_id?: int}
	 */
	private static function create_dataset_from_record( array $record, int $row_index ): array {
		// Create post.
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'odw_dataset',
				'post_title'   => sanitize_text_field( (string) ( $record['title'] ?? '' ) ),
				'post_content' => '',
				'post_status'  => 'draft',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %d: row number. */
					__( 'Zeile %d: Konnte Post nicht erstellen.', 'open-data-wizard' ),
					$row_index
				),
			);
		}

		// Save meta fields.
		update_post_meta( $post_id, '_odw_publisher', sanitize_text_field( (string) ( $record['publisher'] ?? '' ) ) );
		update_post_meta( $post_id, '_odw_description', sanitize_textarea_field( (string) ( $record['description'] ?? '' ) ) );
		update_post_meta( $post_id, '_odw_access_url', esc_url_raw( (string) ( $record['access_url'] ?? '' ) ) );

		// License: map short code to URI if needed.
		$license     = (string) ( $record['license'] ?? '' );
		$license_map = self::get_license_alias_map();
		$license_uri = $license_map[ strtolower( $license ) ] ?? $license;
		update_post_meta( $post_id, '_odw_license', sanitize_text_field( $license_uri ) );

		// Optional fields.
		foreach ( self::OPTIONAL_FIELD_MAP as $field => $meta_key ) {
			if ( isset( $record[ $field ] ) && ! empty( $record[ $field ] ) ) {
				$value = (string) $record[ $field ];

				if ( 'byte_size' === $field ) {
					$value = (string) absint( $value );
				} elseif ( '_odw_attribution_text' === $meta_key ) {
					$value = sanitize_textarea_field( $value );
				} else {
					$value = sanitize_text_field( $value );
				}

				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		// Set import tracking.
		update_post_meta( $post_id, '_odw_imported', current_time( 'mysql' ) );

		return array(
			'success' => true,
			'post_id' => $post_id,
		);
	}

	/**
	 * Helper: Return error response.
	 *
	 * @param string $message Error message.
	 * @return array{success: bool, data: array, error: string, count: int}
	 */
	private static function error_response( string $message ): array {
		return array(
			'success' => false,
			'data'    => array(),
			'error'   => $message,
			'count'   => 0,
		);
	}
}
