<?php
/**
 * Plugin Name: Open Data Wizard — E2E-Saat
 * Description: Legt für die End-to-End-Tests einen bekannten Datenbestand an. Das Verzeichnis tests/e2e/mu-plugins wird über .wp-env.json nach wp-content/mu-plugins gemountet; nichts davon ist Teil des Release-Pakets.
 *
 * Warum ein mu-plugin und nicht ein paar WP-CLI-Aufrufe im Workflow: Die Saat
 * läuft damit lokal und in der CI über denselben Weg, ohne Ausgaben von
 * `wp-env run` zu parsen, und sie ist idempotent — ein zweiter Start legt
 * nichts doppelt an.
 *
 * `mappings` in .wp-env.json bildet Verzeichnisse ab, keine einzelnen Dateien.
 * Deshalb liegt die Datei in einem eigenen Ordner und nicht direkt in tests/e2e.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option, die den einmaligen Lauf markiert. */
const ODW_E2E_SEED_OPTION = 'odw_e2e_seeded';

/**
 * Datensätze für die E2E-Tests anlegen.
 *
 * Läuft auf `init` mit später Priorität: Das Plugin bootet auf
 * `after_setup_theme`, der Custom Post Type und ODW_Quality stehen hier also
 * bereits zur Verfügung.
 */
function odw_e2e_seed(): void {
	if ( get_option( ODW_E2E_SEED_OPTION ) ) {
		return;
	}

	if ( ! post_type_exists( 'odw_dataset' ) ) {
		return;
	}

	// Einmalige Umleitung auf die Einstiegsseite und die Willkommensmeldung
	// abräumen: Beide werden bei der Aktivierung gesetzt und würden die ersten
	// Seitenaufrufe der Tests je nach Reihenfolge unterschiedlich aussehen
	// lassen. Tests sollen an der Software scheitern, nicht am Zufall.
	delete_transient( 'odw_activation_redirect' );
	delete_option( 'odw_show_welcome' );

	$datasets = array(
		array(
			'title'  => 'E2E: Schulstandorte',
			'themes' => array( 'Bildung' ),
			'meta'   => array(
				'_odw_publisher'   => 'Stadt Musterstadt',
				'_odw_description' => 'Standorte aller allgemeinbildenden Schulen im Stadtgebiet.',
				'_odw_access_url'  => 'https://example.org/schulstandorte.csv',
				'_odw_license'     => 'https://creativecommons.org/licenses/by/4.0/',
				'_odw_format'      => 'CSV',
				'_odw_language'    => 'http://publications.europa.eu/resource/authority/language/DEU',
				'_odw_keywords'    => "Schule\nBildung\nStandorte",
			),
		),
		array(
			'title'  => 'E2E: Vereinsregister',
			'themes' => array( 'Soziales' ),
			'meta'   => array(
				'_odw_publisher'   => 'Musterorganisation e.V.',
				'_odw_description' => 'Liste der eingetragenen Vereine mit Gründungsjahr.',
				'_odw_access_url'  => 'https://example.org/vereine.json',
				'_odw_license'     => 'https://creativecommons.org/publicdomain/zero/1.0/',
				'_odw_format'      => 'JSON',
				'_odw_language'    => 'http://publications.europa.eu/resource/authority/language/DEU',
				'_odw_keywords'    => "Vereine\nZivilgesellschaft",
			),
		),
	);

	$created = array();

	foreach ( $datasets as $dataset ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'odw_dataset',
				'post_title'  => $dataset['title'],
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		foreach ( $dataset['meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// Themen sind seit v2.41.0 eine Mehrfachauswahl: Carbon Fields legt sie
		// unter eigenen Meta-Keys ab, ein update_post_meta( '_odw_theme' ) wäre
		// unsichtbar. Deshalb über die öffentliche Schreib-API.
		if ( ! empty( $dataset['themes'] ) && function_exists( 'carbon_set_post_meta' ) && class_exists( 'ODW_Fields' ) ) {
			carbon_set_post_meta( $post_id, 'odw_theme', ODW_Fields::normalize_themes( $dataset['themes'] ) );
			ODW_Fields::sync_theme_index( $post_id );
		}

		if ( class_exists( 'ODW_Quality' ) ) {
			ODW_Quality::store( $post_id, ODW_Quality::calculate( $post_id ) );
		}

		$created[] = $post_id;
	}

	if ( empty( $created ) ) {
		return;
	}

	update_option( ODW_E2E_SEED_OPTION, $created, false );

	// Die Antworten der REST-Endpunkte werden fünf Minuten lang in Transients
	// gehalten. Ohne Leeren sähen die Tests einen Katalog von vor der Saat.
	if ( class_exists( 'ODW_Rest_API' ) ) {
		ODW_Rest_API::delete_catalog_transients_public();

		foreach ( $created as $post_id ) {
			ODW_Rest_API::invalidate_cache( $post_id );
		}
	}
}
add_action( 'init', 'odw_e2e_seed', 20 );
