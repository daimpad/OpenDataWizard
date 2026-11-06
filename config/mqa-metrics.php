<?php
/**
 * MQA-Metrik-Definitionen — Single Source of Truth für das Qualitäts-Scoring
 *
 * Bildet die Metadata Quality Assessment (MQA) Methodik von data.europa.eu ab:
 * 5 FAIR-Dimensionen, 405 Punkte, 4 Bewertungsstufen. Siehe docs/MQA-KONZEPT.md.
 *
 * Format pro Eintrag:
 *   key        — interner Schlüssel
 *   dimension  — findability | accessibility | interoperability | reusability | contextuality
 *   dcat_prop  — DCAT-AP Property (Dokumentation/Anzeige)
 *   label      — benutzerfreundliches Label (Qualitätsbericht)
 *   points     — Gewicht laut MQA
 *   type       — Prüfungsart:
 *                  'present'   — Eigenschaft gesetzt? (offline, Phase 1)
 *                  'vocab'     — Wert aus kontrolliertem Vokabular? (offline, Phase 2)
 *                  'reachable' — URL per HTTP HEAD erreichbar? (Netzwerk, Phase 3)
 *                  'shacl'     — DCAT-AP-SHACL-konform? (extern, Phase 3)
 *   check      — Schlüssel für ODW_Quality::check_metric() (nur bei type='present')
 *
 * Nur Metriken vom Typ 'present' werden aktuell bewertet; 'vocab'/'reachable'/'shacl'
 * liefern „nicht bewertet" und werden aus dem bewertbaren Maximum herausgerechnet,
 * bis die jeweilige Phase implementiert ist.
 *
 * @package OpenDataWizard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	// -------------------------------------------------------------------------
	// Auffindbarkeit (max. 100)
	// -------------------------------------------------------------------------
	array(
		'key'       => 'keyword',
		'dimension' => 'findability',
		'dcat_prop' => 'dcat:keyword',
		'label'     => __( 'Schlüsselwörter (dcat:keyword)', 'open-data-wizard' ),
		'points'    => 30,
		'type'      => 'present',
		'check'     => 'keyword',
	),
	array(
		'key'       => 'theme',
		'dimension' => 'findability',
		'dcat_prop' => 'dcat:theme',
		'label'     => __( 'Thema (dcat:theme)', 'open-data-wizard' ),
		'points'    => 30,
		'type'      => 'present',
		'check'     => 'theme',
	),
	array(
		'key'       => 'spatial',
		'dimension' => 'findability',
		'dcat_prop' => 'dct:spatial',
		'label'     => __( 'Räumliche Abdeckung (dct:spatial)', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'present',
		'check'     => 'spatial',
	),
	array(
		'key'       => 'temporal',
		'dimension' => 'findability',
		'dcat_prop' => 'dct:temporal',
		'label'     => __( 'Zeitliche Abdeckung (dct:temporal)', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'present',
		'check'     => 'temporal',
	),

	// -------------------------------------------------------------------------
	// Zugänglichkeit (max. 100)
	// -------------------------------------------------------------------------
	array(
		'key'       => 'access_url_reachable',
		'dimension' => 'accessibility',
		'dcat_prop' => 'dcat:accessURL',
		'label'     => __( 'AccessURL erreichbar (dcat:accessURL)', 'open-data-wizard' ),
		'points'    => 50,
		'type'      => 'reachable',
		'check'     => 'access_url',
	),
	array(
		'key'       => 'download_url',
		'dimension' => 'accessibility',
		'dcat_prop' => 'dcat:downloadURL',
		'label'     => __( 'DownloadURL gesetzt (dcat:downloadURL)', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'present',
		'check'     => 'download_url',
	),
	array(
		'key'       => 'download_url_reachable',
		'dimension' => 'accessibility',
		'dcat_prop' => 'dcat:downloadURL',
		'label'     => __( 'DownloadURL erreichbar (dcat:downloadURL)', 'open-data-wizard' ),
		'points'    => 30,
		'type'      => 'reachable',
		'check'     => 'download_url',
	),

	// -------------------------------------------------------------------------
	// Interoperabilität (max. 110)
	// -------------------------------------------------------------------------
	array(
		'key'       => 'format',
		'dimension' => 'interoperability',
		'dcat_prop' => 'dct:format',
		'label'     => __( 'Format gesetzt (dct:format)', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'present',
		'check'     => 'format',
	),
	array(
		'key'       => 'media_type',
		'dimension' => 'interoperability',
		'dcat_prop' => 'dcat:mediaType',
		'label'     => __( 'Media-Type gesetzt (dcat:mediaType)', 'open-data-wizard' ),
		'points'    => 10,
		'type'      => 'present',
		'check'     => 'media_type',
	),
	array(
		'key'       => 'format_vocab',
		'dimension' => 'interoperability',
		'dcat_prop' => 'dct:format',
		'label'     => __( 'Format/Media-Type aus Vokabular', 'open-data-wizard' ),
		'points'    => 10,
		'type'      => 'vocab',
		'check'     => 'format_vocab',
	),
	array(
		'key'       => 'format_nonproprietary',
		'dimension' => 'interoperability',
		'dcat_prop' => 'dct:format',
		'label'     => __( 'Nicht-proprietäres Format', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'vocab',
		'check'     => 'format_nonproprietary',
	),
	array(
		'key'       => 'format_machine_readable',
		'dimension' => 'interoperability',
		'dcat_prop' => 'dct:format',
		'label'     => __( 'Maschinenlesbares Format', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'vocab',
		'check'     => 'format_machine_readable',
	),
	array(
		'key'       => 'dcat_ap_compliance',
		'dimension' => 'interoperability',
		'dcat_prop' => 'dcat-ap',
		'label'     => __( 'DCAT-AP-Konformität (SHACL)', 'open-data-wizard' ),
		'points'    => 30,
		'type'      => 'shacl',
		'check'     => 'shacl',
	),

	// -------------------------------------------------------------------------
	// Wiederverwendbarkeit (max. 75)
	// -------------------------------------------------------------------------
	array(
		'key'       => 'license',
		'dimension' => 'reusability',
		'dcat_prop' => 'dct:license',
		'label'     => __( 'Lizenz gesetzt (dct:license)', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'present',
		'check'     => 'license',
	),
	array(
		'key'       => 'license_vocab',
		'dimension' => 'reusability',
		'dcat_prop' => 'dct:license',
		'label'     => __( 'Lizenz aus Vokabular', 'open-data-wizard' ),
		'points'    => 10,
		'type'      => 'vocab',
		'check'     => 'license_vocab',
	),
	array(
		'key'       => 'access_rights',
		'dimension' => 'reusability',
		'dcat_prop' => 'dct:accessRights',
		'label'     => __( 'Zugriffsrechte gesetzt (dct:accessRights)', 'open-data-wizard' ),
		'points'    => 10,
		'type'      => 'present',
		'check'     => 'access_rights',
	),
	array(
		'key'       => 'access_rights_vocab',
		'dimension' => 'reusability',
		'dcat_prop' => 'dct:accessRights',
		'label'     => __( 'Zugriffsrechte aus Vokabular', 'open-data-wizard' ),
		'points'    => 5,
		'type'      => 'vocab',
		'check'     => 'access_rights_vocab',
	),
	array(
		'key'       => 'contact_point',
		'dimension' => 'reusability',
		'dcat_prop' => 'dcat:contactPoint',
		'label'     => __( 'Kontaktpunkt (dcat:contactPoint)', 'open-data-wizard' ),
		'points'    => 20,
		'type'      => 'present',
		'check'     => 'contact_point',
	),
	array(
		'key'       => 'publisher',
		'dimension' => 'reusability',
		'dcat_prop' => 'dct:publisher',
		'label'     => __( 'Herausgeber (dct:publisher)', 'open-data-wizard' ),
		'points'    => 10,
		'type'      => 'present',
		'check'     => 'publisher',
	),

	// -------------------------------------------------------------------------
	// Kontext (max. 20)
	// -------------------------------------------------------------------------
	array(
		'key'       => 'rights',
		'dimension' => 'contextuality',
		'dcat_prop' => 'dct:rights',
		'label'     => __( 'Rechte (dct:rights)', 'open-data-wizard' ),
		'points'    => 5,
		'type'      => 'present',
		'check'     => 'rights',
	),
	array(
		'key'       => 'byte_size',
		'dimension' => 'contextuality',
		'dcat_prop' => 'dcat:byteSize',
		'label'     => __( 'Dateigröße (dcat:byteSize)', 'open-data-wizard' ),
		'points'    => 5,
		'type'      => 'present',
		'check'     => 'byte_size',
	),
	array(
		'key'       => 'issued',
		'dimension' => 'contextuality',
		'dcat_prop' => 'dct:issued',
		'label'     => __( 'Ausstellungsdatum (dct:issued)', 'open-data-wizard' ),
		'points'    => 5,
		'type'      => 'present',
		'check'     => 'issued',
	),
	array(
		'key'       => 'modified',
		'dimension' => 'contextuality',
		'dcat_prop' => 'dct:modified',
		'label'     => __( 'Änderungsdatum (dct:modified)', 'open-data-wizard' ),
		'points'    => 5,
		'type'      => 'present',
		'check'     => 'modified',
	),
);
// Summe: 100 + 100 + 110 + 75 + 20 = 405.
