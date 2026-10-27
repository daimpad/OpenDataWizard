/* global wp */
/**
 * Editor-Skript für den Block „Datenkatalog".
 *
 * Wie bei der Datensatz-Karte in schlichtem JavaScript statt JSX — das
 * Projekt hat für den Admin-Bereich keine JS-Build-Kette.
 *
 * Im Editor steht eine Platzhalterkarte mit einer Zusammenfassung der
 * Einstellungen statt einer Live-Vorschau: Eine serverseitige Vorschau bekäme
 * das Frontend-Stylesheet im Editor-Rahmen nicht mit. Die Themenliste kommt aus
 * window.odwCatalogBlock (siehe ODW_Catalog::localize_editor()).
 */
( function ( blocks, element, blockEditor, components ) {
	'use strict';

	var el       = element.createElement;
	var cfg      = window.odwCatalogBlock || {};
	var themes   = cfg.themes || [];
	var labels   = cfg.labels || {};
	var defaults = cfg.defaults || { perPage: 20, excerpt: 30 };
	var limits   = cfg.limits || { perPage: 50, excerpt: 100 };

	/**
	 * Das theme-Attribut ist eine kommagetrennte Liste von EU-Codes — dieselbe
	 * Form wie im Shortcode, damit beide durch dieselbe Prüfung laufen.
	 *
	 * @param {string} value Attributwert.
	 * @return {Array} Ausgewählte Codes.
	 */
	function selectedThemes( value ) {
		return ( value || '' )
			.split( ',' )
			.map( function ( part ) {
				return part.trim();
			} )
			.filter( function ( part ) {
				return part !== '';
			} );
	}

	function themeLabel( code ) {
		for ( var i = 0; i < themes.length; i++ ) {
			if ( themes[ i ].code === code ) {
				return themes[ i ].label;
			}
		}
		return code;
	}

	/**
	 * Ruhige Zusammenfassung dessen, was im Frontend erscheint.
	 *
	 * @param {Object} attributes Block-Attribute.
	 * @return {Object} Element.
	 */
	function preview( attributes ) {
		var chosen = selectedThemes( attributes.theme );
		var lines  = [];

		if ( attributes.title ) {
			lines.push( el( 'p', { key: 'title' }, el( 'strong', null, attributes.title ) ) );
		}

		lines.push( el( 'p', { key: 'rendered' }, labels.rendered ) );

		lines.push(
			el(
				'p',
				{ key: 'summary' },
				( labels.perPage || '' ) + ': ' + ( attributes.perPage || defaults.perPage ) +
				' · ' + ( attributes.orderby === 'modified' ? labels.byModified : labels.byTitle )
			)
		);

		if ( chosen.length ) {
			lines.push(
				el( 'p', { key: 'themes' }, ( labels.themes || '' ) + ': ' + chosen.map( themeLabel ).join( ', ' ) )
			);
		}

		return el( components.Placeholder, { icon: 'list-view', label: labels.title }, lines );
	}

	function themeControls( attributes, setAttributes ) {
		var chosen = selectedThemes( attributes.theme );

		return themes.map( function ( theme ) {
			return el( components.CheckboxControl, {
				key: theme.code,
				label: theme.label,
				checked: chosen.indexOf( theme.code ) !== -1,
				onChange: function ( isChecked ) {
					var next = chosen.filter( function ( code ) {
						return code !== theme.code;
					} );
					if ( isChecked ) {
						next.push( theme.code );
					}
					setAttributes( { theme: next.join( ',' ) } );
				},
				__nextHasNoMarginBottom: true,
			} );
		} );
	}

	blocks.registerBlockType( 'odw/catalog', {
		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps    = blockEditor.useBlockProps();

			return el(
				'div',
				blockProps,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: labels.panel, initialOpen: true },
						el( components.TextControl, {
							label: labels.heading,
							value: attributes.title || '',
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.RangeControl, {
							label: labels.perPage,
							value: attributes.perPage || defaults.perPage,
							min: 1,
							max: limits.perPage,
							onChange: function ( value ) {
								setAttributes( { perPage: value || defaults.perPage } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.SelectControl, {
							label: labels.orderby,
							value: attributes.orderby || 'title',
							options: [
								{ label: labels.byTitle, value: 'title' },
								{ label: labels.byModified, value: 'modified' },
							],
							onChange: function ( value ) {
								setAttributes( { orderby: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.RangeControl, {
							label: labels.excerpt,
							value: typeof attributes.excerpt === 'number' ? attributes.excerpt : defaults.excerpt,
							min: 0,
							max: limits.excerpt,
							onChange: function ( value ) {
								setAttributes( { excerpt: typeof value === 'number' ? value : defaults.excerpt } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.ToggleControl, {
							label: labels.showDemo,
							help: labels.showDemoHelp,
							checked: !! attributes.showDemo,
							onChange: function ( value ) {
								setAttributes( { showDemo: !! value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					),
					themes.length
						? el(
							components.PanelBody,
							{ title: labels.themes, initialOpen: false },
							el( 'p', null, labels.themesHelp ),
							themeControls( attributes, setAttributes )
						)
						: null
				),
				preview( attributes )
			);
		},

		// Dynamischer Block: Das Markup entsteht beim Ausliefern in PHP über
		// dieselbe Funktion wie beim Shortcode.
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components ) );
