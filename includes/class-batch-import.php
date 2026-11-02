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

	/** Maximum number of records processed per import (DoS / memory guard). */
	private const MAX_RECORDS = 2000;

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
		'keywords'    => '_odw_keywords',
		'format'      => '_odw_format',
		'byte_size'   => '_odw_byte_size',
		'attribution' => '_odw_attribution_text',
	);

	/**
	 * Weitere optionale Spalten für vollständige Datensätze: Spalte => array(
	 * Meta-Schlüssel, Art ). Jede Art wird in validate_row() geprüft und in
	 * clean_value() in die Form gebracht, die das Formular speichert:
	 * - date:  JJJJ-MM-TT oder TT.MM.JJJJ, gespeichert als JJJJ-MM-TT;
	 * - url / email / text;
	 * - vocab: Wert eines Auswahlfelds — URI, Kürzel (ANNUAL, PUBLIC, DEU, de)
	 *   oder Bezeichnung („Jährlich"), gespeichert als URI (resolve_vocab());
	 * - flag:  ja/nein;
	 * - media: Medientyp als IANA-URI oder kurz (text/csv), gespeichert als
	 *   IANA-URI (parse_media_type()).
	 */
	private const EXTENDED_FIELD_MAP = array(
		'language'                  => array( '_odw_language', 'vocab' ),
		'issued'                    => array( '_odw_issued', 'date' ),
		'temporal_start'            => array( '_odw_temporal_start', 'date' ),
		'temporal_end'              => array( '_odw_temporal_end', 'date' ),
		'accrual_periodicity'       => array( '_odw_accrual_periodicity', 'vocab' ),
		'spatial'                   => array( '_odw_spatial', 'text' ),
		'political_geocoding_level' => array( '_odw_political_geocoding_level', 'vocab' ),
		'landing_page'              => array( '_odw_landing_page', 'url' ),
		'download_url'              => array( '_odw_download_url', 'url' ),
		'media_type'                => array( '_odw_media_type', 'media' ),
		'rights'                    => array( '_odw_dist_rights', 'text' ),
		'contact_name'              => array( '_odw_contact_name', 'text' ),
		'contact_email'             => array( '_odw_contact_email', 'email' ),
		'contact_url'               => array( '_odw_contact_url', 'url' ),
		'access_rights'             => array( '_odw_access_rights', 'vocab' ),
		'availability'              => array( '_odw_availability', 'vocab' ),
		'hvd'                       => array( '_odw_is_hvd', 'flag' ),
		'hvd_category'              => array( '_odw_hvd_category', 'vocab' ),
		'version'                   => array( '_odw_version', 'text' ),
		'conforms_to'               => array( '_odw_conforms_to', 'url' ),
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
	 *   errors?: array<int, string>,
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
	 *   errors?: array<int, string>,
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

			// Skip fully empty lines (e.g. a trailing newline at end of file).
			if ( '' === trim( implode( '', array_map( 'strval', $row_data ) ) ) ) {
				continue;
			}

			if ( count( $data ) >= self::MAX_RECORDS ) {
				$errors[] = sprintf(
					/* translators: %d: maximum number of records allowed per import. */
					__( 'Maximale Anzahl von %d Datensätzen erreicht — weitere Zeilen wurden ignoriert.', 'open-data-wizard' ),
					self::MAX_RECORDS
				);
				break;
			}

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
			'errors'  => $errors,
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
	 *   errors?: array<int, string>,
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

		// If a single object (associative array, not a list), wrap it in an array.
		// array_is_list() is false for associative arrays, including ones missing
		// the "title" key — so a single record without a title is still detected
		// as one object instead of being iterated over its properties.
		if ( ! array_is_list( $data ) ) {
			$data = array( $data );
		}

		$records = array();
		$errors  = array();

		foreach ( $data as $idx => $record ) {
			if ( count( $records ) >= self::MAX_RECORDS ) {
				$errors[] = sprintf(
					/* translators: %d: maximum number of records allowed per import. */
					__( 'Maximale Anzahl von %d Datensätzen erreicht — weitere Elemente wurden ignoriert.', 'open-data-wizard' ),
					self::MAX_RECORDS
				);
				break;
			}

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
			'errors'  => $errors,
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

		// Validate byte_size is a plain integer (avoid silent truncation of e.g. "1,5").
		if ( isset( $record['byte_size'] ) && '' !== trim( (string) $record['byte_size'] ) ) {
			if ( ! ctype_digit( trim( (string) $record['byte_size'] ) ) ) {
				$errors[] = sprintf(
					/* translators: 1: row number, 2: invalid byte size value. */
					__( 'Zeile %1$d: Dateigröße muss eine ganze Zahl in Bytes sein: %2$s', 'open-data-wizard' ),
					$row_index,
					(string) $record['byte_size']
				);
			}
		}

		$errors = array_merge( $errors, self::validate_extended( $record, $row_index ) );

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	/**
	 * Prüft die weiteren optionalen Spalten (EXTENDED_FIELD_MAP). Leere Zellen
	 * sind immer erlaubt.
	 *
	 * @param array<string, mixed> $record    Data record.
	 * @param int                  $row_index Row number (for error reporting).
	 * @return string[]
	 */
	private static function validate_extended( array $record, int $row_index ): array {
		$errors = array();

		foreach ( self::EXTENDED_FIELD_MAP as $column => $spec ) {
			$raw = isset( $record[ $column ] ) && is_scalar( $record[ $column ] ) ? trim( (string) $record[ $column ] ) : '';
			if ( '' === $raw ) {
				continue;
			}

			switch ( $spec[1] ) {
				case 'date':
					$ok = '' !== self::parse_date( $raw );
					/* translators: 1: row number, 2: column name, 3: invalid value. */
					$message = __( 'Zeile %1$d: Ungültiges Datum in „%2$s": %3$s (erwartet JJJJ-MM-TT oder TT.MM.JJJJ)', 'open-data-wizard' );
					break;
				case 'url':
					$ok = self::is_valid_url( $raw );
					/* translators: 1: row number, 2: column name, 3: invalid value. */
					$message = __( 'Zeile %1$d: Ungültige Adresse in „%2$s": %3$s', 'open-data-wizard' );
					break;
				case 'email':
					$ok = (bool) is_email( $raw );
					/* translators: 1: row number, 2: column name, 3: invalid value. */
					$message = __( 'Zeile %1$d: Ungültige E-Mail-Adresse in „%2$s": %3$s', 'open-data-wizard' );
					break;
				case 'vocab':
					$ok = '' !== self::resolve_vocab( $column, $raw );
					/* translators: 1: row number, 2: column name, 3: invalid value. */
					$message = __( 'Zeile %1$d: Unbekannter Wert in „%2$s": %3$s', 'open-data-wizard' );
					break;
				case 'media':
					$ok = '' !== self::parse_media_type( $raw );
					/* translators: 1: row number, 2: column name, 3: invalid value. */
					$message = __( 'Zeile %1$d: Ungültiger Medientyp in „%2$s": %3$s (erwartet z. B. text/csv)', 'open-data-wizard' );
					break;
				case 'flag':
					$ok = null !== self::parse_flag( $raw );
					/* translators: 1: row number, 2: column name, 3: invalid value. */
					$message = __( 'Zeile %1$d: „%2$s" erwartet ja oder nein: %3$s', 'open-data-wizard' );
					break;
				default:
					$ok      = true;
					$message = '';
			}

			if ( ! $ok ) {
				$errors[] = sprintf( $message, $row_index, $column, $raw );
			}
		}

		$start = self::parse_date( isset( $record['temporal_start'] ) && is_scalar( $record['temporal_start'] ) ? (string) $record['temporal_start'] : '' );
		$end   = self::parse_date( isset( $record['temporal_end'] ) && is_scalar( $record['temporal_end'] ) ? (string) $record['temporal_end'] : '' );
		if ( '' !== $start && '' !== $end && $start > $end ) {
			$errors[] = sprintf(
				/* translators: 1: row number, 2: start date, 3: end date. */
				__( 'Zeile %1$d: Der Zeitraum beginnt nach seinem Ende: %2$s bis %3$s', 'open-data-wizard' ),
				$row_index,
				$start,
				$end
			);
		}

		return $errors;
	}

	/**
	 * Datum als JJJJ-MM-TT. Nimmt JJJJ-MM-TT und TT.MM.JJJJ an; sonst ''.
	 *
	 * @param string $value Datum aus der Datei.
	 * @return string
	 */
	public static function parse_date( string $value ): string {
		$value = trim( $value );
		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) ) {
			list( , $year, $month, $day ) = $m;
		} elseif ( preg_match( '/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $m ) ) {
			list( , $day, $month, $year ) = $m;
		} else {
			return '';
		}

		if ( ! checkdate( (int) $month, (int) $day, (int) $year ) ) {
			return '';
		}
		return sprintf( '%04d-%02d-%02d', (int) $year, (int) $month, (int) $day );
	}

	/**
	 * Ja/Nein: 'yes' bzw. '' wie das Auswahlfeld, null bei anderem Text.
	 *
	 * @param string $value Wert aus der Datei.
	 * @return string|null
	 */
	public static function parse_flag( string $value ): ?string {
		$value = strtolower( trim( $value ) );
		if ( in_array( $value, array( 'ja', 'yes', 'y', 'j', '1', 'true', 'wahr', 'x' ), true ) ) {
			return 'yes';
		}
		if ( in_array( $value, array( '', 'nein', 'no', 'n', '0', 'false', 'falsch' ), true ) ) {
			return '';
		}
		return null;
	}

	/**
	 * Medientyp als IANA-URI, wie ihn dcat:mediaType erwartet. „text/csv" wird
	 * zu https://www.iana.org/assignments/media-types/text/csv; eine Adresse
	 * bleibt, wie sie ist. Sonst ''.
	 *
	 * @param string $value Wert aus der Datei.
	 * @return string
	 */
	public static function parse_media_type( string $value ): string {
		$value = trim( $value );
		if ( preg_match( '#^[a-z]+/[a-z0-9][a-z0-9.+_-]*$#i', $value ) ) {
			return 'https://www.iana.org/assignments/media-types/' . strtolower( $value );
		}
		return self::is_valid_url( $value ) ? $value : '';
	}

	/**
	 * Wert eines Auswahlfelds als gespeicherte URI. Erkennt die URI selbst,
	 * ihr letztes Pfadstück (ANNUAL, PUBLIC, DEU, federal, c_ac64a52d) und die
	 * Bezeichnung mit oder ohne Klammerzusatz („Jährlich", „Deutsch"), bei der
	 * Sprache auch den zweibuchstabigen Code (de). Sonst ''.
	 *
	 * @param string $column Spalte (language, accrual_periodicity, …).
	 * @param string $value  Wert aus der Datei.
	 * @return string
	 */
	public static function resolve_vocab( string $column, string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		switch ( $column ) {
			case 'language':
				$options = ODW_Fields::get_language_options();
				break;
			case 'accrual_periodicity':
				$options = ODW_Fields::get_periodicity_options();
				break;
			case 'political_geocoding_level':
				$options = ODW_Fields::get_political_geocoding_level_options();
				break;
			case 'access_rights':
				$options = ODW_Fields::get_access_rights_options();
				break;
			case 'availability':
				$options = ODW_Fields::get_availability_options();
				break;
			case 'hvd_category':
				$options = ODW_Fields::get_hvd_category_options();
				break;
			default:
				$options = array();
		}
		unset( $options[''] );

		if ( isset( $options[ $value ] ) ) {
			return $value;
		}

		$needle = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value ) : strtolower( $value );
		foreach ( $options as $key => $label ) {
			$key   = (string) $key;
			$parts = explode( '/', rtrim( $key, '/' ) );
			$label = trim( function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $label ) : strtolower( (string) $label ) );
			$names = array(
				strtolower( (string) end( $parts ) ),
				$label,
				trim( (string) preg_replace( '/\s*\(.*\)$/', '', $label ) ),
			);
			if ( 'language' === $column && function_exists( 'odw_resolve_language_tag' ) ) {
				$names[] = odw_resolve_language_tag( $key );
			}
			if ( in_array( $needle, $names, true ) ) {
				return $key;
			}
		}

		return '';
	}

	/**
	 * Wert einer weiteren Spalte in der Form, die das Formular speichert.
	 *
	 * @param string $column Spalte.
	 * @param string $type   Art aus EXTENDED_FIELD_MAP.
	 * @param string $raw    Wert aus der Datei.
	 * @return string '' bei ungültigem Wert.
	 */
	private static function clean_value( string $column, string $type, string $raw ): string {
		switch ( $type ) {
			case 'date':
				return self::parse_date( $raw );
			case 'url':
				return self::is_valid_url( $raw ) ? esc_url_raw( $raw ) : '';
			case 'media':
				$media = self::parse_media_type( $raw );
				return '' !== $media ? esc_url_raw( $media ) : '';
			case 'email':
				return sanitize_email( $raw );
			case 'vocab':
				return self::resolve_vocab( $column, $raw );
			case 'flag':
				return (string) self::parse_flag( $raw );
			default:
				return sanitize_text_field( self::neutralize_formula( $raw ) );
		}
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
		// Dieselbe Liste wie REST-Filter und Frontend-Katalog. WICHTIG: https-URIs,
		// identisch zu ODW_Fields::get_license_options() — http-Varianten würden
		// Katalog-Filter, Lizenz-Labels und die MQA-Vokabular-Metrik für
		// importierte Datensätze brechen.
		return ODW_Fields::license_aliases();
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

		// Cap the number of records as a defence-in-depth guard: the execute
		// request comes from the client and could bypass the preview limit.
		$records = array_slice( $records, 0, self::MAX_RECORDS );

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
				'post_title'   => sanitize_text_field( self::neutralize_formula( (string) ( $record['title'] ?? '' ) ) ),
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
		update_post_meta( $post_id, '_odw_publisher', sanitize_text_field( self::neutralize_formula( (string) ( $record['publisher'] ?? '' ) ) ) );
		update_post_meta( $post_id, '_odw_description', sanitize_textarea_field( self::neutralize_formula( (string) ( $record['description'] ?? '' ) ) ) );
		update_post_meta( $post_id, '_odw_access_url', esc_url_raw( (string) ( $record['access_url'] ?? '' ) ) );

		// License: map short code to URI if needed. Eine Lizenz außerhalb der
		// Auswahlliste ist — wie im Formular — eine eigene („sonstige"); sonst
		// verwürfe Carbon Fields den Wert beim Lesen, und Formular und JSON-LD
		// zeigten gar keine Lizenz.
		$license     = (string) ( $record['license'] ?? '' );
		$license_map = self::get_license_alias_map();
		$license_uri = $license_map[ strtolower( $license ) ] ?? $license;
		if ( isset( ODW_Fields::get_license_options()[ $license_uri ] ) ) {
			update_post_meta( $post_id, '_odw_license', sanitize_text_field( $license_uri ) );
		} else {
			update_post_meta( $post_id, '_odw_license', 'sonstige' );
			update_post_meta( $post_id, '_odw_license_custom', sanitize_text_field( self::neutralize_formula( $license_uri ) ) );
		}

		// Optional fields.
		foreach ( self::OPTIONAL_FIELD_MAP as $field => $meta_key ) {
			if ( isset( $record[ $field ] ) && ! empty( $record[ $field ] ) ) {
				$value = (string) $record[ $field ];

				if ( 'theme' === $field ) {
					// Themen werden weiter unten gesetzt: Seit v2.41.0 ist das Feld
					// eine Mehrfachauswahl und muss über die Carbon-Fields-API
					// geschrieben werden, nicht über den flachen Meta-Key.
					continue;
				}

				if ( 'byte_size' === $field ) {
					$value = (string) absint( $value );
				} elseif ( 'keywords' === $field ) {
					// Schlagworte werden intern zeilengetrennt gespeichert — genau wie
					// im Formular, das ausdrücklich vor Kommas warnt (ein Komma im
					// Schlagwort ist zulässig, z. B. „Berlin, Stadt"). Bis v2.42.1
					// spaltete der Import zusätzlich an Kommas: Dieselbe Eingabe ergab
					// im Formular ein Schlagwort, im Import zwei — und ein via Komma
					// geschriebenes Schlagwort landete im veröffentlichten JSON-LD als
					// mehrere `dcat:keyword`-Werte, ohne dass jemand das so eingegeben
					// hätte. Nur noch Zeilenumbrüche trennen; ein Komma bleibt Teil des
					// Schlagworts. Mehrere Zeilen in einer CSV-Zelle: Feld in
					// Anführungszeichen setzen, Zeilenumbrüche darin bleiben erhalten
					// (Excel/LibreOffice tun das beim Export automatisch).
					$split = preg_split( '/[\r\n]+/', self::neutralize_formula( $value ) );
					$parts = array_values(
						array_filter(
							array_map( 'trim', is_array( $split ) ? $split : array() ),
							static function ( string $keyword ): bool {
								return '' !== $keyword;
							}
						)
					);
					$value = implode( "\n", array_map( 'sanitize_text_field', $parts ) );
				} elseif ( '_odw_attribution_text' === $meta_key ) {
					$value = sanitize_textarea_field( self::neutralize_formula( $value ) );
				} elseif ( 'format' === $field ) {
					// „geojson" → „GeoJSON": Das Formular ist ein Auswahlfeld, ein
					// Wert in anderer Schreibweise ginge dort verloren.
					$key   = ODW_Fields::resolve_format_key( $value );
					$value = '' !== $key ? $key : sanitize_text_field( self::neutralize_formula( $value ) );
				} else {
					$value = sanitize_text_field( self::neutralize_formula( $value ) );
				}

				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		// Weitere Spalten (Zeitraum, Kontakt, Aktualisierung, HVD …).
		foreach ( self::EXTENDED_FIELD_MAP as $column => $spec ) {
			$raw = isset( $record[ $column ] ) && is_scalar( $record[ $column ] ) ? trim( (string) $record[ $column ] ) : '';
			if ( '' === $raw ) {
				continue;
			}
			$value = self::clean_value( $column, $spec[1], $raw );
			if ( '' !== $value ) {
				update_post_meta( $post_id, $spec[0], $value );
			}
		}

		// Themen: Mehrfachauswahl seit v2.41.0. Der Import erlaubt Komma- oder
		// Zeilentrennung, jeder Eintrag wird zur EU-URI aufgelöst. Geschrieben
		// wird über carbon_set_post_meta(), weil Carbon Fields Mehrfachwerte
		// unter eigenen Meta-Keys ablegt; die flache Spiegelung zieht danach nach.
		if ( isset( $record['theme'] ) && '' !== trim( (string) $record['theme'] ) && class_exists( 'ODW_Fields' ) ) {
			$split  = preg_split( '/[\r\n,]+/', self::neutralize_formula( (string) $record['theme'] ) );
			$themes = ODW_Fields::normalize_themes( is_array( $split ) ? $split : array() );

			if ( array() !== $themes ) {
				carbon_set_post_meta( $post_id, 'odw_theme', $themes );
				ODW_Fields::sync_theme_index( $post_id );
			}
		}

		// Set import tracking.
		update_post_meta( $post_id, '_odw_imported', current_time( 'mysql' ) );

		// Lizenz- und Format-Index für die Katalog-Filter: Wie beim Qualitätswert
		// lief save_post, bevor es diese Meta-Werte gab.
		if ( class_exists( 'ODW_Catalog_Filters' ) ) {
			ODW_Catalog_Filters::sync_index( $post_id );
		}

		// Recalculate quality now that all meta is written. wp_insert_post() above
		// fired save_post before these meta values existed, so the score stored then
		// would be stale; recompute explicitly to reflect the imported metadata.
		if ( class_exists( 'ODW_Quality' ) ) {
			ODW_Quality::store( $post_id, ODW_Quality::calculate( $post_id ) );
		}

		return array(
			'success' => true,
			'post_id' => $post_id,
		);
	}

	/**
	 * Neutralise CSV/formula-injection in an imported cell value.
	 *
	 * Spreadsheet software interprets cells beginning with = + @ (or a tab/CR) as
	 * formulas. Prefixing such values with an apostrophe renders them inert if the
	 * data is ever re-exported and opened in Excel/LibreOffice. A leading "-" is
	 * only neutralised when the value is not a plain number, to preserve negatives.
	 *
	 * @param string $value Raw cell value.
	 * @return string Safe value.
	 */
	private static function neutralize_formula( string $value ): string {
		if ( '' === $value ) {
			return $value;
		}

		$first = $value[0];

		if ( in_array( $first, array( '=', '+', '@', "\t", "\r" ), true ) ) {
			return "'" . $value;
		}

		if ( '-' === $first && ! is_numeric( $value ) ) {
			return "'" . $value;
		}

		return $value;
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
