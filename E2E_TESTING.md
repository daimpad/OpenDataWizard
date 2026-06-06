# End-to-End Testing Guide

This guide explains how to run Playwright E2E tests for Open Data Wizard.

## Prerequisites

- **Node.js** ≥ 18 (for npm)
- **WordPress** running locally (default: http://localhost:10003)
- **Open Data Wizard plugin** activated
- **Admin user** with manage_open_data capability

## Setup

### 1. Install Dependencies

```bash
npm install
```

This installs Playwright and its dependencies (~150MB).

### 2. Configure WordPress

Make sure WordPress is running and accessible:

```bash
# Option 1: LocalWP (recommended)
# Open LocalWP, create a new site, set it to run

# Option 2: Docker
docker run -p 80:80 wordpress:latest

# Option 3: Your own WordPress instance
# Just ensure it's running on the BASE_URL
```

### 3. Environment Variables

Create a `.env.test` file (or set environment variables):

```bash
# WordPress login credentials for testing
export WP_ADMIN_USER=admin
export WP_ADMIN_PASSWORD=password

# WordPress base URL
export BASE_URL=http://localhost:10003
```

## Running Tests

### All Tests (Headless)

```bash
npm run test:e2e
```

Runs all tests in headless mode across all browsers (Chromium, Firefox, WebKit).

### Interactive UI Mode

```bash
npm run test:e2e:ui
```

Opens an interactive interface where you can:
- See test execution in real-time
- Inspect test steps
- Pause and debug individual steps
- Rerun failing tests

### Run with Visible Browser

```bash
npm run test:e2e:headed
```

Same as headless but with visible browser window.

### Debug Mode

```bash
npm run test:e2e:debug
```

Opens Playwright Inspector for step-by-step debugging:
- Step through tests line-by-line
- Inspect element locators
- Execute commands in console

### Run Specific Browser

```bash
npm run test:e2e:chrome     # Chromium only
npm run test:e2e:firefox    # Firefox only
npm run test:e2e:webkit     # Safari only
```

### Run Specific Test File

```bash
npx playwright test tests/e2e/admin-workflow.spec.js
npx playwright test tests/e2e/api-endpoints.spec.js
```

### Run Specific Test Case

```bash
npx playwright test -g "should create new dataset"
```

### Run Tests Matching Pattern

```bash
npx playwright test -g "Admin"
npx playwright test -g "REST API"
```

## Test Structure

### Test Files

- **`tests/e2e/admin-workflow.spec.js`** — Admin UI tests
  - Navigation to datasets
  - Creating new datasets
  - Filling form tabs
  - Validation
  - Settings page access

- **`tests/e2e/api-endpoints.spec.js`** — REST API tests
  - GET /catalog endpoint
  - GET /datasets/{id} endpoint
  - GET /delta endpoint
  - Response validation
  - Pagination and filtering

### Test Utilities

- `BASE_URL` — WordPress instance URL
- `ADMIN_USER` / `ADMIN_PASSWORD` — Login credentials
- `API_BASE` — REST API base URL

## Debugging

### View Test Report

After running tests:

```bash
npx playwright show-report
```

This opens an HTML report with:
- Test execution timeline
- Screenshots of failures
- Video recordings
- Detailed logs

### Inspect Elements

Use `page.pause()` to pause test execution and inspect the page:

```javascript
await page.pause(); // Pauses here, opens Inspector
```

### Take Screenshots

```javascript
await page.screenshot({ path: 'screenshot.png' });
```

### Enable Tracing

Traces are automatically saved on failures. View with:

```bash
npx playwright show-trace trace.zip
```

## Continuous Integration

Tests run automatically on GitHub Actions (`.github/workflows/ci.yml`):

- On every push to main
- On pull requests
- On scheduled daily runs

Results are stored as GitHub Artifacts.

## Best Practices

1. **Keep Tests Fast**
   - Use `test.beforeEach()` for setup
   - Avoid unnecessary waits
   - Reuse test data

2. **Make Tests Reliable**
   - Use stable selectors (avoid changing classes)
   - Wait for elements explicitly
   - Handle dynamic content

3. **Organize Tests**
   - Group related tests with `test.describe()`
   - Use clear, descriptive test names
   - Add comments for complex flows

4. **Test User Workflows**
   - Focus on user actions (click, fill, submit)
   - Verify results visible to users
   - Don't test implementation details

## Troubleshooting

### Tests Timeout

**Problem:** Tests hang or timeout  
**Solution:**
- Increase timeout in `playwright.config.js`
- Check if WordPress is actually running
- Verify network connectivity
- Look for JavaScript errors in browser console

### Login Fails

**Problem:** Admin login doesn't work  
**Solution:**
- Check `WP_ADMIN_USER` and `WP_ADMIN_PASSWORD` credentials
- Verify user exists and can login manually
- Check if WordPress is in maintenance mode

### Element Not Found

**Problem:** Locator returns no elements  
**Solution:**
- Use Inspector to verify selector is correct
- Add delays with `page.waitForSelector()`
- Check if element is in iframe (requires different approach)
- Verify plugin is activated

### Screenshots/Videos Not Saving

**Problem:** No output files after test run  
**Solution:**
- Check `test-results/` and `playwright-report/` directories
- Verify `playwright.config.js` has screenshots/video configured
- Check file permissions

## Resources

- **Playwright Docs:** https://playwright.dev/
- **Playwright Best Practices:** https://playwright.dev/docs/best-practices
- **Selectors Guide:** https://playwright.dev/docs/locators
- **Debugging:** https://playwright.dev/docs/debug

---

**Last Updated:** May 27, 2026  
**Version:** 2.1.4
