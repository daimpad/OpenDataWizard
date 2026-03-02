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

class ODW_Rest_API {

    private const NAMESPACE = 'datenatlas/v1';

    /**
     * DCAT-AP 3.0 JSON-LD @context
     */
    private const JSONLD_CONTEXT = [
        'dcat' => 'https://www.w3.org/ns/dcat#',
        'dct'  => 'http://purl.org/dc/terms/',
        'foaf' => 'http://xmlns.com/foaf/0.1/',
        'xsd'  => 'http://www.w3.org/2001/XMLSchema#',
    ];

    public static function init(): void {
        add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
    }

    public static function register_routes(): void {
        register_rest_route(
            self::NAMESPACE,
            '/catalog',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ self::class, 'get_catalog' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'page'     => [
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => fn( $v ) => is_numeric( $v ) && $v >= 1,
                    ],
                    'per_page' => [
                        'default'           => 20,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => fn( $v ) => is_numeric( $v ) && $v >= 1 && $v <= 100,
                    ],
                    'theme'    => [
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'license'  => [
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/datasets/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ self::class, 'get_dataset' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => fn( $v ) => is_numeric( $v ) && $v > 0,
                    ],
                ],
            ]
        );
    }

    /**
     * GET /catalog
     */
    public static function get_catalog( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $page     = (int) $request->get_param( 'page' );
        $per_page = (int) $request->get_param( 'per_page' );
        $theme    = (string) $request->get_param( 'theme' );
        $license  = (string) $request->get_param( 'license' );

        $query_args = [
            'post_type'      => 'odw_dataset',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'no_found_rows'  => false,
        ];

        // Apply filters via meta queries.
        $meta_query = [];

        if ( ! empty( $theme ) ) {
            $meta_query[] = [
                'key'   => '_odw_theme',
                'value' => $theme,
            ];
        }

        if ( ! empty( $license ) ) {
            // Support alias shorthand, e.g. "cc-by" → "cc-by 4.0" → full URL
            $license_map = self::get_license_alias_map();
            $license_url = $license_map[ strtolower( $license ) ] ?? $license;

            $meta_query[] = [
                'key'   => '_odw_license',
                'value' => $license_url,
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        $query   = new WP_Query( $query_args );
        $posts   = $query->posts;
        $total   = (int) $query->found_posts;
        $pages   = (int) $query->max_num_pages;

        $datasets = [];
        foreach ( $posts as $post ) {
            $jsonld = odw_build_dataset_jsonld( (int) $post->ID );
            if ( $jsonld ) {
                $datasets[] = $jsonld;
            }
        }

        $catalog = [
            '@context'      => self::JSONLD_CONTEXT,
            '@type'         => 'dcat:Catalog',
            'dct:title'     => get_bloginfo( 'name' ) . ' — Datenkatalog',
            'dct:publisher' => [
                '@type'    => 'foaf:Organization',
                'foaf:name' => get_bloginfo( 'name' ),
            ],
            'dcat:dataset'  => $datasets,
        ];

        $response = new WP_REST_Response( $catalog, 200 );
        $response->header( 'Content-Type', 'application/ld+json; charset=UTF-8' );
        $response->header( 'X-WP-Total', (string) $total );
        $response->header( 'X-WP-TotalPages', (string) $pages );

        return $response;
    }

    /**
     * GET /datasets/<id>
     */
    public static function get_dataset( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $post_id = (int) $request->get_param( 'id' );
        $post    = get_post( $post_id );

        if ( ! $post || 'odw_dataset' !== $post->post_type ) {
            return new WP_Error(
                'odw_not_found',
                __( 'Datensatz nicht gefunden.', 'open-data-wizard' ),
                [ 'status' => 404 ]
            );
        }

        if ( 'publish' !== $post->post_status ) {
            return new WP_Error(
                'odw_not_published',
                __( 'Dieser Datensatz ist nicht veröffentlicht.', 'open-data-wizard' ),
                [ 'status' => 403 ]
            );
        }

        $dataset = odw_build_dataset_jsonld( $post_id );

        if ( ! $dataset ) {
            return new WP_Error(
                'odw_build_failed',
                __( 'Datensatz konnte nicht gebaut werden.', 'open-data-wizard' ),
                [ 'status' => 500 ]
            );
        }

        $body = array_merge(
            [ '@context' => self::JSONLD_CONTEXT ],
            $dataset
        );

        $response = new WP_REST_Response( $body, 200 );
        $response->header( 'Content-Type', 'application/ld+json; charset=UTF-8' );

        return $response;
    }

    /**
     * Shorthand alias map for ?license= filter.
     * Maps lowercase aliases to full license URIs.
     */
    private static function get_license_alias_map(): array {
        return [
            'cc0'           => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'cc0-1.0'       => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'cc-by'         => 'https://creativecommons.org/licenses/by/4.0/',
            'cc-by-4.0'     => 'https://creativecommons.org/licenses/by/4.0/',
            'cc-by-sa'      => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'cc-by-sa-4.0'  => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'dl-de-by-2.0'  => 'https://www.govdata.de/dl-de/by-2-0',
        ];
    }
}
