# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Quick Commands

### Development & Testing
```bash
composer install                # Install all dev dependencies (PHPUnit, PHPStan, WPCS)
composer phpcs                  # Run WordPress Coding Standards checks
composer phpcbf                 # Auto-fix PHPCS violations
composer phpstan                # Static analysis (Level 6) — false positives expected from Carbon Fields stubs
composer test                   # Run PHPUnit (all 90 tests)
composer test -- tests/test-fields.php      # Run a single test file
composer test -- --filter testMethodName    # Run specific test by name
```

CI runs on **PHP 8.1, 8.2, 8.3** via GitHub Actions (`.github/workflows/ci.yml`).

---

## Architecture Overview

### High-Level Design

Open Data Wizard is a **WordPress plugin** that bridges the gap between WordPress (where organizations already manage content) and Open Data standards (DCAT-AP 3.0, which machines can understand).

**Core Flow:**
1. Admin creates/edits a **Dataset Post** (CPT: `odw_dataset`) using a guided **Carbon Fields 5-tab form**
2. Form collects DCAT-AP 3.0 metadata (title, license, distributions, etc.)
3. On publish, **validation hooks** block publishing if required fields are missing
4. Metadata is **persisted as post meta** + **JSON-LD cached in transients**
5. **REST API endpoints** expose published datasets as machine-readable JSON-LD
6. External **harvesting systems** can fetch the `/catalog` endpoint and automatically ingest datasets

### Class Hierarchy

All plugin classes use **static methods** (no instantiation) and follow a **hook-based initialization pattern**:

```php
// Typical pattern:
class ODW_Something {
    public static function init(): void {
        add_action( 'hook_name', array( self::class, 'handler_method' ) );
    }
    
    public static function handler_method(): void { ... }
}

// Bootstrap in open-data-wizard.php:
ODW_Something::init();  // Called in odw_bootstrap()
```

### Key Classes & Responsibilities

| Class | Role | Key Methods |
|-------|------|-----------|
| `ODW_Post_Types` | Registers the `odw_dataset` CPT with capability mapping | `register()` — maps all write ops to `manage_open_data` cap |
| `ODW_Fields` | Defines the 5-tab Carbon Fields form + JSON-LD builder | `register()`, `register_required_fields()`, `odw_build_dataset_jsonld()` (companion function) |
| `ODW_Validation` | Blocks publishing if required fields missing; stores errors as transients | `intercept_publish()`, `validate()` |
| `ODW_Quality` | Auto-calculates DCAT-AP completeness (0–100 score, 4 levels) after save | `calculate()`, `check_indicator()`, `get_level()` |
| `ODW_Admin` | Admin UI: list columns, sortable columns, help tabs, wp.media upload meta-box | `render_column()`, `handle_meta_orderby()` (for sortable theme column via `pre_get_posts`) |
| `ODW_Rest_API` | `/catalog` and `/datasets/<id>` + `/delta?since=<ISO8601>` endpoints | `get_catalog()`, `get_dataset()`, `get_delta()` — all with transient caching (5 min TTL) |
| `ODW_Shortcode` | `[odw_dataset id="123"]` renders download card in frontend | `render()` |
| `ODW_Settings` | Settings page (catalog title, defaults, cache TTL, cleanup checkbox) | `get()`, `filter_catalog_title()` |
| `ODW_Setup` | Activation: create demo dataset, show welcome notice | `maybe_create_demo()`, `create_demo_dataset()` |

---

## Key Patterns & Architecture Decisions

### 1. **Capability-Based Access Control**

The plugin restricts dataset creation/editing to users with `manage_open_data` capability (granted to admins + editors on activation).

- **CPT Setup** (`class-post-types.php`): Uses `capability_type => 'odw_dataset'` + explicit `capabilities` map (all write ops → `manage_open_data`)
- **Activation** (`open-data-wizard.php`): `odw_add_capabilities()` grants cap to roles on activation
- **Uninstall** (`uninstall.php`): Removes cap from roles on deinstallation (if opt-in checkbox enabled)

This means **contributors and authors cannot create datasets** without explicitly receiving the cap.

### 2. **JSON-LD as Single Source of Truth**

The JSON-LD object (returned by `odw_build_dataset_jsonld()`) is the **canonical representation** of a dataset for external consumption.

- Populated from **both** Carbon Fields (tabs 1–4) and **post meta** (`_odw_modified`, `_odw_quality_*`, `_odw_file_*`)
- Cached as transients keyed by post ID to avoid rebuilding on every REST request
- Sanitized at **output time**: `access_url` values are run through `esc_url_raw()` to strip dangerous schemes (`javascript:`, `data:`)
- Extensible via the `odw_dataset_jsonld` filter before cache

### 3. **Carbon Fields for Admin UI, Post Meta for Storage**

**Why this split?**
- Carbon Fields (`class-fields.php`) provides a beautiful, translatable UI with validation, but **serializes everything as post meta**
- Directly working with post meta in business logic (`class-validation.php`, `class-quality.php`, JSON builder) keeps the code decoupled from CF's internal APIs

**Key difference:** CF field names like `odw_description` become post meta keys `_odw_description` after save.

### 4. **Transient Caching for REST API**

All REST endpoints cache their responses in transients:
- **Catalog** cache key: `odw_catalog_` + MD5(page, per_page, filters)
- **Dataset** cache key: `odw_dataset_` + post_id
- **Delta** cache key: `odw_delta_` + MD5(since, page, per_page)
- **TTL:** 5 minutes (configurable in settings)

Cache is **invalidated on save/trash** via `save_post_odw_dataset` and `trashed_post` hooks in `class-rest-api.php`.

### 5. **Validation as a Gating Mechanism**

Validation (`class-validation.php`) doesn't prevent saves — it **prevents publishing**:
1. `wp_insert_post_data` filter intercepts publish attempts
2. Runs `validate()` against required fields
3. If errors, **reverts status to draft** and stores errors in a transient (300s TTL)
4. Admin notice (`admin_notices` hook) displays the transient errors

This allows drafts with incomplete data but forces completeness before public visibility.

### 6. **Companion Functions at File Level**

Two companion functions live **outside** class definitions in their respective files:

- `odw_build_dataset_jsonld( int $post_id ): ?array` in `class-fields.php`
  - Used by both REST API and JSON-LD preview tab
  - Not a method because it's called from multiple classes and needs high visibility
  - PHPCS ignores the "mixed declarations" sniff here

- `odw_format_bytes( int $bytes, int $precision = 2 ): string` in `class-shortcode.php`
  - Utility for human-readable file size in download card

### 7. **Pre-Computed File Metadata**

When a file is attached via the wp.media upload meta-box (`odw-file-upload.js`), the **save handler** (`save_file_attachment()` in `class-admin.php`) immediately computes and stores:
- `_odw_file_size` (as integer, bytes)
- `_odw_file_format` (as uppercase string, e.g. "CSV")

This avoids **runtime I/O** in the shortcode rendering (`get_filesize()` is slow).

### 8. **Delta Endpoint for Incremental Harvesting**

`GET /wp-json/datenatlas/v1/delta?since=<ISO8601>` returns only datasets **modified after** a timestamp, plus tombstones for deleted ones.

- Uses `post_modified_gmt` (UTC) for comparison to avoid timezone drift
- Pagination applies only to modified datasets; all tombstones always included
- Used by harvesters to sync only changes, not re-fetch entire catalog

### 9. **UX-First Form Design (v2.0.0+)**

As of v2.0.0, all form fields prioritize **user experience over technical accuracy** in labels:

- **Main label** (Carbon Fields `Field::make()` second parameter): User-friendly question, not DCAT-AP term
  - ✅ Good: "Wer gibt diese Daten heraus?" 
  - ❌ Bad: "Herausgebende Organisation (dct:publisher)"
  
- **Help text** (`set_help_text()`): Preserves all technical context in a structured format:
  - Original technical label (uppercase, DCAT-AP term in parentheses)
  - Blank line
  - Concrete, realistic example(s)
  - Format: `"LABEL (dcat:term)\n\nExample: instance, example, item"`

- **Validation labels** (`ODW_Fields::get_required_fields()`): Use the same user-friendly question as the field label
  - Error messages show: "Worum geht es in diesem Datensatz?" not "Beschreibung (dct:description)"

**Rationale:** Most admins don't know DCAT-AP. The form should be self-documenting with examples. Technical details remain visible for reference but don't obstruct the primary user flow.

---

## Important Context

### Version & Feature Matrix

The plugin tracks features by version in `CHANGELOG.md`. Key versions:
- **v1.0** — MVP (CPT, Carbon Fields form, REST `/catalog` + `/datasets/<id>`)
- **v1.3** — Quality indicators (ampellogik, scoring)
- **v1.4** — Shortcode download card
- **v1.5** — Demo dataset on activation
- **v1.6** — Settings page
- **v1.7** — Extended DCAT-AP Tab 4 (landingPage, accrualPeriodicity, spatial, temporal, contactPoint)
- **v1.8** — Native wp.media upload widget in sidebar
- **v1.9** — Delta-Harvesting endpoint (`/delta?since=<ISO8601>` for incremental harvesting), comprehensive CLAUDE.md
- **v2.0.0** — **Phase 1+2 UX improvements**: All 19 form field labels rewritten with user-friendly questions + practical examples; WP-CLI commands for batch operations
- **v2.1.0** — Per-distribution license, CESSDA topic classification, 4-level quality scoring, external config files (`config/licenses.txt`, `config/dct-format-list.php`, `config/dcat-ap-fields.php`), composite file size widget, shortcode overhaul (keywords + metadata download), plugin rebrand to nozilla
- **v2.1.1** — Bugfix: remove invalid `class` attribute from CF5 input (use `[data-odw-backing]` CSS selector)

### Constants (Defined in `open-data-wizard.php`)

```php
define( 'ODW_VERSION', '2.1.0' );           // Current version
define( 'ODW_PLUGIN_DIR', dirname( __FILE__ ) . '/' );  // /path/to/plugin/
define( 'ODW_PLUGIN_URL', plugins_url( '', __FILE__ ) ); // https://site.com/wp-content/plugins/open-data-wizard/
define( 'ODW_PLUGIN_FILE', __FILE__ );      // /path/to/plugin/open-data-wizard.php
```

These are used throughout for asset loading (`wp_enqueue_script( ..., ODW_PLUGIN_URL . 'assets/...' )`).

### REST API Namespace

All custom endpoints use the `/wp-json/datenatlas/v1/` namespace (not `/wp-json/wp/v2/...`).

This allows **independent versioning** from WordPress REST API and keeps plugin routes in a custom namespace.

---

## Testing

### Unit Test Structure

Tests use **WP_Mock** (mocks WordPress functions) + **PHPUnit**. No database needed.

Each test class loads its target class once and stubs all WordPress functions:

```php
protected function setUp(): void {
    \WP_Mock::setUp();
}

protected function tearDown(): void {
    \WP_Mock::tearDown();
}

private function load_class(): void {
    if ( ! class_exists( 'ODW_Fields' ) ) {
        \WP_Mock::userFunction( 'apply_filters' )->andReturnArg( 1 );
        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );
        require_once ODW_PLUGIN_DIR . 'includes/class-fields.php';
    }
}
```

### Running Tests

```bash
composer test                          # All 90 tests
composer test -- tests/test-fields.php # Single file
composer test -- --filter testMethodName  # Single test
composer test -- --verbose              # Show test names
```

**Test files:**
- `test-fields.php` — `ODW_Fields` static methods (license labels, format MIME types, required fields)
- `test-fields-extended.php` — JSON-LD builder (`odw_build_dataset_jsonld()`)
- `test-quality.php` — `ODW_Quality` scoring and caching (4 levels)
- `test-settings.php` — `ODW_Settings` get/filter methods
- `test-shortcode.php` — `ODW_Shortcode` rendering and utilities
- `test-rest-delta.php` — Delta endpoint validation, caching, tombstones
- `test-cli.php` — WP-CLI commands

---

## Git Workflow & Commits

### Branch Naming

All development happens on branches starting with `claude/`:
```
claude/feature-name-<SESSION_ID>
```

The session ID (last part) is **required** for push to succeed.

### Commit Message Format

Include a reference at the end:
```
git commit -m "Add feature X

Description with details.

https://claude.ai/code/session_<SESSION_ID>"
```

This links commits back to the Claude Code session for traceability.

### Before Pushing

Always run:
```bash
composer phpcs        # Must be 0 violations
composer test         # Must pass all tests
```

CI will re-run these checks; don't waste CI time on violations.

---

## Common Development Tasks

### Adding a New DCAT-AP Field

1. **Define in Carbon Fields** (`class-fields.php`, appropriate tab):
   ```php
   Field::make( 'text', 'odw_my_field', __( 'User-friendly question here?', 'open-data-wizard' ) )
       ->set_help_text( __( 'ORIGINAL TECHNICAL LABEL (dcat:property)', 'open-data-wizard' ) . "\n\n" . __( 'Example: concrete example text here', 'open-data-wizard' ) ),
   ```
   
   **Important (v2.0.0+):** Field labels use **user-friendly questions** instead of technical DCAT-AP terms. The original label and DCAT-AP term go in the help text. Format: `ORIGINAL LABEL (dcat:term)` + newlines + `Example: practical example`

2. **Add to JSON-LD builder** (`odw_build_dataset_jsonld()` in same file):
   ```php
   $my_field = (string) carbon_get_post_meta( $post_id, 'odw_my_field' );
   if ( ! empty( $my_field ) ) {
       $dataset['dcat:myProperty'] = $my_field;
   }
   ```

3. **Update validation** (`class-validation.php`) if required:
   ```php
   // Add to ODW_Fields::get_required_fields()
   // Use the user-friendly label from the field definition, not the technical one
   ```

4. **Add tests** (`test-fields-extended.php`):
   ```php
   public function test_build_includes_my_field(): void {
       $this->load_fields();
       // Mock setup + assertions
   }
   ```

### Modifying the Admin List View

Edit `class-admin.php`:
- **Columns**: `add_columns()` + `render_column()`
- **Sortable columns**: `sortable_columns()` + `handle_meta_orderby()` (for meta-based sorting via `pre_get_posts`)
- **Filters/Dropdowns**: `add_filters()` method (uses `parse_request_args()` from `class-admin.php` to read `$_GET`)

### Adding a REST API Filter

All REST endpoints support:
- `page` / `per_page` — pagination
- `format` — `json` or `jsonld` (controls Content-Type header)
- Custom filters like `theme`, `license` (added as `$meta_query` in `get_catalog()`)

To add a new filter to `/catalog`:
1. Add parameter definition in `register_routes()`
2. Build `$meta_query` clause if filter is a meta field
3. Pass to `WP_Query` constructor
4. Update transient cache key to include filter in MD5

### Adding a WP-CLI Command

WP-CLI commands are registered in `class-cli.php` (only if WP-CLI is defined).

Pattern:
```php
public static function my_command( array $args, array $assoc_args ): void {
    // $args: positional arguments (e.g. 'filter-value')
    // $assoc_args: named arguments (e.g. --all, --format=json)
    
    \WP_CLI::success( 'Operation completed.' );
    // or: \WP_CLI::error( 'Something went wrong.' );
}
```

To register:
1. Add method to `ODW_CLI` class
2. In `init()`, call: `\WP_CLI::add_command( 'open-data-wizard subcommand', array( self::class, 'my_command' ) );`
3. Add docblock with `## OPTIONS`, `## EXAMPLES` sections (WP-CLI standard)
4. Add tests in `tests/test-cli.php` (stub WP_CLI classes at file-level if needed)
5. Update README.md § "WP-CLI Befehle"

---

## Debugging Tips

### Transient Cache Issues

If REST API seems outdated, check:
```php
// Clear all caches
delete_transient( 'odw_catalog_...' );
delete_transient( 'odw_delta_...' );
delete_transient( 'odw_dataset_...' );

// Or programmatically
do_action( 'odw_clear_caches' ); // If you add this action
```

Cache is **automatically invalidated** on `save_post_odw_dataset` and `trashed_post`.

### JSON-LD Output Validation

Use https://validator.schema.org/ to test JSON-LD responses.

Common issues:
- Missing `@context` — check REST endpoint response wrapping in `get_catalog()` / `get_dataset()`
- Invalid URLs in `accessURL` — should be stripped by `esc_url_raw()` in JSON builder
- Missing required fields — check `odw_build_dataset_jsonld()` for null checks

### PHPCS False Positives

The project uses `phpcs.xml` to exclude noisy sniffs:
- `WordPress.Files.FileName` — tests have different naming convention
- `Generic.Files.OneObjectStructurePerFile` — tests have stub classes in one file
- `WordPress.DB.SlowDBQuery` — meta queries are intentional here
- `WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize` — used only for cache keys

If you get unexpected violations, check `phpcs.xml` before adding ignores.

### PHPStan False Positives

Many errors are false positives from **missing Carbon Fields stubs**. Ignore them unless they're about your new code.

Known false positives:
- `Function carbon_get_post_meta not found` — CF function not in stubs
- `Method XYZ not found` on Carbon_Fields classes — incomplete stubs
- `Constant ODW_PLUGIN_DIR not found` — dynamic constant, defined at runtime

---

## Extensibility (Filters & Hooks)

The plugin provides **WordPress filters** for extension by other plugins:

```php
// Add custom license options
add_filter( 'odw_license_options', function( $options ) {
    $options['https://custom.license'] = 'Custom License 1.0';
    return $options;
});

// Modify JSON-LD before output
add_filter( 'odw_dataset_jsonld', function( $jsonld, $post_id ) {
    $jsonld['custom:field'] = 'value';
    return $jsonld;
}, 10, 2 );

// Change catalog title in REST response
add_filter( 'odw_catalog_title', function( $title ) {
    return 'My Custom Catalog Title';
});
```

See `README.md` § "Erweiterbarkeit" for the full list of available hooks.

---

## Performance Considerations

1. **Never call `odw_build_dataset_jsonld()` in loops** without caching — it queries post meta multiple times
2. **Transient caching reduces DB load** — 5 min default TTL is good for most sites (configurable in settings)
3. **WP_Query with `meta_query` is slow** — But unavoidable for filtering by theme/license; transients help
4. **File size computation** (`filesize()`) is slow — That's why we pre-compute and store in `_odw_file_size`

---

## Security Notes

- All `$_GET` / `$_POST` access is sanitized with `sanitize_text_field()` + `absint()` where appropriate
- URL validation: `access_url` is run through `esc_url_raw()` before output (strips `javascript:`, `data:`)
- Nonce checks: Upload handler and settings updates use `wp_verify_nonce()`
- Capability checks: All post modifications require `manage_open_data` cap (or `edit_posts` for standard WP operations)

---

## Resources

- [DCAT-AP 3.0 Spec](https://data.europa.eu/api/hub/store/dataset/dcat-ap)
- [JSON-LD Documentation](https://json-ld.org/)
- [Carbon Fields](https://carbonfields.net/)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
- Project README: `README.md`
- Changelog: `CHANGELOG.md`
