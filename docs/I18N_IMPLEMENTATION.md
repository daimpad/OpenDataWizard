# Internationalization (i18n) Implementation Plan für Open Data Wizard

**Feature:** Plugin-Übersetzung in Englisch (English localization)  
**Version:** v1.10.0+  
**Geschätzter Aufwand:** 5-7 Stunden  
**Status:** Geplant (nicht implementiert)  
**Erstellt:** 2026-04-22

---

## Inhaltsverzeichnis

1. [Überblick](#überblick)
2. [Analyse Ergebnisse](#analyse-ergebnisse)
3. [Phase 1: Setup & POT-Generierung](#phase-1-setup--pot-generierung)
4. [Phase 2: Englische Übersetzung](#phase-2-englische-übersetzung)
5. [Phase 3: Kompilierung & Testing](#phase-3-kompilierung--testing)
6. [Phase 4: Dokumentation & Automation](#phase-4-dokumentation--automation)
7. [Checkliste](#checkliste)

---

## Überblick

Das Open Data Wizard Plugin soll in mehreren Sprachen verfügbar sein. Zuerst wird die englische Übersetzung implementiert, dann können weitere Sprachen folgen.

### Aktueller Status

- ✅ **Text-Domain richtig**: `open-data-wizard` (überall konsistent verwendet)
- ✅ **i18n Funktionen**: Alle Strings sind mit `__()`, `_e()`, `_x()`, `esc_html__()` etc. wrappingiert
- ✅ **load_plugin_textdomain()**: Bereits in `open-data-wizard.php` (Zeile 155-162) konfiguriert
- ✅ **Languages Verzeichnis**: `/languages/` existiert (aktuell leer)
- ⚠️ **Translation Files**: .pot, .po, .mo Dateien fehlen

### Translatable Strings

**Analyseergebnis: 219+ unique translatable Strings**

**Verteilung nach Datei:**
| Datei | Strings | Priorität |
|-------|---------|-----------|
| class-fields.php | ~45 | Hoch (User-facing Form Labels) |
| class-post-types.php | ~40 | Hoch (CPT Labels) |
| class-settings.php | ~35 | Hoch (Settings UI) |
| class-admin.php | ~25 | Hoch (Admin UI) |
| class-quality.php | ~20 | Mittel (Quality Indicators) |
| class-validation.php | ~15 | Mittel (Error Messages) |
| class-rest-api.php | ~12 | Mittel (API Responses) |
| class-shortcode.php | ~10 | Mittel (Frontend) |
| class-cli.php | ~8 | Niedrig (CLI) |
| open-data-wizard.php | ~2 | Niedrig (Plugin Header) |
| class-webhooks.php | ~4 | Niedrig (Webhook Messages) |

---

## Analyse Ergebnisse

### String Scanning Details

Alle gescannten Strings sind korrekt mit WordPress i18n Funktionen wrappingiert:

✅ **Korrekte Pattern gefunden:**
```php
// Standard Translation
__( 'Datensätze', 'open-data-wizard' )
_e( 'Datensatz bearbeiten', 'open-data-wizard' )

// Mit Kontext (Disambiguation)
_x( 'Datensätze', 'Post Type General Name', 'open-data-wizard' )

// Mit Escaping
esc_html__( 'Beschreibung', 'open-data-wizard' )
esc_attr__( 'Titel', 'open-data-wizard' )

// Mit sprintf/Pluralisierung
sprintf( __( 'Recalculated quality scores for %d dataset(s).', 'open-data-wizard' ), $count )
_n( 'Dataset', 'Datasets', $count, 'open-data-wizard' )
```

❌ **Nicht zu übersetzen (korrekt ausgeschlossen):**
- Namespace URIs: `dcat:`, `dct:`, `foaf:`, etc.
- Meta Keys: `_odw_*`, `odw_dataset`
- Code Kommentare
- Regex Patterns
- Debug-Ausgaben

---

## Phase 1: Setup & POT-Generierung

### 1.1 Systemvoraussetzungen

**Option A: Gettext Tools (Empfohlen)**
```bash
# Installation
apt-get install gettext  # Debian/Ubuntu
brew install gettext     # macOS

# Verfügbare Tools nach Installation
xgettext    # String-Extraktion aus Quellcode
msgmerge    # POT mit existierenden PO mergen
msgfmt      # PO → MO kompilieren
```

**Option B: WP-CLI (Alternative)**
```bash
# Funktioniert mit WordPress Installation
wp i18n make-pot . languages/open-data-wizard.pot --domain=open-data-wizard
```

**Option C: PHP-Script (Fallback, kein System-Tool nötig)**
- Custom Regex-basierter Parser
- Länger, aber portable

**Empfehlung für Projekt:** Option A (Gettext Tools) verwenden

### 1.2 Verzeichnisstruktur vorbereiten

**Zielstruktur nach Implementierung:**

```
/languages/
├── open-data-wizard.pot              (Template mit allen Strings)
├── open-data-wizard-en_US.po         (English Quelle)
├── open-data-wizard-en_US.mo         (English kompiliert, Binary)
│
├── [ZUKÜNFTIG]
├── open-data-wizard-de_DE.po         (German, falls nötig)
├── open-data-wizard-de_DE.mo
├── open-data-wizard-fr_FR.po         (French, falls nötig)
└── open-data-wizard-fr_FR.mo
```

### 1.3 POT-Generierung

#### Schritt A: Mit Gettext Tools

```bash
# Kommando ausführen im Plugin-Root-Verzeichnis
xgettext \
    --language=PHP \
    --keyword=__ \
    --keyword=_e \
    --keyword=_x:1,2c \
    --keyword=_n:1,2 \
    --keyword=_nx:1,2,4c \
    --keyword=esc_html__ \
    --keyword=esc_attr__ \
    --keyword=esc_attr_e \
    --keyword=esc_html_e \
    --add-comments=translators \
    --sort-output \
    --package-name="Open Data Wizard" \
    --msgid-bugs-address="https://github.com/daimpad/OpenDataWizard/issues" \
    -o languages/open-data-wizard.pot \
    $(find . -name "*.php" ! -path "./vendor/*" ! -path "./tests/*")
```

**Was dieses Kommando macht:**
- `--language=PHP` — Parser für PHP-Syntax
- `--keyword=__` — Findet alle `__('string')` Aufrufe
- `--keyword=_x:1,2c` — Findet `_x()` mit Kontext (2. Parameter)
- `--keyword=_n:1,2` — Findet `_n()` für Pluralisierung (singular + plural)
- `--add-comments=translators` — Inkludiert `// translators:` Kommentare
- `--sort-output` — Sortiert Einträge alphabetisch
- `-o` — Ausgabedatei

#### Ergebnis: `open-data-wizard.pot`

**Dateiformat:**
```
# Open Data Wizard Translations
# Copyright (C) 2026 Datenatlas Zivilgesellschaft
# This file is distributed under the same license as the Open Data Wizard package.
#
msgid ""
msgstr ""
"Project-Id-Version: Open Data Wizard 1.9.0\n"
"Report-Msgid-Bugs-To: https://github.com/daimpad/OpenDataWizard/issues\n"
"POT-Creation-Date: 2026-04-22 10:30+0000\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"
"Language-Team: LANGUAGE <LL@li.org>\n"
"Language: \n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=2; plural=n != 1;\n"

#: includes/class-post-types.php:41
msgid "Datensätze"
msgstr ""

#: includes/class-post-types.php:42
msgid "Datensatz"
msgstr ""

#: includes/class-post-types.php:44
msgid "Open Data Wizard"
msgstr ""

... (216 weitere Einträge)
```

**Validierung der POT-Datei:**

```bash
# Syntax-Check
msgfmt -c languages/open-data-wizard.pot

# Zeilenanzahl prüfen (sollte ~400-500 Zeilen sein)
wc -l languages/open-data-wizard.pot

# Einträge zählen (sollte ~219 msgid-Blöcke sein)
grep -c '^msgid ' languages/open-data-wizard.pot
```

### 1.4 Qualitätsprüfung POT

**Checkliste für generierte POT:**

- [ ] Keine Duplicate msgid-Einträge (`grep '^msgid ' | sort | uniq -d` sollte leer sein)
- [ ] Alle Strings haben 'open-data-wizard' Text Domain (nicht 'default' o.ä.)
- [ ] Header mit korrekter Charset (UTF-8)
- [ ] Quellcode-Referenzen (`#: includes/...`) sind aktuell
- [ ] Pluralisierungen korrekt geflaggt (msgid_plural vorhanden)
- [ ] Keine Debug-Strings oder Code-Kommentare
- [ ] Filesize ~15-25 KB (normalerweise)

---

## Phase 2: Englische Übersetzung

### 2.1 English Translation File (.po) erstellen

**Vorgang:**
1. .pot Datei kopieren → en_US.po
2. Header aktualisieren (Sprache, Translator-Info)
3. Alle 219 Strings von Deutsch → Englisch übersetzen

**Datei:** `/languages/open-data-wizard-en_US.po`

#### Header anpassen

```po
# English translation for Open Data Wizard
# Copyright (C) 2026 Datenatlas Zivilgesellschaft
# This file is distributed under the same license as the Open Data Wizard package.
#
msgid ""
msgstr ""
"Project-Id-Version: Open Data Wizard 1.9.0\n"
"Report-Msgid-Bugs-To: https://github.com/daimpad/OpenDataWizard/issues\n"
"POT-Creation-Date: 2026-04-22 10:30+0000\n"
"PO-Revision-Date: 2026-04-22 10:30+0000\n"
"Last-Translator: Open Data Wizard Contributors\n"
"Language-Team: English\n"
"Language: en_US\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=2; plural=n != 1;\n"
"X-Generator: Poedit 3.0\n"

#: includes/class-post-types.php:41
msgid "Datensätze"
msgstr "Datasets"

#: includes/class-post-types.php:42
msgid "Datensatz"
msgstr "Dataset"

... (216 weitere Übersetzungen)
```

### 2.2 Übersetzungsliste (219 Strings)

**Zu übersetzende Strings (Priorität nach Nutzer-Sichtbarkeit):**

#### CPT & Admin Labels (Hoch-Priorität)

| Deutsch | English | Datei | Notizen |
|---------|---------|-------|---------|
| Datensätze | Datasets | class-post-types.php | Post Type General Name |
| Datensatz | Dataset | class-post-types.php | Post Type Singular Name |
| Open Data Wizard | Open Data Wizard | class-post-types.php | Menu Name (bleibt gleich) |
| Neuen Datensatz anlegen | Create New Dataset | class-post-types.php | Add New Item |
| Alle Datensätze | All Datasets | class-post-types.php | All Items |
| Datensätze suchen | Search Datasets | class-post-types.php | Search Items |
| Datensatz bearbeiten | Edit Dataset | class-post-types.php | Edit Item |
| Datensatz ansehen | View Dataset | class-post-types.php | View Item |
| Keine Datensätze gefunden | No datasets found | class-post-types.php | Not Found |
| DCAT-AP 3.0 konforme Datensatz-Metadaten | DCAT-AP 3.0 compliant dataset metadata | class-post-types.php | Description |

#### Settings & Form Labels (Hoch-Priorität)

| Deutsch | English | Datei | Kontext |
|---------|---------|-------|---------|
| Pflichtangaben | Mandatory Fields | class-fields.php | Tab 1 Title |
| Optionale Angaben | Optional Fields | class-fields.php | Tab 2 Title |
| Distribution | Distribution | class-fields.php | Tab 3 Title |
| Erweiterte Angaben | Advanced Fields | class-fields.php | Tab 4 Title |
| Vorschau | Preview | class-fields.php | Tab 5 Title |
| Titel | Title | class-fields.php | dct:title |
| Beschreibung | Description | class-fields.php | dct:description |
| Herausgeber | Publisher | class-fields.php | dct:publisher |
| Lizenz | License | class-fields.php | dct:license |
| Sprache | Language | class-fields.php | dct:language |
| Schlagworte | Keywords | class-fields.php | dcat:keyword |
| Thema | Theme | class-fields.php | dcat:theme |
| Datum | Date | class-fields.php | temporal |
| Zugriffs-URL | Access URL | class-fields.php | dcat:accessURL |
| Format | Format | class-fields.php | dct:format |
| Dateigröße | File Size | class-fields.php | dcat:byteSize |
| ... (weitere ~30) | ... | ... | ... |

#### Validierungs- & Error Messages (Mittel-Priorität)

| Deutsch | English | Datei | Kontext |
|---------|---------|-------|---------|
| Pflichtfelder fehlen | Required fields missing | class-validation.php | Error Notice |
| Bitte füllen Sie alle Pflichtfelder aus | Please fill in all required fields | class-validation.php | Help Text |
| Datensatz konnte nicht gespeichert werden | Dataset could not be saved | class-validation.php | Error |

#### Quality & Admin (Mittel-Priorität)

| Deutsch | English | Datei | Kontext |
|---------|---------|-------|---------|
| Qualitätsscore | Quality Score | class-quality.php | Badge Label |
| Sehr gut | Excellent | class-quality.php | Quality Level |
| Befriedigend | Satisfactory | class-quality.php | Quality Level |
| Verbesserungsbedürftig | Needs Improvement | class-quality.php | Quality Level |

#### REST API & Hooks (Niedrig-Priorität)

| Deutsch | English | Datei | Kontext |
|---------|---------|-------|---------|
| Katalog | Catalog | class-rest-api.php | API Response |
| Veröffentlichte Datasets | Published Datasets | class-rest-api.php | Filter |

#### Webhooks (Niedrig-Priorität)

| Deutsch | English | Datei | Kontext |
|---------|---------|-------|---------|
| Webhooks | Webhooks | class-settings.php | Settings Section |
| Webhook URL | Webhook URL | class-settings.php | Settings Field |
| API Token | API Token | class-settings.php | Settings Field |
| Webhook gesendet | Webhook sent | class-webhooks.php | Log Entry |

**Gesamtzahl: ~219 einzigartige Strings**

### 2.3 Übersetzungs-Guidelines

#### Allgemeine Regeln

1. **Terminologie konsistent halten**
   - "Datensatz" → immer "Dataset" (nicht "Data Set" oder "Data Record")
   - "Herausgeber" → "Publisher" (nicht "Editor" = verwirrend)
   - "Lizenz" → "License" (US Standard Schreibweise)

2. **Proper Nouns nicht übersetzen**
   - "DCAT-AP 3.0" bleibt gleich
   - "Civora", "Piveau" → bleibt gleich
   - "JSON-LD" → bleibt gleich

3. **Markup & HTML bewahren**
   - `<br/>`, `<code>`, `<strong>` bleiben in msgstr
   - Beispiel: `"Text mit <strong>Betonung</strong>"` → `"Text with <strong>emphasis</strong>"`

4. **Pluralisierung korrekt handhaben**
   - Deutsch: `_n( 'Datensatz', 'Datensätze', $count )`
   - Englisch: `msgid` = "Dataset", `msgid_plural` = "Datasets"
   - Im .po File: msgstr[0] und msgstr[1]

5. **Variablen und Platzhalter bewahren**
   - `%d` (Zahlen) und `%s` (Strings) müssen erhalten bleiben
   - Beispiel: `"Recalculated quality scores for %d dataset(s)."` bleibt mit `%d`

6. **Kontex-Strings prüfen**
   - `_x('Datensätze', 'Post Type General Name', 'open-data-wizard')`
   - msgctxt muss vorhanden sein in .po Datei

#### WordPress Terminologie (Glossary)

Benutze diese englischen Begriffe für Konsistenz mit WordPress Standard:

| Deutsch | English (WordPress Standard) |
|---------|---|
| Datensatz/Post | Dataset/Post |
| Entwurf | Draft |
| Veröffentlicht | Published |
| Privatpost | Private |
| Gelöscht | Trash |
| Veröffentlichen | Publish |
| Speichern | Save |
| Bearbeiten | Edit |
| Löschen | Delete |
| Vorschau | Preview |
| Ansicht | View |

---

## Phase 3: Kompilierung & Testing

### 3.1 PO → MO Kompilierung

**Kommando:**
```bash
msgfmt languages/open-data-wizard-en_US.po -o languages/open-data-wizard-en_US.mo
```

**Validierung:**
```bash
# Syntax-Check vor Kompilierung
msgfmt -c languages/open-data-wizard-en_US.po

# MO File wurde erstellt (sollte ~50-100 KB sein)
ls -lh languages/open-data-wizard-en_US.mo

# Binary Format prüfen
file languages/open-data-wizard-en_US.mo
# Erwartete Ausgabe: "GNU gettext message catalogue"
```

### 3.2 Testing in WordPress

#### Test-Setup

**1. WordPress Config für English setzen:**
```php
// In wp-config.php oder mu-plugin:
define( 'WPLANG', 'en_US' );
```

**2. Plugin aktivieren und Settings prüfen:**
- Admin Menu sollte englisch sein: "Open Data Wizard" → "All Datasets"
- CPT Labels: "Create New Dataset", "Edit Dataset", etc.

#### Spot-Check Locations

- [ ] **Admin Menu** → "Open Data Wizard" (sollte Englisch sein)
- [ ] **CPT Labels** → "All Datasets", "Edit Dataset", etc.
- [ ] **Settings Page** → "Datasets" → "Settings"
  - [ ] Section Headers
  - [ ] Field Labels
  - [ ] Help Text
- [ ] **Edit Screen**
  - [ ] Tab Titles (Mandatory Fields, Optional Fields, etc.)
  - [ ] Field Labels
  - [ ] Help Text
  - [ ] Meta-Box Titles
- [ ] **Admin List View**
  - [ ] Column Headers
  - [ ] Filter Dropdowns
- [ ] **Validation Messages**
  - [ ] Try to publish with missing required field
  - [ ] Error notice should be in English
- [ ] **Shortcode Output** (Frontend)
  - [ ] Button Labels
  - [ ] Status Text

#### Fehler-Handling

**Wenn Englisch nicht angezeigt wird:**

1. **MO-Datei korrekt kompiliert?**
   ```bash
   strings languages/open-data-wizard-en_US.mo | head -20
   # Sollte englische Strings enthalten
   ```

2. **Dateiname korrekt?**
   - Muss `open-data-wizard-en_US.po` und `open-data-wizard-en_US.mo` sein
   - Text Domain in Code muss `open-data-wizard` sein

3. **WordPress Locale erkannt?**
   ```php
   // In Admin, Test mit:
   echo get_locale(); // Sollte 'en_US' sein
   ```

4. **Plugin Load-Order?**
   - `load_plugin_textdomain()` wird auf `init` Hook (Prio default) aufgerufen
   - Sollte vor anderen i18n Operationen laufen

---

## Phase 4: Dokumentation & Automation

### 4.1 Extraction Script erstellen

**Datei:** `/scripts/generate-pot.sh`

```bash
#!/bin/bash
# Generate POT translation template file
# Usage: ./scripts/generate-pot.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
POT_FILE="$PLUGIN_DIR/languages/open-data-wizard.pot"

# Check if xgettext is installed
if ! command -v xgettext &> /dev/null; then
    echo "Error: xgettext not found"
    echo "Install with: apt-get install gettext (Debian/Ubuntu) or brew install gettext (macOS)"
    exit 1
fi

echo "Generating POT file..."

xgetect \
    --language=PHP \
    --keyword=__ \
    --keyword=_e \
    --keyword=_x:1,2c \
    --keyword=_n:1,2 \
    --keyword=_nx:1,2,4c \
    --keyword=esc_html__ \
    --keyword=esc_attr__ \
    --keyword=esc_attr_e \
    --keyword=esc_html_e \
    --add-comments=translators \
    --sort-output \
    --package-name="Open Data Wizard" \
    --msgid-bugs-address="https://github.com/daimpad/OpenDataWizard/issues" \
    -o "$POT_FILE" \
    $(find "$PLUGIN_DIR" -name "*.php" ! -path "*/vendor/*" ! -path "*/tests/*")

echo "✓ POT file generated: $POT_FILE"

# Validation
echo "Validating..."
msgfmt -c "$POT_FILE"
echo "✓ POT file is valid"

# Count strings
COUNT=$(grep -c '^msgid ' "$POT_FILE" || echo 0)
echo "✓ Found $COUNT translatable strings"
```

**Nutzung:**
```bash
chmod +x scripts/generate-pot.sh
./scripts/generate-pot.sh
```

### 4.2 Übersetzungs-Contributions Guide

**Datei:** `/docs/TRANSLATIONS.md`

```markdown
# Translation Guide for Open Data Wizard

## Adding Translations

### For English (en_US)

1. **Generate POT file** (Template)
   ```bash
   ./scripts/generate-pot.sh
   ```

2. **Copy template to English translation**
   ```bash
   cp languages/open-data-wizard.pot languages/open-data-wizard-en_US.po
   ```

3. **Edit with Poedit or text editor**
   - Update header with language "en_US"
   - Translate all German msgid → English msgstr
   - Save file

4. **Compile to binary format**
   ```bash
   msgfmt languages/open-data-wizard-en_US.po -o languages/open-data-wizard-en_US.mo
   ```

5. **Test in WordPress**
   - Set WPLANG = 'en_US' in wp-config.php
   - Verify Admin UI, Settings, Messages are in English

### For Other Languages (e.g., French)

```bash
# Copy template
cp languages/open-data-wizard.pot languages/open-data-wizard-fr_FR.po

# Edit fr_FR.po with your translations
# nano languages/open-data-wizard-fr_FR.po

# Compile
msgfmt languages/open-data-wizard-fr_FR.po -o languages/open-data-wizard-fr_FR.mo

# Test by setting WPLANG = 'fr_FR'
```

## Tools

- **Poedit** (desktop, GUI): https://poedit.net/
- **Weblate** (online, collaborative): https://weblate.org/
- **Crowdin** (professional, GitHub sync): https://crowdin.com/

## Adding New Translatable Strings

When adding new strings to the code:

1. Always wrap in translation function:
   ```php
   __( 'String to translate', 'open-data-wizard' )
   _e( 'String to output', 'open-data-wizard' )
   esc_html__( 'Safe string', 'open-data-wizard' )
   ```

2. Use context for disambiguation:
   ```php
   _x( 'Datasets', 'Post Type General Name', 'open-data-wizard' )
   ```

3. Regenerate POT file:
   ```bash
   ./scripts/generate-pot.sh
   ```

4. Update all .po files:
   ```bash
   msgmerge -U languages/open-data-wizard-en_US.po languages/open-data-wizard.pot
   ```

5. Retranslate new strings in .po file

6. Recompile .mo file

## Pluralization

For strings with plural forms:

```php
ngettext(
    'One dataset',
    '%d datasets',
    $count,
    'open-data-wizard'
)
```

In .po file, this becomes:
```po
#: file.php:line
msgid "One dataset"
msgid_plural "%d datasets"
msgstr[0] "Ein Datensatz"
msgstr[1] "%d Datensätze"
```
```

### 4.3 GitHub Actions Automation (Optional)

**Datei:** `/.github/workflows/generate-pot.yml`

```yaml
name: Generate POT Translation Template

on:
  push:
    branches: [ main ]
    paths:
      - '**.php'
      - '.github/workflows/generate-pot.yml'

jobs:
  pot:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Install gettext
        run: sudo apt-get install -y gettext
      
      - name: Generate POT
        run: ./scripts/generate-pot.sh
      
      - name: Commit POT if changed
        uses: stefanzweifel/git-auto-commit-action@v4
        with:
          commit_message: "chore: regenerate POT translation template"
          file_pattern: languages/open-data-wizard.pot
          commit_options: '--no-verify'
```

---

## Implementation Checkliste

### Vorbereitung

- [ ] Gettext Tools installiert (`apt-get install gettext`)
- [ ] Git-Branch verifiziert: `claude/wordpress-open-data-wizard-MmOEL`
- [ ] Keine ungespeicherten Änderungen im Repository

### Phase 1: POT-Generierung

- [ ] `./scripts/generate-pot.sh` ausgeführt
- [ ] `languages/open-data-wizard.pot` erstellt (~15-25 KB)
- [ ] POT mit `msgfmt -c` validiert ✓
- [ ] Keine Duplicate msgid Einträge
- [ ] ~219 msgid Blöcke vorhanden
- [ ] Header mit korrekter Charset (UTF-8)

### Phase 2: English Translation

- [ ] `languages/open-data-wizard-en_US.po` erstellt
- [ ] Header angepasst: Language: en_US
- [ ] Alle 219 Strings ins Englische übersetzt
  - [ ] CPT Labels (Datasets, Dataset, Create New Dataset, etc.)
  - [ ] Form Field Labels (Title, Description, License, etc.)
  - [ ] Settings Page Labels
  - [ ] Validation Messages
  - [ ] Quality Indicators
  - [ ] API Responses
  - [ ] Webhook Messages
  - [ ] CLI Messages
- [ ] Pluralisierungen korrekt (msgid_plural)
- [ ] HTML/Markup bewahrt
- [ ] Variablen %d, %s erhalten geblieben
- [ ] WordPress Terminologie konsistent

### Phase 3: Kompilierung & Testing

- [ ] `msgfmt open-data-wizard-en_US.po -o open-data-wizard-en_US.mo` ausgeführt
- [ ] MO-Datei erstellt und korrekt formatiert (`file` Kommando)
- [ ] WordPress WPLANG auf 'en_US' gesetzt
- [ ] Admin Menu englisch: "Open Data Wizard" angezeigt
- [ ] CPT Labels englisch: "All Datasets", "Edit Dataset"
- [ ] Settings Page englisch
- [ ] Validation Messages englisch
- [ ] Shortcode Output englisch
- [ ] Keine Fallback-Strings in Deutsch sichtbar

### Phase 4: Dokumentation

- [ ] `/docs/TRANSLATIONS.md` erstellt
- [ ] `/scripts/generate-pot.sh` erstellt und ausführbar
- [ ] `/.github/workflows/generate-pot.yml` (optional) erstellt
- [ ] README.md mit i18n-Hinweis aktualisiert
- [ ] CLAUDE.md mit Translation-Info aktualisiert
- [ ] CHANGELOG.md mit v1.10.0 Entry

### Finalisierung

- [ ] Alle .pot, .po, .mo Dateien in Git committed
- [ ] Scripts sind ausführbar (chmod +x)
- [ ] Git push zu `claude/wordpress-open-data-wizard-MmOEL`
- [ ] Keine ungespeicherten Dateien
- [ ] Tests passing: `composer test`
- [ ] PHPCS clean: `composer phpcs`

---

## Geschätzter Zeitaufwand

| Phase | Aufgabe | Dauer |
|-------|---------|-------|
| 1 | Gettext Setup + POT-Generierung | 1-2h |
| 2 | English Übersetzung (219 Strings) | 2-3h |
| 3 | Kompilierung + Testing | 30-45 min |
| 4 | Dokumentation + Scripts | 1h |
| — | Debugging & Feinschliff | 30 min |
| **Total** | | **5-7h** |

---

## Kritische Dateien

**Neu zu erstellen:**
- `/languages/open-data-wizard.pot`
- `/languages/open-data-wizard-en_US.po`
- `/languages/open-data-wizard-en_US.mo`
- `/scripts/generate-pot.sh`
- `/docs/TRANSLATIONS.md`

**Zu modifizieren (optional):**
- `README.md` — i18n Section hinzufügen
- `CLAUDE.md` — Translation Workflow dokumentieren
- `CHANGELOG.md` — v1.10.0 Entry mit i18n Feature
- `.github/workflows/generate-pot.yml` — GitHub Actions (optional)

**Zu prüfen (keine Änderungen nötig):**
- `open-data-wizard.php` — load_plugin_textdomain() bereits korrekt
- `includes/*.php` — Alle Strings bereits mit i18n Functions wrappingiert

---

## Notizen für zukünftige Implementierung

1. **Locale Loading**
   - WordPress lädt automatisch `<text-domain>-<locale>.mo` Dateien
   - Fallback auf Original-Strings wenn .mo nicht vorhanden
   - Locale wird via WPLANG oder Site Settings bestimmt

2. **Pluralization**
   - English hat nplurals=2 (singular/plural)
   - Andere Sprachen können mehr Forms haben (z.B. Russisch = 3)
   - Plural-Forms im .po Header MUSS korrekt sein

3. **Performance**
   - .mo Dateien sind binär optimiert (schneller als .po)
   - Caching auf WordPress-Ebene vorhanden
   - Keine Performance-Probleme zu erwarten

4. **Zukunfts-Sprachen**
   - Nach English einfach weitere .po/.mo Datei-Paare hinzufügen
   - Naming-Konvention: `open-data-wizard-<locale>.po/mo`
   - Locale-Codes: en_US, de_DE, fr_FR, es_ES, etc.

5. **Crowdin/Weblate Integration**
   - Falls später automatisierte Übersetzungen gewünscht
   - Können direkt mit GitHub Repo sync
   - Auto-Generate .po von .pot und sync zurück

---

**Status: Bereit zur Implementierung**  
**Letzte Überprüfung:** 2026-04-22  
**Nächster Schritt:** Implementierung starten (Phase 1 - POT Generierung)
