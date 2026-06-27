<script setup>
import { getNode } from "@formkit/core";
import { PhWarning } from "@phosphor-icons/vue";
import {
  computed,
  getCurrentInstance,
  onMounted,
  onUnmounted,
  ref,
  toRef,
  watch,
} from "vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import { useEditModeInfo } from "../composables/useDpiEditMode";
import { useFormValues } from "../composables/useDpiFormValues";
import config from "../config/dcatapdeHappyFlow/page-content-config";
import {
  getChecksumAlgorithms,
  getFileTypes,
  getLanguages,
  getFormatTypes,
  getPlannedAvailability,
  getLicenses,
} from "../HappyFlowComponents/services/dpiV3_apis";
import { eventBus } from "../HappyFlowComponents/services/eventBus";
import ButtonV3 from "../HappyFlowComponents/ui/ButtonV3.vue";
import Dropdown from "../HappyFlowComponents/ui/Dropdown.vue";
import InputField from "../HappyFlowComponents/ui/InputField.vue";
import ModalSimpleV3 from "../HappyFlowComponents/ui/ModalSimpleV3.vue";
import ModalV3 from "../HappyFlowComponents/ui/ModalV3.vue";
import AccessRightsV3 from "../HappyFlowComponents/ui/OptionalInformation/AccessRightsV3.vue";
import AccessServiceV3 from "../HappyFlowComponents/ui/OptionalInformation/AccessServiceV3.vue";
import AvailabilityV3 from "../HappyFlowComponents/ui/OptionalInformation/AvailabilityV3.vue";
import ByteSizeV3 from "../HappyFlowComponents/ui/OptionalInformation/ByteSizeV3.vue";
import ChangeLicenseV3 from "../HappyFlowComponents/ui/OptionalInformation/ChangeLicenseV3.vue";
import ChecksumV3 from "../HappyFlowComponents/ui/OptionalInformation/ChecksumV3.vue";
import ConformsToV3 from "../HappyFlowComponents/ui/OptionalInformation/ConformsToV3.vue";
import DescriptionV3 from "../HappyFlowComponents/ui/OptionalInformation/DescriptionV3.vue";
import DocumentationsV3 from "../HappyFlowComponents/ui/OptionalInformation/DocumentationsV3.vue";
import FileFormatV3 from "../HappyFlowComponents/ui/OptionalInformation/FileFormatV3.vue";
import IssuedDateV3 from "../HappyFlowComponents/ui/OptionalInformation/IssuedDateV3.vue";
import LanguageV3 from "../HappyFlowComponents/ui/OptionalInformation/LanguageV3.vue";
import LicenseAttributionByText from "../HappyFlowComponents/ui/OptionalInformation/LicenseAttributionByText.vue";
import ModifiedDateV3 from "../HappyFlowComponents/ui/OptionalInformation/ModifiedDateV3.vue";

import PolicyV3 from "../HappyFlowComponents/ui/OptionalInformation/PolicyV3.vue";
import StatusV3 from "../HappyFlowComponents/ui/OptionalInformation/StatusV3.vue";
import TextButtonSmall from "../HappyFlowComponents/ui/TextButtonSmall.vue";
import { tr } from "zod/v4/locales";

const props = defineProps({
  context: Object,
  inRap: Boolean,
});

const { isEditMode } = useEditModeInfo();

const { t } = useI18n();

const urlValidationTimeout = ref(null);

const defaultDistribution = {
  isValid: "unset",
  id: 1,
  "dcat:accessURL": "",
  "dct:format": {},
  "dct:title": "",
  documentations: [],
  conformsToItems: [],
  policyItems: [],
  "dcat:downloadURL": [{ "@id": "" }],
  accessServices: [],
  "dct:modified": "",
  "dct:issued": "",
  "dct:description": undefined,
  // "dcatde:licenseAttributionByText": undefined,
  // "dct:accessRights": {},
  "dct:rights": {},
  "dcatap:availability": {},
  "dct:language": [],
  "dcat:byteSize": undefined,
  "dcat:mediaType": {},
  "dcat:compressFormat": {},
  "dcat:packageFormat": {},
  "adms:status": {},
  checksum: {
    title: "",
    "spdx:checksum": "",
    uri: "",
  },
  "dct:license": {
    title: "",
    "dcterms:license": "",
  },
};

const { formValues } = useFormValues();
const shouldLoadExistingDistributions = computed(
  () => isEditMode.value || props.inRap
);
const distributions = shouldLoadExistingDistributions.value
  ? toRef(formValues.value["DistributionSimple"]?.["dcat:distribution"])
  : ref([defaultDistribution]);

// Entfernen wenn im Loader berücksichtigt
if (!distributions.value[0]["dcat:downloadURL"]) {
  console.log(distributions["dcat:downloadURL"], distributions);
  distributions.value[0]["dcat:downloadURL"] = [{ "@id": "" }];
}

const modalConf = ref({});
const modalSimpleConf = ref({});
const sections = ref([]);

const activeV3Modal = ref(false);
const activeSimpleModal = ref(false);
const distributionId = ref(0);

const allFileTypes = ref([]);
const allFormatTypes = ref([]);
const allChecksumURIs = ref([]);
const allAvailabilityURIs = ref([]);
const allLanguageURIs = ref([]);

const filteredDataDistribution = ref([...allFileTypes.value]);
const formatTypes = ref([...allFormatTypes.value]);

const store = useStore();
const specification = ref(null);
const navSteps = ref(null);
const minimumDistributionError = ref(false);

const showChecksum = ref(false);

const errorFound = ref(false);
const firstTimeVisit = ref(true);

// ShowProperties loop
for (let index = 0; index < distributions.value.length; index++) {
  if (
    formValues.value.DistributionSimple["dcat:distribution"][index]?.checksum
      .uri !== "" ||
    formValues.value.DistributionSimple["dcat:distribution"][index]?.checksum
      .title !== ""
  ) {
    showChecksum.value = false;
    break;
  }
}

function loadOptionalInfos() {
  sections.value = [
    {
      title: t(
        "message.dataupload.datasets.dcat:distribution.recommended.title"
      ),
      items: config.distributions.Recommended.map(
        (key) =>
          t(
            `message.dataupload.datasets.dcat:distribution.recommended.${key}`
          ) || key
      ),
      keys: config.distributions.Recommended,
    },
    {
      title: t("message.dataupload.datasets.dcat:distribution.advanced.title"),
      items: config.distributions.Advanced.map(
        (key) =>
          t(`message.dataupload.datasets.dcat:distribution.advanced.${key}`) ||
          key
      ),
      keys: config.distributions.Advanced,
    },
  ];
}

function handleNextClick(data) {
  if (!props.context?.node?.name?.includes("distribution")) return;
  if (data.includes("dcat:distribution") || data.includes("dct:license")) {
    if (!firstTimeVisit.value) {
      validateAllDistributions();
    }
    firstTimeVisit.value = false;
  }
}

function validateAllDistributions(id) {
  let allValid = true;

  distributions.value = distributions.value.map((distribution) => {
    const isDownloadURLValid =
      distribution["dcat:accessURL"] != null &&
      distribution["dcat:accessURL"].trim() !== "" &&
      isValidURL(distribution["dcat:accessURL"]);

    const isFormatValid =
      !!distribution["dct:format"]?.label &&
      distribution["dct:format"].label.trim() !== "" &&
      allFormatTypes.value.some(
        (item) =>
          item["@value"].toUpperCase() ===
          distribution["dct:format"].label.toUpperCase()
      );

    const areDownloadURLsValid = distribution["dcat:downloadURL"].every(
      (urlItem) => {
        const val = urlItem["@id"]?.trim();
        return val === "" || isValidURL(val);
      }
    );
    const isValid = isDownloadURLValid && isFormatValid && areDownloadURLsValid;
    if (!isValid) {
      allValid = false;
    }

    // only when new distribution added, id has value, otherwise id undefined
    if (id === distribution.id && !props.inRap) {
      return {
        ...distribution,
        isValid,
        showErrorDownloadURL: false,
        showErrorFormat: false,
      };
    } else {
      return {
        ...distribution,
        isValid,
        showErrorDownloadURL: !isDownloadURLValid,
        showErrorFormat: !isFormatValid,
      };
    }
  });

  if (props.inRap == true) {
    updateValidationStatus();
  }
  console.log(allValid);

  if (allValid) {
    errorFound.value = false;
  }

  // set first distribution because this is checked by inputpage.vue
  if (!allValid && distributions.value.length > 0) {
    console.log("invalid");
    distributions.value[0].isValid = false;
  }

  if (allValid && distributions.value.length > 0) {
    console.log("valid");
    distributions.value[0].isValid = true;
  }

  if (id == undefined) errorFound.value = !allValid;
  // formValues.value.DistributionSimple['dcat:distributions'] = distributions.value
  formValues.value.DistributionSimple["dcat:distribution"] =
    distributions.value;
}

function validateDistribution(distribution, field) {
  let isFieldValid = true;

  if (field === "dcat:accessURL") {
    isFieldValid =
      distribution[field]?.trim() !== "" &&
      (distribution[field] ? isValidURL(distribution[field]) : false);

    if (!isFieldValid) {
      distribution.showErrorDownloadURL = true;
      if (distribution[field]?.trim() === "") {
        distribution.urlErrorMessage = "Bitte geben Sie eine gültige URL ein.";
      } else {
        distribution.urlErrorMessage = "Bitte geben Sie eine gültige URL ein.";
      }
    } else {
      distribution.showErrorDownloadURL = false;
      distribution.urlErrorMessage = "";
    }
  } else if (field === "dct:format") {
    const formatValue = distribution[field].label?.trim();

    // Format is valid if it's not empty AND exists in the dropdown options
    isFieldValid =
      formatValue !== "" &&
      allFormatTypes.value.some(
        (item) => item["@value"].toLowerCase() === formatValue.toLowerCase()
      );

    if (!isFieldValid) {
      distribution.showErrorFormat = true;
      if (formatValue === "") {
        distribution.formatErrorMessage = "Format ist erforderlich.";
      } else {
        distribution.formatErrorMessage =
          "Bitte wählen Sie ein gültiges Format aus der Liste";
      }
    } else {
      distribution.showErrorFormat = false;
      distribution.formatErrorMessage = "";
    }
  }

  // Update overall validation status
  updateValidationStatus(true);
}

function updateValidationStatus(silent = false) {
  let allValid = true;

  distributions.value = distributions.value.map((distribution) => {
    const isDownloadURLValid =
      distribution["dcat:accessURL"]?.trim() !== "" &&
      isValidURL(distribution["dcat:accessURL"]);

    const isFormatValid =
      distribution["dct:format"]?.label?.trim() !== "" &&
      allFormatTypes.value.some(
        (item) =>
          item["@value"].toUpperCase() ===
          distribution["dct:format"].label?.toUpperCase()
      );

    const areDownloadURLsValid = (distribution["dcat:downloadURL"] || []).every(
      (urlItem) => {
        const val = urlItem["@id"]?.trim();
        return val === "" || isValidURL(val);
      }
    );

    const isValid = isDownloadURLValid && isFormatValid && areDownloadURLsValid;

    if (!isValid) allValid = false;

    return { ...distribution, isValid };
  });

  // Sync first distribution status for the parent loader
  if (distributions.value.length > 0) {
    distributions.value[0].isValid = allValid;
  }

  if (props.inRap === true || !silent || errorFound.value === true) {
    errorFound.value = !allValid;
  }

  formValues.value.DistributionSimple["dcat:distribution"] =
    distributions.value;
}

function isValidURL(url) {
  const pattern = /^(https?|http):\/\/[^\s/$.?#].[^\s]*$/i;

  return pattern.test(url);
}

onUnmounted(() => {
  eventBus.off("nextClicked", handleNextClick);
  if (urlValidationTimeout.value) {
    clearTimeout(urlValidationTimeout.value);
  }
});

let licenseOptions = ref([]);

onMounted(async () => {
  eventBus.on("nextClicked", handleNextClick);

  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;
  if (instance) {
    const env = instance.appContext.app.config.globalProperties.$env;
    specification.value = env.content.dataProviderInterface.specification;
  } else {
    console.log("Instance is null!");
  }
  // console.log(props.context.value);
  // console.log(distributions);

  /** ** load file types for format input field: */
  try {
    const licenseResponse = await getLicenses(env.api.baseUrl);
    licenseOptions.value = licenseResponse.map((item) => ({
      uri: item.uri,
      "@value": item.value,
      label: item.label,
    }));

    const response = await getFileTypes(env.api.baseUrl);
    allFileTypes.value = response.results.map((item) => ({
      "@value": item.pref_label["en"],
      uri: item.resource,
      selected: false,
    }));
  } catch (err) {
    error.value = err;
  }
  try {
    const response = await getFormatTypes(env.api.baseUrl);

    allFormatTypes.value = response.results
      .map((item) => ({
        "@value": item.pref_label["en"],
        uri: item.resource,
        selected: false,
      }))
      .sort((a, b) => a["@value"].localeCompare(b["@value"]));
    formatTypes.value = [...allFormatTypes.value];
  } catch (err) {
    error.value = err;
  }

  // need to load all the Vocabs - can tis be improved?
  try {
    const response = await getChecksumAlgorithms(env.api.baseUrl);
    allChecksumURIs.value = response;

    const responseAV = await getPlannedAvailability(env.api.baseUrl);
    allAvailabilityURIs.value = responseAV;

    const responseLang = await getLanguages(env.api.baseUrl);
    allLanguageURIs.value = responseLang;
  } catch (error) {
    console.log(error);
  }

  loadOptionalInfos();
});

function handleFormatValue(value, id) {
  // console.log(value);
  const distribution = distributions.value.find((item) => item.id === id);
  if (!distribution) return;
  let uri =
    allFormatTypes.value.find((item) => item["@value"] === value).uri || "";
  // When user selects from dropdown, always accept the value
  distribution["dct:format"] = { label: value, uri };
  distribution.showErrorFormat = false;
  distribution.formatErrorMessage = "";

  validateDistribution(distribution, "dct:format");
  formValues.value.DistributionSimple["dcat:distribution"] =
    distributions.value;
}

function handleModifiedDate(value, distributionId) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  if (distribution) {
    distribution["dct:modified"] = value;
    modalConf.value.dctModified = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleIssuedDate(value, distributionId) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  if (distribution) {
    distribution["dct:issued"] = value;
    modalConf.value.dctIssued = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleDescription(value, distributionId) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  if (distribution) {
    distribution["dct:description"] = value;
    modalConf.value.dctDescription = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

// function handleLicenseAttrByText(value, distributionId) {
//   const distribution = distributions.value.find((d) => d.id === distributionId);
//   if (distribution) {
//     distribution["dcatde:licenseAttributionByText"] = value;
//     modalConf.value.nameTextByClauses = value;
//   } else {
//     console.warn(`No matching distribution found for ID ${distributionId}.`);
//   }
// }

function handleAccessRights(value, distributionId, uri) {
  console.log(value, distributionId, uri);

  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (distribution) {
    distribution["dct:accessRights"] = {
      label: { de: value.label },
      uri: value.uri,
    };
    modalConf.value.accessRightsText = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleAddAvailability(value, distributionId, uri) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (!uri) {
    console.log(allAvailabilityURIs.value);

    uri =
      allAvailabilityURIs.value.find((item) => item.label === value).uri || "";
  }
  if (distribution) {
    distribution["dcatap:availability"] = { label: { de: value }, uri };
    modalConf.value.availabilityText = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleStatus(value, distributionId) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (distribution) {
    distribution["adms:status"] = value;
    modalConf.value.statusText = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleChecksum(titleValue, checksumValue, distributionId, uri) {
  showChecksum.value = true;
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (!uri) {
    uri =
      allChecksumURIs.value.find((item) => item.value === checksumValue)
        .resource || "";
  }

  if (distribution) {
    distribution.checksum.title = titleValue;
    distribution.checksum["spdx:checksum"] = checksumValue;
    distribution.checksum.uri = uri || "no URI";
    modalConf.value.checksumText = titleValue;
    modalConf.value.checksumDropdownText = checksumValue;
    modalConf.value.checksumURI = uri;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleChangeLicense(
  titleValue,
  changeLicenseValue,
  distributionId,
  uri
) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  let license = licenseOptions.value.find(
    (obj) => obj["@value"] === changeLicenseValue
  );

  if (distribution) {
    distribution["dct:license"].title = titleValue;
    distribution["dcatde:licenseAttributionByText"] = titleValue;
    distribution["dct:license"]["dcterms:license"] = changeLicenseValue;
    distribution["dct:license"].uri = license.uri;

    formValues.value.DistributionSimple["dct:license"] = [
      {
        title: titleValue,
        "dcterms:license": changeLicenseValue,
        uri: license.uri,
      },
    ];

    modalConf.value.changeLicenseText = titleValue;
    modalConf.value.changeLicenseDropdownText = changeLicenseValue;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleAddLanguage(value, distributionId, uri) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  // console.log(value, uri, distributionId);
  if (!uri) {
    uri = allLanguageURIs.value.find((item) => item.label === value).uri || "";
  }

  if (distribution) {
    distribution["dct:language"] = [{ label: value, uri }];
    modalConf.value.languageText = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleByteSize(value, distributionId) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (distribution) {
    distribution["dcat:byteSize"] = value;
    modalConf.value.byteSizeText = value;
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function handleFileFormat(value, format, distributionId) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  let uri =
    allFileTypes.value.find((item) => item["@value"] === value).uri || "";
  // console.log(uri);
  if (distribution) {
    distribution[format] = { label: value, uri };
    switch (format) {
      case "dcat:mediaType":
        modalConf.value.mediaTypeText = value;
        break;
      case "dcat:compressFormat":
        modalConf.value.compressFormatText = value;
        break;
      case "dcat:packageFormat":
        modalConf.value.packageFormatText = value;
        break;

      default:
        break;
    }
  } else {
    console.warn(`No matching distribution found for ID ${distributionId}.`);
  }
}

function removeConformsToStandardBlock(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Konform zu Standard",
    "conformsToItems",
    true,
    true
  );
}

function removeAvailability(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Verfügbarkeit",
    "dcatap:availability",
    false,
    false
  );
}

function removeStatus(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Status",
    "adms:status",
    false,
    false
  );
}

function removeByteSize(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Größe in Bytes",
    "dcat:byteSize",
    true,
    false
  );
}

function removeLanguage(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Sprache",
    "dct:language",
    true,
    false
  );
}

function deleteFileFormat(formatType, distributionId) {
  removeOptionalFieldModal(distributionId, formatType, formatType, true, false);
}

function removePolicyItemsBlock(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Regelwerk",
    "policyItems",
    true,
    true
  );
}
function removeChecksumBlock(distributionId) {
  removeOptionalFieldModal(distributionId, "Prüfsumme", "checksum", true, true);
}
function removeDURLItemsBlock(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Download URL",
    "dcat:downloadURL",
    true,
    true
  );
}

function removeDocumentationsBlock(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Dokumentation",
    "documentations",
    true,
    true
  );
}

function removeAccessServicesBlock(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Ausliefernder Datenservice",
    "accessServices",
    true,
    true
  );
}

function handleDownloadURLInput(inputValue, disID, URLindex) {
  const activeDist = distributions.value.find((o) => o.id === disID);
  const urlItem = activeDist["dcat:downloadURL"][URLindex];

  // 1. Update the value immediately for the UI
  urlItem["@id"] = inputValue;

  // 2. Clear existing global timeout
  if (urlValidationTimeout.value) {
    clearTimeout(urlValidationTimeout.value);
  }

  // 3. Debounce the validation
  urlValidationTimeout.value = setTimeout(() => {
    const trimmedValue = inputValue.trim();

    if (trimmedValue === "") {
      // It's optional: empty is valid
      urlItem.showError = false;
      urlItem.errorMessage = "";
    } else if (!isValidURL(trimmedValue)) {
      urlItem.showError = true;
      urlItem.errorMessage = "Bitte geben Sie eine gültige URL ein.";
    } else {
      urlItem.showError = false;
      urlItem.errorMessage = "";
    }

    // Trigger global validation check (now including our fixes)
    updateValidationStatus(true);
  }, 800); // 800ms threshold
}

function removeModifiedDate(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Aktualisierungsdatum",
    "dct:modified",
    false
  );
}

function removeIssuedDate(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Veröffentlichungsdatum",
    "dct:issued",
    false
  );
}

function removeDescriptionField(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Beschreibung",
    "dct:description",
    true
  );
}

// function removeLicenseAttributionByText(distributionId) {
//   removeOptionalFieldModal(
//     distributionId,
//     "Namensnennungstext für By-Clauses",
//     "dcatde:licenseAttributionByText",
//     true
//   );
// }

function removeAccessRights(distributionId) {
  removeOptionalFieldModal(
    distributionId,
    "Grad der Zugänglichkeit",
    "dct:accessRights",
    false
  );
}

const navStepsComputed = computed(() => {
  if (specification.value) {
    return store.getters["dpiStore/getNavSteps"](specification.value);
  }
  return {};
});

watch(navStepsComputed, (newVal) => {});

// Optional, deswegen valid
if (!isEditMode.value) props.context.node.input([{ isValid: "unset" }]);
// const distributions = ref([
//   { id: 1, "dcat:accessURL": "", "dct:format": "", "dct:title": "" },
// ]);

function addDistribution() {
  const id = Date.now();

  distributions.value.push({
    isValid: "unset",
    id: id,
    "dcat:accessURL": "",
    "dct:format": {},
    "dct:title": "",
    documentations: [],
    conformsToItems: [],
    "dcat:downloadURL": [{ "@id": "" }],
    policyItems: [],
    accessServices: [],
    "dct:modified": "",
    "dct:issued": "",
    "dct:description": undefined,
    // "dcatde:licenseAttributionByText": undefined,
    // "dct:accessRights": "",
    "dcatap:availability": {},
    "dct:language": [],
    "dcat:byteSize": undefined,
    "dcat:mediaType": {},
    "dcat:compressFormat": {},
    "dcat:packageFormat": {},
    "adms:status": {},
    checksum: {
      title: "",
      "spdx:checksum": "",
      uri: "",
    },
    "dct:license": {
      title: "",
      "dcterms:license": "",
    },
  });

  validateAllDistributions(id);
}

function addDocumentationsToDistribution(distributionId, newDocumentations) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (!distribution) {
    console.error(`Distribution with ID ${distributionId} not found`);
    return;
  }

  if (!Array.isArray(newDocumentations)) {
    console.error("Provided documentations is not an array", newDocumentations);
    return;
  }

  distribution.documentations = distribution.documentations.filter(
    (existingDoc) =>
      newDocumentations.some((newDoc) => newDoc.id === existingDoc.id)
  );

  newDocumentations.forEach((newDoc) => {
    const existingDocIndex = distribution.documentations.findIndex(
      (existingDoc) => existingDoc.id === newDoc.id
    );

    if (existingDocIndex !== -1) {
      // Update existing documentation
      distribution.documentations[existingDocIndex] = {
        ...distribution.documentations[existingDocIndex],
        ...newDoc,
      };
    } else {
      // Add new documentation
      const newId =
        distribution.documentations.length > 0
          ? Math.max(...distribution.documentations.map((doc) => doc.id)) + 1
          : 1;

      distribution.documentations.push({
        id: newDoc.id || newId,
        "dcat:accessURL": newDoc["dcat:accessURL"] || "",
        "dct:format": newDoc["dct:format"] || "",
        "dct:title": newDoc["dct:title"] || "",
        "dct:description": newDoc["dct:description"] || "",
        formatUri: newDoc["formatUri"] || "",
      });
    }
  });
}

function addAccessServicesToDistribution(distributionId, newAccessServices) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (!distribution) {
    console.error(`Distribution with ID ${distributionId} not found`);
    return;
  }

  if (!Array.isArray(newAccessServices)) {
    console.error(
      "Provided access services is not an array",
      newAccessServices
    );
    return;
  }

  distribution.accessServices = distribution.accessServices.filter(
    (existingService) =>
      newAccessServices.some(
        (newService) => newService.id === existingService.id
      )
  );

  newAccessServices.forEach((newService) => {
    const existingServiceIndex = distribution.accessServices.findIndex(
      (existingService) => existingService.id === newService.id
    );

    if (existingServiceIndex !== -1) {
      // Update existing service
      distribution.accessServices[existingServiceIndex] = {
        ...distribution.accessServices[existingServiceIndex],
        ...newService,
      };
    } else {
      // Add new service
      const newId =
        distribution.accessServices.length > 0
          ? Math.max(...distribution.accessServices.map((doc) => doc.id)) + 1
          : 1;

      distribution.accessServices.push({
        id: newService.id || newId,
        "dcat:downloadURL": newService["dcat:downloadURL"] || "",
        "dct:title": newService["dct:title"] || "",
        "dct:description": newService["dct:description"] || "",
      });
    }
  });
}

function addConformsToItemsToDistribution(distributionId, newStandards) {
  const distribution = distributions.value.find((d) => d.id === distributionId);
  if (!distribution) {
    console.error(`Distribution with ID ${distributionId} not found`);
    return;
  }

  if (!Array.isArray(newStandards)) {
    console.error("Provided items is not an array", newStandards);
    return;
  }

  distribution.conformsToItems =
    distribution?.conformsToItems?.filter((existingDoc) =>
      newStandards.some((newDoc) => newDoc.id === existingDoc.id)
    ) || [];

  newStandards.forEach((newStandard) => {
    const existingIndex = distribution.conformsToItems.findIndex(
      (existingDoc) => existingDoc.id === newStandard.id
    );

    if (existingIndex !== -1) {
      // Update existing items
      distribution.conformsToItems[existingIndex] = {
        ...distribution.conformsToItems[existingIndex],
        ...newStandard,
      };
    } else {
      // Add new
      const newId =
        distribution.conformsToItems.length > 0
          ? Math.max(...distribution.conformsToItems.map((doc) => doc.id)) + 1
          : 1;

      distribution.conformsToItems.push({
        id: newStandard.id || newId,
        "dcat:downloadURL": newStandard["dcat:downloadURL"] || "",
        "dct:title": newStandard["dct:title"] || "",
      });
    }
  });
}

function addPolicyItemsToDistribution(distributionId, newPolicyItems) {
  // const distribution = distributions.value.find((d) => d.id === distributionId);
  // if (!distribution) {
  //   console.error(`Distribution with ID ${distributionId} not found`);
  //   return;
  // }
  // if (!Array.isArray(newPolicyItems)) {
  //   console.error("Provided items is not an array", newPolicyItems);
  //   return;
  // }
  // distribution.policyItems =
  //   distribution?.policyItems?.filter((existingDoc) =>
  //     newPolicyItems.some((newDoc) => newDoc.id === existingDoc.id)
  //   ) || [];
  // newPolicyItems.forEach((newPolicyItem) => {
  //   const existingIndex = distribution?.policyItems?.findIndex(
  //     (existingDoc) => existingDoc.id === newPolicyItem.id
  //   );
  //   if (existingIndex !== -1) {
  //     // Update existing items
  //     distribution.policyItems[existingIndex] = {
  //       ...distribution.policyItems[existingIndex],
  //       ...newPolicyItem,
  //     };
  //   } else {
  //     // Add new
  //     const newId =
  //       distribution?.policyItems?.length > 0
  //         ? Math.max(...distribution.policyItems.map((doc) => doc.id)) + 1
  //         : 1;
  //     distribution.policyItems.push({
  //       id: newPolicyItem.id || newId,
  //       "dcat:accessURL": newPolicyItem["dcat:accessURL"] || "",
  //     });
  //   }
  // });
}

function handleButtonAction(action) {
  if (action === "deleteDataset" && distributionId.value !== null) {
    if (distributions.value.length === 1) {
      minimumDistributionError.value = true;
      return;
    }

    distributions.value = distributions.value.filter(
      (item) => item.id !== distributionId.value
    );

    minimumDistributionError.value = false;
    distributionId.value = null;
    validateAllDistributions();
  }
}

// used for Deleting Opt field modal
function handleModalSimpleButtonAction(action) {


  const infoObj = JSON.parse(action);
  let distribution = distributions.value.find(
    (d) => d.id === infoObj.distributionId
  );

  if (infoObj.isArray) {
    console.log(infoObj);
    distribution[infoObj.optType] = [];
    if (infoObj.optType === "checksum") {
      distribution[infoObj.optType] = {
        title: "",
        "spdx:checksum": "",
        uri: "",
      };
    }
    return;
  }
  if (infoObj.useUndefined) {
    if (infoObj.distributionId === 0) {
      infoObj.distributionId = 1;
      distribution = distributions.value.find(
        (d) => d.id === infoObj.distributionId
      );
    }
    distribution[infoObj.optType] = undefined;
  } else distribution[infoObj.optType] = "";
}

function addAdditionalInfosModal(id) {
  distributionId.value = id;

  const distribution = distributions.value.find((dist) => dist.id === id);
  if (!distribution) return;

  modalConf.value = {
    button: "",
    header: "Optionale Informationen hinzufügen",
    text: "",
    action: "deleteDataset",
    showButtons: false,
    optionalInfoView: true,
    distributionId: id,
    documentations: distribution.documentations || [],
    accessServices: distribution.accessServices || [],
    dctModified: distribution["dct:modified"] || "",
    dctIssued: distribution["dct:issued"] || "",
    dctDescription: distribution["dct:description"] || "",
    // nameTextByClauses: distribution["dcatde:licenseAttributionByText"] || "",
    // accessRightsText: distribution["dct:accessRights"].uri || "",
    availabilityText: distribution["dcatap:availability"]?.label?.de || "",
    languageText: distribution?.["dct:language"]?.["@value"] || "",
    byteSizeText: distribution["dcat:byteSize"] || "",
    mediaTypeText: distribution["dcat:mediaType"]?.label || "",
    // compressFormatText: distribution["dcat:compressFormat"].label || "",
    packageFormatText: distribution["dcat:packageFormat"]?.label || "",
    statusText: distribution["adms:status"]?.label || "",
    checksumText: distribution.checksum.title || "",
    checksumURI: distribution.checksum.uri || "",
    checksumDropdownText: distribution?.checksum["spdx:checksum"] || "",
    changeLicenseText: distribution?.changeLicense?.title || "",
    changeLicenseDropdownText:
      distribution?.changeLicense?.["dcterms:license"] || "",
    conformsToItems: distribution?.conformsToItems || [],
    policyItems: distribution?.policyItems || [],
    downloadURL: distribution?.downloadURL || [],
  };
  activeV3Modal.value = true;
  // Prevent background scrolling
  document.body.style.overflow = "hidden";
}

function closeModal() {
  activeV3Modal.value = false;
  document.body.style.overflow = "auto";
}

function removeDistributionModal(id) {
  distributionId.value = id;

  const distribution = distributions.value.find((item) => item.id === id);
  const distributionTitle = distribution ? distribution["dct:title"] : "";
  const distributionFormat = distribution
    ? distribution["dct:format"].label
    : "";
  const distributionLink = distribution ? distribution["dcat:accessURL"] : "";

  modalConf.value = {
    button: "Löschen",
    header: t(
      "message.dataupload.datasets.dcat:distribution.delete-distribution.header"
    ),
    text: t(
      "message.dataupload.datasets.dcat:distribution.delete-distribution.text"
    ),
    action: "deleteDataset",
    title: distributionTitle,
    format: distributionFormat,
    distributionLink,
  };
  activeV3Modal.value = true;
}

function removeOptionalFieldModal(
  id,
  optionalFieldName,
  optionalFieldType,
  useUndefined,
  isArray
) {
  const actionObj = {
    distributionId: id,
    optType: optionalFieldType,
    useUndefined,
    isArray,
  };
  const actionStr = JSON.stringify(actionObj);

  modalSimpleConf.value = {
    button: "Löschen",
    header: `${optionalFieldName} löschen`,
    text: `Sind Sie sicher, dass Sie das optionale Feld ${optionalFieldName} löschen wollen?`,
    action: actionStr,
  };
  activeSimpleModal.value = true;
}

function handleInput(event, field, id) {
  const distribution = distributions.value.find((item) => item.id === id);

  if (!distribution) return;

  if (field === "dcat:accessURL") {
    const value = event?.target?.value;
    distribution[field] = value;

    // Clear existing timeout
    if (urlValidationTimeout.value) {
      clearTimeout(urlValidationTimeout.value);
    }

    // Debounce validation
    urlValidationTimeout.value = setTimeout(() => {
      validateDistribution(distribution, field);
    }, 800);
  } else if (field === "dct:format") {
    const inputValue =
      typeof event === "string" ? event.trim() : event?.target?.value?.trim();

    // Reset error state
    distribution.showErrorFormat = false;
    distribution.formatErrorMessage = "";

    // If input is empty, that's neutral (not valid, not invalid during typing)
    if (inputValue === "") {
      distribution[field] = { label: "", uri: "" };
      if (props.inRap) {
        distribution.showErrorFormat = true;
        distribution.formatErrorMessage = "Format ist erforderlich";
      } else {
        distribution.showErrorFormat = false;
        distribution.formatErrorMessage = "";
      }
      formatTypes.value = [...allFormatTypes.value];
    } else {
      // Check if the exact value exists in allFormatTypes (case-insensitive)
      const exactMatch = allFormatTypes.value.find(
        (item) => item["@value"].toLowerCase() === inputValue.toLowerCase()
      );

      if (exactMatch) {
        distribution[field] = {
          label: exactMatch["@value"],
          uri: exactMatch.uri,
        };
        distribution.showErrorFormat = false;
        distribution.formatErrorMessage = "";
      } else if (inputValue === "") {
        distribution[field] = { label: "", uri: "" };
        distribution.showErrorFormat = false;
      } else {
        distribution[field] = { label: inputValue, uri: "" };
        distribution.showErrorFormat = true;
        distribution.formatErrorMessage =
          "Ungültiges Format. Bitte wählen Sie aus der Liste.";
      }

      // Filter dropdown options for autocomplete
      formatTypes.value = allFormatTypes.value.filter((item) =>
        item["@value"].toLowerCase().includes(inputValue.toLowerCase())
      );
    }

    // Force immediate validation
    validateDistribution(distribution, field);
  } else {
    distribution[field] = event?.target?.value;
    validateDistribution(distribution, field);
  }
}

function updateDocumentations(distributionId, updatedDocs) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  distribution.documentations = distribution.documentations.filter(
    (existingDoc) => updatedDocs.some((newDoc) => newDoc.id === existingDoc.id)
  );
}

function updateAccessServices(distributionId, updatedServices) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  distribution.accessServices = distribution.accessServices.filter(
    (existingService) =>
      updatedServices.some((newDoc) => newDoc.id === existingService.id)
  );
}

function updateConformsTo(distributionId, updatedConformsTo) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  distribution.conformsToItems = distribution.conformsToItems.filter(
    (existingDoc) =>
      updatedConformsTo.some((newDoc) => newDoc.id === existingDoc.id)
  );
}

function updatePolicyItems(distributionId, updatedPolicyItem) {
  const distribution = distributions.value.find((d) => d.id === distributionId);

  distribution.policyItems = distribution.policyItems.filter((existingDoc) =>
    updatedPolicyItem.some((newDoc) => newDoc.id === existingDoc.id)
  );
}
</script>

<template>
  <div class="dpiV3InnerComponentWrap">
    <h4 v-if="!inRap">
      {{ $t("message.dataupload.datasets.dcat:distribution.title") }}
    </h4>
    <div v-if="!inRap" class="copy-large-regular">
      {{ $t("message.dataupload.datasets.dcat:distribution.description") }}
    </div>
    <div
      v-for="(distribution, index) in distributions"
      :key="distribution.id"
      class="dpiV3AutoCompleteWrap"
    >
      <div class="dpiV3_LinkAndMetadata input-container">
        <div style="position: relative; width: 100%">
          <InputField
            :add-on-text="false"
            :datePicker="false"
            :infoIcon="true"
            tooltip_text="Datenlink(s): URL(s), unter denen Ihr Datensatz online gehostet
              wird oder über eine Schnittstelle abgerufen werden kann."
            :placeholder="
              $t(
                'message.dataupload.datasets.dcat:distribution.download-link.placeholder'
              )
            "
            :preIcon="false"
            input-field-size="large"
            :initialHintText="false"
            :label="
              $t(
                'message.dataupload.datasets.dcat:distribution.download-link.label'
              )
            "
            :showEndIcon="false"
            :showError="distribution.showErrorDownloadURL"
            :model-value="distribution['dcat:accessURL']"
            @input="handleInput($event, 'dcat:accessURL', distribution.id)"
          />
          <div
            v-if="
              distribution.showErrorDownloadURL && distribution.urlErrorMessage
            "
            class="validation-error"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              distribution.urlErrorMessage
            }}</span>
          </div>
        </div>
        <div style="position: relative; width: 100%">
          <Dropdown
            dropdown-width="large"
            type="inputField"
            :input-field-props="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: true,
              showEndIcon: false,
              label: $t(
                'message.dataupload.datasets.dcat:distribution.format.label'
              ),
              dropdown_dpiV3: true,
              placeholder: $t(
                'message.dataupload.datasets.dcat:distribution.format.placeholder'
              ),
              inputFieldSize: 'large',
              autocomplete: 'true',
              showError: distribution.showErrorFormat,
            }"
            :data="formatTypes"
            :autocomplete="true"
            @input="handleInput($event, 'dct:format', distribution.id)"
            @update:model-value="handleFormatValue($event, distribution.id)"
            :model-value="distribution?.['dct:format']?.label"
          />
          <div
            v-if="
              distribution.showErrorFormat && distribution.formatErrorMessage
            "
            class="validation-error"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              distribution.formatErrorMessage
            }}</span>
          </div>
        </div>
        <InputField
          v-model="distribution['dct:title']"
          :add-on-text="false"
          :date-picker="false"
          :info-icon="false"
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:distribution.distribution-title.placeholder'
            )
          "
          :pre-icon="false"
          input-field-size="large"
          :initial-hint-text="false"
          :label="
            $t(
              'message.dataupload.datasets.dcat:distribution.distribution-title.label'
            )
          "
          :show-end-icon="false"
          @input="handleInput($event, 'dct:title', distribution.id)"
        />
        <!------------------------------ Documentations --------------------------------->
        <template v-if="(distribution.documentations || []).length > 0">
          <div class="dpiV3_DocumentationsInDistr">
            <div class="dpiV3_TitleDelete">
              <div>Dokumentation (optional)</div>
              <div>
                <TextButtonSmall
                  button-text="löschen"
                  @click="removeDocumentationsBlock(distribution.id)"
                />
              </div>
            </div>
            <div class="dpiV3_documentationContents">
              <div class="dpiV3_Documentation">
                <DocumentationsV3
                  :documentations="distribution.documentations"
                  :file-types="allFormatTypes"
                  :distribution-id="distribution.id"
                  :as-card="true"
                  :show-delete-button="true"
                  @update="
                    (updatedDocs) =>
                      updateDocumentations(distribution.id, updatedDocs)
                  "
                />
              </div>
            </div>
          </div>
        </template>
        <!------------------------------ End Documentations --------------------------------->

        <!------------------------------ Access Services --------------------------------->
        <template v-if="(distribution.accessServices || []).length > 0">
          <div class="dpiV3_DocumentationsInDistr">
            <div class="dpiV3_TitleDelete">
              <div>Ausliefernder Datenservice (optional)</div>
              <div>
                <TextButtonSmall
                  button-text="löschen"
                  @click="removeAccessServicesBlock(distribution.id)"
                />
              </div>
            </div>
            <div class="dpiV3_documentationContents">
              <div class="dpiV3_Documentation">
                <AccessServiceV3
                  :access-services="distribution.accessServices"
                  :distribution-id="distribution.id"
                  :as-card="true"
                  :show-delete-button="true"
                  @update="
                    (updatedAccessServices) =>
                      updateAccessServices(
                        distribution.id,
                        updatedAccessServices
                      )
                  "
                />
              </div>
            </div>
          </div>
        </template>
        <!------------------------------ DownloadURL ---------------------------------->
        <div
          v-for="(item, dloadIndex) in distribution['dcat:downloadURL']"
          :key="dloadIndex"
          class="dpiV3_DocumentationsInDistr"
        >
          <div class="dpiV3_TitleDelete">
            <div class="dpiV3_label" style="margin: 0">
              DownloadURL (optional)
            </div>
            <div v-if="distribution['dcat:downloadURL'].length !== 1">
              <TextButtonSmall
                button-text="löschen"
                @click="distribution['dcat:downloadURL'].splice(dloadIndex, 1)"
              />
            </div>
          </div>

          <div style="position: relative; width: 100%; margin-bottom: 10px">
            <InputField
              :model-value="item['@id']"
              @input="
                handleDownloadURLInput(
                  $event.target.value,
                  distribution.id,
                  dloadIndex
                )
              "
              :showError="item.showError"
              :initialHintText="false"
              :addOnText="false"
              placeholder="Bitte URL eingeben..."
              inputFieldSize="large"
              :label="''"
            />

            <div v-if="item.showError" class="validation-error">
              <PhWarning :size="16" weight="fill" />
              <span class="copy-mini-regular">{{ item.errorMessage }}</span>
            </div>
          </div>

          <div
            v-if="dloadIndex === distribution['dcat:downloadURL'].length - 1"
            class="dpiV3_tempAddMore"
          >
            <ButtonV3
              buttonText="Weitere DownloadURL hinzufügen"
              size="small"
              iconStart="plus"
              variant="tertiary"
              @click="
                distribution['dcat:downloadURL'].push({
                  '@id': '',
                  showError: false,
                  errorMessage: '',
                })
              "
            />
          </div>
        </div>
        <!------------------------------ End DownloadURL ---------------------------------->

        <!------------------------------ Modified Date ---------------------------------->
        <div
          v-if="
            distribution['dct:modified'] &&
            distribution['dct:modified'].trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <ModifiedDateV3
            :show-delete-button="true"
            :distribution-id="distribution.id"
            :model-value="distribution['dct:modified']"
            @add-modified-date="handleModifiedDate"
            @delete-button-clicked="removeModifiedDate(distribution.id)"
          />
        </div>
        <!------------------------------ End Modified Date ---------------------------------->

        <!------------------------------ Issued Date ---------------------------------->
        <div
          v-if="
            distribution['dct:issued'] &&
            distribution['dct:issued'].trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <IssuedDateV3
            :show-delete-button="true"
            :distribution-id="distribution.id"
            :model-value="distribution['dct:issued']"
            @add-issued-date="handleIssuedDate"
            @delete-button-clicked="removeIssuedDate(distribution.id)"
          />
        </div>
        <!------------------------------ End Issued Date ---------------------------------->

        <!------------------------------ Description, dct:description ---------------------------------->
        <div
          v-show="
            distribution['dct:description'] !== undefined &&
            distribution['dct:description'] !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <DescriptionV3
            :distribution-id="distribution.id"
            :description-text="distribution['dct:description']"
            :show-delete-button="true"
            @add-description="handleDescription"
            @delete-description="removeDescriptionField(distribution.id)"
          />
        </div>

        <!------------------------------ End Description, dct:description ---------------------------------->

        <!------------------------------ LicenseAttributionByText , dcatde:licenseAttributionByText ---------------------------------->
        <!-- <div
          v-if="distribution['dcatde:licenseAttributionByText'] !== undefined"
          class="dpiV3_modified dpiV3_label"
        >
          <LicenseAttributionByText
            :show-delete-button="true"
            :distribution-id="distribution.id"
            :name-text-by-clauses="
              distribution['dcatde:licenseAttributionByText']
            "
            @add-license-attr-by-text="handleLicenseAttrByText"
            @delete-button-clicked="
              removeLicenseAttributionByText(distribution.id)
            "
          />
        </div> -->
        <!------------------------------ End LicenseAttributionByText , dcatde:licenseAttributionByText ---------------------------------->
        <div
          v-if="
            distribution['dct:accessRights']?.uri &&
            distribution['dct:accessRights']?.uri.trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <AccessRightsV3
            :show-delete-button="true"
            :distribution-id="distribution.id"
            :access-rights-prop="distribution['dct:accessRights']?.label['de']"
            @add-access-rights="handleAccessRights"
            @delete-button-clicked="removeAccessRights(distribution.id)"
          />
        </div>
        <!------------------- Availability,dcatap:availability ------------------->
        <div
          v-if="
            distribution['dcatap:availability']?.label != undefined &&
            distribution['dcatap:availability']?.label.de &&
            distribution['dcatap:availability']?.label.de.trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <AvailabilityV3
            :availability-text="distribution['dcatap:availability'].label.de"
            :distribution-id="distribution.id"
            :show-delete-button="true"
            @add-availability="handleAddAvailability"
            @delete-button-clicked="removeAvailability(distribution.id)"
          />
        </div>
        <!------------------- End Availability,dcatap:availability ------------------->

        <!-------------------  Status adms:status ------------------->
        <div
          v-if="
            distribution['adms:status']?.['@value'] &&
            distribution['adms:status']?.['@value'].trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <StatusV3
            :status-text="distribution['adms:status']['@value']"
            :distribution-id="distribution.id"
            :show-delete-button="true"
            @add-status="handleStatus"
            @delete-button-clicked="removeStatus(distribution.id)"
          />
        </div>
        <!-------------------  End Status adms:status ------------------->

        <!-------------------  Checksum adms:status ------------------->
        <div
          v-if="distribution.checksum['spdx:checksum'] != ''"
          class="dpiV3_modified dpiV3_label"
        >
          <div
            class="dpiV3_TitleDelete"
            style="margin-bottom: var(--Spacing-1, 4px)"
          >
            <div>Prüfsumme (optional)</div>
            <div>
              <TextButtonSmall
                button-text="löschen"
                @click="removeChecksumBlock(distribution.id)"
              />
            </div>
          </div>
          <ChecksumV3
            :checksum-text="distribution.checksum.title"
            :checksum-dropdown-text="distribution.checksum['spdx:checksum']"
            :distribution-id="distribution.id"
            :distIndex="index"
            :as-card="true"
            :checksum-u-r-i="distribution.checksum.uri"
            :show-delete-button="true"
            @add-checksum="handleChecksum"
          />
        </div>
        <div
          v-if="
            Object.keys(distribution['dct:license']).length > 0 &&
            distribution['dct:license']?.['dcterms:license'] != ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <ChangeLicenseV3
            :change-license-text="distribution['dct:license']?.title"
            :change-license-dropdown-text="
              distribution['dct:license']?.['dcterms:license']
            "
            :distribution-id="distribution.id"
            :as-card="true"
            :show-delete-button="true"
            @add-change-license="handleChangeLicense"
          />
        </div>

        <!------------------------------------ ConformsToStandard ----------------------------------------->
        <div
          v-if="
            distribution.conformsToItems &&
            distribution.conformsToItems.length > 0
          "
          class="dpiV3_DocumentationsInDistr"
        >
          <div class="dpiV3_TitleDelete">
            <div>Konform zu Standard (optional)</div>
            <div>
              <TextButtonSmall
                button-text="löschen"
                @click="removeConformsToStandardBlock(distribution.id)"
              />
            </div>
          </div>
          <ConformsToV3
            :conforms-to-items="distribution.conformsToItems"
            :distribution-id="distribution.id"
            :as-card="true"
            :show-delete-button="true"
            @update="
              (updatedConformsTo) =>
                updateConformsTo(distribution.id, updatedConformsTo)
            "
          />
        </div>

        <!------------------------------------ Policy ----------------------------------------->
        <div
          v-if="
            distribution?.policyItems?.[0]?.['dcat:downloadURL'] != '' &&
            distribution.policyItems.length > 0
          "
          class="dpiV3_DocumentationsInDistr"
        >
          <div class="dpiV3_TitleDelete">
            <div>Regelwerk (optional)</div>
            <div>
              <TextButtonSmall
                button-text="löschen"
                @click="removePolicyItemsBlock(distribution.id)"
              />
            </div>
          </div>
          <PolicyV3
            :policy-items="distribution.policyItems"
            :distribution-id="distribution.id"
            :as-card="true"
            @update="
              (updatededPolicyItems) =>
                updatePolicyItems(distribution.id, updatededPolicyItems)
            "
          />
        </div>
        <!--------------- Language dct:language ---------------->
        <div
          v-if="
            distribution['dct:language']?.[0]?.label ||
            (distribution['dct:language']?.[0]?.['@value'] &&
              distribution['dct:language']?.[0]?.['@value'].trim() !== '')
          "
          class="dpiV3_modified dpiV3_label"
        >
          <LanguageV3
            :distribution-id="distribution.id"
            :show-delete-button="false"
            @add-language="handleAddLanguage"
            @delete-button-clicked="removeLanguage(distribution.id)"
            :inOverview="true"
          />
        </div>

        <!------------------- Byte Size,dcat:byteSize ------------------->
        <div
          v-if="
            distribution['dcat:byteSize'] !== undefined &&
            distribution['dcat:byteSize'] !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <ByteSizeV3
            :byte-size-prop="distribution['dcat:byteSize']"
            :distribution-id="distribution.id"
            :show-delete-button="true"
            @add-byte-size="handleByteSize"
            @delete-button-clicked="removeByteSize(distribution.id)"
          />
        </div>
        <!------------------- End Byte Size,dcat:byteSize ------------------->

        <!------------------------- File Format ------------------------>
        <div
          v-if="
            distribution['dcat:mediaType'] &&
            distribution['dcat:mediaType']?.label &&
            distribution['dcat:mediaType']?.label.trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <FileFormatV3
            :distribution-id="distribution.id"
            :file-types="allFileTypes"
            file-format-type="dcat:mediaType"
            :file-format-text="distribution['dcat:mediaType'].label"
            :show-delete-button="true"
            @add-media-type="handleFileFormat"
            @delete-button-clicked="
              deleteFileFormat('dcat:mediaType', distributionId)
            "
          />
        </div>
        <div
          v-if="
            distribution['dcat:compressFormat'] &&
            distribution['dcat:compressFormat']?.label &&
            distribution['dcat:compressFormat']?.label.trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <FileFormatV3
            :distribution-id="distribution.id"
            :file-types="allFileTypes"
            file-format-type="dcat:compressFormat"
            :compress-format-text="distribution['dcat:compressFormat'].label"
            :show-delete-button="true"
            @add-compress-format="handleFileFormat"
            @delete-button-clicked="
              deleteFileFormat('dcat:compressFormat', distributionId)
            "
          />
        </div>
        <div
          v-if="
            distribution['dcat:packageFormat']?.label &&
            distribution['dcat:packageFormat']?.label.trim() !== ''
          "
          class="dpiV3_modified dpiV3_label"
        >
          <FileFormatV3
            :distribution-id="distribution.id"
            :file-types="allFileTypes"
            file-format-type="dcat:packageFormat"
            :package-format-text="distribution['dcat:packageFormat'].label"
            :show-delete-button="true"
            @add-package-format="handleFileFormat"
            @delete-button-clicked="
              deleteFileFormat('dcat:packageFormat', distributionId)
            "
          />
        </div>
        <!------------------------- End File Format ------------------------>

        <!------------------------------ Buttons ---------------------------------->
        <p
          v-if="minimumDistributionError && distributions.length === 1"
          class="copy-mini-regular dpiV3_text_error"
        >
          Mindestens ein Link muss vorhanden sein.
        </p>
      </div>
      <div class="dpiV3_CTA-Distribution">
        <ButtonV3
          :button-text="
            $t('message.dataupload.datasets.dcat:distribution.add-more-info')
          "
          size="small"
          icon-start="PlusSquare"
          variant="tertiary"
          @click="addAdditionalInfosModal(distribution.id)"
        />
        <ButtonV3
          v-if="distributions.length > 1"
          :button-text="
            $t('message.dataupload.datasets.dcat:distribution.delete')
          "
          size="small"
          icon-start="trash"
          variant="tertiary"
          @click="removeDistributionModal(distribution.id)"
        />
      </div>
    </div>
    <div class="dpiV3_tempAddMore">
      <ButtonV3
        :button-text="
          $t('message.dataupload.datasets.dcat:distribution.add-more-links')
        "
        size="large"
        icon-start="plus"
        variant="tertiary"
        @click="addDistribution"
      />
    </div>
    <!------------------------------ End Buttons ---------------------------------->

    <div
      v-if="errorFound"
      :class="
        inRap == true
          ? 'dpiV3_errormsgWrapper rap-bottom'
          : 'dpiV3_errormsgWrapper'
      "
    >
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular"
        >Bitte füllen Sie alle Pflichtfelder aus, bevor Sie fortfahren.</span
      >
    </div>
  </div>
  <ModalV3
    v-if="activeV3Modal"
    :distibution-id="distributionId"
    :file-types="allFileTypes"
    :format-types="allFormatTypes"
    :buttons="modalConf.button"
    :header-text="modalConf.header"
    :text="modalConf.text"
    :show-buttons="modalConf.showButtons"
    :optional-info-view="modalConf.optionalInfoView"
    :documentations="modalConf.documentations"
    :access-services="modalConf.accessServices"
    :action="modalConf.action"
    :title="modalConf.title"
    :format="modalConf.format"
    :distribution-link="modalConf.distributionLink"
    :sections="sections"
    :context="context"
    :distribution-id="modalConf.distributionId"
    :modified-date="modalConf.dctModified || ''"
    :issued-date="modalConf.dctIssued || ''"
    :description-text="modalConf.dctDescription || ''"
    :access-rights-text="modalConf.accessRightsText || ''"
    :availability-text="modalConf.availabilityText || ''"
    :language-text="modalConf.languageText || ''"
    :byte-size-text="modalConf.byteSizeText || ''"
    :media-type-text="modalConf.mediaTypeText || ''"
    :compress-format-text="modalConf.compressFormatText || ''"
    :package-format-text="modalConf.packageFormatText || ''"
    :status-text="modalConf.statusText || ''"
    :checksum-text="modalConf.checksumText || ''"
    :checksum-u-r-i="modalConf.checksumURI || ''"
    :checksum-dropdown-text="modalConf.checksumDropdownText || ''"
    :change-license-text="modalConf.changeLicenseText || ''"
    :change-license-dropdown-text="modalConf.changeLicenseDropdownText || ''"
    :conforms-to-items="modalConf.conformsToItems"
    :policy-items="modalConf.policyItems"
    :download-URL="modalConf.downloadURL"
    @close="closeModal"
    @action-handling="handleButtonAction($event)"
    @add-documentations="addDocumentationsToDistribution"
    @add-access-services="addAccessServicesToDistribution"
    @add-modified-date="handleModifiedDate"
    @add-issued-date="handleIssuedDate"
    @add-description="handleDescription"
    @add-access-rights="handleAccessRights"
    @add-availability="handleAddAvailability"
    @add-language="handleAddLanguage"
    @add-byte-size="handleByteSize"
    @add-media-type="handleFileFormat"
    @add-compress-format="handleFileFormat"
    @add-package-format="handleFileFormat"
    @add-status="handleStatus"
    @add-checksum="handleChecksum"
    @add-change-license="handleChangeLicense"
    @add-conforms-to-items="addConformsToItemsToDistribution"
    @add-policy-items="addPolicyItemsToDistribution"
  />
  <ModalSimpleV3
    v-if="activeSimpleModal"
    :buttons="modalSimpleConf.button"
    :header-text="modalSimpleConf.header"
    :text="modalSimpleConf.text"
    :action="modalSimpleConf.action"
    @close="activeSimpleModal = false"
    @action-handling="handleModalSimpleButtonAction($event)"
  />
</template>

<style scoped>
.validation-error {
  position: absolute;
  bottom: -20px;
  right: 0;
  color: var(--text-error, #a9242c);
  font-size: var(--copy-small-regular-font-size, 15px);
  display: flex;
  gap: 5px;
  /* Ensure it stays on top of other elements if necessary */
  z-index: 10;
  /* Prevent the error from wrapping and looking weird */
  white-space: nowrap;
}
.dpiV3_documentationContents {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_Documentation {
  display: flex;
  /* padding: var(--Spacing-4, 24px); */
  flex-direction: column;
  align-items: flex-end;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  border-radius: var(--Inside-Modal-Radious, 16px);
  background: var(--Colour-neutral-Neutral0, #fff);
}

.dpiV3_DocumentationsInDistr {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  align-self: stretch;
}

.dpiV3_TitleDelete {
  color: var(--neutral-80, #3d4952);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  align-self: stretch;
}

.dpiV3_text_error {
  color: var(--text-error, #a9242c);
  margin-bottom: 0px;
}

.dpiV3_LinkAndMetadata {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: flex-end;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_LinkAndMetadata.input-container {
  /* This container holds the InputField + the error div */
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--Spacing-3, 16px);
  /* Increase bottom margin of children to prevent overlapping the NEXT input */
  margin-bottom: 10px;
}

.accUrlError {
  position: absolute;
  top: 80px;
  color: var(--text-error, #a9242c);
  text-align: right;
}
.dpiV3_CTA-Distribution {
  padding-top: var(--Spacing-3, 16px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  align-self: stretch;
}
.dpiV3_Label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.dpiV3_label {
  color: var(--neutral-80, #3d4952);
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px);
  margin-bottom: var(--Spacing-1, 4px);
}
.dpiV3_tempAddMore {
  width: 100%;
  display: flex;
  justify-content: end;
}

.dpiV3_errormsgWrapper {
  display: flex;
  gap: 6px;
  width: auto;
  position: absolute;
  right: 50px;
  bottom: 104px;
  color: var(--text-error, #a9242c);
}

.dpiV3_errormsgWrapper span {
  color: var(--text-error, #a9242c);
  text-align: right;
}

.rap-bottom {
  bottom: -50px !important;
  right: 20px !important;
}
</style>
