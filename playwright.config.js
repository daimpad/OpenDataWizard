// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright Configuration for Open Data Wizard E2E Tests
 *
 * Run tests with:
 *   npm run test:e2e              # Run all tests (headless)
 *   npm run test:e2e:ui           # Interactive UI mode
 *   npm run test:e2e:headed       # Run with visible browser
 *   npm run test:e2e:debug        # Debugger mode
 */

module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,

  reporter: 'html',

  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:10003',
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

  webServer: {
    command: 'echo "Make sure WordPress is running on $BASE_URL or http://localhost:10003"',
    reuseExistingServer: !process.env.CI,
    timeout: 120000,
  },
});
