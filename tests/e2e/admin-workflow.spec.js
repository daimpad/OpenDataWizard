// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Der Weg, den Redakteur:innen im Backend tatsächlich gehen.
 *
 * Bewusst ohne `if (await x.count() > 0)`: Eine Zusicherung, die bei fehlendem
 * Element übersprungen wird, ist grün, wenn die Oberfläche kaputt ist. Was hier
 * geprüft wird, muss da sein — der Datenbestand aus tests/e2e/seed.php sorgt
 * dafür, dass es da sein kann.
 */

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD || 'password';

/** Basis-URL der Datensatzliste — alle Unterseiten hängen an diesem Menüpunkt. */
const LIST_URL = '/wp-admin/edit.php?post_type=odw_dataset';

/**
 * Selektor für ein Carbon-Fields-Eingabefeld.
 *
 * Über das name-Attribut, nicht über data-Attribute: Carbon Fields reicht
 * data-Attribute nicht zuverlässig ans DOM durch, der Meta-Key steht dagegen
 * immer im Namen.
 *
 * @param {string} metaKey Meta-Key ohne führenden Unterstrich, z. B. "odw_publisher".
 * @returns {string}
 */
function cfField(metaKey) {
  return `[name$="[_${metaKey}]"]`;
}

test.beforeEach(async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', ADMIN_USER);
  await page.fill('#user_pass', ADMIN_PASSWORD);
  await page.click('#wp-submit');

  await expect(page.locator('#wpadminbar')).toBeVisible();
});

test.describe('Datensatzliste', () => {
  test('zeigt die Datensätze mit allen Zusatzspalten', async ({ page }) => {
    await page.goto(LIST_URL);

    await expect(page.locator('h1.wp-heading-inline')).toContainText('Datensätze');

    for (const column of ['odw_license', 'odw_theme', 'odw_quality', 'odw_status', 'odw_shortcode']) {
      await expect(page.locator(`th.column-${column}`)).toBeVisible();
    }

    await expect(page.locator('table.wp-list-table tbody tr')).not.toHaveCount(0);
    await expect(page.locator('table.wp-list-table tbody')).toContainText('Schulstandorte');
  });

  test('zeigt Qualitätsbadge und Shortcode je Zeile', async ({ page }) => {
    await page.goto(LIST_URL);

    // Nicht `.first()` ohne Filter: Ein noch nicht berechneter Datensatz zeigt
    // „—" statt einer Prozentzahl, und in welcher Reihenfolge die Zeilen stehen,
    // hängt am Anlagezeitpunkt.
    const badge = page.locator('.odw-quality-badge', { hasText: '%' }).first();
    await expect(badge).toBeVisible();

    const shortcode = page.locator('.odw-shortcode-input').first();
    await expect(shortcode).toBeVisible();
    await expect(shortcode).toHaveAttribute('readonly', '');
    await expect(shortcode).toHaveValue(/\[odw_dataset id="\d+"\]/);
  });
});

test.describe('Formular', () => {
  test('öffnet mit fünf Reitern, der erste ist aktiv', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    // Carbon Fields 3.6 rendert ul.cf-container__tabs-list > li.cf-container__tabs-item
    // mit einem <button> als Klickfläche; der aktive Reiter trägt --current.
    // Dieselben Klassennamen adressiert der Block FORMULAR-DESIGN in
    // assets/css/admin.css — ändert Carbon Fields sie, fällt es hier auf.
    const tabs = page.locator('.cf-container__tabs-list .cf-container__tabs-item');
    await expect(tabs).toHaveCount(5);
    await expect(tabs.first()).toContainText('Grundlegende Informationen');
    await expect(page.locator('.cf-container__tabs-item--current')).toHaveCount(1);
  });

  test('nimmt Titel, Herausgeber und Beschreibung entgegen', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    await page.fill('#title', 'E2E: Radverkehrszählstellen');
    await page.fill(cfField('odw_publisher'), 'Stadt Musterstadt');
    await page.fill(cfField('odw_description'), 'Zählstellen für den Radverkehr, stündliche Werte.');

    await expect(page.locator('#title')).toHaveValue('E2E: Radverkehrszählstellen');
    await expect(page.locator(cfField('odw_publisher'))).toHaveValue('Stadt Musterstadt');
  });

  test('wechselt auf den zweiten Reiter', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    await page.locator('.cf-container__tabs-item button', { hasText: 'Sprache' }).click();

    await expect(page.locator('.cf-container__tabs-item--current')).toContainText('Sprache');
    await expect(page.locator(cfField('odw_language'))).toBeVisible();
  });

  test('blockt die Veröffentlichung ohne Pflichtangaben und benennt die Felder', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    await page.fill('#title', 'E2E: Unvollständig');
    await page.click('#publish');

    // Der Beitrag darf nicht veröffentlicht sein, und die Meldung muss sagen,
    // was fehlt — „konnte nicht gespeichert werden" allein hilft niemandem.
    const notice = page.locator('.notice-error');
    await expect(notice).toBeVisible();
    await expect(notice).toContainText('Herausgeb');
    await expect(page.locator('#post-status-display')).toContainText('Entwurf');
  });

  test('speichert einen unvollständigen Entwurf', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    await page.fill('#title', 'E2E: Entwurf ohne Pflichtfelder');
    await page.click('#save-post');

    await expect(page.locator('#message.notice-success')).toBeVisible();
    await expect(page.locator('#title')).toHaveValue('E2E: Entwurf ohne Pflichtfelder');
  });
});

test.describe('Plugin-Seiten', () => {
  test('Einstiegsseite erklärt den Ablauf', async ({ page }) => {
    await page.goto(`${LIST_URL}&page=odw-einstieg`);

    await expect(page.locator('h1.odw-page-title')).toContainText('Einstieg');
    await expect(page.locator('.odw-introduction-page')).toBeVisible();
    await expect(page.locator('img.odw-introduction-figure')).toBeVisible();
    await expect(
      page.locator('.odw-introduction-page a.button-primary')
    ).toContainText('Neuen Datensatz erstellen');
  });

  test('Einstellungsseite zeigt die Harvest-URLs', async ({ page }) => {
    await page.goto(`${LIST_URL}&page=odw-settings`);

    // Der WP-Admin bringt eigene Formulare mit (Suche, Bildschirmoptionen);
    // hier zählt nur, dass die Seite überhaupt eines rendert.
    await expect(page.locator('.wrap form').first()).toBeVisible();
    await expect(page.locator('body')).toContainText('datenatlas/v1/catalog');
  });
});
