<?php
/**
 * Shortcode [odw_dataset id="…"] — Download-Card für Frontend
 *
 * Liest Metadaten via Carbon Fields / get_post_meta(); keine harte CF-Abhängigkeit.
 *
 * Aufbau der Karte (v2.18.0):
 *   1. Datensatzname (groß) + Link „Metadaten JSON" (rechts)
 *   2. Download-Button zur bereitgestellten Datei
 *   3. Bunte Badges: Dateiformat · Dateigröße · Lizenz
 *   4. Aufklappbares Accordion mit allen angegebenen Metadatenfeldern
 *
 * CSS wird lazy eingebunden: assets/css/frontend.css nur auf Seiten mit Shortcode.
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
	 * Nur registrieren — tatsächlich eingebunden wird erst beim Rendern.
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
	 * Reads a Carbon Fields value with a get_post_meta fallback.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Carbon field key (e.g. 'odw_publisher').
	 * @return string
	 */
	private static function meta( int $post_id, string $key ): string {
		if ( function_exists( 'carbon_get_post_meta' ) ) {
			return trim( (string) carbon_get_post_meta( $post_id, $key ) );
		}
		return trim( (string) get_post_meta( $post_id, '_' . $key, true ) );
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

		$title = get_the_title( $post );

		// --- License label ---
		$license_label = '';
		$license_uri   = self::meta( $post_id, 'odw_license' );
		if ( 'sonstige' === $license_uri ) {
			$license_label = self::meta( $post_id, 'odw_license_custom' );
			if ( class_exists( 'ODW_Fields' ) && '' !== $license_label ) {
				$license_label = ODW_Fields::get_license_label( $license_label );
			}
		} elseif ( '' !== $license_uri && class_exists( 'ODW_Fields' ) ) {
			$license_label = ODW_Fields::get_license_label( $license_uri );
		}

		// --- File (media library) ---
		$file_id     = (int) get_post_meta( $post_id, '_odw_file_id', true );
		$file_url    = '';
		$file_size   = '';
		$file_format = '';

		if ( $file_id > 0 ) {
			$url = wp_get_attachment_url( $file_id );
			if ( $url ) {
				$file_url      = $url;
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

		// Format from the form takes precedence over the derived file extension.
		$form_format = self::meta( $post_id, 'odw_format' );
		if ( '' !== $form_format ) {
			$file_format = $form_format;
		}

		// byteSize from the form when no media-library size is available.
		if ( '' === $file_size ) {
			$byte_size = (int) self::meta( $post_id, 'odw_byte_size' );
			if ( $byte_size > 0 ) {
				$file_size = self::format_bytes( $byte_size );
			}
		}

		// --- Download target: downloadURL → media file → accessURL ---
		$download_url = self::meta( $post_id, 'odw_download_url' );
		if ( '' === $download_url ) {
			$download_url = '' !== $file_url ? $file_url : self::meta( $post_id, 'odw_access_url' );
		}

		$metadata_url = rest_url( 'datenatlas/v1/datasets/' . $post_id );
		$meta_rows    = self::collect_metadata_rows( $post_id, $license_label, $file_size );

		// --- HTML ---
		ob_start();
		?>
		<article class="odw-download-card">

			<div class="odw-download-card__top">
				<h2 class="odw-download-card__title"><?php echo esc_html( $title ); ?></h2>
				<a
					class="odw-download-card__metajson"
					href="<?php echo esc_url( $metadata_url ); ?>"
					download="<?php echo esc_attr( 'metadaten-' . $post_id . '.json' ); ?>"
				>
					<span aria-hidden="true">⤓</span> <?php esc_html_e( 'Metadaten JSON', 'open-data-wizard' ); ?>
				</a>
			</div>

			<?php if ( '' !== $download_url ) : ?>
			<a
				class="odw-download-card__download"
				href="<?php echo esc_url( $download_url ); ?>"
				download
			>
				<span aria-hidden="true">⬇</span> <?php esc_html_e( 'Datei herunterladen', 'open-data-wizard' ); ?>
			</a>
			<?php endif; ?>

			<?php
			$badges = array();
			if ( '' !== $file_format ) {
				$badges[] = array( 'format', $file_format );
			}
			if ( '' !== $file_size ) {
				$badges[] = array( 'size', $file_size );
			}
			if ( '' !== $license_label ) {
				$badges[] = array( 'license', $license_label );
			}
			if ( ! empty( $badges ) ) :
				?>
			<div class="odw-download-card__badges">
				<?php foreach ( $badges as $badge ) : ?>
					<span class="odw-badge odw-badge--<?php echo esc_attr( $badge[0] ); ?>"><?php echo esc_html( $badge[1] ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $meta_rows ) ) : ?>
			<details class="odw-download-card__details">
				<summary><?php esc_html_e( 'Alle Metadaten anzeigen', 'open-data-wizard' ); ?></summary>
				<dl class="odw-download-card__metalist">
					<?php foreach ( $meta_rows as $row ) : ?>
					<div class="odw-metarow">
						<dt><?php echo esc_html( $row['label'] ); ?></dt>
						<dd>
							<?php
							if ( ! empty( $row['url'] ) ) {
								printf(
									'<a href="%s" target="_blank" rel="noopener">%s</a>',
									esc_url( $row['value'] ),
									esc_html( $row['value'] )
								);
							} else {
								echo nl2br( esc_html( $row['value'] ) );
							}
							?>
						</dd>
					</div>
					<?php endforeach; ?>
				</dl>
			</details>
			<?php endif; ?>

		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Collects all populated metadata fields as label/value rows for the accordion.
	 *
	 * @param int    $post_id       Post ID.
	 * @param string $license_label Resolved license label.
	 * @param string $file_size     Human-readable file size.
	 * @return array<int, array{label: string, value: string, url: bool}>
	 */
	private static function collect_metadata_rows( int $post_id, string $license_label, string $file_size ): array {
		$rows = array();

		$add = static function ( string $label, string $value, bool $url = false ) use ( &$rows ): void {
			if ( '' !== trim( $value ) ) {
				$rows[] = array(
					'label' => $label,
					'value' => trim( $value ),
					'url'   => $url,
				);
			}
		};

		$add( __( 'Herausgeber', 'open-data-wizard' ), self::meta( $post_id, 'odw_publisher' ) );
		$add( __( 'Beschreibung', 'open-data-wizard' ), self::meta( $post_id, 'odw_description' ) );
		$add( __( 'Thema', 'open-data-wizard' ), self::meta( $post_id, 'odw_theme' ) );
		$add( __( 'CESSDA-Themenfeld', 'open-data-wizard' ), self::meta( $post_id, 'odw_cessda_topic' ), true );
		$add( __( 'Sprache', 'open-data-wizard' ), self::meta( $post_id, 'odw_language' ), true );

		$keywords = array_filter( array_map( 'trim', explode( "\n", self::meta( $post_id, 'odw_keywords' ) ) ) );
		$add( __( 'Schlagworte', 'open-data-wizard' ), implode( ', ', $keywords ) );

		$add( __( 'Zugriffs-URL', 'open-data-wizard' ), self::meta( $post_id, 'odw_access_url' ), true );
		$add( __( 'Download-URL', 'open-data-wizard' ), self::meta( $post_id, 'odw_download_url' ), true );
		$add( __( 'Format', 'open-data-wizard' ), self::meta( $post_id, 'odw_format' ) );
		$add( __( 'Media-Type', 'open-data-wizard' ), self::meta( $post_id, 'odw_media_type' ), true );
		$add( __( 'Dateigröße', 'open-data-wizard' ), $file_size );
		$add( __( 'Lizenz', 'open-data-wizard' ), $license_label );
		$add( __( 'Namensnennung', 'open-data-wizard' ), self::meta( $post_id, 'odw_attribution_text' ) );
		$add( __( 'Zugriffsrechte', 'open-data-wizard' ), self::meta( $post_id, 'odw_access_rights' ), true );

		$add( __( 'Räumliche Abdeckung', 'open-data-wizard' ), self::meta( $post_id, 'odw_spatial' ) );

		$t_start = self::meta( $post_id, 'odw_temporal_start' );
		$t_end   = self::meta( $post_id, 'odw_temporal_end' );
		if ( '' !== $t_start || '' !== $t_end ) {
			$add( __( 'Zeitraum', 'open-data-wizard' ), trim( $t_start . ' – ' . $t_end, ' –' ) );
		}

		$add( __( 'Veröffentlicht', 'open-data-wizard' ), self::meta( $post_id, 'odw_issued' ) );
		$add( __( 'Aktualisiert', 'open-data-wizard' ), self::meta( $post_id, 'odw_modified' ) );
		$add( __( 'Projektseite', 'open-data-wizard' ), self::meta( $post_id, 'odw_landing_page' ), true );
		$add( __( 'Aktualisierungsfrequenz', 'open-data-wizard' ), self::meta( $post_id, 'odw_accrual_periodicity' ), true );
		$add( __( 'Urheber', 'open-data-wizard' ), self::meta( $post_id, 'odw_originator_name' ) );
		$add( __( 'Pflegende Stelle', 'open-data-wizard' ), self::meta( $post_id, 'odw_maintainer_name' ) );

		$contact = array_filter(
			array(
				self::meta( $post_id, 'odw_contact_name' ),
				self::meta( $post_id, 'odw_contact_email' ),
			)
		);
		$add( __( 'Kontakt', 'open-data-wizard' ), implode( ' · ', $contact ) );
		$add( __( 'Kontakt-Website', 'open-data-wizard' ), self::meta( $post_id, 'odw_contact_url' ), true );

		return $rows;
	}

	/**
	 * Formatiert eine Byte-Anzahl als lesbare Größenangabe (B / KB / MB / GB).
	 *
	 * @param int $bytes Rohe Byte-Anzahl (aus _odw_file_size oder filesize()).
	 * @return string    Formatierter String, z.B. "2.5 MB".
	 */
	private static function format_bytes( int $bytes ): string {
		if ( $bytes < 0 ) {
			$bytes = 0;
		}
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
