<?php
/**
 * Settings-Seite für Open Data Wizard
 *
 * Untermenü unter "Datensätze" → "Einstellungen".
 * Einstellungen werden als Array in der Option `odw_settings` gespeichert.
 *
 * Verfügbare Einstellungen:
 *   catalog_title       — Titel des Datenkatalogs (REST API dct:title)
 *   default_publisher   — Vorausgefüllter Herausgeber für neue Datensätze
 *   default_license     — Vorausgewählte Lizenz für neue Datensätze
 *   default_language    — Vorausgewählte Sprache für neue Datensätze
 *   cache_ttl           — Transient-TTL für REST-API-Cache in Sekunden
 *   delete_on_uninstall — Alle Plugin-Daten bei Deinstallation löschen
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ODW_Settings {

    public const OPTION_KEY = 'odw_settings';

    public static function init(): void {
        add_action( 'admin_menu',  [ self::class, 'add_submenu_page' ] );
        add_action( 'admin_init',  [ self::class, 'register_settings' ] );
        add_action( 'admin_post_odw_recalculate_quality', [ self::class, 'handle_recalculate_quality' ] );
        add_filter( 'odw_catalog_title', [ self::class, 'filter_catalog_title' ] );
    }

    // -------------------------------------------------------------------------
    // Menü & Seite
    // -------------------------------------------------------------------------

    public static function add_submenu_page(): void {
        add_submenu_page(
            'edit.php?post_type=odw_dataset',
            __( 'Open Data Wizard — Einstellungen', 'open-data-wizard' ),
            __( 'Einstellungen', 'open-data-wizard' ),
            'manage_options',
            'odw-settings',
            [ self::class, 'render_page' ]
        );
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $recalculated = isset( $_GET['odw_recalculated'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['odw_recalculated'] ) ) : null;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Open Data Wizard — Einstellungen', 'open-data-wizard' ); ?></h1>

            <?php if ( null !== $recalculated ) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %d: number of recalculated datasets */
                        esc_html__( 'Qualitätsscores neu berechnet: %d Datensätze aktualisiert.', 'open-data-wizard' ),
                        $recalculated
                    );
                    ?>
                </p>
            </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'odw_settings_group' );
                do_settings_sections( 'odw-settings' );
                submit_button( __( 'Einstellungen speichern', 'open-data-wizard' ) );
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Datenverwaltung', 'open-data-wizard' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Qualitätsscores aller vorhandenen Datensätze neu berechnen — z.B. nach Änderungen an der Scoring-Logik.', 'open-data-wizard' ); ?>
            </p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="odw_recalculate_quality">
                <?php wp_nonce_field( 'odw_recalculate_quality' ); ?>
                <?php submit_button( __( 'Alle Qualitätsscores neu berechnen', 'open-data-wizard' ), 'secondary', 'submit', false ); ?>
            </form>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Settings API Registrierung
    // -------------------------------------------------------------------------

    public static function register_settings(): void {
        register_setting(
            'odw_settings_group',
            self::OPTION_KEY,
            [
                'sanitize_callback' => [ self::class, 'sanitize' ],
                'default'           => self::get_defaults(),
            ]
        );

        // --- Katalog ---
        add_settings_section(
            'odw_section_catalog',
            __( 'Katalog', 'open-data-wizard' ),
            static function (): void {
                echo '<p class="description">' . esc_html__( 'Metadaten des Datenkatalogs — erscheinen in der REST-API-Antwort von /wp-json/datenatlas/v1/catalog.', 'open-data-wizard' ) . '</p>';
            },
            'odw-settings'
        );

        add_settings_field( 'catalog_title',     __( 'Katalog-Titel', 'open-data-wizard' ),            [ self::class, 'field_catalog_title' ],     'odw-settings', 'odw_section_catalog' );
        add_settings_field( 'default_publisher', __( 'Herausgebende Organisation', 'open-data-wizard' ), [ self::class, 'field_default_publisher' ], 'odw-settings', 'odw_section_catalog' );

        // --- Standardwerte ---
        add_settings_section(
            'odw_section_defaults',
            __( 'Standardwerte für neue Datensätze', 'open-data-wizard' ),
            static function (): void {
                echo '<p class="description">' . esc_html__( 'Diese Werte werden beim Anlegen eines neuen Datensatzes automatisch vorausgefüllt.', 'open-data-wizard' ) . '</p>';
            },
            'odw-settings'
        );

        add_settings_field( 'default_license',  __( 'Standard-Lizenz', 'open-data-wizard' ),  [ self::class, 'field_default_license' ],  'odw-settings', 'odw_section_defaults' );
        add_settings_field( 'default_language', __( 'Standard-Sprache', 'open-data-wizard' ), [ self::class, 'field_default_language' ], 'odw-settings', 'odw_section_defaults' );

        // --- API ---
        add_settings_section(
            'odw_section_api',
            __( 'REST API', 'open-data-wizard' ),
            null,
            'odw-settings'
        );

        add_settings_field( 'cache_ttl', __( 'Cache-Laufzeit (Sekunden)', 'open-data-wizard' ), [ self::class, 'field_cache_ttl' ], 'odw-settings', 'odw_section_api' );

        // --- Deinstallation ---
        add_settings_section(
            'odw_section_uninstall',
            __( 'Deinstallation', 'open-data-wizard' ),
            null,
            'odw-settings'
        );

        add_settings_field( 'delete_on_uninstall', __( 'Daten löschen', 'open-data-wizard' ), [ self::class, 'field_delete_on_uninstall' ], 'odw-settings', 'odw_section_uninstall' );
    }

    // -------------------------------------------------------------------------
    // Feld-Callbacks
    // -------------------------------------------------------------------------

    public static function field_catalog_title(): void {
        $value = self::get( 'catalog_title' );
        $placeholder = get_bloginfo( 'name' ) . ' — Datenkatalog';
        ?>
        <input
            type="text"
            name="<?php echo esc_attr( self::OPTION_KEY . '[catalog_title]' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            placeholder="<?php echo esc_attr( $placeholder ); ?>"
            class="regular-text"
        >
        <p class="description"><?php esc_html_e( 'Leer lassen für den automatischen Wert (Seitenname + „ — Datenkatalog").', 'open-data-wizard' ); ?></p>
        <?php
    }

    public static function field_default_publisher(): void {
        $value = self::get( 'default_publisher' );
        ?>
        <input
            type="text"
            name="<?php echo esc_attr( self::OPTION_KEY . '[default_publisher]' ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            placeholder="<?php echo esc_attr__( 'z.B. Musterorganisation e.V.', 'open-data-wizard' ); ?>"
            class="regular-text"
        >
        <p class="description"><?php esc_html_e( 'Wird im Feld „Herausgebende Organisation" neuer Datensätze vorausgefüllt.', 'open-data-wizard' ); ?></p>
        <?php
    }

    public static function field_default_license(): void {
        $current = self::get( 'default_license' );
        $options = ODW_Fields::get_license_options();
        ?>
        <select name="<?php echo esc_attr( self::OPTION_KEY . '[default_license]' ); ?>">
            <?php foreach ( $options as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>"<?php selected( $current, $value ); ?>>
                <?php echo esc_html( $label ); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e( 'Vorausgewählte Lizenz bei neuen Datensätzen.', 'open-data-wizard' ); ?></p>
        <?php
    }

    public static function field_default_language(): void {
        $current = self::get( 'default_language' );
        $options = [
            ''   => __( '— Kein Standard —', 'open-data-wizard' ),
            'de' => __( 'Deutsch (DE)', 'open-data-wizard' ),
            'en' => __( 'Englisch (EN)', 'open-data-wizard' ),
        ];
        ?>
        <select name="<?php echo esc_attr( self::OPTION_KEY . '[default_language]' ); ?>">
            <?php foreach ( $options as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>"<?php selected( $current, $value ); ?>>
                <?php echo esc_html( $label ); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function field_cache_ttl(): void {
        $value = (int) self::get( 'cache_ttl' );
        ?>
        <input
            type="number"
            name="<?php echo esc_attr( self::OPTION_KEY . '[cache_ttl]' ); ?>"
            value="<?php echo esc_attr( (string) $value ); ?>"
            min="60"
            max="86400"
            step="60"
            class="small-text"
        > <?php esc_html_e( 'Sekunden', 'open-data-wizard' ); ?>
        <p class="description">
            <?php esc_html_e( 'Wie lange Catalog- und Dataset-Antworten gecacht werden. Standard: 300 (5 Minuten). Min: 60, Max: 86400 (24 h).', 'open-data-wizard' ); ?>
        </p>
        <?php
    }

    public static function field_delete_on_uninstall(): void {
        $checked = (bool) self::get( 'delete_on_uninstall' );
        ?>
        <label>
            <input
                type="checkbox"
                name="<?php echo esc_attr( self::OPTION_KEY . '[delete_on_uninstall]' ); ?>"
                value="1"
                <?php checked( $checked ); ?>
            >
            <?php esc_html_e( 'Alle Datensätze, Metadaten und Plugin-Optionen beim Deinstallieren unwiderruflich löschen.', 'open-data-wizard' ); ?>
        </label>
        <?php
    }

    // -------------------------------------------------------------------------
    // Sanitierung
    // -------------------------------------------------------------------------

    public static function sanitize( array $input ): array {
        $defaults = self::get_defaults();

        $output = [];
        $output['catalog_title']       = sanitize_text_field( $input['catalog_title'] ?? '' );
        $output['default_publisher']   = sanitize_text_field( $input['default_publisher'] ?? '' );
        $output['default_license']     = sanitize_text_field( $input['default_license'] ?? '' );
        $output['default_language']    = sanitize_text_field( $input['default_language'] ?? '' );
        $output['delete_on_uninstall'] = ! empty( $input['delete_on_uninstall'] ) ? '1' : '0';

        $ttl = (int) ( $input['cache_ttl'] ?? $defaults['cache_ttl'] );
        $output['cache_ttl'] = max( 60, min( 86400, $ttl ) );

        // Cache nach Einstellungsänderung invalidieren.
        ODW_Rest_API::delete_catalog_transients_public();

        return $output;
    }

    // -------------------------------------------------------------------------
    // Aktionen
    // -------------------------------------------------------------------------

    /**
     * Berechnet Qualitätsscores aller veröffentlichten Datensätze neu.
     */
    public static function handle_recalculate_quality(): void {
        check_admin_referer( 'odw_recalculate_quality' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Keine Berechtigung.', 'open-data-wizard' ) );
        }

        $posts = get_posts( [
            'post_type'      => 'odw_dataset',
            'post_status'    => [ 'publish', 'draft' ],
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        $count = 0;
        foreach ( $posts as $post_id ) {
            ODW_Quality::store( (int) $post_id, ODW_Quality::calculate( (int) $post_id ) );
            $count++;
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page'             => 'odw-settings',
                    'odw_recalculated' => $count,
                ],
                admin_url( 'edit.php?post_type=odw_dataset' )
            )
        );
        exit;
    }

    // -------------------------------------------------------------------------
    // Filter
    // -------------------------------------------------------------------------

    public static function filter_catalog_title( string $default ): string {
        $custom = trim( self::get( 'catalog_title' ) );
        return '' !== $custom ? $custom : $default;
    }

    // -------------------------------------------------------------------------
    // Datenzugriff
    // -------------------------------------------------------------------------

    /**
     * Gibt alle Einstellungen oder einen einzelnen Wert zurück.
     *
     * @return mixed
     */
    public static function get( string $key = '' ) {
        $settings = (array) get_option( self::OPTION_KEY, [] );
        $settings = array_merge( self::get_defaults(), $settings );

        if ( '' === $key ) {
            return $settings;
        }

        return $settings[ $key ] ?? null;
    }

    private static function get_defaults(): array {
        return [
            'catalog_title'       => '',
            'default_publisher'   => '',
            'default_license'     => '',
            'default_language'    => '',
            'cache_ttl'           => 300,
            'delete_on_uninstall' => '0',
        ];
    }
}
