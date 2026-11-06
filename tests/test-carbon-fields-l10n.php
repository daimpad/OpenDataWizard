<?php
/**
 * Tests für ODW_Carbon_Fields_L10n — deutsche Texte der Carbon-Fields-Oberfläche.
 *
 * Kernzusagen:
 * - Die Locale-Daten für setLocaleData() sind nach msgid geschlüsselt, auch
 *   wenn WordPress (ab 6.5) die Einträge als Liste und nur über __get()
 *   herausgibt. Genau daran scheitert Carbon Fields selbst.
 * - Die gebündelte .mo enthält die Texte, um die es geht, und ist zur .po
 *   passend kompiliert.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Stellt WP_Translations (WordPress ≥ 6.5) nach: Eigenschaften nur über
 * __get(), kein __isset(), Einträge als Liste.
 */
class ODW_Test_Magic_Translations {

	/**
	 * Einträge als Liste.
	 *
	 * @var array<int, object>
	 */
	private array $list;

	/**
	 * Kopfzeilen.
	 *
	 * @var array<string, string>
	 */
	private array $head;

	/**
	 * Legt Einträge und Kopfzeilen fest.
	 *
	 * @param array<int, object>    $items Einträge.
	 * @param array<string, string> $head  Kopfzeilen.
	 */
	public function __construct( array $items, array $head ) {
		$this->list = $items;
		$this->head = $head;
	}

	/**
	 * Liefert Einträge und Kopfzeilen nur über den magischen Getter.
	 *
	 * @param string $name Eigenschaft.
	 * @return mixed
	 */
	public function __get( string $name ) {
		if ( 'entries' === $name ) {
			return $this->list;
		}
		if ( 'headers' === $name ) {
			return $this->head;
		}
		return null;
	}
}

/**
 * Unit tests for ODW_Carbon_Fields_L10n.
 */
class Test_ODW_Carbon_Fields_L10n extends TestCase {

	/**
	 * Set up WP_Mock and load the class.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		require_once dirname( __DIR__ ) . '/includes/class-carbon-fields-l10n.php';
		$this->reset_loaded();
	}

	/**
	 * Tear down WP_Mock.
	 */
	protected function tearDown(): void {
		$this->reset_loaded();
		\WP_Mock::tearDown();
	}

	/**
	 * Setzt den Merker „deutsche Datei geladen" zurück (statisch, pro Request).
	 */
	private function reset_loaded(): void {
		$prop = new \ReflectionProperty( ODW_Carbon_Fields_L10n::class, 'german_loaded' );
		$prop->setAccessible( true );
		$prop->setValue( null, false );
	}

	/**
	 * Ein Übersetzungseintrag wie Translation_Entry.
	 *
	 * @param string      $singular     msgid.
	 * @param string[]    $translations msgstr(s).
	 * @param string|null $context      msgctxt.
	 */
	private function entry( string $singular, array $translations, ?string $context = null ): object {
		$entry               = new \stdClass();
		$entry->singular     = $singular;
		$entry->translations = $translations;
		$entry->context      = $context;
		return $entry;
	}

	/**
	 * Liest eine .mo-Datei in ein msgid => msgstr-Array (kleiner Reader für den Test).
	 *
	 * @param string $path Pfad.
	 * @return array<string, string>
	 */
	private function read_mo( string $path ): array {
		$bin = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- lokale Datei.
		$this->assertSame( 0x950412de, unpack( 'V', substr( $bin, 0, 4 ) )[1], 'Keine gültige .mo (Magic Number).' );

		$header = unpack( 'Vmagic/Vrev/Vcount/Vorig/Vtrans', substr( $bin, 0, 20 ) );
		$result = array();
		for ( $i = 0; $i < $header['count']; $i++ ) {
			$o             = unpack( 'Vlen/Voff', substr( $bin, $header['orig'] + 8 * $i, 8 ) );
			$t             = unpack( 'Vlen/Voff', substr( $bin, $header['trans'] + 8 * $i, 8 ) );
			$id            = substr( $bin, $o['off'], $o['len'] );
			$result[ $id ] = substr( $bin, $t['off'], $t['len'] );
		}
		return $result;
	}

	// -----------------------------------------------------------------------
	// Locale
	// -----------------------------------------------------------------------

	/**
	 * Alle deutschen Varianten gelten als deutsch, andere Sprachen nicht.
	 */
	public function test_is_german_locale(): void {
		foreach ( array( 'de', 'de_DE', 'de_DE_formal', 'de_AT', 'de_CH', 'de_CH_informal' ) as $locale ) {
			$this->assertTrue( ODW_Carbon_Fields_L10n::is_german_locale( $locale ), $locale );
		}
		foreach ( array( 'en_US', 'fr_FR', 'dsb_DE', 'hsb_DE', '' ) as $locale ) {
			$this->assertFalse( ODW_Carbon_Fields_L10n::is_german_locale( $locale ), $locale );
		}
	}

	/**
	 * Ohne Argument entscheidet determine_locale().
	 */
	public function test_is_german_locale_defaults_to_request_locale(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'de_DE_formal' );

		$this->assertTrue( ODW_Carbon_Fields_L10n::is_german_locale() );
	}

	// -----------------------------------------------------------------------
	// locale_data()
	// -----------------------------------------------------------------------

	/**
	 * Der Fall, an dem Carbon Fields scheitert: Einträge als Liste, nur über
	 * __get() erreichbar. Die Schlüssel müssen die msgids sein, nicht 0, 1, 2.
	 */
	public function test_locale_data_keys_by_msgid_for_wp_translations(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'de_DE' );

		$translations = new ODW_Test_Magic_Translations(
			array(
				$this->entry( 'There are no entries yet.', array( 'Noch keine Einträge vorhanden.' ) ),
				$this->entry( 'Add %s', array( '%s hinzufügen' ) ),
			),
			array( 'Plural-Forms' => 'nplurals=2; plural=(n != 1);' )
		);

		$data = ODW_Carbon_Fields_L10n::locale_data(
			$translations,
			array(
				'domain' => 'carbon-fields-ui',
				'lang'   => 'de_DE',
			)
		);

		$this->assertArrayNotHasKey( 0, $data );
		$this->assertSame( array( 'Noch keine Einträge vorhanden.' ), $data['There are no entries yet.'] );
		$this->assertSame( array( '%s hinzufügen' ), $data['Add %s'] );
		$this->assertSame( 'carbon-fields-ui', $data['']['domain'] );
		$this->assertSame( 'de_DE', $data['']['lang'] );
		$this->assertSame( 'nplurals=2; plural=(n != 1);', $data['']['plural_forms'] );
	}

	/**
	 * Ältere WordPress-Versionen (MO/Translations): echte Eigenschaften,
	 * Einträge nach Schlüssel indiziert — Ergebnis identisch.
	 */
	public function test_locale_data_with_keyed_entries(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'de_DE' );

		$translations          = new \stdClass();
		$translations->headers = array();
		$translations->entries = array(
			'Select Date' => $this->entry( 'Select Date', array( 'Datum wählen' ) ),
		);

		$data = ODW_Carbon_Fields_L10n::locale_data( $translations );

		$this->assertSame( array( 'Datum wählen' ), $data['Select Date'] );
		$this->assertSame( 'carbon-fields-ui', $data['']['domain'] );
		$this->assertArrayNotHasKey( 'plural_forms', $data[''] );
	}

	/**
	 * Kontext wird wie in @wordpress/i18n mit \u0004 vorangestellt; leere
	 * Übersetzungen fallen weg, damit das englische Original sichtbar bleibt
	 * statt eines leeren Buttons.
	 */
	public function test_locale_data_context_and_empty_translations(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'de_DE' );

		$translations = new ODW_Test_Magic_Translations(
			array(
				$this->entry( 'Add', array( 'Hinzufügen' ), 'button' ),
				$this->entry( 'Clear', array( '' ) ),
				$this->entry( '', array( 'Kopf' ) ),
			),
			array()
		);

		$data = ODW_Carbon_Fields_L10n::locale_data( $translations );

		$this->assertSame( array( 'Hinzufügen' ), $data["button\4Add"] );
		$this->assertArrayNotHasKey( 'Clear', $data );
		$this->assertCount( 2, $data );
	}

	/**
	 * NOOP_Translations (keine Datei geladen, z. B. en_US): nur der Kopf.
	 */
	public function test_locale_data_without_translations(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'en_US' );

		$this->assertSame(
			array(
				'' => array(
					'domain' => 'carbon-fields-ui',
					'lang'   => 'en_US',
				),
			),
			ODW_Carbon_Fields_L10n::locale_data( null )
		);
	}

	// -----------------------------------------------------------------------
	// filter_config()
	// -----------------------------------------------------------------------

	/**
	 * Deutsch: lädt die gebündelte Datei in die Domain carbon-fields-ui und
	 * ersetzt config.locale; übrige Einträge von window.cf bleiben unberührt.
	 */
	public function test_filter_config_loads_german_file(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'de_DE' );
		\WP_Mock::userFunction( 'load_textdomain' )
			->once()
			->with( 'carbon-fields-ui', ODW_PLUGIN_DIR . 'languages/carbon-fields-ui-de_DE.mo' )
			->andReturn( true );
		\WP_Mock::userFunction( 'get_translations_for_domain' )
			->with( 'carbon-fields-ui' )
			->andReturn(
				new ODW_Test_Magic_Translations(
					array( $this->entry( 'There are no entries yet.', array( 'Noch keine Einträge vorhanden.' ) ) ),
					array()
				)
			);

		$config = ODW_Carbon_Fields_L10n::filter_config(
			array(
				'config' => array(
					'locale'  => array(
						'' => array(
							'domain' => 'carbon-fields-ui',
							'lang'   => 'de_DE',
						),
						0  => array( 'kaputt' ),
					),
					'pagenow' => 'post.php',
				),
			)
		);

		$this->assertSame( 'post.php', $config['config']['pagenow'] );
		$this->assertArrayNotHasKey( 0, $config['config']['locale'] );
		$this->assertSame( array( 'Noch keine Einträge vorhanden.' ), $config['config']['locale']['There are no entries yet.'] );

		// Ein zweiter Aufruf im selben Request lädt nicht erneut (once() oben).
		ODW_Carbon_Fields_L10n::filter_config( array( 'config' => array() ) );
	}

	/**
	 * Nicht deutsch: keine deutsche Datei, aber trotzdem korrekt geschlüsselte
	 * Daten — davon profitieren auch die Übersetzungen, die Carbon Fields
	 * selbst mitbringt (z. B. fr_FR).
	 */
	public function test_filter_config_other_locale_does_not_load_german(): void {
		\WP_Mock::userFunction( 'determine_locale' )->andReturn( 'fr_FR' );
		\WP_Mock::userFunction( 'load_textdomain' )->never();
		\WP_Mock::userFunction( 'get_translations_for_domain' )->andReturn(
			new ODW_Test_Magic_Translations(
				array( $this->entry( 'There are no entries yet.', array( "Il n'y a pas encore d'entrées." ) ) ),
				array()
			)
		);

		$config = ODW_Carbon_Fields_L10n::filter_config( array( 'config' => array() ) );

		$this->assertSame( array( "Il n'y a pas encore d'entrées." ), $config['config']['locale']['There are no entries yet.'] );
	}

	/**
	 * Unerwartete Struktur wird unverändert durchgereicht.
	 */
	public function test_filter_config_passes_through_unexpected_input(): void {
		$this->assertSame( 'x', ODW_Carbon_Fields_L10n::filter_config( 'x' ) );
		$this->assertSame( array( 'other' => 1 ), ODW_Carbon_Fields_L10n::filter_config( array( 'other' => 1 ) ) );
	}

	// -----------------------------------------------------------------------
	// Gebündelte Dateien
	// -----------------------------------------------------------------------

	/**
	 * Die .mo enthält die Texte aus dem Formular des Plugins und stimmt mit
	 * der .po überein — eine vergessene Neukompilierung fällt hier auf.
	 */
	public function test_bundled_german_mo_matches_po(): void {
		$mo = $this->read_mo( ODW_Carbon_Fields_L10n::german_mo_path() );

		$expected = array(
			'There are no entries yet.' => 'Noch keine Einträge vorhanden.',
			'Add %s'                    => '%s hinzufügen',
			'Select Date'               => 'Datum wählen',
			'No options.'               => 'Keine Optionen vorhanden.',
			'Collapse All'              => 'Alle zuklappen',
			'Expand All'                => 'Alle aufklappen',
			'This field is required.'   => 'Dieses Feld ist ein Pflichtfeld.',
		);
		foreach ( $expected as $msgid => $msgstr ) {
			$this->assertSame( $msgstr, $mo[ $msgid ] ?? null, $msgid );
		}

		// Jede msgid der .po steht in der .mo.
		$po = (string) file_get_contents( ODW_PLUGIN_DIR . 'languages/carbon-fields-ui-de_DE.po' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- lokale Datei.
		preg_match_all( '/^msgid "(.+)"$/m', $po, $matches );
		$this->assertNotEmpty( $matches[1] );
		foreach ( $matches[1] as $msgid ) {
			$msgid = stripcslashes( $msgid );
			$this->assertArrayHasKey( $msgid, $mo, 'Nicht kompiliert: ' . $msgid );
		}

		$this->assertStringContainsString( 'Plural-Forms: nplurals=2; plural=(n != 1);', $mo[''] );
	}

	/**
	 * Die Schlüssel sind die festen Vorgaben von react-select — nur dann
	 * findet das Skript den Text im DOM.
	 */
	public function test_react_select_strings(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$strings = ODW_Carbon_Fields_L10n::react_select_strings();

		$this->assertSame( array( 'Select...', 'No options' ), array_keys( $strings ) );
		$this->assertSame( 'Auswählen…', $strings['Select...'] );
		$this->assertSame( 'Keine passenden Optionen', $strings['No options'] );
	}
}
