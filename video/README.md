# Videotutorials — Produktion

Konzept, Entscheidungen und Szenenübersicht: [`docs/VIDEO-KONZEPT.md`](../docs/VIDEO-KONZEPT.md).
Dieses Verzeichnis ist nicht Teil des Plugin-Pakets.

| Pfad | Inhalt |
|---|---|
| `folgen/` | Drehbücher, je Folge eine YAML-Datei |
| `material/` | Dateien für die Aufnahmen (erfundene Übungsdaten) |

Aufnahme- und Schnittwerkzeuge folgen.

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

**Schritte:** `oeffne` (Pfad im Backend), `klick` (sichtbare Beschriftung), `tippe` `{ feld, text }`,
`waehle` `{ feld, option }`, `lade_hoch` `{ feld, datei }`, `warte_auf` (Text), `scrolle_zu` (Text).

**Einblendungen:** `markiere` (Beschriftung im Bild), `zoom: true`, `text` (Hinweis), `zeige`
(Element einer Folie erscheinen lassen).

**Zeitpunkt:** `bei` nennt ein Wort aus dem Sprechtext der Szene. Schritt oder Einblendung
erfolgen, wenn dieses Wort gesprochen wird (Zeitmarken von ElevenLabs). Das Wort muss im
Sprechtext vorkommen.

**Sprechtext** ist zugleich der Untertitel. Die Aussprache von Begriffen wie DCAT-AP, CC BY 4.0,
GovData oder .zip regelt das Aussprachewörterbuch, nicht eine Lautschrift im Text.

**Beschriftungen** stammen ohne weitere Angabe aus dem Plugin; für die englische Fassung werden sie in
`languages/open-data-wizard-en_US.po` nachgeschlagen. `quelle: core` kennzeichnet Beschriftungen von
WordPress selbst; `pruefen: true` heißt, bei der ersten Aufnahme gegen die laufende
WordPress-Version prüfen.
