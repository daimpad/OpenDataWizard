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

class ODW_Fields {

    public static function init(): void {
        add_action( 'carbon_fields_register_fields', [ self::class, 'register' ] );
        add_action( 'save_post_odw_dataset', [ self::class, 'set_modified_date' ], 10, 2 );
    }

    public static function register(): void {
        self::register_required_fields();
        self::register_optional_fields();
        self::register_distributions();
    }

    private static function register_required_fields(): void {
        Container::make( 'post_meta', __( 'Pflichtangaben', 'open-data-wizard' ) )
            ->where( 'post_type', '=', 'odw_dataset' )
            ->set_priority( 'high' )
            ->add_tab(
                __( '1 — Pflichtangaben', 'open-data-wizard' ),
                [
                    Field::make( 'html', 'odw_description_tab1_hint' )
                        ->set_html( '<p class="description">' . esc_html__( 'Pflichtfelder gemäß DCAT-AP 3.0. Ohne diese Angaben kann der Datensatz nicht veröffentlicht werden.', 'open-data-wizard' ) . '</p>' ),

                    Field::make( 'text', 'odw_publisher', __( 'Herausgebende Organisation (dct:publisher)', 'open-data-wizard' ) )
                        ->set_required( true )
                        ->set_attribute( 'placeholder', __( 'z.B. Musterorganisation e.V.', 'open-data-wizard' ) ),

                    Field::make( 'textarea', 'odw_description', __( 'Beschreibung (dct:description)', 'open-data-wizard' ) )
                        ->set_required( true )
                        ->set_rows( 5 )
                        ->set_attribute( 'placeholder', __( 'Kurze Beschreibung des Datensatzes…', 'open-data-wizard' ) ),

                    Field::make( 'select', 'odw_license', __( 'Lizenz (dct:license)', 'open-data-wizard' ) )
                        ->set_required( true )
                        ->add_options( self::get_license_options() ),
                ]
            )
            ->add_tab(
                __( '2 — Optionale Angaben', 'open-data-wizard' ),
                [
                    Field::make( 'select', 'odw_language', __( 'Sprache (dct:language)', 'open-data-wizard' ) )
                        ->add_options( [
                            ''   => __( '— Bitte wählen —', 'open-data-wizard' ),
                            'de' => __( 'Deutsch (DE)', 'open-data-wizard' ),
                            'en' => __( 'Englisch (EN)', 'open-data-wizard' ),
                        ] ),

                    Field::make( 'textarea', 'odw_keywords', __( 'Schlagworte (dcat:keyword)', 'open-data-wizard' ) )
                        ->set_rows( 3 )
                        ->set_attribute( 'placeholder', __( 'z.B. Umwelt', 'open-data-wizard' ) )
                        ->set_help_text( __( 'Geben Sie jedes Schlagwort in einer eigenen Zeile ein.', 'open-data-wizard' ) ),

                    Field::make( 'select', 'odw_theme', __( 'Thema (dcat:theme)', 'open-data-wizard' ) )
                        ->add_options( self::get_theme_options() ),

                    Field::make( 'date', 'odw_issued', __( 'Veröffentlichungsdatum (dct:issued)', 'open-data-wizard' ) )
                        ->set_storage_format( 'Y-m-d' )
                        ->set_picker_options( [ 'dateFormat' => 'Y-m-d' ] ),

                    Field::make( 'date', 'odw_modified', __( 'Änderungsdatum (dct:modified)', 'open-data-wizard' ) )
                        ->set_storage_format( 'Y-m-d' )
                        ->set_picker_options( [ 'dateFormat' => 'Y-m-d' ] )
                        ->set_help_text( __( 'Wird automatisch bei jeder Speicherung aktualisiert.', 'open-data-wizard' ) ),
                ]
            )
            ->add_tab(
                __( '3 — Distribution', 'open-data-wizard' ),
                [
                    Field::make( 'complex', 'odw_distributions', __( 'Distributionen (dcat:distribution)', 'open-data-wizard' ) )
                        ->set_min( 1 )
                        ->set_collapsed( false )
                        ->add_fields( [
                            Field::make( 'text', 'access_url', __( 'Zugriffs-URL (dcat:accessURL)', 'open-data-wizard' ) )
                                ->set_required( true )
                                ->set_attribute( 'placeholder', 'https://beispiel.de/daten/datei.csv' )
                                ->set_attribute( 'type', 'url' ),

                            Field::make( 'select', 'format', __( 'Format (dct:format)', 'open-data-wizard' ) )
                                ->add_options( self::get_format_options() ),

                            Field::make( 'text', 'byte_size', __( 'Dateigröße in Bytes (dcat:byteSize)', 'open-data-wizard' ) )
                                ->set_attribute( 'placeholder', __( 'optional, z.B. 204800', 'open-data-wizard' ) )
                                ->set_attribute( 'type', 'number' ),
                        ] ),
                ]
            )
            ->add_tab(
                __( '4 — Vorschau', 'open-data-wizard' ),
                [
                    Field::make( 'html', 'odw_preview_html' )
                        ->set_html( self::get_preview_html() ),
                ]
            );
    }

    private static function register_optional_fields(): void {
        // Fields are bundled in the tabbed container above.
        // This method is kept for structural clarity.
    }

    private static function register_distributions(): void {
        // Distributions are part of the tabbed container above.
    }

    /**
     * Auto-update odw_modified on every save.
     */
    public static function set_modified_date( int $post_id, \WP_Post $post ): void {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( 'odw_dataset' !== $post->post_type ) {
            return;
        }

        // Update without triggering infinite loop
        remove_action( 'save_post_odw_dataset', [ self::class, 'set_modified_date' ], 10 );

        update_post_meta( $post_id, '_odw_modified', gmdate( 'Y-m-d' ) );

        add_action( 'save_post_odw_dataset', [ self::class, 'set_modified_date' ], 10, 2 );
    }

    // -------------------------------------------------------------------------
    // Controlled vocabulary options
    // -------------------------------------------------------------------------

    public static function get_license_options(): array {
        return [
            ''                                                  => __( '— Bitte wählen —', 'open-data-wizard' ),
            'https://creativecommons.org/publicdomain/zero/1.0/' => 'CC0 1.0',
            'https://creativecommons.org/licenses/by/4.0/'       => 'CC-BY 4.0',
            'https://creativecommons.org/licenses/by-sa/4.0/'    => 'CC-BY-SA 4.0',
            'https://www.govdata.de/dl-de/by-2-0'                => 'Datenlizenz Deutschland Namensnennung 2.0',
        ];
    }

    public static function get_theme_options(): array {
        return [
            ''          => __( '— Bitte wählen —', 'open-data-wizard' ),
            'Bildung'   => __( 'Bildung', 'open-data-wizard' ),
            'Gesundheit' => __( 'Gesundheit', 'open-data-wizard' ),
            'Soziales'  => __( 'Soziales', 'open-data-wizard' ),
            'Umwelt'    => __( 'Umwelt', 'open-data-wizard' ),
            'Wirtschaft' => __( 'Wirtschaft', 'open-data-wizard' ),
            'Kultur'    => __( 'Kultur', 'open-data-wizard' ),
            'Sport'     => __( 'Sport', 'open-data-wizard' ),
            'Sonstiges' => __( 'Sonstiges', 'open-data-wizard' ),
        ];
    }

    public static function get_format_options(): array {
        return [
            ''        => __( '— Bitte wählen —', 'open-data-wizard' ),
            'CSV'     => 'CSV',
            'JSON'    => 'JSON',
            'XLSX'    => 'XLSX',
            'PDF'     => 'PDF',
            'GeoJSON' => 'GeoJSON',
            'XML'     => 'XML',
            'Sonstiges' => __( 'Sonstiges', 'open-data-wizard' ),
        ];
    }

    /**
     * Format MIME-type mapping for JSON-LD output.
     */
    public static function get_format_mime( string $format ): string {
        $map = [
            'CSV'     => 'text/csv',
            'JSON'    => 'application/json',
            'XLSX'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'PDF'     => 'application/pdf',
            'GeoJSON' => 'application/geo+json',
            'XML'     => 'application/xml',
        ];

        return $map[ $format ] ?? $format;
    }

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

/**
 * Build DCAT-AP 3.0 JSON-LD array for a single dataset.
 * Used by both the REST API and the preview tab.
 */
function odw_build_dataset_jsonld( int $post_id ): ?array {
    $post = get_post( $post_id );

    if ( ! $post || 'odw_dataset' !== $post->post_type ) {
        return null;
    }

    $title       = $post->post_title;
    $description = carbon_get_post_meta( $post_id, 'odw_description' );
    $publisher   = carbon_get_post_meta( $post_id, 'odw_publisher' );
    $license     = carbon_get_post_meta( $post_id, 'odw_license' );
    $language    = carbon_get_post_meta( $post_id, 'odw_language' );
    $keywords    = carbon_get_post_meta( $post_id, 'odw_keywords' );
    $theme       = carbon_get_post_meta( $post_id, 'odw_theme' );
    $issued      = carbon_get_post_meta( $post_id, 'odw_issued' );
    $modified    = get_post_meta( $post_id, '_odw_modified', true );
    $distributions = carbon_get_post_meta( $post_id, 'odw_distributions' );

    $dataset = [
        '@type'            => 'dcat:Dataset',
        '@id'              => rest_url( 'datenatlas/v1/datasets/' . $post_id ),
        'dct:title'        => $title,
        'dct:description'  => $description,
        'dct:publisher'    => [
            '@type'       => 'foaf:Organization',
            'foaf:name'   => $publisher,
        ],
        'dct:license'      => $license,
    ];

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
        $dataset['dct:issued'] = [
            '@type'  => 'xsd:date',
            '@value' => $issued,
        ];
    }

    if ( ! empty( $modified ) ) {
        $dataset['dct:modified'] = [
            '@type'  => 'xsd:date',
            '@value' => $modified,
        ];
    }

    if ( ! empty( $distributions ) && is_array( $distributions ) ) {
        $dist_list = [];
        foreach ( $distributions as $dist ) {
            if ( empty( $dist['access_url'] ) ) {
                continue;
            }

            $dist_item = [
                '@type'          => 'dcat:Distribution',
                'dcat:accessURL' => $dist['access_url'],
            ];

            if ( ! empty( $dist['format'] ) ) {
                $dist_item['dct:format'] = ODW_Fields::get_format_mime( $dist['format'] );
            }

            if ( isset( $dist['byte_size'] ) && '' !== $dist['byte_size'] ) {
                $dist_item['dcat:byteSize'] = (int) $dist['byte_size'];
            }

            $dist_list[] = $dist_item;
        }

        if ( ! empty( $dist_list ) ) {
            $dataset['dcat:distribution'] = $dist_list;
        }
    }

    return $dataset;
}
