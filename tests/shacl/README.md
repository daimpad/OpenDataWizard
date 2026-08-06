# SHACL Validation for DCAT-AP Output

This directory contains the test harness for validating the plugin's DCAT-AP/DCAT-AP.de JSON-LD output against official SHACL shapes.

## Architecture

### Two-Stage Design

**Why not validate directly in PHP?**
- RDF and SHACL live in PHP (data generation)
- Only usable SHACL engines are in JavaScript
- Solution: PHP generates fixtures → Node validates them

### Components

1. **`generate-fixtures.php`** — Generates test fixtures without WordPress
   - Uses plain function stubs (no WP_Mock outside PHPUnit)
   - Creates minimal, maximal, and catalog fixtures
   - Outputs both `.jsonld` and `.ttl` (validates serializer)
   - Fixtures land in `build/shacl/` (gitignored)

2. **`validate.mjs`** — Node-based SHACL validator
   - Uses `rdf-validate-shacl` (Zazuko, maintained)
   - Supports Tier 1 and Tier 2 (default)
   - Validates fixtures + checks against allowlist
   - Exit 0 = pass, Exit 1 = violations found

3. **`accepted.json`** — Allowlist for known-accepted violations
   - Each entry has:
     - `focusNode` (optional, substring match)
     - `path` (optional, exact match)
     - `messagePattern` (optional, regex)
     - `justification` (required, explains why it's accepted)
   - Without this, first legitimate-but-unfixable violation goes red forever

## Validation Tiers

### Tier 1: v30_de_trans (EU + SHACL-DE)
- `dcat-ap-SHACL.ttl` + `dcat-ap-SHACL-DE.ttl`
- ~200 shapes
- Fully offline
- Fast baseline check

### Tier 2: de_spec minus vocabs (default, recommended)
- Tier 1 + `dcat-ap-spec-german-additions.ttl` + `dcat-ap-de-deprecated.ttl`
- Stricter (includes `foaf:Agent` subclass constraints)
- Fully offline
- **CI uses this tier**

### Tier 3: full v30_de_spec (deferred)
- Tier 2 + `dcat-ap-de-imports.ttl` + `dcat-ap-de-controlledvocabularies.ttl`
- Requires remote ontologies + 16 EU vocabulary graphs (10+ MB)
- Not practical for local/CI validation
- External validators (ITB, MQA) cover this

## Usage

### Locally (requires PHP + Node)

```bash
# Generate fixtures
php tests/shacl/generate-fixtures.php

# Validate (Tier 2, default)
npm run test:shacl

# Validate Tier 1
npm run test:shacl -- --tier=1
```

### CI (GitHub Actions)

Runs automatically on every push to any branch. See `.github/workflows/ci.yml` → `shacl` job.

## Fixture Design

### dataset-minimal.jsonld/ttl
- Required fields only
- Catches over-strict shapes (false violations on valid minimal datasets)

### dataset-maximal.jsonld/ttl
- **Every field** the plugin supports:
  - HVD category + applicable legislation
  - Multilingual literals (Phase D)
  - Extra distributions (Phase E)
  - All DCAT-AP.de fields (dcatde:originator, maintainer, contributorID, etc.)
  - Optional DCAT-AP 3.0 fields
- Stress-tests the full field set
- Most valuable for catching missed constraints

### catalog.jsonld/ttl
- Wraps both minimal and maximal datasets
- Tests catalog-level constraints (dct:publisher, foaf:homepage, etc.)

## Allowlist Format

`accepted.json` example:

```json
[
  {
    "focusNode": "dataset-maximal",
    "path": "http://purl.org/dc/terms/spatial",
    "messagePattern": "Expected URI",
    "justification": "DCAT-AP allows both URI and text literal for dct:spatial; we emit text literal for user convenience (conforms to spec, but some validators flag it)"
  }
]
```

Matching rules:
- `focusNode`: substring match (e.g., `"dataset-maximal"` matches `https://example.org/wp-json/datenatlas/v1/datasets/1`)
- `path`: exact match
- `messagePattern`: regex (anchors implicit)
- All specified fields must match for a violation to be allowlisted

## Guard: Shape Count Assertion

`validate.mjs` counts `sh:NodeShape` declarations in the loaded shapes graph.

**Why?**
- `dcat-ap-SHACL-DE.ttl` alone contains **zero** NodeShapes
- It's 300 severity/message overrides keyed to shapes defined in `dcat-ap-SHACL.ttl`
- Loading it alone → `conforms: true` without validation (false green)
- Guard fails fast if shape count = 0

## foaf:Agent Subclass Entailment (Phase 0b)

`dcat-ap-spec-german-additions.ttl` has 3 constraints with `sh:class foaf:Agent`.

The plugin emits `foaf:Organization` for publisher/originator/maintainer.

**Without entailment:**
- `foaf:Organization ⊑ foaf:Agent` is invisible → violations
- Requires FOAF ontology loaded

**Solution (Tier 2):**
- DCAT-AP.de bundles a FOAF mirror at `validator/resources/mirror/foaf_0_1.rdf`
- `dcat-ap-de-imports.ttl` declares `owl:imports <mirror/foaf_0_1.rdf>`
- **Not loaded in Tier 1/2** (offline constraint)
- Empirical test: if Tier 2 passes without violations on `foaf:Organization`, entailment isn't needed for our output
- If violations occur: either (a) add FOAF mirror to Tier 2, or (b) allowlist them with justification

## Scope Decision: Catalog + Per-Dataset

The plan asked: validate both catalog and per-dataset fixtures, or just harvest endpoint?

**Answer: Both.**

Reasoning:
- Marginal cost is low (one extra mock)
- `/datasets/{id}` is public API consumed directly by external tools
- Catalog wraps datasets — if per-dataset is broken, catalog validation won't catch wrapper logic issues
- Complete coverage prevents "works in catalog, breaks standalone" divergence

## Baseline Red vs. Green-by-Allowlist

The plan asked: land green-by-allowlist or hold until genuinely clean?

**Answer: Land green-by-allowlist.**

Reasoning:
- Honest, version-controlled record of what's actually wrong
- Allowlist becomes documentation: "We emit X because Y, accepted risk"
- CI stays useful immediately rather than being ignored while violations are fixed
- If external ITB said "konform" but Tier 2 is stricter, the delta is valuable to capture

## File Sizes

```
dcat-ap-SHACL.ttl:                      175 KB (EU baseline)
dcat-ap-SHACL-DE.ttl:                    87 KB (severity overrides)
dcat-ap-spec-german-additions.ttl:       11 KB (dcatde: constraints)
dcat-ap-de-deprecated.ttl:              4.6 KB (deprecation warnings)
dcat-ap-de-imports.ttl:                 3.5 KB (ontology imports, unused in Tier 1+2)
dcat-ap-de-controlledvocabularies.ttl:   16 KB (vocab constraints, unused in Tier 1+2)
```

## References

- [DCAT-AP 3.0 Spec](https://github.com/SEMICeu/DCAT-AP)
- [DCAT-AP.de SHACL Validation](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation)
- [rdf-validate-shacl](https://github.com/zazuko/rdf-validate-shacl)
- [SHACL Spec](https://www.w3.org/TR/shacl/)
- [EU ITB Validator](https://www.itb.ec.europa.eu/shacl/dcat-ap/upload)

## Troubleshooting

### "No sh:NodeShape declarations found"

You tried to validate with only `dcat-ap-SHACL-DE.ttl`. Merge it with `dcat-ap-SHACL.ttl` (see Tier composition).

### "File not found: build/shacl/dataset-minimal.jsonld"

Run fixture generator first: `php tests/shacl/generate-fixtures.php`

### "PHP not found" (locally)

CI has PHP. If running locally in a container without PHP, commit the code and let CI run it, or install PHP in your environment.

### Violations on legitimate output

Add to `accepted.json` with:
1. Clear `justification` explaining why it's accepted
2. Minimal match pattern (don't over-allowlist)
3. Consider if output should change instead

## Maintenance

- **SHACL shapes:** Mirror from upstream when DCAT-AP/DCAT-AP.de release new versions
- **Fixtures:** Extend when new fields are added (keep maximal fixture truly maximal)
- **Allowlist:** Review quarterly; remove entries when underlying issue is fixed
- **Dependencies:** `rdf-validate-shacl` is actively maintained; bump cautiously (SHACL engines can be finicky)

## Future: Tier 3

If/when needed:
1. Bundle the 16 EU vocabulary graphs (or fetch at runtime with TTL cache)
2. Determine if they go in data graph or shapes graph (sh:path traverses data, but ITB folds owl:imports into shapes)
3. Add FOAF mirror to Tier 2 if entailment becomes necessary
4. Update `TIERS` object in `validate.mjs`
5. Expect slower execution (10+ MB of triples)

Plausibly never worth doing locally — external ITB validator is the gold standard for full Tier 3.
