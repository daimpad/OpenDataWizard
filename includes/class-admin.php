<?php
/**
 * Admin: Listenansicht, Spalten, Assets
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
        add_action( 'restrict_manage_posts', [ self::class, 'status_filter_dropdown' ] );
        add_filter( 'parse_query', [ self::class, 'apply_status_filter' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
    }

    /**
     * Define list table columns.
     */
    public static function set_columns( array $columns ): array {
        $new_columns = [];

        // Keep checkbox and title.
        $new_columns['cb']           = $columns['cb'] ?? '<input type="checkbox">';
        $new_columns['title']        = __( 'Titel', 'open-data-wizard' );
        $new_columns['odw_license']  = __( 'Lizenz', 'open-data-wizard' );
        $new_columns['odw_theme']    = __( 'Thema', 'open-data-wizard' );
        $new_columns['odw_status']   = __( 'Status', 'open-data-wizard' );
        $new_columns['odw_modified'] = __( 'Änderungsdatum', 'open-data-wizard' );

        return $new_columns;
    }

    /**
     * Render custom column content.
     */
    public static function render_column( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'odw_license':
                $license = carbon_get_post_meta( $post_id, 'odw_license' );
                echo esc_html( self::license_label( (string) $license ) );
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

            case 'odw_modified':
                $modified = get_post_meta( $post_id, '_odw_modified', true );
                echo esc_html( $modified ?: '—' );
                break;
        }
    }

    /**
     * Define sortable columns.
     */
    public static function sortable_columns( array $columns ): array {
        $columns['odw_modified'] = 'modified';
        $columns['odw_theme']    = 'odw_theme';
        return $columns;
    }

    /**
     * Status filter dropdown above list table.
     */
    public static function status_filter_dropdown(): void {
        global $typenow;

        if ( 'odw_dataset' !== $typenow ) {
            return;
        }

        $selected = sanitize_text_field( $_GET['odw_status_filter'] ?? '' );

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

        if ( ! is_admin() || 'edit.php' !== $pagenow || 'odw_dataset' !== $typenow ) {
            return;
        }

        if ( ! $query->is_main_query() ) {
            return;
        }

        $filter = sanitize_text_field( $_GET['odw_status_filter'] ?? '' );

        if ( in_array( $filter, [ 'publish', 'draft' ], true ) ) {
            $query->set( 'post_status', $filter );
        } else {
            // Show both draft and published in "all" view.
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

        // Tab JS only needed on single post edit screen.
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
     * Translate license URI to human-readable label.
     */
    private static function license_label( string $uri ): string {
        $labels = [
            'https://creativecommons.org/publicdomain/zero/1.0/' => 'CC0 1.0',
            'https://creativecommons.org/licenses/by/4.0/'       => 'CC-BY 4.0',
            'https://creativecommons.org/licenses/by-sa/4.0/'    => 'CC-BY-SA 4.0',
            'https://www.govdata.de/dl-de/by-2-0'                => 'DL-DE BY 2.0',
        ];

        return $labels[ $uri ] ?? $uri;
    }
}
