/* global wp, odwFileUpload */
/**
 * Native WordPress Media Library integration for the download-file widget.
 *
 * Das Widget steht als Carbon-Fields-html-Feld in Tab 3 (früher: eigene
 * Meta-Box in der Seitenleiste). Carbon Fields hängt seine Felder erst nach
 * DOMContentLoaded ein und kann sie später neu rendern — Handler, die beim
 * Laden direkt an die Elemente gebunden werden, gingen dabei ins Leere.
 * Deshalb durchgehend delegierte Handler an `document` und Elementzugriffe
 * erst im Moment des Klicks.
 *
 * Der Zustand (ausgewählte Datei) wird serverseitig gerendert, siehe
 * ODW_Admin::file_upload_html() — ohne JavaScript bleibt eine bestehende
 * Verknüpfung also erhalten und geht beim Speichern nicht verloren.
 *
 * Depends on: jquery, wp.media (loaded via wp_enqueue_media())
 */
( function ( $ ) {
	'use strict';

	var frame;

	/**
	 * Setzt die UI in den „Datei ausgewählt"-Zustand.
	 *
	 * @param {string} name Dateiname oder Attachment-Titel aus der Mediathek.
	 */
	function setHasFile( name ) {
		$( '#odw-file-name' ).text( name );
		$( '#odw-file-preview' )
			.removeClass( 'odw-file-preview--empty' )
			.addClass( 'odw-file-preview--has-file' );
		$( '#odw-file-remove-btn' ).prop( 'disabled', false );
	}

	/**
	 * Setzt die UI in den leeren Zustand (keine Datei ausgewählt).
	 */
	function setEmpty() {
		$( '#odw-file-name' ).text( odwFileUpload.labels.noFile );
		$( '#odw-file-preview' )
			.removeClass( 'odw-file-preview--has-file' )
			.addClass( 'odw-file-preview--empty' );
		$( '#odw-file-remove-btn' ).prop( 'disabled', true );
	}

	// Mediathek öffnen.
	$( document ).on( 'click', '#odw-file-select-btn', function ( e ) {
		e.preventDefault();

		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media( {
			title:    odwFileUpload.labels.frameTitle,
			button:   { text: odwFileUpload.labels.frameButton },
			multiple: false,
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$( '#odw-file-id-input' ).val( attachment.id );
			setHasFile( attachment.filename || attachment.title );
		} );

		frame.open();
	} );

	// Verknüpfung entfernen.
	$( document ).on( 'click', '#odw-file-remove-btn', function ( e ) {
		e.preventDefault();
		$( '#odw-file-id-input' ).val( 0 );
		setEmpty();
	} );

} )( jQuery );
