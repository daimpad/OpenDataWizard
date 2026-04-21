# Changelog

Alle nennenswerten Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).
Versionierung folgt [Semantic Versioning](https://semver.org/).

---

## [1.2.0] — 2026-04-21

### Hinzugefügt
- **?format= Parameter** an beiden REST-Endpoints (`/catalog`, `/datasets/<id>`): `jsonld` (Standard, `application/ld+json`) oder `json` (`application/json`) — Grundlage für spätere Content-Negotiation
- **PHPStan Level 6** Konfiguration (`phpstan.neon`)
- **WordPress Coding Standards** via WPCS (`phpcs`/`phpcbf` Scripts in composer.json)
- **PHPUnit** Test-Setup (`phpunit.xml`, `tests/bootstrap.php`, erste Test-Suite für `ODW_Fields`)
- **GitHub Actions CI** Workflow (`.github/workflows/ci.yml`): PHPCS, PHPStan, PHPUnit auf PHP 8.1/8.2/8.3
- **`ODW_Fields::get_required_fields()`** — zentrale Pflichtfeld-Registry als Single Source of Truth

### Geändert
- **Validierungslogik zentralisiert**: `class-validation.php` iteriert über `ODW_Fields::get_required_fields()` statt Felder doppelt zu pflegen
- **`get_field_value()`** vereinfacht: CF-Key-Parameter entfernt, meta_key reicht als Identifier
- **composer.json**: `require-dev` Sektion mit PHPStan, WPCS, PHPUnit hinzugefügt; `allow-plugins` Konfiguration ergänzt

---

## [1.1.0] — 2026-04-21

### Hinzugefügt
- **Activation Hook**: CPT registrieren, Rewrite Rules flushen, Capability `manage_open_data` vergeben
- **Deactivation Hook**: Rewrite Rules flushen
- **`uninstall.php`**: Opt-in Datenlöschung bei Deinstallation (hinter `odw_delete_data_on_uninstall` Option)
- **REST API Transient-Cache**: 5 Minuten TTL für `/catalog` und `/datasets/<id>`; Cache-Invalidierung bei `save_post_odw_dataset` und `trashed_post`; `X-ODW-Cache: HIT/MISS` Header
- **Capability `manage_open_data`**: Administrator und Editor erhalten die Capability bei Plugin-Aktivierung
- **Filter-Hooks**: `odw_license_options`, `odw_theme_options`, `odw_dataset_jsonld`, `odw_catalog_title`
- **Admin Help Tabs**: DCAT-AP Feldbeschreibungen und Harvest-Endpoint Doku auf dem Edit-Screen
- **`ODW_Fields::get_license_label()`**: Single Source of Truth für Lizenz-URI → Label Übersetzung
- **CSS Custom Properties**: `--odw-color-*` Variablen statt hard-codierter Hex-Werte

### Behoben
- **Zeitzonen-Bug**: `gmdate()` → `current_time()` für `_odw_modified` (verhinderte Datums-Abweichung um 1 Tag bei Nicht-UTC-Servern)
- **Sortierbare Spalte „Thema"**: `pre_get_posts` Hook mit `meta_key`/`meta_value` — Sortierung war vorher defekt
- **`$_GET` Sanitization**: `wp_unslash()` + `sanitize_text_field()` konsequent; `absint()` für post_id (class-admin.php, class-validation.php)
- **Byte-Size Validierung**: `is_numeric()` + `>= 0` Prüfung vor JSON-LD Ausgabe
- **Transient-TTL**: 60s → 300s für Validierungsnotices (verhindert Ablauf bei langsamen Servern)
- **sessionStorage Safety**: `try/catch` Wrapper für Private-Browsing-Modus und Quota-Überschreitung; post_id-spezifischer Key (`odw_active_tab_<id>`)
- **MutationObserver Speicherleck**: `disconnect()` via `beforeunload` Event
- **Carbon Fields Boot-Fehler**: `try/catch` um `boot()` mit hilfreicher Admin-Notice statt fatalen PHP-Fehler

---

## [1.0.0] — 2026-03-02

### Hinzugefügt
- **Custom Post Type `odw_dataset`** mit deutschen Labels und Dashicons-database Icon
- **Carbon Fields Formular** mit 4 Tabs:
  - Tab 1: Pflichtfelder (Titel, Beschreibung, Publisher, Lizenz)
  - Tab 2: Optionale Felder (Sprache, Schlagworte, Thema, Datum)
  - Tab 3: Distributionen (accessURL, Format, byteSize) — wiederholbares Complex Field
  - Tab 4: JSON-LD Vorschau (read-only)
- **REST API**:
  - `GET /wp-json/datenatlas/v1/catalog` mit Paginierung und Filtern (`?theme=`, `?license=`)
  - `GET /wp-json/datenatlas/v1/datasets/<id>`
  - Content-Type `application/ld+json`, DCAT-AP 3.0 `@context`
- **Admin-Listenansicht**: Spalten Titel, Lizenz, Thema, Status, Änderungsdatum; Status-Dropdown-Filter
- **Pflichtfeldvalidierung**: Blockiert Veröffentlichung bei fehlenden Pflichtfeldern; Admin-Notice mit Feldnamen
- **Tab-Navigation** (Vanilla JS, kein jQuery): sessionStorage-Persistenz, Keyboard-Navigation
- **Carbon Fields** v3.6 via Composer im Plugin gebündelt (kein Composer-Wissen nötig)
- **DCAT-AP 3.0 JSON-LD** Ausgabe mit allen Pflicht- und empfohlenen Feldern
- **Lizenz-Kurzaliase** im API-Filter (`?license=cc-by`, `?license=cc0` etc.)
- Automatische Aktualisierung von `dct:modified` bei jedem Speichern
