# Data Provider Interface v3

Library to help developers create and manage metadata on piveau platform.

## Development

To run the app locally, you need to have [pnpm](https://pnpm.io/installation) installed.

**Requirements**
- node >= v22.18
- pnpm >= v10.13.1

```sh
# Install lib dependencies
pnpm install

# Install app dependencies and run it
cd app
cp config/user-config.sample.js config/user-config.js
pnpm install
pnpm dev

# Or run histoire instance
cd packages/dpi
pnpm story:dev
```

# Vue 3 + TypeScript + Vite

This template should help get you started developing with Vue 3 and TypeScript in Vite. The template uses Vue 3 `<script setup>` SFCs, check out the [script setup docs](https://v3.vuejs.org/api/sfc-script-setup.html#sfc-script-setup) to learn more.

Learn more about the recommended Project Setup and IDE Support in the [Vue Docs TypeScript Guide](https://vuejs.org/guide/typescript/overview.html#project-setup).

# Dokumentation für converter.js

## Übersicht
Die Datei `converter.js` ist ein JavaScript-Modul, das Formulardaten aus einem DPIv3-Workflow (Data Provider Interface Version 3) in ein JSON-LD-Format konvertiert, um sie an eine API zu senden. Es basiert auf DCAT-AP.DE Standards und strukturiert Daten für Datasets und Distributionen. Der Converter ist modular aufgebaut und kann angepasst werden, um verschiedene APIs anzusprechen, z. B. durch Änderung von URLs, Prefixes oder Datenmappings. 

**WICHTIG:** Nativ wird die piveau-API unterstützt. Sollte das DPI demnach in Kombination mit einem Piveau Backend betrieben werden, sollte der Einrichtungsaufwand äußerst gering sein. Zudem gilt es zu beachten, dass die DCAT-AP.de Spezifikation verwedet wird. Sollte eine **individuelle Spezifikation** vonnöten sein, muss das Schema angepasst und individuelle Komponenten, gemäß den Bedürfnissen der Properties, selbst erstellt und integriert werden. **DIESER PROZESS IST NICHT TRIVIAL.** 

**Hauptzweck:** Konvertierung der Formularwerte aus den DPI-Wizard-Schritten in ein für verschiedene API's geeignetes Format.

**Bekannte Einschränkungen:**
- Standardsprache ist Deutsch (`@language: "de"`). Es gibt derzeit auch keine Möglichkeit, dies im Frontend zu ändern.

## Abhängigkeiten
- `axios`: Für HTTP-Anfragen (z. B. HEAD-Requests zur ID-Überprüfung).
- `asSomeArray`: Hilfsfunktion aus `useDpiSimpleLoader`, um Arrays zu handhaben.

```js 
function asSomeArray(val: T | T[]): T[] | undefined {
     if (Array.isArray(val)) {return val;} return val ? [val] : undefined;
}
```

## Funktionen

### `checkUniqueID(property)`
Überprüft, ob eine Dataset-ID eindeutig ist. (Derzeit nicht funktional)

- **Parameter:** `property` (String) – Die ID.
- **Returns:** Promise, das `true` zurückgibt, wenn die ID verfügbar ist, sonst `false`.
- **Hinweis:** Immer `true` zurückgeben, wenn keine ID gesetzt ist.

### `cleanString(input)`
Bereinigt einen Eingabestring, um ihn als ID zu verwenden: Entfernt nicht-alphanumerische Zeichen, ersetzt Leerzeichen durch "-", und wandelt in Kleinbuchstaben um.

- **Parameter:** `input` (String) – Der zu bereinigende String.
- **Returns:** Bereinigter String.

### `sendDataToAPI(formValues, dpiContext, userData, hubURL)`
Hauptfunktion zur Verarbeitung von Formulardaten und Konvertierung in JSON-LD.

- **Parameter:**
  - `formValues`: Objekt mit Formulardaten aus verschiedenen Schritten (z. B. `Discoverability`, `BasicInfos`). Enthält alle eingegebenen Werte aus dem Wizard
  - `dpiContext`: Objekt mit verschiedenen Informationen/ Konfigurationen zum DPI (hier genutzt für die Prefixes der DCAT-AP Namespaces).
  - `userData`: Benutzerdaten, einschließlich Berechtigungen.
  - `hubURL`: Basis-URL der API.
- **Returns:** Objekt mit `actionParams` (für API-Aufruf) und `body` (JSON-LD-Daten).
- **Datenfluss:**
  1. Erstellt UIDs für Distributionen.
  2. Verarbeitet jeden individuellen Schritt (z. B. Themen, Titel, Beschreibungen, räumliche/temporale Abdeckung).
  3. Wandelt in JSON-LD um und bereitet API-Parameter vor.

### `transformToJSONLD(data, prefixes)`
Wandelt verarbeitete Daten in JSON-LD-Format um.

- **Parameter:**
  - `data`: Verarbeitetes Datenobjekt.
  - `prefixes`: Prefixes für den `@context` des JSON-LD Objekts.
- **Returns:** JSON-LD-Objekt mit `@graph` und `@context`.

## Anpassungen für andere APIs
Der Converter ist flexibel und kann für andere APIs angepasst werden:
- **URLs ändern:** Ersetze `hubUrl` in der `user-config.js` (zu finden im ../app/config Ordner).
- **Prefixes und Kontext:** Aktualisiere `prefixes.js` (zu finden im ..data-provider-interface/config/"spezifikation") wenn weitere Namespaces hinzugefügt werden müssen.
- **Datenmappings:** Modifiziere die Verarbeitung in `sendDataToAPI` für neue Felder oder Formate. Beispiel: Füge neue Schritte hinzu oder ändere Typen (z. B. von `"dcat:Dataset"` zu einem anderen Typ).
- **Validierung:** Erweitere `checkUniqueID`.
- **Sprache und Typen:** Passe Standardsprache oder Datentypen an (z. B. `@language: "en"` für Englisch).
- **Beispiel-Anpassung:** Für eine REST-API ohne JSON-LD: Entferne `transformToJSONLD` und sende `refinedData` direkt als JSON.

# Dokumentation für useDpiSimpleLoader.ts

## Übersicht
Die Datei `useDpiSimpleLoader.ts` ist ein Vue.js Composable, das JSON-LD-Daten (basierend auf dem DCAT-AP.de Standard) in das DPI Format transformiert. Es dient zur Verarbeitung von Dataset- und Distributions-Daten vom Datenportal und stellt sie in einer strukturierten Form für die Benutzeroberfläche bereit. Das Composable ist modular aufgebaut und kann angepasst werden, um verschiedene APIs oder Datenformate zu unterstützen.

**Hauptzweck:** Transformation von DCAT-AP-Daten in ein DPI-konformes Objekt (formValues), inklusive Validierung und Fehlerbehandlung.

```js 
formValues : {
    "Landing": {
        "happyFlowLandingPage": {}
    },
    "Discoverability": {
        "discoverabilityPage": [
        {
            "isValid": "unset"
        }
        ],
        "hvdPage": [
        {
            "isValid": true
        }
        ]
    },
    "BasicInfos": {
        "dct:title": [
        {
            "isValid": "unset",
            "@value": "",
            "@language": "de"
        }
        ],
        "dct:description": [
        {
            "isValid": "unset",
            "@value": "",
            "@language": "de"
        }
        ],
        "dct:modified": [
        {
            "isValid": "unset",
            "@type": "http://www.w3.org/2001/XMLSchema#date"
        }
        ],
        "dct:publisher": [
        {
            "isValid": "unset",
            "foaf:name": "",
            "foaf:mbox": "",
            "foaf:homepage": ""
        }
        ],
        "dcat:contactPoint": [
        {
            "isValid": "unset",
            "vcard:fn": "",
            "vcard:hasEmail": "",
            "vcard:hasTelephone": ""
        }
        ]
    },
    "Covering": {
        "dcatde:politicalGeocodingURI": [
        {
            "isValid": true
        }
        ],
        "dcat:temporalResolution": {
        "isValid": true,
        "type": "dct:PeriodOfTime",
        "dct:temporal": [
            {
            "dataType": "date",
            "dcat:startDate": "",
            "dcat:endDate": ""
            }
        ]
        }
    },
    "DistributionSimple": {
        "dcat:distribution": [
        {
            "isValid": "unset"
        }
        ],
        "dct:license": [
        {
            "isValid": "unset",
            "dcterms:license": "",
            "title": "",
            "uri": ""
        }
        ]
    },
    "ReviewAndPublish": {
        "reviewAndPublishPage": {}
    },
    "Additionals": {}
}
```
***Beispieldarstellung des vom DPI erwarteten Objekts***

**Bekannte Einschränkungen:**
- Fokussiert auf DPIv3-Datasets (prüft `dpi:isDPIv3`) um sicherzustellen, dass valide Datensätze ins UI geladen werden.
- Standardsprache ist Deutsch für lokalisierte Strings.
- Verwendet spezifische Resolvers und Transformer aus `@piveau/jsonld`.
- Verwendet spezifische Endpunkte zu Vokabularen um Autocomplete Inputs mit Labels (anstelle der gespeicherten URIs) darstellen zu können. 

## Abhängigkeiten
- `vue`: Für Reactive-Referenzen und Computed-Properties.
- `@vueuse/core`: Für `useAsyncState` zur asynchronen Verarbeitung.
- `@piveau/jsonld`: Für JSON-LD-Resolver, Transformer und Typen (z. B. `DcatDataset`, `SkosConcept`).
- `lodash-es`: Für `isEmpty` zur Prüfung leerer Objekte.

## Exportierte Funktionen und Interfaces

### `UseDpiSimpleLoaderParams`
Interface für die Parameter des Composables.
- `enabled?: MaybeRefOrGetter<boolean>`: Aktiviert/Deaktiviert die Verarbeitung.
- `hubSearchUrl: string`: URL zur API des Datenportals.

### `normalizeToArray<T>(value: T | T[] | undefined | null): T[]`
Normalisiert einen Wert zu einem Array, behandelt `undefined`/`null` als leeres Array.

- **Parameter:** `value` – Der zu normalisierende Wert.
- **Rückgabe:** Normalisiertes Array

### `getFirstItem<T>(value: T | T[] | undefined | null): T | undefined`
Gibt das erste Element eines Arrays zurück oder den Wert selbst, wenn es kein Array ist.

- **Parameter:** `value` – Der Wert.
- **Rückgabe:** Erstes Element oder `undefined`.

### `asSomeArray<T>(val: T | T[]): T[] | undefined` (veraltet)
Legacy-Funktion für Rückwärtskompatibilität; normalisiert zu einem Array oder `undefined`.

### `purgeNullishAndEmptyProperties(obj: Record<string, any>): Record<string, any>`
Entfernt null/undefined/leere Eigenschaften aus einem Objekt.

- **Parameter:** `obj` – Das Objekt.
- **Rückgabe:** Bereinigtes Objekt.

### `useDpiSimpleLoader(jsonld: MaybeRefOrGetter<Record<string, any>>, options: UseDpiSimpleLoaderParams)`
Haupt-Composable zur Verarbeitung von JSON-LD-Daten.

- **Parameter:**
  - `jsonld`: Die JSON-LD-Daten als Reactive-Wert.
  - `options`: Parameter-Objekt.
- **Rückgabe:** Objekt mit `result` (transformierte DPI-Daten), `isReady`, `errors`, etc.
- **Funktionsweise:** Verwendet `defineJsonldResolver` zur Transformation, prüft auf DPIv3 und wandelt mit `toDpi` um.

### `extractStringValue(value: any): string`
Extrahiert einen String-Wert aus verschiedenen JSON-LD-Formaten (`@value`, `@id`, etc.).

### `extractLocalizedString(value: any, preferredLanguage: string = "de"): string`
Extrahiert einen lokalisierten String, bevorzugt Deutsch.

### `toDpi(dataset: DcatDataset): Record<string, any>`
Transformiert ein DCAT-Dataset in DPI-Format mit Abschnitten wie Landing, Discoverability, etc.

- **Rückgabe:** Objekt mit DPI-Abschnitten.

### `toAccessService(accessServices: any[])` und `toDocument(documents: any[])`
Transformieren Access-Services und Dokumente in DPI-Format.

## Anpassungen für andere APIs
Der Composable ist flexibel und kann angepasst werden:
- **URLs ändern:** Ersetze `hubSearchUrl` in `UseDpiSimpleLoaderParams` durch eine neue Basis-URL.
- **Resolver und Transformer:** Aktualisiere `defineJsonldResolver` mit neuen Resolvers oder Transformern aus `@piveau/jsonld`.
- **Datenmappings:** Modifiziere Funktionen wie `toDpi`, `createBasicInfosSection`, etc., um neue Felder oder Formate zu unterstützen (z. B. andere Standards als DCAT-AP).
- **Validierung:** Erweitere die Prüfung in `useDpiSimpleLoader` für andere Dataset-Typen (z. B. entferne `dpi:isDPIv3`-Check).
- **Sprache:** Ändere `preferredLanguage` in `extractLocalizedString` für andere Standardsprachen.
- **Beispiel-Anpassung:** Für eine REST-API ohne JSON-LD: Entferne JSON-LD-Transformation und sende Rohdaten direkt.

Für detaillierte Änderungen konsultiere den Code oder teste in einer Entwicklungs-Umgebung. Bei Bedarf können Unit-Tests für `toDpi` hinzugefügt werden.