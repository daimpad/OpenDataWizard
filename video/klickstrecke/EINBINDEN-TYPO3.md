# Klickstrecken in TYPO3 einbinden (Datenatlas)

Dieser Ordner enthält je Folge eine HTML-Datei (`folge-0.html` ist der Quick-Start, dann
`folge-1.html` bis `folge-10.html`) und daneben alle Bilder (`.jpg`) und Tonspuren (`.mp3`).
Skript, Daten und Logo stecken in den HTML-Dateien. Andere Dateiarten braucht es nicht. Die
Schriften (Bitter, Lato) kommen von der Website selbst.

## 1. Hochladen (einmalig)

1. In der Dateiliste unter `fileadmin` den Ordner `odw-klickstrecken` anlegen.
2. Alle Dateien dieses Ordners hineinladen, außer dieser Anleitung. Mehrere Dateien lassen sich auf
   einmal auswählen; Unterordner gibt es nicht. Die größte Datei hat unter 1 MB.
3. Zum Prüfen `/fileadmin/odw-klickstrecken/folge-3.html` im Browser aufrufen.

Bei einer neuen Fassung dieselben Dateien noch einmal hochladen und vorhandene Dateien ersetzen.

## 2. Auf einer Seite einbinden

**Eingebettet:** Inhaltselement „Reines HTML“, diesen Code einfügen und die Nummer der Folge an zwei
Stellen anpassen:

```html
<div style="position:relative;padding-bottom:calc(56.25% + 400px)">
  <iframe src="/fileadmin/odw-klickstrecken/folge-3.html" title="Klickstrecke Open Data Wizard, Folge 3" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>
</div>
```

Die Höhe richtet sich nach der Breite. Beim Wechsel der Szene springt die Seite nicht; längere Texte
scrollen unter dem Bild.

**Als Link:** In einem beliebigen Textelement auf `/fileadmin/odw-klickstrecken/folge-3.html`
verlinken. Die Klickstrecke öffnet sich dann als eigene Seite.

## 3. Wenn etwas nicht stimmt

- **Ein Bild oder der Ton fehlt:** Die Datei fehlt im Ordner oder wurde beim Hochladen umbenannt (etwa
  `3_2-1_01.jpg`, wenn es sie schon gab). Datei ersetzen statt umbenennen.
- **Andere Schrift als auf der Website:** Die Klickstrecke nutzt die Schriftdateien der Website unter
  `/_assets/…/Fonts/`. Nach einem TYPO3-Update kann sich dieser Pfad ändern; dann ist das Paket neu
  zu bauen (`website_schriften` in `video/gestaltung/datenatlas.json`).
- **Ton beginnt nach einem Klick in den Text wieder vorn:** Der Server muss Teilanfragen (HTTP-Range)
  für `.mp3` beantworten. Apache und nginx tun das von sich aus.
- **Content Security Policy:** Ist sie im Frontend aktiv, müssen eingebettete Seiten vom eigenen Server
  (`frame-src 'self'`) und `style`-Attribute erlaubt sein.
