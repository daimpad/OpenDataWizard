# Klickstrecken und Videos in TYPO3 einbinden (Datenatlas)

Es gibt zwei Pakete, jeweils ein einziger Ordner ohne Unterordner:

| Paket | Ordner in fileadmin | Inhalt |
|---|---|---|
| Klickstrecken | `odw-klickstrecken` | `folge-0.html` bis `folge-10.html`, dazu Bilder (`.jpg`) und Ton (`.mp3`) |
| Videos | `odw-videos` | `folge-0.html` bis `folge-10.html`, dazu je Folge `folge-<n>.mp4` und `folge-<n>-vorschau.jpg` |

`folge-0` ist der Quick-Start. Skript, Daten, Logo, Untertitel und Kapitel stecken in den
HTML-Dateien; andere Dateiarten braucht es nicht. Die Schriften (Bitter, Lato) kommen von der
Website selbst.

## 1. Hochladen (einmalig)

1. In der Dateiliste unter `fileadmin` den Ordner anlegen (`odw-klickstrecken` bzw. `odw-videos`).
2. Alle Dateien des Pakets hineinladen, außer dieser Anleitung. Mehrere Dateien lassen sich auf einmal
   auswählen. Die Videos sind bis zu 18 MB groß; scheitert der Upload, ist die Upload-Grenze des
   Servers zu niedrig.
3. Zum Prüfen `/fileadmin/odw-klickstrecken/folge-3.html` bzw. `/fileadmin/odw-videos/folge-3.html`
   im Browser aufrufen.

Bei einer neuen Fassung dieselben Dateien noch einmal hochladen und vorhandene Dateien ersetzen.

## 2. Auf einer Seite einbinden

**Eingebettet:** Inhaltselement „Reines HTML“, Code einfügen und die Nummer der Folge an zwei Stellen
anpassen.

Klickstrecke:

```html
<div style="position:relative;padding-bottom:calc(56.25% + 400px)">
  <iframe src="/fileadmin/odw-klickstrecken/folge-3.html" title="Klickstrecke Open Data Wizard, Folge 3" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>
</div>
```

Video:

```html
<div style="position:relative;padding-bottom:calc(56.25% + 160px)">
  <iframe src="/fileadmin/odw-videos/folge-3.html" title="Video Open Data Wizard, Folge 3" loading="lazy" allow="fullscreen" style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>
</div>
```

Die Höhe richtet sich nach der Breite und bleibt beim Abspielen gleich. Längere Texte (Klickstrecke)
und die Kapitelliste (Video) scrollen bei Bedarf unter dem Bild. Untertitel schaltet man im
Videoplayer ein; ein Klick auf ein Kapitel springt an diese Stelle.

**Als Link:** In einem beliebigen Textelement auf die HTML-Datei verlinken, z. B.
`/fileadmin/odw-videos/folge-3.html`. Sie öffnet sich dann als eigene Seite.

## 3. Wenn etwas nicht stimmt

- **Ein Bild, der Ton oder das Video fehlt:** Die Datei fehlt im Ordner oder wurde beim Hochladen
  umbenannt (etwa `folge-3_01.mp4`, wenn es sie schon gab). Datei ersetzen statt umbenennen.
- **Andere Schrift als auf der Website:** Die Seiten nutzen die Schriftdateien der Website unter
  `/_assets/…/Fonts/`. Nach einem TYPO3-Update kann sich dieser Pfad ändern; dann sind die Pakete neu
  zu bauen (`website_schriften` in `video/gestaltung/datenatlas.json`).
- **Ton oder Video beginnt nach einem Sprung wieder vorn:** Der Server muss Teilanfragen (HTTP-Range)
  für `.mp3` und `.mp4` beantworten. Apache und nginx tun das von sich aus.
- **Content Security Policy:** Ist sie im Frontend aktiv, müssen eingebettete Seiten vom eigenen Server
  (`frame-src 'self'`) und `style`-Attribute erlaubt sein.
