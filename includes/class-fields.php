<?php
/**
 * Carbon Fields Felddefinitionen für odw_dataset
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Registers all Carbon Fields field definitions for the odw_dataset post type.
 *
 * @package OpenDataWizard
 */
class ODW_Fields {


	/**
	 * Registers WordPress hooks.
	 */
	public static function init(): void {
		add_action( 'carbon_fields_register_fields', array( self::class, 'register' ) );
		add_action( 'save_post_odw_dataset', array( self::class, 'set_modified_date' ), 10, 2 );
	}

	/**
	 * Entry point: registers all field groups.
	 */
	public static function register(): void {
		self::register_required_fields();
		self::register_optional_fields();
		self::register_distributions();
	}

	/**
	 * Registers the tabbed meta container with all dataset fields.
	 * Tab structure (v2.1+):
	 *   1 — Grundlegende Informationen
	 *   2 — Inhaltliche Angaben
	 *   3 — Datenbereitstellung (Lizenz + Distribution)
	 *   4 — Erweiterte Angaben
	 *   5 — Vorschau
	 */
	private static function register_required_fields(): void {
		Container::make( 'post_meta', __( 'Pflichtangaben', 'open-data-wizard' ) )
			->where( 'post_type', '=', 'odw_dataset' )
			->set_priority( 'high' )

		// -----------------------------------------------------------------
		// Tab 1 — Grundlegende Informationen
		// -----------------------------------------------------------------
			->add_tab(
				__( '1 — Grundlegende Informationen', 'open-data-wizard' ),
				array(

					Field::make( 'text', 'odw_publisher', __( 'Wer gibt diese Daten heraus?', 'open-data-wizard' ) )
						->set_required( true )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_publisher' ) : '' )
						->set_attribute( 'placeholder', __( 'z.B. Musterorganisation e.V.', 'open-data-wizard' ) )
						->set_help_text( __( 'HERAUSGEBENDE ORGANISATION (dct:publisher)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Musterstadt Statistikamt, Umweltbundesamt, Verbraucherzentrale e.V.', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_theme', __( 'In welche Kategorie gehört dieser Datensatz?', 'open-data-wizard' ) )
						->add_options( self::get_theme_options() )
						->set_help_text( __( 'THEMA (dcat:theme)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Umwelt, Bildung, Gesundheit, Wirtschaft, Kultur', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_description', __( 'Worum geht es in diesem Datensatz?', 'open-data-wizard' ) )
						->set_required( true )
						->set_rows( 5 )
						->set_attribute( 'placeholder', __( 'Kurze Beschreibung des Datensatzes…', 'open-data-wizard' ) )
						->set_help_text( __( 'BESCHREIBUNG (dct:description)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Ein Überblick über die bevölkerungsreichsten Städte in Deutschland mit statistischen Daten zu Einwohnerzahl und Entwicklung.', 'open-data-wizard' ) ),
				)
			)

		// -----------------------------------------------------------------
		// Tab 2 — Inhaltliche Angaben
		// -----------------------------------------------------------------
			->add_tab(
				__( '2 — Inhaltliche Angaben', 'open-data-wizard' ),
				array(
					Field::make( 'select', 'odw_language', __( 'In welcher Sprache sind die Daten?', 'open-data-wizard' ) )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_language' ) : '' )
						->add_options( self::get_language_options() )
						->set_help_text( __( 'SPRACHE (dct:language)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Deutsch, Englisch', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_keywords', __( 'Mit welchen Stichworten finde ich diese Daten?', 'open-data-wizard' ) )
						->set_rows( 3 )
						->set_attribute( 'placeholder', __( 'z.B. Umwelt', 'open-data-wizard' ) )
						->set_help_text( __( 'SCHLAGWORTE (dcat:keyword)', 'open-data-wizard' ) . "\n\n" . __( 'Jedes Schlagwort in einer eigenen Zeile. Beispiel: Umwelt, Wasser, Luftverschmutzung', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_issued', __( 'Wann wurden diese Daten zum ersten Mal veröffentlicht?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options(
							array(
								'dateFormat' => 'Y-m-d',
								'locale'     => 'de',
							)
						)
						->set_help_text( __( 'VERÖFFENTLICHUNGSDATUM (dct:issued)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-01-15', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_modified', __( 'Wann wurden diese Daten zuletzt aktualisiert?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options(
							array(
								'dateFormat' => 'Y-m-d',
								'locale'     => 'de',
							)
						)
						->set_help_text( __( 'ÄNDERUNGSDATUM (dct:modified)', 'open-data-wizard' ) . "\n\n" . __( 'Wird automatisch bei jeder Speicherung aktualisiert. Beispiel: 2026-04-22', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_cessda_topic', __( 'CESSDA Themenklassifikation', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-backing', 'cessda' )
						->set_help_text( __( 'CESSDA THEMENKLASSIFIKATION (cessda:topic)', 'open-data-wizard' ) . "\n\n" . __( 'Aus dem CESSDA Controlled Vocabulary (Version 4.2.3, Deutsch). Beispiel: Volkszählungen, Migration, Wirtschaftspolitik', 'open-data-wizard' ) ),
				)
			)

		// -----------------------------------------------------------------
		// Tab 3 — Datenbereitstellung (Lizenz + Distribution)
		// -----------------------------------------------------------------
			->add_tab(
				__( '3 — Datenbereitstellung', 'open-data-wizard' ),
				array(
					Field::make( 'text', 'odw_access_url', __( 'Wo kann ich die Datei herunterladen?', 'open-data-wizard' ) )
						->set_required( true )
						->set_attribute( 'placeholder', 'https://beispiel.de/daten/datei.csv' )
						->set_attribute( 'type', 'url' )
						->set_help_text( __( 'ZUGRIFFS-URL (dcat:accessURL)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: https://beispiel.de/daten/datei.csv', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_format', __( 'In welchem Format ist die Datei?', 'open-data-wizard' ) )
						->add_options( self::get_format_options() )
						->set_help_text( __( 'FORMAT (dct:format)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: CSV, JSON, PDF', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_byte_size', __( 'Dateigröße (Bytes)', 'open-data-wizard' ) )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', '0' )
						->set_attribute( 'data-odw-backing', 'byte_size' ),

					Field::make( 'select', 'odw_license', __( 'Unter welcher Lizenz sind diese Daten verfügbar?', 'open-data-wizard' ) )
						->set_required( true )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_license' ) : '' )
						->add_options( self::get_license_options() )
						->set_help_text( __( 'LIZENZ (dct:license)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: CC0 1.0, CC-BY 4.0 – Diese bestimmt, wie andere die Daten nutzen dürfen.', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_license_info' )
						->set_html( '<div class="odw-license-info" data-odw-license-info hidden></div>' ),

					Field::make( 'text', 'odw_license_custom', __( 'Lizenz-URI eingeben oder auswählen', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'https://example.org/meine-lizenz', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-autosuggest', 'license_custom' )
						->set_help_text( __( 'EIGENE LIZENZ-URI', 'open-data-wizard' ) . "\n\n" . __( 'Vollständige URI der Lizenz eingeben oder aus der Liste auswählen. Beispiel: https://creativecommons.org/licenses/by/4.0/', 'open-data-wizard' ) )
						->set_conditional_logic(
							array(
								array(
									'field'   => 'odw_license',
									'value'   => 'sonstige',
									'compare' => '=',
								),
							)
						),

					Field::make( 'text', 'odw_attribution_text', __( 'Welcher Namensnennungstext soll bei Weiternutzung angegeben werden?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'optional – nur bei CC BY oder CC BY-SA', 'open-data-wizard' ) )
						->set_help_text( __( 'NAMENSNENNUNGSTEXT (dcatde:licenseAttributionByText)', 'open-data-wizard' ) . "\n\n" . __( 'Empfohlen bei CC BY und CC BY-SA Lizenzen. Beispiel: Datensatz von Musterorganisation e.V., bereitgestellt unter CC BY 4.0', 'open-data-wizard' ) )
						->set_conditional_logic(
							array(
								'relation' => 'OR',
								array(
									'field'   => 'odw_license',
									'value'   => 'https://creativecommons.org/licenses/by/4.0/',
									'compare' => '=',
								),
								array(
									'field'   => 'odw_license',
									'value'   => 'https://creativecommons.org/licenses/by-sa/4.0/',
									'compare' => '=',
								),
							)
						),
				)
			)

		// -----------------------------------------------------------------
		// Tab 4 — Erweiterte Angaben (unverändert)
		// -----------------------------------------------------------------
			->add_tab(
				__( '4 — Erweiterte Angaben', 'open-data-wizard' ),
				array(
					Field::make( 'html', 'odw_ext_hint_landing' )
					->set_html( '<h4 style="margin:0 0 4px">' . esc_html__( 'Projektseite & Aktualität', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'text', 'odw_landing_page', __( 'Wo finde ich mehr Informationen zu diesem Projekt?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/projekt' )
						->set_help_text( __( 'PROJEKTSEITE (dcat:landingPage)', 'open-data-wizard' ) . "\n\n" . __( 'URL der Projektwebsite oder des Datenportals mit weiteren Informationen zum Datensatz. Beispiel: https://beispiel.de/projekt', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_accrual_periodicity', __( 'Wie oft werden diese Daten aktualisiert?', 'open-data-wizard' ) )
						->add_options( self::get_periodicity_options() )
						->set_help_text( __( 'AKTUALISIERUNGSFREQUENZ (dct:accrualPeriodicity)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Täglich, Monatlich, Jährlich, Unregelmäßig', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_ext_hint_coverage' )
					->set_html( '<h4 style="margin:16px 0 4px">' . esc_html__( 'Abdeckung', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'select', 'odw_political_geocoding_level', __( 'Auf welcher Verwaltungsebene wurden diese Daten erhoben?', 'open-data-wizard' ) )
						->add_options( self::get_political_geocoding_level_options() )
						->set_help_text( __( 'VERWALTUNGSEBENE (dcatde:politicalGeocodingLevelURI)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Gemeinde, Landkreis, Land, Bund', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_spatial', __( 'Welche geografische Region betreffen diese Daten?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'Region aus der Liste wählen oder eintippen…', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-autosuggest', 'spatial' )
						->set_help_text( __( 'GEOGRAPHISCHE ABDECKUNG (dct:spatial)', 'open-data-wizard' ) . "\n\n" . __( 'Region aus der Liste wählen (mit GeoNames verknüpft) oder Freitext/URI eingeben. Beispiel: Deutschland, Bayern, Berlin', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_temporal_start', __( 'Ab wann sind diese Daten gültig?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options(
							array(
								'dateFormat' => 'Y-m-d',
								'locale'     => 'de',
							)
						)
						->set_help_text( __( 'ZEITLICHER BEZUG — START (dct:temporal)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-01-01', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_temporal_end', __( 'Bis wann sind diese Daten gültig?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options(
							array(
								'dateFormat' => 'Y-m-d',
								'locale'     => 'de',
							)
						)
						->set_help_text( __( 'ZEITLICHER BEZUG — ENDE (dct:temporal)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-12-31', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_ext_hint_contact' )
					->set_html( '<h4 style="margin:16px 0 4px">' . esc_html__( 'Kontakt', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'text', 'odw_contact_name', __( 'Wer ist Ansprechperson für Fragen zu diesen Daten?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Open Data Team', 'open-data-wizard' ) )
						->set_help_text( __( 'Name oder Organisation der Ansprechperson.', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Open Data Team, Statistisches Landesamt', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_contact_email', __( 'Unter welcher E-Mail-Adresse kann ich Fragen stellen?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'email' )
						->set_attribute( 'placeholder', 'opendata@beispiel.de' )
						->set_help_text( __( 'E-Mail-Adresse für Rückfragen.', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: opendata@beispiel.de', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_contact_url', __( 'Auf welcher Website finde ich weitere Kontaktinformationen?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/kontakt' )
						->set_help_text( __( 'Website mit weiteren Kontaktinformationen.', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: https://beispiel.de/kontakt', 'open-data-wizard' ) ),
				)
			)

		// -----------------------------------------------------------------
		// Tab 5 — Vorschau
		// -----------------------------------------------------------------
			->add_tab(
				__( '5 — Vorschau', 'open-data-wizard' ),
				array(
					Field::make( 'html', 'odw_preview_html' )
					->set_html( self::get_preview_html() ),
				)
			);
	}

	/**
	 * Optional fields are bundled in the tabbed container in register_required_fields().
	 */
	private static function register_optional_fields(): void {
		// Fields are bundled in the tabbed container above.
	}

	/**
	 * Distributions are part of the tabbed container in register_required_fields().
	 */
	private static function register_distributions(): void {
		// Distributions are part of the tabbed container above.
	}

	/**
	 * Auto-update odw_modified on every save.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function set_modified_date( int $post_id, \WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'odw_dataset' !== $post->post_type ) {
			return;
		}

		remove_action( 'save_post_odw_dataset', array( self::class, 'set_modified_date' ), 10 );
		update_post_meta( $post_id, '_odw_modified', current_time( 'Y-m-d' ) );
		add_action( 'save_post_odw_dataset', array( self::class, 'set_modified_date' ), 10, 2 );
	}

	// -------------------------------------------------------------------------
	// Required fields registry — single source of truth for validation
	// -------------------------------------------------------------------------

	/**
	 * Returns the required scalar fields definition used by both form rendering
	 * and the validation class. Each entry: [meta_key, label].
	 * Loaded from config/dcat-ap-fields.php.
	 *
	 * @return array<int, array{meta_key: string, label: string}>
	 */
	public static function get_required_fields(): array {
		$all = self::load_field_definitions();

		$required = array();
		foreach ( $all as $field ) {
			// Only scalar fields (with a meta_key) are validated here.
			// Distribution and title are handled separately in ODW_Validation.
			if ( $field['required'] && ! empty( $field['meta_key'] ) ) {
				$required[] = array(
					'meta_key' => $field['meta_key'],
					'label'    => $field['label'],
				);
			}
		}

		return $required;
	}

	// -------------------------------------------------------------------------
	// Config file loaders
	// -------------------------------------------------------------------------

	/**
	 * Load field definitions from config/dcat-ap-fields.php.
	 * Used by ODW_Fields::get_required_fields() and ODW_Quality::get_indicators().
	 *
	 * @return array<int, array{key: string, meta_key: string, dcat_prop: string, label: string, points: int, required: bool}>
	 */
	public static function load_field_definitions(): array {
		$file = ODW_PLUGIN_DIR . 'config/dcat-ap-fields.php';

		if ( ! file_exists( $file ) ) {
			return array();
		}

		$data = include $file;
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Load format definitions from config/dct-format-list.php.
	 *
	 * @return array<string, array{mime: string, eu_uri: string}>
	 */
	private static function load_format_list(): array {
		$file = ODW_PLUGIN_DIR . 'config/dct-format-list.php';

		if ( ! file_exists( $file ) ) {
			return array();
		}

		$data = include $file;
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Load license options from config/licenses.txt.
	 * Format: URI | Label (one per line; lines starting with # are comments).
	 *
	 * @return array<string, string> URI => Label map (excludes the Sonstige entry).
	 */
	public static function load_license_list(): array {
		$file = ODW_PLUGIN_DIR . 'config/licenses.txt';

		if ( ! file_exists( $file ) ) {
			return array();
		}

		$lines   = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		$options = array();

		if ( ! is_array( $lines ) ) {
			return array();
		}

		foreach ( $lines as $line ) {
			$line = trim( $line );

			if ( '' === $line || str_starts_with( $line, '#' ) ) {
				continue;
			}

			$parts = array_map( 'trim', explode( '|', $line, 2 ) );

			if ( 2 === count( $parts ) && '' !== $parts[0] && '' !== $parts[1] ) {
				$options[ $parts[0] ] = $parts[1];
			}
		}

		return $options;
	}

	/**
	 * Parse CESSDA SKOS/RDF file and return concept URI => German label map.
	 * Result is cached in a transient (24h TTL).
	 *
	 * @return array<string, string> URI => German label.
	 */
	public static function load_cessda_options(): array {
		$cache_key = 'odw_cessda_options';
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$file = ODW_PLUGIN_DIR . 'config/TopicClassification-4.2.3_de-4.2.3.rdf';

		if ( ! file_exists( $file ) ) {
			return array();
		}

		$options = array();

		// Suppress XML warnings; file is valid RDF/XML from CESSDA.
		libxml_use_internal_errors( true );
		$xml = simplexml_load_file( $file );
		libxml_clear_errors();

		if ( false === $xml ) {
			return array();
		}

		$xml->registerXPathNamespace( 'rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#' );
		$xml->registerXPathNamespace( 'skos', 'http://www.w3.org/2004/02/skos/core#' );

		$descriptions = $xml->xpath( '//rdf:Description' );

		if ( ! is_array( $descriptions ) ) {
			return array();
		}

		foreach ( $descriptions as $desc ) {
			$attrs = $desc->attributes( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#' );
			$uri   = (string) ( $attrs['about'] ?? '' );

			// Skip ConceptScheme root and entries without a #fragment identifier.
			if ( '' === $uri || ! str_contains( $uri, '#' ) ) {
				continue;
			}

			// Get German prefLabel.
			$labels = $desc->children( 'http://www.w3.org/2004/02/skos/core#' );
			foreach ( $labels as $name => $node ) {
				if ( 'prefLabel' !== $name ) {
					continue;
				}
				$xml_attrs = $node->attributes( 'http://www.w3.org/XML/1998/namespace' );
				if ( 'de' === (string) ( $xml_attrs['lang'] ?? '' ) ) {
					$options[ $uri ] = (string) $node;
					break;
				}
			}
		}

		// Sort by label for a better auto-suggest experience.
		asort( $options );

		set_transient( $cache_key, $options, DAY_IN_SECONDS );

		return $options;
	}

	// -------------------------------------------------------------------------
	// Controlled vocabulary options
	// -------------------------------------------------------------------------

	/**
	 * Lizenzen als URI → Label Map für Select-Felder und den `odw_license_options`-Filter.
	 * Lädt Standard-Optionen aus dem Formular; vollständige Liste via licenses.txt.
	 *
	 * @return array<string, string> Erweiterbar via `add_filter('odw_license_options', ...)`.
	 */
	public static function get_license_options(): array {
		$options = array(
			''                                             => __( '— Bitte wählen —', 'open-data-wizard' ),
			'https://creativecommons.org/publicdomain/zero/1.0/' => __( 'CC0 1.0 — Gemeinfreiheit (keine Rechte vorbehalten)', 'open-data-wizard' ),
			'https://creativecommons.org/licenses/by/4.0/' => __( 'CC BY 4.0 — Namensnennung', 'open-data-wizard' ),
			'https://creativecommons.org/licenses/by-sa/4.0/' => __( 'CC BY-SA 4.0 — Namensnennung, Weitergabe unter gleichen Bedingungen', 'open-data-wizard' ),
			'sonstige'                                     => __( 'Sonstige Lizenz…', 'open-data-wizard' ),
		);

		return (array) apply_filters( 'odw_license_options', $options );
	}

	/**
	 * Kurze, allgemeinverständliche Erklärung je Lizenz (URI → Beschreibungstext).
	 * Wird unter dem Lizenz-Auswahlfeld angezeigt, sobald eine Lizenz gewählt ist.
	 *
	 * @return array<string, string> URI => Beschreibung.
	 */
	public static function get_license_descriptions(): array {
		$descriptions = array(
			'https://creativecommons.org/publicdomain/zero/1.0/' => __( 'Die Daten sind gemeinfrei. Sie dürfen ohne Einschränkungen und ohne Namensnennung kopiert, verändert und – auch kommerziell – weiterverwendet werden.', 'open-data-wizard' ),
			'https://creativecommons.org/licenses/by/4.0/' => __( 'Die Daten dürfen frei – auch kommerziell – genutzt, verändert und weitergegeben werden, sofern die Quelle (Namensnennung) angegeben wird.', 'open-data-wizard' ),
			'https://creativecommons.org/licenses/by-sa/4.0/' => __( 'Wie CC BY: Nutzung und Bearbeitung erlaubt mit Namensnennung. Zusätzlich müssen bearbeitete Daten unter derselben Lizenz weitergegeben werden.', 'open-data-wizard' ),
		);

		return (array) apply_filters( 'odw_license_descriptions', $descriptions );
	}

	/**
	 * Kuratierte Liste geografischer Regionen (Name → GeoNames-URI) für das
	 * dct:spatial-Feld. Deckt Deutschland, die 16 Bundesländer und größere Städte ab.
	 * Erweiterbar via `odw_spatial_options`-Filter.
	 *
	 * @return array<string, string> Regionsname => GeoNames-URI.
	 */
	public static function get_spatial_options(): array {
		$base    = 'https://sws.geonames.org/';
		$options = array(
			'Deutschland'            => $base . '2921044/',
			'Baden-Württemberg'      => $base . '2953481/',
			'Bayern'                 => $base . '2951839/',
			'Berlin'                 => $base . '2950157/',
			'Brandenburg'            => $base . '2945356/',
			'Bremen'                 => $base . '2944387/',
			'Hamburg'                => $base . '2911297/',
			'Hessen'                 => $base . '2905330/',
			'Mecklenburg-Vorpommern' => $base . '2872567/',
			'Niedersachsen'          => $base . '2862926/',
			'Nordrhein-Westfalen'    => $base . '2861876/',
			'Rheinland-Pfalz'        => $base . '2847618/',
			'Saarland'               => $base . '2842635/',
			'Sachsen'                => $base . '2842566/',
			'Sachsen-Anhalt'         => $base . '2842565/',
			'Schleswig-Holstein'     => $base . '2838632/',
			'Thüringen'              => $base . '2822542/',
			'München'                => $base . '2867714/',
			'Köln'                   => $base . '2886242/',
			'Frankfurt am Main'      => $base . '2925533/',
			'Stuttgart'              => $base . '2825297/',
			'Düsseldorf'             => $base . '2934246/',
			'Leipzig'                => $base . '2879139/',
			'Dresden'                => $base . '2935022/',
			'Hannover'               => $base . '2910831/',
			'Nürnberg'               => $base . '2861650/',
		);

		return (array) apply_filters( 'odw_spatial_options', $options );
	}

	/**
	 * Translate a license URI to its human-readable label.
	 * Checks known options first, then the external licenses.txt list.
	 *
	 * @param  string $uri License URI (or 'sonstige').
	 * @return string Human-readable label, or the URI itself if not found.
	 */
	public static function get_license_label( string $uri ): string {
		$options = self::get_license_options();

		if ( isset( $options[ $uri ] ) ) {
			return $options[ $uri ];
		}

		$extended = self::load_license_list();

		return $extended[ $uri ] ?? $uri;
	}

	/**
	 * Themen-Vokabular als EU-Vocabulary-URI → Label Map.
	 *
	 * @return array<string, string>
	 */
	public static function get_theme_options(): array {
		$base    = 'http://publications.europa.eu/resource/authority/data-theme/';
		$options = array(
			''             => __( '— Bitte wählen —', 'open-data-wizard' ),
			$base . 'EDUC' => __( 'Bildung, Kultur & Sport', 'open-data-wizard' ),
			$base . 'HEAL' => __( 'Gesundheit', 'open-data-wizard' ),
			$base . 'SOCI' => __( 'Bevölkerung & Gesellschaft', 'open-data-wizard' ),
			$base . 'ENVI' => __( 'Umwelt', 'open-data-wizard' ),
			$base . 'ECON' => __( 'Wirtschaft & Finanzen', 'open-data-wizard' ),
			$base . 'GOVE' => __( 'Verwaltung & öffentlicher Sektor', 'open-data-wizard' ),
			$base . 'TECH' => __( 'Wissenschaft & Technologie', 'open-data-wizard' ),
			$base . 'TRAN' => __( 'Verkehr', 'open-data-wizard' ),
			$base . 'AGRI' => __( 'Landwirtschaft & Ernährung', 'open-data-wizard' ),
			$base . 'ENER' => __( 'Energie', 'open-data-wizard' ),
			$base . 'JUST' => __( 'Justiz & Sicherheit', 'open-data-wizard' ),
			$base . 'REGI' => __( 'Regionen & Städte', 'open-data-wizard' ),
			$base . 'INTR' => __( 'Internationale Themen', 'open-data-wizard' ),
		);

		return (array) apply_filters( 'odw_theme_options', $options );
	}

	/**
	 * Aktualisierungsfrequenzen.
	 *
	 * @return array<string, string>
	 */
	public static function get_periodicity_options(): array {
		$base = 'http://publications.europa.eu/resource/authority/frequency/';
		return array(
			''                  => __( '— Bitte wählen —', 'open-data-wizard' ),
			$base . 'DAILY'     => __( 'Täglich', 'open-data-wizard' ),
			$base . 'WEEKLY'    => __( 'Wöchentlich', 'open-data-wizard' ),
			$base . 'MONTHLY'   => __( 'Monatlich', 'open-data-wizard' ),
			$base . 'QUARTERLY' => __( 'Vierteljährlich', 'open-data-wizard' ),
			$base . 'ANNUAL'    => __( 'Jährlich', 'open-data-wizard' ),
			$base . 'BIENNIAL'  => __( 'Zweijährlich', 'open-data-wizard' ),
			$base . 'IRREG'     => __( 'Unregelmäßig', 'open-data-wizard' ),
			$base . 'UNKNOWN'   => __( 'Unbekannt', 'open-data-wizard' ),
		);
	}

	/**
	 * Dateiformate aus config/dct-format-list.php.
	 *
	 * @return array<string, string>
	 */
	public static function get_format_options(): array {
		$list    = self::load_format_list();
		$options = array( '' => __( '— Bitte wählen —', 'open-data-wizard' ) );

		foreach ( array_keys( $list ) as $key ) {
			$options[ $key ] = $key;
		}

		return $options;
	}

	/**
	 * Format MIME-type mapping — loaded from config/dct-format-list.php.
	 *
	 * @param  string $format Short format label (e.g. "CSV").
	 * @return string MIME type, or the original format string if unknown.
	 */
	public static function get_format_mime( string $format ): string {
		$list = self::load_format_list();
		return $list[ $format ]['mime'] ?? $format;
	}

	/**
	 * Maps a short format label to its EU Publications Office file-type URI.
	 *
	 * @param  string $format Short format label (e.g. "CSV").
	 * @return string EU file-type URI, or the original string if unknown.
	 */
	public static function get_format_eu_uri( string $format ): string {
		$list    = self::load_format_list();
		$eu_code = $list[ $format ]['eu_uri'] ?? '';

		if ( '' === $eu_code ) {
			return $format;
		}

		return 'http://publications.europa.eu/resource/authority/file-type/' . $eu_code;
	}

	/**
	 * Language options.
	 *
	 * @return array<string, string>
	 */
	public static function get_language_options(): array {
		$base = 'http://publications.europa.eu/resource/authority/language/';
		return array(
			''            => __( '— Bitte wählen —', 'open-data-wizard' ),
			$base . 'DEU' => __( 'Deutsch (DE)', 'open-data-wizard' ),
			$base . 'ENG' => __( 'Englisch (EN)', 'open-data-wizard' ),
		);
	}

	/**
	 * Administrative geocoding level options.
	 *
	 * @return array<string, string>
	 */
	public static function get_political_geocoding_level_options(): array {
		$base = 'http://dcat-ap.de/def/politicalGeocoding/Level/';
		return array(
			''                               => __( '— Bitte wählen —', 'open-data-wizard' ),
			$base . 'federal'                => __( 'Bund (Federal)', 'open-data-wizard' ),
			$base . 'state'                  => __( 'Land (Bundesland)', 'open-data-wizard' ),
			$base . 'administrativeDistrict' => __( 'Landkreis', 'open-data-wizard' ),
			$base . 'municipality'           => __( 'Gemeinde', 'open-data-wizard' ),
		);
	}

	// -------------------------------------------------------------------------
	// HTML helpers
	// -------------------------------------------------------------------------

	/**
	 * Renders the HTML for the composite file-size widget (Änderung 8).
	 * The backing byte_size field is hidden via CSS; JS syncs the two.
	 *
	 * @return string HTML markup.
	 */
	/**
	 * Generates the HTML for the JSON-LD preview tab.
	 *
	 * @return string HTML output.
	 */
	private static function get_preview_html(): string {
		ob_start();
		?>
		<div class="odw-preview-wrapper">
			<p class="description">
		<?php esc_html_e( 'Die Vorschau zeigt das generierte JSON-LD basierend auf den zuletzt gespeicherten Feldinhalten.', 'open-data-wizard' ); ?>
		<?php esc_html_e( 'Speichern Sie den Datensatz, um die Vorschau zu aktualisieren.', 'open-data-wizard' ); ?>
			</p>
			<div id="odw-jsonld-preview">
		<?php
		$post_id = get_the_ID();
		if ( $post_id ) {
			$json = odw_build_dataset_jsonld( (int) $post_id );
			if ( $json ) {
				echo '<pre class="odw-jsonld-code">';
				echo esc_html( wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
				echo '</pre>';
			} else {
					echo '<p>' . esc_html__( 'Noch keine Daten vorhanden. Bitte erst Pflichtfelder befüllen und speichern.', 'open-data-wizard' ) . '</p>';
			}
		}
		?>
			</div>
		<?php if ( $post_id ) : ?>
				<p>
					<a href="<?php echo esc_url( rest_url( 'datenatlas/v1/datasets/' . $post_id ) ); ?>" target="_blank" class="button">
			<?php esc_html_e( 'REST-Endpoint öffnen', 'open-data-wizard' ); ?>
					</a>
				</p>
		<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed -- intentional companion function to the class above.
/**
 * Sanitise a value used as a JSON-LD "@id" (IRI).
 *
 * Values that carry a URI scheme are passed through esc_url_raw() so dangerous
 * schemes such as javascript: or data: are stripped. Bare codes or labels (e.g.
 * "de" or "Soziales") have no scheme and would be blanked by esc_url_raw(), so
 * they are returned unchanged.
 *
 * @param  string $value Raw @id value.
 * @return string Sanitised @id value.
 */
function odw_sanitize_jsonld_id( string $value ): string {
	if ( preg_match( '#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $value ) ) {
		return esc_url_raw( $value );
	}

	return $value;
}

/**
 * Build DCAT-AP 3.0 JSON-LD array for a single dataset.
 * Used by both the REST API and the preview tab.
 *
 * @param  int $post_id Dataset post ID.
 * @return array<string, mixed>|null JSON-LD array, or null when the post is not a valid dataset.
 */
function odw_build_dataset_jsonld( int $post_id ): ?array {
	$post = get_post( $post_id );

	if ( ! $post || 'odw_dataset' !== $post->post_type ) {
		return null;
	}

	$title               = $post->post_title;
	$description         = carbon_get_post_meta( $post_id, 'odw_description' );
	$publisher           = carbon_get_post_meta( $post_id, 'odw_publisher' );
	$language            = carbon_get_post_meta( $post_id, 'odw_language' );
	$keywords            = carbon_get_post_meta( $post_id, 'odw_keywords' );
	$theme               = carbon_get_post_meta( $post_id, 'odw_theme' );
	$issued              = carbon_get_post_meta( $post_id, 'odw_issued' );
	$modified            = get_post_meta( $post_id, '_odw_modified', true );
	$dist_access_url     = (string) carbon_get_post_meta( $post_id, 'odw_access_url' );
	$dist_format         = (string) carbon_get_post_meta( $post_id, 'odw_format' );
	$dist_byte_size      = (string) carbon_get_post_meta( $post_id, 'odw_byte_size' );
	$dist_license        = (string) carbon_get_post_meta( $post_id, 'odw_license' );
	$dist_license_custom = (string) carbon_get_post_meta( $post_id, 'odw_license_custom' );
	$dist_attribution    = (string) carbon_get_post_meta( $post_id, 'odw_attribution_text' );
	$cessda_topic        = (string) carbon_get_post_meta( $post_id, 'odw_cessda_topic' );

	// Extended DCAT-AP fields (Tab 4).
	$landing_page              = (string) carbon_get_post_meta( $post_id, 'odw_landing_page' );
	$accrual_periodicity       = (string) carbon_get_post_meta( $post_id, 'odw_accrual_periodicity' );
	$political_geocoding_level = (string) carbon_get_post_meta( $post_id, 'odw_political_geocoding_level' );
	$spatial                   = (string) carbon_get_post_meta( $post_id, 'odw_spatial' );
	$temporal_start            = (string) carbon_get_post_meta( $post_id, 'odw_temporal_start' );
	$temporal_end              = (string) carbon_get_post_meta( $post_id, 'odw_temporal_end' );
	$contact_name              = (string) carbon_get_post_meta( $post_id, 'odw_contact_name' );
	$contact_email             = (string) carbon_get_post_meta( $post_id, 'odw_contact_email' );
	$contact_url               = (string) carbon_get_post_meta( $post_id, 'odw_contact_url' );

	$dataset = array(
		'@type'           => 'dcat:Dataset',
		'@id'             => rest_url( 'datenatlas/v1/datasets/' . $post_id ),
		'dct:title'       => $title,
		'dct:description' => $description,
		'dct:publisher'   => array(
			'@type'     => 'foaf:Organization',
			'foaf:name' => $publisher,
		),
	);

	if ( ! empty( $language ) ) {
		$lang_base               = 'http://publications.europa.eu/resource/authority/language/';
		$lang_legacy             = array(
			'de' => $lang_base . 'DEU',
			'en' => $lang_base . 'ENG',
		);
		$lang_uri                = $lang_legacy[ (string) $language ] ?? (string) $language;
		$dataset['dct:language'] = array( '@id' => odw_sanitize_jsonld_id( $lang_uri ) );
	}

	if ( ! empty( $keywords ) && is_string( $keywords ) ) {
		$keyword_list = array_values( array_filter( array_map( 'trim', explode( "\n", $keywords ) ) ) );
		if ( ! empty( $keyword_list ) ) {
			$dataset['dcat:keyword'] = $keyword_list;
		}
	}

	if ( ! empty( $theme ) ) {
		$theme_base            = 'http://publications.europa.eu/resource/authority/data-theme/';
		$theme_legacy          = array(
			'Bildung'    => $theme_base . 'EDUC',
			'Gesundheit' => $theme_base . 'HEAL',
			'Soziales'   => $theme_base . 'SOCI',
			'Umwelt'     => $theme_base . 'ENVI',
			'Wirtschaft' => $theme_base . 'ECON',
			'Kultur'     => $theme_base . 'EDUC',
			'Sport'      => $theme_base . 'EDUC',
			'Sonstiges'  => $theme_base . 'GOVE',
		);
		$dataset['dcat:theme'] = array( '@id' => odw_sanitize_jsonld_id( $theme_legacy[ (string) $theme ] ?? (string) $theme ) );
	}

	if ( ! empty( $cessda_topic ) ) {
		$dataset['cessda:topic'] = array( '@id' => odw_sanitize_jsonld_id( (string) $cessda_topic ) );
	}

	if ( ! empty( $issued ) ) {
		$dataset['dct:issued'] = array(
			'@type'  => 'xsd:date',
			'@value' => $issued,
		);
	}

	if ( ! empty( $modified ) ) {
		$dataset['dct:modified'] = array(
			'@type'  => 'xsd:date',
			'@value' => $modified,
		);
	}

	$dist_access_url_safe = esc_url_raw( $dist_access_url );
	if ( ! empty( $dist_access_url_safe ) ) {
		$dist_item = array(
			'@type'          => 'dcat:Distribution',
			'dcat:accessURL' => $dist_access_url_safe,
		);

		if ( ! empty( $dist_format ) ) {
			$dist_item['dct:format'] = array( '@id' => ODW_Fields::get_format_eu_uri( $dist_format ) );
		}

		$byte_size_int = (int) $dist_byte_size;
		if ( $byte_size_int > 0 ) {
			$dist_item['dcat:byteSize'] = $byte_size_int;
		}

		$effective_license = $dist_license;
		if ( 'sonstige' === $dist_license && ! empty( $dist_license_custom ) ) {
			$effective_license = $dist_license_custom;
		}
		if ( ! empty( $effective_license ) && 'sonstige' !== $effective_license ) {
			$dist_item['dct:license'] = array( '@id' => odw_sanitize_jsonld_id( (string) $effective_license ) );
		}

		if ( ! empty( $dist_attribution ) ) {
			$dist_item['dcatde:licenseAttributionByText'] = $dist_attribution;
		}

		$dataset['dcat:distribution'] = array( $dist_item );
	}

	// Extended DCAT-AP fields (Tab 4).

	if ( ! empty( $landing_page ) ) {
		$dataset['dcat:landingPage'] = array( '@id' => odw_sanitize_jsonld_id( (string) $landing_page ) );
	}

	if ( ! empty( $accrual_periodicity ) ) {
		$dataset['dct:accrualPeriodicity'] = array( '@id' => odw_sanitize_jsonld_id( (string) $accrual_periodicity ) );
	}

	if ( ! empty( $political_geocoding_level ) ) {
		$dataset['dcatde:politicalGeocodingLevelURI'] = array( '@id' => odw_sanitize_jsonld_id( (string) $political_geocoding_level ) );
	}

	if ( ! empty( $spatial ) ) {
		$location = array(
			'@type'          => 'dct:Location',
			'skos:prefLabel' => $spatial,
		);

		// Link to GeoNames when the value matches a curated region name; if the
		// value is itself a URI, use it directly as @id.
		$spatial_map = ODW_Fields::get_spatial_options();
		if ( isset( $spatial_map[ $spatial ] ) ) {
			$location['@id'] = odw_sanitize_jsonld_id( (string) $spatial_map[ $spatial ] );
		} elseif ( preg_match( '#^https?://#', (string) $spatial ) ) {
			$location['@id'] = odw_sanitize_jsonld_id( (string) $spatial );
			unset( $location['skos:prefLabel'] );
		}

		$dataset['dct:spatial'] = $location;
	}

	if ( ! empty( $temporal_start ) || ! empty( $temporal_end ) ) {
		$period = array( '@type' => 'dct:PeriodOfTime' );
		if ( ! empty( $temporal_start ) ) {
			$period['dcat:startDate'] = array(
				'@type'  => 'xsd:date',
				'@value' => $temporal_start,
			);
		}
		if ( ! empty( $temporal_end ) ) {
			$period['dcat:endDate'] = array(
				'@type'  => 'xsd:date',
				'@value' => $temporal_end,
			);
		}
		$dataset['dct:temporal'] = $period;
	}

	if ( ! empty( $contact_name ) || ! empty( $contact_email ) ) {
		$contact = array( '@type' => 'vcard:Organization' );
		if ( ! empty( $contact_name ) ) {
			$contact['vcard:fn'] = $contact_name;
		}
		if ( ! empty( $contact_email ) ) {
			$contact['vcard:hasEmail'] = 'mailto:' . $contact_email;
		}
		if ( ! empty( $contact_url ) ) {
			$contact['vcard:hasURL'] = array( '@id' => odw_sanitize_jsonld_id( (string) $contact_url ) );
		}
		$dataset['dcat:contactPoint'] = $contact;
	}

	/**
	 * Filters the complete DCAT-AP JSON-LD array before output.
	 *
	 * @param array<string, mixed> $dataset  The JSON-LD dataset array.
	 * @param int                  $post_id  The dataset post ID.
	 */
	return (array) apply_filters( 'odw_dataset_jsonld', $dataset, $post_id );
}

/**
 * Compute byte size from a distribution array.
 * Supports the legacy single-field format (byte_size in bytes)
 * and the new composite format (byte_size_value + byte_size_unit).
 *
 * @param  array<string, mixed> $dist Distribution sub-array from Carbon Fields.
 * @return int Byte count, or 0 if not set.
 */
function odw_compute_byte_size( array $dist ): int {
	// New composite format.
	$value = $dist['byte_size_value'] ?? '';
	if ( '' !== $value && is_numeric( $value ) && (float) $value > 0 ) {
		$unit    = (string) ( $dist['byte_size_unit'] ?? 'MB' );
		$factors = array(
			'KB' => 1024,
			'MB' => 1048576,
			'GB' => 1073741824,
		);
		return (int) round( (float) $value * ( $factors[ $unit ] ?? 1048576 ) );
	}

	// Legacy: raw byte count.
	$legacy = $dist['byte_size'] ?? '';
	if ( '' !== $legacy && is_numeric( $legacy ) && (int) $legacy >= 0 ) {
		return (int) $legacy;
	}

	return 0;
}
