<?php
/**
 * Shortcode [odw_dataset id="…"] — Download-Card für Frontend
 *
 * Liest Metadaten direkt via get_post_meta(); keine Carbon Fields Abhängigkeit.
 *
 * Datei-Metadaten (_odw_file_size, _odw_file_format) werden seit v1.8.0 beim
 * Speichern vorberechnet (ODW_Admin::save_file_attachment) und hier direkt
 * gelesen. Für ältere Datensätze ohne diese Meta-Einträge greift ein Fallback
 * auf filesize()/pathinfo() zur Laufzeit.
 *
 * CSS wird lazy per wp_register_style / wp_enqueue_style eingebunden:
 * assets/css/frontend.css wird nur auf Seiten geladen, die den Shortcode rendern.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Download-Card shortcode [odw_dataset id="…"].
 *
 * @package OpenDataWizard
 */
class ODW_Shortcode {

	/**
	 * Registers the shortcode and enqueue hook.
	 */
	public static function init(): void {
		add_shortcode( 'odw_dataset', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_assets' ) );
	}

	/**
	 * Nur registrieren — tatsächlich eingebunden wird erst beim Rendern,
	 * damit CSS nur auf Seiten geladen wird, die den Shortcode verwenden.
	 */
	public static function register_assets(): void {
		wp_register_style(
			'odw-frontend',
			ODW_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			ODW_VERSION
		);
	}

	/**
	 * Shortcode-Handler — gibt HTML zurück, kein direktes echo.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes. Expects `id` with the post ID.
	 * @return string Rendered HTML or empty string when the post is not found/published.
	 */
	public static function render( $atts ): string {
		$atts    = shortcode_atts( array( 'id' => '0' ), $atts, 'odw_dataset' );
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
		$title   = get_the_title( $post );
		$theme   = (string) get_post_meta( $post_id, '_odw_theme', true );
		$file_id = (int) get_post_meta( $post_id, '_odw_file_id', true );

		// License from post meta.
		$license_label = '';
		if ( function_exists( 'carbon_get_post_meta' ) ) {
			$license_uri = (string) carbon_get_post_meta( $post_id, 'odw_license' );
			if ( 'sonstige' === $license_uri ) {
				$license_label = (string) carbon_get_post_meta( $post_id, 'odw_license_custom' );
			} elseif ( '' !== $license_uri ) {
				$license_label = ODW_Fields::get_license_label( $license_uri );
			}
		}

		// Keywords: newline-separated string → array of trimmed, non-empty values.
		$keywords_raw = (string) get_post_meta( $post_id, '_odw_keywords', true );
		$keywords     = array_filter( array_map( 'trim', explode( "\n", $keywords_raw ) ) );

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
				$file_format   = $stored_format ? $stored_format : strtoupper( (string) pathinfo( $url, PATHINFO_EXTENSION ) );

				$stored_size = get_post_meta( $post_id, '_odw_file_size', true );
				if ( '' !== $stored_size && is_numeric( $stored_size ) ) {
					$file_size = self::format_bytes( (int) $stored_size );
				} else {
					$file_path = get_attached_file( $file_id );
					if ( $file_path && is_readable( $file_path ) ) {
						$file_size = self::format_bytes( (int) filesize( $file_path ) );
					}
				}
			}
		}

		$metadata_url = rest_url( 'datenatlas/v1/datasets/' . $post_id );

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

			<?php if ( $license_label ) : ?>
			<dl class="odw-download-card__meta">
				<div class="odw-download-card__meta-row">
					<dt><?php esc_html_e( 'Lizenz', 'open-data-wizard' ); ?></dt>
					<dd><?php echo esc_html( $license_label ); ?></dd>
				</div>
			</dl>
			<?php endif; ?>

			<?php if ( ! empty( $keywords ) ) : ?>
			<div class="odw-download-card__keywords">
				<?php foreach ( $keywords as $keyword ) : ?>
					<span class="odw-download-card__keyword"><?php echo esc_html( $keyword ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="odw-download-card__footer">
				<?php if ( '' !== $file_url ) : ?>
				<a
					href="<?php echo esc_url( $file_url ); ?>"
					class="odw-download-card__button"
					download
				>
					<?php esc_html_e( 'Herunterladen', 'open-data-wizard' ); ?>
				</a>

					<?php
					$file_info_parts = array_filter( array( $file_format, $file_size ) );
					if ( ! empty( $file_info_parts ) ) :
						?>
				<span class="odw-download-card__file-info">
						<?php echo esc_html( implode( ' · ', $file_info_parts ) ); ?>
				</span>
					<?php endif; ?>
				<?php endif; ?>

				<a
					href="<?php echo esc_url( $metadata_url ); ?>"
					class="odw-download-card__button odw-download-card__button--meta"
					target="_blank"
					rel="noopener"
				>
					<?php esc_html_e( 'Metadaten (JSON-LD)', 'open-data-wizard' ); ?>
				</a>
			</div>

		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Formatiert eine Byte-Anzahl als lesbare Größenangabe (B / KB / MB / GB).
	 *
	 * @param int $bytes Rohe Byte-Anzahl (aus _odw_file_size oder filesize()).
	 * @return string    Formatierter String, z.B. "2.5 MB".
	 */
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
