<?php
/**
 * Plugin Name:       Open Data Wizard
 * Plugin URI:        https://github.com/daimpad/OpenDataWizard
 * Description:       DCAT-AP 3.0 konforme Open Data Metadatenverwaltung für zivilgesellschaftliche Organisationen. Bereitstellung als maschinenlesbarer Endpoint für Civora/Piveau-Harvesting.
 * Version:           1.0.0
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

define( 'ODW_VERSION', '1.0.0' );
define( 'ODW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ODW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ODW_PLUGIN_FILE', __FILE__ );

/**
 * Bootstrap Carbon Fields.
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

    \Carbon_Fields\Carbon_Fields::boot();

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
