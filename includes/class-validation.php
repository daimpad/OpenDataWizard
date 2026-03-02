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

class ODW_Validation {

    /** Transient-Prefix für Validierungsfehler (per Post-ID). */
    private const TRANSIENT_PREFIX = 'odw_validation_errors_';

    public static function init(): void {
        add_filter( 'wp_insert_post_data', [ self::class, 'intercept_publish' ], 10, 2 );
        add_action( 'admin_notices', [ self::class, 'show_validation_notice' ] );
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
            60 // seconds
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
     * @param int                  $post_id  Post ID.
     * @param array<string, mixed> $postarr  Raw $_POST data.
     * @return string[]  Array of human-readable error messages (empty = valid).
     */
    private static function validate( int $post_id, array $postarr ): array {
        $errors = [];

        // Carbon Fields stores compact input in a JSON blob during save.
        $cf_input = self::get_carbon_input( $postarr );

        // --- Titel ---
        $title = trim( (string) ( $postarr['post_title'] ?? '' ) );
        if ( '' === $title ) {
            $errors[] = __( 'Titel (dct:title)', 'open-data-wizard' );
        }

        // --- Beschreibung ---
        $description = self::get_field_value( $post_id, $cf_input, '_odw_description', 'odw_description' );
        if ( '' === trim( (string) $description ) ) {
            $errors[] = __( 'Beschreibung (dct:description)', 'open-data-wizard' );
        }

        // --- Publisher ---
        $publisher = self::get_field_value( $post_id, $cf_input, '_odw_publisher', 'odw_publisher' );
        if ( '' === trim( (string) $publisher ) ) {
            $errors[] = __( 'Herausgebende Organisation (dct:publisher)', 'open-data-wizard' );
        }

        // --- Lizenz ---
        $license = self::get_field_value( $post_id, $cf_input, '_odw_license', 'odw_license' );
        if ( '' === trim( (string) $license ) ) {
            $errors[] = __( 'Lizenz (dct:license)', 'open-data-wizard' );
        }

        // --- Mindestens 1 Distribution mit Zugriffs-URL ---
        $has_distribution = self::has_valid_distribution( $post_id, $cf_input );
        if ( ! $has_distribution ) {
            $errors[] = __( 'Mindestens eine Distribution mit Zugriffs-URL (dcat:accessURL)', 'open-data-wizard' );
        }

        return $errors;
    }

    /**
     * Get a field value: prefer CF compact input (new save), fall back to existing meta.
     *
     * @param int                  $post_id    Post ID.
     * @param array<string, mixed> $cf_input   Decoded Carbon Fields compact input.
     * @param string               $meta_key   DB meta key (with underscore prefix).
     * @param string               $cf_key     Carbon Fields key (without underscore).
     * @return mixed
     */
    private static function get_field_value( int $post_id, array $cf_input, string $meta_key, string $cf_key ): mixed {
        // Prefer new POST data from Carbon Fields.
        if ( isset( $cf_input[ $meta_key ] ) ) {
            return $cf_input[ $meta_key ];
        }

        // Fall back to existing meta (already saved).
        return get_post_meta( $post_id, $meta_key, true );
    }

    /**
     * Check whether the post has at least one distribution with a non-empty access_url.
     *
     * @param int                  $post_id  Post ID.
     * @param array<string, mixed> $cf_input Decoded Carbon Fields compact input.
     */
    private static function has_valid_distribution( int $post_id, array $cf_input ): bool {
        // Check CF compact input for new distributions.
        foreach ( $cf_input as $key => $value ) {
            // CF compact keys for complex fields look like: _odw_distributions[0][access_url]
            if ( str_contains( (string) $key, '_odw_distributions' ) && str_contains( (string) $key, 'access_url' ) ) {
                if ( ! empty( $value ) ) {
                    return true;
                }
            }
        }

        // Fall back to existing meta.
        $distributions = carbon_get_post_meta( $post_id, 'odw_distributions' );

        if ( ! is_array( $distributions ) ) {
            return false;
        }

        foreach ( $distributions as $dist ) {
            if ( ! empty( $dist['access_url'] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse the Carbon Fields compact JSON input from $_POST.
     *
     * @param array<string, mixed> $postarr
     * @return array<string, mixed>
     */
    private static function get_carbon_input( array $postarr ): array {
        $raw = $postarr['carbon_fields_compact_input'] ?? '';

        if ( empty( $raw ) ) {
            return [];
        }

        if ( is_array( $raw ) ) {
            return $raw;
        }

        $decoded = json_decode( (string) $raw, true );
        return is_array( $decoded ) ? $decoded : [];
    }

    /**
     * Display Admin Notice if validation errors are stored for the current post.
     */
    public static function show_validation_notice(): void {
        $screen = get_current_screen();

        if ( ! $screen || ! in_array( $screen->base, [ 'post', 'post-new' ], true ) ) {
            return;
        }

        $post_id = (int) ( $_GET['post'] ?? $_POST['post_ID'] ?? 0 );

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
        echo '<p>' . esc_html__( 'Folgende Pflichtfelder fehlen oder sind leer:', 'open-data-wizard' ) . '</p>';
        echo '<ul class="odw-missing-fields">';

        foreach ( $errors as $field_label ) {
            echo '<li>' . esc_html( $field_label ) . '</li>';
        }

        echo '</ul>';
        echo '<p>' . esc_html__( 'Der Datensatz wurde als Entwurf gespeichert. Bitte alle Pflichtfelder befüllen und erneut veröffentlichen.', 'open-data-wizard' ) . '</p>';
        echo '</div>';
    }
}
