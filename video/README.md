# Videotutorials — Produktion

Konzept, Entscheidungen und Szenenübersicht: [`docs/VIDEO-KONZEPT.md`](../docs/VIDEO-KONZEPT.md).
Dieses Verzeichnis ist nicht Teil des Plugin-Pakets.

| Pfad | Inhalt |
|---|---|
| `folgen/` | Drehbücher, je Folge eine YAML-Datei |
| `material/` | Dateien für die Aufnahmen (erfundene Übungsdaten) |
| `gestaltung/` | Gestaltungsvarianten `odw` und `datenatlas` (Farben, Schriften, Logo) |
| `aufnahme/` | WordPress für die Aufnahmen einrichten, Ausgangszustände, Bildschirmfotos aufnehmen |
| `klickstrecke/` | Web Component `<odw-klickstrecke>`, Paketbau, Einbindung in TYPO3 |
| `ausgabe/` | alles Erzeugte (nicht im Repository) |

Schnitt und Ton folgen.

## Ablauf

Voraussetzungen: PHP 8.1+ mit `pdo_sqlite`, Composer, Node mit Playwright, Python 3 mit PyYAML, npm.

```bash
bash video/aufnahme/einrichten.sh          # deutsches WordPress mit SQLite, einmalig
bash video/aufnahme/starte-server.sh &     # http://127.0.0.1:8888
node video/aufnahme/aufnahme.js            # alle Folgen; oder z. B. „… aufnahme.js 0 3"
python3 video/klickstrecke/baue-paket.py --aufnahme video/ausgabe/aufnahme --ton video/ausgabe/ton
```

Die Tonspuren liegen als `video/ausgabe/ton/<folge>/<szene>.mp3` (z. B. `ton/0/Q1.mp3`); ohne `--ton`
entsteht das Paket ohne Stimme. Erzeugt werden sie mit ElevenLabs (Stimme „Damian"). Was die Stimme
anders sprechen soll als im Sprechtext geschrieben, steht in `video/aussprache/de.yml` — der
Sprechtext selbst bleibt unverändert, weil er zugleich Untertitel ist.

Das Paket liegt danach in `video/ausgabe/odw-tutorials/`; `beispiel.html` darin zeigt alle Folgen mit
Umschalter für die Gestaltung, `EINBINDEN.md` beschreibt die Einbindung in TYPO3 13.

Die Aufnahme meldet jeden Schritt mit ✓ oder ✗. Ein ✗ heißt fast immer: Eine Beschriftung im Drehbuch
stimmt nicht mehr mit WordPress oder dem Plugin überein — das Bildschirmfoto zum Schritt zeigt, was
stattdessen zu sehen war.

## Drehbuch-Format

Ein Drehbuch ist die einzige Quelle für Ton, Aufnahme, Schnitt, Untertitel und YouTube-Beschreibung.

**Kopf:** `folge`, `titel`, `plugin_version`, `umgebung` (Ausgangszustand der Aufnahme), `szenen`.

**Szene**

| Schlüssel | Bedeutung |
|---|---|
| `id` | Szenennummer aus dem Konzept |
| `kapitel` | Kapitelname für YouTube (jedes Kapitel mindestens 10 s) |
| `bild` | `folie` oder `screencast` |
| `dauer` | geplante Dauer; maßgeblich ist die Länge des erzeugten Tons |
| `sprechtext` | je Sprache (`de`, später `en`); gesprochen von „Damian" (ElevenLabs) |
| `folie` | Bildbeschreibung einer Folie |
| `schritte` | Klickstrecke eines Screencasts, der Reihe nach |
| `einblendung` | Markierung, Zoom, Hinweistext oder Folienelement über dem Bild |

**Schritte:** `oeffne` (Pfad; `{ text, besucher: true }` ruft ihn abgemeldet auf), `handy` (Pfad in
Handy-Breite, mittig auf der Bühne), `klick` (sichtbare Beschriftung), `tippe` `{ feld, text }`,
`waehle` `{ feld, option }`, `lade_hoch` `{ feld, datei }`, `warte_auf` (Text), `scrolle_zu` (Text),
`bestaetige` (Rückfrage des Browsers mit „OK" bestätigen; `text` nennt die erwartete Frage).
Ein Schritt mit nur einem Text kann auch als Objekt `{ text, bei }` geschrieben werden.

**Einblendungen:** `markiere` (Beschriftung im Bild), `zoom: true`, `text` (Hinweis), `zeige`
(Element einer Folie erscheinen lassen).

**Zeitpunkt:** `bei` nennt ein Wort aus dem Sprechtext der Szene. Schritt oder Einblendung
erfolgen, wenn dieses Wort gesprochen wird (Zeitmarken von ElevenLabs). Das Wort muss im
Sprechtext vorkommen.

**Sprechtext** ist zugleich der Untertitel. Die Aussprache von Begriffen wie DCAT-AP, CC BY 4.0,
GovData oder .zip regelt das Aussprachewörterbuch, nicht eine Lautschrift im Text.

**Beschriftungen** stammen ohne weitere Angabe aus dem Plugin; für die englische Fassung werden sie in
`languages/open-data-wizard-en_US.po` nachgeschlagen. `quelle: core` kennzeichnet Beschriftungen von
WordPress selbst, `quelle: carbon-fields` solche der Formular-Bibliothek Carbon Fields (deutsch über
`languages/carbon-fields-ui-de_DE.po`); `pruefen: true` heißt, bei der ersten Aufnahme gegen die laufende
WordPress-Version prüfen.
