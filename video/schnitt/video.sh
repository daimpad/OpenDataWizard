#!/usr/bin/env bash
# Erzeugt das Video einer Folge aus der fertigen Klickstrecke (Bilder, Folien, Ton).
#
#   bash video/schnitt/video.sh <folge> [gestaltung]        # gestaltung: odw (Standard) oder datenatlas
#
# Voraussetzung: video/ausgabe/odw-tutorials ist mit Ton gebaut
# (python3 video/klickstrecke/baue-paket.py --aufnahme video/ausgabe/aufnahme --ton video/ausgabe/ton).
# Ergebnis in video/ausgabe/videos/: folge-<n>-<gestaltung>.mp4, .vtt, -kapitel.txt,
# -beschreibung.txt, -vorschau.jpg
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
FOLGE="${1:?Folge fehlt}"
GESTALTUNG="${2:-odw}"
ARBEIT="$REPO/video/ausgabe/schnitt/folge-$FOLGE-$GESTALTUNG"
rm -rf "$ARBEIT"
node "$REPO/video/schnitt/zustaende.js" "$FOLGE" "$GESTALTUNG" "$ARBEIT"
python3 "$REPO/video/schnitt/montiere.py" "$ARBEIT" "$REPO/video/ausgabe/ton/$FOLGE" "$REPO/video/ausgabe/videos/folge-$FOLGE-$GESTALTUNG"
