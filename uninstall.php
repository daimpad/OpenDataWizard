<?php
/**
 * Plugin Deinstallation: Bereinigung aller Plugin-Daten.
 *
 * Wird von WordPress ausgeführt wenn das Plugin deinstalliert wird.
 * Nur aktiv wenn die Einstellung „Daten löschen" aktiviert ist.
 *
 * @package OpenDataWizard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Nur löschen wenn die Option gesetzt ist (Opt-in Datenlöschung).
if ( ! get_option( 'odw_delete_data_on_uninstall', false ) ) {
    return;
}

// Capabilities entfernen.
$roles = [ 'administrator', 'editor' ];
foreach ( $roles as $role_name ) {
    $role = get_role( $role_name );
    if ( $role ) {
        $role->remove_cap( 'manage_open_data' );
    }
}

// Alle odw_dataset Posts inkl. Postmeta löschen.
$posts = get_posts( [
    'post_type'      => 'odw_dataset',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
] );

foreach ( $posts as $post_id ) {
    wp_delete_post( (int) $post_id, true );
}

// Plugin-Optionen löschen.
delete_option( 'odw_delete_data_on_uninstall' );

// Transients bereinigen.
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '_transient_odw_%'
    )
);
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '_transient_timeout_odw_%'
    )
);
