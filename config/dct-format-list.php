<?php
/**
 * Dateiformatliste für DCAT-AP Distributionen
 *
 * Jeder Eintrag: Kurzbezeichnung => ['mime' => MIME-Typ, 'eu_uri' => EU-Publications-Office-Kürzel]
 * eu_uri wird zu http://publications.europa.eu/resource/authority/file-type/{eu_uri} aufgelöst.
 * Leere eu_uri bedeutet: kein EU-Standard-URI vorhanden → Kurzbezeichnung als Fallback.
 *
 * @package OpenDataWizard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'CSV'       => array(
		'mime'   => 'text/csv',
		'eu_uri' => 'CSV',
	),
	'JSON'      => array(
		'mime'   => 'application/json',
		'eu_uri' => 'JSON',
	),
	'XLSX'      => array(
		'mime'   => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'eu_uri' => 'XLSX',
	),
	'ODS'       => array(
		'mime'   => 'application/vnd.oasis.opendocument.spreadsheet',
		'eu_uri' => 'ODS',
	),
	'PDF'       => array(
		'mime'   => 'application/pdf',
		'eu_uri' => 'PDF',
	),
	'XML'       => array(
		'mime'   => 'application/xml',
		'eu_uri' => 'XML',
	),
	'GeoJSON'   => array(
		'mime'   => 'application/geo+json',
		'eu_uri' => 'GEOJSON',
	),
	'GML'       => array(
		'mime'   => 'application/gml+xml',
		'eu_uri' => 'GML',
	),
	'SHP'       => array(
		'mime'   => 'application/x-esri-shape',
		'eu_uri' => 'SHP',
	),
	'GPKG'      => array(
		'mime'   => 'application/geopackage+sqlite3',
		'eu_uri' => 'GPKG',
	),
	'KML'       => array(
		'mime'   => 'application/vnd.google-earth.kml+xml',
		'eu_uri' => 'KML',
	),
	'ZIP'       => array(
		'mime'   => 'application/zip',
		'eu_uri' => 'ZIP',
	),
	'TIFF'      => array(
		'mime'   => 'image/tiff',
		'eu_uri' => 'TIFF',
	),
	'RDF'       => array(
		'mime'   => 'application/rdf+xml',
		'eu_uri' => 'RDF_XML',
	),
	'TURTLE'    => array(
		'mime'   => 'text/turtle',
		'eu_uri' => 'TURTLE',
	),
	'N-TRIPLES' => array(
		'mime'   => 'application/n-triples',
		'eu_uri' => 'N_TRIPLES',
	),
	'JSON-LD'   => array(
		'mime'   => 'application/ld+json',
		'eu_uri' => 'JSON_LD',
	),
	'Sonstiges' => array(
		'mime'   => '',
		'eu_uri' => '',
	),
);
