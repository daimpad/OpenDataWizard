// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Beispieldaten: mit einem Klick laden, nur für die Redaktion sichtbar, in
 * keinem Harvest-Endpunkt, mit einem Klick wieder weg.
 *
 * Ein einziger Test, weil die Schritte aufeinander aufbauen — und weil die
 * übrigen Dateien Datensätze zählen: Die Beispiele dürfen nur während dieses
 * Tests existieren. Vorher und nachher setzt die Saat (odw-e2e/v1/samples)
 * sie zurück, auch wenn der Test unterwegs scheitert.
 *
 * Woran die Beispiele zu erkennen sind: Alle ihre Adressen liegen unter
 * `musterstadt.example` bzw. anderen `.example`-Domains, die gesäten
 * Datensätze nicht.
 */

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD || 'password';

const INTRO = '/wp-admin/edit.php?post_type=odw_dataset&page=odw-einstieg';
const LIST = '/wp-admin/edit.php?post_type=odw_dataset';
const CATALOG = '/odw-e2e-katalog-3a/';
const API = '/wp-json/datenatlas/v1';
const SAMPLE_DOMAIN = 'musterstadt.example';
const SAMPLE_PUBLISHER = 'Stadtwerke Musterstadt GmbH';

test.describe.configure({ mode: 'serial' });

test.beforeAll(async ({ request }) => {
  expect((await request.delete('/wp-json/odw-e2e/v1/samples')).ok()).toBe(true);
});

test.afterAll(async ({ request }) => {
  expect((await request.delete('/wp-json/odw-e2e/v1/samples')).ok()).toBe(true);
});

test('Beispieldaten: laden, nur für die Redaktion, nie im Endpunkt, entfernen', async ({ page, browser, request }) => {
  // 42 Datensätze anlegen, prüfen und veröffentlichen dauert, dazu zwei
  // Browser-Sitzungen — mehr als die üblichen 30 Sekunden.
  test.setTimeout(120_000);

  await page.goto('/wp-login.php');
  await page.fill('#user_login', ADMIN_USER);
  await page.fill('#user_pass', ADMIN_PASSWORD);
  await page.click('#wp-submit');
  await expect(page.locator('#wpadminbar')).toBeVisible();

  // Laden auf der Einstiegsseite.
  await page.goto(INTRO);
  const box = page.locator('#odw-beispieldaten');
  await expect(box).toContainText('Beispieldaten zum Ausprobieren');
  await box.getByRole('button', { name: 'Beispieldaten laden' }).click();

  await expect(page).toHaveURL(/odw_samples=loaded/);
  await expect(page.locator('.notice-success')).toContainText('Beispieldatensätze wurden angelegt und veröffentlicht');
  const sampleRow = page.locator('tr', { has: page.locator('.post-state', { hasText: 'Beispieldaten' }) }).first();
  await expect(sampleRow).toBeVisible();
  const sampleId = Number(String(await sampleRow.getAttribute('id')).replace('post-', ''));
  expect(sampleId).toBeGreaterThan(0);

  // In keinem Endpunkt — ohne Anmeldung, wie ein Harvester.
  const catalog = await request.get(`${API}/catalog?full=1`);
  expect(catalog.status()).toBe(200);
  const catalogText = await catalog.text();
  expect(catalogText).toContain('E2E: Schulstandorte');
  expect(catalogText).not.toContain(SAMPLE_DOMAIN);

  const turtle = await request.get(`${API}/catalog?full=1&format=turtle`);
  expect(turtle.status()).toBe(200);
  expect(await turtle.text()).not.toContain(SAMPLE_DOMAIN);

  const delta = await request.get(`${API}/delta?since=2000-01-01`);
  expect(delta.status()).toBe(200);
  expect(await delta.text()).not.toContain(SAMPLE_DOMAIN);

  expect((await request.get(`${API}/datasets/${sampleId}`)).status()).toBe(404);

  // Die Redaktion sieht sie im Katalog, gekennzeichnet.
  await page.goto(CATALOG);
  const editorCatalog = page.locator('section#odw-catalog');
  await expect(editorCatalog.locator('.odw-badge--sample').first()).toBeVisible();
  await expect(editorCatalog.locator('#odw-filter-publisher')).toContainText(SAMPLE_PUBLISHER);

  const editorDetail = await page.goto(`${CATALOG}?odw_ds=${sampleId}`);
  expect(editorDetail?.status()).toBe(200);
  await expect(page.locator('.odw-download-card__sample')).toBeVisible();
  await expect(page.locator('.odw-download-card__metajson')).toHaveCount(0);
  await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);

  // Besucher sehen sie nicht — weder in der Liste noch in den Filtern.
  const visitor = await browser.newContext();
  const anon = await visitor.newPage();
  await anon.goto(CATALOG);
  const visitorCatalog = anon.locator('section#odw-catalog');
  await expect(visitorCatalog.locator('.odw-catalog__item').first()).toBeVisible();
  await expect(visitorCatalog.locator('.odw-badge--sample')).toHaveCount(0);
  await expect(visitorCatalog.locator('#odw-filter-publisher')).not.toContainText(SAMPLE_PUBLISHER);
  const visitorDetail = await anon.goto(`${CATALOG}?odw_ds=${sampleId}`);
  expect(visitorDetail?.status()).toBe(404);
  await visitor.close();

  // Entfernen über den Hinweis in der Datensatzliste.
  await page.goto(LIST);
  page.once('dialog', (dialog) => dialog.accept());
  await page.locator('.odw-samples-notice').getByRole('button', { name: 'Beispieldaten entfernen' }).click();
  await expect(page).toHaveURL(/odw_samples=removed/);
  await expect(page.locator('.notice-success')).toContainText('Beispieldatensätze endgültig gelöscht');
  await expect(page.locator('.post-state', { hasText: 'Beispieldaten' })).toHaveCount(0);
});
