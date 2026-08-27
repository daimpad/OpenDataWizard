# Feld-Referenz — Open Data Wizard

> **Automatisch generiert** aus `config/field-catalog.php` durch
> `wp open-data-wizard docs` bzw. `php bin/generate-field-reference.php`.
> **Nicht von Hand bearbeiten** — Änderungen im Katalog vornehmen und neu generieren.

Diese Referenz dokumentiert jedes Formularfeld des Wizards in **vier Stufen**:

1. **DCAT-AP-Frage** — die Frage in der Terminologie des Standards.
2. **Verständliche Frage** — dieselbe Frage in Alltagssprache.
3. **DCAT-AP-Langbeschreibung** — die vollständige, normkonforme Definition.
4. **Verständliche Langbeschreibung** — ausführliche Erklärung ohne Fachjargon, mit Beispiel.

Legende der Stufen-Spalte: **Pflicht** (Veröffentlichung wird ohne dieses Feld blockiert) ·
**Empfohlen** · **Optional** · **Bedingt** (nur in bestimmten Fällen relevant).

## Inhaltsverzeichnis

- **1 — Grundlegende Informationen**
  - [Wer gibt diese Daten heraus?](#wer-gibt-diese-daten-heraus)
  - [Welchem Thema ist dieser Datensatz zugeordnet?](#welchem-thema-ist-dieser-datensatz-zugeordnet)
  - [Mit welchen Schlagworten finde ich diese Daten?](#mit-welchen-schlagworten-finde-ich-diese-daten)
  - [Welchem Themenfeld nach CESSDA ordnen Sie den Datensatz zu?](#welchem-themenfeld-nach-cessda-ordnen-sie-den-datensatz-zu)
  - [Welchem Engagementfeld ist dieser Datensatz zuzuordnen?](#welchem-engagementfeld-ist-dieser-datensatz-zuzuordnen)
  - [Worum geht es in diesem Datensatz?](#worum-geht-es-in-diesem-datensatz)
- **2 — Sprache & Übersetzungen**
  - [In welcher Sprache sind die Daten?](#in-welcher-sprache-sind-die-daten)
- **3 — Datenbereitstellung**
  - [Wo kann man die Datei herunterladen oder ansehen?](#wo-kann-man-die-datei-herunterladen-oder-ansehen)
  - [In welchem Format ist die Datei?](#in-welchem-format-ist-die-datei)
  - [Wie groß ist die Datei?](#wie-groß-ist-die-datei)
  - [Unter welcher Lizenz sind diese Daten verfügbar?](#unter-welcher-lizenz-sind-diese-daten-verfügbar)
  - [Welche eigene Lizenz-URI möchten Sie angeben?](#welche-eigene-lizenz-uri-möchten-sie-angeben)
  - [Welcher Namensnennungstext soll bei Weiternutzung angegeben werden?](#welcher-namensnennungstext-soll-bei-weiternutzung-angegeben-werden)
  - [Wie dauerhaft ist diese Datei verfügbar?](#wie-dauerhaft-ist-diese-datei-verfügbar)
- **4 — Erweiterte Angaben**
  - [Wo finde ich mehr Informationen zu diesem Projekt?](#wo-finde-ich-mehr-informationen-zu-diesem-projekt)
  - [Wann wurden diese Daten zum ersten Mal veröffentlicht?](#wann-wurden-diese-daten-zum-ersten-mal-veröffentlicht)
  - [Wann wurden diese Daten zuletzt aktualisiert?](#wann-wurden-diese-daten-zuletzt-aktualisiert)
  - [Wie oft werden diese Daten aktualisiert?](#wie-oft-werden-diese-daten-aktualisiert)
  - [Auf welcher Verwaltungsebene wurden diese Daten erhoben?](#auf-welcher-verwaltungsebene-wurden-diese-daten-erhoben)
  - [Auf welches amtliche Gebiet beziehen sich die Daten?](#auf-welches-amtliche-gebiet-beziehen-sich-die-daten)
  - [Wie lässt sich der räumliche Bezug in Worten beschreiben?](#wie-lässt-sich-der-räumliche-bezug-in-worten-beschreiben)
  - [Welche geografische Region betreffen diese Daten?](#welche-geografische-region-betreffen-diese-daten)
  - [Ab wann sind diese Daten gültig?](#ab-wann-sind-diese-daten-gültig)
  - [Bis wann sind diese Daten gültig?](#bis-wann-sind-diese-daten-gültig)
  - [Wer ist Ansprechperson für Fragen zu diesen Daten?](#wer-ist-ansprechperson-für-fragen-zu-diesen-daten)
  - [Unter welcher E-Mail-Adresse kann ich Fragen stellen?](#unter-welcher-e-mail-adresse-kann-ich-fragen-stellen)
  - [Auf welcher Website finde ich weitere Kontaktinformationen?](#auf-welcher-website-finde-ich-weitere-kontaktinformationen)
  - [Welche Stelle stellt diese Daten im GovData-Verbund bereit?](#welche-stelle-stellt-diese-daten-im-govdata-verbund-bereit)
  - [Wer hat diese Daten ursprünglich erstellt?](#wer-hat-diese-daten-ursprünglich-erstellt)
  - [Wie lautet die E-Mail-Adresse des Urhebers?](#wie-lautet-die-e-mail-adresse-des-urhebers)
  - [Wer pflegt diese Daten laufend?](#wer-pflegt-diese-daten-laufend)
  - [Wie lautet die E-Mail-Adresse der pflegenden Stelle?](#wie-lautet-die-e-mail-adresse-der-pflegenden-stelle)
  - [Auf welcher rechtlichen Grundlage werden die Daten bereitgestellt?](#auf-welcher-rechtlichen-grundlage-werden-die-daten-bereitgestellt)
  - [Wo ist das Qualitätssicherungs-Verfahren dokumentiert?](#wo-ist-das-qualitätssicherungs-verfahren-dokumentiert)
  - [Wer darf auf diese Daten zugreifen?](#wer-darf-auf-diese-daten-zugreifen)
  - [Welches weitere EU-Thema möchten Sie ergänzen?](#welches-weitere-eu-thema-möchten-sie-ergänzen)
  - [Ist dies ein hochwertiger Datensatz (HVD)?](#ist-dies-ein-hochwertiger-datensatz-hvd)
  - [Welcher HVD-Kategorie gehört dieser Datensatz an?](#welcher-hvd-kategorie-gehört-dieser-datensatz-an)
  - [Welche eindeutige Kennung hat dieser Datensatz?](#welche-eindeutige-kennung-hat-dieser-datensatz)
  - [Um welchen Typ von Datensatz handelt es sich?](#um-welchen-typ-von-datensatz-handelt-es-sich)
  - [Wer hat diese Daten erstellt?](#wer-hat-diese-daten-erstellt)
  - [Wie lautet die E-Mail-Adresse des Erstellers?](#wie-lautet-die-e-mail-adresse-des-erstellers)
  - [Welche Version hat dieser Datensatz?](#welche-version-hat-dieser-datensatz)
  - [Was hat sich in dieser Version geändert?](#was-hat-sich-in-dieser-version-geändert)
  - [Welche räumliche Auflösung haben die Daten (in Metern)?](#welche-räumliche-auflösung-haben-die-daten-in-metern)
  - [Welche zeitliche Auflösung haben die Daten?](#welche-zeitliche-auflösung-haben-die-daten)
  - [Welchem Standard oder Schema entsprechen die Daten?](#welchem-standard-oder-schema-entsprechen-die-daten)
  - [Woher stammen die Daten und wie sind sie entstanden?](#woher-stammen-die-daten-und-wie-sind-sie-entstanden)
  - [Wie heißt die bereitgestellte Datei/Distribution?](#wie-heißt-die-bereitgestellte-dateidistribution)
  - [Wie lässt sich die Distribution beschreiben?](#wie-lässt-sich-die-distribution-beschreiben)
  - [Wie lautet der direkte Download-Link zur Datei?](#wie-lautet-der-direkte-download-link-zur-datei)
  - [Welchen Medientyp (MIME) hat die Datei?](#welchen-medientyp-mime-hat-die-datei)
  - [Welche Nutzungsrechte gelten für die Datei?](#welche-nutzungsrechte-gelten-für-die-datei)

---

## 1 — Grundlegende Informationen

### Wer gibt diese Daten heraus?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:publisher` |
| Meta-Key | `_odw_publisher` |
| Stufe | Pflicht |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche Stelle ist `dct:publisher` (verantwortliche Herausgeberin) des Datensatzes?

**2 · Verständliche Frage:** Wer gibt diese Daten heraus?

**3 · DCAT-AP-Langbeschreibung:** `dct:publisher` benennt die für die Bereitstellung des Datensatzes verantwortliche Stelle (eine `foaf:Agent`-Entität). In DCAT-AP 3.0 ist die Angabe verpflichtend (Multiplizität 1..1). Idealerweise wird die Organisation über eine URI referenziert; als Minimalform genügt der Name (`foaf:name`).

**4 · Verständliche Langbeschreibung:** Tragen Sie hier die Organisation ein, die die Daten offiziell veröffentlicht — also wer dafür verantwortlich ist, dass die Daten bereitstehen. Das ist oft eine Behörde, ein Amt oder ein Verein. Beispiel: „Statistisches Landesamt Musterstadt" oder „Umweltbundesamt".

### Welchem Thema ist dieser Datensatz zugeordnet?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:theme` |
| Meta-Key | `_odw_theme` |
| Stufe | Empfohlen |
| Vokabular | `data-theme` |

**1 · DCAT-AP-Frage:** Welchem `dcat:theme` (Datenthema) ist der Datensatz zugeordnet?

**2 · Verständliche Frage:** Welchem Thema ist dieser Datensatz zugeordnet?

**3 · DCAT-AP-Langbeschreibung:** `dcat:theme` ordnet den Datensatz einer oder mehreren Kategorien zu. In DCAT-AP wird das kontrollierte EU-Vokabular „Data Theme" (`http://publications.europa.eu/resource/authority/data-theme/`) verwendet, z. B. `ENVI` (Umwelt) oder `EDUC` (Bildung). Multiplizität 0..n.

**4 · Verständliche Langbeschreibung:** Wählen Sie das Themengebiet, zu dem die Daten am besten passen — ähnlich einer Schublade, in die der Datensatz einsortiert wird. Das hilft anderen, Ihre Daten über Themenfilter zu finden. Beispiel: Umwelt, Bildung, Gesundheit, Wirtschaft, Kultur.

### Mit welchen Schlagworten finde ich diese Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:keyword` |
| Meta-Key | `_odw_keywords` |
| Stufe | Empfohlen |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dcat:keyword` (Schlagwörter) beschreiben den Datensatz?

**2 · Verständliche Frage:** Mit welchen Schlagworten finde ich diese Daten?

**3 · DCAT-AP-Langbeschreibung:** `dcat:keyword` sind freie, sprachlich getaggte Schlagwörter zur Verbesserung der Auffindbarkeit (`literal-lang`, Multiplizität 0..n). Anders als `dcat:theme` sind sie nicht an ein kontrolliertes Vokabular gebunden. Je Schlagwort ein Wert.

**4 · Verständliche Langbeschreibung:** Tragen Sie einzelne Schlagwörter ein, unter denen man Ihre Daten suchen würde — jedes Wort in eine eigene Zeile. Sie ergänzen das Thema und machen die Daten leichter auffindbar. Beispiel: Umwelt, Wasser, Luftverschmutzung.

### Welchem Themenfeld nach CESSDA ordnen Sie den Datensatz zu?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:subject` |
| Meta-Key | `_odw_cessda_topic` |
| Stufe | Optional |
| Vokabular | `cessda` |

**1 · DCAT-AP-Frage:** Welches `dct:subject` (Fachthema) nach CESSDA-Vokabular beschreibt den Datensatz?

**2 · Verständliche Frage:** Welchem Themenfeld nach CESSDA ordnen Sie den Datensatz zu?

**3 · DCAT-AP-Langbeschreibung:** `dct:subject` verweist auf ein fachliches Thema aus einem kontrollierten Vokabular — hier die CESSDA Topic Classification (Version 4.2.3). Der Wert ist die Konzept-URI aus dem CESSDA-Vokabular. Mehrfachangabe möglich (Multiplizität 0..n); erscheint zusammen mit anderen Themenzuordnungen als Liste. Hinweis: `dct:subject` gehört nicht zum DCAT-AP.de-Profil — die offiziellen SHACL-Shapes kennen die Eigenschaft für Datensätze nicht. Als Dublin-Core-Angabe ist sie zulässig (RDF erlaubt zusätzliche Aussagen) und wird hier bewusst ausgegeben; streng profilkonforme Portale können sie ignorieren.

**4 · Verständliche Langbeschreibung:** Optional für sozial- und wirtschaftswissenschaftliche Daten: Ordnen Sie den Datensatz einem Fachthema aus dem CESSDA-Katalog zu (ein europäischer Standard für Forschungsdaten). Tippen Sie das Thema ein und wählen Sie aus der Vorschlagsliste. Beispiel: Volkszählungen, Migration, Wirtschaftspolitik.

### Welchem Engagementfeld ist dieser Datensatz zuzuordnen?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:subject` |
| Meta-Key | `_odw_engagementfeld` |
| Stufe | Optional |
| Vokabular | `engagementfeld` |

**1 · DCAT-AP-Frage:** Welches `dct:subject` (Engagementfeld) nach ZiviZ-Vokabular beschreibt den Datensatz?

**2 · Verständliche Frage:** Welchem Engagementfeld ist dieser Datensatz zuzuordnen?

**3 · DCAT-AP-Langbeschreibung:** `dct:subject` mit einem Konzept aus dem ZiviZ-Vokabular „Engagementfeld" (`https://ziviz.de/def/engagementfeld/`), das zivilgesellschaftliche Tätigkeitsfelder klassifiziert. Der Wert ist die Konzept-URI; die Label→URI-Auflösung erfolgt beim Speichern. Mehrfachangabe möglich (Multiplizität 0..n). Hinweis: `dct:subject` gehört nicht zum DCAT-AP.de-Profil — die offiziellen SHACL-Shapes kennen die Eigenschaft für Datensätze nicht. Als Dublin-Core-Angabe ist sie zulässig (RDF erlaubt zusätzliche Aussagen) und wird hier bewusst ausgegeben; streng profilkonforme Portale können sie ignorieren.

**4 · Verständliche Langbeschreibung:** Optional für Organisationen der Zivilgesellschaft: Ordnen Sie den Datensatz einem Engagementfeld zu — also dem gesellschaftlichen Bereich, in dem Sie aktiv sind. Wählen Sie einen Eintrag aus der Liste; die passende Kennung wird automatisch gesetzt. Beispiel: Kultur, Sport, Umwelt- und Naturschutz.

### Worum geht es in diesem Datensatz?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:description` |
| Meta-Key | `_odw_description` |
| Stufe | Pflicht |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wie lautet die `dct:description` (Freitextbeschreibung) des Datensatzes?

**2 · Verständliche Frage:** Worum geht es in diesem Datensatz?

**3 · DCAT-AP-Langbeschreibung:** `dct:description` ist eine frei formulierte, sprachlich getaggte Beschreibung des Datensatzes (`literal-lang`). In DCAT-AP 3.0 verpflichtend (Multiplizität 1..n je Sprache). Sie ergänzt den Titel um Inhalt, Kontext, Erhebungsmethode und Abgrenzung des Datensatzes.

**4 · Verständliche Langbeschreibung:** Beschreiben Sie in eigenen Worten, was in den Daten steckt: Worum geht es, was kann man damit machen, was ist enthalten? Ein bis zwei Absätze reichen. Beispiel: „Überblick über die bevölkerungsreichsten Städte Deutschlands mit Einwohnerzahlen und Entwicklung seit 2010."

---

## 2 — Sprache & Übersetzungen

### In welcher Sprache sind die Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:language` |
| Meta-Key | `_odw_language` |
| Stufe | Empfohlen |
| Vokabular | `language` |

**1 · DCAT-AP-Frage:** In welcher `dct:language` (Sprache) liegen die Daten vor?

**2 · Verständliche Frage:** In welcher Sprache sind die Daten?

**3 · DCAT-AP-Langbeschreibung:** `dct:language` gibt die Sprache des Datensatzes an, referenziert über das EU-Authority-Vokabular „Language" (`http://publications.europa.eu/resource/authority/language/`, z. B. `DEU`, `ENG`). Multiplizität 0..n — mehrsprachige Datensätze können mehrere Sprachen angeben.

**4 · Verständliche Langbeschreibung:** Geben Sie an, in welcher Sprache die Inhalte der Daten verfasst sind (z. B. die Spaltenüberschriften und Texte). Das hilft Nutzenden einzuschätzen, ob sie die Daten verstehen. Beispiel: Deutsch, Englisch.

---

## 3 — Datenbereitstellung

### Wo kann man die Datei herunterladen oder ansehen?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:accessURL` |
| Meta-Key | `_odw_access_url` |
| Stufe | Pflicht |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Unter welcher `dcat:accessURL` ist die Distribution erreichbar?

**2 · Verständliche Frage:** Wo kann man die Datei herunterladen oder ansehen?

**3 · DCAT-AP-Langbeschreibung:** `dcat:accessURL` verweist auf eine Ressource, über die auf die Distribution zugegriffen werden kann (Range `rdfs:Resource`, Multiplizität 1..n je Distribution). Sie kann auf eine Landing-Page, einen Feed, einen Endpunkt oder — bei direktem Download — dieselbe Ressource wie `dcat:downloadURL` zeigen. Pflichtangabe je Distribution.

**4 · Verständliche Langbeschreibung:** Tragen Sie den Link ein, unter dem Ihre Daten zu finden sind — die Adresse einer Datei zum Herunterladen oder einer Seite, auf der die Daten liegen. Alternativ laden Sie die Datei über die Mediathek-Box hoch; dann wird dieser Link automatisch gesetzt und Sie müssen ihn nicht selbst eintippen. Beispiel: https://beispiel.de/daten/statistik.csv.

### In welchem Format ist die Datei?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:format` |
| Meta-Key | `_odw_format` |
| Stufe | Empfohlen |
| Vokabular | `file-type` |

**1 · DCAT-AP-Frage:** Welches `dct:format` (Dateiformat) hat die Distribution?

**2 · Verständliche Frage:** In welchem Format ist die Datei?

**3 · DCAT-AP-Langbeschreibung:** `dct:format` gibt das Format der Distribution an, referenziert über das EU-Authority-Vokabular „File Type" (`http://publications.europa.eu/resource/authority/file-type/`, z. B. `CSV`, `JSON`, `PDF`). Multiplizität 0..1. Ergänzt `dcat:mediaType` (MIME-Typ) um das anwendungsnahe Format.

**4 · Verständliche Langbeschreibung:** Wählen Sie das Format der bereitgestellten Datei. Daran erkennen Nutzende, mit welchem Programm sie die Daten öffnen können. Maschinenlesbare, offene Formate wie CSV oder JSON sind besonders empfehlenswert. Beispiel: CSV, JSON, PDF.

### Wie groß ist die Datei?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:byteSize` |
| Meta-Key | `_odw_byte_size` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wie groß ist die Distribution laut `dcat:byteSize` (in Bytes)?

**2 · Verständliche Frage:** Wie groß ist die Datei?

**3 · DCAT-AP-Langbeschreibung:** `dcat:byteSize` gibt die Größe der Distribution in Bytes als nicht-negative Ganzzahl an (`xsd:nonNegativeInteger`, Multiplizität 0..1). Bei einem Mediathek-Upload wird der Wert automatisch aus der Datei ermittelt.

**4 · Verständliche Langbeschreibung:** Die Dateigröße in Bytes — nur die reine Zahl. Nutzende sehen so vorab, wie viel sie herunterladen. Bei hochgeladenen Dateien wird die Größe automatisch berechnet. Beispiel: 2048576 (das sind rund 2 MB).

### Unter welcher Lizenz sind diese Daten verfügbar?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:license` |
| Meta-Key | `_odw_license` |
| Stufe | Pflicht |
| Vokabular | `licenses` |

**1 · DCAT-AP-Frage:** Unter welcher `dct:license` (Lizenz) steht die Distribution?

**2 · Verständliche Frage:** Unter welcher Lizenz sind diese Daten verfügbar?

**3 · DCAT-AP-Langbeschreibung:** `dct:license` benennt das rechtliche Dokument, unter dem die Distribution bereitgestellt wird (Range `dct:LicenseDocument`, referenziert per URI). Verpflichtend für die Wiederverwendbarkeit. Empfohlen werden URIs offener Lizenzen (Creative Commons, Datenlizenz Deutschland, DCAT-AP.de-Lizenzregister).

**4 · Verständliche Langbeschreibung:** Legen Sie fest, was andere mit Ihren Daten tun dürfen — das regelt die Lizenz. Wählen Sie eine der vorgeschlagenen Standardlizenzen; „offene" Lizenzen erlauben die freie Weiternutzung. Ohne Lizenz sind Daten rechtlich unklar und kaum nachnutzbar. Beispiel: CC-BY 4.0 (Namensnennung).

### Welche eigene Lizenz-URI möchten Sie angeben?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:license` |
| Meta-Key | `_odw_license_custom` |
| Stufe | Bedingt |
| Vokabular | `licenses` |

**1 · DCAT-AP-Frage:** Welche `dct:license`-URI gilt, wenn keine Standardlizenz zutrifft?

**2 · Verständliche Frage:** Welche eigene Lizenz-URI möchten Sie angeben?

**3 · DCAT-AP-Langbeschreibung:** Nur relevant, wenn im Lizenzfeld „Sonstige" gewählt wurde: Hier wird die `dct:license`-URI einer nicht in der Standardliste enthaltenen Lizenz eingetragen. Das Auto-Suggest bietet zusätzlich die URIs des DCAT-AP.de-Lizenzregisters (`http://dcat-ap.de/def/licenses/…`) an.

**4 · Verständliche Langbeschreibung:** Dieses Feld erscheint nur, wenn Sie oben „Sonstige" Lizenz gewählt haben. Tragen Sie dann die Web-Adresse (URI) Ihrer Lizenz ein oder wählen Sie eine aus der Vorschlagsliste. Beispiel: http://dcat-ap.de/def/licenses/dl-by-de/2.0.

### Welcher Namensnennungstext soll bei Weiternutzung angegeben werden?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:licenseAttributionByText` |
| Meta-Key | `_odw_attribution_text` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welcher `dcatde:licenseAttributionByText` (Namensnennungstext) ist bei Nachnutzung anzugeben?

**2 · Verständliche Frage:** Welcher Namensnennungstext soll bei Weiternutzung angegeben werden?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:licenseAttributionByText` (DCAT-AP.de) enthält den exakten Namensnennungstext, den Nachnutzende bei Lizenzen mit Namensnennungspflicht (z. B. CC-BY, DL-DE-BY) zitieren müssen. Freitext (`literal`), Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Bei Lizenzen mit Namensnennung („BY") geben Sie hier vor, wie andere Sie nennen sollen, wenn sie Ihre Daten verwenden. So bekommen Sie korrekt Credit. Beispiel: „Datenquelle: Stadt Musterstadt, 2026".

### Wie dauerhaft ist diese Datei verfügbar?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatap:availability` |
| Meta-Key | `_odw_availability` |
| Stufe | Optional |
| Vokabular | `planned-availability` |

**1 · DCAT-AP-Frage:** Welche `dcatap:availability` (Verfügbarkeitsgarantie) hat die Distribution?

**2 · Verständliche Frage:** Wie dauerhaft ist diese Datei verfügbar?

**3 · DCAT-AP-Langbeschreibung:** `dcatap:availability` beschreibt die geplante Verfügbarkeit einer Distribution über das EU-Vokabular „Planned Availability" (z. B. `AVAILABLE`, `TEMPORARY`, `EXPERIMENTAL`, `STABLE`). Multiplizität 0..1. Gibt Nachnutzenden Planungssicherheit.

**4 · Verständliche Langbeschreibung:** Geben Sie an, wie verlässlich die Datei langfristig erreichbar bleibt — dauerhaft, nur vorübergehend oder experimentell. Das hilft anderen einzuschätzen, ob sie sich auf den Link verlassen können. Beispiel: Dauerhaft verfügbar.

---

## 4 — Erweiterte Angaben

### Wo finde ich mehr Informationen zu diesem Projekt?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:landingPage` |
| Meta-Key | `_odw_landing_page` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dcat:landingPage` (Projekt-/Infoseite) gehört zum Datensatz?

**2 · Verständliche Frage:** Wo finde ich mehr Informationen zu diesem Projekt?

**3 · DCAT-AP-Langbeschreibung:** `dcat:landingPage` verweist auf eine menschenlesbare Webseite mit weiteren Informationen zum Datensatz (Range `foaf:Document`, Multiplizität 0..n). Anders als `dcat:accessURL` führt sie nicht direkt zu den Daten, sondern zu Kontext, Dokumentation oder Projektbeschreibung.

**4 · Verständliche Langbeschreibung:** Verlinken Sie eine Webseite, auf der man mehr über die Daten oder das Projekt erfährt — etwa eine Projekt- oder Dokumentationsseite. Das ist nicht der direkte Download, sondern die „Über uns"-Seite zu den Daten. Beispiel: https://beispiel.de/projekt.

### Wann wurden diese Daten zum ersten Mal veröffentlicht?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:issued` |
| Meta-Key | `_odw_issued` |
| Stufe | Empfohlen |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wann wurde der Datensatz `dct:issued` (erstveröffentlicht)?

**2 · Verständliche Frage:** Wann wurden diese Daten zum ersten Mal veröffentlicht?

**3 · DCAT-AP-Langbeschreibung:** `dct:issued` ist das Datum der formalen Erstveröffentlichung des Datensatzes, typisiert als `xsd:date` bzw. `xsd:dateTime`. Multiplizität 0..1. Nicht zu verwechseln mit `dct:modified` (letzte Änderung) oder dem Erhebungszeitraum (`dct:temporal`).

**4 · Verständliche Langbeschreibung:** Geben Sie das Datum an, an dem die Daten erstmals veröffentlicht wurden. Das ist der „Geburtstag" des Datensatzes, nicht der Zeitraum, den die Daten abdecken. Beispiel: 2024-01-15.

### Wann wurden diese Daten zuletzt aktualisiert?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:modified` |
| Meta-Key | `_odw_modified` |
| Stufe | Empfohlen |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wann wurde der Datensatz zuletzt `dct:modified` (geändert)?

**2 · Verständliche Frage:** Wann wurden diese Daten zuletzt aktualisiert?

**3 · DCAT-AP-Langbeschreibung:** `dct:modified` gibt das Datum der letzten inhaltlichen Änderung des Datensatzes an (`xsd:date`/`xsd:dateTime`, Multiplizität 0..1). Das Plugin setzt diesen Wert bei jeder Speicherung automatisch; ein Eingabefeld gibt es bewusst nicht, da eine manuelle Angabe beim nächsten Speichern überschrieben würde.

**4 · Verständliche Langbeschreibung:** Das Datum der letzten Aktualisierung. Es wird beim Speichern automatisch gesetzt, sodass Nutzende immer sehen, wie aktuell die Daten sind — Sie müssen dafür nichts eintragen. Beispiel: 2026-04-22.

### Wie oft werden diese Daten aktualisiert?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:accrualPeriodicity` |
| Meta-Key | `_odw_accrual_periodicity` |
| Stufe | Optional |
| Vokabular | `frequency` |

**1 · DCAT-AP-Frage:** Welche `dct:accrualPeriodicity` (Aktualisierungsfrequenz) hat der Datensatz?

**2 · Verständliche Frage:** Wie oft werden diese Daten aktualisiert?

**3 · DCAT-AP-Langbeschreibung:** `dct:accrualPeriodicity` gibt an, in welchem Rhythmus der Datensatz aktualisiert wird, referenziert über das EU-Vokabular „Frequency" (`http://publications.europa.eu/resource/authority/frequency/`, z. B. `DAILY`, `MONTHLY`, `ANNUAL`). Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Geben Sie an, wie regelmäßig neue Daten hinzukommen — täglich, monatlich, jährlich oder unregelmäßig. Nutzende wissen dann, wie oft sich ein erneuter Blick lohnt. Beispiel: Jährlich.

### Auf welcher Verwaltungsebene wurden diese Daten erhoben?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:politicalGeocodingLevelURI` |
| Meta-Key | `_odw_political_geocoding_level` |
| Stufe | Optional |
| Vokabular | `politicalGeocodingLevel` |

**1 · DCAT-AP-Frage:** Welche `dcatde:politicalGeocodingLevelURI` (Verwaltungsebene) betrifft der Datensatz?

**2 · Verständliche Frage:** Auf welcher Verwaltungsebene wurden diese Daten erhoben?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:politicalGeocodingLevelURI` (DCAT-AP.de) klassifiziert die administrative Ebene des räumlichen Bezugs über das GovData-Vokabular (Bund, Land, Kreis, Kommune). Multiplizität 0..1. Ergänzt die konkrete Gebietsangabe.

**4 · Verständliche Langbeschreibung:** Wählen Sie, auf welcher staatlichen Ebene die Daten angesiedelt sind — Bund, Land, Landkreis oder Gemeinde. Das ordnet die Daten geografisch-administrativ ein. Beispiel: Gemeinde.

### Auf welches amtliche Gebiet beziehen sich die Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:politicalGeocodingURI` |
| Meta-Key | `_odw_political_geocoding_uri` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welches `dcatde:politicalGeocodingURI` (amtliches Gebiet) beschreibt den räumlichen Bezug?

**2 · Verständliche Frage:** Auf welches amtliche Gebiet beziehen sich die Daten?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:politicalGeocodingURI` (DCAT-AP.de) referenziert ein konkretes amtliches Gebiet über eine URI (z. B. amtlicher Regionalschlüssel/AGS als URI). Multiplizität 0..n. Ermöglicht die eindeutige, maschinenlesbare Verortung.

**4 · Verständliche Langbeschreibung:** Verlinken Sie das genaue amtliche Gebiet, auf das sich die Daten beziehen — als offizielle Kennung (z. B. Gemeindeschlüssel). Damit ist eindeutig, welche Region gemeint ist. Beispiel: die URI zum amtlichen Gemeindeschlüssel Ihrer Stadt.

### Wie lässt sich der räumliche Bezug in Worten beschreiben?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:geocodingDescription` |
| Meta-Key | `_odw_geocoding_description` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wie lautet die `dcatde:geocodingDescription` (textuelle Beschreibung des räumlichen Bezugs)?

**2 · Verständliche Frage:** Wie lässt sich der räumliche Bezug in Worten beschreiben?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:geocodingDescription` (DCAT-AP.de) ist eine frei formulierte textuelle Beschreibung der räumlichen Abdeckung (`literal`/`literal-lang`). Multiplizität 0..n. Ergänzt die maschinenlesbaren Angaben `dct:spatial` und `dcatde:politicalGeocodingURI` um eine menschenlesbare Erläuterung.

**4 · Verständliche Langbeschreibung:** Beschreiben Sie den räumlichen Bezug zusätzlich in Worten — etwa wenn ein Gebietsschlüssel allein nicht genau genug ist. Das hilft Nutzenden, die Abdeckung einzuordnen. Beispiel: „Stadtgebiet Musterstadt ohne den Ortsteil X".

### Welche geografische Region betreffen diese Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:spatial` |
| Meta-Key | `_odw_spatial` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dct:spatial` (geografische Abdeckung) hat der Datensatz?

**2 · Verständliche Frage:** Welche geografische Region betreffen diese Daten?

**3 · DCAT-AP-Langbeschreibung:** `dct:spatial` beschreibt die räumliche Abdeckung des Datensatzes (Range `dct:Location`). Als Wert dient eine Gebiets-URI (z. B. GeoNames, EU-Continents) oder eine Freitext-Ortsangabe. Multiplizität 0..n.

**4 · Verständliche Langbeschreibung:** Geben Sie an, welches geografische Gebiet die Daten abdecken — eine Stadt, ein Bundesland, ein Land oder eine Region. So finden Nutzende Daten zu ihrer Gegend. Beispiel: Musterstadt oder Bayern.

### Ab wann sind diese Daten gültig?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:temporal` |
| Meta-Key | `_odw_temporal_start` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Ab welchem `dct:temporal`-Startdatum gilt der Abdeckungszeitraum?

**2 · Verständliche Frage:** Ab wann sind diese Daten gültig?

**3 · DCAT-AP-Langbeschreibung:** `dct:temporal` beschreibt den Zeitraum, den die Daten inhaltlich abdecken (Range `dct:PeriodOfTime`). Dieses Feld liefert die Startangabe (`dcat:startDate`) des Intervalls. Multiplizität 0..1. Nicht zu verwechseln mit `dct:issued` (Veröffentlichung).

**4 · Verständliche Langbeschreibung:** Der Beginn des Zeitraums, den die Daten inhaltlich beschreiben — also ab wann die erfassten Werte gelten. Das ist der abgedeckte Zeitraum, nicht das Veröffentlichungsdatum. Beispiel: 2020-01-01.

### Bis wann sind diese Daten gültig?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:temporal` |
| Meta-Key | `_odw_temporal_end` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Bis zu welchem `dct:temporal`-Enddatum gilt der Abdeckungszeitraum?

**2 · Verständliche Frage:** Bis wann sind diese Daten gültig?

**3 · DCAT-AP-Langbeschreibung:** `dct:temporal` liefert hier die Endangabe (`dcat:endDate`) des abgedeckten Zeitintervalls (Range `dct:PeriodOfTime`). Multiplizität 0..1. Zusammen mit dem Startdatum ergibt sich der vollständige Abdeckungszeitraum.

**4 · Verständliche Langbeschreibung:** Das Ende des Zeitraums, den die Daten abdecken — bis wann die erfassten Werte reichen. Bleibt das Feld leer, gilt der Zeitraum als offen (bis heute fortlaufend). Beispiel: 2025-12-31.

### Wer ist Ansprechperson für Fragen zu diesen Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:contactPoint` |
| Meta-Key | `_odw_contact_name` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wie lautet der `vcard:fn` (Name) des `dcat:contactPoint`?

**2 · Verständliche Frage:** Wer ist Ansprechperson für Fragen zu diesen Daten?

**3 · DCAT-AP-Langbeschreibung:** Teil des `dcat:contactPoint` (Range `vcard:Kind`): `vcard:fn` benennt die Kontaktstelle oder -person für Rückfragen zum Datensatz. Multiplizität des Kontaktpunkts 0..n. Name, E-Mail und URL bilden zusammen einen Kontaktpunkt.

**4 · Verständliche Langbeschreibung:** Nennen Sie, an wen sich Nutzende bei Fragen wenden können — eine Person oder eine Abteilung. So ist klar, wer für die Daten zuständig ist. Beispiel: Open-Data-Team der Stadt Musterstadt.

### Unter welcher E-Mail-Adresse kann ich Fragen stellen?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:contactPoint` |
| Meta-Key | `_odw_contact_email` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `vcard:hasEmail` gehört zum `dcat:contactPoint`?

**2 · Verständliche Frage:** Unter welcher E-Mail-Adresse kann ich Fragen stellen?

**3 · DCAT-AP-Langbeschreibung:** Teil des `dcat:contactPoint`: `vcard:hasEmail` liefert die E-Mail-Adresse der Kontaktstelle (als `mailto:`-URI serialisiert). Multiplizität 0..1 je Kontaktpunkt.

**4 · Verständliche Langbeschreibung:** Die E-Mail-Adresse, unter der man Fragen zu den Daten stellen kann. Das ist der einfachste Weg für Nachnutzende, Sie zu erreichen. Beispiel: opendata@musterstadt.de.

### Auf welcher Website finde ich weitere Kontaktinformationen?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:contactPoint` |
| Meta-Key | `_odw_contact_url` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `vcard:hasURL` gehört zum `dcat:contactPoint`?

**2 · Verständliche Frage:** Auf welcher Website finde ich weitere Kontaktinformationen?

**3 · DCAT-AP-Langbeschreibung:** Teil des `dcat:contactPoint`: `vcard:hasURL` verweist auf eine Webseite mit weiteren Kontaktinformationen der zuständigen Stelle. Multiplizität 0..1 je Kontaktpunkt.

**4 · Verständliche Langbeschreibung:** Verlinken Sie optional eine Kontakt- oder Impressumsseite mit weiteren Wegen, Sie zu erreichen. Ergänzt E-Mail und Name um eine Anlaufstelle im Web. Beispiel: https://musterstadt.de/kontakt.

### Welche Stelle stellt diese Daten im GovData-Verbund bereit?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:contributorID` |
| Meta-Key | `_odw_contributor_id` |
| Stufe | Optional |
| Vokabular | `contributors` |

**1 · DCAT-AP-Frage:** Welche `dcatde:contributorID` (bereitstellende Stelle) gilt im GovData-Verbund?

**2 · Verständliche Frage:** Welche Stelle stellt diese Daten im GovData-Verbund bereit?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:contributorID` (DCAT-AP.de) ist die offizielle Kennung der im GovData-Verbund bereitstellenden Stelle, referenziert über das gebündelte Contributors-Vokabular. Multiplizität 0..n. Dient der Zuordnung im nationalen Metadatenverbund.

**4 · Verständliche Langbeschreibung:** Nur für Stellen, die im deutschen GovData-Verbund veröffentlichen: Wählen Sie Ihre Stelle aus der Liste; die zugehörige offizielle Kennung wird automatisch verwendet. Für die meisten Nutzenden ist dieses Feld nicht relevant. Beispiel: Bundesministerium des Innern.

### Wer hat diese Daten ursprünglich erstellt?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:originator` |
| Meta-Key | `_odw_originator_name` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wer ist `dcatde:originator` (ursprünglicher Urheber) der Daten?

**2 · Verständliche Frage:** Wer hat diese Daten ursprünglich erstellt?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:originator` (DCAT-AP.de) benennt die Stelle, von der die Daten ursprünglich stammen (Range `foaf:Agent`). Kann von Herausgeber (`dct:publisher`) und Ersteller (`dct:creator`) abweichen. Multiplizität 0..n.

**4 · Verständliche Langbeschreibung:** Nennen Sie die Stelle, von der die Daten ursprünglich stammen — das kann eine andere sein als die, die sie jetzt veröffentlicht. So bleibt die Herkunft nachvollziehbar. Beispiel: Statistisches Landesamt.

### Wie lautet die E-Mail-Adresse des Urhebers?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:originator` |
| Meta-Key | `_odw_originator_email` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche E-Mail-Adresse gehört zum `dcatde:originator`?

**2 · Verständliche Frage:** Wie lautet die E-Mail-Adresse des Urhebers?

**3 · DCAT-AP-Langbeschreibung:** Ergänzt `dcatde:originator` um eine E-Mail-Adresse des ursprünglichen Urhebers (als `mailto:`-URI). Optional, Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Optional die E-Mail-Adresse der ursprünglichen Urheber-Stelle, falls abweichend erreichbar. Beispiel: statistik@landesamt.de.

### Wer pflegt diese Daten laufend?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:maintainer` |
| Meta-Key | `_odw_maintainer_name` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wer ist `dcatde:maintainer` (pflegende Stelle) der Daten?

**2 · Verständliche Frage:** Wer pflegt diese Daten laufend?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:maintainer` (DCAT-AP.de) benennt die für die laufende Pflege und Aktualisierung zuständige Stelle (Range `foaf:Agent`). Multiplizität 0..n. Kann von Herausgeber und Urheber abweichen.

**4 · Verständliche Langbeschreibung:** Nennen Sie, wer die Daten laufend betreut und aktuell hält. Das ist die Stelle, die sich um Updates kümmert — nicht unbedingt die, die sie erstellt hat. Beispiel: Open-Data-Team.

### Wie lautet die E-Mail-Adresse der pflegenden Stelle?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:maintainer` |
| Meta-Key | `_odw_maintainer_email` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche E-Mail-Adresse gehört zum `dcatde:maintainer`?

**2 · Verständliche Frage:** Wie lautet die E-Mail-Adresse der pflegenden Stelle?

**3 · DCAT-AP-Langbeschreibung:** Ergänzt `dcatde:maintainer` um eine E-Mail-Adresse der pflegenden Stelle (als `mailto:`-URI). Optional, Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Optional die E-Mail-Adresse der Stelle, die die Daten pflegt. Beispiel: opendata@beispiel.de.

### Auf welcher rechtlichen Grundlage werden die Daten bereitgestellt?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:legalBasis` |
| Meta-Key | `_odw_legal_basis` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Auf welcher `dcatde:legalBasis` (Rechtsgrundlage) beruht die Bereitstellung?

**2 · Verständliche Frage:** Auf welcher rechtlichen Grundlage werden die Daten bereitgestellt?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:legalBasis` (DCAT-AP.de) nennt das Gesetz oder die Verordnung, die die Bereitstellung der Daten regelt (Freitext, `literal`). Multiplizität 0..n.

**4 · Verständliche Langbeschreibung:** Falls es eine gesetzliche Grundlage für die Veröffentlichung gibt, nennen Sie sie hier. Das schafft Transparenz über die rechtliche Verpflichtung oder Erlaubnis. Beispiel: § 12a EGovG (E-Government-Gesetz).

### Wo ist das Qualitätssicherungs-Verfahren dokumentiert?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatde:qualityProcessURI` |
| Meta-Key | `_odw_quality_process_uri` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dcatde:qualityProcessURI` dokumentiert das Qualitätssicherungs-Verfahren?

**2 · Verständliche Frage:** Wo ist das Qualitätssicherungs-Verfahren dokumentiert?

**3 · DCAT-AP-Langbeschreibung:** `dcatde:qualityProcessURI` (DCAT-AP.de) verweist per URI auf eine Beschreibung des Qualitätssicherungs-Prozesses des Datensatzes. Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Verlinken Sie optional eine Seite, die beschreibt, wie Sie die Qualität der Daten sichern (z. B. Prüfschritte). Das stärkt das Vertrauen in Ihre Daten. Beispiel: https://beispiel.de/qualitaetssicherung.

### Wer darf auf diese Daten zugreifen?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:accessRights` |
| Meta-Key | `_odw_access_rights` |
| Stufe | Optional |
| Vokabular | `access-right` |

**1 · DCAT-AP-Frage:** Welche `dct:accessRights` (Zugriffsklassifikation) gelten für den Datensatz?

**2 · Verständliche Frage:** Wer darf auf diese Daten zugreifen?

**3 · DCAT-AP-Langbeschreibung:** `dct:accessRights` klassifiziert den Zugriffsstatus über das EU-Vokabular „Access Right" (`PUBLIC`, `RESTRICTED`, `NON_PUBLIC`). Multiplizität 0..1. Relevant für die MQA-Dimension Wiederverwendbarkeit.

**4 · Verständliche Langbeschreibung:** Geben Sie an, ob die Daten für alle offen (öffentlich), nur eingeschränkt oder gar nicht öffentlich zugänglich sind. Für offene Daten ist das in der Regel „öffentlich". Beispiel: Öffentlich.

### Welches weitere EU-Thema möchten Sie ergänzen?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:theme` |
| Meta-Key | `_odw_theme_uri` |
| Stufe | Optional |
| Vokabular | `data-theme` |

**1 · DCAT-AP-Frage:** Welches zusätzliche `dcat:theme` (EU-Themen-URI) gilt für den Datensatz?

**2 · Verständliche Frage:** Welches weitere EU-Thema möchten Sie ergänzen?

**3 · DCAT-AP-Langbeschreibung:** Zusätzliches `dcat:theme` als direkte EU-Themen-URI (`http://publications.europa.eu/resource/authority/data-theme/…`). Ergänzt die Themen-Auswahl aus Tab 1 um weitere Themen. Multiplizität 0..n.

**4 · Verständliche Langbeschreibung:** Falls Ihr Datensatz zu mehr als einem Thema passt, ergänzen Sie hier ein weiteres EU-Thema aus der Vorschlagsliste. Das erhöht die Auffindbarkeit über mehrere Themenfilter. Beispiel: Umwelt zusätzlich zu Gesundheit.

### Ist dies ein hochwertiger Datensatz (HVD)?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatap:applicableLegislation` |
| Meta-Key | `_odw_is_hvd` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Ist der Datensatz ein High-Value-Dataset nach `dcatap:applicableLegislation` (EU-VO 2023/138)?

**2 · Verständliche Frage:** Ist dies ein hochwertiger Datensatz (HVD)?

**3 · DCAT-AP-Langbeschreibung:** Kennzeichen, ob der Datensatz ein High-Value-Dataset im Sinne der EU-Durchführungsverordnung 2023/138 ist. Ist es gesetzt, wird `dcatap:applicableLegislation` (Verweis auf die Verordnung) ausgegeben und das Feld HVD-Kategorie relevant.

**4 · Verständliche Langbeschreibung:** „High-Value-Datasets" sind von der EU als besonders wertvoll eingestufte Datensätze (z. B. Geo-, Umwelt- oder Mobilitätsdaten). Setzen Sie dies nur, wenn Ihr Datensatz in eine der offiziellen HVD-Kategorien fällt — im Zweifel „Nein". Beispiel: Ja, bei amtlichen Geodaten.

### Welcher HVD-Kategorie gehört dieser Datensatz an?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcatap:hvdCategory` |
| Meta-Key | `_odw_hvd_category` |
| Stufe | Optional |
| Vokabular | `hvd-category` |

**1 · DCAT-AP-Frage:** Welcher `dcatap:hvdCategory` (HVD-Kategorie) gehört der Datensatz an?

**2 · Verständliche Frage:** Welcher HVD-Kategorie gehört dieser Datensatz an?

**3 · DCAT-AP-Langbeschreibung:** `dcatap:hvdCategory` ordnet ein High-Value-Dataset einer der sechs HVD-Kategorien der EU zu (Geospatial, Erdbeobachtung/Umwelt, Meteorologie, Statistik, Unternehmen, Mobilität), referenziert per EU-URI. Nur relevant, wenn HVD gesetzt ist. Multiplizität 0..n. Hinweis: Die Eigenschaft stammt aus der HVD-Erweiterung zu DCAT-AP (EU-Durchführungsverordnung 2023/138), nicht aus dem Kernprofil DCAT-AP 3.0; die mitgelieferten Kern-Shapes prüfen sie deshalb nicht.

**4 · Verständliche Langbeschreibung:** Wenn es ein hochwertiger Datensatz ist, wählen Sie hier die passende der sechs EU-HVD-Kategorien. Damit ordnen Sie den Datensatz korrekt in das EU-Schema ein. Beispiel: Georaum, Mobilität, Statistik.

### Welche eindeutige Kennung hat dieser Datensatz?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:identifier` |
| Meta-Key | `_odw_identifier` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welcher `dct:identifier` (eindeutige Kennung) identifiziert den Datensatz?

**2 · Verständliche Frage:** Welche eindeutige Kennung hat dieser Datensatz?

**3 · DCAT-AP-Langbeschreibung:** `dct:identifier` ist eine eindeutige, im System der Herausgeberin vergebene Kennung des Datensatzes (Freitext/`literal`). Multiplizität 0..n. Ermöglicht die stabile Referenzierung über Systemgrenzen hinweg.

**4 · Verständliche Langbeschreibung:** Falls Ihr Datensatz eine feste Kennnummer aus Ihrem eigenen System hat, tragen Sie sie hier ein. So bleibt der Datensatz eindeutig identifizierbar. Beispiel: DS-2026-00042.

### Um welchen Typ von Datensatz handelt es sich?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:type` |
| Meta-Key | `_odw_type` |
| Stufe | Optional |
| Vokabular | `dataset-type` |

**1 · DCAT-AP-Frage:** Welchen `dct:type` (Datensatz-Typ) hat der Datensatz?

**2 · Verständliche Frage:** Um welchen Typ von Datensatz handelt es sich?

**3 · DCAT-AP-Langbeschreibung:** `dct:type` klassifiziert die Art des Datensatzes, idealerweise über das EU-Vokabular „Dataset Type". Multiplizität 0..1. Unterscheidet z. B. Erhebungsdaten, Registerdaten oder Geodaten.

**4 · Verständliche Langbeschreibung:** Geben Sie an, um welche Art von Datensatz es sich grundsätzlich handelt. Das hilft bei der groben Einordnung. Beispiel: Statistische Erhebung, Geodaten.

### Wer hat diese Daten erstellt?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:creator` |
| Meta-Key | `_odw_creator_name` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wer ist `dct:creator` (Ersteller) des Datensatzes?

**2 · Verständliche Frage:** Wer hat diese Daten erstellt?

**3 · DCAT-AP-Langbeschreibung:** `dct:creator` benennt die primär für die Erstellung des Datensatzes verantwortliche Stelle oder Person (Range `foaf:Agent`). Multiplizität 0..n. Unterscheidet sich vom Herausgeber (`dct:publisher`), der für die Bereitstellung verantwortlich ist.

**4 · Verständliche Langbeschreibung:** Nennen Sie, wer die Daten inhaltlich erstellt hat — die Person oder Stelle, die die eigentliche Arbeit gemacht hat. Das kann von der herausgebenden Organisation abweichen. Beispiel: Forschungsgruppe Stadtklima.

### Wie lautet die E-Mail-Adresse des Erstellers?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:creator` |
| Meta-Key | `_odw_creator_email` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche E-Mail-Adresse gehört zum `dct:creator`?

**2 · Verständliche Frage:** Wie lautet die E-Mail-Adresse des Erstellers?

**3 · DCAT-AP-Langbeschreibung:** Ergänzt `dct:creator` um eine E-Mail-Adresse der erstellenden Stelle (als `mailto:`-URI). Optional, Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Optional die E-Mail-Adresse der erstellenden Person oder Stelle. Beispiel: forschung@beispiel.de.

### Welche Version hat dieser Datensatz?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `owl:versionInfo` |
| Meta-Key | `_odw_version` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `owl:versionInfo` (Versionsbezeichnung) hat der Datensatz?

**2 · Verständliche Frage:** Welche Version hat dieser Datensatz?

**3 · DCAT-AP-Langbeschreibung:** `owl:versionInfo` gibt die Versionsbezeichnung des Datensatzes als Freitext an (`literal`). Multiplizität 0..1. Erlaubt die Unterscheidung aufeinanderfolgender Ausgaben desselben Datensatzes.

**4 · Verständliche Langbeschreibung:** Falls es mehrere Ausgaben Ihrer Daten gibt, tragen Sie die Versionsnummer ein. So wissen Nutzende, welche Fassung sie vor sich haben. Beispiel: 2.0.

### Was hat sich in dieser Version geändert?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `adms:versionNotes` |
| Meta-Key | `_odw_version_notes` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `adms:versionNotes` (Änderungshinweise) beschreiben diese Version?

**2 · Verständliche Frage:** Was hat sich in dieser Version geändert?

**3 · DCAT-AP-Langbeschreibung:** `adms:versionNotes` beschreibt die Änderungen gegenüber der Vorversion als sprachlich getaggten Freitext (`literal-lang`). Multiplizität 0..n. Ergänzt `owl:versionInfo` um den inhaltlichen Änderungsverlauf.

**4 · Verständliche Langbeschreibung:** Beschreiben Sie kurz, was sich gegenüber der letzten Version geändert hat — wie ein Änderungsprotokoll. Nutzende sehen so, ob sich ein erneuter Download lohnt. Beispiel: „Daten für 2025 ergänzt, Tippfehler korrigiert."

### Welche räumliche Auflösung haben die Daten (in Metern)?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:spatialResolutionInMeters` |
| Meta-Key | `_odw_spatial_resolution` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dcat:spatialResolutionInMeters` (räumliche Auflösung) haben die Daten?

**2 · Verständliche Frage:** Welche räumliche Auflösung haben die Daten (in Metern)?

**3 · DCAT-AP-Langbeschreibung:** `dcat:spatialResolutionInMeters` gibt die kleinste räumlich unterscheidbare Einheit in Metern an (`xsd:decimal`). Multiplizität 0..n. Relevant vor allem für Geodaten und Rasterdaten.

**4 · Verständliche Langbeschreibung:** Bei Geodaten: Geben Sie an, wie fein die Daten räumlich aufgelöst sind — der kleinste unterscheidbare Abstand in Metern. Ein kleiner Wert bedeutet detailliertere Daten. Beispiel: 10 (Raster von 10 Metern).

### Welche zeitliche Auflösung haben die Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:temporalResolution` |
| Meta-Key | `_odw_temporal_resolution` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dcat:temporalResolution` (zeitliche Auflösung) haben die Daten?

**2 · Verständliche Frage:** Welche zeitliche Auflösung haben die Daten?

**3 · DCAT-AP-Langbeschreibung:** `dcat:temporalResolution` gibt die kleinste zeitlich unterscheidbare Einheit als ISO-8601-Dauer an (`xsd:duration`). Multiplizität 0..1. Beschreibt z. B. den Messabstand einer Zeitreihe.

**4 · Verständliche Langbeschreibung:** Bei Zeitreihen: Geben Sie an, in welchem zeitlichen Abstand die Werte erfasst sind — z. B. stündlich, täglich, monatlich. Format ist die ISO-8601-Dauer. Beispiel: P1D (ein Tag) oder PT1H (eine Stunde).

### Welchem Standard oder Schema entsprechen die Daten?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:conformsTo` |
| Meta-Key | `_odw_conforms_to` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welchem `dct:conformsTo` (Standard/Schema) entsprechen die Daten?

**2 · Verständliche Frage:** Welchem Standard oder Schema entsprechen die Daten?

**3 · DCAT-AP-Langbeschreibung:** `dct:conformsTo` verweist auf einen implementierten Standard, ein Anwendungsprofil oder ein Schema, dem die Daten entsprechen (Range `dct:Standard`, per URI oder Bezeichnung). Multiplizität 0..n.

**4 · Verständliche Langbeschreibung:** Falls Ihre Daten einem bestimmten Standard oder Schema folgen, nennen Sie ihn hier. So können Systeme die Daten korrekt interpretieren. Beispiel: INSPIRE, XÖV oder ein fachliches Datenschema.

### Woher stammen die Daten und wie sind sie entstanden?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:provenance` |
| Meta-Key | `_odw_provenance` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dct:provenance` (Herkunft/Entstehung) hat der Datensatz?

**2 · Verständliche Frage:** Woher stammen die Daten und wie sind sie entstanden?

**3 · DCAT-AP-Langbeschreibung:** `dct:provenance` beschreibt die Herkunft und Entstehungsgeschichte des Datensatzes (Range `dct:ProvenanceStatement`, Freitext). Multiplizität 0..n. Dokumentiert Erhebungs- und Verarbeitungsschritte.

**4 · Verständliche Langbeschreibung:** Beschreiben Sie, woher die Daten kommen und wie sie entstanden sind — z. B. aus welcher Erhebung oder welchem Verfahren. Das macht die Daten nachvollziehbar und vertrauenswürdig. Beispiel: „Erhoben im Rahmen der Bürgerbefragung 2025, anonymisiert aufbereitet."

### Wie heißt die bereitgestellte Datei/Distribution?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:title` |
| Meta-Key | `_odw_dist_title` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wie lautet der `dct:title` der Distribution?

**2 · Verständliche Frage:** Wie heißt die bereitgestellte Datei/Distribution?

**3 · DCAT-AP-Langbeschreibung:** `dct:title` auf Ebene der Distribution benennt die konkrete bereitgestellte Ressource (`literal-lang`). Multiplizität 0..n. Nützlich, wenn der Distributionsname vom Datensatztitel abweicht.

**4 · Verständliche Langbeschreibung:** Geben Sie der bereitgestellten Datei einen eigenen Namen, falls er sich vom Titel des Datensatzes unterscheidet. Beispiel: „Rohdaten CSV 2025".

### Wie lässt sich die Distribution beschreiben?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:description` |
| Meta-Key | `_odw_dist_description` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Wie lautet die `dct:description` der Distribution?

**2 · Verständliche Frage:** Wie lässt sich die Distribution beschreiben?

**3 · DCAT-AP-Langbeschreibung:** `dct:description` auf Distributionsebene beschreibt die konkrete Datei/Ressource (`literal-lang`). Multiplizität 0..n. Ergänzt die Datensatzbeschreibung um distributionsspezifische Hinweise (z. B. Spaltenaufbau).

**4 · Verständliche Langbeschreibung:** Beschreiben Sie die konkrete Datei näher — etwa welche Spalten sie enthält oder wie sie aufgebaut ist. Das hilft beim direkten Umgang mit der Datei. Beispiel: „Spalten: Jahr, Stadt, Einwohner; Trennzeichen Semikolon."

### Wie lautet der direkte Download-Link zur Datei?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:downloadURL` |
| Meta-Key | `_odw_download_url` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Unter welcher `dcat:downloadURL` ist die Datei direkt herunterladbar?

**2 · Verständliche Frage:** Wie lautet der direkte Download-Link zur Datei?

**3 · DCAT-AP-Langbeschreibung:** `dcat:downloadURL` verweist auf eine direkt herunterladbare Datei (Range `rdfs:Resource`). Anders als `dcat:accessURL` garantiert sie einen unmittelbaren Download derselben Ressource. Multiplizität 0..n. Relevant für die MQA-Dimension Zugänglichkeit.

**4 · Verständliche Langbeschreibung:** Falls es einen direkten Link gibt, der die Datei sofort herunterlädt (ohne Umweg über eine Seite), tragen Sie ihn hier ein. Das ist der bequemste Weg für Nachnutzende. Beispiel: https://beispiel.de/daten/statistik.csv.

### Welchen Medientyp (MIME) hat die Datei?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dcat:mediaType` |
| Meta-Key | `_odw_media_type` |
| Stufe | Optional |
| Vokabular | `iana-media-type` |

**1 · DCAT-AP-Frage:** Welchen `dcat:mediaType` (IANA-MIME-Typ) hat die Distribution?

**2 · Verständliche Frage:** Welchen Medientyp (MIME) hat die Datei?

**3 · DCAT-AP-Langbeschreibung:** `dcat:mediaType` gibt den Medientyp der Distribution als IANA-Media-Type an (z. B. `text/csv`, `application/json`), referenziert über das IANA-Media-Types-Register. Multiplizität 0..1. Ergänzt `dct:format` um den technischen MIME-Typ.

**4 · Verständliche Langbeschreibung:** Der technische Medientyp der Datei (MIME-Typ) — die maschinenlesbare Entsprechung zum Format. Systeme erkennen daran, wie sie die Datei behandeln müssen. Beispiel: text/csv, application/json.

### Welche Nutzungsrechte gelten für die Datei?

| Eigenschaft | Wert |
|---|---|
| DCAT-Property | `dct:rights` |
| Meta-Key | `_odw_dist_rights` |
| Stufe | Optional |
| Vokabular | — |

**1 · DCAT-AP-Frage:** Welche `dct:rights` (Nutzungsrechte) gelten für die Distribution?

**2 · Verständliche Frage:** Welche Nutzungsrechte gelten für die Datei?

**3 · DCAT-AP-Langbeschreibung:** `dct:rights` beschreibt rechtliche Hinweise zur Distribution jenseits der Lizenz — etwa Urheberrechts- oder Zugriffsvermerke (Range `dct:RightsStatement`, Freitext oder URI). Multiplizität 0..1.

**4 · Verständliche Langbeschreibung:** Ergänzende rechtliche Hinweise zur Datei, die über die Lizenz hinausgehen — z. B. Urheberrechtsvermerke. Für viele offene Datensätze bleibt das leer. Beispiel: „© Stadt Musterstadt 2026".

