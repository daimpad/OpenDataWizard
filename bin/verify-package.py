#!/usr/bin/env python3
"""Prüft das gebaute Release-Paket gegen das, was der Code zur Laufzeit braucht.

Hintergrund: PHPCS, PHPStan, PHPUnit und die SHACL-Prüfung laufen alle gegen das
Repository — das ausgelieferte Paket sieht keine von ihnen an. Eine vergessene
Zeile in der Allowlist von `bin/build-release.sh` erzeugt daher ein ZIP, das in
einer grünen CI entsteht und trotzdem in einer echten WordPress-Installation
Fehler wirft. Genau das ist beim Gutenberg-Block (v2.38.0) passiert, wo `blocks/`
zunächst fehlte, und nur beim manuellen Entpacken aufgefallen.

Geprüft wird:

1. **Laufzeit-Pfade.** Jeder Pfad, den der Code über die Konstanten
   `ODW_PLUGIN_DIR` / `ODW_PLUGIN_URL` zusammensetzt, muss im Paket liegen. Die
   Liste wird aus den PHP-Dateien *im Paket selbst* gelesen, ist also nie
   veraltet — sie wächst automatisch mit, sobald jemand eine Datei einbindet.
2. **Bootstrap-Dateien**, die kein solcher Ausdruck erwähnt: der Composer-
   Autoloader, die `block.json` des Blocks und die kompilierte `.mo`.
3. **Versionsgleichheit** zwischen Plugin-Header, `ODW_VERSION` und dem obersten
   CHANGELOG-Eintrag. Diese drei werden von Hand gepflegt; weichen sie ab, zeigt
   WordPress die falsche Version an und der Updater vergleicht gegen den
   falschen Stand.

Aufruf (ZIP oder bereits entpacktes Plugin-Verzeichnis):

    python3 bin/verify-package.py dist/open-data-wizard-2.40.0.zip
    python3 bin/verify-package.py /tmp/paket/open-data-wizard

Exit-Code 0, wenn alles stimmt, sonst 1 samt Liste der Befunde.
"""

import glob
import os
import re
import sys
import tempfile
import zipfile

# Dateien, die kein `ODW_PLUGIN_DIR . '…'`-Ausdruck nennt, ohne die das Plugin
# aber nicht läuft — jeweils mit dem Grund, warum sie hier stehen.
BOOTSTRAP_FILES = {
    'vendor/autoload.php': 'Ohne Autoloader bricht odw_bootstrap() mit einer Admin-Notice ab.',
    'blocks/dataset-card/block.json': 'register_block_type() liest die Blockdefinition aus diesem Verzeichnis.',
    'languages/open-data-wizard-en_US.mo': 'Die .po allein wird von WordPress nicht gelesen.',
}

# Pfade, die der Code zwar bildet, die im Paket aber bewusst fehlen dürfen.
KNOWN_ABSENT = {
    'docs/FELD-REFERENZ.md': (
        'Wird von `wp open-data-wizard docs` geschrieben, nicht gelesen — '
        'ODW_Field_Reference::write() legt das Verzeichnis selbst an.'
    ),
}

REFERENCE_RE = re.compile(r"ODW_PLUGIN_(?:DIR|URL)\s*\.\s*'([^']+)'")


def collect_references(plugin_dir):
    """Sammelt alle über die Plugin-Konstanten gebildeten Pfade aus dem Paket."""
    paths = set()
    sources = [os.path.join(plugin_dir, 'open-data-wizard.php')]
    sources += sorted(glob.glob(os.path.join(plugin_dir, 'includes', '*.php')))

    for source in sources:
        if not os.path.isfile(source):
            continue
        with open(source, encoding='utf-8') as handle:
            for match in REFERENCE_RE.finditer(handle.read()):
                paths.add(match.group(1))

    return sorted(paths)


def check_reference(plugin_dir, reference):
    """Prüft einen einzelnen Laufzeit-Pfad. Gibt None zurück oder einen Befund.

    Drei Formen kommen vor, weil PHP die Konstante mit Variablen weiterbaut:
    ein vollständiger Pfad, ein Verzeichnispräfix (endet auf `/`) und ein
    Dateipräfix ohne Endung (endet auf `.`, z. B. `samples/import-example.`).
    """
    target = os.path.join(plugin_dir, reference)

    if reference.endswith('/'):
        if not os.path.isdir(target):
            return 'Verzeichnis fehlt im Paket'
        return None

    if reference.endswith('.'):
        if not glob.glob(target + '*'):
            return 'keine Datei mit diesem Präfix im Paket'
        return None

    if not os.path.exists(target):
        return 'fehlt im Paket'

    return None


def check_versions(repo_root):
    """Vergleicht Plugin-Header, ODW_VERSION und den obersten CHANGELOG-Eintrag."""
    findings = []
    main_file = os.path.join(repo_root, 'open-data-wizard.php')
    changelog = os.path.join(repo_root, 'CHANGELOG.md')

    with open(main_file, encoding='utf-8') as handle:
        source = handle.read()

    header = re.search(r'^\s*\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)', source, re.M)
    constant = re.search(r"define\(\s*'ODW_VERSION',\s*'([0-9]+\.[0-9]+\.[0-9]+)'", source)

    if header is None:
        findings.append('Plugin-Header: keine Version gefunden')
    if constant is None:
        findings.append('ODW_VERSION: nicht gefunden')

    entry = None
    if os.path.isfile(changelog):
        with open(changelog, encoding='utf-8') as handle:
            entry = re.search(r'^## \[([0-9]+\.[0-9]+\.[0-9]+)\]', handle.read(), re.M)
    if entry is None:
        findings.append('CHANGELOG.md: kein Versionseintrag gefunden')

    if findings:
        return findings

    versions = {
        'Plugin-Header': header.group(1),
        'ODW_VERSION': constant.group(1),
        'CHANGELOG.md': entry.group(1),
    }

    if len(set(versions.values())) > 1:
        findings.append(
            'Versionen weichen ab: '
            + ', '.join('%s = %s' % (name, value) for name, value in versions.items())
        )

    return findings


def resolve_plugin_dir(argument, workspace):
    """Nimmt ein ZIP oder ein Verzeichnis entgegen und liefert das Plugin-Verzeichnis."""
    if os.path.isdir(argument):
        return argument

    if not zipfile.is_zipfile(argument):
        print('Fehler: %s ist weder ein Verzeichnis noch ein ZIP-Archiv.' % argument, file=sys.stderr)
        return None

    with zipfile.ZipFile(argument) as archive:
        archive.extractall(workspace)

    unpacked = os.path.join(workspace, 'open-data-wizard')
    if not os.path.isdir(unpacked):
        print('Fehler: Das Archiv enthält kein Verzeichnis „open-data-wizard".', file=sys.stderr)
        return None

    return unpacked


def main():
    if len(sys.argv) != 2:
        print(__doc__.strip(), file=sys.stderr)
        return 2

    repo_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

    with tempfile.TemporaryDirectory() as workspace:
        plugin_dir = resolve_plugin_dir(sys.argv[1], workspace)
        if plugin_dir is None:
            return 1

        findings = []

        references = collect_references(plugin_dir)
        if not references:
            findings.append(
                'Keine Laufzeit-Pfade gefunden — enthält das Paket überhaupt die PHP-Quellen?'
            )

        skipped = 0
        for reference in references:
            if reference in KNOWN_ABSENT:
                skipped += 1
                continue
            problem = check_reference(plugin_dir, reference)
            if problem is not None:
                findings.append('%s — %s' % (reference, problem))

        for path, reason in sorted(BOOTSTRAP_FILES.items()):
            if not os.path.exists(os.path.join(plugin_dir, path)):
                findings.append('%s — fehlt im Paket. %s' % (path, reason))

        # Getrennt gehalten, damit der Hinweis am Ende zum Befund passt: Ein
        # fehlender Pfad gehört in die Allowlist, eine abweichende Version nicht.
        path_findings = list(findings)
        version_findings = check_versions(repo_root)
        findings += version_findings

        print('Laufzeit-Pfade: %d geprüft, %d bewusst ausgenommen'
              % (len(references) - skipped, skipped))

        if findings:
            print('\nBefunde: %d' % len(findings))
            for finding in findings:
                print('  - %s' % finding)
            print('')
            if path_findings:
                print('Fehlende Pfade gehören in die Allowlist in bin/build-release.sh.')
            if version_findings:
                print('Version an drei Stellen gleichhalten: Plugin-Header, '
                      'ODW_VERSION, oberster CHANGELOG-Eintrag.')
            return 1

        print('OK — das Paket enthält alles, was der Code zur Laufzeit anfasst.')
        return 0


if __name__ == '__main__':
    sys.exit(main())
