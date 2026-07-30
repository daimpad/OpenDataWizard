<?php
/**
 * Plugin Name:       Open Data Wizard
 * Plugin URI:        https://github.com/daimpad/OpenDataWizard
 * Description:       DCAT-AP 3.0 konforme Open Data Metadatenverwaltung für WordPress. Bereitstellung als maschinenlesbarer JSON-LD-Endpoint für offene Daten.
 * Version:           2.21.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            nozilla
 * Author URI:        https://github.com/daimpad/OpenDataWizard
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       open-data-wizard
 * Domain Path:       /languages
 * GitHub Plugin URI: daimpad/OpenDataWizard
 * Primary Branch:    main
 * GitHub Branch:     main
 * Release Asset:     false
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ODW_VERSION', '2.21.0' );
define( 'ODW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ODW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ODW_PLUGIN_FILE', __FILE__ );

// Setup-Klasse früh laden: on_activation() wird vor dem vollständigen
// Bootstrap aufgerufen, darf Carbon Fields daher nicht voraussetzen.
require_once ODW_PLUGIN_DIR . 'includes/class-setup.php';

// ---------------------------------------------------------------------------
// Activation / Deactivation
// ---------------------------------------------------------------------------

register_activation_hook( __FILE__, 'odw_activate' );
register_deactivation_hook( __FILE__, 'odw_deactivate' );

/**
 * Runs on plugin activation: registers CPT and flushes rewrite rules.
 */
function odw_activate(): void {
	odw_register_cpt_static();
	flush_rewrite_rules();
	ODW_Setup::on_activation();
}

/**
 * Runs on plugin deactivation: flushes rewrite rules.
 */
function odw_deactivate(): void {
	flush_rewrite_rules();
}

/**
 * Minimal CPT registration used during activation (before theme is set up).
 */
function odw_register_cpt_static(): void {
	register_post_type(
		'odw_dataset',
		array(
			'public'   => false,
			'supports' => array( 'title', 'revisions' ),
		)
	);
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
		add_action(
			'admin_notices',
			function (): void {
				echo '<div class="notice notice-error"><p>';
				esc_html_e(
					'Open Data Wizard: Vendor-Abhängigkeiten fehlen. Bitte composer install im Plugin-Verzeichnis ausführen.',
					'open-data-wizard'
				);
				echo '</p></div>';
			}
		);
		return;
	}

	require_once $autoloader;

	try {
		\Carbon_Fields\Carbon_Fields::boot();
	} catch ( \Throwable $e ) {
		add_action(
			'admin_notices',
			function () use ( $e ): void {
				echo '<div class="notice notice-error"><p>';
				printf(
				/* translators: %s: Error message */
					esc_html__( 'Open Data Wizard: Carbon Fields konnte nicht initialisiert werden — %s', 'open-data-wizard' ),
					esc_html( $e->getMessage() )
				);
				echo '</p></div>';
			}
		);
		return;
	}

	require_once ODW_PLUGIN_DIR . 'includes/class-settings.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-post-types.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-rest-api.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-validation.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-quality.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-admin.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-batch-import.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-shortcode.php';
	require_once ODW_PLUGIN_DIR . 'includes/class-cli.php';

	ODW_Settings::init();
	ODW_Post_Types::init();
	ODW_Fields::init();
	ODW_Rest_API::init();
	ODW_Validation::init();
	ODW_Quality::init();
	ODW_Admin::init();
	ODW_Shortcode::init();
	ODW_Setup::init();
	ODW_CLI::init();
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
