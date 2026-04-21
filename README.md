# 🧙 Open Data Wizard

![Lizenz](https://img.shields.io/github/license/daimpad/OpenDataWizard?style=flat-square&color=blue&label=Lizenz)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-8892BF?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-compatible-21759B?style=flat-square&logo=wordpress&logoColor=white)
![DCAT-AP](https://img.shields.io/badge/DCAT--AP-3.0-brightgreen?style=flat-square)
![Status](https://img.shields.io/badge/Status-MVP%20in%20Entwicklung-orange?style=flat-square)
![PRs Welcome](https://img.shields.io/badge/PRs-willkommen-brightgreen?style=flat-square)

**Ein WordPress-Plugin zur einfachen Veröffentlichung offener Daten nach DCAT-AP 3.0**

Open Data Wizard ermöglicht es Organisationen und Einzelpersonen, Datensätze direkt in WordPress zu beschreiben und als maschinenlesbare, standardkonforme Metadaten bereitzustellen — ohne technische Vorkenntnisse, ohne externe Plattformabhängigkeit.

---

## Das Problem

Offene Daten zu veröffentlichen ist schwieriger als es sein müsste. Wer Daten auf einer Open-Data-Plattform einstellen will, landet schnell vor komplexen Formularen, unbekannten Fachbegriffen oder muss sich auf eine externe Infrastruktur verlassen, über die keine Kontrolle besteht.

Dabei besitzen viele Organisationen bereits eine WordPress-Website und damit eine Infrastruktur, die sie kennen und die sie kontrollieren.

**Open Data Wizard setzt genau hier an.**

---

## Die Idee

Das Plugin bringt einen geführten Metadaten-Wizard ins WordPress-Backend. Organisationen beschreiben ihre Datensätze dort, wo sie ohnehin arbeiten. Das Plugin generiert daraus eine maschinenlesbare Beschreibung nach dem internationalen Standard **DCAT-AP 3.0** und stellt sie unter einer persistenten URL bereit.

Open-Data-Plattformen können diese URL als Harvest-Quelle einbinden und die Metadaten automatisch einsammeln. **Die Daten bleiben bei der Organisation. Die Plattform kommt zu ihr.**

---

## Was ist DCAT-AP?

DCAT-AP (Data Catalog Vocabulary — Application Profile) ist ein europäischer Standard zur Beschreibung von Datensätzen und Datenkatalogen. Er definiert, welche Angaben ein Datensatz braucht, damit er von Plattformen, Suchmaschinen und Anwendungen einheitlich gelesen und verarbeitet werden kann — Titel, Beschreibung, Lizenz, Format, Herausgeber und mehr.

Open Data Wizard implementiert **DCAT-AP 3.0** und erzeugt valide **JSON-LD**-Ausgaben, die direkt von kompatiblen Harvesting-Systemen (z.B. Piveau/CKANN) verarbeitet werden können.

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

### 🧭 Geführter Wizard
Vier-Schritt-Assistent mit Pflichtfeldprüfung, Hilfetexten und Inline-Validierung:

1. **Pflichtangaben** — Titel, Beschreibung, Herausgeber, Lizenz
2. **Optionale Angaben** — Sprache, Schlagworte, Thema, Zeitraum
3. **Distribution** — Zugriffs-URL, Format, Dateigröße
4. **Vorschau** — generiertes JSON-LD live einsehen

### 🔗 Maschinenlesbarer Endpoint
Das Plugin stellt einen öffentlichen REST API Endpoint bereit:

```
GET https://deine-website.de/wp-json/datenatlas/v1/catalog
GET https://deine-website.de/wp-json/datenatlas/v1/datasets/<id>
```

Diese URL kann bei einer Open-Data-Plattform als Harvest-Quelle eingetragen werden — einmalig, ohne weiteren Aufwand.

Parameter: `page`, `per_page`, `theme`, `license`, `format` (`jsonld` oder `json`)

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
├── open-data-wizard.php          # Plugin-Header & Bootstrap
├── uninstall.php                 # Opt-in Datenlöschung bei Deinstallation
├── composer.json
├── phpstan.neon
├── phpunit.xml
├── vendor/                       # Carbon Fields (gebündelt)
├── includes/
│   ├── class-post-types.php      # CPT-Registrierung: odw_dataset
│   ├── class-fields.php          # Carbon Fields, DCAT-AP Mapping, Pflichtfeld-Registry
│   ├── class-rest-api.php        # REST Endpoints mit Transient-Cache
│   ├── class-validation.php      # Pflichtfeldprüfung
│   └── class-admin.php           # Listenansicht, Help Tabs, Assets
├── assets/
│   ├── js/wizard-tabs.js         # Tab-Navigation (Vanilla JS)
│   └── css/admin.css             # Admin-Styles (CSS Custom Properties)
├── tests/
│   ├── bootstrap.php
│   └── test-fields.php
├── .github/workflows/ci.yml
└── languages/
```

### Feldmapping DCAT-AP 3.0

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Titel | `dct:title` | ✓ |
| Beschreibung | `dct:description` | ✓ |
| Herausgeber | `dct:publisher` | ✓ |
| Lizenz | `dct:license` | ✓ |
| Sprache | `dct:language` | — |
| Schlagworte | `dcat:keyword` | — |
| Thema | `dcat:theme` | — |
| Veröffentlichungsdatum | `dct:issued` | — |
| Änderungsdatum | `dct:modified` | — |
| Zugriffs-URL (Distribution) | `dcat:accessURL` | ✓ (min. 1) |
| Format (Distribution) | `dct:format` | — |
| Dateigröße (Distribution) | `dcat:byteSize` | — |

### REST API

#### Catalog
```
GET /wp-json/datenatlas/v1/catalog
```
Liefert alle veröffentlichten Datasets als `dcat:Catalog` in JSON-LD.

| Parameter  | Standard | Beschreibung                                       |
|------------|----------|----------------------------------------------------|
| `page`     | 1        | Seitennummer                                       |
| `per_page` | 20       | Einträge pro Seite (max. 100)                      |
| `theme`    | –        | Filter nach Thema (z.B. `Bildung`)                |
| `license`  | –        | Filter: Kurzform (`cc-by`) oder volle URI          |
| `format`   | `jsonld` | `jsonld` → `application/ld+json`, `json` → `application/json` |

Response-Header: `X-WP-Total`, `X-WP-TotalPages`, `X-ODW-Cache` (`HIT`/`MISS`)

#### Einzel-Dataset
```
GET /wp-json/datenatlas/v1/datasets/<id>
```

#### Beispiel-Response

```json
{
  "@context": {
    "dcat": "https://www.w3.org/ns/dcat#",
    "dct": "http://purl.org/dc/terms/",
    "foaf": "http://xmlns.com/foaf/0.1/"
  },
  "@type": "dcat:Catalog",
  "dcat:dataset": [
    {
      "@type": "dcat:Dataset",
      "dct:title": "Mitgliederdaten 2023",
      "dct:description": "Anonymisierte Mitgliederstatistik.",
      "dct:publisher": {
        "@type": "foaf:Organization",
        "foaf:name": "Musterorganisation e.V."
      },
      "dct:license": "https://creativecommons.org/licenses/by/4.0/",
      "dcat:distribution": [
        {
          "@type": "dcat:Distribution",
          "dcat:accessURL": "https://organisation.de/daten/mitglieder.csv",
          "dct:format": "text/csv"
        }
      ]
    }
  ]
}
```

### Erweiterbarkeit

Das Plugin stellt folgende WordPress-Filter zur Erweiterung bereit:

| Hook                  | Beschreibung                                      |
|-----------------------|---------------------------------------------------|
| `odw_license_options` | Weitere Lizenz-Optionen hinzufügen                |
| `odw_theme_options`   | Weitere Thema-Optionen hinzufügen                 |
| `odw_dataset_jsonld`  | JSON-LD Array vor Ausgabe anpassen                |
| `odw_catalog_title`   | Catalog-Titel anpassen                            |

```php
// Eigene Lizenz hinzufügen
add_filter( 'odw_license_options', function( array $options ): array {
    $options['https://example.com/custom-license'] = 'Custom License 1.0';
    return $options;
} );

// JSON-LD Dataset anpassen
add_filter( 'odw_dataset_jsonld', function( array $jsonld, int $post_id ): array {
    $jsonld['dct:spatial'] = 'https://sws.geonames.org/2921044/'; // Deutschland
    return $jsonld;
}, 10, 2 );
```

### Abhängigkeiten

| Paket | Version | Lizenz |
|---|---|---|
| [Carbon Fields](https://carbonfields.net/) | ^3.6 | MIT |

---

## Roadmap

- [ ] Delta-Harvesting Endpoint (`/changes?since=<timestamp>`)
- [ ] Push/Webhook bei Statusänderung
- [ ] Content Negotiation: Turtle / RDF-XML
- [ ] Automatische Metadatenextraktion aus hochgeladenen Dateien
- [ ] Qualitätsindikatoren (Vollständigkeit, Lizenzklarheit)
- [ ] Mehrsprachigkeit (WPML/Polylang)
- [ ] CESSDA-Felder als optionales Profil

---

## Mitwirken

Beiträge sind willkommen — ob Fehlermeldungen, Verbesserungsvorschläge oder Pull Requests.

Bitte öffne zunächst ein [Issue](https://github.com/daimpad/OpenDataWizard/issues), bevor du größere Änderungen einreichst.

```bash
git checkout -b feature/mein-feature
git commit -m "Add: Kurzbeschreibung"
git push origin feature/mein-feature
```

---

## Deinstallation

Das Plugin löscht bei Deinstallation standardmäßig **keine** Daten (Opt-in). Um alle Plugin-Daten zu löschen, die Option `odw_delete_data_on_uninstall` auf `true` setzen:

```php
update_option( 'odw_delete_data_on_uninstall', true );
```

Danach das Plugin im WordPress-Backend deinstallieren.

---

## Lizenz

GPL-2.0-or-later — siehe [`LICENSE`](./LICENSE)
