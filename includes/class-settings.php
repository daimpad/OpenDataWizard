<?php
/**
 * Settings-Seite für Open Data Wizard
 *
 * Untermenü unter "Datensätze" → "Einstellungen".
 * Einstellungen werden als Array in der Option `odw_settings` gespeichert.
 *
 * Verfügbare Einstellungen:
 *   catalog_title       — Titel des Datenkatalogs (REST API dct:title)
 *   catalog_description — Beschreibung des Datenkatalogs (REST API dct:description)
 *   default_publisher   — Vorausgefüllter Herausgeber für neue Datensätze
 *   default_license     — Vorausgewählte Lizenz für neue Datensätze
 *   default_language    — Vorausgewählte Sprache für neue Datensätze
 *   cache_ttl           — Transient-TTL für REST-API-Cache in Sekunden
 *   delete_on_uninstall — Alle Plugin-Daten bei Deinstallation löschen
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page and WordPress Settings API registration for Open Data Wizard.
 *
 * @package OpenDataWizard
 */
class ODW_Settings {

	public const OPTION_KEY = 'odw_settings';

	/**
	 * Registers all WordPress hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'add_submenu_page' ) );
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
		add_action( 'admin_post_odw_recalculate_quality', array( self::class, 'handle_recalculate_quality' ) );
		add_filter( 'odw_catalog_title', array( self::class, 'filter_catalog_title' ) );
		add_filter( 'odw_catalog_description', array( self::class, 'filter_catalog_description' ) );
	}

	// -------------------------------------------------------------------------
	// Menü & Seite
	// -------------------------------------------------------------------------

	/**
	 * Adds the Settings submenu page under the dataset list.
	 */
	public static function add_submenu_page(): void {
		add_submenu_page(
			'edit.php?post_type=odw_dataset',
			__( 'Open Data Wizard — Einstellungen', 'open-data-wizard' ),
			__( 'Einstellungen', 'open-data-wizard' ),
			'manage_options',
			'odw-settings',
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Renders the settings page HTML.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only redirect param set by handle_recalculate_quality() after nonce check.
		$recalculated = isset( $_GET['odw_recalculated'] ) ? absint( wp_unslash( $_GET['odw_recalculated'] ) ) : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Open Data Wizard — Einstellungen', 'open-data-wizard' ); ?></h1>

			<?php if ( null !== $recalculated ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %d: number of recalculated datasets */
						esc_html__( 'Qualitätsscores neu berechnet: %d Datensätze aktualisiert.', 'open-data-wizard' ),
						absint( $recalculated )
					);
					?>
				</p>
			</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'odw_settings_group' );
				do_settings_sections( 'odw-settings' );
				submit_button( __( 'Einstellungen speichern', 'open-data-wizard' ) );
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Datenverwaltung', 'open-data-wizard' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Qualitätsscores aller vorhandenen Datensätze neu berechnen — z.B. nach Änderungen an der Scoring-Logik.', 'open-data-wizard' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="odw_recalculate_quality">
				<?php wp_nonce_field( 'odw_recalculate_quality' ); ?>
				<?php submit_button( __( 'Alle Qualitätsscores neu berechnen', 'open-data-wizard' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Settings API Registrierung
	// -------------------------------------------------------------------------

	/**
	 * Registers settings, sections, and fields via the WordPress Settings API.
	 */
	public static function register_settings(): void {
		register_setting(
			'odw_settings_group',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => self::get_defaults(),
			)
		);

		// --- Katalog ---
		add_settings_section(
			'odw_section_catalog',
			__( 'Katalog', 'open-data-wizard' ),
			static function (): void {
				echo '<p class="description">' . esc_html__( 'Metadaten des Datenkatalogs — erscheinen in der REST-API-Antwort des Catalog-Endpoints.', 'open-data-wizard' ) . '</p>';
			},
			'odw-settings'
		);

		add_settings_field( 'catalog_title', __( 'Katalog-Titel', 'open-data-wizard' ), array( self::class, 'field_catalog_title' ), 'odw-settings', 'odw_section_catalog' );
		add_settings_field( 'catalog_description', __( 'Katalog-Beschreibung', 'open-data-wizard' ), array( self::class, 'field_catalog_description' ), 'odw-settings', 'odw_section_catalog' );

		// --- Standardwerte ---
		add_settings_section(
			'odw_section_defaults',
			__( 'Standardwerte für neue Datensätze', 'open-data-wizard' ),
			static function (): void {
				echo '<p class="description">' . esc_html__( 'Diese Werte werden beim Anlegen eines neuen Datensatzes automatisch vorausgefüllt.', 'open-data-wizard' ) . '</p>';
			},
			'odw-settings'
		);

		add_settings_field( 'default_publisher', __( 'Herausgebende Organisation', 'open-data-wizard' ), array( self::class, 'field_default_publisher' ), 'odw-settings', 'odw_section_defaults' );
		add_settings_field( 'default_license', __( 'Standard-Lizenz', 'open-data-wizard' ), array( self::class, 'field_default_license' ), 'odw-settings', 'odw_section_defaults' );
		add_settings_field( 'default_language', __( 'Standard-Sprache', 'open-data-wizard' ), array( self::class, 'field_default_language' ), 'odw-settings', 'odw_section_defaults' );

		// --- API ---
		add_settings_section(
			'odw_section_api',
			__( 'REST API', 'open-data-wizard' ),
			null,
			'odw-settings'
		);

		add_settings_field( 'cache_ttl', __( 'Cache-Laufzeit (Sekunden)', 'open-data-wizard' ), array( self::class, 'field_cache_ttl' ), 'odw-settings', 'odw_section_api' );

		// --- Qualität (MQA) ---
		add_settings_section(
			'odw_section_quality',
			__( 'Qualitätsprüfung (MQA)', 'open-data-wizard' ),
			null,
			'odw-settings'
		);

		add_settings_field( 'mqa_check_urls', __( 'URL-Erreichbarkeit prüfen', 'open-data-wizard' ), array( self::class, 'field_mqa_check_urls' ), 'odw-settings', 'odw_section_quality' );

		// --- Deinstallation ---
		add_settings_section(
			'odw_section_uninstall',
			__( 'Deinstallation', 'open-data-wizard' ),
			null,
			'odw-settings'
		);

		add_settings_field( 'delete_on_uninstall', __( 'Daten löschen', 'open-data-wizard' ), array( self::class, 'field_delete_on_uninstall' ), 'odw-settings', 'odw_section_uninstall' );
	}

	// -------------------------------------------------------------------------
	// Feld-Callbacks
	// -------------------------------------------------------------------------

	/**
	 * Renders the catalog title settings field.
	 */
	public static function field_catalog_title(): void {
		$value       = self::get( 'catalog_title' );
		$placeholder = get_bloginfo( 'name' ) . ' — Datenkatalog';
		?>
		<input
			type="text"
			name="<?php echo esc_attr( self::OPTION_KEY . '[catalog_title]' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="regular-text"
		>
		<p class="description"><?php esc_html_e( 'Leer lassen für den automatischen Wert (Seitenname + „ — Datenkatalog").', 'open-data-wizard' ); ?></p>
		<?php
	}

	/**
	 * Renders the catalog description settings field.
	 */
	public static function field_catalog_description(): void {
		$value = self::get( 'catalog_description' );
		?>
		<textarea
			name="<?php echo esc_attr( self::OPTION_KEY . '[catalog_description]' ); ?>"
			rows="3"
			class="large-text"
		><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Kurze Beschreibung des Katalogs — erscheint als dct:description in der REST-API-Antwort (Pflichtfeld gemäß DCAT-AP.de).', 'open-data-wizard' ); ?></p>
		<?php
	}

	/**
	 * Renders the default publisher settings field.
	 */
	public static function field_default_publisher(): void {
		$value = self::get( 'default_publisher' );
		?>
		<input
			type="text"
			name="<?php echo esc_attr( self::OPTION_KEY . '[default_publisher]' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr__( 'z.B. Musterorganisation e.V.', 'open-data-wizard' ); ?>"
			class="regular-text"
		>
		<p class="description"><?php esc_html_e( 'Wird im Feld „Herausgebende Organisation" neuer Datensätze vorausgefüllt.', 'open-data-wizard' ); ?></p>
		<?php
	}

	/**
	 * Renders the default license settings field.
	 */
	public static function field_default_license(): void {
		$current = self::get( 'default_license' );
		$options = ODW_Fields::get_license_options();
		?>
		<select name="<?php echo esc_attr( self::OPTION_KEY . '[default_license]' ); ?>">
			<?php foreach ( $options as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $current, $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Vorausgewählte Lizenz bei neuen Datensätzen.', 'open-data-wizard' ); ?></p>
		<?php
	}

	/**
	 * Renders the default language settings field.
	 */
	public static function field_default_language(): void {
		$current = self::get( 'default_language' );
		$options = array_merge(
			array( '' => __( '— Kein Standard —', 'open-data-wizard' ) ),
			array_slice( ODW_Fields::get_language_options(), 1 )
		);
		?>
		<select name="<?php echo esc_attr( self::OPTION_KEY . '[default_language]' ); ?>">
			<?php foreach ( $options as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $current, $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Renders the cache TTL settings field.
	 */
	public static function field_cache_ttl(): void {
		$value = (int) self::get( 'cache_ttl' );
		?>
		<input
			type="number"
			name="<?php echo esc_attr( self::OPTION_KEY . '[cache_ttl]' ); ?>"
			value="<?php echo esc_attr( (string) $value ); ?>"
			min="60"
			max="86400"
			step="60"
			class="small-text"
		> <?php esc_html_e( 'Sekunden', 'open-data-wizard' ); ?>
		<p class="description">
			<?php esc_html_e( 'Wie lange Catalog- und Dataset-Antworten gecacht werden. Standard: 300 (5 Minuten). Min: 60, Max: 86400 (24 h).', 'open-data-wizard' ); ?>
		</p>
		<?php
	}

	/**
	 * Renders the MQA URL-reachability settings field.
	 */
	public static function field_mqa_check_urls(): void {
		$checked = (bool) self::get( 'mqa_check_urls' );
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( self::OPTION_KEY . '[mqa_check_urls]' ); ?>"
				value="1"
				<?php checked( $checked ); ?>
			>
			<?php esc_html_e( 'Zugriffs- und Download-URLs beim Speichern per HTTP-HEAD auf Erreichbarkeit prüfen (MQA-Zugänglichkeit, +80 Punkte).', 'open-data-wizard' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Sendet ausgehende Anfragen an die angegebenen Datensatz-URLs. Ergebnisse werden 24 Stunden zwischengespeichert. Standardmäßig deaktiviert.', 'open-data-wizard' ); ?>
		</p>
		<?php
	}

	/**
	 * Renders the delete-on-uninstall settings field.
	 */
	public static function field_delete_on_uninstall(): void {
		$checked = (bool) self::get( 'delete_on_uninstall' );
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( self::OPTION_KEY . '[delete_on_uninstall]' ); ?>"
				value="1"
				<?php checked( $checked ); ?>
			>
			<?php esc_html_e( 'Alle Datensätze, Metadaten und Plugin-Optionen beim Deinstallieren unwiderruflich löschen.', 'open-data-wizard' ); ?>
		</label>
		<?php
	}

	// -------------------------------------------------------------------------
	// Sanitierung
	// -------------------------------------------------------------------------

	/**
	 * Sanitizes and validates the settings array before saving.
	 *
	 * @param array<string, mixed> $input Raw input array from the settings form.
	 * @return array<string, mixed> Sanitized settings array.
	 */
	public static function sanitize( array $input ): array {
		$defaults = self::get_defaults();

		$output                        = array();
		$output['catalog_title']       = sanitize_text_field( $input['catalog_title'] ?? '' );
		$output['catalog_description'] = sanitize_textarea_field( $input['catalog_description'] ?? '' );
		$output['default_publisher']   = sanitize_text_field( $input['default_publisher'] ?? '' );
		$output['default_license']     = sanitize_text_field( $input['default_license'] ?? '' );
		$output['delete_on_uninstall'] = ! empty( $input['delete_on_uninstall'] ) ? '1' : '0';
		$output['mqa_check_urls']      = ! empty( $input['mqa_check_urls'] ) ? '1' : '0';

		// Migrate legacy ISO language codes to EU language URIs.
		$lang_raw                   = sanitize_text_field( $input['default_language'] ?? '' );
		$lang_map                   = array(
			'de' => 'http://publications.europa.eu/resource/authority/language/DEU',
			'en' => 'http://publications.europa.eu/resource/authority/language/ENG',
		);
		$output['default_language'] = $lang_map[ $lang_raw ] ?? $lang_raw;

		$ttl                 = (int) ( $input['cache_ttl'] ?? $defaults['cache_ttl'] );
		$output['cache_ttl'] = max( 60, min( 86400, $ttl ) );

		// Cache nach Einstellungsänderung invalidieren.
		ODW_Rest_API::delete_catalog_transients_public();

		return $output;
	}

	// -------------------------------------------------------------------------
	// Aktionen
	// -------------------------------------------------------------------------

	/**
	 * Berechnet Qualitätsscores aller veröffentlichten Datensätze neu.
	 */
	public static function handle_recalculate_quality(): void {
		check_admin_referer( 'odw_recalculate_quality' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'open-data-wizard' ) );
		}

		$posts = get_posts(
			array(
				'post_type'      => 'odw_dataset',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$count = 0;
		foreach ( $posts as $post_id ) {
			ODW_Quality::store( (int) $post_id, ODW_Quality::calculate( (int) $post_id ) );
			++$count;
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'             => 'odw-settings',
					'odw_recalculated' => $count,
				),
				admin_url( 'edit.php?post_type=odw_dataset' )
			)
		);
		exit;
	}

	// -------------------------------------------------------------------------
	// Filter
	// -------------------------------------------------------------------------

	/**
	 * Filter callback for `odw_catalog_title`: returns stored custom title or falls back to default.
	 *
	 * @param string $odw_default Default catalog title provided by the caller.
	 * @return string Custom title when set, default otherwise.
	 */
	public static function filter_catalog_title( string $odw_default ): string {
		$custom = trim( self::get( 'catalog_title' ) );
		return '' !== $custom ? $custom : $odw_default;
	}

	/**
	 * Filter callback for `odw_catalog_description`: returns stored catalog description.
	 *
	 * @param string $fallback Fallback empty string provided by the caller.
	 * @return string Catalog description when set, otherwise fallback.
	 */
	public static function filter_catalog_description( string $fallback ): string {
		$stored = trim( (string) self::get( 'catalog_description' ) );
		return '' !== $stored ? $stored : $fallback;
	}

	// -------------------------------------------------------------------------
	// Datenzugriff
	// -------------------------------------------------------------------------

	/**
	 * Returns all settings or a single value by key.
	 *
	 * @param string $key Setting key. Pass empty string to get all settings.
	 * @return mixed All settings array, or the individual value (or null if key unknown).
	 */
	public static function get( string $key = '' ) {
		$settings = (array) get_option( self::OPTION_KEY, array() );
		$settings = array_merge( self::get_defaults(), $settings );

		if ( '' === $key ) {
			return $settings;
		}

		return $settings[ $key ] ?? null;
	}

	/**
	 * Returns the default settings values.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_defaults(): array {
		return array(
			'catalog_title'       => '',
			'catalog_description' => '',
			'default_publisher'   => '',
			'default_license'     => '',
			'default_language'    => '',
			'cache_ttl'           => 300,
			'delete_on_uninstall' => '0',
			'mqa_check_urls'      => '0',
		);
	}
}
