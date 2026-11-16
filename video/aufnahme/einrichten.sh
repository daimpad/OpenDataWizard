#!/usr/bin/env bash
# Richtet ein deutsches WordPress mit SQLite für die Aufnahmen ein — ohne Docker und ohne MySQL.
# Braucht: PHP 8.1+ mit pdo_sqlite, curl, unzip, composer (für das Release-ZIP), Python 3.
#
#   bash video/aufnahme/einrichten.sh
#
# Arbeitsordner: $ODW_VIDEO_WP (Standard video/ausgabe/wordpress). Danach starte-server.sh.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
BASE="${ODW_VIDEO_WP:-$REPO/video/ausgabe/wordpress}"
mkdir -p "$BASE/ini"
cd "$BASE"
WP="php $BASE/wp-cli.phar --path=$BASE/wordpress --allow-root --quiet"

echo "== Release-ZIP"
ls "$REPO"/dist/open-data-wizard-*.zip >/dev/null 2>&1 || ( cd "$REPO" && bash bin/build-release.sh )

echo "== WordPress, SQLite-Anbindung, WP-CLI"
[ -f wp.zip ] || curl -sS -o wp.zip https://de.wordpress.org/latest-de_DE.zip
[ -d wordpress ] || unzip -q wp.zip
[ -f sqlite.zip ] || curl -sS -o sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.zip
[ -d wordpress/wp-content/plugins/sqlite-database-integration ] || unzip -q sqlite.zip -d wordpress/wp-content/plugins/
sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$BASE/wordpress/wp-content/plugins/sqlite-database-integration#" \
    -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
    wordpress/wp-content/plugins/sqlite-database-integration/db.copy > wordpress/wp-content/db.php
[ -f wp-cli.phar ] || curl -sS -o wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
printf 'upload_max_filesize=64M\npost_max_size=64M\nmemory_limit=512M\nmax_execution_time=120\n' > ini/aufnahme.ini

echo "== Installation"
# Wiederholter Lauf: vom gesicherten Ausgangszustand ausgehen, nicht vom Stand der letzten Aufnahme.
if [ -f basis.sqlite ] && [ -f wordpress/wp-content/database/db-path.php ]; then
  cp basis.sqlite "$(php -r 'echo include $argv[1];' wordpress/wp-content/database/db-path.php)"
fi
[ -f wordpress/wp-config.php ] || $WP config create --dbname=wp --dbuser=wp --dbpass=wp --dbhost=localhost --locale=de_DE --skip-check
$WP core is-installed 2>/dev/null || $WP core install --url=http://127.0.0.1:8888 --title="Förderverein Musterstadt" \
  --admin_user=redaktion --admin_password=vorschau --admin_email=redaktion@example.org --skip-email --locale=de_DE
# Adresse ausdrücklich setzen: Mit absolutem --path errät die Installation sonst „…/wordpress".
$WP option update siteurl http://127.0.0.1:8888
$WP option update home http://127.0.0.1:8888
$WP rewrite structure '/%postname%/'
$WP plugin delete akismet hello 2>/dev/null || true
$WP user meta update 1 wp_persisted_preferences --format=json \
  '{"core/edit-post":{"welcomeGuide":false},"core":{"enableChoosePatternModal":false},"_modified":"2026-01-01T00:00:00.000Z"}'

# Spuren der SQLite-Anbindung aus Pluginliste und Werkzeugleiste ausblenden.
mkdir -p wordpress/wp-content/mu-plugins
cat > wordpress/wp-content/mu-plugins/aufnahme.php <<'PHP'
<?php
/* Nur für die Video-Aufnahmen. */
add_filter( 'all_plugins', function ( $p ) { unset( $p['sqlite-database-integration/load.php'] ); return $p; } );
add_action( 'wp_before_admin_bar_render', function () { global $wp_admin_bar; $wp_admin_bar->remove_node( 'sqlite-db-integration' ); } );
PHP

echo "== Ausgangszustand sichern"
DB="$(php -r 'echo include $argv[1];' wordpress/wp-content/database/db-path.php)"
cp "$DB" basis.sqlite
echo "Fertig. Server starten: bash video/aufnahme/starte-server.sh"
