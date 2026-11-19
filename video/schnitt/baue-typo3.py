#!/usr/bin/env python3
"""Baut die Videos als TYPO3-Fassung (nur Gestaltung „datenatlas").

    python3 video/schnitt/baue-typo3.py

Voraussetzung: die Videos der Gestaltung datenatlas in video/ausgabe/videos/
(bash video/schnitt/video.sh <folge> datenatlas). Ergebnis in video/ausgabe/datenatlas-typo3-videos/,
ein einziger Ordner für fileadmin:

    folge-<n>.html            Seite mit Video, Untertiteln (eingebettet) und Kapitelliste
    folge-<n>.mp4             das Video
    folge-<n>-vorschau.jpg    Vorschaubild
    EINBINDEN.md              Anleitung (nicht hochladen)

Für Server, die nur wenige Dateiarten annehmen: Untertitel und Kapitel stecken in der HTML-Datei,
.vtt-Dateien braucht es nicht. Die Schriften kommen von der Datenatlas-Website (website_schriften in
video/gestaltung/datenatlas.json). Im iframe hat die Seite eine feste Höhe; nur die Kapitelliste scrollt.
"""
import base64
import glob
import html
import json
import os
import re
import shutil

import yaml

HIER = os.path.dirname(os.path.abspath(__file__))
VIDEO = os.path.dirname(HIER)
QUELLE = os.path.join(VIDEO, 'ausgabe', 'videos')
ZIEL = os.path.join(VIDEO, 'ausgabe', 'datenatlas-typo3-videos')

SEITE = """<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{titel}</title>
<style>
{schriften}
:root {{ {farben} }}
html, body {{ margin: 0; background: var(--grund); color: var(--text); }}
body {{ padding: 8px; font: 400 1rem/1.5 "Lato", system-ui, -apple-system, "Segoe UI", sans-serif; }}
.video {{ max-width: 1100px; margin: 0 auto; display: grid; gap: 12px; }}
h1 {{ margin: 0; font: 700 1.05rem/1.3 "Bitter", Georgia, serif; }}
video {{ display: block; width: 100%; aspect-ratio: 16 / 9; background: #000; border-radius: 6px; }}
.kapitel {{ list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px 8px; }}
.kapitel button {{ font: inherit; font-size: .9rem; color: inherit; background: var(--grund); border: 1px solid var(--linie);
  border-radius: 6px; padding: 4px 10px; cursor: pointer; }}
.kapitel .zeit {{ color: var(--gedimmt); font-variant-numeric: tabular-nums; margin-right: 6px; }}
.kapitel button[aria-current="true"] {{ background: var(--markierung); border-color: var(--markierung); }}
.kapitel button[aria-current="true"] .zeit {{ color: inherit; }}
button:focus-visible, video:focus-visible {{ outline: 3px solid var(--fokus); outline-offset: 2px; }}
/* Im iframe: das Fenster ganz füllen, nur die Kapitelliste scrollt. */
.eingebettet, .eingebettet body {{ height: 100%; }}
.eingebettet body {{ padding: 0; }}
.eingebettet .video {{ height: 100%; max-width: none; display: flex; flex-direction: column; }}
.eingebettet .video > * {{ flex: none; }}
.eingebettet .video > nav {{ flex: 1 1 0; min-height: 0; overflow-y: auto; }}
</style>
</head>
<body>
<main class="video">
<h1>{kopf}</h1>
<video controls preload="metadata" playsinline poster="{poster}">
<source src="{mp4}" type="video/mp4">
<track kind="subtitles" srclang="de" label="Deutsch" src="data:text/vtt;base64,{vtt}">
</video>
<nav aria-label="Kapitel"><ol class="kapitel">
{kapitel}
</ol></nav>
</main>
<script>
if (window.top !== window.self) document.documentElement.classList.add('eingebettet');
(function () {{
  var v = document.querySelector('video'), knoepfe = [].slice.call(document.querySelectorAll('.kapitel button'));
  knoepfe.forEach(function (k) {{ k.addEventListener('click', function () {{ v.currentTime = +k.dataset.t; v.play(); }}); }});
  v.addEventListener('timeupdate', function () {{
    var aktiv = null;
    knoepfe.forEach(function (k) {{ if (+k.dataset.t <= v.currentTime + 0.2) aktiv = k; }});
    knoepfe.forEach(function (k) {{ k.setAttribute('aria-current', k === aktiv ? 'true' : 'false'); }});
  }});
}})();
</script>
</body>
</html>
"""


def sekunden(zeit):
    teile = [int(x) for x in zeit.split(':')]
    return sum(x * 60 ** i for i, x in enumerate(reversed(teile)))


def main():
    g = json.load(open(os.path.join(VIDEO, 'gestaltung', 'datenatlas.json'), encoding='utf-8'))
    web = g.get('website_schriften', {})
    regeln = []
    for rolle in ('anzeige', 'text'):
        familie = g['schriften'][rolle]['familie']
        for schnitt, url in sorted(web.get(rolle, {}).items()):
            regeln.append(f'@font-face {{ font-family: "{familie}"; font-weight: {schnitt}; font-style: normal; '
                          f'font-display: swap; src: url("{url}") format("woff2"); }}')
    b = g['bedienung']
    farben = '; '.join(f'--{k}: {b[k]}' for k in ('grund', 'text', 'gedimmt', 'linie', 'markierung', 'fokus'))

    shutil.rmtree(ZIEL, ignore_errors=True)
    os.makedirs(ZIEL)
    fertig = []
    for datei in sorted(glob.glob(os.path.join(VIDEO, 'folgen', '*.yml'))):
        d = yaml.safe_load(open(datei, encoding='utf-8'))
        n = d['folge']
        stamm = os.path.join(QUELLE, f'folge-{n}-datenatlas')
        if not os.path.exists(stamm + '.mp4'):
            print(f'Folge {n}: kein Video (bash video/schnitt/video.sh {n} datenatlas)')
            continue
        shutil.copy(stamm + '.mp4', os.path.join(ZIEL, f'folge-{n}.mp4'))
        shutil.copy(stamm + '-vorschau.jpg', os.path.join(ZIEL, f'folge-{n}-vorschau.jpg'))
        vtt = base64.b64encode(open(stamm + '.vtt', 'rb').read()).decode('ascii')
        kapitel = []
        for zeile in open(stamm + '-kapitel.txt', encoding='utf-8').read().splitlines():
            m = re.match(r'(\d+(?::\d+)+)\s+(.+)', zeile.strip())
            if m:
                kapitel.append(f'<li><button type="button" data-t="{sekunden(m[1])}"><span class="zeit">{m[1]}</span>'
                               f'{html.escape(m[2])}</button></li>')
        kopf = ('Quick-Start · ' + d['titel'].replace('Quick-Start: ', '')) if n == 0 else f'Folge {n} · {d["titel"]}'
        open(os.path.join(ZIEL, f'folge-{n}.html'), 'w', encoding='utf-8').write(SEITE.format(
            titel=html.escape(f'{kopf} · Open Data Wizard'), kopf=html.escape(kopf), schriften='\n'.join(regeln),
            farben=farben, poster=f'folge-{n}-vorschau.jpg', mp4=f'folge-{n}.mp4', vtt=vtt, kapitel='\n'.join(kapitel)))
        fertig.append(n)
    shutil.copy(os.path.join(VIDEO, 'klickstrecke', 'EINBINDEN-TYPO3.md'), os.path.join(ZIEL, 'EINBINDEN.md'))
    print(f'TYPO3-Fassung der Videos: {ZIEL} (Folgen {", ".join(map(str, fertig))})')


if __name__ == '__main__':
    main()
