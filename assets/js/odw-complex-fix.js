/**
 * Open Data Wizard — Complex Field Collapse Fix
 *
 * Verhindert, dass beim Klick auf "Add Entry" in Complex Fields
 * das Collapse-Toggle versehentlich mit ausgelöst wird (Event-Bubbling-Problem).
 */
(function () {
	'use strict';

	function preventCollapseOnAddEntry() {
		// Beobachte alle Carbon Fields Complex Field action buttons.
		// "Add Entry" Button sitzt in .cf-complex__actions
		document.addEventListener( 'click', function ( e ) {
			// Suche nach dem direkten Button-Element (Add Entry Button)
			var button = e.target.closest( '.cf-complex__actions button' );
			if ( ! button ) {
				return;
			}

			// Stoppe Event-Bubbling, damit der Click nicht zu
			// einem Collapse-Toggle bubbelt
			e.stopPropagation();
		}, true ); // Use capture phase, damit es vor anderen Listenern läuft
	}

	// Starte sobald DOM ready ist
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', preventCollapseOnAddEntry );
	} else {
		preventCollapseOnAddEntry();
	}
})();
