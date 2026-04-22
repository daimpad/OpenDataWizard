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
	 */
	private static function register_required_fields(): void {
		Container::make( 'post_meta', __( 'Pflichtangaben', 'open-data-wizard' ) )
			->where( 'post_type', '=', 'odw_dataset' )
			->set_priority( 'high' )
			->add_tab(
				__( '1 — Pflichtangaben', 'open-data-wizard' ),
				array(
					Field::make( 'html', 'odw_description_tab1_hint' )
						->set_html( '<p class="description">' . esc_html__( 'Pflichtfelder gemäß DCAT-AP 3.0. Ohne diese Angaben kann der Datensatz nicht veröffentlicht werden.', 'open-data-wizard' ) . '</p>' ),

					Field::make( 'text', 'odw_publisher', __( 'Wer gibt diese Daten heraus?', 'open-data-wizard' ) )
						->set_required( true )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_publisher' ) : '' )
						->set_attribute( 'placeholder', __( 'z.B. Musterorganisation e.V.', 'open-data-wizard' ) )
						->set_help_text( __( 'HERAUSGEBENDE ORGANISATION (dct:publisher)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Musterstadt Statistikamt, Umweltbundesamt, Verbraucherzentrale e.V.', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_description', __( 'Worum geht es in diesem Datensatz?', 'open-data-wizard' ) )
						->set_required( true )
						->set_rows( 5 )
						->set_attribute( 'placeholder', __( 'Kurze Beschreibung des Datensatzes…', 'open-data-wizard' ) )
						->set_help_text( __( 'BESCHREIBUNG (dct:description)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Ein Überblick über die bevölkerungsreichsten Städte in Deutschland mit statistischen Daten zu Einwohnerzahl und Entwicklung.', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_license', __( 'Unter welcher Lizenz sind diese Daten verfügbar?', 'open-data-wizard' ) )
						->set_required( true )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_license' ) : '' )
						->add_options( self::get_license_options() )
						->set_help_text( __( 'LIZENZ (dct:license)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: CC0 1.0, CC-BY 4.0 – Diese bestimmt, wie andere die Daten nutzen dürfen.', 'open-data-wizard' ) ),
				)
			)
			->add_tab(
				__( '2 — Optionale Angaben', 'open-data-wizard' ),
				array(
					Field::make( 'select', 'odw_language', __( 'In welcher Sprache sind die Daten?', 'open-data-wizard' ) )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_language' ) : '' )
						->add_options(
							array(
								''   => __( '— Bitte wählen —', 'open-data-wizard' ),
								'de' => __( 'Deutsch (DE)', 'open-data-wizard' ),
								'en' => __( 'Englisch (EN)', 'open-data-wizard' ),
							)
						)
						->set_help_text( __( 'SPRACHE (dct:language)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Deutsch, Englisch', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_keywords', __( 'Mit welchen Stichworten finde ich diese Daten?', 'open-data-wizard' ) )
						->set_rows( 3 )
						->set_attribute( 'placeholder', __( 'z.B. Umwelt', 'open-data-wizard' ) )
						->set_help_text( __( 'SCHLAGWORTE (dcat:keyword)', 'open-data-wizard' ) . "\n\n" . __( 'Jedes Schlagwort in einer eigenen Zeile. Beispiel: Umwelt, Wasser, Luftverschmutzung', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_theme', __( 'In welche Kategorie gehört dieser Datensatz?', 'open-data-wizard' ) )
						->add_options( self::get_theme_options() )
						->set_help_text( __( 'THEMA (dcat:theme)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Umwelt, Bildung, Gesundheit, Wirtschaft, Kultur', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_issued', __( 'Wann wurden diese Daten zum ersten Mal veröffentlicht?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options( array( 'dateFormat' => 'Y-m-d' ) )
						->set_help_text( __( 'VERÖFFENTLICHUNGSDATUM (dct:issued)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-01-15', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_modified', __( 'Wann wurden diese Daten zuletzt aktualisiert?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options( array( 'dateFormat' => 'Y-m-d' ) )
						->set_help_text( __( 'ÄNDERUNGSDATUM (dct:modified)', 'open-data-wizard' ) . "\n\n" . __( 'Wird automatisch bei jeder Speicherung aktualisiert. Beispiel: 2026-04-22', 'open-data-wizard' ) ),
				)
			)
			->add_tab(
				__( '3 — Distribution', 'open-data-wizard' ),
				array(
					Field::make( 'complex', 'odw_distributions', __( 'Wo können die Daten heruntergeladen werden?', 'open-data-wizard' ) )
						->set_min( 1 )
						->set_collapsed( false )
						->add_fields(
							array(
								Field::make( 'text', 'access_url', __( 'Wo kann ich die Datei herunterladen?', 'open-data-wizard' ) )
									->set_required( true )
									->set_attribute( 'placeholder', 'https://beispiel.de/daten/datei.csv' )
									->set_attribute( 'type', 'url' )
									->set_help_text( __( 'ZUGRIFFS-URL (dcat:accessURL)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: https://beispiel.de/daten/datei.csv', 'open-data-wizard' ) ),

								Field::make( 'select', 'format', __( 'In welchem Format ist die Datei?', 'open-data-wizard' ) )
									->add_options( self::get_format_options() )
									->set_help_text( __( 'FORMAT (dct:format)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: CSV, JSON, PDF', 'open-data-wizard' ) ),

								Field::make( 'text', 'byte_size', __( 'Wie groß ist die Datei (in Bytes)?', 'open-data-wizard' ) )
									->set_attribute( 'placeholder', __( 'optional, z.B. 204800', 'open-data-wizard' ) )
									->set_attribute( 'type', 'number' )
									->set_attribute( 'min', '0' )
									->set_help_text( __( 'DATEIGRÖSSE IN BYTES (dcat:byteSize)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 204800 (ca. 200 KB). Optional.', 'open-data-wizard' ) ),
							)
						)
						->set_help_text( __( 'DISTRIBUTIONEN (dcat:distribution)', 'open-data-wizard' ) . "\n\n" . __( 'Sie können mehrere Dateiformate (z.B. CSV und JSON) als separate Distributionen anbieten.', 'open-data-wizard' ) ),

				)
			)
			->add_tab(
				__( '4 — Erweiterte Angaben', 'open-data-wizard' ),
				array(
					// --- Projektseite & Aktualität ---
					Field::make( 'html', 'odw_ext_hint_landing' )
						->set_html( '<h4 style="margin:0 0 4px">' . esc_html__( 'Projektseite & Aktualität', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'text', 'odw_landing_page', __( 'Wo finde ich mehr Informationen zu diesem Projekt?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/projekt' )
						->set_help_text( __( 'PROJEKTSEITE (dcat:landingPage)', 'open-data-wizard' ) . "\n\n" . __( 'URL der Projektwebsite oder des Datenportals mit weiteren Informationen zum Datensatz. Beispiel: https://beispiel.de/projekt', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_accrual_periodicity', __( 'Wie oft werden diese Daten aktualisiert?', 'open-data-wizard' ) )
						->add_options( self::get_periodicity_options() )
						->set_help_text( __( 'AKTUALISIERUNGSFREQUENZ (dct:accrualPeriodicity)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Täglich, Monatlich, Jährlich, Unregelmäßig', 'open-data-wizard' ) ),

					// --- Abdeckung ---
					Field::make( 'html', 'odw_ext_hint_coverage' )
						->set_html( '<h4 style="margin:16px 0 4px">' . esc_html__( 'Abdeckung', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'text', 'odw_spatial', __( 'Welche geografische Region betreffen diese Daten?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Deutschland, Berlin oder GeoNames-URI', 'open-data-wizard' ) )
						->set_help_text( __( 'GEOGRAPHISCHE ABDECKUNG (dct:spatial)', 'open-data-wizard' ) . "\n\n" . __( 'Freitext oder URI. Beispiel: Deutschland, Berlin, https://sws.geonames.org/2950159/', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_temporal_start', __( 'Ab wann sind diese Daten gültig?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options( array( 'dateFormat' => 'Y-m-d' ) )
						->set_help_text( __( 'ZEITLICHER BEZUG — START (dct:temporal)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-01-01', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_temporal_end', __( 'Bis wann sind diese Daten gültig?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options( array( 'dateFormat' => 'Y-m-d' ) )
						->set_help_text( __( 'ZEITLICHER BEZUG — ENDE (dct:temporal)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-12-31', 'open-data-wizard' ) ),

					// --- Kontaktpunkt ---
					Field::make( 'html', 'odw_ext_hint_contact' )
						->set_html( '<h4 style="margin:16px 0 4px">' . esc_html__( 'Kontaktpunkt (dcat:contactPoint)', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'text', 'odw_contact_name', __( 'Wer ist Ansprechperson für Fragen zu diesen Daten?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Open Data Team', 'open-data-wizard' ) )
						->set_help_text( __( 'NAME / ORGANISATION (dcat:contactPoint)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Open Data Team, Statistisches Landesamt', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_contact_email', __( 'Unter welcher E-Mail-Adresse kann ich Fragen stellen?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'email' )
						->set_attribute( 'placeholder', 'opendata@beispiel.de' )
						->set_help_text( __( 'E-MAIL-ADRESSE (dcat:contactPoint)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: opendata@beispiel.de', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_contact_url', __( 'Auf welcher Website finde ich weitere Kontaktinformationen?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/kontakt' )
						->set_help_text( __( 'WEBSITE (dcat:contactPoint)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: https://beispiel.de/kontakt', 'open-data-wizard' ) ),
				)
			)
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

		// Update without triggering an infinite loop.
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
	 *
	 * @return array<int, array{meta_key: string, label: string}>
	 */
	public static function get_required_fields(): array {
		return array(
			array(
				'meta_key' => '_odw_description',
				'label'    => __( 'Worum geht es in diesem Datensatz?', 'open-data-wizard' ),
			),
			array(
				'meta_key' => '_odw_publisher',
				'label'    => __( 'Wer gibt diese Daten heraus?', 'open-data-wizard' ),
			),
			array(
				'meta_key' => '_odw_license',
				'label'    => __( 'Unter welcher Lizenz sind diese Daten verfügbar?', 'open-data-wizard' ),
			),
		);
	}

	// -------------------------------------------------------------------------
	// Controlled vocabulary options
	// -------------------------------------------------------------------------

	/**
	 * Lizenzen als URI → Label Map für Select-Felder und den `odw_license_options`-Filter.
	 *
	 * @return array<string, string> Erweiterbar via `add_filter('odw_license_options', ...)`.
	 */
	public static function get_license_options(): array {
		$options = array(
			''                                             => __( '— Bitte wählen —', 'open-data-wizard' ),
			'https://creativecommons.org/publicdomain/zero/1.0/' => 'CC0 1.0',
			'https://creativecommons.org/licenses/by/4.0/' => 'CC-BY 4.0',
			'https://creativecommons.org/licenses/by-sa/4.0/' => 'CC-BY-SA 4.0',
			'https://www.govdata.de/dl-de/by-2-0'          => 'Datenlizenz Deutschland Namensnennung 2.0',
		);

		return (array) apply_filters( 'odw_license_options', $options );
	}

	/**
	 * Translate a license URI to its human-readable label.
	 * Single source of truth — used by Fields and Admin classes.
	 *
	 * @param string $uri License URI.
	 * @return string Human-readable label, or the URI itself if not found.
	 */
	public static function get_license_label( string $uri ): string {
		$options = self::get_license_options();
		return $options[ $uri ] ?? $uri;
	}

	/**
	 * Themen-Vokabular als Label → Label Map für das DCAT-AP `dcat:theme`-Feld.
	 *
	 * @return array<string, string> Erweiterbar via `add_filter('odw_theme_options', ...)`.
	 */
	public static function get_theme_options(): array {
		$options = array(
			''           => __( '— Bitte wählen —', 'open-data-wizard' ),
			'Bildung'    => __( 'Bildung', 'open-data-wizard' ),
			'Gesundheit' => __( 'Gesundheit', 'open-data-wizard' ),
			'Soziales'   => __( 'Soziales', 'open-data-wizard' ),
			'Umwelt'     => __( 'Umwelt', 'open-data-wizard' ),
			'Wirtschaft' => __( 'Wirtschaft', 'open-data-wizard' ),
			'Kultur'     => __( 'Kultur', 'open-data-wizard' ),
			'Sport'      => __( 'Sport', 'open-data-wizard' ),
			'Sonstiges'  => __( 'Sonstiges', 'open-data-wizard' ),
		);

		return (array) apply_filters( 'odw_theme_options', $options );
	}

	/**
	 * Aktualisierungsfrequenzen aus dem EU Publications Office Frequency Vocabulary.
	 *
	 * Basis-URI: http://publications.europa.eu/resource/authority/frequency/
	 * Vollständige URI wird als Wert gespeichert und im JSON-LD als `@id` ausgegeben.
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
	 * Dateiformate als Kurzbezeichnung → Kurzbezeichnung Map für das Distribution-Feld.
	 * Die Kurzbezeichnung wird via `get_format_mime()` in den MIME-Typ für JSON-LD übersetzt.
	 *
	 * @return array<string, string>
	 */
	public static function get_format_options(): array {
		return array(
			''          => __( '— Bitte wählen —', 'open-data-wizard' ),
			'CSV'       => 'CSV',
			'JSON'      => 'JSON',
			'XLSX'      => 'XLSX',
			'PDF'       => 'PDF',
			'GeoJSON'   => 'GeoJSON',
			'XML'       => 'XML',
			'Sonstiges' => __( 'Sonstiges', 'open-data-wizard' ),
		);
	}

	/**
	 * Format MIME-type mapping for JSON-LD output.
	 *
	 * @param string $format Short format label (e.g. "CSV").
	 * @return string MIME type, or the original format string if unknown.
	 */
	public static function get_format_mime( string $format ): string {
		$map = array(
			'CSV'     => 'text/csv',
			'JSON'    => 'application/json',
			'XLSX'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'PDF'     => 'application/pdf',
			'GeoJSON' => 'application/geo+json',
			'XML'     => 'application/xml',
		);

		return $map[ $format ] ?? $format;
	}

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
 * Build DCAT-AP 3.0 JSON-LD array for a single dataset.
 * Used by both the REST API and the preview tab.
 *
 * @param int $post_id Dataset post ID.
 * @return array<string, mixed>|null JSON-LD array, or null when the post is not a valid dataset.
 */
function odw_build_dataset_jsonld( int $post_id ): ?array {
	$post = get_post( $post_id );

	if ( ! $post || 'odw_dataset' !== $post->post_type ) {
		return null;
	}

	$title         = $post->post_title;
	$description   = carbon_get_post_meta( $post_id, 'odw_description' );
	$publisher     = carbon_get_post_meta( $post_id, 'odw_publisher' );
	$license       = carbon_get_post_meta( $post_id, 'odw_license' );
	$language      = carbon_get_post_meta( $post_id, 'odw_language' );
	$keywords      = carbon_get_post_meta( $post_id, 'odw_keywords' );
	$theme         = carbon_get_post_meta( $post_id, 'odw_theme' );
	$issued        = carbon_get_post_meta( $post_id, 'odw_issued' );
	$modified      = get_post_meta( $post_id, '_odw_modified', true );
	$distributions = carbon_get_post_meta( $post_id, 'odw_distributions' );

	// Extended DCAT-AP fields (Tab 4).
	$landing_page        = (string) carbon_get_post_meta( $post_id, 'odw_landing_page' );
	$accrual_periodicity = (string) carbon_get_post_meta( $post_id, 'odw_accrual_periodicity' );
	$spatial             = (string) carbon_get_post_meta( $post_id, 'odw_spatial' );
	$temporal_start      = (string) carbon_get_post_meta( $post_id, 'odw_temporal_start' );
	$temporal_end        = (string) carbon_get_post_meta( $post_id, 'odw_temporal_end' );
	$contact_name        = (string) carbon_get_post_meta( $post_id, 'odw_contact_name' );
	$contact_email       = (string) carbon_get_post_meta( $post_id, 'odw_contact_email' );
	$contact_url         = (string) carbon_get_post_meta( $post_id, 'odw_contact_url' );

	$dataset = array(
		'@type'           => 'dcat:Dataset',
		'@id'             => rest_url( 'datenatlas/v1/datasets/' . $post_id ),
		'dct:title'       => $title,
		'dct:description' => $description,
		'dct:publisher'   => array(
			'@type'     => 'foaf:Organization',
			'foaf:name' => $publisher,
		),
		'dct:license'     => $license,
	);

	if ( ! empty( $language ) ) {
		$dataset['dct:language'] = $language;
	}

	if ( ! empty( $keywords ) && is_string( $keywords ) ) {
		$keyword_list = array_values( array_filter( array_map( 'trim', explode( "\n", $keywords ) ) ) );
		if ( ! empty( $keyword_list ) ) {
			$dataset['dcat:keyword'] = $keyword_list;
		}
	}

	if ( ! empty( $theme ) ) {
		$dataset['dcat:theme'] = $theme;
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

	if ( ! empty( $distributions ) && is_array( $distributions ) ) {
		$dist_list = array();
		foreach ( $distributions as $dist ) {
			// esc_url_raw() strips javascript:, data:, and other non-HTTP schemes.
			$access_url = esc_url_raw( (string) ( $dist['access_url'] ?? '' ) );
			if ( empty( $access_url ) ) {
				continue;
			}

			$dist_item = array(
				'@type'          => 'dcat:Distribution',
				'dcat:accessURL' => $access_url,
			);

			if ( ! empty( $dist['format'] ) ) {
				$dist_item['dct:format'] = ODW_Fields::get_format_mime( $dist['format'] );
			}

			if ( isset( $dist['byte_size'] ) && '' !== $dist['byte_size'] && is_numeric( $dist['byte_size'] ) && (int) $dist['byte_size'] >= 0 ) {
				$dist_item['dcat:byteSize'] = (int) $dist['byte_size'];
			}

			$dist_list[] = $dist_item;
		}

		if ( ! empty( $dist_list ) ) {
			$dataset['dcat:distribution'] = $dist_list;
		}
	}

	// Extended DCAT-AP fields (Tab 4).

	if ( ! empty( $landing_page ) ) {
		$dataset['dcat:landingPage'] = array( '@id' => $landing_page );
	}

	if ( ! empty( $accrual_periodicity ) ) {
		$dataset['dct:accrualPeriodicity'] = array( '@id' => $accrual_periodicity );
	}

	if ( ! empty( $spatial ) ) {
		$dataset['dct:spatial'] = array(
			'@type'          => 'dct:Location',
			'skos:prefLabel' => $spatial,
		);
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
			$contact['vcard:hasURL'] = array( '@id' => $contact_url );
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
