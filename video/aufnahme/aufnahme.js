// Führt die Klickschritte der Drehbücher (video/folgen/*.yml) in einem laufenden WordPress aus und
// hält nach jedem Schritt ein Bildschirmfoto fest.
//
//   node video/aufnahme/aufnahme.js [folge …]
//
// Ergebnis: <ausgabe>/<folge>/<szene>-<n>.jpg und <ausgabe>/ergebnis.json (Schritte, Fehler,
// vermessene Markierungen). Vorher: video/aufnahme/einrichten.sh und starte-server.sh.
// Umgebungsvariablen: ODW_VIDEO_URL (Standard http://127.0.0.1:8888), ODW_VIDEO_WP (WordPress-
// Arbeitsordner), ODW_VIDEO_AUFNAHME (Ausgabe, Standard video/ausgabe/aufnahme).
'use strict';
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const HIER = __dirname;
const REPO = path.resolve(HIER, '..', '..');
const BASE = process.env.ODW_VIDEO_URL || 'http://127.0.0.1:8888';
const AUS = process.env.ODW_VIDEO_AUFNAHME || path.join(REPO, 'video', 'ausgabe', 'aufnahme');
const VIEW = { width: 1600, height: 900 }; // 16:9 wie das Video

// Drehbücher lesen (YAML → JSON über PyYAML, damit dieses Skript ohne npm-Pakete auskommt).
const drehbuecher = JSON.parse(execFileSync('python3', ['-c',
  'import yaml,json,glob,sys; print(json.dumps([yaml.safe_load(open(f,encoding="utf-8")) for f in sorted(glob.glob(sys.argv[1]))], ensure_ascii=False))',
  path.join(REPO, 'video', 'folgen', '*.yml')], { encoding: 'utf8' }));
const nurFolgen = process.argv.slice(2).map(Number);

function ersetze(wert, daten) {
  if (typeof wert !== 'string') return wert;
  const m = wert.match(/^\{([\w.]+)\}$/);
  if (!m) return wert;
  const v = m[1].split('.').reduce((o, k) => (o ? o[k] : undefined), daten);
  return Array.isArray(v) ? v.join('\n') : v;
}

// Erstes sichtbares Element aus mehreren Kandidaten, auch in iframes (Block-Editor).
async function finde(page, kandidaten, timeout = 6000) {
  const frames = () => [page.mainFrame(), ...page.frames().filter((f) => f !== page.mainFrame())];
  const ende = Date.now() + timeout;
  while (Date.now() < ende) {
    for (const f of frames()) {
      for (const k of kandidaten) {
        const loc = k(f);
        const n = await loc.count().catch(() => 0);
        for (let i = 0; i < n; i++) {
          const el = loc.nth(i);
          if (await el.isVisible().catch(() => false)) return el;
        }
      }
    }
    await page.waitForTimeout(200);
  }
  return null;
}

const anfang = (t) => new RegExp('^\\s*' + String(t).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
const exakt = (t) => new RegExp(String(t ?? '') === '' ? '^$a' : '^\\s*' + String(t).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*$');

async function klicke(page, text) {
  // Mehrere gleich beschriftete Elemente: das erste nehmen, das sich tatsächlich klicken lässt.
  const kandidaten = [
    (f) => f.getByRole('button', { name: text, exact: true }),
    (f) => f.getByRole('link', { name: text, exact: true }),
  ];
  for (const f of [page.mainFrame(), ...page.frames().filter((x) => x !== page.mainFrame())]) {
    for (const k of kandidaten) {
      const loc = k(f); const n = await loc.count().catch(() => 0);
      if (n < 2) continue;
      for (let i = n - 1; i >= 0; i--) {
        const el = loc.nth(i);
        if (!(await el.isVisible().catch(() => false))) continue;
        try { await el.click({ timeout: 2500 }); return; } catch (e) { /* nächstes */ }
      }
    }
  }
  const el = await finde(page, [
    (f) => f.getByRole('button', { name: text, exact: true }),
    (f) => f.getByRole('link', { name: text, exact: true }),
    (f) => f.getByRole('tab', { name: text, exact: true }),
    (f) => f.getByRole('menuitem', { name: text, exact: true }),
    (f) => f.getByRole('option', { name: text, exact: true }),
    (f) => f.getByText(exakt(text)),
    (f) => f.getByRole('button', { name: text }),
    (f) => f.getByText(text),
  ]);
  if (!el) throw new Error(`Nichts Klickbares mit „${text}" gefunden`);
  await el.scrollIntoViewIfNeeded().catch(() => {});
  await el.click();
}

// Feld über seine sichtbare Beschriftung: <label>, aria-label, Platzhalter oder Carbon-Fields-Feld.
async function feld(page, name, art) {
  const css = art === 'select' ? 'select' : 'input:not([type=hidden]):not([type=checkbox]), textarea, [contenteditable=true]';
  return finde(page, [
    (f) => f.getByLabel(name, { exact: true }),
    (f) => f.getByRole('textbox', { name, exact: true }),
    (f) => f.getByPlaceholder(name, { exact: true }),
    // Nur das innerste Feld: Ein wiederholbarer Block („Weitere Distributionen") ist selbst ein
    // .cf-field und enthält die Beschriftungen seiner Unterfelder; sonst träfe „Lizenz" das erste
    // Auswahlfeld des Blocks.
    (f) => f.locator('.odw-cessda-widget, .cf-field, tr').filter({ has: f.locator('label, th', { hasText: anfang(name) }) })
      .filter({ hasNot: f.locator('.cf-field') }).locator(css),
    (f) => f.getByLabel(name),
  ]);
}

async function tippe(page, name, text) {
  const el = await feld(page, name, 'text');
  if (!el) throw new Error(`Eingabefeld „${name}" nicht gefunden`);
  await el.scrollIntoViewIfNeeded().catch(() => {});
  await el.click();
  const tag = await el.evaluate((e) => (e.isContentEditable ? 'ce' : e.tagName));
  if (tag === 'ce') { await page.keyboard.press('ControlOrMeta+a'); await page.keyboard.type(text, { delay: 5 }); }
  else await el.fill(text);
}

async function waehle(page, name, option) {
  const sel = await feld(page, name, 'select');
  if (sel && (await sel.evaluate((e) => e.tagName)) === 'SELECT') {
    await sel.scrollIntoViewIfNeeded().catch(() => {});
    await sel.selectOption({ label: option });
    return;
  }
  // Mehrfachauswahl (react-select in Carbon Fields)
  const box = await finde(page, [(f) => f.locator('.cf-field').filter({ has: f.locator('label', { hasText: anfang(name) }) })]);
  if (!box) throw new Error(`Auswahlfeld „${name}" nicht gefunden`);
  await box.scrollIntoViewIfNeeded().catch(() => {});
  await box.locator('[class*="__control"]').first().click();
  await page.keyboard.type(option, { delay: 20 });
  const opt = await finde(page, [(f) => f.locator('[class*="__option"]').filter({ hasText: exakt(option) })], 3000);
  if (!opt) throw new Error(`Option „${option}" in „${name}" nicht gefunden`);
  await opt.click();
  await page.keyboard.press('Escape');
}

async function ladeHoch(page, name, datei) {
  const pfad = datei === '<release-zip>'
    ? fs.readdirSync(path.join(REPO, 'dist')).filter((d) => d.endsWith('.zip')).map((d) => path.join(REPO, 'dist', d))[0]
    : path.join(REPO, 'video/material', datei);
  // Erst einen Reiter mit diesem Namen öffnen, falls es ihn gibt (Mediathek: „Dateien hochladen").
  const reiter = await finde(page, [(f) => f.getByRole('tab', { name, exact: true }), (f) => f.getByRole('link', { name, exact: true })], 1000);
  if (reiter) await reiter.click();
  const input = page.locator('input[type=file]');
  await input.last().setInputFiles(pfad);
  if (datei !== '<release-zip>') await page.waitForSelector('.attachment.save-ready, .attachment.selected, .media-sidebar .attachment-details', { timeout: 20000 }).catch(() => {});
}

// Steht der Text in einem größeren Block mit eigenem Scrollbereich (JSON-LD-Vorschau), diesen so
// verschieben, dass der Text in seiner Mitte steht. Läuft im Browser.
function zentriereText(e, t) {
  const w = document.createTreeWalker(e, NodeFilter.SHOW_TEXT);
  for (let n = w.nextNode(); n; n = w.nextNode()) {
    const i = n.data.indexOf(t);
    if (i < 0) continue;
    const r = document.createRange(); r.setStart(n, i); r.setEnd(n, i + t.length);
    for (let p = n.parentElement; p; p = p.parentElement) {
      if (p.scrollHeight > p.clientHeight + 4 && /(auto|scroll)/.test(getComputedStyle(p).overflowY)) {
        p.scrollTop += r.getBoundingClientRect().top - p.getBoundingClientRect().top - p.clientHeight / 2;
        break;
      }
    }
    return;
  }
}

// Rahmen um genau diesen Text, nicht um den ganzen Block, in dem er steht. Läuft im Browser.
function textRahmen(e, t) {
  const w = document.createTreeWalker(e, NodeFilter.SHOW_TEXT);
  for (let n = w.nextNode(); n; n = w.nextNode()) {
    const i = n.data.indexOf(t);
    if (i < 0) continue;
    const r = document.createRange(); r.setStart(n, i); r.setEnd(n, i + t.length);
    const b = r.getBoundingClientRect();
    return { x: b.x, y: b.y, width: b.width, height: b.height };
  }
  return null;
}

async function schritt(page, s, daten) {
  const [art, wert] = Object.entries(s).find(([k]) => k !== 'bei') || [];
  const v = typeof wert === 'object' && wert !== null ? wert : { text: wert };
  const t = ersetze(v.text, daten);
  switch (art) {
    case 'oeffne': await page.goto(BASE + wert, { waitUntil: 'load' }); break;
    case 'klick': await klicke(page, t); break;
    case 'tippe': await tippe(page, ersetze(v.feld, daten), t); break;
    case 'waehle': await waehle(page, ersetze(v.feld, daten), ersetze(v.option, daten)); break;
    case 'lade_hoch': await ladeHoch(page, v.feld, ersetze(v.datei, daten)); break;
    case 'warte_auf': {
      const el = await finde(page, [(f) => f.getByText(t)], 20000);
      if (!el) throw new Error(`Text „${t}" erschien nicht`);
      break;
    }
    case 'scrolle_zu': {
      const el = await finde(page, [(f) => f.getByText(exakt(t)), (f) => f.getByText(t)]);
      if (!el) throw new Error(`Text „${t}" nicht gefunden`);
      await el.evaluate((e) => e.scrollIntoView({ block: 'center' }));
      await el.evaluate(zentriereText, t);
      break;
    }
    case 'bestaetige': break; // Rückfragen werden global mit „OK" beantwortet
    default: throw new Error(`Unbekannter Schritt „${art}"`);
  }
  await page.waitForLoadState('load').catch(() => {});
  await page.waitForTimeout(700);
}

function beschreibe(s, daten) {
  const [art, wert] = Object.entries(s).find(([k]) => k !== 'bei') || [];
  if (typeof wert !== 'object' || wert === null) return `${art}: ${wert}`;
  const teile = ['feld', 'text', 'option', 'datei'].filter((k) => wert[k]).map((k) => `${k}: ${ersetze(wert[k], daten)}`);
  return `${art} · ${teile.join(' · ')}`;
}

async function markierungen(page, liste, daten) {
  const aus = [];
  for (const m of liste || []) {
    if (!m.markiere) continue;
    const t = ersetze(m.markiere, daten);
    if (!t) { aus.push({ text: m.markiere, bei: m.bei || null, box: null }); continue; }
    const el = await finde(page, [(f) => f.getByText(exakt(t)), (f) => f.getByLabel(t, { exact: true }), (f) => f.getByText(t)], 800);
    let box = el ? await el.boundingBox() : null;
    // Steht der Text nur als Teil eines größeren Blocks da, den Rahmen auf den Text selbst legen.
    // Nur im Hauptdokument: In einem iframe (Block-Editor) wären die Koordinaten relativ zum iframe.
    if (el && (await el.evaluate((e, x) => window === window.top && e.textContent.trim() !== x, t))) box = (await el.evaluate(textRahmen, t)) || box;
    aus.push({ text: t, bei: m.bei || null, box: box && box.y >= 0 && box.y < VIEW.height ? box : null });
  }
  return aus;
}

(async () => {
  fs.mkdirSync(AUS, { recursive: true });
  const ergebnisDatei = path.join(AUS, 'ergebnis.json');
  const ergebnis = fs.existsSync(ergebnisDatei) ? JSON.parse(fs.readFileSync(ergebnisDatei, 'utf8')) : {};
  // Deutsche Browser-Oberfläche, sonst heißt der Knopf eines Datei-Felds „Choose File“. Das gelingt nur
  // mit dem vollständigen Chromium (neuer Headless-Modus); die Headless-Shell bleibt englisch.
  const browser = await chromium.launch({ channel: 'chromium', args: ['--lang=de-DE'], env: { ...process.env, LANG: 'de_DE.UTF-8', LANGUAGE: 'de' } });
  for (const db of drehbuecher) {
    if (nurFolgen.length && !nurFolgen.includes(db.folge)) continue;
    if (!db.szenen.some((s) => s.bild === 'screencast')) continue;
    console.log(`\n== Folge ${db.folge} (${db.umgebung})`);
    execFileSync(path.join(HIER, 'zustand.sh'), [db.umgebung], { stdio: 'inherit' });
    const ctx = await browser.newContext({ viewport: VIEW, locale: 'de-DE' });
    const page = await ctx.newPage();
    page.on('dialog', (d) => d.accept());
    await page.goto(BASE + '/wp-login.php');
    await page.fill('#user_login', 'redaktion');
    await page.fill('#user_pass', 'vorschau');
    await page.click('#wp-submit');
    await page.waitForLoadState('load');
    const ordner = path.join(AUS, String(db.folge));
    fs.rmSync(ordner, { recursive: true, force: true });
    fs.mkdirSync(ordner, { recursive: true });
    const folge = { szenen: {} };
    for (const sz of db.szenen) {
      if (sz.bild !== 'screencast') continue;
      const bilder = [];
      const schritte = sz.schritte || [];
      for (let i = 0; i < schritte.length; i++) {
        const s = schritte[i];
        let fehler = null;
        try { await schritt(page, s, db); } catch (e) { fehler = e.message.split('\n')[0]; }
        const datei = `${db.folge}/${sz.id}-${i + 1}.jpg`;
        await page.screenshot({ path: path.join(AUS, datei), type: 'jpeg', quality: 78 });
        const bei = s.bei || (Object.values(s).find((v) => v && typeof v === 'object') || {}).bei || null;
        bilder.push({ datei, schritt: beschreibe(s, db), bei, fehler });
        console.log(`${fehler ? '✗' : '✓'} ${sz.id}.${i + 1} ${beschreibe(s, db)}${fehler ? '  → ' + fehler : ''}`);
      }
      folge.szenen[sz.id] = { bilder, markierungen: await markierungen(page, sz.einblendung, db) };
    }
    ergebnis[db.folge] = folge;
    await ctx.close();
  }
  await browser.close();
  fs.writeFileSync(ergebnisDatei, JSON.stringify(ergebnis, null, 1));
})();
