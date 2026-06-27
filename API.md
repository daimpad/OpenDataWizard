# REST API Documentation — Open Data Wizard

## Overview

Open Data Wizard provides three REST API endpoints under the namespace `/wp-json/datenatlas/v1/`:

- **GET /catalog** — Retrieve all published datasets as a DCAT Catalog
- **GET /datasets/{id}** — Retrieve a single dataset
- **GET /delta** — Incremental harvesting (datasets changed since timestamp)

All responses are **DCAT-AP 3.0** compliant and serialized as **JSON-LD**.

---

## Base URL

```
https://your-site.com/wp-json/datenatlas/v1
```

## Content Types

- **JSON-LD** (default): `application/ld+json`
- **JSON**: `application/json` (set `format=json`)

---

## Endpoints

### 1. GET /catalog

Retrieve all published datasets as a structured DCAT Catalog.

#### Request

```bash
curl -X GET "https://example.com/wp-json/datenatlas/v1/catalog" \
  -H "Accept: application/ld+json"
```

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number (starts at 1) |
| `per_page` | integer | 20 | Items per page (max: 100) |
| `theme` | string | — | Filter by theme (e.g., `Bildung`, `Umwelt`) |
| `license` | string | — | Filter by license (short code: `cc-by`, `cc0`, `odc-odbl` or full URI) |
| `format` | string | `jsonld` | Response format: `jsonld` or `json` |

#### Response Headers

| Header | Description |
|--------|-------------|
| `X-WP-Total` | Total number of datasets |
| `X-WP-TotalPages` | Total number of pages |
| `X-ODW-Cache` | Cache status: `HIT` or `MISS` (TTL: 5 min) |

#### Response Example

```bash
curl "https://example.com/wp-json/datenatlas/v1/catalog?page=1&per_page=2&format=json"
```

```json
{
  "@context": {
    "dcat": "https://www.w3.org/ns/dcat#",
    "dct": "http://purl.org/dc/terms/",
    "foaf": "http://xmlns.com/foaf/0.1/"
  },
  "@type": "dcat:Catalog",
  "dct:title": "Mein Datenkatalog",
  "dct:description": "Offene Daten der Beispielorganisation",
  "dct:issued": "2026-01-01",
  "dcat:dataset": [
    {
      "@type": "dcat:Dataset",
      "@id": "https://example.com/?p=123",
      "dct:title": "Bevölkerungszahlen 2024",
      "dct:description": "Bevölkerungszahlen nach Stadt und Jahr",
      "dct:issued": "2024-01-15",
      "dct:modified": "2026-05-27",
      "dct:publisher": {
        "@type": "foaf:Organization",
        "foaf:name": "Statistikamt"
      },
      "dcat:theme": [
        "http://publications.europa.eu/resource/authority/data-theme/SOCI"
      ],
      "dcat:distribution": {
        "@type": "dcat:Distribution",
        "dcat:accessURL": "https://example.com/downloads/bevoelkerung.csv",
        "dct:format": "http://publications.europa.eu/resource/authority/file-type/CSV",
        "dcat:byteSize": 245678,
        "dct:license": "http://creativecommons.org/licenses/by/4.0/"
      }
    }
  ]
}
```

#### Filtering Examples

```bash
# Get datasets with theme "Bildung"
curl "https://example.com/wp-json/datenatlas/v1/catalog?theme=Bildung"

# Get datasets with CC-BY license (by short code)
curl "https://example.com/wp-json/datenatlas/v1/catalog?license=cc-by"

# Get datasets with CC-BY license (by full URI)
curl "https://example.com/wp-json/datenatlas/v1/catalog?license=http://creativecommons.org/licenses/by/4.0/"

# Pagination with per_page
curl "https://example.com/wp-json/datenatlas/v1/catalog?page=2&per_page=50"

# Get as plain JSON instead of JSON-LD
curl "https://example.com/wp-json/datenatlas/v1/catalog?format=json"
```

---

### 2. GET /datasets/{id}

Retrieve a single dataset by WordPress post ID.

#### Request

```bash
curl -X GET "https://example.com/wp-json/datenatlas/v1/datasets/123" \
  -H "Accept: application/ld+json"
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | WordPress post ID (required) |

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `format` | string | `jsonld` | Response format: `jsonld` or `json` |

#### Response Example

```json
{
  "@context": {
    "dcat": "https://www.w3.org/ns/dcat#",
    "dct": "http://purl.org/dc/terms/"
  },
  "@type": "dcat:Dataset",
  "@id": "https://example.com/?p=123",
  "dct:title": "Bevölkerungszahlen 2024",
  "dct:description": "Bevölkerungszahlen nach Stadt und Jahr",
  "dct:issued": "2024-01-15",
  "dct:modified": "2026-05-27",
  "dct:publisher": {
    "@type": "foaf:Organization",
    "foaf:name": "Statistikamt"
  },
  "dcat:theme": [
    "http://publications.europa.eu/resource/authority/data-theme/SOCI"
  ],
  "dcat:distribution": {
    "@type": "dcat:Distribution",
    "dcat:accessURL": "https://example.com/downloads/bevoelkerung.csv",
    "dct:format": "http://publications.europa.eu/resource/authority/file-type/CSV",
    "dcat:byteSize": 245678,
    "dct:license": "http://creativecommons.org/licenses/by/4.0/"
  }
}
```

#### Examples

```bash
# Get single dataset as JSON-LD
curl "https://example.com/wp-json/datenatlas/v1/datasets/123"

# Get single dataset as plain JSON
curl "https://example.com/wp-json/datenatlas/v1/datasets/123?format=json"
```

---

### 3. GET /delta

Incremental harvesting — retrieve only datasets modified since a specific timestamp, plus tombstones for deleted datasets.

**Use case:** Harvest systems can poll this endpoint periodically to sync only recent changes instead of re-harvesting the entire catalog.

#### Request

```bash
curl -X GET "https://example.com/wp-json/datenatlas/v1/delta?since=2026-05-27T00:00:00Z" \
  -H "Accept: application/ld+json"
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `since` | string (ISO 8601) | ✓ Yes | Timestamp to retrieve changes since. Format: `YYYY-MM-DDTHH:MM:SSZ` or `YYYY-MM-DD` |
| `page` | integer | — | Page number (default: 1) |
| `per_page` | integer | — | Items per page (default: 20, max: 100) |
| `format` | string | — | Response format: `jsonld` or `json` (default: `jsonld`) |

#### Valid ISO 8601 Formats

```bash
# Date only (treated as 00:00:00 UTC)
?since=2026-05-27

# Full datetime with Z (UTC)
?since=2026-05-27T00:00:00Z

# Datetime with timezone offset
?since=2026-05-27T00:00:00+02:00

# Datetime without timezone (treated as UTC)
?since=2026-05-27T00:00:00
```

> **Note:** Invalid or overflow dates (e.g. `2024-13-45`) are rejected with a `400`
> validation error rather than being silently normalised.

#### Response Example

```json
{
  "@context": { ... },
  "@type": "dcat:Catalog",
  "dcat:dataset": [
    {
      "@type": "dcat:Dataset",
      "@id": "https://example.com/?p=123",
      "dct:title": "Updated Dataset",
      "dct:modified": "2026-05-27T14:30:00Z",
      ...
    }
  ],
  "odw:tombstones": [
    {
      "@type": "odw:Tombstone",
      "@id": "https://example.com/?p=456",
      "dct:issued": "2026-05-26T10:00:00Z"
    }
  ]
}
```

#### Examples

```bash
# Get datasets modified since May 1, 2026
curl "https://example.com/wp-json/datenatlas/v1/delta?since=2026-05-01"

# Get datasets modified since specific time (UTC)
curl "https://example.com/wp-json/datenatlas/v1/delta?since=2026-05-27T14:30:00Z"

# Get delta with pagination
curl "https://example.com/wp-json/datenatlas/v1/delta?since=2026-05-27&page=2&per_page=50"

# Get delta as plain JSON
curl "https://example.com/wp-json/datenatlas/v1/delta?since=2026-05-27&format=json"
```

---

## Client Examples

### JavaScript (Fetch API)

```javascript
// Fetch catalog with filters
async function getCatalog() {
  const url = new URL('https://example.com/wp-json/datenatlas/v1/catalog');
  url.searchParams.append('theme', 'Bildung');
  url.searchParams.append('per_page', 50);

  const response = await fetch(url.toString(), {
    headers: { 'Accept': 'application/ld+json' }
  });

  if (!response.ok) throw new Error(`HTTP ${response.status}`);

  const data = await response.json();
  console.log(`Total datasets: ${response.headers.get('X-WP-Total')}`);
  return data;
}

// Fetch single dataset
async function getDataset(postId) {
  const response = await fetch(
    `https://example.com/wp-json/datenatlas/v1/datasets/${postId}`
  );
  return response.json();
}

// Incremental harvest (delta)
async function getDelta(lastSync) {
  const since = lastSync.toISOString(); // "2026-05-27T00:00:00Z"
  const response = await fetch(
    `https://example.com/wp-json/datenatlas/v1/delta?since=${since}`
  );
  return response.json();
}
```

### Python (Requests)

```python
import requests
from datetime import datetime, timedelta

BASE_URL = 'https://example.com/wp-json/datenatlas/v1'

# Fetch catalog
def get_catalog(theme=None, license=None, page=1, per_page=20):
    params = {
        'page': page,
        'per_page': per_page,
        'format': 'json'
    }
    if theme:
        params['theme'] = theme
    if license:
        params['license'] = license

    response = requests.get(f'{BASE_URL}/catalog', params=params)
    response.raise_for_status()
    return response.json()

# Fetch single dataset
def get_dataset(post_id):
    response = requests.get(f'{BASE_URL}/datasets/{post_id}')
    response.raise_for_status()
    return response.json()

# Incremental harvest
def get_delta(since: datetime, page=1, per_page=20):
    since_str = since.isoformat() + 'Z'
    params = {
        'since': since_str,
        'page': page,
        'per_page': per_page
    }
    response = requests.get(f'{BASE_URL}/delta', params=params)
    response.raise_for_status()
    return response.json()

# Usage
catalog = get_catalog(theme='Bildung', per_page=50)
dataset = get_dataset(123)
delta = get_delta(datetime.now() - timedelta(days=1))
```

### cURL

```bash
# List all datasets
curl "https://example.com/wp-json/datenatlas/v1/catalog?per_page=100" \
  -H "Accept: application/ld+json" \
  -w "\nTotal: %{http_code}\n"

# Get single dataset with pretty-print
curl "https://example.com/wp-json/datenatlas/v1/datasets/123" | jq .

# Harvest changes since last week
SINCE=$(date -u -d '7 days ago' +%Y-%m-%dT%H:%M:%SZ)
curl "https://example.com/wp-json/datenatlas/v1/delta?since=$SINCE&per_page=100"
```

---

## Caching & Performance

All endpoints are cached with a **5-minute TTL** (configurable in settings):

```
X-ODW-Cache: HIT
X-ODW-Cache: MISS
```

Cache is automatically invalidated when:
- A dataset is saved or updated
- A dataset is moved to trash
- A dataset is permanently deleted

---

## Error Handling

### 404 Not Found

```json
{
  "code": "rest_post_invalid_id",
  "message": "Invalid post ID.",
  "data": { "status": 404 }
}
```

### 400 Bad Request

```json
{
  "code": "rest_invalid_param",
  "message": "Invalid parameter(s): since",
  "data": {
    "status": 400,
    "params": { "since": "Invalid ISO 8601 datetime" }
  }
}
```

---

## Standards & Conformance

- **DCAT-AP 3.0** — European Data Catalog Vocabulary
- **JSON-LD 1.1** — Linked Data format
- **DCATAP.de** — German DCAT-AP profile
- **ISO 8601** — Date/time format for delta queries
- **REST** — RESTful API design

### Validation

Test your API output with:
- [Schema.org Validator](https://validator.schema.org/)
- [DCAT-AP Validator](https://data.europa.eu/sites/default/files/library/dcat_application_profile_for_data_portals_in_europe_version_2.1_0.zip)

---

## Rate Limiting

Currently, there is **no hard rate limit**. However:

- Requests are cached (5 min TTL) to prevent repeated database queries
- Pagination is limited to 100 items per page
- For high-traffic scenarios, enable HTTP caching headers in your web server

---

## Harvesting Integration

### Set as Harvest Source in Open-Data-Portal

Most Open-Data-Platforms (like CKAN, Frictionless Data, GovData) support this:

1. Go to **Harvest Sources** / **New Harvest Source**
2. **Source URL:** `https://your-site.com/wp-json/datenatlas/v1/catalog`
3. **Type:** `DCAT-AP` or `DCAT`
4. **Save & Run**

The platform will automatically harvest all published datasets.

### Scheduled Delta Harvesting

For efficiency, harvest platforms can implement incremental harvesting:

```pseudo
last_sync = get_last_harvest_timestamp()
delta = GET /delta?since=last_sync&per_page=100
for dataset in delta['dcat:dataset']:
    update_dataset(dataset)
for tombstone in delta['odw:tombstones']:
    delete_dataset(tombstone['@id'])
save_timestamp(now())
```

---

## Support & Issues

- **Bug Report:** [GitHub Issues](https://github.com/daimpad/OpenDataWizard/issues)
- **API Question:** Create a GitHub Discussion
- **Security Issue:** See [SECURITY.md](./SECURITY.md)

---

**Last Updated:** June 27, 2026
**Version:** 2.1.5
**License:** GPL-2.0-or-later
