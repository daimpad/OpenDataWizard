<img width="6408" height="2002" alt="Vector-Logo-of-Open-Data-Wizard" src="https://github.com/user-attachments/assets/24adf4f5-ec4a-4b19-a9e9-c02d55da00c5" />

# Open Data Wizard 🧙 

![Lizenz](https://img.shields.io/github/license/daimpad/OpenDataWizard?style=flat-square&color=blue&label=Lizenz)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-8892BF?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-compatible-21759B?style=flat-square&logo=wordpress&logoColor=white)
![DCAT-AP](https://img.shields.io/badge/DCAT--AP-3.0-brightgreen?style=flat-square)
![Status](https://img.shields.io/badge/Status-v2.0.0%20in%20Entwicklung-brightgreen?style=flat-square)
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

### 😊 Benutzerfreundliche Formularsprache
**Neu in v2.0.0:** Das Wizard-Formular wurde komplett überarbeitet, um es auch ohne DCAT-AP-Kenntnisse intuitiv zu machen:
- **Klare Fragen statt technischer Begriffe:** Statt „Herausgebende Organisation (dct:publisher)" fragt das Plugin jetzt: „Wer gibt diese Daten heraus?"
- **Hilfreiche Beispiele:** Jedes Feld hat konkrete, praxisnahe Beispiele (z.B. „Beispiel: Musterstadt Statistikamt, Umweltbundesamt")
- **Ursprüngliche Labels in Hilfetexten:** DCAT-AP Bezeichnungen und technische Details bleiben in den Hilfetexten sichtbar — nichts geht verloren
- **Validierungsmeldungen in Klartext:** Fehler zeigen neue, verständliche Feldnamen statt technischer Ausdrücke

### 🧭 Geführter Wizard
Fünf-Tab-Assistent mit Pflichtfeldprüfung, benutzerfreundlichen Frageformulierungen und praktischen Beispielen:

1. **Pflichtangaben** — „Wer gibt diese Daten heraus?", „Worum geht es in diesem Datensatz?", „Unter welcher Lizenz sind diese Daten verfügbar?"
2. **Optionale Angaben** — „In welcher Sprache sind die Daten?", „Mit welchen Stichworten finde ich diese Daten?", „In welche Kategorie gehört dieser Datensatz?", Zeitangaben, Änderungsdatum (auto)
3. **Distribution** — „Wo können die Daten heruntergeladen werden?" (wiederholbar): Zugriffs-URL, Format, Dateigröße mit Inline-Hilfe und Beispielen
4. **Erweiterte Angaben** — „Wo finde ich mehr Informationen?", „Wie oft werden diese Daten aktualisiert?", geografische und zeitliche Abdeckung, Kontaktinformationen
5. **Vorschau** — generiertes JSON-LD live einsehen

**Phase 1+2 UX-Verbesserungen (v2.0.0):** Alle 19 Formularfelder wurden mit benutzergerechten Frageformulierungen statt technischen DCAT-AP-Begriffen neugestaltet. Jedes Feld hat Hilfetexte mit dem Original-Label, der DCAT-AP Bezeichnung und praktischen Beispielen. Dies macht das Plugin auch für Anfänger ohne DCAT-AP-Kenntnisse intuitiv bedienbar.

### 📎 Download-Datei (nativer wp.media Upload)
Sidebar-Meta-Box auf dem Edit-Screen — vollständig unabhängig von Carbon Fields:
- **„Datei auswählen / hochladen"**-Button öffnet den nativen WordPress Media Library Frame (`wp.media`)
- Dateivorschau zeigt den Attachment-Titel mit Dokumenten-Icon; „Entfernen"-Button mit Capability-Check
- Beim Speichern werden `_odw_file_size` (Bytes) und `_odw_file_format` (z.B. „CSV") automatisch aus der Datei berechnet und gespeichert — kein Runtime-I/O beim Shortcode-Rendering nötig
- JavaScript (`assets/js/odw-file-upload.js`, jQuery): Media-Frame-Instanz wird wiederverwendet; aktueller Dateiname via `wp_localize_script` aus PHP initialisiert
- Sicherheit: `wp_verify_nonce` + `current_user_can('edit_post')` im PHP-Save-Handler

### ⚙️ Einstellungsseite
Untermenü unter *Datensätze → Einstellungen* mit vier Bereichen:
- **Katalog** — Titel (überschreibt DCAT-AP `dct:title` im Catalog-Endpoint) und Herausgebende Organisation
- **Standardwerte** — Standard-Lizenz und -Sprache (werden bei neuen Datensätzen vorausgefüllt)
- **REST API** — Cache-Laufzeit (60–86400 Sekunden)
- **Deinstallation** — Opt-in Checkbox für vollständige Datenlöschung

### 📊 Qualitätsindikatoren
Automatische Metadaten-Vollständigkeitsprüfung nach DCAT-AP 3.0 (0–100 Punkte, Ampellogik):
- **Grün** (≥ 80 Pkt.) · **Gelb** (50–79 Pkt.) · **Rot** (< 50 Pkt.)
- Berechnung nach jedem Speichern; Ergebnis in der Admin-Listenansicht und als Meta-Box sichtbar
- Score wird im JSON-LD als `odw:qualityScore` mitgeliefert

### 📥 Download-Card Shortcode
```
[odw_dataset id="123"]
```
Rendert eine strukturierte Download-Card im Frontend: Titel, Thema-Badge, Lizenz, DCAT-Qualitätsscore, Dateigröße/-format und Download-Button. CSS (`assets/css/frontend.css`) wird nur auf Seiten geladen, die den Shortcode enthalten.

### 🔗 REST API Endpoints

Das Plugin stellt öffentliche REST API Endpoints bereit:

```
GET https://deine-website.de/wp-json/datenatlas/v1/catalog
GET https://deine-website.de/wp-json/datenatlas/v1/datasets/<id>
GET https://deine-website.de/wp-json/datenatlas/v1/delta?since=<ISO8601>
```

Diese URLs können bei einer Open-Data-Plattform als Harvest-Quelle eingetragen werden — einmalig, ohne weiteren Aufwand.

**Catalog-Parameter:** `page`, `per_page`, `theme`, `license`, `format` (`jsonld` oder `json`)

**Delta-Parameter:** `since` (erforderlich, ISO 8601), `page`, `per_page`, `format` — liefert nur Datensätze, die nach dem angegebenen Zeitstempel geändert wurden, plus Tombstones für gelöschte Datensätze (ideal für inkrementelles Harvesting)

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
├── open-data-wizard.php              # Plugin-Header & Bootstrap (v1.9.0)
├── uninstall.php                     # Opt-in Datenlöschung
├── composer.json
├── phpstan.neon                      # Statische Analyse Level 6
├── phpunit.xml                       # PHPUnit 9 Konfiguration
├── vendor/                           # Carbon Fields + Dev-Dependencies (gebündelt)
├── includes/
│   ├── class-post-types.php          # CPT-Registrierung: odw_dataset
│   ├── class-fields.php              # Carbon Fields (5 Tabs), DCAT-AP Mapping, JSON-LD Builder
│   ├── class-rest-api.php            # REST Endpoints /catalog + /datasets/<id> + /delta, Transient-Cache [v1.9.0]
│   ├── class-validation.php          # Pflichtfeldprüfung vor Veröffentlichung
│   ├── class-quality.php             # Qualitätsindikatoren (0–100 Punkte, Ampellogik)  [v1.3.0]
│   ├── class-admin.php               # Listenansicht, wp.media Meta-Box, Help Tabs, Assets
│   ├── class-shortcode.php           # [odw_dataset]-Shortcode, Download-Card             [v1.4.0]
│   ├── class-setup.php               # Demo-Datensatz bei Aktivierung, Willkommens-Notice [v1.5.0]
│   ├── class-settings.php            # Einstellungsseite (Catalog, Defaults, API, Cleanup) [v1.6.0]
│   └── class-cli.php                 # WP-CLI Befehle (Massenoperationen)                  [v2.0.0]
├── assets/
│   ├── js/
│   │   ├── wizard-tabs.js            # Tab-Navigation (Vanilla JS, kein jQuery)
│   │   └── odw-file-upload.js        # wp.media Upload-Widget (jQuery)                   [v1.8.0]
│   ├── css/
│   │   ├── admin.css                 # Admin-Styles (CSS Custom Properties)
│   │   └── frontend.css              # Shortcode Download-Card (strukturell, kein Theme-Overhead)
│   └── sample/
│       └── beispiel-datensatz.csv    # Demo-Datensatz für die Aktivierungs-Installation
├── tests/
│   ├── bootstrap.php                 # PHPUnit + WP_Mock Bootstrap
│   ├── test-fields.php               # Tests: ODW_Fields (Lizenz, Format, Pflichtfelder)
│   ├── test-settings.php             # Tests: ODW_Settings (get(), filter_catalog_title())
│   ├── test-quality.php              # Tests: ODW_Quality (Scoring, Level, Meta)
│   ├── test-shortcode.php            # Tests: ODW_Shortcode (format_bytes, render edge cases)
│   └── test-fields-extended.php      # Tests: JSON-LD Builder v1.7.0 Felder
├── .github/workflows/ci.yml          # CI: PHPCS + PHPStan + PHPUnit (PHP 8.1–8.3)
└── languages/
```

### Feldmapping DCAT-AP 3.0

#### Tab 1 — Pflichtangaben

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Titel | `dct:title` | ✓ |
| Beschreibung | `dct:description` | ✓ |
| Herausgeber | `dct:publisher` → `foaf:Organization` | ✓ |
| Lizenz | `dct:license` (URI) | ✓ |

#### Tab 2 — Optionale Angaben

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Sprache | `dct:language` | — |
| Schlagworte (eine pro Zeile) | `dcat:keyword` | — |
| Thema | `dcat:theme` | — |
| Veröffentlichungsdatum | `dct:issued` | — |
| Änderungsdatum | `dct:modified` (auto) | — |

#### Tab 3 — Distribution

| Feld | DCAT-AP Prädikat | Pflicht |
|---|---|---|
| Zugriffs-URL | `dcat:accessURL` | ✓ (min. 1) |
| Format | `dct:format` (MIME) | — |
| Dateigröße in Bytes | `dcat:byteSize` | — |

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
| Attachment-ID | `_odw_file_id` | Mediathek-Datei (wird als Download-Button im Shortcode verwendet) |
| Dateigröße (auto) | `_odw_file_size` | Bytes, auto-berechnet beim Speichern |
| Dateiformat (auto) | `_odw_file_format` | Großbuchstaben-Extension (z.B. „CSV"), auto-berechnet |

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

#### Delta-Harvesting (inkrementell)
```
GET /wp-json/datenatlas/v1/delta?since=2024-01-01T00:00:00Z
```
Liefert nur Datensätze, die nach dem `since`-Zeitstempel geändert wurden, plus Tombstone-Einträge für gelöschte Datensätze.

| Parameter  | Standard | Beschreibung                                       |
|------------|----------|----------------------------------------------------|
| `since`    | erforderlich | ISO 8601 Datetime (z.B. `2024-01-01`, `2024-06-15T12:30:00Z`) — nur Änderungen nach dieser Zeit |
| `page`     | 1        | Seitennummer (für geänderte Datasets)             |
| `per_page` | 20       | Einträge pro Seite (max. 100); gilt nur für geänderte Datasets, nicht für Tombstones |
| `format`   | `jsonld` | `jsonld` → `application/ld+json`, `json` → `application/json` |

Response-Header: `X-ODW-Delta-Since` (echo des Parameters), `X-ODW-Generated-At` (aktueller Zeitstempel — nutze ihn als nächster `since` Wert), `X-WP-Total`, `X-WP-TotalPages`, `X-ODW-Cache`

Response-Body enthält:
- `dcat:dataset` — Array mit vollständigen JSON-LD Objekten aller geänderten Datasets
- `odw:removed` — Array mit Tombstone-Objekten (`@id`, `@type`, `odw:removedAt`) für gelöschte Datasets

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
      "dct:license": "https://creativecommons.org/licenses/by/4.0/",
      "dcat:distribution": [
        {
          "@type": "dcat:Distribution",
          "dcat:accessURL": "https://organisation.de/daten/mitglieder.csv",
          "dct:format": "text/csv",
          "dcat:byteSize": 20480
        }
      ],
      "dcat:contactPoint": {
        "@type": "vcard:Organization",
        "vcard:fn": "Open Data Team",
        "vcard:hasEmail": "mailto:opendata@organisation.de"
      },
      "odw:qualityScore": { "odw:score": 85, "odw:maxScore": 100, "odw:level": "high" }
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

### WP-CLI Befehle

Das Plugin stellt WP-CLI Befehle für Massenoperationen bereit:

```bash
# Qualitätsscores für alle veröffentlichten Datasets neu berechnen
wp open-data-wizard quality recalculate

# Alle Datasets einschließlich Entwürfe und Trash berechnen
wp open-data-wizard quality recalculate --all

# Alle REST API Transient-Caches löschen
wp open-data-wizard cache clear
```

Diese Befehle sind nützlich für:
- Regelmäßige Qualitäts-Neubewertung nach Massenänderungen
- Cache-Invalidierung nach manuellen DB-Eingriffen oder Migrationen
- Automatisierung via Cron-Jobs oder CI/CD-Pipelines

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

## Roadmap

**Abgeschlossen (v1.0 — v2.0.0):**
- [x] Custom Post Type `odw_dataset` mit vollständiger DCAT-AP 3.0 Unterstützung — v1.0.0
- [x] Five-Tab Wizard-Formular mit Validierung und Hilfetexten — v1.0.0
- [x] REST API Endpoints: `/catalog`, `/datasets/<id>`, `/delta?since=<timestamp>` — v1.9.0
- [x] Qualitätsindikatoren / Ampellogik (0–100 Punkte) — v1.3.0
- [x] Download-Card Shortcode `[odw_dataset]` — v1.4.0
- [x] Demo-Datensatz bei Aktivierung — v1.5.0
- [x] Einstellungsseite (Catalog-Titel, Defaults, API, Cleanup) — v1.6.0
- [x] Erweiterte DCAT-AP Felder (Tab 4) — v1.7.0
- [x] Nativer wp.media Upload-Widget — v1.8.0
- [x] **Benutzerfreundliche UX-Überarbeitung (Phase 1+2)** — v2.0.0
- [x] WP-CLI Befehle für Massenoperationen — v2.0.0

**In Planung (v2.1+):**
- [ ] Push/Webhook bei Statusänderung an Piveau
- [ ] Content Negotiation: Turtle / RDF-XML Ausgabe
- [ ] Gutenberg Block für die Download-Card
- [ ] Mehrsprachigkeit (WPML/Polylang)
- [ ] CESSDA-Felder als optionales Profil
- [ ] Phase 3 UX: Tooltip-Popups, Video-Tutorials, Wizard-Vorschau

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

Das Plugin löscht bei Deinstallation standardmäßig **keine** Daten (Opt-in).

Um alle Plugin-Daten zu löschen, die Checkbox unter **Datensätze → Einstellungen → Deinstallation** aktivieren und dann das Plugin im WordPress-Backend deinstallieren. `uninstall.php` entfernt in diesem Fall alle `odw_dataset`-Posts, alle `_odw_*`-Metafelder sowie die Plugin-Optionen (`odw_settings`, `odw_demo_post_id`, `odw_show_welcome`).

---

## Lizenz

GPL-2.0-or-later — siehe [`LICENSE`](./LICENSE)
