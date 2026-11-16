<?php
/**
 * Legt den Übungsdatensatz so an, wie er am Ende einer Folge als Entwurf gespeichert ist.
 * Aufruf über zustand.sh:
 *
 *   wp eval-file video/aufnahme/uebungsdatensatz.php '<JSON folgen/03, uebungsdatensatz>'
 *       → Ende von Folge 3 („uebungsdatensatz-entwurf")
 *   wp eval-file video/aufnahme/uebungsdatensatz.php '<JSON 03>' <Anhang-ID> '<JSON folgen/04, uebungsdatensatz>'
 *       → Ende von Folge 4 („uebungsdatensatz-folge-4"): dazu die CSV-Datei aus der Mediathek,
 *         Lizenz mit Namensnennungstext, Verfügbarkeit und die JSON-Distribution
 *   … '<JSON 05>'  → Ende von Folge 5 („uebungsdatensatz-folge-5"): dazu Reiter 4
 *   … '<JSON 05>' '<JSON 06>'  → Ende von Folge 6 („uebungsdatensatz-veroeffentlicht"): dazu der
 *         Medientyp, und der Datensatz ist veröffentlicht
 *
 * Angelegt wird über den Batch-Import des Plugins, also auf demselben Weg wie ein importierter
 * Entwurf. Ohne Folge-4-Angaben bleibt Reiter 3 leer bis auf die Lizenz, die wie im Formular auf
 * dem Standardwert aus den Einstellungen steht. Die englischen Übersetzungen aus Folge 3 fehlen;
 * die späteren Folgen brauchen sie nicht.
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

$anhang = (int) ( $args[1] ?? 0 );
$f4     = json_decode( (string) ( $args[2] ?? '' ), true );
if ( $anhang && is_array( $f4 ) ) {
	// Die Drehbücher nennen Beschriftungen aus dem Formular; gespeichert werden die URIs dahinter.
	$uri = static function ( array $optionen, string $label ): string {
		$uri = array_search( $label, $optionen, true );
		if ( false === $uri ) {
			WP_CLI::error( "Unbekannte Auswahl „{$label}“." );
		}
		return (string) $uri;
	};
	$lizenzen = ODW_Fields::get_license_options();
	$pfad     = (string) get_attached_file( $anhang );
	$groesse  = (int) filesize( $pfad );

	// Wie nach dem Upload in Folge 4 (ODW_Admin::save_file_attachment(), ab 2.47.6).
	update_post_meta( $id, '_odw_file_id', $anhang );
	update_post_meta( $id, '_odw_file_size', $groesse );
	update_post_meta( $id, '_odw_file_format', strtoupper( (string) pathinfo( $pfad, PATHINFO_EXTENSION ) ) );
	update_post_meta( $id, '_odw_access_url', esc_url_raw( (string) wp_get_attachment_url( $anhang ) ) );
	update_post_meta( $id, '_odw_format', ODW_Fields::resolve_format_key( (string) pathinfo( $pfad, PATHINFO_EXTENSION ) ) );
	update_post_meta( $id, '_odw_byte_size', (string) $groesse );

	update_post_meta( $id, '_odw_license', $uri( $lizenzen, $f4['lizenz'] ) );
	update_post_meta( $id, '_odw_attribution_text', $f4['namensnennung'] );
	update_post_meta( $id, '_odw_availability', $uri( ODW_Fields::get_availability_options(), $f4['verfuegbarkeit'] ) );
	carbon_set_post_meta(
		$id,
		'odw_extra_distributions',
		array(
			array(
				'access_url' => $f4['zweite_distribution']['url'],
				'format'     => $f4['zweite_distribution']['format'],
				'license'    => $uri( $lizenzen, $f4['zweite_distribution']['lizenz'] ),
			),
		)
	);

	$f5 = json_decode( (string) ( $args[3] ?? '' ), true );
	if ( is_array( $f5 ) ) {
		// Reiter 4 wie am Ende von Folge 5. Daten stehen im Drehbuch so, wie man sie tippt (TT.MM.JJJJ).
		$datum = static function ( string $tt_mm_jjjj ): string {
			$d = DateTime::createFromFormat( 'd.m.Y', $tt_mm_jjjj );
			return $d ? $d->format( 'Y-m-d' ) : '';
		};
		update_post_meta( $id, '_odw_landing_page', $f5['projektseite'] );
		update_post_meta( $id, '_odw_issued', $datum( $f5['erstveroeffentlichung'] ) );
		update_post_meta( $id, '_odw_accrual_periodicity', $uri( ODW_Fields::get_periodicity_options(), $f5['frequenz'] ) );
		update_post_meta( $id, '_odw_political_geocoding_level', $uri( ODW_Fields::get_political_geocoding_level_options(), $f5['verwaltungsebene'] ) );
		update_post_meta( $id, '_odw_geocoding_description', $f5['raum_in_worten'] );
		update_post_meta( $id, '_odw_spatial', $f5['region'] );
		update_post_meta( $id, '_odw_temporal_start', $datum( $f5['zeitraum_von'] ) );
		update_post_meta( $id, '_odw_temporal_end', $datum( $f5['zeitraum_bis'] ) );
		update_post_meta( $id, '_odw_contact_name', $f5['kontakt_name'] );
		update_post_meta( $id, '_odw_contact_email', $f5['kontakt_email'] );
		update_post_meta( $id, '_odw_contact_url', $f5['kontakt_website'] );
		update_post_meta( $id, '_odw_maintainer_name', $f5['pflegende_stelle'] );
	}

	$f6 = json_decode( (string) ( $args[4] ?? '' ), true );
	if ( is_array( $f6 ) ) {
		// Wie am Ende von Folge 6: Medientyp nachgetragen, veröffentlicht.
		update_post_meta( $id, '_odw_media_type', $f6['medientyp'] );
		wp_publish_post( $id );
	}

	ODW_Quality::store( $id, ODW_Quality::calculate( $id ) );
	ODW_Catalog_Filters::sync_index( $id );
}

WP_CLI::log( "Übungsdatensatz als Entwurf angelegt (ID $id)." );
