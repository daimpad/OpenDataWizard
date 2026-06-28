<?php
/**
 * DCAT-AP 3.0 Felddefinitionen — Single Source of Truth
 *
 * Definiert alle Qualitätsindikatoren und Pflichtfelder des Plugins.
 * Wird von ODW_Quality (Scoring) und ODW_Fields (Validierungsregistry) geladen.
 *
 * Format pro Eintrag:
 *   key         — interner Schlüssel (für check_indicator)
 *   meta_key    — WP-Post-Meta-Schlüssel (nur für skalare CF-Felder; Pflicht/Empfohlen-Felder)
 *   dcat_prop   — DCAT-AP 3.0 Property (Dokumentationszweck)
 *   label       — Benutzerfreundliches Label (erscheint in Fehlermeldungen und Qualitätsbericht)
 *   points      — Punkte bei Erfüllung (Summe aller required = 55, alle = 100)
 *   required    — true = Pflichtfeld (Veröffentlichung wird blockiert), false = empfohlen/optional
 *
 * Deklarative Schema-Metadaten (optional; aktuell beschreibend, künftig steuernd):
 *   profile     — 'ap' (DCAT-AP 3.0) | 'ap.de' (DCAT-AP.de) | 'hvd' (High-Value-Dataset)
 *   tier        — 'mandatory' | 'recommended' | 'optional' (vom Plugin erzwungene Stufe)
 *   range       — 'literal' | 'literal-lang' | 'uri' | 'node' (JSON-LD-Wertform)
 *   cardinality — '0..1' | '0..n' | '1..1' | '1..n' (Kardinalität laut Profil)
 *   entity      — 'dataset' | 'distribution' | 'catalog' (Ziel-Entität)
 *   vocab       — ID eines kontrollierten Vokabulars (falls zutreffend), sonst ''
 *
 * Die Metadaten-Schlüssel sind abwärtskompatibel: bestehende Konsumenten
 * (ODW_Quality, ODW_Validation) lesen weiterhin nur key/meta_key/label/points/required.
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
		'key'         => 'title',
		'meta_key'    => '',
		'dcat_prop'   => 'dct:title',
		'label'       => __( 'Titel (dct:title)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => true,
		'profile'     => 'ap',
		'tier'        => 'mandatory',
		'range'       => 'literal-lang',
		'cardinality' => '1..n',
		'entity'      => 'dataset',
		'vocab'       => '',
	),
	array(
		'key'         => 'description',
		'meta_key'    => '_odw_description',
		'dcat_prop'   => 'dct:description',
		'label'       => __( 'Beschreibung (dct:description)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => true,
		'profile'     => 'ap',
		'tier'        => 'mandatory',
		'range'       => 'literal-lang',
		'cardinality' => '1..n',
		'entity'      => 'dataset',
		'vocab'       => '',
	),
	array(
		'key'         => 'publisher',
		'meta_key'    => '_odw_publisher',
		'dcat_prop'   => 'dct:publisher',
		'label'       => __( 'Herausgeber (dct:publisher)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => true,
		'profile'     => 'ap',
		'tier'        => 'mandatory',
		'range'       => 'node',
		'cardinality' => '0..1',
		'entity'      => 'dataset',
		'vocab'       => '',
	),
	array(
		'key'         => 'license',
		'meta_key'    => '',
		'dcat_prop'   => 'dct:license',
		'label'       => __( 'Lizenz (dct:license)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => true,
		'profile'     => 'ap',
		'tier'        => 'mandatory',
		'range'       => 'uri',
		'cardinality' => '0..1',
		'entity'      => 'distribution',
		'vocab'       => 'licence',
	),
	array(
		'key'         => 'distribution',
		'meta_key'    => '',
		'dcat_prop'   => 'dcat:accessURL',
		'label'       => __( 'Distribution mit Zugriffs-URL (dcat:accessURL)', 'open-data-wizard' ),
		'points'      => 15,
		'required'    => true,
		'profile'     => 'ap',
		'tier'        => 'mandatory',
		'range'       => 'uri',
		'cardinality' => '1..n',
		'entity'      => 'distribution',
		'vocab'       => '',
	),

	// -------------------------------------------------------------------------
	// Empfohlene Felder (DCAT-AP 3.0 recommended) — 40 Punkte
	// -------------------------------------------------------------------------

	array(
		'key'         => 'language',
		'meta_key'    => '_odw_language',
		'dcat_prop'   => 'dct:language',
		'label'       => __( 'Sprache (dct:language)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => false,
		'profile'     => 'ap',
		'tier'        => 'recommended',
		'range'       => 'uri',
		'cardinality' => '0..n',
		'entity'      => 'dataset',
		'vocab'       => 'language',
	),
	array(
		'key'         => 'keywords',
		'meta_key'    => '_odw_keywords',
		'dcat_prop'   => 'dcat:keyword',
		'label'       => __( 'Schlagworte (dcat:keyword)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => false,
		'profile'     => 'ap',
		'tier'        => 'recommended',
		'range'       => 'literal-lang',
		'cardinality' => '0..n',
		'entity'      => 'dataset',
		'vocab'       => '',
	),
	array(
		'key'         => 'theme',
		'meta_key'    => '_odw_theme',
		'dcat_prop'   => 'dcat:theme',
		'label'       => __( 'Thema (dcat:theme)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => false,
		'profile'     => 'ap',
		'tier'        => 'recommended',
		'range'       => 'uri',
		'cardinality' => '0..n',
		'entity'      => 'dataset',
		'vocab'       => 'data-theme',
	),
	array(
		'key'         => 'issued',
		'meta_key'    => '_odw_issued',
		'dcat_prop'   => 'dct:issued',
		'label'       => __( 'Veröffentlichungsdatum (dct:issued)', 'open-data-wizard' ),
		'points'      => 10,
		'required'    => false,
		'profile'     => 'ap',
		'tier'        => 'recommended',
		'range'       => 'literal',
		'cardinality' => '0..1',
		'entity'      => 'dataset',
		'vocab'       => '',
	),

	// -------------------------------------------------------------------------
	// Optionale Angaben — 5 Punkte
	// -------------------------------------------------------------------------

	array(
		'key'         => 'dist_format',
		'meta_key'    => '',
		'dcat_prop'   => 'dct:format',
		'label'       => __( 'Format der Distribution (dct:format)', 'open-data-wizard' ),
		'points'      => 5,
		'required'    => false,
		'profile'     => 'ap',
		'tier'        => 'optional',
		'range'       => 'uri',
		'cardinality' => '0..1',
		'entity'      => 'distribution',
		'vocab'       => 'file-type',
	),

);
// Summe: 55 + 40 + 5 = 100.
