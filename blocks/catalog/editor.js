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
	var licenses = cfg.licenses || [];
	var labels   = cfg.labels || {};
	var facets   = [
		{ key: 'theme', label: labels.filterTheme },
		{ key: 'license', label: labels.filterLic },
		{ key: 'format', label: labels.filterFormat },
		{ key: 'sort', label: labels.filterSort },
	];
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

		if ( attributes.license ) {
			lines.push( el( 'p', { key: 'license' }, ( labels.license || '' ) + ': ' + licenseLabel( attributes.license ) ) );
		}

		var active = activeFacets( attributes ).map( function ( key ) {
			for ( var i = 0; i < facets.length; i++ ) {
				if ( facets[ i ].key === key ) {
					return facets[ i ].label;
				}
			}
			return key;
		} );
		if ( active.length || attributes.search ) {
			lines.push(
				el(
					'p',
					{ key: 'visitor' },
					( active.length ? ( labels.filtersOn || '' ) + ': ' + active.join( ', ' ) : '' ) +
					( active.length && attributes.search ? ' · ' : '' ) +
					( attributes.search ? labels.searchOn : '' )
				)
			);
		}

		return el( components.Placeholder, { icon: 'list-view', label: labels.title }, lines );
	}

	function licenseLabel( value ) {
		for ( var i = 0; i < licenses.length; i++ ) {
			if ( licenses[ i ].value === value ) {
				return licenses[ i ].label;
			}
		}
		return value;
	}

	/**
	 * Freigeschaltete Filter, ohne die fest eingestellten — wie im Frontend.
	 *
	 * @param {Object} attributes Block-Attribute.
	 * @return {Array} Schlüssel.
	 */
	function activeFacets( attributes ) {
		var wanted = Array.isArray( attributes.filters ) ? attributes.filters : [];
		return wanted.filter( function ( key ) {
			if ( 'theme' === key && selectedThemes( attributes.theme ).length ) {
				return false;
			}
			if ( 'license' === key && attributes.license ) {
				return false;
			}
			return true;
		} );
	}

	function facetControls( attributes, setAttributes ) {
		var wanted = Array.isArray( attributes.filters ) ? attributes.filters : [];

		return facets.map( function ( facet ) {
			var fixed = ( 'theme' === facet.key && selectedThemes( attributes.theme ).length ) ||
				( 'license' === facet.key && attributes.license );

			return el( components.CheckboxControl, {
				key: facet.key,
				label: facet.label,
				checked: ! fixed && wanted.indexOf( facet.key ) !== -1,
				disabled: !! fixed,
				onChange: function ( isChecked ) {
					var next = wanted.filter( function ( key ) {
						return key !== facet.key;
					} );
					if ( isChecked ) {
						next.push( facet.key );
					}
					// Feste Reihenfolge wie im Formular.
					next = facets
						.map( function ( f ) {
							return f.key;
						} )
						.filter( function ( key ) {
							return next.indexOf( key ) !== -1;
						} );
					setAttributes( { filters: next } );
				},
				__nextHasNoMarginBottom: true,
			} );
		} );
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
						: null,
					licenses.length
						? el(
							components.PanelBody,
							{ title: labels.license, initialOpen: false },
							el( components.SelectControl, {
								label: labels.license,
								value: attributes.license || '',
								options: [ { label: labels.allLicenses, value: '' } ].concat( licenses ),
								onChange: function ( value ) {
									setAttributes( { license: value } );
								},
								__nextHasNoMarginBottom: true,
							} )
						)
						: null,
					el(
						components.PanelBody,
						{ title: labels.visitor, initialOpen: false },
						el( 'p', null, labels.visitorHelp ),
						facetControls( attributes, setAttributes ),
						el( components.ToggleControl, {
							label: labels.search,
							checked: !! attributes.search,
							onChange: function ( value ) {
								setAttributes( { search: !! value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
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
