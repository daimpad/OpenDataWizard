<?php
/**
 * Standalone-Generator für die Feld-Referenz.
 *
 * Erzeugt docs/FELD-REFERENZ.md aus config/field-catalog.php — ohne WordPress,
 * damit der Befehl auch lokal und in der CI läuft.
 *
 * Aufruf:
 *   php bin/generate-field-reference.php
 *
 * Innerhalb von WordPress dieselbe Ausgabe per WP-CLI:
 *   wp open-data-wizard docs
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

// Standalone-CLI-Generator: direkte Konsolenausgabe und Dateisystem-Zugriff sind
// hier beabsichtigt (kein WordPress-Kontext, kein Web-Output).
// phpcs:disable WordPress.Security.EscapeOutput
// phpcs:disable WordPress.WP.AlternativeFunctions

// Die Katalog- und Generator-Dateien schützen sich mit einem ABSPATH-Guard.
// Für den Standalone-Lauf definieren wir einen Stub, damit sie geladen werden dürfen.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require __DIR__ . '/../includes/class-field-reference.php';

$target = __DIR__ . '/../docs/FELD-REFERENZ.md';
$bytes  = ODW_Field_Reference::write( $target );

if ( $bytes > 0 ) {
	$count = count( ODW_Field_Reference::load_catalog() );
	echo 'OK — ' . $count . ' Felder, ' . $bytes . " Bytes → docs/FELD-REFERENZ.md\n";
	exit( 0 );
}

fwrite( STDERR, "Fehler: Datei konnte nicht geschrieben werden.\n" );
exit( 1 );
