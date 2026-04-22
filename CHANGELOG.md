# Changelog

Alle nennenswerten Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).
Versionierung folgt [Semantic Versioning](https://semver.org/).

---

## [1.9.0] — 2026-04-22

### Hinzugefügt
- **Delta-Harvesting Endpoint** (`GET /wp-json/datenatlas/v1/delta?since=<ISO8601>`):
  - Inkrementelles Harvesting für externe Datenportale (z.B. Civora, Piveau)
  - Liefert nur Datasets, die nach einem Zeitstempel geändert wurden
  - Tombstone-Einträge für gelöschte/verschobene Datasets (`odw:removed` Array)
  - ISO 8601 Zeitformat-Unterstützung (YYYY-MM-DD, YYYY-MM-DDTHH:MM:SS, ISO-offsets)
  - Response-Header: `X-ODW-Delta-Since`, `X-ODW-Generated-At` (für nächsten `since` Wert)
  - 13 neue PHPUnit-Tests für Validierung, Caching, Tombstones
- **CLAUDE.md** — Umfassender Entwicklerleitfaden für zukünftige Claude Code Instanzen:
  - Architektur-Übersicht, Klassen-Hierarchie
  - Wichtigste Patterns (Capabilities, JSON-LD, Caching, Validierung, Companion Functions)
  - Debugging-Tipps, Testing-Anleitung, Security-Noten
  - Erweiterbarkeit via Filters und Hooks

### Geändert
- **WordPress Coding Standards** vollständig implementiert (PHPCS Level):
  - 178 Violations → 0 Violations (alle Docblocks, @param/@return Tags, Kommentar-Zeichensetzung)
  - `phpcs.xml` Konfiguration zur Ausgrenzung praktischer Sniffs (Carbon Fields, Tests)
  - Yoda Conditions, Short Ternaries, WordPress Globals-Konflikte behoben

### Sicherheit
- **`access_url` Sanitisierung**: Alle Distribution-URLs werden in der JSON-LD Ausgabe durch `esc_url_raw()` geführt (strippt `javascript:`, `data:` etc. vor Ausgabe)
- **Capability-basierte Zugriffssteuerung**: CPT `capability_type` auf `odw_dataset` mit explizitem Mapping aller Schreib-Operationen (create, edit, delete, publish) auf `manage_open_data` Capability; effektiv: nur Admins/Editoren können Datasets anlegen

### Behoben
- PHPStan 2.x Konfiguration (extensions zu includes migriert)
- Nonce-Verification Warnings in class-validation.php konsolidiert
- Dokumentation in README.md mit Delta-Endpoint erweitert

---

## [1.8.0] — 2026-04-21

### Hinzugefügt
- **Native wp.media Upload-Widget** als Sidebar-Meta-Box auf dem Datensatz-Edit-Screen:
  - Button „Datei auswählen / hochladen" öffnet den nativen WordPress Media Library Frame
  - Dateivorschau zeigt den Dateinamen (oder „Keine Datei ausgewählt") mit Dokumenten-Icon
  - „Entfernen"-Button löscht die Verknüpfung und leert alle abhängigen Meta-Felder
- **Automatische Meta-Berechnung beim Speichern**: Nach jeder Dateiauswahl werden `_odw_file_size` (Bytes als Integer) und `_odw_file_format` (z.B. „CSV") direkt aus der Mediathek-Datei ausgelesen und gespeichert — kein Runtime-`filesize()`-Aufruf mehr beim Shortcode-Rendering nötig
- **`assets/js/odw-file-upload.js`** — jQuery + wp.media Integration; UI-Zustand wird serverseitig via `wp_localize_script` initialisiert; Media-Frame-Instanz wird wiederverwendet

### Geändert
- **Shortcode** `[odw_dataset]` liest `_odw_file_size` und `_odw_file_format` jetzt aus vorberechnetem Post-Meta (mit Fallback auf Runtime-Berechnung für ältere Datensätze)
- Carbon Fields `Field::make('file', 'odw_file_id')` in Tab 3 entfernt — ersetzt durch die native Meta-Box-Implementierung in der Sidebar

### Sicherheit
- `save_file_attachment()` prüft `wp_verify_nonce('odw_save_file_attachment')` und `current_user_can('edit_post')` vor jeder Speicherung

---

## [1.7.0] — 2026-04-21

### Hinzugefügt
- **Tab 4 — Erweiterte Angaben** im Datensatz-Formular mit 8 neuen DCAT-AP 3.0 Feldern:
  - `dcat:landingPage` — URL der Projektwebsite
  - `dct:accrualPeriodicity` — Aktualisierungsfrequenz (EU Publications Office Vokabular: täglich bis zweijährlich)
  - `dct:spatial` — Geographische Abdeckung (Freitext oder URI, z.B. GeoNames)
  - `dct:temporal` — Zeitlicher Bezug mit Start- und Enddatum (`dcat:startDate`, `dcat:endDate`)
  - `dcat:contactPoint` — Kontaktpunkt mit Name, E-Mail (`mailto:`-Prefix) und Website (`vcard:Organization`)
- **`vcard` und `skos` Namespaces** im JSON-LD `@context` der REST API
- **`ODW_Fields::get_periodicity_options()`** — Kontrolliertes Vokabular für Aktualisierungsfrequenzen

### Geändert
- Vorschau-Tab umbenannt von „4" auf „5" (Erweiterte Angaben ist jetzt Tab 4)
- Help Tab Beschreibung aktualisiert

---

## [1.6.0] — 2026-04-21

### Hinzugefügt
- **Settings-Seite** unter *Datensätze → Einstellungen* mit vier Sektionen:
  - **Katalog**: Katalog-Titel (überschreibt den Standardwert im REST API), Herausgebende Organisation
  - **Standardwerte**: Standard-Lizenz und Standard-Sprache — werden bei neuen Datensätzen automatisch vorausgefüllt (via `set_default_value()` in Carbon Fields)
  - **REST API**: Cache-Laufzeit konfigurierbar (60–86400 s, Standard 300 s)
  - **Deinstallation**: Checkbox für opt-in Datenlöschung (ersetzt separate Option)
- **„Alle Qualitätsscores neu berechnen"**-Button auf der Settings-Seite (nonce-gesichert, zeigt Anzahl aktualisierter Datensätze)
- **`ODW_Settings::get()`** — zentrale API für Einstellungszugriff in anderen Klassen
- **`ODW_Rest_API::delete_catalog_transients_public()`** — öffentlicher Alias für Cache-Invalidierung nach Einstellungsänderungen

### Geändert
- `uninstall.php`: liest jetzt `odw_settings[delete_on_uninstall]` statt separater Option; löscht auch `odw_settings`, `odw_demo_post_id`, `odw_show_welcome`

---

## [1.5.0] — 2026-04-21

### Hinzugefügt
- **Demo-Datensatz bei Installation**: Beim ersten Admin-Aufruf nach der Aktivierung wird automatisch ein vollständig befüllter Demo-Datensatz (`odw_dataset`) erstellt — inklusive Beispiel-CSV aus der Mediathek (`assets/sample/beispiel-datensatz.csv`), allen Meta-Feldern, CF-Distribution und berechnetem Qualitätsscore
- **Willkommens-Notice** (einmalig, dismissible): Zeigt nach der Aktivierung den fertigen Shortcode (`[odw_dataset id="…"]`) zum direkten Copy-Paste, Links zum Demo-Datensatz und zur Übersicht sowie einen „Hinweis ausblenden"-Link (Nonce-gesichert)
- **`includes/class-setup.php`**: Kapselt die gesamte Installations-Logik; `on_activation()` setzt nur eine Option (kein CF-Zugriff), `maybe_create_demo()` läuft auf `admin_init` wenn Carbon Fields vollständig initialisiert ist

---

## [1.4.0] — 2026-04-21

### Hinzugefügt
- **Shortcode `[odw_dataset id="…"]`** — gibt eine Download-Card im Frontend aus mit Titel, Thema-Badge, Lizenz, DCAT-Qualität, Dateigröße/Format und Download-Button
- **Download-Datei über Mediathek** (`_odw_file_id`): Neues File-Feld in Tab 3 verknüpft eine Datei aus der WordPress-Mediathek; Dateigröße und Format werden automatisch ermittelt
- **Shortcode-Spalte in der Admin-Übersicht**: Zeigt `[odw_dataset id="123"]` als klickbares, schreibgeschütztes Textfeld (Klick = Markierung für Copy-Paste)
- **`assets/css/frontend.css`**: Strukturelles Layout der Download-Card (Flexbox/Grid, Abstände, Rahmen) ohne feste Farben — erbt vollständig vom aktiven Theme

---

## [1.3.0] — 2026-04-21

### Hinzugefügt
- **Qualitätsindikatoren / Ampellogik** (`includes/class-quality.php`): Automatische Bewertung der Metadaten-Vollständigkeit (0–100 Punkte, 3 Levels: Gut/Mittel/Verbesserungsbedarf)
  - 10 Indikatoren in 3 Gruppen: Pflichtfelder (55 Pkt.), Empfohlene Felder (40 Pkt.), Optionale Angaben (5 Pkt.)
  - Automatische Neuberechnung nach jedem Speichern (`save_post_odw_dataset`, Priorität 30)
  - Persistenz in 4 Meta-Keys: `_odw_quality_score`, `_odw_quality_level`, `_odw_quality_indicators`, `_odw_quality_calculated_at`
- **Qualitätsspalte in der Admin-Listenansicht**: Farbiger Badge (● 85) mit Tooltip; sortierbar
- **Qualitätsbericht-Meta-Box** auf dem Edit-Screen: Fortschrittsbalken, Ampel-Badge, gruppierte Indikator-Tabelle (✓/✗) mit Punkten, Zeitstempel der letzten Berechnung
- **`odw:qualityScore` im JSON-LD**: Qualitätsdaten werden via `odw_dataset_jsonld` Filter an den REST-API Output angehängt (`odw:score`, `odw:maxScore`, `odw:level`, `odw:calculatedAt`)
- **`odw:` JSON-LD Namespace** (`https://github.com/daimpad/OpenDataWizard/ns#`) in `JSONLD_CONTEXT`
- **CSS Qualitäts-Styles**: `--odw-color-quality-*` Custom Properties; `.odw-quality-badge`, `.odw-quality-gauge`, `.odw-quality-table` Komponenten

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
