# Changelog — Open Data Wizard

Alle bedeutsamen Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.32.0] — 2026-07-30

Aufgeräumtes Onboarding, Batch-Import im Admin-Design + vollständige Übersetzung
(Paket B, Teil 4 von 4 — Abschluss).

### ✨ Changed / Fixed
- **Batch-Import-Seite ins Admin-Design integriert (B6):** Die zahllosen Inline-Styles
  sind in `admin.css`-Klassen (`.odw-batch-*`) umgezogen, die Buttons nutzen jetzt die
  Standard-WordPress-Stile (`button-primary`), und dekorative Emojis sind `aria-hidden`
  (keine störende Screenreader-Ausgabe mehr). Emojis wurden aus übersetzbaren Strings
  entfernt.
- **Bugfix Batch-Vorschau:** Der Vorschaubereich wurde nie eingeblendet (das umschließende
  `#odw-preview-section` blieb dauerhaft `display:none`) — die Vorschau erschien nie. Jetzt
  wird der Bereich beim Klick auf „Vorschau" korrekt angezeigt. (Fiel erst auf, seit die
  Capability-Vergabe aus v2.27.0 die Seite überhaupt erreichbar macht.)
- **Einstiegsseite mit dem realen Formular synchronisiert:** Schritt 1 erwähnt die neue
  optionale Untergruppe („Weitere Einordnung & Übersetzungen"), Schritt 2 nicht länger die
  dorthin verschobene Themenklassifikation; der „Erste Schritte"-Abschnitt erklärt jetzt das
  Pflichtfeld-Sternchen und das Entwurf-/Veröffentlichen-Verhalten aus v2.29.0.

### 🌍 i18n
- **Vollständige englische Übersetzung** aller in Paket B (v2.29.0–v2.32.0) neu hinzugekommenen
  Strings (27 Einträge) — `en_US` ist wieder lückenlos.
- **Reproduzierbarer PO→MO-Compiler** als `bin/compile-mo.py` gebündelt (dependency-frei, ersetzt
  das fehlende `msgfmt`); in `CLAUDE.md` dokumentiert.

---

## [2.31.0] — 2026-07-30

Einheitliche Qualitäts-Darstellung (Paket B, Teil 3 von 4).

### 💅 Changed
- **Ein Leitwert überall (B4):** Metadatenqualität wird jetzt konsequent als
  **Prozent + Stufe** dargestellt (z. B. „72 % · Gut") — in der Listenspalte
  **und** in der Qualitäts-Meta-Box. Zuvor mischten sich drei Zahlensysteme
  (Listenspalte „21/100 Punkte", Meta-Box „54/259 von 405").
- **Listenspalte:** Badge zeigt den Prozentwert (`72 %`); der MQA-Rohwert
  (`MQA 54/259 Punkte`) steht nur noch zusätzlich im Tooltip.
- **Meta-Box:** Überschrift ist jetzt der große **Prozentwert + Stufen-Badge**;
  der MQA-Rohwert („MQA-Rohwert: 54 / 259 Punkte (von max. 405)") erscheint als
  unauffällige Nebenzeile unter dem Fortschrittsbalken.

### 🔧 Technisch
- Der in `_odw_mqa` gespeicherte `score` ist bereits der MQA-Prozentwert
  (`achieved/assessable*100`); Listenspalte und Meta-Box nutzen jetzt beide
  denselben Wert und dieselbe Formel.
- Zwei tote CSS-Selektoren entfernt (`.odw-quality-score-number`,
  `.odw-quality-of-max`), Meta-Box-Summary auf ein klares Kopf-/Nebenzeilen-Layout
  umgestellt.

---

## [2.30.0] — 2026-07-30

Aufgeräumtes Tab 1 und einheitliche Begriffe (Paket B, Teil 2 von 4).

### ✨ Changed
- **Tab 1 „Grundlegende Informationen" entschlackt (B3):** Reihenfolge jetzt
  **Herausgeber → Beschreibung → Thema** (die zwei Pflichtangaben stehen oben).
  CESSDA-Themenklassifikation, ZiviZ-Engagementfeld und die Titel-/Beschreibungs-
  Übersetzungen sind in eine **aufklappbare Untergruppe** („Weitere Einordnung &
  Übersetzungen") ans Tab-Ende gewandert — gleiches Accordion-Muster wie in Tab 4.
- **Einheitliche Begriffe (B5):** Durchgängig **„Thema"** (statt „Kategorie") für
  `dcat:theme` und **„Schlagworte/Schlagwörter"** (statt „Stichworte") für
  `dcat:keyword` — im Formular, in der Feld-Referenz, in den MQA-Metrik-Labels und
  auf der Einstiegsseite.

### 🔧 Technisch
- Das Accordion (`sectionFields` in `odw-admin-fields.js`) klappt jetzt auch
  eingefügte Widgets (z. B. das sichtbare CESSDA-Eingabefeld) mit ein, nicht nur
  Carbon-Fields-Felder.
- `docs/FELD-REFERENZ.md` neu generiert (`php bin/generate-field-reference.php`).

### ✅ Tests
- Neuer Terminologie-Test stellt die Begriffs-Konsistenz im Feld-Katalog sicher.
  Gesamt: 177.

---

## [2.29.0] — 2026-07-30

Bessere Pflichtfeld-Führung für nicht-technische Nutzer (Paket B, Teil 1 von 4).

### ✨ Added / Changed
- **Pflichtfeld-Konzept repariert:** Die Pflichtfelder (Herausgeber, Beschreibung, Lizenz)
  sperren nicht länger schon das Speichern eines leeren **Entwurfs**. Stattdessen sind sie
  sichtbar mit einem roten Sternchen (`*`) markiert, eine kurze Legende erklärt: „Pflichtfeld
  zum Veröffentlichen — als Entwurf jederzeit unvollständig speicherbar". Erzwungen wird die
  Vollständigkeit weiterhin ausschließlich beim **Veröffentlichen** (Server-Validierung).
- **Fehlermeldungen mit Ort und Sprung:** Wird die Veröffentlichung blockiert, nennt die
  Meldung jetzt pro fehlender Angabe den **Tab** („Tab 3 — Datenbereitstellung") und ein
  **Klartext-Label in Formularsprache** (der technische DCAT-AP-Begriff steht nur noch klein
  in Klammern). Ein Button **„Zum Feld springen"** wechselt direkt auf den passenden Tab,
  klappt bei Bedarf die zugehörige Gruppe auf und hebt das Feld kurz hervor.

### 🔧 Technisch
- `ODW_Validation::validate()` liefert strukturierte Fehler (`label`, `dcat`, `tab`, `target`,
  `section`) statt roher Strings; `ODW_Fields::get_required_fields()` trägt den Registry-`key` mit.
- Neue JS-Routinen in `odw-admin-fields.js` (`initRequiredMarks`, `initGotoLinks`) — progressive
  Enhancement, ohne JS bleiben alle Felder sichtbar und die Validierung greift weiterhin.

### ✅ Tests
- Validierungstests auf die strukturierte Fehlerform umgestellt; neuer Test prüft Tab/Target-
  Metadaten für die Sprung-Links. Gesamt: 176.

---

## [2.28.0] — 2026-07-30

UX- und Konsistenz-Verbesserungen (Teil 2 des Audit-Nachgangs).

### 💅 Changed / Fixed
- **Keine rohen URIs mehr im UI:** Thema, Sprache, Aktualisierungsfrequenz, Zugriffsrechte und
  CESSDA-Themenfeld werden in der Listenspalte und der Frontend-Card als **lesbares Label** angezeigt
  (statt der EU-URI). Der REST-Filter `?theme=` akzeptiert weiterhin URI, Code (`SOCI`) oder Label.
- **Qualitäts-Balken zeigt die richtigen Ampelfarben** (CSS-Klassen-Mismatch behoben).
- **Deutsche Repeater-Beschriftungen:** „Übersetzung(en)" bzw. „Distribution(en)" statt „Entry" via
  `setup_labels()`.
- **Einstiegsseite** ist jetzt auch für Redakteure sichtbar (`manage_open_data` statt `manage_options`).
- **Änderungsdatum** ist als automatisch/schreibgeschützt gekennzeichnet (wird bei jedem Speichern gesetzt).
- **Dateigröße-Widget** verändert gespeicherte Byte-Werte nicht mehr beim bloßen Öffnen der Seite.
- **Format-Ableitung beim Upload** ist case-/trennzeichen-tolerant (`GEOJSON` → `GeoJSON`, `jsonld` →
  `JSON-LD`) und speichert den kanonischen Format-Key.
- **Batch-Import:** Schlagworte werden bei Komma- ODER Zeilentrennung korrekt in mehrere `dcat:keyword`
  aufgeteilt; das `theme`-Feld (Code/Label) wird zur EU-URI aufgelöst; die Seite siezt jetzt durchgängig.
- **Lizenz-Schnellauswahl** um **DL-DE BY 2.0** und **DL-DE Zero 2.0** ergänzt (inkl. Klartext-Erklärung).
- **Tab-Navigation-Accessibility:** Tooltips per **Escape** schließbar, größere Klickfläche, sichtbarer
  Fokusring; Frontend-Größen-Badge abgedunkelt (besserer Kontrast).
- **Einstellungen:** „Herausgebende Organisation" in die Sektion „Standardwerte" verschoben.
- Toter CSS-Selektor entfernt.

### 🌍 i18n
- **Vollständige englische Übersetzung:** 19 zuvor fehlende Strings ergänzt (u. a. Datenatlas-Block,
  neues Zugriffs-URL-Label, CESSDA-Label) — `en_US` ist jetzt lückenlos (systematisch per Tokenizer geprüft).

### ✅ Tests
- Neue Tests für `resolve_theme_uri()`, `resolve_label()`, `resolve_format_key()`. Gesamt: 175.

---

## [2.27.0] — 2026-07-30

Sicherheits- und Bugfix-Release nach einem umfassenden Audit (Security, Funktion, UX).

### 🔒 Security
- **SSRF-Härtung:** Die opt-in-URL-Erreichbarkeitsprüfung nutzt jetzt `wp_safe_remote_head/get`
  (blockt Loopback-/private/link-lokale Ziele wie `127.0.0.1` oder Cloud-Metadata-Adressen).
- **XSS behoben:** Die Fehlerliste der Batch-Import-Vorschau wird jetzt ausschließlich per `.text()`
  ins DOM geschrieben — präparierte CSV-/JSON-Inhalte können kein Script mehr im wp-admin ausführen.
- **Entwurfs-Enumeration unterbunden:** REST `/datasets/<id>` antwortet für unveröffentlichte
  Datensätze jetzt mit 404 (statt 403) und identischer Meldung.

### 🐛 Fixed
- **Capability `manage_open_data` wird jetzt tatsächlich vergeben** (Aktivierung + upgrade-sicherer
  Nachtrag auf `admin_init`; Entfernung bei Deinstallation). Vorher war der **Batch-Import auf frischen
  Installationen für alle unzugänglich** — auch für Administratoren.
- **Auto-Zugriffs-URL aus Datei-Upload & Auto-`dct:modified` funktionieren jetzt wirklich:** Beide
  Hooks liefen auf `save_post_odw_dataset` und wurden anschließend von Carbon Fields (`save_post`@10)
  wieder überschrieben — jetzt `save_post`@20 mit Post-Type-Guard.
- **Repeater-Header zeigen wieder Inhalte:** `set_header_template()` stand vor `add_fields()` und wurde
  von Carbon Fields verworfen (Template wird der zuletzt registrierten Gruppe zugewiesen) — alle vier
  Repeater korrigiert.
- **Papierkorb-Ansicht repariert:** Der Status-Filter erzwang in jeder Listenansicht `publish/draft`
  und machte Papierkorb/Ausstehend/Privat unbrauchbar — er greift jetzt nur noch bei explizitem
  Dropdown-Wert.
- **Tab-Navigation wiederbelebt:** `wizard-tabs.js` und die Tab-CSS zielten auf veraltete
  Carbon-Fields-Klassen (`tabs-nav`/`cf-tab__label`) — auf die echten CF-3.6-Klassen
  (`tabs-list`/`tabs-item--current`, Button) umgestellt; Tab-Persistenz, Pfeiltasten (+ Home/End)
  und das Tab-Styling funktionieren wieder.
- **`wp open-data-wizard quality recalculate` speichert jetzt** (rief zuvor nur `calculate()` ohne
  `store()` auf).
- **Delta-Harvesting:** `?since=YYYY-MM-DD` beginnt jetzt um 00:00 UTC (statt zur aktuellen Uhrzeit) —
  same-day-Änderungen gehen nicht mehr verloren (`!Y-m-d`-Format).
- **Publish-Validierung konsistent zu Multi-Distribution & Upload:** Zusätzliche Distributionen zählen
  als gültige Distribution; das Entfernen der hochgeladenen Datei wird im selben Save erkannt
  (POST-Wert hat Vorrang); Lizenzpflicht gilt jetzt auch für Upload-only-Datensätze und für jede
  zusätzliche Distribution.
- **Batch-Import-Lizenz-Aliasse** auf die `https`-URIs des Plugins vereinheitlicht (vorher `http` →
  Katalog-Filter, Lizenz-Labels und MQA-Vokabular-Metrik scheiterten für Importe); `dl-de-by`/`dl-de-zero`
  als Aliasse ergänzt.
- **MQA-Erreichbarkeitsmetriken** berücksichtigen jetzt alle Distributionen (eine erreichbare URL
  genügt) statt nur der primären.
- **Deinstallation löscht jetzt auch Papierkorb-Datensätze** (`post_status 'any'` schloss `trash` aus).

### ✅ Tests
- 4 neue Tests: Delta-Mitternacht-Semantik, Extra-Distribution als gültige Distribution,
  Lizenzpflicht bei Upload-only, „sonstige" ohne eigene URI. Gesamt: 172.

---

## [2.26.1] — 2026-07-30

### 🐛 Fixed
- **Übersetzungs-Repeater (Titel/Beschreibung) ließen sich nicht befüllen.** Die Unterfelder hießen
  `value` — ein bei Carbon Fields **reserviertes Schlüsselwort** für Complex-Felder. Dadurch erschien im
  Backend die Meldung „*value is a reserved keyword for Complex fields*" und der „Add entry"-Button
  reagierte nicht. Die Unterfelder heißen jetzt `content`; „Titel/Beschreibung in weiteren Sprachen"
  funktioniert wieder. (Da zuvor keine Einträge gespeichert werden konnten, ist **keine Migration** nötig.)
- **Regressionstest** ergänzt, der reservierte Complex-Unterfeldnamen (`value`/`_type`) künftig verhindert.

---

## [2.26.0] — 2026-07-30

### ✨ Added — DCAT-AP.de-Feld & Profi-UX
- **Neues Feld `dcatde:geocodingDescription`** („Wie lässt sich der räumliche Bezug in Worten
  beschreiben?", Tab „Erweiterte Angaben" → Abdeckung): textuelle Ergänzung zu `dct:spatial` /
  `dcatde:politicalGeocodingURI`. Im JSON-LD als sprachgetaggtes Literal, in Feld-Referenz und
  Frontend-Widget-Accordion enthalten.
- **Profi-UX v2:** Der große Bereich „Erweiterte Angaben" (Tab 4) ist jetzt in **einzeln aufklappbare
  Untergruppen** gegliedert (Verantwortlichkeiten · Zugriff · HVD · Weitere DCAT-AP-Felder ·
  Distribution) statt eines einzigen großen Reveals. Standardmäßig eingeklappt; Zustand je Gruppe in
  `sessionStorage`. Progressive Enhancement: ohne JS bleiben alle Felder sichtbar.

### 🌍 i18n / ✅ Tests
- 7 neue UI-Strings in `en_US`; JSON-LD-Test für `dcatde:geocodingDescription` (167 Tests). Feld-Referenz
  neu generiert (53 Felder).

---

## [2.25.0] — 2026-07-30

### 📚 Added — Offizielle SHACL-Shapes gebündelt
- Neues Verzeichnis **`config/shacl/`** mit den **offiziellen** DCAT-AP-SHACL-Shapes als Referenz:
  - `dcat-ap-SHACL.ttl` — EU DCAT-AP 3.0 (SEMIC, CC BY 4.0)
  - `dcat-ap-SHACL-DE.ttl` — DCAT-AP.de (GovData, CC0)
- **`config/shacl/README.md`** dokumentiert Herkunft, Lizenzen, Abrufdatum und die Anleitung zur
  Validierung der Plugin-Ausgabe (JSON-LD des Datensatzes → externer EU-/DCAT-AP.de-Validator bzw. lokal
  per pySHACL).
- **Hinweis:** Das Plugin führt SHACL nicht selbst aus (keine PHP-Engine); die Dateien dienen als Referenz
  und für die externe Validierung. Verlinkt aus `docs/MQA-KONZEPT.md` und `DOCUMENTATION.md`.

### ✅ Tests
- Integritätstest, dass beide Shape-Dateien vorhanden sind und den SHACL-Namespace referenzieren
  (166 Tests).

---

## [2.24.0] — 2026-07-30

### ✨ Added — Zusätzliche Distributionen in Widget & Scoring
- **Frontend-Card:** Zusätzliche Distributionen (aus „Weitere Distributionen") werden jetzt unterhalb der
  primären Datei mit **eigenem Download-Link** und **Format-/Größe-Badges** aufgelistet.
- **MQA-Scoring:** Distribution-bezogene Metriken (Format, Media-Type, Download-URL, Lizenz, Rechte,
  Dateigröße sowie Format-/Lizenz-Vokabular) gelten jetzt als erfüllt, sobald **irgendeine** Distribution
  sie erfüllt — nicht mehr nur die primäre. Ein Datensatz wird also nicht abgewertet, wenn z. B. ein
  maschinenlesbares Format nur in einer zusätzlichen Distribution vorliegt.
- Intern: `ODW_Quality::all_distributions()` (primäre + zusätzliche) und `any_distribution()`.

### ✅ Tests / i18n
- Reflection-Tests für `all_distributions()`/`any_distribution()` (inkl. „sonstige"-Lizenz einer
  zusätzlichen Distribution). Gesamt: 165 Tests. Ein neuer UI-String in `en_US`.

---

## [2.23.0] — 2026-07-30

### ✨ Added — Mehrsprachige Literale (Phase D, additiv)
- Neue optionale **Übersetzungs-Felder** für Titel, Beschreibung und Schlagworte:
  „Titel/Beschreibung/Schlagworte in weiteren Sprachen" (Repeater mit `{ Sprache, Wert }`).
- **JSON-LD:** `dct:title` und `dct:description` werden zu einem **Array sprachgetaggter Literale**,
  sobald Übersetzungen vorhanden sind — ohne Übersetzung bleibt es ein Einzelobjekt
  (**rückwärtskompatibel**). Übersetzte Schlagworte werden an `dcat:keyword` angehängt.
- Intern: gemeinsamer Helfer `odw_collect_lang_literals()`. **Keine Datenmigration** — die bisherigen
  Angaben bleiben die Hauptsprache.

### 🌍 i18n
- 13 neue UI-Strings in `en_US` übersetzt.

### ✅ Tests
- Mehrsprachiger Titel wird zum Array; Einzelsprache bleibt Objekt; übersetzte Schlagworte werden
  angehängt; Helfer überspringt leere/ungültige Zeilen. Gesamt: 162 Tests.

---

## [2.22.0] — 2026-07-30

### ✨ Added — Multi-Distribution (Phase E, additiv)
- Neues optionales, **wiederholbares Feld „Weitere Distributionen"** (Tab „Datenbereitstellung"): Ein
  Datensatz kann jetzt **mehrere Distributionen** haben — z. B. dieselben Daten in einem weiteren Format
  oder unter einer anderen URL. Jede zusätzliche Distribution hat eigene Felder (Zugriffs-URL, Format,
  Größe, Lizenz, Download-URL, MediaType, Titel, Beschreibung, Namensnennung, Rechte).
- **Rückwärtskompatibel & ohne Migration:** Die bisherige (primäre) Distribution bleibt unverändert; die
  Repeater-Einträge werden zusätzlich an `dcat:distribution` angehängt.
- Intern: Ein gemeinsamer Helfer `odw_build_distribution_node()` erzeugt primäre und zusätzliche
  Distributionen mit identischer JSON-LD-Struktur.

### 🌍 i18n
- 22 neue UI-Strings in `en_US` übersetzt.

### ✅ Tests
- Integrationstest (primäre + zusätzliche Distributionen → Array) und Helfer-Unit-Tests (kein Access-URL →
  `null`; eigene Lizenz + Freitext-Rechte; Sprachtag-Weglassung). Gesamt: 158 Tests.

### 📝 Hinweise
- Scoring/MQA bewertet weiterhin die **primäre** Distribution; die Frontend-Card zeigt weiterhin die
  primäre (zusätzliche Distributionen sind über REST/JSON-LD verfügbar) — mögliche Folgeerweiterung.

---

## [2.21.0] — 2026-07-30

### ✨ Added — „Mehr erfahren" im Formular (katalog-gespeist)
- Jedes Formularfeld erhält ein aufklappbares **„Mehr erfahren"** mit der **DCAT-AP-Langbeschreibung**
  und der **verständlichen Langbeschreibung** aus `config/field-catalog.php` — dieselbe Quelle wie die
  Feld-Referenz (docs/FELD-REFERENZ.md). Die ausführlichen Erklärungen erscheinen damit direkt im
  Wizard; die kurzen, **übersetzbaren** Hilfetexte bleiben unverändert (kein i18n-Regress).
- Die Katalog-Langtexte gelangen über `ODW_Field_Reference::js_map()` ins Admin-JS; das Panel wird per
  `assets/js/odw-admin-fields.js` progressiv ergänzt (auch für nachgeladene CF-Felder).
- 3 neue UI-Strings in `en_US` übersetzt.

### ✅ Tests
- Neuer **Konsistenztest**: Jedes Datenfeld des Formulars hat einen Katalog-Eintrag und umgekehrt —
  CI schlägt fehl, wenn ein Feld ergänzt/entfernt, aber der Katalog nicht gepflegt wurde.

---

## [2.20.0] — 2026-07-09

### 📚 Added — Mehrstufige Feld-Referenz
- Neue Dokumentation **[docs/FELD-REFERENZ.md](docs/FELD-REFERENZ.md)**: Jedes der **52 Formularfelder**
  wird in **vier Stufen** beschrieben — (1) DCAT-AP-Frage, (2) verständliche Frage, (3) DCAT-AP-
  Langbeschreibung, (4) verständliche Langbeschreibung — jeweils mit DCAT-Property, Meta-Key, Stufe
  (Pflicht/Empfohlen/Optional/Bedingt) und Vokabular.
- **Single Source of Truth:** `config/field-catalog.php`. Das Dokument wird daraus generiert — per
  **WP-CLI** (`wp open-data-wizard docs`) oder standalone (`php bin/generate-field-reference.php`,
  ohne WordPress, CI-tauglich) durch die neue Klasse `ODW_Field_Reference`.
- **Verlinkt** aus README und DOCUMENTATION.
- **Tests** (`tests/test-field-catalog.php`): Katalog-Vollständigkeit, eindeutige Keys und ein
  Sync-Check, der fehlschlägt, wenn der Katalog geändert, aber die Doku nicht neu generiert wurde.

---

## [2.19.0] — 2026-07-09

### ✨ Added — ZiviZ-Engagementfelder
- Neues **optionales** Auswahlfeld **„In welchem Engagementfeld ist die Organisation aktiv?"** im
  Schritt „Grundlegende Informationen". Auto-Suggest aus dem gebündelten **ZiviZ-Engagementfeld-
  Vokabular** (`config/vocabularies/engagementfeld.json`, 16 Felder — Soziale Dienste, Kultur, Sport,
  Umwelt- und Naturschutz u. a.); die zugehörige `ziviz.de`-URI wird automatisch verwendet.
- Im JSON-LD als `dct:subject` ausgegeben — zusammen mit einer CESSDA-Klassifikation wird
  `dct:subject` zu einem Array.
- Erscheint auch im Frontend-Widget-Accordion „Alle Metadaten anzeigen".

---

## [2.18.0] — 2026-07-09

### 💅 Changed — Download-Card (`[odw_dataset]`) neu gestaltet
- **Datensatzname groß** oben, rechts daneben ein Link **„Metadaten JSON"** (lädt das JSON-LD herunter).
- Prominenter **Download-Button** zur bereitgestellten Datei (Download-URL → Mediathek-Datei → Zugriffs-URL).
- Darunter **bunte Badges** in kleiner Schrift: **Dateiformat · Dateigröße · Lizenz**.
- **Aufklappbares Accordion** („Alle Metadaten anzeigen") mit **allen angegebenen Metadatenfeldern**
  (Herausgeber, Beschreibung, Thema, CESSDA, Sprache, Schlagworte, URLs, Format, Zeitraum, Kontakt u. v. m.).
- Frontend-CSS entsprechend überarbeitet; 18 neue UI-Strings in `en_US` übersetzt.

---

## [2.17.0] — 2026-07-09

### ✨ Added
- **Datei per Link ODER Upload** (Datenbereitstellung): Das Zugriffs-URL-Feld heißt jetzt
  „Ergänzen Sie den Link zu Ihrem Datensatz oder laden Sie die Datei in die Mediathek hoch". Wer eine
  Datei über die Mediathek-Box hochlädt, muss die URL **nicht** mehr eintippen — Zugriffs-URL und
  Format werden beim Speichern automatisch aus der Datei übernommen. Pflichtprüfung ist erfüllt,
  sobald **entweder** ein Link eingetragen **oder** eine Datei hochgeladen wurde.
- **„Auf dem Datenatlas Zivilgesellschaft veröffentlichen"** (optional): Im letzten Wizard-Schritt
  (Vorschau) verlinkt ein Button auf [datenatlas-zivilgesellschaft.de](https://datenatlas-zivilgesellschaft.de).

---

## [2.16.0] — 2026-07-08

### ✨ Changed
- **CESSDA-Themenklassifikation** in den Schritt **„Grundlegende Informationen"** verschoben (vormals
  „Inhaltliche Angaben") und mit klarerer Fragestellung versehen: „Ordnen Sie den Datensatz einem oder
  mehreren Themenfeld nach CESSDA-Vokabular zu".
- **Lizenz „Sonstige"**: Die Auswahlliste (Auto-Suggest) wurde um das offizielle
  **DCAT-AP.de-Lizenzregister** (`http://dcat-ap.de/def/licenses/…`, 34 Lizenzen) erweitert — von den
  Datenlizenzen Deutschland über Creative-Commons- und Open-Data-Commons-Lizenzen bis zu Software- und
  eingeschränkten Lizenzen. Diese URIs zählen zugleich für die MQA-Metrik „Lizenz aus Vokabular".

---

## [2.15.0] — 2026-07-07

MQA-Qualitäts-Scoring **Phase 3a**: optionale URL-Erreichbarkeitsprüfung.

### ✨ Added — URL-Erreichbarkeit (opt-in)
- Neue Einstellung **„URL-Erreichbarkeit prüfen"** (Bereich *Qualitätsprüfung (MQA)*,
  standardmäßig deaktiviert). Ist sie aktiv, prüft das Plugin `dcat:accessURL` und
  `dcat:downloadURL` beim Speichern per **HTTP HEAD** (mit GET-Fallback bei 405/501) auf
  Erreichbarkeit (Statuscode 200–399).
- Ergebnisse werden **24 Stunden** als Transient zwischengespeichert (Cache-Key je URL),
  sodass wiederholte Speichervorgänge keine erneuten Requests auslösen.
- Damit steigt das bewertbare Maximum bei aktivierter Prüfung von ~295 auf **~375 von 405**.
  Es verbleibt nur die **DCAT-AP-SHACL-Konformität** (30 P) als „nicht bewertet".

### 🔒 Datenschutz/Performance
- Die Prüfung ist **opt-in** und sendet ausgehende Anfragen ausschließlich an die im Datensatz
  hinterlegten URLs (kein Fremddienst). Kurzer Timeout (5 s) + 24h-Cache.

### 🧾 i18n
- 4 neue UI-Strings; `en_US.mo` neu kompiliert.

### ✅ Tests
- Erreichbarkeitslogik: Cache-Hit (kein Request) und 2xx→erreichbar (mit Caching).

---

## [2.14.0] — 2026-07-07

MQA-Qualitäts-Scoring **Phase 2**: Vokabular-Prüfungen (offline). Siehe `docs/MQA-KONZEPT.md`.

### ✨ Added — Vokabular-Metriken bewertet
- Die MQA-Metriken vom Typ `vocab` werden jetzt bewertet (offline, ohne neue Felder):
  - **Format/Media-Type aus Vokabular** (10) — Format ist Teil des kontrollierten EU-Vokabulars.
  - **Nicht-proprietäres Format** (20) und **Maschinenlesbares Format** (20) — aus den neuen Flags
    in `config/dct-format-list.php`.
  - **Lizenz aus Vokabular** (10) — Lizenz-URI ist im bekannten Lizenz-Set (`config/licenses.txt`
    bzw. Standardlizenzen).
  - **Zugriffsrechte aus Vokabular** (5) — Wert stammt aus dem EU-Access-Right-Vokabular.
- Das bewertbare Maximum steigt damit von ~230 auf **~295 von 405 Punkten** (rein offline).
- `config/dct-format-list.php` um `machine_readable` und `non_proprietary` je Format erweitert
  (orientiert an den EU-Vokabularen).

### 🔧 Verbleibend (Phase 3, opt-in)
- Nur noch **URL-Erreichbarkeit** (80) und **DCAT-AP-SHACL-Konformität** (30) sind „nicht bewertet".

### ✅ Tests
- Neuer Test für die Format-Flags (`get_format_meta`: CSV offen/maschinenlesbar, XLSX proprietär,
  „Sonstiges" ohne Vokabular).

---

## [2.13.0] — 2026-07-07

Neues Qualitäts-Scoring nach der EU-MQA-Methodik (Phase 1). Siehe `docs/MQA-KONZEPT.md`.

### ✨ Changed — Qualitätsprüfung auf MQA umgestellt
- Die Qualitätsprüfung folgt jetzt dem **Metadata Quality Assessment (MQA)** von data.europa.eu:
  **5 FAIR-Dimensionen** (Auffindbarkeit, Zugänglichkeit, Interoperabilität, Wiederverwendbarkeit,
  Kontext), **405 Punkte**, **4 Bewertungsstufen** (Ausgezeichnet / Gut / Ausreichend / Mangelhaft).
- Die Metriken stehen in der neuen Single-Source-of-Truth `config/mqa-metrics.php`.
- Die Meta-Box zeigt die Bewertung pro Dimension inkl. Punkte und Metrik-Status.
- Das JSON-LD/REST-Feld `odw:qualityScore` weist nun MQA-Punkte (von 405), die Bewertungsstufe und
  die Dimensions-Aufschlüsselung aus (verweist auf die MQA-Methodik-URL).

### 🔧 Phasenumfang
- **Phase 1 (dieses Release):** Alle „gesetzt?"-Metriken werden bewertet (offline).
- Vokabular-, URL-Erreichbarkeits- und DCAT-AP-SHACL-Metriken sind als **„nicht bewertet"** verdrahtet
  und werden aus dem bewertbaren Maximum herausgerechnet; die Bewertungsstufen werden **proportional**
  auf das bewertbare Maximum skaliert. Diese Metriken folgen in Phase 2 (Vokabular) und Phase 3
  (Netzwerk/SHACL).

### ♻️ Abwärtskompatibilität
- Die Publish-Validierung (`config/dcat-ap-fields.php`) bleibt unverändert.
- Der abgeleitete 0–100-Score und das Level bleiben in `_odw_quality_score` / `_odw_quality_level`
  erhalten (Admin-Listenspalte, Sortierung). Das vollständige MQA-Ergebnis liegt in `_odw_mqa`.

### 🌍 i18n
- 33 neue MQA-UI-Strings in `.po`/`.pot` ergänzt und `en_US.mo` neu kompiliert.

### ✅ Tests
- Neue Tests für Bewertungsstufen (proportionale Schwellen), Metrik-Summen je Dimension (405 gesamt)
  und die MQA-Persistierung/JSON-LD-Ausgabe.

---

## [2.12.0] — 2026-06-28

UX-Feinschliff und Vervollständigung der optionalen DCAT-AP-3.0-Felder.

### ✨ Added — optionale DCAT-AP-3.0-Felder (Tab 4, „Erweiterte Angaben")
Alle Pflicht- und empfohlenen DCAT-AP-3.0-Felder waren bereits enthalten; ergänzt wurden die praktischen **optionalen** Felder:
- **Dataset:** `dct:identifier`, `dct:type`, `dct:creator` (foaf:Agent), `owl:versionInfo`, `adms:versionNotes`, `dcat:spatialResolutionInMeters` (xsd:decimal), `dcat:temporalResolution` (xsd:duration), `dct:conformsTo`, `dct:provenance` (dct:ProvenanceStatement).
- **Distribution:** `dct:title`, `dct:description`, `dcat:downloadURL`, `dcat:mediaType`, `dct:rights` (URI oder dct:RightsStatement).
- `@context` um `rdfs` ergänzt (für `dct:ProvenanceStatement`/`dct:RightsStatement`-Label).

### 🐛 Fixed / Changed
- **CESSDA-Themenklassifikation:** Eingabefeld nun linksbündig über die volle Zeilenbreite (wie die übrigen Felder; bisher auf 400 px begrenzt).
- **Profi-Bereich umbenannt:** Toggle „Erweiterte Angaben **für Profis** …" → „Erweiterte Angaben …".
- **Willkommens-Hinweis nur einmalig:** „Open Data Wizard erfolgreich installiert!" wird jetzt genau einmal nach der Installation angezeigt und dann automatisch entfernt (statt bis zum manuellen Ausblenden bei jedem Seitenaufruf).

### ✅ Tests
- Neue Builder-Tests für die optionalen Dataset- und Distribution-Felder (inkl. Nicht-numerische räumliche Auflösung wird verworfen; Freitext-Rechte werden zu `dct:RightsStatement`).

---

## [2.11.3] — 2026-06-28

UX-/Korrektur-Fixes aus Praxis-Feedback bei der Live-Installation.

### 🐛 Fixed
- **Hilfe-Tooltip (ⓘ) sprang in die nächste Zeile:** Das ⓘ-Symbol wird jetzt **innerhalb des Feld-Labels** verankert und sitzt damit in derselben Zeile wie die Formularfrage (zuvor als Geschwister-Element unter dem block-level Label umgebrochen).
- **CESSDA-Themenklassifikation ohne Erklärung:** Da das eigentliche CF-Feld (mit Hilfetext) ausgeblendet ist, hatte das JS-gebaute Eingabe-Widget keinen Tooltip. Es erhält nun denselben ⓘ-Tooltip mit Erklärung **(dct:subject)** inkl. Handlungsaufforderung („Thema eintippen oder auswählen"). Die URI wird weiterhin automatisch verknüpft.
- **DCAT-AP-Namen in der Qualitätsprüfung ergänzt:** Die Indikatoren *Beschreibung*, *Herausgeber* und *Lizenz* trugen in der Registry (`config/dcat-ap-fields.php`) noch die reine Formularfrage als Label; sie heißen nun einheitlich `Beschreibung (dct:description)`, `Herausgeber (dct:publisher)` und `Lizenz (dct:license)` — konsistent mit den übrigen Indikatoren (wirkt auch auf Validierungsmeldungen).
- **Qualitäts-Score wird beim ersten Veröffentlichen/Speichern korrekt berechnet:** Der Neuberechnungs-Hook lief auf `save_post_odw_dataset`, das WordPress **vor** dem generischen `save_post` feuert — genau dort speichert Carbon Fields aber erst seine Meta-Werte (Priorität 10). Dadurch wurde der Score aus veralteten Daten berechnet und stimmte erst nach dem **zweiten** Speichern. Der Hook läuft nun auf `save_post` (Priorität 30, nach dem CF-Save) mit Post-Type-Guard. Batch-Importe berechnen die Qualität nun ebenfalls explizit nach dem Schreiben der Metadaten.

### ✅ Tests
- Neuer Registry-Test stellt sicher, dass jedes Label seinen DCAT-AP-Property-Namen trägt.

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
