#!/usr/bin/env python3
"""Baut das Klickstrecken-Paket für fileadmin (TYPO3) oder jede andere Website.

    python3 video/klickstrecke/baue-paket.py --aufnahme video/ausgabe/aufnahme

Liest die Drehbücher (video/folgen/*.yml), die Ergebnisse der Aufnahme (bilder/<folge>/…,
ergebnis.json) und, falls vorhanden, die Tonspuren (ton/<folge>/<szene>.mp3). Ergebnis in
video/ausgabe/odw-tutorials/:

    odw-klickstrecke.js        Web Component <odw-klickstrecke>
    gestaltung/<name>.json      Gestaltungsvarianten, dazu gestaltung/logos/
    schriften/                  Schriftdateien (SIL Open Font License) samt Lizenztexten
    folge-<n>/daten.json        je Folge: Szenen, Sprechtext, Klickschritte
    folge-<n>/bilder/, ton/     Bildschirmfotos und Tonspuren
    beispiel.html               Testseite mit Umschalter für Folge und Gestaltung
    EINBINDEN.md                Anleitung für TYPO3 13

Braucht Python 3 mit PyYAML und npm (lädt die Schriften als @fontsource-Pakete mit fester Version).
"""
import argparse
import glob
import json
import os
import re
import shutil
import subprocess
import tarfile

import yaml

HIER = os.path.dirname(os.path.abspath(__file__))
VIDEO = os.path.dirname(HIER)
REPO = os.path.dirname(VIDEO)
FONTSOURCE_VERSION = '5.3.0'

ORTE = {
    '/wp-admin/plugins.php': '„Plugins"',
    '/wp-admin/edit.php?post_type=odw_dataset': '„Datenkatalog" → „Alle Datensätze"',
    '/wp-admin/edit.php?post_type=odw_dataset&page=odw-einstieg': '„Datenkatalog" → „Einstieg"',
    '/wp-admin/edit.php?post_type=odw_dataset&page=odw-settings': '„Datenkatalog" → „Einstellungen"',
    '/wp-admin/post-new.php?post_type=page': '„Seiten" → „Seite hinzufügen"',
}


def ersetze(wert, kontext):
    if not isinstance(wert, str):
        return wert
    m = re.fullmatch(r'\{([\w.]+)\}', wert)
    if not m:
        return wert
    x = kontext
    for k in m.group(1).split('.'):
        x = (x or {}).get(k)
    return ', '.join(x) if isinstance(x, list) else x


def typo(text):
    """Deutsche Anführungszeichen: „…" aus den Drehbüchern wird zu „…“."""
    return re.sub(r'„([^"“”]*)"', r'„\1“', text) if isinstance(text, str) else text


def kurz(text, n=70):
    text = ' '.join(str(text).split())
    return text if len(text) <= n else text[: n - 1].rstrip() + '…'


def schritt_text(s, kontext):
    """Klickschritt aus dem Drehbuch als verständlicher Satz."""
    art, wert = next((k, v) for k, v in s.items() if k != 'bei')
    v = wert if isinstance(wert, dict) else {'text': wert}
    t = ersetze(v.get('text'), kontext)
    feld = ersetze(v.get('feld'), kontext)
    if art == 'oeffne':
        if v.get('besucher'):
            return 'Die Seite als Besucher aufrufen (abgemeldet)'
        return f'{ORTE[t]} öffnen' if t in ORTE else 'Die veröffentlichte Seite aufrufen'
    if art == 'handy':
        return 'Die Seite auf dem Handy aufrufen'
    if art == 'klick':
        return f'„{t}" anklicken'
    if art == 'tippe':
        return f'In „{feld}" eingeben: {kurz(t)}'
    if art == 'waehle':
        return f'In „{feld}" auswählen: {ersetze(v.get("option"), kontext)}'
    if art == 'lade_hoch':
        datei = ersetze(v.get('datei'), kontext)
        return 'Das Release-ZIP hochladen' if datei == '<release-zip>' else f'Datei hochladen: {datei}'
    if art == 'warte_auf':
        return f'Meldung: „{kurz(t)}"'
    if art == 'scrolle_zu':
        return f'Zu „{t}" blättern'
    if art == 'bestaetige':
        return 'Rückfrage bestätigen'
    return art


def schriften(ziel, cache):
    os.makedirs(os.path.join(ziel, 'schriften'), exist_ok=True)
    os.makedirs(cache, exist_ok=True)
    benoetigt = {}
    for datei in glob.glob(os.path.join(VIDEO, 'gestaltung', '*.json')):
        g = json.load(open(datei, encoding='utf-8'))
        for rolle in g['schriften'].values():
            benoetigt.setdefault(rolle['paket'], set()).update(rolle['schnitte'])
    for paket, schnitte in sorted(benoetigt.items()):
        tgz = os.path.join(cache, f'fontsource-{paket}-{FONTSOURCE_VERSION}.tgz')
        if not os.path.exists(tgz):
            subprocess.run(['npm', 'pack', f'@fontsource/{paket}@{FONTSOURCE_VERSION}', '--silent'], cwd=cache, check=True, stdout=subprocess.DEVNULL)
        with tarfile.open(tgz) as tar:
            for w in sorted(schnitte):
                name = f'{paket}-latin-{w}-normal.woff2'
                with tar.extractfile(f'package/files/{name}') as quelle, open(os.path.join(ziel, 'schriften', name), 'wb') as aus:
                    aus.write(quelle.read())
            with tar.extractfile('package/LICENSE') as quelle, open(os.path.join(ziel, 'schriften', f'LIZENZ-{paket}.txt'), 'wb') as aus:
                aus.write(quelle.read())


def gestaltung(ziel):
    """Gestaltungsvarianten und Logos. Fehlt ein Logo (z. B. die Marke des Datenatlas, die nicht im
    Repository liegt), wird der Name als Schriftzug gezeigt."""
    aus = os.path.join(ziel, 'gestaltung')
    shutil.rmtree(aus, ignore_errors=True)
    os.makedirs(os.path.join(aus, 'logos'))
    shutil.copy(os.path.join(REPO, 'assets', 'images', 'ODW-Logo.svg'), os.path.join(aus, 'logos', 'odw.svg'))
    for datei in glob.glob(os.path.join(VIDEO, 'gestaltung', 'logos', '*.svg')):
        shutil.copy(datei, os.path.join(aus, 'logos'))
    for datei in glob.glob(os.path.join(VIDEO, 'gestaltung', '*.json')):
        g = json.load(open(datei, encoding='utf-8'))
        if g.get('logo') and not os.path.exists(os.path.join(aus, g['logo'])):
            print(f'Hinweis: {g["logo"]} fehlt — Variante „{g["name"]}" zeigt den Namen als Schriftzug.')
            g['logo'] = None
        json.dump(g, open(os.path.join(aus, os.path.basename(datei)), 'w', encoding='utf-8'), ensure_ascii=False, indent=2)


def folge_paket(drehbuch, aufnahme, ton_quelle, ziel):
    nr = drehbuch['folge']
    ordner = os.path.join(ziel, f'folge-{nr}')
    shutil.rmtree(ordner, ignore_errors=True)
    os.makedirs(ordner)
    ergebnis = {}
    pfad = os.path.join(aufnahme, 'ergebnis.json') if aufnahme else None
    if pfad and os.path.exists(pfad):
        ergebnis = json.load(open(pfad, encoding='utf-8')).get(str(nr), {}).get('szenen', {})
    szenen, fehlende_bilder = [], 0
    for s in drehbuch['szenen']:
        r = ergebnis.get(str(s['id']), {})
        schritte = s.get('schritte', [])
        bilder = []
        for i, b in enumerate(r.get('bilder', [])):
            quelle = os.path.join(aufnahme, b['datei'])
            if not os.path.exists(quelle):
                fehlende_bilder += 1
                continue
            os.makedirs(os.path.join(ordner, 'bilder'), exist_ok=True)
            name = os.path.basename(b['datei'])
            shutil.copy(quelle, os.path.join(ordner, 'bilder', name))
            text = schritt_text(schritte[i], drehbuch) if i < len(schritte) else b['schritt']
            bilder.append({'datei': f'bilder/{name}', 'text': typo(text), 'schritt': b['schritt'], 'bei': b['bei']})
        ton = None
        if ton_quelle:
            mp3 = os.path.join(ton_quelle, str(nr), f'{s["id"]}.mp3')
            if os.path.exists(mp3):
                os.makedirs(os.path.join(ordner, 'ton'), exist_ok=True)
                shutil.copy(mp3, os.path.join(ordner, 'ton', f'{s["id"]}.mp3'))
                ton = f'ton/{s["id"]}.mp3'
        szenen.append({
            'id': str(s['id']), 'kapitel': typo(s['kapitel']), 'bild': s['bild'],
            'sprechtext': typo(' '.join(s['sprechtext']['de'].split())),
            'folie_danach': s.get('folie_danach'),
            'einblendung': [{k: (typo(ersetze(v, drehbuch)) if k == 'text' else ersetze(v, drehbuch)) for k, v in e.items()} for e in s.get('einblendung', [])],
            'bilder': bilder, 'markierungen': r.get('markierungen', []), 'ton': ton,
        })
    daten = {'folge': nr, 'titel': drehbuch['titel'], 'plugin_version': drehbuch['plugin_version'], 'szenen': szenen}
    json.dump(daten, open(os.path.join(ordner, 'daten.json'), 'w', encoding='utf-8'), ensure_ascii=False)
    return nr, drehbuch['titel'], sum(len(x['bilder']) for x in szenen), sum(1 for x in szenen if x['ton']), fehlende_bilder


def main():
    ap = argparse.ArgumentParser(description=__doc__.split('\n')[0])
    ap.add_argument('--aufnahme', help='Ordner mit bilder/ und ergebnis.json der Aufnahme')
    ap.add_argument('--ton', help='Ordner mit ton/<folge>/<szene>.mp3')
    ap.add_argument('--ziel', default=os.path.join(VIDEO, 'ausgabe', 'odw-tutorials'))
    args = ap.parse_args()

    ziel = args.ziel
    os.makedirs(ziel, exist_ok=True)
    shutil.copy(os.path.join(HIER, 'odw-klickstrecke.js'), ziel)
    gestaltung(ziel)
    schriften(ziel, os.path.join(VIDEO, 'ausgabe', '.cache'))

    folgen = []
    for datei in sorted(glob.glob(os.path.join(VIDEO, 'folgen', '*.yml'))):
        folgen.append(folge_paket(yaml.safe_load(open(datei, encoding='utf-8')), args.aufnahme, args.ton, ziel))

    for name in ('beispiel.html', 'EINBINDEN.md'):
        shutil.copy(os.path.join(HIER, name), ziel)
    for nr, titel, bilder, toene, fehlend in folgen:
        hinweis = f', {fehlend} Bilder fehlen' if fehlend else ''
        print(f'Folge {nr}: {titel} — {bilder} Bilder, {toene} Tonspuren{hinweis}')
    print(f'Paket: {ziel}')


if __name__ == '__main__':
    main()
