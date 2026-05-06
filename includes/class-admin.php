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
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'add_meta_boxes', array( self::class, 'register_help_tabs' ) );
		add_action( 'load-post.php', array( self::class, 'register_help_tabs' ) );
		add_action( 'load-post-new.php', array( self::class, 'register_help_tabs' ) );
		add_action( 'add_meta_boxes', array( self::class, 'register_file_meta_box' ) );
		add_action( 'save_post_odw_dataset', array( self::class, 'save_file_attachment' ), 20, 2 );
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
				// License is now stored per distribution (Änderung 7); show label from first distribution.
				$dists = carbon_get_post_meta( $post_id, 'odw_distributions' );
				$lic   = '';
				if ( is_array( $dists ) ) {
					foreach ( $dists as $dist ) {
						$candidate = (string) ( $dist['license'] ?? '' );
						if ( '' !== $candidate ) {
							$lic = ( 'sonstige' === $candidate && ! empty( $dist['license_custom'] ) )
								? (string) $dist['license_custom']
								: $candidate;
							break;
						}
					}
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
}
