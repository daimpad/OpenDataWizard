# Changelog

Alle nennenswerten Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).
Versionierung folgt [Semantic Versioning](https://semver.org/).

---

## [2.1.1] — 2026-05-06

### Behoben
- **Carbon Fields Attribut-Fehler**: `set_attribute('class', ...)` ist in CF5 nicht erlaubt (nur `data-*` und eine feste Whitelist). Das `byte_size` Backing-Feld verwendete fälschlicherweise ein `class`-Attribut — ersetzt durch den bereits vorhandenen `[data-odw-backing]` Selektor im CSS.

---

## [2.1.0] — 2026-05-06

### Hinzugefügt
- **Externe Konfigurationsdateien** für einfachere Wartung ohne PHP-Kenntnisse:
  - `config/licenses.txt` — Lizenzdatei im Format `URI | Label`; enthält CC0, CC-BY, CC-BY-SA, ODbL u.a.
  - `config/dct-format-list.php` — 18 Dateiformate mit MIME-Type und EU-URI (CSV, JSON, GeoJSON, RDF usw.)
  - `config/dcat-ap-fields.php` — Single Source of Truth für alle Felddefinitionen (Schlüssel, DCAT-AP Prädikat, Punkte, Pflicht-Flag)
- **Lizenz pro Distribution**: Lizenz ist jetzt Pflichtfeld für jede Distribution (DCAT-AP-konform). Entfernt vom Dataset-Level.
  - Option „Sonstige" öffnet ein Freitextfeld mit Auto-Suggest aus `config/licenses.txt`
  - Deutschland-Lizenz entfernt
- **CESSDA-Themenklassifikation**: Neues Auto-Suggest-Feld in Tab 2 mit 95 deutschen Konzepten aus der CESSDA Topic Classification 4.2.3 (SKOS/RDF, 24h Transient-Cache)
- **Vier Qualitätsstufen** (vorher drei):
  - Perfekt (100 Punkte) — alle Felder ausgefüllt
  - Gut (56–99 Punkte) — über Mindestanforderung
  - Ausreichend (55 Punkte) — genau alle Pflichtfelder erfüllt
  - Verbesserungsbedarf (< 55 Punkte) — Pflichtfelder fehlen
- **Wizard-Tabs umstrukturiert**:
  - Tab 1: Grundlegende Informationen (Herausgeber, Beschreibung, Thema)
  - Tab 2: Inhaltliche Angaben (Sprache, Schlagworte, Datum, CESSDA)
  - Tab 3: Datenbereitstellung (Distributions mit Lizenz als Pflichtfeld)
  - Tab 4: Erweiterte Angaben (unverändert)
  - Tab 5: Vorschau (unverändert)
- **Composite Dateigröße-Widget** in jeder Distribution: Zahleneingabe + Einheiten-Dropdown (KB/MB/GB); JavaScript berechnet Bytes für das versteckte `byte_size` Backing-Feld; Anzeige wird beim Laden aus gespeicherten Bytes wiederhergestellt
- **`assets/js/odw-admin-fields.js`** — neues Script für License Auto-Suggest, CESSDA Auto-Suggest und Dateigröße-Widget (kein jQuery, MutationObserver für dynamische CF-Gruppen)
- **Shortcode-Widget überarbeitet**: Qualitätsanzeige entfernt; Schlagwörter als Tag-Pillen; Metadaten-Download-Button (JSON-LD REST-Endpoint)

### Geändert
- Plugin-Author: „Datenatlas Zivilgesellschaft" → **nozilla** (GitHub: https://github.com/daimpad/OpenDataWizard)
- Alle Civora-, Piveau- und Datenatlas-Markenreferenzen aus Kommentaren und Dokumentation entfernt; funktionaler REST-Namespace `datenatlas/v1` bleibt erhalten
- `ODW_Fields::get_required_fields()` — Lizenz nicht mehr als skalares Pflichtfeld (jetzt per Distribution via `all_distributions_have_license()`)
- `ODW_Quality::check_indicator('license')` — prüft jetzt Distributions statt `_odw_license` Meta
- Demo-Publisher-Name: „Datenatlas Zivilgesellschaft e.V." → „Musterorganisation e.V."
- Stale Planungsdokumente (`docs/WEBHOOK_IMPLEMENTATION.md`, `docs/I18N_IMPLEMENTATION.md`) entfernt

### Behoben
- Lizenz-Anzeige in der Admin-Listenansicht liest jetzt aus erster Distribution statt `_odw_license`

---

## [2.0.0] — 2026-04-29

### Hinzugefügt
- **Benutzerfreundlicher Wizard (Phase 1+2 UX-Verbesserung)**:
  - Alle 19 Formularfelder mit benutzergerechten Fragen statt technischen DCAT-AP-Begriffen
  - Hilfetexte mit Original-Label, DCAT-AP Bezeichnung und praktischen Beispielen für alle Felder
  - Validierungsmeldungen verwenden verständliche Labels
- **WP-CLI Befehle** (`includes/class-cli.php`):
  - `wp open-data-wizard quality recalculate` — Qualitätsscores neu berechnen
  - `wp open-data-wizard quality recalculate --all` — inkl. Draft und Trash
  - `wp open-data-wizard cache clear` — REST API Transient-Caches löschen

---

## [1.9.0] — 2026-04-22

### Hinzugefügt
- **Delta-Harvesting Endpoint** (`GET /wp-json/datenatlas/v1/delta?since=<ISO8601>`):
  - Inkrementelles Harvesting für externe Datenportale
  - Tombstone-Einträge für gelöschte/verschobene Datasets (`odw:removed` Array)
  - ISO 8601 Zeitformat-Unterstützung
  - Response-Header: `X-ODW-Delta-Since`, `X-ODW-Generated-At`
  - 13 neue PHPUnit-Tests
- **CLAUDE.md** — Umfassender Entwicklerleitfaden

### Geändert
- WordPress Coding Standards vollständig implementiert (178 → 0 Violations)

### Sicherheit
- `access_url` Sanitisierung: alle Distribution-URLs durch `esc_url_raw()` (strippt `javascript:`, `data:`)
- Capability-basierte Zugriffssteuerung: nur `manage_open_data` kann Datasets anlegen

---

## [1.8.0] — 2026-04-21

### Hinzugefügt
- **Native wp.media Upload-Widget** als Sidebar-Meta-Box
- **Automatische Meta-Berechnung**: `_odw_file_size` und `_odw_file_format` werden beim Speichern berechnet

### Geändert
- Shortcode liest `_odw_file_size` und `_odw_file_format` aus vorberechnetem Post-Meta

---

## [1.7.0] — 2026-04-21

### Hinzugefügt
- **Tab 4 — Erweiterte Angaben**: `dcat:landingPage`, `dct:accrualPeriodicity`, `dct:spatial`, `dct:temporal`, `dcat:contactPoint`
- `vcard` und `skos` Namespaces im JSON-LD `@context`

---

## [1.6.0] — 2026-04-21

### Hinzugefügt
- **Settings-Seite** mit Katalog, Standardwerte, REST API und Deinstallation
- „Alle Qualitätsscores neu berechnen"-Button

---

## [1.5.0] — 2026-04-21

### Hinzugefügt
- **Demo-Datensatz bei Installation** mit Beispiel-CSV und Willkommens-Notice

---

## [1.4.0] — 2026-04-21

### Hinzugefügt
- **Shortcode `[odw_dataset id="…"]`** — Download-Card im Frontend
- Shortcode-Spalte in der Admin-Übersicht

---

## [1.3.0] — 2026-04-21

### Hinzugefügt
- **Qualitätsindikatoren** (`includes/class-quality.php`): 0–100 Punkte, Ampellogik
- Qualitätsspalte und Qualitätsbericht-Meta-Box im Admin
- `odw:qualityScore` im JSON-LD

---

## [1.2.0] — 2026-04-21

### Hinzugefügt
- `?format=` Parameter (jsonld/json) an REST-Endpoints
- PHPStan Level 6, WPCS, PHPUnit Test-Setup
- GitHub Actions CI (PHP 8.1/8.2/8.3)

---

## [1.1.0] — 2026-04-21

### Hinzugefügt
- Activation/Deactivation Hooks, `uninstall.php`
- REST API Transient-Cache (5 min TTL)
- Capability `manage_open_data`
- Filter-Hooks: `odw_license_options`, `odw_theme_options`, `odw_dataset_jsonld`, `odw_catalog_title`
- Admin Help Tabs

---

## [1.0.0] — 2026-03-02

### Hinzugefügt
- Custom Post Type `odw_dataset`
- Carbon Fields Formular mit 4 Tabs
- REST API: `/catalog` und `/datasets/<id>`
- Admin-Listenansicht mit Spalten und Filtern
- Pflichtfeldvalidierung
- DCAT-AP 3.0 JSON-LD Ausgabe
