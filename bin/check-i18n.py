#!/usr/bin/env python3
"""Prüft, ob alle übersetzbaren Zeichenketten im Katalog stehen.

Extrahiert jede Zeichenkette, die im PHP-Quellcode durch eine der
i18n-Funktionen mit der Textdomain `open-data-wizard` läuft, und vergleicht sie
gegen die msgid-Einträge von `languages/open-data-wizard-en_US.po`.

Hintergrund: Die Quellsprache des Plugins ist Deutsch, die englische Fassung
überschreibt sie auf en_US-Installationen. Eine beim Umformulieren vergessene
Übersetzung fällt dort sonst erst auf, wenn mitten im englischen Backend ein
deutscher Satz steht.

Aufruf aus dem Projektverzeichnis:

    python3 bin/check-i18n.py

Exit-Code 0, wenn nichts fehlt, sonst 1 samt Liste der fehlenden Einträge.
"""

import glob
import io
import os
import re
import sys

FUNCS = r"(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)"
# Einfache Anführungszeichen: kein Escaping von \n nötig, PHP nimmt sie wörtlich.
PAT_SQ = re.compile(FUNCS + r"\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'open-data-wizard'\s*\)")
# Doppelte Anführungszeichen: PHP interpretiert \n und \t.
PAT_DQ = re.compile(FUNCS + r'\(\s*"((?:[^"\\]|\\.)*)"\s*,\s*\'open-data-wizard\'\s*\)')

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def source_strings():
    """Alle übersetzbaren Zeichenketten aus dem Plugin-Quellcode."""
    found = set()
    files = [os.path.join(ROOT, 'open-data-wizard.php')]
    files += sorted(glob.glob(os.path.join(ROOT, 'includes', '*.php')))

    for path in files:
        src = io.open(path, encoding='utf-8').read()
        for match in PAT_SQ.finditer(src):
            found.add(match.group(1).replace("\\'", "'").replace('\\\\', '\\'))
        for match in PAT_DQ.finditer(src):
            value = match.group(1)
            value = value.replace('\\n', '\n').replace('\\t', '\t').replace('\\"', '"')
            found.add(value)

    found.discard('')
    return found


def catalog_strings(po_path):
    """Alle msgid-Einträge der Übersetzungsdatei."""
    text = io.open(po_path, encoding='utf-8').read()
    entries = set()
    for match in re.finditer(r'^msgid "((?:[^"\\]|\\.)*)"$', text, re.M):
        value = match.group(1)
        value = value.replace('\\"', '"').replace('\\n', '\n')
        value = value.replace('\\t', '\t').replace('\\\\', '\\')
        entries.add(value)
    return entries


def main():
    po_path = os.path.join(ROOT, 'languages', 'open-data-wizard-en_US.po')
    if not os.path.isfile(po_path):
        sys.stderr.write('Übersetzungsdatei nicht gefunden: %s\n' % po_path)
        return 1

    source = source_strings()
    catalog = catalog_strings(po_path)
    missing = sorted(s for s in source if s not in catalog)

    print('Quelle: %d Zeichenketten, Katalog: %d Einträge' % (len(source), len(catalog)))

    if not missing:
        print('OK — keine fehlenden Übersetzungen.')
        return 0

    print('Fehlend: %d' % len(missing))
    for value in missing:
        preview = value.replace('\n', '\\n')
        if len(preview) > 120:
            preview = preview[:120] + '…'
        print('  - ' + preview)
    print('\nBitte in .pot und .po ergänzen, danach:')
    print('  python3 bin/compile-mo.py languages/open-data-wizard-en_US.po '
          'languages/open-data-wizard-en_US.mo')
    return 1


if __name__ == '__main__':
    sys.exit(main())
