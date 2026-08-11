# Changelog — Open Data Wizard

Alle bedeutsamen Änderungen an diesem Projekt sind in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.40.0] — 2026-08-11

Eine Ablaufgrafik, die erklärt, was das Plugin eigentlich tut.

### ✨ Added
- **`assets/images/ODW-Ablauf.svg`** — „Vom Formular ins Datenportal" in drei Schritten:
  Formular ausfüllen → DCAT-AP.de-Metadaten erzeugen → Portale ernten den Katalog ab.
  Eingebunden im README (unter „Die Idee") und auf der Einstiegsseite im Backend.

### ℹ️ Zwei Entscheidungen dahinter
- **Nur Begriffe, die das Plugin auch wirklich ausgibt.** Die Vorlage zeigte unter anderem
  `dct:filleout`, `dct:snapet` und `dct:name` — die ersten beiden existieren im Vokabular
  nicht, und der Herausgebername ist `foaf:name`, nicht `dct:name`. Die Grafik zeigt jetzt
  `dcat:Catalog`, `dct:title`, `dct:publisher`, `foaf:name`, `dcat:dataset`, `dct:description`
  und `dcat:distribution` — jeder Begriff gegen `class-fields.php` und `class-rest-api.php`
  geprüft. Eine Erklärgrafik mit erfundenen Fachbegriffen erklärt das Falsche.
- **SVG statt Rasterbild.** Text bleibt Text: scharf auf jedem Bildschirm, durchsuchbar, und
  mit `<title>`/`<desc>` auch für Screenreader beschrieben. Die Datei ist rund 7 KB groß und
  liegt im Repository statt an einer Attachment-URL — sie funktioniert damit auch im Fork.

---

## [2.39.1] — 2026-08-10

Wartungs-Release. **Am Plugin selbst ändert sich nichts** — keine Änderung an PHP, CSS oder
JavaScript, Carbon Fields unverändert bei v3.6.9. Wer 2.39.0 installiert hat, verpasst nichts.

### 📝 Dokumentation
- **README-Kopf auf die Bildsprache des Logos umgestellt.** Das bisherige Banner hing an einer
  GitHub-Attachment-URL — an den Upload gebunden, nicht an das Repository. Jetzt zeigt der Kopf
  auf `assets/images/ODW-Logo.svg` aus dem Repository und funktioniert damit auch in einem Fork
  oder lokalen Klon. Badges in den Markenfarben, Zauberer-Emoji entfallen.

### 🔧 Nur im Repository, nicht im Paket
- **`main` war zwischenzeitlich rot.** Zwei npm-Updates von Dependabot (#106, #107) wurden
  nacheinander gemergt; der zweite nahm beim Merge die `package.json`-Änderung des ersten zurück,
  behielt aber dessen Lock-Eintrag. `npm ci` verweigerte danach den Dienst und der SHACL-Job
  scheiterte. Behoben, beide Upgrades zusammen verifiziert.
- **Ursache in der Dependabot-Konfiguration abgestellt.** Die Gruppen deckten nur `minor` und
  `patch` ab; ein Major-Sprung fiel heraus und bekam einen eigenen Pull Request — daher zwei
  konkurrierende Änderungen an derselben Lock-Datei. Alle Gruppen umfassen jetzt sämtliche
  Update-Typen. Carbon Fields bleibt bewusst ungruppiert und bekommt weiterhin einen eigenen
  Pull Request.
- Entwicklungsabhängigkeiten aktualisiert (PHPUnit, PHPStan, WP_Mock, php-parser,
  `@zazuko/env-node` 3.x, `rdf-validate-shacl` 0.6.x) sowie GitHub Actions. Nichts davon wird
  ausgeliefert.

---

## [2.39.0] — 2026-08-10

Das Logo im Backend.

### ✨ Added
- **Logo als Menü-Icon** im Backend statt des generischen `dashicons-database`.
- **Logo im Kopf der Einstiegs- und der Einstellungsseite**, neben der jeweiligen Überschrift.
- `assets/images/` enthält jetzt `ODW-Logo.svg`, `ODW-Logo.png` und die zugeschnittene
  Icon-Fassung `ODW-Icon.svg`.

### ℹ️ Zwei Entscheidungen dahinter
- **Eigene Icon-Fassung fürs Menü.** Menü-Icons sind 20 × 20 Pixel; die Wortmarke „ODW" wäre dort
  ein unlesbarer Fleck. `ODW-Icon.svg` schneidet den `viewBox` auf das Zeichen zu und lässt die
  drei Wortmarken-Pfade weg — dieselbe Zeichnung, anderer Ausschnitt, keine Änderung an den
  übrigen Pfaden.
- **Als URL eingebunden, nicht als base64-`data:`-URI.** WordPress dimmt Menü-Icons auf 60 Prozent
  Deckkraft, bis man daraufzeigt; ein `data:`-SVG behält volle Deckkraft und stäche als einziges
  Symbol im Menü heraus. Nebeneffekt: rund 7 KB Base64 weniger auf jeder Admin-Seite.

Beides wurde vor dem Einbau in einem Browser gerendert und geprüft — bei 20 Pixeln auf dunklem
wie hellem Menügrund, und die Kopfzeilen bei 40 bzw. 48 Pixeln.

### 🎨 Styling
- `.odw-page-title` / `.odw-page-logo` richten Logo und Überschrift aneinander aus. Nur die Höhe
  ist festgelegt — das Logo ist mit 932 × 830 nicht quadratisch, eine feste Breite dazu würde es
  verzerren.

### ℹ️ Nicht eingebunden
Die Download-Karte im Frontend bleibt ohne Logo: Sie steht auf fremden Websites, dort wäre unser
Zeichen Absender statt Beiwerk. Und das README behält seinen bestehenden Kopf — zwei
unterschiedliche Logos übereinander wären Rauschen.

---

## [2.38.0] — 2026-08-07

Gutenberg-Block „Datensatz-Karte" — keine IDs mehr abtippen.

### ✨ Added
- **Block „Datensatz-Karte"** als Alternative zum Shortcode `[odw_dataset id="123"]`. Im Editor
  über das Plus-Symbol einfügen und den Datensatz aus einer Liste wählen; zur Auswahl stehen nur
  veröffentlichte Datensätze. Wer den Shortcode bevorzugt oder klassische Editoren nutzt, kann
  ihn unverändert weiterverwenden.

### ℹ️ Umsetzung
- **Dynamischer Block:** Gespeichert wird ausschließlich die Datensatz-ID, das Markup entsteht
  beim Ausliefern über `ODW_Shortcode::render()`. Damit gibt es genau eine Quelle für die Karte —
  Änderungen daran wirken auf Block und Shortcode gleichermaßen —, und ein später umbenannter
  Datensatz erscheint mit seinem aktuellen Titel statt als eingefrorene Kopie im Beitrag.
- **Kein Build-Schritt.** `blocks/dataset-card/` enthält `block.json` und ein Editor-Skript in
  schlichtem JavaScript statt JSX. Das Projekt hat keine JS-Build-Kette für den Admin-Bereich,
  und eine allein für diesen Block einzuführen wäre viel Apparat für wenig Ertrag.
- **Auswahlliste über `wp_localize_script`,** nicht über den Core-Datenspeicher: Der Custom Post
  Type ist bewusst nicht über die WP-REST-API exponiert (`show_in_rest => false`), dafür gibt es
  die eigenen Endpunkte. Die Liste wird nur im Editor aufgebaut (`enqueue_block_editor_assets`)
  und ist auf 200 Einträge begrenzt.
- **Platzhalter statt Live-Vorschau im Editor.** Eine serverseitig gerenderte Vorschau bekäme das
  Frontend-Stylesheet im Editor-Rahmen nicht mit und sähe dort kaputt aus; die Platzhalterkarte
  nutzt WordPress' eigene `Placeholder`-Komponente und braucht deshalb kein eigenes CSS.
- `bin/build-release.sh` kopiert `blocks/` mit ins Paket — ohne diese Ergänzung wäre der Block in
  der Installation schlicht nicht vorhanden gewesen.

---

## [2.37.0] — 2026-08-07

Alle 24 EU-Amtssprachen statt zwei.

### ✨ Added
- **Sprachauswahl als gebündeltes Vokabular** (`config/vocabularies/language.json`). Bisher standen
  fest im Code nur Deutsch und Englisch zur Wahl — und zwar nicht nur beim Sprachfeld des
  Datensatzes, sondern auch in allen drei Übersetzungs-Repeatern und bei der Standardsprache in
  den Einstellungen. Mehrsprachige Metadaten waren damit praktisch auf Englisch beschränkt,
  obwohl das Plugin sie als Funktion führt.

### ℹ️ Hintergrund
Die Begrenzung lag ausschließlich in der Auswahlliste: `odw_resolve_language_tag()` setzte alle
24 EU-Sprachcodes schon vorher korrekt nach BCP-47 um (`DEU` → `de`, `POL` → `pl` …). Die
JSON-LD-Ausgabe war also längst vorbereitet, das Formular bot die Sprachen nur nicht an.

Deutsch und Englisch stehen weiterhin an erster Stelle, die übrigen 22 alphabetisch darunter.
Bleibt die Vokabulardatei einmal aus, fällt die Auswahl auf Deutsch und Englisch zurück — ohne
mindestens eine Sprache ließe sich weder eine Übersetzung pflegen noch eine Standardsprache
wählen.

Wie bei den anderen gebündelten Vokabularen stehen die Bezeichnungen in der JSON-Datei und
durchlaufen damit nicht die Übersetzungsdateien. Das ISO-Kürzel im Label („Polnisch (PL)")
macht den Eintrag trotzdem eindeutig.

### 📝 Dokumentation
- Die Roadmap in `CLAUDE.md` führte `access-right` und die EU-Sprachliste als offen. Das
  `access-right`-Vokabular liegt seit Längerem vor; beide Punkte sind jetzt als erledigt vermerkt.

---

## [2.36.0] — 2026-08-07

Überarbeitetes Formular-Design — Typografie, Abstände, Eingabefelder.

### 🔧 Changed
- **Abstände kommen jetzt vom Raster, nicht mehr von Rändern je Feld.** Das Tab-Panel ist ein
  CSS-Grid mit `gap`; Margins an einzelnen Feldern kollabierten oder verdoppelten sich, je
  nachdem was daneben stand. Das Ergebnis ist ein gleichmäßiger vertikaler Rhythmus.
- **Zwei Spalten für zusammengehörige Felder.** Format und Dateigröße, Beginn und Ende des
  Zeitraums, Name und E-Mail bei Urheber, pflegender Stelle und Ersteller sowie die beiden
  Kontaktfelder stehen nebeneinander statt jeweils über die volle Breite. Unterhalb von 782 px —
  der Bruchstelle des WordPress-Backends — wird wieder einspaltig umbrochen.
- **Eingabefelder einheitlich und größer.** 40 statt rund 30 Pixel Höhe, weichere Rundung,
  sichtbarer Fokus-Ring. Text-, Auswahl-, Zahlen- und Datumsfelder erbten bislang
  unterschiedliche Standardhöhen von WordPress und sahen entsprechend uneinheitlich aus.
- **Reiter mit Unterstrich** statt gerahmter Karteikarten-Optik.
- **Wiederholbare Distributionen als Karten** mit eigener Kontur und ruhigem Kopfbereich.
- **Datei-Upload als getönter Block** direkt unter der Zugriffs-URL — sichtbar die Alternative
  zu dem Feld darüber, nicht ein weiteres Pflichtfeld daneben.
- Hilfetexte nicht mehr kursiv und mit größerem Zeilenabstand. Betrifft die Anzeige ohne
  JavaScript; mit JavaScript wandert der Text weiterhin in den ⓘ-Tooltip.

### ℹ️ Wartungshinweis
Alle Regeln, die an Klassennamen von Carbon Fields hängen, stehen gesammelt im Block
**`FORMULAR-DESIGN`** am Ende von `assets/css/admin.css`. Das ist nach einem Carbon-Fields-Update
die einzige Stelle, die zu prüfen ist. Die Abhängigkeit ist damit von acht auf rund zwanzig
CF-Klassen gestiegen — der Preis für die Gestaltung, dafür an einem Ort statt verstreut.

Die Zweispaltigkeit wählt Felder über ihr `name`-Attribut aus, nicht über ein `data`-Attribut:
Carbon Fields reicht `data`-Attribute bei `<select>` nicht zuverlässig ans DOM durch, der
Meta-Key steht dagegen immer im Namen. Dadurch war **keine Änderung an PHP nötig** — der
Umbau ist reines CSS.

### 🎨 Added
- Token für Abstände, Radien, Feldhöhe und Schriftgrößen in `:root`. Werte stehen damit an einer
  Stelle statt über 1.400 Zeilen verteilt.

---

## [2.35.8] — 2026-08-07

Datei-Upload steht dort, wo er hingehört.

### 🔧 Changed
- **Der Datei-Upload ist keine Seitenleisten-Box mehr,** sondern steht in Tab 3 unmittelbar unter
  der Zugriffs-URL — der Angabe, zu der er die Alternative ist. Vorher waren es zwei getrennte
  Orte für denselben Sachverhalt: Link im Formular, Datei in der Seitenleiste. Das lud zu
  widersprüchlichen Angaben ein, ohne dass die Oberfläche den Zusammenhang zeigte. Die Hilfetexte
  beider Felder verweisen jetzt aufeinander.

### ℹ️ Technische Hinweise
- Das Widget ist ein Carbon-Fields-`html`-Feld, dessen Inhalt über einen **Callback** entsteht.
  `Html_Field::to_json()` ruft ihn erst beim Rendern des Containers auf, dort steht der
  Beitragskontext zur Verfügung — der aktuelle Dateizustand wird also weiterhin serverseitig
  ausgegeben und hängt nicht davon ab, dass JavaScript ihn nachträgt.
- `odw-file-upload.js` nutzt jetzt durchgehend **delegierte Handler** an `document` statt direkter
  Bindung beim Laden. Carbon Fields hängt seine Felder erst nach `DOMContentLoaded` ein und kann
  sie später neu rendern; direkt gebundene Handler hätten ins Leere gegriffen.
- `save_file_attachment()` ist unverändert und bricht weiterhin ab, wenn die Nonce fehlt — sollte
  das Widget einmal nicht rendern, geht eine bestehende Verknüpfung nicht verloren.

---

## [2.35.7] — 2026-08-06

Zweite Runde aus dem Usability-Test: Gliederung und Verständlichkeit.

### 🔧 Changed
- **Tab-Zuschnitt nach inhaltlicher Zusammengehörigkeit.** Schlagworte stehen jetzt in Tab 1 bei
  Thema, CESSDA-Klassifikation und Engagementfeld — alle vier beschreiben, worum es geht, und
  gehörten nie zu Sprache und Datumsangaben. Die Erstveröffentlichung ist nach Tab 4 zur
  Aktualisierungsfrequenz gewandert. Tab 2 heißt entsprechend **„Sprache & Übersetzungen"** und
  trägt jetzt sämtliche Übersetzungs-Repeater (Titel, Beschreibung, Schlagworte), die vorher auf
  zwei Tabs verteilt waren.
- **Zugriffsrechte sind mit „Öffentlich" vorbelegt.** Für ein Open-Data-Plugin ist das der
  Normalfall; die Vorbelegung steht sichtbar im Formular und lässt sich jederzeit ändern. Bewusst
  kein Automatismus aus dem Beitragsstatus — Metadaten sollen nicht im Hintergrund entstehen.
- **Die HVD-Felder sagen jetzt, für wen sie gedacht sind.** High-Value-Datensätze sind eine
  Rechtskategorie der EU-Durchführungsverordnung 2023/138 für öffentliche Stellen. Beschriftung und
  Hilfetext nennen das ausdrücklich, damit Vereine und Verbände die Felder überspringen können,
  ohne zu rätseln. Für Kommunen bleiben sie unverändert nutzbar.

### 🐛 Fixed
- **Das CESSDA-Eingabefeld war viel zu schmal** und schnitt seinen Platzhalter ab. Das Widget wird
  als Geschwister neben das ausgeblendete Carbon-Fields-Feld gesetzt und steht damit außerhalb von
  `.cf-field` — dessen Breitenregeln griffen nicht, und der Browser fiel auf die Standardbreite
  eines Textfelds zurück (`size=20`, rund 250 px).

### 🌍 i18n
- 11 geänderte Zeichenketten in `.pot`/`.po`, `.mo` neu kompiliert (628 Einträge, 0 unübersetzt).
  Ein neues Prüfskript vergleicht alle übersetzbaren Zeichenketten des Quellcodes gegen den
  Katalog — damit fällt eine vergessene Übersetzung künftig sofort auf.

---

## [2.35.6] — 2026-08-06

Automatische Updates laufen wieder.

### 🐛 Fixed
- **„Aktualisierung fehlgeschlagen. Das Aktualisierungspaket ist nicht verfügbar."** Das Update
  wurde im Backend angezeigt, ließ sich aber nicht installieren: Der Updater meldete die neue
  Version, konnte aber keine Download-URL liefern. Ursache war der Bezug über das Release-Asset —
  der läuft über die GitHub-API und scheitert, sobald das unauthentifizierte Kontingent
  (60 Anfragen pro Stunde und IP) erschöpft ist oder das Updater-Plugin Release-Assets nicht
  unterstützt.

### 🔧 Changed
- **Update-Pakete kommen jetzt aus dem Branch `release`.** Die CI erzeugt ihn bei jeder
  Veröffentlichung neu; er enthält exakt den Inhalt des Release-ZIPs, also das installationsfertige
  Plugin samt `vendor/`. Der Updater lädt damit ein schlichtes Quellarchiv statt über die API —
  kein Token, kein Rate-Limit, unabhängig vom eingesetzten Updater-Plugin. Die Kopfzeilen lauten
  entsprechend `GitHub Branch: release` und `Release Asset: false`.
- `main` bleibt unverändert frei von `vendor/`: Der Branch wird ausschließlich aus dem gebauten
  ZIP erzeugt und bei jedem Release force-gepusht, wächst also nicht mit.

### ℹ️ Einmaliger Zwischenschritt
Der Updater liest die Kopfzeilen der **installierten** Version. Wer von 2.35.5 oder früher kommt,
muss das Release-ZIP daher noch **einmal von Hand** über „Plugins → Installieren → Plugin hochladen"
einspielen. Ab 2.35.6 laufen Updates automatisch. Datensätze bleiben erhalten — sie liegen in der
Datenbank, nicht in den Plugin-Dateien.

---

## [2.35.5] — 2026-08-06

Erste Runde aus einem Usability-Test des Formulars.

### 🐛 Fixed
- **Der Qualitätswert startete unerklärt bei 2 %.** `set_modified_date()` schreibt `_odw_modified`
  bei jedem Speichern; die MQA-Metrik „Änderungsdatum" (5 Punkte von 295 bewertbaren) war damit
  schon nach dem ersten Speichern eines leeren Entwurfs erfüllt. Der Bericht weist solche Metriken
  jetzt als „(automatisch)" aus und erklärt den Startwert in einem Satz — statt eine Zahl zu
  zeigen, die wie ein Rechenfehler wirkt.
- **Das Engagementfeld war falsch beschriftet.** Die Frage lautete „In welchem Engagementfeld ist
  die *Organisation* aktiv?", der Wert landet aber als `dct:subject` am *Datensatz*. Wer die
  Beschriftung wörtlich nahm, erzeugte eine inhaltlich falsche Sacherschließung. Neu: „Welchem
  Engagementfeld ist dieser Datensatz zuzuordnen?"
- **Zusatz-Distributionen fehlte ein Feld.** `dcatap:availability` gab es nur für die primäre
  Distribution, obwohl `odw_build_distribution_node()` es für jede Distribution auswertet.

### 🔧 Changed
- **Das Änderungsdatum hat kein Eingabefeld mehr.** Es wurde bei jedem Speichern überschrieben,
  eine Eingabe ging also kommentarlos verloren; ein gesperrtes Feld samt „Datum wählen"-Button
  vorzuhalten war Ballast. Der Wert bleibt in der Übersichtsspalte, im Qualitätsbericht und in der
  JSON-LD-Vorschau sichtbar. `ODW_Quality` und der Shortcode lesen ihn jetzt direkt aus der
  Post-Meta statt über Carbon Fields. Im Feld-Katalog markiert ein neues Flag `auto` solche
  Eigenschaften: Sie bleiben in `docs/FELD-REFERENZ.md` dokumentiert — sie stehen ja weiterhin im
  JSON-LD —, sind aber von der Formular-Abdeckungsprüfung ausgenommen.
- **Keine Scheingenauigkeit mehr im Qualitätsbericht.** Die Bewertung ist pro Metrik binär; „0 / 30"
  suggerierte eine Skala, die es nicht gibt. Offene Metriken zeigen jetzt „+30 möglich".
- **Vier Beschriftungen verständlicher:** „gültig" → Zeitraum, den die Daten abdecken (`dct:temporal`);
  „Wo finde ich mehr Informationen…" → „Wo finde ich die Projektseite zu diesen Daten?";
  „Ansprechperson" → „An wen kann ich mich … wenden?" (erlaubt auch eine Stelle oder Organisation,
  wie `dcat:contactPoint` es vorsieht); „Wie dauerhaft ist diese Datei verfügbar?" → „Wie verlässlich
  bleibt diese Datei abrufbar?".
- „Distribution — erweitert" heißt jetzt „Primäre Distribution — weitere Angaben" und beantwortet
  damit, ob die Sektion alle Distributionen betrifft (nein).
- Im Panel „Mehr erfahren" steht die alltagssprachliche Erklärung vor der DCAT-AP-Definition.
- Der Ausklapp-Button ist so breit wie seine Beschriftung statt über die volle Spalte, und die
  aufgeklappte Sektion bekommt eine Faltlinie — vorher war nicht erkennbar, worauf er sich bezieht.

### 🌍 i18n
- 8 geänderte, 3 entfallene und 6 neue Zeichenketten in `.pot`/`.po`; `.mo` neu kompiliert
  (628 Einträge, 0 unübersetzt).

---

## [2.35.4] — 2026-08-06

Automatisierte SHACL-Validierung in der CI — Beitrag von [@jstet](https://github.com/jstet) (CorrelAid).

### ✨ Added
- **SHACL-Validierung bei jedem Commit.** Ein neuer CI-Job prüft die erzeugten JSON-LD- und
  Turtle-Dokumente gegen die offiziellen SHACL-Shapes von DCAT-AP 3.0 (EU) und DCAT-AP.de
  (GovData). Validiert werden ein minimaler und ein maximaler Datensatz sowie der vollständige
  Katalog — die Fixtures entstehen dabei aus dem echten Produktivcode
  (`odw_build_dataset_jsonld()`, `ODW_Rest_API::build_catalog_document()`, `ODW_Rdf::to_turtle()`),
  nicht aus nachgebauten Beispieldokumenten. Bisher war die Konformität nur von Hand prüfbar und
  Abweichungen fielen erst nach der Einreichung bei einem Portal auf.
- Die fehlenden Vokabulare und Shapes liegen jetzt unter `config/shacl/` (DCAT-AP.de-Vokabulare,
  Deprecation-Shapes, deutsche Ergänzungen, FOAF 0.1, vCard). Sie sind reine Referenzdaten und
  werden von `bin/build-release.sh` nicht ins Release-ZIP übernommen.

### 🐛 Fixed
- **`dct:publisher` und `dcat:contactPoint` waren für strenge Validatoren nicht konform.**
  Ausgegeben wurde nur die Unterklasse (`foaf:Organization` bzw. `vcard:Organization`). Validatoren
  ohne OWL-Reasoning — und das sind die meisten europäischen Portale — können daraus die
  Oberklasse nicht herleiten und beanstandeten die Ausgabe. Beide Typen werden nun explizit
  ausgegeben (`foaf:Organization` + `foaf:Agent`, `vcard:Organization` + `vcard:Kind`). Das ist
  gültiges JSON-LD und eine Obermenge der bisherigen Ausgabe — bestehende Harvester sind nicht
  betroffen.

### 🔧 Changed
- `ODW_Rest_API::build_catalog_document()` ist `public static`, damit die Fixture-Erzeugung den
  echten Katalog-Builder aufruft statt seine Logik zu kopieren.
- `tests/shacl/` ist aus PHPUnit und PHPCS ausgenommen: Der Fixture-Generator ist ein
  CLI-Skript, kein Test.

### 🧹 Aufgeräumt
- Der Fixture-Generator legt sein Ausgabeverzeichnis mit `is_dir()`-Guard an (keine
  `mkdir()`-Warnung bei wiederholten Läufen) und bricht ab, wenn Verzeichnis oder Datei nicht
  geschrieben werden können — vorher meldete er „✓" auch für Dateien, die nie entstanden.
- `validate.mjs` nennt bei fehlendem Fixture den Grund und den nötigen Befehl, statt „(skipping)"
  zu melden und trotzdem abzubrechen.

---

## [2.35.3] — 2026-08-03

Carbon-Fields-Warnung „Your site seems to be slightly misconfigured" beseitigt.

### 🐛 Fixed
- **Ungültiges Attribut am Änderungsdatum-Feld.** Das Feld „Zuletzt aktualisiert (automatisch)"
  setzte seit v2.28.0 ein `readOnly`-Attribut. Carbon Fields erlaubt bei Datumsfeldern jedoch nur
  `placeholder`, `autocomplete` und `data-*` — das Attribut wurde deshalb verworfen und löste im
  Backend die Sammelmeldung „Your site seems to be slightly misconfigured … Only the following
  attributes are allowed: placeholder, autocomplete, data-*" aus. Die Sperre ist jetzt über ein
  `data-odw-readonly`-Attribut umgesetzt: Das Eingabefeld ist schreibgeschützt, aus der Tab-Reihenfolge
  genommen und der Datepicker lässt sich nicht mehr öffnen. Das Datum wird beim Speichern ohnehin
  automatisch gesetzt, eine manuelle Eingabe wurde bislang kommentarlos überschrieben.

### 🧪 Tests
- Neuer Regressionstest prüft **alle** `set_attribute()`-Aufrufe der Formulardefinition gegen die
  Attribut-Allowlists der jeweiligen Carbon-Fields-Feldklasse. Ein unzulässiges Attribut wirft in
  Produktion (ohne `WP_DEBUG`) keine Exception, sondern wird still verworfen — ohne diesen Test
  fällt so etwas erst im Backend einer echten Installation auf.

---

## [2.35.2] — 2026-08-03

Verständliche Hilfe, wenn die Installation unvollständig ist.

### 🐛 Fixed
- **Die Fehlermeldung bei fehlenden Abhängigkeiten war für die Zielgruppe unbrauchbar.** Sie riet zu
  „composer install im Plugin-Verzeichnis ausführen" — eine Anweisung, die Redakteur:innen ohne
  Kommandozeilenzugang nicht befolgen können. Die Meldung nennt jetzt zuerst den Weg über die
  Oberfläche: fertiges Plugin-Paket (ZIP) von der Releases-Seite herunterladen und über
  „Plugins → Installieren → Plugin hochladen" einspielen, mit dem ausdrücklichen Hinweis, dass die
  eigenen Datensätze dabei erhalten bleiben. Der Composer-Hinweis bleibt als Zusatz für
  Entwickler:innen erhalten.

### ℹ️ Hintergrund
Seit v2.34.0 enthält das **Quellcode-Archiv** des Repositorys bewusst keine Abhängigkeiten mehr —
vollständig ist nur das automatisch gebaute **Release-ZIP**. Wer das Plugin aus dem Quellcode-Archiv
installiert (oder aus einer Version vor 2.34.0 über den GitHub-Updater aktualisiert hat, dessen
Header noch auf das Quellarchiv verwies), erhält daher eine unvollständige Installation. Einmalig das
Release-ZIP einspielen behebt das dauerhaft: Ab dann verweist der Header auf das Release-Asset, und
weitere Updates ziehen automatisch das vollständige Paket.

### 🌍 i18n
- Neue Meldungstexte ins en_US übersetzt, veralteter Eintrag entfernt (625 Einträge, lückenlos).

## [2.35.1] — 2026-07-31

Fehler aus einem Robustheitstest der Turtle-Ausgabe.

### 🐛 Fixed
- **Ein leerer Katalog erzeugte ungültiges Turtle.** Ohne veröffentlichte Datensätze trägt das
  Dokument `dcat:dataset => []`; der Serializer gab daraus `dcat:dataset .` aus — ein Prädikat
  ohne Objekt, das **jeder RDF-Parser zurückweist**. Betroffen war damit genau der Fall einer
  frischen Installation (oder wenn alle Datensätze noch Entwürfe sind): Ein Harvester hätte beim
  ersten Abruf einen Syntaxfehler erhalten. Leere Werte lassen das Prädikat jetzt vollständig
  entfallen. Durch einen Regressionstest abgesichert (ohne den Fix nachweislich rot).

### ✅ Geprüft (ohne Befund)
Robustheitstest der Turtle-Ausgabe mit gezielt unangenehmen Eingaben: Anführungszeichen,
Backslashes, Zeilenumbrüche, Tabs, Steuerzeichen, Unicode/Emoji, ein **Turtle-Injektions-Versuch**,
IRIs mit Sonderzeichen, 50-KB-Strings und verschachtelte Knoten — alle Ausgaben sind von `rdflib`
parsebar, die Injektion wird sauber escaped (keine eingeschleusten Triples).
Zusätzlich: JSON-LD- und Turtle-Ausgabe desselben Katalogs sind **graph-isomorph** (68 Triples),
der Serializer ist damit unabhängig gegen den JSON-LD-Pfad verifiziert. Reguläre Ausgabe
byte-identisch, weiterhin DCAT-AP.de-konform. 198 Tests grün.

## [2.35.0] — 2026-07-31

Content Negotiation vervollständigt.

### ✨ Added
- **Turtle für alle Endpunkte.** `/datasets/<id>` und `/delta` liefern jetzt ebenfalls
  `text/turtle` — bisher konnte das nur der Katalog. Damit muss ein RDF-Harvester nicht
  je Route die Serialisierung wechseln.
- **Auswertung des `Accept`-Headers.** Fehlt `?format=`, entscheidet der Header über die
  Serialisierung: `text/turtle` (inkl. der Alt-Bezeichnung `application/x-turtle`),
  `application/ld+json` und `application/json` werden erkannt. **q-Werte** nach RFC 9110
  werden berücksichtigt, bei Gleichstand gilt die Reihenfolge des Clients, `q=0` schließt
  einen Typ aus. Wildcards (`*/*`), unbekannte Typen und ein leerer Header fallen auf
  JSON-LD zurück — der DCAT-AP-Standardfall.
- **`Vary: Accept`** auf allen Antworten, damit zwischengeschaltete Caches nicht ein
  Turtle-Dokument an einen JSON-LD-Client ausliefern.

### 💡 Verhalten
Ein explizites **`?format=` hat immer Vorrang** vor dem Header — die URL bleibt damit
eindeutig und kopierbar. Der Alias `ttl` wird weiterhin auf `turtle` normalisiert.

### 🔧 Technisch
- Gemeinsame Helfer `resolve_format()`, `negotiate_accept()`, `serialize_document()` und
  `document_response()`; alle drei Endpunkte nutzen jetzt denselben Pfad für Aushandlung,
  Serialisierung und Turtle-Caching (bisher nur im Katalog).
- Die Format-Argumente der Routen sind vereinheitlicht; ein leerer Default bedeutet
  „nicht angegeben" und aktiviert die Header-Aushandlung.

### ✅ Tests
- Sieben neue Tests: Media-Type-Zuordnung, q-Werte, Gleichstand, `q=0`, Fallbacks
  (inkl. browsertypischem Header), Vorrang von `?format=` sowie Turtle-Ausgabe eines
  Einzeldatensatzes samt `Vary: Accept`.
- `WP_REST_Request`-Stub um `get_header()`/`set_header()` ergänzt. Gesamt: **197**.
- Gegenprobe: Ein Einzeldatensatz als Turtle ergibt valides RDF (per `rdflib` geprüft).

---

## [2.34.2] — 2026-07-31

Härtung der öffentlichen Endpoints (Defense-in-Depth).

> **Einordnung:** Das Sicherheits-Review zu v2.33.1 fand **keine ausnutzbare Lücke**. Die beiden
> folgenden Punkte waren dort als nachrangige „Residuals" vermerkt und werden jetzt geschlossen —
> es besteht kein Handlungsdruck für bestehende Installationen.

### 🔐 Hardening
- **`/delta`-Cache-Schlüssel wird normalisiert.** Er entstand bisher aus der **rohen** `since`-Eingabe,
  sodass jede Schreibweise desselben Zeitpunkts (`2024-01-01`, `…T00:00:00Z`, `…+00:00`) einen eigenen
  Transient anlegte. Da der Endpoint unauthentifiziert ist, ließ sich der Schlüsselraum so unnötig
  aufblähen. Jetzt bildet der bereits geparste, kanonische UTC-Zeitstempel den Schlüssel — der
  Schlüsselraum ist damit auf tatsächlich verschiedene Zeitpunkte begrenzt.
- **Turtle-Antworten werden gecacht.** Der Katalog-Transient sparte nur die Datenbankarbeit; der
  gesamte Katalog wurde bei **jedem** Aufruf neu nach Turtle serialisiert — auf einem
  unauthentifizierten Endpoint unnötige CPU-Last pro Anfrage. Das serialisierte Dokument liegt jetzt
  in einem eigenen Transient (`…_ttl`), der von der bestehenden Cache-Invalidierung miterfasst wird.

### ✅ Tests
- Zwei Regressionstests (beide gegengeprüft: ohne die jeweilige Härtung rot) — Schreibweisen-Kollaps
  des Delta-Schlüssels und Wiederverwendung des gecachten Turtle-Dokuments.
- `WP_REST_Response`-Stub um `get_data()`, `get_headers()` und `get_status()` ergänzt, damit Tests
  dieselben Zugriffsmethoden nutzen wie der Produktivcode. Gesamt: 190.

### 🚫 Bewusst nicht umgesetzt
Rate-Limiting bleibt außen vor — das gehört auf Host-/WAF-Ebene, nicht in ein WordPress-Plugin.
Die Turtle-Ausgabe ist byte-identisch geblieben und weiterhin DCAT-AP.de-konform.

---

## [2.34.1] — 2026-07-31

Befunde aus einem Gesamt-Gegencheck (Funktion, UX, Code, i18n).

### 🐛 Fixed
- **`wp open-data-wizard docs` schlug auf jeder ZIP-Installation fehl.** Der Befehl schreibt nach
  `docs/FELD-REFERENZ.md`, doch das Release-ZIP liefert (korrekterweise) kein `docs/`-Verzeichnis aus —
  `file_put_contents()` scheiterte still und meldete nur „konnte nicht geschrieben werden".
  `ODW_Field_Reference::write()` legt das Zielverzeichnis jetzt an (WordPress-unabhängig, damit der
  Standalone-Generator weiter funktioniert). Durch einen Regressionstest abgesichert.
- **Eine fehlende englische Übersetzung** ergänzt (`Offene Daten, bereitgestellt von %s.` — der in
  v2.33.1 eingeführte Katalog-Beschreibungs-Fallback). `en_US` ist wieder lückenlos (620 Einträge,
  per Tokenizer gegen den Quelltext geprüft).

### 📄 Docs
- **CLAUDE.md aktualisiert:** stand noch auf v2.5.1 und „94 Tests". Projektstruktur um `class-rdf.php`,
  `class-field-reference.php`, `bin/`, `docs/`, `samples/` und die neuen `config/`-Dateien ergänzt;
  Klassenübersicht um `ODW_Rdf` und `ODW_Field_Reference`; der Roadmap-Abschnitt führte Phase D und E
  fälschlich noch als offen.
- **README:** Version-Badge aktualisiert; die Roadmap weist den Harvest-Endpoint samt Turtle als
  erledigt aus, offen bleibt die vollständige Content Negotiation (`Accept`-Header, `/datasets/<id>`).

### ✅ Geprüft (ohne Befund)
PHP-/JS-Syntax aller Dateien, Datei-Integrität (alle `require`- und Asset-Pfade existieren),
Sprungziele und Pflichtfeld-Marker der neuen UX gegen die realen Feldnamen, Turtle-Ausgabe
(valides RDF, GovData DCAT-AP.de `Conforms: True`), keine TODO/FIXME-Marker. 188 Tests grün.

---

## [2.34.0] — 2026-07-31

Reproduzierbare Abhängigkeiten: `vendor/` wird nicht mehr versioniert.

### 🧹 Changed
- **`vendor/` ist nicht mehr im Repository** (4.487 Dateien entfernt). Die Abhängigkeiten werden
  ausschließlich aus `composer.lock` erzeugt — lokal und in der CI per `composer install`, für das
  Release-ZIP per `bin/build-release.sh` (`--no-dev`).
  *Hintergrund:* Das eingecheckte `vendor/` hing dem Lock hinterher (Dependabot aktualisierte den
  Lock auf WPCS 3.4.1, das committete `vendor/` blieb bei 3.3.0). Dadurch prüfte die CI strenger
  als die lokale Umgebung — mit dieser Umstellung kann die Klasse von Abweichung nicht mehr
  entstehen. Die **Laufzeit**-Abhängigkeit (Carbon Fields) war nie betroffen.
- **Updates laufen über das Release-ZIP** (`Release Asset: true`). Der GitHub-Updater zieht damit
  das gebaute, schlanke Plugin-Paket statt eines Quell-Tarballs — der zuvor auch PHPUnit, PHPStan
  und WPCS auf Produktionsseiten gebracht hätte.

### 📄 Docs
- README und CLAUDE.md weisen `composer install` als Pflichtschritt aus und erklären, dass ein
  vergessenes `composer install` die typische Ursache abweichender PHPCS-/PHPStan-Ergebnisse ist.

> **Hinweis für Entwickler:innen:** Nach dem Update einmalig `composer install` ausführen.
> Am Auslieferungsweg für Endnutzer:innen ändert sich nichts — das Release-ZIP wird weiterhin
> automatisch gebaut und veröffentlicht.

---

## [2.33.1] — 2026-07-31

DCAT-AP-Konformitätsfixes aus der SHACL-Validierung des Harvest-Endpoints.

### 🐛 Fixed
- **`dcat:byteSize` als typisiertes Literal:** Die Dateigröße wurde als blanke Zahl ausgegeben
  (serialisiert als `xsd:integer`) und verletzte damit die DCAT-AP-Vorgabe
  `xsd:nonNegativeInteger`. Sie wird nun als `"20480"^^xsd:nonNegativeInteger` emittiert.
- **Katalog hat immer eine `dct:description`:** Die EU-`CatalogShape` verlangt mindestens eine
  Beschreibung. Ist in den Einstellungen keine gesetzt, wird jetzt auf den WordPress-Untertitel
  und schließlich auf einen generischen Satz zurückgefallen.

### 🔐 Security (Härtung)
- **`dct:format` wird jetzt ebenfalls über den gemeinsamen `@id`-Sanitizer geführt.** Es war der
  einzige `@id`-Wert im JSON-LD-Builder, der `odw_sanitize_jsonld_id()` übersprang; da
  `get_format_eu_uri()` unbekannte Formate unverändert zurückgibt, konnte ein per Batch-Import
  eingeschleustes Schema (`javascript:`/`data:`) theoretisch bis in die öffentliche Ausgabe
  gelangen. Ein umfassendes Sicherheits-Review (6 Prüfdimensionen mit adversarialer Verifikation)
  fand **keine ausnutzbare Lücke** — dies ist reine Defense-in-Depth/Konsistenz.

### ✅ Validierung
- Die mit dem echten Plugin-Code erzeugte Turtle-Ausgabe wurde per **pySHACL** gegen die
  gebündelten offiziellen Shapes geprüft: **GovData DCAT-AP.de → `Conforms: True`**;
  **EU DCAT-AP 3.0 → `Conforms: True`** (mit geladenen Vokabularen, wie in produktiven Harvestern).
  Die im Standalone-Lauf verbleibenden `sh:class`-Meldungen betreffen ausschließlich nicht
  mitgeladene EU-Vokabulare, nicht die Ausgabe des Plugins.
- Neue Regressionstests für das `byteSize`-Literal und die `dct:format`-Sanitisierung. Gesamt: 187.

---

## [2.33.0] — 2026-07-31

Bereitstellung für Metadaten-Harvesting durch externe Open-Data-Portale.

### ✨ Added
- **Voll-Katalog-Endpoint (`?full=1`):** Der Catalog-Endpoint liefert auf Wunsch **alle
  veröffentlichten Datensätze in einem Abruf** (ohne Paginierung) als ein `dcat:Catalog`
  → `dcat:Dataset` → `dcat:Distribution` — der Bereitstellungspunkt, den RDF-Harvester
  erwarten. Der Katalog trägt ein stabiles `@id` und `foaf:homepage`.
- **Turtle-Serialisierung (`?format=turtle`):** Neuer dependency-freier JSON-LD→Turtle-Serializer
  (`includes/class-rdf.php`) liefert denselben Graphen als `text/turtle` (roh, via
  `rest_pre_serve_request`). `json`/`jsonld` bleiben verfügbar. Format-Alias `ttl` wird akzeptiert.
- **Admin-Harvest-Box:** Unter _Datensätze → Einstellungen → Harvesting_ werden die
  kopierfertigen Katalog-URLs (Turtle + JSON-LD) samt Onboarding-Hinweis angezeigt.
- **README-Abschnitt „Harvesting durch externe Open-Data-Portale"** mit den Harvest-URLs.

### ✅ Tests
- Neuer Turtle-Serializer durch `tests/test-rdf.php` abgesichert (Prefixe, benannte Subjekte,
  Blank Nodes, Sprach-/Typ-Literale, IRI-Referenzen, Escaping); zusätzlich per `rdflib` als valides
  RDF gegengeprüft. Gesamt: 184.

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
