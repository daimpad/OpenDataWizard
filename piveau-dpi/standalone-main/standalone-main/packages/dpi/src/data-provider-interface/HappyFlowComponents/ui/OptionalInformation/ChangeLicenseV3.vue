<template>
  <p v-if="!asCard">
    Die von Ihnen gewählte Lizenz bestimmt, wie andere Ihre Daten verwenden und
    teilen können. Je einfacher die Lizenz, desto besser. Um eine
    Wiederverwendung zu erleichtern, ist die Auswahl eines bereits verbreiteten
    Lizenzstandards zu empfehlen.
  </p>
  <div class="dpiV3_LinkAndMetadata">
    <Dropdown
      @update:modelValue="handleChangeLicenseVal($event)"
      dropdownWidth="large"
      type="inputField"
      v-model="changeLicenseDropdownVal"
      :inputFieldProps="{
        addOnText: false,
        initialHintText: false,
        datePicker: false,
        infoIcon: false,
        preIcon: false,
        label: 'Lizenz nur für diesen Link',
        dropdown_dpiV3: true,
        placeholder: 'Geben Sie die Lizenz ein...',
        inputFieldSize: 'large',
        modelValue: changeLicenseDropdownVal,
        defaultInput: !showDeleteButton,
      }"
      :data="licenseOptions"
      @deleteDropdownField="deleteModifiedField"
      :class="{ dpiV3_Space3: showDeleteButton }"
    />
    <div v-if="licenseError" class="dpiV3_errorMessage">
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular">{{ licenseError }}</span>
    </div>

    <InputField
      v-if="showNameInput"
      @input="handleChangeLicenseTitle($event, '')"
      v-model="changeLicenseTitleText"
      :addOnText="false"
      :datePicker="false"
      :infoIcon="false"
      :placeholder="'Bitte ' + 'Namensnennungstext' + ' eingeben...'"
      :preIcon="false"
      inputFieldSize="large"
      :initialHintText="true"
      label="Nennung des Datenbereitstellers"
      :showEndIcon="false"
      :defaultInput="!showDeleteButton"
      @deleteButtonClicked="deleteTitleField"
      supportingHintMessage="Dieser Namensnennungstext stellt sicher, dass die Lizenzbedingungen eingehalten werden."
    />
    <div v-if="attributionError" class="dpiV3_errorMessage">
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular">{{ attributionError }}</span>
    </div>
  </div>
</template>

<script setup>
import { getLicenses } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import {
  onMounted,
  getCurrentInstance,
  ref,
  defineProps,
  defineEmits,
  watch,
} from "vue";
import InputField from "../InputField.vue";
import { PhWarning } from "@phosphor-icons/vue";

let licenseOptions = ref([]);
const licenseError = ref(null);
const attributionError = ref(null);

onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    if (props.changeLicenseText !== "") {
      showNameInput.value = true;
    }
    const response = await getLicenses(env.api.baseUrl);
    licenseOptions.value = response.map((item) => ({
      uri: item.uri,
      "@value": item.value,
      label: item.label,
    }));
  } catch (error) {
    console.error("Failed to load licenses", error);
  }
});

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  changeLicenseText: { type: String, default: "" },
  changeLicenseDropdownText: { type: String, default: "" },
  asCard: { type: Boolean, default: false },
});

let showNameInput = ref(false);

const changeLicenseTitleText = ref(props.changeLicenseText);
const plannedAvailabilityOptions = ref([]);
const changeLicenseDropdownVal = ref(props.changeLicenseDropdownText);

const emits = defineEmits(["addChangeLicense", "deleteButtonClicked"]);

const validate = (value, trigger) => {
  const license =
    typeof changeLicenseDropdownVal.value === "string"
      ? changeLicenseDropdownVal.value.trim()
      : "";
  const attribution =
    typeof changeLicenseTitleText.value === "string"
      ? changeLicenseTitleText.value.trim()
      : "";

  let hasErrors = false;

  if (trigger) {
    // Only show name input for CC BY or DL-BY-DE licenses
    const requiresAttribution =
      value !== "" && (value.includes("cc-by") || value.includes("dl-by-de"));
    showNameInput.value = requiresAttribution;
  }

  // Clear previous errors
  licenseError.value = null;
  attributionError.value = null;

  // If license contains 'by-', attribution is required
  if (
    license &&
    (license.toLowerCase().includes("cc-by") ||
      license.toLowerCase().includes("cc by"))
  ) {
    if (!attribution) {
      attributionError.value =
        "Nennung des Datenbereitstellers ist erforderlich für diese Lizenz";
      hasErrors = true;
    }
  }

  // If attribution is filled, license must be filled
  if (attribution && !license) {
    licenseError.value =
      "Lizenz ist erforderlich wenn Nennung des Datenbereitstellers ausgefüllt ist";
    hasErrors = true;
  }

  return hasErrors;
};

const handleChangeLicenseTitle = (event) => {
  const value = typeof event === "string" ? event : event?.target?.value ?? "";
  changeLicenseTitleText.value = value;

  emits(
    "addChangeLicense",
    changeLicenseTitleText.value,
    changeLicenseDropdownVal.value,
    props.distributionId
  );

  // Validate after update
  setTimeout(() => {
    validate(event, false);
  }, 0);
};

const handleChangeLicenseVal = (event) => {
  let licenseObject = licenseOptions.value.find(
    (obj) => obj["@value"] === event
  );
  changeLicenseDropdownVal.value = event;
  changeLicenseTitleText.value = "";
  emits(
    "addChangeLicense",
    changeLicenseTitleText.value,
    changeLicenseDropdownVal.value,
    props.distributionId,
    licenseObject.uri
  );

  // Validate after update
  setTimeout(() => {
    validate(event, true);
  }, 0);
};

watch(
  () => props.changeLicenseDropdownText,
  (newVal) => {
    changeLicenseDropdownVal.value = newVal;
  }
);

watch(
  () => props.changeLicenseText,
  (newVal) => {
    changeLicenseTitleText.value = newVal;
  }
);

const deleteModifiedField = () => {
  changeLicenseDropdownVal.value = "";
  setTimeout(() => {
    validate();
  }, 0);
};

const deleteTitleField = () => {
  changeLicenseTitleText.value = "";
  setTimeout(() => {
    validate();
  }, 0);
};

// Expose validation for parent component
defineExpose({
  validate,
});
</script>

<style scoped>
.dpiV3_Space3 {
  margin-bottom: var(--Spacing-3, 16px);
}

.dpiV3_errorMessage {
  display: flex;
  align-items: center;
  gap: 4px;
  color: var(--text-error, #a9242c);
  margin-top: 4px;
  margin-bottom: 8px;
}

.dpiV3_errorMessage svg {
  color: var(--text-error, #a9242c);
  flex-shrink: 0;
}
</style>
