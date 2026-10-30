<?php
/**
 * Custom Post Type: odw_dataset
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the odw_dataset custom post type.
 *
 * @package OpenDataWizard
 */
class ODW_Post_Types {

	/**
	 * Registers the init hook that triggers CPT registration.
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ) );
	}

	/**
	 * Registriert den Custom Post Type `odw_dataset`.
	 *
	 * Der CPT ist nicht öffentlich (kein Frontend-Permalink), wird aber im
	 * Admin-Bereich angezeigt und über eigene REST-Endpoints ausgeliefert
	 * (`show_in_rest => false`, da wir einen eigenen REST-Namespace verwenden).
	 *
	 * Zugriffssteuerung über Standard-WordPress-Capabilities (edit_posts etc.),
	 * sodass alle Rollen mit Backend-Zugriff Datensätze anlegen können.
	 */
	public static function register(): void {
		$labels = array(
			'name'                  => _x( 'Datensätze', 'Post Type General Name', 'open-data-wizard' ),
			'singular_name'         => _x( 'Datensatz', 'Post Type Singular Name', 'open-data-wizard' ),
			'menu_name'             => __( 'Open Data Wizard', 'open-data-wizard' ),
			'name_admin_bar'        => __( 'Datensatz', 'open-data-wizard' ),
			'add_new'               => __( 'Neuen Datensatz anlegen', 'open-data-wizard' ),
			'add_new_item'          => __( 'Neuen Datensatz anlegen', 'open-data-wizard' ),
			'new_item'              => __( 'Neuer Datensatz', 'open-data-wizard' ),
			'edit_item'             => __( 'Datensatz bearbeiten', 'open-data-wizard' ),
			'view_item'             => __( 'Datensatz ansehen', 'open-data-wizard' ),
			'all_items'             => __( 'Alle Datensätze', 'open-data-wizard' ),
			'search_items'          => __( 'Datensätze suchen', 'open-data-wizard' ),
			'not_found'             => __( 'Keine Datensätze gefunden.', 'open-data-wizard' ),
			'not_found_in_trash'    => __( 'Keine Datensätze im Papierkorb.', 'open-data-wizard' ),
			'featured_image'        => __( 'Vorschaubild', 'open-data-wizard' ),
			'set_featured_image'    => __( 'Vorschaubild festlegen', 'open-data-wizard' ),
			'remove_featured_image' => __( 'Vorschaubild entfernen', 'open-data-wizard' ),
			'use_featured_image'    => __( 'Als Vorschaubild verwenden', 'open-data-wizard' ),
			'archives'              => __( 'Datensatz-Archiv', 'open-data-wizard' ),
			'insert_into_item'      => __( 'In Datensatz einfügen', 'open-data-wizard' ),
			'uploaded_to_this_item' => __( 'Zu diesem Datensatz hochgeladen', 'open-data-wizard' ),
			'items_list'            => __( 'Datensatzliste', 'open-data-wizard' ),
			'items_list_navigation' => __( 'Datensatzliste Navigation', 'open-data-wizard' ),
			'filter_items_list'     => __( 'Datensatzliste filtern', 'open-data-wizard' ),
		);

		$args = array(
			'label'              => __( 'Datensatz', 'open-data-wizard' ),
			'labels'             => $labels,
			'description'        => __( 'DCAT-AP 3.0 konforme Datensatz-Metadaten', 'open-data-wizard' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => false, // REST is handled by custom endpoints in class-rest-api.php.
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 20,
			// Ein Dashicon aus dem WordPress-Vorrat, kein eigenes Logo: Im
			// Admin-Menü sind alle Symbole gleich gezeichnet und werden bis zum
			// Daraufzeigen gedimmt. Eine Marke an dieser Stelle fällt aus der
			// Reihe, statt sich einzufügen.
			'menu_icon'          => 'dashicons-database',
			'supports'           => array( 'title', 'revisions' ),
			'taxonomies'         => array(),
		);

		register_post_type( 'odw_dataset', $args );
	}
}
