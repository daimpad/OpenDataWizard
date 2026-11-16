# Videotutorials — Konzept

> **Entwurf 0.1 zur Abstimmung.** Bezieht sich auf Plugin-Version 2.47.5. Menü- und Feldnamen sind
> aus dem Code übernommen; ändern sie sich, ändert sich das Drehbuch mit.

## 1 · Ziel und Zielgruppe

Eine Reihe kurzer Einzelvideos (2–5 Minuten), die den Open Data Wizard von der Installation bis zur
Anbindung an ein Open-Data-Portal erklärt. Eine Folge beantwortet eine Frage; jede ist für sich
verständlich und einzeln verlinkbar.

**Zielgruppe:** Menschen, die WordPress-Plugins installieren und bedienen können oder eine
Vorstellung davon haben — Redaktionen in Vereinen, Stiftungen, Forschungseinrichtungen und
Kommunen. Keine reinen IT-Fachleute. Für Datenaffine gibt es in einzelnen Folgen kurze, markierte
Einschübe „Blick unter die Haube", die man überspringen kann.

**Stimme:** Damian (ElevenLabs). **Produktion:** automatisiert (Abschnitt 6).

## 2 · Die Reihe im Überblick

| Nr. | Titel | Länge | Bild | Lernziel |
|---|---|---|---|---|
| 0 | **Quick-Start:** In fünf Minuten zum ersten offenen Datensatz | ~5:00 | Screencast + Folien | Installieren, einen Datensatz veröffentlichen, Harvest-URL finden |
| 1 | Warum Metadaten? DCAT-AP und Harvesting | ~3:00 | nur Folien | Das Prinzip verstehen: Daten bleiben, Beschreibung wandert |
| 2 | Installation, Updates, Einstellungen, Beispieldaten | ~4:00 | Screencast | Sauber installieren und das Plugin einrichten |
| 3 | Das Formular I: Grundlegendes und Sprache (Reiter 1–2) | ~4:00 | Screencast | Einen Datensatz inhaltlich beschreiben |
| 4 | Das Formular II: Datenbereitstellung (Reiter 3) | ~4:30 | Screencast + Folie | Datei, Format, Lizenz, mehrere Distributionen |
| 5 | Das Formular III: Erweiterte Angaben (Reiter 4) | ~4:30 | Screencast | Optionale Angaben gezielt nutzen |
| 6 | Prüfen, Veröffentlichen, Qualität | ~3:30 | Screencast + Folie | Validierung und Qualitätsanzeige lesen |
| 7 | Viele Datensätze auf einmal: Batch-Import | ~3:30 | Screencast + Folie | CSV/JSON vorbereiten und importieren |
| 8 | Auf der eigenen Website: Karte und Datenkatalog | ~4:30 | Screencast | Datensätze öffentlich zeigen |
| 9 | Anbindung an Open-Data-Portale | ~4:00 | Screencast + Folien | Harvest-URLs, Formate, Konformität prüfen |
| 10 | Für Administrator:innen: WP-CLI, Rechte, Deinstallation | ~2:30 | Screencast (Terminal) | Wartung und Verwaltung |

Gesamt rund 43 Minuten. Folge 0 steht für sich; die Folgen 1–10 vertiefen.

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
- **Durchgehend Beispieldaten** (Musterstadt, Musterkreis): erfunden, also keine echten Personen-
  oder Organisationsdaten im Bild.
- **Barrierefreiheit:** Untertitel (WebVTT) und Transkript zu jeder Folge; Folien mit
  ausreichendem Kontrast, Information nie nur über Farbe. Für öffentliche Stellen relevant (BITV 2.0).
- **Gestaltung:** Farben und Logo des Projekts (`#03FF9A` auf `#101010`, `assets/images/ODW-Logo.svg`);
  `assets/images/ODW-Ablauf.svg` als Grundlage der Ablauf-Folie.
- **Abspann** mit Plugin-Version, damit erkennbar ist, auf welchem Stand ein Video beruht.

## 4 · Folge 0 — Quick-Start

**Lernziel:** Nach fünf Minuten ist ein Datensatz veröffentlicht, auf einer Seite sichtbar und
die Harvest-URL bekannt.

| # | Dauer | Bild | Klickstrecke / Inhalt | Kernaussage Sprechtext |
|---|---|---|---|---|
| Q1 | 0:20 | Folie | Titel, Logo | Worum es geht und für wen. |
| Q2 | 0:25 | Folie (animiert) | Formular ausfüllen → DCAT-AP.de erzeugen → Portale holen ab | Die Daten bleiben bei Ihnen; nur ihre Beschreibung wandert. |
| Q3 | 0:20 | Screencast GitHub | Releases → `open-data-wizard-<version>.zip` | Einblendung: Nicht der grüne „Code"-Button — dort fehlen Bibliotheken. |
| Q4 | 0:30 | Screencast WP | Plugins → Installieren → Plugin hochladen → Datei auswählen → Jetzt installieren → Plugin aktivieren | Danach erscheint links der Menüpunkt „Datenkatalog". |
| Q5 | 0:15 | Screencast | Datenkatalog → Einstieg → „Beispieldaten laden" | *Optional.* 42 erfundene Beispiele zum Ausprobieren, gekennzeichnet, nie an Portale ausgeliefert. |
| Q6 | 0:15 | Screencast | Datenkatalog → Neuen Datensatz anlegen → Titel | Fünf Reiter; ⓘ zeigt den Fachbegriff, „Mehr erfahren" die Erklärung. |
| Q7 | 0:30 | Screencast | Reiter 1: „Wer gibt diese Daten heraus?"\*, „Worum geht es in diesem Datensatz?"\*, Themen, Schlagworte | Sternchen = nötig zum Veröffentlichen. Als Entwurf speichern geht immer. |
| Q8 | 0:25 | Screencast | „Veröffentlichen" → Meldung nennt Reiter und fehlende Felder → „Zum Feld springen" | Unvollständiges wird nicht veröffentlicht — das Plugin zeigt, wo etwas fehlt. |
| Q9 | 0:30 | Screencast | Reiter 3: „Datei auswählen / hochladen" (Mediathek) oder Link; Lizenz, z. B. „CC-BY 4.0 (Namensnennung)"; Namensnennungstext | Format und Dateigröße ermittelt das Plugin beim Speichern. |
| Q10 | 0:20 | Screencast | Reiter „5 — Vorschau" → Veröffentlichen → Box „Qualitätsprüfung (MQA)" | Prozent und Stufe zeigen, wie vollständig die Beschreibung ist (→ Folge 6). |
| Q11 | 0:30 | Screencast | Seiten → Neue Seite → „+" → Block „Datensatz-Karte" → Datensatz in der Seitenleiste wählen → Veröffentlichen → Seite ansehen | Besucher finden Download, Lizenz und Metadaten an einer Stelle. |
| Q12 | 0:20 | Screencast + Folie | Datenkatalog → Einstellungen → Harvesting → URL kopieren; Folie: Portal trägt die URL ein | Einmal beim Portal melden — es holt Änderungen von selbst ab. |
| Q13 | 0:15 | Folie | Abspann: Hinweis auf die Folgen 1–10, Version | — |

Sprechtext bei rund 130 Wörtern pro Minute: etwa 650 Wörter.

## 5 · Folgen 1–10 — Inhalte

Die Szenentabellen folgen nach Abstimmung dieses Entwurfs. Hier zunächst Inhalt und Klickstrecke.

**1 · Warum Metadaten?** *(nur Folien)*
Daten und Metadaten, erklärt am Bibliothekskatalog (Buch und Karteikarte) · DCAT-AP als gemeinsame
Sprache, DCAT-AP.de als deutsche Ergänzung · Katalog → Datensatz → Distribution · Pull-Harvesting:
das Portal holt ab, mehrere Portale aus einer Quelle · Was das Plugin nicht ist (kein Portal, kein
Datenmanagement, kein Ersatz für fachliche Sorgfalt).

**2 · Installation, Updates, Einstellungen, Beispieldaten**
Release-ZIP und Quellcode-Archiv im Vergleich (Folie), dazu der Hinweis „Installation unvollständig"
bei falschem Paket · automatische Updates (Folie, kurz) · Datenkatalog → Einstellungen: *Katalog*
(Titel, herausgebende Organisation), *Standardwerte für neue Datensätze* (Sprache), *REST API*
(Cache-Laufzeit), *Harvesting* (nur Anzeige), *Qualitätsprüfung (MQA)* (URL-Erreichbarkeit prüfen),
*Deinstallation* (Daten löschen) · Einstieg: Beispieldaten laden und entfernen, „Als eigenen
Datensatz übernehmen".

**3 · Das Formular I: Grundlegendes und Sprache**
Aufbau: fünf Reiter, Sternchen, ⓘ und „Mehr erfahren" · Reiter 1: Herausgeber\*, Beschreibung\*,
Themen (EU-Datenthemen, Mehrfachauswahl), Schlagworte (eines pro Zeile); aufklappbar „Weitere
Einordnung": CESSDA-Themenfeld, Engagementfelder · Reiter 2: Sprache der Daten (24 EU-Amtssprachen);
Titel, Beschreibung und Schlagworte in weiteren Sprachen.
*Unter der Haube:* ein Titel mit Sprachkennung im JSON-LD.

**4 · Das Formular II: Datenbereitstellung**
Zugriffs-URL\* oder Datei aus der Mediathek · Format, Dateigröße · Lizenz\* aus der Liste oder
„Sonstige" mit eigener Lizenz-URI; Namensnennungstext · Verlässlichkeit der Abrufbarkeit, Zugriffsrechte ·
weitere Distributionen (z. B. dieselben Daten als CSV und GeoJSON).
*Folie:* ein Datensatz, mehrere Distributionen.

**5 · Das Formular III: Erweiterte Angaben**
Gruppen in Reihenfolge: *Projektseite & Aktualität* (Projektseite, Erstveröffentlichung,
Aktualisierungsfrequenz) · *Abdeckung* (Verwaltungsebene, amtliches Gebiet, räumlicher Bezug in
Worten, geografische Region, Zeitraum) · *Kontakt* · *Erweiterte Angaben (optional)* mit den
aufklappbaren Gruppen Verantwortlichkeiten & Herkunft, HVD (nur öffentliche Stellen), Weitere
DCAT-AP-Felder, Primäre Distribution. Botschaft: nur öffnen, was man braucht.

**6 · Prüfen, Veröffentlichen, Qualität**
Reiter Vorschau: Pflichtprüfung während des Ausfüllens, darunter das gespeicherte JSON-LD ·
blockierte Veröffentlichung, „Zum Feld springen" · Box „Qualitätsprüfung (MQA)": Prozent, Stufe,
Punkte je Kriterium · Spalte „Qualität" in der Datensatzliste.
*Folie:* vier Stufen (Ausgezeichnet ≥ 87 %, Gut 55–86 %, Ausreichend 30–54 %, Mangelhaft < 30 %),
fünf MQA-Dimensionen.

**7 · Batch-Import**
Datenkatalog → Batch-Import · Beispieldatei `samples/import-example.csv` in einer Tabellenkalkulation ·
Pflichtspalten `title`, `publisher`, `description`, `access_url`, `license` (Folie) · Vorschau mit
gültigen Zeilen und Fehlern → Auswahl → Import als Entwürfe → nacharbeiten und veröffentlichen.

**8 · Auf der eigenen Website**
Block „Datensatz-Karte" und Shortcode `[odw_dataset id="…"]` · Block „Datenkatalog" mit den
Einstellungen der Seitenleiste (Einträge pro Seite, Sortierung, Themen, Auszug, Lizenz, Filter,
Suche, Beispieldaten zeigen) · Frontend: Liste, Filter mit Trefferzahlen, Filter-Chips, Suche,
Detailansicht, „Zurück zum Katalog", Ansicht auf dem Handy · Hinweis: ein Katalog pro Seite.

**9 · Anbindung an Open-Data-Portale** *(Schwerpunkt für Datenaffine)*
Einstellungen → Harvesting · Endpunkte `/catalog`, `?full=1`, `&format=turtle`, `/datasets/<id>`,
`/delta?since=` (Folie) · JSON-LD und Turtle nebeneinander · was nicht ausgeliefert wird
(Entwürfe, private, passwortgeschützte Datensätze, Beispieldaten) · Prüfung mit dem EU-SHACL-Validator ·
Anmeldung bei GovData oder einem Landesportal (Folie, außerhalb des Plugins).

**10 · Für Administrator:innen**
`wp open-data-wizard samples load|remove|status`, `quality recalculate`, `cache clear` · Berechtigung
`manage_open_data` (Administrator:innen und Redakteur:innen) · Deinstallation mit und ohne Datenlöschung.

## 6 · Automatisierte Produktion

**Grundsatz: Das Drehbuch ist die einzige Quelle.** Je Folge eine Datei, die Sprechtext,
Klickschritte und Einblendungen Szene für Szene enthält. Daraus entstehen Ton, Aufnahme und Schnitt.

```yaml
# video/folgen/00-quickstart.yml (Skizze)
- id: Q7
  bild: screencast
  sprechtext: >
    Im ersten Reiter fragt das Formular, wer die Daten herausgibt …
  schritte:
    - klick: "Reiter: 1 — Grundlegende Informationen"
    - tippe: { feld: "Wer gibt diese Daten heraus?", text: "Stadt Musterstadt" }
  einblendung: { markiere: "Wer gibt diese Daten heraus?", text: "* = Pflicht zum Veröffentlichen" }
```

| Schritt | Werkzeug | Ergebnis |
|---|---|---|
| 1 · Ton | ElevenLabs, Stimme „Damian", mit Zeitmarken | Audio je Szene + Wortzeiten |
| 2 · Aufnahme | Playwright gegen eine eigene `wp-env`-Instanz | Video je Szene; Szenendauer = Audiolänge |
| 3 · Folien | Remotion (React), Projektdesign | Folien als Komponenten, animierbar |
| 4 · Schnitt | Remotion | MP4 (1920 × 1080), Zooms, Markierungen, Untertitel |
| 5 · Begleitmaterial | aus Drehbuch + Zeitmarken | WebVTT-Untertitel, Transkript, Kapitelmarken |

**Einzelheiten:**
- **Der Ton gibt das Tempo vor.** Erst entsteht das Audio; die Aufnahme wartet je Schritt, bis der
  zugehörige Satz gesprochen ist. Bild und Ton müssen dadurch nicht von Hand synchronisiert werden.
- **Reproduzierbarer Zustand.** Die bestehende `wp-env`-Umgebung und das Saat-Plugin der E2E-Tests
  liefern den Ausgangspunkt; für die Aufnahmen kommt eine eigene Konfiguration dazu (deutsches
  WordPress, feste Fenstergröße, keine Update-Hinweise, neutraler Benutzername). Für die
  Installationsszenen eine Instanz ohne vorinstalliertes Plugin.
- **Mauszeiger.** Playwright nimmt keinen Zeiger auf; er wird per Skript eingeblendet. Die
  Klickkoordinaten gehen an Remotion für Zooms.
- **Aussprache.** Ein ElevenLabs-Aussprachewörterbuch für DCAT-AP, JSON-LD, GovData, Turtle,
  CESSDA, HVD, MQA.
- **Kosten.** Audio wird nur neu erzeugt, wenn sich der Sprechtext einer Szene ändert (Prüfsumme je
  Szene). Ändert sich nur die Oberfläche, läuft allein die Aufnahme neu.
- **Pflege.** Nach einem Release mit sichtbaren Änderungen genügt ein neuer Lauf; das Drehbuch
  verweist auf dieselben Beschriftungen wie das Plugin.
- **Ablage.** Vorschlag: Verzeichnis `video/` im Repository. Es gelangt nicht ins Release-ZIP, weil
  `bin/build-release.sh` mit einer Allowlist arbeitet. Der ElevenLabs-Schlüssel bleibt lokal und
  kommt nie ins Repository.

## 7 · Offene Punkte

1. **Anrede:** „Sie" wie in der Plugin-Oberfläche oder „du" wie in Teilen der README?
2. **Quick-Start:** Beispieldaten zeigen (Q5) oder erst in Folge 2? Die Fehlermeldung (Q8) als
   didaktisches Mittel behalten?
3. **Folge 10** als Video oder nur als Textdokumentation?
4. **Veröffentlichung:** Plattform (YouTube, PeerTube, Vimeo, eigener Server)? Verlinkung aus README
   und Einstieg-Seite — im Backend eher verlinken als einbetten (Datenschutz).
5. **Remotion:** Lizenzbedingungen für das Projekt prüfen; Alternative wäre ein Schnitt mit ffmpeg.
6. **Englische Fassung** später, mit derselben Stimme?
7. **Ablage des Produktionscodes:** `video/` in diesem Repository oder ein eigenes Repository?
