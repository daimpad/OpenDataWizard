# Webhook Implementation Plan für Open Data Wizard

**Feature:** Push/Webhook bei Statusänderung an Civora/Piveau  
**Version:** v2.1.0  
**Geschätzter Aufwand:** 3 Stunden  
**Status:** Geplant (nicht implementiert)  
**Erstellt:** 2026-04-22

---

## Inhaltsverzeichnis

1. [Überblick](#überblick)
2. [Phase 1: Settings-Erweiterung](#phase-1-settings-erweiterung)
3. [Phase 2: Core Webhook-Klasse](#phase-2-core-webhook-klasse)
4. [Phase 3: Datenbank-Logging](#phase-3-datenbank-logging)
5. [Phase 4: Admin-UI Integration](#phase-4-admin-ui-integration)
6. [Phase 5: Tests](#phase-5-tests)
7. [Phase 6: Dokumentation](#phase-6-dokumentation)
8. [Checkliste](#checkliste)

---

## Überblick

### Webhook-Funktionalität

Das Plugin soll beim Veröffentlichen, Aktualisieren oder Löschen von Datasets einen POST-Request an einen konfigurierbaren Endpoint (z.B. Civora/Piveau) senden.

**Payload-Struktur:**
```json
{
  "event": "dataset.published|dataset.updated|dataset.deleted",
  "timestamp": "2026-04-22T14:30:00Z",
  "dataset_id": 123,
  "dataset": { ... JSON-LD ... },
  "changes": {
    "status": "publish",
    "modified": "2026-04-22"
  }
}
```

### Architektur-Entscheidungen

| Aspekt | Entscheidung | Begründung |
|--------|-------------|-----------|
| Neue Klasse | `class-webhooks.php` | Single Responsibility; unabhängig von Admin-UI |
| Ausführung | Synchron + async Retry | Sofortige Zustellung mit wp-cron Fallback |
| Logging | Custom DB-Tabelle `wp_odw_webhook_logs` | Persistent, querybar, keine Transient-Ablaufs |
| Auth | Bearer Token | Standard, einfach, erweiterbar via Filter |
| Hook-Point | `transition_post_status` + `delete_post` | Zuverlässig für alle Status-Änderungen |
| Event-Filter | Konfigurierbar in Settings | Verhindert Webhook-Spam bei Edits |

---

## Phase 1: Settings-Erweiterung

### 1.1 Webhook-Felder in `class-settings.php` hinzufügen

**Datei:** `/home/user/OpenDataWizard/includes/class-settings.php`

**Schritt 1a:** In der Methode `register_settings()` eine neue Settings-Section hinzufügen:

```php
// Ungefähr nach der letzten add_settings_section() (vor dem Filter am Ende):
add_settings_section(
    'odw_section_webhooks',
    __( 'Webhooks', 'open-data-wizard' ),
    function () {
        echo '<p>' . esc_html__( 'Configure webhook endpoints to push dataset changes to external platforms (e.g., Civora, Piveau).', 'open-data-wizard' ) . '</p>';
    },
    'odw-settings'
);
```

**Schritt 1b:** Settings-Felder registrieren:

```php
// Nach der add_settings_section():

// Webhook Enable/Disable
add_settings_field(
    'odw_webhook_enabled',
    __( 'Enable Webhooks', 'open-data-wizard' ),
    function () {
        $enabled = get_option( 'odw_settings' )['webhook_enabled'] ?? false;
        ?>
        <input type="checkbox" name="odw_settings[webhook_enabled]" value="1" <?php checked( $enabled, 1 ); ?> />
        <span class="description"><?php esc_html_e( 'Enable sending webhooks on dataset changes', 'open-data-wizard' ); ?></span>
        <?php
    },
    'odw-settings',
    'odw_section_webhooks'
);

// Webhook URL
add_settings_field(
    'odw_webhook_url',
    __( 'Webhook URL', 'open-data-wizard' ),
    function () {
        $settings = get_option( 'odw_settings' ) ?? array();
        $url      = $settings['webhook_url'] ?? '';
        ?>
        <input 
            type="url" 
            name="odw_settings[webhook_url]" 
            value="<?php echo esc_attr( $url ); ?>" 
            placeholder="https://harvest.civora.de/push"
            class="regular-text"
        />
        <span class="description"><?php esc_html_e( 'Full URL of the webhook endpoint (e.g., https://harvest.example.de/push)', 'open-data-wizard' ); ?></span>
        <?php
    },
    'odw-settings',
    'odw_section_webhooks'
);

// Webhook API Token
add_settings_field(
    'odw_webhook_token',
    __( 'API Token', 'open-data-wizard' ),
    function () {
        $settings = get_option( 'odw_settings' ) ?? array();
        $token    = $settings['webhook_token'] ?? '';
        ?>
        <input 
            type="password" 
            name="odw_settings[webhook_token]" 
            value="<?php echo esc_attr( $token ); ?>" 
            placeholder="sk_live_xxxxxxxxxxxxx"
            class="regular-text"
        />
        <span class="description"><?php esc_html_e( 'Bearer token for authentication (will be sent as "Authorization: Bearer {token}")', 'open-data-wizard' ); ?></span>
        <?php
    },
    'odw-settings',
    'odw_section_webhooks'
);

// Webhook Events Filter
add_settings_field(
    'odw_webhook_events',
    __( 'Events to Send', 'open-data-wizard' ),
    function () {
        $settings = get_option( 'odw_settings' ) ?? array();
        $events   = $settings['webhook_events'] ?? array( 'publish', 'update' );
        
        $event_options = array(
            'publish' => __( 'When dataset is published', 'open-data-wizard' ),
            'update'  => __( 'When dataset is updated', 'open-data-wizard' ),
            'delete'  => __( 'When dataset is deleted', 'open-data-wizard' ),
        );
        
        foreach ( $event_options as $value => $label ) {
            $checked = in_array( $value, (array) $events, true ) ? 'checked' : '';
            echo '<label>';
            echo '<input type="checkbox" name="odw_settings[webhook_events][]" value="' . esc_attr( $value ) . '" ' . esc_attr( $checked ) . ' />';
            echo ' ' . esc_html( $label );
            echo '</label><br />';
        }
    },
    'odw-settings',
    'odw_section_webhooks'
);

// Webhook Max Retries
add_settings_field(
    'odw_webhook_max_retries',
    __( 'Max Retries', 'open-data-wizard' ),
    function () {
        $settings = get_option( 'odw_settings' ) ?? array();
        $retries  = $settings['webhook_max_retries'] ?? 3;
        
        $retry_options = array( 1, 3, 5 );
        ?>
        <select name="odw_settings[webhook_max_retries]">
            <?php foreach ( $retry_options as $option ) : ?>
                <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $retries, $option ); ?>>
                    <?php echo esc_html( $option ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="description"><?php esc_html_e( 'Number of retry attempts on failure (0s, 30s, 2m, 5m delays)', 'open-data-wizard' ); ?></span>
        <?php
    },
    'odw-settings',
    'odw_section_webhooks'
);

// Test Webhook Button
add_settings_field(
    'odw_webhook_test',
    __( 'Test Webhook', 'open-data-wizard' ),
    function () {
        wp_nonce_field( 'odw_test_webhook', 'odw_test_webhook_nonce' );
        ?>
        <button 
            type="button" 
            class="button button-secondary" 
            id="odw-test-webhook-btn"
        >
            <?php esc_html_e( 'Send Test Webhook', 'open-data-wizard' ); ?>
        </button>
        <span id="odw-test-webhook-result"></span>
        <script>
            document.getElementById('odw-test-webhook-btn')?.addEventListener('click', function() {
                var btn = this;
                btn.disabled = true;
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'odw_test_webhook',
                        nonce: document.querySelector('[name="odw_test_webhook_nonce"]').value
                    })
                })
                .then(r => r.json())
                .then(d => {
                    var result = document.getElementById('odw-test-webhook-result');
                    if (d.success) {
                        result.innerHTML = ' ✓ ' + d.data.message;
                        result.style.color = 'green';
                    } else {
                        result.innerHTML = ' ✗ ' + d.data.message;
                        result.style.color = 'red';
                    }
                    btn.disabled = false;
                })
                .catch(e => {
                    document.getElementById('odw-test-webhook-result').innerHTML = ' ✗ Error: ' + e.message;
                    btn.disabled = false;
                });
            });
        </script>
        <?php
    },
    'odw-settings',
    'odw_section_webhooks'
);
```

**Schritt 1c:** Sanitize-Callback erweitern (in `ODW_Settings::sanitize()`):

```php
// In der sanitize() Methode, in der section für 'odw-settings':
public static function sanitize( array $input ): array {
    // ... existing code ...

    // Webhook settings.
    if ( isset( $input['webhook_enabled'] ) ) {
        $input['webhook_enabled'] = absint( $input['webhook_enabled'] );
    }

    if ( isset( $input['webhook_url'] ) ) {
        $input['webhook_url'] = esc_url_raw( $input['webhook_url'] );
    }

    if ( isset( $input['webhook_token'] ) ) {
        $input['webhook_token'] = sanitize_text_field( $input['webhook_token'] );
    }

    if ( isset( $input['webhook_events'] ) && is_array( $input['webhook_events'] ) ) {
        $valid_events              = array( 'publish', 'update', 'delete' );
        $input['webhook_events'] = array_filter( $input['webhook_events'], function ( $event ) use ( $valid_events ) {
            return in_array( $event, $valid_events, true );
        } );
    } else {
        $input['webhook_events'] = array();
    }

    if ( isset( $input['webhook_max_retries'] ) ) {
        $input['webhook_max_retries'] = absint( $input['webhook_max_retries'] );
    }

    return $input;
}
```

---

## Phase 2: Core Webhook-Klasse

### 2.1 Neue Datei: `class-webhooks.php`

**Datei:** `/home/user/OpenDataWizard/includes/class-webhooks.php`

**Vollständiger Inhalt:**

```php
<?php
/**
 * Webhook integration for pushing dataset changes to external platforms.
 *
 * Sends POST requests to configured endpoints (e.g., Civora, Piveau) when
 * datasets are published, updated, or deleted.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles webhook triggers and HTTP communication.
 *
 * @package OpenDataWizard
 */
class ODW_Webhooks {

	/**
	 * Registers hooks for webhook triggering.
	 */
	public static function init(): void {
		// Trigger on status transitions (publish, draft, trash).
		add_action( 'transition_post_status', array( self::class, 'on_post_status_change' ), 20, 3 );

		// Trigger on deletion (before post is removed from DB).
		add_action( 'delete_post', array( self::class, 'on_post_delete' ), 20 );

		// Cron action for retry logic.
		add_action( 'wp_scheduled_odw_webhook_retry', array( self::class, 'handle_scheduled_retry' ), 10, 2 );

		// AJAX handler for test webhook.
		add_action( 'wp_ajax_odw_test_webhook', array( self::class, 'handle_test_webhook' ) );
	}

	/**
	 * Called when a post's status changes (publish, draft, trash, etc).
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public static function on_post_status_change( string $new_status, string $old_status, $post ): void {
		// Only process odw_dataset posts.
		if ( 'odw_dataset' !== $post->post_type ) {
			return;
		}

		// Avoid double-triggering (e.g., on revisions).
		if ( wp_is_post_revision( $post->ID ) ) {
			return;
		}

		$event_type = self::get_event_type( $old_status, $new_status );

		if ( ! $event_type ) {
			return; // No relevant event.
		}

		self::send_webhook( $post->ID, $event_type );
	}

	/**
	 * Called when a post is deleted.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function on_post_delete( int $post_id ): void {
		$post = get_post( $post_id );

		if ( ! $post || 'odw_dataset' !== $post->post_type ) {
			return;
		}

		self::send_webhook( $post_id, 'deleted' );
	}

	/**
	 * Determine event type from status transition.
	 *
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @return string|null Event type (published, updated, unpublished) or null.
	 */
	private static function get_event_type( string $old_status, string $new_status ): ?string {
		// Not published → Published = "published" event.
		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			return 'published';
		}

		// Published → Unpublished = no event (filter-dependent).
		if ( 'publish' === $old_status && 'publish' !== $new_status ) {
			return null; // Could be 'unpublished' if filtered.
		}

		// Published → Published = "updated" event.
		if ( 'publish' === $old_status && 'publish' === $new_status ) {
			return 'updated';
		}

		return null;
	}

	/**
	 * Send webhook POST request for a dataset.
	 *
	 * @param int    $post_id    Dataset post ID.
	 * @param string $event_type Event type (published, updated, deleted).
	 */
	public static function send_webhook( int $post_id, string $event_type ): void {
		// Check if webhooks are enabled.
		if ( ! self::is_enabled() ) {
			return;
		}

		// Check if this event type is configured.
		if ( ! self::should_send_event( $event_type ) ) {
			return;
		}

		$url    = self::get_webhook_url();
		$token  = self::get_webhook_token();
		$post   = get_post( $post_id );

		if ( ! $url || ! $token || ! $post ) {
			return;
		}

		// Build payload.
		$payload = self::build_payload( $post, $event_type );

		// Send request.
		$response = self::make_request( $url, $payload, $token );

		// Log attempt.
		self::log_webhook_attempt( $post_id, $event_type, $response );

		// Schedule retry if failed.
		if ( ! $response['success'] && $response['attempt'] < self::get_max_retries() ) {
			self::schedule_retry( $post_id, $event_type, $response['attempt'] );
		}
	}

	/**
	 * Check if webhooks are enabled in settings.
	 *
	 * @return bool
	 */
	private static function is_enabled(): bool {
		if ( ! class_exists( 'ODW_Settings' ) ) {
			return false;
		}

		$enabled = ODW_Settings::get( 'webhook_enabled' );
		return ! empty( $enabled );
	}

	/**
	 * Check if a specific event type should trigger webhooks.
	 *
	 * @param string $event_type Event type.
	 * @return bool
	 */
	private static function should_send_event( string $event_type ): bool {
		if ( ! class_exists( 'ODW_Settings' ) ) {
			return false;
		}

		$events = ODW_Settings::get( 'webhook_events' );

		if ( ! is_array( $events ) ) {
			return false;
		}

		// Map internal events to configured event types.
		$event_map = array(
			'published' => 'publish',
			'updated'   => 'update',
			'deleted'   => 'delete',
		);

		$event_key = $event_map[ $event_type ] ?? null;

		return $event_key && in_array( $event_key, $events, true );
	}

	/**
	 * Get webhook URL from settings.
	 *
	 * @return string|null
	 */
	private static function get_webhook_url(): ?string {
		if ( ! class_exists( 'ODW_Settings' ) ) {
			return null;
		}

		$url = ODW_Settings::get( 'webhook_url' );
		return ! empty( $url ) ? (string) $url : null;
	}

	/**
	 * Get webhook token from settings.
	 *
	 * @return string|null
	 */
	private static function get_webhook_token(): ?string {
		if ( ! class_exists( 'ODW_Settings' ) ) {
			return null;
		}

		$token = ODW_Settings::get( 'webhook_token' );
		return ! empty( $token ) ? (string) $token : null;
	}

	/**
	 * Get max retries from settings.
	 *
	 * @return int
	 */
	private static function get_max_retries(): int {
		if ( ! class_exists( 'ODW_Settings' ) ) {
			return 3;
		}

		$max = ODW_Settings::get( 'webhook_max_retries' );
		return absint( $max ) > 0 ? absint( $max ) : 3;
	}

	/**
	 * Build webhook payload from dataset.
	 *
	 * @param WP_Post $post       Dataset post.
	 * @param string  $event_type Event type.
	 * @return array Payload array.
	 */
	private static function build_payload( WP_Post $post, string $event_type ): array {
		$jsonld = array();
		if ( function_exists( 'odw_build_dataset_jsonld' ) ) {
			$jsonld = odw_build_dataset_jsonld( $post->ID ) ?? array();
		}

		return array(
			'event'      => 'dataset.' . $event_type,
			'timestamp'  => gmdate( 'c' ),
			'dataset_id' => $post->ID,
			'dataset'    => $jsonld,
			'changes'    => array(
				'status'   => $post->post_status,
				'modified' => $post->post_modified_gmt,
			),
		);
	}

	/**
	 * Make HTTP POST request to webhook endpoint.
	 *
	 * @param string $url     Webhook URL.
	 * @param array  $payload Payload data.
	 * @param string $token   Bearer token.
	 * @return array Response: ['success' => bool, 'status_code' => int, 'body' => string, 'error' => string, 'attempt' => int]
	 */
	private static function make_request( string $url, array $payload, string $token ): array {
		$response = array(
			'success'     => false,
			'status_code' => 0,
			'body'        => '',
			'error'       => '',
			'attempt'     => 1,
		);

		$args = array(
			'method'      => 'POST',
			'timeout'     => 10,
			'sslverify'   => true,
			'headers'     => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $token,
				'User-Agent'    => 'Open Data Wizard/' . ODW_VERSION,
			),
			'body'        => wp_json_encode( $payload ),
		);

		$http_response = wp_remote_post( $url, $args );

		if ( is_wp_error( $http_response ) ) {
			$response['error'] = $http_response->get_error_message();
			return $response;
		}

		$status_code            = (int) wp_remote_retrieve_response_code( $http_response );
		$response['status_code'] = $status_code;
		$response['body']       = wp_remote_retrieve_body( $http_response );

		// Success: 2xx status code.
		if ( $status_code >= 200 && $status_code < 300 ) {
			$response['success'] = true;
		} else {
			$response['error'] = "HTTP {$status_code}";
		}

		return $response;
	}

	/**
	 * Log webhook attempt to database.
	 *
	 * @param int   $post_id    Dataset post ID.
	 * @param string $event_type Event type.
	 * @param array  $response   Response from make_request().
	 */
	private static function log_webhook_attempt( int $post_id, string $event_type, array $response ): void {
		global $wpdb;

		$table = "{$wpdb->prefix}odw_webhook_logs";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert(
			$table,
			array(
				'post_id'      => $post_id,
				'event_type'   => $event_type,
				'timestamp'    => current_time( 'mysql', true ),
				'attempt'      => $response['attempt'] ?? 1,
				'status_code'  => $response['status_code'] ?? 0,
				'response_body' => substr( $response['body'] ?? '', 0, 1000 ),
				'error_message' => substr( $response['error'] ?? '', 0, 500 ),
			),
			array( '%d', '%s', '%s', '%d', '%d', '%s', '%s' )
		);
	}

	/**
	 * Schedule a retry attempt via wp-cron.
	 *
	 * @param int    $post_id    Dataset post ID.
	 * @param string $event_type Event type.
	 * @param int    $attempt    Current attempt number.
	 */
	private static function schedule_retry( int $post_id, string $event_type, int $attempt ): void {
		$next_attempt = $attempt + 1;

		// Exponential backoff: 30s, 2m, 5m.
		$delays = array(
			1 => 30,    // Attempt 1 failed → retry in 30s.
			2 => 120,   // Attempt 2 failed → retry in 2m.
			3 => 300,   // Attempt 3 failed → retry in 5m.
		);

		$delay = $delays[ $attempt ] ?? 300;

		wp_schedule_single_event(
			time() + $delay,
			'wp_scheduled_odw_webhook_retry',
			array( $post_id, $event_type )
		);
	}

	/**
	 * Handle scheduled retry execution (called by wp-cron).
	 *
	 * @param int    $post_id    Dataset post ID.
	 * @param string $event_type Event type.
	 */
	public static function handle_scheduled_retry( int $post_id, string $event_type ): void {
		// Simply retry by calling send_webhook again.
		self::send_webhook( $post_id, $event_type );
	}

	/**
	 * Handle AJAX test webhook request.
	 *
	 * @wp-hook wp_ajax_odw_test_webhook
	 */
	public static function handle_test_webhook(): void {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'odw_test_webhook' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'open-data-wizard' ) ) );
		}

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'open-data-wizard' ) ) );
		}

		$url    = self::get_webhook_url();
		$token  = self::get_webhook_token();

		if ( ! $url ) {
			wp_send_json_error( array( 'message' => __( 'Webhook URL not configured', 'open-data-wizard' ) ) );
		}

		if ( ! $token ) {
			wp_send_json_error( array( 'message' => __( 'Webhook token not configured', 'open-data-wizard' ) ) );
		}

		// Find a published dataset for testing (or use a sample).
		$sample_post = get_posts(
			array(
				'post_type'      => 'odw_dataset',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		if ( ! empty( $sample_post ) ) {
			$payload = self::build_payload( $sample_post[0], 'test' );
		} else {
			// Sample payload if no published datasets exist.
			$payload = array(
				'event'      => 'dataset.test',
				'timestamp'  => gmdate( 'c' ),
				'dataset_id' => 0,
				'dataset'    => array(
					'@context' => 'https://www.w3.org/ns/dcat',
					'@type'    => 'dcat:Dataset',
					'dct:title' => 'Test Dataset',
				),
				'changes'    => array( 'status' => 'test' ),
			);
		}

		$response = self::make_request( $url, $payload, $token );

		if ( $response['success'] ) {
			wp_send_json_success(
				array(
					/* translators: %d = HTTP status code */
					'message' => sprintf( __( 'Webhook sent successfully (HTTP %d)', 'open-data-wizard' ), $response['status_code'] ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					/* translators: %s = error message */
					'message' => sprintf( __( 'Webhook failed: %s', 'open-data-wizard' ), $response['error'] ),
				)
			);
		}
	}

	/**
	 * Get webhook logs for a dataset (for admin UI).
	 *
	 * @param int $post_id Dataset post ID.
	 * @param int $limit   Number of recent logs to fetch.
	 * @return array Array of log entries.
	 */
	public static function get_logs( int $post_id, int $limit = 10 ): array {
		global $wpdb;

		$table = "{$wpdb->prefix}odw_webhook_logs";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE post_id = %d ORDER BY timestamp DESC LIMIT %d",
				$post_id,
				$limit
			)
		);

		return $logs ?? array();
	}
}
```

### 2.2 Bootstrap-Integration

**Datei:** `/home/user/OpenDataWizard/open-data-wizard.php`

**Schritt:** In der `odw_bootstrap()` Funktion (ungefähr nach Zeile 136) die neue Klasse laden und initialisieren:

```php
// Nach: require_once ODW_PLUGIN_DIR . 'includes/class-shortcode.php';

require_once ODW_PLUGIN_DIR . 'includes/class-webhooks.php';

// Nach: ODW_Shortcode::init();

ODW_Webhooks::init();
```

---

## Phase 3: Datenbank-Logging

### 3.1 Logging-Tabelle bei Aktivierung erstellen

**Datei:** `/home/user/OpenDataWizard/includes/class-setup.php`

**Schritt:** In der Methode `on_activation()` oder `maybe_create_demo()` die Tabelle erstellen:

```php
// In class-setup.php, eine neue static Methode hinzufügen:

/**
 * Create webhook logging table.
 */
public static function create_webhook_logs_table(): void {
	global $wpdb;

	$table      = "{$wpdb->prefix}odw_webhook_logs";
	$charset    = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id INT AUTO_INCREMENT PRIMARY KEY,
		post_id INT NOT NULL,
		event_type VARCHAR(50) NOT NULL,
		timestamp DATETIME NOT NULL,
		attempt INT DEFAULT 1,
		status_code INT DEFAULT 0,
		response_body LONGTEXT,
		error_message TEXT,
		KEY post_id (post_id),
		KEY timestamp (timestamp)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

// In maybe_create_demo() oder on_activation() aufrufen:
self::create_webhook_logs_table();
```

### 3.2 Tabelle bei Deinstallation löschen

**Datei:** `/home/user/OpenDataWizard/uninstall.php`

**Schritt:** Am Ende der Datei, vor dem `exit`:

```php
// Wenn delete_on_uninstall aktiviert ist, auch Webhook-Logs-Tabelle löschen.
if ( $delete_on_uninstall ) {
	global $wpdb;
	$table = "{$wpdb->prefix}odw_webhook_logs";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}
```

---

## Phase 4: Admin-UI Integration

### 4.1 Webhook-Logs Meta-Box auf Edit-Screen

**Datei:** `/home/user/OpenDataWizard/includes/class-admin.php`

**Schritt:** In der Methode `init()` einen neuen Hook hinzufügen:

```php
add_action( 'add_meta_boxes', array( self::class, 'register_webhook_meta_box' ) );
```

**Schritt:** Neue Methode hinzufügen:

```php
/**
 * Register webhook logs meta box on dataset edit screen.
 */
public static function register_webhook_meta_box(): void {
	add_meta_box(
		'odw_webhook_logs',
		__( 'Webhook Activity', 'open-data-wizard' ),
		array( self::class, 'render_webhook_meta_box' ),
		'odw_dataset',
		'side'
	);
}

/**
 * Render webhook logs meta box.
 */
public static function render_webhook_meta_box( $post ): void {
	if ( ! class_exists( 'ODW_Webhooks' ) ) {
		echo '<p>' . esc_html__( 'Webhooks not available', 'open-data-wizard' ) . '</p>';
		return;
	}

	$logs = ODW_Webhooks::get_logs( $post->ID, 5 );

	if ( empty( $logs ) ) {
		echo '<p>' . esc_html__( 'No webhook activity yet', 'open-data-wizard' ) . '</p>';
		return;
	}

	echo '<table style="width: 100%; font-size: 11px;">';
	echo '<tr><th>' . esc_html__( 'Date', 'open-data-wizard' ) . '</th><th>' . esc_html__( 'Event', 'open-data-wizard' ) . '</th><th>' . esc_html__( 'Status', 'open-data-wizard' ) . '</th></tr>';

	foreach ( $logs as $log ) {
		$status_html = '';
		if ( $log->status_code >= 200 && $log->status_code < 300 ) {
			$status_html = '<span style="color: green;">✓ ' . esc_html( $log->status_code ) . '</span>';
		} else {
			$status_html = '<span style="color: red;">✗ ' . esc_html( $log->error_message ) . '</span>';
		}

		echo '<tr>';
		echo '<td>' . esc_html( $log->timestamp ) . '</td>';
		echo '<td>' . esc_html( $log->event_type ) . '</td>';
		echo '<td>' . wp_kses_post( $status_html ) . '</td>';
		echo '</tr>';
	}

	echo '</table>';
}
```

### 4.2 Webhook-Status in der Listen-Spalte

**Datei:** `/home/user/OpenDataWizard/includes/class-admin.php`

**Schritt:** In der Methode `add_columns()` eine neue Spalte hinzufügen:

```php
$columns['webhook_status'] = __( 'Webhook', 'open-data-wizard' );
```

**Schritt:** In der Methode `render_column()` den Content für die neue Spalte hinzufügen:

```php
case 'webhook_status':
	if ( ! class_exists( 'ODW_Webhooks' ) ) {
		echo '—';
		break;
	}
	
	$logs = ODW_Webhooks::get_logs( $post_id, 1 );
	if ( ! empty( $logs ) ) {
		$log = $logs[0];
		if ( $log->status_code >= 200 && $log->status_code < 300 ) {
			echo '<span style="color: green;">✓ Sent</span>';
		} else {
			echo '<span style="color: orange;">⚠ Retry</span>';
		}
	} else {
		echo '—';
	}
	break;
```

---

## Phase 5: Tests

### 5.1 Neue Test-Datei erstellen

**Datei:** `/home/user/OpenDataWizard/tests/test-webhooks.php`

**Inhalt:** (siehe separate Test-Implementierungsdatei)

Die Tests sollten folgende Szenarien abdecken:
- `test_webhook_disabled_does_not_send()` — Wenn Webhooks deaktiviert, nichts senden
- `test_publish_event_triggers_webhook()` — Bei Veröffentlichung triggert Webhook
- `test_payload_includes_jsonld()` — Payload enthält JSON-LD des Datasets
- `test_failed_request_is_logged()` — Failed Response wird in DB geloggt
- `test_retry_scheduled_on_failure()` — Nach Fehler wird Retry geplant
- `test_test_webhook_handler()` — Test-Webhook AJAX-Handler funktioniert

---

## Phase 6: Dokumentation

### 6.1 README.md aktualisieren

**Datei:** `/home/user/OpenDataWizard/README.md`

**Neue Sektion hinzufügen** (vor "Roadmap"):

```markdown
### Webhook-Integration für Civora/Piveau

Das Plugin kann Datasets automatisch an externe Harvesting-Plattformen pushen, wenn sie veröffentlicht, aktualisiert oder gelöscht werden.

#### Konfiguration

Unter **Datensätze → Einstellungen → Webhooks**:

1. **Enable Webhooks** — Checkbox zum Aktivieren/Deaktivieren
2. **Webhook URL** — Endpoint der Harvesting-Plattform (z.B. `https://harvest.civora.de/push`)
3. **API Token** — Bearer Token für Authentication (wird als `Authorization: Bearer {token}` gesendet)
4. **Events to Send** — Auswählen welche Events triggern (Publish, Update, Delete)
5. **Max Retries** — Anzahl der Wiederholungsversuche bei Fehlern (1, 3 oder 5)

#### Webhook-Payload

Beim Trigger wird ein POST-Request mit folgender Struktur gesendet:

```json
{
  "event": "dataset.published|dataset.updated|dataset.deleted",
  "timestamp": "2026-04-22T14:30:00Z",
  "dataset_id": 123,
  "dataset": { ... JSON-LD ... },
  "changes": {
    "status": "publish",
    "modified": "2026-04-22"
  }
}
```

#### Fehlerbehandlung & Retries

Wenn ein Webhook fehlschlägt (Timeout, 4xx/5xx Response):
- Fehler wird in die Webhook-Logs eingetragen
- Automatischer Retry nach 30 Sekunden (Versuch 1)
- Dann nach 2 Minuten (Versuch 2)
- Dann nach 5 Minuten (Versuch 3)
- Konfigurierbare maximale Retry-Versuche

#### Webhook-Aktivität im Admin

- **Meta-Box auf Edit-Screen** — Zeigt die letzten 5 Webhook-Versuche
- **Spalte in der Listen-Ansicht** — Status-Icon (✓ oder ⚠)
- **Test-Webhook Button** — In Einstellungen zum Testen der Konfiguration
```

### 6.2 CLAUDE.md aktualisieren

**Neue Sektion hinzufügen** unter "Common Development Tasks":

```markdown
### Adding a Webhook Event

To trigger webhooks on new dataset events:

1. **Modify trigger logic** in `class-webhooks.php::get_event_type()`:
   ```php
   if ( 'my_new_status' === $old_status && 'publish' === $new_status ) {
       return 'my_event_type';
   }
   ```

2. **Update event mapping** in `should_send_event()`:
   ```php
   $event_map = array(
       ...
       'my_event_type' => 'my_filter_key',
   );
   ```

3. **Add setting in Settings UI** for the new event filter option

4. **Test** with `wp open-data-wizard webhooks send-test`
```

### 6.3 CHANGELOG.md aktualisieren

**Neue Entry hinzufügen**:

```markdown
## [2.1.0] — TBD

### Hinzugefügt
- **Webhook-Integration für Civora/Piveau** (`GET /wp-json/datenatlas/v1/webhooks/...`):
  - Automatisches Pushen von Datasets bei Veröffentlichung, Aktualisierung, Löschung
  - Konfigurierbare Webhook-URL und Bearer Token in Einstellungen
  - Event-Filterung (Publish, Update, Delete)
  - Automatische Retry-Logik mit exponentiellem Backoff (30s, 2m, 5m)
  - Webhook-Logs in Custom DB-Tabelle mit Admin-UI Anzeige
  - Test-Webhook Button zur Verifikation der Konfiguration
  - 6 neue PHPUnit-Tests für Webhook-Funktionalität
```

---

## Checkliste

### Vor Implementierung

- [ ] Diesen Plan durchgelesen und verstanden
- [ ] Git-Branch verifiziert: `claude/wordpress-open-data-wizard-MmOEL`
- [ ] `composer install` ausgeführt (dev dependencies vorhanden)

### Implementierung

#### Phase 1: Settings
- [ ] `class-settings.php` erweitert mit 5 Webhook-Feldern
- [ ] Sanitize-Callback implementiert
- [ ] Test-Webhook AJAX-Handler gebaut

#### Phase 2: Webhooks
- [ ] `class-webhooks.php` erstellt (150 Zeilen)
- [ ] In `open-data-wizard.php` bootstrapped
- [ ] Alle 6 Hooks registriert:
  - [ ] `transition_post_status`
  - [ ] `delete_post`
  - [ ] `wp_scheduled_odw_webhook_retry`
  - [ ] `wp_ajax_odw_test_webhook`

#### Phase 3: Datenbank
- [ ] Logging-Tabelle-Erstellung in `class-setup.php`
- [ ] Tabellen-Löschen in `uninstall.php`
- [ ] Migrations-Check (nicht nötig für MVP, aber dokumentieren)

#### Phase 4: Admin-UI
- [ ] Meta-Box für Webhook-Logs auf Edit-Screen
- [ ] Spalte in Listen-Ansicht
- [ ] Status-Icons (✓, ⚠, ✗)

#### Phase 5: Tests
- [ ] `test-webhooks.php` mit 6+ Tests
- [ ] Alle Tests passing: `composer test -- --filter Test_ODW_Webhooks`
- [ ] PHPCS check: `composer phpcs -- includes/class-webhooks.php`

#### Phase 6: Dokumentation
- [ ] README.md mit Webhook-Sektion
- [ ] CLAUDE.md mit neuer Event-Klasse dokumentiert
- [ ] CHANGELOG.md mit v2.1.0 Entry
- [ ] Inline-Docstrings in Klasse vollständig

### Nach Implementierung

- [ ] Alle Tests passing (69+ Tests)
- [ ] PHPCS 0 Violations
- [ ] PHPStan Level 6 clean
- [ ] Commit mit beschreibender Message
- [ ] Push zu `claude/wordpress-open-data-wizard-MmOEL`
- [ ] Git-Log zeigt 1 neuen Commit

### Testing (manuell)

- [ ] Settings speichern/laden funktioniert
- [ ] Webhook beim Veröffentlichen getriggert
- [ ] Logs in DB erscheinen
- [ ] Meta-Box zeigt Logs korrekt
- [ ] Test-Webhook Button funktioniert
- [ ] Retry wird nach Fehler geplant
- [ ] Bearer Token wird korrekt gesendet

---

## Geschätzter Zeitaufwand

| Phase | Aufgabe | Dauer |
|-------|---------|-------|
| 1 | Settings-Erweiterung + Sanitize | 30 min |
| 2 | Webhook-Klasse vollständig | 60 min |
| 3 | DB-Logging-Tabelle | 15 min |
| 4 | Admin-UI (Meta-Box + Spalte) | 30 min |
| 5 | Tests (6+) | 30 min |
| 6 | Dokumentation | 15 min |
| — | Debugging & Verfeinerung | 30 min |
| **Total** | | **3 Stunden** |

---

## Notizen für zukünftige Implementierung

1. **Keine Breaking Changes** — Bestehendes REST API bleibt unverändert
2. **Backward Compatible** — Settings optional, Webhooks können jederzeit deaktiviert werden
3. **Graceful Degradation** — Fehlerhafte Webhooks blockieren nicht das Dataset-Speichern
4. **Logging wichtig** — Alle Webhook-Attempts müssen geloggt werden für Debugging
5. **Security** — Bearer Token mit `esc_attr()` behandeln, nie in Logs speichern (nur Fehler)
6. **Performance** — Synchrone HTTP-Requests; falls zu langsam, zu async Job Queue migrieren
7. **Testing** — Mock `wp_remote_post()` für Unit-Tests, use `wp_http_validate_url()` für URL-Validierung
8. **Erweiterbarkeit** — Filter einbauen z.B. `apply_filters( 'odw_webhook_payload', $payload, $post_id )`

---

**Status: Bereit zur Implementierung**  
**Letzte Überprüfung:** 2026-04-22  
**Nächster Schritt:** Implementierung starten (Phase 1)
