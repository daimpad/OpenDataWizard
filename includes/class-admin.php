<?php
/**
 * Admin: Listenansicht, Spalten, Assets, Help Tabs, Download-Datei Meta Box
 *
 * Verantwortlichkeiten:
 *  - Listenansicht (Spalten, Sortierung, Status-Filter)
 *  - Admin-Assets (CSS + wizard-tabs.js + odw-file-upload.js)
 *  - Download-Datei Sidebar-Meta-Box: nativer wp.media Upload-Button,
 *    Nonce-gesichertes Speichern, Auto-Berechnung _odw_file_size/_odw_file_format
 *  - Help Tabs auf dem Edit-Screen (DCAT-AP Feldbeschreibungen, API-Doku)
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the admin UI: list table columns, sortable meta ordering, assets, help tabs, and file meta box.
 *
 * @package OpenDataWizard
 */
class ODW_Admin {

	/**
	 * Registers all WordPress hooks for the admin UI.
	 */
	public static function init(): void {
		add_filter( 'manage_odw_dataset_posts_columns', array( self::class, 'set_columns' ) );
		add_action( 'manage_odw_dataset_posts_custom_column', array( self::class, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-odw_dataset_sortable_columns', array( self::class, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( self::class, 'handle_meta_orderby' ) );
		add_action( 'restrict_manage_posts', array( self::class, 'status_filter_dropdown' ) );
		add_filter( 'parse_query', array( self::class, 'apply_status_filter' ) );
		add_action( 'admin_menu', array( self::class, 'register_introduction_page' ), 5 );
		add_action( 'admin_menu', array( self::class, 'register_batch_import_page' ), 6 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'add_meta_boxes', array( self::class, 'register_help_tabs' ) );
		add_action( 'load-post.php', array( self::class, 'register_help_tabs' ) );
		add_action( 'load-post-new.php', array( self::class, 'register_help_tabs' ) );
		add_action( 'add_meta_boxes', array( self::class, 'register_file_meta_box' ) );
		add_action( 'save_post_odw_dataset', array( self::class, 'save_file_attachment' ), 20, 2 );
		add_action( 'wp_ajax_odw_batch_import_preview', array( self::class, 'ajax_batch_import_preview' ) );
		add_action( 'wp_ajax_odw_batch_import_execute', array( self::class, 'ajax_batch_import_execute' ) );
	}

	/**
	 * Define list table columns.
	 *
	 * @param array<string, string> $columns Default columns.
	 * @return array<string, string> Modified columns.
	 */
	public static function set_columns( array $columns ): array {
		$new_columns = array();

		$new_columns['cb']            = $columns['cb'] ?? '<input type="checkbox">';
		$new_columns['title']         = __( 'Titel', 'open-data-wizard' );
		$new_columns['odw_license']   = __( 'Lizenz', 'open-data-wizard' );
		$new_columns['odw_theme']     = __( 'Thema', 'open-data-wizard' );
		$new_columns['odw_quality']   = __( 'Qualität', 'open-data-wizard' );
		$new_columns['odw_status']    = __( 'Status', 'open-data-wizard' );
		$new_columns['odw_modified']  = __( 'Änderungsdatum', 'open-data-wizard' );
		$new_columns['odw_shortcode'] = __( 'Shortcode', 'open-data-wizard' );

		return $new_columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Post ID.
	 */
	public static function render_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'odw_license':
				$lic = (string) carbon_get_post_meta( $post_id, 'odw_license' );
				if ( 'sonstige' === $lic ) {
					$lic = (string) carbon_get_post_meta( $post_id, 'odw_license_custom' );
				}
				echo esc_html( '' !== $lic ? ODW_Fields::get_license_label( $lic ) : '—' );
				break;

			case 'odw_theme':
				$theme = carbon_get_post_meta( $post_id, 'odw_theme' );
				echo esc_html( (string) $theme );
				break;

			case 'odw_status':
				$post   = get_post( $post_id );
				$status = $post ? $post->post_status : '';

				if ( 'publish' === $status ) {
					echo '<span class="odw-status-badge odw-status-badge--published">' . esc_html__( 'Veröffentlicht', 'open-data-wizard' ) . '</span>';
				} else {
					echo '<span class="odw-status-badge odw-status-badge--draft">' . esc_html__( 'Entwurf', 'open-data-wizard' ) . '</span>';
				}
				break;

			case 'odw_quality':
				$quality = ODW_Quality::get( $post_id );

				if ( '' === $quality['level'] ) {
					echo '<span class="odw-quality-badge odw-quality--unknown" title="' . esc_attr__( 'Noch nicht berechnet', 'open-data-wizard' ) . '">—</span>';
				} else {
					$level      = $quality['level'];
					$score      = $quality['score'];
					$label      = ODW_Quality::get_level_label( $level );
					$title_attr = sprintf( '%s · %d/100 %s', $label, $score, __( 'Punkte', 'open-data-wizard' ) );
					printf(
						'<span class="odw-quality-badge odw-quality--%s" title="%s"><span class="odw-quality-dot" aria-hidden="true">●</span> %d</span>',
						esc_attr( $level ),
						esc_attr( $title_attr ),
						(int) $score
					);
				}
				break;

			case 'odw_modified':
				$modified = get_post_meta( $post_id, '_odw_modified', true );
				echo esc_html( $modified ? $modified : '—' );
				break;

			case 'odw_shortcode':
				$shortcode = '[odw_dataset id="' . $post_id . '"]';
				printf(
					'<input type="text" class="odw-shortcode-input" readonly value="%s" onclick="this.select();" title="%s">',
					esc_attr( $shortcode ),
					esc_attr__( 'Klicken zum Markieren', 'open-data-wizard' )
				);
				break;
		}
	}

	/**
	 * Define sortable columns.
	 *
	 * @param array<string, string> $columns Existing sortable columns.
	 * @return array<string, string> Extended sortable columns.
	 */
	public static function sortable_columns( array $columns ): array {
		$columns['odw_modified'] = 'modified';
		$columns['odw_theme']    = 'odw_theme';
		$columns['odw_quality']  = 'odw_quality';
		return $columns;
	}

	/**
	 * Enable meta-based ordering for the Thema column.
	 *
	 * @param WP_Query $query Current query object.
	 */
	public static function handle_meta_orderby( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'odw_dataset' !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( 'odw_theme' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_odw_theme' );
			$query->set( 'orderby', 'meta_value' );
		}

		if ( 'odw_quality' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_odw_quality_score' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Status filter dropdown above list table.
	 */
	public static function status_filter_dropdown(): void {
		global $typenow;

		if ( ! isset( $typenow ) || 'odw_dataset' !== $typenow ) {
			return;
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected = isset( $_GET['odw_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['odw_status_filter'] ) ) : '';

		$options = array(
			''        => __( 'Alle Status', 'open-data-wizard' ),
			'publish' => __( 'Veröffentlicht', 'open-data-wizard' ),
			'draft'   => __( 'Entwurf', 'open-data-wizard' ),
		);

		echo '<select name="odw_status_filter">';
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $selected, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * Apply status filter to query.
	 *
	 * @param WP_Query $query Current query object.
	 */
	public static function apply_status_filter( WP_Query $query ): void {
		global $pagenow, $typenow;

		if ( ! is_admin() || 'edit.php' !== $pagenow || ! isset( $typenow ) || 'odw_dataset' !== $typenow ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filter = isset( $_GET['odw_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['odw_status_filter'] ) ) : '';

		if ( in_array( $filter, array( 'publish', 'draft' ), true ) ) {
			$query->set( 'post_status', $filter );
		} else {
			$query->set( 'post_status', array( 'publish', 'draft' ) );
		}
	}

	/**
	 * Enqueue admin assets (only on odw_dataset screens).
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_assets( string $hook ): void {
		$screen = get_current_screen();

		if ( ! $screen || 'odw_dataset' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'odw-admin',
			ODW_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			ODW_VERSION
		);

		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_script(
				'odw-wizard-tabs',
				ODW_PLUGIN_URL . 'assets/js/wizard-tabs.js',
				array(),
				ODW_VERSION,
				true
			);

			wp_enqueue_media();

			wp_enqueue_script(
				'odw-file-upload',
				ODW_PLUGIN_URL . 'assets/js/odw-file-upload.js',
				array( 'jquery' ),
				ODW_VERSION,
				true
			);

			wp_enqueue_script(
				'odw-admin-fields',
				ODW_PLUGIN_URL . 'assets/js/odw-admin-fields.js',
				array(),
				ODW_VERSION,
				true
			);

			wp_enqueue_script(
				'odw-complex-fix',
				ODW_PLUGIN_URL . 'assets/js/odw-complex-fix.js',
				array(),
				ODW_VERSION,
				true
			);

			// Build license auto-suggest options from config/licenses.txt.
			$license_file_options = array();
			foreach ( ODW_Fields::load_license_list() as $uri => $label ) {
				$license_file_options[] = array(
					'value' => $uri,
					'label' => $label,
				);
			}

			// Build CESSDA auto-suggest options from SKOS file.
			$cessda_options = array();
			foreach ( ODW_Fields::load_cessda_options() as $uri => $label ) {
				$cessda_options[] = array(
					'value' => $uri,
					'label' => $label,
				);
			}

			wp_localize_script(
				'odw-admin-fields',
				'odwAdminFields',
				array(
					'licenseOptions' => $license_file_options,
					'cessdaOptions'  => $cessda_options,
					'fileSizeWidget' => array(
						'label'       => __( 'Dateigröße', 'open-data-wizard' ),
						'optional'    => __( '(optional)', 'open-data-wizard' ),
						'placeholder' => __( 'z. B. 2.5', 'open-data-wizard' ),
						'ariaNumber'  => __( 'Dateigröße Zahlenwert', 'open-data-wizard' ),
						'ariaUnit'    => __( 'Einheit', 'open-data-wizard' ),
						'helpText'    => __( 'Bitte geben Sie die ungefähre Größe der Datei an und wählen Sie die passende Einheit. 1 MB = 1.024 KB', 'open-data-wizard' ),
					),
				)
			);

			global $post;
			$file_id   = $post ? (int) get_post_meta( $post->ID, '_odw_file_id', true ) : 0;
			$file_name = '';
			if ( $file_id > 0 ) {
				$attachment = get_post( $file_id );
				$file_name  = $attachment instanceof \WP_Post ? $attachment->post_title : '';
			}

			wp_localize_script(
				'odw-file-upload',
				'odwFileUpload',
				array(
					'currentId'   => $file_id,
					'currentName' => $file_name,
					'labels'      => array(
						'frameTitle'  => __( 'Datei auswählen oder hochladen', 'open-data-wizard' ),
						'frameButton' => __( 'Auswählen', 'open-data-wizard' ),
						'noFile'      => __( 'Keine Datei ausgewählt', 'open-data-wizard' ),
					),
				)
			);
		}
	}

	// -------------------------------------------------------------------------
	// Download-Datei — Native Media Library Meta Box
	// -------------------------------------------------------------------------

	/**
	 * Register the file-upload meta box on the dataset edit screen.
	 */
	public static function register_file_meta_box(): void {
		add_meta_box(
			'odw-file-upload',
			__( 'Download-Datei (Mediathek)', 'open-data-wizard' ),
			array( self::class, 'render_file_meta_box' ),
			'odw_dataset',
			'side',
			'default'
		);
	}

	/**
	 * Render the file-upload meta box.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_file_meta_box( \WP_Post $post ): void {
		$file_id  = (int) get_post_meta( $post->ID, '_odw_file_id', true );
		$has_file = $file_id > 0;

		$file_name = '';
		if ( $has_file ) {
			$attachment = get_post( $file_id );
			$file_name  = $attachment instanceof \WP_Post ? $attachment->post_title : '';
		}

		wp_nonce_field( 'odw_save_file_attachment', 'odw_file_upload_nonce' );
		?>
		<div class="odw-file-upload">

			<input
				type="hidden"
				id="odw-file-id-input"
				name="_odw_file_id"
				value="<?php echo esc_attr( (string) ( $has_file ? $file_id : 0 ) ); ?>"
			>

			<div id="odw-file-preview" class="odw-file-preview <?php echo $has_file ? 'odw-file-preview--has-file' : 'odw-file-preview--empty'; ?>">
				<span class="dashicons dashicons-media-document odw-file-icon" aria-hidden="true"></span>
				<span id="odw-file-name" class="odw-file-name">
					<?php echo $has_file ? esc_html( $file_name ) : esc_html__( 'Keine Datei ausgewählt', 'open-data-wizard' ); ?>
				</span>
			</div>

			<div class="odw-file-actions">
				<button type="button" id="odw-file-select-btn" class="button">
					<span class="dashicons dashicons-upload" aria-hidden="true"></span>
					<?php esc_html_e( 'Datei auswählen / hochladen', 'open-data-wizard' ); ?>
				</button>
				<button
					type="button"
					id="odw-file-remove-btn"
					class="button button-link-delete"
					<?php echo $has_file ? '' : 'disabled'; ?>
				>
					<?php esc_html_e( 'Entfernen', 'open-data-wizard' ); ?>
				</button>
			</div>

			<p class="description">
				<?php esc_html_e( 'Datei aus der Mediathek verknüpfen — wird als Download-Button im [odw_dataset]-Shortcode angezeigt.', 'open-data-wizard' ); ?>
			</p>

		</div>
		<?php
	}

	/**
	 * Save the selected attachment ID and pre-compute size + format meta.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object (required by hook signature).
	 */
	public static function save_file_attachment( int $post_id, \WP_Post $post ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['odw_file_upload_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['odw_file_upload_nonce'] ) ),
				'odw_save_file_attachment'
			)
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$file_id = isset( $_POST['_odw_file_id'] ) ? absint( $_POST['_odw_file_id'] ) : 0;

		update_post_meta( $post_id, '_odw_file_id', $file_id );

		if ( $file_id > 0 ) {
			$file_path = get_attached_file( $file_id );
			if ( $file_path && file_exists( $file_path ) ) {
				update_post_meta( $post_id, '_odw_file_size', (int) filesize( $file_path ) );
				update_post_meta( $post_id, '_odw_file_format', strtoupper( (string) pathinfo( $file_path, PATHINFO_EXTENSION ) ) );
			}
		} else {
			delete_post_meta( $post_id, '_odw_file_size' );
			delete_post_meta( $post_id, '_odw_file_format' );
		}
	}

	/**
	 * Register Help Tabs on the odw_dataset edit screen.
	 */
	public static function register_help_tabs(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'odw_dataset' !== $screen->post_type ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'odw-help-fields',
				'title'   => __( 'Felder', 'open-data-wizard' ),
				'content' => self::help_content_fields(),
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'odw-help-api',
				'title'   => __( 'Harvest-Endpoint', 'open-data-wizard' ),
				'content' => self::help_content_api(),
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'Weitere Informationen:', 'open-data-wizard' ) . '</strong></p>' .
			'<p><a href="https://www.w3.org/TR/vocab-dcat-3/" target="_blank">DCAT-AP 3.0 Spezifikation</a></p>' .
			'<p><a href="https://github.com/daimpad/OpenDataWizard" target="_blank">Plugin-Dokumentation</a></p>'
		);
	}

	/**
	 * Returns HTML for the Fields help tab.
	 *
	 * @return string HTML content.
	 */
	private static function help_content_fields(): string {
		ob_start();
		?>
		<h3><?php esc_html_e( 'DCAT-AP 3.0 Pflichtfelder', 'open-data-wizard' ); ?></h3>
		<ul>
			<li><strong>dct:title</strong> — <?php esc_html_e( 'Titel des Datensatzes (WordPress-Titel-Feld)', 'open-data-wizard' ); ?></li>
			<li><strong>dct:description</strong> — <?php esc_html_e( 'Beschreibung des Datensatzes', 'open-data-wizard' ); ?></li>
			<li><strong>dct:publisher</strong> — <?php esc_html_e( 'Name der herausgebenden Organisation', 'open-data-wizard' ); ?></li>
			<li><strong>dct:license</strong> — <?php esc_html_e( 'Lizenz aus dem kontrollierten Vokabular', 'open-data-wizard' ); ?></li>
		</ul>
		<h3><?php esc_html_e( 'Distribution', 'open-data-wizard' ); ?></h3>
		<p><?php esc_html_e( 'Jeder Datensatz benötigt mindestens eine Distribution mit einer Zugriffs-URL (dcat:accessURL). Mehrere Distributionen (z.B. CSV + JSON) können hinzugefügt werden.', 'open-data-wizard' ); ?></p>
		<h3><?php esc_html_e( 'Erweiterte Angaben', 'open-data-wizard' ); ?></h3>
		<p><?php esc_html_e( 'Tab 4 enthält optionale DCAT-AP Felder: Projektseite (dcat:landingPage), Aktualisierungsfrequenz (dct:accrualPeriodicity), geographische und zeitliche Abdeckung sowie einen Kontaktpunkt (dcat:contactPoint).', 'open-data-wizard' ); ?></p>
		<h3><?php esc_html_e( 'Vorschau', 'open-data-wizard' ); ?></h3>
		<p><?php esc_html_e( 'Tab 5 zeigt das generierte JSON-LD nach dem Speichern. Dort finden Sie auch den direkten Link zum REST-Endpoint.', 'open-data-wizard' ); ?></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Returns HTML for the Harvest-Endpoint help tab.
	 *
	 * @return string HTML content.
	 */
	private static function help_content_api(): string {
		$catalog_url = rest_url( 'datenatlas/v1/catalog' );
		$dataset_url = rest_url( 'datenatlas/v1/datasets/{id}' );

		ob_start();
		?>
		<h3><?php esc_html_e( 'Catalog-Endpoint (REST API)', 'open-data-wizard' ); ?></h3>
		<p><code><?php echo esc_html( $catalog_url ); ?></code></p>
		<p><?php esc_html_e( 'Liefert alle veröffentlichten Datensätze als dcat:Catalog (JSON-LD). Unterstützt:', 'open-data-wizard' ); ?></p>
		<ul>
			<li><code>?page=1&amp;per_page=20</code> — <?php esc_html_e( 'Paginierung', 'open-data-wizard' ); ?></li>
			<li><code>?theme=Bildung</code> — <?php esc_html_e( 'Filter nach Thema', 'open-data-wizard' ); ?></li>
			<li><code>?license=cc-by</code> — <?php esc_html_e( 'Filter nach Lizenz (Kurzform)', 'open-data-wizard' ); ?></li>
		</ul>
		<h3><?php esc_html_e( 'Einzelner Datensatz', 'open-data-wizard' ); ?></h3>
		<p><code><?php echo esc_html( $dataset_url ); ?></code></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Register the introduction submenu page.
	 */
	public static function register_introduction_page(): void {
		add_submenu_page(
			'edit.php?post_type=odw_dataset',
			__( 'Einstieg', 'open-data-wizard' ),
			__( 'Einstieg', 'open-data-wizard' ),
			'manage_options',
			'odw-einstieg',
			array( self::class, 'render_introduction_page' )
		);
	}

	/**
	 * Render the introduction page.
	 */
	public static function render_introduction_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Open Data Wizard — Einstieg', 'open-data-wizard' ); ?></h1>

			<div class="odw-introduction-page">
				<h2><?php esc_html_e( 'Willkommen im Open Data Wizard', 'open-data-wizard' ); ?></h2>

				<p class="description">
					<?php esc_html_e( 'Der Open Data Wizard ermöglicht es Ihnen, Datensätze direkt in WordPress zu beschreiben und als maschinenlesbare, standardkonforme Metadaten bereitzustellen — ohne technische Vorkenntnisse.', 'open-data-wizard' ); ?>
				</p>

				<h3><?php esc_html_e( 'Das Problem', 'open-data-wizard' ); ?></h3>
				<p>
					<?php esc_html_e( 'Offene Daten zu veröffentlichen ist schwieriger als es sein müsste. Komplexe Formulare und unbekannte Fachbegriffe erschweren die Arbeit. Der Open Data Wizard vereinfacht dies, indem er Sie mit verständlichen Fragen durch den Prozess führt.', 'open-data-wizard' ); ?>
				</p>

				<h3><?php esc_html_e( 'Die Idee', 'open-data-wizard' ); ?></h3>
				<p>
					<?php esc_html_e( 'Beschreiben Sie Ihre Datensätze dort, wo Sie ohnehin arbeiten – direkt in WordPress. Das Plugin generiert daraus eine maschinenlesbare Beschreibung nach dem internationalen Standard DCAT-AP 3.0 und stellt sie unter einer persistenten URL bereit.', 'open-data-wizard' ); ?>
					<?php esc_html_e( 'Open-Data-Plattformen können diese URL als Harvest-Quelle einbinden und die Metadaten automatisch einsammeln.', 'open-data-wizard' ); ?>
				</p>

				<h3><?php esc_html_e( 'Wie funktioniert es?', 'open-data-wizard' ); ?></h3>
				<p><?php esc_html_e( 'Das Wizard-Formular ist in fünf einfache Schritte unterteilt:', 'open-data-wizard' ); ?></p>
				<ol>
					<li>
						<strong><?php esc_html_e( '1 — Grundlegende Informationen', 'open-data-wizard' ); ?></strong><br>
						<?php esc_html_e( 'Wer gibt diese Daten heraus? Worum geht es? In welche Kategorie gehört der Datensatz?', 'open-data-wizard' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( '2 — Inhaltliche Angaben', 'open-data-wizard' ); ?></strong><br>
						<?php esc_html_e( 'Sprache, Schlagworte, Veröffentlichungs- und Änderungsdatum, Themenklassifikation.', 'open-data-wizard' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( '3 — Datenbereitstellung', 'open-data-wizard' ); ?></strong><br>
						<?php esc_html_e( 'Download-URL, Format, Dateigröße, Lizenz und Zuschreibungstext.', 'open-data-wizard' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( '4 — Erweiterte Angaben', 'open-data-wizard' ); ?></strong><br>
						<?php esc_html_e( 'Projektseite, Aktualisierungsfrequenz, geografische und zeitliche Abdeckung, Kontaktinformationen.', 'open-data-wizard' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( '5 — Vorschau', 'open-data-wizard' ); ?></strong><br>
						<?php esc_html_e( 'Generiertes JSON-LD live einsehen und über die REST-API abrufen.', 'open-data-wizard' ); ?>
					</li>
				</ol>

				<h3><?php esc_html_e( 'Erste Schritte', 'open-data-wizard' ); ?></h3>
				<p>
					<?php esc_html_e( 'Füllen Sie die Pflichtfelder aus (mit * gekennzeichnet) und speichern Sie den Datensatz. Sie können ihn später noch bearbeiten. Jedes Feld hat hilfreiche Beispiele – schauen Sie in den Hilfetexten vorbei!', 'open-data-wizard' ); ?>
				</p>

				<p class="submit">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=odw_dataset' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Neuen Datensatz erstellen', 'open-data-wizard' ); ?>
					</a>
				</p>
			</div>
		</div>

		<style>
			.odw-introduction-page {
				max-width: 800px;
				line-height: 1.8;
				color: var(--odw-color-text, #1d2327);
				background: #fff;
				padding: 20px;
				border-radius: 4px;
				margin-top: 20px;
			}
			.odw-introduction-page h2 {
				margin-top: 0;
				color: var(--odw-color-primary, #2271b1);
			}
			.odw-introduction-page h3 {
				margin-top: 24px;
				color: var(--odw-color-primary, #2271b1);
				font-size: 16px;
			}
			.odw-introduction-page ol {
				list-style-position: inside;
				padding-left: 0;
			}
			.odw-introduction-page li {
				margin-bottom: 12px;
				padding-left: 0;
			}
			.odw-introduction-page .description {
				font-size: 15px;
				color: var(--odw-color-text, #1d2327);
			}
		</style>
		<?php
	}

	/**
	 * Register the batch import submenu page.
	 */
	public static function register_batch_import_page(): void {
		add_submenu_page(
			'edit.php?post_type=odw_dataset',
			__( 'Batch-Import', 'open-data-wizard' ),
			__( 'Batch-Import', 'open-data-wizard' ),
			'manage_open_data',
			'odw-batch-import',
			array( self::class, 'render_batch_import_page' )
		);
	}

	/**
	 * Render the batch import page.
	 */
	public static function render_batch_import_page(): void {
		if ( ! current_user_can( 'manage_open_data' ) ) {
			wp_die( esc_html__( 'Zugriff verweigert.', 'open-data-wizard' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Batch-Import', 'open-data-wizard' ); ?></h1>

			<div class="odw-batch-import-container" style="max-width: 800px; margin-top: 20px;">

				<!-- Upload Section -->
				<div id="odw-upload-section" class="odw-section" style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
					<h2><?php esc_html_e( 'Datei hochladen', 'open-data-wizard' ); ?></h2>

					<form id="odw-import-form" method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'odw_batch_import' ); ?>

						<div style="margin-bottom: 15px;">
							<label for="odw-import-file" style="display: block; margin-bottom: 8px; font-weight: 600;">
								<?php esc_html_e( 'CSV oder JSON Datei', 'open-data-wizard' ); ?>
							</label>
							<input type="file" id="odw-import-file" name="import_file" accept=".csv,.json" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; max-width: 400px;">
							<p class="description" style="margin-top: 8px;">
								<?php esc_html_e( 'Unterstützte Formate: CSV, JSON (max. 10MB)', 'open-data-wizard' ); ?>
							</p>
						</div>

						<button type="button" id="odw-preview-btn" class="button button-primary" style="margin-right: 10px;">
							<?php esc_html_e( 'Vorschau', 'open-data-wizard' ); ?>
						</button>

						<a href="<?php echo esc_url( admin_url( 'admin.php?page=odw-batch-import&sample=csv' ) ); ?>" class="button">
							<?php esc_html_e( 'CSV-Beispiel', 'open-data-wizard' ); ?>
						</a>
					</form>
				</div>

				<!-- Preview Section (hidden until preview is clicked) -->
				<div id="odw-preview-section" class="odw-section" style="display: none; border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
					<h2><?php esc_html_e( 'Vorschau', 'open-data-wizard' ); ?></h2>

					<div id="odw-preview-loading" style="display: none; text-align: center; padding: 40px;">
						<span class="spinner" style="float: none; visibility: visible;"></span>
						<p><?php esc_html_e( 'Datei wird analysiert…', 'open-data-wizard' ); ?></p>
					</div>

					<div id="odw-preview-content" style="display: none;">
						<div style="margin-bottom: 15px;">
							<strong><?php esc_html_e( 'Gültige Datensätze:', 'open-data-wizard' ); ?></strong>
							<span id="odw-preview-count" style="font-size: 18px; color: #1a7f37; font-weight: 600;">0</span>
						</div>

						<table id="odw-preview-table" class="wp-list-table widefat striped" style="margin-bottom: 15px;">
							<thead>
								<tr>
									<th style="width: 30px;"><input type="checkbox" id="odw-select-all" checked></th>
									<th><?php esc_html_e( 'Titel', 'open-data-wizard' ); ?></th>
									<th><?php esc_html_e( 'Herausgeber', 'open-data-wizard' ); ?></th>
									<th><?php esc_html_e( 'Lizenz', 'open-data-wizard' ); ?></th>
									<th><?php esc_html_e( 'Status', 'open-data-wizard' ); ?></th>
								</tr>
							</thead>
							<tbody id="odw-preview-rows"></tbody>
						</table>

						<div id="odw-errors-section" style="display: none; margin-bottom: 15px;">
							<strong style="color: #c1272d;">
								<?php esc_html_e( 'Fehler:', 'open-data-wizard' ); ?>
							</strong>
							<ul id="odw-errors-list" style="margin-top: 10px; color: #7d1212;"></ul>
						</div>
					</div>

					<div id="odw-preview-error" style="display: none; color: #c1272d; padding: 15px; background: #ffd7d5; border-radius: 4px;"></div>
				</div>

				<!-- Import Section (hidden until preview is successful) -->
				<div id="odw-import-section" class="odw-section" style="display: none; border: 1px solid #ddd; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
					<h2><?php esc_html_e( 'Datensätze importieren', 'open-data-wizard' ); ?></h2>

					<p><?php esc_html_e( 'Bereit zum Importieren! Klicke auf den Button unten, um alle Datensätze als Entwürfe zu erstellen.', 'open-data-wizard' ); ?></p>

					<button type="button" id="odw-import-btn" class="button button-success" style="margin-right: 10px; padding: 10px 20px; font-size: 14px;">
						<?php esc_html_e( 'Importieren', 'open-data-wizard' ); ?>
					</button>
					<button type="button" id="odw-import-cancel-btn" class="button">
						<?php esc_html_e( 'Abbrechen', 'open-data-wizard' ); ?>
					</button>

					<div id="odw-import-progress" style="display: none; margin-top: 20px;">
						<div style="margin-bottom: 10px;">
							<span id="odw-import-status"><?php esc_html_e( 'Import läuft…', 'open-data-wizard' ); ?></span>
						</div>
						<progress id="odw-import-progress-bar" value="0" max="100" style="width: 100%; height: 25px; border-radius: 4px;"></progress>
					</div>

					<div id="odw-import-result" style="display: none; margin-top: 20px; padding: 15px; border-radius: 4px;">
						<h3><?php esc_html_e( 'Import abgeschlossen!', 'open-data-wizard' ); ?></h3>
						<p>
							<?php esc_html_e( 'Erstellt:', 'open-data-wizard' ); ?>
							<strong id="odw-import-result-created" style="color: #1a7f37;">0</strong>
						</p>
						<p>
							<?php esc_html_e( 'Fehler:', 'open-data-wizard' ); ?>
							<strong id="odw-import-result-failed" style="color: #c1272d;">0</strong>
						</p>
						<p style="margin-top: 15px;">
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=odw_dataset' ) ); ?>" class="button button-primary">
								<?php esc_html_e( 'Zur Datensatzliste', 'open-data-wizard' ); ?>
							</a>
						</p>
					</div>
				</div>

			</div>
		</div>

		<script>
			(function($) {
				var previewData = [];

				$('#odw-preview-btn').on('click', function() {
					var fileInput = document.getElementById('odw-import-file');
					if (!fileInput.files.length) {
						alert('<?php esc_html_e( 'Bitte wähle eine Datei aus.', 'open-data-wizard' ); ?>');
						return;
					}

					previewData = [];
					$('#odw-preview-content').hide();
					$('#odw-preview-error').hide();
					$('#odw-preview-loading').show();
					$('#odw-import-section').hide();

					var formData = new FormData();
					formData.append('action', 'odw_batch_import_preview');
					formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'odw_batch_import' ) ); ?>');
					formData.append('file', fileInput.files[0]);

					$.ajax({
						url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#odw-preview-loading').hide();
							if (response.success) {
								previewData = response.data.records;
								displayPreview(response.data);
								$('#odw-preview-content').show();
								$('#odw-import-section').show();
							} else {
								$('#odw-preview-error').text(response.data.error).show();
							}
						},
						error: function() {
							$('#odw-preview-loading').hide();
							$('#odw-preview-error').text('<?php esc_html_e( 'Ein Fehler ist aufgetreten.', 'open-data-wizard' ); ?>').show();
						}
					});
				});

				function displayPreview(data) {
					$('#odw-preview-count').text(data.records.length);
					var rows = '';
					$.each(data.records, function(idx, record) {
						rows += '<tr>';
						rows += '<td><input type="checkbox" class="odw-row-select" value="' + idx + '" checked></td>';
						rows += '<td>' + (record.title || '-') + '</td>';
						rows += '<td>' + (record.publisher || '-') + '</td>';
						rows += '<td>' + (record.license || '-') + '</td>';
						rows += '<td><span class="odw-status-badge odw-status-badge--draft"><?php esc_html_e( 'Entwurf', 'open-data-wizard' ); ?></span></td>';
						rows += '</tr>';
					});
					$('#odw-preview-rows').html(rows);

					if (data.errors && data.errors.length > 0) {
						var errorHtml = '';
						$.each(data.errors, function(idx, error) {
							errorHtml += '<li>' + error + '</li>';
						});
						$('#odw-errors-list').html(errorHtml);
						$('#odw-errors-section').show();
					} else {
						$('#odw-errors-section').hide();
					}
				}

				$('#odw-select-all').on('change', function() {
					$('.odw-row-select').prop('checked', $(this).is(':checked'));
				});

				$('#odw-import-btn').on('click', function() {
					var selected = [];
					$('.odw-row-select:checked').each(function() {
						selected.push(parseInt($(this).val()));
					});

					if (!selected.length) {
						alert('<?php esc_html_e( 'Keine Datensätze ausgewählt.', 'open-data-wizard' ); ?>');
						return;
					}

					$('#odw-import-section').hide();
					$('#odw-import-progress').show();

					var records = selected.map(function(idx) {
						return previewData[idx];
					});

					$.ajax({
						url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'odw_batch_import_execute',
							nonce: '<?php echo esc_js( wp_create_nonce( 'odw_batch_import' ) ); ?>',
							records: JSON.stringify(records)
						},
						success: function(response) {
							$('#odw-import-progress').hide();
							if (response.success) {
								$('#odw-import-result-created').text(response.data.created);
								$('#odw-import-result-failed').text(response.data.failed);
								$('#odw-import-result').show();
							} else {
								alert('Error: ' + response.data.error);
							}
						},
						error: function() {
							$('#odw-import-progress').hide();
							alert('<?php esc_html_e( 'Ein Fehler ist aufgetreten.', 'open-data-wizard' ); ?>');
						}
					});
				});

				$('#odw-import-cancel-btn').on('click', function() {
					location.reload();
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX: Preview batch import file.
	 */
	public static function ajax_batch_import_preview(): void {
		check_ajax_referer( 'odw_batch_import' );

		if ( ! current_user_can( 'manage_open_data' ) ) {
			wp_send_json_error( array( 'error' => __( 'Zugriff verweigert.', 'open-data-wizard' ) ) );
		}

		if ( ! isset( $_FILES['file'] ) ) {
			wp_send_json_error( array( 'error' => __( 'Keine Datei hochgeladen.', 'open-data-wizard' ) ) );
		}

		$file = $_FILES['file'];

		// Validate file
		$allowed_types = array( 'text/csv', 'application/json', 'text/plain' );
		if ( ! in_array( $file['type'], $allowed_types, true ) ) {
			wp_send_json_error( array( 'error' => __( 'Ungültiger Dateityp.', 'open-data-wizard' ) ) );
		}

		// Move to temp
		$temp_file = wp_tempnam();
		if ( ! move_uploaded_file( $file['tmp_name'], $temp_file ) ) {
			wp_send_json_error( array( 'error' => __( 'Datei konnte nicht hochgeladen werden.', 'open-data-wizard' ) ) );
		}

		// Parse file
		$result = ODW_Batch_Import::parse_file( $temp_file );
		unlink( $temp_file );

		if ( ! $result['success'] ) {
			wp_send_json_error( array( 'error' => $result['error'] ) );
		}

		wp_send_json_success(
			array(
				'records' => $result['data'],
				'errors'  => array(),
			)
		);
	}

	/**
	 * AJAX: Execute batch import.
	 */
	public static function ajax_batch_import_execute(): void {
		check_ajax_referer( 'odw_batch_import' );

		if ( ! current_user_can( 'manage_open_data' ) ) {
			wp_send_json_error( array( 'error' => __( 'Zugriff verweigert.', 'open-data-wizard' ) ) );
		}

		$records_json = sanitize_text_field( wp_unslash( $_POST['records'] ?? '[]' ) );
		$records      = json_decode( $records_json, true );

		if ( ! is_array( $records ) || empty( $records ) ) {
			wp_send_json_error( array( 'error' => __( 'Keine Datensätze zum Importieren.', 'open-data-wizard' ) ) );
		}

		$result = ODW_Batch_Import::import_records( $records );

		wp_send_json_success(
			array(
				'created' => $result['created'],
				'failed'  => $result['failed'],
				'errors'  => $result['errors'],
			)
		);
	}
}
