# Videotutorials — Konzept

> **Entwurf 0.4 zur Abstimmung.** Bezieht sich auf Plugin-Version 2.47.5. Menü- und Feldnamen sind
> aus dem Code übernommen; ändern sie sich, ändert sich das Drehbuch mit.

## 1 · Ziel, Zielgruppe, Entscheidungen

Eine Reihe kurzer Einzelvideos (2–5 Minuten), die den Open Data Wizard von der Installation bis zur
Anbindung an ein Open-Data-Portal erklärt. Eine Folge beantwortet eine Frage; jede ist für sich
verständlich und einzeln verlinkbar.

**Zielgruppe:** Menschen, die WordPress-Plugins installieren und bedienen können oder eine
Vorstellung davon haben — Redaktionen in Vereinen, Stiftungen, Forschungseinrichtungen und
Kommunen. Keine reinen IT-Fachleute. Für Datenaffine gibt es in einzelnen Folgen kurze, markierte
Einschübe „Blick unter die Haube", die man überspringen kann.

**Entschieden:**

| Punkt | Entscheidung |
|---|---|
| Format | Reihe kurzer Einzelvideos, Folge 0 als eigenständiger Quick-Start |
| Stimme | Damian (ElevenLabs) |
| Anrede | „Sie" — wie in der Plugin-Oberfläche |
| Beispieldaten | werden im Quick-Start geladen (Szene Q5) |
| Fehlermeldung im Quick-Start | bleibt als Lernmoment (Szene Q8) |
| Folge 10 | wird als Video produziert |
| Veröffentlichung | YouTube, auf dem Kanal des Kunden (Abschnitt 7) |
| Lizenz der Videos | CC BY, Namensnennung: das Projekt („Open Data Wizard") |
| Fremde Oberflächen | werden als Folie nachgebildet (GitHub, EU-Validator) |
| Gestaltung | Projektdesign; kein Logo und kein Name des Kunden |
| Produktion | automatisiert, Produktionscode unter `video/` in diesem Repository (Abschnitt 6) |
| Ton-Dateien | nur lokal erzeugt; geliefert wird das fertige Video |
| Stimme | Der ElevenLabs-Tarif deckt die Nutzung auf dem Kanal des Kunden und unter CC BY ab |

## 2 · Die Reihe im Überblick

| Nr. | Titel | Länge | Bild | Lernziel |
|---|---|---|---|---|
| 0 | **Quick-Start:** In fünf Minuten zum ersten offenen Datensatz | ~5:00 | Screencast + Folien | Installieren, einen Datensatz veröffentlichen, Harvest-URL finden |
| 1 | Warum Metadaten? DCAT-AP und Harvesting | ~3:00 | nur Folien | Das Prinzip verstehen: Daten bleiben, Beschreibung wandert |
| 2 | Installation, Updates, Einstellungen, Beispieldaten | ~4:00 | Screencast + Folien | Sauber installieren und das Plugin einrichten |
| 3 | Das Formular I: Grundlegendes und Sprache (Reiter 1–2) | ~3:45 | Screencast | Einen Datensatz inhaltlich beschreiben |
| 4 | Das Formular II: Datenbereitstellung (Reiter 3) | ~4:00 | Screencast + Folie | Datei, Format, Lizenz, mehrere Distributionen |
| 5 | Das Formular III: Erweiterte Angaben (Reiter 4) | ~4:30 | Screencast | Optionale Angaben gezielt nutzen |
| 6 | Prüfen, Veröffentlichen, Qualität | ~3:30 | Screencast + Folien | Validierung und Qualitätsanzeige lesen |
| 7 | Viele Datensätze auf einmal: Batch-Import | ~3:30 | Screencast + Folien | CSV/JSON vorbereiten und importieren |
| 8 | Auf der eigenen Website: Karte und Datenkatalog | ~4:10 | Screencast | Datensätze öffentlich zeigen |
| 9 | Anbindung an Open-Data-Portale | ~3:50 | Screencast + Folien | Harvest-URLs, Formate, Konformität prüfen |
| 10 | Für Administrator:innen: WP-CLI, Rechte, Deinstallation | ~2:10 | Terminal + Screencast | Wartung und Verwaltung |

Gesamt rund 41 Minuten. Folge 0 steht für sich; die Folgen 1–10 vertiefen.

## 3 · Gestaltungsprinzipien

- **Screencast, wo es etwas zu klicken gibt; Folie, wo nichts zu sehen ist.** Folien erklären
  Begriffe und Abläufe außerhalb der Oberfläche: Daten und Metadaten, Abholen durch Portale,
  Datensatz und Distribution, Qualitätsstufen.
- **Ein Gedanke pro Szene.** Zooms und Markierungen lenken auf das Feld, über das gerade gesprochen
  wird; der Rest der Oberfläche tritt zurück.
- **Fragen statt Fachbegriffe** — wie im Formular. Der DCAT-AP-Begriff wird genannt, aber nicht
  vorausgesetzt.
- **Blick unter die Haube** (10–30 s, sichtbar gekennzeichnet, eigene Kapitelmarke): JSON-LD,
  Turtle, Endpunkte — für Datenaffine.
- **Nur freie Oberflächen im Bild.** Gezeigt werden WordPress (GPL), das Plugin und die eigene
  Übungswebsite. Fremde Seiten — GitHub, der EU-Validator, Portale — werden als Folie nachgebildet,
  damit das ganze Video unter CC BY stehen kann.
- **Durchgehend erfundene Daten.** Die mitgelieferten Beispieldaten (Musterstadt, Musterkreis) und
  ein eigener Übungsdatensatz: *Förderverein Musterstadt e. V. — Mitgliederentwicklung 2015–2025*,
  mit einer kleinen CSV-Datei aus `video/material/`. Keine echten Personen- oder Organisationsdaten
  im Bild.
- **Barrierefreiheit:** Untertitel (WebVTT) und Transkript zu jeder Folge; Folien mit
  ausreichendem Kontrast, Information nie nur über Farbe. Für öffentliche Stellen relevant (BITV 2.0).
- **Gestaltung:** Farben und Logo des Projekts (`#03FF9A` auf `#101010`, `assets/images/ODW-Logo.svg`);
  `assets/images/ODW-Ablauf.svg` als Grundlage der Ablauf-Folie.
- **Jede Folge** beginnt mit einer Titelfolie (10 s) und endet mit einem Abspann (10 s): Hinweis auf
  die passende nächste Folge, die Plugin-Version, auf der das Video beruht, und die Lizenz (CC BY). In den Tabellen
  unten sind beide nicht eigens aufgeführt.

## 4 · Folge 0 — Quick-Start

**Lernziel:** Nach fünf Minuten ist ein Datensatz veröffentlicht, auf einer Seite sichtbar und
die Harvest-URL bekannt.

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| Q1 | 0:20 | Folie | Titel, Logo | Worum es geht und für wen. |
| Q2 | 0:25 | Folie (animiert) | Formular ausfüllen → DCAT-AP.de erzeugen → Portale holen ab | Die Daten bleiben bei Ihnen; nur ihre Beschreibung wandert. |
| Q3 | 0:20 | Folie (Releases-Seite nachgebildet) | `open-data-wizard-<version>.zip` hervorgehoben, „Code"-Button durchgestrichen | Nicht der grüne „Code"-Button — dort fehlen Bibliotheken. |
| Q4 | 0:30 | Screencast WP | Plugins → Installieren → Plugin hochladen → Datei auswählen → Jetzt installieren → Plugin aktivieren | Danach erscheint links der Menüpunkt „Datenkatalog". |
| Q5 | 0:15 | Screencast | Datenkatalog → Einstieg → „Beispieldaten laden" → Alle Datensätze | 42 erfundene Beispiele zum Ausprobieren — gekennzeichnet, nie an Portale ausgeliefert. |
| Q6 | 0:15 | Screencast | Datenkatalog → Neuen Datensatz anlegen → Titel | Fünf Reiter; ⓘ zeigt den Fachbegriff, „Mehr erfahren" die Erklärung. |
| Q7 | 0:30 | Screencast | Reiter 1: „Wer gibt diese Daten heraus?"\* (Förderverein Musterstadt e. V.), „Worum geht es in diesem Datensatz?"\*, Themen, Schlagworte | Sternchen = nötig zum Veröffentlichen. Als Entwurf speichern geht immer. |
| Q8 | 0:25 | Screencast | „Veröffentlichen" → Meldung nennt Reiter und fehlende Felder → „Zum Feld springen" | Unvollständiges wird nicht veröffentlicht — das Plugin zeigt, wo etwas fehlt. |
| Q9 | 0:30 | Screencast | Reiter 3: „Datei auswählen / hochladen" → Mediathek → CSV; Lizenz „CC-BY 4.0 (Namensnennung)"; Namensnennungstext | Format und Dateigröße ermittelt das Plugin beim Speichern. |
| Q10 | 0:20 | Screencast | Reiter „5 — Vorschau" → Veröffentlichen → Box „Qualitätsprüfung (MQA)" | Prozent und Stufe zeigen, wie vollständig die Beschreibung ist (→ Folge 6). |
| Q11 | 0:30 | Screencast | Seiten → Neue Seite → „+" → Block „Datensatz-Karte" → eigenen Datensatz in der Seitenleiste wählen → Veröffentlichen → Seite ansehen | Besucher finden Download, Lizenz und Metadaten an einer Stelle. |
| Q12 | 0:20 | Screencast + Folie | Datenkatalog → Einstellungen → Harvesting → URL kopieren; Folie: Portal trägt die URL ein | Einmal beim Portal melden — es holt Änderungen von selbst ab. Die Beispieldaten sind dort nicht enthalten, nur Ihr eigener Datensatz. |
| Q13 | 0:15 | Folie | Abspann: Hinweis auf die Folgen 1–10, Version | — |

Sprechtext bei rund 130 Wörtern pro Minute: etwa 650 Wörter.

## 5 · Folgen 1–10 — Szenen

### Folge 1 · Warum Metadaten? DCAT-AP und Harvesting *(nur Folien)*

| # | Dauer | Bild | Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 1.1 | 0:30 | Folie | Buch und Karteikarte | Die Daten sind das Buch, die Metadaten die Karteikarte: wer, was, wo, unter welchen Bedingungen. |
| 1.2 | 0:30 | Folie | DCAT-AP, DCAT-AP.de | Eine gemeinsame Sprache für Beschreibungen, damit Portale sie ohne Nacharbeit lesen. DCAT-AP.de ergänzt deutsche Besonderheiten. |
| 1.3 | 0:30 | Folie (Diagramm) | Katalog → Datensatz → Distribution | Ein Katalog enthält Datensätze; ein Datensatz kann in mehreren Dateien oder Formaten vorliegen. |
| 1.4 | 0:40 | Folie (animiert) | Website mit Harvest-URL ← GovData oder Landesportal ← data.europa.eu | Pull-Harvesting: Portale holen ab, in einer Kette bis zum europäischen Portal. Die Daten bleiben bei Ihnen; eine Quelle versorgt viele Portale. |
| 1.5 | 0:30 | Folie | Was das Plugin nicht ist | Kein Portal, kein Datenmanagement, kein Ersatz für fachliche Sorgfalt — es prüft, *ob* eine Lizenz angegeben ist, nicht *welche* passt. |

### Folge 2 · Installation, Updates, Einstellungen, Beispieldaten

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 2.1 | 0:25 | Folie | Release-ZIP und Quellcode-Archiv nebeneinander | Nur das ZIP unter *Releases* enthält die nötigen Bibliotheken. |
| 2.2 | 0:20 | Screencast | Hinweis „Open Data Wizard: Installation unvollständig" | So sieht es aus, wenn das falsche Archiv installiert wurde. Abhilfe: das Release-ZIP hochladen. |
| 2.3 | 0:25 | Screencast | Plugins → Installieren → Plugin hochladen → Release-ZIP → Plugin aktivieren | Der Normalfall in vier Klicks. |
| 2.4 | 0:20 | Folie | Automatische Updates | Neue Versionen erscheinen im Backend. Ihre Datensätze liegen in der Datenbank und bleiben bei Updates erhalten. |
| 2.5 | 0:50 | Screencast | Datenkatalog → Einstellungen: *Katalog* (Katalog-Titel, Katalog-Beschreibung) · *Standardwerte für neue Datensätze* (Herausgebende Organisation, Standard-Lizenz, Standard-Sprache) · *REST API* (Cache-Laufzeit) · *Qualitätsprüfung (MQA)* (URL-Erreichbarkeit prüfen) · *Deinstallation* (Daten löschen) → Änderungen speichern | Standardwerte sparen Tipparbeit bei jedem neuen Datensatz. Der Abschnitt *Harvesting* folgt in Folge 9. |
| 2.6 | 0:40 | Screencast | Datenkatalog → Einstieg → „Beispieldaten laden" → Alle Datensätze („— Beispieldaten" hinter dem Titel) | Beispiele zum Ausprobieren aller Funktionen, deutlich gekennzeichnet. |
| 2.7 | 0:25 | Screencast | Ein Beispiel öffnen → „Als eigenen Datensatz übernehmen" | Wer ein Beispiel als Vorlage nutzt, macht es damit zu einem regulären Datensatz. |
| 2.8 | 0:15 | Screencast | „Beispieldaten entfernen" | Ein Klick, und sie sind endgültig weg. |

### Folge 3 · Das Formular I: Grundlegendes und Sprache

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 3.1 | 0:35 | Screencast | Neuen Datensatz anlegen → fünf Reiter im Überblick; ⓘ (Zeiger darüber); „Mehr erfahren" aufklappen; rotes Sternchen | Zwei Hilfen mit klaren Rollen: ⓘ nennt den Fachbegriff, „Mehr erfahren" erklärt mit Beispiel. |
| 3.2 | 0:25 | Screencast | Titel; „Wer gibt diese Daten heraus?"\* | Die Organisation, die für die Daten verantwortlich ist. |
| 3.3 | 0:30 | Screencast | „Worum geht es in diesem Datensatz?"\* | Für Menschen geschrieben, die den Datensatz nicht kennen: Inhalt, Herkunft, Zeitraum. |
| 3.4 | 0:30 | Screencast | Themen (Mehrfachauswahl); Schlagworte (eines pro Zeile) | Themen sind eine feste, europaweit gleiche Liste; Schlagworte sind freie Suchbegriffe. |
| 3.5 | 0:25 | Screencast | „Weitere Einordnung (optional)" aufklappen: CESSDA-Themenfeld, Engagementfelder | Zusätzliche Ordnungen für Forschung und Zivilgesellschaft. |
| 3.6 | 0:35 | Screencast | Reiter 2: Sprache der Daten; „Titel in weiteren Sprachen" → Eintrag hinzufügen → Englisch + Titel; ebenso Beschreibung und Schlagworte | Die Sprache ist aus den Einstellungen vorbelegt. Portale zeigen die passende Fassung. |
| 3.7 | 0:20 | Blick unter die Haube | JSON-LD-Ausschnitt: Titel mit Sprachkennung `de` und `en` | So steht die Übersetzung in den Metadaten. |

### Folge 4 · Das Formular II: Datenbereitstellung

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 4.1 | 0:30 | Folie | Datensatz und Distribution | Ein Datensatz, mehrere Wege zu den Daten: Dateien, Formate, Zugänge. |
| 4.2 | 0:35 | Screencast | Reiter 3: Zugriffs-URL\* (Link eintragen) — oder „Datei auswählen / hochladen" → Mediathek | Link, wenn die Datei woanders liegt; Upload, wenn sie in WordPress liegen soll. Beim Upload ermittelt das Plugin Format und Größe. |
| 4.3 | 0:20 | Screencast | „In welchem Format ist die Datei?", Dateigröße (Bytes) | Beim Link von Hand angeben. |
| 4.4 | 0:40 | Screencast | Lizenz\* wählen; Lizenzhinweis; „Sonstige" → „Lizenz-URI eingeben oder auswählen"; Namensnennungstext | Die Lizenz entscheidet, ob Daten als Open Data gelten. Bei Namensnennungs-Lizenzen den Text vorgeben, der genannt werden soll. |
| 4.5 | 0:25 | Screencast | „Wie verlässlich bleibt diese Datei abrufbar?", „Wer darf auf diese Daten zugreifen?" | Verlässlichkeit und Zugriffsrechte helfen Nutzenden, die Daten einzuschätzen. |
| 4.6 | 0:45 | Screencast | „Weitere Distributionen" → Eintrag hinzufügen → Zugriffs-URL, Format GeoJSON, Lizenz | Dieselben Daten in einem zweiten Format. Auch hier ist die Lizenz Pflicht. |
| 4.7 | 0:20 | Blick unter die Haube | JSON-LD: `dcat:distribution` mit zwei Einträgen | Zwei Distributionen, ein Datensatz. |

### Folge 5 · Das Formular III: Erweiterte Angaben

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 5.1 | 0:20 | Screencast | Reiter 4 im Überblick: drei offene Gruppen, Profi-Bereich eingeklappt | Alles hier ist freiwillig. Jede Angabe macht die Daten leichter auffindbar. |
| 5.2 | 0:35 | Screencast | *Projektseite & Aktualität:* Projektseite, Erstveröffentlichung, Aktualisierungsfrequenz | Das Änderungsdatum setzt das Plugin bei jedem Speichern selbst. |
| 5.3 | 0:50 | Screencast | *Abdeckung:* Verwaltungsebene, amtliches Gebiet (Vorschläge beim Tippen), räumlicher Bezug in Worten, geografische Region, Zeitraum von–bis | Wo und wann gelten die Daten? |
| 5.4 | 0:25 | Screencast | *Kontakt:* Ansprechperson, E-Mail, Website | An wen sich Nutzende mit Fragen wenden. |
| 5.5 | 0:20 | Screencast | „Erweiterte Angaben (optional)" | „Öffnen Sie gezielt nur die Gruppen, die Sie brauchen." |
| 5.6 | 0:35 | Screencast | *Verantwortlichkeiten & Herkunft:* GovData-Stelle (Vorschläge), Urheber, pflegende Stelle, Rechtsgrundlage, Qualitätssicherung | Vor allem für öffentliche Stellen und den GovData-Verbund. |
| 5.7 | 0:25 | Screencast | *HVD — nur für öffentliche Stellen:* Ja → Kategorie | Hochwertige Datensätze nach EU-Durchführungsverordnung 2023/138. |
| 5.8 | 0:25 | Screencast | *Weitere DCAT-AP-Felder* (Kennung, Typ, Version, Auflösung, Standard, Herkunft) und *Primäre Distribution — weitere Angaben* (Titel, direkter Download-Link, Medientyp, Nutzungsrechte) | Nur kurz gezeigt: nützlich vor allem für Forschungsdaten. |
| 5.9 | 0:15 | Screencast | Speichern → Qualität vorher/nachher (Einblendung) | Mehr Angaben, höhere Qualität. |

### Folge 6 · Prüfen, Veröffentlichen, Qualität

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 6.1 | 0:30 | Screencast | Reiter 5 — Vorschau: Pflichtprüfung (fehlende Angabe markiert), darunter das gespeicherte JSON-LD | Die Prüfung läuft schon beim Ausfüllen. |
| 6.2 | 0:35 | Screencast | Veröffentlichen ohne Lizenz → Datensatz bleibt Entwurf; Meldung nennt Reiter und Feld → „Zum Feld springen" → Feld hervorgehoben | Entwürfe lassen sich immer speichern; veröffentlicht wird nur Vollständiges. |
| 6.3 | 0:20 | Screencast | Lizenz wählen → Veröffentlichen | — |
| 6.4 | 0:30 | Folie | Vier Stufen (Ausgezeichnet ≥ 87 %, Gut 55–86 %, Ausreichend 30–54 %, Mangelhaft < 30 %); fünf Dimensionen (Auffindbarkeit, Zugänglichkeit, Interoperabilität, Wiederverwendbarkeit, Kontext) | Gemessen nach der Methode, die auch das europäische Datenportal verwendet. |
| 6.5 | 0:35 | Screencast | Box „Qualitätsprüfung (MQA)": Prozent, Stufe, Punkte je Kriterium; fehlendes Kriterium ergänzen → Wert steigt | Die Box zeigt, bei welchen Kriterien noch Punkte zu holen sind. |
| 6.6 | 0:20 | Screencast | Alle Datensätze → Spalte „Qualität" sortieren | Den Bestand nach Nachholbedarf ordnen. |
| 6.7 | 0:20 | Einblendung | Formale Konformität (SHACL) | Wird extern geprüft — siehe Folge 9. |

### Folge 7 · Viele Datensätze auf einmal: Batch-Import

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 7.1 | 0:25 | Folie | Wann sich der Import lohnt | Viele Datensätze, oder ein Bestand, der schon in einer Tabelle steht. |
| 7.2 | 0:35 | Folie (aus der CSV erzeugt) | Importdatei als Tabelle; Spaltenköpfe = Feldnamen; Schlagworte mit Zeilenumbruch in einer Zelle | Eine Zeile, ein Datensatz. |
| 7.3 | 0:25 | Folie | Pflichtspalten `title`, `publisher`, `description`, `access_url`, `license`; Auswahl optionaler Spalten; Lizenz-Kürzel | Fünf Spalten genügen, alle weiteren sind möglich. |
| 7.4 | 0:30 | Screencast | Datenkatalog → Batch-Import → „CSV- oder JSON-Datei" wählen → Vorschau | Die Beispieldateien zum Herunterladen stehen auf derselben Seite. |
| 7.5 | 0:35 | Screencast | „Gültige Datensätze" und „Fehler gefunden" (Zeile ohne Lizenz) → Auswahl → „Jetzt importieren" | Fehlerhafte Zeilen werden benannt und nicht importiert. |
| 7.6 | 0:25 | Screencast | Alle Datensätze: neue Einträge als Entwurf → einen öffnen, prüfen, veröffentlichen | Importiert wird immer als Entwurf — Sie entscheiden, was veröffentlicht wird. |
| 7.7 | 0:15 | Screencast | Dieselbe Seite mit JSON-Datei (kurz) | JSON funktioniert genauso. |

Für 7.5 liegt in `video/material/` eine Importdatei mit drei gültigen Zeilen und einer Zeile ohne Lizenz.

### Folge 8 · Auf der eigenen Website: Karte und Datenkatalog

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 8.1 | 0:35 | Screencast | Seite → „+" → „Datensatz-Karte" → Datensatz in der Seitenleiste wählen | Kein Abtippen von Nummern: Sie wählen aus einer Liste. |
| 8.2 | 0:15 | Screencast | Alle Datensätze → Spalte „Shortcode" → `[odw_dataset id="…"]` | Für klassische Editoren und Widgets. |
| 8.3 | 0:25 | Screencast Frontend | Karte: Titel, Thema, Lizenz, Schlagworte, Download, Metadaten (JSON-LD) | Alles Wichtige auf einen Blick. |
| 8.4 | 0:40 | Screencast | Neue Seite → Block „Datenkatalog" → Seitenleiste: Überschrift, Einträge pro Seite, Sortierung, Themen, Auszug, Filter für Besucher, Suchfeld, Beispieldaten auch Besuchern zeigen | Ein Block, viele Einstellmöglichkeiten. |
| 8.5 | 0:50 | Screencast Frontend | Liste; Filter Thema mit Trefferzahlen; Filter-Chip entfernen; Suche; Zeitraum; „Nur Open Data" | Besucher finden, was sie suchen. |
| 8.6 | 0:25 | Screencast Frontend | Titel → Detailansicht → „Zurück zum Katalog" | Die Detailansicht öffnet sich auf derselben Seite. |
| 8.7 | 0:20 | Screencast (Handy-Breite) | Filter eingeklappt, Liste untereinander | Funktioniert auch auf dem Handy. |
| 8.8 | 0:20 | Einblendung | Ein Katalog pro Seite; Beispieldaten sehen sonst nur Angemeldete | Zwei Regeln, die man kennen sollte. |

Aufnahme mit der Option „Beispieldaten auch Besuchern zeigen", sonst wäre die Liste für Besucher fast
leer; 8.8 erklärt das.

### Folge 9 · Anbindung an Open-Data-Portale *(Schwerpunkt für Datenaffine)*

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 9.1 | 0:30 | Screencast | Einstellungen → Harvesting: „DCAT-AP.de · Turtle (empfohlen)", „DCAT-AP.de · JSON-LD" → „Endpunkt prüfen" → „Der Harvest-Endpunkt antwortet." | Diese URL geben Sie dem Portal. |
| 9.2 | 0:35 | Folie | Endpunkte: `/catalog` (seitenweise), `?full=1` (alles in einem Abruf), `&format=turtle`, `/datasets/<id>`, `/delta?since=` | Für jede Art des Abholens ein passender Endpunkt. |
| 9.3 | 0:30 | Blick unter die Haube | Katalog als JSON-LD und als Turtle nebeneinander | Derselbe Inhalt, zwei Schreibweisen. |
| 9.4 | 0:25 | Folie | Content Negotiation: `Accept`-Header, `?format=` hat Vorrang | Harvester können das Format auch über den Header aushandeln. |
| 9.5 | 0:25 | Folie | Nicht ausgeliefert: Entwürfe, private, passwortgeschützte Datensätze, Beispieldaten; Löschmeldungen über `/delta` | Portale sehen genau das, was Besucher Ihrer Website sehen. |
| 9.6 | 0:40 | Folie (Validator nachgebildet) | Datensatz-URL → EU-SHACL-Validator → Ergebnis ohne Verstöße | So belegen Sie die Konformität selbst, bevor Sie sich bei einem Portal melden. |
| 9.7 | 0:25 | Folie | Beim Portal melden: GovData oder Landesportal kontaktieren, Harvest-URL angeben | Das Portal richtet das Abholen ein; danach läuft es von selbst. |

Die Folie in 9.6 nennt die Adresse des Validators im Bild, damit Zuschauende ihn selbst aufrufen
können.

### Folge 10 · Für Administrator:innen

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| 10.1 | 0:25 | Folie | Berechtigung `manage_open_data`: bei Aktivierung an Administrator:innen und Redakteur:innen | Wer Datensätze bearbeiten darf. |
| 10.2 | 0:50 | Terminal | `wp open-data-wizard samples status`, `samples load`, `samples remove --yes`, `quality recalculate`, `cache clear` | Wiederkehrende Aufgaben ohne Klicken, auch für Skripte. |
| 10.3 | 0:35 | Screencast | Einstellungen → Deinstallation → „Daten löschen" mit und ohne Häkchen; Plugin deaktivieren und löschen | Ohne Häkchen bleiben Ihre Datensätze in der Datenbank erhalten. |

## 6 · Automatisierte Produktion

**Grundsatz: Das Drehbuch ist die einzige Quelle.** Je Folge eine Datei, die Sprechtext,
Klickschritte und Einblendungen Szene für Szene enthält. Daraus entstehen Ton, Aufnahme, Schnitt,
Untertitel und YouTube-Beschreibung.

```yaml
# video/folgen/00-quickstart.yml (Skizze)
- id: Q7
  bild: screencast
  sprechtext: >
    Im ersten Reiter fragt das Formular, wer die Daten herausgibt …
  schritte:
    - klick: "Reiter: 1 — Grundlegende Informationen"
    - tippe: { feld: "Wer gibt diese Daten heraus?", text: "Förderverein Musterstadt e. V." }
  einblendung: { markiere: "Wer gibt diese Daten heraus?", text: "* = Pflicht zum Veröffentlichen" }
```

| Schritt | Werkzeug | Ergebnis |
|---|---|---|
| 1 · Ton | ElevenLabs, Stimme „Damian", mit Zeitmarken | Audio je Szene + Wortzeiten |
| 2 · Aufnahme | Playwright gegen eine eigene `wp-env`-Instanz | Video je Szene; Szenendauer = Audiolänge |
| 3 · Folien | Remotion (React), Projektdesign | Folien als Komponenten, animierbar |
| 4 · Schnitt | Remotion | MP4 (1920 × 1080) mit Zooms und Markierungen; Untertitel als eigene Datei |
| 5 · Begleitmaterial | aus Drehbuch + Zeitmarken | WebVTT-Untertitel, Transkript, Kapitelmarken, YouTube-Beschreibung, Vorschaubild |

**Einzelheiten:**
- **Der Ton gibt das Tempo vor.** Erst entsteht das Audio; die Aufnahme wartet je Schritt, bis der
  zugehörige Satz gesprochen ist. Bild und Ton müssen dadurch nicht von Hand synchronisiert werden.
- **Reproduzierbarer Zustand.** Die bestehende `wp-env`-Umgebung und das Saat-Plugin der E2E-Tests
  liefern den Ausgangspunkt; für die Aufnahmen kommt eine eigene Konfiguration dazu (deutsches
  WordPress, feste Fenstergröße, keine Update-Hinweise, neutraler Benutzername). Für die
  Installationsszenen eine Instanz ohne vorinstalliertes Plugin; das hochgeladene ZIP baut
  `bin/build-release.sh`. Die Aufnahme braucht keinen externen Dienst.
- **Mauszeiger.** Playwright nimmt keinen Zeiger auf; er wird per Skript eingeblendet. Die
  Klickkoordinaten gehen an Remotion für Zooms.
- **Terminal (Folge 10).** Die Befehle laufen echt über `wp-env`; ihre Ausgabe wird aufgezeichnet und
  in Remotion als Terminal dargestellt.
- **Aussprache.** Ein ElevenLabs-Aussprachewörterbuch für DCAT-AP, JSON-LD, GovData, Turtle,
  CESSDA, HVD, MQA, SHACL.
- **Kosten.** Audio wird nur neu erzeugt, wenn sich der Sprechtext einer Szene ändert (Prüfsumme je
  Szene). Ändert sich nur die Oberfläche, läuft allein die Aufnahme neu.
- **Pflege.** Nach einem Release mit sichtbaren Änderungen genügt ein neuer Lauf; das Drehbuch
  verweist auf dieselben Beschriftungen wie das Plugin.
- **Schlüssel.** Der ElevenLabs-Schlüssel bleibt lokal und kommt nie ins Repository.
- **Ton-Dateien** entstehen lokal in `video/ausgabe/` und werden nicht eingecheckt. Auf einem
  anderen Rechner wird der Ton neu erzeugt; das kostet einmalig Credits.

**Verzeichnis `video/`:**

```
video/
├── README.md        Ablauf und Befehle
├── folgen/          Drehbücher, je Folge eine YAML-Datei
├── material/        Dateien für die Aufnahmen (Übungs-CSV, Importdatei mit Fehlerzeile)
├── aufnahme/        Playwright-Skripte, wp-env-Konfiguration, Zeiger- und Markierungsskript
├── remotion/        Folien und Schnitt, mit eigenem package.json
├── aussprache/      Aussprachewörterbuch
└── ausgabe/         erzeugte Videos, Audio, Untertitel — nicht im Repository (.gitignore)
```

- **Eigenes `package.json`** in `video/remotion/`, damit die Entwicklungsabhängigkeiten des Plugins
  schlank bleiben.
- **Nicht im Plugin-Paket:** `bin/build-release.sh` arbeitet mit einer Allowlist; `video/` gelangt
  nicht ins ZIP.
- **Nicht in der CI:** Die E2E-Konfiguration liest nur `tests/e2e/`; die Aufnahmeskripte laufen
  nicht versehentlich als Tests mit.

**Remotion-Lizenz** (Stand der Remotion-FAQ, November 2026): kostenlos für Einzelpersonen,
Organisationen mit bis zu drei Personen und gemeinnützige Organisationen; sonst kostenpflichtig.
Erhält der Kunde nur die fertigen Videos, zählt er nicht mit — es kommt auf die Personen an, die
Remotion in diesem Projekt bedienen. Das Remotion-Projekt bleibt deshalb hier im Repository und
wird nicht an den Kunden übergeben.

## 7 · Veröffentlichung auf YouTube

- **Kanal:** Die Videos erscheinen auf dem Kanal des Kunden. Je Folge entsteht ein Lieferpaket:
  MP4, WebVTT-Untertitel, Vorschaubild, Titel und Beschreibung als Textdatei.
- **Lizenz: CC BY, Namensnennung „Open Data Wizard"** mit Link auf das Repository. YouTube bietet
  in den Upload-Einstellungen „Creative Commons – Namensnennung" an (Version 3.0). Soll zusätzlich
  CC BY 4.0 gelten, steht das ausdrücklich in der Beschreibung. Der Abspann nennt dieselbe Lizenz
  und Namensnennung.
- **Playlist** „Open Data Wizard" mit den Folgen 0–10 in dieser Reihenfolge.
- **Titel-Schema:** „Open Data Wizard · 0 · Quick-Start: In fünf Minuten zum ersten offenen Datensatz".
- **Beschreibung** wird aus dem Drehbuch erzeugt: Kurztext, Kapitelmarken, Links (Release,
  README, Feld-Referenz), Plugin-Version. YouTube erkennt Kapitel, wenn die erste Marke bei 0:00
  steht, es mindestens drei gibt und jedes Kapitel mindestens zehn Sekunden lang ist — die
  Szenenlängen oben erfüllen das.
- **Untertitel** als WebVTT hochladen. Automatische Untertitel verfälschen Fachbegriffe wie
  „DCAT-AP" oder „GovData".
- **Vorschaubild** je Folge aus Remotion, im selben Layout mit Folgennummer.
- **Einbindung:** README und Einstieg-Seite verlinken auf die Playlist. Im WordPress-Backend wird
  nicht eingebettet: ein eingebettetes Video lädt Inhalte von YouTube, bevor jemand klickt
  (Datenschutz).
- **Hochladen** über den Kunden oder mit dessen Zugang, von Hand; das Lieferpaket enthält alles
  Nötige.

## 8 · Offene Punkte

1. **Lizenzversion:** Genügt CC BY 3.0 aus den YouTube-Einstellungen, oder soll in der Beschreibung
   zusätzlich CC BY 4.0 stehen?
2. **Remotion:** Wie viele Personen bedienen Remotion in diesem Projekt, und ist der Träger
   gemeinnützig? Bei höchstens drei Personen oder Gemeinnützigkeit ist die Nutzung kostenlos.
3. **Englische Fassung** später, mit derselben Stimme?
