// Spielt eine Folge der Klickstrecke Bild für Bild ab (25 Bilder je Sekunde) und hält jeden neuen
// Zustand der Bühne als PNG fest — nur dann, wenn sich etwas ändert. Die Montage (montiere.py) macht
// daraus das Video.
//
//   node video/schnitt/zustaende.js <folge> <gestaltung> <ausgabeordner>
//
// Voraussetzung: das Paket der Klickstrecke in video/ausgabe/odw-tutorials (baue-paket.py mit --ton).
// Ergebnis: <ausgabeordner>/zustaende.json und <ausgabeordner>/z-<szene>-<n>.png
'use strict';
const { chromium } = require('playwright');
const http = require('http');
const fs = require('fs');
const path = require('path');

const REPO = path.resolve(__dirname, '..', '..');
const PAKET = path.join(REPO, 'video', 'ausgabe', 'odw-tutorials');
const FPS = 25;
const [folge, gestaltung = 'odw', aus] = process.argv.slice(2);
if (!/^\d+$/.test(folge || '') || !/^[a-z0-9-]+$/.test(gestaltung) || !aus) {
  console.error('Aufruf: node video/schnitt/zustaende.js <folge> <gestaltung> <ausgabeordner>');
  process.exit(1);
}

const TYPEN = { '.html': 'text/html; charset=utf-8', '.js': 'text/javascript', '.json': 'application/json', '.css': 'text/css',
  '.mp3': 'audio/mpeg', '.jpg': 'image/jpeg', '.png': 'image/png', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.woff': 'font/woff', '.ttf': 'font/ttf' };

// Eine Seite nur mit der Klickstrecke, ohne Rand: Die Bühne ist dann genau 1920 × 1080 Pixel groß.
const SEITE = `<!doctype html><html lang="de"><head><meta charset="utf-8"><style>html,body{margin:0;background:#000}</style></head>
<body><odw-klickstrecke src="folge-${folge}/daten.json" gestaltung="${gestaltung}"></odw-klickstrecke>
<script type="module" src="odw-klickstrecke.js"></script></body></html>`;

function server() {
  return new Promise((ok) => {
    const s = http.createServer((req, res) => {
      const pfad = decodeURIComponent(new URL(req.url, 'http://x').pathname);
      if (pfad === '/__video__.html') { res.writeHead(200, { 'content-type': TYPEN['.html'] }); return res.end(SEITE); }
      const datei = path.join(PAKET, path.normalize(pfad).replace(/^([/\\])+/, ''));
      if (!datei.startsWith(PAKET) || !fs.existsSync(datei) || fs.statSync(datei).isDirectory()) { res.writeHead(404); return res.end(); }
      res.writeHead(200, { 'content-type': TYPEN[path.extname(datei)] || 'application/octet-stream' });
      fs.createReadStream(datei).pipe(res);
    });
    s.listen(0, '127.0.0.1', () => ok(s));
  });
}

(async () => {
  fs.mkdirSync(aus, { recursive: true });
  const srv = await server();
  const basis = `http://127.0.0.1:${srv.address().port}`;
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1300 }, deviceScaleFactor: 1 });
  const fehler = [];
  page.on('pageerror', (e) => fehler.push(e.message));
  page.on('response', (r) => { if (r.status() >= 400) fehler.push(`${r.status()} ${r.url()}`); });
  await page.goto(`${basis}/__video__.html`, { waitUntil: 'networkidle' });
  await page.waitForFunction(() => { const k = document.querySelector('odw-klickstrecke'); return k && k.f && k.el; }, null, { timeout: 20000 });

  // Übergänge aus: Jedes Bild zeigt einen fertigen Zustand; die Montage blendet weich über.
  // Ecken und Rahmen der Bühne gehören zur Klickstrecke, nicht ins Video.
  await page.evaluate(() => {
    const k = document.querySelector('odw-klickstrecke');
    const st = document.createElement('style');
    st.textContent = '*{transition:none!important;animation:none!important}.buehne{border-radius:0!important;outline:0!important}';
    k.root.appendChild(st);
  });
  const anzahl = await page.evaluate(() => document.querySelector('odw-klickstrecke').f.szenen.length);
  const ergebnis = { folge: +folge, gestaltung, fps: FPS, szenen: [] };

  for (let i = 0; i < anzahl; i++) {
    // Szene öffnen und warten, bis die Länge der Tonspur bekannt ist (sie bestimmt den Zeitplan).
    const info = await page.evaluate((i) => new Promise((fertig) => {
      const k = document.querySelector('odw-klickstrecke');
      k.zeige(i);
      const weiter = () => setTimeout(() => {
        const sz = k.f.szenen[i];
        fertig({ id: sz.id, kapitel: sz.kapitel, sprechtext: sz.sprechtext, gesamt: k.plan.gesamt, ton: sz.ton || null,
          woerter: k.plan.liste.map((x) => ({ w: x.w, start: x.start })) });
      }, 60);
      if (!k.ton) return weiter();
      if (k.ton.readyState >= 1) return weiter();
      k.ton.addEventListener('loadedmetadata', weiter, { once: true });
      k.ton.addEventListener('error', weiter, { once: true });
    }), i);
    await page.evaluate(() => document.fonts.ready);
    const rahmen = await page.evaluate(() => { const r = document.querySelector('odw-klickstrecke').el.buehne.getBoundingClientRect(); return { x: r.x, y: r.y, width: r.width, height: r.height }; });

    const zustaende = [];
    let letzte = null;
    const schritte = Math.ceil(info.gesamt * FPS);
    for (let f = 0; f <= schritte; f++) {
      const t = Math.min(f / FPS, info.gesamt);
      const sig = await page.evaluate(async (t) => {
        const k = document.querySelector('odw-klickstrecke');
        k.t = t; k.aktualisiere();
        const img = k.el.buehne.querySelector('img');
        if (img) { if (!img.complete) await new Promise((r) => { img.onload = r; img.onerror = r; }); await img.decode().catch(() => {}); }
        return k.el.buehne.innerHTML;
      }, t);
      if (sig !== letzte) {
        const datei = `z-${info.id}-${zustaende.length}.png`;
        await page.screenshot({ path: path.join(aus, datei), clip: rahmen, type: 'png' });
        zustaende.push({ t, datei });
        letzte = sig;
      }
    }
    ergebnis.szenen.push({ ...info, zustaende });
    console.log(`${info.id}: ${info.gesamt.toFixed(1)} s, ${zustaende.length} Zustände`);
  }

  fs.writeFileSync(path.join(aus, 'zustaende.json'), JSON.stringify(ergebnis, null, 1));
  await browser.close();
  srv.close();
  if (fehler.length) { console.error('Fehler beim Laden:', [...new Set(fehler)].join(' | ')); process.exit(2); }
})();
