<img width="6408" height="2002" alt="Vector-Logo-of-Open-Data-Wizard_white" src="https://github.com/user-attachments/assets/a6296bc2-2952-4c27-84c9-a6d531886335" />


# Open Data Wizard 🧙 

![Lizenz](https://img.shields.io/github/license/daimpad/OpenDataWizard?style=flat-square&color=blue&label=Lizenz)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-8892BF?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-compatible-21759B?style=flat-square&logo=wordpress&logoColor=white)
![DCAT-AP](https://img.shields.io/badge/DCAT--AP-3.0-brightgreen?style=flat-square)
![Version](https://img.shields.io/badge/Version-2.3.0-brightgreen?style=flat-square)
![PRs Welcome](https://img.shields.io/badge/PRs-willkommen-brightgreen?style=flat-square)

**Ein WordPress-Plugin zur einfachen Veröffentlichung offener Daten nach DCAT-AP 3.0**

Open Data Wizard ermöglicht es Organisationen und Einzelpersonen, Datensätze direkt in WordPress zu beschreiben und als maschinenlesbare, standardkonforme Metadaten bereitzustellen — ohne technische Vorkenntnisse, ohne externe Plattformabhängigkeit.

---

## Das Problem

Offene Daten zu veröffentlichen ist schwieriger als es sein müsste. Wer Daten auf einer Open-Data-Plattform einstellen will, landet schnell vor komplexen Formularen, unbekannten Fachbegriffen oder muss sich auf eine externe Infrastruktur verlassen, über die keine Kontrolle besteht.

Dabei besitzen viele Organisationen bereits eine WordPress-Website und damit eine Infrastruktur, die sie kennen und die sie kontrollieren.

**Hier kann der Open Data Wizard helfen.**

---

## Die Idee

Das Plugin bringt einen geführten Metadaten-Wizard ins WordPress-Backend. Organisationen beschreiben ihre Datensätze dort, wo sie ohnehin arbeiten. Das Plugin generiert daraus eine maschinenlesbare Beschreibung nach dem internationalen Standard **DCAT-AP 3.0** und stellt sie unter einer persistenten URL bereit.

Open-Data-Plattformen können diese URL als Harvest-Quelle einbinden und die Metadaten automatisch einsammeln. **Die Daten bleiben bei der Organisation. Die Plattform kommt zu ihr.**

---

## Was ist DCAT-AP?

DCAT-AP (Data Catalog Vocabulary — Application Profile) ist ein europäischer Standard zur Beschreibung von Datensätzen und Datenkatalogen. Er definiert, welche Angaben ein Datensatz braucht, damit er von Plattformen, Suchmaschinen und Anwendungen einheitlich gelesen und verarbeitet werden kann — Titel, Beschreibung, Lizenz, Format, Herausgeber und mehr.

Open Data Wizard implementiert **DCAT-AP 3.0** und erzeugt valide **JSON-LD**-Ausgaben.

---

## Für wen ist das Plugin?

- **Vereine, NGOs und gemeinnützige Organisationen**, die Daten transparent zugänglich machen möchten
- **Forschungseinrichtungen und Bildungsträger**, die Daten unter offener Lizenz veröffentlichen wollen
- **Kommunen und öffentliche Einrichtungen** mit WordPress-Infrastruktur
- **Alle**, die offene Daten standardkonform veröffentlichen wollen — ohne Programmierkenntnisse

---

## Funktionsübersicht

### 🗂 Datensätze verwalten
Eigener Bereich im WordPress-Backend mit Übersicht, Filterung und Statusverwaltung (Entwurf / Veröffentlicht).

### 😊 Benutzerfreundliche Formularsprache
Das Wizard-Formular wurde vollständig überarbeitet, um es auch ohne DCAT-AP-Kenntnisse intuitiv zu machen:
- **Klare Fragen statt technischer Begriffe:** Statt „Herausgebende Organisation (dct:publisher)" fragt das Plugin: „Wer gibt diese Daten heraus?"
- **Hilfreiche Beispiele:** Jedes Feld hat konkrete, praxisnahe Beispiele
- **Ursprüngliche Labels in Hilfetexten:** DCAT-AP Bezeichnungen und technische Details bleiben in den Hilfetexten sichtbar
- **Validierungsmeldungen in Klartext:** Fehler zeigen verständliche Feldnamen statt technischer Ausdrücke

### 🧭 Geführter Wizard
Fünf-Tab-Assistent mit Pflichtfeldprüfung und praktischen Beispielen:

1. **Grundlegende Informationen** — „Wer gibt diese Daten heraus?", „Worum geht es in diesem Datensatz?", „In welche Kategorie gehört dieser Datensatz?"
2. **Inhaltliche Angaben** — „In welcher Sprache sind die Daten?", „Mit welchen Stichworten finde ich diese Daten?", Zeitangaben, CESSDA-Themenklassifikation
3. **Datenbereitstellung** — Distributionen (wiederholbar): Zugriffs-URL, Format, Dateigröße, **Lizenz (Pflichtfeld pro Distribution)**, Zuschreibungstext
4. **Erweiterte Angaben** — Projektseite, Aktualisierungsfrequenz, geografische und zeitliche Abdeckung, Kontaktinformationen
5. **Vorschau** — generiertes JSON-LD live einsehen

### 🏷 Lizenz-Auswahl
- Vordefinierte Auswahlliste mit ausgeschriebenen Lizenznamen (z. B. „CC BY 4.0 — Namensnennung")
- Unter der Auswahl erscheint eine allgemeinverständliche Erklärung, was die gewählte Lizenz erlaubt
- Option „Sonstige" öffnet ein Freitextfeld mit Auto-Suggest aus `config/licenses.txt`
- Lizenz ist **Pflichtfeld pro Distribution** (nicht am Datensatz selbst)

### 🎓 CESSDA-Themenklassifikation
Auswahlfeld aus der CESSDA Topic Classification 4.2.3 (95 deutsche Konzepte, SKOS/RDF, 24h Cache). Im Feld wird das sprechende Label (z. B. „Bildung") angezeigt; die zugehörige URI wird im Hintergrund gespeichert und als Hinweis eingeblendet.

### 🗺 Geografische Region (GeoNames)
Kuratierte Auswahlliste (Deutschland, alle 16 Bundesländer, größere Städte). Die Auswahl wird im JSON-LD automatisch mit der passenden GeoNames-URI verknüpft; Freitext und eigene URIs bleiben möglich.

### 📥 Batch-Import (CSV & JSON)
```
Datensätze → Batch-Import
```
Importiere mehrere Datensätze auf einmal aus CSV oder JSON Dateien. Der Import-Wizard zeigt eine Vorschau aller gültigen Zeilen, markiert Fehler, und lässt dich auswählen, welche Datensätze importiert werden. Alle importierten Datensätze werden als **Entwürfe** erstellt — zur Bearbeitung vor Publishing bereit.

**Unterstützte Formate:**
- **CSV**: Spaltenköpfe = Feldnamen (title, publisher, description, access_url, license, theme, language, format, issued, keywords, byte_size, attribution)
- **JSON**: Array von Objekten oder einzelnes Objekt mit gleichen Feldnamen

**Pflichtfelder beim Import:**
- `title` — Datensatztitel
- `publisher` — Herausgebende Organisation
- `description` — Beschreibung (Mindestens 10 Zeichen)
- `access_url` — Download-URL (muss mit http/https beginnen)
- `license` — Lizenz (short code wie `cc-by` oder volle URI)

**Optionale Felder:**
- `theme` — Datenkategorie (z.B. SOCI, ECON, EDUC)
- `language` — Sprache (z.B. de, en)
- `format` — Dateiformat (z.B. CSV, JSON, PDF)
- `issued` — Veröffentlichungsdatum
- `keywords` — Schlagworte (komma-getrennt)
- `byte_size` — Dateigröße in Bytes (nur ganze Zahl; abweichende Werte werden als Fehler markiert)
- `attribution` — Namensnennungstext

**Gut zu wissen:**
- **Excel-kompatibel:** UTF-8-CSVs mit BOM (Excel-Standardexport) werden korrekt eingelesen.
- **Limit:** Bis zu **2.000 Datensätze** pro Import (Schutz vor Speicher-/Timeout-Problemen).
- **Sicherheit:** Zell-Inhalte, die als Tabellen-Formel interpretiert werden könnten (Beginn mit `=` `+` `@` `-`), werden beim Import automatisch neutralisiert; Datei-Typ wird anhand der echten Endung geprüft, nicht des Browser-MIME-Typs.

[📥 CSV-Beispiel herunterladen](./samples/import-example.csv)  |  [📄 JSON-Beispiel](./samples/import-example.json)
Sidebar-Meta-Box — vollständig unabhängig von Carbon Fields:
- „Datei auswählen / hochladen"-Button öffnet den nativen WordPress Media Library Frame
- Beim Speichern werden `_odw_file_size` (Bytes) und `_odw_file_format` (z.B. „CSV") automatisch berechnet
- Sicherheit: `wp_verify_nonce` + `current_user_can('edit_post')`

### ⚙️ Einstellungsseite
Untermenü unter *Datensätze → Einstellungen* mit vier Bereichen:
- **Katalog** — Titel und Herausgebende Organisation
- **Standardwerte** — Standard-Sprache (wird bei neuen Datensätzen vorausgefüllt)
- **REST API** — Cache-Laufzeit (60–86400 Sekunden)
- **Deinstallation** — Opt-in Checkbox für vollständige Datenlöschung

### 📊 Qualitätsindikatoren
Automatische Metadaten-Vollständigkeitsprüfung nach DCAT-AP 3.0 (0–100 Punkte, 4 Stufen):

| Stufe | Punkte | Bedeutung |
|---|---|---|
| Perfekt | 100 | Alle Felder ausgefüllt |
| Gut | 56–99 | Über Mindestanforderung |
| Ausreichend | 55 | Genau alle Pflichtfelder |
| Verbesserungsbedarf | < 55 | Pflichtfelder unvollständig |

Berechnung nach jedem Speichern; Ergebnis in der Admin-Listenansicht und als Meta-Box sichtbar.

### 📥 Download-Card Shortcode
```
[odw_dataset id="123"]
```
Rendert eine strukturierte Download-Card im Frontend: Titel, Thema-Badge, Lizenz, Schlagwörter als Tag-Pillen, Download-Button sowie einen **Metadaten-Download-Button (JSON-LD)**. CSS (`assets/css/frontend.css`) wird nur auf Seiten geladen, die den Shortcode enthalten.

### 🔗 REST API Endpoints

```
GET https://deine-website.de/wp-json/datenatlas/v1/catalog
GET https://deine-website.de/wp-json/datenatlas/v1/datasets/<id>
GET https://deine-website.de/wp-json/datenatlas/v1/delta?since=<ISO8601>
```

Diese URLs können bei einer Open-Data-Plattform als Harvest-Quelle eingetragen werden — einmalig, ohne weiteren Aufwand.

**Catalog-Parameter:** `page`, `per_page`, `theme`, `license`, `format` (`jsonld` oder `json`)

**Delta-Parameter:** `since` (erforderlich, ISO 8601), `page`, `per_page`, `format` — liefert nur Datensätze, die nach dem angegebenen Zeitstempel geändert wurden, plus Tombstones für gelöschte Datensätze

### ✅ DCAT-AP 3.0 Konformität
Alle Ausgaben sind DCAT-AP 3.0 konform und in JSON-LD serialisiert.

---

## Installation

### Für Anwender:innen

1. ZIP-Datei aus den [Releases](https://github.com/daimpad/OpenDataWizard/releases) herunterladen
2. Im WordPress-Backend: **Plugins → Installieren → Plugin hochladen**
3. Plugin aktivieren

Keine weiteren Abhängigkeiten. Keine Programmierkenntnisse erforderlich.

### Für Entwickler:innen

```bash
git clone https://github.com/daimpad/OpenDataWizard.git
cd OpenDataWizard
composer install   # inkl. PHPStan, WPCS, PHPUnit
```

Den Plugin-Ordner in eine lokale WordPress-Instanz einbinden (z.B. via [LocalWP](https://localwp.com)).

**Systemvoraussetzungen:**
- WordPress ≥ 6.4
- PHP ≥ 8.1
- Composer (nur für Entwicklung)

**Dev-Tools:**

```bash
composer phpcs      # WordPress Coding Standards prüfen
composer phpcbf     # Automatisch korrigieren
composer phpstan    # Statische Analyse (Level 6)
composer test       # PHPUnit-Tests ausführen
```

CI läuft via GitHub Actions (`.github/workflows/ci.yml`) auf PHP 8.1, 8.2 und 8.3.

**Für Entwickler:** Siehe [`CLAUDE.md`](./CLAUDE.md) für Architektur-Übersicht, Patterns, Debugging-Tipps und Workflows.

---

## Technische Dokumentation

### Architektur

```
Präsentation    →   WP-Admin-Masken, Wizard (Tabs, Validierung)
Domäne          →   Metadatenmodell, DCAT-AP Mapping, Validierungslogik
Infrastruktur   →   REST API, JSON-LD Serialisierung, Custom Post Type
```

### Dateistruktur

```
open-data-wizard/
├── open-data-wizard.php              # Plugin-Header & Bootstrap
├── uninstall.php                     # Opt-in Datenlöschung
├── composer.json
├── config/
│   ├── licenses.txt                  # Lizenzdatei (URI | Label)
│   ├── dct-format-list.php           # Dateiformate (MIME + EU-URI)
│   ├── dcat-ap-fields.php            # Felddefinitionen (Qualität + Validierung)
│   ├── TopicClassification-4.2.3_de-4.2.3.rdf  # CESSDA SKOS/RDF (95 Konzepte)
│   ├── phpcs.xml                     # PHPCS Konfiguration
│   ├── phpstan.neon                  # PHPStan Level 6
│   └── phpunit.xml                   # PHPUnit 9 Konfiguration
├── vendor/                           # Carbon Fields + Dev-Dependencies
├── includes/
│   ├── class-post-types.php          # CPT-Registrierung: odw_dataset
│   ├── class-fields.php              # Carbon Fields (5 Tabs), JSON-LD Builder
│   ├── class-rest-api.php            # REST Endpoints + Transient-Cache
│   ├── class-validation.php          # Pflichtfeldprüfung vor Veröffentlichung
│   ├── class-quality.php             # Qualitätsindikatoren (0–100, 4 Stufen)
│   ├── class-admin.php               # Listenansicht, wp.media Meta-Box, Help Tabs
│   ├── class-shortcode.php           # [odw_dataset]-Shortcode, Download-Card
│   ├── class-setup.php               # Demo-Datensatz bei Aktivierung
│   ├── class-settings.php            # Einstellungsseite
│   └── class-cli.php                 # WP-CLI Befehle
├── assets/
│   ├── js/
│   │   ├── wizard-tabs.js            # Tab-Navigation (Vanilla JS)
│   │   ├── odw-file-upload.js        # wp.media Upload-Widget (jQuery)
│   │   └── odw-admin-fields.js       # License/CESSDA Auto-Suggest, Dateigröße-Widget
│   ├── css/
│   │   ├── admin.css                 # Admin-Styles
│   │   └── frontend.css              # Shortcode Download-Card
│   └── sample/
│       └── beispiel-datensatz.csv    # Demo-Datensatz CSV
├── tests/
│   ├── bootstrap.php
│   ├── test-fields.php
│   ├── test-fields-extended.php
│   ├── test-quality.php
│   ├── test-settings.php
│   ├── test-shortcode.php
│   ├── test-rest-delta.php
│   └── test-cli.php
└── .github/workflows/ci.yml          # CI: PHPCS + PHPStan + PHPUnit (PHP 8.1–8.3)
```

### Feldmapping DCAT-AP 3.0

#### Tab 1 — Grundlegende Informationen

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Titel | `dct:title` | ✓ |
| Beschreibung | `dct:description` | ✓ |
| Herausgeber | `dct:publisher` → `foaf:Organization` | ✓ |
| Thema | `dcat:theme` | — |

#### Tab 2 — Inhaltliche Angaben

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Sprache | `dct:language` | — |
| Schlagworte (eine pro Zeile) | `dcat:keyword` | — |
| Veröffentlichungsdatum | `dct:issued` | — |
| Änderungsdatum | `dct:modified` (auto) | — |
| CESSDA Themenklassifikation | `cessda:topic` | — |

#### Tab 3 — Datenbereitstellung (Distribution)

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Zugriffs-URL | `dcat:accessURL` | ✓ (min. 1) |
| Format | `dct:format` (MIME) | — |
| Dateigröße | `dcat:byteSize` | — |
| Lizenz | `dct:license` (URI) | ✓ pro Distribution |
| Zuschreibungstext | `odrl:attribution` | — |

#### Tab 4 — Erweiterte Angaben

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Projektseite | `dcat:landingPage` (`@id`) | — |
| Aktualisierungsfrequenz | `dct:accrualPeriodicity` (EU-URI) | — |
| Geographische Abdeckung | `dct:spatial` → `dct:Location` + `skos:prefLabel` | — |
| Zeitlicher Bezug Start | `dct:temporal` → `dcat:startDate` | — |
| Zeitlicher Bezug Ende | `dct:temporal` → `dcat:endDate` | — |
| Kontaktpunkt Name | `dcat:contactPoint` → `vcard:fn` | — |
| Kontaktpunkt E-Mail | `vcard:hasEmail` (mit `mailto:`-Prefix) | — |
| Kontaktpunkt Website | `vcard:hasURL` (`@id`) | — |

#### Sidebar — Download-Datei

| Feld | Interner Meta-Key | Beschreibung |
|---|---|---|
| Attachment-ID | `_odw_file_id` | Mediathek-Datei |
| Dateigröße (auto) | `_odw_file_size` | Bytes, auto-berechnet beim Speichern |
| Dateiformat (auto) | `_odw_file_format` | z.B. „CSV", auto-berechnet |

### REST API

#### Catalog
```
GET /wp-json/datenatlas/v1/catalog
```

| Parameter  | Standard | Beschreibung |
|------------|----------|--------------|
| `page`     | 1        | Seitennummer |
| `per_page` | 20       | Einträge pro Seite (max. 100) |
| `theme`    | –        | Filter nach Thema |
| `license`  | –        | Filter: Kurzform (`cc-by`) oder volle URI |
| `format`   | `jsonld` | `jsonld` → `application/ld+json`, `json` → `application/json` |

Response-Header: `X-WP-Total`, `X-WP-TotalPages`, `X-ODW-Cache` (`HIT`/`MISS`)

#### Einzel-Dataset
```
GET /wp-json/datenatlas/v1/datasets/<id>
```

#### Delta-Harvesting (inkrementell)
```
GET /wp-json/datenatlas/v1/delta?since=2024-01-01T00:00:00Z
```

| Parameter  | Standard | Beschreibung |
|------------|----------|--------------|
| `since`    | erforderlich | ISO 8601 Datetime |
| `page`     | 1        | Seitennummer |
| `per_page` | 20       | Einträge pro Seite (max. 100) |
| `format`   | `jsonld` | Content-Type |

Response enthält `dcat:dataset` (geänderte Datasets) und `odw:removed` (Tombstones).

#### Beispiel-Response

```json
{
  "@context": {
    "dcat":  "https://www.w3.org/ns/dcat#",
    "dct":   "http://purl.org/dc/terms/",
    "foaf":  "http://xmlns.com/foaf/0.1/",
    "xsd":   "http://www.w3.org/2001/XMLSchema#",
    "vcard": "http://www.w3.org/2006/vcard/ns#",
    "skos":  "http://www.w3.org/2004/02/skos/core#",
    "odw":   "https://github.com/daimpad/OpenDataWizard/ns#"
  },
  "@type": "dcat:Catalog",
  "dct:title": "Mein Datenkatalog",
  "dcat:dataset": [
    {
      "@type": "dcat:Dataset",
      "dct:title": "Mitgliederdaten 2023",
      "dct:description": "Anonymisierte Mitgliederstatistik.",
      "dct:publisher": { "@type": "foaf:Organization", "foaf:name": "Musterorganisation e.V." },
      "dcat:distribution": [
        {
          "@type": "dcat:Distribution",
          "dcat:accessURL": "https://organisation.de/daten/mitglieder.csv",
          "dct:format": "text/csv",
          "dct:license": "https://creativecommons.org/licenses/by/4.0/",
          "dcat:byteSize": 20480
        }
      ]
    }
  ]
}
```

### Erweiterbarkeit

| Hook                  | Beschreibung |
|-----------------------|--------------|
| `odw_license_options` | Weitere Lizenz-Optionen hinzufügen |
| `odw_theme_options`   | Weitere Thema-Optionen hinzufügen |
| `odw_dataset_jsonld`  | JSON-LD Array vor Ausgabe anpassen |
| `odw_catalog_title`   | Catalog-Titel anpassen |

```php
// Eigene Lizenz hinzufügen
add_filter( 'odw_license_options', function( array $options ): array {
    $options['https://example.com/custom-license'] = 'Custom License 1.0';
    return $options;
} );

// JSON-LD Dataset anpassen
add_filter( 'odw_dataset_jsonld', function( array $jsonld, int $post_id ): array {
    $jsonld['dct:spatial'] = 'https://sws.geonames.org/2921044/';
    return $jsonld;
}, 10, 2 );
```

### WP-CLI Befehle

```bash
# Qualitätsscores für alle veröffentlichten Datasets neu berechnen
wp open-data-wizard quality recalculate

# Alle Datasets einschließlich Entwürfe und Trash berechnen
wp open-data-wizard quality recalculate --all

# Alle REST API Transient-Caches löschen
wp open-data-wizard cache clear
```

---

### Abhängigkeiten

| Paket | Version | Zweck |
|---|---|---|
| [Carbon Fields](https://carbonfields.net/) | ^3.6 | Admin-Formular (5-Tab-Wizard) |
| [PHPUnit](https://phpunit.de/) | ^9.6 | Unit-Tests (dev) |
| [WP_Mock](https://github.com/10up/wp_mock) | ^1.0 | WordPress-Stubs für Tests (dev) |
| [PHPStan](https://phpstan.org/) + WordPress-Stubs | ^2.0 | Statische Analyse Level 6 (dev) |
| [WPCS](https://github.com/WordPress/WordPress-Coding-Standards) | ^3.1 | Coding Standards (dev) |

---

## 🧩 Technische Spezifikationen

> **Zweck dieser Sektion:** Zentrale Ablage für alle technischen Spezifikationen des Open Data Wizard.
> Hier werden Metadatenmodell, Namespaces, kontrollierte Vokabulare, der vollständige DCAT-AP-Feldkatalog,
> die Gap-Analyse zum Standard sowie die Umsetzungsplanung gepflegt. Neue technische Festlegungen gehören
> in diese Sektion (nicht in die anwenderorientierten Kapitel weiter oben).
>
> Grundlage ist eine Analyse des **piveau Data Provider Interface (DPI, Standalone)** — der etablierten
> Referenzimplementierung für DCAT-AP-/DCAT-AP.de-Metadatenerfassung der Fraunhofer-FOKUS-/data.europa.eu-Toolchain.
> Das DPI verfolgt dasselbe Grundprinzip wie ODW (ein Formular zur DCAT-AP-konformen Metadatenbereitstellung),
> deckt aber den vollständigen Standard inkl. der deutschen Erweiterung ab. Diese Spezifikation extrahiert die
> übernehmbaren Konzepte und beschreibt deren Einbettung in die ODW-Architektur.

### 1. Referenzarchitektur piveau DPI (Analyseergebnis)

Das DPI ist ein Vue-3-/FormKit-/TypeScript-Frontend. Sein Kern ist eine **deklarative Feld-Registry**: Felder
werden nicht im Code verdrahtet, sondern in einer Datenstruktur beschrieben, die ein generischer Renderer zur
Laufzeit in ein mehrstufiges Formular übersetzt. Pro Spezifikationsprofil existiert ein Ordner mit identischem
Bausatz:

| Baustein (piveau) | Inhalt | ODW-Pendant |
|---|---|---|
| `input-definition.ts` | Alle Felder: Typ, DCAT-Prädikat, Kardinalität, Validierung, Vokabular | `config/dcat-ap-fields.php` (erweiterbar) |
| `page-content-config.js` | Zuordnung Feld → Wizard-Schritt (Essentials/Additionals) | Tab-Zuordnung in `class-fields.php` |
| `prefixes.js` | Namespace-Präfixe für JSON-LD | `odw_build_dataset_jsonld()` (`@context`) |
| `vocab-prefixes.js` | Basis-URIs der kontrollierten Vokabulare | `dct-format-list.php`, `licenses.txt`, CESSDA-RDF |
| `format-types.js` | MIME → EU-Format-URI Mapping | `config/dct-format-list.php` |
| `converter.js` | Formwerte → kanonisches DCAT-AP-JSON-LD | `odw_build_dataset_jsonld()` |

Profile im DPI: `dcatap` (EU-Basis), `dcatapde` (DCAT-AP.de), `dcatapdeODB` (Open Data Bayern), `dcatapdeHappyFlow`
(geführter Vereinfachungs-Wizard). Der **HappyFlow** ist für ODW das relevanteste Vorbild: Er trennt einen kurzen
Pflichtpfad (`Landing → Discoverability → BasicInfos → Covering → DistributionSimple → ReviewAndPublish`) von einem
aufklappbaren Block `Additionals` mit ~35 optionalen Profi-Feldern. Genau diese Trennung erlaubt „einfache UI **und**
voller DCAT-AP-Umfang".

> **Was übernommen wird:** Konzepte (deklarative Registry, Essentials/Additionals-Trennung, Feldkatalog, Vokabular-Bindung).
> **Was nicht übernommen wird:** die piveau-Hub-Infrastruktur (axios-Uploads, externe Vokabular-/Datei-Endpunkte) und
> der Tech-Stack (Vue/FormKit). ODW bleibt self-contained in WordPress/PHP/Carbon Fields.

### 2. Namespace-Registry (`@context`)

Verbindliche Präfixe für die JSON-LD-Serialisierung (abgeglichen mit DCAT-AP.de / piveau). `dcatde` und `dcatap`
sind für die deutsche Erweiterung bzw. HVD/Availability nötig.

| Präfix | Namespace-URI | Benötigt für |
|---|---|---|
| `dcat` | `http://www.w3.org/ns/dcat#` | Kernvokabular |
| `dct` | `http://purl.org/dc/terms/` | Dublin Core Terms |
| `dcatap` | `http://data.europa.eu/r5r/` | `availability`, `hvdCategory`, `applicableLegislation` |
| `dcatde` | `http://dcat-ap.de/def/dcatde/` | DCAT-AP.de-Erweiterungsfelder |
| `foaf` | `http://xmlns.com/foaf/0.1/` | Agent / Organisation / Page |
| `vcard` | `http://www.w3.org/2006/vcard/ns#` | Kontaktpunkt |
| `adms` | `http://www.w3.org/ns/adms#` | Identifier, versionNotes, status |
| `spdx` | `http://spdx.org/rdf/terms#` | Checksum |
| `odrl` | `http://www.w3.org/ns/odrl/2/` | hasPolicy |
| `prov` | `http://www.w3.org/ns/prov#` | qualifiedAttribution, wasGeneratedBy |
| `locn` | `http://www.w3.org/ns/locn#` | Geometrie / Adresse |
| `skos` | `http://www.w3.org/2004/02/skos/core#` | prefLabel, notation |
| `owl` | `http://www.w3.org/2002/07/owl#` | versionInfo |
| `xsd` | `http://www.w3.org/2001/XMLSchema#` | Datentypen (Datum) |

### 3. Kontrollierte Vokabulare (Registry)

DCAT-AP verlangt für viele Felder URIs aus EU-Authority-Tables statt Freitext. Basis-URIs (aus piveau
`vocab-prefixes.js`), die ODW schrittweise einbinden sollte:

| Vokabular-ID | Basis-URI | Genutzt von Feld | ODW-Status |
|---|---|---|---|
| `data-theme` | `http://publications.europa.eu/resource/authority/data-theme/` | `dcat:theme` | ⚠️ Freitext |
| `language` | `http://publications.europa.eu/resource/authority/language/` | `dct:language` | ⚠️ Freitext |
| `file-type` | `http://publications.europa.eu/resource/authority/file-type/` | `dct:format` | ✅ (`dct-format-list.php`) |
| `frequency` | `http://publications.europa.eu/resource/authority/frequency/` | `dct:accrualPeriodicity` | ✅ URI |
| `access-right` | `http://publications.europa.eu/resource/authority/access-right/` | `dct:accessRights` | ❌ |
| `licence` | `http://dcat-ap.de/def/licenses/` | `dct:license` | ✅ (`licenses.txt`) |
| `planned-availability` | `http://dcat-ap.de/def/plannedAvailability/` | `dcatap:availability` | ❌ |
| `political-geocoding-level` | `http://dcat-ap.de/def/politicalGeocoding/Level/` | `dcatde:politicalGeocodingLevelURI` | ❌ |
| `eurovoc` | `http://eurovoc.europa.eu/` | `dct:subject` | ❌ (CESSDA stattdessen) |
| `corporate-body` | `http://publications.europa.eu/resource/authority/corporate-body/` | `dct:publisher` | ⚠️ Freitext |
| `iana-media-types` | `https://www.iana.org/assignments/media-types/` | `dcat:mediaType` | ❌ |

> **Wiederverwendbares Muster:** Der bestehende CESSDA-Auto-Suggest (`odw-admin-fields.js` + SKOS/RDF) ist die
> Blaupause für ein generisches „Vokabular-Autosuggest"-Widget, das künftig `data-theme`, `access-right`,
> `planned-availability` etc. aus lokal gebündelten Vokabulardateien bedient (keine externe Abhängigkeit).

### 4. Vollständiger DCAT-AP-Feldkatalog & Gap-Analyse

**Legende** — Profil: `AP` = DCAT-AP 3.0 · `DE` = DCAT-AP.de 2.0 · `HVD` = High-Value-Dataset-Pflicht ·
Norm-Kard.: Kardinalität laut Standard (`M`andatory/`R`ecommended/`O`ptional) ·
ODW: ✅ vorhanden · ⚠️ teilweise/Freitext · ❌ fehlt.

#### 4.1 Dataset (`dcat:Dataset`)

| DCAT-Prädikat | Range | Profil | Norm-Kard. | ODW |
|---|---|---|---|---|
| `dct:title` | lang-Literal | AP | M (1..n) | ⚠️ String |
| `dct:description` | lang-Literal | AP | M (1..n) | ⚠️ String |
| `dct:publisher` | `foaf:Agent` | AP | R (0..1) | ⚠️ Freitext |
| `dcat:contactPoint` | `vcard:Kind` | AP | R (0..n) | ⚠️ name/mail/url |
| `dcat:distribution` | `dcat:Distribution` | AP | R (0..n) | ⚠️ genau 1 |
| `dcat:keyword` | lang-Literal | AP | R (0..n) | ✅ |
| `dcat:theme` | URI (`data-theme`) | AP | R (0..n) | ⚠️ Freitext |
| `dct:spatial` | `dct:Location` | AP | O (0..n) | ⚠️ |
| `dct:temporal` | `dct:PeriodOfTime` | AP | O (0..n) | ✅ start/end |
| `dct:issued` / `dct:modified` | Datum | AP | O (0..1) | ✅ |
| `dct:accessRights` | URI (`access-right`) | AP | O (0..1) | ❌ |
| `dct:accrualPeriodicity` | URI (`frequency`) | AP | O (0..1) | ✅ |
| `dct:language` | URI (`language`) | AP | O (0..n) | ⚠️ Freitext |
| `dct:identifier` / `adms:identifier` | Literal / Node | AP | O (0..n) | ❌ |
| `dct:subject` | URI (`eurovoc`) | AP | O (0..n) | ❌ |
| `dct:creator` | `foaf:Agent` | AP | O (0..n) | ❌ |
| `dct:type` | URI (`dataset-type`) | AP | O (0..1) | ❌ |
| `dcat:landingPage` | `foaf:Document` | AP | O (0..n) | ✅ |
| `dct:conformsTo` / `dct:provenance` | Node | AP | O (0..n) | ❌ |
| `owl:versionInfo` / `adms:versionNotes` | Literal | AP | O | ❌ |
| `dcat:spatialResolutionInMeters` | Dezimal | AP | O | ❌ |
| `dcat:temporalResolution` | Duration | AP | O | ❌ |
| `prov:qualifiedAttribution`, `dcat:qualifiedRelation`, `prov:wasGeneratedBy` | Node | AP | O | ❌ |
| `dcatde:politicalGeocodingLevelURI` | URI | DE | R (DE) | ❌ |
| `dcatde:politicalGeocodingURI` | URI | DE | O (0..n) | ❌ |
| `dcatde:geocodingDescription` | lang-Literal | DE | O | ❌ |
| `dcatde:contributorID` | URI (`contributors`) | DE | R (DE) | ❌ |
| `dcatde:legalBasis` | lang-Literal | DE | O | ❌ |
| `dcatde:qualityProcessURI` | URI | DE | O | ❌ |
| `dcatde:originator` / `dcatde:maintainer` | `foaf:Agent` | DE | O | ❌ |
| `dcatap:availability` | URI (`planned-availability`) | DE | R (DE) | ❌ |
| `dcatap:hvdCategory` | URI | HVD | M *wenn HVD* | ❌ |
| `dcatap:applicableLegislation` | URI | HVD | M *wenn HVD* | ❌ |

#### 4.2 Distribution (`dcat:Distribution`)

| DCAT-Prädikat | Range | Profil | Norm-Kard. | ODW |
|---|---|---|---|---|
| `dcat:accessURL` | URI | AP | M (1..n) | ✅ |
| `dcat:downloadURL` | URI | AP | O (0..n) | ❌ |
| `dct:format` | URI (`file-type`) | AP | R (0..1) | ✅ |
| `dcat:mediaType` | URI (`iana`) | AP | O (0..1) | ❌ |
| `dct:license` | URI (`licence`) | AP | R (0..1) | ✅ |
| `dcat:byteSize` | Nonneg-Integer | AP | O (0..1) | ✅ |
| `dct:title` / `dct:description` | lang-Literal | AP | O | ❌ |
| `dcatap:availability` | URI (`planned-availability`) | DE | R (DE) | ❌ |
| `spdx:checksum` | Node | AP | O | ❌ |
| `dcat:accessService` | `dcat:DataService` | AP | O | ❌ |
| `dct:conformsTo`, `dct:issued`, `dct:modified`, `dct:rights` | div. | AP | O | ❌ |
| `adms:status` | URI | AP | O | ❌ |
| `odrl:hasPolicy` | Node | AP | O | ❌ |
| `dcat:compressFormat` / `packageFormat` | URI (`iana`) | AP | O | ❌ |
| `foaf:page` | `foaf:Document` | AP | O | ❌ |
| `dcatde:licenseAttributionByText` | lang-Literal | DE | O (bei CC-BY) | ⚠️ siehe Hinweis |

> **Korrektheitshinweis:** Der ODW-Zuschreibungstext wird derzeit als `odrl:attribution` ausgegeben
> (siehe Feldmapping Tab 3). DCAT-AP.de-konform ist `dcatde:licenseAttributionByText` (sprachgetaggtes Literal).
> Diese Abweichung sollte in Phase A korrigiert werden.

#### 4.3 Catalogue (`dcat:Catalog`)

ODW erzeugt den Katalog **automatisch** im REST-Endpoint `/catalog` (eine WordPress-Site = ein Katalog). Die
Catalogue-Felder (`dct:title`, `dct:publisher`, `dct:license`, `dct:language`, `foaf:homepage`, `dct:spatial`)
gehören daher in die **Einstellungsseite**, nicht in das Dataset-Formular. Aktuell werden Titel/Publisher
unterstützt; `dct:license`, `dct:language`, `dcat:themeTaxonomy` und `dct:spatial` des Katalogs sind offen.

### 5. Erweiterung der Feld-Registry (`config/dcat-ap-fields.php`)

Heute trägt jeder Registry-Eintrag: `key`, `meta_key`, `dcat_prop`, `label`, `points`, `required`. Damit **eine**
Definition künftig Formular, Validierung, Qualität *und* JSON-LD treibt (Single Source of Truth, wie piveaus
`input-definition.ts`), werden folgende **optionale** Schlüssel ergänzt (abwärtskompatibel — bestehende Einträge
bleiben gültig):

```php
array(
    // Bestand:
    'key'         => 'access_rights',
    'meta_key'    => '_odw_access_rights',
    'dcat_prop'   => 'dct:accessRights',
    'label'       => __( 'Zugriffsrechte', 'open-data-wizard' ),
    'points'      => 0,
    'required'    => false,
    // NEU (alle optional, mit sinnvollen Defaults):
    'profile'     => 'ap',          // 'ap' | 'ap.de' | 'hvd'
    'tier'        => 'optional',    // 'mandatory' | 'recommended' | 'optional'
    'range'       => 'uri',         // 'literal' | 'literal-lang' | 'uri' | 'node'
    'cardinality' => '0..1',        // '0..1' | '0..n' | '1..1' | '1..n'
    'vocab'       => 'access-right', // ID aus der Vokabular-Registry (Abschnitt 3)
    'tab'         => 'advanced-pro', // 'basic'|'content'|'distribution'|'advanced'|'advanced-pro'
    'entity'      => 'dataset',     // 'dataset' | 'distribution' | 'catalog'
),
```

- `profile`/`tier`/`cardinality` steuern Validierung und Qualitäts-Scoring deklarativ.
- `range` steuert die JSON-LD-Serialisierung (`uri` → `{"@id": …}`, `literal-lang` → `{"@value":…,"@language":…}`).
- `vocab` aktiviert das Vokabular-Autosuggest-Widget.
- `tab`/`entity` steuern die automatische Einsortierung im Carbon-Fields-Formular.

### 6. Mapping piveau-FormKit → Carbon Fields

Übersetzungstabelle für die Portierung der piveau-Feldtypen in die ODW-Formulartechnik:

| piveau (`$formkit`) | Carbon Fields | Hinweis |
|---|---|---|
| `text` / `simpleInput` | `Field::make( 'text', … )` | mit Sanitization |
| `textarea` | `Field::make( 'textarea', … )` | |
| `select` / `simpleSelect` | `Field::make( 'select', … )` | Optionen aus Vokabular |
| `auto` (Vokabular-Autocomplete) | `text` + Autosuggest-JS | CESSDA-Muster generalisieren |
| `repeatable` (group) | `Field::make( 'complex', … )` | wiederholbare Gruppen |
| `group` / `formkitGroup` | `complex` (max. 1) | strukturierter Knoten |
| `simpleConditional` | `text/select` + `->set_conditional_logic()` | Vokabular ODER manuell |
| `fileupload` | wp.media-Meta-Box | bereits in ODW vorhanden |
| `date` / `datetime-local` | `Field::make( 'date' / 'date_time', … )` | xsd-Typ in JSON-LD |
| `url` | `text` + `esc_url_raw()` | blockt `javascript:` |
| `id` | verstecktes Feld / Post-Slug | Dataset-Identifier |

### 7. Umsetzungsplanung (phasiert)

Priorisiert nach Nutzen/Aufwand; jede Phase ist eigenständig auslieferbar.

#### Phase A — Fundament & Korrekturen (v2.2, nicht brechend)
- Namespace-Registry (Abschnitt 2) vollständig in `@context` von `odw_build_dataset_jsonld()` aufnehmen.
- Feld-Registry-Schema (Abschnitt 5) um die neuen optionalen Schlüssel erweitern; bestehende Einträge anreichern.
- Korrektur: Zuschreibungstext `odrl:attribution` → `dcatde:licenseAttributionByText` (lang-Literal).
- Neuer Formular-Bereich „Erweiterte Angaben (für Profis)" als ausklappbarer Abschnitt vorbereitet (Grundgerüst).
- _Betroffen:_ `class-fields.php`, `class-rest-api.php`/JSON-LD-Builder, `config/dcat-ap-fields.php`.

#### Phase B — DCAT-AP.de & Vokabulare (v2.3)
- DCAT-AP.de-Felder ergänzen: `dcatde:politicalGeocodingLevelURI`, `politicalGeocodingURI`, `contributorID`,
  `legalBasis`, `qualityProcessURI`, `originator`, `maintainer`, `dcatap:availability`.
- Generisches Vokabular-Autosuggest-Widget (CESSDA-Muster) + lokal gebündelte Vokabulardateien für
  `data-theme`, `access-right`, `planned-availability`, `language`.
- _Betroffen:_ `class-fields.php`, `odw-admin-fields.js`, neue `config/vocabularies/*`, JSON-LD-Builder, `class-quality.php`.

#### Phase C — HVD-Unterstützung (v2.4)
- Schalter „High-Value-Dataset" → bedingte Pflichtfelder `dcatap:hvdCategory` + `dcatap:applicableLegislation`.
- Bedingte Validierung (`tier`/`profile` = `hvd`) in `class-validation.php`.
- _Betroffen:_ `class-fields.php` (conditional logic), `class-validation.php`, JSON-LD-Builder.

#### Phase D — Mehrsprachige Literale (v2.5, Datenmodell-Migration)
- Umstellung von String- auf sprachgetaggte Literale (`@value`/`@language`) für `title`, `description`, `keyword`.
- Migrationsroutine + WP-CLI-Befehl für Bestandsdaten; Default-Sprache aus Einstellungen.
- _Betroffen:_ Datenmodell (Post Meta), JSON-LD-Builder, Validierung, REST-API, Tests.

#### Phase E — Multi-Distribution (optional, v2.6)
- Rücknahme der v2.1.4-Vereinfachung hinter einem Einstellungs-Schalter: wiederholbare Distributionen
  (`Field::make('complex')`) für Nutzer mit mehreren Dateien pro Datensatz.
- _Betroffen:_ `class-fields.php`, JSON-LD-Builder, Quality-Scoring, Shortcode-Card, Tests.

### 8. UX-Konzept: Essentials/Additionals (aus HappyFlow)

Beibehaltung der bestehenden 5-Tab-Struktur, ergänzt um die piveau-Trennung:

```
Pflichtpfad (Einsteiger):   Tab 1 Grundlagen → Tab 2 Inhalt → Tab 3 Distribution → Tab 5 Vorschau/Publish
Profi-Erweiterung (opt-in): Tab 4 „Erweiterte Angaben"  ▸ ausklappbar in Gruppen
                            (Abdeckung · Provenienz · Versionierung · DCAT-AP.de · HVD)
```

Damit bleibt die Einstiegshürde niedrig, während der volle DCAT-AP-/DCAT-AP.de-Umfang optional verfügbar ist —
analog zum HappyFlow-Block `Additionals`. Tab 5 erhält zusätzlich eine **Validierungs-Checkliste**
(Pflicht/Empfohlen/HVD) vor der Veröffentlichung (piveau-Schritt `ReviewAndPublish`).

### 9. Nicht-Ziele / bewusste Abgrenzungen

- **Keine piveau-Hub-Anbindung:** ODW serialisiert lokal nach JSON-LD; keine externen Upload-/Vokabular-Endpunkte.
- **Keine Vue/FormKit-Portierung:** Umsetzung bleibt Carbon Fields / PHP.
- **Vokabulare lokal gebündelt:** keine Laufzeit-Abhängigkeit von data.europa.eu zur Editierzeit.
- **Catalogue-Metadaten** verbleiben in den Einstellungen, nicht im Dataset-Formular.

---

## Roadmap

**Abgeschlossen (v1.0 — v2.1):**
- [x] Custom Post Type `odw_dataset` mit DCAT-AP 3.0 Unterstützung
- [x] Five-Tab Wizard-Formular mit Validierung und Hilfetexten
- [x] REST API Endpoints: `/catalog`, `/datasets/<id>`, `/delta?since=<timestamp>`
- [x] Qualitätsindikatoren / 4-Stufen-Ampellogik (Perfekt / Gut / Ausreichend / Verbesserungsbedarf)
- [x] Download-Card Shortcode `[odw_dataset]` mit Keywords und Metadaten-Download
- [x] Demo-Datensatz bei Aktivierung
- [x] Einstellungsseite (Catalog-Titel, Defaults, API, Cleanup)
- [x] Erweiterte DCAT-AP Felder (Tab 4)
- [x] Nativer wp.media Upload-Widget
- [x] Benutzerfreundliche UX-Überarbeitung
- [x] WP-CLI Befehle für Massenoperationen
- [x] Lizenz pro Distribution (DCAT-AP-konform)
- [x] CESSDA-Themenklassifikation (Auto-Suggest aus SKOS/RDF)
- [x] Externe Konfigurationsdateien (licenses.txt, dct-format-list.php, dcat-ap-fields.php)

**In Planung (v2.2+):** — Details siehe [Technische Spezifikationen § 7](#7-umsetzungsplanung-phasiert)
- [ ] Phase A: Namespace-/Feld-Registry-Erweiterung + `dcatde:licenseAttributionByText`-Korrektur (v2.2)
- [ ] Phase B: DCAT-AP.de-Felder (politicalGeocoding, contributorID, availability …) + Vokabular-Autosuggest (v2.3)
- [ ] Phase C: HVD-Unterstützung (`dcatap:hvdCategory` + `applicableLegislation`) (v2.4)
- [ ] Phase D: Mehrsprachige Literale (`@language`/`@value`) inkl. Migration (v2.5)
- [ ] Phase E: Multi-Distribution (opt-in) (v2.6)
- [ ] Content Negotiation: Turtle / RDF-XML Ausgabe
- [ ] Gutenberg Block für die Download-Card
- [ ] Mehrsprachigkeit (WPML/Polylang)
- [ ] Phase 3 UX: Tooltip-Popups, Wizard-Vorschau

---

## Mitwirken

Beiträge sind willkommen — ob Fehlermeldungen, Verbesserungsvorschläge oder Pull Requests.

Bitte öffne zunächst ein [Issue](https://github.com/daimpad/OpenDataWizard/issues), bevor du größere Änderungen einreichst.

---

## Deinstallation

Das Plugin löscht bei Deinstallation standardmäßig **keine** Daten (Opt-in).

Um alle Plugin-Daten zu löschen, die Checkbox unter **Datensätze → Einstellungen → Deinstallation** aktivieren und dann das Plugin im WordPress-Backend deinstallieren. `uninstall.php` entfernt alle `odw_dataset`-Posts, alle `_odw_*`-Metafelder sowie die Plugin-Optionen.

---

## Lizenz

GPL-2.0-or-later — siehe [`LICENSE`](./LICENSE)
