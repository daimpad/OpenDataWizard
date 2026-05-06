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

	private const DEMO_OPTION    = 'odw_demo_post_id';
	private const WELCOME_OPTION = 'odw_show_welcome';

	/**
	 * Wird direkt aus register_activation_hook aufgerufen — Carbon Fields
	 * ist zu diesem Zeitpunkt noch nicht geladen.
	 */
	public static function on_activation(): void {
		update_option( self::WELCOME_OPTION, '1', false );
	}

	/**
	 * Wird aus odw_bootstrap() aufgerufen, nachdem Carbon Fields initialisiert ist.
	 */
	public static function init(): void {
		add_action( 'admin_init', array( self::class, 'maybe_create_demo' ) );
		add_action( 'admin_init', array( self::class, 'handle_dismiss' ) );
		add_action( 'admin_notices', array( self::class, 'render_welcome_notice' ) );
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
		update_post_meta( $post_id, '_odw_theme', 'Soziales' );
		update_post_meta( $post_id, '_odw_issued', current_time( 'Y-m-d' ) );
		update_post_meta( $post_id, '_odw_modified', current_time( 'Y-m-d' ) );

		// Beispiel-CSV importieren und als Mediathek-Eintrag verknüpfen.
		$file_id = self::import_sample_file();

		if ( $file_id ) {
			update_post_meta( $post_id, '_odw_file_id', $file_id );

			// Distribution via Carbon Fields API setzen, damit JSON-LD und
			// Qualitätsprüfung korrekte Daten lesen.
			if ( function_exists( 'carbon_set_post_meta' ) ) {
				$file_url = wp_get_attachment_url( $file_id );
				if ( $file_url ) {
					carbon_set_post_meta(
						$post_id,
						'odw_distributions',
						array(
							array(
								'access_url' => $file_url,
								'format'     => 'CSV',
								'byte_size'  => '',
							),
						)
					);
				}
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
