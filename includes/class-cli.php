<?php
/**
 * WP-CLI Commands for Open Data Wizard
 *
 * Provides command-line utilities for bulk operations:
 * - wp open-data-wizard quality recalculate
 * - wp open-data-wizard cache clear
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP-CLI commands for Open Data Wizard.
 *
 * @package OpenDataWizard
 */
class ODW_CLI {

	/**
	 * Registers WP-CLI commands if WP-CLI is available.
	 */
	public static function init(): void {
		if ( ! defined( 'WP_CLI' ) ) {
			return;
		}
		\WP_CLI::add_command( 'open-data-wizard quality recalculate', array( self::class, 'quality_recalculate' ) );
		\WP_CLI::add_command( 'open-data-wizard cache clear', array( self::class, 'cache_clear' ) );
		\WP_CLI::add_command( 'open-data-wizard docs', array( self::class, 'generate_docs' ) );
	}

	/**
	 * Regenerates the field reference (docs/FELD-REFERENZ.md) from the field catalog.
	 *
	 * ## EXAMPLES
	 *
	 *     wp open-data-wizard docs
	 *
	 * @param array<string, mixed> $args Positional arguments.
	 * @param array<string, mixed> $assoc_args Named arguments.
	 */
	public static function generate_docs( array $args, array $assoc_args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( ! class_exists( 'ODW_Field_Reference' ) ) {
			require_once ODW_PLUGIN_DIR . 'includes/class-field-reference.php';
		}

		$target = ODW_PLUGIN_DIR . 'docs/FELD-REFERENZ.md';
		$bytes  = ODW_Field_Reference::write( $target );

		if ( $bytes > 0 ) {
			$count = count( ODW_Field_Reference::load_catalog() );
			\WP_CLI::success( sprintf( 'Feld-Referenz erzeugt: %d Felder, %d Bytes → docs/FELD-REFERENZ.md', $count, $bytes ) );
			return;
		}

		\WP_CLI::error( 'Feld-Referenz konnte nicht geschrieben werden.' );
	}

	/**
	 * Recalculates quality scores for all published datasets.
	 *
	 * ## OPTIONS
	 *
	 * [--all]
	 * : Include draft and trashed datasets (default: only published)
	 *
	 * ## EXAMPLES
	 *
	 *     wp open-data-wizard quality recalculate
	 *     wp open-data-wizard quality recalculate --all
	 *
	 * @param array<string, mixed> $args Positional arguments.
	 * @param array<string, mixed> $assoc_args Named arguments (--all, etc).
	 */
	public static function quality_recalculate( array $args, array $assoc_args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		// Determine which datasets to process.
		$post_status = isset( $assoc_args['all'] ) ? array( 'publish', 'draft', 'trash' ) : 'publish';

		$query = new \WP_Query(
			array(
				'post_type'      => 'odw_dataset',
				'post_status'    => $post_status,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$post_ids = $query->posts;

		if ( empty( $post_ids ) ) {
			\WP_CLI::success( 'No datasets found.' );
			return;
		}

		$count    = 0;
		$progress = \WP_CLI\Utils\make_progress_bar( 'Recalculating quality scores', count( $post_ids ) );

		foreach ( $post_ids as $post_id ) {
			if ( class_exists( 'ODW_Quality' ) ) {
				// calculate() liefert nur das Ergebnis — erst store() persistiert
				// Score/Level/MQA-Daten in den Post-Metas.
				ODW_Quality::store( $post_id, ODW_Quality::calculate( $post_id ) );
			}
			$progress->tick();
			++$count;
		}

		$progress->finish();

		\WP_CLI::success(
			sprintf(
				/* translators: %d = number of datasets processed */
				__( 'Recalculated quality scores for %d dataset(s).', 'open-data-wizard' ),
				$count
			)
		);
	}

	/**
	 * Clears all REST API transient caches.
	 *
	 * Removes cached responses for:
	 * - Catalog endpoints (all pages and filters)
	 * - Individual dataset endpoints
	 * - Delta endpoint (all since values)
	 *
	 * ## EXAMPLES
	 *
	 *     wp open-data-wizard cache clear
	 *
	 * @param array<string, mixed> $args Positional arguments.
	 * @param array<string, mixed> $assoc_args Named arguments.
	 */
	public static function cache_clear( array $args, array $assoc_args ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $wpdb;

		// Delete all odw_catalog_* transients (only main entries, not timeouts).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$catalog_transients = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_odw_catalog_%' AND option_name NOT LIKE '_transient_timeout%'"
		);

		// Delete all odw_delta_* transients (only main entries, not timeouts).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$delta_transients = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_odw_delta_%' AND option_name NOT LIKE '_transient_timeout%'"
		);

		// Delete all odw_dataset_* transients (only main entries, not timeouts).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$dataset_transients = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_odw_dataset_%' AND option_name NOT LIKE '_transient_timeout%'"
		);

		$all_transients = array_merge( $catalog_transients, $delta_transients, $dataset_transients );
		$count          = count( $all_transients );

		foreach ( $all_transients as $transient ) {
			// Remove '_transient_' prefix to get the cache key.
			$key = str_replace( '_transient_', '', $transient );
			delete_transient( $key );
		}

		\WP_CLI::success(
			sprintf(
				/* translators: %d = number of transients cleared */
				__( 'Cleared %d transient cache(s).', 'open-data-wizard' ),
				$count
			)
		);
	}
}
