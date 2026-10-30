// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Der Datenkatalog im Frontend: Shortcode [odw_catalog].
 *
 * Die Seiten legt tests/e2e/mu-plugins/odw-e2e-seed.php an:
 *   /odw-e2e-katalog/         [odw_catalog]
 *   /odw-e2e-katalog-seiten/  [odw_catalog per_page="1"]
 * Dazu kommen ein Entwurf und ein passwortgeschützter Datensatz, die der
 * Katalog nirgends zeigen darf.
 *
 * Die Zählungen sind selbstbezüglich statt absolut: Ob der Beispieldatensatz
 * existiert, hängt davon ab, ob vorher eine Admin-Seite aufgerufen wurde. Der
 * Katalog blendet ihn zwar aus — fest steht trotzdem nur, dass die Trefferzeile
 * zur Zahl der Einträge passt und die gesäten Datensätze darunter sind.
 */

const CATALOG = '/odw-e2e-katalog/';
const PAGED = '/odw-e2e-katalog-seiten/';
const FILTERED = '/odw-e2e-katalog-filter/';

/** Trefferzeile für n Datensätze auf einer Seite. */
function countLine(n) {
  return n === 1 ? 'Ein Datensatz' : `${n} Datensätze`;
}

/** Zahl in Klammern am Ende einer Facetten-Option, z. B. „Umwelt (3)". */
async function optionCount(select, value) {
  const text = await select.locator(`option[value="${value}"]`).innerText();
  const match = text.match(/\((\d+)\)$/);
  expect(match, `Option ${value}: ${text}`).not.toBeNull();
  return Number(match[1]);
}

test.describe('Datenkatalog im Frontend', () => {
  test('listet die veröffentlichten Datensätze', async ({ page }) => {
    const response = await page.goto(CATALOG);
    expect(response?.status()).toBe(200);

    const catalog = page.locator('section#odw-catalog');
    await expect(catalog).toBeVisible();
    await expect(catalog).toContainText('E2E: Schulstandorte');
    await expect(catalog).toContainText('E2E: Vereinsregister');

    const items = catalog.locator('.odw-catalog__item');
    const n = await items.count();
    expect(n).toBeGreaterThanOrEqual(2);
    await expect(catalog.locator('.odw-catalog__count')).toHaveText(`${n} Datensätze`);
  });

  test('zeigt weder Entwürfe noch passwortgeschützte Datensätze', async ({ page }) => {
    await page.goto(CATALOG);

    const catalog = page.locator('section#odw-catalog');
    await expect(catalog.locator('.odw-catalog__item').first()).toBeVisible();
    await expect(catalog).not.toContainText('E2E: Entwurf (nicht im Katalog)');
    await expect(catalog).not.toContainText('E2E: Passwortgeschützt (nicht im Katalog)');
  });

  test('Badges und Hauptlink kommen aus den Metadaten', async ({ page }) => {
    await page.goto(CATALOG);

    const entry = page.locator('.odw-entry', { hasText: 'E2E: Schulstandorte' });
    await expect(entry.locator('.odw-badge--theme')).toHaveText('Bildung, Kultur & Sport');
    await expect(entry.locator('.odw-badge--format')).toHaveText('CSV');
    await expect(entry.locator('.odw-badge--license')).toContainText('CC BY 4.0');

    // Gesät ist nur eine Zugriffs-URL, keine Datei: Der Link heißt deshalb
    // „Zum Datenangebot" und trägt kein download-Attribut.
    const action = entry.locator('a.odw-entry__action');
    await expect(action).toHaveAttribute('href', 'https://example.org/schulstandorte.csv');
    expect(await action.getAttribute('download')).toBeNull();
    await expect(action).toContainText('Zum Datenangebot');
  });

  test('das Stylesheet steht im Head', async ({ page }) => {
    await page.goto(CATALOG);

    // Prüft nur das Ergebnis. Im Block-Theme der Testumgebung rendert
    // WordPress den Inhalt vor wp_head(), das Stylesheet landet dort also
    // auch ohne ODW_Catalog::maybe_enqueue_assets() im Head. Das frühe
    // Einbinden selbst sichern die Unit-Tests in tests/test-catalog.php.
    await expect(page.locator('head link#odw-frontend-css')).toHaveCount(1);
  });

  test('öffnet die Detailansicht auf derselben Seite und führt zurück', async ({ page }) => {
    await page.goto(CATALOG);
    await page.locator('.odw-entry__title a', { hasText: 'E2E: Schulstandorte' }).click();

    await expect(page).toHaveURL(/[?&]odw_ds=\d+/);
    const card = page.locator('#odw-catalog .odw-download-card');
    await expect(card).toBeVisible();
    await expect(card.locator('.odw-download-card__title')).toHaveText('E2E: Schulstandorte');
    await expect(page.locator('#odw-catalog .odw-catalog__item')).toHaveCount(0);

    await page.locator('.odw-catalog__back a').click();
    await expect(page).not.toHaveURL(/odw_ds=/);
    await expect(page.locator('#odw-catalog .odw-catalog__item').first()).toBeVisible();
  });

  test('Entwurf, Passwortschutz und unbekannte ID antworten gleich', async ({ page, request }) => {
    const response = await request.get('/wp-json/odw-e2e/v1/fixtures');
    expect(response.status()).toBe(200);
    const fixtures = await response.json();
    expect(fixtures.draft).toBeGreaterThan(0);
    expect(fixtures.protected).toBeGreaterThan(0);

    for (const id of [fixtures.draft, fixtures.protected, 999999]) {
      const detail = await page.goto(`${CATALOG}?odw_ds=${id}`);
      // Seit Stufe 2 ein echter 404 — das Seitentemplate bleibt erhalten.
      expect(detail?.status()).toBe(404);
      await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
      await expect(page.locator('#odw-catalog .odw-catalog__empty')).toHaveText('Datensatz nicht gefunden.');
      await expect(page.locator('#odw-catalog .odw-download-card')).toHaveCount(0);
      await expect(page.locator('#odw-catalog .odw-catalog__back a')).toBeVisible();
    }
  });

  test('blättert mit eigener Seitennavigation und merkt sich die Seite', async ({ page }) => {
    await page.goto(PAGED);

    const nav = page.locator('nav.odw-catalog__pagination');
    await expect(nav).toBeVisible();
    await expect(page.locator('.odw-catalog__item')).toHaveCount(1);
    await expect(nav.locator('[aria-current="page"]')).toHaveText('1');
    const first = await page.locator('.odw-entry__title').innerText();

    await nav.getByRole('link', { name: 'Nächste Seite' }).click();
    await expect(page).toHaveURL(/[?&]odw_page=2/);
    await expect(nav.locator('[aria-current="page"]')).toHaveText('2');
    await expect(page.locator('.odw-catalog__item')).toHaveCount(1);
    await expect(page.locator('.odw-entry__title')).not.toHaveText(first);
    await expect(page.locator('.odw-catalog__count')).toHaveText(/^Datensatz 2 von \d+$/);

    // Detailansicht von Seite 2 aus: Der Rückweg führt wieder auf Seite 2.
    await page.locator('.odw-entry__title a').click();
    await expect(page).toHaveURL(/odw_ds=\d+/);
    await expect(page).toHaveURL(/odw_page=2/);
    await page.locator('.odw-catalog__back a').click();
    await expect(page).toHaveURL(/odw_page=2/);
    await expect(page).not.toHaveURL(/odw_ds=/);
  });

  test('eine Seite jenseits des Endes führt zur ersten zurück', async ({ page }) => {
    await page.goto(`${PAGED}?odw_page=999`);

    await expect(page.locator('#odw-catalog .odw-catalog__empty')).toContainText('Diese Seite gibt es nicht.');
    await page.locator('#odw-catalog .odw-catalog__empty a').click();
    await expect(page).not.toHaveURL(/odw_page=/);
    await expect(page.locator('.odw-catalog__item')).toHaveCount(1);
  });
});

test.describe('Datenkatalog: Filter, Suche und Kopfbereich (Stufe 2)', () => {
  test('ein Katalog ohne filters/search bleibt wie in Stufe 1', async ({ page }) => {
    await page.goto(CATALOG);

    await expect(page.locator('#odw-catalog form.odw-catalog__filters')).toHaveCount(0);
    await expect(page.locator('meta[name="robots"]')).not.toHaveAttribute('content', /noindex/);
  });

  test('das Formular bietet Facetten mit Trefferzahlen', async ({ page }) => {
    await page.goto(FILTERED);

    const form = page.locator('#odw-catalog form.odw-catalog__filters');
    await expect(form).toBeVisible();
    await expect(form.getByLabel('Datensätze durchsuchen')).toBeVisible();

    const theme = form.getByLabel('Thema');
    await expect(theme.locator('option').first()).toHaveText('Alle Themen');
    await expect(theme.locator('option[value="EDUC"]')).toHaveText(/^Bildung, Kultur & Sport \(\d+\)$/);
    await expect(form.getByLabel('Lizenz').locator('option[value="cc-by"]')).toHaveText(/^CC BY 4\.0.*\(\d+\)$/);
    await expect(form.getByLabel('Format').locator('option[value="CSV"]')).toHaveText(/^CSV \(\d+\)$/);
    await expect(form.getByLabel('Sortierung')).toBeVisible();
  });

  test('ein Themenfilter grenzt ein, ist noindex und lässt sich zurücksetzen', async ({ page }) => {
    await page.goto(FILTERED);
    const form = page.locator('#odw-catalog form.odw-catalog__filters');
    const expected = await optionCount(form.getByLabel('Thema'), 'EDUC');

    await form.getByLabel('Thema').selectOption('EDUC');
    await form.getByRole('button', { name: 'Anzeigen' }).click();

    await expect(page).toHaveURL(/[?&]odw_theme=EDUC/);
    const catalog = page.locator('section#odw-catalog');
    await expect(catalog.locator('.odw-catalog__count')).toHaveText(countLine(expected));
    await expect(catalog).toContainText('E2E: Schulstandorte');
    await expect(catalog).not.toContainText('E2E: Vereinsregister');
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
    await expect(form.getByLabel('Thema')).toHaveValue('EDUC');

    await catalog.getByRole('link', { name: 'Filter zurücksetzen' }).first().click();
    await expect(page).not.toHaveURL(/odw_theme=/);
    await expect(catalog).toContainText('E2E: Vereinsregister');
  });

  test('Lizenz- und Formatfilter laufen über die Index-Felder', async ({ page }) => {
    await page.goto(`${FILTERED}?odw_license=cc0`);
    const catalog = page.locator('section#odw-catalog');
    await expect(catalog).toContainText('E2E: Vereinsregister');
    await expect(catalog).not.toContainText('E2E: Schulstandorte');

    await page.goto(`${FILTERED}?odw_format=CSV`);
    await expect(catalog).toContainText('E2E: Schulstandorte');
    await expect(catalog).not.toContainText('E2E: Vereinsregister');
  });

  test('die Suche findet Titel, Beschreibung und Schlagworte', async ({ page }) => {
    const catalog = page.locator('section#odw-catalog');

    // Schlagwort „Vereine" des Vereinsregisters.
    await page.goto(FILTERED);
    await page.getByLabel('Datensätze durchsuchen').fill('Vereine');
    await page.getByRole('button', { name: 'Anzeigen' }).click();
    await expect(page).toHaveURL(/[?&]odw_q=Vereine/);
    await expect(catalog).toContainText('E2E: Vereinsregister');
    await expect(catalog).not.toContainText('E2E: Schulstandorte');

    // „allgemeinbildenden" steht nur in der Beschreibung der Schulstandorte.
    await page.goto(`${FILTERED}?odw_q=allgemeinbildenden`);
    await expect(catalog).toContainText('E2E: Schulstandorte');
    await expect(catalog).not.toContainText('E2E: Vereinsregister');

    // Nichts gefunden: Leerzustand mit Rückweg.
    await page.goto(`${FILTERED}?odw_q=xyzkeintreffer`);
    await expect(catalog.locator('.odw-catalog__empty')).toContainText('Keine Datensätze gefunden.');
    await expect(catalog.locator('.odw-catalog__empty a')).toHaveText('Filter zurücksetzen');
  });

  test('Detailansicht: eigener Titel, Canonical ohne Listenzustand, Rückweg mit Filter', async ({ page }) => {
    await page.goto(`${FILTERED}?odw_theme=EDUC`);
    await page.locator('.odw-entry__title a', { hasText: 'E2E: Schulstandorte' }).click();

    await expect(page).toHaveURL(/odw_ds=\d+/);
    await expect(page).toHaveURL(/odw_theme=EDUC/);
    const id = new URL(page.url()).searchParams.get('odw_ds');

    await expect(page).toHaveTitle(/^E2E: Schulstandorte/);
    const canonical = await page.locator('link[rel="canonical"]').getAttribute('href');
    expect(canonical).toMatch(new RegExp(`odw-e2e-katalog-filter/\\?odw_ds=${id}$`));
    await expect(page.locator(`link[rel="alternate"][type="application/ld+json"]`)).toHaveAttribute('href', new RegExp(`datasets/${id}`));
    await expect(page.locator('meta[name="robots"]')).not.toHaveAttribute('content', /noindex/);

    await page.locator('.odw-catalog__back a').click();
    await expect(page).toHaveURL(/odw_theme=EDUC/);
    await expect(page).not.toHaveURL(/odw_ds=/);
  });

  test('Listenseiten ab 2 haben einen eigenen Canonical', async ({ page }) => {
    await page.goto(`${PAGED}?odw_page=2`);

    const canonical = await page.locator('link[rel="canonical"]').getAttribute('href');
    expect(canonical).toMatch(/odw-e2e-katalog-seiten\/\?odw_page=2$/);
  });
});
