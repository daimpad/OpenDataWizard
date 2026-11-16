<?php
/**
 * Beispieldaten — laden, kennzeichnen, entfernen
 *
 * Die Beispieldatensätze aus samples/import-example.json lassen sich mit einem
 * Klick anlegen und wieder entfernen. Jeder trägt den Meta-Schlüssel
 * _odw_sample. Daran erkennen ihn:
 *
 * - die REST-Endpunkte /catalog, /datasets/<id> und /delta, die ihn nie
 *   ausliefern — erfundene Musterstadt-Daten dürfen nicht versehentlich bei
 *   einem Portal wie GovData landen;
 * - der Datenkatalog auf der Website, der ihn nur angemeldeten
 *   Redaktionsmitgliedern zeigt (oder allen mit show_demo="1");
 * - die Karte, die ihn als Beispiel kennzeichnet und keinen Metadaten-Link
 *   anbietet, der ins Leere führte.
 *
 * Ein Beispiel bleibt ein Beispiel, auch wenn es bearbeitet wird: Wer sich nur
 * das Formular ansieht und dabei auf „Aktualisieren" klickt, soll nichts
 * veröffentlichen. Zum eigenen Datensatz wird es erst über „Als eigenen
 * Datensatz übernehmen".
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sample data for trying out the plugin, kept out of every harvest endpoint.
 *
 * @package OpenDataWizard
 */
class ODW_Samples {

	/** Kennzeichen eines Beispieldatensatzes ('1', beim alten Demo-Datensatz 'demo'). */
	public const META_KEY = '_odw_sample';

	/** Wert für den Demo-Datensatz, den Versionen vor 2.47.0 beim Aktivieren anlegten. */
	public const DEMO = 'demo';

	/** Quelle der Beispieldaten, relativ zum Plugin-Verzeichnis. */
	private const FILE = 'samples/import-example.json';

	/** Admin-Aktionen (admin-post.php). */
	private const ACTION_LOAD   = 'odw_samples_load';
	private const ACTION_REMOVE = 'odw_samples_remove';
	private const ACTION_ADOPT  = 'odw_sample_adopt';

	/** Query-Argument für die Rückmeldung nach einer Aktion. */
	private const RESULT_ARG = 'odw_samples';

	/** Alle Status, in denen ein Beispiel liegen kann — auch der Papierkorb. */
	private const STATUSES = array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' );

	/**
	 * IDs je Request, damit Hinweis und Kasten nicht zweimal abfragen.
	 *
	 * @var array<int, int>|null
	 */
	private static ?array $ids = null;

	/**
	 * IDs der aus der Beispieldatei geladenen Datensätze (ohne den alten
	 * Demo-Datensatz), je Request.
	 *
	 * @var array<int, int>|null
	 */
	private static ?array $loaded = null;

	/**
	 * Registriert Admin-Aktionen, Hinweise und die Kennzeichnung in der Liste.
	 */
	public static function init(): void {
		add_action( 'admin_post_' . self::ACTION_LOAD, array( self::class, 'handle_load' ) );
		add_action( 'admin_post_' . self::ACTION_REMOVE, array( self::class, 'handle_remove' ) );
		add_action( 'admin_post_' . self::ACTION_ADOPT, array( self::class, 'handle_adopt' ) );
		add_action( 'admin_notices', array( self::class, 'render_notices' ) );
		add_filter( 'display_post_states', array( self::class, 'post_states' ), 10, 2 );
	}

	// -------------------------------------------------------------------------
	// Abfragen
	// -------------------------------------------------------------------------

	/**
	 * Ist der Datensatz ein Beispiel?
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_sample( int $post_id ): bool {
		return $post_id > 0 && '' !== (string) get_post_meta( $post_id, self::META_KEY, true );
	}

	/**
	 * Klausel für meta_query, die Beispiele ausschließt.
	 *
	 * @return array{key: string, compare: string}
	 */
	public static function exclude_clause(): array {
		return array(
			'key'     => self::META_KEY,
			'compare' => 'NOT EXISTS',
		);
	}

	/**
	 * IDs aller Beispieldatensätze, in jedem Status — auch der alte
	 * Demo-Datensatz.
	 *
	 * @return array<int, int>
	 */
	public static function ids(): array {
		if ( null === self::$ids ) {
			self::$ids = self::query_ids( array() );
		}
		return self::$ids;
	}

	/**
	 * IDs der aus der Beispieldatei geladenen Datensätze. Der alte
	 * Demo-Datensatz zählt nicht: Er soll das Laden nicht verhindern.
	 *
	 * @return array<int, int>
	 */
	public static function loaded_ids(): array {
		if ( null === self::$loaded ) {
			self::$loaded = self::query_ids( array( 'meta_value' => '1' ) );
		}
		return self::$loaded;
	}

	/**
	 * Datensätze mit Kennzeichnung, in jedem Status.
	 *
	 * @param array<string, string> $extra Weitere Abfrage-Argumente.
	 * @return array<int, int>
	 */
	private static function query_ids( array $extra ): array {
		$query = new WP_Query(
			array_merge(
				array(
					'post_type'              => 'odw_dataset',
					'post_status'            => self::STATUSES,
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'meta_key'               => self::META_KEY,
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				),
				$extra
			)
		);

		return array_map( 'intval', (array) $query->posts );
	}

	/**
	 * Vergisst die gemerkten IDs (nach Laden, Entfernen, Übernehmen).
	 */
	private static function forget(): void {
		self::$ids    = null;
		self::$loaded = null;
	}

	/**
	 * Anzahl der Datensätze in der Beispieldatei.
	 *
	 * @return int
	 */
	public static function available(): int {
		$file = ODW_PLUGIN_DIR . self::FILE;
		if ( ! is_readable( $file ) ) {
			return 0;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local bundled file.
		$data = json_decode( (string) file_get_contents( $file ), true );
		return is_array( $data ) ? count( $data ) : 0;
	}

	// -------------------------------------------------------------------------
	// Laden, entfernen, übernehmen
	// -------------------------------------------------------------------------

	/**
	 * Legt die Beispieldatensätze an und veröffentlicht sie — nur, wenn noch
	 * keine da sind, damit ein Doppelklick nicht alles zweimal anlegt.
	 *
	 * Erst die Kennzeichnung, dann die Veröffentlichung: So ist ein Beispiel
	 * in keinem Moment ein gewöhnlicher veröffentlichter Datensatz.
	 *
	 * @return array{created: int, published: int, error: string} error: '' | exists | file
	 */
	public static function load(): array {
		if ( array() !== self::loaded_ids() ) {
			return array(
				'created'   => 0,
				'published' => 0,
				'error'     => 'exists',
			);
		}

		$parsed = ODW_Batch_Import::parse_file( ODW_PLUGIN_DIR . self::FILE );
		if ( empty( $parsed['success'] ) || empty( $parsed['data'] ) ) {
			return array(
				'created'   => 0,
				'published' => 0,
				'error'     => 'file',
			);
		}

		$result    = ODW_Batch_Import::import_records( $parsed['data'] );
		$published = 0;

		foreach ( $result['ids'] as $post_id ) {
			update_post_meta( $post_id, self::META_KEY, '1' );

			$updated = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				),
				true
			);
			if ( ! is_wp_error( $updated ) && 'publish' === get_post_status( $post_id ) ) {
				++$published;
			}
		}

		self::forget();

		return array(
			'created'   => count( $result['ids'] ),
			'published' => $published,
			'error'     => '',
		);
	}

	/**
	 * Löscht alle Beispieldatensätze endgültig. Beim alten Demo-Datensatz geht
	 * die mitgelieferte CSV aus der Mediathek mit — das Plugin hatte sie selbst
	 * angelegt.
	 *
	 * Kein Papierkorb: Ein Beispiel im Papierkorb wäre im Delta-Endpunkt eine
	 * Löschmeldung für einen Datensatz, den kein Harvester je gesehen hat.
	 *
	 * @return int Anzahl der gelöschten Datensätze.
	 */
	public static function remove(): int {
		$removed = 0;

		foreach ( self::ids() as $post_id ) {
			if ( self::DEMO === get_post_meta( $post_id, self::META_KEY, true ) ) {
				$file_id = (int) get_post_meta( $post_id, '_odw_file_id', true );
				if ( $file_id > 0 && 'attachment' === get_post_type( $file_id ) ) {
					wp_delete_attachment( $file_id, true );
				}
			}

			if ( wp_delete_post( $post_id, true ) ) {
				++$removed;
			}
		}

		self::forget();

		return $removed;
	}

	/**
	 * Macht aus einem Beispiel einen eigenen Datensatz.
	 *
	 * Das Speichern über wp_update_post() setzt das Änderungsdatum, damit der
	 * Datensatz im Delta-Endpunkt als neu auftaucht, und löst die üblichen
	 * Speicher-Hooks aus (Caches, Katalog-Index).
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function adopt( int $post_id ): bool {
		if ( ! self::is_sample( $post_id ) ) {
			return false;
		}

		delete_post_meta( $post_id, self::META_KEY );
		wp_update_post( array( 'ID' => $post_id ) );
		self::forget();

		return true;
	}

	// -------------------------------------------------------------------------
	// Admin-Aktionen
	// -------------------------------------------------------------------------

	/**
	 * Aktion „Beispieldaten laden" (admin-post.php).
	 */
	public static function handle_load(): void {
		self::guard( self::ACTION_LOAD );

		$result = self::load();
		$code   = '' !== $result['error'] ? $result['error'] : 'loaded';

		self::redirect( admin_url( 'edit.php?post_type=odw_dataset' ), $code, $result['published'] );
	}

	/**
	 * Aktion „Beispieldaten entfernen" (admin-post.php).
	 */
	public static function handle_remove(): void {
		self::guard( self::ACTION_REMOVE );

		$removed = self::remove();
		$back    = wp_get_referer();

		self::redirect( $back ? $back : admin_url( 'edit.php?post_type=odw_dataset' ), 'removed', $removed );
	}

	/**
	 * Aktion „Als eigenen Datensatz übernehmen" (admin-post.php, ?post=<ID>).
	 */
	public static function handle_adopt(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified right below.
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Zugriff verweigert.', 'open-data-wizard' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::ACTION_ADOPT . '_' . $post_id );

		$code = self::adopt( $post_id ) ? 'adopted' : 'not_sample';

		self::redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), $code, 1 );
	}

	/**
	 * Berechtigung und Nonce für Laden und Entfernen.
	 *
	 * @param string $action Aktion.
	 */
	private static function guard( string $action ): void {
		if ( ! current_user_can( 'manage_open_data' ) ) {
			wp_die( esc_html__( 'Zugriff verweigert.', 'open-data-wizard' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( $action );
	}

	/**
	 * Zurück mit Rückmeldung.
	 *
	 * @param string $url   Ziel.
	 * @param string $code  Ergebnis.
	 * @param int    $count Anzahl.
	 */
	private static function redirect( string $url, string $code, int $count ): void {
		$url = remove_query_arg( array( self::RESULT_ARG, 'odw_count' ), $url );
		wp_safe_redirect(
			add_query_arg(
				array(
					self::RESULT_ARG => $code,
					'odw_count'      => $count,
				),
				$url
			)
		);
		exit;
	}

	// -------------------------------------------------------------------------
	// Oberfläche
	// -------------------------------------------------------------------------

	/**
	 * „Beispieldaten" hinter dem Titel in der Datensatzliste.
	 *
	 * @param array<string, string> $states Post states.
	 * @param WP_Post               $post   Post.
	 * @return array<string, string>
	 */
	public static function post_states( array $states, WP_Post $post ): array {
		if ( 'odw_dataset' === $post->post_type && self::is_sample( (int) $post->ID ) ) {
			$states['odw_sample'] = __( 'Beispieldaten', 'open-data-wizard' );
		}
		return $states;
	}

	/**
	 * Gilt der aktuelle Admin-Bildschirm dem Plugin?
	 *
	 * @return bool
	 */
	private static function on_plugin_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen instanceof WP_Screen
			&& ( 'odw_dataset' === $screen->post_type || false !== strpos( (string) $screen->id, 'odw-' ) );
	}

	/**
	 * Rückmeldung nach einer Aktion, Hinweis auf geladene Beispieldaten und —
	 * im Formular eines Beispiels — der Weg zum eigenen Datensatz.
	 */
	public static function render_notices(): void {
		if ( ! current_user_can( 'manage_open_data' ) || ! self::on_plugin_screen() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display of a result code.
		$code  = isset( $_GET[ self::RESULT_ARG ] ) ? sanitize_key( wp_unslash( $_GET[ self::RESULT_ARG ] ) ) : '';
		$count = isset( $_GET['odw_count'] ) ? absint( wp_unslash( $_GET['odw_count'] ) ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( '' !== $code ) {
			self::render_result( $code, $count );
		}

		$screen = get_current_screen();
		$post   = get_post();
		if ( $screen instanceof WP_Screen && 'post' === $screen->base && $post instanceof WP_Post && self::is_sample( (int) $post->ID ) ) {
			self::render_edit_notice( (int) $post->ID );
			return;
		}

		// Einstiegsseite und Batch-Import zeigen denselben Stand im Kasten.
		$ids = self::ids();
		if ( array() === $ids || 'loaded' === $code || self::screen_has_box() ) {
			return;
		}

		echo '<div class="notice notice-info odw-samples-notice"><p>';
		printf(
			/* translators: %d: number of sample datasets. */
			esc_html__( 'Beispieldatensätze vorhanden: %d. Sie erscheinen in keinem Harvest-Endpunkt, und im Datenkatalog auf der Website sieht sie nur, wer angemeldet ist und Datensätze bearbeiten darf.', 'open-data-wizard' ),
			(int) count( $ids )
		);
		echo '</p>';
		self::render_remove_form();
		echo '</div>';
	}

	/**
	 * Zeigt der Bildschirm den Kasten „Beispieldaten" (render_box())?
	 *
	 * @return bool
	 */
	private static function screen_has_box(): bool {
		$screen = get_current_screen();
		$id     = $screen instanceof WP_Screen ? (string) $screen->id : '';
		return false !== strpos( $id, 'odw-einstieg' ) || false !== strpos( $id, 'odw-batch-import' );
	}

	/**
	 * Rückmeldung nach Laden, Entfernen oder Übernehmen.
	 *
	 * @param string $code  Ergebnis.
	 * @param int    $count Anzahl.
	 */
	private static function render_result( string $code, int $count ): void {
		switch ( $code ) {
			case 'loaded':
				/* translators: %d: number of sample datasets. */
				$message = sprintf( __( '%d Beispieldatensätze wurden angelegt und veröffentlicht. Sie erscheinen in keinem Harvest-Endpunkt, und im Datenkatalog auf der Website sieht sie nur, wer angemeldet ist und Datensätze bearbeiten darf.', 'open-data-wizard' ), $count );
				$type    = 'success';
				break;
			case 'removed':
				/* translators: %d: number of removed sample datasets. */
				$message = sprintf( __( 'Beispieldatensätze endgültig gelöscht: %d.', 'open-data-wizard' ), $count );
				$type    = 'success';
				break;
			case 'adopted':
				$message = __( 'Der Datensatz ist jetzt ein eigener Datensatz. Veröffentlicht erscheint er in den Harvest-Endpunkten und im Datenkatalog.', 'open-data-wizard' );
				$type    = 'success';
				break;
			case 'exists':
				$message = __( 'Die Beispieldaten sind bereits geladen.', 'open-data-wizard' );
				$type    = 'info';
				break;
			case 'file':
				$message = __( 'Die Beispieldatei konnte nicht gelesen werden.', 'open-data-wizard' );
				$type    = 'error';
				break;
			default:
				return;
		}

		printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $type ), esc_html( $message ) );
	}

	/**
	 * Hinweis im Formular eines Beispiels.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function render_edit_notice( int $post_id ): void {
		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => self::ACTION_ADOPT,
					'post'   => $post_id,
				),
				admin_url( 'admin-post.php' )
			),
			self::ACTION_ADOPT . '_' . $post_id
		);
		?>
		<div class="notice notice-info odw-samples-notice">
			<p>
				<?php esc_html_e( 'Dies ist ein Beispieldatensatz. Er erscheint in keinem Harvest-Endpunkt, und im Datenkatalog sieht ihn nur, wer angemeldet ist und Datensätze bearbeiten darf. Das gilt auch, wenn Sie ihn bearbeiten und speichern.', 'open-data-wizard' ); ?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Als eigenen Datensatz übernehmen', 'open-data-wizard' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Kasten „Beispieldaten" für Einstiegsseite und Batch-Import.
	 *
	 * @param string $heading Überschriften-Element (h2, h3).
	 * @param string $css     CSS-Klasse der Überschrift.
	 */
	public static function render_box( string $heading = 'h2', string $css = '' ): void {
		if ( ! current_user_can( 'manage_open_data' ) ) {
			return;
		}

		$tag = in_array( $heading, array( 'h2', 'h3' ), true ) ? $heading : 'h2';
		$ids = self::ids();
		?>
		<div class="odw-samples-box" id="odw-beispieldaten">
			<<?php echo esc_html( $tag ); ?> class="<?php echo esc_attr( $css ); ?>"><?php esc_html_e( 'Beispieldaten zum Ausprobieren', 'open-data-wizard' ); ?></<?php echo esc_html( $tag ); ?>>
			<?php if ( array() === self::loaded_ids() ) : ?>
				<p>
					<?php
					printf(
						/* translators: %d: number of sample datasets in the bundled file. */
						esc_html__( 'Zum Ausprobieren können Sie %d vollständig ausgefüllte, frei erfundene Datensätze laden (Musterstadt, Musterkreis, Musterland). Sie erscheinen in keinem Harvest-Endpunkt, und im Datenkatalog auf der Website sieht sie nur, wer angemeldet ist und Datensätze bearbeiten darf. Mit einem Klick sind sie wieder entfernt.', 'open-data-wizard' ),
						(int) self::available()
					);
					?>
				</p>
				<form class="odw-samples-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION_LOAD ); ?>">
					<?php wp_nonce_field( self::ACTION_LOAD ); ?>
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Beispieldaten laden', 'open-data-wizard' ); ?></button>
				</form>
			<?php endif; ?>
			<?php if ( array() !== $ids ) : ?>
				<p>
					<?php
					printf(
						/* translators: %d: number of sample datasets. */
						esc_html__( 'Beispieldatensätze vorhanden: %d. Sie erscheinen in keinem Harvest-Endpunkt, und im Datenkatalog auf der Website sieht sie nur, wer angemeldet ist und Datensätze bearbeiten darf.', 'open-data-wizard' ),
						(int) count( $ids )
					);
					?>
				</p>
				<?php self::render_remove_form(); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Formular „Beispieldaten entfernen" mit Rückfrage. Ein eigenes
	 * Block-Element, nicht in einem <p>: Ein Formular darf dort nicht stehen.
	 */
	private static function render_remove_form(): void {
		$confirm = __( 'Alle Beispieldatensätze endgültig löschen? Änderungen daran gehen verloren.', 'open-data-wizard' );
		?>
		<form class="odw-samples-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return window.confirm( this.dataset.confirm );" data-confirm="<?php echo esc_attr( $confirm ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION_REMOVE ); ?>">
			<?php wp_nonce_field( self::ACTION_REMOVE ); ?>
			<button type="submit" class="button"><?php esc_html_e( 'Beispieldaten entfernen', 'open-data-wizard' ); ?></button>
		</form>
		<?php
	}
}
