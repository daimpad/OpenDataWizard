<?php
/**
 * Plugin Name:       Open Data Wizard
 * Plugin URI:        https://github.com/daimpad/OpenDataWizard
 * Description:       DCAT-AP 3.0 konforme Open Data Metadatenverwaltung für zivilgesellschaftliche Organisationen. Bereitstellung als maschinenlesbarer Endpoint für Civora/Piveau-Harvesting.
 * Version:           1.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Datenatlas Zivilgesellschaft
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       open-data-wizard
 * Domain Path:       /languages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ODW_VERSION', '1.1.0' );
define( 'ODW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ODW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ODW_PLUGIN_FILE', __FILE__ );

// ---------------------------------------------------------------------------
// Activation / Deactivation
// ---------------------------------------------------------------------------

register_activation_hook( __FILE__, 'odw_activate' );
register_deactivation_hook( __FILE__, 'odw_deactivate' );

function odw_activate(): void {
    odw_register_cpt_static();
    flush_rewrite_rules();
    odw_add_capabilities();
}

function odw_deactivate(): void {
    flush_rewrite_rules();
}

/**
 * Grant manage_open_data capability to administrator and editor roles.
 * Called on activation; removed by uninstall.php.
 */
function odw_add_capabilities(): void {
    $roles = [ 'administrator', 'editor' ];
    foreach ( $roles as $role_name ) {
        $role = get_role( $role_name );
        if ( $role ) {
            $role->add_cap( 'manage_open_data' );
        }
    }
}

/**
 * Minimal CPT registration used during activation (before theme is set up).
 */
function odw_register_cpt_static(): void {
    register_post_type( 'odw_dataset', [
        'public'   => false,
        'supports' => [ 'title', 'revisions' ],
    ] );
}

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

/**
 * Bootstrap Carbon Fields and all plugin modules.
 */
function odw_bootstrap(): void {
    $autoloader = ODW_PLUGIN_DIR . 'vendor/autoload.php';

    if ( ! file_exists( $autoloader ) ) {
        add_action( 'admin_notices', function (): void {
            echo '<div class="notice notice-error"><p>';
            esc_html_e(
                'Open Data Wizard: Vendor-Abhängigkeiten fehlen. Bitte composer install im Plugin-Verzeichnis ausführen.',
                'open-data-wizard'
            );
            echo '</p></div>';
        } );
        return;
    }

    require_once $autoloader;

    try {
        \Carbon_Fields\Carbon_Fields::boot();
    } catch ( \Throwable $e ) {
        add_action( 'admin_notices', function () use ( $e ): void {
            echo '<div class="notice notice-error"><p>';
            printf(
                /* translators: %s: Error message */
                esc_html__( 'Open Data Wizard: Carbon Fields konnte nicht initialisiert werden — %s', 'open-data-wizard' ),
                esc_html( $e->getMessage() )
            );
            echo '</p></div>';
        } );
        return;
    }

    require_once ODW_PLUGIN_DIR . 'includes/class-post-types.php';
    require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
    require_once ODW_PLUGIN_DIR . 'includes/class-rest-api.php';
    require_once ODW_PLUGIN_DIR . 'includes/class-validation.php';
    require_once ODW_PLUGIN_DIR . 'includes/class-admin.php';

    ODW_Post_Types::init();
    ODW_Fields::init();
    ODW_Rest_API::init();
    ODW_Validation::init();
    ODW_Admin::init();
}
add_action( 'after_setup_theme', 'odw_bootstrap' );

/**
 * Load plugin textdomain.
 */
function odw_load_textdomain(): void {
    load_plugin_textdomain(
        'open-data-wizard',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}
add_action( 'init', 'odw_load_textdomain' );
