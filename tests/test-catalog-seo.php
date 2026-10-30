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
		\WP_Mock::expectAction( 'odw_catalog_changed', 501 );

		ODW_Catalog_Seo::after_insert( 501, $this->post( 501 ), true, $this->post( 501, 'draft' ) );
		ODW_Catalog_Seo::after_insert( 501, $this->post( 501 ), true, $this->post( 501 ) );

		$this->verify_expectations();
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
		\WP_Mock::expectAction( 'odw_catalog_changed', 504 );

		ODW_Catalog_Seo::after_delete( 504, $this->post( 504 ) );
		ODW_Catalog_Seo::after_delete( 505, $this->post( 505, 'draft' ) );

		$this->verify_expectations();
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
}
