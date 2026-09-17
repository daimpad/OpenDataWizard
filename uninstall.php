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
$odw_settings = (array) get_option( 'odw_settings', array() );
if ( empty( $odw_settings['delete_on_uninstall'] ) ) {
	return;
}

// Alle odw_dataset Posts inkl. Postmeta löschen.
// 'any' würde Stati mit exclude_from_search (trash, auto-draft) auslassen —
// daher explizit alle registrierten Stati abfragen.
$odw_post_ids = get_posts(
	array(
		'post_type'      => 'odw_dataset',
		'post_status'    => array_values( get_post_stati() ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $odw_post_ids as $odw_pid ) {
	wp_delete_post( (int) $odw_pid, true );
}

// Custom Capability aus allen Rollen entfernen.
$odw_roles = wp_roles();
foreach ( $odw_roles->role_objects as $odw_role ) {
	if ( $odw_role->has_cap( 'manage_open_data' ) ) {
		$odw_role->remove_cap( 'manage_open_data' );
	}
}

// Plugin-Optionen löschen.
delete_option( 'odw_settings' );
delete_option( 'odw_demo_post_id' );
delete_option( 'odw_show_welcome' );

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
