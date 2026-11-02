<?php
/**
 * Tests für ODW_Samples — Beispieldaten laden, entfernen, übernehmen — und für
 * die Übernahme des Demo-Datensatzes früherer Versionen (ODW_Setup).
 *
 * Kernzusage: Ein Beispiel ist in keinem Moment ein gewöhnlicher
 * veröffentlichter Datensatz. Die Kennzeichnung kommt vor der Veröffentlichung,
 * und nur „Als eigenen Datensatz übernehmen" hebt sie wieder auf.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ODW_Samples.
 */
class Test_ODW_Samples extends TestCase {

	/**
	 * $wpdb before the test, restored afterwards.
	 *
	 * @var mixed
	 */
	private $wpdb_before;

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
		$this->wpdb_before = $GLOBALS['wpdb'] ?? null;
	}

	/**
	 * Tear down WP_Mock, reset the per-request ID cache and restore $wpdb.
	 */
	protected function tearDown(): void {
		if ( class_exists( 'ODW_Samples' ) ) {
			$this->set_ids( null );
		}
		$GLOBALS['wpdb'] = $this->wpdb_before;
		\WP_Mock::tearDown();
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Loads the classes under test.
	 */
	private function load(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );

		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}

		foreach ( array( 'class-fields.php', 'class-batch-import.php', 'class-samples.php', 'class-setup.php' ) as $file ) {
			require_once ODW_PLUGIN_DIR . 'includes/' . $file;
		}
	}

	/**
	 * Sets the per-request ID caches, as if ids() and loaded_ids() had
	 * queried the database.
	 *
	 * @param array<int, int>|null $ids    All sample IDs, null to reset.
	 * @param array<int, int>|null $loaded IDs loaded from the file; defaults to $ids.
	 */
	private function set_ids( ?array $ids, ?array $loaded = null ): void {
		foreach ( array(
			'ids'    => $ids,
			'loaded' => $loaded ?? $ids,
		) as $name => $value ) {
			$property = new \ReflectionProperty( 'ODW_Samples', $name );
			$property->setAccessible( true );
			$property->setValue( null, $value );
		}
	}

	// -----------------------------------------------------------------------
	// Kennzeichnung
	// -----------------------------------------------------------------------

	/**
	 * Ein Beispiel erkennt man am Meta-Schlüssel _odw_sample.
	 */
	public function test_is_sample(): void {
		$this->load();

		\WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			static function ( $post_id, $key ) {
				return 5 === $post_id && '_odw_sample' === $key ? '1' : '';
			}
		);

		$this->assertTrue( ODW_Samples::is_sample( 5 ) );
		$this->assertFalse( ODW_Samples::is_sample( 6 ) );
		$this->assertFalse( ODW_Samples::is_sample( 0 ) );
	}

	/**
	 * Dieselbe Klausel schließt Beispiele aus Endpunkten und Katalog aus.
	 */
	public function test_exclude_clause(): void {
		$this->load();

		$this->assertSame(
			array(
				'key'     => '_odw_sample',
				'compare' => 'NOT EXISTS',
			),
			ODW_Samples::exclude_clause()
		);
	}

	/**
	 * In der Datensatzliste steht „Beispieldaten" hinter dem Titel.
	 */
	public function test_post_states(): void {
		$this->load();

		\WP_Mock::userFunction( 'get_post_meta' )->andReturn( '1' );

		$post            = new WP_Post();
		$post->ID        = 5;
		$post->post_type = 'odw_dataset';

		$this->assertSame( array( 'odw_sample' => 'Beispieldaten' ), ODW_Samples::post_states( array(), $post ) );

		$page            = new WP_Post();
		$page->ID        = 6;
		$page->post_type = 'page';
		$this->assertSame( array(), ODW_Samples::post_states( array(), $page ) );
	}

	/**
	 * Die mitgelieferte Datei bringt mindestens 40 Beispiele.
	 */
	public function test_available_counts_the_bundled_file(): void {
		$this->load();

		$this->assertGreaterThanOrEqual( 40, ODW_Samples::available() );
	}

	// -----------------------------------------------------------------------
	// Laden
	// -----------------------------------------------------------------------

	/**
	 * Sind schon Beispiele da, legt load() keine zweiten an — ein Doppelklick
	 * auf „Beispieldaten laden" bleibt folgenlos.
	 */
	public function test_load_refuses_when_samples_exist(): void {
		$this->load();
		$this->set_ids( array( 11, 12 ) );

		\WP_Mock::userFunction( 'wp_insert_post' )->never();

		$this->assertSame(
			array(
				'created'   => 0,
				'published' => 0,
				'error'     => 'exists',
			),
			ODW_Samples::load()
		);
	}

	/**
	 * Der alte Demo-Datensatz ist zwar ein Beispiel, verhindert das Laden aber
	 * nicht: Er stammt nicht aus der Beispieldatei.
	 */
	public function test_load_is_not_blocked_by_the_old_demo(): void {
		$this->load();
		$this->set_ids( array( 9 ), array() );

		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing(
			static function ( $url, $component = -1 ) {
				return parse_url( (string) $url, (int) $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Testersatz.
			}
		);
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'is_email' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'wp_insert_post' )->atLeast()->once()->andReturn( 0 );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( true );

		$this->assertNotSame( 'exists', ODW_Samples::load()['error'] );
	}

	/**
	 * Jeder Datensatz der Beispieldatei wird angelegt, gekennzeichnet und erst
	 * danach veröffentlicht.
	 */
	public function test_load_marks_every_dataset_before_publishing(): void {
		$this->load();
		$this->set_ids( array() );

		$events = array();
		$next   = 100;

		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing(
			static function ( $url, $component = -1 ) {
				return parse_url( (string) $url, (int) $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Testersatz.
			}
		);
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'is_email' )->andReturnUsing(
			static function ( $email ) {
				return false !== filter_var( (string) $email, FILTER_VALIDATE_EMAIL ) ? $email : false;
			}
		);
		\WP_Mock::userFunction( 'wp_insert_post' )->andReturnUsing(
			static function () use ( &$next ) {
				return ++$next;
			}
		);
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'get_post' )->andReturn( null );
		\WP_Mock::userFunction( 'get_post_type' )->andReturn( 'post' );
		\WP_Mock::userFunction( 'current_time' )->andReturn( '2026-11-02 12:00:00' );
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'sanitize_textarea_field' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'sanitize_email' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'absint' )->andReturnUsing(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);
		\WP_Mock::userFunction( 'carbon_set_post_meta' )->andReturn( null );
		\WP_Mock::userFunction( 'update_post_meta' )->andReturnUsing(
			static function ( $post_id, $key, $value ) use ( &$events ) {
				if ( '_odw_sample' === $key ) {
					$events[] = array( 'mark', (int) $post_id, $value );
				}
				return true;
			}
		);
		\WP_Mock::userFunction( 'wp_update_post' )->andReturnUsing(
			static function ( $data ) use ( &$events ) {
				$events[] = array( 'publish', (int) $data['ID'], $data['post_status'] );
				return (int) $data['ID'];
			}
		);
		\WP_Mock::userFunction( 'get_post_status' )->andReturn( 'publish' );

		$result = ODW_Samples::load();

		$expected = ODW_Samples::available();
		$this->assertSame( '', $result['error'] );
		$this->assertSame( $expected, $result['created'] );
		$this->assertSame( $expected, $result['published'] );
		$this->assertCount( 2 * $expected, $events );

		for ( $i = 0; $i < $expected; $i++ ) {
			$id = 101 + $i;
			$this->assertSame( array( 'mark', $id, '1' ), $events[ 2 * $i ], "Datensatz $id wird zuerst gekennzeichnet." );
			$this->assertSame( array( 'publish', $id, 'publish' ), $events[ 2 * $i + 1 ], "Datensatz $id wird danach veröffentlicht." );
		}
	}

	// -----------------------------------------------------------------------
	// Entfernen und übernehmen
	// -----------------------------------------------------------------------

	/**
	 * Entfernen löscht endgültig (kein Papierkorb, also keine Löschmeldung im
	 * Delta) — beim alten Demo-Datensatz samt der mitgelieferten CSV.
	 */
	public function test_remove_deletes_samples_and_the_demo_file(): void {
		$this->load();
		$this->set_ids( array( 11, 12 ) );

		\WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			static function ( $post_id, $key ) {
				if ( '_odw_sample' === $key ) {
					return 12 === $post_id ? 'demo' : '1';
				}
				return '_odw_file_id' === $key && 12 === $post_id ? '77' : '';
			}
		);
		\WP_Mock::userFunction( 'get_post_type' )->with( 77 )->andReturn( 'attachment' );
		\WP_Mock::userFunction( 'wp_delete_attachment' )->once()->with( 77, true )->andReturn( true );
		\WP_Mock::userFunction( 'wp_delete_post' )->once()->with( 11, true )->andReturn( true );
		\WP_Mock::userFunction( 'wp_delete_post' )->once()->with( 12, true )->andReturn( true );

		$this->assertSame( 2, ODW_Samples::remove() );
	}

	/**
	 * Übernehmen hebt die Kennzeichnung auf und speichert den Datensatz neu —
	 * so taucht er im Delta-Endpunkt als geändert auf.
	 */
	public function test_adopt_unmarks_and_touches_the_dataset(): void {
		$this->load();

		\WP_Mock::userFunction( 'get_post_meta' )->andReturn( '1' );
		\WP_Mock::userFunction( 'delete_post_meta' )->once()->with( 11, '_odw_sample' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_update_post' )->once()->with( array( 'ID' => 11 ) )->andReturn( 11 );

		$this->assertTrue( ODW_Samples::adopt( 11 ) );
	}

	/**
	 * Ein gewöhnlicher Datensatz bleibt beim Übernehmen unberührt.
	 */
	public function test_adopt_ignores_ordinary_datasets(): void {
		$this->load();

		\WP_Mock::userFunction( 'get_post_meta' )->andReturn( '' );
		\WP_Mock::userFunction( 'delete_post_meta' )->never();
		\WP_Mock::userFunction( 'wp_update_post' )->never();

		$this->assertFalse( ODW_Samples::adopt( 11 ) );
	}

	// -----------------------------------------------------------------------
	// Demo-Datensatz früherer Versionen (ODW_Setup)
	// -----------------------------------------------------------------------

	/**
	 * Registers get_option/update_option/delete_option over an option store
	 * and a $wpdb stand-in for the cache invalidation.
	 *
	 * @param array<string, mixed> $options Option store.
	 * @param array<string, mixed> $meta    Meta store, filled by update_post_meta().
	 * @param WP_Post              $post    Demo post.
	 */
	private function mock_demo_environment( array &$options, array &$meta, WP_Post $post ): void {
		\WP_Mock::userFunction( 'get_option' )->andReturnUsing(
			static function ( $name, $fallback = false ) use ( &$options ) {
				return $options[ $name ] ?? $fallback;
			}
		);
		\WP_Mock::userFunction( 'update_option' )->andReturnUsing(
			static function ( $name, $value ) use ( &$options ) {
				$options[ $name ] = $value;
				return true;
			}
		);
		\WP_Mock::userFunction( 'delete_option' )->andReturnUsing(
			static function ( $name ) use ( &$options ) {
				unset( $options[ $name ] );
				return true;
			}
		);
		\WP_Mock::userFunction( 'get_post' )->andReturn( $post );
		\WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			static function ( $post_id, $key ) use ( &$meta ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				return $meta[ $key ] ?? '';
			}
		);
		\WP_Mock::userFunction( 'update_post_meta' )->andReturnUsing(
			static function ( $post_id, $key, $value ) use ( &$meta ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				$meta[ $key ] = $value;
				return true;
			}
		);
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'delete_transient' )->andReturn( true );

		$GLOBALS['wpdb'] = new class() {
			/**
			 * Options table.
			 *
			 * @var string
			 */
			public string $options = 'wp_options';

			/**
			 * Stand-in for $wpdb->prepare().
			 *
			 * @param string $query Query.
			 * @return string
			 */
			public function prepare( string $query ): string {
				return $query;
			}

			/**
			 * Stand-in for $wpdb->query().
			 *
			 * @return int
			 */
			public function query(): int {
				return 0;
			}
		};
	}

	/**
	 * Ein nie bearbeiteter Demo-Datensatz wird zum Beispiel — damit fällt er
	 * aus den Endpunkten — und bekommt eine Sprache, die das Auswahlfeld kennt.
	 */
	public function test_migrate_demo_marks_untouched_demo_and_fixes_language(): void {
		$this->load();

		$post                    = new WP_Post();
		$post->ID                = 9;
		$post->post_type         = 'odw_dataset';
		$post->post_date_gmt     = '2026-09-01 10:00:00';
		$post->post_modified_gmt = '2026-09-01 10:00:00';

		$options = array( 'odw_demo_post_id' => 9 );
		$meta    = array( '_odw_language' => 'de' );
		$this->mock_demo_environment( $options, $meta, $post );

		ODW_Setup::maybe_migrate_demo();

		$this->assertSame( 'demo', $meta['_odw_sample'] );
		$this->assertSame( 'http://publications.europa.eu/resource/authority/language/DEU', $meta['_odw_language'] );
		$this->assertArrayNotHasKey( 'odw_demo_post_id', $options );
		$this->assertSame( '1', $options['odw_demo_migrated'] );

		// Einmalig: Der zweite Aufruf ändert nichts mehr.
		$meta = array();
		ODW_Setup::maybe_migrate_demo();
		$this->assertSame( array(), $meta );
	}

	/**
	 * Wer den Demo-Datensatz bearbeitet hat, hat daraus vermutlich den ersten
	 * echten gemacht. Er bleibt in den Endpunkten; nur die Sprache wird
	 * repariert.
	 */
	public function test_migrate_demo_keeps_an_edited_demo(): void {
		$this->load();

		$post                    = new WP_Post();
		$post->ID                = 9;
		$post->post_type         = 'odw_dataset';
		$post->post_date_gmt     = '2026-09-01 10:00:00';
		$post->post_modified_gmt = '2026-10-15 08:30:00';

		$options = array( 'odw_demo_post_id' => 9 );
		$meta    = array( '_odw_language' => 'de' );
		$this->mock_demo_environment( $options, $meta, $post );

		ODW_Setup::maybe_migrate_demo();

		$this->assertArrayNotHasKey( '_odw_sample', $meta );
		$this->assertSame( 'http://publications.europa.eu/resource/authority/language/DEU', $meta['_odw_language'] );
		$this->assertSame( '1', $options['odw_demo_migrated'] );
	}
}
