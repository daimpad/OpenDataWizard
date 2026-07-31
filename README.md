<img width="6408" height="2002" alt="Vector-Logo-of-Open-Data-Wizard_white" src="https://github.com/user-attachments/assets/a6296bc2-2952-4c27-84c9-a6d531886335" />


# Open Data Wizard 🧙 

![Lizenz](https://img.shields.io/github/license/daimpad/OpenDataWizard?style=flat-square&color=blue&label=Lizenz)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-8892BF?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-compatible-21759B?style=flat-square&logo=wordpress&logoColor=white)
![DCAT-AP](https://img.shields.io/badge/DCAT--AP-3.0-brightgreen?style=flat-square)
![Version](https://img.shields.io/badge/Version-2.34.2-brightgreen?style=flat-square)
![PRs Welcome](https://img.shields.io/badge/PRs-willkommen-brightgreen?style=flat-square)

📖 [Dokumentation](DOCUMENTATION.md) · 📋 [Feld-Referenz](docs/FELD-REFERENZ.md) · 📐 [Technische Spezifikation](TECHNICAL-SPEC.md) · 📝 [Changelog](CHANGELOG.md) · 🛡️ [Security](SECURITY.md) · ⚖️ [Lizenz](LICENSE)

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
- **Validierungsmeldungen in Klartext, mit Ort:** Wird die Veröffentlichung blockiert, nennt die Meldung den Tab und den verständlichen Feldnamen (der technische DCAT-AP-Begriff steht nur klein daneben) — ein Klick auf „Zum Feld springen" öffnet den passenden Tab und hebt das Feld hervor

### 🧭 Geführter Wizard
Fünf-Tab-Assistent mit praktischen Beispielen. Pflichtfelder sind mit einem roten Sternchen (`*`) gekennzeichnet; als **Entwurf** lässt sich jederzeit unvollständig speichern — erst zum **Veröffentlichen** müssen alle Pflichtangaben ausgefüllt sein.

1. **Grundlegende Informationen** — „Wer gibt diese Daten heraus?", „Worum geht es in diesem Datensatz?", „Welchem Thema ist dieser Datensatz zugeordnet?". Weniger häufige Angaben (CESSDA-Themenklassifikation, ZiviZ-Engagementfeld, Titel-/Beschreibungs-Übersetzungen) liegen in einer aufklappbaren Untergruppe am Tab-Ende.
2. **Inhaltliche Angaben** — „In welcher Sprache sind die Daten?", „Mit welchen Schlagworten finde ich diese Daten?", Veröffentlichungs- und Änderungsdatum
3. **Datenbereitstellung** — Zugriffs-URL **oder** Datei-Upload (Mediathek), Format, Dateigröße, **Lizenz (Pflicht je Distribution)**, Namensnennungstext; optional weitere Distributionen (wiederholbar)
4. **Erweiterte Angaben** — Projektseite, Aktualisierungsfrequenz, geografische und zeitliche Abdeckung, Kontaktinformationen, Verantwortlichkeiten (Urheber/pflegende Stelle, GovData-Contributor-ID), High-Value-Datensatz (HVD) Kategorie
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
- `theme` — Thema (z.B. SOCI, ECON, EDUC)
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

Die Batch-Import-Seite ist vollständig ins WordPress-Admin-Design integriert (Standard-Buttons, dezente Icons).

[📥 CSV-Beispiel herunterladen](./samples/import-example.csv)  |  [📄 JSON-Beispiel](./samples/import-example.json)

### 📎 Datei-Upload (Mediathek)
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
Automatische Metadaten-Qualitätsprüfung nach der [EU-MQA-Methodik](https://data.europa.eu/mqa/methodology). Der Leitwert ist überall **Prozent + Stufe** (z. B. „72 % · Gut"):

| Stufe | Prozent | Bedeutung |
|---|---|---|
| Ausgezeichnet | ≥ 87 % | nahezu vollständig |
| Gut | 55–86 % | über der Mindestanforderung |
| Ausreichend | 30–54 % | Grundangaben vorhanden |
| Mangelhaft | < 30 % | wesentliche Angaben fehlen |

Berechnung nach jedem Speichern. Die **Listenspalte** zeigt Prozent + Stufe; die **Qualitäts-Meta-Box** ergänzt den MQA-Rohwert (z. B. „54 / 259 Punkte, von max. 405") als Detailzeile.

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

**Catalog-Parameter:** `page`, `per_page`, `theme`, `license`, `format` (`jsonld`, `json` oder `turtle`), `full`

**Delta-Parameter:** `since` (erforderlich, ISO 8601), `page`, `per_page`, `format` — liefert nur Datensätze, die nach dem angegebenen Zeitstempel geändert wurden, plus Tombstones für gelöschte Datensätze

### 🌾 Harvesting durch piveau/Civora (Datenatlas Zivilgesellschaft)

Portale auf Basis von **piveau/Civora** — etwa der [Datenatlas Zivilgesellschaft](https://datenatlas-zivilgesellschaft.de) — holen Metadaten per **Pull-Harvesting** ab: Sie geben dem Betreiber **eine stabile URL**, unter der Ihr **kompletter Katalog als ein DCAT-AP.de-Dokument** liegt. Genau dafür bietet der Catalog-Endpoint einen **Voll-Modus**:

```
# Vollständiger Katalog als Turtle (empfohlen für RDF-Harvester)
GET https://deine-website.de/wp-json/datenatlas/v1/catalog?full=1&format=turtle   →  text/turtle

# … oder als JSON-LD
GET https://deine-website.de/wp-json/datenatlas/v1/catalog?full=1                 →  application/ld+json
```

- **`full=1`** liefert **alle veröffentlichten Datensätze in einem Abruf** (ohne Paginierung) als `dcat:Catalog` → `dcat:Dataset` → `dcat:Distribution` — das Muster, das piveaus `importing-rdf`-Konnektor erwartet.
- **`format=turtle`** serialisiert denselben Graphen als **Turtle** (`text/turtle`) — ohne externe RDF-Bibliothek. JSON-LD (`application/ld+json`) und `json` bleiben verfügbar.
- Die Datensatz-URIs (`@id`) sind **über Releases stabil** (an die Post-ID gebunden), sodass Harvester Aktualisierungen/Löschungen korrekt zuordnen und keine Duplikate anlegen.

Die fertigen Harvest-URLs zeigt das Plugin **kopierfertig unter _Datensätze → Einstellungen → Harvesting (piveau/Civora)_** an.

#### So melden Sie den Katalog beim Datenatlas an
1. **Vorab validieren** — das Dokument gegen den [EU-DCAT-AP-SHACL-Validator](https://www.itb.ec.europa.eu/shacl/dcat-ap/upload) **und** die [GovData-DCAT-AP.de-SHACL-Validation](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation) prüfen; alle `sh:Violation` beheben (siehe [SHACL-Abschnitt](#-dcat-ap-validierung-shacl)).
2. **Onboarding per E-Mail** — dem Datenatlas-Team **(a)** die gewählte URL, **(b)** die Serialisierung (Turtle/JSON-LD), **(c)** einen gewünschten Katalognamen und **(d)** ein Aktualisierungsintervall mitteilen. Einrichtung laut Datenatlas-Doku ca. 1–2 Wochen (Testläufe + Mapping).

#### Vor der Anmeldung mit dem Betreiber (DKSR) zu klären
1. **DCAT-AP-Version** — prüft Civora gegen DCAT-AP.de 2.0 oder 3.0 (bzw. reines DCAT-AP 3.0)?
2. **Serialisierung/`inputFormat`** — Turtle, RDF/XML oder JSON-LD? Welcher `Content-Type`?
3. **Identifier-Konvention** — Regeln für `dct:identifier`/Dataset-URIs (Idempotenz bei Updates)?
4. **Delete-Semantik** — Vollabgleich (Datensatz verschwindet → gelöscht) oder explizites Statusfeld?
5. **Paginierung** — ab welcher Katalogröße ist Hydra-Paginierung erwünscht?
6. **Katalog-Ebene** — genau ein `dcat:Catalog` je Organisation? Wie wird der Katalogname/-slug festgelegt?
7. **Domänenfelder** — sollen sozialwissenschaftliche Felder (CESSDA, GND) mit ausgeliefert werden?
8. **Intervall & Monitoring** — welches Harvest-Intervall, und gibt es Zugang zu Job-/Fehlerberichten?
9. **Pflichtfeld `dcat:theme`** — ist das EU-`data-theme`-Vokabular verpflichtend, und wie werden zivilgesellschaftliche Themen darauf gemappt?

> **Push-API?** piveau bietet zwar einen API-Push (`PUT` mit `X-API-Key`), der Schlüssel liegt aber beim **Katalogbetreiber**, nicht beim Datenanbieter — und der Datenatlas dokumentiert keinen Anbieter-Push. Der richtige Weg für dieses Plugin ist daher der **Pull** über die obige Katalog-URL.

### ✅ DCAT-AP 3.0 Konformität
Alle Ausgaben sind DCAT-AP 3.0 konform und in JSON-LD serialisiert.

### 🔎 DCAT-AP-Validierung (SHACL)

Ob die erzeugten Metadaten dem Standard **formal** entsprechen, lässt sich mit **SHACL** prüfen — der
offiziellen Regelsprache, mit der die EU und GovData DCAT-AP-Konformität definieren. Damit du nicht auf
inoffizielle Regeln angewiesen bist, bringt das Plugin die **maßgeblichen, offiziellen Shape-Dateien**
gebündelt mit (unter [`config/shacl/`](config/shacl/)):

| Datei | Herkunft | Zweck | Lizenz |
|---|---|---|---|
| `dcat-ap-SHACL.ttl` | [SEMICeu/DCAT-AP](https://github.com/SEMICeu/DCAT-AP) (Release 3.0.0) | generische **EU-DCAT-AP-3.0**-Regeln | CC BY 4.0 |
| `dcat-ap-SHACL-DE.ttl` | [GovDataOfficial](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation) (v3.0) | **DCAT-AP.de**-Ergänzungen (u. a. `dcatde:`-Felder, deutschsprachige Meldungen) | CC0 |

> **Wichtig:** Das Plugin führt SHACL **nicht selbst** aus — dafür gibt es in PHP keine praxistaugliche
> Engine. Die Dateien liegen als **Referenz** bei; die eigentliche Prüfung erfolgt über einen externen
> Validierungsdienst oder ein lokales SHACL-Werkzeug. So bleibt das Plugin self-contained und ohne
> Pflicht-Netzwerkzugriff.

#### So validierst du einen Datensatz

1. **JSON-LD abrufen** — jeder veröffentlichte Datensatz ist über die REST-API erreichbar:
   ```
   https://<deine-site>/wp-json/datenatlas/v1/datasets/<ID>
   ```
   (den ganzen Katalog liefert `…/datenatlas/v1/catalog`).

2. **Gegen die Shapes prüfen** — mit einem der offiziellen Dienste (Datei hochladen bzw. JSON-LD einfügen):
   - **EU-Validator (SHACL / ITB):** <https://www.itb.ec.europa.eu/shacl/dcat-ap/upload>
   - **data.europa.eu MQA:** <https://data.europa.eu/mqa/>
   - **DCAT-AP.de-Validator** (Docker/ITB): siehe
     [DCAT-AP.de-SHACL-Validation](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation)

3. **Oder lokal** validieren, z. B. mit [pySHACL](https://github.com/RDFLib/pySHACL) gegen die gebündelten Shapes:
   ```bash
   # JSON-LD des Datensatzes speichern …
   curl -s "https://<deine-site>/wp-json/datenatlas/v1/datasets/42" -o datensatz.jsonld
   # … und gegen die DCAT-AP.de-Shapes prüfen
   pyshacl -s config/shacl/dcat-ap-SHACL-DE.ttl -df json-ld datensatz.jsonld
   ```

Der Validierungs-Report zeigt Verstöße (`sh:Violation`) mit Pfad und Meldung — so siehst du genau, welches
Feld ggf. nachzubessern ist.

#### Verhältnis zum Qualitäts-Score

Die integrierten **Qualitätsindikatoren** (siehe oben) decken die „gesetzt?"- und Vokabular-Prüfungen der
[EU-MQA-Methodik](https://data.europa.eu/mqa/methodology) bereits offline ab. Die MQA-Metrik
**„DCAT-AP-Konformität" (SHACL, 30 Punkte)** wird davon bewusst **nicht** automatisch bewertet, sondern über
die hier beschriebene externe Validierung geprüft (Ansatz „achievable max"). Details im
[MQA-Konzept](docs/MQA-KONZEPT.md).

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
composer install   # erzeugt vendor/ (Carbon Fields + PHPStan, WPCS, PHPUnit)
```

> **`composer install` ist zwingend erforderlich.** `vendor/` ist bewusst **nicht** im Repository —
> es wird reproduzierbar aus `composer.lock` erzeugt (lokal, in der CI und beim Release-Build).
> Ohne diesen Schritt fehlt Carbon Fields und das Plugin zeigt einen entsprechenden Admin-Hinweis.

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

## 🛠 Technische Dokumentation

Die ausführliche Entwickler-Dokumentation (Architektur, Dateistruktur, DCAT-AP-Feldmapping, REST-API,
Erweiterbarkeit, WP-CLI, Abhängigkeiten) ist in einem eigenen Dokument gepflegt:

**➡️ [DOCUMENTATION.md](DOCUMENTATION.md)**

---

## 🧩 Technische Spezifikationen

Alle technischen Spezifikationen — Metadatenmodell, JSON-LD-Namespaces, kontrollierte Vokabulare, der
vollständige DCAT-AP-/DCAT-AP.de-Feldkatalog mit Gap-Analyse, das Feld-Registry-Schema sowie die phasierte
Umsetzungsplanung samt Umsetzungsstand — sind in einem eigenen Dokument gepflegt:

**➡️ [TECHNICAL-SPEC.md](TECHNICAL-SPEC.md)**

Neue technische Festlegungen gehören in dieses Dokument (nicht in die anwenderorientierten Kapitel oben).

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

**In Planung (v2.2+):** — Details siehe [Technische Spezifikationen § 7](TECHNICAL-SPEC.md#7-umsetzungsplanung-phasiert)
- [x] Phase A: Konformitäts-Korrekturen, `@context`-Namespaces und Feld-Registry-Schema (v2.3.2 / 2.4.0 / 2.5.1)
- [x] Phase B: DCAT-AP.de-Felder (contributorID, originator, maintainer, availability) + generisches Vokabular-Autosuggest (v2.5)
- [x] Phase C: HVD-Unterstützung (`dcatap:hvdCategory` + `applicableLegislation`) (v2.4)
- [x] Phase D: Mehrsprachige Literale (`@language`/`@value`) — Titel, Beschreibung und Schlagworte je Sprache
- [x] Phase E: Multi-Distribution — wiederholbare Distributionen (opt-in)
- [x] Phase 3 UX: Tooltip-Popups (ⓘ) und Live-Wizard-Vorschau (Tab 5)
- [x] UX-Ausbau (Paket B, v2.29–2.32): Pflichtfeld-Sternchen + Publish-Validierung, Fehlermeldungen mit „Zum Feld springen", einheitliche Prozent-Qualitätsanzeige, entschlacktes Tab 1, konsistente Begriffe („Thema"/„Schlagworte"), Batch-Import im Admin-Design
- [x] Harvest-Endpoint für piveau/Civora: Voll-Katalog (`?full=1`) + **Turtle**-Serialisierung (v2.33.0)
- [ ] Content Negotiation vervollständigen: Turtle auch für `/datasets/<id>`, Auswertung des `Accept`-Headers, optional RDF/XML
- [ ] Gutenberg Block für die Download-Card
- [ ] Mehrsprachigkeit (WPML/Polylang)

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
