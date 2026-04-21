<?php
/**
 * REST API Endpoints für Open Data Wizard
 *
 * Namespace:  /wp-json/datenatlas/v1/
 * Endpoints:  GET /catalog, GET /datasets/<id>
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API endpoints for the Open Data Wizard plugin.
 *
 * @package OpenDataWizard
 */
class ODW_Rest_API {

	private const NAMESPACE = 'datenatlas/v1';

	/** Cache-TTL in Sekunden (5 Minuten). */
	private const CACHE_TTL = 300;

	/**
	 * DCAT-AP 3.0 JSON-LD @context inkl. Plugin-eigenem odw:-Namespace für Qualitätsdaten.
	 */
	private const JSONLD_CONTEXT = array(
		'dcat'  => 'https://www.w3.org/ns/dcat#',
		'dct'   => 'http://purl.org/dc/terms/',
		'foaf'  => 'http://xmlns.com/foaf/0.1/',
		'xsd'   => 'http://www.w3.org/2001/XMLSchema#',
		'vcard' => 'http://www.w3.org/2006/vcard/ns#',
		'skos'  => 'http://www.w3.org/2004/02/skos/core#',
		'odw'   => 'https://github.com/daimpad/OpenDataWizard/ns#',
	);

	/**
	 * Registers WordPress hooks.
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );

		// Cache invalidieren wenn ein Datensatz gespeichert oder gelöscht wird.
		add_action( 'save_post_odw_dataset', array( self::class, 'invalidate_cache' ) );
		add_action( 'trashed_post', array( self::class, 'invalidate_cache_on_trash' ) );
	}

	/**
	 * Registers the /catalog and /datasets/<id> REST routes.
	 */
	public static function register_routes(): void {
		$format_arg = array(
			'default'           => 'jsonld',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => fn( $v ) => in_array( $v, array( 'json', 'jsonld' ), true ),
		);

		register_rest_route(
			self::NAMESPACE,
			'/catalog',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_catalog' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'page'     => array(
						'default'           => 1,
						'sanitize_callback' => 'absint',
						'validate_callback' => fn( $v ) => is_numeric( $v ) && $v >= 1,
					),
					'per_page' => array(
						'default'           => 20,
						'sanitize_callback' => 'absint',
						'validate_callback' => fn( $v ) => is_numeric( $v ) && $v >= 1 && $v <= 100,
					),
					'theme'    => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'license'  => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'format'   => $format_arg,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/datasets/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_dataset' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id'     => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
						'validate_callback' => fn( $v ) => is_numeric( $v ) && $v > 0,
					),
					'format' => $format_arg,
				),
			)
		);
	}

	/**
	 * GET /catalog — returns a pageable dcat:Catalog JSON-LD response.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return WP_REST_Response|WP_Error Response on success, WP_Error on failure.
	 */
	public static function get_catalog( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$page     = (int) $request->get_param( 'page' );
		$per_page = (int) $request->get_param( 'per_page' );
		$theme    = (string) $request->get_param( 'theme' );
		$license  = (string) $request->get_param( 'license' );

		$cache_key = 'odw_catalog_' . md5( serialize( array( $page, $per_page, $theme, $license ) ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			$content_type = self::resolve_content_type( (string) $request->get_param( 'format' ) );
			$response     = new WP_REST_Response( $cached['body'], 200 );
			$response->header( 'Content-Type', $content_type );
			$response->header( 'X-WP-Total', (string) $cached['total'] );
			$response->header( 'X-WP-TotalPages', (string) $cached['pages'] );
			$response->header( 'X-ODW-Cache', 'HIT' );
			return $response;
		}

		$query_args = array(
			'post_type'      => 'odw_dataset',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => false,
		);

		$meta_query = array();

		if ( ! empty( $theme ) ) {
			$meta_query[] = array(
				'key'   => '_odw_theme',
				'value' => $theme,
			);
		}

		if ( ! empty( $license ) ) {
			$license_map = self::get_license_alias_map();
			$license_url = $license_map[ strtolower( $license ) ] ?? $license;

			$meta_query[] = array(
				'key'   => '_odw_license',
				'value' => $license_url,
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		$posts = $query->posts;
		$total = (int) $query->found_posts;
		$pages = (int) $query->max_num_pages;

		$datasets = array();
		foreach ( $posts as $post ) {
			$jsonld = odw_build_dataset_jsonld( (int) $post->ID );
			if ( $jsonld ) {
				$datasets[] = $jsonld;
			}
		}

		/**
		 * Filters the catalog title in the JSON-LD output.
		 *
		 * @param string $title The catalog title.
		 */
		$catalog_title = (string) apply_filters(
			'odw_catalog_title',
			get_bloginfo( 'name' ) . ' — Datenkatalog'
		);

		$catalog = array(
			'@context'      => self::JSONLD_CONTEXT,
			'@type'         => 'dcat:Catalog',
			'dct:title'     => $catalog_title,
			'dct:publisher' => array(
				'@type'     => 'foaf:Organization',
				'foaf:name' => get_bloginfo( 'name' ),
			),
			'dcat:dataset'  => $datasets,
		);

		set_transient(
			$cache_key,
			array(
				'body'  => $catalog,
				'total' => $total,
				'pages' => $pages,
			),
			self::CACHE_TTL
		);

		$content_type = self::resolve_content_type( (string) $request->get_param( 'format' ) );

		$response = new WP_REST_Response( $catalog, 200 );
		$response->header( 'Content-Type', $content_type );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $pages );
		$response->header( 'X-ODW-Cache', 'MISS' );

		return $response;
	}

	/**
	 * GET /datasets/<id> — returns a single dcat:Dataset JSON-LD response.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return WP_REST_Response|WP_Error Response on success, WP_Error on failure.
	 */
	public static function get_dataset( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'odw_dataset' !== $post->post_type ) {
			return new WP_Error(
				'odw_not_found',
				__( 'Datensatz nicht gefunden.', 'open-data-wizard' ),
				array( 'status' => 404 )
			);
		}

		if ( 'publish' !== $post->post_status ) {
			return new WP_Error(
				'odw_not_published',
				__( 'Dieser Datensatz ist nicht veröffentlicht.', 'open-data-wizard' ),
				array( 'status' => 403 )
			);
		}

		$cache_key = 'odw_dataset_' . $post_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			$content_type = self::resolve_content_type( (string) $request->get_param( 'format' ) );
			$response     = new WP_REST_Response( $cached, 200 );
			$response->header( 'Content-Type', $content_type );
			$response->header( 'X-ODW-Cache', 'HIT' );
			return $response;
		}

		$dataset = odw_build_dataset_jsonld( $post_id );

		if ( ! $dataset ) {
			return new WP_Error(
				'odw_build_failed',
				__( 'Datensatz konnte nicht gebaut werden.', 'open-data-wizard' ),
				array( 'status' => 500 )
			);
		}

		$body = array_merge(
			array( '@context' => self::JSONLD_CONTEXT ),
			$dataset
		);

		set_transient( $cache_key, $body, self::CACHE_TTL );

		$content_type = self::resolve_content_type( (string) $request->get_param( 'format' ) );

		$response = new WP_REST_Response( $body, 200 );
		$response->header( 'Content-Type', $content_type );
		$response->header( 'X-ODW-Cache', 'MISS' );

		return $response;
	}

	/**
	 * Invalidate all catalog caches when a dataset is saved.
	 *
	 * @param int $post_id Post ID of the saved dataset.
	 */
	public static function invalidate_cache( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post || 'odw_dataset' !== $post->post_type ) {
			return;
		}

		// Invalidate the single-dataset cache.
		delete_transient( 'odw_dataset_' . $post_id );

		// Invalidate all catalog caches via pattern.
		self::delete_catalog_transients();
	}

	/**
	 * Invalidate cache when a dataset is trashed.
	 *
	 * @param int $post_id Post ID of the trashed post.
	 */
	public static function invalidate_cache_on_trash( int $post_id ): void {
		$post = get_post( $post_id );
		if ( $post && 'odw_dataset' === $post->post_type ) {
			self::invalidate_cache( $post_id );
		}
	}

	/**
	 * Delete all catalog transients using a direct DB query (no viable alternative for pattern delete).
	 * Public alias used by ODW_Settings when cache TTL changes.
	 */
	public static function delete_catalog_transients_public(): void {
		self::delete_catalog_transients();
	}

	/**
	 * Deletes all odw_catalog_* transients via a direct DB query (pattern delete has no WP API).
	 */
	private static function delete_catalog_transients(): void {
		global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_odw_catalog_%',
				'_transient_timeout_odw_catalog_%'
			)
		);
	}

	/**
	 * Resolve Content-Type header from ?format= parameter.
	 *
	 * ?format=jsonld (default) → application/ld+json
	 * ?format=json            → application/json
	 *
	 * Foundation for future Turtle/RDF-XML content negotiation.
	 *
	 * @param string $format Format parameter value ('json' or 'jsonld').
	 * @return string Content-Type header value.
	 */
	private static function resolve_content_type( string $format ): string {
		return 'json' === $format
			? 'application/json; charset=UTF-8'
			: 'application/ld+json; charset=UTF-8';
	}

	/**
	 * Shorthand alias map for ?license= filter.
	 */
	private static function get_license_alias_map(): array {
		return array(
			'cc0'          => 'https://creativecommons.org/publicdomain/zero/1.0/',
			'cc0-1.0'      => 'https://creativecommons.org/publicdomain/zero/1.0/',
			'cc-by'        => 'https://creativecommons.org/licenses/by/4.0/',
			'cc-by-4.0'    => 'https://creativecommons.org/licenses/by/4.0/',
			'cc-by-sa'     => 'https://creativecommons.org/licenses/by-sa/4.0/',
			'cc-by-sa-4.0' => 'https://creativecommons.org/licenses/by-sa/4.0/',
			'dl-de-by-2.0' => 'https://www.govdata.de/dl-de/by-2-0',
		);
	}
}
