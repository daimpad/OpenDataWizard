/* global odwCarbonFieldsL10n */
/**
 * Open Data Wizard — Texte der Mehrfachauswahl, die keine Übersetzung erreicht.
 *
 * Die Mehrfachauswahl von Carbon Fields (Themen, Engagementfelder) baut auf
 * react-select auf. Dessen Platzhalter „Select..." und die Meldung
 * „No options" im Aufklappmenü sind Vorgaben der Bibliothek; Carbon Fields
 * reicht dafür keine Eigenschaft durch, und sie laufen durch keine Textdomain.
 * Alle übrigen Carbon-Fields-Texte kommen regulär über die Übersetzung
 * (siehe includes/class-carbon-fields-l10n.php) und werden hier nicht angefasst.
 *
 * Getauscht wird nur Text, der exakt dem englischen Original entspricht, und
 * nur in den Elementen mit dem Klassenpräfix `cf-multiselect`, das Carbon
 * Fields an react-select übergibt. React schreibt diesen Text nicht erneut,
 * solange er sich nicht ändert; erscheint das Element neu (Menü öffnen,
 * Auswahl leeren), fängt der MutationObserver es wieder ab.
 */
( function () {
	'use strict';

	var map = ( typeof odwCarbonFieldsL10n !== 'undefined' && odwCarbonFieldsL10n.reactSelect ) || {};

	var SELECTOR = '.cf-multiselect__placeholder, .cf-multiselect__menu-notice';

	function localize( el ) {
		var text = el.textContent;
		if ( Object.prototype.hasOwnProperty.call( map, text ) && map[ text ] && map[ text ] !== text ) {
			el.textContent = map[ text ];
		}
	}

	function scan( root ) {
		if ( ! root || root.nodeType !== 1 ) {
			return;
		}
		if ( root.matches( SELECTOR ) ) {
			localize( root );
		}
		root.querySelectorAll( SELECTOR ).forEach( localize );
	}

	function start() {
		if ( Object.keys( map ).length === 0 ) {
			return;
		}
		scan( document.body );
		new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( scan );
			} );
		} ).observe( document.body, { childList: true, subtree: true } );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
