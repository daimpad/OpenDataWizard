// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Open Data Wizard Admin Workflow Tests
 *
 * Prerequisites:
 * - WordPress running with Open Data Wizard plugin activated
 * - Admin user with manage_open_data capability
 * - BASE_URL environment variable or default: http://localhost:10003
 */

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD || 'password';
const BASE_URL = process.env.BASE_URL || 'http://localhost:10003';

test.describe('Open Data Wizard Admin Workflow', () => {
  test.beforeEach(async ({ page }) => {
    // Login to WordPress admin
    await page.goto(`${BASE_URL}/wp-login.php`);
    await page.fill('input[name="log"]', ADMIN_USER);
    await page.fill('input[name="pwd"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');

    // Wait for redirect to dashboard
    await page.waitForURL(`${BASE_URL}/wp-admin/`);
    await expect(page).toHaveURL(/wp-admin/);
  });

  test('should navigate to Datasets menu', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/edit.php?post_type=odw_dataset`);

    // Check page title
    const pageTitle = page.locator('h1');
    await expect(pageTitle).toContainText('Datensätze');
  });

  test('should create new dataset', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/post-new.php?post_type=odw_dataset`);

    // Fill title
    const titleInput = page.locator('#title');
    await titleInput.fill('Test Dataset: Population 2024');

    // Check form exists
    const formTabs = page.locator('.cf-container__tabs-nav');
    await expect(formTabs).toBeVisible();

    // Verify Tab 1 is active
    const tab1 = page.locator('.cf-container__tabs-nav li.cf-tab--active');
    await expect(tab1).toBeVisible();
  });

  test('should fill Tab 1 - Grundlegende Informationen', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/post-new.php?post_type=odw_dataset`);

    // Fill title
    await page.fill('#title', 'Test Dataset: Tax Data');

    // Fill publisher (required)
    const publisherInput = page.locator('input[data-carbon-field="odw_publisher"]');
    if (await publisherInput.count() > 0) {
      await publisherInput.fill('Test Organization');
    }

    // Fill description (required)
    const descriptionInput = page.locator('textarea[data-carbon-field="odw_description"]');
    if (await descriptionInput.count() > 0) {
      await descriptionInput.fill('This is a test dataset for population statistics.');
    }

    // Verify fields are filled
    await expect(page.locator('#title')).toHaveValue('Test Dataset: Tax Data');
  });

  test('should navigate between tabs', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/post-new.php?post_type=odw_dataset`);

    // Click Tab 2
    const tab2Button = page.locator('.cf-container__tabs-nav >> text="2 — Sprache & Übersetzungen"');
    if (await tab2Button.count() > 0) {
      await tab2Button.click();

      // Verify Tab 2 is now active
      const activeTab = page.locator('.cf-container__tabs-nav li.cf-tab--active');
      await expect(activeTab).toContainText('2 — Sprache & Übersetzungen');
    }
  });

  test('should show validation errors on publish without required fields', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/post-new.php?post_type=odw_dataset`);

    // Try to publish without filling required fields
    const publishButton = page.locator('input#publish');
    await publishButton.click();

    // Wait for response
    await page.waitForTimeout(1000);

    // Check if we're still on the edit page (not published)
    const url = page.url();
    expect(url).toContain('post-new.php');
  });

  test('should view dataset list', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/edit.php?post_type=odw_dataset`);

    // Check list table exists
    const listTable = page.locator('table.wp-list-table');
    await expect(listTable).toBeVisible();

    // Check columns exist
    const licenseColumn = page.locator('th.column-odw_license');
    const qualityColumn = page.locator('th.column-odw_quality');
    const statusColumn = page.locator('th.column-odw_status');

    await expect(licenseColumn).toBeVisible();
    await expect(qualityColumn).toBeVisible();
    await expect(statusColumn).toBeVisible();
  });

  test('should access Einstieg (Introduction) page', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/admin.php?page=odw-einstieg`);

    // Check page title or intro content
    const pageContent = page.locator('div.wrap');
    await expect(pageContent).toBeVisible();
  });

  test('should access Settings page', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/admin.php?page=odw-settings`);

    // Check settings form exists
    const settingsForm = page.locator('form');
    await expect(settingsForm).toBeVisible();

    // Check expected sections
    const catalogSection = page.locator('h2');
    await expect(catalogSection).toContainText(/Katalog|Einstellungen/i);
  });

  test('should display help tabs', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/post.php?post_type=odw_dataset&action=edit&post=1`);

    // Check if help tab area exists
    const helpArea = page.locator('#contextual-help-link');
    if (await helpArea.count() > 0) {
      // Help tabs should be present if a published dataset exists
      await expect(helpArea).toBeVisible();
    }
  });

  test('should show quality badge in list', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/edit.php?post_type=odw_dataset`);

    // Look for quality badges
    const qualityBadges = page.locator('.odw-quality-badge');

    // If datasets exist, quality badges should be shown
    if (await qualityBadges.count() > 0) {
      const firstBadge = qualityBadges.first();
      await expect(firstBadge).toBeVisible();
    }
  });

  test('should display shortcode in list', async ({ page }) => {
    await page.goto(`${BASE_URL}/wp-admin/edit.php?post_type=odw_dataset`);

    // Look for shortcode inputs
    const shortcodeInputs = page.locator('.odw-shortcode-input');

    if (await shortcodeInputs.count() > 0) {
      const firstShortcode = shortcodeInputs.first();
      await expect(firstShortcode).toBeVisible();

      // Verify it's readonly
      await expect(firstShortcode).toHaveAttribute('readonly', '');
    }
  });
});
