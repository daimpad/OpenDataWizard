# SHACL-Shapes (DCAT-AP-Validierung)

Dieses Verzeichnis bündelt die **offiziellen SHACL-Shapes** zur Validierung von DCAT-AP-Metadaten.
Der Open Data Wizard erzeugt DCAT-AP-3.0-/DCAT-AP.de-JSON-LD — diese Shapes sind die maßgebliche
Grundlage, gegen die diese Ausgabe geprüft wird (u. a. für die MQA-Metrik „DCAT-AP-Konformität").

> **Hinweis:** Das Plugin führt SHACL **nicht selbst** aus — dafür gibt es in PHP keine praxistaugliche
> Engine. Die Dateien liegen hier als **Referenz** bei; die eigentliche Validierung erfolgt über einen
> externen Validierungsdienst oder ein lokales SHACL-Werkzeug (siehe unten). Damit bleibt das Plugin
> self-contained und ohne Pflicht-Netzwerkzugriff.

## Enthaltene Dateien

| Datei | Herkunft | Lizenz |
|-------|----------|--------|
| `dcat-ap-SHACL.ttl` | [SEMICeu/DCAT-AP](https://github.com/SEMICeu/DCAT-AP), `releases/3.0.0/shacl/dcat-ap-SHACL.ttl` | CC BY 4.0 (EU/SEMIC) |
| `dcat-ap-SHACL-DE.ttl` | [GovDataOfficial/DCAT-AP.de-SHACL-Validation](https://github.com/GovDataOfficial/DCAT-AP.de-SHACL-Validation), `validator/resources/v3.0/shapes/dcat-ap-SHACL-DE.ttl` | CC0 (GovData) |

- **`dcat-ap-SHACL.ttl`** — die generischen DCAT-AP-3.0-Constraints der EU (SEMIC).
- **`dcat-ap-SHACL-DE.ttl`** — die DCAT-AP.de-Ergänzungen/-Anpassungen (deutschsprachige Fehlermeldungen,
  deaktivierte bzw. ergänzte Shapes). Für dieses Plugin besonders relevant, da `dcatde:`-Felder ausgegeben
  werden.

**Stand:** unveränderte Spiegelung des jeweiligen `master`-Stands, abgerufen am **2026-07-30**.
Bei Bedarf aus den oben verlinkten Quellen aktualisieren (Dateien 1:1 ersetzen).

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
3. Alternativ **lokal** validieren, z. B. mit [pySHACL](https://github.com/RDFLib/pySHACL):
   ```bash
   pyshacl -s config/shacl/dcat-ap-SHACL-DE.ttl -df json-ld datensatz.jsonld
   ```

## Verhältnis zum MQA-Scoring

Die MQA-Dimension „Interoperabilität" enthält die Metrik **DCAT-AP-Konformität (SHACL, 30 Punkte)**.
Diese wird vom Plugin standardmäßig als **„nicht bewertet"** geführt und aus dem Nenner herausgerechnet
(„achievable max", siehe [`docs/MQA-KONZEPT.md`](../../docs/MQA-KONZEPT.md)). Wer die volle 405-Punkte-
Bewertung braucht, kann die Validierung über einen der oben genannten externen Dienste durchführen.
