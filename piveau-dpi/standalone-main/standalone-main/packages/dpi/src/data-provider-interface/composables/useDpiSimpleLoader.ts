import {
  type MaybeRefOrGetter,
  computed,
  ref,
  toRef,
  toValue,
  watch,
} from "vue";
import { useAsyncState } from "@vueuse/core";
import {
  asArray,
  datasetResolvers,
  type DcatDataset,
  dcatDatasetTransformer,
  defineJsonldResolver,
  type SkosConcept,
  type DcatDistribution,
  schemaDocument,
} from "@piveau/jsonld";
import { isEmpty } from "lodash-es";

export interface UseDpiSimpleLoaderParams {
  enabled?: MaybeRefOrGetter<boolean>;
  hubSearchUrl: string;
}

/**
 * Normalize a value to an array, handling undefined/null gracefully
 */
export function normalizeToArray<T>(value: T | T[] | undefined | null): T[] {
  if (value === undefined || value === null) {
    return [];
  }
  return Array.isArray(value) ? value : [value];
}

/**
 * Get the first item from a maybe-array value
 */
export function getFirstItem<T>(
  value: T | T[] | undefined | null
): T | undefined {
  if (value === undefined || value === null) {
    return undefined;
  }
  return Array.isArray(value) ? value[0] : value;
}

/**
 * Legacy function for backward compatibility
 */
export function asSomeArray<T>(val: T | T[]): T[] | undefined {
  if (Array.isArray(val)) {
    return val;
  }
  return val ? [val] : undefined;
}

export function purgeNullishAndEmptyProperties(
  obj: Record<string, any>
): Record<string, any> {
  return Object.fromEntries(
    Object.entries(obj).filter(
      ([_, value]) => value !== null && value !== undefined && !isEmpty(value)
    )
  );
}

export function useDpiSimpleLoader(
  jsonld: MaybeRefOrGetter<Record<string, any>>,
  options: UseDpiSimpleLoaderParams
) {
  const { enabled, hubSearchUrl } = options;
  const _enabled = toRef(enabled);

  const { safeTransform } = defineJsonldResolver({
    baseUrl: hubSearchUrl,
    resolvers: datasetResolvers(),
    transformer: dcatDatasetTransformer,
  });

  const errors = ref<{ code: string; message: string }[]>([]);

  // Parse DCAT-AP data
  const {
    state: processedInput,
    isReady: _isProcessedInputReady,
    isLoading: _isProcessedInputLoading,
    error: _error,
    execute: executeParser,
  } = useAsyncState(
    async () => {
      const transformed = await safeTransform(toValue(jsonld));

      if (transformed.error) {
        errors.value.push({
          code: "transform_failed",
          message: transformed.error.message,
        });
        return null;
      }

      const data = transformed.data;

      if (!data || !data["dpi:isDPIv3"]?.["@value"]) {
        errors.value.push({
          code: "not_dpi_v3",
          message: `The dataset is not a DPI v3 dataset. ID ${data["@id"]}`,
        });
      }

      const dpiData = toDpi(data);
      return {
        dpiData,
        data,
      };
    },
    null,
    { immediate: false }
  );

  const isProcessedInputReady = computed(
    () =>
      _enabled.value &&
      !_isProcessedInputLoading.value &&
      !!_isProcessedInputReady.value
  );
  const isMaterialized = computed(
    () => _enabled.value && !!isProcessedInputReady.value
  );
  const isReady = computed(() => isMaterialized.value);
  const result = computed(() => processedInput.value?.dpiData);
  const rawResult = computed(() => processedInput.value?.data);

  // Re-run parser when input changes
  watch(
    () => toValue(jsonld),
    (newValue) => (newValue && _enabled.value ? executeParser() : null)
  );

  watch(rawResult, (newValue) => {
    console.log("jsonld resolved result:", newValue);
  });

  watch(_error, (newValue) => {
    console.log("jsonld error:", newValue);
  });

  return {
    processedInput,
    isProcessedInputReady,

    result,
    isMaterialized,

    isReady,
    errors,
  };
}

/**
 * Extract a string value from various JSON-LD value formats
 */
export function extractStringValue(value: any): string {
  if (typeof value === "string") {
    return value;
  }

  if (value && typeof value === "object") {
    // If it has @value, use that
    if (value["@value"]) {
      return String(value["@value"]);
    }
    // If it has @id, use that
    if (value["@id"]) {
      return String(value["@id"]);
    }
  }

  return "";
}

/**
 * @deprecated Use getFirstItem instead
 */
function getFirst(value: any): any {
  return getFirstItem(value);
}

/**
 * Extract a localized string from a multilingual object or language container
 * Prefers German ('de') language when available
 */
export function extractLocalizedString(
  value: any,
  preferredLanguage: string = "de"
): string {
  if (!value) {
    return "";
  }

  if (typeof value === "string") {
    return value;
  }

  // Handle multilingual objects: { en: 'hello', de: 'hallo' }
  if (
    typeof value === "object" &&
    !Array.isArray(value) &&
    !value["@value"] &&
    !value["@id"]
  ) {
    return value[preferredLanguage] || Object.values(value)[0] || "";
  }

  // Handle language containers: [{ @language: 'en', @value: 'hello' }]
  if (Array.isArray(value)) {
    const preferred = value.find(
      (item: any) => item["@language"] === preferredLanguage
    );
    if (preferred) {
      return preferred["@value"] || "";
    }
    // Fallback to first item
    const first = value[0];
    return first?.["@value"] || extractStringValue(first) || "";
  }

  // Handle single language-tagged string: { @language: 'en', @value: 'hello' }
  if (value["@language"] && value["@value"]) {
    return value["@value"];
  }

  // Fallback to generic string extraction
  return extractStringValue(value);
}

/**
 * @deprecated Use extractLocalizedString instead
 */
function getLocalizedLabel(labelObject: any): string {
  return extractLocalizedString(labelObject);
}

/**
 * @deprecated Use extractLocalizedString instead
 */
function _getLocalizedString(values: any[]): string {
  return extractLocalizedString(values);
}

interface UriLabel {
  uri: string;
  label: string;
  "@value"?: string;
}

function defineUriLabel(options: UriLabel): UriLabel {
  return {
    uri: options.uri,
    label: options["label"],

    //
    "@value": options["label"],
  };
}

function toUriLabel(
  concept: SkosConcept,
  options?: {
    useAltLabel?: boolean;
  }
): UriLabel {
  const label = options?.useAltLabel
    ? concept["altLabel"]
    : concept["prefLabel"];
  return defineUriLabel({
    uri: concept["@id"] || concept["purl:identifier"] || "",
    label: label
      ? extractLocalizedString(label)
      : concept["purl:identifier"] || "",
  });
}

function toLiteral(
  value:
    | string
    | {
        "@type"?: string;
        "@value": string;
      }
): {
  "@type"?: string;
  "@value": string;
} {
  if (typeof value === "string") {
    return {
      "@value": value,
    };
  }
  return value;
}

/**
 * Create a valid DPI item with common structure
 */
function createDpiItem(
  data: Record<string, any>,
  isValid: boolean = true
): Record<string, any> {
  return {
    isValid,
    ...data,
  };
}

/**
 * Transform language containers to DPI language-tagged strings
 */
function transformLanguageContainer(
  value: any,
  defaultLanguage: string = "de"
): any[] {
  if (!value) {
    return [];
  }

  const normalized = normalizeToArray(value);

  return normalized.map((item: any) => {
    if (typeof item === "string") {
      return createDpiItem({
        "@value": item,
        "@language": defaultLanguage,
      });
    }

    if (item && typeof item === "object") {
      // Handle language-tagged strings
      if (item["@language"] && item["@value"]) {
        return createDpiItem({
          "@value": item["@value"],
          "@language": item["@language"],
        });
      }

      // Handle other object types
      return createDpiItem({
        "@value": extractStringValue(item),
        "@language": defaultLanguage,
      });
    }

    return createDpiItem({
      "@value": "",
      "@language": defaultLanguage,
    });
  });
}

/**
 * Transform values to DPI typed literals (for dates, etc.)
 */
function transformToTypedLiteral(value: any, type: string): any[] {
  if (!value) {
    return [];
  }

  const normalized = normalizeToArray(value);

  return normalized.map((item: any) =>
    createDpiItem({
      "@type": type,
      "@value": extractStringValue(item),
    })
  );
}

/**
 * Transform a contact/publisher object to DPI format
 */
function transformContactObject(
  contact: any,
  fields: Record<string, string>
): Record<string, any> {
  const result: Record<string, any> = { isValid: true };

  for (const [dpiField, sourceField] of Object.entries(fields)) {
    result[dpiField] = extractStringValue(contact?.[sourceField]);
  }

  return result;
}

/**
 * Create the Landing section of DPI output
 */
function createLandingSection(dataset: DcatDataset): Record<string, any> {
  return {
    happyFlowLandingPage: {},
  };
}

/**
 * Create the Discoverability section of DPI output
 */
function createDiscoverabilitySection(
  dataset: DcatDataset
): Record<string, any> {
  const result: any = {
    discoverabilityPage: [{ isValid: true }] as any[],
  };

  if (dataset["dcatap:hvdCategory"]) {
    const hvdCategories = normalizeToArray(
      dataset["dcatap:hvdCategory"]
    ).filter(Boolean);
    result.hvdPage = hvdCategories?.map((category) => {
      return {
        isValid: true,
        uri: category["@id"],
        label: extractLocalizedString(category.prefLabel),
      };
    });
  }

  if (dataset["dcat:theme"]) {
    const themes = normalizeToArray(dataset["dcat:theme"]);
    result.discoverabilityPage = [
      { isValid: true },
      ...themes.map((theme) =>
        createDpiItem({
          id: extractStringValue(theme).split("/").pop(),
          uri: extractStringValue(theme),
          label: extractLocalizedString(theme.prefLabel),
        })
      ),
    ];
  }

  return result;
}

/**
 * Create the BasicInfos section of DPI output
 */
function createBasicInfosSection(dataset: DcatDataset): Record<string, any> {
  const id = dataset["@id"]?.split("/").pop() || "";

  const result = {
    datasetID: id,
    "dct:title": [] as any[],
    "dct:description": [] as any[],
    "dct:modified": [""],
    "dct:publisher": [] as any[],
    "dcat:contactPoint": [] as any[],
  };

  // Transform title
  if (dataset["dct:title"]) {
    result["dct:title"] = transformLanguageContainer(dataset["dct:title"]);
  }

  // Transform description
  if (dataset["dct:description"]) {
    result["dct:description"] = transformLanguageContainer(
      dataset["dct:description"]
    );
  }

  // Transform modified date
  if (dataset["dct:modified"]) {
    result["dct:modified"] = transformToTypedLiteral(
      dataset["dct:modified"],
      "http://www.w3.org/2001/XMLSchema#date"
    );
  }

  // Transform publisher
  if (dataset["dct:publisher"]) {
    const publisher = getFirstItem(dataset["dct:publisher"]);
    result["dct:publisher"] = [
      transformContactObject(publisher, {
        "foaf:name": "foaf:name",
        "foaf:mbox": "foaf:mbox",
        "foaf:homepage": "foaf:homepage",
      }),
    ];
  }

  // Transform contact point
  if (dataset["dcat:contactPoint"]) {
    const contact = getFirstItem(dataset["dcat:contactPoint"]);
    result["dcat:contactPoint"] = [
      transformContactObject(contact, {
        "vcard:fn": "vcard:fn",
        "vcard:hasEmail": "vcard:hasEmail",
        "vcard:hasTelephone": "vcard:hasTelephone",
      }),
    ];
  }

  return result;
}

/**
 * Create the Covering section of DPI output
 */
function createCoveringSection(dataset: DcatDataset): Record<string, any> {
  return {
    "dcatde:politicalGeocodingURI": dataset[
      "dcatapde:politicalGeocodingURI"
    ]?.map((val) => {
      const isValid = true;

      // TODO implemenet i18n here!
      const keyMapping = {
        districtKey: "Kreis",
        governmentDistrictKey: "Bezirk",
        municipalityKey: "Gemeindeschlüssel",
        municipalAssociationKey: "Gemeindeverbände",
        stateKey: "Bundesland",
        regionalKey: "Regionalschlüssel",
      };

      let inVoc = val["@id"].split("/").slice(-2, -1)[0];
      inVoc = keyMapping[inVoc] || inVoc; // Standardwert bleibt inVoc, wenn kein Mapping vorhanden ist

      return {
        isValid,
        uri: val["@id"],
        id: val["prefLabel"]?.["de"] || val["@id"].split("/").pop(),
        label: extractLocalizedString(val.altLabel),
        inVoc: inVoc,
      };
    }),

    "dcat:temporalResolution": dataset["dct:temporal"]?.reduce((acc, val) => {
      const isValid = true;

      const firstStartDate = getFirstItem(val["dcat:startDate"]);
      const firstEndDate = getFirstItem(val["dcat:endDate"]);

      const startDate = extractStringValue(firstStartDate) || "";
      const endDate = extractStringValue(firstEndDate) || "";

      const dateType =
        typeof firstStartDate !== "string" &&
        firstStartDate?.["@type"]! ===
          "http://www.w3.org/2001/XMLSchema#dateTime"
          ? "dateTime"
          : "date";

      const startDateResolved =
        dateType === "date" ? startDate : startDate.split("T")[0];
      const endDateResolved =
        dateType === "date" ? endDate : endDate.split("T")[0];

      const startTimeResolved =
        dateType === "dateTime" ? startDate.split("T")[1] : "";
      const endTimeResolved =
        dateType === "dateTime" ? endDate.split("T")[1] : "";

      // Gib das letzte Element direkt zurück
      return {
        isValid,
        type: "dct:PeriodOfTime",
        "dct:temporal": [
          {
            dataType: dateType,
            "dcat:startDate": startDateResolved,
            "dcat:endDate": endDateResolved,
            startTime: startTimeResolved || "",
            endTime: endTimeResolved || "",
          },
        ],
      };
    }, {}),
  };
}

/**
 * Transform a single distribution to DPI format
 */
function transformDistribution(
  dist: DcatDistribution,
  idx: number
): Record<string, any> {
  if (!dist) {
    return {};
  }

  const checksum = getFirstItem(dist["spdx:checksum"]);

  return {
    isValid: true,
    id: idx + 1,
    "dcat:accessURL":
      extractStringValue(getFirstItem(dist["dcat:accessURL"])) || "",
    "dcat:downloadURL": dist["dcat:downloadURL"]?.map((val) => ({
      "@id": extractStringValue(val),
    })) || [{ "@id": "" }],
    "dct:format": {
      // redundancy in label and title for better compatibility
      label: getFirstItem(dist["dct:format"])?.["purl:identifier"] || "",
      title: getFirstItem(dist["dct:format"])?.["purl:identifier"] || "",
      uri: getFirstItem(dist["dct:format"])?.["@id"] || "",
    },
    "dct:title": extractLocalizedString(dist["dct:title"]),
    "dct:description": extractLocalizedString(dist["dct:description"]),
    "dct:modified": extractStringValue(getFirstItem(dist["dct:modified"])),
    "dct:issued": extractStringValue(getFirstItem(dist["dct:issued"])),
    "dcat:byteSize": extractStringValue(getFirstItem(dist["dcat:byteSize"])),
    "dcatde:licenseAttributionByText":
      extractLocalizedString(dist["dcatapde:licenseAttributionByText"]) || "",
    "dct:license": dist["dct:license"]
      ? {
          isValid: true,
          uri: getFirstItem(dist["dct:license"])?.["@id"] || "",
          "dcterms:license":
            extractLocalizedString(
              getFirstItem(dist["dct:license"])?.altLabel
            ) || "",
          label:
            extractLocalizedString(
              getFirstItem(dist["dct:license"])?.prefLabel
            ) || "",
          title:
            extractLocalizedString(dist["dcatapde:licenseAttributionByText"]) ||
            "",
        }
      : {},
    "dct:accessRights": dist["dct:accessRights"]
      ? getFirstItem({
        uri: getFirstItem(dist["dct:accessRights"])?.["@id"] || '',
        label: getFirstItem(dist["dct:accessRights"])?.["prefLabel"] || { de: ''},
      })
      : {},
    "dcatap:availability": {
      // Redundant label and title for better compatibility
      label: getFirstItem(dist["dcatap:availability"])?.["prefLabel"] || "",
      title: getFirstItem(dist["dcatap:availability"])?.["prefLabel"] || "",
      uri: getFirstItem(dist["dcatap:availability"])?.["@id"] || "",
    },
    "dct:language":
      dist["dct:language"]
        ?.map((val: SkosConcept) => toUriLabel(val))
        .filter(Boolean) || [],
    "dcat:mediaType": defineUriLabel({
      label:
        extractLocalizedString(
          getFirstItem(dist["dcat:mediaType"])?.prefLabel
        ) || "",
      uri: getFirstItem(dist["dcat:mediaType"])?.["@id"] || "",
    }),
    "dcat:compressFormat": defineUriLabel({
      label:
        extractLocalizedString(
          getFirstItem(dist["dcat:compressFormat"])?.prefLabel
        ) || "",
      uri: getFirstItem(dist["dcat:compressFormat"])?.["@id"] || "",
    }),
    "dcat:packageFormat": defineUriLabel({
      label:
        extractLocalizedString(
          getFirstItem(dist["dcat:packageFormat"])?.prefLabel
        ) || "",
      uri: getFirstItem(dist["dcat:packageFormat"])?.["@id"] || "",
    }),
    "adms:status": {
      uri: getFirstItem(dist["adms:status"])?.["@id"] || "",
      "@value":
        extractLocalizedString(getFirstItem(dist["adms:status"])?.prefLabel) ||
        "",
    },
    checksum: {
      title: extractStringValue(getFirstItem(checksum?.["spdx:checksumValue"])),
      "spdx:checksum":
        extractStringValue(getFirstItem(checksum?.["spdx:algorithm"]))
          ?.split("_")
          .pop() || "",
      uri: extractStringValue(
        getFirstItem(checksum?.["spdx:algorithm"])
      )?.replace("spdx:", "http://spdx.org/rdf/terms#"),
    },
    accessServices: toAccessService(dist["dcat:accessService"] || []),
    documentations: toDocument(dist["foaf:page"] || []),
    conformsToItems: dist["dct:conformsTo"]
      ?.map((val: any, idx: number) => ({
        // for some reason dct:conformsTo is different from the one on dcat:dataset level
        id: idx + 1,
        "dcat:accessURL": val["@id"],
        "dct:title": extractLocalizedString(val["rdfs:label"]) || "",
        "dcat:downloadURL": val["@id"],

        // redundancy to match properties on dcat:dataset level
        "rdfs:label": extractLocalizedString(val["rdfs:label"]) || "",
        uri: val["@id"],
      }))
      .filter(Boolean),
    policyItems: dist["odrl:hasPolicy"]?.map((val, idx) => ({
      // don't ask
      id: idx + 1,
      "dcat:downloadURL": val["@id"],
      "dcat:accessURL": val["@id"],
    })) || [{ id: 1, "dcat:downloadURL": "", "dcat:accessURL": "" }],
  };
}

/**
 * Create the DistributionSimple section of DPI output
 */
function createDistributionSimpleSection(
  dataset: DcatDataset
): Record<string, any> {
  const result = {
    "dcat:distribution": [] as any[],
  };

  if (dataset["dcat:distribution"]) {
    const distributions = normalizeToArray(dataset["dcat:distribution"]);
    result["dcat:distribution"] = distributions
      .map((dist, idx) => transformDistribution(dist, idx))
      .filter((dist) => Object.keys(dist).length > 0);
  }
  result["dct:license"] = [result["dcat:distribution"]?.[0]?.["dct:license"]];

  console.log(result);

  return result;
}

/**
 * Create the ReviewAndPublish section of DPI output
 */
function createReviewAndPublishSection(
  dataset: DcatDataset
): Record<string, any> {
  return {
    reviewAndPublishPage: [{ isValid: true }],
  };
}

/**
 * Create the Additionals section of DPI output
 */
function createAdditionalsSection(dataset: DcatDataset): Record<string, any> {
  const additionals = {
    "dct:language": dataset["dct:language"]
      ?.map((val: SkosConcept) => toUriLabel(val))
      .filter(Boolean),
    "dcatde:politicalGeocodingLevelURI": dataset[
      "dcatapde:politicalGeocodingLevelURI"
    ]?.map((level) => {
      return {
        uri: level["@id"],
        "@value": extractLocalizedString(level.prefLabel),
      };
    }),
    "dct:conformsTo": dataset["dct:conformsTo"]
      ?.map((val: any) => ({
        "rdfs:label": extractLocalizedString(val["rdfs:label"]) || "",
        "@id": val["@id"],
      }))
      .filter(Boolean),
    "dct:accessRights": dataset["dct:accessRights"]
      ? asSomeArray(toUriLabel(dataset["dct:accessRights"]))
      : [],
    "dct:issued": normalizeToArray(dataset["dct:issued"]),
    "dct:provenance": dataset["dct:provenance"]
      ?.map((val: any) => ({
        "rdfs:label": extractLocalizedString(val["rdfs:label"]) || "",
        uri: val["@id"],
      }))
      .filter(Boolean),
    "dct:accrualPeriodicity": dataset["dct:accrualPeriodicity"]
      ? asSomeArray(toUriLabel(dataset["dct:accrualPeriodicity"]))
      : [],
    "dct:type": dataset["dct:type"]
      ?.map((val: SkosConcept) => toUriLabel(val))
      .filter(Boolean),
    "dcat:spatialResolutionInMeters":
      dataset["dcat:spatialResolutionInMeters"]?.map(toLiteral) || undefined,
    "dcat:temporalResolution": dataset["dcat:temporalResolution"]
      ? dataset["dcat:temporalResolution"]
          .map(toLiteral)
          .map((value) => {
            const duration = value["@value"];
            const match = duration.match(
              /^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)?$/
            );
            if (match) {
              const [, year, month, day, hour, minute, second] = match.map(
                (v) => v || "0"
              );
              return [
                { Year: year },
                { Month: month },
                { Day: day },
                { Hour: hour },
                { Minute: minute },
                { Second: second },
              ];
            }
            return undefined;
          })
          .filter(Boolean)
          .flat()
      : undefined,
    "dcat:qualifiedRelation": asSomeArray(
      dataset["dcat:qualifiedRelation"]
    )?.filter(Boolean),
    "dct:creator": asSomeArray(dataset["dct:creator"])?.filter(Boolean).map((val)=>({
      ...val,
      'rdf:type': val?.['@type']==='foaf:Agent'?'Person':'Organisation'
    })),
    "dct:contributor": asSomeArray(dataset["dct:contributor"])?.filter(Boolean)
    .map((val)=>({
      ...val,
      'rdf:type': val?.['@type']==='foaf:Agent'?'Person':'Organisation'
    })),
    "dcatde:contributorID": dataset["dcatapde:contributorID"]
      ?.map((val: SkosConcept) => toUriLabel(val))
      .filter(Boolean),
    "dcatde:geocodingDescription": asSomeArray(
      dataset["dcatapde:geocodingDescription"]
    )?.filter(Boolean),
    "dct:identifier": dataset["dct:identifier"]?.map((val) => ({
      "@value": val,
    })),
    "adms:identifier": asSomeArray(dataset["adms:identifier"])?.filter(Boolean),
    "owl:versionInfo": dataset["owl:versionInfo"]?.map((val: any) => ({
      "@value": val,
    })),
    "adms:versionNotes": dataset["adms:versionNotes"],
    "dcatde:legalBasis": asSomeArray(dataset["dcatapde:legalBasis"])?.filter(
      Boolean
    ),
    "dct:relation": asSomeArray(dataset["dct:relation"])?.filter(Boolean),
    "dcat:landingPage": asSomeArray(dataset["dcat:landingPage"])?.filter(
      Boolean
    ),
    "prov:wasGeneratedBy": asSomeArray(dataset["prov:wasGeneratedBy"])?.filter(
      Boolean
    ),
    "dct:isReferencedBy": asSomeArray(dataset["dct:isReferencedBy"])?.filter(
      Boolean
    ),
    "dct:source": asSomeArray(dataset["dct:source"])?.filter(Boolean),
    "dct:hasVersion": asSomeArray(dataset["dct:hasVersion"])?.filter(Boolean),
    "dct:isVersionOf": asSomeArray(dataset["dct:isVersionOf"])?.filter(Boolean),
    "prov:qualifiedAttribution": asSomeArray(
      dataset["prov:qualifiedAttribution"]
    )?.filter(Boolean),

    "dcat:keyword": asSomeArray(dataset["dcat:keyword"])?.filter(Boolean),
    "dct:spatial": asSomeArray(dataset["dct:spatial"])
      ?.filter((item) => {
        if (!item) return false;
        return !item["@id"].startsWith(
          "http://dcat-ap.de/def/politicalGeocoding/"
        );
      })
      .filter(Boolean),
    "dct:references": asSomeArray(dataset["dct:references"])?.filter(Boolean),

    "foaf:page": asSomeArray(dataset["foaf:page"])
      ?.filter(Boolean)
      .map((page) => {
        if (!page) return undefined;

        const maybeFormat = getFirstItem(page["dct:format"]);

        return {
          "dct:title": getFirstItem(page["dct:title"]),
          "dct:description": getFirstItem(page["dct:description"]),
          "@value": maybeFormat
            ? extractLocalizedString(maybeFormat["prefLabel"])
            : "",
          uri: maybeFormat ? maybeFormat["@id"] : "",
          "foaf:homepage": getFirstItem(page["foaf:homepage"]) || "",
        };
      }),

    "dcatap:availability": dataset["dcatap:availability"]?.map(
      (availability) => ({
        // Redundant label and title for better compatibility
        label: getFirstItem(availability)?.["prefLabel"] || "",
        title: getFirstItem(availability)?.["prefLabel"] || "",
        uri: getFirstItem(availability)?.["@id"] || "",
        "@value":
          extractLocalizedString(getFirstItem(availability)?.["prefLabel"]) ||
          "",
      })
    ),
  };

  return purgeNullishAndEmptyProperties(additionals);
}

/**
 * Transform a DCAT-AP dataset to DPI format
 */
export function toDpi(dataset: DcatDataset): Record<string, any> {
  return {
    Landing: createLandingSection(dataset),
    Discoverability: createDiscoverabilitySection(dataset),
    BasicInfos: createBasicInfosSection(dataset),
    Covering: createCoveringSection(dataset),
    DistributionSimple: createDistributionSimpleSection(dataset),
    ReviewAndPublish: createReviewAndPublishSection(dataset),
    Additionals: createAdditionalsSection(dataset),
  };
}

export function toAccessService(accessServices: any[]) {
  if (
    !accessServices ||
    !Array.isArray(accessServices) ||
    accessServices.length === 0
  )
    return [];
  return accessServices.map((accessService: any, idx: number) => ({
    id: idx + 1,
    "dcat:downloadURL":
      getFirstItem(accessService["dcat:endpointURL"])?.["@id"] || "",
    "dcat:endpointURL":
      getFirstItem(accessService["dcat:endpointURL"])?.["@id"] || "",
    "dct:description":
      extractLocalizedString(accessService["dct:description"]) || "",
    "dct:title": extractLocalizedString(accessService["dct:title"]) || "",
  }));
}

export function toDocument(documents: any[]) {
  if (!documents || !Array.isArray(documents) || documents.length === 0)
    return [];
  return documents.map((document: any, idx: number) => ({
    id: idx + 1,
    "dcat:downloadURL": getFirstItem(document["dcat:accessURL"])?.["@id"] || "",
    "dcat:accessURL": getFirstItem(document["dcat:accessURL"])?.["@id"] || "",
    "dct:description":
      extractLocalizedString(document["dct:description"]) || "",
    "dct:title": extractLocalizedString(document["dct:title"]) || "",
    "dct:format": document["dct:format"]
      ? toUriLabel(getFirstItem(document["dct:format"]))?.label || ""
      : "",
    formatUri: document["dct:format"]
      ? toUriLabel(getFirstItem(document["dct:format"]))?.uri || ""
      : "",
  }));
}
