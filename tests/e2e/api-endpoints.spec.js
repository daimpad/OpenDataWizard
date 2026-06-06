// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Open Data Wizard REST API Endpoint Tests
 *
 * Tests the public REST API endpoints:
 * - GET /wp-json/datenatlas/v1/catalog
 * - GET /wp-json/datenatlas/v1/datasets/{id}
 * - GET /wp-json/datenatlas/v1/delta
 */

const BASE_URL = process.env.BASE_URL || 'http://localhost:10003';
const API_BASE = `${BASE_URL}/wp-json/datenatlas/v1`;

test.describe('REST API Endpoints', () => {
  test('GET /catalog should return DCAT Catalog', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog`, {
      headers: {
        'Accept': 'application/ld+json',
      },
    });

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('application/json');

    const data = await response.json();
    expect(data).toHaveProperty('@context');
    expect(data).toHaveProperty('@type');
    expect(data['@type']).toBe('dcat:Catalog');
    expect(data).toHaveProperty('dcat:dataset');
  });

  test('GET /catalog should return pagination headers', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?page=1&per_page=20`, {
      headers: { 'Accept': 'application/ld+json' },
    });

    expect(response.status()).toBe(200);
    expect(response.headers()).toHaveProperty('x-wp-total');
    expect(response.headers()).toHaveProperty('x-wp-totalpages');
    expect(response.headers()).toHaveProperty('x-odw-cache');
  });

  test('GET /catalog?per_page should limit results', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?per_page=5`);
    const data = await response.json();

    if (data['dcat:dataset'] && Array.isArray(data['dcat:dataset'])) {
      expect(data['dcat:dataset'].length).toBeLessThanOrEqual(5);
    }
  });

  test('GET /catalog?format=json should return plain JSON', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?format=json`);

    expect(response.status()).toBe(200);
    const contentType = response.headers()['content-type'];
    expect(contentType).toContain('application/json');
  });

  test('GET /catalog?theme should filter by theme', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?theme=Bildung`);

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data).toHaveProperty('dcat:dataset');
  });

  test('GET /catalog?license should filter by license', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog?license=cc-by`);

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data).toHaveProperty('dcat:dataset');
  });

  test('GET /datasets/{id} should return single dataset', async ({ request }) => {
    // First, get catalog to find a dataset ID
    const catalogResponse = await request.get(`${API_BASE}/catalog?per_page=1`);
    const catalogData = await catalogResponse.json();

    if (catalogData['dcat:dataset'] && catalogData['dcat:dataset'].length > 0) {
      const datasetId = catalogData['dcat:dataset'][0]['@id']?.split('p=')[1];

      if (datasetId) {
        const response = await request.get(`${API_BASE}/datasets/${datasetId}`);

        expect(response.status()).toBe(200);
        const data = await response.json();
        expect(data).toHaveProperty('@type');
        expect(data['@type']).toBe('dcat:Dataset');
      }
    }
  });

  test('GET /datasets/{invalid} should return 404', async ({ request }) => {
    const response = await request.get(`${API_BASE}/datasets/99999999`);

    expect(response.status()).toBe(404);
  });

  test('GET /delta?since should return changed datasets', async ({ request }) => {
    // Use a recent date to get datasets
    const sinceDate = new Date();
    sinceDate.setDate(sinceDate.getDate() - 7); // Last 7 days
    const since = sinceDate.toISOString();

    const response = await request.get(`${API_BASE}/delta?since=${since}`);

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data).toHaveProperty('@type');
    expect(data).toHaveProperty('dcat:dataset');
  });

  test('GET /delta without since should return 400', async ({ request }) => {
    const response = await request.get(`${API_BASE}/delta`);

    // Should fail validation without 'since' parameter
    expect([400, 422]).toContain(response.status());
  });

  test('GET /delta with invalid date should return error', async ({ request }) => {
    const response = await request.get(`${API_BASE}/delta?since=invalid-date`);

    expect([400, 422]).toContain(response.status());
  });

  test('GET /delta?since with valid ISO 8601 formats', async ({ request }) => {
    const testCases = [
      '2026-05-27', // Date only
      '2026-05-27T00:00:00Z', // UTC
      '2026-05-27T00:00:00+02:00', // With timezone
    ];

    for (const since of testCases) {
      const response = await request.get(`${API_BASE}/delta?since=${since}`);

      expect(response.status()).toBe(200);
      const data = await response.json();
      expect(data).toHaveProperty('dcat:dataset');
    }
  });

  test('API response cache headers', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog`);

    // Check for cache status header
    const cacheHeader = response.headers()['x-odw-cache'];
    expect(['HIT', 'MISS']).toContain(cacheHeader);
  });

  test('API should validate per_page limit', async ({ request }) => {
    // Test max 100 items per page
    const response = await request.get(`${API_BASE}/catalog?per_page=1000`);

    const data = await response.json();
    if (data['dcat:dataset'] && Array.isArray(data['dcat:dataset'])) {
      expect(data['dcat:dataset'].length).toBeLessThanOrEqual(100);
    }
  });

  test('API should return valid JSON-LD context', async ({ request }) => {
    const response = await request.get(`${API_BASE}/catalog`);
    const data = await response.json();

    expect(data).toHaveProperty('@context');
    const context = data['@context'];

    // Check for expected namespaces
    expect(context).toHaveProperty('dcat');
    expect(context).toHaveProperty('dct');
    expect(context).toHaveProperty('foaf');
  });
});
