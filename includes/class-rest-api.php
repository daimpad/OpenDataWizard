<?php
/**
 * REST API Endpoints für Open Data Wizard
 *
 * Namespace:  /wp-json/odw/v1/ (alias: /wp-json/datenatlas/v1/)
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

	/**
	 * DCAT-AP 3.0 / DCAT-AP.de JSON-LD @context inkl. Plugin-eigenem odw:-Namespace.
	 */
	private const JSONLD_CONTEXT = array(
		'dcat'   => 'http://www.w3.org/ns/dcat#',
		'dcatap' => 'http://data.europa.eu/r5r/',
		'dct'    => 'http://purl.org/dc/terms/',
		'foaf'   => 'http://xmlns.com/foaf/0.1/',
		'xsd'    => 'http://www.w3.org/2001/XMLSchema#',
		'vcard'  => 'http://www.w3.org/2006/vcard/ns#',
		'skos'   => 'http://www.w3.org/2004/02/skos/core#',
		'rdfs'   => 'http://www.w3.org/2000/01/rdf-schema#',
		'locn'   => 'http://www.w3.org/ns/locn#',
		'adms'   => 'http://www.w3.org/ns/adms#',
		'owl'    => 'http://www.w3.org/2002/07/owl#',
		'prov'   => 'http://www.w3.org/ns/prov#',
		'odrl'   => 'http://www.w3.org/ns/odrl/2/',
		'spdx'   => 'http://spdx.org/rdf/terms#',
		'dcatde' => 'http://dcat-ap.de/def/dcatde/',
		'odw'    => 'https://github.com/daimpad/OpenDataWizard/ns#',
	);

	/**
	 * Get the configured cache TTL in seconds. Falls back to 5 minutes if not configured.
	 *
	 * @return int Cache TTL in seconds.
	 */
	private static function get_cache_ttl(): int {
		if ( class_exists( 'ODW_Settings' ) ) {
			$ttl = (int) ODW_Settings::get( 'cache_ttl' );
			if ( 0 !== $ttl ) {
				return $ttl;
			}
		}
		return 300;
	}

	/**
	 * Registers WordPress hooks.
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );

		// Turtle-Antworten roh ausliefern (WP würde sonst JSON-kodieren).
		add_filter( 'rest_pre_serve_request', array( self::class, 'serve_raw_rdf' ), 10, 2 );

		// Cache invalidieren wenn ein Datensatz gespeichert oder gelöscht wird.
		add_action( 'save_post_odw_dataset', array( self::class, 'invalidate_cache' ) );
		add_action( 'trashed_post', array( self::class, 'invalidate_cache_on_trash' ) );
	}

	/**
	 * Registers the /catalog, /datasets/<id>, and /delta REST routes.
	 */
	public static function register_routes(): void {
		// Leerer Default bedeutet bewusst "nicht angegeben": Dann entscheidet der
		// Accept-Header (siehe resolve_format). Alle Endpunkte liefern dieselben
		// Serialisierungen, damit Harvester sich nicht je Route umstellen müssen.
		$format_arg = array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => fn( $v ) => in_array( $v, array( '', 'json', 'jsonld', 'turtle', 'ttl' ), true ),
		);

		$pagination_args = array(
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
		);

		register_rest_route(
			self::NAMESPACE,
			'/catalog',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_catalog' ),
				'permission_callback' => '__return_true',
				'args'                => array_merge(
					$pagination_args,
					array(
						'theme'   => array(
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'license' => array(
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						),
						// full=1 liefert den vollständigen Katalog (alle veröffentlichten
						// Datensätze) in einem Dokument — der Bereitstellungspunkt für
						// Pull-Harvesting durch externe Open-Data-Portale.
						'full'    => array(
							'default'           => 0,
							'sanitize_callback' => 'absint',
							'validate_callback' => fn( $v ) => in_array( (int) $v, array( 0, 1 ), true ),
						),
						'format'  => $format_arg,
					)
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

		register_rest_route(
			self::NAMESPACE,
			'/delta',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_delta' ),
				'permission_callback' => '__return_true',
				'args'                => array_merge(
					$pagination_args,
					array(
						'since'  => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => array( self::class, 'validate_since_param' ),
							'description'       => 'ISO 8601 datetime — return only datasets modified after this point.',
						),
						'format' => $format_arg,
					)
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
		$format  = self::resolve_format( $request );
		$full    = 1 === (int) $request->get_param( 'full' );
		$theme   = (string) $request->get_param( 'theme' );
		$license = (string) $request->get_param( 'license' );

		// full=1: der gesamte Katalog in einem Dokument (Harvest-Endpoint) — keine
		// Paginierung. Sonst die angeforderte Seite.
		$page     = $full ? 1 : (int) $request->get_param( 'page' );
		$per_page = $full ? -1 : (int) $request->get_param( 'per_page' );

		$cache_key = 'odw_catalog_' . md5( serialize( array( $full ? 'full' : 'page', $page, $per_page, $theme, $license ) ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return self::catalog_response( $cached['body'], (int) $cached['total'], (int) $cached['pages'], $format, 'HIT', $cache_key );
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
				'value' => class_exists( 'ODW_Fields' ) ? ODW_Fields::resolve_theme_uri( $theme ) : $theme,
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

		$catalog = self::build_catalog_document( $datasets );

		// Only cache non-empty result pages. This prevents unauthenticated
		// requests with arbitrary theme/license/page values (which yield no
		// datasets) from creating unbounded distinct transients.
		if ( ! empty( $datasets ) ) {
			set_transient(
				$cache_key,
				array(
					'body'  => $catalog,
					'total' => $total,
					'pages' => $pages,
				),
				self::get_cache_ttl()
			);
		}

		return self::catalog_response( $catalog, $total, $pages, $format, 'MISS', $cache_key );
	}

	/**
	 * Assemble the dcat:Catalog JSON-LD document from the given dataset nodes.
	 *
	 * The catalog carries a stable `@id` (its own endpoint URL) and a homepage so
	 * RDF harvesters can dereference and de-duplicate it across runs.
	 *
	 * @param array<int, array<string, mixed>> $datasets Dataset JSON-LD nodes.
	 * @return array<string, mixed>
	 */
	private static function build_catalog_document( array $datasets ): array {
		/**
		 * Filters the catalog title in the JSON-LD output.
		 *
		 * @param string $title The catalog title.
		 */
		$catalog_title = (string) apply_filters(
			'odw_catalog_title',
			get_bloginfo( 'name' ) . ' — Datenkatalog'
		);

		/**
		 * Filters the catalog description in the JSON-LD output.
		 *
		 * @param string $description The catalog description (empty by default).
		 */
		$catalog_description = (string) apply_filters( 'odw_catalog_description', '' );

		// DCAT-AP verlangt dct:description am Katalog (min. 1). Ist keine gesetzt,
		// auf die WordPress-Seitenbeschreibung und schließlich einen generischen
		// Satz zurückfallen, damit die Konformität gewahrt bleibt.
		if ( '' === trim( $catalog_description ) ) {
			$tagline             = (string) get_bloginfo( 'description' );
			$catalog_description = '' !== trim( $tagline )
				? $tagline
				/* translators: %s: site name. */
				: sprintf( __( 'Offene Daten, bereitgestellt von %s.', 'open-data-wizard' ), get_bloginfo( 'name' ) );
		}

		$catalog = array(
			'@context'      => self::JSONLD_CONTEXT,
			'@id'           => rest_url( self::NAMESPACE . '/catalog' ),
			'@type'         => 'dcat:Catalog',
			'dct:title'     => array(
				'@value'    => $catalog_title,
				'@language' => 'de',
			),
			'dct:publisher' => array(
				'@type'     => 'foaf:Organization',
				'foaf:name' => get_bloginfo( 'name' ),
			),
			'foaf:homepage' => array( '@id' => home_url( '/' ) ),
			'dcat:dataset'  => $datasets,
		);

		if ( '' !== $catalog_description ) {
			$catalog['dct:description'] = array(
				'@value'    => $catalog_description,
				'@language' => 'de',
			);
		}

		return $catalog;
	}

	/**
	 * Build the catalog response in the requested serialisation.
	 *
	 * For `turtle` the body is a raw Turtle string (served verbatim via
	 * serve_raw_rdf()); otherwise it is the JSON-LD array.
	 *
	 * @param array<string, mixed> $catalog     Catalog JSON-LD document.
	 * @param int                  $total       Total datasets.
	 * @param int                  $pages       Total pages.
	 * @param string               $format      Normalised format (turtle|json|jsonld).
	 * @param string               $cache_state HIT or MISS.
	 * @param string               $cache_key   Transient key of the catalogue ('' disables Turtle caching).
	 * @return WP_REST_Response
	 */
	private static function catalog_response( array $catalog, int $total, int $pages, string $format, string $cache_state, string $cache_key = '' ): WP_REST_Response {
		list( $body, $content_type ) = self::serialize_document( $catalog, $format, $cache_key );

		$response = new WP_REST_Response( $body, 200 );
		$response->header( 'Content-Type', $content_type );
		// Die Antwort hängt vom Accept-Header ab — Caches müssen danach variieren.
		$response->header( 'Vary', 'Accept' );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $pages );
		$response->header( 'X-ODW-Cache', $cache_state );

		return $response;
	}

	/**
	 * Normalise a format parameter (maps the `ttl` alias to `turtle`).
	 *
	 * @param string $format Raw format parameter.
	 * @return string
	 */
	private static function normalize_format( string $format ): string {
		return 'ttl' === $format ? 'turtle' : $format;
	}

	/**
	 * Media types this API can serialise, mapped to the internal format key.
	 * `application/x-turtle` is the legacy alias some clients still send.
	 */
	private const MEDIA_TYPES = array(
		'text/turtle'          => 'turtle',
		'application/x-turtle' => 'turtle',
		'application/ld+json'  => 'jsonld',
		'application/json'     => 'json',
	);

	/**
	 * Determine the response serialisation for a request.
	 *
	 * An explicit `?format=` always wins — it is unambiguous and stays
	 * copy-pasteable. Only when it is absent is the `Accept` header evaluated,
	 * so RDF harvesters that negotiate purely via headers are served correctly.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return string One of turtle|json|jsonld.
	 */
	private static function resolve_format( WP_REST_Request $request ): string {
		$explicit = self::normalize_format( (string) $request->get_param( 'format' ) );

		if ( '' !== $explicit ) {
			return $explicit;
		}

		return self::negotiate_accept( (string) $request->get_header( 'accept' ) );
	}

	/**
	 * Pick the best supported serialisation from an Accept header.
	 *
	 * Honours q-values (RFC 9110); equal weights keep the client's order. An
	 * unknown or wildcard-only Accept falls back to JSON-LD, the DCAT-AP default.
	 *
	 * @param string $accept Raw Accept header value.
	 * @return string One of turtle|json|jsonld.
	 */
	private static function negotiate_accept( string $accept ): string {
		$accept = trim( $accept );
		if ( '' === $accept ) {
			return 'jsonld';
		}

		$candidates = array();

		foreach ( explode( ',', $accept ) as $index => $part ) {
			$segments   = explode( ';', $part );
			$media_type = strtolower( trim( array_shift( $segments ) ) );

			if ( '' === $media_type ) {
				continue;
			}

			$quality = 1.0;
			foreach ( $segments as $segment ) {
				$segment = trim( $segment );
				if ( 0 === stripos( $segment, 'q=' ) ) {
					$quality = (float) substr( $segment, 2 );
				}
			}

			// q=0 means "not acceptable" — skip entirely.
			if ( $quality <= 0 ) {
				continue;
			}

			$candidates[] = array(
				'type'    => $media_type,
				'quality' => $quality,
				'order'   => $index,
			);
		}

		// Highest q first; ties keep the order in which the client listed them.
		usort(
			$candidates,
			static function ( array $a, array $b ): int {
				if ( $a['quality'] === $b['quality'] ) {
					return $a['order'] <=> $b['order'];
				}
				return $b['quality'] <=> $a['quality'];
			}
		);

		foreach ( $candidates as $candidate ) {
			if ( isset( self::MEDIA_TYPES[ $candidate['type'] ] ) ) {
				return self::MEDIA_TYPES[ $candidate['type'] ];
			}
			// */* (or a type wildcard) accepts our default.
			if ( '*/*' === $candidate['type'] || 'application/*' === $candidate['type'] ) {
				return 'jsonld';
			}
		}

		return 'jsonld';
	}

	/**
	 * Serialise a JSON-LD document into the requested representation.
	 *
	 * Turtle is cached separately: the document transient only avoids the
	 * database work, while serialising the whole graph would otherwise run on
	 * every request to these unauthenticated endpoints.
	 *
	 * @param array<string, mixed> $doc       JSON-LD document.
	 * @param string               $format    Normalised format (turtle|json|jsonld).
	 * @param string               $cache_key Transient key of the document ('' disables Turtle caching).
	 * @return array{0: array<string, mixed>|string, 1: string} Body and Content-Type.
	 */
	private static function serialize_document( array $doc, string $format, string $cache_key = '' ): array {
		if ( 'turtle' !== $format ) {
			return array( $doc, self::resolve_content_type( $format ) );
		}

		$turtle_key = '' !== $cache_key ? $cache_key . '_ttl' : '';
		$cached_ttl = '' !== $turtle_key ? get_transient( $turtle_key ) : false;

		if ( is_string( $cached_ttl ) && '' !== $cached_ttl ) {
			return array( $cached_ttl, 'text/turtle; charset=UTF-8' );
		}

		$body = ODW_Rdf::to_turtle( $doc );
		if ( '' !== $turtle_key ) {
			set_transient( $turtle_key, $body, self::get_cache_ttl() );
		}

		return array( $body, 'text/turtle; charset=UTF-8' );
	}

	/**
	 * Build a REST response for a single JSON-LD document.
	 *
	 * Shared by /datasets/<id> and /delta so every endpoint negotiates and caches
	 * identically.
	 *
	 * @param array<string, mixed> $doc         JSON-LD document.
	 * @param string               $format      Normalised format (turtle|json|jsonld).
	 * @param string               $cache_state HIT or MISS.
	 * @param string               $cache_key   Transient key ('' disables Turtle caching).
	 * @param array<string,string> $headers     Additional response headers.
	 * @return WP_REST_Response
	 */
	private static function document_response( array $doc, string $format, string $cache_state, string $cache_key = '', array $headers = array() ): WP_REST_Response {
		list( $body, $content_type ) = self::serialize_document( $doc, $format, $cache_key );

		$response = new WP_REST_Response( $body, 200 );
		$response->header( 'Content-Type', $content_type );
		// Die Antwort hängt vom Accept-Header ab — Caches müssen danach variieren.
		$response->header( 'Vary', 'Accept' );
		$response->header( 'X-ODW-Cache', $cache_state );

		foreach ( $headers as $key => $value ) {
			$response->header( $key, $value );
		}

		return $response;
	}

	/**
	 * Serve RDF (Turtle) responses verbatim instead of JSON-encoding them.
	 *
	 * WordPress' REST server always JSON-encodes response data; for a Turtle
	 * string body we short-circuit and echo it raw with the right Content-Type.
	 *
	 * @param bool             $served  Whether the request was already served.
	 * @param WP_HTTP_Response $result  Response object (a WP_REST_Response for REST routes).
	 * @return bool
	 */
	public static function serve_raw_rdf( $served, $result ): bool {
		if ( $served || ! $result instanceof WP_REST_Response ) {
			return (bool) $served;
		}

		$headers      = $result->get_headers();
		$content_type = (string) ( $headers['Content-Type'] ?? '' );
		$data         = $result->get_data();

		if ( false === strpos( $content_type, 'text/turtle' ) || ! is_string( $data ) ) {
			return (bool) $served;
		}

		if ( ! headers_sent() ) {
			foreach ( $headers as $key => $value ) {
				header( $key . ': ' . $value );
			}
			status_header( $result->get_status() );
		}

		echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- raw Turtle body, correct Content-Type set.
		return true;
	}

	/**
	 * GET /datasets/<id> — returns a single dcat:Dataset JSON-LD response.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return WP_REST_Response|WP_Error Response on success, WP_Error on failure.
	 */
	public static function get_dataset( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$format  = self::resolve_format( $request );
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
			// Bewusst identisch zum Nicht-Existieren (404 + gleiche Meldung),
			// damit Entwurfs-IDs nicht von außen enumerierbar sind.
			return new WP_Error(
				'odw_not_found',
				__( 'Datensatz nicht gefunden.', 'open-data-wizard' ),
				array( 'status' => 404 )
			);
		}

		$cache_key = 'odw_dataset_' . $post_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return self::document_response( $cached, $format, 'HIT', $cache_key );
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

		set_transient( $cache_key, $body, self::get_cache_ttl() );

		return self::document_response( $body, $format, 'MISS', $cache_key );
	}

	/**
	 * GET /delta — returns datasets modified or removed since a given ISO 8601 timestamp.
	 *
	 * Response body keys:
	 *  - dcat:dataset  — array of full JSON-LD dataset objects modified after `since`
	 *  - odw:removed   — array of tombstone objects for datasets trashed after `since`
	 *
	 * Pagination (page/per_page) applies only to the modified set; all tombstones for the
	 * requested window are always included in full so harvesters don't miss deletions.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return WP_REST_Response|WP_Error Response on success, WP_Error on invalid input.
	 */
	public static function get_delta( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$format   = self::resolve_format( $request );
		$since    = (string) $request->get_param( 'since' );
		$page     = (int) $request->get_param( 'page' );
		$per_page = (int) $request->get_param( 'per_page' );

		$since_dt = self::parse_iso8601( $since );
		if ( null === $since_dt ) {
			return new WP_Error(
				'odw_invalid_since',
				__( 'Ungültiges Datumsformat für "since". Bitte ISO 8601 verwenden (z. B. 2024-01-01T00:00:00Z).', 'open-data-wizard' ),
				array( 'status' => 400 )
			);
		}

		// Den Cache-Schlüssel aus dem NORMALISIERTEN Zeitstempel bilden, nicht aus der
		// rohen Eingabe: Sonst erzeugt jede Schreibweise desselben Zeitpunkts
		// ("2024-01-01", "2024-01-01T00:00:00Z", "…+00:00") einen eigenen Transient.
		// Der Endpoint ist unauthentifiziert — so bleibt der Schlüsselraum auf
		// tatsächlich verschiedene Zeitpunkte begrenzt statt auf beliebige Varianten.
		$since_canonical = $since_dt->format( DATE_ATOM );
		$cache_key       = 'odw_delta_' . md5( serialize( array( $since_canonical, $page, $per_page ) ) );
		$cached          = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return self::document_response(
				$cached['body'],
				$format,
				'HIT',
				$cache_key,
				array(
					'X-WP-Total'         => (string) $cached['total'],
					'X-WP-TotalPages'    => (string) $cached['pages'],
					'X-ODW-Delta-Since'  => $since,
					'X-ODW-Generated-At' => (string) $cached['generated_at'],
				)
			);
		}

		// Compare against UTC-stored post_modified_gmt to avoid timezone drift.
		$after_gmt = $since_dt->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );

		$modified_query = new WP_Query(
			array(
				'post_type'      => 'odw_dataset',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'no_found_rows'  => false,
				'date_query'     => array(
					array(
						'column' => 'post_modified_gmt',
						'after'  => $after_gmt,
					),
				),
			)
		);

		// Tombstones: all trashed datasets in the window (never paginated so harvesters get them all).
		$removed_query = new WP_Query(
			array(
				'post_type'      => 'odw_dataset',
				'post_status'    => 'trash',
				'posts_per_page' => -1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'no_found_rows'  => true,
				'date_query'     => array(
					array(
						'column' => 'post_modified_gmt',
						'after'  => $after_gmt,
					),
				),
			)
		);

		$datasets = array();
		foreach ( $modified_query->posts as $post ) {
			$jsonld = odw_build_dataset_jsonld( (int) $post->ID );
			if ( $jsonld ) {
				$datasets[] = $jsonld;
			}
		}

		$removed = array();
		foreach ( $removed_query->posts as $post ) {
			$removed[] = array(
				'@id'           => rest_url( self::NAMESPACE . '/datasets/' . $post->ID ),
				'@type'         => 'dcat:Dataset',
				'odw:removedAt' => gmdate( 'c', strtotime( $post->post_modified_gmt ) ),
			);
		}

		$total        = (int) $modified_query->found_posts;
		$pages        = (int) $modified_query->max_num_pages;
		$generated_at = gmdate( 'c' );

		$body = array(
			'@context'          => self::JSONLD_CONTEXT,
			'@type'             => 'odw:DeltaCatalog',
			'dct:issued'        => $generated_at,
			'odw:since'         => $since,
			'odw:totalModified' => $total,
			'odw:totalRemoved'  => count( $removed ),
			'dcat:dataset'      => $datasets,
			'odw:removed'       => $removed,
		);

		// Only cache deltas that actually carry changes. An arbitrary `since`
		// value usually yields an empty delta; caching those would let
		// unauthenticated callers create unbounded distinct transients.
		if ( ! empty( $datasets ) || ! empty( $removed ) ) {
			set_transient(
				$cache_key,
				array(
					'body'         => $body,
					'total'        => $total,
					'pages'        => $pages,
					'generated_at' => $generated_at,
				),
				self::get_cache_ttl()
			);
		}

		return self::document_response(
			$body,
			$format,
			'MISS',
			$cache_key,
			array(
				'X-WP-Total'         => (string) $total,
				'X-WP-TotalPages'    => (string) $pages,
				'X-ODW-Delta-Since'  => $since,
				'X-ODW-Generated-At' => $generated_at,
			)
		);
	}

	/**
	 * Validates the `since` query parameter as an ISO 8601 date or datetime string.
	 *
	 * Accepted formats: YYYY-MM-DD, YYYY-MM-DDTHH:MM:SS, YYYY-MM-DDTHH:MM:SSZ,
	 * and YYYY-MM-DDTHH:MM:SS+HH:MM offset notation.
	 *
	 * @param mixed $value Raw parameter value from the request.
	 * @return bool True when the value is a recognised ISO 8601 string.
	 */
	public static function validate_since_param( mixed $value ): bool {
		return null !== self::parse_iso8601( (string) $value );
	}

	/**
	 * Parses an ISO 8601 date/datetime string into a DateTimeImmutable (UTC).
	 *
	 * @param string $value ISO 8601 string to parse.
	 * @return DateTimeImmutable|null Parsed datetime in UTC, or null on failure.
	 */
	private static function parse_iso8601( string $value ): ?DateTimeImmutable {
		$utc = new DateTimeZone( 'UTC' );

		// Formats tried in descending specificity. Das führende '!' setzt alle
		// nicht angegebenen Teile auf 0 — ohne '!' würde bei 'Y-m-d' die AKTUELLE
		// Uhrzeit übernommen und same-day-Änderungen im Delta verschluckt.
		$formats = array(
			'!Y-m-d\TH:i:sP',  // Numeric timezone offset notation.
			'!Y-m-d\TH:i:s\Z', // UTC Z suffix.
			'!Y-m-d\TH:i:s',   // No timezone (assumed UTC).
			'!Y-m-d',           // Date only, start of day UTC.
		);

		foreach ( $formats as $format ) {
			$dt = DateTimeImmutable::createFromFormat( $format, $value, $utc );

			// createFromFormat() silently normalises overflow values (e.g. month 13
			// or day 45), so reject any input that produced warnings or errors.
			$last_errors = DateTimeImmutable::getLastErrors();
			$has_problem = is_array( $last_errors )
				&& ( $last_errors['warning_count'] > 0 || $last_errors['error_count'] > 0 );

			if ( false !== $dt && ! $has_problem ) {
				return $dt->setTimezone( $utc );
			}
		}

		return null;
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
	 * Deletes all odw_catalog_* and odw_delta_* transients via a direct DB query.
	 *
	 * Pattern-based transient deletion has no WP API, so a direct query is required.
	 */
	private static function delete_catalog_transients(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
				'_transient_odw_catalog_%',
				'_transient_timeout_odw_catalog_%',
				'_transient_odw_delta_%',
				'_transient_timeout_odw_delta_%'
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
