<?php
/**
 * Shortcode [odw_dataset id="…"] — Download-Card für Frontend
 *
 * Liest Metadaten direkt via get_post_meta(); keine Carbon Fields Abhängigkeit.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ODW_Shortcode {

    public static function init(): void {
        add_shortcode( 'odw_dataset', [ self::class, 'render' ] );
        add_action( 'wp_enqueue_scripts', [ self::class, 'register_assets' ] );
    }

    /**
     * Nur registrieren — tatsächlich eingebunden wird erst beim Rendern,
     * damit CSS nur auf Seiten geladen wird, die den Shortcode verwenden.
     */
    public static function register_assets(): void {
        wp_register_style(
            'odw-frontend',
            ODW_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            ODW_VERSION
        );
    }

    /**
     * Shortcode-Handler — gibt HTML zurück, kein direktes echo.
     *
     * @param array<string, string>|string $atts
     */
    public static function render( $atts ): string {
        $atts    = shortcode_atts( [ 'id' => '0' ], $atts, 'odw_dataset' );
        $post_id = absint( $atts['id'] );

        if ( ! $post_id ) {
            return '';
        }

        $post = get_post( $post_id );

        if ( ! $post || 'odw_dataset' !== $post->post_type || 'publish' !== $post->post_status ) {
            return '';
        }

        wp_enqueue_style( 'odw-frontend' );

        // --- Metadaten ---
        $title         = get_the_title( $post );
        $theme         = (string) get_post_meta( $post_id, '_odw_theme', true );
        $license_uri   = (string) get_post_meta( $post_id, '_odw_license', true );
        $license_label = ODW_Fields::get_license_label( $license_uri );
        $quality_level = (string) get_post_meta( $post_id, '_odw_quality_level', true );
        $quality_score = (int)    get_post_meta( $post_id, '_odw_quality_score', true );
        $file_id       = (int)    get_post_meta( $post_id, '_odw_file_id', true );

        // --- Datei-Informationen aus der Mediathek ---
        $file_url    = '';
        $file_size   = '';
        $file_format = '';

        if ( $file_id > 0 ) {
            $url = wp_get_attachment_url( $file_id );
            if ( $url ) {
                $file_url = $url;

                // Use pre-computed meta (set on save) — fall back to runtime on old entries.
                $stored_format = (string) get_post_meta( $post_id, '_odw_file_format', true );
                $file_format   = $stored_format ?: strtoupper( (string) pathinfo( $url, PATHINFO_EXTENSION ) );

                $stored_size = get_post_meta( $post_id, '_odw_file_size', true );
                if ( $stored_size !== '' && is_numeric( $stored_size ) ) {
                    $file_size = self::format_bytes( (int) $stored_size );
                } else {
                    $file_path = get_attached_file( $file_id );
                    if ( $file_path && is_readable( $file_path ) ) {
                        $file_size = self::format_bytes( (int) filesize( $file_path ) );
                    }
                }
            }
        }

        // --- Qualitätslabel ---
        $quality_label = '';
        if ( '' !== $quality_level ) {
            $quality_label = ODW_Quality::get_level_label( $quality_level );
            if ( $quality_score > 0 ) {
                $quality_label .= ' (' . $quality_score . '/100)';
            }
        }

        // --- HTML aufbauen ---
        ob_start();
        ?>
        <article class="odw-download-card">

            <div class="odw-download-card__header">
                <h3 class="odw-download-card__title"><?php echo esc_html( $title ); ?></h3>
                <?php if ( '' !== $theme ) : ?>
                    <span class="odw-download-card__theme"><?php echo esc_html( $theme ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( $license_label || $quality_label ) : ?>
            <dl class="odw-download-card__meta">

                <?php if ( $license_label ) : ?>
                <div class="odw-download-card__meta-row">
                    <dt><?php esc_html_e( 'Lizenz', 'open-data-wizard' ); ?></dt>
                    <dd><?php echo esc_html( $license_label ); ?></dd>
                </div>
                <?php endif; ?>

                <?php if ( $quality_label ) : ?>
                <div class="odw-download-card__meta-row">
                    <dt><?php esc_html_e( 'DCAT-Qualität', 'open-data-wizard' ); ?></dt>
                    <dd><?php echo esc_html( $quality_label ); ?></dd>
                </div>
                <?php endif; ?>

            </dl>
            <?php endif; ?>

            <?php if ( '' !== $file_url ) : ?>
            <div class="odw-download-card__footer">
                <a
                    href="<?php echo esc_url( $file_url ); ?>"
                    class="odw-download-card__button"
                    download
                >
                    <?php esc_html_e( 'Herunterladen', 'open-data-wizard' ); ?>
                </a>

                <?php
                $file_info_parts = array_filter( [ $file_format, $file_size ] );
                if ( ! empty( $file_info_parts ) ) :
                ?>
                <span class="odw-download-card__file-info">
                    <?php echo esc_html( implode( ' · ', $file_info_parts ) ); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </article>
        <?php
        return (string) ob_get_clean();
    }

    private static function format_bytes( int $bytes ): string {
        if ( $bytes >= 1_073_741_824 ) {
            return round( $bytes / 1_073_741_824, 1 ) . ' GB';
        }
        if ( $bytes >= 1_048_576 ) {
            return round( $bytes / 1_048_576, 1 ) . ' MB';
        }
        if ( $bytes >= 1_024 ) {
            return round( $bytes / 1_024, 1 ) . ' KB';
        }
        return $bytes . ' B';
    }
}
