#!/usr/bin/env bash
#
# build-release.sh — erzeugt ein installationsfertiges WordPress-Plugin-ZIP.
#
# Enthält ausschließlich die zur Laufzeit benötigten Dateien:
#   - Plugin-Bootstrap, Klassen, Assets, Blöcke, Sprachen, Beispiel-Dateien
#   - config/ (nur Datendateien; Dev-Configs und SHACL-Referenz entfallen)
#   - vendor/ mit ausschließlich der Produktionsabhängigkeit (Carbon Fields),
#     via `composer install --no-dev`, inkl. Bereinigung der Carbon-Fields-Dev-Dateien
#
# Nicht enthalten: tests/, docs/, .github/, .git/, Dev-Configs, composer.json/lock,
# node/Playwright, interne Doku (CLAUDE.md, TECHNICAL-SPEC.md, …).
#
# Aufruf:   bin/build-release.sh
# Ergebnis: dist/open-data-wizard-<version>.zip
#
set -euo pipefail

SLUG="open-data-wizard"

# --- Pfade -----------------------------------------------------------------
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$( cd "$SCRIPT_DIR/.." && pwd )"
DIST_DIR="$ROOT_DIR/dist"
BUILD_DIR="$DIST_DIR/build"
PLUG_DIR="$BUILD_DIR/$SLUG"

# --- Voraussetzungen -------------------------------------------------------
command -v composer >/dev/null 2>&1 || { echo "Fehler: composer nicht gefunden." >&2; exit 1; }
command -v zip >/dev/null 2>&1      || { echo "Fehler: zip nicht gefunden." >&2; exit 1; }

# --- Version aus dem Plugin-Header lesen -----------------------------------
VERSION="$( grep -oE "define\( *'ODW_VERSION', *'[^']+'" "$ROOT_DIR/$SLUG.php" | grep -oE "[0-9]+\.[0-9]+\.[0-9]+" | head -1 )"
if [ -z "$VERSION" ]; then
	echo "Fehler: Version (ODW_VERSION) konnte nicht ermittelt werden." >&2
	exit 1
fi
echo "→ Baue $SLUG Version $VERSION"

# --- Staging vorbereiten ---------------------------------------------------
rm -rf "$BUILD_DIR"
mkdir -p "$PLUG_DIR"

# --- Runtime-Quelldateien kopieren (Allowlist) -----------------------------
cp "$ROOT_DIR/$SLUG.php" "$ROOT_DIR/uninstall.php" "$ROOT_DIR/README.md" "$ROOT_DIR/LICENSE" "$PLUG_DIR/"
cp -R "$ROOT_DIR/includes" "$ROOT_DIR/assets" "$ROOT_DIR/blocks" "$ROOT_DIR/languages" "$ROOT_DIR/samples" "$PLUG_DIR/"

# config/ vollständig kopieren, dann Dev-/Referenz-Anteile entfernen
cp -R "$ROOT_DIR/config" "$PLUG_DIR/"
rm -f  "$PLUG_DIR/config/phpcs.xml" "$PLUG_DIR/config/phpstan.neon" "$PLUG_DIR/config/phpunit.xml"
rm -rf "$PLUG_DIR/config/shacl"

# --- Produktionsabhängigkeiten (nur Carbon Fields) -------------------------
cp "$ROOT_DIR/composer.json" "$ROOT_DIR/composer.lock" "$PLUG_DIR/"
(
	cd "$PLUG_DIR"
	COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
)
# composer-Manifeste zur Laufzeit nicht nötig (Autoloader ist generiert)
rm -f "$PLUG_DIR/composer.json" "$PLUG_DIR/composer.lock"

# --- Carbon Fields entschlacken (kompilierte build/-Assets bleiben!) -------
CF_DIR="$PLUG_DIR/vendor/htmlburger/carbon-fields"
if [ -d "$CF_DIR" ]; then
	rm -rf "$CF_DIR/.git" "$CF_DIR/tests" "$CF_DIR/bin" "$CF_DIR/packages" "$CF_DIR/.phpstorm.meta.php"
	# Dev-/Lint-/CI-Konfiguration und Doku (explizit, um kein .*-Globbing zu riskieren)
	rm -f "$CF_DIR/.babelrc.js" "$CF_DIR/.browserlistrc" "$CF_DIR/.editorconfig" \
	      "$CF_DIR/.eslintrc.js" "$CF_DIR/.gitattributes" "$CF_DIR/.gitignore" \
	      "$CF_DIR/.huskyrc.js" "$CF_DIR/.lintstagedrc.js" "$CF_DIR/.nvmrc" \
	      "$CF_DIR/.postcssrc.js" "$CF_DIR/.scrutinizer.yml" "$CF_DIR/.travis.yml"
	rm -f "$CF_DIR/CONTRIBUTING.md" "$CF_DIR/DEVELOPMENT.md" "$CF_DIR/ISSUE_TEMPLATE.md" \
	      "$CF_DIR/README.md" "$CF_DIR/Vagrantfile" "$CF_DIR/phpcs.xml" "$CF_DIR/phpunit.xml" \
	      "$CF_DIR/webpack.config.js" "$CF_DIR/yarn.lock" "$CF_DIR/package.json"
fi

# --- Aufräumen: OS-Artefakte -----------------------------------------------
find "$PLUG_DIR" -name '.DS_Store' -delete 2>/dev/null || true

# --- ZIP erstellen ---------------------------------------------------------
ZIP_PATH="$DIST_DIR/$SLUG-$VERSION.zip"
rm -f "$ZIP_PATH"
( cd "$BUILD_DIR" && zip -rqX "$ZIP_PATH" "$SLUG" )

# --- Bereinigen & Zusammenfassung ------------------------------------------
rm -rf "$BUILD_DIR"
SIZE="$( du -h "$ZIP_PATH" | cut -f1 )"
echo "✓ Fertig: dist/$SLUG-$VERSION.zip ($SIZE)"
echo "  Installation: WordPress → Plugins → Installieren → Plugin hochladen"
