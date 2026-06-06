# Changelog — Open Data Wizard

Alle bedeutsamen Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

**Last Updated:** May 27, 2026
