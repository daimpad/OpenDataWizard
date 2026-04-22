/* global wp, odwFileUpload */
/**
 * Native WordPress Media Library integration for the Download-Datei meta box.
 *
 * Depends on: jquery, wp.media (loaded via wp_enqueue_media())
 */
( function ( $ ) {
    'use strict';

    var frame;

    var $preview   = $( '#odw-file-preview' );
    var $fileName  = $( '#odw-file-name' );
    var $input     = $( '#odw-file-id-input' );
    var $selectBtn = $( '#odw-file-select-btn' );
    var $removeBtn = $( '#odw-file-remove-btn' );

    // Restore UI state from server-rendered data on page load.
    if ( odwFileUpload.currentId > 0 && odwFileUpload.currentName ) {
        _setHasFile( odwFileUpload.currentName );
    }

    // Open the WordPress Media Library frame.
    $selectBtn.on( 'click', function ( e ) {
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
            $input.val( attachment.id );
            _setHasFile( attachment.filename || attachment.title );
        } );

        frame.open();
    } );

    // Clear the current attachment selection.
    $removeBtn.on( 'click', function ( e ) {
        e.preventDefault();
        $input.val( 0 );
        _setEmpty();
    } );

    /**
     * Setzt die UI in den „Datei ausgewählt"-Zustand.
     *
     * @param {string} name Dateiname oder Attachment-Titel aus der Mediathek.
     */
    function _setHasFile( name ) {
        $fileName.text( name );
        $preview
            .removeClass( 'odw-file-preview--empty' )
            .addClass( 'odw-file-preview--has-file' );
        $removeBtn.prop( 'disabled', false );
    }

    /**
     * Setzt die UI in den leeren Zustand (keine Datei ausgewählt).
     * Wird beim Klick auf „Entfernen" aufgerufen.
     */
    function _setEmpty() {
        $fileName.text( odwFileUpload.labels.noFile );
        $preview
            .removeClass( 'odw-file-preview--has-file' )
            .addClass( 'odw-file-preview--empty' );
        $removeBtn.prop( 'disabled', true );
    }

} )( jQuery );
