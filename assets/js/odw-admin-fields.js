/* global odwAdminFields */
/**
 * Open Data Wizard — Admin Field Enhancements
 *
 * Handles:
 *  1. Auto-suggest datalist for license_custom (inside distributions)
 *  2. Auto-suggest datalist for CESSDA topic classification
 *  3. Composite file-size widget (Zahl + Einheit → Bytes in backing field)
 */
( function () {
	'use strict';

	var data = ( typeof odwAdminFields !== 'undefined' ) ? odwAdminFields : {};

	// -------------------------------------------------------------------------
	// Utility: set a value on a React-controlled or plain input
	// -------------------------------------------------------------------------
	function setInputValue( input, value ) {
		if ( ! input ) {
			return;
		}
		var nativeSetter = Object.getOwnPropertyDescriptor( window.HTMLInputElement.prototype, 'value' );
		if ( nativeSetter && nativeSetter.set ) {
			nativeSetter.set.call( input, value );
		} else {
			input.value = value;
		}
		input.dispatchEvent( new Event( 'input',  { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	// -------------------------------------------------------------------------
	// Attach datalist to a single input
	// -------------------------------------------------------------------------
	function attachDatalist( input, listId, options ) {
		if ( ! input || input.getAttribute( 'list' ) ) {
			return;
		}
		var existing = document.getElementById( listId );
		if ( ! existing ) {
			var dl = document.createElement( 'datalist' );
			dl.id  = listId;
			options.forEach( function ( opt ) {
				var el    = document.createElement( 'option' );
				el.value  = opt.value;
				el.label  = opt.label;
				dl.appendChild( el );
			} );
			document.body.appendChild( dl );
		}
		input.setAttribute( 'list', listId );
	}

	// -------------------------------------------------------------------------
	// 1. License auto-suggest (inside distribution complex groups)
	// -------------------------------------------------------------------------
	function initLicenseAutosuggest() {
		if ( ! data.licenseOptions || ! data.licenseOptions.length ) {
			return;
		}
		document.querySelectorAll( 'input[data-odw-autosuggest="license_custom"]' ).forEach( function ( input ) {
			attachDatalist( input, 'odw-license-datalist', data.licenseOptions );
		} );
	}

	// -------------------------------------------------------------------------
	// 2. CESSDA topic auto-suggest (standalone field)
	// -------------------------------------------------------------------------
	function initCessdaAutosuggest() {
		if ( ! data.cessdaOptions || ! data.cessdaOptions.length ) {
			return;
		}
		document.querySelectorAll( 'input[data-odw-autosuggest="cessda"]' ).forEach( function ( input ) {
			attachDatalist( input, 'odw-cessda-datalist', data.cessdaOptions );
		} );
	}

	// -------------------------------------------------------------------------
	// 3. File-size composite widget
	// Finds every .odw-filesize-widget and wires up its number+unit inputs
	// to write computed bytes into the backing CF field.
	// -------------------------------------------------------------------------
	function initFileSizeWidgets() {
		document.querySelectorAll( '.odw-filesize-widget' ).forEach( function ( widget ) {
			var numberInput = widget.querySelector( '.odw-filesize-number' );
			var unitSelect  = widget.querySelector( '.odw-filesize-unit' );
			var hint        = widget.querySelector( '.odw-filesize-hint' );

			// Find the backing CF input (data-odw-backing="byte_size" in same group).
			var group       = widget.closest( '.carbon-fields-group, .carbon-complex-group, .cf-group, [data-group]' );
			var backing     = group
				? group.querySelector( 'input[data-odw-backing="byte_size"]' )
				: null;

			if ( ! numberInput || ! unitSelect ) {
				return;
			}

			var factors = { KB: 1024, MB: 1048576, GB: 1073741824 };

			function updateBacking() {
				var num  = parseFloat( numberInput.value );
				var unit = unitSelect.value;
				if ( isNaN( num ) || num < 0 ) {
					numberInput.setCustomValidity( 'Bitte einen positiven Wert eingeben.' );
					if ( hint ) {
						hint.textContent = '';
					}
					return;
				}
				numberInput.setCustomValidity( '' );

				var bytes = Math.round( num * ( factors[ unit ] || 1048576 ) );

				if ( hint ) {
					hint.textContent = '= ' + bytes.toLocaleString( 'de-DE' ) + ' Bytes';
				}

				if ( backing ) {
					setInputValue( backing, String( bytes ) );
				}
			}

			numberInput.addEventListener( 'input',  updateBacking );
			numberInput.addEventListener( 'change', updateBacking );
			unitSelect.addEventListener(  'change', updateBacking );

			// Restore display value from stored bytes on page load.
			if ( backing && backing.value && backing.value !== '0' && backing.value !== '' ) {
				var stored = parseInt( backing.value, 10 );
				if ( ! isNaN( stored ) && stored > 0 ) {
					var displayUnit = 'MB';
					var displayVal;
					if ( stored >= 1073741824 ) {
						displayUnit = 'GB';
						displayVal  = stored / 1073741824;
					} else if ( stored >= 1048576 ) {
						displayUnit = 'MB';
						displayVal  = stored / 1048576;
					} else {
						displayUnit = 'KB';
						displayVal  = stored / 1024;
					}
					numberInput.value = parseFloat( displayVal.toFixed( 2 ) );
					unitSelect.value  = displayUnit;
					updateBacking();
				}
			}
		} );
	}

	// -------------------------------------------------------------------------
	// Observe DOM for dynamically added CF complex groups (e.g. new distribution)
	// -------------------------------------------------------------------------
	function observeNewGroups() {
		var observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					// Re-run inits for any newly added groups.
					node.querySelectorAll( 'input[data-odw-autosuggest="license_custom"]' ).forEach( function ( input ) {
						attachDatalist( input, 'odw-license-datalist', data.licenseOptions || [] );
					} );
					node.querySelectorAll( '.odw-filesize-widget' ).forEach( function () {
						initFileSizeWidgets();
					} );
				} );
			} );
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	}

	// -------------------------------------------------------------------------
	// Boot on DOMContentLoaded
	// -------------------------------------------------------------------------
	document.addEventListener( 'DOMContentLoaded', function () {
		initLicenseAutosuggest();
		initCessdaAutosuggest();
		initFileSizeWidgets();
		observeNewGroups();
	} );

} )();
