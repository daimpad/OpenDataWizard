/*
 * <odw-klickstrecke> — interaktive Klickstrecke zu einer Folge der Open-Data-Wizard-Videoreihe.
 *
 *   <odw-klickstrecke src="folge-0/daten.json" gestaltung="odw"></odw-klickstrecke>
 *   <script type="module" src="odw-klickstrecke.js"></script>
 *
 * Attribute
 *   src          Pfad zur daten.json einer Folge (relativ zur Seite). Bilder und Ton liegen relativ dazu.
 *   gestaltung   Name einer Gestaltungsvariante (Datei gestaltung/<name>.json neben diesem Skript),
 *                Standard: „odw".
 *
 * Keine Abhängigkeiten, keine externen Dienste, keine Cookies. Schriften werden aus schriften/ neben
 * diesem Skript geladen. Shadow DOM hält die Stile der einbindenden Seite (TYPO3, WordPress) fern.
 * Quelle: video/klickstrecke/ im Repository daimpad/OpenDataWizard (GPL-2.0-or-later).
 */
const BASIS = new URL('.', import.meta.url);
const BW = 1600, BH = 900; // Größe der Bildschirmfotos
const geladeneSchriften = new Set();

const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const tokens = (w) => w.match(/[A-Za-z0-9_ÄÖÜäöüßé-]+/g) || [];
const mmss = (s) => `${Math.floor(s / 60)}:${String(Math.floor(s % 60)).padStart(2, '0')}`;

function ladeSchriften(g) {
  for (const rolle of Object.values(g.schriften || {})) {
    for (const w of rolle.schnitte) {
      const schluessel = `${rolle.paket}-${w}`;
      if (geladeneSchriften.has(schluessel)) continue;
      geladeneSchriften.add(schluessel);
      const url = new URL(`schriften/${rolle.paket}-latin-${w}-normal.woff2`, BASIS).href;
      // @font-face wirkt nur im Dokument, nicht im Shadow DOM — daher hier am Dokument anmelden.
      const f = new FontFace(rolle.familie, `url(${url}) format("woff2")`, { weight: String(w), style: 'normal', display: 'swap' });
      document.fonts.add(f);
      f.load().catch(() => { /* Ersatzschrift aus dem Stapel greift */ });
    }
  }
}

function gestaltungsCss(g) {
  const v = [];
  for (const [k, x] of Object.entries(g.folie)) v.push(`--f-${k}:${x}`);
  for (const [k, x] of Object.entries(g.bedienung)) v.push(`--b-${k}:${x}`);
  const fam = (r, ersatz) => `"${g.schriften[r].familie}", ${ersatz}`;
  v.push(`--s-anzeige:${fam('anzeige', 'system-ui, sans-serif')}`, `--s-text:${fam('text', 'system-ui, -apple-system, "Segoe UI", sans-serif')}`, `--s-mono:${fam('mono', 'ui-monospace, Menlo, Consolas, monospace')}`);
  return `:host{${v.join(';')}}`;
}

// ---------------------------------------------------------------------------------------------
// Zeitplan: geschätzte Wortzeiten (130 Wörter/min, Pausen an Satzzeichen). Mit Ton werden sie auf
// dessen Länge gestreckt; liefert die daten.json Wortzeiten (ElevenLabs), gelten diese.
function zeitplan(sz, tonDauer) {
  const woerter = sz.sprechtext.split(/\s+/).filter(Boolean);
  let z = 0;
  let liste = woerter.map((w) => {
    const start = z; let dauer = 60 / 130;
    if (/[.?!:]["“”„«»]?$/.test(w)) dauer += 0.35; else if (/[,;]["“”„«»]?$/.test(w)) dauer += 0.15;
    z += dauer; return { w, start, tok: tokens(w) };
  });
  let gesamt = z + 0.8;
  if (Array.isArray(sz.wortzeiten) && sz.wortzeiten.length === liste.length) {
    liste = liste.map((x, i) => ({ ...x, start: sz.wortzeiten[i] }));
    gesamt = tonDauer || (sz.wortzeiten[sz.wortzeiten.length - 1] + 1);
  } else if (tonDauer) {
    const f = tonDauer / gesamt; liste = liste.map((x) => ({ ...x, start: x.start * f })); gesamt = tonDauer;
  }
  const wann = (bei) => { if (!bei) return null; const x = liste.find((e) => e.tok.includes(bei)); return x ? x.start : null; };
  let vorher = 0;
  const bilder = (sz.bilder || []).map((b, i) => {
    let zt = b.bei ? wann(b.bei) : null;
    if (zt === null) zt = i === 0 ? 0 : vorher + 0.6;
    zt = Math.max(zt, i === 0 ? 0 : vorher + 0.3); vorher = zt; return { ...b, zeit: zt };
  });
  let mi = 0;
  const einbl = (sz.einblendung || []).map((e) => {
    const m = e.markiere ? (sz.markierungen || [])[mi++] : null;
    return { ...e, zeit: wann(e.bei) ?? 0, box: m ? m.box : null };
  });
  return { liste, gesamt, wann, bilder, einbl };
}

// ---------------------------------------------------------------------------------------------
// Folien. Farben ausschließlich über Klassen und Variablen der Gestaltung.
const REIHE = ['Quick-Start: In fünf Minuten zum ersten offenen Datensatz', 'Warum Metadaten? DCAT-AP und Harvesting', 'Installation, Updates, Einstellungen, Beispieldaten',
  'Das Formular I: Grundlegendes und Sprache', 'Das Formular II: Datenbereitstellung', 'Das Formular III: Erweiterte Angaben', 'Prüfen, Veröffentlichen, Qualität',
  'Viele Datensätze auf einmal: Batch-Import', 'Auf der eigenen Website: Karte und Datenkatalog', 'Anbindung an Open-Data-Portale', 'Für Administrator:innen'];

function marke(g) {
  // Breite Wortmarken (Seitenverhältnis über 2:1) bekommen mehr Platz als ein Bildzeichen.
  return g.logo ? `<img class="f-logo${g.logo_breit ? ' breit' : ''}" src="${esc(new URL('gestaltung/' + g.logo, BASIS).href)}" alt="${esc(g.marke)}">` : `<span class="f-wortmarke">${esc(g.marke)}</span>`;
}
function titelFolie(f, g) {
  const [a, b] = f.folge === 0 ? ['Quick-Start', f.titel.replace(/^Quick-Start:\s*/, '')] : [`Folge ${f.folge}`, f.titel];
  return `<div class="folie"><div class="f-titel">${marke(g)}<div class="f-eyebrow">${esc(a)}</div><h2>${esc(b)}</h2><p>Open Data Wizard · Videoreihe</p></div></div>`;
}
function abspannFolie(f, g) {
  const n = f.folge + 1;
  const weiter = f.folge === 0 ? 'Im Detail ab Folge 1: Warum Metadaten?' : `Als Nächstes · Folge ${n}: ${REIHE[n] || ''}`;
  return `<div class="folie"><div class="f-titel">${marke(g)}<div class="f-eyebrow">Wie es weitergeht</div><h2>${esc(weiter)}</h2></div>
    <div class="f-fuss"><span>Plugin-Version ${esc(f.plugin_version)}</span><span>${esc(g.namensnennung)}</span></div></div>`;
}
const PFEIL = (id) => `<defs><marker id="${id}" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto"><path d="M0,0 L10,5 L0,10 z" class="c-akzent"/></marker></defs>`;
const FOLIEN = {
  '0/Q2': () => `<div class="folie"><div class="f-kopf">Das Prinzip</div><h3 class="f-h">Beschreiben, bereitstellen, abholen lassen</h3>
    <div class="f-drei">
      <div class="f-schritt" data-bei="Formular"><span class="n">1</span><b>Formular ausfüllen</b><span>in WordPress, in Alltagssprache</span></div><div class="f-pfeil" data-bei="DCAT-AP">→</div>
      <div class="f-schritt" data-bei="DCAT-AP"><span class="n">2</span><b>DCAT-AP-Beschreibung</b><span>maschinenlesbar, nach europäischem Standard</span></div><div class="f-pfeil" data-bei="GovData">→</div>
      <div class="f-schritt" data-bei="GovData"><span class="n">3</span><b>Portale holen ab</b><span>z. B. GovData, regelmäßig und selbsttätig</span></div>
    </div><div class="f-band" data-bei="Wichtig">Die Datei bleibt auf Ihrer Website. Nur die Beschreibung wandert.</div></div>`,
  '0/Q3': () => `<div class="folie"><div class="f-kopf">Das richtige Paket</div>
    <div class="f-zwei" style="grid-template-columns:1.4fr 1fr;margin-top:1cqw">
      <div class="f-panel"><h3 style="font-size:2.6cqw">Open Data Wizard 2.47.5</h3><div class="f-klein">Releases · Assets</div>
        <div class="f-asset gut" data-bei="Datei"><span>open-data-wizard-2.47.5.zip</span><span class="f-ja">✓ dieses</span></div>
        <div class="f-asset schlecht"><span>Source code (zip)</span></div><div class="f-asset schlecht"><span>Source code (tar.gz)</span></div></div>
      <div class="f-stapel" data-bei="Achtung"><span class="f-code">&lt;&gt; Code ▾</span><span class="f-satz">Unvollständig: Damit meldet das Plugin „Installation unvollständig“.</span></div>
    </div></div>`,
  '1/1.1': () => `<div class="folie"><div class="f-regal">
      <div><div class="f-ueber">Die Daten</div><div class="f-buecher">${[18, 22, 16, 24, 20, 23, 17, 21].map((h, i) => `<i style="height:${h}cqw"${i === 4 ? ' class="b" data-z="Buch"' : ''}></i>`).join('')}</div></div>
      <div><div class="f-ueber" data-z="Beschriftung Metadaten">Metadaten</div><div class="f-karte" data-z="Karteikarte">
        <div class="zeile" data-z="Zeile Titel"><b>Titel</b><span>Mitgliederzahlen 2015–2025</span></div>
        <div class="zeile" data-z="Zeile Herausgeber"><b>Herausgeber</b><span>Förderverein Musterstadt e. V.</span></div>
        <div class="zeile" data-z="Zeile Inhalt"><b>Inhalt</b><span>Mitglieder, Eintritte, Austritte</span></div>
        <div class="zeile" data-z="Zeile Standort"><b>Standort</b><span>Regal 3, Fach B</span></div>
        <div class="zeile" data-z="Zeile Ausleihe"><b>Ausleihe</b><span>frei, mit Quellenangabe</span></div></div></div></div></div>`,
  '1/1.2': () => `<div class="folie"><div class="f-regal" style="grid-template-columns:1fr 1.25fr">
      <div class="f-etiketten"><div class="f-etikett" data-z="Etikett DCAT-AP"><b>DCAT-AP</b><span>Europa · welche Angaben es gibt und wie sie heißen</span></div>
        <div class="f-etikett" data-z="Etikett DCAT-AP.de"><b>DCAT-AP.de</b><span>Deutschland · z. B. amtliche Gebietsschlüssel</span></div></div>
      <div><div class="f-ueber">Gemeinsame Regeln für die Karteikarte</div><div class="f-karte"><div data-z="Zeilen mit Standardnamen">
        ${[['Titel', 'dct:title'], ['Beschreibung', 'dct:description'], ['Herausgeber', 'dct:publisher'], ['Lizenz', 'dct:license'], ['Format', 'dct:format']]
          .map(([a, b]) => `<div class="zeile"><b>${a}</b><em data-z="Fachbegriffe">${b}</em></div>`).join('')}</div></div></div></div></div>`,
  '1/1.3': () => `<div class="folie"><svg class="f-svg" viewBox="0 0 1600 900" role="img" aria-label="Ein Katalog enthält Datensätze, ein Datensatz enthält Distributionen">
      <g data-z="Rahmen Katalog"><rect x="110" y="90" width="1380" height="720" rx="22" fill="none" class="s-linie" stroke-width="3"/>
        <text x="150" y="150" class="c-akzent t-anz" font-size="34" font-weight="700">Katalog</text><text x="150" y="190" class="c-gedimmt" font-size="24">alle Daten Ihrer Organisation</text>
        <rect x="1080" y="250" width="340" height="110" rx="14" class="c-flaeche s-linie"/><text x="1105" y="315" class="c-gedimmt" font-size="24">Veranstaltungen 2024</text>
        <rect x="1080" y="390" width="340" height="110" rx="14" class="c-flaeche s-linie"/><text x="1105" y="455" class="c-gedimmt" font-size="24">Spendenaufkommen</text></g>
      <g data-z="Rahmen Datensatz"><rect x="170" y="240" width="860" height="520" rx="18" class="c-flaeche s-akzent" stroke-width="3"/>
        <text x="210" y="300" class="c-text t-anz" font-size="32" font-weight="700">Datensatz</text><text x="210" y="340" class="c-gedimmt" font-size="24">Mitgliederzahlen 2015–2025</text></g>
      <g data-z="Distribution CSV"><rect x="230" y="420" width="330" height="270" rx="14" class="c-grund s-linie"/><text x="395" y="545" text-anchor="middle" class="c-akzent t-mono" font-size="52" font-weight="700">CSV</text><text x="395" y="600" text-anchor="middle" class="c-gedimmt" font-size="22">für Tabellenprogramme</text></g>
      <g data-z="Distribution JSON"><rect x="620" y="420" width="330" height="270" rx="14" class="c-grund s-linie"/><text x="785" y="545" text-anchor="middle" class="c-akzent t-mono" font-size="52" font-weight="700">JSON</text><text x="785" y="600" text-anchor="middle" class="c-gedimmt" font-size="22">für Software</text>
        <text x="590" y="735" text-anchor="middle" class="c-text t-anz" font-size="26" font-weight="700">Distributionen</text></g></svg></div>`,
  '1/1.4': () => `<div class="folie"><svg class="f-svg" viewBox="0 0 1600 900" role="img" aria-label="Portale rufen die Katalog-Adresse ab; nur die Beschreibung wandert">${PFEIL('pf')}
      <g data-z="Website mit Adresse"><rect x="80" y="230" width="460" height="440" rx="18" class="c-flaeche s-linie" stroke-width="3"/>
        <text x="110" y="267" class="c-gedimmt t-mono" font-size="20">ihre-website.de/…/catalog</text><text x="110" y="340" class="c-text t-anz" font-size="30" font-weight="700">Ihre Website</text>
        <g class="hervor" data-z="Dateisymbol hervorgehoben" data-modus="hervor"><rect x="120" y="390" width="150" height="190" rx="10" class="c-grund s-akzent" stroke-width="3"/><text x="195" y="500" text-anchor="middle" class="c-akzent t-mono" font-size="34" font-weight="700">CSV</text><text x="195" y="620" text-anchor="middle" class="c-gedimmt" font-size="20">bleibt hier</text></g>
        <rect x="320" y="400" width="180" height="170" rx="8" class="c-papier"/><rect x="320" y="400" width="180" height="16" class="c-kopf"/>${[440, 475, 510, 545].map((y) => `<rect x="340" y="${y}" width="140" height="8" rx="4" class="c-papierlinie"/>`).join('')}</g>
      <g data-z="Portale mit Abruf-Pfeilen"><rect x="930" y="200" width="290" height="130" rx="14" class="c-flaeche s-linie" stroke-width="3"/><text x="1075" y="275" text-anchor="middle" class="c-text t-anz" font-size="30" font-weight="700">Landesportal</text>
        <rect x="930" y="560" width="290" height="130" rx="14" class="c-flaeche s-linie" stroke-width="3"/><text x="1075" y="635" text-anchor="middle" class="c-text t-anz" font-size="30" font-weight="700">GovData</text>
        <path d="M925 265 C 760 265, 700 360, 552 400" fill="none" class="s-akzent" stroke-width="4" marker-end="url(#pf)"/><path d="M925 625 C 760 625, 700 540, 552 520" fill="none" class="s-akzent" stroke-width="4" marker-end="url(#pf)"/>
        <text x="640" y="215" class="c-gedimmt" font-size="22">ruft regelmäßig ab</text></g>
      <g data-z="Karteikarte wandert" class="wandert"><rect x="740" y="410" width="120" height="110" rx="6" class="c-papier"/><rect x="740" y="410" width="120" height="12" class="c-kopf"/>${[440, 465, 490].map((y) => `<rect x="756" y="${y}" width="88" height="7" rx="3" class="c-papierlinie"/>`).join('')}<text x="800" y="555" text-anchor="middle" class="c-text" font-size="20">nur die Beschreibung</text></g>
      <g data-z="Beschriftung Harvesting"><text x="1075" y="125" text-anchor="middle" class="c-akzent t-anz" font-size="40" font-weight="700">Harvesting = Ernten</text></g>
      <g data-z="data.europa.eu"><rect x="1300" y="560" width="250" height="130" rx="14" class="c-flaeche s-akzent" stroke-width="3"/><text x="1425" y="635" text-anchor="middle" class="c-text t-anz" font-size="26" font-weight="700">data.europa.eu</text>
        <path d="M1300 625 L 1232 625" fill="none" class="s-akzent" stroke-width="4" marker-end="url(#pf)"/></g></svg></div>`,
  '1/1.5': () => `<div class="folie"><div class="f-kopf">Zur Einordnung</div><h3 class="f-h">Was der Open Data Wizard nicht ist</h3><div class="f-nein">
      <div data-z="Kein Datenportal"><span class="x">≠</span><div><b>Kein Datenportal</b><span>macht Ihre Organisation für Portale auffindbar, ersetzt sie aber nicht</span></div></div>
      <div data-z="Kein Datenmanagement"><span class="x">≠</span><div><b>Kein Datenmanagement</b><span>Ihre Dateien liegen, wo sie liegen</span></div></div>
      <div data-z="Kein Ersatz für fachliche Sorgfalt"><span class="x">≠</span><div><b>Kein Ersatz für fachliche Sorgfalt</b><span>prüft, ob eine Lizenz angegeben ist; welche passt, entscheiden Sie</span></div></div></div></div>`,
  '2/2.1': () => `<div class="folie"><div class="f-kopf">Das richtige Paket</div><h3 class="f-h">Zwei Downloads, einer zum Installieren</h3><div class="f-zwei">
      <div class="f-paket" data-z="Release-ZIP"><span class="f-urteil ja" data-z="Häkchen">✓</span><b>Release-ZIP</b><code>open-data-wizard-2.47.5.zip</code><span>fertiges Plugin, mit allen Bibliotheken</span></div>
      <div class="f-paket" data-z="Quellcode"><span class="f-urteil nein" data-z="Kreuz">✕</span><b>Quellcode</b><code class="gedimmt">grüner Button „Code“</code><span>für die Entwicklung, ohne Bibliotheken</span></div></div></div>`,
  '2/2.4': () => `<div class="folie"><svg class="f-svg" viewBox="0 0 1600 900" role="img" aria-label="Zwei Wege zu neuen Versionen; die Datensätze bleiben in der Datenbank">${PFEIL('pf2')}
      <text x="110" y="120" class="c-akzent t-anz" font-size="26" font-weight="700" letter-spacing="3">UPDATES</text>
      <g data-z="Weg mit Update-Plugin"><text x="110" y="215" class="c-gedimmt" font-size="24">mit Update-Plugin, z. B. Git Updater</text>
        <rect x="110" y="240" width="380" height="110" rx="14" class="c-flaeche s-linie" stroke-width="3"/><text x="300" y="305" text-anchor="middle" class="c-text" font-size="26">Neue Version erschienen</text>
        <path d="M500 295 L 590 295" class="s-akzent" stroke-width="4" marker-end="url(#pf2)"/>
        <rect x="600" y="240" width="420" height="110" rx="14" class="c-flaeche s-akzent" stroke-width="3"/><text x="810" y="305" text-anchor="middle" class="c-text" font-size="26">WordPress meldet sie</text></g>
      <g data-z="Weg ohne Update-Plugin"><text x="110" y="445" class="c-gedimmt" font-size="24">ohne Update-Plugin</text>
        <rect x="110" y="470" width="380" height="110" rx="14" class="c-flaeche s-linie" stroke-width="3"/><text x="300" y="535" text-anchor="middle" class="c-text" font-size="26">Release-ZIP hochladen</text>
        <path d="M500 525 L 590 525" class="s-akzent" stroke-width="4" marker-end="url(#pf2)"/>
        <rect x="600" y="470" width="420" height="110" rx="14" class="c-flaeche s-akzent" stroke-width="3"/><text x="810" y="535" text-anchor="middle" class="c-text" font-size="26">Vorhandene Version ersetzen</text></g>
      <g data-z="Datenbank"><path d="M1120 330 L1120 600 A170 44 0 0 0 1460 600 L1460 330" class="c-flaeche s-akzent" stroke-width="3"/>
        <ellipse cx="1290" cy="330" rx="170" ry="44" class="c-flaeche s-akzent" stroke-width="3"/><text x="1290" y="470" text-anchor="middle" class="c-text t-anz" font-size="30" font-weight="700">Datenbank</text>
        <text x="1290" y="515" text-anchor="middle" class="c-gedimmt" font-size="22">Ihre Datensätze bleiben</text>
        <path d="M1030 295 C 1080 295, 1090 400, 1112 420" fill="none" class="s-linie" stroke-width="3" stroke-dasharray="8 8"/><path d="M1030 525 C 1080 525, 1090 480, 1112 470" fill="none" class="s-linie" stroke-width="3" stroke-dasharray="8 8"/></g></svg></div>`,
};
function folieFuer(f, sz, g) {
  const k = `${f.folge}/${sz.id}`;
  if (FOLIEN[k]) return FOLIEN[k]();
  if (/^(Q1|\d+\.0)$/.test(sz.id)) return titelFolie(f, g);
  return abspannFolie(f, g);
}

// ---------------------------------------------------------------------------------------------
const CSS = `
:host { display: block; container-type: inline-size; font-family: var(--s-text); color: var(--b-text); background: var(--b-grund); line-height: 1.5; }
* { box-sizing: border-box; }
[hidden] { display: none !important; }
button, select { font: inherit; color: inherit; }
:focus-visible { outline: 3px solid var(--b-fokus); outline-offset: 2px; }
.ks { display: grid; gap: 12px; }
.kopf { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; justify-content: space-between; }
.kopf .titel { font: 700 1.05rem/1.3 var(--s-anzeige); }
.kopf select { border: 1px solid var(--b-linie); background: var(--b-grund); border-radius: 6px; padding: 6px 8px; max-width: 100%; }
.buehne { position: relative; aspect-ratio: 16 / 9; width: 100%; background: var(--f-grund); border-radius: 6px; overflow: hidden; container-type: inline-size; outline: 1px solid var(--b-linie); outline-offset: -1px; }
.leiste { display: flex; flex-wrap: wrap; gap: 8px 10px; align-items: center; }
.knopf { border: 1px solid var(--b-linie); background: var(--b-grund); border-radius: 6px; padding: 8px 14px; cursor: pointer; min-height: 40px; }
.knopf:hover:not([disabled]) { border-color: var(--b-text); }
.knopf[disabled] { opacity: .45; cursor: default; }
.knopf.haupt { background: var(--b-knopf); color: var(--b-knopf_text); border-color: var(--b-knopf); font-weight: 600; }
.stand { color: var(--b-gedimmt); font-size: .9rem; font-variant-numeric: tabular-nums; }
.fortschritt { flex: 1 1 160px; height: 4px; background: var(--b-linie); border-radius: 2px; position: relative; }
.fortschritt i { position: absolute; left: 0; top: 0; bottom: 0; background: var(--b-knopf); border-radius: 2px; }
.bildnav { display: flex; gap: 8px; align-items: center; font-size: .9rem; color: var(--b-gedimmt); }
.bildnav button { border: 1px solid var(--b-linie); background: var(--b-grund); border-radius: 6px; padding: 2px 10px; cursor: pointer; }
.text h3 { margin: 0 0 4px; font: 700 1.2rem/1.3 var(--s-anzeige); text-wrap: balance; }
.text p { margin: 0; font-size: 1.05rem; line-height: 1.7; max-width: 68ch; }
.w { border-radius: 3px; }
.w.jetzt { background: var(--b-markierung); }
.schritte { margin: 8px 0 0; padding-left: 20px; color: var(--b-gedimmt); font-size: .9rem; }
.schritte li.aktiv { color: var(--b-text); font-weight: 600; }

/* Bühne: Bildschirmfotos */
.scr { position: absolute; inset: 0; transition: transform .6s ease; transform-origin: 50% 50%; }
.scr img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.box { position: absolute; border: .35cqw solid var(--f-akzent); border-radius: .6cqw; box-shadow: 0 0 0 100vmax rgba(0, 0, 0, .25); transition: opacity .35s; opacity: 0; }
.untertitel { position: absolute; left: 50%; bottom: 3.2cqw; transform: translateX(-50%); max-width: 80%; background: var(--f-grund); color: var(--f-text); border-left: .4cqw solid var(--f-akzent);
  font: 600 1.7cqw/1.35 var(--s-text); padding: .9cqw 1.4cqw; border-radius: .4cqw; transition: opacity .35s; opacity: 0; }
.etikett { position: absolute; left: 2.2cqw; top: 2.2cqw; background: var(--f-akzent); color: var(--f-akzent_text); font: 700 1.3cqw/1 var(--s-anzeige); text-transform: uppercase; letter-spacing: .06em; padding: .7cqw 1cqw; border-radius: .3cqw; transition: opacity .35s; opacity: 0; }
.mini { position: absolute; right: 2.4cqw; bottom: 2.4cqw; width: 34cqw; background: var(--f-grund); border: 1px solid var(--f-linie); border-radius: .8cqw; padding: 1.4cqw; color: var(--f-text); font: 500 1.5cqw/1.35 var(--s-text); }
.mini b { display: block; color: var(--f-akzent_schrift); margin-bottom: .6cqw; }

/* Bühne: Folien */
.folie { position: absolute; inset: 0; background: var(--f-grund); color: var(--f-text); font-family: var(--s-text); padding: 5.5cqw 6.5cqw; overflow: hidden; }
.folie h2, .folie h3 { font-family: var(--s-anzeige); margin: 0; text-wrap: balance; }
.z-an { transition: opacity .45s ease, transform .45s ease, box-shadow .45s ease, border-color .45s ease; }
.z-weg { opacity: 0 !important; transform: translateY(1cqw); }
svg .z-weg { transform: none; }
svg .wandert { transition: transform 1.4s ease, opacity .45s ease; }
svg .wandert.z-weg { transform: translateX(-330px); }
.f-titel { display: grid; align-content: center; height: 100%; gap: 1.4cqw; }
.f-logo { width: 7cqw; height: auto; }
.f-logo.breit { width: 22cqw; }
.f-wortmarke { font: 700 1.8cqw/1 var(--s-anzeige); color: var(--f-akzent_schrift); }
.f-eyebrow, .f-kopf, .f-ueber { font: 700 1.5cqw/1 var(--s-anzeige); color: var(--f-akzent_schrift); text-transform: uppercase; letter-spacing: .12em; }
.f-kopf { margin-bottom: 1.6cqw; } .f-ueber { margin-bottom: 1.2cqw; }
.f-titel h2 { font-size: 5.2cqw; line-height: 1.08; letter-spacing: -.015em; max-width: 22ch; }
.f-titel p { margin: 0; font-size: 2.2cqw; color: var(--f-gedimmt); }
.f-fuss { position: absolute; left: 6.5cqw; right: 6.5cqw; bottom: 4cqw; display: flex; justify-content: space-between; gap: 2cqw; font: 500 1.3cqw/1.3 var(--s-mono); color: var(--f-gedimmt); }
.f-h { font-size: 3.6cqw; line-height: 1.1; max-width: 26ch; }
.f-drei { display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; gap: 1.4cqw; align-items: center; margin-top: 4cqw; }
.f-schritt { border: 1px solid var(--f-linie); border-radius: 1cqw; padding: 2cqw; min-height: 15cqw; display: grid; align-content: start; gap: .8cqw; }
.f-schritt .n { font: 700 1.3cqw/1 var(--s-mono); color: var(--f-akzent_schrift); }
.f-schritt b { font: 700 2.2cqw/1.15 var(--s-anzeige); }
.f-schritt span { color: var(--f-gedimmt); font-size: 1.5cqw; line-height: 1.35; }
.f-pfeil { font: 700 3cqw/1 var(--s-anzeige); color: var(--f-akzent); }
.f-band { margin-top: 3cqw; padding: 1.6cqw 2cqw; border-left: .5cqw solid var(--f-akzent); background: var(--f-flaeche); font: 500 2cqw/1.35 var(--s-anzeige); }
.f-panel { background: var(--f-flaeche); border: 1px solid var(--f-linie); border-radius: 1cqw; padding: 2.4cqw; }
.f-klein { color: var(--f-gedimmt); font-size: 1.4cqw; margin: .6cqw 0 1.4cqw; }
.f-asset { display: flex; justify-content: space-between; align-items: center; gap: 2cqw; padding: 1.3cqw 1.6cqw; border: 1px solid var(--f-linie); border-radius: .7cqw; font: 500 1.7cqw/1.2 var(--s-mono); margin-top: 1cqw; }
.f-asset.gut:not(.z-weg) { border-color: var(--f-akzent); box-shadow: 0 0 0 .3cqw color-mix(in srgb, var(--f-akzent) 30%, transparent); }
.f-asset.schlecht { color: var(--f-gedimmt); text-decoration: line-through; }
.f-ja { color: var(--f-akzent_schrift); }
.f-stapel { display: grid; align-content: center; gap: 2cqw; justify-items: start; }
.f-satz { font-size: 1.9cqw; line-height: 1.35; }
.f-code { display: inline-flex; align-items: center; gap: 1cqw; background: #1f883d; color: #fff; font: 600 1.8cqw/1 var(--s-text); padding: 1.1cqw 1.8cqw; border-radius: .7cqw; position: relative; }
.f-code::after { content: ""; position: absolute; left: -10%; right: -10%; top: 50%; height: .45cqw; background: var(--f-warnung); transform: rotate(-12deg); }
.f-zwei { display: grid; grid-template-columns: 1fr 1fr; gap: 3cqw; margin-top: 3.5cqw; }
.f-paket { border: 1px solid var(--f-linie); border-radius: 1.2cqw; padding: 2.6cqw; display: grid; gap: 1cqw; position: relative; }
.f-paket b { font: 700 2.8cqw/1.1 var(--s-anzeige); }
.f-paket code { font: 500 1.6cqw/1.2 var(--s-mono); color: var(--f-akzent_schrift); }
.f-paket code.gedimmt { color: var(--f-gedimmt); }
.f-paket span { color: var(--f-gedimmt); font-size: 1.7cqw; }
.f-urteil { position: absolute; right: 2cqw; top: 2cqw; width: 5cqw; height: 5cqw; border-radius: 50%; display: grid; place-items: center; font: 700 3cqw/1 var(--s-anzeige); }
.f-urteil.ja { background: var(--f-akzent); color: var(--f-akzent_text); }
.f-urteil.nein { background: var(--f-warnung); color: var(--f-grund); }
.f-regal { display: grid; grid-template-columns: 1fr 1.1fr; gap: 5cqw; align-items: center; height: 100%; }
.f-buecher { display: flex; align-items: flex-end; gap: .8cqw; height: 26cqw; border-bottom: .5cqw solid var(--f-linie); padding: 0 1cqw; }
.f-buecher i { display: block; width: 3.4cqw; background: var(--f-flaeche); outline: 1px solid var(--f-linie); border-radius: .3cqw .3cqw 0 0; }
.f-buecher i.b { background: var(--f-akzent); outline: 0; }
.f-karte { background: var(--f-papier); color: var(--f-tinte); border-radius: .8cqw; padding: 2.2cqw 2.6cqw; border-top: .9cqw solid var(--f-karte_kopf); box-shadow: 0 1.2cqw 3cqw rgba(0, 0, 0, .35); }
.f-karte .zeile { display: grid; grid-template-columns: 13cqw minmax(0, 1fr); gap: 1.4cqw; padding: .9cqw 0; border-bottom: 1px solid var(--f-papierlinie); font-size: 1.7cqw; }
.f-karte .zeile:last-child { border-bottom: 0; }
.f-karte .zeile b { font-family: var(--s-anzeige); }
.f-karte .zeile em { font: normal 500 1.45cqw/1.3 var(--s-mono); color: var(--f-tinte); opacity: .75; }
.f-etiketten { display: grid; gap: 1.6cqw; align-content: center; }
.f-etikett { border: 1px solid var(--f-linie); border-radius: 1cqw; padding: 2cqw 2.4cqw; display: grid; gap: .4cqw; }
.f-etikett b { font: 700 3cqw/1 var(--s-anzeige); color: var(--f-akzent); }
.f-etikett span { color: var(--f-gedimmt); font-size: 1.7cqw; }
.f-svg { position: absolute; inset: 0; width: 100%; height: 100%; font-family: var(--s-text); }
.f-nein { display: grid; gap: 2.2cqw; margin-top: 3.4cqw; }
.f-nein > div { display: grid; grid-template-columns: 4cqw minmax(0, 1fr); gap: 1.6cqw; align-items: baseline; }
.f-nein .x { font: 700 2.8cqw/1 var(--s-anzeige); color: var(--f-warnung); }
.f-nein b { font: 700 3cqw/1.1 var(--s-anzeige); display: block; }
.f-nein span { color: var(--f-gedimmt); font-size: 1.8cqw; }
.c-grund { fill: var(--f-grund); } .c-flaeche { fill: var(--f-flaeche); } .c-text { fill: var(--f-text); } .c-gedimmt { fill: var(--f-gedimmt); }
.c-akzent { fill: var(--f-akzent); } .c-papier { fill: var(--f-papier); } .c-papierlinie { fill: var(--f-papierlinie); } .c-kopf { fill: var(--f-karte_kopf); }
.s-linie { stroke: var(--f-linie); } .s-akzent { stroke: var(--f-akzent); }
.t-anz { font-family: var(--s-anzeige); } .t-mono { font-family: var(--s-mono); }
.hervor.aktiv { filter: drop-shadow(0 0 1.2cqw var(--f-akzent)); }
.meldung { padding: 24px; color: var(--b-gedimmt); }
@container (max-width: 560px) { .text p { font-size: 1rem; } .knopf { padding: 8px 10px; } }
@media (prefers-reduced-motion: reduce) { .z-an, .scr, .box, .untertitel, .etikett, svg .wandert { transition: none !important; } }
`;

class OdwKlickstrecke extends HTMLElement {
  static get observedAttributes() { return ['src', 'gestaltung']; }

  constructor() {
    super();
    this.root = this.attachShadow({ mode: 'open' });
    this.si = 0; this.t = 0; this.laeuft = false; this.ton = null; this.letzter = 0;
    this.schleife = this.schleife.bind(this);
  }

  connectedCallback() { this.lade(); }
  attributeChangedCallback(_n, alt, neu) { if (this.isConnected && alt !== null && alt !== neu) this.lade(); }
  disconnectedCallback() { this.halte(); }

  async lade() {
    this.halte();
    this.root.innerHTML = `<style>${CSS}</style><div class="meldung">Klickstrecke wird geladen …</div>`;
    try {
      const name = (this.getAttribute('gestaltung') || 'odw').replace(/[^a-z0-9-]/gi, '');
      this.g = await (await fetch(new URL(`gestaltung/${name}.json`, BASIS))).json();
      ladeSchriften(this.g);
      const src = new URL(this.getAttribute('src') || 'daten.json', document.baseURI);
      this.datenBasis = new URL('.', src);
      this.f = await (await fetch(src)).json();
    } catch (e) {
      this.root.innerHTML = `<style>${CSS}</style><div class="meldung">Die Klickstrecke konnte nicht geladen werden.</div>`;
      return;
    }
    this.baue();
    this.zeige(0);
  }

  url(pfad) { return new URL(pfad, this.datenBasis).href; }

  baue() {
    const f = this.f;
    this.root.innerHTML = `<style>${gestaltungsCss(this.g)}${CSS}</style>
      <section class="ks" aria-label="${esc(f.titel)}">
        <div class="kopf"><span class="titel">${esc(f.folge === 0 ? 'Quick-Start' : 'Folge ' + f.folge)} · ${esc(f.titel.replace(/^Quick-Start:\s*/, ''))}</span>
          <label><span hidden>Kapitel</span><select class="kapitel" aria-label="Kapitel wählen">${f.szenen.map((s, i) => `<option value="${i}">${i + 1}. ${esc(s.kapitel)}</option>`).join('')}</select></label></div>
        <div class="buehne" part="buehne"></div>
        <div class="leiste">
          <button class="knopf zurueck" type="button">◀ Zurück</button>
          <button class="knopf haupt spiel" type="button"></button>
          <button class="knopf weiter" type="button">Weiter ▶</button>
          <span class="stand"></span>
          <div class="fortschritt" aria-hidden="true"><i></i></div>
        </div>
        <div class="bildnav" hidden><button type="button" class="bild-zurueck" aria-label="Voriges Bild">‹</button><span class="bild-stand"></span><button type="button" class="bild-weiter" aria-label="Nächstes Bild">›</button></div>
        <div class="text" aria-live="polite"><h3></h3><p></p><ol class="schritte"></ol></div>
      </section>`;
    const $ = (s) => this.root.querySelector(s);
    this.el = { buehne: $('.buehne'), spiel: $('.spiel'), stand: $('.stand'), fuell: $('.fortschritt i'), kapitel: $('.kapitel'),
      h3: $('.text h3'), p: $('.text p'), schritte: $('.schritte'), bildnav: $('.bildnav'), bildStand: $('.bild-stand') };
    $('.zurueck').addEventListener('click', () => this.zeige(this.si - 1));
    $('.weiter').addEventListener('click', () => this.zeige(this.si + 1));
    this.el.spiel.addEventListener('click', () => (this.laeuft ? this.halte() : this.spiele()));
    this.el.kapitel.addEventListener('change', () => this.zeige(+this.el.kapitel.value));
    $('.bild-zurueck').addEventListener('click', () => this.springeBild(-1));
    $('.bild-weiter').addEventListener('click', () => this.springeBild(1));
    this.el.p.addEventListener('click', (e) => { const w = e.target.closest('.w'); if (w && this.plan) { this.t = this.plan.liste[+w.dataset.i].start; if (this.ton) this.ton.currentTime = this.t; this.aktualisiere(); } });
    this.root.querySelector('.ks').addEventListener('keydown', (e) => {
      if (e.target.closest('select')) return;
      if (e.key === 'ArrowRight') { this.zeige(this.si + 1); e.preventDefault(); }
      if (e.key === 'ArrowLeft') { this.zeige(this.si - 1); e.preventDefault(); }
    });
  }

  zeige(i, weiterSpielen = false) {
    const n = this.f.szenen.length;
    if (i < 0 || i >= n) return;
    this.halte();
    this.si = i;
    const sz = this.f.szenen[i];
    this.ton = sz.ton ? new Audio(this.url(sz.ton)) : null;
    if (this.ton) this.ton.preload = 'metadata';
    this.plan = zeitplan(sz, null);
    if (this.ton) this.ton.addEventListener('loadedmetadata', () => { const anteil = this.t / this.plan.gesamt; this.plan = zeitplan(sz, this.ton.duration); this.t = anteil * this.plan.gesamt; this.zeichneLeisten(); this.aktualisiere(); });
    this.el.kapitel.value = String(i);
    this.root.querySelector('.zurueck').disabled = i === 0;
    this.root.querySelector('.weiter').disabled = i === n - 1;
    this.el.stand.textContent = `Schritt ${i + 1} von ${n}`;
    this.el.spiel.textContent = this.ton ? '▶ Anhören' : '▶ Abspielen';
    this.el.h3.textContent = sz.kapitel;
    this.el.p.innerHTML = this.plan.liste.map((x, k) => `<span class="w" data-i="${k}">${esc(x.w)}</span>`).join(' ');
    // Bühne
    if (sz.bild === 'folie') this.el.buehne.innerHTML = folieFuer(this.f, sz, this.g);
    else {
      const mini = sz.folie_danach ? `<div class="mini" data-bei="GovData"><b>Katalog-Adresse → Portal</b>GovData oder Landesportal tragen die Adresse ein und holen regelmäßig ab.</div>` : '';
      this.el.buehne.innerHTML = `<div class="scr"><img alt="">${this.plan.einbl.map((e, k) => e.box ? `<div class="box" data-e="${k}" style="left:${e.box.x / BW * 100}%;top:${e.box.y / BH * 100}%;width:${e.box.width / BW * 100}%;height:${e.box.height / BH * 100}%"></div>` : '').join('')}</div>
        ${this.plan.einbl.map((e, k) => e.text && e.markiere ? `<div class="untertitel" data-e="${k}">${esc(e.text)}</div>` : (e.text ? `<div class="etikett" data-e="${k}">${esc(e.text)}</div>` : '')).join('')}${mini}`;
      this.el.bild = this.el.buehne.querySelector('img');
      this.el.scr = this.el.buehne.querySelector('.scr');
    }
    this.el.buehne.querySelectorAll('[data-z],[data-bei]').forEach((el) => el.classList.add('z-an'));
    this.el.bildnav.hidden = !(sz.bild === 'screencast' && this.plan.bilder.length > 1);
    this.el.schritte.innerHTML = sz.bild === 'screencast' ? this.plan.bilder.map((b) => `<li>${esc(b.text || b.schritt)}</li>`).join('') : '';
    this.t = weiterSpielen ? 0 : this.plan.gesamt; // in Ruhe: Endzustand, damit alles sichtbar ist
    this.zeichneLeisten();
    this.aktualisiere();
    if (weiterSpielen) this.spiele();
  }

  zeichneLeisten() { /* Platz für Markierungen in der Fortschrittsleiste; bewusst schlicht gehalten */ }

  springeBild(r) {
    const b = this.plan.bilder; if (!b.length) return;
    let k = 0; b.forEach((x, i) => { if (this.t >= x.zeit) k = i; });
    k = Math.max(0, Math.min(b.length - 1, k + r));
    this.halte(); this.t = b[k].zeit + 0.01; this.aktualisiere();
  }

  spiele() {
    if (this.t >= this.plan.gesamt - 0.05) this.t = 0;
    this.laeuft = true;
    this.el.spiel.textContent = '❚❚ Pause';
    if (this.ton) { this.ton.currentTime = this.t; this.ton.play().catch(() => { this.ton = null; this.letzter = performance.now(); requestAnimationFrame(this.schleife); }); }
    this.letzter = performance.now();
    requestAnimationFrame(this.schleife);
  }

  halte() {
    this.laeuft = false;
    if (this.ton) this.ton.pause();
    if (this.el && this.el.spiel && this.f) this.el.spiel.textContent = this.f.szenen[this.si].ton ? '▶ Anhören' : '▶ Abspielen';
  }

  schleife(jetzt) {
    if (!this.laeuft) return;
    if (this.ton && !this.ton.paused) this.t = this.ton.currentTime;
    else { this.t += (jetzt - this.letzter) / 1000; }
    this.letzter = jetzt;
    if (this.t >= this.plan.gesamt || (this.ton && this.ton.ended)) {
      this.t = this.plan.gesamt; this.aktualisiere();
      if (this.si < this.f.szenen.length - 1) { this.zeige(this.si + 1, true); return; }
      this.halte(); return;
    }
    this.aktualisiere();
    requestAnimationFrame(this.schleife);
  }

  aktualisiere() {
    const sz = this.f.szenen[this.si], p = this.plan, t = this.t;
    let jetzt = -1; p.liste.forEach((x, i) => { if (t >= x.start) jetzt = i; });
    if (!this.laeuft) jetzt = -1;
    this.el.p.querySelectorAll('.w').forEach((el, i) => el.classList.toggle('jetzt', i === jetzt));
    const zeitVon = (el) => {
      if (el.dataset.z) { const e = p.einbl.find((x) => x.zeige === el.dataset.z); return e ? e.zeit : 0; }
      if (el.dataset.bei) { const z = p.wann(el.dataset.bei); return z === null ? 0 : z; }
      return 0;
    };
    this.el.buehne.querySelectorAll('[data-z],[data-bei]').forEach((el) => {
      const an = t >= zeitVon(el);
      if (el.dataset.modus === 'hervor') el.classList.toggle('aktiv', an); else el.classList.toggle('z-weg', !an);
    });
    if (sz.bild === 'screencast' && p.bilder.length) {
      let k = 0; p.bilder.forEach((b, i) => { if (t >= b.zeit) k = i; });
      const b = p.bilder[k], src = this.url(b.datei);
      if (this.el.bild.getAttribute('src') !== src) { this.el.bild.setAttribute('src', src); this.el.bild.alt = `Bildschirmfoto nach dem Schritt: ${b.text || b.schritt}`; }
      this.el.bildStand.textContent = `Bild ${k + 1} von ${p.bilder.length}`;
      this.el.schritte.querySelectorAll('li').forEach((li, i) => li.classList.toggle('aktiv', i === k));
      const letztes = k === p.bilder.length - 1; let zoom = null;
      p.einbl.forEach((e, i) => {
        const an = t >= e.zeit;
        this.el.buehne.querySelectorAll(`[data-e="${i}"]`).forEach((el) => { el.style.opacity = el.classList.contains('box') ? (an && letztes ? 1 : 0) : (an ? 1 : 0); });
        if (an && letztes && e.zoom && e.box) zoom = e.box;
      });
      if (zoom) { this.el.scr.style.transformOrigin = `${(zoom.x + zoom.width / 2) / BW * 100}% ${(zoom.y + zoom.height / 2) / BH * 100}%`; this.el.scr.style.transform = 'scale(1.55)'; }
      else this.el.scr.style.transform = 'none';
    }
    this.el.fuell.style.width = `${Math.min(100, t / p.gesamt * 100)}%`;
    this.el.stand.textContent = `Schritt ${this.si + 1} von ${this.f.szenen.length} · ${mmss(t)} / ${mmss(p.gesamt)}`;
  }
}

if (!customElements.get('odw-klickstrecke')) customElements.define('odw-klickstrecke', OdwKlickstrecke);
