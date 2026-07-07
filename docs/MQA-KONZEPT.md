# Konzept: Metadata Quality Assessment (MQA) im Open Data Wizard

**Status:** Entwurf zur Entscheidung · **Bezug:** [data.europa.eu MQA Methodologie](https://data.europa.eu/mqa/methodology)
**Ziel:** Das aktuelle einfache Qualitäts-Scoring (0–100, Feldvollständigkeit) durch das
standardkonforme MQA-Modell der EU (5 Dimensionen, 405 Punkte, 4 Stufen) ersetzen bzw. erweitern.

---

## 1. Ausgangslage

Das Plugin erzeugt bereits **DCAT-AP-3.0-JSON-LD** — exakt die Datengrundlage, die die MQA bewertet.
Das heutige Scoring (`includes/class-quality.php`, Konfiguration `config/dcat-ap-fields.php`) ist eine
vereinfachte Teilmenge der MQA: Es prüft nur „Feld gesetzt?" für ~10 Felder und vergibt 0–100 Punkte.

> **Stand v2.12.0:** Das Datenmodell wurde inzwischen erheblich ausgebaut. **Alle** für die MQA
> relevanten Felder sind bereits vorhanden — u. a. `dcat:downloadURL` (`odw_download_url`),
> `dcat:mediaType` (`odw_media_type`), `dct:accessRights` (`odw_access_rights`) und `dct:rights`
> (`odw_dist_rights`). Die in einer früheren Fassung dieses Konzepts als „neu anzulegen"
> aufgeführten Felder **entfallen damit** — es bleibt reine Scoring-Arbeit (siehe Abschnitt 4).

Die MQA ist reicher: Sie unterscheidet **fünf FAIR-Dimensionen**, prüft neben „gesetzt?" auch
**Vokabular-Konformität**, **Erreichbarkeit** und **DCAT-AP-Konformität (SHACL)** und drückt das
Ergebnis in **vier Bewertungsstufen** aus.

---

## 2. Das MQA-Modell (Referenz)

| Dimension | Max. Punkte | Metrik (Property) | Gewicht | Prüfung |
|-----------|-------------|-------------------|---------|---------|
| **Auffindbarkeit** | 100 | Schlüsselwörter `dcat:keyword` | 30 | gesetzt? |
| | | Kategorien `dcat:theme` | 30 | gesetzt? |
| | | Räumlich `dct:spatial` | 20 | gesetzt? |
| | | Zeitlich `dct:temporal` | 20 | gesetzt? |
| **Zugänglichkeit** | 100 | `dcat:accessURL` erreichbar | 50 | HTTP HEAD 200/300 |
| | | `dcat:downloadURL` gesetzt | 20 | gesetzt? |
| | | `dcat:downloadURL` erreichbar | 30 | HTTP HEAD 200/300 |
| **Interoperabilität** | 110 | `dct:format` gesetzt | 20 | gesetzt? |
| | | `dcat:mediaType` gesetzt | 10 | gesetzt? |
| | | Format/MediaType aus Vokabular | 10 | in EU-Vokabular? |
| | | Nicht-proprietär `dct:format` | 20 | in EU-Liste? |
| | | Maschinenlesbar `dct:format` | 20 | in EU-Liste? |
| | | DCAT-AP-Konformität | 30 | SHACL-Validierung |
| **Wiederverwendbarkeit** | 75 | Lizenz `dct:license` gesetzt | 20 | gesetzt? |
| | | Lizenz aus Vokabular | 10 | in EU-Liste? |
| | | Zugriffsrechte `dct:accessRights` | 10 | gesetzt? |
| | | Zugriffsrechte aus Vokabular | 5 | in EU-Liste? |
| | | Kontaktpunkt `dcat:contactPoint` | 20 | gesetzt? |
| | | Herausgeber `dct:publisher` | 10 | gesetzt? |
| **Kontext** | 20 | Rechte `dct:rights` | 5 | gesetzt? |
| | | Dateigröße `dcat:byteSize` | 5 | gesetzt? |
| | | Ausstellungsdatum `dct:issued` | 5 | gesetzt? |
| | | Änderungsdatum `dct:modified` | 5 | gesetzt? |
| **Summe** | **405** | | | |

**Bewertungsstufen:** Excellent 351–405 · Gut 221–350 · Ausreichend 121–220 · Mangelhaft 0–120

---

## 3. Abgleich mit dem Plugin (Machbarkeit je Metrik)

Legende: ✅ vorhanden/offline · ➕ neues Feld nötig · 📚 Vokabularliste nötig · 🌐 Netzwerk · 🔬 SHACL

| Metrik | Punkte | Plugin-Status | Aufwand |
|--------|--------|---------------|---------|
| `dcat:keyword` gesetzt | 30 | ✅ `_odw_keywords` | — |
| `dcat:theme` gesetzt | 30 | ✅ `_odw_theme` | — |
| `dct:spatial` gesetzt | 20 | ✅ `_odw_spatial` | — |
| `dct:temporal` gesetzt | 20 | ✅ `_odw_temporal_start/end` | — |
| `dcat:accessURL` gesetzt | (impliziert) | ✅ `_odw_access_url` | — |
| `dcat:accessURL` erreichbar | 50 | 🌐 | HTTP-HEAD + Cache |
| `dcat:downloadURL` gesetzt | 20 | ✅ `odw_download_url` (v2.12.0) | — |
| `dcat:downloadURL` erreichbar | 30 | 🌐 | HTTP-HEAD |
| `dct:format` gesetzt | 20 | ✅ `odw_format` | — |
| `dcat:mediaType` gesetzt | 10 | ✅ `odw_media_type` (v2.12.0) | — |
| Format/MediaType aus Vokabular | 10 | 📚 `dct-format-list.php` = kontrolliertes Set | klein |
| Nicht-proprietär | 20 | 📚 Flag pro Format ergänzen | klein |
| Maschinenlesbar | 20 | 📚 Flag pro Format ergänzen | klein |
| DCAT-AP-Konformität | 30 | 🔬 SHACL | extern/komplex |
| `dct:license` gesetzt | 20 | ✅ `odw_license` | — |
| Lizenz aus Vokabular | 10 | 📚 `licenses.txt` vorhanden | klein |
| `dct:accessRights` gesetzt | 10 | ✅ `odw_access_rights` (v2.12.0) | — |
| `dct:accessRights` aus Vokabular | 5 | 📚 EU-Vokabular (3 Werte) prüfen | klein |
| `dcat:contactPoint` gesetzt | 20 | ✅ `odw_contact_*` | — |
| `dct:publisher` gesetzt | 10 | ✅ `odw_publisher` | — |
| `dct:rights` gesetzt | 5 | ✅ `odw_dist_rights` (v2.12.0) | — |
| `dcat:byteSize` gesetzt | 5 | ✅ `odw_byte_size` | — |
| `dct:issued` gesetzt | 5 | ✅ `odw_issued` | — |
| `dct:modified` gesetzt | 5 | ✅ `odw_modified` | — |

**Zusammenfassung (Stand v2.12.0):**
- **Offline sofort abbildbar:** ~**265 von 405 Punkten** — alle „gesetzt?"-Metriken (alle Felder
  sind vorhanden).
- **Plus Vokabular-Checks** (Format-Flags, Lizenz-/AccessRights-/MediaType-Vokabular): weitere **~30
  Punkte** → **~295/405 rein offline**, ohne neue Felder.
- **Nur mit Netzwerk:** accessURL/downloadURL-Erreichbarkeit = **80 Punkte**.
- **Nur mit SHACL:** DCAT-AP-Konformität = **30 Punkte**.

---

## 4. Verbleibende Ergänzungen (nur Vokabular, keine neuen Felder)

Seit v2.12.0 sind **alle Eingabefelder vorhanden** (`odw_download_url`, `odw_media_type`,
`odw_access_rights`, `odw_dist_rights` …). Es sind daher **keine neuen Formularfelder** mehr nötig —
nur zwei kleine Vokabular-Ergänzungen für die Interoperabilitäts-/Wiederverwendbarkeits-Metriken:

1. **Format-Flags** in `config/dct-format-list.php` — je Format `machine_readable` und
   `non_proprietary`:
   ```php
   'CSV' => array( 'mime' => 'text/csv', 'eu_uri' => 'CSV',
                   'machine_readable' => true, 'non_proprietary' => true ),
   ```
2. **AccessRights-Vokabular** — kleine Prüfliste der 3 EU-Werte (PUBLIC / RESTRICTED / NON_PUBLIC),
   entweder inline oder als `config/access-rights.php`.

Lizenz-Vokabular (`licenses.txt`) und das kontrollierte Format-Set (`dct-format-list.php`) existieren
bereits und werden für die „aus Vokabular?"-Metriken wiederverwendet.

---

## 5. Umgang mit nicht-offline-prüfbaren Metriken (Netzwerk & SHACL)

Zwei Metrikgruppen kann das Plugin nicht rein lokal bestimmen: **URL-Erreichbarkeit** (80 P) und
**DCAT-AP-SHACL-Konformität** (30 P) — zusammen **110 von 405 Punkten**.

Empfohlener Ansatz (**„achievable max"**):
- Standardmäßig werden diese Metriken als **„nicht bewertet"** markiert und **aus dem Nenner
  herausgerechnet**. Angezeigt wird z. B. „270/295 bewertbaren Punkten" plus ein Hinweis, welche
  Metriken nicht geprüft wurden.
- Die vier Bewertungsstufen werden **proportional** auf den bewertbaren Maximalwert skaliert, damit
  ein Datensatz nicht dauerhaft für etwas abgewertet wird, das das Plugin nicht messen kann.
- **Optional per Einstellung aktivierbar (Phase 3):**
  - *Erreichbarkeits-Check:* `wp_remote_head()` mit Transient-Cache (z. B. 24 h), asynchron/on-demand.
  - *SHACL:* Anbindung des offiziellen [DCAT-AP SHACL Validierungsdienstes](https://data.europa.eu/api/mqa/shacl) oder Auslassen.

So bleibt das Plugin **self-contained** und ohne Pflicht-Netzwerkzugriff, kann aber auf Wunsch die
volle 405-Punkte-Bewertung liefern.

---

## 6. Architektur / Umsetzung im Code

**Konfiguration (Single Source of Truth):** Neue Datei `config/mqa-metrics.php` — Liste aller
Metriken mit `dimension`, `key`, `property`, `points`, `type` (`present` | `vocab` | `reachable` |
`shacl`), analog zum bestehenden `dcat-ap-fields.php`.

**Scoring-Klasse:** `includes/class-quality.php` wird auf das MQA-Modell umgestellt (oder eine neue
`ODW_MQA`-Klasse, die `ODW_Quality` ablöst). Rückgabe:
```php
array(
  'score'      => 270,          // erreichte Punkte
  'assessable' => 295,          // bewertbarer Max-Wert (ohne Netzwerk/SHACL, falls deaktiviert)
  'max'        => 405,
  'rating'     => 'good',       // excellent | good | sufficient | bad
  'dimensions' => array(
    'findability'    => array( 'score' => 100, 'max' => 100 ),
    'accessibility'  => array( 'score' =>  20, 'max' =>  20, 'not_assessed' => 80 ),
    // …
  ),
  'metrics'    => array( /* je Metrik: passed, points, earned, not_assessed */ ),
)
```

**Metrik-Prüfungen** greifen auf das kanonische JSON-LD (`odw_build_dataset_jsonld()`) zu bzw. auf
die Post-Meta — pro Metrik eine kleine, testbare Funktion.

---

## 7. Backward-Kompatibilität & Migration

- **Meta-Keys:** Bestehende `_odw_quality_score` (0–100) bleibt als abgeleiteter Wert erhalten
  (`round( score / assessable * 100 )`), damit Admin-Spalte, Shortcode und REST weiter funktionieren.
  Zusätzlich neue Keys `_odw_mqa_*` für Punkte, Stufe und Dimensions-Breakdown.
- **Neuberechnung:** Beim nächsten Speichern automatisch; für Bestand `wp open-data-wizard quality
  recalculate` (existiert bereits).
- **REST/JSON-LD:** MQA-Ergebnis unter dem plugin-eigenen `odw:`-Namespace ausweisen
  (`odw:mqaScore`, `odw:mqaRating`, Dimensions-Breakdown) — bricht keine bestehenden Konsumenten.
- **Admin-UI:** Qualitäts-Meta-Box zeigt künftig den Dimensions-Breakdown (5 Balken) + Stufe +
  konkrete Verbesserungshinweise; die Ampel/Badge in der Listenansicht mappt auf die 4 MQA-Stufen.

---

## 8. Phasenplan

| Phase | Inhalt | Punkte abgedeckt | Netzwerk? |
|-------|--------|------------------|-----------|
| **1 — Fundament** | MQA-Config + Scoring-Klasse + 5 Dimensionen + 4 Stufen; alle „gesetzt?"-Metriken (alle Felder vorhanden); UI-Breakdown; Migration/BC | ~265 | nein |
| **2 — Vokabular** | Format-Flags (nicht-proprietär/maschinenlesbar), Lizenz-/AccessRights-/MediaType-Vokabular-Checks | ~295 | nein |
| **3 — Netzwerk + SHACL (optional)** | Erreichbarkeits-Check (gecacht, per Einstellung), optionale SHACL-Anbindung | 405 | ja (opt-in) |

Jede Phase ist ein eigener, testbarer PR (mit Unit-Tests je Metrik, PHPCS/PHPStan grün).

---

## 9. Offene Entscheidungen

1. **Netzwerk/SHACL:** überhaupt umsetzen (Phase 3) oder dauerhaft „nicht bewertet"?
2. **Ersetzen vs. parallel:** MQA das alte 0–100-Scoring **ablösen** oder **zusätzlich** anzeigen?
3. **Stufen-Skalierung:** bei deaktiviertem Netzwerk/SHACL proportional skalieren (empfohlen) oder
   feste 405-Skala mit sichtbarer Lücke?
4. **Gewichtung im UI:** Sollen die MQA-Verbesserungshinweise nach Punktgewicht priorisiert werden
   (z. B. „accessURL erreichbar = 50 P" zuerst)? (Neue Felder sind seit v2.12.0 nicht mehr nötig.)

---

## 10. Empfehlung

**Phase 1 + 2 offline umsetzen** (deckt ~295/405 Punkte standardkonform ab, ohne externe
Abhängigkeit), MQA **ablösend** für das alte Scoring, mit **proportionaler Stufen-Skalierung** und
klarer Kennzeichnung der nicht bewertbaren Metriken. **Phase 3** (Netzwerk/SHACL) später optional per
Einstellung nachrüsten.
