<?php
/**
 * Admin: Listenansicht, Spalten, Assets, Help Tabs
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ODW_Admin {

    public static function init(): void {
        add_filter( 'manage_odw_dataset_posts_columns', [ self::class, 'set_columns' ] );
        add_action( 'manage_odw_dataset_posts_custom_column', [ self::class, 'render_column' ], 10, 2 );
        add_filter( 'manage_edit-odw_dataset_sortable_columns', [ self::class, 'sortable_columns' ] );
        add_action( 'pre_get_posts', [ self::class, 'handle_meta_orderby' ] );
        add_action( 'restrict_manage_posts', [ self::class, 'status_filter_dropdown' ] );
        add_filter( 'parse_query', [ self::class, 'apply_status_filter' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
        add_action( 'add_meta_boxes', [ self::class, 'register_help_tabs' ] );
        add_action( 'load-post.php', [ self::class, 'register_help_tabs' ] );
        add_action( 'load-post-new.php', [ self::class, 'register_help_tabs' ] );
    }

    /**
     * Define list table columns.
     */
    public static function set_columns( array $columns ): array {
        $new_columns = [];

        $new_columns['cb']            = $columns['cb'] ?? '<input type="checkbox">';
        $new_columns['title']         = __( 'Titel', 'open-data-wizard' );
        $new_columns['odw_license']   = __( 'Lizenz', 'open-data-wizard' );
        $new_columns['odw_theme']     = __( 'Thema', 'open-data-wizard' );
        $new_columns['odw_quality']    = __( 'Qualität', 'open-data-wizard' );
        $new_columns['odw_status']    = __( 'Status', 'open-data-wizard' );
        $new_columns['odw_modified']  = __( 'Änderungsdatum', 'open-data-wizard' );
        $new_columns['odw_shortcode'] = __( 'Shortcode', 'open-data-wizard' );

        return $new_columns;
    }

    /**
     * Render custom column content.
     */
    public static function render_column( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'odw_license':
                $license = (string) carbon_get_post_meta( $post_id, 'odw_license' );
                echo esc_html( ODW_Fields::get_license_label( $license ) );
                break;

            case 'odw_theme':
                $theme = carbon_get_post_meta( $post_id, 'odw_theme' );
                echo esc_html( (string) $theme );
                break;

            case 'odw_status':
                $post   = get_post( $post_id );
                $status = $post ? $post->post_status : '';

                if ( 'publish' === $status ) {
                    echo '<span class="odw-status-badge odw-status-badge--published">' . esc_html__( 'Veröffentlicht', 'open-data-wizard' ) . '</span>';
                } else {
                    echo '<span class="odw-status-badge odw-status-badge--draft">' . esc_html__( 'Entwurf', 'open-data-wizard' ) . '</span>';
                }
                break;

            case 'odw_quality':
                $quality = ODW_Quality::get( $post_id );

                if ( '' === $quality['level'] ) {
                    echo '<span class="odw-quality-badge odw-quality--unknown" title="' . esc_attr__( 'Noch nicht berechnet', 'open-data-wizard' ) . '">—</span>';
                } else {
                    $level       = $quality['level'];
                    $score       = $quality['score'];
                    $label       = ODW_Quality::get_level_label( $level );
                    $title_attr  = sprintf( '%s · %d/100 %s', $label, $score, __( 'Punkte', 'open-data-wizard' ) );
                    printf(
                        '<span class="odw-quality-badge odw-quality--%s" title="%s"><span class="odw-quality-dot" aria-hidden="true">●</span> %d</span>',
                        esc_attr( $level ),
                        esc_attr( $title_attr ),
                        (int) $score
                    );
                }
                break;

            case 'odw_modified':
                $modified = get_post_meta( $post_id, '_odw_modified', true );
                echo esc_html( $modified ?: '—' );
                break;

            case 'odw_shortcode':
                $shortcode = '[odw_dataset id="' . $post_id . '"]';
                printf(
                    '<input type="text" class="odw-shortcode-input" readonly value="%s" onclick="this.select();" title="%s">',
                    esc_attr( $shortcode ),
                    esc_attr__( 'Klicken zum Markieren', 'open-data-wizard' )
                );
                break;
        }
    }

    /**
     * Define sortable columns.
     */
    public static function sortable_columns( array $columns ): array {
        $columns['odw_modified'] = 'modified';
        $columns['odw_theme']    = 'odw_theme';
        $columns['odw_quality']  = 'odw_quality';
        return $columns;
    }

    /**
     * Enable meta-based ordering for the Thema column.
     */
    public static function handle_meta_orderby( WP_Query $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( 'odw_dataset' !== $query->get( 'post_type' ) ) {
            return;
        }

        if ( 'odw_theme' === $query->get( 'orderby' ) ) {
            $query->set( 'meta_key', '_odw_theme' );
            $query->set( 'orderby', 'meta_value' );
        }

        if ( 'odw_quality' === $query->get( 'orderby' ) ) {
            $query->set( 'meta_key', '_odw_quality_score' );
            $query->set( 'orderby', 'meta_value_num' );
        }
    }

    /**
     * Status filter dropdown above list table.
     */
    public static function status_filter_dropdown(): void {
        global $typenow;

        if ( ! isset( $typenow ) || 'odw_dataset' !== $typenow ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $selected = isset( $_GET['odw_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['odw_status_filter'] ) ) : '';

        $options = [
            ''        => __( 'Alle Status', 'open-data-wizard' ),
            'publish' => __( 'Veröffentlicht', 'open-data-wizard' ),
            'draft'   => __( 'Entwurf', 'open-data-wizard' ),
        ];

        echo '<select name="odw_status_filter">';
        foreach ( $options as $value => $label ) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr( $value ),
                selected( $selected, $value, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }

    /**
     * Apply status filter to query.
     */
    public static function apply_status_filter( WP_Query $query ): void {
        global $pagenow, $typenow;

        if ( ! is_admin() || 'edit.php' !== $pagenow || ! isset( $typenow ) || 'odw_dataset' !== $typenow ) {
            return;
        }

        if ( ! $query->is_main_query() ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $filter = isset( $_GET['odw_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['odw_status_filter'] ) ) : '';

        if ( in_array( $filter, [ 'publish', 'draft' ], true ) ) {
            $query->set( 'post_status', $filter );
        } else {
            $query->set( 'post_status', [ 'publish', 'draft' ] );
        }
    }

    /**
     * Enqueue admin assets (only on odw_dataset screens).
     */
    public static function enqueue_assets( string $hook ): void {
        $screen = get_current_screen();

        if ( ! $screen || 'odw_dataset' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_style(
            'odw-admin',
            ODW_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ODW_VERSION
        );

        if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            wp_enqueue_script(
                'odw-wizard-tabs',
                ODW_PLUGIN_URL . 'assets/js/wizard-tabs.js',
                [],
                ODW_VERSION,
                true
            );
        }
    }

    /**
     * Register Help Tabs on the odw_dataset edit screen.
     */
    public static function register_help_tabs(): void {
        $screen = get_current_screen();

        if ( ! $screen || 'odw_dataset' !== $screen->post_type ) {
            return;
        }

        $screen->add_help_tab( [
            'id'      => 'odw-help-fields',
            'title'   => __( 'Felder', 'open-data-wizard' ),
            'content' => self::help_content_fields(),
        ] );

        $screen->add_help_tab( [
            'id'      => 'odw-help-api',
            'title'   => __( 'Harvest-Endpoint', 'open-data-wizard' ),
            'content' => self::help_content_api(),
        ] );

        $screen->set_help_sidebar(
            '<p><strong>' . esc_html__( 'Weitere Informationen:', 'open-data-wizard' ) . '</strong></p>' .
            '<p><a href="https://www.w3.org/TR/vocab-dcat-3/" target="_blank">DCAT-AP 3.0 Spezifikation</a></p>' .
            '<p><a href="https://github.com/daimpad/OpenDataWizard" target="_blank">Plugin-Dokumentation</a></p>'
        );
    }

    private static function help_content_fields(): string {
        ob_start();
        ?>
        <h3><?php esc_html_e( 'DCAT-AP 3.0 Pflichtfelder', 'open-data-wizard' ); ?></h3>
        <ul>
            <li><strong>dct:title</strong> — <?php esc_html_e( 'Titel des Datensatzes (WordPress-Titel-Feld)', 'open-data-wizard' ); ?></li>
            <li><strong>dct:description</strong> — <?php esc_html_e( 'Beschreibung des Datensatzes', 'open-data-wizard' ); ?></li>
            <li><strong>dct:publisher</strong> — <?php esc_html_e( 'Name der herausgebenden Organisation', 'open-data-wizard' ); ?></li>
            <li><strong>dct:license</strong> — <?php esc_html_e( 'Lizenz aus dem kontrollierten Vokabular', 'open-data-wizard' ); ?></li>
        </ul>
        <h3><?php esc_html_e( 'Distribution', 'open-data-wizard' ); ?></h3>
        <p><?php esc_html_e( 'Jeder Datensatz benötigt mindestens eine Distribution mit einer Zugriffs-URL (dcat:accessURL). Mehrere Distributionen (z.B. CSV + JSON) können hinzugefügt werden.', 'open-data-wizard' ); ?></p>
        <h3><?php esc_html_e( 'Erweiterte Angaben', 'open-data-wizard' ); ?></h3>
        <p><?php esc_html_e( 'Tab 4 enthält optionale DCAT-AP Felder: Projektseite (dcat:landingPage), Aktualisierungsfrequenz (dct:accrualPeriodicity), geographische und zeitliche Abdeckung sowie einen Kontaktpunkt (dcat:contactPoint).', 'open-data-wizard' ); ?></p>
        <h3><?php esc_html_e( 'Vorschau', 'open-data-wizard' ); ?></h3>
        <p><?php esc_html_e( 'Tab 5 zeigt das generierte JSON-LD nach dem Speichern. Dort finden Sie auch den direkten Link zum REST-Endpoint.', 'open-data-wizard' ); ?></p>
        <?php
        return ob_get_clean();
    }

    private static function help_content_api(): string {
        $catalog_url  = rest_url( 'datenatlas/v1/catalog' );
        $dataset_url  = rest_url( 'datenatlas/v1/datasets/{id}' );

        ob_start();
        ?>
        <h3><?php esc_html_e( 'Catalog-Endpoint (für Civora/Piveau)', 'open-data-wizard' ); ?></h3>
        <p><code><?php echo esc_html( $catalog_url ); ?></code></p>
        <p><?php esc_html_e( 'Liefert alle veröffentlichten Datensätze als dcat:Catalog (JSON-LD). Unterstützt:', 'open-data-wizard' ); ?></p>
        <ul>
            <li><code>?page=1&amp;per_page=20</code> — <?php esc_html_e( 'Paginierung', 'open-data-wizard' ); ?></li>
            <li><code>?theme=Bildung</code> — <?php esc_html_e( 'Filter nach Thema', 'open-data-wizard' ); ?></li>
            <li><code>?license=cc-by</code> — <?php esc_html_e( 'Filter nach Lizenz (Kurzform)', 'open-data-wizard' ); ?></li>
        </ul>
        <h3><?php esc_html_e( 'Einzelner Datensatz', 'open-data-wizard' ); ?></h3>
        <p><code><?php echo esc_html( $dataset_url ); ?></code></p>
        <?php
        return ob_get_clean();
    }
}
