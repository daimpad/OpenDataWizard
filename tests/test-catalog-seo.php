<?php
/**
 * Tests für ODW_Catalog_Seo (Kopfbereich und Cache-Hinweise, Stufe 2).
 *
 * Geprüft werden die reinen Funktionen. Statuscode, Canonical, Titel und
 * robots gegen ein echtes WordPress prüft tests/e2e/catalog.spec.js.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! class_exists( 'WP_Post' ) ) {
	/**
	 * Minimaler WP_Post-Ersatz — dieselben Felder wie in test-catalog.php und
	 * test-quality.php, damit die Ladereihenfolge keine Rolle spielt.
	 *
	 * @package OpenDataWizard
	 */
	class WP_Post {

		/**
		 * Post ID.
		 *
		 * @var int
		 */
		public $ID = 0;

		/**
		 * Post type.
		 *
		 * @var string
		 */
		public $post_type = 'odw_dataset';

		/**
		 * Post status.
		 *
		 * @var string
		 */
		public $post_status = 'draft';

		/**
		 * Creation date (GMT).
		 *
		 * @var string
		 */
		public $post_date_gmt = '';

		/**
		 * Last modification date (GMT).
		 *
		 * @var string
		 */
		public $post_modified_gmt = '';

		/**
		 * Last modification date (local time).
		 *
		 * @var string
		 */
		public $post_modified = '';

		/**
		 * Post content.
		 *
		 * @var string
		 */
		public $post_content = '';
	}
}

/**
 * Unit tests for ODW_Catalog_Seo.
 *
 * @package OpenDataWizard
 */
class Test_ODW_Catalog_Seo extends TestCase {

	/**
	 * Set up WP_Mock before each test.
	 */
	protected function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Tear down WP_Mock after each test.
	 */
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Lädt die Klassen.
	 */
	private function load(): void {
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		\WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );

		foreach ( array( 'class-fields.php', 'class-setup.php', 'class-shortcode.php', 'class-catalog.php', 'class-catalog-filters.php', 'class-catalog-seo.php' ) as $file ) {
			require_once ODW_PLUGIN_DIR . 'includes/' . $file;
		}
	}

	/**
	 * Ein Datensatz-Objekt.
	 *
	 * @param int    $id     Post ID.
	 * @param string $status Status.
	 * @return WP_Post
	 */
	private function post( int $id, string $status = 'publish' ): WP_Post {
		$post              = new WP_Post();
		$post->ID          = $id;
		$post->post_type   = 'odw_dataset';
		$post->post_status = $status;
		return $post;
	}

	/**
	 * Prüft die once()/never()-Erwartungen sofort und zählt sie.
	 */
	private function verify_expectations(): void {
		$container = \Mockery::getContainer();
		$container->mockery_verify();
		$this->addToAssertionCount( $container->mockery_getExpectationCount() );
	}

	/**
	 * Ungültige ID: 404 und noindex, kein Canonical, kein Titel.
	 */
	public function test_head_state_unknown_dataset(): void {
		$this->load();

		$this->assertSame(
			array(
				'status'         => 404,
				'noindex'        => true,
				'canonical_args' => array(),
				'title'          => '',
				'dataset'        => 0,
			),
			ODW_Catalog_Seo::head_state(
				array(
					'page'    => 3,
					'dataset' => 999,
				),
				true,
				null
			)
		);
	}

	/**
	 * Gültige ID: Canonical nur mit odw_ds (ohne Listenzustand), Titel des
	 * Datensatzes, indexierbar — auch wenn die Adresse Filter für den Rückweg
	 * trägt.
	 */
	public function test_head_state_dataset(): void {
		$this->load();
		\WP_Mock::userFunction( 'get_the_title' )->andReturn( 'Schulstandorte' );

		$this->assertSame(
			array(
				'status'         => 200,
				'noindex'        => false,
				'canonical_args' => array( 'odw_ds' => 21 ),
				'title'          => 'Schulstandorte',
				'dataset'        => 21,
			),
			ODW_Catalog_Seo::head_state(
				array(
					'page'    => 2,
					'dataset' => 21,
				),
				true,
				$this->post( 21 )
			)
		);
	}

	/**
	 * Liste: ab Seite 2 eigener Canonical; Filter, Suche oder Sortierung
	 * ergeben noindex ohne eigenen Canonical; Seite 1 bleibt unberührt.
	 */
	public function test_head_state_list(): void {
		$this->load();

		$page2 = ODW_Catalog_Seo::head_state(
			array(
				'page'    => 2,
				'dataset' => 0,
			),
			false,
			null
		);
		$this->assertSame( array( 'odw_page' => 2 ), $page2['canonical_args'] );
		$this->assertFalse( $page2['noindex'] );
		$this->assertSame( 200, $page2['status'] );

		$filtered = ODW_Catalog_Seo::head_state(
			array(
				'page'    => 2,
				'dataset' => 0,
			),
			true,
			null
		);
		$this->assertTrue( $filtered['noindex'] );
		$this->assertSame( array(), $filtered['canonical_args'] );

		$first = ODW_Catalog_Seo::head_state(
			array(
				'page'    => 1,
				'dataset' => 0,
			),
			false,
			null
		);
		$this->assertSame( array(), $first['canonical_args'] );
		$this->assertFalse( $first['noindex'] );

		$beyond = ODW_Catalog_Seo::head_state(
			array(
				'page'    => 9,
				'dataset' => 0,
			),
			false,
			null,
			true
		);
		$this->assertSame( 404, $beyond['status'], 'Seite hinter dem Ende: 404 statt Soft-404.' );
		$this->assertTrue( $beyond['noindex'] );
		$this->assertSame( array(), $beyond['canonical_args'] );
	}

	/**
	 * Titel der Detailansicht: Datensatz vor dem Seitentitel, der Rest bleibt.
	 */
	public function test_title_parts(): void {
		$this->load();

		$this->assertSame(
			array(
				'title'       => 'Schulstandorte',
				'odw_catalog' => 'Unsere Daten',
				'site'        => 'Musterstadt',
			),
			ODW_Catalog_Seo::title_parts(
				array(
					'title' => 'Unsere Daten',
					'site'  => 'Musterstadt',
				),
				'Schulstandorte'
			)
		);
	}

	/**
	 * Den Katalog betrifft jeder Wechsel von oder nach „veröffentlicht".
	 */
	public function test_affects_catalog(): void {
		$this->load();

		$this->assertTrue( ODW_Catalog_Seo::affects_catalog( 'publish', 'new' ) );
		$this->assertTrue( ODW_Catalog_Seo::affects_catalog( 'publish', 'publish' ) );
		$this->assertTrue( ODW_Catalog_Seo::affects_catalog( 'trash', 'publish' ) );
		$this->assertTrue( ODW_Catalog_Seo::affects_catalog( 'draft', 'publish' ) );
		$this->assertFalse( ODW_Catalog_Seo::affects_catalog( 'draft', 'draft' ) );
		$this->assertFalse( ODW_Catalog_Seo::affects_catalog( 'draft', 'trash' ) );
	}

	/**
	 * Speichern eines veröffentlichten Datensatzes meldet odw_catalog_changed
	 * — einmal, auch wenn derselbe Request ihn mehrfach speichert.
	 */
	public function test_after_insert_announces_once(): void {
		$this->load();
		$count = $this->count_announcements( array( 501 ) );

		ODW_Catalog_Seo::after_insert( 501, $this->post( 501 ), true, $this->post( 501, 'draft' ) );
		ODW_Catalog_Seo::after_insert( 501, $this->post( 501 ), true, $this->post( 501 ) );

		$this->assertSame( array( 501 => 1 ), $count->calls );
	}

	/**
	 * Zählt odw_catalog_changed je Post ID.
	 *
	 * @param array<int, int> $ids IDs, die gemeldet werden könnten.
	 * @return object Zähler mit calls: array<int, int>.
	 */
	private function count_announcements( array $ids ): object {
		$counter        = new stdClass();
		$counter->calls = array_fill_keys( $ids, 0 );
		foreach ( $ids as $id ) {
			\WP_Mock::onAction( 'odw_catalog_changed' )->with( $id )->perform(
				static function () use ( $counter, $id ): void {
					++$counter->calls[ $id ];
				}
			);
		}
		return $counter;
	}

	/**
	 * Gemeldet wird nur, was den öffentlichen Katalog betrifft: kein neuer
	 * Entwurf, kein fremder Post Type.
	 */
	public function test_should_announce(): void {
		$this->load();

		$page            = $this->post( 503 );
		$page->post_type = 'page';

		$this->assertFalse( ODW_Catalog_Seo::should_announce( $this->post( 502, 'draft' ), null ) );
		$this->assertFalse( ODW_Catalog_Seo::should_announce( $page, $page ) );
		$this->assertFalse( ODW_Catalog_Seo::should_announce( null, null ) );
		$this->assertTrue( ODW_Catalog_Seo::should_announce( $this->post( 502 ), null ), 'Sofort veröffentlicht.' );
		$this->assertTrue( ODW_Catalog_Seo::should_announce( $this->post( 502, 'trash' ), $this->post( 502 ) ), 'In den Papierkorb.' );
	}

	/**
	 * Endgültiges Löschen eines veröffentlichten Datensatzes meldet ebenfalls.
	 */
	public function test_after_delete_announces_published(): void {
		$this->load();
		$count = $this->count_announcements( array( 504, 505 ) );

		ODW_Catalog_Seo::after_delete( 504, $this->post( 504 ) );
		ODW_Catalog_Seo::after_delete( 505, $this->post( 505, 'draft' ) );

		$this->assertSame(
			array(
				504 => 1,
				505 => 0,
			),
			$count->calls,
			'Ein gelöschter Entwurf stand nie im Katalog.'
		);
	}

	/**
	 * Canonical: ungültige ID ohne Canonical, Detail und Seite 2 mit genau
	 * ihrem Parameter (Filter der Adresse fallen weg), sonst unverändert.
	 */
	public function test_canonical_for(): void {
		$this->load();
		\WP_Mock::userFunction( 'remove_query_arg' )->andReturnUsing(
			static function ( $keys, $url ) {
				$parts = explode( '?', (string) $url, 2 );
				$query = array();
				if ( isset( $parts[1] ) ) {
					parse_str( $parts[1], $query );
				}
				foreach ( (array) $keys as $key ) {
					unset( $query[ $key ] );
				}
				return $parts[0] . ( $query ? '?' . http_build_query( $query ) : '' );
			}
		);
		\WP_Mock::userFunction( 'add_query_arg' )->andReturnUsing(
			static function ( $args, $url ) {
				return $url . ( false === strpos( (string) $url, '?' ) ? '?' : '&' ) . http_build_query( $args );
			}
		);

		$base = array(
			'status'         => 200,
			'noindex'        => false,
			'canonical_args' => array(),
			'title'          => '',
			'dataset'        => 0,
		);

		$this->assertSame( '', ODW_Catalog_Seo::canonical_for( 'https://example.org/daten/', array_merge( $base, array( 'status' => 404 ) ) ) );
		$this->assertSame( 'https://example.org/daten/', ODW_Catalog_Seo::canonical_for( 'https://example.org/daten/', $base ) );
		$this->assertSame(
			'https://example.org/daten/?odw_ds=21',
			ODW_Catalog_Seo::canonical_for( 'https://example.org/daten/?odw_theme=EDUC', array_merge( $base, array( 'canonical_args' => array( 'odw_ds' => 21 ) ) ) )
		);
		$this->assertSame(
			'https://example.org/?page_id=7&odw_page=2',
			ODW_Catalog_Seo::canonical_for( 'https://example.org/?page_id=7', array_merge( $base, array( 'canonical_args' => array( 'odw_page' => 2 ) ) ) ),
			'Fremde Parameter der Seite (page_id) bleiben.'
		);
	}

	/**
	 * Titel eines SEO-Plugins: Datensatz davor, sonst unverändert.
	 */
	public function test_title_for(): void {
		$this->load();

		$this->assertSame( 'Schulstandorte – Unsere Daten – Musterstadt', ODW_Catalog_Seo::title_for( 'Unsere Daten – Musterstadt', 'Schulstandorte' ) );
		$this->assertSame( 'Unsere Daten', ODW_Catalog_Seo::title_for( 'Unsere Daten', '' ) );
		$this->assertSame( '', ODW_Catalog_Seo::title_for( '', 'Schulstandorte' ), 'Ein leerer Titel bleibt leer — das Plugin fällt dann auf Core zurück.' );
	}

	/**
	 * Yoasts Crawl-Bereinigung lässt alle eigenen Parameter stehen.
	 */
	public function test_allow_params(): void {
		$this->load();

		$this->assertSame(
			array( 'utm_source', 'odw_page', 'odw_ds', 'odw_theme', 'odw_license', 'odw_format', 'odw_sort', 'odw_q' ),
			ODW_Catalog_Seo::allow_params( array( 'utm_source', 'odw_page' ) )
		);
	}

	/**
	 * Setzt den Zustand der aufgerufenen Seite, wie head() ihn ermitteln würde.
	 *
	 * @param array<string, mixed>|null $head Zustand, null zum Zurücksetzen.
	 */
	private function set_head( ?array $head ): void {
		$property = new ReflectionProperty( 'ODW_Catalog_Seo', 'head' );
		$property->setAccessible( true );
		$property->setValue( null, $head );
	}

	/**
	 * Zustand einer Seite mit Abweichungen.
	 *
	 * @param array<string, mixed> $set Abweichungen.
	 * @return array<string, mixed>
	 */
	private function head( array $set ): array {
		return array_merge(
			array(
				'status'         => 200,
				'noindex'        => false,
				'canonical_args' => array(),
				'title'          => '',
				'dataset'        => 0,
				'post_id'        => 7,
			),
			$set
		);
	}

	/**
	 * Ein Yoast-Presentation-Objekt.
	 *
	 * @param int $id Objekt-ID.
	 * @return object
	 */
	private function presentation( int $id ): object {
		return (object) array(
			'model' => (object) array(
				'object_type' => 'post',
				'object_id'   => $id,
			),
		);
	}

	/**
	 * Robots in den drei Formen: Core-Array, Yoast-Form, Rank-Math-Form — nur
	 * für die Katalogseite und nur bei noindex.
	 */
	public function test_robots_adapters(): void {
		$this->load();
		$this->set_head( $this->head( array( 'noindex' => true ) ) );

		$this->assertSame(
			array(
				'max-image-preview' => 'large',
				'noindex'           => true,
				'follow'            => true,
			),
			ODW_Catalog_Seo::filter_robots(
				array(
					'index'             => true,
					'max-image-preview' => 'large',
				)
			)
		);
		$this->assertSame(
			array(
				'noindex'  => true,
				'nofollow' => true,
			),
			ODW_Catalog_Seo::filter_robots(
				array(
					'noindex'  => true,
					'nofollow' => true,
				)
			),
			'blog_public = 0: kein „noindex, nofollow, follow".'
		);

		$yoast = ODW_Catalog_Seo::yoast_robots(
			array(
				'index'             => 'index',
				'follow'            => 'follow',
				'max-snippet'       => 'max-snippet:-1',
				'max-image-preview' => 'max-image-preview:large',
			),
			$this->presentation( 7 )
		);
		$this->assertSame(
			array(
				'index'  => 'noindex',
				'follow' => 'follow',
			),
			$yoast
		);
		$this->assertSame( array( 'index' => 'index' ), ODW_Catalog_Seo::yoast_robots( array( 'index' => 'index' ), $this->presentation( 8 ) ), 'Fremde Seite bleibt unberührt.' );
		$this->assertSame(
			array(
				'index'  => 'noindex',
				'follow' => 'nofollow',
			),
			ODW_Catalog_Seo::yoast_robots(
				array(
					'index'  => 'index',
					'follow' => 'nofollow',
				),
				$this->presentation( 7 )
			),
			'Ein nofollow der Redaktion bleibt.'
		);

		$this->assertSame(
			array(
				'index'  => 'noindex',
				'follow' => 'nofollow',
			),
			ODW_Catalog_Seo::rank_math_robots(
				array(
					'index'  => 'index',
					'follow' => 'nofollow',
				)
			),
			'Ein vorhandenes nofollow (blog_public = 0) bleibt.'
		);

		$this->set_head( $this->head( array() ) );
		$this->assertSame( array( 'index' => 'index' ), ODW_Catalog_Seo::rank_math_robots( array( 'index' => 'index' ) ) );
		$this->set_head( null );
	}

	/**
	 * Titel: Core-Teile, Yoast und Rank Math bekommen den Datensatztitel
	 * vorangestellt; Social-Titel nicht doppelt.
	 */
	public function test_title_adapters(): void {
		$this->load();
		$this->mock_strip_tags();
		$this->set_head(
			$this->head(
				array(
					'title'   => 'Schulstandorte',
					'dataset' => 21,
				)
			)
		);

		$this->assertSame( 'Schulstandorte', ODW_Catalog_Seo::filter_title_parts( array( 'title' => 'Daten' ) )['title'] );
		$this->assertSame( 'Schulstandorte – Daten – Stadt', ODW_Catalog_Seo::yoast_title( 'Daten – Stadt', $this->presentation( 7 ) ) );
		$this->assertSame( 'Daten – Stadt', ODW_Catalog_Seo::yoast_title( 'Daten – Stadt', $this->presentation( 9 ) ) );
		$this->assertSame( 'Schulstandorte – Daten | Stadt', ODW_Catalog_Seo::rank_math_title( 'Daten | Stadt' ) );
		$this->assertSame( 'Schulstandorte – Daten | Stadt', ODW_Catalog_Seo::rank_math_social_title( 'Schulstandorte – Daten | Stadt' ), 'Schon ergänzt: nicht doppelt.' );
		$this->assertSame( 'Schulstandorte – Teilen', ODW_Catalog_Seo::rank_math_social_title( 'Teilen' ) );

		$this->set_head( null );
	}

	/**
	 * Stellt wp_strip_all_tags() bereit.
	 */
	private function mock_strip_tags(): void {
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			static function ( $text ): string {
				return trim( strip_tags( (string) $text ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Testersatz für wp_strip_all_tags(), kein WordPress geladen.
			}
		);
	}

	/**
	 * Rank Math bearbeitet den Seitentitel nach dem Filter weiter — der
	 * schon ergänzte Datensatztitel wird trotzdem erkannt.
	 */
	public function test_starts_with_title(): void {
		$this->load();
		$this->mock_strip_tags();

		$this->assertTrue( ODW_Catalog_Seo::starts_with_title( 'Schulstandorte Der Stadt – Daten – Stadt', 'Schulstandorte der stadt' ), 'Großschreibung jedes Worts.' );
		$this->assertTrue( ODW_Catalog_Seo::starts_with_title( 'CO2-Emissionen – Daten', 'CO<sub>2</sub>-Emissionen' ), 'Tags entfernt.' );
		$this->assertTrue( ODW_Catalog_Seo::starts_with_title( 'Schüler &amp; Lehrer – Daten', 'Schüler &#038; Lehrer' ), 'Entitäten.' );
		$this->assertTrue( ODW_Catalog_Seo::starts_with_title( 'Zwei Leer – Daten', 'Zwei  Leer' ), 'Leerraum zusammengefasst.' );
		$this->assertFalse( ODW_Catalog_Seo::starts_with_title( 'Daten – Stadt', 'Schulstandorte' ) );

		$this->set_head(
			$this->head(
				array(
					'title'   => 'Schulstandorte der stadt',
					'dataset' => 21,
				)
			)
		);
		$this->assertSame( 'Schulstandorte Der Stadt – Daten', ODW_Catalog_Seo::rank_math_social_title( 'Schulstandorte Der Stadt – Daten' ) );
		$this->set_head( null );
	}

	/**
	 * Yoast-Schema: Fremde Werte laufen unverändert durch (ein false entfernt
	 * den Knoten), der Name ist Text ohne Entitäten.
	 */
	public function test_yoast_schema_webpage(): void {
		$this->load();
		$this->mock_strip_tags();
		$this->set_head(
			$this->head(
				array(
					'title'   => 'Schüler &#038; Lehrer',
					'dataset' => 21,
				)
			)
		);
		$context = (object) array(
			'indexable' => (object) array( 'object_id' => 7 ),
		);

		$this->assertFalse( ODW_Catalog_Seo::yoast_schema_webpage( false, $context ) );
		$this->assertNull( ODW_Catalog_Seo::yoast_schema_webpage( null, $context ) );

		$piece = ODW_Catalog_Seo::yoast_schema_webpage( array( 'name' => 'Daten - Stadt' ), $context );
		$this->assertSame( 'Schüler & Lehrer – Daten - Stadt', $piece['name'] );

		$this->set_head( null );
	}

	/**
	 * Canonical bei Yoast und Rank Math: ungültige ID ohne Canonical.
	 */
	public function test_canonical_adapters(): void {
		$this->load();
		$this->set_head( $this->head( array( 'status' => 404 ) ) );

		$this->assertSame( '', ODW_Catalog_Seo::yoast_url( 'https://example.org/daten/', $this->presentation( 7 ) ) );
		$this->assertSame( '', ODW_Catalog_Seo::rank_math_canonical( 'https://example.org/daten/' ) );
		$this->assertSame( 'https://example.org/andere/', ODW_Catalog_Seo::yoast_url( 'https://example.org/andere/', $this->presentation( 8 ) ) );

		$this->set_head( null );
	}

	/**
	 * All in One SEO: robots in seiner Form, Titel nur einmal und mit
	 * maskierten Smart-Tag-/Shortcode-Zeichen, Canonical ohne Verdopplung.
	 */
	public function test_aioseo_adapters(): void {
		$this->load();
		\WP_Mock::userFunction( 'remove_query_arg' )->andReturnUsing(
			static function ( $keys, $url ) {
				$parts = explode( '?', (string) $url, 2 );
				$query = array();
				if ( isset( $parts[1] ) ) {
					parse_str( $parts[1], $query );
				}
				foreach ( (array) $keys as $key ) {
					unset( $query[ $key ] );
				}
				return $parts[0] . ( $query ? '?' . http_build_query( $query ) : '' );
			}
		);
		\WP_Mock::userFunction( 'add_query_arg' )->andReturnUsing(
			static function ( $args, $url ) {
				return $url . ( false === strpos( (string) $url, '?' ) ? '?' : '&' ) . http_build_query( $args );
			}
		);

		$this->set_head(
			$this->head(
				array(
					'title'          => 'Messwerte #1 [Rohdaten]',
					'dataset'        => 21,
					'canonical_args' => array( 'odw_ds' => 21 ),
				)
			)
		);

		$once = ODW_Catalog_Seo::aioseo_title( 'Daten - Stadt' );
		$this->assertSame( 'Messwerte &#35;1 &#91;Rohdaten] – Daten - Stadt', $once );
		$this->assertSame( $once, ODW_Catalog_Seo::aioseo_title( $once ), 'Zweiter Aufruf (og:title) stellt nicht erneut voran.' );

		// get_canonical_url hat odw_ds schon angehängt — nicht verdoppeln.
		$this->assertSame( 'https://example.org/daten/?odw_ds=21', ODW_Catalog_Seo::aioseo_canonical( 'https://example.org/daten/?odw_ds=21' ) );

		$this->set_head( $this->head( array( 'noindex' => true ) ) );
		$this->assertSame(
			array(
				'noindex'  => 'noindex',
				'nofollow' => 'nofollow',
			),
			ODW_Catalog_Seo::aioseo_robots(
				array(
					'noindex'  => '',
					'nofollow' => 'nofollow',
				)
			)
		);

		$this->set_head( null );
	}

	/**
	 * AIOSEO-Schema: Der WebPage-Knoten bekommt die Adresse der Detailansicht,
	 * andere Knoten und die @id bleiben.
	 */
	public function test_aioseo_schema(): void {
		$this->load();
		\WP_Mock::userFunction( 'remove_query_arg' )->andReturnArg( 1 );
		\WP_Mock::userFunction( 'add_query_arg' )->andReturnUsing(
			static function ( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);

		$graph = array(
			array(
				'@type' => 'WebPage',
				'@id'   => 'https://example.org/daten/#webpage',
				'url'   => 'https://example.org/daten/',
			),
			array(
				'@type' => 'WebSite',
				'@id'   => 'https://example.org/#website',
				'url'   => 'https://example.org/',
			),
		);

		$this->set_head(
			$this->head(
				array(
					'title'          => 'Schulstandorte',
					'dataset'        => 21,
					'canonical_args' => array( 'odw_ds' => 21 ),
				)
			)
		);
		$out = ODW_Catalog_Seo::aioseo_schema( $graph );
		$this->assertSame( 'https://example.org/daten/?odw_ds=21', $out[0]['url'] );
		$this->assertSame( 'https://example.org/daten/#webpage', $out[0]['@id'] );
		$this->assertSame( $graph[1], $out[1] );

		$this->set_head( $this->head( array( 'noindex' => true ) ) );
		$this->assertSame( $graph, ODW_Catalog_Seo::aioseo_schema( $graph ), 'Varianten und 404: unverändert.' );

		$this->set_head( null );
	}
}
