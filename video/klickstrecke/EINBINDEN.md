# Klickstrecken einbinden (TYPO3 13)

## 1. Ablegen

Den Ordner `odw-tutorials/` vollständig nach `fileadmin/odw-tutorials/` kopieren, am einfachsten
per SFTP. Die Struktur bleibt erhalten:

```
fileadmin/odw-tutorials/
├── odw-klickstrecke.js
├── gestaltung/        (odw.json, datenatlas.json, logos/)
├── schriften/
└── folge-0/ … folge-3/   (daten.json, bilder/, ton/)
```

`beispiel.html` im selben Ordner zeigt alle Folgen mit Umschalter für die Gestaltung — zum Prüfen,
ob der Server alle Dateien ausliefert.

## 2. Auf einer Seite einbinden

Inhaltselement **„Reines HTML"** anlegen und einfügen (Folge und Gestaltung anpassen):

```html
<odw-klickstrecke src="/fileadmin/odw-tutorials/folge-0/daten.json" gestaltung="datenatlas"></odw-klickstrecke>
<script type="module" src="/fileadmin/odw-tutorials/odw-klickstrecke.js"></script>
```

- `gestaltung="datenatlas"` oder `gestaltung="odw"`.
- Mehrere Klickstrecken auf einer Seite sind möglich; das `<script>` genügt einmal.
- Es gibt kein Inline-Skript, keine Cookies und keine Anfragen an andere Server.

## 3. Prüfen

- Liefert der Server `.js`, `.json`, `.woff2`, `.mp3` und `.jpg` aus `fileadmin` aus?
  (`beispiel.html` aufrufen; bleibt die Bühne leer, fehlt meist eine dieser Dateiarten.)
- Ist im Frontend eine Content Security Policy aktiv, müssen Skripte, Schriften, Bilder und Ton vom
  eigenen Server erlaubt sein (`'self'`). Das ist üblicherweise der Fall.
- Der Server muss Teilanfragen (HTTP-Range) für `.mp3` beantworten, sonst beginnt der Ton nach einem
  Sprung im Text wieder am Anfang. Apache und nginx tun das von sich aus. Zum Testen auf dem eigenen
  Rechner eignet sich `python3 -m http.server` deshalb nicht, `npx http-server` dagegen schon.

## Videos

Im Inhaltselement **„Text & Medien"**:

- **YouTube:** die Video-Adresse als Medium hinzufügen; vor dem Laden braucht es eine Einwilligung
  über die Consent-Lösung der Website.
- **MP4:** die Datei aus `fileadmin` als Medium wählen. Untertitel (WebVTT) zeigt das
  Standard-Element nach unserem Kenntnisstand nicht an; das ist bei der Umsetzung zu prüfen.
