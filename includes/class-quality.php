<?php
/**
 * Qualitäts-Scoring nach der EU-MQA-Methodik für odw_dataset
 *
 * Bewertet die Metadatenqualität nach dem Metadata Quality Assessment (MQA) von
 * data.europa.eu: 5 FAIR-Dimensionen, 405 Punkte, 4 Bewertungsstufen. Die Metriken
 * stehen in config/mqa-metrics.php; siehe docs/MQA-KONZEPT.md.
 *
 * Aktuell bewertet werden alle „gesetzt?"-Metriken (offline). Vokabular-,
 * Erreichbarkeits- und SHACL-Metriken sind als „nicht bewertet" verdrahtet und
 * werden aus dem bewertbaren Maximum herausgerechnet; die Bewertungsstufen werden
 * proportional auf das bewertbare Maximum skaliert.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MQA-basiertes Qualitäts-Scoring und Ampellogik für odw_dataset posts.
 *
 * @package OpenDataWizard
 */
class ODW_Quality {

	// MQA-Bewertungsstufen.
	public const RATING_EXCELLENT  = 'excellent';
	public const RATING_GOOD       = 'good';
	public const RATING_SUFFICIENT = 'sufficient';
	public const RATING_BAD        = 'bad';

	// Legacy-Level-Konstanten (Abwärtskompatibilität: Admin-Spalte, Badge-CSS).
	public const LEVEL_PERFECT    = 'perfect';
	public const LEVEL_HIGH       = 'high';
	public const LEVEL_SUFFICIENT = 'sufficient';
	public const LEVEL_LOW        = 'low';

	/** Fallback-Schwelle (Summe aller Pflichtpunkte), falls die Indikator-Config fehlt. */
	private const REQUIRED_ONLY_SCORE = 55;

	/** MQA-Gesamtmaximum. */
	private const MQA_MAX = 405;

	// MQA-Original-Schwellen (Anteile von 405), proportional angewendet.
	private const RATING_EXCELLENT_RATIO  = 351 / 405;
	private const RATING_GOOD_RATIO       = 221 / 405;
	private const RATING_SUFFICIENT_RATIO = 121 / 405;

	/** Dimensionen in Anzeigereihenfolge. */
	private const DIMENSIONS = array( 'findability', 'accessibility', 'interoperability', 'reusability', 'contextuality' );

	/**
	 * Metriken, die das Plugin selbst erfüllt — ohne Zutun der Redaktion.
	 *
	 * `_odw_modified` wird von ODW_Fields::set_modified_date() bei jedem Speichern
	 * geschrieben. Die Metrik ist damit schon nach dem ersten Speichern eines
	 * leeren Entwurfs erfüllt, was ohne Kennzeichnung wie ein Rechenfehler wirkt
	 * ("warum 2 %, wenn noch nichts ausgefüllt ist?").
	 */
	private const AUTO_FULFILLED = array( 'modified' );

	/**
	 * Registers WordPress hooks.
	 */
	public static function init(): void {
		// Qualität nach jedem echten Speichern neu berechnen. Priorität 30 stellt
		// sicher, dass Carbon Fields (Priorität 10) seine Meta bereits geschrieben hat.
		add_action( 'save_post', array( self::class, 'recalculate_on_save' ), 30 );

		// Meta-Box auf dem Edit-Screen registrieren.
		add_action( 'add_meta_boxes', array( self::class, 'register_meta_box' ) );

		// Qualitätsdaten in JSON-LD einbetten (REST API + Vorschau).
		add_filter( 'odw_dataset_jsonld', array( self::class, 'append_to_jsonld' ), 10, 2 );
	}

	// -------------------------------------------------------------------------
	// Metrik-Definitionen
	// -------------------------------------------------------------------------

	/**
	 * Lädt die MQA-Metriken aus config/mqa-metrics.php.
	 *
	 * @return array<int, array{key: string, dimension: string, dcat_prop: string, label: string, points: int, type: string, check: string}>
	 */
	public static function get_metrics(): array {
		$file = ODW_PLUGIN_DIR . 'config/mqa-metrics.php';

		if ( ! file_exists( $file ) ) {
			return array();
		}

		$data = include $file;
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Menschenlesbare Labels der Dimensionen.
	 *
	 * @return array<string, string>
	 */
	public static function get_dimension_labels(): array {
		return array(
			'findability'      => __( 'Auffindbarkeit', 'open-data-wizard' ),
			'accessibility'    => __( 'Zugänglichkeit', 'open-data-wizard' ),
			'interoperability' => __( 'Interoperabilität', 'open-data-wizard' ),
			'reusability'      => __( 'Wiederverwendbarkeit', 'open-data-wizard' ),
			'contextuality'    => __( 'Kontext', 'open-data-wizard' ),
		);
	}

	// -------------------------------------------------------------------------
	// Scoring
	// -------------------------------------------------------------------------

	/**
	 * Berechnet den MQA-Qualitätsscore für einen Datensatz.
	 *
	 * @param int $post_id Dataset post ID.
	 * @return array<string, mixed> MQA-Ergebnis inkl. abgeleitetem 0–100-Score und Legacy-Level.
	 */
	public static function calculate( int $post_id ): array {
		$post = get_post( $post_id );

		if ( ! $post || 'odw_dataset' !== $post->post_type ) {
			return self::empty_result();
		}

		$dimensions = array();
		foreach ( self::DIMENSIONS as $dim ) {
			$dimensions[ $dim ] = array(
				'achieved'   => 0,
				'assessable' => 0,
				'max'        => 0,
			);
		}

		$metrics = array();

		foreach ( self::get_metrics() as $metric ) {
			$dim = $metric['dimension'];
			if ( ! isset( $dimensions[ $dim ] ) ) {
				$dimensions[ $dim ] = array(
					'achieved'   => 0,
					'assessable' => 0,
					'max'        => 0,
				);
			}

			$points                     = (int) $metric['points'];
			$dimensions[ $dim ]['max'] += $points;

			$passed = self::evaluate_metric( $metric, $post );

			if ( null === $passed ) {
				$status = 'not_assessed';
			} else {
				$dimensions[ $dim ]['assessable'] += $points;
				if ( $passed ) {
					$dimensions[ $dim ]['achieved'] += $points;
				}
				$status = $passed ? 'passed' : 'failed';
			}

			$metrics[ $metric['key'] ] = array(
				'label'     => $metric['label'],
				'dimension' => $dim,
				'points'    => $points,
				'status'    => $status,
			);
		}

		$achieved   = (int) array_sum( array_column( $dimensions, 'achieved' ) );
		$assessable = (int) array_sum( array_column( $dimensions, 'assessable' ) );
		$rating     = self::get_rating( $achieved, $assessable );

		return array(
			'achieved'      => $achieved,
			'assessable'    => $assessable,
			'max'           => self::MQA_MAX,
			'rating'        => $rating,
			'dimensions'    => $dimensions,
			'metrics'       => $metrics,
			'calculated_at' => current_time( 'Y-m-d H:i:s' ),
			// Abwärtskompatibilität für Admin-Spalte, Sortierung und Alt-Konsumenten.
			'score'         => $assessable > 0 ? (int) round( $achieved / $assessable * 100 ) : 0,
			'level'         => self::rating_to_level( $rating ),
		);
	}

	/**
	 * Wertet eine einzelne Metrik aus.
	 *
	 * @param array<string, mixed> $metric Metrik-Definition.
	 * @param \WP_Post             $post   Dataset post object.
	 * @return bool|null true = erfüllt, false = nicht erfüllt, null = nicht bewertet (Vokabular/Netzwerk/SHACL).
	 */
	private static function evaluate_metric( array $metric, \WP_Post $post ): ?bool {
		$type = (string) ( $metric['type'] ?? '' );

		if ( 'present' === $type ) {
			return self::check_metric( (string) $metric['check'], $post );
		}

		if ( 'vocab' === $type ) {
			return self::check_vocab_metric( (string) $metric['check'], $post );
		}

		if ( 'reachable' === $type && self::url_checks_enabled() ) {
			return self::check_reachable_metric( (string) $metric['check'], $post );
		}

		// SHACL (und Erreichbarkeit bei deaktivierter Einstellung) folgt in Phase 3+.
		return null;
	}

	/**
	 * Ist die opt-in-URL-Erreichbarkeitsprüfung aktiviert?
	 *
	 * @return bool
	 */
	private static function url_checks_enabled(): bool {
		return class_exists( 'ODW_Settings' ) && (bool) ODW_Settings::get( 'mqa_check_urls' );
	}

	/**
	 * „Ist die Eigenschaft gesetzt?"-Prüfung je Metrik.
	 *
	 * @param string   $check Check-Schlüssel aus der Metrik-Definition.
	 * @param \WP_Post $post  Dataset post object.
	 * @return bool True wenn die Eigenschaft im Datensatz vorhanden ist.
	 */
	private static function check_metric( string $check, \WP_Post $post ): bool {
		$id = $post->ID;

		switch ( $check ) {
			case 'keyword':
				$raw      = (string) carbon_get_post_meta( $id, 'odw_keywords' );
				$parts    = preg_split( '/\r?\n/', $raw );
				$keywords = is_array( $parts ) ? array_filter( array_map( 'trim', $parts ) ) : array();
				return ! empty( $keywords );

			case 'theme':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_theme' ) );

			case 'spatial':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_spatial' ) );

			case 'temporal':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_temporal_start' ) )
					|| '' !== trim( (string) carbon_get_post_meta( $id, 'odw_temporal_end' ) );

			case 'download_url':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return '' !== trim( $d['download'] );
					}
				);

			case 'format':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return '' !== trim( $d['format'] );
					}
				);

			case 'media_type':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return '' !== trim( $d['media_type'] );
					}
				);

			case 'license':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return '' !== trim( $d['license'] );
					}
				);

			case 'access_rights':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_access_rights' ) );

			case 'contact_point':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_contact_name' ) )
					|| '' !== trim( (string) carbon_get_post_meta( $id, 'odw_contact_email' ) )
					|| '' !== trim( (string) carbon_get_post_meta( $id, 'odw_contact_url' ) );

			case 'publisher':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_publisher' ) );

			case 'rights':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return '' !== trim( $d['rights'] );
					}
				);

			case 'byte_size':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return (int) $d['byte_size'] > 0;
					}
				);

			case 'issued':
				return '' !== trim( (string) carbon_get_post_meta( $id, 'odw_issued' ) );

			case 'modified':
				// Kein Carbon-Fields-Feld mehr — set_modified_date() schreibt den
				// Wert direkt in die Post-Meta, dort wird er auch gelesen.
				return '' !== trim( (string) get_post_meta( $id, '_odw_modified', true ) );
		}

		return false;
	}

	/**
	 * „Stammt der Wert aus einem kontrollierten Vokabular?"-Prüfung (MQA Phase 2).
	 *
	 * @param string   $check Check-Schlüssel aus der Metrik-Definition.
	 * @param \WP_Post $post  Dataset post object.
	 * @return bool True wenn der Wert einem kontrollierten Vokabular entspricht.
	 */
	private static function check_vocab_metric( string $check, \WP_Post $post ): bool {
		if ( ! class_exists( 'ODW_Fields' ) ) {
			return false;
		}

		$id = $post->ID;

		switch ( $check ) {
			case 'format_vocab':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return '' !== trim( $d['format'] )
							&& '' !== (string) ( ODW_Fields::get_format_meta( $d['format'] )['eu_uri'] ?? '' );
					}
				);

			case 'format_nonproprietary':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return true === ( ODW_Fields::get_format_meta( $d['format'] )['non_proprietary'] ?? false );
					}
				);

			case 'format_machine_readable':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return true === ( ODW_Fields::get_format_meta( $d['format'] )['machine_readable'] ?? false );
					}
				);

			case 'license_vocab':
				return self::any_distribution(
					$id,
					static function ( array $d ): bool {
						return self::license_in_vocab( $d['license'] );
					}
				);

			case 'access_rights_vocab':
				$value = (string) carbon_get_post_meta( $id, 'odw_access_rights' );
				if ( '' === $value ) {
					return false;
				}
				foreach ( ODW_Fields::load_vocabulary( 'access-right' ) as $entry ) {
					if ( $entry['value'] === $value ) {
						return true;
					}
				}
				return false;
		}

		return false;
	}

	/**
	 * Effektive Lizenz-URI (bei „sonstige" die eigene URI).
	 *
	 * @param int $id Post ID.
	 * @return string
	 */
	private static function effective_license( int $id ): string {
		$lic = (string) carbon_get_post_meta( $id, 'odw_license' );
		if ( 'sonstige' === $lic ) {
			return (string) carbon_get_post_meta( $id, 'odw_license_custom' );
		}
		return $lic;
	}

	/**
	 * Alle Distributionen eines Datensatzes (primäre + zusätzliche) als
	 * normalisierte Wertelisten. Distribution-bezogene MQA-Metriken gelten als
	 * erfüllt, sobald **irgendeine** Distribution die Bedingung erfüllt.
	 *
	 * @param int $id Post ID.
	 * @return array<int, array<string, string>>
	 */
	private static function all_distributions( int $id ): array {
		$rows = array();

		$rows[] = array(
			'access'     => (string) carbon_get_post_meta( $id, 'odw_access_url' ),
			'format'     => (string) carbon_get_post_meta( $id, 'odw_format' ),
			'media_type' => (string) carbon_get_post_meta( $id, 'odw_media_type' ),
			'download'   => (string) carbon_get_post_meta( $id, 'odw_download_url' ),
			'byte_size'  => (string) carbon_get_post_meta( $id, 'odw_byte_size' ),
			'license'    => self::effective_license( $id ),
			'rights'     => (string) carbon_get_post_meta( $id, 'odw_dist_rights' ),
		);

		$extras = carbon_get_post_meta( $id, 'odw_extra_distributions' );
		if ( is_array( $extras ) ) {
			foreach ( $extras as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$license = (string) ( $row['license'] ?? '' );
				if ( 'sonstige' === $license ) {
					$license = (string) ( $row['license_custom'] ?? '' );
				}
				$rows[] = array(
					'access'     => (string) ( $row['access_url'] ?? '' ),
					'format'     => (string) ( $row['format'] ?? '' ),
					'media_type' => (string) ( $row['media_type'] ?? '' ),
					'download'   => (string) ( $row['download_url'] ?? '' ),
					'byte_size'  => (string) ( $row['byte_size'] ?? '' ),
					'license'    => $license,
					'rights'     => (string) ( $row['rights'] ?? '' ),
				);
			}
		}

		return $rows;
	}

	/**
	 * True, wenn mindestens eine Distribution das Prädikat erfüllt.
	 *
	 * @param int      $id   Post ID.
	 * @param callable $pred Prädikat, das eine Distribution-Wertliste erhält.
	 * @return bool
	 */
	private static function any_distribution( int $id, callable $pred ): bool {
		foreach ( self::all_distributions( $id ) as $distribution ) {
			if ( $pred( $distribution ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Prüft, ob eine Lizenz-URI in einem bekannten Lizenz-Vokabular enthalten ist.
	 *
	 * @param string $uri Lizenz-URI.
	 * @return bool
	 */
	private static function license_in_vocab( string $uri ): bool {
		if ( '' === $uri || 'sonstige' === $uri ) {
			return false;
		}

		$options = ODW_Fields::get_license_options();
		if ( isset( $options[ $uri ] ) ) {
			return true;
		}

		$extended = ODW_Fields::load_license_list();
		return isset( $extended[ $uri ] );
	}

	/**
	 * „Ist die referenzierte URL erreichbar?"-Prüfung (MQA Phase 3, opt-in).
	 *
	 * @param string   $check Check-Schlüssel (access_url | download_url).
	 * @param \WP_Post $post  Dataset post object.
	 * @return bool True wenn die URL per HTTP-HEAD einen 2xx/3xx-Status liefert.
	 */
	private static function check_reachable_metric( string $check, \WP_Post $post ): bool {
		// Alle Distributionen (primäre + zusätzliche) berücksichtigen — eine
		// erreichbare URL genügt, damit Repeater-Distributionen nicht als
		// „fehlgeschlagen" bewertet werden.
		$key = 'download_url' === $check ? 'download' : 'access';

		foreach ( self::all_distributions( $post->ID ) as $distribution ) {
			$url = trim( $distribution[ $key ] );
			if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
				continue;
			}
			if ( self::url_is_reachable( $url ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Prüft die Erreichbarkeit einer URL per HTTP HEAD (mit GET-Fallback), 24h gecacht.
	 *
	 * @param string $url Zu prüfende URL.
	 * @return bool True bei Statuscode 200–399.
	 */
	private static function url_is_reachable( string $url ): bool {
		$cache_key = 'odw_mqa_reach_' . md5( $url );
		$cached    = get_transient( $cache_key );
		if ( '1' === $cached || '0' === $cached ) {
			return '1' === $cached;
		}

		// SSRF-Schutz: wp_safe_remote_* validiert die URL via wp_http_validate_url()
		// und blockt Loopback-/private/link-lokale Ziele sowie exotische Ports.
		$args = array(
			'timeout'     => 5,
			'redirection' => 3,
			'user-agent'  => 'OpenDataWizard-MQA/1.0',
		);

		$response = wp_safe_remote_head( $url, $args );
		$code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );

		// Manche Server lehnen HEAD ab (405/501) — dann ein leichtgewichtiges GET versuchen.
		if ( 405 === $code || 501 === $code || 0 === $code ) {
			$response = wp_safe_remote_get( $url, $args );
			$code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		}

		$ok = $code >= 200 && $code < 400;
		set_transient( $cache_key, $ok ? '1' : '0', DAY_IN_SECONDS );

		return $ok;
	}

	/**
	 * Ermittelt die MQA-Bewertungsstufe aus erreichten/bewertbaren Punkten.
	 * Die MQA-Schwellen (351/221/121 von 405) werden proportional auf das
	 * bewertbare Maximum skaliert.
	 *
	 * @param int $achieved   Erreichte Punkte.
	 * @param int $assessable Bewertbares Maximum.
	 * @return string Eine der RATING_* Konstanten.
	 */
	public static function get_rating( int $achieved, int $assessable ): string {
		if ( $assessable <= 0 ) {
			return self::RATING_BAD;
		}

		$ratio = $achieved / $assessable;

		if ( $ratio >= self::RATING_EXCELLENT_RATIO ) {
			return self::RATING_EXCELLENT;
		}
		if ( $ratio >= self::RATING_GOOD_RATIO ) {
			return self::RATING_GOOD;
		}
		if ( $ratio >= self::RATING_SUFFICIENT_RATIO ) {
			return self::RATING_SUFFICIENT;
		}
		return self::RATING_BAD;
	}

	/**
	 * Bildet eine MQA-Bewertungsstufe auf eine Legacy-Level-Konstante ab
	 * (für Admin-Spalte und Badge-CSS).
	 *
	 * @param string $rating Eine der RATING_* Konstanten.
	 * @return string Eine der LEVEL_* Konstanten.
	 */
	private static function rating_to_level( string $rating ): string {
		return array(
			self::RATING_EXCELLENT  => self::LEVEL_PERFECT,
			self::RATING_GOOD       => self::LEVEL_HIGH,
			self::RATING_SUFFICIENT => self::LEVEL_SUFFICIENT,
			self::RATING_BAD        => self::LEVEL_LOW,
		)[ $rating ] ?? self::LEVEL_LOW;
	}

	// -------------------------------------------------------------------------
	// Persistierung
	// -------------------------------------------------------------------------

	/**
	 * Holt gespeicherte Qualitätsdaten aus Post-Meta.
	 *
	 * @param int $post_id Dataset post ID.
	 * @return array<string, mixed>
	 */
	public static function get( int $post_id ): array {
		$stored = get_post_meta( $post_id, '_odw_mqa', true );

		if ( ! is_array( $stored ) || empty( $stored['rating'] ) ) {
			return self::empty_result();
		}

		return $stored;
	}

	/**
	 * Speichert Qualitätsdaten in Post-Meta.
	 *
	 * @param int                  $post_id Dataset post ID.
	 * @param array<string, mixed> $result  Result array from calculate().
	 */
	public static function store( int $post_id, array $result ): void {
		update_post_meta( $post_id, '_odw_mqa', $result );

		// Abwärtskompatible Skalar-Meta für Admin-Spalte + Sortierung.
		update_post_meta( $post_id, '_odw_quality_score', $result['score'] ?? 0 );
		update_post_meta( $post_id, '_odw_quality_level', $result['level'] ?? '' );
		update_post_meta( $post_id, '_odw_quality_calculated_at', $result['calculated_at'] ?? '' );
	}

	/**
	 * Hook-Callback: Qualität nach jedem Speichern neu berechnen.
	 *
	 * @param int $post_id Dataset post ID.
	 */
	public static function recalculate_on_save( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( 'odw_dataset' !== get_post_type( $post_id ) ) {
			return;
		}

		self::store( $post_id, self::calculate( $post_id ) );
	}

	// -------------------------------------------------------------------------
	// REST API Integration
	// -------------------------------------------------------------------------

	/**
	 * Hängt die MQA-Qualitätsdaten an den JSON-LD Dataset-Array an.
	 *
	 * @param array<string, mixed> $dataset Der JSON-LD Array.
	 * @param int                  $post_id Post-ID.
	 * @return array<string, mixed>
	 */
	public static function append_to_jsonld( array $dataset, int $post_id ): array {
		$quality = self::get( $post_id );

		if ( empty( $quality['rating'] ) ) {
			return $dataset;
		}

		$dimensions = array();
		foreach ( (array) ( $quality['dimensions'] ?? array() ) as $dim => $data ) {
			$dimensions[ $dim ] = array(
				'odw:score'      => (int) ( $data['achieved'] ?? 0 ),
				'odw:assessable' => (int) ( $data['assessable'] ?? 0 ),
				'odw:maxScore'   => (int) ( $data['max'] ?? 0 ),
			);
		}

		$dataset['odw:qualityScore'] = array(
			'@type'            => 'odw:QualityScore',
			'odw:methodology'  => 'https://data.europa.eu/mqa/methodology',
			'odw:score'        => (int) ( $quality['achieved'] ?? 0 ),
			'odw:assessable'   => (int) ( $quality['assessable'] ?? 0 ),
			'odw:maxScore'     => (int) ( $quality['max'] ?? self::MQA_MAX ),
			'odw:rating'       => (string) $quality['rating'],
			'odw:dimensions'   => $dimensions,
			'odw:calculatedAt' => (string) ( $quality['calculated_at'] ?? '' ),
		);

		return $dataset;
	}

	// -------------------------------------------------------------------------
	// Admin Meta-Box
	// -------------------------------------------------------------------------

	/**
	 * Registriert die Qualitäts-Meta-Box auf dem Edit-Screen.
	 */
	public static function register_meta_box(): void {
		add_meta_box(
			'odw-quality-report',
			__( 'Qualitätsprüfung (MQA)', 'open-data-wizard' ),
			array( self::class, 'render_meta_box' ),
			'odw_dataset',
			'normal',
			'default'
		);
	}

	/**
	 * Rendert den Inhalt der Qualitäts-Meta-Box (MQA-Dimensionen + Metriken).
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_meta_box( \WP_Post $post ): void {
		$quality = self::get( $post->ID );

		if ( empty( $quality['rating'] ) ) {
			echo '<p class="description">' . esc_html__( 'Noch keine Qualitätsanalyse vorhanden. Datensatz speichern, um die Prüfung auszuführen.', 'open-data-wizard' ) . '</p>';
			return;
		}

		$achieved     = (int) ( $quality['achieved'] ?? 0 );
		$assessable   = (int) ( $quality['assessable'] ?? 0 );
		$max          = (int) ( $quality['max'] ?? self::MQA_MAX );
		$rating       = (string) $quality['rating'];
		$rating_label = self::get_rating_label( $rating );
		$level_class  = 'odw-quality--' . self::rating_to_level( $rating );
		$percent      = $assessable > 0 ? (int) round( $achieved / $assessable * 100 ) : 0;
		$not_assessed = $max - $assessable;
		$dim_labels   = self::get_dimension_labels();
		$metrics      = (array) ( $quality['metrics'] ?? array() );
		?>
		<div class="odw-quality-report">

			<p class="description" style="margin: 0 0 10px;">
				<?php esc_html_e( 'Bewertung nach der EU-Metadata-Quality-Assessment-Methodik (data.europa.eu). Die Werte werden bei jedem Speichern neu berechnet – Änderungen im Formular wirken sich erst nach dem Speichern aus.', 'open-data-wizard' ); ?>
			</p>

			<div class="odw-quality-summary">
				<div class="odw-quality-headline">
					<span class="odw-quality-percent"><?php echo esc_html( sprintf( '%d %%', $percent ) ); ?></span>
					<span class="odw-quality-level-badge <?php echo esc_attr( $level_class ); ?>">
						<?php echo esc_html( $rating_label ); ?>
					</span>
				</div>
				<div class="odw-quality-gauge">
					<div class="odw-quality-bar <?php echo esc_attr( $level_class ); ?>"
						style="width: <?php echo esc_attr( (string) $percent ); ?>%"
						role="progressbar"
						aria-valuenow="<?php echo esc_attr( (string) $percent ); ?>"
						aria-valuemin="0"
						aria-valuemax="100">
					</div>
				</div>
				<p class="odw-quality-rawscore description">
					<?php
					if ( $assessable !== $max ) {
						echo esc_html(
							sprintf(
							/* translators: 1: achieved MQA points, 2: assessable MQA points, 3: MQA maximum */
								__( 'MQA-Rohwert: %1$d / %2$d Punkte (von max. %3$d)', 'open-data-wizard' ),
								$achieved,
								$assessable,
								$max
							)
						);
					} else {
						echo esc_html(
							sprintf(
							/* translators: 1: achieved MQA points, 2: assessable MQA points */
								__( 'MQA-Rohwert: %1$d / %2$d Punkte', 'open-data-wizard' ),
								$achieved,
								$assessable
							)
						);
					}
					?>
				</p>
			</div>

			<?php
			// Erklärt den Startwert: Ein frisch gespeicherter, leerer Entwurf steht
			// nicht bei 0 %, weil das Änderungsdatum automatisch gesetzt wird.
			$auto_points = 0;
			foreach ( self::AUTO_FULFILLED as $auto_key ) {
				if ( 'passed' === ( $metrics[ $auto_key ]['status'] ?? '' ) ) {
					$auto_points += (int) ( $metrics[ $auto_key ]['points'] ?? 0 );
				}
			}
			if ( $auto_points > 0 ) :
				?>
			<p class="description" style="margin: 0 0 12px;">
				<?php
				echo esc_html(
					sprintf(
					/* translators: %d: MQA points the plugin fulfils by itself */
						__( 'Davon steuert der Wizard %d Punkte selbst bei: Das Änderungsdatum wird bei jedem Speichern automatisch gesetzt. Deshalb steht auch ein noch leerer Datensatz nicht bei 0 %%.', 'open-data-wizard' ),
						$auto_points
					)
				);
				?>
			</p>
			<?php endif; ?>

			<?php if ( $not_assessed > 0 ) : ?>
			<p class="description" style="margin: 0 0 12px;">
				<?php
				echo esc_html(
					sprintf(
					/* translators: %d: number of points not yet assessed */
						__( '%d Punkte konnten nicht automatisch bewertet werden (in der Tabelle mit „–" markiert).', 'open-data-wizard' ),
						$not_assessed
					)
				);
				?>
			</p>
			<?php endif; ?>

			<table class="odw-quality-table widefat striped">
				<tbody>
				<?php
				foreach ( self::DIMENSIONS as $dim ) {
					$dim_data = $quality['dimensions'][ $dim ] ?? array(
						'achieved'   => 0,
						'assessable' => 0,
						'max'        => 0,
					);
					$d_ach    = (int) $dim_data['achieved'];
					$d_ass    = (int) $dim_data['assessable'];
					$d_max    = (int) $dim_data['max'];

					echo '<tr class="odw-quality-section-row"><th colspan="3">'
						. esc_html( $dim_labels[ $dim ] ?? $dim )
						. ' <span class="odw-quality-dim-score">' . esc_html( sprintf( '%d / %d', $d_ach, $d_ass ) )
						. ( $d_ass !== $d_max ? esc_html( sprintf( ' (max. %d)', $d_max ) ) : '' )
						. '</span></th></tr>';

					foreach ( $metrics as $metric_key => $m ) {
						if ( ( $m['dimension'] ?? '' ) !== $dim ) {
							continue;
						}

						$status = $m['status'] ?? 'failed';
						$pts    = (int) ( $m['points'] ?? 0 );

						// Jede Metrik ist binär — es gibt keine Teilpunkte. "0 / 30"
						// suggerierte eine Skala, die es nicht gibt; offene Metriken
						// zeigen daher, was zu gewinnen ist, nicht einen Bruch.
						if ( 'passed' === $status ) {
							$icon        = '✓';
							$row_class   = 'odw-quality-pass';
							$pts_display = (string) $pts;
						} elseif ( 'not_assessed' === $status ) {
							$icon        = '–';
							$row_class   = 'odw-quality-notassessed';
							$pts_display = '–';
						} else {
							$icon        = '✗';
							$row_class   = 'odw-quality-fail';
							/* translators: %d: points obtainable for this metric */
							$pts_display = sprintf( __( '+%d möglich', 'open-data-wizard' ), $pts );
						}

						$label = (string) $m['label'];
						if ( 'passed' === $status && in_array( (string) $metric_key, self::AUTO_FULFILLED, true ) ) {
							$label .= ' ' . __( '(automatisch)', 'open-data-wizard' );
						}
						?>
						<tr class="<?php echo esc_attr( $row_class ); ?>">
							<td><?php echo esc_html( $label ); ?></td>
							<td class="odw-quality-col-pts"><?php echo esc_html( $pts_display ); ?></td>
							<td class="odw-quality-col-status"><?php echo esc_html( $icon ); ?></td>
						</tr>
						<?php
					}
				}
				?>
				</tbody>
			</table>

			<p class="odw-quality-footer description">
				<?php
				printf(
					/* translators: %s: Datetime of last quality calculation */
					esc_html__( 'Letzte Berechnung: %s · Wird bei jedem Speichern aktualisiert.', 'open-data-wizard' ),
					esc_html( (string) ( $quality['calculated_at'] ?? '' ) )
				);
				?>
			</p>
		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Anzeigehilfen
	// -------------------------------------------------------------------------

	/**
	 * Menschenlesbares Label einer MQA-Bewertungsstufe.
	 *
	 * @param string $rating Eine der RATING_* Konstanten.
	 * @return string Übersetztes Label.
	 */
	public static function get_rating_label( string $rating ): string {
		return array(
			self::RATING_EXCELLENT  => __( 'Ausgezeichnet', 'open-data-wizard' ),
			self::RATING_GOOD       => __( 'Gut', 'open-data-wizard' ),
			self::RATING_SUFFICIENT => __( 'Ausreichend', 'open-data-wizard' ),
			self::RATING_BAD        => __( 'Mangelhaft', 'open-data-wizard' ),
		)[ $rating ] ?? __( 'Unbekannt', 'open-data-wizard' );
	}

	// -------------------------------------------------------------------------
	// Abwärtskompatibilität (Legacy-API für Admin-Spalte, Validierung, Tests)
	// -------------------------------------------------------------------------

	/**
	 * Gibt die DCAT-AP-Indikatoren (Validierungsregistry) zurück.
	 * Weiterhin aus config/dcat-ap-fields.php geladen — steuert die Publish-Validierung.
	 *
	 * @return array<int, array{key: string, label: string, points: int, required: bool}>
	 */
	public static function get_indicators(): array {
		if ( class_exists( 'ODW_Fields' ) ) {
			$defs = ODW_Fields::load_field_definitions();
			if ( ! empty( $defs ) ) {
				return $defs;
			}
		}

		return array();
	}

	/**
	 * Summe der Pflichtpunkte aus der Indikator-Config (Legacy-Schwelle).
	 *
	 * @return int
	 */
	private static function get_required_only_score(): int {
		$sum = 0;
		foreach ( self::get_indicators() as $indicator ) {
			if ( ! empty( $indicator['required'] ) ) {
				$sum += (int) $indicator['points'];
			}
		}

		$threshold = $sum > 0 ? $sum : self::REQUIRED_ONLY_SCORE;

		return min( 100, $threshold );
	}

	/**
	 * Legacy: ermittelt ein 4-stufiges Level aus einem 0–100-Score.
	 * Weiterhin von der Admin-Listenspalte verwendet.
	 *
	 * @param int $score Numeric score 0–100.
	 * @return string One of LEVEL_PERFECT, LEVEL_HIGH, LEVEL_SUFFICIENT, LEVEL_LOW.
	 */
	public static function get_level( int $score ): string {
		$required_only = self::get_required_only_score();

		if ( 100 === $score ) {
			return self::LEVEL_PERFECT;
		}
		if ( $score > $required_only ) {
			return self::LEVEL_HIGH;
		}
		if ( $required_only === $score ) {
			return self::LEVEL_SUFFICIENT;
		}
		return self::LEVEL_LOW;
	}

	/**
	 * Legacy-Label für eine Level-Konstante (Admin-Spalte).
	 *
	 * @param string $level One of LEVEL_*.
	 * @return string Translated label.
	 */
	public static function get_level_label( string $level ): string {
		return array(
			self::LEVEL_PERFECT    => __( 'Perfekt', 'open-data-wizard' ),
			self::LEVEL_HIGH       => __( 'Gut', 'open-data-wizard' ),
			self::LEVEL_SUFFICIENT => __( 'Ausreichend', 'open-data-wizard' ),
			self::LEVEL_LOW        => __( 'Verbesserungsbedarf', 'open-data-wizard' ),
		)[ $level ] ?? __( 'Unbekannt', 'open-data-wizard' );
	}

	// -------------------------------------------------------------------------
	// Intern
	// -------------------------------------------------------------------------

	/**
	 * Leeres MQA-Ergebnis (noch keine Analyse vorhanden).
	 *
	 * @return array<string, mixed>
	 */
	private static function empty_result(): array {
		return array(
			'achieved'      => 0,
			'assessable'    => 0,
			'max'           => self::MQA_MAX,
			'rating'        => '',
			'dimensions'    => array(),
			'metrics'       => array(),
			'calculated_at' => '',
			'score'         => 0,
			'level'         => '',
		);
	}
}
