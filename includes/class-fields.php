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
		// Auf 'save_post' (nicht 'save_post_odw_dataset'): Carbon Fields speichert
		// die Formularwerte auf 'save_post'@10, und WordPress feuert
		// save_post_{post_type} grundsätzlich DAVOR — nur mit 'save_post'@20 läuft
		// das Auto-Update nach CF und wird nicht vom Formularwert überschrieben.
		add_action( 'save_post', array( self::class, 'set_modified_date' ), 20, 2 );
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
	 *   2 — Sprache & Übersetzungen
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
						// Pflicht wird NICHT über Carbon Fields' set_required erzwungen
						// (das sperrt schon das Speichern eines leeren Entwurfs). Stattdessen
						// markiert JS das Feld mit einem Sternchen und die Server-Validierung
						// (ODW_Validation) blockiert nur die Veröffentlichung.
						->set_attribute( 'data-odw-required', '1' )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_publisher' ) : '' )
						->set_attribute( 'placeholder', __( 'z.B. Musterorganisation e.V.', 'open-data-wizard' ) )
						->set_help_text( __( 'HERAUSGEBENDE ORGANISATION (dct:publisher)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Musterstadt Statistikamt, Umweltbundesamt, Verbraucherzentrale e.V.', 'open-data-wizard' ) ),

					// Beschreibung direkt nach dem Herausgeber (beide Pflicht), damit die
					// zwei wichtigsten Angaben oben stehen (B3).
					Field::make( 'textarea', 'odw_description', __( 'Worum geht es in diesem Datensatz?', 'open-data-wizard' ) )
						// Pflicht nur zum Veröffentlichen (siehe odw_publisher).
						->set_attribute( 'data-odw-required', '1' )
						->set_rows( 5 )
						->set_attribute( 'placeholder', __( 'Kurze Beschreibung des Datensatzes…', 'open-data-wizard' ) )
						->set_help_text( __( 'BESCHREIBUNG (dct:description)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Ein Überblick über die bevölkerungsreichsten Städte in Deutschland mit statistischen Daten zu Einwohnerzahl und Entwicklung.', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_theme', __( 'Welchem Thema ist dieser Datensatz zugeordnet?', 'open-data-wizard' ) )
						->add_options( self::get_theme_options() )
						->set_help_text( __( 'THEMA (dcat:theme)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Umwelt, Bildung, Gesundheit, Wirtschaft, Kultur', 'open-data-wizard' ) ),

					// Schlagworte gehören zur inhaltlichen Erschließung und stehen
					// deshalb bei Thema, CESSDA und Engagementfeld statt bei Sprache
					// und Übersetzungen. Sie bleiben oberhalb der aufklappbaren
					// Untergruppe sichtbar — an ihnen hängen 30 MQA-Punkte.
					Field::make( 'textarea', 'odw_keywords', __( 'Mit welchen Schlagworten finde ich diese Daten?', 'open-data-wizard' ) )
						->set_rows( 3 )
						->set_attribute( 'placeholder', __( "Umwelt\nWasser\nLuftverschmutzung", 'open-data-wizard' ) )
						->set_help_text( __( 'SCHLAGWORTE (dcat:keyword)', 'open-data-wizard' ) . "\n\n" . __( "Jedes Schlagwort in eine eigene Zeile (nicht mit Komma trennen). Beispiel:\nUmwelt\nWasser\nLuftverschmutzung", 'open-data-wizard' ) ),

					// Weniger häufige Einordnungen (CESSDA, ZiviZ) als aufklappbare
					// Untergruppe ans Tab-Ende — entschlackt den Einstieg für die
					// typische Nutzung (B3, Accordion-Muster wie Tab 4).
					Field::make( 'html', 'odw_hint_tab1_extra' )
					->set_html(
						'<button type="button" class="odw-section-toggle" data-odw-section-toggle="tab1extra" aria-expanded="false">'
						. '<span class="odw-section-caret" aria-hidden="true">▸</span> '
						. esc_html__( 'Weitere Einordnung (optional)', 'open-data-wizard' )
						. '</button>'
					),

					Field::make( 'text', 'odw_cessda_topic', __( 'Ordnen Sie den Datensatz einem oder mehreren Themenfeld nach CESSDA-Vokabular zu', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-backing', 'cessda' )
						->set_help_text( __( 'CESSDA THEMENKLASSIFIKATION (dct:subject)', 'open-data-wizard' ) . "\n\n" . __( 'Aus dem CESSDA Controlled Vocabulary (Version 4.2.3, Deutsch). Beispiel: Volkszählungen, Migration, Wirtschaftspolitik', 'open-data-wizard' ) ),

					// Der Wert landet als dct:subject am Datensatz, nicht am Herausgeber.
					// Die Frage muss das abbilden — sonst tragen Redakteur:innen das
					// Tätigkeitsfeld ihrer Organisation ein und der Datensatz erhält
					// eine inhaltlich falsche Sacherschließung.
					Field::make( 'text', 'odw_engagementfeld', __( 'Welchem Engagementfeld ist dieser Datensatz zuzuordnen?', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-vocab', 'engagementfeld' )
						->set_attribute( 'placeholder', __( 'Engagementfeld eintippen oder auswählen…', 'open-data-wizard' ) )
						->set_help_text( __( 'ENGAGEMENTFELD (ZiviZ, dct:subject)', 'open-data-wizard' ) . "\n\n" . __( 'Optional: Ordnen Sie den Datensatz einem Engagementfeld der Zivilgesellschaft nach dem ZiviZ-Vokabular zu. Feld aus der Liste wählen — die zugehörige URI wird automatisch verwendet. Beispiel: Kultur, Sport, Umwelt- und Naturschutz.', 'open-data-wizard' ) ),

				)
			)

		// -----------------------------------------------------------------
		// Tab 2 — Sprache & Übersetzungen
		//
		// Trägt alles Sprachbezogene an einem Ort: die Hauptsprache des
		// Datensatzes und sämtliche Übersetzungs-Repeater. Titel- und
		// Beschreibungsübersetzungen standen vorher in Tab 1, die
		// Schlagwortübersetzungen hier — getrennt von ihresgleichen.
		// -----------------------------------------------------------------
			->add_tab(
				__( '2 — Sprache & Übersetzungen', 'open-data-wizard' ),
				array(
					Field::make( 'select', 'odw_language', __( 'In welcher Sprache sind die Daten?', 'open-data-wizard' ) )
						->set_default_value( class_exists( 'ODW_Settings' ) ? (string) ODW_Settings::get( 'default_language' ) : '' )
						->add_options( self::get_language_options() )
						->set_help_text( __( 'SPRACHE (dct:language)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Deutsch, Englisch', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_hint_translations' )
						->set_html( '<h4 style="margin:16px 0 4px">' . esc_html__( 'Übersetzungen (optional)', 'open-data-wizard' ) . '</h4><p class="description" style="margin:0">' . esc_html__( 'Titel, Beschreibung und Schlagworte zusätzlich in weiteren Sprachen — für mehrsprachige, DCAT-AP-konforme Metadaten. Die Angaben in Tab 1 bleiben die Hauptsprache.', 'open-data-wizard' ) . '</p>' ),

					Field::make( 'complex', 'odw_title_translations', __( 'Titel in weiteren Sprachen', 'open-data-wizard' ) )
						->set_collapsed( true )
						->setup_labels(
							array(
								'singular_name' => __( 'Übersetzung', 'open-data-wizard' ),
								'plural_name'   => __( 'Übersetzungen', 'open-data-wizard' ),
							)
						)
						->add_fields(
							array(
								Field::make( 'select', 'language', __( 'Sprache', 'open-data-wizard' ) )
									->add_options( self::get_language_options() )
									->set_help_text( __( 'Sprache dieser Übersetzung (dct:title @language)', 'open-data-wizard' ) ),
								Field::make( 'text', 'content', __( 'Titel', 'open-data-wizard' ) )
									->set_help_text( __( 'Übersetzter Titel', 'open-data-wizard' ) ),
							)
						)
						// Muss NACH add_fields() stehen: CF hängt das Template an die
						// zuletzt registrierte Gruppe — vorher existiert keine.
						->set_header_template( '<%- content || "' . esc_js( __( 'Übersetzung', 'open-data-wizard' ) ) . '" %>' ),

					Field::make( 'complex', 'odw_description_translations', __( 'Beschreibung in weiteren Sprachen', 'open-data-wizard' ) )
						->set_collapsed( true )
						->setup_labels(
							array(
								'singular_name' => __( 'Übersetzung', 'open-data-wizard' ),
								'plural_name'   => __( 'Übersetzungen', 'open-data-wizard' ),
							)
						)
						->add_fields(
							array(
								Field::make( 'select', 'language', __( 'Sprache', 'open-data-wizard' ) )
									->add_options( self::get_language_options() )
									->set_help_text( __( 'Sprache dieser Übersetzung (dct:description @language)', 'open-data-wizard' ) ),
								Field::make( 'textarea', 'content', __( 'Beschreibung', 'open-data-wizard' ) )
									->set_rows( 3 )
									->set_help_text( __( 'Übersetzte Beschreibung', 'open-data-wizard' ) ),
							)
						)
						->set_header_template( '<%- content || "' . esc_js( __( 'Übersetzung', 'open-data-wizard' ) ) . '" %>' ),

					Field::make( 'complex', 'odw_keyword_translations', __( 'Schlagworte in weiteren Sprachen', 'open-data-wizard' ) )
						->set_collapsed( true )
						->setup_labels(
							array(
								'singular_name' => __( 'Übersetzung', 'open-data-wizard' ),
								'plural_name'   => __( 'Übersetzungen', 'open-data-wizard' ),
							)
						)
						->set_help_text( __( 'Optional: übersetzte Schlagworte je Sprache (dcat:keyword @language).', 'open-data-wizard' ) )
						->add_fields(
							array(
								Field::make( 'select', 'language', __( 'Sprache', 'open-data-wizard' ) )
									->add_options( self::get_language_options() )
									->set_help_text( __( 'Sprache dieser Schlagworte', 'open-data-wizard' ) ),
								Field::make( 'textarea', 'keywords', __( 'Schlagworte', 'open-data-wizard' ) )
									->set_rows( 3 )
									->set_help_text( __( 'Jedes übersetzte Schlagwort in einer eigenen Zeile.', 'open-data-wizard' ) ),
							)
						)
						->set_header_template( '<%- language || "' . esc_js( __( 'Übersetzung', 'open-data-wizard' ) ) . '" %>' ),

				)
			)

		// -----------------------------------------------------------------
		// Tab 3 — Datenbereitstellung (Lizenz + Distribution)
		// -----------------------------------------------------------------
			->add_tab(
				__( '3 — Datenbereitstellung', 'open-data-wizard' ),
				array(
					Field::make( 'text', 'odw_access_url', __( 'Ergänzen Sie den Link zu Ihrem Datensatz oder laden Sie die Datei in die Mediathek hoch', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', 'https://beispiel.de/daten/datei.csv' )
						->set_attribute( 'type', 'url' )
						->set_help_text( __( 'ZUGRIFFS-URL (dcat:accessURL)', 'open-data-wizard' ) . "\n\n" . __( 'Zwei Wege — einer genügt: Tragen Sie hier den Link zu Ihrer Datei ein ODER laden Sie die Datei direkt darunter aus der Mediathek hoch. Bei einem Upload wird die URL beim Speichern automatisch übernommen; dieses Feld können Sie dann leer lassen.', 'open-data-wizard' ) ),

					// Der Datei-Upload stand früher als eigene Meta-Box in der
					// Seitenleiste — zwei Orte für denselben Sachverhalt, die zu
					// widersprüchlichen Angaben einluden. Jetzt steht er unmittelbar
					// unter der Zugriffs-URL, zu der er die Alternative ist.
					//
					// Als Closure statt als Array-Callable: is_callable() prüft beim
					// Registrieren, ODW_Admin ist dann je nach Ladereihenfolge noch
					// nicht bekannt. Der Aufruf selbst erfolgt erst beim Rendern.
					Field::make( 'html', 'odw_file_upload' )
						->set_html(
							static function (): string {
								return class_exists( 'ODW_Admin' ) ? ODW_Admin::file_upload_html() : '';
							}
						),

					Field::make( 'select', 'odw_format', __( 'In welchem Format ist die Datei?', 'open-data-wizard' ) )
						->add_options( self::get_format_options() )
						->set_help_text( __( 'FORMAT (dct:format)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: CSV, JSON, PDF', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_byte_size', __( 'Dateigröße (Bytes)', 'open-data-wizard' ) )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', '0' )
						->set_attribute( 'data-odw-backing', 'byte_size' ),

					Field::make( 'select', 'odw_license', __( 'Unter welcher Lizenz sind diese Daten verfügbar?', 'open-data-wizard' ) )
						// Pflicht nur zum Veröffentlichen (siehe odw_publisher).
						->set_attribute( 'data-odw-required', '1' )
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

					Field::make( 'select', 'odw_availability', __( 'Wie verlässlich bleibt diese Datei abrufbar?', 'open-data-wizard' ) )
						->add_options( self::get_availability_options() )
						->set_help_text( __( 'PLANBARE VERFÜGBARKEIT (dcatap:availability)', 'open-data-wizard' ) . "\n\n" . __( 'Wie verlässlich/dauerhaft ist der Zugriff auf diese Datei geplant? Beispiel: Stabil, Verfügbar, Temporär', 'open-data-wizard' ) ),

					Field::make( 'complex', 'odw_extra_distributions', __( 'Weitere Distributionen', 'open-data-wizard' ) )
						->set_help_text( __( 'MEHRERE DISTRIBUTIONEN (dcat:distribution)', 'open-data-wizard' ) . "\n\n" . __( 'Optional: zusätzliche Zugänge zu diesem Datensatz — z. B. dieselben Daten in einem weiteren Format oder unter einer anderen URL. Die oben angegebene Datei bleibt die primäre Distribution.', 'open-data-wizard' ) )
						->set_collapsed( true )
						->setup_labels(
							array(
								'singular_name' => __( 'Distribution', 'open-data-wizard' ),
								'plural_name'   => __( 'Distributionen', 'open-data-wizard' ),
							)
						)
						->add_fields(
							array(
								Field::make( 'text', 'access_url', __( 'Zugriffs-URL', 'open-data-wizard' ) )
									->set_required( true )
									->set_attribute( 'type', 'url' )
									->set_attribute( 'placeholder', 'https://beispiel.de/daten/datei.json' )
									->set_help_text( __( 'Wo kann diese Distribution abgerufen werden? (dcat:accessURL)', 'open-data-wizard' ) ),
								Field::make( 'select', 'format', __( 'Format', 'open-data-wizard' ) )
									->add_options( self::get_format_options() )
									->set_help_text( __( 'Dateiformat dieser Distribution (dct:format)', 'open-data-wizard' ) ),
								Field::make( 'text', 'byte_size', __( 'Dateigröße (Bytes)', 'open-data-wizard' ) )
									->set_attribute( 'type', 'number' )
									->set_help_text( __( 'Größe in Bytes, nur Zahl (dcat:byteSize)', 'open-data-wizard' ) ),
								Field::make( 'select', 'license', __( 'Lizenz', 'open-data-wizard' ) )
									->add_options( self::get_license_options() )
									->set_help_text( __( 'Lizenz dieser Distribution (dct:license)', 'open-data-wizard' ) ),
								Field::make( 'text', 'license_custom', __( 'Eigene Lizenz-URI (bei „Sonstige")', 'open-data-wizard' ) )
									->set_attribute( 'data-odw-autosuggest', 'license_custom' )
									->set_help_text( __( 'Nur ausfüllen, wenn oben „Sonstige" gewählt wurde.', 'open-data-wizard' ) ),
								Field::make( 'text', 'download_url', __( 'Direkter Download-Link', 'open-data-wizard' ) )
									->set_attribute( 'type', 'url' )
									->set_help_text( __( 'Direkter Download dieser Distribution (dcat:downloadURL)', 'open-data-wizard' ) ),
								Field::make( 'text', 'media_type', __( 'Media-Type (MIME)', 'open-data-wizard' ) )
									->set_help_text( __( 'IANA-Medientyp, z. B. application/json (dcat:mediaType)', 'open-data-wizard' ) ),
								Field::make( 'text', 'title', __( 'Titel der Distribution', 'open-data-wizard' ) )
									->set_help_text( __( 'Kurzer Titel dieser Distribution (dct:title)', 'open-data-wizard' ) ),
								Field::make( 'textarea', 'description', __( 'Beschreibung der Distribution', 'open-data-wizard' ) )
									->set_rows( 2 )
									->set_help_text( __( 'Kurze Beschreibung dieser Distribution (dct:description)', 'open-data-wizard' ) ),
								Field::make( 'text', 'attribution', __( 'Namensnennungstext', 'open-data-wizard' ) )
									->set_help_text( __( 'Nur bei Namensnennungslizenzen (dcatde:licenseAttributionByText)', 'open-data-wizard' ) ),
								// Das einzige Feld, das der primären Distribution bislang
								// voraus war. odw_build_distribution_node() wertet
								// 'availability' für jede Distribution aus, es fehlte nur
								// die Eingabemöglichkeit.
								Field::make( 'select', 'availability', __( 'Verlässlichkeit der Abrufbarkeit', 'open-data-wizard' ) )
									->add_options( self::get_availability_options() )
									->set_help_text( __( 'Wie verlässlich bleibt diese Distribution abrufbar? (dcatap:availability)', 'open-data-wizard' ) ),
								Field::make( 'text', 'rights', __( 'Nutzungsrechte', 'open-data-wizard' ) )
									->set_help_text( __( 'Rechtlicher Hinweis über die Lizenz hinaus (dct:rights)', 'open-data-wizard' ) ),
							)
						)
						->set_header_template( '<%- access_url || "' . esc_js( __( 'Weitere Distribution', 'open-data-wizard' ) ) . '" %>' ),
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

					Field::make( 'text', 'odw_landing_page', __( 'Wo finde ich die Projektseite zu diesen Daten?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/projekt' )
						->set_help_text( __( 'PROJEKTSEITE (dcat:landingPage)', 'open-data-wizard' ) . "\n\n" . __( 'URL der Projektwebsite oder des Datenportals mit weiteren Informationen zum Datensatz. Beispiel: https://beispiel.de/projekt', 'open-data-wizard' ) ),

					// Erstveröffentlichung und Aktualisierungsfrequenz beschreiben
					// beide die Aktualität des Datensatzes und stehen deshalb hier
					// beisammen — unter „Inhaltliche Angaben" war das Datum fehl am Platz.
					Field::make( 'date', 'odw_issued', __( 'Wann wurden diese Daten zum ersten Mal veröffentlicht?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options(
							array(
								'dateFormat' => 'Y-m-d',
								'locale'     => 'de',
							)
						)
						->set_help_text( __( 'VERÖFFENTLICHUNGSDATUM (dct:issued)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-01-15', 'open-data-wizard' ) ),

					// Das Änderungsdatum (dct:modified) hat kein Eingabefeld mehr:
					// ODW_Fields::set_modified_date() überschreibt _odw_modified bei
					// jedem Speichern, eine Eingabe ging also kommentarlos verloren.
					// Ein gesperrtes Feld mitsamt „Datum wählen"-Button vorzuhalten,
					// das niemand bedienen kann, ist Ballast. Der Wert bleibt in der
					// Übersichtsspalte „Änderungsdatum", im Qualitätsbericht und in
					// der JSON-LD-Vorschau sichtbar.
					Field::make( 'html', 'odw_hint_modified' )
						->set_html(
							'<p class="description odw-hint-auto">'
							. esc_html__( 'Das Änderungsdatum (dct:modified) wird bei jedem Speichern automatisch gesetzt.', 'open-data-wizard' )
							. '</p>'
						),

					Field::make( 'select', 'odw_accrual_periodicity', __( 'Wie oft werden diese Daten aktualisiert?', 'open-data-wizard' ) )
						->add_options( self::get_periodicity_options() )
						->set_help_text( __( 'AKTUALISIERUNGSFREQUENZ (dct:accrualPeriodicity)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Täglich, Monatlich, Jährlich, Unregelmäßig', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_ext_hint_coverage' )
					->set_html( '<h4 style="margin:16px 0 4px">' . esc_html__( 'Abdeckung', 'open-data-wizard' ) . '</h4>' ),

					Field::make( 'select', 'odw_political_geocoding_level', __( 'Auf welcher Verwaltungsebene wurden diese Daten erhoben?', 'open-data-wizard' ) )
						->add_options( self::get_political_geocoding_level_options() )
						->set_help_text( __( 'VERWALTUNGSEBENE (dcatde:politicalGeocodingLevelURI)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Gemeinde, Landkreis, Land, Bund', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_political_geocoding_uri', __( 'Auf welches amtliche Gebiet beziehen sich die Daten?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'http://dcat-ap.de/def/politicalGeocoding/regionalKey/09' )
						->set_help_text( __( 'AMTLICHER GEBIETSSCHLÜSSEL (dcatde:politicalGeocodingURI)', 'open-data-wizard' ) . "\n\n" . __( 'URI des amtlichen Regional-/Gemeindeschlüssels (AGS/ARS). Beispiel: http://dcat-ap.de/def/politicalGeocoding/regionalKey/09', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_geocoding_description', __( 'Wie lässt sich der räumliche Bezug in Worten beschreiben?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Stadtgebiet Musterstadt ohne Ortsteil X', 'open-data-wizard' ) )
						->set_help_text( __( 'RÄUMLICHE BESCHREIBUNG (dcatde:geocodingDescription)', 'open-data-wizard' ) . "\n\n" . __( 'Freitextbeschreibung des räumlichen Bezugs, ergänzend zum amtlichen Gebietsschlüssel. Beispiel: Stadtgebiet Musterstadt', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_spatial', __( 'Welche geografische Region betreffen diese Daten?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'Region aus der Liste wählen oder eintippen…', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-autosuggest', 'spatial' )
						->set_help_text( __( 'GEOGRAPHISCHE ABDECKUNG (dct:spatial)', 'open-data-wizard' ) . "\n\n" . __( 'Region aus der Liste wählen (mit GeoNames verknüpft) oder Freitext/URI eingeben. Beispiel: Deutschland, Bayern, Berlin', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_temporal_start', __( 'Ab wann reicht der Zeitraum, den die Daten abdecken?', 'open-data-wizard' ) )
						->set_storage_format( 'Y-m-d' )
						->set_picker_options(
							array(
								'dateFormat' => 'Y-m-d',
								'locale'     => 'de',
							)
						)
						->set_help_text( __( 'ZEITLICHER BEZUG — START (dct:temporal)', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: 2024-01-01', 'open-data-wizard' ) ),

					Field::make( 'date', 'odw_temporal_end', __( 'Bis wann reicht der Zeitraum, den die Daten abdecken?', 'open-data-wizard' ) )
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

					// "Ansprechperson" las sich so, als sei zwingend ein Mensch gemeint —
					// dcat:contactPoint erlaubt aber ausdrücklich auch eine Stelle oder
					// Organisation, und genau das zeigt das Beispiel im Hilfetext.
					Field::make( 'text', 'odw_contact_name', __( 'An wen kann ich mich bei Fragen zu diesen Daten wenden?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Open Data Team', 'open-data-wizard' ) )
						->set_help_text( __( 'Name einer Person, eines Teams oder einer Organisation.', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: Open Data Team, Statistisches Landesamt', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_contact_email', __( 'Unter welcher E-Mail-Adresse kann ich Fragen stellen?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'email' )
						->set_attribute( 'placeholder', 'opendata@beispiel.de' )
						->set_help_text( __( 'E-Mail-Adresse für Rückfragen.', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: opendata@beispiel.de', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_contact_url', __( 'Auf welcher Website finde ich weitere Kontaktinformationen?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/kontakt' )
						->set_help_text( __( 'Website mit weiteren Kontaktinformationen.', 'open-data-wizard' ) . "\n\n" . __( 'Beispiel: https://beispiel.de/kontakt', 'open-data-wizard' ) ),

					// "Erweiterte Angaben" — the groups below are individually
					// collapsible (progressive enhancement via odw-admin-fields.js:
					// each odw-section-toggle collapses its fields until the next).
					Field::make( 'html', 'odw_ext_hint_pro' )
					->set_html(
						'<h4 style="margin:20px 0 2px">' . esc_html__( 'Erweiterte Angaben (optional)', 'open-data-wizard' ) . '</h4>'
						. '<p class="description" style="margin:0 0 4px">' . esc_html__( 'Für Profis: DCAT-AP.de-, HVD- und weitere Detailfelder. Öffnen Sie gezielt nur die Gruppen, die Sie brauchen.', 'open-data-wizard' ) . '</p>'
					),

					Field::make( 'html', 'odw_ext_hint_responsibility' )
					->set_html(
						'<button type="button" class="odw-section-toggle" data-odw-section-toggle="responsibility" aria-expanded="false">'
						. '<span class="odw-section-caret" aria-hidden="true">▸</span> '
						. esc_html__( 'Verantwortlichkeiten & Herkunft', 'open-data-wizard' )
						. '</button>'
					),

					Field::make( 'text', 'odw_contributor_id', __( 'Welche Stelle stellt diese Daten im GovData-Verbund bereit?', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-vocab', 'contributors' )
						->set_attribute( 'placeholder', __( 'Stelle eintippen oder auswählen…', 'open-data-wizard' ) )
						->set_help_text( __( 'CONTRIBUTOR-ID (dcatde:contributorID)', 'open-data-wizard' ) . "\n\n" . __( 'Offizielle Kennung der bereitstellenden Stelle aus dem DCAT-AP.de-Verzeichnis. Stelle aus der Liste wählen; die zugehörige offizielle URI wird automatisch verwendet.', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_originator_name', __( 'Wer hat diese Daten ursprünglich erstellt?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Statistisches Landesamt', 'open-data-wizard' ) )
						->set_help_text( __( 'URHEBER (dcatde:originator)', 'open-data-wizard' ) . "\n\n" . __( 'Stelle, von der die Daten ursprünglich stammen (kann von Herausgeber abweichen).', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_originator_email', __( 'E-Mail des Urhebers', 'open-data-wizard' ) )
						->set_attribute( 'type', 'email' )
						->set_attribute( 'placeholder', 'kontakt@beispiel.de' )
						->set_help_text( __( 'E-Mail-Adresse des Urhebers (optional).', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_maintainer_name', __( 'Wer pflegt diese Daten laufend?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z.B. Open Data Team', 'open-data-wizard' ) )
						->set_help_text( __( 'PFLEGENDE STELLE (dcatde:maintainer)', 'open-data-wizard' ) . "\n\n" . __( 'Stelle, die für die laufende Pflege/Aktualisierung der Daten zuständig ist.', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_maintainer_email', __( 'E-Mail der pflegenden Stelle', 'open-data-wizard' ) )
						->set_attribute( 'type', 'email' )
						->set_attribute( 'placeholder', 'opendata@beispiel.de' )
						->set_help_text( __( 'E-Mail-Adresse der pflegenden Stelle (optional).', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_legal_basis', __( 'Auf welcher rechtlichen Grundlage werden die Daten bereitgestellt?', 'open-data-wizard' ) )
						->set_rows( 2 )
						->set_attribute( 'placeholder', __( 'z.B. § 12a EGovG, Informationsweiterverwendungsgesetz (IWG)', 'open-data-wizard' ) )
						->set_help_text( __( 'RECHTSGRUNDLAGE (dcatde:legalBasis)', 'open-data-wizard' ) . "\n\n" . __( 'Gesetz/Verordnung, das die Bereitstellung regelt. Beispiel: § 12a EGovG', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_quality_process_uri', __( 'Wo ist das Qualitätssicherungs-Verfahren dokumentiert?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/qualitaetssicherung' )
						->set_help_text( __( 'QUALITÄTSPROZESS (dcatde:qualityProcessURI)', 'open-data-wizard' ) . "\n\n" . __( 'URL zur Beschreibung des Qualitätssicherungs-Prozesses (optional).', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_ext_hint_access' )
					->set_html(
						'<button type="button" class="odw-section-toggle" data-odw-section-toggle="access" aria-expanded="false">'
						. '<span class="odw-section-caret" aria-hidden="true">▸</span> '
						. esc_html__( 'Zugriff & weitere Auffindbarkeit', 'open-data-wizard' )
						. '</button>'
					),

					// „Öffentlich“ ist bei einem Open-Data-Plugin der Normalfall und
					// steht deshalb vorausgewählt — sichtbar im Formular und jederzeit
					// änderbar. Bewusst kein Hintergrund-Automatismus aus dem
					// Beitragsstatus: Metadaten, die niemand gesehen hat, sollen nicht
					// still entstehen.
					Field::make( 'select', 'odw_access_rights', __( 'Wer darf auf diese Daten zugreifen?', 'open-data-wizard' ) )
						->add_options( self::get_access_rights_options() )
						->set_default_value( 'http://publications.europa.eu/resource/authority/access-right/PUBLIC' )
						->set_help_text( __( 'ZUGRIFFSRECHTE (dct:accessRights)', 'open-data-wizard' ) . "\n\n" . __( 'Zugriffs-Klassifikation des Datensatzes. Vorbelegt mit „Öffentlich“ — bitte ändern, wenn die Daten nur eingeschränkt oder gar nicht öffentlich zugänglich sind.', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_theme_uri', __( 'Weiteres EU-Thema (Themen-URI)?', 'open-data-wizard' ) )
						->set_attribute( 'data-odw-vocab', 'data-theme' )
						->set_attribute( 'placeholder', __( 'EU-Datenthema eintippen oder auswählen…', 'open-data-wizard' ) )
						->set_help_text( __( 'ZUSÄTZLICHES THEMA (dcat:theme)', 'open-data-wizard' ) . "\n\n" . __( 'Optional ein weiteres Thema aus der offiziellen EU-Themenliste (ergänzt das Thema aus Tab 1).', 'open-data-wizard' ) ),

					Field::make( 'html', 'odw_ext_hint_hvd' )
					->set_html(
						'<button type="button" class="odw-section-toggle" data-odw-section-toggle="hvd" aria-expanded="false">'
						. '<span class="odw-section-caret" aria-hidden="true">▸</span> '
						. esc_html__( 'High-Value-Datensatz (HVD) — nur für öffentliche Stellen', 'open-data-wizard' )
						. '</button>'
					),

					// HVD ist eine Rechtskategorie der EU-Durchführungsverordnung
					// 2023/138 und richtet sich an öffentliche Stellen. Für Vereine
					// und Verbände ist sie praktisch nie einschlägig, für Kommunen
					// dagegen schon — deshalb bleiben die Felder, sagen aber selbst,
					// für wen sie gedacht sind.
					Field::make( 'select', 'odw_is_hvd', __( 'Nur für öffentliche Stellen: Ist dies ein hochwertiger Datensatz (HVD)?', 'open-data-wizard' ) )
						->add_options( self::get_hvd_flag_options() )
						->set_help_text( __( 'HIGH-VALUE-DATENSATZ (EU-Durchführungsverordnung 2023/138)', 'open-data-wizard' ) . "\n\n" . __( 'Betrifft ausschließlich öffentliche Stellen — Vereine, Verbände und andere zivilgesellschaftliche Organisationen können dieses Feld leer lassen. Hochwertige Datensätze (HVD) sind von der EU festgelegte Datensätze mit besonderem Nutzen für Wirtschaft und Gesellschaft. Falls zutreffend, wählen Sie unten die passende Kategorie.', 'open-data-wizard' ) ),

					Field::make( 'select', 'odw_hvd_category', __( 'Welcher HVD-Kategorie gehört dieser Datensatz an?', 'open-data-wizard' ) )
						->add_options( self::get_hvd_category_options() )
						->set_help_text( __( 'HVD-KATEGORIE (dcatap:hvdCategory)', 'open-data-wizard' ) . "\n\n" . __( 'Eine der sechs EU-Themenkategorien. Beispiel: Georaum, Meteorologie, Mobilität', 'open-data-wizard' ) )
						->set_conditional_logic(
							array(
								array(
									'field'   => 'odw_is_hvd',
									'value'   => 'yes',
									'compare' => '=',
								),
							)
						),

					Field::make( 'html', 'odw_ext_hint_more' )
					->set_html(
						'<button type="button" class="odw-section-toggle" data-odw-section-toggle="more" aria-expanded="false">'
						. '<span class="odw-section-caret" aria-hidden="true">▸</span> '
						. esc_html__( 'Weitere DCAT-AP-Felder (optional)', 'open-data-wizard' )
						. '</button>'
					),

					Field::make( 'text', 'odw_identifier', __( 'Welche eindeutige Kennung hat dieser Datensatz?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z. B. DOI oder interne ID', 'open-data-wizard' ) )
						->set_help_text( __( 'IDENTIFIER (dct:identifier)', 'open-data-wizard' ) . "\n\n" . __( 'Eine eindeutige Kennung des Datensatzes (z. B. DOI oder interne ID). Beispiel: 10.1234/abcd', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_type', __( 'Um welchen Typ von Datensatz handelt es sich?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'http://publications.europa.eu/resource/authority/dataset-type/...' )
						->set_help_text( __( 'DATENSATZ-TYP (dct:type)', 'open-data-wizard' ) . "\n\n" . __( 'URI aus der EU-Liste „dataset-type". Beispiel: http://publications.europa.eu/resource/authority/dataset-type/STATISTICAL', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_creator_name', __( 'Wer hat diese Daten erstellt?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z. B. Statistisches Amt', 'open-data-wizard' ) )
						->set_help_text( __( 'ERSTELLER (dct:creator)', 'open-data-wizard' ) . "\n\n" . __( 'Stelle oder Person, die die Daten erstellt hat (kann vom Herausgeber abweichen).', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_creator_email', __( 'E-Mail-Adresse des Erstellers (optional).', 'open-data-wizard' ) )
						->set_attribute( 'type', 'email' )
						->set_help_text( __( 'E-MAIL DES ERSTELLERS (foaf:mbox)', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_version', __( 'Welche Version hat dieser Datensatz?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z. B. 1.0', 'open-data-wizard' ) )
						->set_help_text( __( 'VERSION (owl:versionInfo)', 'open-data-wizard' ) . "\n\n" . __( 'Versionsbezeichnung des Datensatzes. Beispiel: 1.0, 2024-Q1', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_version_notes', __( 'Was hat sich in dieser Version geändert?', 'open-data-wizard' ) )
						->set_rows( 2 )
						->set_help_text( __( 'VERSIONSHINWEISE (adms:versionNotes)', 'open-data-wizard' ) . "\n\n" . __( 'Kurze Beschreibung der Änderungen gegenüber der Vorversion.', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_spatial_resolution', __( 'Welche räumliche Auflösung haben die Daten (in Metern)?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', __( 'z. B. 30', 'open-data-wizard' ) )
						->set_help_text( __( 'RÄUMLICHE AUFLÖSUNG IN METERN (dcat:spatialResolutionInMeters)', 'open-data-wizard' ) . "\n\n" . __( 'Kleinste räumlich auflösbare Distanz in Metern (nur Zahl). Beispiel: 30', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_temporal_resolution', __( 'Welche zeitliche Auflösung haben die Daten?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', 'P1D' )
						->set_help_text( __( 'ZEITLICHE AUFLÖSUNG (dcat:temporalResolution)', 'open-data-wizard' ) . "\n\n" . __( 'Als ISO-8601-Dauer. Beispiel: P1D (täglich), PT1H (stündlich)', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_conforms_to', __( 'Welchem Standard oder Schema entsprechen die Daten?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://…' )
						->set_help_text( __( 'KONFORM ZU (dct:conformsTo)', 'open-data-wizard' ) . "\n\n" . __( 'URI des Standards/Anwendungsprofils, dem die Daten folgen.', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_provenance', __( 'Woher stammen die Daten und wie sind sie entstanden?', 'open-data-wizard' ) )
						->set_rows( 2 )
						->set_help_text( __( 'HERKUNFT (dct:provenance)', 'open-data-wizard' ) . "\n\n" . __( 'Freitext zur Entstehung und Herkunft der Daten.', 'open-data-wizard' ) ),

					// Die Überschrift benennt ausdrücklich die primäre Distribution:
					// Die Felder dieser Sektion sind Singular-Felder und gelten nicht
					// für die Einträge des Repeaters „Weitere Distributionen".
					Field::make( 'html', 'odw_ext_hint_dist' )
					->set_html(
						'<button type="button" class="odw-section-toggle" data-odw-section-toggle="dist" aria-expanded="false">'
						. '<span class="odw-section-caret" aria-hidden="true">▸</span> '
						. esc_html__( 'Primäre Distribution — weitere Angaben', 'open-data-wizard' )
						. '</button>'
					),

					Field::make( 'text', 'odw_dist_title', __( 'Wie heißt die bereitgestellte Datei/Distribution?', 'open-data-wizard' ) )
						->set_help_text( __( 'TITEL DER DISTRIBUTION (dct:title)', 'open-data-wizard' ) . "\n\n" . __( 'Kurzer Titel der Distribution. Beispiel: Gesamtdaten als CSV', 'open-data-wizard' ) ),

					Field::make( 'textarea', 'odw_dist_description', __( 'Wie lässt sich die Distribution beschreiben?', 'open-data-wizard' ) )
						->set_rows( 2 )
						->set_help_text( __( 'BESCHREIBUNG DER DISTRIBUTION (dct:description)', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_download_url', __( 'Direkter Download-Link zur Datei?', 'open-data-wizard' ) )
						->set_attribute( 'type', 'url' )
						->set_attribute( 'placeholder', 'https://beispiel.de/daten/datei.csv' )
						->set_help_text( __( 'DOWNLOAD-URL (dcat:downloadURL)', 'open-data-wizard' ) . "\n\n" . __( 'Direkte URL zum Herunterladen der Datei (im Gegensatz zur Zugriffs-URL).', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_media_type', __( 'Welchen Medientyp (MIME) hat die Datei?', 'open-data-wizard' ) )
						->set_attribute( 'placeholder', 'https://www.iana.org/assignments/media-types/text/csv' )
						->set_help_text( __( 'MEDIENTYP (dcat:mediaType)', 'open-data-wizard' ) . "\n\n" . __( 'IANA-Media-Type als URI. Beispiel: https://www.iana.org/assignments/media-types/text/csv', 'open-data-wizard' ) ),

					Field::make( 'text', 'odw_dist_rights', __( 'Welche Nutzungsrechte gelten für die Datei?', 'open-data-wizard' ) )
						->set_help_text( __( 'RECHTE (dct:rights)', 'open-data-wizard' ) . "\n\n" . __( 'URI oder Freitext zu den Nutzungsrechten der Distribution.', 'open-data-wizard' ) ),
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

					Field::make( 'html', 'odw_datenatlas_publish' )
					->set_html(
						'<div class="odw-datenatlas-publish">'
						. '<h4 style="margin:16px 0 4px">' . esc_html__( 'Auf dem Datenatlas Zivilgesellschaft veröffentlichen', 'open-data-wizard' ) . '</h4>'
						. '<p class="description" style="margin:0 0 10px">' . esc_html__( 'Optional: Machen Sie diesen Datensatz zusätzlich auf dem Datenatlas Zivilgesellschaft auffindbar.', 'open-data-wizard' ) . '</p>'
						. '<a href="https://datenatlas-zivilgesellschaft.de" target="_blank" rel="noopener noreferrer" class="button button-primary">'
						. esc_html__( 'Zum Datenatlas Zivilgesellschaft', 'open-data-wizard' ) . ' ↗</a>'
						. '</div>'
					),
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

		remove_action( 'save_post', array( self::class, 'set_modified_date' ), 20 );
		update_post_meta( $post_id, '_odw_modified', current_time( 'Y-m-d' ) );
		add_action( 'save_post', array( self::class, 'set_modified_date' ), 20, 2 );
	}

	// -------------------------------------------------------------------------
	// Required fields registry — single source of truth for validation
	// -------------------------------------------------------------------------

	/**
	 * Returns the required scalar fields definition used by both form rendering
	 * and the validation class. Each entry: [meta_key, label].
	 * Loaded from config/dcat-ap-fields.php.
	 *
	 * @return array<int, array{key: string, meta_key: string, label: string}>
	 */
	public static function get_required_fields(): array {
		$all = self::load_field_definitions();

		$required = array();
		foreach ( $all as $field ) {
			// Only scalar fields (with a meta_key) are validated here.
			// Distribution and title are handled separately in ODW_Validation.
			if ( $field['required'] && ! empty( $field['meta_key'] ) ) {
				$required[] = array(
					'key'      => $field['key'],
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
			'https://www.govdata.de/dl-de/by-2-0'          => __( 'Datenlizenz Deutschland — Namensnennung 2.0 (DL-DE BY)', 'open-data-wizard' ),
			'https://www.govdata.de/dl-de/zero-2-0'        => __( 'Datenlizenz Deutschland — Zero 2.0 (DL-DE Zero)', 'open-data-wizard' ),
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
			'https://www.govdata.de/dl-de/by-2-0'          => __( 'Amtliche Datenlizenz Deutschland. Die Daten dürfen frei – auch kommerziell – genutzt, verändert und weitergegeben werden, sofern die Quelle genannt wird (Namensnennung).', 'open-data-wizard' ),
			'https://www.govdata.de/dl-de/zero-2-0'        => __( 'Amtliche Datenlizenz Deutschland ohne Namensnennung. Die Daten dürfen ohne Einschränkungen und ohne Quellenangabe – auch kommerziell – weiterverwendet werden.', 'open-data-wizard' ),
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
	 * Löst einen gespeicherten Vokabular-Wert (i. d. R. eine URI) zum lesbaren
	 * Label auf. Fällt auf den Rohwert zurück, wenn keine Zuordnung existiert
	 * (z. B. Freitext-Spatial oder eigene URIs).
	 *
	 * @param string $vocab Vokabular-Kennung (theme|language|periodicity|access_rights|cessda).
	 * @param string $value Gespeicherter Wert.
	 * @return string Lesbares Label oder der Rohwert.
	 */
	/**
	 * Löst einen Themen-Wert (volle EU-URI, Code wie „SOCI" oder deutsches Label)
	 * auf die gespeicherte EU-URI auf. Rohwert-Rückfall, wenn nichts passt.
	 *
	 * @param string $value Themen-Eingabe.
	 * @return string EU-Themen-URI oder der Rohwert.
	 */
	public static function resolve_theme_uri( string $value ): string {
		$value = trim( $value );
		if ( '' === $value || preg_match( '#^https?://#i', $value ) ) {
			return $value;
		}

		$base = 'http://publications.europa.eu/resource/authority/data-theme/';
		foreach ( self::get_theme_options() as $uri => $label ) {
			if ( '' === $uri ) {
				continue;
			}
			if ( 0 === strcasecmp( str_replace( $base, '', $uri ), $value )
				|| 0 === strcasecmp( (string) $label, $value )
			) {
				return $uri;
			}
		}

		return $value;
	}

	/**
	 * Löst einen gespeicherten Vokabular-Wert (i. d. R. eine URI) zum lesbaren
	 * Label auf. Fällt auf den Rohwert zurück, wenn keine Zuordnung existiert
	 * (z. B. Freitext-Spatial oder eigene URIs).
	 *
	 * @param string $vocab Vokabular-Kennung (theme|language|periodicity|access_rights|cessda).
	 * @param string $value Gespeicherter Wert.
	 * @return string Lesbares Label oder der Rohwert.
	 */
	public static function resolve_label( string $vocab, string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		switch ( $vocab ) {
			case 'theme':
				$map = self::get_theme_options();
				break;
			case 'language':
				$map = self::get_language_options();
				break;
			case 'periodicity':
				$map = self::get_periodicity_options();
				break;
			case 'access_rights':
				$map = self::get_access_rights_options();
				break;
			case 'cessda':
				$map = self::load_cessda_options();
				break;
			default:
				$map = array();
		}

		return isset( $map[ $value ] ) && '' !== (string) $map[ $value ] ? (string) $map[ $value ] : $value;
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
	 * Returns the config entry for a format label (mime, eu_uri and MQA flags).
	 *
	 * @param  string $format Short format label (e.g. "CSV").
	 * @return array{mime?: string, eu_uri?: string, machine_readable?: bool, non_proprietary?: bool}
	 */
	public static function get_format_meta( string $format ): array {
		$list = self::load_format_list();
		if ( isset( $list[ $format ] ) ) {
			return $list[ $format ];
		}

		$key = self::resolve_format_key( $format );
		return '' !== $key ? $list[ $key ] : array();
	}

	/**
	 * Ordnet eine (Datei-)Endung dem kanonischen Format-Key zu — case- und
	 * trennzeichen-tolerant, damit z. B. „GEOJSON" → „GeoJSON" und
	 * „JSONLD" → „JSON-LD" auflösen. Leerstring, wenn kein Format passt.
	 *
	 * @param string $format Roh-Endung/Format.
	 * @return string Kanonischer Key aus dct-format-list.php oder ''.
	 */
	public static function resolve_format_key( string $format ): string {
		$list = self::load_format_list();
		if ( isset( $list[ $format ] ) ) {
			return $format;
		}

		$normalize = static function ( string $s ): string {
			return strtolower( (string) preg_replace( '/[^a-z0-9]/i', '', $s ) );
		};
		$needle    = $normalize( $format );
		if ( '' === $needle ) {
			return '';
		}

		foreach ( array_keys( $list ) as $key ) {
			if ( $normalize( (string) $key ) === $needle ) {
				return (string) $key;
			}
		}

		return '';
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

	/**
	 * High-Value-Dataset (HVD) Ja/Nein options.
	 *
	 * @return array<string, string>
	 */
	public static function get_hvd_flag_options(): array {
		return array(
			''    => __( 'Nein', 'open-data-wizard' ),
			'yes' => __( 'Ja, dieser Datensatz ist ein High-Value-Datensatz', 'open-data-wizard' ),
		);
	}

	/**
	 * High-Value-Dataset thematic categories (EU Implementing Regulation 2023/138).
	 * Values are the canonical concept URIs from the EU "High-value dataset
	 * categories" authority table (http://data.europa.eu/bna/).
	 *
	 * @return array<string, string>
	 */
	public static function get_hvd_category_options(): array {
		$base = 'http://data.europa.eu/bna/';
		return array(
			''                   => __( '— Bitte wählen —', 'open-data-wizard' ),
			$base . 'c_ac64a52d' => __( 'Georaum (Geospatial)', 'open-data-wizard' ),
			$base . 'c_dd313021' => __( 'Erdbeobachtung und Umwelt', 'open-data-wizard' ),
			$base . 'c_164e0bf5' => __( 'Meteorologie', 'open-data-wizard' ),
			$base . 'c_e1da4e07' => __( 'Statistik', 'open-data-wizard' ),
			$base . 'c_a9135398' => __( 'Unternehmen und Eigentümerschaft von Unternehmen', 'open-data-wizard' ),
			$base . 'c_b79e35eb' => __( 'Mobilität', 'open-data-wizard' ),
		);
	}

	/**
	 * Planned-availability options (DCAT-AP.de `dcatap:availability`).
	 * Values are the canonical concept URIs from the DCAT-AP.de planned
	 * availability vocabulary (http://dcat-ap.de/def/plannedAvailability/).
	 *
	 * @return array<string, string>
	 */
	public static function get_availability_options(): array {
		// EU-managed authority table (required by the EU dcatap:availability SHACL);
		// the deprecated dcat-ap.de/def/plannedAvailability list must not be used.
		$base = 'http://publications.europa.eu/resource/authority/planned-availability/';
		return array(
			''                     => __( '— Bitte wählen —', 'open-data-wizard' ),
			$base . 'AVAILABLE'    => __( 'Verfügbar (mittelfristige Planung)', 'open-data-wizard' ),
			$base . 'STABLE'       => __( 'Stabil (langfristig verfügbar)', 'open-data-wizard' ),
			$base . 'EXPERIMENTAL' => __( 'Experimentell', 'open-data-wizard' ),
			$base . 'TEMPORARY'    => __( 'Temporär (kann jederzeit entfallen)', 'open-data-wizard' ),
		);
	}

	/**
	 * Access-rights options (DCAT-AP `dct:accessRights`), sourced from the bundled
	 * EU access-right vocabulary (config/vocabularies/access-right.json).
	 *
	 * @return array<string, string>
	 */
	public static function get_access_rights_options(): array {
		$options = array( '' => __( '— Bitte wählen —', 'open-data-wizard' ) );
		foreach ( self::load_vocabulary( 'access-right' ) as $entry ) {
			$options[ $entry['value'] ] = $entry['label'];
		}
		return $options;
	}

	/**
	 * Loads a bundled controlled vocabulary from config/vocabularies/<id>.json.
	 * Each entry is normalised to { value, label } for the autosuggest widget.
	 * Results are cached in a transient for 24h.
	 *
	 * @param string $id Vocabulary identifier (e.g. 'contributors').
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function load_vocabulary( string $id ): array {
		$id = preg_replace( '/[^a-z0-9_-]/', '', $id );
		if ( '' === (string) $id ) {
			return array();
		}

		$cache_key = 'odw_vocab_' . $id;
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$file = ODW_PLUGIN_DIR . 'config/vocabularies/' . $id . '.json';
		if ( ! is_readable( $file ) ) {
			return array();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local bundled config file, not a remote URL.
		$decoded = json_decode( (string) file_get_contents( $file ), true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		$out = array();
		foreach ( $decoded as $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['value'] ) ) {
				continue;
			}
			$out[] = array(
				'value' => (string) $entry['value'],
				'label' => (string) ( $entry['label'] ?? $entry['value'] ),
			);
		}

		set_transient( $cache_key, $out, DAY_IN_SECONDS );
		return $out;
	}

	// -------------------------------------------------------------------------
	// HTML helpers
	// -------------------------------------------------------------------------

	/**
	 * Field definitions for the live wizard preview (Tab 5).
	 *
	 * Single source of truth shared by the preview markup (get_preview_html())
	 * and the JS that fills it (localised in ODW_Admin::enqueue_assets()). Each
	 * entry maps a form field to a human-readable label and flags whether the
	 * field is a publishing requirement (shown in the completeness checklist)
	 * and/or part of the summary card. The "key" matches the Carbon Fields name
	 * (without the leading underscore); the special key "title" maps to the
	 * native post-title input.
	 *
	 * @return array<int, array{key: string, label: string, required: bool, card: bool}>
	 */
	public static function get_live_preview_fields(): array {
		return array(
			array(
				'key'      => 'title',
				'label'    => __( 'Titel', 'open-data-wizard' ),
				'required' => true,
				'card'     => true,
			),
			array(
				'key'      => 'odw_publisher',
				'label'    => __( 'Herausgeber', 'open-data-wizard' ),
				'required' => true,
				'card'     => true,
			),
			array(
				'key'      => 'odw_description',
				'label'    => __( 'Beschreibung', 'open-data-wizard' ),
				'required' => true,
				'card'     => true,
			),
			array(
				'key'      => 'odw_theme',
				'label'    => __( 'Thema', 'open-data-wizard' ),
				'required' => false,
				'card'     => true,
			),
			array(
				'key'      => 'odw_access_url',
				'label'    => __( 'Zugriffs-URL', 'open-data-wizard' ),
				'required' => true,
				'card'     => true,
			),
			array(
				'key'      => 'odw_format',
				'label'    => __( 'Format', 'open-data-wizard' ),
				'required' => false,
				'card'     => true,
			),
			array(
				'key'      => 'odw_license',
				'label'    => __( 'Lizenz', 'open-data-wizard' ),
				'required' => true,
				'card'     => true,
			),
			array(
				'key'      => 'odw_byte_size',
				'label'    => __( 'Dateigröße', 'open-data-wizard' ),
				'required' => false,
				'card'     => true,
			),
		);
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
			<?php
			// Live preview panel — populated and revealed by JS (progressive
			// enhancement). Without JS it stays hidden and the saved JSON-LD
			// below remains the fallback.
			?>
			<div class="odw-live-preview" data-odw-live-preview hidden>
				<div class="odw-live-preview__head">
					<h3 class="odw-live-preview__title"><?php esc_html_e( 'Live-Vorschau', 'open-data-wizard' ); ?></h3>
					<span class="odw-live-progress" data-odw-live-progress></span>
				</div>
				<p class="description odw-live-preview__intro">
					<?php esc_html_e( 'Aktualisiert sich automatisch, während Sie die Felder ausfüllen — ohne Speichern.', 'open-data-wizard' ); ?>
				</p>
				<div class="odw-live-preview__cols">
					<div class="odw-live-preview__col">
						<h4><?php esc_html_e( 'Pflichtangaben', 'open-data-wizard' ); ?></h4>
						<ul class="odw-live-checklist" data-odw-live-checklist></ul>
					</div>
					<div class="odw-live-preview__col">
						<h4><?php esc_html_e( 'Zusammenfassung', 'open-data-wizard' ); ?></h4>
						<dl class="odw-live-card" data-odw-live-card></dl>
					</div>
				</div>
			</div>

			<p class="description">
		<?php esc_html_e( 'Die JSON-LD-Ansicht unten zeigt das zuletzt gespeicherte Ergebnis.', 'open-data-wizard' ); ?>
		<?php esc_html_e( 'Speichern Sie den Datensatz, um sie zu aktualisieren.', 'open-data-wizard' ); ?>
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
	$value = trim( $value );

	// Detect a URI scheme even when it is obfuscated with embedded whitespace or
	// control characters (e.g. " javascript:…" or "java\nscript:…"). The probe is
	// only used for the test; if a scheme is present the value is routed through
	// esc_url_raw(), which strips dangerous schemes such as javascript:/data:.
	$probe = preg_replace( '/[\s\x00-\x1F]+/', '', $value );
	if ( is_string( $probe ) && preg_match( '#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $probe ) ) {
		return esc_url_raw( $value );
	}

	return $value;
}

/**
 * Build a foaf:Agent node (name + optional mailbox) for DCAT-AP.de roles such
 * as dcatde:originator and dcatde:maintainer. Returns null when no name is set.
 *
 * @param  string $name  Agent name.
 * @param  string $email Agent e-mail (optional).
 * @return array<string, mixed>|null
 */
function odw_build_agent_node( string $name, string $email = '' ): ?array {
	if ( '' === trim( $name ) ) {
		return null;
	}

	$node = array(
		'@type'     => 'foaf:Agent',
		'foaf:name' => $name,
	);

	if ( '' !== trim( $email ) ) {
		$node['foaf:mbox'] = array( '@id' => odw_sanitize_jsonld_id( 'mailto:' . $email ) );
	}

	return $node;
}

/**
 * Resolve a controlled-vocabulary field value to its canonical URI.
 *
 * Vocabulary autosuggest fields store the human-readable label; this maps that
 * label back to the URI for JSON-LD output. A value that is already an http(s)
 * URI is returned as-is; an unknown label resolves to an empty string so it is
 * not emitted as a bogus @id.
 *
 * @param  string $value    Stored field value (label or URI).
 * @param  string $vocab_id Vocabulary identifier (e.g. 'contributors').
 * @return string Canonical URI, or '' when it cannot be resolved.
 */
function odw_resolve_vocab_uri( string $value, string $vocab_id ): string {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}

	if ( preg_match( '#^https?://#i', $value ) ) {
		return $value;
	}

	foreach ( ODW_Fields::load_vocabulary( $vocab_id ) as $entry ) {
		if ( 0 === strcasecmp( (string) $entry['label'], $value ) ) {
			return (string) $entry['value'];
		}
	}

	return '';
}

/**
 * Resolve a stored language value to a BCP-47 tag for language-tagged literals.
 *
 * Accepts the EU authority language URI (…/authority/language/DEU), a bare EU
 * three-letter code (DEU), or a legacy two-letter tag (de). Returns '' when it
 * cannot be mapped, so callers can fall back.
 *
 * @param  string $language Stored language value.
 * @return string BCP-47 language tag (e.g. 'de'), or '' if unresolved.
 */
function odw_resolve_language_tag( string $language ): string {
	$value = trim( $language );
	if ( '' === $value ) {
		return '';
	}

	// Already a 2-letter BCP-47 tag (legacy storage).
	if ( preg_match( '/^[a-z]{2}$/', $value ) ) {
		return $value;
	}

	// EU authority three-letter code → BCP-47 (covers the EU official languages).
	$map = array(
		'BUL' => 'bg',
		'CES' => 'cs',
		'DAN' => 'da',
		'DEU' => 'de',
		'ELL' => 'el',
		'ENG' => 'en',
		'EST' => 'et',
		'FIN' => 'fi',
		'FRA' => 'fr',
		'GLE' => 'ga',
		'HRV' => 'hr',
		'HUN' => 'hu',
		'ITA' => 'it',
		'LAV' => 'lv',
		'LIT' => 'lt',
		'MLT' => 'mt',
		'NLD' => 'nl',
		'POL' => 'pl',
		'POR' => 'pt',
		'RON' => 'ro',
		'SLK' => 'sk',
		'SLV' => 'sl',
		'SPA' => 'es',
		'SWE' => 'sv',
	);

	$parts = explode( '/', rtrim( $value, '/' ) );
	$code  = strtoupper( (string) end( $parts ) );

	return $map[ $code ] ?? '';
}

/**
 * Build a DCAT-AP language-tagged literal { @value, @language }.
 *
 * @param  string $value Literal value.
 * @param  string $tag   BCP-47 language tag; omitted from the output when empty.
 * @return array<string, string>
 */
function odw_lang_literal( string $value, string $tag ): array {
	$literal = array( '@value' => $value );
	if ( '' !== $tag ) {
		$literal['@language'] = $tag;
	}
	return $literal;
}

/**
 * Collect language-tagged literals for one field: the primary value plus any
 * translations from a { language, value } repeater. Returns a list of
 * { @value, @language } entries (possibly empty). The primary value comes first.
 *
 * @param string $primary_value Primary-language value.
 * @param string $primary_tag   BCP-47 tag for the primary value.
 * @param mixed  $translations  Repeater rows (array of { language, value }), or non-array.
 * @return array<int, array<string, string>>
 */
function odw_collect_lang_literals( string $primary_value, string $primary_tag, $translations ): array {
	$literals = array();

	if ( '' !== trim( $primary_value ) ) {
		$literals[] = odw_lang_literal( $primary_value, $primary_tag );
	}

	if ( is_array( $translations ) ) {
		foreach ( $translations as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$value = trim( (string) ( $row['content'] ?? '' ) );
			if ( '' === $value ) {
				continue;
			}
			$tag        = odw_resolve_language_tag( (string) ( $row['language'] ?? '' ) );
			$literals[] = odw_lang_literal( $value, $tag );
		}
	}

	return $literals;
}

/**
 * Build a single dcat:Distribution JSON-LD node from a flat set of values.
 *
 * Shared by the primary distribution (Tab 3 singular fields) and each additional
 * distribution (the odw_extra_distributions repeater), so both produce identical
 * node structure. Returns null when no access URL is present (nothing to emit).
 *
 * @param array<string, mixed> $d        Distribution values (access_url, format, byte_size,
 *                                        license, license_custom, attribution, availability,
 *                                        title, description, download_url, media_type, rights).
 * @param string               $lang_tag BCP-47 language tag for title/description literals.
 * @return array<string, mixed>|null
 */
function odw_build_distribution_node( array $d, string $lang_tag ): ?array {
	$access = esc_url_raw( (string) ( $d['access_url'] ?? '' ) );
	if ( '' === $access ) {
		return null;
	}

	$node = array(
		'@type'          => 'dcat:Distribution',
		'dcat:accessURL' => array( '@id' => $access ),
	);

	$format = (string) ( $d['format'] ?? '' );
	if ( '' !== $format ) {
		// get_format_eu_uri() gibt unbekannte Formate unverändert zurück; wie bei
		// allen anderen @id-Werten durch den gemeinsamen Sanitizer schicken, damit
		// kein gefährliches Schema (javascript:/data:) ins öffentliche JSON-LD gelangt.
		$node['dct:format'] = array( '@id' => odw_sanitize_jsonld_id( ODW_Fields::get_format_eu_uri( $format ) ) );
	}

	$byte_size = (int) ( $d['byte_size'] ?? 0 );
	if ( $byte_size > 0 ) {
		// DCAT-AP verlangt xsd:nonNegativeInteger — als typisiertes Literal ausgeben,
		// nicht als blanke Zahl (die sonst als xsd:integer serialisiert würde).
		$node['dcat:byteSize'] = array(
			'@value' => (string) $byte_size,
			'@type'  => 'xsd:nonNegativeInteger',
		);
	}

	$license = (string) ( $d['license'] ?? '' );
	if ( 'sonstige' === $license && ! empty( $d['license_custom'] ) ) {
		$license = (string) $d['license_custom'];
	}
	if ( '' !== $license && 'sonstige' !== $license ) {
		$node['dct:license'] = array( '@id' => odw_sanitize_jsonld_id( $license ) );
	}

	if ( ! empty( $d['attribution'] ) ) {
		$node['dcatde:licenseAttributionByText'] = (string) $d['attribution'];
	}

	if ( ! empty( $d['availability'] ) ) {
		$node['dcatap:availability'] = array( '@id' => odw_sanitize_jsonld_id( (string) $d['availability'] ) );
	}

	if ( ! empty( $d['title'] ) ) {
		$node['dct:title'] = odw_lang_literal( (string) $d['title'], $lang_tag );
	}

	if ( ! empty( $d['description'] ) ) {
		$node['dct:description'] = odw_lang_literal( (string) $d['description'], $lang_tag );
	}

	$download = esc_url_raw( (string) ( $d['download_url'] ?? '' ) );
	if ( '' !== $download ) {
		$node['dcat:downloadURL'] = array( '@id' => $download );
	}

	if ( ! empty( $d['media_type'] ) ) {
		$node['dcat:mediaType'] = array( '@id' => odw_sanitize_jsonld_id( (string) $d['media_type'] ) );
	}

	$rights = (string) ( $d['rights'] ?? '' );
	if ( '' !== $rights ) {
		if ( preg_match( '#^https?://#', $rights ) ) {
			$node['dct:rights'] = array( '@id' => odw_sanitize_jsonld_id( $rights ) );
		} else {
			$node['dct:rights'] = array(
				'@type'      => 'dct:RightsStatement',
				'rdfs:label' => $rights,
			);
		}
	}

	return $node;
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
	$theme_uri           = (string) carbon_get_post_meta( $post_id, 'odw_theme_uri' );
	$issued              = carbon_get_post_meta( $post_id, 'odw_issued' );
	$modified            = get_post_meta( $post_id, '_odw_modified', true );
	$dist_access_url     = (string) carbon_get_post_meta( $post_id, 'odw_access_url' );
	$dist_format         = (string) carbon_get_post_meta( $post_id, 'odw_format' );
	$dist_byte_size      = (string) carbon_get_post_meta( $post_id, 'odw_byte_size' );
	$dist_license        = (string) carbon_get_post_meta( $post_id, 'odw_license' );
	$dist_license_custom = (string) carbon_get_post_meta( $post_id, 'odw_license_custom' );
	$dist_attribution    = (string) carbon_get_post_meta( $post_id, 'odw_attribution_text' );
	$dist_availability   = (string) carbon_get_post_meta( $post_id, 'odw_availability' );
	$cessda_topic        = (string) carbon_get_post_meta( $post_id, 'odw_cessda_topic' );
	$engagementfeld      = (string) carbon_get_post_meta( $post_id, 'odw_engagementfeld' );

	// Extended DCAT-AP fields (Tab 4).
	$landing_page              = (string) carbon_get_post_meta( $post_id, 'odw_landing_page' );
	$accrual_periodicity       = (string) carbon_get_post_meta( $post_id, 'odw_accrual_periodicity' );
	$political_geocoding_level = (string) carbon_get_post_meta( $post_id, 'odw_political_geocoding_level' );
	$political_geocoding_uri   = (string) carbon_get_post_meta( $post_id, 'odw_political_geocoding_uri' );
	$geocoding_description     = (string) carbon_get_post_meta( $post_id, 'odw_geocoding_description' );
	$spatial                   = (string) carbon_get_post_meta( $post_id, 'odw_spatial' );
	$temporal_start            = (string) carbon_get_post_meta( $post_id, 'odw_temporal_start' );
	$temporal_end              = (string) carbon_get_post_meta( $post_id, 'odw_temporal_end' );
	$contact_name              = (string) carbon_get_post_meta( $post_id, 'odw_contact_name' );
	$contact_email             = (string) carbon_get_post_meta( $post_id, 'odw_contact_email' );
	$contact_url               = (string) carbon_get_post_meta( $post_id, 'odw_contact_url' );
	$is_hvd                    = (string) carbon_get_post_meta( $post_id, 'odw_is_hvd' );
	$hvd_category              = (string) carbon_get_post_meta( $post_id, 'odw_hvd_category' );
	$contributor_id            = (string) carbon_get_post_meta( $post_id, 'odw_contributor_id' );
	$originator_name           = (string) carbon_get_post_meta( $post_id, 'odw_originator_name' );
	$originator_email          = (string) carbon_get_post_meta( $post_id, 'odw_originator_email' );
	$maintainer_name           = (string) carbon_get_post_meta( $post_id, 'odw_maintainer_name' );
	$maintainer_email          = (string) carbon_get_post_meta( $post_id, 'odw_maintainer_email' );
	$legal_basis               = (string) carbon_get_post_meta( $post_id, 'odw_legal_basis' );
	$quality_process_uri       = (string) carbon_get_post_meta( $post_id, 'odw_quality_process_uri' );
	$access_rights             = (string) carbon_get_post_meta( $post_id, 'odw_access_rights' );

	// Additional optional DCAT-AP 3.0 fields (advanced, Tab 4).
	$identifier          = (string) carbon_get_post_meta( $post_id, 'odw_identifier' );
	$dataset_type        = (string) carbon_get_post_meta( $post_id, 'odw_type' );
	$creator_name        = (string) carbon_get_post_meta( $post_id, 'odw_creator_name' );
	$creator_email       = (string) carbon_get_post_meta( $post_id, 'odw_creator_email' );
	$version             = (string) carbon_get_post_meta( $post_id, 'odw_version' );
	$version_notes       = (string) carbon_get_post_meta( $post_id, 'odw_version_notes' );
	$spatial_resolution  = (string) carbon_get_post_meta( $post_id, 'odw_spatial_resolution' );
	$temporal_resolution = (string) carbon_get_post_meta( $post_id, 'odw_temporal_resolution' );
	$conforms_to         = (string) carbon_get_post_meta( $post_id, 'odw_conforms_to' );
	$provenance          = (string) carbon_get_post_meta( $post_id, 'odw_provenance' );
	$dist_title          = (string) carbon_get_post_meta( $post_id, 'odw_dist_title' );
	$dist_description    = (string) carbon_get_post_meta( $post_id, 'odw_dist_description' );
	$download_url        = (string) carbon_get_post_meta( $post_id, 'odw_download_url' );
	$media_type          = (string) carbon_get_post_meta( $post_id, 'odw_media_type' );
	$dist_rights         = (string) carbon_get_post_meta( $post_id, 'odw_dist_rights' );

	// Content language tag (BCP-47) for language-tagged literals. Derived from the
	// dataset language field, falling back to the configured default, then 'de'.
	$lang_tag = odw_resolve_language_tag( (string) $language );
	if ( '' === $lang_tag && class_exists( 'ODW_Settings' ) ) {
		$lang_tag = odw_resolve_language_tag( (string) ODW_Settings::get( 'default_language' ) );
	}
	if ( '' === $lang_tag ) {
		$lang_tag = 'de';
	}

	// Title/description as language-tagged literals — a single object when only
	// the primary language is present (backward compatible), an array of objects
	// once translations are added (odw_title_translations / …_description…).
	$title_literals       = odw_collect_lang_literals( (string) $title, $lang_tag, carbon_get_post_meta( $post_id, 'odw_title_translations' ) );
	$description_literals = odw_collect_lang_literals( (string) $description, $lang_tag, carbon_get_post_meta( $post_id, 'odw_description_translations' ) );

	$dataset = array(
		'@type'           => 'dcat:Dataset',
		'@id'             => rest_url( 'datenatlas/v1/datasets/' . $post_id ),
		'dct:title'       => 1 === count( $title_literals ) ? $title_literals[0] : $title_literals,
		'dct:description' => 1 === count( $description_literals ) ? $description_literals[0] : $description_literals,
		'dct:publisher'   => array(
			'@type'     => array( 'foaf:Organization', 'foaf:Agent' ),
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

	$keyword_literals = array();
	if ( ! empty( $keywords ) && is_string( $keywords ) ) {
		foreach ( array_values( array_filter( array_map( 'trim', explode( "\n", $keywords ) ) ) ) as $kw ) {
			$keyword_literals[] = odw_lang_literal( (string) $kw, $lang_tag );
		}
	}

	// Translated keywords (odw_keyword_translations): each row carries a language
	// and its own newline-separated keyword list; all are appended.
	$keyword_translations = carbon_get_post_meta( $post_id, 'odw_keyword_translations' );
	if ( is_array( $keyword_translations ) ) {
		foreach ( $keyword_translations as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$row_tag = odw_resolve_language_tag( (string) ( $row['language'] ?? '' ) );
			foreach ( array_values( array_filter( array_map( 'trim', explode( "\n", (string) ( $row['keywords'] ?? '' ) ) ) ) ) as $kw ) {
				$keyword_literals[] = odw_lang_literal( (string) $kw, $row_tag );
			}
		}
	}

	if ( ! empty( $keyword_literals ) ) {
		$dataset['dcat:keyword'] = $keyword_literals;
	}

	$themes = array();
	if ( ! empty( $theme ) ) {
		$theme_base   = 'http://publications.europa.eu/resource/authority/data-theme/';
		$theme_legacy = array(
			'Bildung'    => $theme_base . 'EDUC',
			'Gesundheit' => $theme_base . 'HEAL',
			'Soziales'   => $theme_base . 'SOCI',
			'Umwelt'     => $theme_base . 'ENVI',
			'Wirtschaft' => $theme_base . 'ECON',
			'Kultur'     => $theme_base . 'EDUC',
			'Sport'      => $theme_base . 'EDUC',
			'Sonstiges'  => $theme_base . 'GOVE',
		);
		$themes[]     = array( '@id' => odw_sanitize_jsonld_id( $theme_legacy[ (string) $theme ] ?? (string) $theme ) );
	}
	// Optional additional EU data-theme (advanced field, bundled vocabulary).
	// The field stores the human-readable label; resolve it to the official URI
	// (a directly entered URI passes through unchanged).
	$theme_uri_resolved = odw_resolve_vocab_uri( $theme_uri, 'data-theme' );
	if ( '' !== $theme_uri_resolved ) {
		$themes[] = array( '@id' => odw_sanitize_jsonld_id( $theme_uri_resolved ) );
	}
	if ( 1 === count( $themes ) ) {
		$dataset['dcat:theme'] = $themes[0];
	} elseif ( count( $themes ) > 1 ) {
		$dataset['dcat:theme'] = $themes;
	}

	// Thematic classifications are attached via the standard DCAT-AP subject
	// property. CESSDA topic and ZiviZ engagement field both map to dct:subject;
	// when both are present, dct:subject becomes an array.
	$subjects = array();
	if ( ! empty( $cessda_topic ) ) {
		$subjects[] = array( '@id' => odw_sanitize_jsonld_id( (string) $cessda_topic ) );
	}
	if ( ! empty( $engagementfeld ) ) {
		$engagement_uri = odw_resolve_vocab_uri( $engagementfeld, 'engagementfeld' );
		if ( '' !== $engagement_uri ) {
			$subjects[] = array( '@id' => odw_sanitize_jsonld_id( $engagement_uri ) );
		}
	}
	if ( ! empty( $subjects ) ) {
		$dataset['dct:subject'] = 1 === count( $subjects ) ? $subjects[0] : $subjects;
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

	// Primary distribution (Tab 3 singular fields) plus any additional
	// distributions from the odw_extra_distributions repeater. All nodes share
	// odw_build_distribution_node() so their JSON-LD structure is identical.
	$distributions = array();

	$primary_distribution = odw_build_distribution_node(
		array(
			'access_url'     => $dist_access_url,
			'format'         => $dist_format,
			'byte_size'      => $dist_byte_size,
			'license'        => $dist_license,
			'license_custom' => $dist_license_custom,
			'attribution'    => $dist_attribution,
			'availability'   => $dist_availability,
			'title'          => $dist_title,
			'description'    => $dist_description,
			'download_url'   => $download_url,
			'media_type'     => $media_type,
			'rights'         => $dist_rights,
		),
		$lang_tag
	);
	if ( null !== $primary_distribution ) {
		$distributions[] = $primary_distribution;
	}

	$extra_distributions = carbon_get_post_meta( $post_id, 'odw_extra_distributions' );
	if ( is_array( $extra_distributions ) ) {
		foreach ( $extra_distributions as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$node = odw_build_distribution_node(
				array(
					'access_url'     => $row['access_url'] ?? '',
					'format'         => $row['format'] ?? '',
					'byte_size'      => $row['byte_size'] ?? '',
					'license'        => $row['license'] ?? '',
					'license_custom' => $row['license_custom'] ?? '',
					'attribution'    => $row['attribution'] ?? '',
					'title'          => $row['title'] ?? '',
					'description'    => $row['description'] ?? '',
					'download_url'   => $row['download_url'] ?? '',
					'media_type'     => $row['media_type'] ?? '',
					'rights'         => $row['rights'] ?? '',
				),
				$lang_tag
			);
			if ( null !== $node ) {
				$distributions[] = $node;
			}
		}
	}

	if ( ! empty( $distributions ) ) {
		$dataset['dcat:distribution'] = $distributions;
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

	if ( ! empty( $political_geocoding_uri ) ) {
		$dataset['dcatde:politicalGeocodingURI'] = array( '@id' => odw_sanitize_jsonld_id( (string) $political_geocoding_uri ) );
	}

	if ( '' !== trim( (string) $geocoding_description ) ) {
		$dataset['dcatde:geocodingDescription'] = odw_lang_literal( (string) $geocoding_description, $lang_tag );
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
		$contact = array( '@type' => array( 'vcard:Organization', 'vcard:Kind' ) );
		if ( ! empty( $contact_name ) ) {
			$contact['vcard:fn'] = $contact_name;
		}
		if ( ! empty( $contact_email ) ) {
			$contact['vcard:hasEmail'] = array( '@id' => odw_sanitize_jsonld_id( 'mailto:' . $contact_email ) );
		}
		if ( ! empty( $contact_url ) ) {
			$contact['vcard:hasURL'] = array( '@id' => odw_sanitize_jsonld_id( (string) $contact_url ) );
		}
		$dataset['dcat:contactPoint'] = $contact;
	}

	// High-Value-Dataset (HVD) per EU Implementing Regulation 2023/138. Both
	// hvdCategory and applicableLegislation are emitted together; a category is
	// required (enforced in ODW_Validation) so applicableLegislation never
	// appears without it.
	if ( 'yes' === $is_hvd && ! empty( $hvd_category ) ) {
		$dataset['dcatap:hvdCategory']           = array( '@id' => odw_sanitize_jsonld_id( (string) $hvd_category ) );
		$dataset['dcatap:applicableLegislation'] = array( '@id' => 'http://data.europa.eu/eli/reg_impl/2023/138/oj' );
	}

	// DCAT-AP.de: contributor identifier of the data-providing body (GovData).
	// The field stores the human-readable label; resolve it to the official URI.
	$contributor_uri = odw_resolve_vocab_uri( $contributor_id, 'contributors' );
	if ( '' !== $contributor_uri ) {
		$dataset['dcatde:contributorID'] = array( '@id' => odw_sanitize_jsonld_id( $contributor_uri ) );
	}

	// DCAT-AP.de: originator (who created the data) and maintainer (who keeps it
	// current), each a foaf:Agent with name and optional mailbox.
	$originator = odw_build_agent_node( $originator_name, $originator_email );
	if ( null !== $originator ) {
		$dataset['dcatde:originator'] = $originator;
	}

	$maintainer = odw_build_agent_node( $maintainer_name, $maintainer_email );
	if ( null !== $maintainer ) {
		$dataset['dcatde:maintainer'] = $maintainer;
	}

	// DCAT-AP.de: legal basis (literal) and quality-process documentation (URI).
	if ( ! empty( $legal_basis ) ) {
		$dataset['dcatde:legalBasis'] = $legal_basis;
	}

	if ( ! empty( $quality_process_uri ) ) {
		$dataset['dcatde:qualityProcessURI'] = array( '@id' => odw_sanitize_jsonld_id( (string) $quality_process_uri ) );
	}

	// DCAT-AP: access classification of the dataset (EU access-right vocabulary).
	if ( ! empty( $access_rights ) ) {
		$dataset['dct:accessRights'] = array( '@id' => odw_sanitize_jsonld_id( (string) $access_rights ) );
	}

	// Additional optional DCAT-AP 3.0 dataset properties (advanced, Tab 4).
	if ( '' !== $identifier ) {
		$dataset['dct:identifier'] = $identifier;
	}

	if ( '' !== $dataset_type ) {
		$dataset['dct:type'] = array( '@id' => odw_sanitize_jsonld_id( $dataset_type ) );
	}

	$creator = odw_build_agent_node( $creator_name, $creator_email );
	if ( null !== $creator ) {
		$dataset['dct:creator'] = $creator;
	}

	if ( '' !== $version ) {
		$dataset['owl:versionInfo'] = $version;
	}

	if ( '' !== $version_notes ) {
		$dataset['adms:versionNotes'] = odw_lang_literal( $version_notes, $lang_tag );
	}

	if ( '' !== $spatial_resolution && is_numeric( $spatial_resolution ) ) {
		$dataset['dcat:spatialResolutionInMeters'] = array(
			'@type'  => 'xsd:decimal',
			'@value' => $spatial_resolution,
		);
	}

	if ( '' !== $temporal_resolution ) {
		$dataset['dcat:temporalResolution'] = array(
			'@type'  => 'xsd:duration',
			'@value' => $temporal_resolution,
		);
	}

	if ( '' !== $conforms_to ) {
		$dataset['dct:conformsTo'] = array( '@id' => odw_sanitize_jsonld_id( $conforms_to ) );
	}

	if ( '' !== $provenance ) {
		$dataset['dct:provenance'] = array(
			'@type'      => 'dct:ProvenanceStatement',
			'rdfs:label' => odw_lang_literal( $provenance, $lang_tag ),
		);
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
