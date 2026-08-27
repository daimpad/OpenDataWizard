# SHACL-Shapes (DCAT-AP-Validierung)

Dieses Verzeichnis bündelt die **offiziellen SHACL-Shapes** zur Validierung von DCAT-AP-Metadaten.
Der Open Data Wizard erzeugt DCAT-AP-3.0-/DCAT-AP.de-JSON-LD — diese Shapes sind die maßgebliche
Grundlage, gegen die diese Ausgabe geprüft wird (u. a. für die MQA-Metrik „DCAT-AP-Konformität").

> **Hinweis:** Das Plugin führt SHACL **nicht selbst** aus — dafür gibt es in PHP keine praxistaugliche
> Engine. Die Dateien liegen hier als **Referenz** bei; die eigentliche Validierung erfolgt über einen
> externen Validierungsdienst oder ein lokales SHACL-Werkzeug (siehe unten). Damit bleibt das Plugin
> self-contained und ohne Pflicht-Netzwerkzugriff.

## Enhaltene Dateien

| Datei | Größe | Herkunft | Lizenz | Verwendung |
|-------|-------|----------|--------|------------|
| `dcat-ap-SHACL.ttl` | 175 KB | [SEMICeu/DCAT-AP](https://github.com/SEMICeu/DCAT-AP), `releases/3.0.0/shacl/dcat-ap-SHACL.ttl` | CC BY 4.0 (EU/SEMIC) | Tier 1+2 |
| `dcat-ap-SHACL-DE.ttl` | 87 KB | [GovDataOfficial/DCAT-AP.de-SHACL-Validation](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation), `validator/resources/v3.0/shapes/dcat-ap-SHACL-DE.ttl` | CC0 (GovData) | Tier 1+2 |
| `dcat-ap-spec-german-additions.ttl` | 11 KB | GovData, `validator/resources/v3.0/shapes/dcat-ap-spec-german-additions.ttl` | CC0 (GovData) | Tier 2 |
| `dcat-ap-de-deprecated.ttl` | 4.6 KB | GovData, `validator/resources/v3.0/shapes/dcat-ap-de-deprecated.ttl` | CC0 (GovData) | Tier 2 |
| `dcat-ap-de-imports.ttl` | 3.5 KB | GovData, `validator/resources/v3.0/shapes/dcat-ap-de-imports.ttl` | CC0 (GovData) | Referenz (Tier 3) |
| `dcat-ap-de-controlledvocabularies.ttl` | 16 KB | GovData, `validator/resources/v3.0/shapes/dcat-ap-de-controlledvocabularies.ttl` | CC0 (GovData) | Referenz (Tier 3) |

### Datei-Beschreibungen

- **`dcat-ap-SHACL.ttl`** — Die generischen DCAT-AP-3.0-Constraints der EU (SEMIC). Enthält alle Pflichtfelder und Multiplizitäten für DCAT-AP-konforme Metadaten.
- **`dcat-ap-SHACL-DE.ttl`** — Die DCAT-AP.de-Ergänzungen/-Anpassungen. Enthält deutschsprachige Fehlermeldungen, deaktivierte Shapes und Severity-Overrides. Für dieses Plugin besonders relevant, da `dcatde:`-Felder ausgegeben werden.
- **`dcat-ap-spec-german-additions.ttl`** — Zusätzliche deutsche Shapes (u.a. `foaf:Agent`-Constraints für `dcatde:originator`/`maintainer`).
- **`dcat-ap-de-deprecated.ttl`** — Deprecation-Hinweise für veraltete Felder.
- **`dcat-ap-de-imports.ttl`** — Ontologie-Imports (FOAF, vCard, org) + EU-Authority-Tables. **Nicht** für lokale Offline-Validierung verwendet (Tier 1+2), nur als Referenz gebündelt.
- **`dcat-ap-de-controlledvocabularies.ttl`** — Vocabulary-Constraints (z.B. `dcat:theme ∈ EU-Data-Theme`). **Nicht** für lokale Offline-Validierung verwendet, nur als Referenz gebündelt.

**Stand:** Spiegelung vom **2026-08-06** (unverändert aus den jeweiligen `master`-Branches).
Bei Bedarf aus den oben verlinkten Quellen aktualisieren (Dateien 1:1 ersetzen).

## Validierungs-Tiers

Die lokale SHACL-Validierung (CI + `npm run test:shacl`) unterstützt zwei Stufen:

### Tier 1: v30_de_trans (EU + SHACL-DE, vollständig offline)
Kombiniert:
- `dcat-ap-SHACL.ttl` (EU-Basis-Constraints)
- `dcat-ap-SHACL-DE.ttl` (deutsche Severity-Overrides + deaktivierte Shapes)

**Eigenschaften:**
- Vollständig offline (keine Remote-Ontologien, keine Vokabular-Imports)
- Schnell (~200 Shapes)
- Standard-Tier für CI

### Tier 2: de_spec minus vocabs (+ german-additions + deprecated)
Tier 1 **plus**:
- `dcat-ap-spec-german-additions.ttl` (zusätzliche Shapes für `dcatde:originator`/`maintainer`)
- `dcat-ap-de-deprecated.ttl` (Deprecation-Warnings)

**Eigenschaften:**
- Vollständig offline
- Strikter als Tier 1 (z.B. `foaf:Agent`-Subclass-Constraints)
- **Empfohlen** für finale Prüfungen

### Tier 3: full v30_de_spec (+ imports + controlledvocabularies)
Tier 2 **plus**:
- `dcat-ap-de-imports.ttl` (lädt FOAF/vCard/org-Ontologien + 16 EU-Vokabulare)
- `dcat-ap-de-controlledvocabularies.ttl` (wertebereichs-Constraints, z.B. `dcat:theme`)

**Eigenschaften:**
- **Nicht offline** (entailment requires ontology loading; vocabulary-Graphs sind 10+ MB)
- Aufgeschoben — für lokale Validierung unpraktisch
- Externe Validatoren (ITB, MQA) decken dies ab

## Metadaten eines Datensatzes validieren

1. JSON-LD des Datensatzes abrufen:
   ```
   https://<deine-site>/wp-json/datenatlas/v1/datasets/<ID>
   ```
2. Gegen die Shapes prüfen — z. B. mit einem der offiziellen Dienste:
   - **EU-Validator (SHACL / ITB):** <https://www.itb.ec.europa.eu/shacl/dcat-ap/upload>
   - **data.europa.eu MQA:** <https://data.europa.eu/mqa/>
   - **DCAT-AP.de-Validator (Docker/ITB):** siehe
     [DCAT-AP.de-SHACL-Validation](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation)
3. Alternativ **lokal** validieren:

   **Mit Node.js (empfohlen, automatisiert):**
   ```bash
   npm run test:shacl                     # Tier 2 (Standard)
   npm run test:shacl -- --tier=1        # Tier 1 (nur EU + SHACL-DE)
   ```

   **Mit pySHACL (manuell, benötigt merged graph):**
   ```bash
   # FALSCH (validiert nichts):
   # pyshacl -s config/shacl/dcat-ap-SHACL-DE.ttl -df json-ld datensatz.jsonld

   # Korrekt — Shapes müssen in einem Graph gemerged werden:
   # Tier 1:
   pyshacl -s <(cat config/shacl/dcat-ap-SHACL.ttl config/shacl/dcat-ap-SHACL-DE.ttl) \
           -df json-ld datensatz.jsonld

   # Tier 2:
   pyshacl -s <(cat config/shacl/dcat-ap-SHACL.ttl \
                    config/shacl/dcat-ap-SHACL-DE.ttl \
                    config/shacl/dcat-ap-spec-german-additions.ttl \
                    config/shacl/dcat-ap-de-deprecated.ttl) \
           -df json-ld datensatz.jsonld
   ```

   **Warum nicht nur `-s dcat-ap-SHACL-DE.ttl`?**
   `dcat-ap-SHACL-DE.ttl` enthält _nur_ Overrides (Severity, Deactivation) für Shapes, die in `dcat-ap-SHACL.ttl` definiert sind. Ohne die EU-Basis-Datei lädt pySHACL null NodeShapes → `conforms: true` ohne Prüfung (false green).

## Verhältnis zum MQA-Scoring

Die MQA-Dimension „Interoperabilität" enthält die Metrik **DCAT-AP-Konformität (SHACL, 30 Punkte)**.
Diese wird vom Plugin standardmäßig als **„nicht bewertet"** geführt und aus dem Nenner herausgerechnet
(„achievable max", siehe [`docs/MQA-KONZEPT.md`](../../docs/MQA-KONZEPT.md)). Wer die volle 405-Punkte-
Bewertung braucht, kann die Validierung über einen der oben genannten externen Dienste durchführen.
