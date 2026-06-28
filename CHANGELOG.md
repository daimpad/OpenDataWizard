# Changelog — Open Data Wizard

Alle bedeutsamen Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.11.2] — 2026-06-28

### 🔧 Changed
- **Git-Updater-Kompatibilität:** Header `Primary Branch: main` ergänzt. Aktuelle Git-Updater-Versionen werten `Primary Branch` aus; ohne ihn wurde fälschlich von `master` geladen (404 beim Installations-/Update-ZIP). `GitHub Branch: main` bleibt zur Abwärtskompatibilität erhalten.

---

## [2.11.1] — 2026-06-28

Code-Review-Korrekturen an der v2.11.0-UX (Hilfe-Tooltips), gefunden bei einer Überprüfung des Codes.

### 🐛 Fixed
- **Tooltip-Selektor jetzt feldgenau (`:scope >`):** Der äußere Tab-/Container-Feldknoten konnte zuvor den Hilfetext eines verschachtelten Kindfeldes greifen und ein verirrtes ⓘ am Container erzeugen. Tooltips werden nun nur noch am tatsächlich zugehörigen Feld erzeugt.
- **React-sichere Tooltip-Erzeugung:** Der Hilfetext-Knoten (`<em class="cf-field__help">`) wird von Carbon Fields per React (`dangerouslySetInnerHTML`) verwaltet. Statt ihn ins Popup zu verschieben (Risiko von doppeltem/wiederhergestelltem Inline-Hilfetext beim Re-Render von Feldern mit bedingter Logik), wird er nun **geklont**; das Original bleibt im DOM und wird über eine an das Attribut `data-odw-tip-init` gebundene CSS-Regel ausgeblendet (überlebt React-Re-Renders).

---

## [2.11.0] — 2026-06-28

Phase 3 UX: zwei Verbesserungen der Eingabemaske (reine Progressive Enhancement, kein Datenmodell-Eingriff).

### ✨ Added
- **Hilfe-Tooltips (ⓘ-Popups):** Der Hilfetext jedes Formularfelds (inkl. des technischen DCAT-AP-Labels) wird jetzt in ein kompaktes ⓘ-Symbol neben dem Feldtitel ausgelagert. Das Popup öffnet bei Hover, Tastatur-Fokus oder Klick (touch-/tastaturfreundlich) und entrümpelt so das Formular. Ohne JavaScript bleibt der Hilfetext wie bisher inline sichtbar.
- **Live-Vorschau (Tab „Vorschau"):** Oberhalb der gespeicherten JSON-LD-Ansicht zeigt ein neues Panel eine Pflichtangaben-Checkliste (mit Fortschrittsanzeige „x von y ausgefüllt") und eine Zusammenfassungs-Karte der Kernfelder, die sich beim Tippen automatisch aktualisieren — ohne Speichern. Die Feldliste stammt aus `ODW_Fields::get_live_preview_fields()` (Single Source of Truth, in PHP getestet). Ohne JavaScript bleibt das Panel ausgeblendet; die gespeicherte JSON-LD-Ansicht bleibt der Rückfall.

### 🌍 i18n
- Neue UI-Strings in `.pot`/`.po` ergänzt und `en_US.mo` neu kompiliert.

---

## [2.10.0] — 2026-06-28

Ergebnisse eines vollständigen Korrektheits- und Sicherheits-Reviews des gesamten Plugins. Es wurden keine kritischen oder hohen Schwachstellen gefunden; die folgenden Härtungen/Korrekturen wurden umgesetzt.

### 🔒 Security / Hardening
- **REST-Endpoints cachen keine leeren Ergebnisse mehr** (`/catalog`, `/delta`): Unauthentifizierte Aufrufe mit beliebigen `theme`/`license`/`page`/`since`-Werten erzeugten bisher je einen Transient (auch bei 0 Treffern), was die `wp_options`-Tabelle aufblähen konnte. Es werden nur noch Ergebnisse mit Inhalt gecacht.

### 🐛 Fixed / Changed
- **`dcat:accessURL`** und **`vcard:hasEmail`** werden im JSON-LD jetzt als IRI-Ressourcen (`{"@id": …}`) ausgegeben — konsistent mit allen anderen URI-Feldern und DCAT-AP-konform (zuvor als Plain-String bzw. ungesäuberter `mailto:`-String). Die Kontakt-E-Mail läuft nun ebenfalls durch `odw_sanitize_jsonld_id()`.
- **Batch-Import-Vorschau:** Zeilen-Validierungsfehler werden jetzt an die Vorschau zurückgegeben, statt still verworfen zu werden — Nutzer sehen nun, wenn einzelne Zeilen beim Import übersprungen werden.

### ✅ Review-Ergebnis (geprüft, keine Änderung nötig)
- Sanitisierung/Escaping (Shortcode, Vorschau, Admin-Spalten), Nonce-/Capability-Prüfungen, Datei-Upload, Sample-Download (kein Path-Traversal/Header-Injection), CSV-Formel-Injection-Neutralisierung, MIME-/Endungsprüfung, Opt-in-Deinstallation, `odw_sanitize_jsonld_id()` gegen `javascript:`/`data:` (inkl. Obfuskierung) — alles korrekt.

---

## [2.9.0] — 2026-06-28

Phase D: mehrsprachige Literale (`@value`/`@language`) für die Kernfelder.

### 🔧 Changed
- **`dct:title`, `dct:description`, `dcat:keyword`** werden im JSON-LD jetzt als **sprachgetaggte Literale** ausgegeben (`{"@value": "…", "@language": "de"}` bzw. ein Array solcher Literale für Keywords) statt als reine Strings — die DCAT-AP-konforme Form. Die Sprache wird aus dem Sprache-Feld des Datensatzes abgeleitet (EU-URI → BCP-47), mit Rückfall auf die Standardsprache und zuletzt `de`.
- **Keine Datenmigration nötig:** Werte bleiben unverändert als Klartext gespeichert; die Sprach-Auszeichnung erfolgt erst bei der JSON-LD-Erzeugung. Formular und Eingabe sind unverändert (eine Sprache pro Feld).

> Hinweis für Harvester/Konsumenten: Die Ausgabe dieser drei Felder ändert ihre Form von Plain-String auf `{@value,@language}`. Das ist die standardkonforme Darstellung; DCAT-AP-Harvester verarbeiten sie korrekt.

---

## [2.8.1] — 2026-06-28

Bugfix aus einem Code-Review.

### 🐛 Fixed
- **Zusätzliches EU-Thema (`dcat:theme`):** Das optionale Profi-Feld speichert (wie Contributor-ID) das menschenlesbare Label aus dem Autosuggest. Beim Aufbau des JSON-LD wurde dieses Label jedoch direkt als `@id` ausgegeben (z. B. `{"@id": "Energie"}` statt der EU-URI) — ungültiger IRI. Der Wert wird nun über `odw_resolve_vocab_uri()` zur kanonischen `data-theme`-URI aufgelöst (direkt eingegebene URIs werden weiterhin durchgereicht). Regressionstest ergänzt.

---

## [2.8.0] — 2026-06-28

Profi-UX: ausklappbarer „Erweiterte Angaben für Profis"-Bereich (aus der piveau-DPI-Analyse, Essentials/Additionals-Muster).

### ✨ Added
- **Ausklappbarer Profi-Bereich in Tab 4:** Die spezialisierten Felder (DCAT-AP.de-Verantwortlichkeiten/Rechtsgrundlage, Zugriffsrechte, Zusatz-Thema, HVD) sind standardmäßig eingeklappt und über einen Umschalter einblendbar. Das hält die Eingabemaske für Einsteiger schlank, ohne den vollen DCAT-AP-/DCAT-AP.de-Umfang zu verbergen.
- Umsetzung als Progressive Enhancement (CSS/JS); ohne JavaScript bleiben alle Felder sichtbar. Der Zustand wird je Sitzung gemerkt (`sessionStorage`). Keine Änderung an Feldspeicherung oder JSON-LD.

### 🌍 i18n
- Neuer Umschalter-Text übersetzbar und in der englischen Übersetzung (`en_US`) enthalten.

---

## [2.7.0] — 2026-06-28

Weitere gebündelte Vokabulare + Zugriffsrechte-Feld (aus der piveau-DPI-Analyse).

### ✨ Added
- **`dct:accessRights`** (Tab 4) — Zugriffs-Klassifikation des Datensatzes (Öffentlich / Eingeschränkt / Nicht öffentlich), gespeist aus dem gebündelten EU-Vokabular `access-right`.
- **Zusätzliches EU-Thema** (Tab 4, optional) — Autosuggest-Feld aus der gebündelten EU-Themenliste (`data-theme`); ergänzt die kuratierte Kategorie aus Tab 1 und wird als zusätzliches `dcat:theme` ausgegeben.
- **Gebündelte Vokabulare:** `config/vocabularies/access-right.json` und `config/vocabularies/data-theme.json` (offizielle EU-Listen) für das generische Autosuggest.

### 🔧 Notes
- Die bestehenden Auswahlfelder für Thema (Tab 1) und Sprache bleiben aus Kompatibilitätsgründen kuratierte Selects; die vollständige EU-Sprachliste (454 Einträge) wird bewusst nicht als Autosuggest aufgezwungen.

### 🌍 i18n
- Neue Oberflächentexte übersetzbar und in der englischen Übersetzung (`en_US`) enthalten.

---

## [2.6.0] — 2026-06-28

Weitere DCAT-AP.de-Felder (aus der piveau-DPI-Analyse).

### ✨ Added
- **`dcatde:politicalGeocodingURI`** (Tab 4) — URI des amtlichen Regional-/Gemeindeschlüssels (AGS/ARS).
- **`dcatde:legalBasis`** (Tab 4) — Rechtsgrundlage der Bereitstellung (Freitext-Literal).
- **`dcatde:qualityProcessURI`** (Tab 4) — URL zur Dokumentation des Qualitätssicherungs-Prozesses.

### 🌍 i18n
- Neue Oberflächentexte übersetzbar und in der englischen Übersetzung (`en_US`) enthalten.

---

## [2.5.1] — 2026-06-28

Abschluss von Phase A: deklaratives Schema der Feld-Registry (aus der piveau-DPI-Analyse).

### 🔧 Changed
- **Feld-Registry erweitert (`config/dcat-ap-fields.php`):** Jeder Eintrag trägt nun zusätzliche, deklarative Schema-Metadaten — `profile` (ap/ap.de/hvd), `tier` (mandatory/recommended/optional), `range` (literal/literal-lang/uri/node), `cardinality`, `entity` (dataset/distribution/catalog) und `vocab`. Die Registry ist damit die dokumentierte Single Source of Truth für Pflichtigkeit, Kardinalität und Wertform.
- Vollständig abwärtskompatibel: bestehende Konsumenten (Qualitäts-Scoring, Validierung) lesen unverändert nur die Basis-Schlüssel; das 0–100-Punkteschema bleibt identisch.

### 🧪 Tests
- Neue `tests/test-registry-schema.php` sichert die Schema-Invarianten (gültige Enum-Werte, Konsistenz von `tier`/`required`, Punktesumme 100, eindeutige Keys).

---

## [2.5.0] — 2026-06-28

Phase B der Technischen Spezifikationen: weitere DCAT-AP.de-Felder und ein generisches Vokabular-Autosuggest (aus der piveau-DPI-Analyse).

### ✨ Added
- **DCAT-AP.de-Felder:**
  - `dcatap:availability` (Tab 3, Distribution) — planbare Verfügbarkeit aus dem DCAT-AP.de-Vokabular.
  - `dcatde:contributorID` (Tab 4) — bereitstellende Stelle aus dem offiziellen GovData-Verzeichnis (69 Einträge), mit Autosuggest.
  - `dcatde:originator` und `dcatde:maintainer` (Tab 4) — Urheber bzw. pflegende Stelle als `foaf:Agent` (Name + optional E-Mail).
- **Generisches Vokabular-Autosuggest:** Felder mit `data-odw-vocab="<id>"` beziehen ihre Vorschläge aus lokal gebündelten Vokabulardateien unter `config/vocabularies/` (CESSDA-Muster verallgemeinert) — keine externe Laufzeitabhängigkeit.

### 🌍 i18n
- Neue Oberflächentexte übersetzbar und in der englischen Übersetzung (`en_US`) enthalten.

---

## [2.4.0] — 2026-06-27

Unterstützung für High-Value-Datensätze (HVD) und Vervollständigung der JSON-LD-Namespaces (aus der piveau-DPI-Analyse).

### ✨ Added
- **High-Value-Datensatz (HVD) Unterstützung:** Tab 4 enthält nun eine HVD-Markierung und eine Auswahl der sechs EU-Themenkategorien (Georaum, Erdbeobachtung und Umwelt, Meteorologie, Statistik, Unternehmen und Eigentümerschaft, Mobilität). Markierte Datensätze geben `dcatap:hvdCategory` sowie `dcatap:applicableLegislation` (EU-Durchführungsverordnung 2023/138) im JSON-LD aus. Die Kategorie-URIs stammen aus dem offiziellen EU-Vokabular (`http://data.europa.eu/bna/`).
- **Validierung:** Ist ein Datensatz als HVD markiert, ist die Auswahl einer HVD-Kategorie vor der Veröffentlichung verpflichtend.

### 🔧 Changed
- **JSON-LD `@context` vervollständigt:** Zusätzliche Standard-Namespaces ergänzt — `dcatap` (r5r), `locn`, `adms`, `owl`, `prov`, `odrl`, `spdx` — damit aktuelle und künftige DCAT-AP-Terme korrekt auflösen.

### 🌍 i18n
- Neue HVD-Oberflächentexte sind übersetzbar und in der englischen Übersetzung (`en_US`) enthalten.

---

## [2.3.2] — 2026-06-27

Konformitäts-Korrekturen an der JSON-LD-Ausgabe (aus der piveau-DPI-Analyse).

### 🐛 Fixed
- **Ungültiges JSON-LD bei CESSDA-Thema:** Das CESSDA-Thema wurde unter dem Präfix `cessda:topic` ausgegeben, das im `@context` nicht deklariert war — der Term war damit nicht auflösbar. Es wird nun als DCAT-AP-konformes `dct:subject` (mit `@id`) ausgegeben.
- **Kanonischer DCAT-Namespace:** Der `dcat`-Namespace im `@context` nutzte `https://www.w3.org/ns/dcat#`. Dadurch expandierten `dcat:`-Terme (z. B. `dcat:Dataset`) zu anderen IRIs als die registrierte DCAT-Vokabular-URI, was das Typ-/Property-Matching bei Harvestern bricht. Korrigiert auf die kanonische `http://`-Form.

### 📝 Docs
- README-Sektion „Technische Spezifikationen": Gap-Analyse an den tatsächlichen Code-Stand angepasst (`dcatde:politicalGeocodingLevelURI`, `dct:spatial`/GeoNames, `dct:subject`, `dcatde:licenseAttributionByText` jetzt als umgesetzt markiert); Feldmapping Tab 3 auf `dcatde:licenseAttributionByText` korrigiert.

---

## [2.3.1] — 2026-06-27

Härtungs-Release aus einem Code-Review der jüngsten Änderungen.

### 🔒 Security
- **JSON-LD `@id`-Sanitisierung gehärtet:** `odw_sanitize_jsonld_id()` ließ sich durch führenden Whitespace oder eingebettete Steuerzeichen umgehen (z. B. `" javascript:…"`), wodurch ein gefährliches Schema ungeprüft in die JSON-LD-Ausgabe gelangen konnte. Werte werden jetzt vor der Schema-Erkennung normalisiert.

### 🐛 Fixed
- **Batch-Import:** Schlägt das Verschieben der hochgeladenen Datei fehl, wird die von `wp_tempnam()` angelegte temporäre Datei nun aufgeräumt (kein Datei-Leak mehr).
- **Dateigröße-Widget:** Wird die Zahl gelöscht, wird auch der gespeicherte Byte-Wert geleert (vorher blieb ein alter Wert erhalten).
- **Quality-Schwelle:** Die „Ausreichend"-Schwelle wird auf 0–100 begrenzt, damit die Level-Einstufung auch bei abweichender Punkte-Konfiguration konsistent bleibt.

### 🌍 i18n
- Die letzten hartkodierten deutschen Texte im Dateigröße-Widget (Validierungsmeldung, Einheit „Bytes", Zahlenformat) werden jetzt lokalisiert und in der englischen Übersetzung berücksichtigt (338 Strings).

---

## [2.3.0] — 2026-06-27

### 🌍 Added
- **Englische Lokalisierung:** Der gesamte Wizard, die Admin-Seiten, Einstellungen, Hilfetexte und Meldungen sind jetzt auf Englisch verfügbar. Auf englischsprachigen WordPress-Installationen (`en_US`) erscheint die Oberfläche automatisch in Englisch, auf deutschen weiterhin auf Deutsch.
  - Neue Übersetzungsdateien: `languages/open-data-wizard-en_US.po` / `.mo` (336 Strings)
  - Aktualisierte Vorlage `languages/open-data-wizard.pot` für weitere Sprachen
  - Die technischen DCAT-AP-Bezeichner (z. B. `dct:license`) bleiben sprachunabhängig

---

## [2.2.1] — 2026-06-27

Bugfix- und Bedienbarkeits-Release. Repariert den Batch-Import-Workflow und verbessert den Einstieg.

### 🐛 Fixed
- **Batch-Import funktioniert wieder:** Auf der Batch-Import-Seite wurde kein jQuery geladen, wodurch der „Vorschau"-Button ohne Reaktion blieb. jQuery wird jetzt gezielt eingebunden und der Seiten-Code erst nach dem Laden ausgeführt.
- **Nonce-Prüfung der Upload-/Import-AJAX-Aufrufe:** Das JavaScript sendet das Sicherheits-Token im Feld `nonce`; die Server-Prüfung erwartete bisher `_wpnonce` und schlug daher immer fehl. Beide AJAX-Handler prüfen nun das korrekte Feld.
- **Beispieldatei-Download:** Die Links „CSV-/JSON-Beispiel herunterladen" führten ins Leere. Ein Download-Handler liefert die Beispieldateien jetzt korrekt aus.

### ✨ Improvements
- **Einleitung zum Batch-Import:** Die Seite erklärt nun mit einem kurzen Einleitungstext, wie der Import funktioniert.
- **Direkt zum Einstieg nach Installation:** Nach der Aktivierung wird einmalig automatisch die „Einstieg"-Seite geöffnet.
- **Namensnennungstext kontextabhängig:** Das Feld „Namensnennungstext" erscheint nur noch, wenn als Lizenz CC BY 4.0 oder CC BY-SA 4.0 gewählt ist.
- **Hinweis bei der Qualitätsprüfung:** Ein einleitender Satz stellt klar, dass die Werte erst beim Speichern aktualisiert werden.

---

## [2.2.0] — 2026-06-27

Formular- und Bedienbarkeits-Release. Verbessert die Verständlichkeit der Eingabemaske und verknüpft geografische Angaben mit GeoNames.

### 🎉 Added
- **GeoNames-Verknüpfung (dct:spatial):** Das Feld „Geografische Region" bietet jetzt eine kuratierte Auswahlliste (Deutschland, alle 16 Bundesländer, größere Städte). Die Auswahl wird im JSON-LD automatisch mit der passenden GeoNames-URI (`@id`) verknüpft; Freitext/eigene URIs bleiben weiterhin möglich.
- **Lizenz-Erklärungen:** Unter der Lizenzauswahl erscheint eine allgemeinverständliche Beschreibung, was die gewählte Lizenz erlaubt (CC0, CC BY, CC BY-SA).

### ✨ Improvements
- **Deutscher Kalender:** Die Datumsauswahl (Veröffentlichungs-, Änderungs- und Gültigkeitsdaten) zeigt Monatsnamen, Wochentage und Platzhalter nun auf Deutsch.
- **CESSDA-Themenklassifikation:** Im Eingabefeld wird jetzt das sprechende Label (z. B. „Bildung") angezeigt statt der URI. Die URI wird im Hintergrund gespeichert und zusätzlich als Hinweis eingeblendet — das JSON-LD bleibt DCAT-AP-konform.
- **Ausgeschriebene Lizenznamen:** Die Lizenzauswahl nennt die Lizenzen vollständig (z. B. „CC BY 4.0 — Namensnennung").
- **Kontakt-Abschnitt:** Überschrift „Kontaktpunkt (dcat:contactPoint)" zu „Kontakt" vereinfacht; technische Feldbezeichnungen ausgeblendet.

### 🐛 Fixed
- **Abstände:** Das Dateigröße-Widget wird nun mit demselben linken Einzug (20 px) wie die übrigen Formularfelder dargestellt.

### 🧪 Tests & Qualität
- **94 PHPUnit-Tests** (zuvor 92), PHPStan Level 6, PHPCS sauber (Exit 0)

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
