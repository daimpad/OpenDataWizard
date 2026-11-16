#!/usr/bin/env python3
"""Montiert eine Folge: aus den Zuständen der Bühne (zustaende.js) und dem Ton je Szene entstehen

    <ziel>.mp4               1920 × 1080, 25 Bilder je Sekunde, H.264 + AAC
    <ziel>.vtt               Untertitel (WebVTT) aus den Sprechtexten
    <ziel>-kapitel.txt       Kapitelmarken für YouTube
    <ziel>-beschreibung.txt  Titel und Beschreibung für YouTube
    <ziel>-vorschau.jpg      Vorschaubild 1280 × 720 (Titelfolie)

Aufruf:

    python3 video/schnitt/montiere.py <zustandsordner> <tonordner> <ziel ohne Endung>

Zeitachse: kurzer Vorlauf auf der Titelfolie, dann jede Szene so lang wie ihr Ton, dazwischen eine
Pause von 0,8 s, am Ende ein Nachlauf auf dem Abspann. Wechselt die Bühne, blendet das Bild in
0,28 s über. Kapitel unter zehn Sekunden (YouTube verlangt mindestens zehn) werden mit dem folgenden
zusammengelegt; Vor- und Nachlauf sind so bemessen, dass erstes und letztes Kapitel lang genug sind.
"""

import json
import os
import subprocess
import sys
import tempfile
import wave

from PIL import Image

FPS = 25
BREITE, HOEHE = 1920, 1080
PAUSE = 0.8          # zwischen zwei Szenen
VORLAUF_MIN = 1.0    # Titelfolie, bevor die Stimme einsetzt
NACHLAUF_MIN = 3.0   # Abspann, nachdem die Stimme endet
KAPITEL_MIN = 10.2   # YouTube: jedes Kapitel mindestens zehn Sekunden (Zielwert mit etwas Luft)
KAPITEL_GRENZE = 10.0  # darunter wird ein Kapitel zusammengelegt
BLENDE = 0.28
RATE = 48000
REPO_URL = 'https://github.com/daimpad/OpenDataWizard'


def wav_aus_mp3(mp3, ziel):
    subprocess.run(['ffmpeg', '-v', 'error', '-y', '-i', mp3, '-ac', '1', '-ar', str(RATE), '-sample_fmt', 's16', ziel], check=True)
    with wave.open(ziel) as w:
        return w.readframes(w.getnframes())


def zeit_vtt(s):
    ms = int(round(s * 1000))
    return f'{ms // 3600000:02d}:{ms // 60000 % 60:02d}:{ms // 1000 % 60:02d}.{ms % 1000:03d}'


def zeit_kurz(s):
    s = int(s)
    return f'{s // 3600}:{s // 60 % 60:02d}:{s % 60:02d}' if s >= 3600 else f'{s // 60}:{s % 60:02d}'


def zeilen(text, breite=42):
    """Bricht einen Untertitel in höchstens zwei ausgewogene Zeilen um."""
    if len(text) <= breite:
        return text
    woerter = text.split(' ')
    beste = None
    for i in range(1, len(woerter)):
        a, b = ' '.join(woerter[:i]), ' '.join(woerter[i:])
        # Ausgewogen, aber lieber nach einem Satzzeichen umbrechen.
        guete = max(len(a), len(b)) - (6 if a.rstrip('“”"»«').endswith((',', ';', ':')) else 0)
        if beste is None or guete < beste[0]:
            beste = (guete, a, b)
    return beste[1] + '\n' + beste[2]


def untertitel(woerter, ende):
    """Fasst Wörter (mit Startzeit) zu Untertiteln zusammen: höchstens zwei Zeilen à 42 Zeichen,
    nach Satzende ein neuer Untertitel. Wird ein Untertitel zu lang, endet er möglichst an einem
    Komma, Semikolon oder Doppelpunkt statt mitten im Satzteil."""
    cues, aktuell = [], []
    for w in woerter:
        kandidat = ' '.join(x['w'] for x in aktuell + [w])
        if aktuell and len(kandidat) > 84:
            schnitt = len(aktuell)
            for k in range(len(aktuell) - 1, 1, -1):
                if aktuell[k]['w'].rstrip('“”"»«').endswith((',', ';', ':')) and len(' '.join(x['w'] for x in aktuell[:k + 1])) >= 28:
                    schnitt = k + 1
                    break
            cues.append(aktuell[:schnitt])
            aktuell = aktuell[schnitt:]
        aktuell.append(w)
        if w['w'].rstrip('“”"»«').endswith(('.', '!', '?')) and len(' '.join(x['w'] for x in aktuell)) > 24:
            cues.append(aktuell)
            aktuell = []
    if aktuell:
        cues.append(aktuell)
    aus = []
    for i, c in enumerate(cues):
        start = c[0]['start']
        stopp = cues[i + 1][0]['start'] if i + 1 < len(cues) else ende
        aus.append((start, stopp, zeilen(' '.join(x['w'] for x in c))))
    return aus


def main():
    if len(sys.argv) != 4:
        sys.exit(__doc__)
    ordner, tonordner, ziel = sys.argv[1:]
    daten = json.load(open(os.path.join(ordner, 'zustaende.json'), encoding='utf-8'))
    paket = json.load(open(os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'ausgabe', 'odw-tutorials',
                                        f'folge-{daten["folge"]}', 'daten.json'), encoding='utf-8'))
    szenen = daten['szenen']
    os.makedirs(os.path.dirname(os.path.abspath(ziel)), exist_ok=True)
    tmp = tempfile.mkdtemp(prefix='montage-', dir=ordner)

    # --- Ton je Szene, exakt in Samples ---
    pcm = []
    for sz in szenen:
        mp3 = os.path.join(tonordner, f'{sz["id"]}.mp3')
        if os.path.exists(mp3):
            pcm.append(wav_aus_mp3(mp3, os.path.join(tmp, f'{sz["id"]}.wav')))
        else:
            pcm.append(b'\0\0' * int(round(sz['gesamt'] * RATE)))
    laenge = [len(p) / 2 / RATE for p in pcm]

    # --- Zeitachse ---
    vorlauf = max(VORLAUF_MIN, KAPITEL_MIN - laenge[0] - PAUSE) if len(szenen) > 1 else VORLAUF_MIN
    start, t = [], vorlauf
    for i, l in enumerate(laenge):
        start.append(t)
        t += l + (PAUSE if i < len(laenge) - 1 else 0)
    ende = max(t + NACHLAUF_MIN, start[-1] + KAPITEL_MIN) if len(szenen) > 1 else t + NACHLAUF_MIN
    gesamt = round(ende * FPS) / FPS

    # Tonspur: Vorlauf, Szenen mit Pausen, Nachlauf.
    stille = lambda s: b'\0\0' * int(round(s * RATE))
    with wave.open(os.path.join(tmp, 'ton.wav'), 'wb') as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(RATE)
        w.writeframes(stille(vorlauf))
        for i, p in enumerate(pcm):
            w.writeframes(p)
            if i < len(pcm) - 1:
                w.writeframes(stille(PAUSE))
        w.writeframes(stille(gesamt - t))

    # Zustände auf der gemeinsamen Zeitachse; die Szene wird auf ihre Tonlänge gestreckt.
    zustaende = []
    for i, sz in enumerate(szenen):
        faktor = laenge[i] / sz['gesamt'] if sz['gesamt'] else 1
        for k, z in enumerate(sz['zustaende']):
            zt = 0.0 if (i == 0 and k == 0) else start[i] + z['t'] * faktor
            zustaende.append((zt, os.path.join(ordner, z['datei'])))
    zustaende.sort(key=lambda x: x[0])

    # --- Bild: Rohbilder an ffmpeg, mit Überblendung bei jedem Wechsel ---
    ff = subprocess.Popen(['ffmpeg', '-v', 'error', '-y', '-f', 'rawvideo', '-pix_fmt', 'rgb24', '-s', f'{BREITE}x{HOEHE}', '-r', str(FPS),
                           '-i', '-', '-i', os.path.join(tmp, 'ton.wav'), '-c:v', 'libx264', '-preset', 'medium', '-crf', '18',
                           '-tune', 'stillimage', '-pix_fmt', 'yuv420p', '-c:a', 'aac', '-b:a', '192k', '-movflags', '+faststart',
                           '-shortest', ziel + '.mp4'], stdin=subprocess.PIPE)
    def lade(p):
        bild = Image.open(p).convert('RGB')
        return bild if bild.size == (BREITE, HOEHE) else bild.resize((BREITE, HOEHE), Image.LANCZOS)

    j, angezeigt, von, zu, wechsel = 0, None, None, None, -1.0
    roh = None
    for f in range(int(round(gesamt * FPS))):
        T = f / FPS
        while j < len(zustaende) and zustaende[j][0] <= T + 1e-9:
            von, zu, wechsel = angezeigt, lade(zustaende[j][1]), zustaende[j][0]
            j += 1
            roh = None
        if von is not None and T - wechsel < BLENDE:
            bild = Image.blend(von, zu, max(0.0, min(1.0, (T - wechsel) / BLENDE)))
            ff.stdin.write(bild.tobytes())
            angezeigt = bild
            roh = None
        else:
            if roh is None:
                angezeigt = zu
                roh = zu.tobytes()
            ff.stdin.write(roh)
    ff.stdin.close()
    if ff.wait() != 0:
        sys.exit('ffmpeg ist fehlgeschlagen.')

    # --- Untertitel ---
    cues = []
    for i, sz in enumerate(szenen):
        faktor = laenge[i] / sz['gesamt'] if sz['gesamt'] else 1
        woerter = [{'w': w['w'], 'start': start[i] + w['start'] * faktor} for w in sz['woerter']]
        cues += untertitel(woerter, start[i] + laenge[i])
    with open(ziel + '.vtt', 'w', encoding='utf-8') as v:
        v.write('WEBVTT\n\n')
        for n, (a, b, text) in enumerate(cues, 1):
            v.write(f'{n}\n{zeit_vtt(a)} --> {zeit_vtt(b)}\n{text}\n\n')

    # --- Kapitel (mindestens zehn Sekunden je Kapitel) ---
    kapitel = [[0.0 if i == 0 else start[i], sz['kapitel']] for i, sz in enumerate(szenen)]
    grenzen = [k[0] for k in kapitel[1:]] + [gesamt]
    zusammen = []
    for (a, name), b in zip(kapitel, grenzen):
        if zusammen and (zusammen[-1][2] - zusammen[-1][0]) < KAPITEL_GRENZE:
            zusammen[-1][2] = b  # das zu kurze vorige Kapitel reicht bis hierher
            continue
        zusammen.append([a, name, b])
    if len(zusammen) > 1 and zusammen[-1][2] - zusammen[-1][0] < KAPITEL_GRENZE:
        zusammen[-2][2] = zusammen[-1][2]
        zusammen.pop()
    kapiteltext = '\n'.join(f'{zeit_kurz(a)} {name}' for a, name, _ in zusammen)
    open(ziel + '-kapitel.txt', 'w', encoding='utf-8').write(kapiteltext + '\n')

    # --- Titel und Beschreibung ---
    n = daten['folge']
    titel = paket['titel']
    yt_titel = f'Open Data Wizard · {n} · {titel}'
    einleitung = szenen[0]['sprechtext'].split(':', 1)[-1].strip() if n else szenen[0]['sprechtext']
    beschreibung = f'''{yt_titel}

{einleitung}

Kapitel
{kapiteltext}

Open Data Wizard ist ein WordPress-Plugin, mit dem Organisationen ihre Datensätze nach DCAT-AP.de beschreiben und an Open-Data-Portale wie GovData liefern.

Plugin herunterladen: {REPO_URL}/releases
Anleitung (README): {REPO_URL}#readme
Feld-Referenz: {REPO_URL}/blob/main/docs/FELD-REFERENZ.md

Gezeigt wird Plugin-Version {paket.get("plugin_version", "")}.
Lizenz des Videos: CC BY 3.0 · Namensnennung: „Open Data Wizard“ ({REPO_URL})
'''
    open(ziel + '-beschreibung.txt', 'w', encoding='utf-8').write(beschreibung)

    # --- Vorschaubild: Titelfolie im fertigen Zustand ---
    erste = [p for zt, p in zustaende if zt < start[1]] if len(szenen) > 1 else [zustaende[-1][1]]
    Image.open(erste[-1]).convert('RGB').resize((1280, 720), Image.LANCZOS).save(ziel + '-vorschau.jpg', quality=90)

    print(f'{ziel}.mp4: {zeit_kurz(gesamt)} ({gesamt:.1f} s), {len(zustaende)} Bildzustände, {len(cues)} Untertitel, {len(zusammen)} Kapitel')
    if len(yt_titel) > 100:
        print(f'Achtung: Der Titel ist {len(yt_titel)} Zeichen lang; YouTube erlaubt 100.')


if __name__ == '__main__':
    main()
