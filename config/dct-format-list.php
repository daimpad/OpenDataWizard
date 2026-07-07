<?php
/**
 * Dateiformatliste für DCAT-AP Distributionen
 *
 * Jeder Eintrag: Kurzbezeichnung => [
 *   'mime'             => MIME-Typ,
 *   'eu_uri'           => EU-Publications-Office-Kürzel (leer = kein EU-Standard-URI),
 *   'machine_readable' => bool (MQA: maschinenlesbares Format),
 *   'non_proprietary'  => bool (MQA: nicht-proprietäres Format),
 * ]
 * eu_uri wird zu http://publications.europa.eu/resource/authority/file-type/{eu_uri} aufgelöst.
 * Die MQA-Flags orientieren sich an den EU-Vokabularen für maschinenlesbare bzw.
 * nicht-proprietäre Formate (data.europa.eu) und werden für das Qualitäts-Scoring genutzt.
 *
 * @package OpenDataWizard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'CSV'       => array(
		'mime'             => 'text/csv',
		'eu_uri'           => 'CSV',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'JSON'      => array(
		'mime'             => 'application/json',
		'eu_uri'           => 'JSON',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'XLSX'      => array(
		'mime'             => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'eu_uri'           => 'XLSX',
		'machine_readable' => true,
		'non_proprietary'  => false,
	),
	'ODS'       => array(
		'mime'             => 'application/vnd.oasis.opendocument.spreadsheet',
		'eu_uri'           => 'ODS',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'PDF'       => array(
		'mime'             => 'application/pdf',
		'eu_uri'           => 'PDF',
		'machine_readable' => false,
		'non_proprietary'  => true,
	),
	'XML'       => array(
		'mime'             => 'application/xml',
		'eu_uri'           => 'XML',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'GeoJSON'   => array(
		'mime'             => 'application/geo+json',
		'eu_uri'           => 'GEOJSON',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'GML'       => array(
		'mime'             => 'application/gml+xml',
		'eu_uri'           => 'GML',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'SHP'       => array(
		'mime'             => 'application/x-esri-shape',
		'eu_uri'           => 'SHP',
		'machine_readable' => true,
		'non_proprietary'  => false,
	),
	'GPKG'      => array(
		'mime'             => 'application/geopackage+sqlite3',
		'eu_uri'           => 'GPKG',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'KML'       => array(
		'mime'             => 'application/vnd.google-earth.kml+xml',
		'eu_uri'           => 'KML',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'ZIP'       => array(
		'mime'             => 'application/zip',
		'eu_uri'           => 'ZIP',
		'machine_readable' => false,
		'non_proprietary'  => true,
	),
	'TIFF'      => array(
		'mime'             => 'image/tiff',
		'eu_uri'           => 'TIFF',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'RDF'       => array(
		'mime'             => 'application/rdf+xml',
		'eu_uri'           => 'RDF_XML',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'TURTLE'    => array(
		'mime'             => 'text/turtle',
		'eu_uri'           => 'TURTLE',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'N-TRIPLES' => array(
		'mime'             => 'application/n-triples',
		'eu_uri'           => 'N_TRIPLES',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'JSON-LD'   => array(
		'mime'             => 'application/ld+json',
		'eu_uri'           => 'JSON_LD',
		'machine_readable' => true,
		'non_proprietary'  => true,
	),
	'Sonstiges' => array(
		'mime'             => '',
		'eu_uri'           => '',
		'machine_readable' => false,
		'non_proprietary'  => false,
	),
);
