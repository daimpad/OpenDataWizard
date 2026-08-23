// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Die öffentlichen Harvest-Endpunkte, so wie ein Portal sie abruft.
 *
 * Geprüft wird gegen den Datenbestand aus tests/e2e/seed.php: zwei
 * veröffentlichte Datensätze, einer davon mit Thema „Bildung" und CC BY.
 * Dadurch können die Zusicherungen unbedingt sein — ein `if (count > 0)`
 * bestünde auch dann, wenn der Endpunkt gar nichts liefert.
 */

const API_BASE = '/wp-json/datenatlas/v1';

/**
 * Liest die Datensatz-IDs aus dem Katalog.
 *
 * `@id` ist die REST-URL des Datensatzes (.../datasets/123) — die ID ist das
 * letzte Pfadsegment.
 *
 * @param {import('@playwright/test').APIRequestContext} request
 * @returns {Promise<string[]>}
 */
async function catalogDatasetIds(request) {
  const response = await request.get(`${API_BASE}/catalog?per_page=100`);
  expect(response.status()).toBe(200);

  const data = await response.json();
  const datasets = data['dcat:dataset'] || [];

  return datasets.map((entry) => String(entry['@id'] || '').split('/').filter(Boolean).pop());
}

test.describe('Harvest-Endpunkte', () => {
  test('/catalog liefert einen dcat:Catalog mit den gesäten Datensätzen', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog`, {
      headers: { Accept: 'application/ld+json' },
    });

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('json');

    const data = await response.json();
    expect(data['@type']).toBe('dcat:Catalog');
    expect(Array.isArray(data['dcat:dataset'])).toBe(true);
    expect(data['dcat:dataset'].length).toBeGreaterThanOrEqual(2);
  });

  test('/catalog setzt die Paginierungs- und Cache-Header', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?page=1&per_page=20`);
    const headers = response.headers();

    expect(response.status()).toBe(200);
    expect(Number(headers['x-wp-total'])).toBeGreaterThanOrEqual(2);
    expect(Number(headers['x-wp-totalpages'])).toBeGreaterThanOrEqual(1);
    expect(['HIT', 'MISS']).toContain(headers['x-odw-cache']);
  });

  test('/catalog?per_page begrenzt die Trefferzahl', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?per_page=1`);
    const data = await response.json();

    expect(response.status()).toBe(200);
    expect(data['dcat:dataset']).toHaveLength(1);
  });

  test('/catalog?theme filtert auf das gewählte Thema', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?theme=Bildung`);
    const data = await response.json();

    expect(response.status()).toBe(200);
    expect(data['dcat:dataset'].length).toBeGreaterThanOrEqual(1);

    const titles = JSON.stringify(data['dcat:dataset']);
    expect(titles).toContain('Schulstandorte');
    expect(titles).not.toContain('Vereinsregister');
  });

  test('/catalog?license filtert auf die gewählte Lizenz', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?license=cc-by`);
    const data = await response.json();

    expect(response.status()).toBe(200);
    expect(JSON.stringify(data['dcat:dataset'])).toContain('Schulstandorte');
  });

  test('/catalog liefert einen @context mit den erwarteten Namensräumen', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog`);
    const context = (await response.json())['@context'];

    for (const prefix of ['dcat', 'dct', 'foaf', 'dcatde']) {
      expect(context).toHaveProperty(prefix);
    }
  });

  test('/catalog?full=1&format=turtle liefert Turtle', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?full=1&format=turtle`);

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('text/turtle');

    const body = await response.text();
    expect(body).toContain('a dcat:Catalog');
    expect(body).toContain('dcat:dataset');
  });

  test('ohne ?format entscheidet der Accept-Header', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog`, {
      headers: { Accept: 'text/turtle' },
    });

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('text/turtle');
    expect(response.headers()['vary']).toContain('Accept');
  });

  test('/datasets/{id} liefert den einzelnen Datensatz', async ({ request }) => {
    const ids = await catalogDatasetIds(request);
    expect(ids.length).toBeGreaterThanOrEqual(2);

    const response = await request.get(`${API_BASE}/datasets/${ids[0]}`);
    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data['@type']).toBe('dcat:Dataset');
    expect(data).toHaveProperty('dct:title');
    expect(data).toHaveProperty('dct:publisher');
    expect(data).toHaveProperty('dcat:distribution');
  });

  test('/datasets/{unbekannt} antwortet mit 404', async ({ request }) => {
    const response = await request.get(`${API_BASE}/datasets/99999999`);

    expect(response.status()).toBe(404);
  });

  test('/delta?since liefert die seit dem Zeitpunkt geänderten Datensätze', async ({ request }) => {
    const since = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString();
    const response = await request.get(`${API_BASE}/delta?since=${encodeURIComponent(since)}`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('@type');
    // Die Saat liegt innerhalb der letzten sieben Tage, muss also enthalten sein.
    expect(data['dcat:dataset'].length).toBeGreaterThanOrEqual(2);
  });

  test('/delta ohne since wird abgewiesen', async ({ request }) => {
    const response = await request.get(`${API_BASE}/delta`);

    expect([400, 422]).toContain(response.status());
  });

  test('/delta mit unlesbarem Datum wird abgewiesen', async ({ request }) => {
    const response = await request.get(`${API_BASE}/delta?since=kein-datum`);

    expect([400, 422]).toContain(response.status());
  });

  test('/delta akzeptiert die gängigen ISO-8601-Schreibweisen', async ({ request }) => {
    const variants = ['2020-01-01', '2020-01-01T00:00:00Z', '2020-01-01T00:00:00+02:00'];

    for (const since of variants) {
      const response = await request.get(`${API_BASE}/delta?since=${encodeURIComponent(since)}`);

      expect(response.status(), `since=${since}`).toBe(200);
      expect(await response.json()).toHaveProperty('dcat:dataset');
    }
  });

  test('der zweite Abruf kommt aus dem Cache', async ({ request }) => {
    const url = `${API_BASE}/catalog?per_page=7&page=1`;

    await request.get(url);
    const second = await request.get(url);

    expect(second.headers()['x-odw-cache']).toBe('HIT');
  });

  test('per_page wird nach oben begrenzt', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?per_page=1000`);

    // Die Registrierung begrenzt auf 100 — entweder weist die Validierung den
    // Wert ab, oder es kommen höchstens 100 Einträge zurück.
    if (response.status() === 200) {
      const data = await response.json();
      expect(data['dcat:dataset'].length).toBeLessThanOrEqual(100);
    } else {
      expect([400, 422]).toContain(response.status());
    }
  });

  test('der Custom Post Type ist nicht über die WP-REST-API erreichbar', async ({ request }) => {
    // Bewusste Entscheidung des Plugins: Die Datensätze verlassen das System
    // ausschließlich über die DCAT-Endpunkte, nicht über /wp/v2.
    const response = await request.get('/wp-json/wp/v2/odw_dataset');

    expect(response.status()).toBe(404);
  });
});
