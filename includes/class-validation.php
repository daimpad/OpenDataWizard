<?php
/**
 * Pflichtfeldvalidierung vor dem Statuswechsel auf „Veröffentlicht"
 *
 * Blockiert publish wenn Pflichtfelder fehlen und zeigt Admin-Notice
 * mit konkreten Feldnamen.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocks publishing of odw_dataset posts that fail required-field validation.
 *
 * @package OpenDataWizard
 */
class ODW_Validation {

	/** Transient-Prefix für Validierungsfehler (per Post-ID). */
	private const TRANSIENT_PREFIX = 'odw_validation_errors_';

	/**
	 * Registers WordPress hooks.
	 */
	public static function init(): void {
		add_filter( 'wp_insert_post_data', array( self::class, 'intercept_publish' ), 10, 2 );
		add_action( 'admin_notices', array( self::class, 'show_validation_notice' ) );
	}

	/**
	 * Intercept the post save and prevent publishing if required fields are missing.
	 * Runs before post is written to DB.
	 *
	 * @param array<string, mixed> $data    Sanitised post data to be inserted.
	 * @param array<string, mixed> $postarr Raw $_POST data.
	 * @return array<string, mixed>
	 */
	public static function intercept_publish( array $data, array $postarr ): array {
		// Only act on odw_dataset posts being set to publish.
		if ( 'odw_dataset' !== $data['post_type'] ) {
			return $data;
		}

		if ( 'publish' !== $data['post_status'] ) {
			return $data;
		}

		$post_id = (int) ( $postarr['ID'] ?? 0 );

		if ( ! $post_id ) {
			return $data;
		}

		// Skip if prior status was already publish (re-saving a published post is OK
		// as long as fields aren't removed — validated below).
		$errors = self::validate( $post_id, $postarr );

		if ( empty( $errors ) ) {
			return $data;
		}

		// Revert status to draft.
		$data['post_status'] = 'draft';

		// Store errors so the admin notice can display them.
		set_transient(
			self::TRANSIENT_PREFIX . $post_id,
			$errors,
			300 // 5 Minuten
		);

		return $data;
	}

	/**
	 * Validate required fields.
	 *
	 * Carbon Fields saves to post_meta directly before/during save_post.
	 * At wp_insert_post_data time, the CF values may not yet be in the DB,
	 * so we additionally look at $_POST['carbon_fields_compact_input'].
	 *
	 * Each error is a structured entry so the admin notice can point at the exact
	 * tab and field (B2): { label, dcat, tab, target, section }.
	 *
	 * @param int                  $post_id  Post ID.
	 * @param array<string, mixed> $postarr  Raw $_POST data.
	 * @return array<int, array{label: string, dcat: string, tab: int, target: string, section: string}>
	 *         Structured error entries (empty = valid).
	 */
	private static function validate( int $post_id, array $postarr ): array {
		$errors = array();

		// Carbon Fields stores compact input in a JSON blob during save.
		$cf_input = self::get_carbon_input( $postarr );

		// --- Titel (WP-native, nicht in Carbon Fields) ---
		$title = trim( (string) ( $postarr['post_title'] ?? '' ) );
		if ( '' === $title ) {
			$errors[] = self::error( 'title' );
		}

		// --- Pflichtfelder aus zentraler Registry (ODW_Fields::get_required_fields) ---
		foreach ( ODW_Fields::get_required_fields() as $field ) {
			$value = self::get_field_value( $post_id, $cf_input, $field['meta_key'] );
			if ( '' === trim( (string) $value ) ) {
				$errors[] = self::error( $field['key'], $field['label'] );
			}
		}

		// --- Mindestens 1 Distribution mit Zugriffs-URL ---
		$has_distribution = self::has_valid_distribution( $post_id, $cf_input );
		if ( ! $has_distribution ) {
			$errors[] = self::error( 'distribution' );
		}

		// --- Lizenz in jeder Distribution (Änderung 7) ---
		if ( $has_distribution && ! self::all_distributions_have_license( $post_id, $cf_input ) ) {
			$errors[] = self::error( 'license' );
		}

		// --- HVD: Kategorie ist Pflicht, wenn als High-Value-Datensatz markiert ---
		$is_hvd = (string) self::get_field_value( $post_id, $cf_input, '_odw_is_hvd' );
		if ( 'yes' === $is_hvd ) {
			$hvd_category = (string) self::get_field_value( $post_id, $cf_input, '_odw_hvd_category' );
			if ( '' === trim( $hvd_category ) ) {
				$errors[] = self::error( 'hvd_category' );
			}
		}

		return $errors;
	}

	/**
	 * Build a structured error entry for a required-field key.
	 *
	 * Central catalogue (form-language label + technical DCAT term + tab number +
	 * DOM target + optional collapsible section) so the admin notice can render a
	 * clickable "jump to field" link that switches tab and expands the group.
	 *
	 * @param string $key      Registry/error key.
	 * @param string $fallback Optional label fallback (registry label) for keys not in the map.
	 * @return array{label: string, dcat: string, tab: int, target: string, section: string}
	 */
	private static function error( string $key, string $fallback = '' ): array {
		$map = array(
			'title'        => array(
				'label'   => __( 'Titel', 'open-data-wizard' ),
				'dcat'    => 'dct:title',
				'tab'     => 0,
				'target'  => 'title',
				'section' => '',
			),
			'publisher'    => array(
				'label'   => __( 'Herausgebende Organisation', 'open-data-wizard' ),
				'dcat'    => 'dct:publisher',
				'tab'     => 1,
				'target'  => '_odw_publisher',
				'section' => '',
			),
			'description'  => array(
				'label'   => __( 'Beschreibung', 'open-data-wizard' ),
				'dcat'    => 'dct:description',
				'tab'     => 1,
				'target'  => '_odw_description',
				'section' => '',
			),
			'distribution' => array(
				'label'   => __( 'Link zur Datei oder Datei-Upload', 'open-data-wizard' ),
				'dcat'    => 'dcat:accessURL',
				'tab'     => 3,
				'target'  => '_odw_access_url',
				'section' => '',
			),
			'license'      => array(
				'label'   => __( 'Lizenz', 'open-data-wizard' ),
				'dcat'    => 'dct:license',
				'tab'     => 3,
				'target'  => '_odw_license',
				'section' => '',
			),
			'hvd_category' => array(
				'label'   => __( 'HVD-Kategorie', 'open-data-wizard' ),
				'dcat'    => 'dcatap:hvdCategory',
				'tab'     => 4,
				'target'  => '_odw_hvd_category',
				'section' => 'hvd',
			),
		);

		$entry = $map[ $key ] ?? array(
			'label'   => '' !== $fallback ? $fallback : $key,
			'dcat'    => '',
			'tab'     => 0,
			'target'  => '',
			'section' => '',
		);

		return array(
			'label'   => (string) $entry['label'],
			'dcat'    => (string) $entry['dcat'],
			'tab'     => (int) $entry['tab'],
			'target'  => (string) $entry['target'],
			'section' => (string) $entry['section'],
		);
	}

	/**
	 * Human-readable tab name for the given 1-based tab number (0 = no tab / post title).
	 *
	 * @param int $tab Tab number.
	 * @return string
	 */
	private static function tab_name( int $tab ): string {
		$names = array(
			1 => __( 'Grundlegende Informationen', 'open-data-wizard' ),
			2 => __( 'Sprache & Übersetzungen', 'open-data-wizard' ),
			3 => __( 'Datenbereitstellung', 'open-data-wizard' ),
			4 => __( 'Erweiterte Angaben', 'open-data-wizard' ),
			5 => __( 'Vorschau', 'open-data-wizard' ),
		);

		return $names[ $tab ] ?? '';
	}

	/**
	 * Get a field value: prefer CF compact input (new save), fall back to existing meta.
	 *
	 * @param int                  $post_id   Post ID.
	 * @param array<string, mixed> $cf_input  Decoded Carbon Fields compact input.
	 * @param string               $meta_key  DB meta key (underscore-prefixed, e.g. _odw_publisher).
	 * @return mixed
	 */
	private static function get_field_value( int $post_id, array $cf_input, string $meta_key ): mixed {
		if ( isset( $cf_input[ $meta_key ] ) ) {
			return $cf_input[ $meta_key ];
		}

		return get_post_meta( $post_id, $meta_key, true );
	}

	/**
	 * Check whether the post has a valid access_url.
	 *
	 * @param int                  $post_id  Post ID.
	 * @param array<string, mixed> $cf_input Decoded Carbon Fields compact input.
	 */
	private static function has_valid_distribution( int $post_id, array $cf_input ): bool {
		if ( self::has_primary_distribution( $post_id, $cf_input ) ) {
			return true;
		}

		// Zusätzliche Distributionen (Repeater) zählen ebenfalls.
		foreach ( self::get_extra_distribution_rows( $post_id, $cf_input ) as $row ) {
			if ( '' !== $row['access_url'] && self::is_valid_url( $row['access_url'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Primäre Distribution vorhanden? (Zugriffs-URL oder Mediathek-Upload.)
	 *
	 * @param int                  $post_id  Post ID.
	 * @param array<string, mixed> $cf_input Decoded Carbon Fields compact input.
	 */
	private static function has_primary_distribution( int $post_id, array $cf_input ): bool {
		// Check CF compact input first.
		$access_url = (string) ( $cf_input['_odw_access_url'] ?? '' );
		if ( ! empty( $access_url ) && self::is_valid_url( $access_url ) ) {
			return true;
		}

		// Fall back to existing meta.
		$access_url = (string) carbon_get_post_meta( $post_id, 'odw_access_url' );
		if ( ! empty( $access_url ) && self::is_valid_url( $access_url ) ) {
			return true;
		}

		// A media-library upload also counts as a valid distribution — its access
		// URL is derived from the file on save (see ODW_Admin::save_file_attachment).
		return self::get_effective_file_id( $post_id ) > 0;
	}

	/**
	 * Effektive Mediathek-Datei-ID: Der POST-Wert (mit Nonce) hat Vorrang vor dem
	 * gespeicherten Meta — sonst würde das Entfernen der Datei (POST=0) im selben
	 * Save von der alten Meta-ID überdeckt und ein Datensatz ohne Distribution
	 * bliebe veröffentlicht.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function get_effective_file_id( int $post_id ): int {
		if ( isset( $_POST['_odw_file_id'], $_POST['odw_file_upload_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['odw_file_upload_nonce'] ) ), 'odw_save_file_attachment' )
		) {
			return absint( wp_unslash( $_POST['_odw_file_id'] ) );
		}

		return (int) get_post_meta( $post_id, '_odw_file_id', true );
	}

	/**
	 * Zusätzliche Distributionen als normalisierte Zeilen.
	 *
	 * Compact-Input-Zeilen tragen Unterfeld-Schlüssel MIT führendem Unterstrich
	 * (_access_url), gespeicherte Meta-Zeilen (carbon_get_post_meta) OHNE.
	 *
	 * @param int                  $post_id  Post ID.
	 * @param array<string, mixed> $cf_input Decoded Carbon Fields compact input.
	 * @return array<int, array{access_url: string, license: string, license_custom: string}>
	 */
	private static function get_extra_distribution_rows( int $post_id, array $cf_input ): array {
		$raw = $cf_input['_odw_extra_distributions'] ?? carbon_get_post_meta( $post_id, 'odw_extra_distributions' );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$rows = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$rows[] = array(
				'access_url'     => trim( (string) ( $row['_access_url'] ?? $row['access_url'] ?? '' ) ),
				'license'        => trim( (string) ( $row['_license'] ?? $row['license'] ?? '' ) ),
				'license_custom' => trim( (string) ( $row['_license_custom'] ?? $row['license_custom'] ?? '' ) ),
			);
		}

		return $rows;
	}

	/**
	 * Check that every distribution (primary + extras) carries a license.
	 *
	 * @param int                  $post_id  Post ID.
	 * @param array<string, mixed> $cf_input Decoded Carbon Fields compact input.
	 */
	private static function all_distributions_have_license( int $post_id, array $cf_input ): bool {
		// Primäre Distribution (URL ODER Datei-Upload) → Lizenz Pflicht.
		if ( self::has_primary_distribution( $post_id, $cf_input ) ) {
			$license = (string) ( $cf_input['_odw_license'] ?? carbon_get_post_meta( $post_id, 'odw_license' ) );
			if ( '' === $license ) {
				return false;
			}

			if ( 'sonstige' === $license ) {
				$custom = (string) ( $cf_input['_odw_license_custom'] ?? carbon_get_post_meta( $post_id, 'odw_license_custom' ) );
				if ( empty( $custom ) ) {
					return false;
				}
			}
		}

		// Jede zusätzliche Distribution mit Zugriffs-URL braucht ebenfalls eine Lizenz.
		foreach ( self::get_extra_distribution_rows( $post_id, $cf_input ) as $row ) {
			if ( '' === $row['access_url'] ) {
				continue;
			}
			if ( '' === $row['license'] ) {
				return false;
			}
			if ( 'sonstige' === $row['license'] && '' === $row['license_custom'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate that a string is a safe HTTP(S) URL.
	 * Blocks javascript:, data:, and other non-HTTP schemes.
	 *
	 * @param string $url URL to validate.
	 * @return bool True when scheme is http, https, ftp, or ftps.
	 */
	private static function is_valid_url( string $url ): bool {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
		return in_array( $scheme, array( 'http', 'https', 'ftp', 'ftps' ), true );
	}

	/**
	 * Parse the Carbon Fields compact JSON input from $_POST.
	 *
	 * @param array<string, mixed> $postarr Raw $_POST data passed to wp_insert_post_data.
	 * @return array<string, mixed> Decoded field map, empty array on failure.
	 */
	private static function get_carbon_input( array $postarr ): array {
		$raw = $postarr['carbon_fields_compact_input'] ?? '';

		if ( empty( $raw ) ) {
			return array();
		}

		if ( is_array( $raw ) ) {
			return $raw;
		}

		$decoded = json_decode( (string) $raw, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Display Admin Notice if validation errors are stored for the current post.
	 */
	public static function show_validation_notice(): void {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing -- read-only: used only to look up stored transient errors.
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : ( isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0 );

		if ( ! $post_id ) {
			return;
		}

		$errors = get_transient( self::TRANSIENT_PREFIX . $post_id );

		if ( ! is_array( $errors ) || empty( $errors ) ) {
			return;
		}

		delete_transient( self::TRANSIENT_PREFIX . $post_id );

		echo '<div class="notice notice-error odw-validation-notice is-dismissible">';
		echo '<p><strong>' . esc_html__( 'Open Data Wizard: Veröffentlichung blockiert', 'open-data-wizard' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Folgende Pflichtangaben fehlen oder sind leer:', 'open-data-wizard' ) . '</p>';
		echo '<ul class="odw-missing-fields">';

		foreach ( $errors as $error ) {
			// Backward-compat: a plain string (e.g. from an older stored transient)
			// is rendered as-is without a jump link.
			if ( ! is_array( $error ) ) {
				echo '<li>' . esc_html( (string) $error ) . '</li>';
				continue;
			}

			$tab      = (int) ( $error['tab'] ?? 0 );
			$tab_name = self::tab_name( $tab );
			$label    = (string) ( $error['label'] ?? '' );
			$dcat     = (string) ( $error['dcat'] ?? '' );
			$target   = (string) ( $error['target'] ?? '' );
			$section  = (string) ( $error['section'] ?? '' );

			echo '<li>';

			if ( '' !== $tab_name ) {
				/* translators: %s: form tab name. */
				echo '<span class="odw-missing-tab">' . esc_html( sprintf( __( 'Tab %s', 'open-data-wizard' ), $tab_name ) ) . ':</span> ';
			}

			echo '<span class="odw-missing-label">' . esc_html( $label ) . '</span>';

			if ( '' !== $dcat ) {
				echo ' <span class="odw-missing-dcat">(' . esc_html( $dcat ) . ')</span>';
			}

			if ( '' !== $target ) {
				printf(
					' <button type="button" class="button-link odw-goto-field" data-odw-goto-tab="%1$d" data-odw-goto-target="%2$s" data-odw-goto-section="%3$s">%4$s</button>',
					(int) $tab,
					esc_attr( $target ),
					esc_attr( $section ),
					esc_html__( 'Zum Feld springen', 'open-data-wizard' )
				);
			}

			echo '</li>';
		}

		echo '</ul>';
		echo '<p>' . esc_html__( 'Der Datensatz wurde als Entwurf gespeichert. Bitte alle Pflichtangaben befüllen und erneut veröffentlichen. Als Entwurf können Sie den Datensatz jederzeit speichern.', 'open-data-wizard' ) . '</p>';
		echo '</div>';
	}
}
