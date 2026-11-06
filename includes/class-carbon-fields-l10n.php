<?php
/**
 * Deutsche Oberflächentexte für Carbon Fields
 *
 * Die Beschriftungen der Felder kommen aus dem Plugin und sind deutsch. Die
 * Bedienelemente, die Carbon Fields selbst zeichnet — „There are no entries
 * yet.", „Add Distribution", „Select Date", „No options." —, stammen dagegen
 * aus der JavaScript-Oberfläche von Carbon Fields (Textdomain
 * `carbon-fields-ui`). Sie blieben auch in einem deutschen WordPress englisch,
 * aus zwei voneinander unabhängigen Gründen:
 *
 * 1. Carbon Fields 3.6.11 liefert für `carbon-fields-ui` keine deutsche Datei
 *    mit (nur bg_BG, ca, el, en, es_ES, fr_FR, ru_RU, uk).
 * 2. Carbon Fields lädt seine UI-Texte nicht über wp_set_script_translations(),
 *    sondern selbst: Loader::load_textdomain() lädt die `.mo` auf
 *    `after_setup_theme`, Loader::get_ui_translations() liest sie über
 *    get_translations_for_domain()->entries aus und gibt sie per
 *    wp_localize_script() als `cf.config.locale` an die Oberfläche, die damit
 *    `setLocaleData()` aus @wordpress/i18n aufruft. Seit WordPress 6.5 liefert
 *    `->entries` aber eine Liste (WP_Translations::__get()), keine nach msgid
 *    geschlüsselte Tabelle. Carbon Fields übernimmt die Listenindizes 0, 1, 2 …
 *    als msgids — die Übersetzungen kommen in der Oberfläche nie an, auch die
 *    mitgelieferten nicht.
 *
 * Diese Klasse behebt beides an der Stelle, die Carbon Fields dafür vorsieht,
 * dem Filter `carbon_fields_config`: Für deutsche Locales lädt sie die
 * gebündelte Übersetzung `languages/carbon-fields-ui-de_DE.mo` in die Domain
 * `carbon-fields-ui` und baut `cf.config.locale` danach neu auf, geschlüsselt
 * nach msgid (samt Kontext). Nichts davon liegt in `vendor/`, das nicht
 * eingecheckt ist und bei jedem `composer install` neu entsteht.
 *
 * Die Mehrfachauswahl zeigt zusätzlich zwei Texte, die keine Übersetzung
 * erreichen kann: „Select..." und „No options" setzt die Bibliothek
 * react-select als feste Vorgabe, Carbon Fields reicht dafür nichts durch.
 * Diese beiden ersetzt assets/js/odw-carbon-fields-l10n.js im DOM; die
 * Zielwörter kommen aus react_select_strings() und laufen damit durch die
 * Textdomain des Plugins (en_US erhält wieder das englische Original).
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies German UI strings to the Carbon Fields JavaScript interface.
 *
 * @package OpenDataWizard
 */
class ODW_Carbon_Fields_L10n {

	/** Textdomain der Carbon-Fields-Oberfläche. */
	public const UI_DOMAIN = 'carbon-fields-ui';

	/**
	 * Ob die deutsche Datei in diesem Request bereits geladen wurde.
	 *
	 * @var bool
	 */
	private static bool $german_loaded = false;

	/**
	 * Registers hooks.
	 */
	public static function init(): void {
		add_filter( 'carbon_fields_config', array( self::class, 'filter_config' ) );
	}

	/**
	 * Ob eine Locale deutsch ist (de_DE, de_DE_formal, de_AT, de_CH, …).
	 *
	 * Alle deutschen Varianten erhalten dieselbe Datei: Das Plugin spricht
	 * durchgehend mit „Sie" an, eine eigene du-Fassung gibt es nicht.
	 *
	 * @param string|null $locale Locale; ohne Angabe die des aktuellen Requests.
	 */
	public static function is_german_locale( ?string $locale = null ): bool {
		$locale = $locale ?? determine_locale();

		return 'de' === $locale || 0 === strpos( $locale, 'de_' );
	}

	/**
	 * Absoluter Pfad der gebündelten deutschen Übersetzung.
	 */
	public static function german_mo_path(): string {
		return ODW_PLUGIN_DIR . 'languages/carbon-fields-ui-de_DE.mo';
	}

	/**
	 * Filter `carbon_fields_config`: lädt bei deutscher Locale die gebündelte
	 * Übersetzung und baut `config.locale` nach msgid geschlüsselt neu auf.
	 *
	 * Carbon Fields ruft den Filter in Loader::enqueue_assets() auf
	 * (`admin_print_footer_scripts`), also nur auf Seiten mit seinen Feldern
	 * und erst, wenn die Locale feststeht.
	 *
	 * @param mixed $config Konfiguration, die Carbon Fields als `window.cf` ausgibt.
	 * @return mixed
	 */
	public static function filter_config( $config ) {
		if ( ! is_array( $config ) || ! isset( $config['config'] ) || ! is_array( $config['config'] ) ) {
			return $config;
		}

		if ( self::is_german_locale() ) {
			self::load_german();
		}

		$header = array();
		if ( isset( $config['config']['locale'][''] ) && is_array( $config['config']['locale'][''] ) ) {
			$header = $config['config']['locale'][''];
		}

		$config['config']['locale'] = self::locale_data( get_translations_for_domain( self::UI_DOMAIN ), $header );

		return $config;
	}

	/**
	 * Lädt die deutsche Übersetzung in die Domain `carbon-fields-ui`.
	 *
	 * Ergänzt eine bereits geladene Datei, statt sie zu ersetzen — sollte
	 * Carbon Fields künftig selbst eine deutsche Datei mitbringen, bleiben
	 * deren Einträge erhalten, wo diese Datei keine hat.
	 */
	public static function load_german(): bool {
		if ( self::$german_loaded ) {
			return true;
		}

		$path = self::german_mo_path();
		if ( ! is_readable( $path ) ) {
			return false;
		}

		self::$german_loaded = (bool) load_textdomain( self::UI_DOMAIN, $path );

		return self::$german_loaded;
	}

	/**
	 * Baut die Locale-Daten für `setLocaleData()` (@wordpress/i18n).
	 *
	 * Format: `'' => [ domain, lang, plural_forms ]`, danach je Eintrag
	 * `msgid => [ Übersetzung, … ]`; mit Kontext lautet der Schlüssel
	 * `Kontext\u0004msgid`. Der Schlüssel wird aus `singular`/`context` des
	 * Eintrags gebildet und nicht aus dem Array-Index — das ist der Punkt, an
	 * dem Carbon Fields seit WordPress 6.5 scheitert. So funktioniert es mit
	 * der Liste aus WP_Translations ebenso wie mit der nach Schlüssel
	 * indizierten Tabelle älterer Versionen.
	 *
	 * @param object|null          $translations Ergebnis von get_translations_for_domain().
	 * @param array<string, mixed> $header       Kopfeintrag, den Carbon Fields gebildet hat.
	 * @return array<string, mixed>
	 */
	public static function locale_data( $translations, array $header = array() ): array {
		$header = array_merge(
			array(
				'domain' => self::UI_DOMAIN,
				'lang'   => determine_locale(),
			),
			$header
		);

		$headers = self::read_array( $translations, 'headers' );
		if ( empty( $header['plural_forms'] ) && ! empty( $headers['Plural-Forms'] ) ) {
			$header['plural_forms'] = (string) $headers['Plural-Forms'];
		}

		$data = array( '' => $header );

		$entries = self::read_array( $translations, 'entries' );

		foreach ( $entries as $entry ) {
			if ( ! is_object( $entry ) || ! isset( $entry->singular ) || '' === (string) $entry->singular ) {
				continue;
			}

			$values = isset( $entry->translations ) && is_array( $entry->translations )
				? array_map( 'strval', array_values( $entry->translations ) )
				: array();
			if ( '' === implode( '', $values ) ) {
				continue;
			}

			$key = (string) $entry->singular;
			if ( isset( $entry->context ) && '' !== (string) $entry->context ) {
				$key = $entry->context . "\4" . $key;
			}

			$data[ $key ] = $values;
		}

		return $data;
	}

	/**
	 * Liest eine Array-Eigenschaft eines Übersetzungsobjekts.
	 *
	 * Nicht über isset() oder `??`: WP_Translations (ab WordPress 6.5) stellt
	 * `entries` und `headers` nur über __get() bereit und kennt kein
	 * __isset() — isset() wäre dort immer false, die Übersetzungen fielen
	 * stillschweigend weg. NOOP_Translations und MO haben echte Eigenschaften.
	 *
	 * @param mixed  $source Übersetzungsobjekt.
	 * @param string $name   Eigenschaft.
	 * @return array<mixed>
	 */
	private static function read_array( $source, string $name ): array {
		if ( ! is_object( $source ) ) {
			return array();
		}
		if ( ! property_exists( $source, $name ) && ! method_exists( $source, '__get' ) ) {
			return array();
		}

		$value = $source->$name;

		return is_array( $value ) ? $value : array();
	}

	/**
	 * Texte für die Mehrfachauswahl, die react-select fest vorgibt.
	 *
	 * Schlüssel: englischer Originaltext im DOM; Wert: Ersatz. Auf en_US
	 * übersetzt die Plugin-Domain zurück ins Original — dann ist der Tausch
	 * ein No-op.
	 *
	 * @return array<string, string>
	 */
	public static function react_select_strings(): array {
		return array(
			'Select...'  => __( 'Auswählen…', 'open-data-wizard' ),
			'No options' => __( 'Keine passenden Optionen', 'open-data-wizard' ),
		);
	}
}
