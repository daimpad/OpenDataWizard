<?php
/**
 * PHPUnit Bootstrap
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// WP_Mock initialisieren (WordPress-Funktionen mocken).
\WP_Mock::bootstrap();

// Plugin-Konstanten definieren damit Includes geladen werden können.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/tmp/wordpress/' );
}
if ( ! defined( 'ODW_VERSION' ) ) {
    define( 'ODW_VERSION', '1.2.0' );
}
if ( ! defined( 'ODW_PLUGIN_DIR' ) ) {
    define( 'ODW_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'ODW_PLUGIN_URL' ) ) {
    define( 'ODW_PLUGIN_URL', 'http://localhost/wp-content/plugins/open-data-wizard/' );
}
if ( ! defined( 'ODW_PLUGIN_FILE' ) ) {
    define( 'ODW_PLUGIN_FILE', dirname( __DIR__ ) . '/open-data-wizard.php' );
}
