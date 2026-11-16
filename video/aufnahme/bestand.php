<?php
/**
 * Zwei knapp beschriebene, veröffentlichte Datensätze des Fördervereins — dieselben, die Folge 1
 * auf Folie 1.3 im Katalog zeigt. Nur die Pflichtangaben sind ausgefüllt, damit die Liste in
 * Folge 6 (Szene 6.7) beim Sortieren nach Qualität tatsächlich Nachholbedarf zeigt; die
 * Beispieldaten sind dafür zu vollständig.
 *
 *   wp eval-file video/aufnahme/bestand.php
 *
 * @package OpenDataWizard\Video
 */

// phpcs:ignoreFile -- Werkzeug für die Videoaufnahme, nicht Teil des Plugins.

$basis = array(
	'publisher' => 'Förderverein Musterstadt e. V.',
	'license'   => 'https://creativecommons.org/licenses/by/4.0/',
);
$ergebnis = ODW_Batch_Import::import_records(
	array(
		$basis + array(
			'title'       => 'Veranstaltungen des Fördervereins 2024',
			'description' => 'Termine, Orte und Teilnehmendenzahlen der Veranstaltungen des Fördervereins Musterstadt e. V. im Jahr 2024.',
			'access_url'  => 'https://foerderverein-musterstadt.example/daten/veranstaltungen-2024.csv',
		),
		$basis + array(
			'title'       => 'Spendenaufkommen des Fördervereins 2020–2025',
			'description' => 'Jährliche Summe der Spenden an den Förderverein Musterstadt e. V. von 2020 bis 2025.',
			'access_url'  => 'https://foerderverein-musterstadt.example/daten/spenden-2020-2025.csv',
		),
	)
);

foreach ( $ergebnis['ids'] as $id ) {
	delete_post_meta( $id, '_odw_imported' );
	wp_publish_post( $id );
	ODW_Quality::store( $id, ODW_Quality::calculate( $id ) );
	WP_CLI::log( sprintf( 'Bestand: %s (%d %%)', get_the_title( $id ), (int) get_post_meta( $id, '_odw_quality_score', true ) ) );
}
