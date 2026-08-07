<?php
/**
 * Gutenberg-Block „Datensatz-Karte"
 *
 * Alternative zum Shortcode `[odw_dataset id="123"]`: Statt eine ID
 * abzutippen, wählen Redakteur:innen den Datensatz aus einer Liste.
 *
 * Der Block ist dynamisch — das Markup entsteht beim Ausliefern über
 * ODW_Shortcode::render(). So gibt es genau eine Quelle für die Karte, und
 * Änderungen an ihr wirken auf Shortcode und Block gleichermaßen. Gespeichert
 * wird nur die Datensatz-ID, nicht das gerenderte HTML; ein umbenannter oder
 * gelöschter Datensatz hinterlässt daher keine eingefrorene Kopie im Beitrag.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the dataset card block.
 *
 * @package OpenDataWizard
 */
class ODW_Block {

	/** Obergrenze der Auswahlliste im Editor. */
	private const MAX_CHOICES = 200;

	/**
	 * Registers hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ) );
		// Die Datensatzliste nur im Editor aufbauen — auf jeder Frontend-Seite
		// eine Abfrage laufen zu lassen, die dort niemand braucht, wäre Ballast.
		add_action( 'enqueue_block_editor_assets', array( self::class, 'localize' ) );
	}

	/**
	 * Registers the editor script and the block itself.
	 */
	public static function register(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Eigenes Handle statt des von block.json abgeleiteten: Nur so lässt
		// sich die Datensatzliste zuverlässig daran hängen.
		wp_register_script(
			'odw-dataset-card-editor',
			ODW_PLUGIN_URL . 'blocks/dataset-card/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
			ODW_VERSION,
			true
		);

		register_block_type(
			ODW_PLUGIN_DIR . 'blocks/dataset-card',
			array( 'render_callback' => array( self::class, 'render' ) )
		);
	}

	/**
	 * Passes the list of published datasets and the editor labels to the script.
	 */
	public static function localize(): void {
		$posts = get_posts(
			array(
				'post_type'        => 'odw_dataset',
				'post_status'      => 'publish',
				'numberposts'      => self::MAX_CHOICES,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'suppress_filters' => false,
			)
		);

		$datasets = array();
		foreach ( $posts as $post ) {
			$datasets[] = array(
				'id'    => (int) $post->ID,
				'title' => $post->post_title !== '' ? $post->post_title : sprintf(
					/* translators: %d: post ID of an untitled dataset */
					__( '(ohne Titel, ID %d)', 'open-data-wizard' ),
					(int) $post->ID
				),
			);
		}

		wp_localize_script(
			'odw-dataset-card-editor',
			'odwDatasetBlock',
			array(
				'datasets' => $datasets,
				'labels'   => array(
					'title'    => __( 'Datensatz-Karte', 'open-data-wizard' ),
					'panel'    => __( 'Datensatz', 'open-data-wizard' ),
					'field'    => __( 'Welcher Datensatz soll angezeigt werden?', 'open-data-wizard' ),
					'choose'   => __( '— Bitte wählen —', 'open-data-wizard' ),
					'help'     => __( 'Nur veröffentlichte Datensätze stehen zur Auswahl. Entwürfe erscheinen erst nach dem Veröffentlichen.', 'open-data-wizard' ),
					'none'     => __( 'Es gibt noch keinen veröffentlichten Datensatz. Legen Sie zuerst einen an und veröffentlichen Sie ihn.', 'open-data-wizard' ),
					'pick'     => __( 'Wählen Sie rechts in der Seitenleiste einen Datensatz aus.', 'open-data-wizard' ),
					'rendered' => __( 'wird als Download-Karte ausgegeben', 'open-data-wizard' ),
				),
			)
		);
	}

	/**
	 * Renders the block on the front end.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public static function render( array $attributes ): string {
		$dataset_id = isset( $attributes['datasetId'] ) ? absint( $attributes['datasetId'] ) : 0;

		if ( $dataset_id <= 0 ) {
			return '';
		}

		if ( ! class_exists( 'ODW_Shortcode' ) ) {
			return '';
		}

		// Dieselbe Ausgabe wie beim Shortcode, inklusive dessen Prüfungen auf
		// Beitragstyp und Veröffentlichungsstatus und dem Einbinden des
		// Frontend-Stylesheets.
		return ODW_Shortcode::render( array( 'id' => (string) $dataset_id ) );
	}
}
