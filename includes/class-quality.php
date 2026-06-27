<?php
/**
 * Qualitätsindikatoren / Ampellogik für odw_dataset
 *
 * Berechnet einen Qualitätsscore (0–100) aus DCAT-AP 3.0 Feldvollständigkeit,
 * speichert ihn in Post-Meta und stellt ihn im Admin und REST API bereit.
 *
 * Level-Logik (siehe get_level()):
 *   perfect    — 100 Punkte (alle Felder ausgefüllt)
 *   high       — über der Pflichtfeld-Schwelle, aber < 100
 *   sufficient — genau die Pflichtfeld-Schwelle (alle Pflichtfelder, keine optionalen)
 *   low        — unter der Pflichtfeld-Schwelle (Pflichtfelder fehlen)
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quality indicator and traffic-light logic for odw_dataset posts.
 *
 * @package OpenDataWizard
 */
class ODW_Quality {

	public const LEVEL_PERFECT    = 'perfect';
	public const LEVEL_HIGH       = 'high';
	public const LEVEL_SUFFICIENT = 'sufficient';
	public const LEVEL_LOW        = 'low';

	/** Fallback score at which all required fields are fulfilled when indicators are unavailable. */
	private const REQUIRED_ONLY_SCORE = 55;

	/**
	 * Registers WordPress hooks.
	 */
	public static function init(): void {
		// Qualität nach jedem echten Speichern neu berechnen (nach CF-Save und set_modified_date).
		add_action( 'save_post_odw_dataset', array( self::class, 'recalculate_on_save' ), 30 );

		// Meta-Box auf dem Edit-Screen registrieren.
		add_action( 'add_meta_boxes', array( self::class, 'register_meta_box' ) );

		// Qualitätsdaten in JSON-LD einbetten (REST API + Vorschau).
		add_filter( 'odw_dataset_jsonld', array( self::class, 'append_to_jsonld' ), 10, 2 );
	}

	// -------------------------------------------------------------------------
	// Indikator-Definitionen — Single Source of Truth
	// -------------------------------------------------------------------------

	/**
	 * Gibt alle Qualitätsindikatoren mit Punktewertung zurück.
	 * Geladen aus config/dcat-ap-fields.php via ODW_Fields::load_field_definitions().
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

		// Fallback when ODW_Fields is not available (e.g. unit tests).
		return array(
			array(
				'key'      => 'title',
				'label'    => __( 'Titel (dct:title)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => true,
			),
			array(
				'key'      => 'description',
				'label'    => __( 'Beschreibung (dct:description)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => true,
			),
			array(
				'key'      => 'publisher',
				'label'    => __( 'Herausgeber (dct:publisher)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => true,
			),
			array(
				'key'      => 'license',
				'label'    => __( 'Lizenz (dct:license)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => true,
			),
			array(
				'key'      => 'distribution',
				'label'    => __( 'Distribution mit URL (dcat:accessURL)', 'open-data-wizard' ),
				'points'   => 15,
				'required' => true,
			),
			array(
				'key'      => 'language',
				'label'    => __( 'Sprache (dct:language)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => false,
			),
			array(
				'key'      => 'keywords',
				'label'    => __( 'Schlagworte (dcat:keyword)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => false,
			),
			array(
				'key'      => 'theme',
				'label'    => __( 'Thema (dcat:theme)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => false,
			),
			array(
				'key'      => 'issued',
				'label'    => __( 'Veröffentlichungsdatum (dct:issued)', 'open-data-wizard' ),
				'points'   => 10,
				'required' => false,
			),
			array(
				'key'      => 'dist_format',
				'label'    => __( 'Format der Distribution (dct:format)', 'open-data-wizard' ),
				'points'   => 5,
				'required' => false,
			),
		);
	}

	// -------------------------------------------------------------------------
	// Scoring
	// -------------------------------------------------------------------------

	/**
	 * Berechnet den Qualitätsscore für einen Datensatz.
	 *
	 * @param int $post_id Dataset post ID.
	 * @return array{score: int, level: string, indicators: array<string, array{label: string, points: int, earned: int, passed: bool, required: bool}>, calculated_at: string}
	 */
	public static function calculate( int $post_id ): array {
		$post = get_post( $post_id );

		if ( ! $post || 'odw_dataset' !== $post->post_type ) {
			return self::empty_result();
		}

		$total     = 0;
		$breakdown = array();

		foreach ( self::get_indicators() as $indicator ) {
			$passed = self::check_indicator( $indicator['key'], $post );
			$earned = $passed ? $indicator['points'] : 0;
			$total += $earned;

			$breakdown[ $indicator['key'] ] = array(
				'label'    => $indicator['label'],
				'points'   => $indicator['points'],
				'earned'   => $earned,
				'passed'   => $passed,
				'required' => $indicator['required'],
			);
		}

		// Clamp into the documented 0–100 range so a changed config can never
		// produce an out-of-range score.
		$total = max( 0, min( 100, $total ) );

		return array(
			'score'         => $total,
			'level'         => self::get_level( $total ),
			'indicators'    => $breakdown,
			'calculated_at' => current_time( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Sum of points for all required indicators — the score reached when exactly
	 * the required fields are filled. Computed from the active config so the level
	 * thresholds stay correct if the point values change.
	 *
	 * @return int Required-only score threshold.
	 */
	private static function get_required_only_score(): int {
		$sum = 0;
		foreach ( self::get_indicators() as $indicator ) {
			if ( ! empty( $indicator['required'] ) ) {
				$sum += (int) $indicator['points'];
			}
		}

		$threshold = $sum > 0 ? $sum : self::REQUIRED_ONLY_SCORE;

		// Keep the threshold within the clamped 0–100 score range so get_level()
		// stays consistent even if a filter pushes the required points above 100.
		return min( 100, $threshold );
	}

	/**
	 * Prüft einen einzelnen Indikator am WP_Post-Objekt.
	 *
	 * @param string   $key  Indicator key (e.g. 'title', 'license').
	 * @param \WP_Post $post Dataset post object.
	 * @return bool True when the indicator passes.
	 */
	private static function check_indicator( string $key, \WP_Post $post ): bool {
		switch ( $key ) {
			case 'title':
				return '' !== trim( $post->post_title );

			case 'description':
				return '' !== trim( (string) carbon_get_post_meta( $post->ID, 'odw_description' ) );

			case 'publisher':
				return '' !== trim( (string) carbon_get_post_meta( $post->ID, 'odw_publisher' ) );

			case 'license':
				$lic = (string) carbon_get_post_meta( $post->ID, 'odw_license' );
				if ( '' !== $lic && 'sonstige' !== $lic ) {
					return true;
				}
				if ( 'sonstige' === $lic ) {
					return '' !== (string) carbon_get_post_meta( $post->ID, 'odw_license_custom' );
				}
				return false;

			case 'distribution':
				return '' !== (string) carbon_get_post_meta( $post->ID, 'odw_access_url' );

			case 'language':
				return '' !== trim( (string) carbon_get_post_meta( $post->ID, 'odw_language' ) );

			case 'keywords':
				$raw      = (string) carbon_get_post_meta( $post->ID, 'odw_keywords' );
				$keywords = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $raw ) ) );
				return ! empty( $keywords );

			case 'theme':
				return '' !== trim( (string) carbon_get_post_meta( $post->ID, 'odw_theme' ) );

			case 'issued':
				return '' !== trim( (string) carbon_get_post_meta( $post->ID, 'odw_issued' ) );

			case 'dist_format':
				return '' !== (string) carbon_get_post_meta( $post->ID, 'odw_format' );
		}

		return false;
	}

	/**
	 * Ermittelt das Qualitätslevel aus dem Score.
	 *
	 * 100           → Perfekt     (alle Felder ausgefüllt)
	 * 56–99         → Gut         (alle Pflichtfelder + einige optionale)
	 * REQUIRED_ONLY → Ausreichend (genau alle Pflichtfelder, keine optionalen)
	 * < REQUIRED    → Verbesserungsbedarf (Pflichtfelder fehlen)
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

	// -------------------------------------------------------------------------
	// Persistierung
	// -------------------------------------------------------------------------

	/**
	 * Holt gespeicherte Qualitätsdaten aus Post-Meta.
	 *
	 * @param int $post_id Dataset post ID.
	 * @return array{score: int, level: string, indicators: array, calculated_at: string}
	 */
	public static function get( int $post_id ): array {
		$level = (string) get_post_meta( $post_id, '_odw_quality_level', true );

		if ( '' === $level ) {
			return self::empty_result();
		}

		$indicators = get_post_meta( $post_id, '_odw_quality_indicators', true );

		return array(
			'score'         => (int) get_post_meta( $post_id, '_odw_quality_score', true ),
			'level'         => $level,
			'indicators'    => is_array( $indicators ) ? $indicators : array(),
			'calculated_at' => (string) get_post_meta( $post_id, '_odw_quality_calculated_at', true ),
		);
	}

	/**
	 * Speichert Qualitätsdaten in Post-Meta.
	 *
	 * @param int                  $post_id Dataset post ID.
	 * @param array<string, mixed> $result  Result array from calculate().
	 */
	public static function store( int $post_id, array $result ): void {
		update_post_meta( $post_id, '_odw_quality_score', $result['score'] );
		update_post_meta( $post_id, '_odw_quality_level', $result['level'] );
		update_post_meta( $post_id, '_odw_quality_indicators', $result['indicators'] );
		update_post_meta( $post_id, '_odw_quality_calculated_at', $result['calculated_at'] );
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

		$result = self::calculate( $post_id );
		self::store( $post_id, $result );
	}

	// -------------------------------------------------------------------------
	// REST API Integration
	// -------------------------------------------------------------------------

	/**
	 * Hängt Qualitätsdaten an den JSON-LD Dataset-Array an.
	 *
	 * @param array<string, mixed> $dataset  Der JSON-LD Array.
	 * @param int                  $post_id  Post-ID.
	 * @return array<string, mixed>
	 */
	public static function append_to_jsonld( array $dataset, int $post_id ): array {
		$quality = self::get( $post_id );

		if ( '' === $quality['level'] ) {
			return $dataset;
		}

		$dataset['odw:qualityScore'] = array(
			'@type'            => 'odw:QualityScore',
			'odw:score'        => $quality['score'],
			'odw:maxScore'     => 100,
			'odw:level'        => $quality['level'],
			'odw:calculatedAt' => $quality['calculated_at'],
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
			__( 'Qualitätsprüfung', 'open-data-wizard' ),
			array( self::class, 'render_meta_box' ),
			'odw_dataset',
			'normal',
			'default'
		);
	}

	/**
	 * Rendert den Inhalt der Qualitäts-Meta-Box.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_meta_box( \WP_Post $post ): void {
		$quality    = self::get( $post->ID );
		$indicators = self::get_indicators();

		if ( '' === $quality['level'] ) {
			echo '<p class="description">' . esc_html__( 'Noch keine Qualitätsanalyse vorhanden. Datensatz speichern, um die Prüfung auszuführen.', 'open-data-wizard' ) . '</p>';
			return;
		}

		$score       = $quality['score'];
		$level       = $quality['level'];
		$level_label = self::get_level_label( $level );
		// Map 'perfect' to 'high' CSS class so existing styles apply.
		$level_class = 'odw-quality--' . ( 'perfect' === $level ? 'high' : $level );
		$stored      = $quality['indicators'];
		?>
		<div class="odw-quality-report">

			<p class="description" style="margin: 0 0 10px;">
				<?php esc_html_e( 'Die Qualitätsprüfung bewertet die Vollständigkeit der Metadaten. Die Werte werden bei jedem Speichern neu berechnet – Änderungen im Formular wirken sich erst nach dem Speichern auf das Ergebnis aus.', 'open-data-wizard' ); ?>
			</p>

			<div class="odw-quality-summary">
				<div class="odw-quality-gauge-wrap">
					<div class="odw-quality-gauge">
						<div class="odw-quality-bar odw-quality-bar--<?php echo esc_attr( $level ); ?>"
							style="width: <?php echo esc_attr( (string) $score ); ?>%"
							role="progressbar"
							aria-valuenow="<?php echo esc_attr( (string) $score ); ?>"
							aria-valuemin="0"
							aria-valuemax="100">
						</div>
					</div>
					<span class="odw-quality-score-number"><?php echo esc_html( (string) $score ); ?> / 100</span>
				</div>
				<span class="odw-quality-level-badge <?php echo esc_attr( $level_class ); ?>">
					<?php echo esc_html( $level_label ); ?>
				</span>
			</div>

			<table class="odw-quality-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Indikator', 'open-data-wizard' ); ?></th>
						<th class="odw-quality-col-pts"><?php esc_html_e( 'Punkte', 'open-data-wizard' ); ?></th>
						<th class="odw-quality-col-status"><?php esc_html_e( 'Status', 'open-data-wizard' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$prev_required = null;
				foreach ( $indicators as $indicator ) {
					$key      = $indicator['key'];
					$required = $indicator['required'];
					$row      = $stored[ $key ] ?? null;
					$passed   = $row['passed'] ?? false;
					$earned   = $row['earned'] ?? 0;
					$pts      = $indicator['points'];

					// Abschnittsüberschrift bei Wechsel Pflicht → Empfohlen → Optional.
					if ( $prev_required !== $required ) {
						if ( true === $required ) {
							echo '<tr class="odw-quality-section-row"><th colspan="3">' . esc_html__( 'Pflichtfelder', 'open-data-wizard' ) . '</th></tr>';
						} elseif ( false === $required && null !== $prev_required ) {
							$section = ( $pts >= 10 )
								? __( 'Empfohlene Felder', 'open-data-wizard' )
								: __( 'Optionale Angaben', 'open-data-wizard' );
							echo '<tr class="odw-quality-section-row"><th colspan="3">' . esc_html( $section ) . '</th></tr>';
						}
						$prev_required = $required;
					} elseif ( false === $required && $pts < 10 && $prev_pts >= 10 ) {
						// Wechsel von Empfohlen zu Optional innerhalb derselben required=false Gruppe.
						echo '<tr class="odw-quality-section-row"><th colspan="3">' . esc_html__( 'Optionale Angaben', 'open-data-wizard' ) . '</th></tr>';
					}

					$prev_pts = $pts;

					$status_icon  = $passed ? '✓' : '✗';
					$status_class = $passed ? 'odw-quality-pass' : 'odw-quality-fail';
					$pts_display  = $passed ? $pts : "0 / {$pts}";
					?>
					<tr class="<?php echo esc_attr( $status_class ); ?>">
						<td><?php echo esc_html( $indicator['label'] ); ?></td>
						<td class="odw-quality-col-pts"><?php echo esc_html( (string) $pts_display ); ?></td>
						<td class="odw-quality-col-status"><?php echo esc_html( $status_icon ); ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>

			<p class="odw-quality-footer description">
				<?php
				printf(
					/* translators: %s: Datetime of last quality calculation */
					esc_html__( 'Letzte Berechnung: %s · Wird bei jedem Speichern aktualisiert.', 'open-data-wizard' ),
					esc_html( $quality['calculated_at'] )
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
	 * Returns a human-readable label for a quality level constant.
	 *
	 * @param string $level One of LEVEL_HIGH, LEVEL_MEDIUM, LEVEL_LOW.
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
	 * Returns a zeroed-out quality result used when no data has been stored yet.
	 *
	 * @return array{score: int, level: string, indicators: array, calculated_at: string}
	 */
	private static function empty_result(): array {
		return array(
			'score'         => 0,
			'level'         => '',
			'indicators'    => array(),
			'calculated_at' => '',
		);
	}
}
