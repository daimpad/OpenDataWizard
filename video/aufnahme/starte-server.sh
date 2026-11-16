#!/usr/bin/env bash
# Startet das Aufnahme-WordPress auf http://127.0.0.1:8888 (PHP-Entwicklungsserver über WP-CLI).
# Nach einem Wechsel zwischen Plugin-Ordnern den Server neu starten (PHP merkt sich Dateipfade).
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
BASE="${ODW_VIDEO_WP:-$REPO/video/ausgabe/wordpress}"
# Mehrere Worker: „Endpunkt prüfen" (Folge 9) ruft die eigene Seite ab, während die Anfrage noch läuft.
PHP_CLI_SERVER_WORKERS=4 PHP_INI_SCAN_DIR=":$BASE/ini" exec php "$BASE/wp-cli.phar" server --host=127.0.0.1 --port=8888 --path="$BASE/wordpress" --allow-root
