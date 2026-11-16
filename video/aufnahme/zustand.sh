#!/usr/bin/env bash
# Setzt das Aufnahme-WordPress auf einen Ausgangszustand der Drehbücher (Feld `umgebung`).
#
#   bash video/aufnahme/zustand.sh <umgebung>
#
# wordpress-ohne-plugin · wordpress-mit-quellcode-archiv · plugin-aktiv · plugin-eingerichtet ·
# uebungsdatensatz-entwurf · uebungsdatensatz-folge-4
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
BASE="${ODW_VIDEO_WP:-$REPO/video/ausgabe/wordpress}"
WP="php $BASE/wp-cli.phar --path=$BASE/wordpress --allow-root --quiet"
PLUG="$BASE/wordpress/wp-content/plugins"
DB="$(php -r 'echo include $argv[1];' "$BASE/wordpress/wp-content/database/db-path.php")"

cp "$BASE/basis.sqlite" "$DB"
rm -rf "$PLUG/open-data-wizard" "$PLUG/OpenDataWizard-main" "$BASE/wordpress/wp-content/uploads"

case "${1:?Ausgangszustand fehlt}" in
  wordpress-ohne-plugin) ;;
  wordpress-mit-quellcode-archiv)
    # Wie der Download über den grünen „Code"-Button: anderer Ordnername, ohne vendor/.
    ( cd "$REPO" && git archive --format=tar --prefix=OpenDataWizard-main/ HEAD ) | tar -x -C "$PLUG"
    $WP plugin activate OpenDataWizard-main ;;
  plugin-aktiv|plugin-eingerichtet|uebungsdatensatz-entwurf|uebungsdatensatz-folge-4)
    unzip -q "$(ls "$REPO"/dist/open-data-wizard-*.zip | tail -1)" -d "$PLUG"   # das echte Release-Paket
    $WP plugin activate open-data-wizard
    if [ "$1" != plugin-aktiv ]; then   # Einstellungen wie am Ende von Folge 2
      $WP option update odw_settings --format=json '{"default_publisher":"Förderverein Musterstadt e. V.","default_license":"https://creativecommons.org/licenses/by/4.0/","default_language":"http://publications.europa.eu/resource/authority/language/DEU","catalog_description":"Offene Daten des Fördervereins Musterstadt e. V."}'
    fi
    uebung() { python3 -c 'import yaml,json,sys; print(json.dumps(yaml.safe_load(open(sys.argv[1],encoding="utf-8"))["uebungsdatensatz"],ensure_ascii=False))' "$REPO/video/folgen/$1"; }
    if [ "$1" = uebungsdatensatz-entwurf ]; then   # dazu der Entwurf vom Ende von Folge 3
      $WP eval-file "$REPO/video/aufnahme/uebungsdatensatz.php" "$(uebung 03-formular-grundlegendes.yml)"
    fi
    if [ "$1" = uebungsdatensatz-folge-4 ]; then   # der Entwurf vom Ende von Folge 4, mit Datei
      anhang="$(php "$BASE/wp-cli.phar" --path="$BASE/wordpress" --allow-root media import "$REPO/video/material/foerderverein-mitglieder.csv" --porcelain)"
      $WP eval-file "$REPO/video/aufnahme/uebungsdatensatz.php" "$(uebung 03-formular-grundlegendes.yml)" "$anhang" "$(uebung 04-formular-datenbereitstellung.yml)"
    fi ;;
  *) echo "Unbekannter Ausgangszustand: $1" >&2; exit 1 ;;
esac
echo "Ausgangszustand: $1"
