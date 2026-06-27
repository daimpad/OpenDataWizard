<template>
  <div class="dpiV3InnerComponentWrap">
    <h4>{{ $t("message.dataupload.datasets.dct:license.title") }}</h4>
    <div class="copy-large-regular">
      {{ $t("message.dataupload.datasets.dct:license.description") }}
    </div>
    <Dropdown
      @update:modelValue="handleChangeLicenseVal($event, 'dcterms:license', 1)"
      @input="validateLicenseInput"
      @blur="handleLicenseBlur"
      dropdownWidth="large"
      type="inputField"
      v-model="changeLicenseDropdownVal"
      :inputFieldProps="{
        addOnText: false,
        initialHintText: false,
        datePicker: false,
        infoIcon: false,
        preIcon: false,
        label: 'Lizenz',
        dropdown_dpiV3: true,
        placeholder: 'Lizenz wählen...',
        inputFieldSize: 'large',
        modelValue: changeLicenseDropdownVal,
        defaultInput: true,
        showError:
          licenseErrorMessage !== '' ||
          (arr[0].isValid === 'unset' && validInputs[1] === false),
      }"
      :data="licenseOptions"
      @deleteButtonClicked="deleteModifiedField"
    />
    <div v-if="licenseErrorMessage" class="validation-error">
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular">{{ licenseErrorMessage }}</span>
    </div>
    <div class="input-container">
      <InputField
        v-if="showNameInput"
        :modelValue="arr[0]['title']"
        @update:modelValue="updateValue($event, 'title', 2)"
        :addOnText="false"
        :datePicker="false"
        :infoIcon="false"
        placeholder="Geben Sie den Namensnennungstext für Ihre Lizenz an..."
        :preIcon="false"
        inputFieldSize="large"
        :initialHintText="true"
        :label="
          $t(
            'message.dataupload.datasets.dcat:distribution.advanced.dcatde:licenseAttributionByText'
          )
        "
        :showEndIcon="false"
        :defaultInput="true"
      
        @deleteButtonClicked="deleteModifiedField"
        supportingHintMessage="Dieser Namensnennungstext stellt sicher, dass die Lizenzbedingungen eingehalten werden."
      />
      <div
        v-if="arr[0].isValid == false && validationErrors[2].show"
        class="dpiV3_errormsgWrapper"
      >
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ validationErrors[2].message }}</span>
      </div>
    </div>

    <div class="dpiV3_Content_InputPage">
      <div class="dpiV3_Card_Tips">
        <div class="dpiV3_Icon_Title">
          <PhLightbulb :size="32" color="#009fe3" />
          <div class="dpiV3_Info-Text dpiV3_activeStepName">
            {{ $t("message.dataupload.datasets.dct:license.advices.title") }}
          </div>
        </div>
        <div class="dpiV3_CT-Content">
          <div class="dpiV3_copy_large_regular dpiV3_hvd_frame3846">
            {{
              $t("message.dataupload.datasets.dct:license.advices.description")
            }}
          </div>

          <div class="dpiV3_hvd_frame3846">
            <div class="dpiV3_dots dpiV3_copy_large_semi_bold">
              {{
                $t(
                  "message.dataupload.datasets.dct:license.advices.licenseName1"
                )
              }}
              <span class="dpiV3_normal">{{
                $t(
                  "message.dataupload.datasets.dct:license.advices.licenseName1_desc"
                )
              }}</span>
            </div>
            <div class="dpiV3_dots dpiV3_copy_large_semi_bold">
              {{
                $t(
                  "message.dataupload.datasets.dct:license.advices.licenseName2"
                )
              }}
              <span class="dpiV3_normal">{{
                $t(
                  "message.dataupload.datasets.dct:license.advices.licenseName2_desc"
                )
              }}</span>
            </div>
            <div class="dpiV3_dots dpiV3_copy_large_semi_bold">
              {{
                $t(
                  "message.dataupload.datasets.dct:license.advices.licenseName3"
                )
              }}
              <span class="dpiV3_normal">{{
                $t(
                  "message.dataupload.datasets.dct:license.advices.licenseName3_desc"
                )
              }}</span>
            </div>
            <a
              href="https://oc.bydata.de/sharing/articles/licenses"
              target="_blank"
              rel="noopener noreferrer"
              class="dpiV3_link"
            >
              <PhArrowSquareOut :size="24" />
              {{
                $t(
                  "message.dataupload.datasets.dct:license.advices.linkToLicenses"
                )
              }}
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="end_container">
      <span class="copy-large-regular">
        Die Wahl der Lizenz spielt eine wichtige Rolle für den korrekten Umgang
        mit Ihrem Datensatz.
      </span>
      <div
        class="form-error-message"
        v-if="arr.find((obj) => obj.isValid === false)"
      >
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular"
          >Bitte füllen Sie alle Pflichtfelder aus, bevor Sie fortfahren.</span
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  onMounted,
  onUnmounted,
  getCurrentInstance,
  watch,
  computed,
} from "vue";
import InputField from "../HappyFlowComponents/ui/InputField.vue";
import Dropdown from "../HappyFlowComponents/ui/Dropdown.vue";
import { PhLightbulb, PhArrowSquareOut, PhWarning } from "@phosphor-icons/vue";
import { getLicenses } from "../HappyFlowComponents/services/dpiV3_apis";
import { useEditModeInfo } from "../composables/useDpiEditMode";
import { useFormValues } from "../composables/useDpiFormValues";

const { isEditMode } = useEditModeInfo();
const { formValues } = useFormValues();

let showNameInput = ref(false);
let licenseOptions = ref([]);
let changeLicenseDropdownVal = ref("");
let typingTimeout = ref(null);

const licenseErrorMessage = ref("");

const props = defineProps({
  context: Object,
  changeLicenseText: { type: String, default: "" },
  changeLicenseDropdownText: { type: String, default: "" },
});

let validInputs = ref({ 1: "unset", 2: "unset" });
const arr = ref([
  { isValid: "unset", "dcterms:license": "", title: "", uri: "" },
]);

// Add validation errors state
const validationErrors = ref({
  2: {
    show: false,
    message: "Bitte geben Sie einen Namensnennungstext für diese Lizenz ein.",
  },
});

if (!isEditMode.value) props.context.node.input(arr);

const handleChangeLicenseVal = (e, namespace, iIndex) => {
  validateLicense(e, namespace, iIndex);
};

const validateLicenseInput = (event) => {
  const inputValue =
    typeof event === "object" && event.target ? event.target.value : event;

  // Clear previous timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }

  // Immediate validation for exact matches or empty values
  validateLicenseTyping(inputValue, "dcterms:license", 1);

  // Debounced validation for errors (after user stops typing)
  typingTimeout.value = setTimeout(() => {
    validateLicenseWithErrors(inputValue, "dcterms:license", 1);
  }, 800); // Wait 800ms after user stops typing
};

// New method for validation during typing (more lenient)
const validateLicenseTyping = (value, namespace, iIndex) => {
  // Clear error message while typing
  licenseErrorMessage.value = "";

  // Check if the current value exactly matches a license
  const exactMatch = licenseOptions.value.find(
    (option) => option["@value"] === value
  );

  if (exactMatch) {
    // Exact match found - validate as normal
    validateLicense(value, namespace, iIndex, exactMatch.uri);
  } else if (value === "") {
    // Empty value - reset validation
    validInputs.value[iIndex] = false;
    arr.value[0].isValid = "unset";
    showNameInput.value = false;
    validationErrors.value[2].show = false;

    arr.value = arr.value.map((item) => {
      if (namespace in item) {
        return { ...item, [namespace]: "", uri: "", title: "" };
      }
      return item;
    });
    props.context.node.input(arr.value);
  } else {
    // Partial input - don't show error yet, but don't mark as valid either
    validInputs.value[iIndex] = "unset";
    arr.value[0].isValid = "unset";
  }
};

// Method for validation with error messages (after user stops typing or loses focus)
const validateLicenseWithErrors = (value, namespace, iIndex) => {
  if (value === "") {
    // Empty value is fine, don't show error
    licenseErrorMessage.value = "";
    return;
  }

  const exactMatch = licenseOptions.value.find(
    (option) => option["@value"] === value
  );

  if (exactMatch) {
    // Exact match found - validate as normal
    validateLicense(value, namespace, iIndex, exactMatch.uri);
  } else {
    // No match found - show error
    licenseErrorMessage.value =
      "Bitte wählen Sie eine gültige Lizenz aus der Liste";
    validInputs.value[iIndex] = false;
    arr.value[0].isValid = "unset";
  }
};

// Handle blur event (when user leaves the field)
const handleLicenseBlur = (event) => {
  // Clear any pending timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }

  // Validate immediately on blur
  const inputValue = event.target.value;
  validateLicenseWithErrors(inputValue, "dcterms:license", 1);
};

// Updated method to handle model updates with validation
const updateValue = (newValue, namespace, iIndex) => {
  // Update the field value
  arr.value = arr.value.map((item) => {
    if (namespace in item) {
      return { ...item, [namespace]: newValue };
    }
    return item;
  });

  // Validate attribution field
  if (namespace === "title") {
    const currentLicense = arr.value[0]?.["dcterms:license"] || "";
    const requiresAttribution =
      currentLicense !== "" &&
      (currentLicense.includes("cc-by") || currentLicense.includes("dl-by-de"));

    if (requiresAttribution) {
      const isValidAttribution = newValue && newValue.trim() !== "";
      validationErrors.value[2].show = !isValidAttribution;
      validInputs.value[2] = isValidAttribution;

      // Update overall validity - only set to true or 'unset', never false
      if (validInputs.value[1] === true && isValidAttribution) {
        arr.value[0].isValid = true;
      } else {
        arr.value[0].isValid = "unset";
      }
    } else {
      validationErrors.value[2].show = false;
      validInputs.value[2] = "unset";
    }
  }

  props.context.node.input(arr.value);
};

const validateLicense = (value, namespace, iIndex, uri) => {
  const isValidLicense =
    value === "" ||
    licenseOptions.value.some((option) => option["@value"] === value);
  let title = ref();

  // if (licenseOptions.value.some((option) => option["@value"] === value)) {
  //   title.value = licenseOptions.value.find(
  //     (option) => option["@value"] === value
  //   ).label;
  // }

  if (!isValidLicense && value !== "") {
    licenseErrorMessage.value =
      "Bitte wählen Sie eine gültige Lizenz aus der Liste";
    validInputs.value[iIndex] = false;
    arr.value[0].isValid = "unset";
  } else {
    licenseErrorMessage.value = "";

    // Only show name input for CC BY or DL-BY-DE licenses
    const requiresAttribution =
      value !== "" && (value.includes("cc-by") || value.includes("dl-by-de"));
    showNameInput.value = requiresAttribution;

    if (value !== "") {
      validInputs.value[iIndex] = true;

      // Check if attribution is required and provided
      const currentAttribution = arr.value[0]?.title || "";
      const isValidOverall =
        !requiresAttribution ||
        (requiresAttribution && currentAttribution.trim() !== "");

      // Only set to true or 'unset', never false
      arr.value[0].isValid = isValidOverall ? true : "unset";

      // Update attribution validation
      if (requiresAttribution) {
        validationErrors.value[2].show = currentAttribution.trim() === "";
        validInputs.value[2] = currentAttribution.trim() !== "";
      } else {
        validationErrors.value[2].show = false;
        validInputs.value[2] = "unset";
      }
    } else {
      validInputs.value[iIndex] = false;
      arr.value[0].isValid = "unset";
      validationErrors.value[2].show = false;
    }

    // Update the model
    arr.value = arr.value.map((item) => {
      if (namespace in item) {
        return { ...item, [namespace]: value, uri: uri, title: title };
      }
      return item;
    });
  }
  props.context.node.input(arr.value);
};

const handleChangeLicenseTitle = (e, namespace, iIndex) => {
  if (e.target.value != "") {
    validInputs.value[iIndex] = true;
    if (validInputs.value[1] && validInputs.value[2]) {
      arr.value[0].isValid = true;
    }
  } else {
    validInputs.value[iIndex] = "unset";
    if (validInputs.value[1] !== true || validInputs.value[2] !== true) {
      arr.value[0].isValid = "unset";
    }
  }

  const newValue = e.target.value;
  arr.value = arr.value.map((item) => {
    if (namespace in item) {
      return { ...item, [namespace]: newValue };
    }
    return item;
  });
  props.context.node.input(arr.value);
};

watch(changeLicenseDropdownVal, (newValue) => {
  let uri = licenseOptions.value.find(
    (item) => item["@value"] === newValue
  )?.uri;

  if (newValue !== undefined) {
    validateLicense(newValue, "dcterms:license", 1, uri);
  }
});

watch(
  () => arr.value[0].isValid,
  (newValue) => {
    // console.log("License validity changed:", newValue);
    if (newValue === false) {
      // When parent sets validity to false, validate and show errors
      const currentValue = arr.value[0]?.["dcterms:license"] || "";

      if (currentValue === "") {
        licenseErrorMessage.value = "Bitte wählen Sie eine Lizenz aus.";
      } else {
        // Check if it's a valid license
        const exactMatch = licenseOptions.value.find(
          (option) => option["@value"] === currentValue
        );
        if (!exactMatch) {
          licenseErrorMessage.value =
            "Bitte wählen Sie eine gültige Lizenz aus der Liste";
        }
      }

      // Also check attribution field if needed
      const requiresAttribution =
        currentValue !== "" &&
        (currentValue.includes("cc-by") || currentValue.includes("dl-by-de"));

      if (requiresAttribution) {
        const currentAttribution = arr.value[0]?.title || "";
        if (currentAttribution.trim() === "") {
          validationErrors.value[2].show = true;
        }
      }
    }
  }
);

onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getLicenses(env.api.baseUrl);
    licenseOptions.value = response.map((item) => ({
      "@value": item.value,
      label: item.label,
      uri: item.uri,
    }));
    licenseOptions.value.sort((a, b) => a["@value"].localeCompare(b["@value"]));
    // console.log(licenseOptions.value);
  } catch (error) {
    console.error("Failed to load licenses", error);
  }
});

onUnmounted(() => {
  // Clean up timeout to prevent memory leaks
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
});
</script>

<style scoped>
.dpiV3InnerComponentWrap {
  position: relative;
}
.end_container {
  position: relative;
}
.validation-error {
  color: var(--text-error, #a9242c);
  font-size: var(--copy-small-regular-font-size, 15px);
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.dpiV3_dots::before {
  content: "• ";
  margin-right: 8px;
}

.dpiV3_normal {
  font-weight: normal;
}

.dpiV3_link {
  color: var(--blue-80);
  margin-top: var(--Spacing-5);
  display: flex;
  font-size: 15px;
  font-weight: 600;
  line-height: 160%;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.dpiV3_link:hover {
  opacity: 0.8;
  text-decoration: underline;
}

.dpiV3_link:focus {
  outline: 2px solid var(--Focused, #0196d8);
  border-radius: 4px;
}

.dpiV3_errormsgWrapper {
  display: flex;
  align-items: center;
  gap: 6px;
  width: auto;
  position: absolute;
  right: 0;
  bottom: -25px;
  color: var(--text-error, #a9242c);
}
.input-container {
  position: relative;
  width: 100%;
}
.form-error-message {
  width: 375px;
  position: absolute;
  right: 10px;
  bottom: -25px;
  color: var(--text-error, #a9242c);
  display: flex;
  align-items: center;
  gap: 6px;
}

.form-error-message span {
  color: var(--text-error, #a9242c);
  text-align: right;
}
</style>
