<template>
  <div class="dpiV3InnerComponentWrap">
    <h4>
      {{ $t("message.dataupload.datasets.dcatde:politicalGeocodingURI.title") }}
    </h4>
    <div class="copy-large-regular">
      {{
        $t(
          "message.dataupload.datasets.dcatde:politicalGeocodingURI.description"
        )
      }}
    </div>
    <div class="dropdown-container">
      <Dropdown
        dropdownWidth="large"
        type="inputField"
        @input="filterVocabularies($event)"
        :inputFieldProps="{
          addOnText: false,
          initialHintText: false,
          datePicker: false,
          infoIcon: false,
          preIcon: true,
          showEndIcon: false,
          label: 'Geopolitische Abdeckung',
          dropdown_dpiV3: true,
          placeholder: 'Geben Sie die geopolitische Abdeckung ein..',
          inputFieldSize: 'large',
          autocomplete: 'true',
          error: isInputInvalid,
        }"
        @valueSent="handleValue"
        :data="URIList"
        multi="true"
        :autocomplete="true"
        buttonText="Dropdown"
        text="Dropdown"
        :loading="isLoading"
      />
      <!-- Empty data state message -->
      <div
        class="empty-state-message"
        v-if="searchPerformed && !isLoading && URIList.length === 0"
      >
        <PhMagnifyingGlass :size="16" />
        <span
          >Keine Ergebnisse gefunden. Bitte versuchen Sie einen anderen
          Suchbegriff.</span
        >
      </div>
      <!-- Validation error message -->
      <div class="validation-error" v-if="isInputInvalid && currentSearchTerm">
        Bitte wählen Sie einen gültigen Wert aus der Liste aus.
      </div>
    </div>
  </div>
</template>

<script setup>
import Dropdown from "../HappyFlowComponents/ui/Dropdown.vue";
import { filterGeocodingURIs } from "../HappyFlowComponents/services/dpiV3_apis";
import { ref, computed } from "vue";
import { getCurrentInstance } from "vue";
import { PhMagnifyingGlass } from "@phosphor-icons/vue";
import { useEditModeInfo } from "../composables";
import { useFormValues } from "../composables/useDpiFormValues";

const { isEditMode } = useEditModeInfo();
const { formValues } = useFormValues();
// Data source for the dropdown
let URIList = ref([]);
// Access environment configuration
let instance = getCurrentInstance().appContext.app.config.globalProperties.$env;
// Debounce timer for input
let debounceTimer;
// Loading state for the API request
const isLoading = ref(false);
// Track if a search has been attempted
const searchPerformed = ref(false);
// Track current search term
const currentSearchTerm = ref("");
// Track if user has selected a valid value
const hasSelectedValue = ref(false);

const props = defineProps({
  context: Object,
});

// Computed property to determine if input is invalid
const isInputInvalid = computed(() => {
  return (
    currentSearchTerm.value &&
    searchPerformed.value &&
    !isLoading.value &&
    URIList.value.length === 0 &&
    !hasSelectedValue.value
  );
});

// Initialize with valid state since it's optional
// if (!isEditMode.value) props.context.node.input([{ 'isValid': true }])
if (!isEditMode.value)
  formValues.value.Covering["dcatde:politicalGeocodingURI"] = [
    { isValid: true },
  ];
/**
 * Filters vocabulary items based on user input
 * Shows loading state while fetching data from API
 * @param {string} e - The user input value
 */
const filterVocabularies = async (e) => {
  clearTimeout(debounceTimer); // Clear previous timer
  currentSearchTerm.value = e;
  hasSelectedValue.value = false; // Reset selection state when typing

  // Set a new debounce timer
  debounceTimer = setTimeout(async () => {
    if (e !== "") {
      try {
        // Show loading spinner
        isLoading.value = true;
        searchPerformed.value = true;

        // Call API to get filtered results
        URIList.value = await filterGeocodingURIs(e, instance.api.baseUrl);

        // Validate after results are loaded
        validateInput();
      } catch (error) {
        console.log("Error fetching geocoding data:", error);
        // Clear data on error
        URIList.value = [];
        validateInput();
      } finally {
        // Hide spinner when done (success or error)
        isLoading.value = false;
      }
    } else {
      // Clear the results if input is empty
      URIList.value = [];
      searchPerformed.value = false;
      hasSelectedValue.value = false;
      // Input is valid when empty
      formValues.value.Covering["dcatde:politicalGeocodingURI"] = [
        { isValid: true },
      ];
      // props.context.node.input([{ 'isValid': true }]);
    }
  }, 500); // Wait 500ms after the last input before making API call
};

/**
 * Validates the current input and updates the validation state
 */
const validateInput = () => {
  const isValid = !isInputInvalid.value;
  formValues.value.Covering["dcatde:politicalGeocodingURI"] = [
    { isValid: true },
  ];
  // props.context.node.input([{ 'isValid': isValid }]);
};

/**
 * Handles the selected value from the dropdown
 * @param {Object} item - The selected item with its data
 * @param {string} header - The category header
 */
const handleValue = (item, header) => {
  hasSelectedValue.value = true;

  formValues.value.Covering["dcatde:politicalGeocodingURI"] = [
    {
      isValid: true,
      uri: item.resource,
      id: item.id,
      label: item.alt_label["de"],
      inVoc: header,
    },
  ];
  // set Geocoding Link as Spatial
  // if (formValues.value.Additionals["dct:spatial"] === undefined) {
  //   formValues.value.Additionals["dct:spatial"] = [];
  //   formValues.value.Additionals["dct:spatial"].push({ "@id": item.resource });
  // } else
  //   formValues.value.Additionals["dct:spatial"] = [{ "@id": item.resource }];
  // props.context.node.input([{
  //     'isValid': true,
  //     'uri': item.resource,
  //     'id': item.id,
  //     'label': item.alt_label['de'],
  //     'inVoc': header
  // }]);

  // set Geocoding Link as GeoCodingLevelURI
  const trimmedKey = item.resource.split("/").slice(-2, -1)[0];

  let levelUri = "";

  switch (trimmedKey) {
    case "districtKey":
      levelUri = "http://dcat-ap.de/def/politicalGeocoding/Level/administrativeDistrict";
      break;
    case "stateKey":
      levelUri = "http://dcat-ap.de/def/politicalGeocoding/Level/state";
      break;
    case "regionalKey":
      levelUri = "http://dcat-ap.de/def/politicalGeocoding/Level/european";
      break;
    case "municipalAssociationKey":
      levelUri = "http://dcat-ap.de/def/politicalGeocoding/Level/federal";
      break;
    case "municipalityKey":
      levelUri = "http://dcat-ap.de/def/politicalGeocoding/Level/municipality";
      break;
    // Füge weitere Fälle nach Bedarf hinzu
  }
  // console.log(trimmedKey);
  if (
    formValues.value.Additionals["dcatde:politicalGeocodingLevelURI"] ===
    undefined
  ) {
    formValues.value.Additionals["dcatde:politicalGeocodingLevelURI"] = [];
    formValues.value.Additionals["dcatde:politicalGeocodingLevelURI"].push({
      "@id": levelUri,
    });
  } else
    formValues.value.Additionals["dcatde:politicalGeocodingLevelURI"] = [
      { "@id": levelUri },
    ];
  // props.context.node.input([{
  //     'isValid': true,
  //     'uri': item.resource,
  //     'id': item.id,
  //     'label': item.alt_label['de'],
  //     'inVoc': header
  // }]);
};
</script>

<style scoped>
.dropdown-container {
  width: 100%;
  position: relative;
}

/* Ensures proper spacing between description and dropdown */
.dpiV3InnerComponentWrap > div:first-of-type {
  margin-bottom: 16px;
}

/* Add responsive styling */
@media screen and (max-width: 768px) {
  .dropdown-container {
    width: 100%;
  }
}

/* Empty state styling */
.empty-state-message {
  display: flex;
  align-items: center;
  color: var(--text-secondary, #6e7781);
  margin-top: 8px;
  font-size: 14px;
}

.empty-state-message svg {
  margin-right: 8px;
  color: var(--text-secondary, #6e7781);
}

/* Validation error styling */
.validation-error {
  color: var(--error-color, #dc3545);
  font-size: 14px;
  margin-top: 4px;
}
</style>
