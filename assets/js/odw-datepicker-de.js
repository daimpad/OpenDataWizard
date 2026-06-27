/**
 * Open Data Wizard — German localization for the Carbon Fields date picker (flatpickr).
 *
 * Registers the German locale on the global flatpickr instance and re-localizes
 * any pickers that were already initialised, so month names, weekday names and
 * the "Select Date" placeholder appear in German.
 */
( function () {
	'use strict';

	var German = {
		weekdays: {
			shorthand: [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ],
			longhand: [ 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' ]
		},
		months: {
			shorthand: [ 'Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez' ],
			longhand: [ 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' ]
		},
		firstDayOfWeek: 1,
		weekAbbreviation: 'KW',
		rangeSeparator: ' bis ',
		scrollTitle: 'Zum Ändern scrollen',
		toggleTitle: 'Zum Umschalten klicken',
		time_24hr: true
	};

	function registerLocale() {
		if ( ! window.flatpickr ) {
			return false;
		}
		window.flatpickr.l10ns = window.flatpickr.l10ns || {};
		window.flatpickr.l10ns.de = German;
		try {
			window.flatpickr.localize( German );
		} catch ( e ) {
			// localize() may not exist on very old builds; l10ns.de is enough.
		}
		return true;
	}

	// flatpickr ships inside the Carbon Fields bundle and may load after this
	// script, so register as soon as it becomes available.
	if ( ! registerLocale() ) {
		var tries = 0;
		var timer = setInterval( function () {
			tries++;
			if ( registerLocale() || tries > 60 ) {
				clearInterval( timer );
			}
		}, 50 );
	}

	function localizeExisting() {
		registerLocale();
		document.querySelectorAll( 'input' ).forEach( function ( input ) {
			if ( input._flatpickr ) {
				input._flatpickr.set( 'locale', German );
			}
			if ( input.placeholder === 'Select Date' || input.placeholder === 'Select date' ) {
				input.placeholder = 'Datum wählen…';
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		localizeExisting();
		// Carbon Fields mounts its React date pickers asynchronously; re-apply a
		// few times to catch instances created after initial paint.
		var passes = 0;
		var rerun  = setInterval( function () {
			passes++;
			localizeExisting();
			if ( passes > 10 ) {
				clearInterval( rerun );
			}
		}, 400 );
	} );
} )();
