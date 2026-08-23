// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright-Konfiguration für die End-to-End-Tests.
 *
 * Die Tests brauchen eine laufende WordPress-Installation mit aktiviertem
 * Plugin. Die liefert `wp-env` (Docker):
 *
 *   npm run env:start        # WordPress starten, Plugin einhängen, Saat legen
 *   npm run test:e2e         # Tests gegen die Testumgebung (Port 8889)
 *   npm run env:stop
 *
 * Gegen eine andere Installation: BASE_URL, WP_ADMIN_USER und WP_ADMIN_PASSWORD
 * setzen.
 */

// Die Testumgebung von wp-env, nicht die Entwicklungsumgebung (8888): Sie wird
// bei jedem `wp-env clean tests` zurückgesetzt und stört die lokale Spielwiese
// nicht.
const BASE_URL = process.env.BASE_URL || 'http://localhost:8889';

module.exports = defineConfig({
  testDir: './tests/e2e',
  testMatch: '**/*.spec.js',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,

  reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',

  use: {
    baseURL: BASE_URL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],
});
