#!/usr/bin/env python3
"""Compile a gettext .po file into a binary .mo file.

Standalone, dependency-free replacement for `msgfmt` (which is not available in
the build container). Handles the subset of the PO format this plugin uses:
single- and multi-line msgid/msgstr, C-style escapes, and the empty-msgid
header entry. Fuzzy/obsolete entries and plurals are not used by this plugin.

Usage:
    python3 bin/compile-mo.py languages/open-data-wizard-en_US.po \
                              languages/open-data-wizard-en_US.mo
"""
import struct
import sys


def unescape(value: str) -> str:
    """Turn a PO C-escaped string body into its literal value."""
    out = []
    i = 0
    while i < len(value):
        ch = value[i]
        if ch == '\\' and i + 1 < len(value):
            nxt = value[i + 1]
            out.append(
                {'n': '\n', 't': '\t', 'r': '\r', '"': '"', '\\': '\\'}.get(nxt, nxt)
            )
            i += 2
            continue
        out.append(ch)
        i += 1
    return ''.join(out)


def parse_po(path: str) -> dict:
    """Parse a .po file into an {msgid: msgstr} dict (both unescaped)."""
    entries = {}
    msgid = None
    msgstr = None
    target = None  # 'id' or 'str'

    def flush():
        if msgid is not None and msgstr is not None:
            entries[unescape(msgid)] = unescape(msgstr)

    with open(path, encoding='utf-8') as handle:
        for raw in handle:
            line = raw.rstrip('\n')
            stripped = line.strip()
            if stripped == '' or stripped.startswith('#'):
                continue
            if stripped.startswith('msgid '):
                flush()
                msgid = stripped[len('msgid '):].strip()[1:-1]
                msgstr = None
                target = 'id'
            elif stripped.startswith('msgstr '):
                msgstr = stripped[len('msgstr '):].strip()[1:-1]
                target = 'str'
            elif stripped.startswith('"') and stripped.endswith('"'):
                chunk = stripped[1:-1]
                if target == 'id':
                    msgid += chunk
                elif target == 'str':
                    msgstr += chunk
        flush()
    return entries


def compile_mo(entries: dict, path: str) -> int:
    """Write the {msgid: msgstr} dict as a binary .mo file. Returns bytes."""
    keys = sorted(entries.keys())
    offsets = []
    ids = b''
    strs = b''
    for key in keys:
        value = entries[key]
        kb = key.encode('utf-8')
        vb = value.encode('utf-8')
        offsets.append((len(ids), len(kb), len(strs), len(vb)))
        ids += kb + b'\x00'
        strs += vb + b'\x00'

    count = len(keys)
    key_start = 7 * 4 + 16 * count
    val_start = key_start + len(ids)

    out = bytearray()
    out += struct.pack('<I', 0x950412DE)  # magic
    out += struct.pack('<I', 0)           # revision
    out += struct.pack('<I', count)
    out += struct.pack('<I', 7 * 4)                    # offset of key table
    out += struct.pack('<I', 7 * 4 + 8 * count)        # offset of value table
    out += struct.pack('<I', 0)           # hash size
    out += struct.pack('<I', 0)           # hash offset

    for o_id, l_id, _o_str, _l_str in offsets:
        out += struct.pack('<II', l_id, key_start + o_id)
    for _o_id, _l_id, o_str, l_str in offsets:
        out += struct.pack('<II', l_str, val_start + o_str)

    out += ids
    out += strs

    with open(path, 'wb') as handle:
        handle.write(out)
    return len(out)


def main() -> int:
    if len(sys.argv) != 3:
        print('Usage: compile-mo.py <input.po> <output.mo>', file=sys.stderr)
        return 2
    entries = parse_po(sys.argv[1])
    size = compile_mo(entries, sys.argv[2])
    print(f'OK — {len(entries)} entries, {size} bytes → {sys.argv[2]}')
    return 0


if __name__ == '__main__':
    sys.exit(main())
