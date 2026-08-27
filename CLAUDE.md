# CLAUDE.md — Open Data Wizard Projekt-Dokumentation

Umfassende Dokumentation für die Fortentwicklung des Open Data Wizard WordPress-Plugins. Enthält Architektur, Entwicklungsworkflows, Testing-Strategien und Sicherheitsrichtlinien.

---

## 🚀 Quick Start

### Installation & Setup
```bash
# Clone und Dependencies installieren
git clone https://github.com/daimpad/OpenDataWizard.git
cd OpenDataWizard
composer install   # PFLICHT: erzeugt vendor/ (nicht im Repo)

# WordPress aktivieren (requires `wp` CLI)
wp plugin activate open-data-wizard
```

> **`vendor/` ist nicht eingecheckt** (seit v2.34.0) und wird ausschließlich aus `composer.lock`
> erzeugt — lokal und in der CI per `composer install`, für das Release-ZIP per
> `bin/build-release.sh` (`--no-dev`). Dadurch kann die Toolchain lokal nicht mehr vom Lock
> abweichen. Läuft PHPCS/PHPStan lokal anders als in der CI, ist fast immer ein vergessenes
> `composer install` die Ursache (z. B. nach einem Dependabot-Update des Locks).

### Schnelle Befehle
```bash
# Entwicklung & Testing
./vendor/bin/phpcs --standard=config/phpcs.xml                 # Code-Style prüfen
./vendor/bin/phpcbf --standard=config/phpcs.xml includes/      # Auto-fix Style-Fehler
./vendor/bin/phpunit --configuration=config/phpunit.xml        # Unit-Tests (243 Tests)
./vendor/bin/phpstan analyse --configuration=config/phpstan.neon  # Static analysis

# Spezifische Tests
./vendor/bin/phpunit --configuration=config/phpunit.xml tests/test-fields.php
./vendor/bin/phpunit --configuration=config/phpunit.xml --filter testMethodName

# Paket & Übersetzungen (ohne Composer lauffähig)
python3 bin/check-i18n.py                                      # fehlende Übersetzungen
bash bin/build-release.sh                                      # ZIP bauen
python3 bin/verify-package.py dist/open-data-wizard-<ver>.zip   # Paket prüfen

# End-to-End (braucht Docker)
npm run env:start                                              # WordPress + Plugin + Saat
npm run test:e2e                                               # Chromium gegen Port 8889
npm run env:stop
```

### CI/CD Pipeline
- Runs auf **PHP 8.1, 8.2, 8.3** via GitHub Actions (`.github/workflows/ci.yml`)
- Sieben Jobs: PHPCS, PHPStan, PHPUnit (drei PHP-Versionen), SHACL, **Release-Paket**,
  **Übersetzungen**, **End-to-End**
- **Pull Requests** werden geblocked, wenn CI fehlschlägt

> **Warum ein eigener Paket-Job?** Alle übrigen Prüfungen laufen gegen das
> Repository — das ausgelieferte ZIP sieht keine von ihnen an. Eine vergessene
> Zeile in der Allowlist von `bin/build-release.sh` erzeugt deshalb ein Paket,
> das in grüner CI entsteht und trotzdem in einer echten Installation Fehler
> wirft (so geschehen mit `blocks/` in v2.38.0). `bin/verify-package.py` baut das
> ZIP, liest jeden über `ODW_PLUGIN_DIR`/`ODW_PLUGIN_URL` gebildeten Pfad aus den
> **im Paket enthaltenen** PHP-Dateien und prüft, ob er dort liegt. Die Liste ist
> damit nie veraltet. Zusätzlich: Autoloader, `block.json`, `.mo` sowie die
> Gleichheit von Plugin-Header, `ODW_VERSION` und oberstem CHANGELOG-Eintrag.
> Bewusste Ausnahmen stehen mit Begründung in `KNOWN_ABSENT`.

> **End-to-End-Tests.** `npm run env:start` startet über `wp-env` (Docker) ein
> WordPress mit eingehängtem Plugin, schaltet es auf **Deutsch** und setzt
> **sprechende Permalinks** (`--hard`, damit die `.htaccess` geschrieben wird —
> ohne sie gibt es `/wp-json/` nicht). Beides ist nötig, sonst scheitert jeder
> Test: In einem en_US-WordPress greift die mitgelieferte Übersetzung, und die
> deutschen Beschriftungen sind nicht zu finden. `tests/e2e/mu-plugins/odw-e2e-seed.php` wird dabei als
> mu-plugin gemountet und legt einmalig zwei veröffentlichte Datensätze an —
> deshalb können die Zusicherungen unbedingt sein. Ein `if (await x.count() > 0)`
> in einem E2E-Test besteht auch dann, wenn die Oberfläche kaputt ist; solche
> Konstruktionen gehören nicht in diese Dateien. Die CI fährt nur Chromium,
> lokal stehen über `npm run test:e2e:all` auch Firefox und WebKit bereit.

---

## 📐 Projekt-Struktur

```
open-data-wizard/
├── open-data-wizard.php              # Plugin-Header & Bootstrap
├── uninstall.php                     # Deinstallations-Hook
├── includes/
│   ├── class-setup.php              # Activation/Demo-Dataset
│   ├── class-post-types.php         # CPT: odw_dataset
│   ├── class-fields.php             # Carbon Fields Formular (5 Tabs)
│   ├── class-validation.php         # Publishing-Validation
│   ├── class-quality.php            # Qualitäts-Scoring (0-100)
│   ├── class-admin.php              # Admin UI, Spalten, Intro-Seite
│   ├── class-rest-api.php           # REST Endpoints (/catalog, /datasets/<id>, /delta)
│   ├── class-rdf.php                # JSON-LD → Turtle Serializer (Harvest-Ausgabe)
│   ├── class-field-reference.php    # Feld-Referenz-Generator (docs/FELD-REFERENZ.md)
│   ├── class-settings.php           # Einstellungsseite
│   ├── class-shortcode.php          # [odw_dataset id="123"] Frontend-Card
│   ├── class-batch-import.php       # CSV/JSON Batch-Import (Parser + Importer)
│   └── class-cli.php                # WP-CLI Befehle
├── assets/
│   ├── css/
│   │   ├── admin.css               # Admin-UI Styling (Tabs, Badges, etc.)
│   │   └── frontend.css            # Frontend Download-Card Styling
│   ├── js/
│   │   ├── wizard-tabs.js          # Tab-Navigation mit SessionStorage
│   │   ├── odw-admin-fields.js     # Field-Initialization, Autosuggest
│   │   └── odw-file-upload.js      # File-Upload Handler (wp.media)
│   └── images/                      # Icons, Logos
├── config/
│   ├── phpcs.xml                   # WordPress Coding Standards
│   ├── phpstan.neon                # Static Analysis Konfiguration
│   ├── phpunit.xml                 # Unit-Test Konfiguration
│   ├── licenses.txt                # Lizenz-Liste (URI | Label)
│   ├── dct-format-list.php         # Format-Mapping (MIME → DCAT-AP URI)
│   ├── dcat-ap-fields.php          # Feld-Registry (Meta-Key, Label, Required + Schema-Metadaten)
│   ├── field-catalog.php           # Feld-Katalog (Quelle für docs/FELD-REFERENZ.md)
│   ├── mqa-metrics.php             # EU-MQA-Metriken (5 Dimensionen, 405 Punkte)
│   ├── shacl/                      # Offizielle SHACL-Shapes (EU + GovData) — nur Referenz
│   └── vocabularies/               # Lokal gebündelte Vokabulare (z.B. contributors.json)
├── bin/
│   ├── build-release.sh            # Baut das schlanke Plugin-ZIP (Allowlist + composer --no-dev)
│   ├── generate-field-reference.php # Standalone-Generator für docs/FELD-REFERENZ.md
│   └── compile-mo.py               # PO→MO-Compiler (ersetzt fehlendes msgfmt)
├── docs/
│   └── FELD-REFERENZ.md            # Generiert — nicht von Hand bearbeiten
├── samples/                         # Beispieldateien für den Batch-Import (CSV/JSON)
├── tests/
│   ├── test-fields.php             # ODW_Fields Methoden-Tests
│   ├── test-fields-extended.php    # JSON-LD Builder Tests
│   ├── test-quality.php            # Qualitäts-Scoring Tests
│   ├── test-settings.php           # Settings-Filter Tests
│   ├── test-shortcode.php          # Shortcode Rendering Tests
│   ├── test-rest-delta.php         # Delta Endpoint Tests
│   ├── test-cli.php                # CLI Commands Tests
│   └── class-stubs.php             # WP_Mock Stubs
├── languages/
│   ├── open-data-wizard.pot        # i18n Translation Template
│   ├── open-data-wizard-en_US.po   # Englische Übersetzung (Quelle)
│   └── open-data-wizard-en_US.mo   # Englische Übersetzung (kompiliert)
├── README.md                        # User-facing Dokumentation (DE) + Doku-Navigation
├── DOCUMENTATION.md                 # Technische Dokumentation (Architektur, REST-API, CLI …)
├── TECHNICAL-SPEC.md                # Technische Spezifikationen + DCAT-AP-Roadmap/Umsetzungsstand
├── CHANGELOG.md                     # Version-History
├── composer.json                    # PHP Dependencies
└── CLAUDE.md                        # Diese Datei
```

---

## 🏗️ Architektur-Überblick

### Kern-Flow

```
Admin erstellt Dataset (CPT: odw_dataset)
    ↓
Carbon Fields Formular (5 Tabs) erfasst DCAT-AP 3.0 Metadaten
    ↓
Validation-Hook prüft Pflichtfelder vor Publishing
    ↓
Post Meta speichert: _odw_publisher, _odw_description, _odw_license, etc.
    ↓
Quality-Hook berechnet Vollständigkeits-Score (0-100)
    ↓
REST-API baut JSON-LD & cached in Transients (5 min TTL)
    ↓
Externe Harvester rufen /catalog, /datasets/<id>, oder /delta ab
```

### Klassen & Verantwortlichkeiten

| Klasse | Zweck | Haupt-Methoden |
|--------|-------|----------------|
| **ODW_Setup** | Activation: Demo-Dataset erstellen, Welcome Notice | `on_activation()`, `maybe_create_demo()` |
| **ODW_Post_Types** | CPT Registrierung mit Capabilities | `register()` |
| **ODW_Fields** | Carbon Fields Formular (5 Tabs) + JSON-LD Builder | `register_required_fields()`, `odw_build_dataset_jsonld()` |
| **ODW_Validation** | Publish-Blocking bei fehlenden Pflichtfeldern | `intercept_publish()`, `validate()` |
| **ODW_Quality** | Qualitäts-Scoring & Caching | `calculate()`, `get_level()` |
| **ODW_Admin** | Admin UI (Spalten, Intro-Seite, Datei-Upload-Widget in Tab 3) | `register_introduction_page()`, `render_column()`, `file_upload_html()`, `save_file_attachment()` |
| **ODW_Rest_API** | REST Endpoints mit Transient-Caching | `get_catalog()`, `get_dataset()`, `get_delta()` |
| **ODW_Rdf** | JSON-LD → Turtle (dependency-frei) für RDF-Harvester | `to_turtle()` |
| **ODW_Field_Reference** | Erzeugt `docs/FELD-REFERENZ.md` aus `config/field-catalog.php` | `build()`, `write()`, `js_map()` |
| **ODW_Settings** | Plugin-Einstellungsseite | `get()`, `filter_catalog_title()` |
| **ODW_Block** | Gutenberg-Block „Datensatz-Karte“ (dynamisch, rendert über ODW_Shortcode) | `register()`, `localize()`, `render()` |
| **ODW_Shortcode** | Frontend Download-Card: `[odw_dataset id="123"]` | `render()` |
| **ODW_Batch_Import** | CSV/JSON Batch-Import: Parsing, Validierung, Bulk-Insert | `parse_file()`, `validate_row()`, `import_records()` |
| **ODW_CLI** | WP-CLI Befehle (Qualitäts-Recalc, Cache-Clear) | `quality_recalculate()`, `cache_clear()` |

### Design Patterns

#### 1. **Statische Methoden ohne Instantiierung**
Alle Klassen verwenden **nur statische Methoden** — keine `new`-Operationen:
```php
class ODW_Something {
    public static function init(): void {
        add_action( 'hook_name', array( self::class, 'handler_method' ) );
    }
    
    public static function handler_method(): void { ... }
}
```

#### 2. **JSON-LD als Single Source of Truth**
Die `odw_build_dataset_jsonld()` Funktion erzeugt die **kanonische JSON-LD Darstellung**:
- Verwendet Carbon Fields + Post Meta
- Wird in Transients gecacht (keyed by post_id)
- Output sanitized mit `esc_url_raw()`, `esc_attr()`, etc.
- Erweiterbar via `odw_dataset_jsonld` Filter

#### 3. **Post Meta für Business-Logic**
Auch wenn Carbon Fields UI bietet — intern arbeiten wir mit **Post Meta**:
- CF Field `odw_description` → Post Meta Key `_odw_description`
- Decoupling von CF internals
- Einfaches Testing (keine CF Dependencies)

#### 4. **Transient Caching für Performance**
Alle REST-Responses werden gecacht:
- **Catalog**: `odw_catalog_` + MD5(page, per_page, filters)
- **Dataset**: `odw_dataset_` + post_id
- **Delta**: `odw_delta_` + MD5(since, page)
- **TTL**: 5 Minuten (in Einstellungen konfigurierbar)
- **Invalidation**: Automatisch auf `save_post_odw_dataset` + `trashed_post`

#### 5. **Validation = Publish-Blocking**
Validation greift nicht bei Save ein — nur bei **Publish-Attempt**:
1. `wp_insert_post_data` Filter prüft Publish-Status
2. Fehler? → Post zurück zu Draft + Error-Transient (300s TTL)
3. Admin Notice zeigt Fehler
→ Erlaubt Drafts, erzwingt Vollständigkeit für Public

#### 6. **Companion Functions außerhalb Klassen**
Zwei Funktionen stehen **außerhalb** class-Definitionen (absichtlich):

```php
// In class-fields.php, nach class-Definition:
function odw_build_dataset_jsonld( int $post_id ): ?array { ... }

// In class-shortcode.php:
function odw_format_bytes( int $bytes, int $precision = 2 ): string { ... }
```
Grund: Hohe Sichtbarkeit, mehrere Klassen verwenden sie, kein `self::`-Overhead.

---

## 📋 Formular-Struktur (Carbon Fields, 5 Tabs)

### Tab 1: Grundlegende Informationen
```php
- odw_publisher (REQUIRED)
  → Herausgebende Organisation (dct:publisher)

- odw_description (REQUIRED)
  → Worum geht es in diesem Datensatz? (dct:description)

- odw_theme
  → In welche Kategorie gehört dieser Datensatz? (dcat:theme)

- odw_keywords
  → Mit welchen Schlagworten finde ich diese Daten? (dcat:keyword)

// Aufklappbar „Weitere Einordnung (optional)":
- odw_cessda_topic     → CESSDA Topic Classification (dct:subject)
- odw_engagementfeld   → ZiviZ-Engagementfeld des Datensatzes (dct:subject)
```

### Tab 2: Sprache & Übersetzungen
```php
- odw_language
  → In welcher Sprache sind die Daten? (dct:language)

// Übersetzungs-Repeater (mehrsprachige Literale):
- odw_title_translations
- odw_description_translations
- odw_keyword_translations
```

### Tab 3: Datenbereitstellung (SIMPLIFIED v2.1.4!)
```php
- odw_access_url (REQUIRED)
  → Wo kann ich die Datei herunterladen? (dcat:accessURL)
  
- odw_format
  → In welchem Format ist die Datei? (dct:format) — EU-URI
  
- odw_byte_size
  → Dateigröße in Bytes (dcat:byteSize) — nur Zahl
  
- odw_license (REQUIRED)
  → Unter welcher Lizenz sind die Daten? (dct:license)
  
- odw_license_custom (Conditional!)
  → Nur sichtbar wenn odw_license == 'sonstige'
  → Custom Lizenz-URI eingeben/auswählen
  
- odw_attribution_text
  → Namensnennungstext (dcatde:licenseAttributionByText) — nur bei CC-BY
```

**v2.1.4 CHANGE:** Keine komplexen/repeater Felder mehr! Zuvor gab es:
- `odw_distributions[]` (repeater mit sub-fields: access_url, format, byte_size, license)
- → **Simplify zu Singular**: Eine Distribution pro Dataset (UI UX improvement)
- Validation + Quality-Scoring angepasst

### Tab 4: Erweiterte Angaben
```php
- odw_landing_page
  → Projektseite (dcat:landingPage)
  
- odw_accrual_periodicity
  → Aktualisierungsfrequenz (dct:accrualPeriodicity)
  
- odw_political_geocoding_level
  → Verwaltungsebene (dcatde:politicalGeocodingLevelURI)
  
- odw_spatial
  → Geografische Region (dct:spatial) — Freitext oder URI
  
- odw_temporal_start, odw_temporal_end
  → Zeitlicher Bezug (dct:temporal)
  
- odw_contact_name, odw_contact_email, odw_contact_url
  → Kontaktpunkt (dcat:contactPoint)
```

### Tab 5: Vorschau
```php
- Live JSON-LD Preview (Read-Only HTML Field)
- REST Endpoint URL zum Abrufen
- Wird bei jedem Save regeneriert
```

---

## 🎨 Admin Interface

### Menü-Struktur
```
WordPress Admin → Datensätze
├── Alle Datensätze (List View mit Spalten)
├── Einstieg (neu in v2.1.4!)
│   └── Welcome/Intro Page mit:
│       - What is Open Data Wizard?
│       - How it works (5-step overview)
│       - Link: "Neuen Datensatz erstellen"
├── Neue Datensatz (Create Form)
├── Einstellungen (Settings Page)
│   ├── Katalog (Title, Publisher)
│   ├── Standardwerte (Default Language, License)
│   ├── REST API (Cache TTL)
│   └── Deinstallation (Cleanup Checkbox)
```

### List View Spalten
```
[Checkbox] | Titel | Lizenz | Thema | Qualität | Status | Änderungsdatum | Shortcode
```

- **Lizenz**: Zeigt `odw_license` Label; wenn 'sonstige', zeigt `odw_license_custom`
- **Qualität**: Badge mit Score (0-100) + 4-Level Ampel (Grün/Gelb/Rot/Grau)
- **Status**: Badge "Veröffentlicht" oder "Entwurf"
- **Shortcode**: `[odw_dataset id="123"]` — klickbar zum Auswählen

### Einstiegsseite (neu v2.1.4)
In `class-admin.php` Methode `register_introduction_page()`:
- Submenu unter "Datensätze"
- Welcome Content mit Step-by-Step Erklärung
- Button: "Neuen Datensatz erstellen"
- Styling via inline `<style>` im HTML

---

## 🔐 Sicherheit & Capabilities

### Capability System
- **`manage_open_data`** — Custom Capability für Dataset-Operations
- Automatisch an **Admins + Editors** vergeben bei Activation
- Entfernt bei Deinstallation (wenn Checkbox aktiviert)

### Kontrollpunkte
```php
// CPT: Alle Write-Ops require manage_open_data
'capability_type' => 'odw_dataset',
'capabilities' => [
    'create_posts' => 'manage_open_data',
    'edit_posts' => 'manage_open_data',
    'delete_posts' => 'manage_open_data',
]

// File Upload: wp_verify_nonce() + current_user_can('edit_post')
// Settings Update: wp_verify_nonce() + current_user_can('manage_options')
```

### Input Sanitization
- `$_GET`/`$_POST`: `sanitize_text_field()`, `absint()`
- URLs: `esc_url_raw()` vor Output (blockt `javascript:`, `data:`)
- Meta Values: `esc_attr()`, `esc_html()` bei Output

---

## 🌍 Internationalisierung (i18n)

### Text Domain: `open-data-wizard`
```php
__( 'Translatable Text', 'open-data-wizard' )
esc_html__( 'User-facing Text', 'open-data-wizard' )
esc_attr__( 'Attribute Text', 'open-data-wizard' )
```

### Translation Files
- **Source language**: German (the `__()` default strings are German)
- **POT Template**: `languages/open-data-wizard.pot`
- **English**: `languages/open-data-wizard-en_US.po` / `.mo` — overrides the German
  source strings on `en_US` installs (336 strings)
- Loaded via `load_plugin_textdomain()` on `init` hook
- The `.mo` is generated without gettext tooling; if strings change, regenerate
  the `.po`/`.mo` (see the generator approach in the v2.3.0 PR)

### Praktiken
- Alle UI-Texte translatable
- Technische Labels (DCAT-AP) in Help-Text nicht-translatable oder extra markiert
- Form Labels: User-friendly Frage → Hilftext hat technisches Label

---

## 🧪 Testing & Quality Assurance

### Test-Suite Struktur
**PHPUnit + WP_Mock** (keine Datenbank nötig)

#### Test Files
- **test-fields.php** — ODW_Fields: License Labels, Format MIME, Required Fields
- **test-fields-extended.php** — JSON-LD Builder: Complete JSON-LD output validation
- **test-quality.php** — Quality Scoring: 0-100 Score calculation, 4-level mapping
- **test-settings.php** — Settings get/filter methods
- **test-shortcode.php** — Shortcode rendering, byte formatting
- **test-rest-delta.php** — Delta endpoint validation, cache logic
- **test-cli.php** — WP-CLI commands

#### Running Tests
```bash
# Alle 243 Tests
./vendor/bin/phpunit --configuration=config/phpunit.xml

# Spezifische Test-Datei
./vendor/bin/phpunit --configuration=config/phpunit.xml tests/test-fields.php

# Einzelner Test
./vendor/bin/phpunit --configuration=config/phpunit.xml --filter testMethodName

# Mit Details
./vendor/bin/phpunit --configuration=config/phpunit.xml --verbose
```

#### Test-Pattern
```php
class Test_ODW_Fields extends \WP_Mock\Tools\TestCase {
    protected function setUp(): void {
        \WP_Mock::setUp();
    }
    
    protected function tearDown(): void {
        \WP_Mock::tearDown();
    }
    
    public function test_something(): void {
        \WP_Mock::userFunction( '__' )->andReturnArg( 0 );
        \WP_Mock::userFunction( 'esc_html__' )->andReturnArg( 0 );
        
        // require_once if needed
        // assertions
    }
}
```

### Code Quality Tools
```bash
# WordPress Coding Standards
./vendor/bin/phpcs --standard=config/phpcs.xml
./vendor/bin/phpcbf --standard=config/phpcs.xml includes/

# Static Analysis (PHP 8.1+ Type Hints)
./vendor/bin/phpstan analyse --configuration=config/phpstan.neon

# Expected: 0 PHPCS violations, PHPStan Level 6
```

---

## 📦 REST API Endpoints

### Base Namespace
All endpoints unter `/wp-json/datenatlas/v1/`

### GET /catalog
**Alle veröffentlichten Datasets als dcat:Catalog (JSON-LD)**

Query-Parameter:
- `page=1`, `per_page=20` — Pagination
- `theme=Bildung` — Filter by theme
- `license=cc-by` — Filter by license short code
- `format=json|jsonld` — Response format

Response (JSON-LD):
```json
{
  "@context": "https://www.w3.org/ns/dcat",
  "@type": "dcat:Catalog",
  "dct:title": "...",
  "dcat:dataset": [ { ... }, { ... } ]
}
```

Caching: `odw_catalog_` + MD5 key, TTL 5 min

### GET /datasets/{id}
**Einzelner Dataset als dcat:Dataset (JSON-LD)**

Response:
```json
{
  "@context": "...",
  "@type": "dcat:Dataset",
  "dct:title": "...",
  "dct:description": "...",
  "dcat:distribution": { ... }
}
```

Caching: `odw_dataset_{post_id}`, TTL 5 min

### GET /delta?since={ISO8601}
**Inkrementelles Harvesting: Nur Änderungen seit Timestamp**

Query-Parameter:
- `since=2026-01-01T00:00:00Z` (ISO 8601)
- `page=1`, `per_page=20` — Pagination

Response:
```json
{
  "datasets": [ { ... } ],          // Geänderte Datasets
  "tombstones": [                    // Gelöschte Datasets
    { "id": "post-id", "deleted": true }
  ]
}
```

Caching: `odw_delta_` + MD5 key, TTL 5 min

---

## 🎯 CSS Spacing & Styling

### Admin Interface Spacing (ab v2.36.0)
Abstände kommen vom Raster des Tab-Panels, nicht mehr von Margins je Feld —
`gap` kollabiert nicht und verdoppelt sich nicht.

```css
.cf-container__tab {
    display: grid;
    grid-template-columns: repeat( 2, minmax( 0, 1fr ) );
    gap: var( --odw-space-5 ) var( --odw-space-4 );
    padding: var( --odw-space-5 ) 20px var( --odw-space-6 );
}

/* Alles über die volle Breite; halbe Breite ist ein Opt-in. */
.cf-container__tab > * { grid-column: 1 / -1; }

/* Ausgewählt über das name-Attribut, nicht über ein data-Attribut:
   Carbon Fields reicht data-Attribute bei <select> nicht zuverlässig
   ans DOM durch, der Meta-Key steht dagegen immer im Namen. */
.cf-container__tab > .cf-field:has( [name$="[_odw_temporal_start]"] ) {
    grid-column: span 1;
}
```

> **Achtung bei Carbon-Fields-Updates:** Alle Regeln, die an CF-Klassennamen
> hängen, stehen gesammelt im Block `FORMULAR-DESIGN` am Ende von
> `assets/css/admin.css`. Das ist die einzige Stelle, die nach einem Update
> zu prüfen ist.

### CSS Variables (Admin)
```css
:root {
    --odw-color-primary:     #2271b1;
    --odw-color-primary-bg:  #fff;
    --odw-color-border:      #c3c4c7;
    --odw-color-bg-light:    #f6f7f7;
    --odw-color-text:        #1d2327;
    --odw-color-text-muted:  #50575e;
    /* + Quality Colors */
    --odw-color-quality-high-dot: #1a7f37;
    /* + More... */
}
```

### Responsiveness
- **Admin**: Keine spezielle Responsive-Styles (WordPress Admin-Standard)
- **Frontend Shortcode**: Responsive Download-Card (Flexbox)

---

## 📝 Git Workflow & Commits

### Branch Strategy
**WICHTIG (ab v2.1.4):** Alle Commits gehen **direkt zu `main`**

```bash
# Lokal entwickeln
git checkout main
git pull origin main

# Änderungen machen
# ... edit files ...

# Testen
./vendor/bin/phpcs --standard=config/phpcs.xml
./vendor/bin/phpunit --configuration=config/phpunit.xml

# Commit (mit Session-URL)
git commit -m "Feature/Fix: Beschreibung

Detaillierte Erklärung was sich ändert und warum.

https://claude.ai/code/session_01JB1xUQM892bVZ4Yv3MZjvq"

# Pushen zu main
git push origin main
```

### Commit Message Conventions
```
<Type>: <Kurzbeschreibung (max 70 chars)>

<Detaillierte Erklärung:>
- Was hat sich geändert
- Warum war diese Änderung nötig
- Betroffene Komponenten
- Breaking Changes (falls relevant)

https://claude.ai/code/session_<SESSION_ID>
```

Typen:
- `feat:` — Neue Feature
- `fix:` — Bug Fix
- `refactor:` — Code Umstrukturierung (keine neue Funktionalität)
- `style:` — PHPCS/Formatting Fixes
- `test:` — Test-Updates
- `docs:` — Dokumentation
- `chore:` — Maintenance, Dependencies

---

## 🔧 Häufige Development Tasks

### Adding a DCAT-AP Field
1. **Define in Carbon Fields** (`includes/class-fields.php`, appropriate tab)
   ```php
   Field::make( 'text', 'odw_my_field', __( 'User Question?', 'open-data-wizard' ) )
       ->set_help_text( __( 'TECHNICAL LABEL (dcat:term)', 'open-data-wizard' ) . "\n\n" . __( 'Example: ...', 'open-data-wizard' ) )
   ```

2. **Add to JSON-LD Builder** (same file, `odw_build_dataset_jsonld()`)
   ```php
   $my_field = (string) carbon_get_post_meta( $post_id, 'odw_my_field' );
   if ( ! empty( $my_field ) ) {
       $dataset['dcat:myProperty'] = $my_field;
   }
   ```

3. **Update Validation** (`includes/class-validation.php`) if required
   ```php
   // Add to get_required_fields() if mandatory
   ```

4. **Write Tests** (`tests/test-fields-extended.php`)
   ```php
   public function test_build_includes_my_field(): void {
       // Mock setup + assertions
   }
   ```

5. **Update Quality Indicators** (`includes/class-quality.php`) if applicable

### Adding Admin List Column
In `class-admin.php`:
1. Add to `set_columns()` array
2. Implement in `render_column()` switch-case
3. Optional: Add to `sortable_columns()` + `handle_meta_orderby()`

### Adding REST API Filter
In `class-rest-api.php` `get_catalog()`:
1. Add parameter to `register_routes()`
2. Build `$meta_query` if meta-based
3. Update transient cache key to include filter in MD5

### Adding WP-CLI Command
In `class-cli.php`:
1. Define method with `array $args, array $assoc_args` params
2. Register in `init()`: `\WP_CLI::add_command( 'open-data-wizard cmd', ... )`
3. Add docblock with `## OPTIONS`, `## EXAMPLES`
4. Add tests in `tests/test-cli.php`

---

## 🐛 Debugging & Troubleshooting

### Cache Issues
```php
// Clear ALL transients
delete_transient( 'odw_catalog_*' );
delete_transient( 'odw_dataset_*' );
delete_transient( 'odw_delta_*' );

// Or via Action (if implemented)
do_action( 'odw_clear_caches' );
```

### JSON-LD Validation
- Test via https://validator.schema.org/
- Check `odw_build_dataset_jsonld()` for null values
- Ensure `access_url` is clean (no `javascript:` schemes)

### PHPCS False Positives
Already excluded in `config/phpcs.xml`:
- `WordPress.Files.FileName` — tests use different naming
- `Generic.Files.OneObjectStructurePerFile` — test stubs
- `WordPress.DB.SlowDBQuery` — meta queries intentional
- `WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize` — cache keys only

### PHPStan Ignores
Known false positives from incomplete Carbon Fields stubs:
- `Function carbon_get_post_meta not found`
- `Method XYZ not found` on Carbon_Fields classes
- `Constant ODW_PLUGIN_DIR not found` — defined at runtime

---

## 🚀 Version Management

### Version Bumping
Update **both** locations:
1. Plugin header in `open-data-wizard.php`
   ```php
   /**
    * Version: 2.1.5
    */
   ```

2. Constant definition
   ```php
   define( 'ODW_VERSION', '2.1.5' );
   ```

3. Update `CHANGELOG.md` with:
   - New features
   - Bug fixes
   - Breaking changes
   - Security updates

### Semantic Versioning
- **MAJOR.MINOR.PATCH**
- MAJOR: Breaking API changes
- MINOR: New features (backward-compatible)
- PATCH: Bug fixes only

Current: **v2.41.1**

---

## 🧩 Projektstand & DCAT-AP-Roadmap (piveau-DPI-Analyse)

Die technische Weiterentwicklung folgt einer Analyse des **piveau Data Provider Interface (DPI)**. Die
vollständige Spezifikation (Metadatenmodell, Namespaces, Vokabulare, DCAT-AP-/DCAT-AP.de-Feldkatalog mit
Gap-Analyse, Feld-Registry-Schema, phasierte Umsetzungsplanung) liegt in **[`TECHNICAL-SPEC.md`](TECHNICAL-SPEC.md)**.
Neue technische Festlegungen gehören dorthin, nicht in README oder CLAUDE.md.

### ✅ Erledigt

- **Konformitäts-Fixes (v2.3.2):** CESSDA-Thema als `dct:subject` (statt undeklariertem `cessda:`-Präfix);
  `dcat`-Namespace auf kanonisches `http://www.w3.org/ns/dcat#`.
- **HVD-Unterstützung (v2.4.0):** `dcatap:hvdCategory` + `dcatap:applicableLegislation` (EU-Reg. 2023/138);
  `@context` um `dcatap`, `locn`, `adms`, `owl`, `prov`, `odrl`, `spdx` vervollständigt.
- **DCAT-AP.de-Felder + Vokabular-Autosuggest (v2.5.0):** `dcatde:contributorID` (gebündeltes Vokabular,
  `config/vocabularies/`), `dcatde:originator`, `dcatde:maintainer`, `dcatap:availability` (EU-Authority-Table);
  generisches Autosuggest via `data-odw-vocab` + `odw_resolve_vocab_uri()`.
- **Feld-Registry-Schema (v2.5.1):** `config/dcat-ap-fields.php` trägt deklarative Metadaten
  (`profile`, `tier`, `range`, `cardinality`, `entity`, `vocab`); abwärtskompatibel, durch
  `tests/test-registry-schema.php` abgesichert.
- **Phase D — Mehrsprachige Literale (v2.2x):** `title`/`description`/`keyword` als
  `@language`/`@value`, je Sprache über Übersetzungs-Repeater pflegbar.
- **Phase E — Multi-Distribution:** wiederholbare Distributionen (`odw_extra_distributions`)
  zusätzlich zur primären Distribution.
- **Weitere DCAT-AP.de-Felder:** `politicalGeocodingURI`, `legalBasis`, `qualityProcessURI`,
  `geocodingDescription` sowie Profi-UX („Erweiterte Angaben" in aufklappbaren Gruppen).
- **UX-Paket B (v2.29.0–v2.32.0):** Pflichtfeld-Sternchen statt CF-`set_required` (Entwürfe bleiben
  speicherbar), Fehlermeldungen mit Tab-Angabe + „Zum Feld springen", einheitliche Prozent-Anzeige
  der Qualität, entschlacktes Tab 1, konsistente Begriffe („Thema"/„Schlagworte").
- **Harvest-Endpoint (v2.33.0):** `/catalog?full=1` (vollständiger Katalog in einem
  Dokument) + `&format=turtle` über den dependency-freien `ODW_Rdf`-Serializer; Admin-Box mit den
  kopierfertigen URLs. Gegen die gebündelten SHACL-Shapes validiert (DCAT-AP.de: konform).
- **Reproduzierbare Abhängigkeiten (v2.34.0):** `vendor/` ist nicht mehr eingecheckt (siehe Quick Start).
- **Gebündelte Vokabulare vervollständigt (v2.37.0):** `access-right` (3 Stufen) und `language`
  (alle 24 EU-Amtssprachen) liegen unter `config/vocabularies/`. Die Sprachauswahl bot vorher nur
  Deutsch und Englisch, obwohl `odw_resolve_language_tag()` alle 24 Codes bereits umsetzen konnte.
- **Gutenberg-Block (v2.38.0):** „Datensatz-Karte“ als Alternative zum Shortcode. Dynamischer
  Block ohne Build-Schritt — `blocks/dataset-card/` mit `block.json` und schlichtem JS; das
  Rendern delegiert an `ODW_Shortcode::render()`. Die Auswahlliste kommt über
  `wp_localize_script`, weil der CPT bewusst nicht über die WP-REST-API exponiert ist.
- **Content Negotiation (v2.35.0):** Alle drei Endpunkte liefern `jsonld`/`json`/`turtle`; ohne
  `?format=` entscheidet der `Accept`-Header (q-Werte, `Vary: Accept`). Explizites `?format=`
  hat Vorrang.

### ☐ Noch offen / geplant

- **Optional: RDF/XML** als weitere Serialisierung (Turtle und JSON-LD decken die gängigen
  Harvester bereits ab).
- **Mehrsprachigkeit der Oberfläche** (WPML/Polylang) — das Datenmodell unterstützt Mehrsprachigkeit
  bereits, die Integration fehlt.
- **Optional/künftig:** Registry-getriebenes Formular-/JSON-LD-Rendering (Aufräumarbeit ohne
  sichtbaren Nutzen).

> **Hinweis zu i18n:** Im aktuellen Container ist `msgfmt` nicht verfügbar. Die `.mo` wird daher per
> gebündeltem, dependency-freiem PO→MO-Skript neu erzeugt:
> `python3 bin/compile-mo.py languages/open-data-wizard-en_US.po languages/open-data-wizard-en_US.mo`.
> Bei Änderungen an Strings die `.po`/`.pot` pflegen (neue `msgid`/`msgstr`-Paare ergänzen) und die
> `.mo` neu kompilieren.

---

## 📚 Resources & External Links

### Standards & Specifications
- [DCAT-AP 3.0 Specification](https://data.europa.eu/api/hub/store/dataset/dcat-ap)
- [JSON-LD 1.1 Spec](https://www.w3.org/TR/json-ld11/)
- [DCAT Vocabulary](https://www.w3.org/TR/vocab-dcat-3/)
- [DCT (Dublin Core Terms)](https://purl.org/dc/terms/)

### WordPress/PHP
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Security](https://developer.wordpress.org/plugins/security/)
- [PHP 8.1+ Type Hints](https://www.php.net/manual/en/language.types.type-hints.php)

### Libraries
- [Carbon Fields Docs](https://carbonfields.net/)
- [PHPUnit Documentation](https://phpunit.de/)
- [WP-Mock for Mocking](https://github.com/10up/wp_mock)
- [WPCS Standards](https://github.com/WordPress/WordPress-Coding-Standards)

### Project Files
- `README.md` — User-facing documentation (DE)
- `CHANGELOG.md` — Complete version history & features
- `LICENSE` — GPL-2.0-or-later
- `composer.json` — PHP dependencies
- `.github/workflows/ci.yml` — GitHub Actions CI/CD

---

## 🎓 Best Practices

### Code Quality
1. **Always run PHPCS before committing**
   ```bash
   ./vendor/bin/phpcs --standard=config/phpcs.xml
   ```

2. **All public methods must have type hints**
   ```php
   public static function my_method( string $param ): string { ... }
   ```

3. **Use strict types at file top**
   ```php
   declare(strict_types=1);
   ```

4. **Avoid deeply nested conditionals** — use early returns

5. **Document complex logic** — single-line comments explaining "why"

### Security
1. **Always sanitize user input** — `sanitize_text_field()`, `absint()`
2. **Always escape output** — `esc_html()`, `esc_attr()`, `esc_url_raw()`
3. **Verify nonces** — `wp_verify_nonce()` for form submissions
4. **Check capabilities** — `current_user_can()` for all write operations
5. **No serialize() user data** — only for cache keys

### Testing
1. **Write tests for new features** — Aim for >80% coverage
2. **Mock all WordPress functions** — WP_Mock does the heavy lifting
3. **Test edge cases** — null values, empty strings, large datasets
4. **Run full test suite before pushing** — alle Tests müssen grün sein

### Performance
1. **Never call `odw_build_dataset_jsonld()` in loops** — expensive, cache it
2. **Use transients for expensive operations** — 5 min TTL is standard
3. **Index meta_key queries** — add `meta_query` indexes if searching large datasets
4. **Pre-compute file metadata** — store file size & format, don't calculate at render

---

## 📞 Support & Contact

- **GitHub Issues**: Bug reports & feature requests
- **README.md**: User documentation
- **Code Comments**: Complex logic explanation
- **This CLAUDE.md**: Developer guidance

---

**Zuletzt aktualisiert**: Version 2.39.1 (August 2026)
**Autor**: Open Data Wizard Team (nozilla)
**License**: GPL-2.0-or-later
