<?php
/**
 * Installations-Setup: Demo-Datensatz und Willkommens-Notice
 *
 * Wird beim ersten Admin-Aufruf nach der Plugin-Aktivierung ausgeführt.
 * - Erstellt einen Demo-Datensatz mit Beispiel-CSV aus der Mediathek.
 * - Zeigt eine einmalige Admin-Notice mit dem fertigen Shortcode.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles first-run setup: creates demo dataset and shows welcome notice.
 *
 * @package OpenDataWizard
 */
class ODW_Setup {

	private const DEMO_OPTION     = 'odw_demo_post_id';
	private const WELCOME_OPTION  = 'odw_show_welcome';
	private const REDIRECT_OPTION = 'odw_activation_redirect';
	private const THEME_MIGRATED  = 'odw_theme_multi_migrated';

	/**
	 * Custom capability, die Batch-Import & Co. schützt.
	 */
	public const CAPABILITY = 'manage_open_data';

	/**
	 * Wird direkt aus register_activation_hook aufgerufen — Carbon Fields
	 * ist zu diesem Zeitpunkt noch nicht geladen.
	 */
	public static function on_activation(): void {
		update_option( self::WELCOME_OPTION, '1', false );
		// Flag a one-time redirect to the introduction page on the next admin load.
		set_transient( self::REDIRECT_OPTION, '1', 60 );
		self::grant_capability();
	}

	/**
	 * Wird aus odw_bootstrap() aufgerufen, nachdem Carbon Fields initialisiert ist.
	 */
	public static function init(): void {
		add_action( 'admin_init', array( self::class, 'maybe_grant_capability' ) );
		add_action( 'admin_init', array( self::class, 'maybe_redirect_to_intro' ) );
		add_action( 'admin_init', array( self::class, 'maybe_create_demo' ) );
		add_action( 'admin_init', array( self::class, 'maybe_migrate_themes' ) );
		add_action( 'admin_init', array( self::class, 'handle_dismiss' ) );
		add_action( 'admin_notices', array( self::class, 'render_welcome_notice' ) );
	}

	/**
	 * Überführt Einzel-Themen in die Mehrfachauswahl (einmalig, ab v2.41.0).
	 *
	 * Bis v2.40.x war `odw_theme` ein Auswahlfeld und lag flach unter
	 * `_odw_theme`; ein zweites Thema konnte unter `_odw_theme_uri` stehen.
	 * Carbon Fields legt Mehrfachwerte unter eigenen Meta-Keys ab, die alten
	 * Zeilen wären also unsichtbar geworden — deshalb diese Umschreibung.
	 *
	 * Geschrieben wird über carbon_set_post_meta(), nicht über selbstgebaute
	 * Meta-Keys: Das Schlüsselformat ist ein Interna von Carbon Fields und
	 * darf sich ändern.
	 */
	public static function maybe_migrate_themes(): void {
		if ( get_option( self::THEME_MIGRATED ) ) {
			return;
		}
		if ( ! function_exists( 'carbon_set_post_meta' ) || ! class_exists( 'ODW_Fields' ) ) {
			return;
		}

		$posts = get_posts(
			array(
				'post_type'        => 'odw_dataset',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);

		foreach ( $posts as $post_id ) {
			$post_id = (int) $post_id;
			$alt     = get_post_meta( $post_id, '_odw_theme', true );
			$alt_uri = get_post_meta( $post_id, '_odw_theme_uri', true );

			$themes = ODW_Fields::normalize_themes( array( $alt, $alt_uri ) );

			if ( array() !== $themes ) {
				carbon_set_post_meta( $post_id, 'odw_theme', $themes );
				ODW_Fields::sync_theme_index( $post_id );
			}

			// Die alten flachen Zeilen entfernen: Sie würden sonst neben den
			// neuen stehen bleiben und bei einer erneuten Migration wieder
			// eingelesen — mit dann womöglich überholten Werten.
			delete_post_meta( $post_id, '_odw_theme' );
			delete_post_meta( $post_id, '_odw_theme_uri' );
		}

		update_option( self::THEME_MIGRATED, '1', false );
	}

	/**
	 * Vergibt manage_open_data an Administratoren und Redakteure.
	 */
	public static function grant_capability(): void {
		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role && ! $role->has_cap( self::CAPABILITY ) ) {
				$role->add_cap( self::CAPABILITY );
			}
		}
	}

	/**
	 * Upgrade-sicherer Nachtrag: Bestehende Installationen durchlaufen den
	 * Aktivierungs-Hook nicht erneut — die Capability wird daher bei Bedarf
	 * auf admin_init nachvergeben (billiger has_cap-Check).
	 */
	public static function maybe_grant_capability(): void {
		$admin = get_role( 'administrator' );
		if ( $admin && ! $admin->has_cap( self::CAPABILITY ) ) {
			self::grant_capability();
		}
	}

	/**
	 * Redirects to the introduction page once, directly after activation.
	 */
	public static function maybe_redirect_to_intro(): void {
		if ( ! get_transient( self::REDIRECT_OPTION ) ) {
			return;
		}

		delete_transient( self::REDIRECT_OPTION );

		// Skip during bulk plugin activation, AJAX, or for users without access.
		if ( wp_doing_ajax() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_open_data' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=odw_dataset&page=odw-einstieg' ) );
		exit;
	}

	// -------------------------------------------------------------------------
	// Demo-Datensatz
	// -------------------------------------------------------------------------

	/**
	 * Erstellt den Demo-Datensatz genau einmal nach der Aktivierung.
	 * Läuft auf admin_init — Carbon Fields ist hier vollständig initialisiert.
	 */
	public static function maybe_create_demo(): void {
		if ( ! get_option( self::WELCOME_OPTION ) ) {
			return;
		}

		if ( get_option( self::DEMO_OPTION ) ) {
			return;
		}

		$post_id = self::create_demo_dataset();

		if ( $post_id ) {
			update_option( self::DEMO_OPTION, $post_id, false );
		}
	}

	/**
	 * Inserts the demo dataset post and imports the sample CSV file.
	 *
	 * @return int Post ID on success, 0 on failure.
	 */
	private static function create_demo_dataset(): int {
		$post_id = wp_insert_post(
			array(
				'post_title'  => __( 'Beispiel: Zivilgesellschaftliche Organisationen', 'open-data-wizard' ),
				'post_status' => 'publish',
				'post_type'   => 'odw_dataset',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		// Einfache Felder direkt per update_post_meta setzen —
		// Carbon Fields liest dieselben _odw_* Keys über carbon_get_post_meta().
		update_post_meta(
			$post_id,
			'_odw_description',
			__(
				'Dieser Demo-Datensatz enthält eine Beispielliste zivilgesellschaftlicher Organisationen. Er wurde automatisch bei der Plugin-Installation erstellt und kann als Vorlage oder zum Testen des [odw_dataset]-Shortcodes verwendet werden.',
				'open-data-wizard'
			)
		);
		update_post_meta( $post_id, '_odw_publisher', __( 'Musterorganisation e.V.', 'open-data-wizard' ) );
		update_post_meta( $post_id, '_odw_license', 'https://creativecommons.org/publicdomain/zero/1.0/' );
		update_post_meta( $post_id, '_odw_language', 'de' );
		update_post_meta( $post_id, '_odw_keywords', "Zivilgesellschaft\nEngagement\nOrganisationen\nDemo" );
		if ( function_exists( 'carbon_set_post_meta' ) && class_exists( 'ODW_Fields' ) ) {
			carbon_set_post_meta( $post_id, 'odw_theme', ODW_Fields::normalize_themes( 'Soziales' ) );
			ODW_Fields::sync_theme_index( $post_id );
		}
		update_post_meta( $post_id, '_odw_issued', current_time( 'Y-m-d' ) );
		update_post_meta( $post_id, '_odw_modified', current_time( 'Y-m-d' ) );

		// Beispiel-CSV importieren und als Mediathek-Eintrag verknüpfen.
		$file_id = self::import_sample_file();

		if ( $file_id ) {
			update_post_meta( $post_id, '_odw_file_id', $file_id );

			$file_url = wp_get_attachment_url( $file_id );
			if ( $file_url ) {
				// Save singular distribution fields (v2.1.4: odw_distributions repeater removed).
				update_post_meta( $post_id, '_odw_access_url', esc_url_raw( $file_url ) );
				update_post_meta( $post_id, '_odw_format', 'CSV' );
			}
		}

		// Qualitätsscore sofort berechnen und persistieren.
		ODW_Quality::store( $post_id, ODW_Quality::calculate( $post_id ) );

		return $post_id;
	}

	/**
	 * Kopiert die gebündelte Beispiel-CSV in das Upload-Verzeichnis und
	 * legt einen Mediathek-Eintrag an.
	 */
	private static function import_sample_file(): int {
		$source = ODW_PLUGIN_DIR . 'assets/sample/beispiel-datensatz.csv';

		if ( ! file_exists( $source ) ) {
			return 0;
		}

		$upload = wp_upload_dir();
		$dest   = trailingslashit( $upload['path'] ) . 'odw-beispiel-datensatz.csv';

		if ( ! copy( $source, $dest ) ) {
			return 0;
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'text/csv',
				'post_title'     => __( 'Open Data Wizard — Demo-Datensatz (CSV)', 'open-data-wizard' ),
				'post_status'    => 'inherit',
			),
			$dest
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			if ( file_exists( $dest ) ) {
				wp_delete_file( $dest );
			}
			return 0;
		}

		return (int) $attachment_id;
	}

	// -------------------------------------------------------------------------
	// Willkommens-Notice
	// -------------------------------------------------------------------------

	/**
	 * Verarbeitet den Dismiss-Link (GET-Parameter + Nonce).
	 */
	public static function handle_dismiss(): void {
		if ( ! isset( $_GET['odw_dismiss_welcome'] ) ) {
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'odw_dismiss_welcome' ) ) {
			wp_die( esc_html__( 'Sicherheitsüberprüfung fehlgeschlagen.', 'open-data-wizard' ) );
		}

		delete_option( self::WELCOME_OPTION );

		wp_safe_redirect( remove_query_arg( array( 'odw_dismiss_welcome', '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Gibt die einmalige Willkommens-Notice aus.
	 */
	public static function render_welcome_notice(): void {
		if ( ! get_option( self::WELCOME_OPTION ) ) {
			return;
		}

		// Show exactly once: remove the flag immediately so the notice does not
		// reappear on subsequent admin page loads, even without an explicit dismiss.
		delete_option( self::WELCOME_OPTION );

		$demo_id     = (int) get_option( self::DEMO_OPTION );
		$edit_url    = $demo_id ? get_edit_post_link( $demo_id ) : null;
		$list_url    = admin_url( 'edit.php?post_type=odw_dataset' );
		$dismiss_url = wp_nonce_url( add_query_arg( 'odw_dismiss_welcome', '1' ), 'odw_dismiss_welcome' );
		$shortcode   = $demo_id ? '[odw_dataset id="' . $demo_id . '"]' : '';
		?>
		<div class="notice notice-success">
			<p>
				<strong><?php esc_html_e( 'Open Data Wizard erfolgreich installiert!', 'open-data-wizard' ); ?></strong>
			</p>

			<?php if ( $demo_id ) : ?>
			<p>
				<?php esc_html_e( 'Ein Demo-Datensatz mit Beispiel-CSV wurde automatisch erstellt. Fügen Sie diesen Shortcode in einen Beitrag oder eine Seite ein, um die Download-Card anzuzeigen:', 'open-data-wizard' ); ?>
			</p>
			<p>
				<input
					type="text"
					readonly
					value="<?php echo esc_attr( $shortcode ); ?>"
					onclick="this.select();"
					style="font-family:monospace;font-size:13px;padding:3px 8px;width:260px;cursor:text;vertical-align:middle;"
					title="<?php esc_attr_e( 'Klicken zum Markieren', 'open-data-wizard' ); ?>"
				>
			</p>
			<?php endif; ?>

			<p>
				<?php if ( $edit_url ) : ?>
				<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Demo-Datensatz bearbeiten', 'open-data-wizard' ); ?>
				</a>
				&nbsp;
				<?php endif; ?>
				<a href="<?php echo esc_url( $list_url ); ?>" class="button">
					<?php esc_html_e( 'Alle Datensätze', 'open-data-wizard' ); ?>
				</a>
				&nbsp;
				<a href="<?php echo esc_url( $dismiss_url ); ?>">
					<?php esc_html_e( 'Hinweis ausblenden', 'open-data-wizard' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
