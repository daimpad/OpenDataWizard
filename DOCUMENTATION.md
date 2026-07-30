# Technische Dokumentation — Open Data Wizard

> Entwickler-orientierte Dokumentation: Architektur, Dateistruktur, DCAT-AP-Feldmapping, REST-API,
> Erweiterbarkeit, WP-CLI und Abhängigkeiten. Zurück zur Übersicht: [README](README.md) · 
> Vertiefende Spezifikation & Roadmap: [TECHNICAL-SPEC.md](TECHNICAL-SPEC.md).

---


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
│   ├── field-catalog.php             # Feld-Katalog (Quelle der Feld-Referenz)
│   ├── shacl/                        # Offizielle SHACL-Shapes (EU + DCAT-AP.de) + Anleitung
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

> 📋 **Vollständige, mehrstufige Feld-Referenz:** [docs/FELD-REFERENZ.md](docs/FELD-REFERENZ.md)
> dokumentiert jedes Formularfeld in vier Stufen (DCAT-AP-Frage · verständliche Frage ·
> DCAT-AP-Langbeschreibung · verständliche Langbeschreibung). Sie wird aus `config/field-catalog.php`
> generiert (`php bin/generate-field-reference.php` bzw. `wp open-data-wizard docs`).

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
| Zuschreibungstext | `dcatde:licenseAttributionByText` | — |
| Planbare Verfügbarkeit | `dcatap:availability` (`@id`, DCAT-AP.de-URI) | — |

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
| HVD-Markierung | (steuert HVD-Ausgabe) | — |
| HVD-Kategorie | `dcatap:hvdCategory` (`@id`, EU-URI) | ✓ wenn HVD |
| HVD-Rechtsgrundlage (auto) | `dcatap:applicableLegislation` (Reg. 2023/138) | — |
| Bereitstellende Stelle | `dcatde:contributorID` (`@id`, GovData-Verzeichnis) | — |
| Urheber (Name/E-Mail) | `dcatde:originator` → `foaf:Agent` | — |
| Pflegende Stelle (Name/E-Mail) | `dcatde:maintainer` → `foaf:Agent` | — |

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

### Release bauen

Ein installationsfertiges Plugin-ZIP (nur Laufzeit-Dateien inkl. Produktions-`vendor/` mit Carbon
Fields, ohne Tests/Doku/Dev-Configs) erzeugt:

```bash
bin/build-release.sh
# → dist/open-data-wizard-<version>.zip  (Version aus dem Plugin-Header)
```

Installation in WordPress: **Plugins → Installieren → Plugin hochladen**. Das Skript nutzt
`composer install --no-dev`, entschlackt die Carbon-Fields-Dev-Dateien (die kompilierten
`build/`-Assets bleiben erhalten) und legt das ZIP unter `dist/` ab (gitignored).
