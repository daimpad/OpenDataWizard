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
 * Handles first-run setup: welcome notice, capability, one-time migrations.
 *
 * Seit v2.47.0 legt die Aktivierung keinen Demo-Datensatz mehr an — der
 * Katalog startet leer. Beispieldaten lädt man bei Bedarf (ODW_Samples).
 *
 * @package OpenDataWizard
 */
class ODW_Setup {

	private const DEMO_OPTION     = 'odw_demo_post_id';
	private const WELCOME_OPTION  = 'odw_show_welcome';
	private const REDIRECT_OPTION = 'odw_activation_redirect';
	private const MULTI_MIGRATED  = 'odw_multi_value_migrated';
	private const DEMO_MIGRATED   = 'odw_demo_migrated';

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
		add_action( 'admin_init', array( self::class, 'maybe_migrate_demo' ) );
		add_action( 'admin_init', array( self::class, 'maybe_migrate_multi_values' ) );
		add_action( 'admin_init', array( self::class, 'handle_dismiss' ) );
		add_action( 'admin_notices', array( self::class, 'render_welcome_notice' ) );
	}

	/**
	 * Überführt Einzelwerte in die Mehrfachauswahlen (einmalig, ab v2.41.0).
	 *
	 * Betroffen sind `odw_theme` und `odw_engagementfeld`. Bis v2.40.x waren
	 * beide Auswahl- bzw. Textfelder und lagen flach unter `_odw_theme` bzw.
	 * `_odw_engagementfeld`; ein zweites Thema konnte unter `_odw_theme_uri`
	 * stehen. Carbon Fields legt Mehrfachwerte unter eigenen Meta-Keys ab, die
	 * alten Zeilen wären also unsichtbar geworden — deshalb diese Umschreibung.
	 *
	 * Geschrieben wird über carbon_set_post_meta(), nicht über selbstgebaute
	 * Meta-Keys: Das Schlüsselformat ist ein Interna von Carbon Fields und
	 * darf sich ändern.
	 */
	public static function maybe_migrate_multi_values(): void {
		if ( get_option( self::MULTI_MIGRATED ) ) {
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

			// Engagementfeld: bis v2.40.x ein Textfeld mit Autosuggest, das
			// entweder die URI oder — bei von Hand eingetragenen Werten — das
			// Label enthielt. normalize_vocabulary() löst beides auf.
			$alt_engagement = get_post_meta( $post_id, '_odw_engagementfeld', true );
			$engagement     = ODW_Fields::normalize_vocabulary( $alt_engagement, 'engagementfeld' );

			if ( array() !== $engagement ) {
				carbon_set_post_meta( $post_id, 'odw_engagementfeld', $engagement );
			}

			delete_post_meta( $post_id, '_odw_engagementfeld' );
		}

		update_option( self::MULTI_MIGRATED, '1', false );
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
	// Demo-Datensatz früherer Versionen
	// -------------------------------------------------------------------------

	/**
	 * Übernimmt den Demo-Datensatz, den Versionen vor 2.47.0 beim Aktivieren
	 * anlegten (einmalig).
	 *
	 * - Nie bearbeitet, ist er noch das Beispiel und wird als solches
	 *   gekennzeichnet: Dann erscheint er in keinem Harvest-Endpunkt mehr —
	 *   bisher lieferte der Katalog ihn an jedes Portal aus — und lässt sich
	 *   mit den übrigen Beispieldaten entfernen.
	 * - Wer ihn bearbeitet hat, hat daraus vermutlich den ersten echten
	 *   Datensatz gemacht; der bleibt, wie er ist.
	 *
	 * In beiden Fällen wird die Sprache repariert: Sie stand roh als „de" im
	 * Feld, das Auswahlfeld kennt aber nur EU-URIs, und Formular wie JSON-LD
	 * zeigten deshalb keine Sprache.
	 */
	public static function maybe_migrate_demo(): void {
		if ( get_option( self::DEMO_MIGRATED ) ) {
			return;
		}

		$post_id = (int) get_option( self::DEMO_OPTION );
		$post    = $post_id > 0 ? get_post( $post_id ) : null;

		if ( $post instanceof WP_Post && 'odw_dataset' === $post->post_type ) {
			$language = (string) get_post_meta( $post_id, '_odw_language', true );
			if ( '' !== $language && 0 !== strpos( $language, 'http' ) && class_exists( 'ODW_Batch_Import' ) ) {
				$uri = ODW_Batch_Import::resolve_vocab( 'language', $language );
				if ( '' !== $uri ) {
					update_post_meta( $post_id, '_odw_language', $uri );
				}
			}

			// wp_insert_post() setzt post_modified gleich post_date; jedes
			// spätere Speichern hebt die Gleichheit auf. Der Demo-Datensatz
			// bekam nach dem Anlegen nur noch Meta-Werte.
			if ( (string) $post->post_modified_gmt === (string) $post->post_date_gmt && class_exists( 'ODW_Samples' ) ) {
				update_post_meta( $post_id, ODW_Samples::META_KEY, ODW_Samples::DEMO );
			}

			if ( class_exists( 'ODW_Rest_API' ) ) {
				ODW_Rest_API::invalidate_cache( $post_id );
			}
		}

		delete_option( self::DEMO_OPTION );
		update_option( self::DEMO_MIGRATED, '1', false );
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

		$new_url     = admin_url( 'post-new.php?post_type=odw_dataset' );
		$samples_url = admin_url( 'edit.php?post_type=odw_dataset&page=odw-einstieg' ) . '#odw-beispieldaten';
		$dismiss_url = wp_nonce_url( add_query_arg( 'odw_dismiss_welcome', '1' ), 'odw_dismiss_welcome' );
		?>
		<div class="notice notice-success">
			<p>
				<strong><?php esc_html_e( 'Open Data Wizard erfolgreich installiert!', 'open-data-wizard' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'Der Katalog ist leer. Legen Sie Ihren ersten Datensatz an — oder laden Sie zum Ausprobieren Beispieldaten. Sie erscheinen in keinem Harvest-Endpunkt und lassen sich mit einem Klick wieder entfernen.', 'open-data-wizard' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $new_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Neuen Datensatz erstellen', 'open-data-wizard' ); ?>
				</a>
				&nbsp;
				<a href="<?php echo esc_url( $samples_url ); ?>" class="button">
					<?php esc_html_e( 'Zu den Beispieldaten', 'open-data-wizard' ); ?>
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
