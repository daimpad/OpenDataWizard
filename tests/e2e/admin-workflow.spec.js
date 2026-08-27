// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Der Weg, den Redakteur:innen im Backend tatsächlich gehen.
 *
 * Die Testumgebung läuft auf Deutsch (siehe `env:start` in package.json). Das
 * ist kein Detail: Die Quellsprache des Plugins ist Deutsch, die mitgelieferte
 * en_US-Übersetzung überschreibt sie auf englischen Installationen. Ein
 * WordPress in en_US zeigt daher die *Übersetzung* — die Beschriftungen, um
 * die es der Redaktion geht, stehen nur im deutschen Original.
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

    // Auf thead eingegrenzt: WordPress rendert dieselben Spaltenköpfe ein
    // zweites Mal im tfoot, ein ungebundener Selektor trifft also zwei
    // Elemente und Playwright bricht mit einer Strict-Mode-Verletzung ab.
    for (const column of ['odw_license', 'odw_theme', 'odw_quality', 'odw_status', 'odw_shortcode']) {
      await expect(page.locator(`thead th.column-${column}`)).toBeVisible();
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

  test('„Mehr erfahren" zeigt Merkmale, Erklärung und Definition', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    // Das Panel hängt am Feld, nicht am Reiter — der Herausgeber steht in Tab 1.
    const panel = page.locator('.cf-field', { has: page.locator(cfField('odw_publisher')) })
      .locator('details.odw-field-more');
    await expect(panel).toBeVisible();
    await panel.locator('summary').click();

    // Zwei Merkmalszeilen über der Definition: Eigenschaft und Multiplizität.
    // Beide standen vorher nur mitten im Fließtext.
    const facts = panel.locator('.odw-field-more__facts');
    await expect(facts.locator('dt')).toHaveText(['Eigenschaft', 'Multiplizität']);
    await expect(facts.locator('dd')).toHaveText(['dct:publisher', '1..1']);

    // Darunter unverändert beide Langtexte.
    await expect(panel).toContainText('Einfach erklärt');
    await expect(panel).toContainText('DCAT-AP-Definition');

    // Und ein Link auf den Abschnitt der Spezifikation, der zu dieser
    // Profil-Klasse gehört — der Herausgeber steht am Datensatz.
    const spec = panel.locator('a.odw-field-more__spec');
    await expect(spec).toHaveAttribute(
      'href',
      'https://www.dcat-ap.de/def/dcatde/3.0/spec/#datensatz'
    );
    await expect(spec).toContainText('Datensatz');
  });

  test('das Tooltip trägt nur noch den Fachbegriff', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    // Rollentrennung: Im ⓘ steht der DCAT-AP-Begriff, alles Erklärende liegt
    // im Panel. Das Beispiel, das früher hier stand, darf nicht zurückkehren.
    const tip = page.locator('.cf-field', { has: page.locator(cfField('odw_publisher')) })
      .locator('.odw-help-pop');
    await expect(tip).toContainText('dct:publisher');
    await expect(tip).not.toContainText('Beispiel');
  });

  test('Thema ist eine Mehrfachauswahl mit den EU-Themen', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    // Seit v2.41.0 ein Mehrfachfeld: dcat:theme erlaubt 0..n Themen. Vorher
    // gab es ein Auswahlfeld hier und ein Tipp-Feld unter „Erweiterte Angaben".
    const themeField = page.locator('.cf-field', { has: page.locator(cfField('odw_theme')) });
    await expect(themeField).toBeVisible();
    // Carbon Fields 3.6 rendert multiselect über react-select: ein <div> mit der
    // Klasse cf-multiselect__select, kein <select multiple>. Die Klasse
    // „cf-multiselect" allein gibt es nicht — sie ist nur der classNamePrefix
    // für cf-multiselect__control, __menu und so weiter.
    await expect(themeField.locator('.cf-multiselect__select')).toHaveCount(1);

    // Das frühere Zusatzfeld gibt es nicht mehr.
    await expect(page.locator(cfField('odw_theme_uri'))).toHaveCount(0);
  });

  test('Engagementfeld ist eine Mehrfachauswahl, keine Vorschlagsliste', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    // Seit v2.41.0 ein Mehrfachfeld mit den 16 ZiviZ-Feldern. Vorher ein
    // Textfeld mit datalist — die Auswahl war erst nach dem Tippen zu sehen.
    await page.locator('[data-odw-section-toggle="tab1extra"]').click();

    const feld = page.locator('.cf-field', { has: page.locator(cfField('odw_engagementfeld')) });
    await expect(feld.locator('.cf-multiselect__select')).toHaveCount(1);

    // react-select hängt die Optionen erst ins DOM, wenn das Menü offen ist —
    // ohne den Klick steht dort nur der Platzhalter, und eine Zusicherung auf
    // den Optionstext wäre grün, egal welche Optionen das Feld hat.
    await feld.locator('.cf-multiselect__control').click();
    await expect(feld.locator('.cf-multiselect__menu')).toContainText('Umwelt- und Naturschutz');
  });

  test('die CESSDA-Vorschläge öffnen sich beim Anklicken', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    await page.locator('[data-odw-section-toggle="tab1extra"]').click();

    // Eigene Liste statt <datalist>: Ein natives datalist ist Browser-Chrome
    // und für Playwright unsichtbar — und es öffnet erst beim Tippen, was der
    // Tester bemängelt hatte.
    const eingabe = page.locator('.odw-cessda-input');
    const liste = page.locator('.odw-suggest__list');

    await expect(liste).toBeHidden();
    await eingabe.click();
    await expect(liste).toBeVisible();

    // Leeres Feld zeigt alles, Tippen grenzt ein.
    const alle = await liste.locator('li:not([hidden])').count();
    expect(alle).toBeGreaterThan(1);

    await eingabe.fill('Migration');
    const gefiltert = await liste.locator('li:not([hidden])').count();
    expect(gefiltert).toBeLessThan(alle);

    // Ein Klick auf den Vorschlag trägt das Label ein und legt die URI im
    // verborgenen Feld ab — das ist der Wert, der als dct:subject rausgeht.
    await liste.locator('li:not([hidden])').first().click();
    await expect(liste).toBeHidden();
    await expect(page.locator(cfField('odw_cessda_topic'))).toHaveValue(/^https?:\/\//);
  });

  test('ein frisch geöffnetes Formular startet bei 0 %', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=odw_dataset');

    // WordPress legt beim Öffnen einen Auto-Entwurf an und feuert dabei
    // save_post. Vorher zählte der Bericht dessen Vorgabewerte mit — die
    // Zugriffsrechte stehen auf „öffentlich", das Änderungsdatum wurde
    // gestempelt — und das leere Formular startete bei 7 %.
    const box = page.locator('#odw-quality-report');
    await expect(box.locator('.odw-quality-percent')).toHaveText('0 %');
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

    // Die Katalog-URLs stehen in schreibgeschützten Eingabefeldern (zum
    // Markieren per Klick). Ihr Wert ist kein Textknoten — toContainText
    // findet ihn deshalb nie, egal wie die Seite aussieht.
    const harvestUrl = page.locator('.wrap input[readonly]').first();
    await expect(harvestUrl).toHaveValue(/datenatlas\/v1\/catalog/);
  });
});
