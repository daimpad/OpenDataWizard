/* global wp */
/**
 * Editor-Skript für den Block „Datensatz-Karte".
 *
 * Bewusst in schlichtem JavaScript statt JSX: Das Projekt hat keine
 * JS-Build-Kette für den Admin-Bereich, und eine allein für diesen Block
 * einzuführen wäre viel Apparat für wenig Ertrag. Der Preis ist das
 * ausgeschriebene createElement() statt der gewohnten JSX-Schreibweise.
 *
 * Die Auswahlliste kommt aus window.odwDatasetBlock (siehe ODW_Block): Der
 * Custom Post Type ist absichtlich nicht über die WP-REST-API exponiert —
 * dafür gibt es die eigenen Endpunkte —, also lässt er sich im Editor nicht
 * über den Core-Datenspeicher abfragen.
 *
 * Im Editor steht eine Platzhalterkarte statt einer Live-Vorschau. Eine
 * serverseitig gerenderte Vorschau bekäme das Frontend-Stylesheet im
 * Editor-Rahmen nicht mit und sähe dort kaputt aus — eine ehrliche
 * Platzhalterkarte ist verlässlicher als eine halbe Vorschau.
 */
( function ( blocks, element, blockEditor, components ) {
	'use strict';

	var el       = element.createElement;
	var cfg      = window.odwDatasetBlock || { datasets: [], labels: {} };
	var datasets = cfg.datasets || [];
	var labels   = cfg.labels || {};

	function findDataset( id ) {
		for ( var i = 0; i < datasets.length; i++ ) {
			if ( datasets[ i ].id === id ) {
				return datasets[ i ];
			}
		}
		return null;
	}

	function selectOptions() {
		var options = [ { label: labels.choose || '', value: 0 } ];
		for ( var i = 0; i < datasets.length; i++ ) {
			options.push( { label: datasets[ i ].title, value: datasets[ i ].id } );
		}
		return options;
	}

	/**
	 * Die Karte im Editor — kein Abbild des Frontends, sondern eine ruhige
	 * Zusammenfassung dessen, was veröffentlicht wird.
	 *
	 * @param {Object} dataset Ausgewählter Datensatz oder null.
	 * @return {Object} Element.
	 */
	function preview( dataset, onChange ) {
		// Durchgehend die Placeholder-Komponente von WordPress: Sie bringt ihr
		// Aussehen selbst mit. Eigenes CSS wäre im Beitragseditor wirkungslos —
		// assets/css/admin.css lädt nur auf den Datensatz-Bildschirmen.
		if ( ! datasets.length ) {
			return el(
				components.Placeholder,
				{ icon: 'database', label: labels.title },
				el( 'p', null, labels.none )
			);
		}

		if ( ! dataset ) {
			return el(
				components.Placeholder,
				{ icon: 'database', label: labels.title, instructions: labels.pick },
				el( components.SelectControl, {
					value: 0,
					options: selectOptions(),
					onChange: onChange,
					__nextHasNoMarginBottom: true,
				} )
			);
		}

		return el(
			components.Placeholder,
			{ icon: 'database', label: labels.title },
			el( 'p', null, el( 'strong', null, dataset.title ) ),
			el( 'p', null, labels.rendered )
		);
	}

	blocks.registerBlockType( 'odw/dataset-card', {
		edit: function ( props ) {
			var blockProps = blockEditor.useBlockProps();
			var id         = props.attributes.datasetId || 0;
			var dataset    = findDataset( id );

			function choose( value ) {
				props.setAttributes( { datasetId: parseInt( value, 10 ) || 0 } );
			}

			return el(
				'div',
				blockProps,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: labels.panel, initialOpen: true },
						el( components.SelectControl, {
							label: labels.field,
							value: id,
							options: selectOptions(),
							help: datasets.length ? labels.help : labels.none,
							onChange: choose,
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				preview( dataset, choose )
			);
		},

		// Dynamischer Block: Das Markup entsteht beim Ausliefern in PHP über
		// dieselbe Funktion wie beim Shortcode, damit es nur eine Quelle gibt.
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components ) );
