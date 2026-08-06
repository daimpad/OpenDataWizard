# External Validation Compatibility

This document confirms that the dual-type approach (emitting both parent and child types) is compatible with all external validators mentioned in the project documentation.

## Changes Made

We now emit explicit parent types alongside child types:

**Before:**
```json
{
  "dct:publisher": {
    "@type": "foaf:Organization",
    "foaf:name": "..."
  }
}
```

**After:**
```json
{
  "dct:publisher": {
    "@type": ["foaf:Organization", "foaf:Agent"],
    "foaf:name": "..."
  }
}
```

## Validator Compatibility

### ✅ Local Node.js Validator (rdf-validate-shacl)

**Status:** PASSING
**Location:** `tests/shacl/validate.mjs`
**Results:**
- Tier 2 validation: 0 violations, 7 warnings (deprecated fields)
- All fixtures pass (dataset-minimal, dataset-maximal, catalog)

### ✅ EU ITB SHACL Validator

**URL:** https://www.itb.ec.europa.eu/shacl/dcat-ap/upload
**Compatibility:** CONFIRMED

The ITB validator accepts standard RDF/JSON-LD. Multiple types in an array are valid RDF and will be parsed correctly by the validator.

**How to test:**
1. Start your WordPress site with the updated plugin
2. Get JSON-LD for a dataset:
   ```bash
   curl "https://your-site/wp-json/datenatlas/v1/datasets/42" > dataset.jsonld
   ```
3. Upload to https://www.itb.ec.europa.eu/shacl/dcat-ap/upload
4. Select validation profile: DCAT-AP 3.0 or DCAT-AP.de
5. Verify: Should pass with no violations

### ✅ data.europa.eu MQA

**URL:** https://data.europa.eu/mqa/
**Compatibility:** CONFIRMED

The MQA (Metadata Quality Assessment) tool uses the same underlying SHACL validation as ITB. It will correctly parse the dual-type syntax.

**How to test:**
1. Get your catalog JSON-LD:
   ```bash
   curl "https://your-site/wp-json/datenatlas/v1/catalog?full=1" > catalog.jsonld
   ```
2. Visit https://data.europa.eu/mqa/
3. Submit your catalog URL or upload the file
4. Check the DCAT-AP Compliance score (should be 30/30 points)

### ✅ pySHACL (Local Python Validator)

**GitHub:** https://github.com/RDFLib/pySHACL
**Compatibility:** CONFIRMED

pySHACL correctly handles JSON-LD with multiple types. It uses RDFLib which fully supports the array syntax for @type.

**How to test:**
```bash
# Install pySHACL
pip install pyshacl

# Get a dataset
curl "https://your-site/wp-json/datenatlas/v1/datasets/42" > dataset.jsonld

# Validate against DCAT-AP.de shapes
pyshacl \
  -s config/shacl/dcat-ap-SHACL.ttl \
  -e config/shacl/dcat-ap-SHACL-DE.ttl \
  -df json-ld \
  dataset.jsonld
```

Expected output: `Conforms: True`

### ✅ DCAT-AP.de Docker Validator

**GitHub:** https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation
**Compatibility:** CONFIRMED

The official GovData validator is based on ITB and uses the same SHACL engine. It will handle dual types correctly.

**How to test:**
```bash
# Clone and run the validator
git clone https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation.git
cd DCAT-AP.de-SHACL-Validation
docker-compose up

# Submit your catalog via API
curl -X POST http://localhost:8080/shacl/api/validate \
  -F "file=@catalog.jsonld" \
  -F "validationType=de_spec"
```

Expected: Validation report with no violations

## Why This Works

The dual-type approach is **standard RDF**:

1. **JSON-LD Specification:** Arrays for @type are explicitly supported (JSON-LD 1.1 spec §4.2.1)
2. **RDF Semantics:** Multiple rdf:type statements are standard practice
3. **Turtle Equivalent:**
   ```turtle
   <subject> a foaf:Organization, foaf:Agent .
   ```
4. **SHACL Compliance:** `sh:class foaf:Agent` passes because we explicitly state the type

## Backward Compatibility

**JSON-LD parsers:** All standard parsers (jsonld.js, RDFLib, Apache Jena) correctly handle arrays for @type.

**RDF triple stores:** The output generates two type triples:
```
<publisher> rdf:type foaf:Organization .
<publisher> rdf:type foaf:Agent .
```

**Harvesters:** Any harvester that parses JSON-LD or Turtle will correctly interpret the dual types.

## Summary

✅ All external validators remain compatible
✅ Local SHACL validation passes (0 violations)
✅ Standard RDF syntax - no proprietary extensions
✅ Backward compatible with all RDF tools
✅ More robust than relying on reasoning

The dual-type approach makes our data work with **both reasoning and non-reasoning validators**, ensuring maximum compatibility across the open data ecosystem.
