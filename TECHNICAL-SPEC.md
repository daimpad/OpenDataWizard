# Technische Spezifikationen — Open Data Wizard

> Zentrale Ablage für alle technischen Festlegungen des Open Data Wizard. Zurück zur Übersicht: [README](README.md).

## 📌 Umsetzungsstand

Stand: **v2.5.1**. Abgeleitet aus der Analyse des piveau Data Provider Interface (siehe Abschnitt 1).

| Phase | Inhalt | Status |
|---|---|---|
| **A** | Konformitäts-Korrekturen (CESSDA→`dct:subject`, kanonischer `dcat`-NS, `dcatde:licenseAttributionByText`), vollständige `@context`-Namespaces, deklaratives Feld-Registry-Schema | ✅ v2.3.2 / 2.4.0 / 2.5.1 |
| **B** | DCAT-AP.de-Felder (`contributorID`, `originator`, `maintainer`, `availability`) + generisches Vokabular-Autosuggest | ✅ v2.5.0 |
| **C** | HVD-Unterstützung (`dcatap:hvdCategory` + `dcatap:applicableLegislation`) | ✅ v2.4.0 |
| **D** | Mehrsprachige Literale (`@language`/`@value`) für title/description/keyword (Output-Tagging, ohne Migration) | ✅ v2.9.0 |
| **E** | Multi-Distribution (opt-in, wiederholbare Distributionen) | ☐ offen |
| — | Profi-UX (ausklappbarer „Erweiterte Angaben"-Bereich) ✅ v2.8.0; Hilfe-Tooltips + Live-Vorschau ✅ v2.11.0; Registry-getriebenes Formular/JSON-LD ☐ | teils |

Details zur phasierten Umsetzungsplanung in Abschnitt 7.

---


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
| `data-theme` | `http://publications.europa.eu/resource/authority/data-theme/` | `dcat:theme` | ✅ (Select + gebündelt) |
| `language` | `http://publications.europa.eu/resource/authority/language/` | `dct:language` | ⚠️ Freitext |
| `file-type` | `http://publications.europa.eu/resource/authority/file-type/` | `dct:format` | ✅ (`dct-format-list.php`) |
| `frequency` | `http://publications.europa.eu/resource/authority/frequency/` | `dct:accrualPeriodicity` | ✅ URI |
| `access-right` | `http://publications.europa.eu/resource/authority/access-right/` | `dct:accessRights` | ✅ (gebündelt) |
| `licence` | `http://dcat-ap.de/def/licenses/` | `dct:license` | ✅ (`licenses.txt`) |
| `planned-availability` | `http://publications.europa.eu/resource/authority/planned-availability/` | `dcatap:availability` | ✅ (Auswahl) |
| `contributors` | `http://dcat-ap.de/def/contributors/` | `dcatde:contributorID` | ✅ (gebündelt, 69) |
| `political-geocoding-level` | `http://dcat-ap.de/def/politicalGeocoding/Level/` | `dcatde:politicalGeocodingLevelURI` | ✅ (Auswahl) |
| `eurovoc` | `http://eurovoc.europa.eu/` | `dct:subject` | ⚠️ (via CESSDA, nicht EuroVoc) |
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
| `dct:title` | lang-Literal | AP | M (1..n) | ✅ |
| `dct:description` | lang-Literal | AP | M (1..n) | ✅ |
| `dct:publisher` | `foaf:Agent` | AP | R (0..1) | ⚠️ Freitext |
| `dcat:contactPoint` | `vcard:Kind` | AP | R (0..n) | ⚠️ name/mail/url |
| `dcat:distribution` | `dcat:Distribution` | AP | R (0..n) | ⚠️ genau 1 |
| `dcat:keyword` | lang-Literal | AP | R (0..n) | ✅ |
| `dcat:theme` | URI (`data-theme`) | AP | R (0..n) | ✅ (Select + Zusatz-URI) |
| `dct:spatial` | `dct:Location` | AP | O (0..n) | ✅ (GeoNames) |
| `dct:temporal` | `dct:PeriodOfTime` | AP | O (0..n) | ✅ start/end |
| `dct:issued` / `dct:modified` | Datum | AP | O (0..1) | ✅ |
| `dct:accessRights` | URI (`access-right`) | AP | O (0..1) | ✅ |
| `dct:accrualPeriodicity` | URI (`frequency`) | AP | O (0..1) | ✅ |
| `dct:language` | URI (`language`) | AP | O (0..n) | ⚠️ Freitext |
| `dct:identifier` | Literal | AP | O (0..n) | ✅ (`adms:identifier` ❌) |
| `dct:subject` | URI (`eurovoc`) | AP | O (0..n) | ✅ (via CESSDA-Thema) |
| `dct:creator` | `foaf:Agent` | AP | O (0..n) | ✅ |
| `dct:type` | URI (`dataset-type`) | AP | O (0..1) | ✅ |
| `dcat:landingPage` | `foaf:Document` | AP | O (0..n) | ✅ |
| `dct:conformsTo` / `dct:provenance` | Node | AP | O (0..n) | ✅ |
| `owl:versionInfo` / `adms:versionNotes` | Literal | AP | O | ✅ |
| `dcat:spatialResolutionInMeters` | Dezimal | AP | O | ✅ |
| `dcat:temporalResolution` | Duration | AP | O | ✅ |
| `prov:qualifiedAttribution`, `dcat:qualifiedRelation`, `prov:wasGeneratedBy` | Node | AP | O | ❌ |
| `dcatde:politicalGeocodingLevelURI` | URI | DE | R (DE) | ✅ |
| `dcatde:politicalGeocodingURI` | URI | DE | O (0..n) | ✅ |
| `dcatde:geocodingDescription` | lang-Literal | DE | O | ❌ |
| `dcatde:contributorID` | URI (`contributors`) | DE | R (DE) | ✅ (Autosuggest) |
| `dcatde:legalBasis` | lang-Literal | DE | O | ✅ |
| `dcatde:qualityProcessURI` | URI | DE | O | ✅ |
| `dcatde:originator` / `dcatde:maintainer` | `foaf:Agent` | DE | O | ✅ |
| `dcatap:availability` | URI (`planned-availability`) | DE | R (DE) | ✅ |
| `dcatap:hvdCategory` | URI | HVD | M *wenn HVD* | ✅ |
| `dcatap:applicableLegislation` | URI | HVD | M *wenn HVD* | ✅ (auto) |

#### 4.2 Distribution (`dcat:Distribution`)

| DCAT-Prädikat | Range | Profil | Norm-Kard. | ODW |
|---|---|---|---|---|
| `dcat:accessURL` | URI | AP | M (1..n) | ✅ |
| `dcat:downloadURL` | URI | AP | O (0..n) | ✅ |
| `dct:format` | URI (`file-type`) | AP | R (0..1) | ✅ |
| `dcat:mediaType` | URI (`iana`) | AP | O (0..1) | ✅ |
| `dct:license` | URI (`licence`) | AP | R (0..1) | ✅ |
| `dcat:byteSize` | Nonneg-Integer | AP | O (0..1) | ✅ |
| `dct:title` / `dct:description` | lang-Literal | AP | O | ✅ |
| `dcatap:availability` | URI (`planned-availability`) | DE | R (DE) | ✅ |
| `spdx:checksum` | Node | AP | O | ❌ |
| `dcat:accessService` | `dcat:DataService` | AP | O | ❌ |
| `dct:rights` | URI/Node | AP | O | ✅ |
| `dct:conformsTo`, `dct:issued`, `dct:modified` (Distribution) | div. | AP | O | ❌ |
| `adms:status` | URI | AP | O | ❌ |
| `odrl:hasPolicy` | Node | AP | O | ❌ |
| `dcat:compressFormat` / `packageFormat` | URI (`iana`) | AP | O | ❌ |
| `foaf:page` | `foaf:Document` | AP | O | ❌ |
| `dcatde:licenseAttributionByText` | lang-Literal | DE | O (bei CC-BY) | ✅ |

> **Erledigt (v2.3):** Der Zuschreibungstext wird konform als `dcatde:licenseAttributionByText` ausgegeben
> (zuvor fälschlich `odrl:attribution`). Das CESSDA-Thema wird als `dct:subject` ausgegeben (zuvor undeklariertes
> `cessda:`-Präfix → ungültiges JSON-LD), und der `dcat`-Namespace nutzt die kanonische `http://`-Form.

#### 4.3 Catalogue (`dcat:Catalog`)

ODW erzeugt den Katalog **automatisch** im REST-Endpoint `/catalog` (eine WordPress-Site = ein Katalog). Die
Catalogue-Felder (`dct:title`, `dct:publisher`, `dct:license`, `dct:language`, `foaf:homepage`, `dct:spatial`)
gehören daher in die **Einstellungsseite**, nicht in das Dataset-Formular. Aktuell werden Titel/Publisher
unterstützt; `dct:license`, `dct:language`, `dcat:themeTaxonomy` und `dct:spatial` des Katalogs sind offen.

### 5. Erweiterung der Feld-Registry (`config/dcat-ap-fields.php`) — ✅ umgesetzt (v2.5.1)

Jeder Registry-Eintrag trägt die Basis-Schlüssel `key`, `meta_key`, `dcat_prop`, `label`, `points`, `required`
sowie seit v2.5.1 die **deklarativen Schema-Metadaten** `profile`, `tier`, `range`, `cardinality`, `entity`, `vocab`.
Damit ist die Registry die dokumentierte Single Source of Truth für Pflichtigkeit, Kardinalität und Wertform
(wie piveaus `input-definition.ts`). Die Metadaten sind **abwärtskompatibel** — bestehende Konsumenten
(Qualität, Validierung) lesen weiterhin nur die Basis-Schlüssel; das 0–100-Punkteschema bleibt unverändert.
Eine Schema-Validierung sichert die Invarianten (`tests/test-registry-schema.php`):

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
- ✅ Korrektur: Zuschreibungstext `odrl:attribution` → `dcatde:licenseAttributionByText` (erledigt v2.3).
- ✅ Korrektur: CESSDA-Thema als `dct:subject` statt undeklariertem `cessda:`-Präfix (gültiges JSON-LD).
- ✅ Korrektur: `dcat`-Namespace auf kanonisches `http://www.w3.org/ns/dcat#`.
- ✅ Namespace-Registry (Abschnitt 2) vollständig in `@context` aufgenommen (`dcatap`, `locn`, `adms`, `owl`, `prov`, `odrl`, `spdx`) — v2.4.0.
- ✅ Feld-Registry-Schema (Abschnitt 5) um die deklarativen Schlüssel erweitert; bestehende Einträge angereichert — v2.5.1.
- ✅ Formular-Bereich „Erweiterte Angaben (für Profis)" als ausklappbarer Abschnitt in Tab 4 (v2.8.0, Progressive Enhancement).
- ✅ Hilfe-Tooltips (ⓘ-Popups statt Inline-Hilfetext) + Live-Vorschau in Tab 5 (Pflichtangaben-Checkliste mit Fortschritt + Zusammenfassungs-Karte, Aktualisierung beim Tippen) — v2.11.0, Progressive Enhancement (`assets/js/odw-admin-fields.js`, `assets/css/admin.css`, `ODW_Fields::get_live_preview_fields()`).
- _Betroffen:_ `class-fields.php`, `class-rest-api.php`/JSON-LD-Builder, `config/dcat-ap-fields.php`.

#### Phase B — DCAT-AP.de & Vokabulare (v2.5) — ✅ weitgehend umgesetzt
- ✅ DCAT-AP.de-Felder: `dcatde:contributorID`, `dcatde:originator`, `dcatde:maintainer`, `dcatap:availability` (zzgl. `dcatde:politicalGeocodingLevelURI` aus v2.3).
- ✅ Generisches Vokabular-Autosuggest (`data-odw-vocab="<id>"`) + lokal gebündelte Vokabulardateien unter `config/vocabularies/` (Start: `contributors`, 69 Einträge).
- ✅ DCAT-AP.de-Felder `politicalGeocodingURI`, `legalBasis`, `qualityProcessURI` (v2.6.0).
- ✅ Gebündelte Vokabulare `access-right` (Feld `dct:accessRights`) und `data-theme` (Zusatz-Theme) (v2.7.0).
- ☐ Offen (optional): vollständige EU-Sprachliste als Autosuggest (bewusst zurückgestellt).
- _Betroffen:_ `class-fields.php`, `class-admin.php`, `odw-admin-fields.js`, `config/vocabularies/*`, JSON-LD-Builder.

#### Phase C — HVD-Unterstützung (v2.4) — ✅ umgesetzt
- ✅ Schalter „High-Value-Dataset" (Tab 4) → bedingtes Pflichtfeld `dcatap:hvdCategory` (sechs EU-Kategorien) + automatisches `dcatap:applicableLegislation` (Reg. 2023/138).
- ✅ Bedingte Validierung: HVD-Kategorie ist Pflicht, sobald der Datensatz als HVD markiert ist (`class-validation.php`).
- ✅ `dcatap`-Namespace + restliche Standard-Präfixe (`locn`, `adms`, `owl`, `prov`, `odrl`, `spdx`) im `@context` ergänzt.
- _Betroffen:_ `class-fields.php` (Felder, Options-Helfer, JSON-LD-Builder), `class-validation.php`, `class-rest-api.php`.

#### Phase D — Mehrsprachige Literale (v2.9.0) — ✅ umgesetzt
- ✅ `dct:title`, `dct:description`, `dcat:keyword` werden als sprachgetaggte Literale (`{"@value","@language"}`) ausgegeben.
- ✅ **Output-Tagging gewählt (statt Datenmodell-Migration):** Werte bleiben als Klartext gespeichert; die Sprache wird bei der JSON-LD-Erzeugung aus dem Sprache-Feld (EU-URI → BCP-47, `odw_resolve_language_tag()`) abgeleitet, Rückfall Standardsprache → `de`. Dadurch **keine Datenmigration** und keine UX-Änderung; eine Sprache pro Feld.
- _Betroffen:_ `class-fields.php` (Builder + `odw_lang_literal()`/`odw_resolve_language_tag()`), `config/dcat-ap-fields.php` (range → `literal-lang`), Tests.
- ☐ Offen (optional/künftig): echtes mehrsprachiges Datenmodell mit mehreren Sprachen je Feld (wiederholbare Eingabe + Migration).

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
