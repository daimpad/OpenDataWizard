# Changelog — Open Data Wizard

Alle bedeutsamen Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.1.5] — 2026-06-27

Wartungs- und Sicherheits-Release. Behebt einen kritischen Batch-Import-Bug aus 2.1.4 und härtet Datei-Upload, REST-API, Qualitäts-Scoring und JSON-LD-Ausgabe.

### 🐛 Fixed

#### Batch-Import (kritisch)
- **Upload komplett repariert:** `wp_tempnam()` erzeugt stets `.tmp`-Dateien, wodurch die Formaterkennung jeden Upload mit „Format nicht unterstützt" abwies. `parse_file()` nutzt jetzt den Original-Dateinamen zur Format-Erkennung.
- **Excel-CSVs:** UTF-8-BOM im Header wird entfernt — Excel-exportierte CSVs werden nicht mehr fälschlich mit „Pflichtfeld title fehlt" abgewiesen.
- **Leere Zeilen:** Vollständig leere CSV-Zeilen (z. B. abschließender Zeilenumbruch) werden übersprungen statt als Spaltenzahl-Fehler gemeldet.
- **Einzelnes JSON-Objekt:** Erkennung via `array_is_list()` — ein Objekt ohne `title` wird nicht mehr feldweise iteriert.

#### Demo-Datensatz
- Distribution wurde unter dem in 2.1.4 entfernten Repeater-Key `odw_distributions` gespeichert. Jetzt korrekt über `_odw_access_url` / `_odw_format` — Demo-Datensatz erscheint vollständig in JSON-LD und Qualitäts-Scoring.

#### Einstellungen & CLI
- **Cache-TTL:** Die Einstellung „Cache-Laufzeit" wurde von der REST-API ignoriert (fest 300 s) und wird nun tatsächlich verwendet.
- **`wp open-data-wizard cache clear`:** Löschte aufgrund fehlerhafter Präfix-Behandlung keine Transients und meldete eine zu hohe Anzahl — korrigiert.

#### Weitere Korrekturen
- **Qualitäts-Score:** Auf 0–100 begrenzt; die „Ausreichend"-Schwelle wird dynamisch aus der Feld-Konfiguration berechnet statt fest verdrahtet.
- **Delta-Endpoint:** Ungültige Überlauf-Daten (z. B. `2024-13-45`) werden im `since`-Parameter abgewiesen.
- **Download-Card:** Negative Byte-Werte in `format_bytes()` werden abgefangen.

### 🔒 Security
- **Stored XSS in der Batch-Import-Vorschau behoben:** Roh-Zellinhalte aus hochgeladenen Dateien werden jetzt clientseitig escaped statt direkt ins DOM geschrieben.
- **CSV-/Formel-Injection:** Importierte Zellen, die mit `=`, `+`, `@`, `-` (oder Tab/CR) beginnen, werden neutralisiert.
- **JSON-LD `@id`-Felder:** Nutzergesteuerte URIs (landingPage, contactURL, language, theme, license u. a.) laufen durch `esc_url_raw()` und blockieren `javascript:`/`data:`-Schemata.
- **Datei-Upload:** Validierung über echte Datei-Endung statt browser-gemeldetem MIME-Typ; `is_uploaded_file()`-Prüfung vor `move_uploaded_file()`; korrektes Unslashing der `$_FILES`-Werte.
- **Import-Limit:** Maximal 2.000 Datensätze pro Import (Speicher-/Timeout-Schutz).

### 🧪 Tests & Qualität
- **92 PHPUnit-Tests** (zuvor 90), PHPStan Level 6, PHPCS sauber (Exit 0)
- `manage_open_data` als bekannte Custom-Capability in `config/phpcs.xml` registriert; legitime lokale Datei-Operationen des CSV-Parsers gezielt ausgenommen

---

## [2.1.4] — 2026-05-27

### 🎉 Added

#### Batch-Import Feature
- **New admin page:** "Datensätze → Batch-Import" für Massenimport von Datensätzen
- **CSV & JSON support:** Automatische Format-Erkennung und Parsing
- **Preview mode:** Zeige gültige und ungültige Zeilen vor dem Import
- **Validation:** Umfassende Validierung mit detaillierten Error-Messages pro Zeile
- **Batch selection:** Wähle einzelne oder alle Datensätze zum Importieren
- **License mapping:** Short codes (cc-by, cc0) werden automatisch zu URIs gemappt
- **Sample files:** CSV und JSON Beispiel-Dateien im Verzeichnis `/samples`
- **UI Polish:** Icons, Animationen, Fortschrittsbalken, bessere Error-Messaging

#### Documentation
- **API.md:** Vollständige REST API Dokumentation (600+ Zeilen)
- **SECURITY.md:** Umfassender Security Audit (Status: ✅ SECURE)
- **E2E_TESTING.md:** Playwright E2E Testing Guide
- **GitHub Issue Templates:** Bug, Feature, Security Templates
- **Enhanced CLAUDE.md:** Deutsche Übersetzungen & bessere Struktur

#### Testing Infrastructure
- **Playwright E2E Setup:** Multi-Browser Testing (Chrome, Firefox, Safari)
- **Admin Workflow Tests:** 11 UI Tests für das Admin-Interface
- **API Tests:** 15 Tests für REST Endpoints
- **package.json:** npm scripts für test:e2e, test:e2e:ui, test:e2e:debug

### ✨ Improvements
- **UI Polish:** Batch-Import mit Icons, Animationen, besseren Farben
- **README.md:** Erweitert mit Batch-Import Sektion
- **Progress Tracking:** Animated spinner & progress bar
- **Error Reporting:** Detaillierte Error-Messages mit Kontext
- **Documentation:** Umfassende API & Security Dokumentation

### 🔒 Security
- Full Security Audit durchgeführt
- OWASP Top 10 Compliance verified
- Nonce verification & capability checks in Batch-Import
- Input sanitization per field

---

## [2.1.3] — 2026-05-20

### Fixed
- Tab 3 distribution fields auto-show on page load
- File size composite widget initialization

---

## [2.1.2] — 2026-05-15

### Fixed
- Tab switching logic with complex fields
- Validation error display on publish

---

## [2.1.1] — 2026-05-10

### Changed
- Removed auto-click on "Add Entry" button
- Restored manual "Add Entry" workflow

---

## [2.1.0] — 2026-05-01

### 🎉 Added

#### Major Feature: Simplified Distribution Model
- Removed complex repeater field with multiple distributions
- Added single, flat distribution fields
- Better UX for typical use case

#### Admin UI Improvements
- New "Einstieg" (Introduction) page
- Welcome content & workflow overview
- Form spacing fixes

### ✨ Improvements
- Validation logic updated
- Quality scoring adjusted
- JSON-LD builder simplified

### ✅ Tests
- 90 PHPUnit tests passing
- PHPCS compliant
- PHPStan Level 6

---

## [2.0.0] — 2026-04-01

### 🎉 Initial Release
- DCAT-AP 3.0 compliant metadata form
- 5-tab wizard interface
- JSON-LD generation
- REST API endpoints
- Quality scoring system
- WordPress integration

---

**Last Updated:** June 27, 2026
