/* global odwAdminFields */
/**
 * Open Data Wizard — Admin Field Enhancements
 *
 * Handles:
 *  1. Auto-suggest datalist for license_custom (inside distributions)
 *  2. Auto-suggest datalist for CESSDA topic classification
 *  3. Composite file-size widget (Zahl + Einheit → Bytes in backing field)
 *     Widget HTML is built via JS so no CF html-field is needed inside
 *     the complex field (CF5 React renderer crashes on html fields there).
 */
( function () {
	'use strict';

	var data = ( typeof odwAdminFields !== 'undefined' ) ? odwAdminFields : {};
	var i18n = data.fileSizeWidget || {};

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
	// 2. CESSDA topic widget — visible field shows the human-readable label,
	//    the hidden backing CF field stores the concept URI (DCAT-AP compliant).
	// -------------------------------------------------------------------------
	function initCessdaWidget() {
		var opts = data.cessdaOptions || [];
		if ( ! opts.length ) {
			return;
		}

		var w         = data.cessdaWidget || {};
		var labelToUri = {};
		var uriToLabel = {};
		opts.forEach( function ( o ) {
			labelToUri[ o.label ] = o.value;
			uriToLabel[ o.value ] = o.label;
		} );

		// Shared datalist of human-readable labels.
		var listId = 'odw-cessda-label-datalist';
		if ( ! document.getElementById( listId ) ) {
			var dl = document.createElement( 'datalist' );
			dl.id  = listId;
			opts.forEach( function ( o ) {
				var el   = document.createElement( 'option' );
				el.value = o.label;
				dl.appendChild( el );
			} );
			document.body.appendChild( dl );
		}

		document.querySelectorAll( 'input[data-odw-backing="cessda"]' ).forEach( function ( backing ) {
			if ( backing.dataset.odwCessdaInit ) {
				return;
			}
			backing.dataset.odwCessdaInit = '1';

			var fieldWrapper = backing.closest( '.cf-field' ) || backing.parentElement;
			if ( ! fieldWrapper || ! fieldWrapper.parentNode ) {
				return;
			}

			var wrap   = document.createElement( 'div' );
			wrap.className = 'odw-cessda-widget';

			var lbl = document.createElement( 'label' );
			lbl.className   = 'odw-cessda-label';
			lbl.textContent = w.label || '';

			// The real CF field (with its help text) is hidden, so attach the same
			// ⓘ tooltip here to explain what the CESSDA classification is.
			if ( w.help ) {
				var tipLabel = ( data.helpTip && data.helpTip.label ) || '';
				lbl.appendChild( buildHelpTip( helpTextNode( w.help ), tipLabel ) );
			}

			var input = document.createElement( 'input' );
			input.type        = 'text';
			input.className    = 'odw-cessda-input';
			input.placeholder  = w.placeholder || '';
			input.setAttribute( 'list', listId );

			var hint = document.createElement( 'span' );
			hint.className = 'odw-cessda-hint description';

			wrap.appendChild( lbl );
			wrap.appendChild( input );
			wrap.appendChild( hint );
			fieldWrapper.parentNode.insertBefore( wrap, fieldWrapper );

			function showHint( uri ) {
				hint.textContent = uri ? ( ( w.linkLabel || '' ) + ' ' + uri ) : '';
			}

			// Restore the label from the stored URI on load.
			if ( backing.value ) {
				input.value = uriToLabel[ backing.value ] || backing.value;
				showHint( uriToLabel[ backing.value ] ? backing.value : '' );
			}

			function sync() {
				var label = input.value.trim();
				var uri   = labelToUri[ label ] || '';
				if ( ! uri && /^https?:\/\//.test( label ) ) {
					uri = label;
				}
				setInputValue( backing, uri );
				showHint( uri );
			}

			input.addEventListener( 'input',  sync );
			input.addEventListener( 'change', sync );
		} );
	}

	// -------------------------------------------------------------------------
	// 2b. License description — show a plain-language explanation under the
	//     license <select> whenever a license is chosen.
	// -------------------------------------------------------------------------
	function initLicenseInfo() {
		var descriptions = data.licenseDescriptions || {};

		document.querySelectorAll( '[data-odw-license-info]' ).forEach( function ( info ) {
			if ( info.dataset.odwLicenseInit ) {
				return;
			}
			var field = info.closest( '.cf-field' );
			if ( ! field ) {
				return;
			}

			// The license <select> sits in the preceding .cf-field.
			var select = null;
			var prev   = field.previousElementSibling;
			while ( prev && ! select ) {
				select = prev.querySelector ? prev.querySelector( 'select' ) : null;
				prev   = prev.previousElementSibling;
			}
			if ( ! select ) {
				return;
			}

			info.dataset.odwLicenseInit = '1';

			function update() {
				var text = descriptions[ select.value ];
				if ( text ) {
					info.textContent = text;
					info.hidden      = false;
				} else {
					info.textContent = '';
					info.hidden      = true;
				}
			}

			select.addEventListener( 'change', update );
			update();
		} );
	}

	// -------------------------------------------------------------------------
	// 2c. Spatial (dct:spatial) auto-suggest — curated GeoNames region names.
	// -------------------------------------------------------------------------
	function initSpatialAutosuggest() {
		if ( ! data.spatialOptions || ! data.spatialOptions.length ) {
			return;
		}
		document.querySelectorAll( 'input[data-odw-autosuggest="spatial"]' ).forEach( function ( input ) {
			attachDatalist( input, 'odw-spatial-datalist', data.spatialOptions );
		} );
	}

	// -------------------------------------------------------------------------
	// 2d. Generic vocabulary auto-suggest — any input[data-odw-vocab="<id>"]
	// pulls its options from data.vocabularies[<id>] (bundled JSON vocab).
	// -------------------------------------------------------------------------
	function attachVocab( input ) {
		var id = input.getAttribute( 'data-odw-vocab' );
		if ( ! id ) {
			return;
		}
		var vocab = ( data.vocabularies || {} )[ id ];
		if ( ! vocab || ! vocab.length ) {
			return;
		}
		// Suggest the human-readable label as the field value (so the user can
		// type a name to filter in every browser and never sees a raw URI). The
		// label→URI resolution happens server-side in odw_resolve_vocab_uri().
		var opts = vocab.map( function ( o ) {
			return { value: o.label, label: '' };
		} );
		attachDatalist( input, 'odw-vocab-datalist-' + id, opts );
	}

	function initVocabAutosuggest() {
		document.querySelectorAll( 'input[data-odw-vocab]' ).forEach( attachVocab );
	}

	// -------------------------------------------------------------------------
	// 2e. "Erweiterte Angaben für Profis" — collapse all Tab-4 fields after the
	// toggle into an opt-in section. Progressive enhancement: if JS is absent,
	// every field simply stays visible. State persists in sessionStorage.
	// -------------------------------------------------------------------------
	// Fields belonging to a section = every .cf-field after the heading until
	// the next section heading (or the end of the tab).
	function sectionFields( wrapper ) {
		var fields = [];
		var node   = wrapper.nextElementSibling;
		while ( node ) {
			if ( node.classList && node.classList.contains( 'cf-field' ) ) {
				if ( node.querySelector && node.querySelector( '[data-odw-section-toggle]' ) ) {
					break;
				}
				fields.push( node );
			}
			node = node.nextElementSibling;
		}
		return fields;
	}

	function applySection( btn, fields, open ) {
		fields.forEach( function ( f ) {
			f.classList.toggle( 'odw-pro-collapsed', ! open );
		} );
		btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		btn.classList.toggle( 'odw-section-open', open );
	}

	function initProSection() {
		document.querySelectorAll( '[data-odw-section-toggle]' ).forEach( function ( btn ) {
			var wrapper = btn.closest( '.cf-field' );
			if ( ! wrapper || ! wrapper.parentNode ) {
				return;
			}
			var key  = 'odw_sec_' + ( btn.getAttribute( 'data-odw-section-toggle' ) || '' );
			var open = sessionStorage.getItem( key ) === '1'; // Default: collapsed.
			applySection( btn, sectionFields( wrapper ), open );

			if ( ! btn.getAttribute( 'data-odw-sec-bound' ) ) {
				btn.setAttribute( 'data-odw-sec-bound', '1' );
				btn.addEventListener( 'click', function () {
					var nowOpen = sessionStorage.getItem( key ) !== '1';
					try {
						sessionStorage.setItem( key, nowOpen ? '1' : '0' );
					} catch ( e ) {} // eslint-disable-line no-empty
					applySection( btn, sectionFields( wrapper ), nowOpen );
				} );
			}
		} );
	}

	// -------------------------------------------------------------------------
	// 3. File-size composite widget
	// Built dynamically from JS so no CF html-field is needed inside the
	// complex field. Finds every hidden [data-odw-backing] input and inserts
	// a visible composite widget before its CF field wrapper.
	// -------------------------------------------------------------------------
	function buildWidgetHtml() {
		var label       = i18n.label       || 'Dateigröße';
		var optional    = i18n.optional    || '(optional)';
		var placeholder = i18n.placeholder || 'z. B. 2.5';
		var ariaNumber  = i18n.ariaNumber  || 'Dateigröße Zahlenwert';
		var ariaUnit    = i18n.ariaUnit    || 'Einheit';
		var helpText    = i18n.helpText    || '1 MB = 1.024 KB';

		return '<div class="odw-filesize-widget">' +
			'<label class="odw-filesize-label">' + label +
				' <span class="odw-filesize-optional">' + optional + '</span>' +
			'</label>' +
			'<div class="odw-filesize-row">' +
				'<input type="number" class="odw-filesize-number" min="0" step="0.1"' +
					' placeholder="' + placeholder + '" aria-label="' + ariaNumber + '">' +
				'<select class="odw-filesize-unit" aria-label="' + ariaUnit + '">' +
					'<option value="KB">KB</option>' +
					'<option value="MB" selected>MB</option>' +
					'<option value="GB">GB</option>' +
				'</select>' +
				'<span class="odw-filesize-hint description"></span>' +
			'</div>' +
			'<p class="odw-filesize-helptext description">' + helpText + '</p>' +
		'</div>';
	}

	function wireWidget( widget, backing ) {
		var numberInput = widget.querySelector( '.odw-filesize-number' );
		var unitSelect  = widget.querySelector( '.odw-filesize-unit' );
		var hint        = widget.querySelector( '.odw-filesize-hint' );
		var factors     = { KB: 1024, MB: 1048576, GB: 1073741824 };

		function updateBacking() {
			var raw  = numberInput.value.trim();

			// Empty is valid — the field is optional. Clear the stored value.
			if ( '' === raw ) {
				numberInput.setCustomValidity( '' );
				if ( hint ) {
					hint.textContent = '';
				}
				if ( backing ) {
					setInputValue( backing, '' );
				}
				return;
			}

			var num  = parseFloat( raw );
			var unit = unitSelect.value;
			if ( isNaN( num ) || num < 0 ) {
				numberInput.setCustomValidity( i18n.invalid || 'Bitte einen positiven Wert eingeben.' );
				if ( hint ) {
					hint.textContent = '';
				}
				return;
			}
			numberInput.setCustomValidity( '' );

			var bytes = Math.round( num * ( factors[ unit ] || 1048576 ) );

			if ( hint ) {
				hint.textContent = '= ' + bytes.toLocaleString( i18n.locale || undefined ) + ' ' + ( i18n.bytes || 'Bytes' );
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
				var displayUnit, displayVal;
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
	}

	function initFileSizeWidget( backing ) {
		if ( ! backing || backing.dataset.odwWidgetInit ) {
			return;
		}
		backing.dataset.odwWidgetInit = '1';

		// Find the CF field wrapper that contains the backing input.
		// CF5 uses .cf-field as the wrapper; fall back to closest div.
		var fieldWrapper = backing.closest( '.cf-field' ) ||
		                   backing.closest( 'div[class]' ) ||
		                   backing.parentElement;

		if ( ! fieldWrapper || ! fieldWrapper.parentNode ) {
			return;
		}

		var widget = document.createElement( 'div' );
		widget.innerHTML = buildWidgetHtml();
		var widgetEl = widget.firstElementChild;

		fieldWrapper.parentNode.insertBefore( widgetEl, fieldWrapper );
		wireWidget( widgetEl, backing );
	}

	function initFileSizeWidgets() {
		document.querySelectorAll( 'input[data-odw-backing="byte_size"]' ).forEach( function ( backing ) {
			initFileSizeWidget( backing );
		} );
	}

	// -------------------------------------------------------------------------
	// 4. Help tooltips — turn each field's inline help text into an ⓘ popup next
	// to the label. Declutters the form while keeping the technical DCAT-AP
	// label one hover/click away. Progressive enhancement: without JS the help
	// text simply stays inline.
	// -------------------------------------------------------------------------
	// One document-level handler closes any open click-tooltip on outside tap.
	function ensureTipDocHandler() {
		if ( document.body.dataset.odwTipDocBound ) {
			return;
		}
		document.body.dataset.odwTipDocBound = '1';
		document.addEventListener( 'click', function ( e ) {
			document.querySelectorAll( '.odw-help-tip-wrap.is-open' ).forEach( function ( w ) {
				if ( ! w.contains( e.target ) ) {
					w.classList.remove( 'is-open' );
					var b = w.querySelector( '.odw-help-tip' );
					if ( b ) {
						b.setAttribute( 'aria-expanded', 'false' );
					}
				}
			} );
		} );
	}

	// Build an ⓘ tooltip wrapper around a content node. Reused by the field help
	// tooltips and the JS-built widgets (e.g. CESSDA) so they look identical.
	function buildHelpTip( contentNode, tipLabel ) {
		ensureTipDocHandler();

		var wrap = document.createElement( 'span' );
		wrap.className = 'odw-help-tip-wrap';

		var btn = document.createElement( 'button' );
		btn.type     = 'button';
		btn.className = 'odw-help-tip';
		btn.setAttribute( 'aria-label', tipLabel || 'Hilfe' );
		btn.setAttribute( 'aria-expanded', 'false' );
		btn.innerHTML = '<span aria-hidden="true">i</span>';

		var pop = document.createElement( 'span' );
		pop.className = 'odw-help-pop';
		pop.setAttribute( 'role', 'tooltip' );
		pop.appendChild( contentNode );

		wrap.appendChild( btn );
		wrap.appendChild( pop );

		// Click toggles for touch/keyboard; hover/focus is handled in CSS.
		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var open = wrap.classList.toggle( 'is-open' );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		return wrap;
	}

	// Build a popup content node from a plain (multi-line) help string.
	function helpTextNode( text ) {
		var span = document.createElement( 'span' );
		span.className   = 'odw-help-pop__content';
		span.textContent = text; // CSS white-space:pre-line preserves line breaks.
		return span;
	}

	function initHelpTooltips() {
		var tip = data.helpTip || {};
		document.querySelectorAll( '.cf-field' ).forEach( function ( field ) {
			if ( field.dataset.odwTipInit ) {
				return;
			}
			// Only this field's own help — ":scope >" prevents a container/complex
			// field from grabbing a nested child field's help element.
			var help = field.querySelector( ':scope > .cf-field__help' );
			if ( ! help || ! ( help.textContent || '' ).trim() ) {
				return;
			}
			// Anchor the ⓘ inside the label so it sits inline with the question
			// text (the head/label is block-level, so a sibling would wrap below).
			var anchor = field.querySelector( ':scope > .cf-field__head > .cf-field__label' ) ||
				field.querySelector( ':scope > .cf-field__head' ) ||
				field.querySelector( ':scope > label' );
			if ( ! anchor ) {
				return;
			}
			// The attribute both guards re-processing and drives the CSS rule that
			// hides the inline help (robust even if React re-renders the <em>).
			field.dataset.odwTipInit = '1';

			// Clone (not move) the React-owned help node into the popup so its
			// exact content/formatting is preserved while React keeps owning the
			// original, which the CSS rule hides from the inline flow.
			var content = help.cloneNode( true );
			content.className = 'odw-help-pop__content';

			anchor.appendChild( buildHelpTip( content, tip.label ) );
		} );
	}

	// -------------------------------------------------------------------------
	// 4b. "Mehr erfahren" — an expandable panel per field with the catalog's
	// DCAT-AP and plain-language long descriptions (config/field-catalog.php via
	// odwAdminFields.fieldCatalog). Additive: the translatable inline help stays.
	// -------------------------------------------------------------------------
	function moreBlock( label, text ) {
		var p = document.createElement( 'p' );
		p.className = 'odw-field-more__block';
		if ( label ) {
			var strong = document.createElement( 'strong' );
			strong.textContent = label + ': ';
			p.appendChild( strong );
		}
		p.appendChild( document.createTextNode( text ) );
		return p;
	}

	function initFieldMore() {
		var cfg     = data.fieldMore || {};
		var catalog = data.fieldCatalog || [];
		catalog.forEach( function ( f ) {
			if ( ! f.meta_key ) {
				return;
			}
			// Match the Carbon Fields compact input by its exact meta-key suffix,
			// e.g. carbon_fields_compact_input[_odw_access_url].
			var input = document.querySelector( '[name$="[' + f.meta_key + ']"]' );
			if ( ! input ) {
				return;
			}
			var field = input.closest( '.cf-field' );
			if ( ! field || field.dataset.odwMoreInit ) {
				return;
			}
			field.dataset.odwMoreInit = '1';

			var details = document.createElement( 'details' );
			details.className = 'odw-field-more';

			var summary = document.createElement( 'summary' );
			summary.textContent = cfg.toggle || 'Mehr erfahren';
			details.appendChild( summary );

			var body = document.createElement( 'div' );
			body.className = 'odw-field-more__body';
			if ( f.desc_dcat ) {
				body.appendChild( moreBlock( cfg.dcat, f.desc_dcat ) );
			}
			if ( f.desc_human ) {
				body.appendChild( moreBlock( cfg.plain, f.desc_human ) );
			}
			details.appendChild( body );
			field.appendChild( details );
		} );
	}

	// -------------------------------------------------------------------------
	// 5. Live wizard preview (Tab 5) — a completeness checklist + summary card
	// that update as the user types, without saving. The field list and labels
	// come from PHP (odwAdminFields.livePreview). Progressive enhancement: the
	// panel stays hidden when JS is off and the saved JSON-LD remains the view.
	// -------------------------------------------------------------------------
	function fieldInput( key ) {
		if ( 'title' === key ) {
			return document.getElementById( 'title' );
		}
		// Carbon Fields compact inputs are named carbon_fields_compact_input[_odw_x].
		return document.querySelector( '[name$="[_' + key + ']"]' );
	}

	function fieldValue( key ) {
		var el = fieldInput( key );
		if ( ! el ) {
			return '';
		}
		if ( 'SELECT' === el.tagName && el.selectedIndex >= 0 ) {
			var opt = el.options[ el.selectedIndex ];
			// Skip empty placeholder options ("— bitte wählen —" style: empty value).
			if ( ! el.value ) {
				return '';
			}
			return ( opt.text || el.value ).trim();
		}
		return ( el.value || '' ).trim();
	}

	function initLivePreview() {
		var cfg = data.livePreview;
		if ( ! cfg || ! cfg.fields || ! cfg.fields.length ) {
			return;
		}
		var panel = document.querySelector( '[data-odw-live-preview]' );
		if ( ! panel || panel.dataset.odwLiveInit ) {
			return;
		}
		panel.dataset.odwLiveInit = '1';
		panel.hidden = false;

		var checklist = panel.querySelector( '[data-odw-live-checklist]' );
		var card      = panel.querySelector( '[data-odw-live-card]' );
		var progress  = panel.querySelector( '[data-odw-live-progress]' );

		function refresh() {
			var requiredTotal = 0;
			var requiredDone  = 0;
			var checkHtml = '';
			var cardHtml  = '';

			cfg.fields.forEach( function ( f ) {
				var val    = fieldValue( f.key );
				var filled = '' !== val;

				if ( f.required ) {
					requiredTotal++;
					if ( filled ) {
						requiredDone++;
					}
					checkHtml += '<li class="odw-live-checklist__item ' +
						( filled ? 'is-done' : 'is-missing' ) + '">' +
						'<span class="odw-live-check" aria-hidden="true">' +
						( filled ? '✓' : '○' ) + '</span>' +
						'<span class="odw-live-check__label">' + escapeHtml( f.label ) + '</span>' +
						'</li>';
				}

				if ( f.card ) {
					var display = filled
						? '<dd>' + escapeHtml( val ) + '</dd>'
						: '<dd class="odw-live-card__empty">' + escapeHtml( cfg.empty || '' ) + '</dd>';
					cardHtml += '<dt>' + escapeHtml( f.label ) + '</dt>' + display;
				}
			} );

			if ( checklist ) {
				checklist.innerHTML = checkHtml;
			}
			if ( card ) {
				card.innerHTML = cardHtml;
			}
			if ( progress ) {
				if ( requiredTotal > 0 && requiredDone === requiredTotal ) {
					progress.textContent = cfg.complete || '';
					progress.className   = 'odw-live-progress is-complete';
				} else {
					progress.textContent = ( cfg.progressTmpl || '%1$d / %2$d' )
						.replace( '%1$d', requiredDone ).replace( '%2$d', requiredTotal );
					progress.className   = 'odw-live-progress';
				}
			}
		}

		// Update on any field change anywhere in the form (events bubble).
		document.addEventListener( 'input',  refresh );
		document.addEventListener( 'change', refresh );
		refresh();
	}

	function escapeHtml( str ) {
		return String( str ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
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
					node.querySelectorAll( 'input[data-odw-backing="byte_size"]' ).forEach( function ( backing ) {
						initFileSizeWidget( backing );
					} );
					node.querySelectorAll( 'input[data-odw-vocab]' ).forEach( attachVocab );
					initHelpTooltips();
					initFieldMore();
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
		initLicenseInfo();
		initCessdaWidget();
		initSpatialAutosuggest();
		initVocabAutosuggest();
		initProSection();
		initFileSizeWidgets();
		initHelpTooltips();
		initFieldMore();
		initLivePreview();
		observeNewGroups();

		// Carbon Fields mounts fields asynchronously; re-run widget inits a few
		// times so they attach once the inputs exist in the DOM.
		var passes = 0;
		var rerun  = setInterval( function () {
			passes++;
			initLicenseInfo();
			initCessdaWidget();
			initSpatialAutosuggest();
			initVocabAutosuggest();
			initProSection();
			initFileSizeWidgets();
			initHelpTooltips();
			initFieldMore();
			initLivePreview();
			if ( passes > 10 ) {
				clearInterval( rerun );
			}
		}, 400 );
	} );

} )();
