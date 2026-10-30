/**
 * Open Data Wizard — Complex Field Fix
 *
 * Entfernt den zu aggressiven stopPropagation()-Handler, der Carbon Fields
 * Event-Handling blockierte und zu unerwartetem Tab-Wechsel führte.
 *
 * Carbon Fields 3.6.9 verwaltet Complex Fields korrekt, wenn wir nicht
 * seine Event-Propagation blockieren.
 */
(function () {
	'use strict';

	// Dieser File ist jetzt leer und dient nur der Dokumentation.
	// Die stopPropagation()-Lösung war zu aggressiv und verursachte
	// den Tab-Wechsel zu Tab 5 (Vorschau), wenn "Add Entry" geklickt wurde.
	//
	// Carbon Fields kann seine eigenen Events korrekt verwalten, wenn wir
	// nicht interferieren.
})();
