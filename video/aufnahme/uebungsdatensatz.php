<?php
/**
 * Legt den Übungsdatensatz so an, wie er am Ende von Folge 3 als Entwurf gespeichert ist
 * (Ausgangszustand „uebungsdatensatz-entwurf"). Aufruf über zustand.sh:
 *
 *   wp eval-file video/aufnahme/uebungsdatensatz.php '<JSON aus folgen/03-…yml, Schlüssel uebungsdatensatz>'
 *
 * Angelegt wird über den Batch-Import des Plugins, also auf demselben Weg wie ein importierter
 * Entwurf. Reiter 3 bleibt leer bis auf die Lizenz, die wie im Formular auf dem Standardwert aus den
 * Einstellungen steht. Die englischen Übersetzungen aus Folge 3 fehlen; Folge 4 braucht sie nicht.
 *
 * @package OpenDataWizard\Video
 */

// phpcs:ignoreFile -- Werkzeug für die Videoaufnahme, nicht Teil des Plugins.

$daten = json_decode( (string) ( $args[0] ?? '' ), true );
if ( ! is_array( $daten ) || empty( $daten['titel'] ) ) {
	WP_CLI::error( 'Übungsdatensatz fehlt oder ist unvollständig.' );
}

$ergebnis = ODW_Batch_Import::import_records(
	array(
		array(
			'title'       => $daten['titel'],
			'publisher'   => $daten['herausgeber'],
			'description' => $daten['beschreibung'],
			'theme'       => $daten['thema'],
			'keywords'    => implode( "\n", (array) $daten['schlagworte'] ),
			'license'     => (string) ODW_Settings::get( 'default_license' ),
		),
	)
);

$id = (int) ( $ergebnis['ids'][0] ?? 0 );
if ( ! $id ) {
	WP_CLI::error( 'Übungsdatensatz konnte nicht angelegt werden: ' . wp_json_encode( $ergebnis['errors'] ?? array() ) );
}
// Im Film ist der Datensatz von Hand angelegt, nicht importiert.
delete_post_meta( $id, '_odw_imported' );
WP_CLI::log( "Übungsdatensatz als Entwurf angelegt (ID $id)." );
