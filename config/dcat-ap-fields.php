<?php
/**
 * DCAT-AP 3.0 Felddefinitionen — Single Source of Truth
 *
 * Definiert alle Qualitätsindikatoren und Pflichtfelder des Plugins.
 * Wird von ODW_Quality (Scoring) und ODW_Fields (Validierungsregistry) geladen.
 *
 * Format pro Eintrag:
 *   key       — interner Schlüssel (für check_indicator)
 *   meta_key  — WP-Post-Meta-Schlüssel (nur für skalare CF-Felder; Pflicht/Empfohlen-Felder)
 *   dcat_prop — DCAT-AP 3.0 Property (Dokumentationszweck)
 *   label     — Benutzerfreundliches Label (erscheint in Fehlermeldungen und Qualitätsbericht)
 *   points    — Punkte bei Erfüllung (Summe aller required = 55, alle = 100)
 *   required  — true = Pflichtfeld (Veröffentlichung wird blockiert), false = empfohlen/optional
 *
 * Um DCAT-AP-Updates einzuspielen: nur diese Datei anpassen, keine Klassen ändern.
 *
 * @package OpenDataWizard
 */

// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- config data, not a DB query.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	// -------------------------------------------------------------------------
	// Pflichtfelder (DCAT-AP 3.0 mandatory) — 55 Punkte
	// -------------------------------------------------------------------------

	array(
		'key'       => 'title',
		'meta_key'  => '',
		'dcat_prop' => 'dct:title',
		'label'     => __( 'Titel (dct:title)', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => true,
	),
	array(
		'key'       => 'description',
		'meta_key'  => '_odw_description',
		'dcat_prop' => 'dct:description',
		'label'     => __( 'Worum geht es in diesem Datensatz?', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => true,
	),
	array(
		'key'       => 'publisher',
		'meta_key'  => '_odw_publisher',
		'dcat_prop' => 'dct:publisher',
		'label'     => __( 'Wer gibt diese Daten heraus?', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => true,
	),
	array(
		'key'       => 'license',
		'meta_key'  => '',
		'dcat_prop' => 'dct:license',
		'label'     => __( 'Unter welcher Lizenz sind diese Daten verfügbar?', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => true,
	),
	array(
		'key'       => 'distribution',
		'meta_key'  => '',
		'dcat_prop' => 'dcat:accessURL',
		'label'     => __( 'Distribution mit Zugriffs-URL (dcat:accessURL)', 'open-data-wizard' ),
		'points'    => 15,
		'required'  => true,
	),

	// -------------------------------------------------------------------------
	// Empfohlene Felder (DCAT-AP 3.0 recommended) — 40 Punkte
	// -------------------------------------------------------------------------

	array(
		'key'       => 'language',
		'meta_key'  => '_odw_language',
		'dcat_prop' => 'dct:language',
		'label'     => __( 'Sprache (dct:language)', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => false,
	),
	array(
		'key'       => 'keywords',
		'meta_key'  => '_odw_keywords',
		'dcat_prop' => 'dcat:keyword',
		'label'     => __( 'Schlagworte (dcat:keyword)', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => false,
	),
	array(
		'key'       => 'theme',
		'meta_key'  => '_odw_theme',
		'dcat_prop' => 'dcat:theme',
		'label'     => __( 'Thema (dcat:theme)', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => false,
	),
	array(
		'key'       => 'issued',
		'meta_key'  => '_odw_issued',
		'dcat_prop' => 'dct:issued',
		'label'     => __( 'Veröffentlichungsdatum (dct:issued)', 'open-data-wizard' ),
		'points'    => 10,
		'required'  => false,
	),

	// -------------------------------------------------------------------------
	// Optionale Angaben — 5 Punkte
	// -------------------------------------------------------------------------

	array(
		'key'       => 'dist_format',
		'meta_key'  => '',
		'dcat_prop' => 'dct:format',
		'label'     => __( 'Format der Distribution (dct:format)', 'open-data-wizard' ),
		'points'    => 5,
		'required'  => false,
	),

);
// Summe: 55 + 40 + 5 = 100.
