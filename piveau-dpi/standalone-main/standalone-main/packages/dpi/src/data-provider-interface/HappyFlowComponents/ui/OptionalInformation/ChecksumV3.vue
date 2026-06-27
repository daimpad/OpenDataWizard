<template>
  <!-- <div class="dpiV3_Top">
    
    <div class="dpiV3_label">Prüfsumme</div>
    <TextButtonSmall
      v-if="showDeleteButton"
      buttonText="löschen"
      @click="deleteButton"
      tabindex="0"
    />
  </div> -->

  <div class="dpiV3_LinkAndMetadata" :class="{ dpiV3_docAllAsCard: asCard }">
    <div class="dpiV3AutoCompleteWrap" :class="{ dpiV3_docAsCard: asCard }">
      <InputField
        @input="handleChecksumTitle($event, '')"
        v-model="checkSumTitleText"
        :addOnText="false"
        :datePicker="false"
        :infoIcon="false"
        :placeholder="'Bitte ' + 'Prüfsumme' + ' eingeben...'"
        :preIcon="false"
        inputFieldSize="large"
        :initialHintText="false"
        label="Prüfsumme"
        :showEndIcon="false"
        :defaultInput="true"
        @deleteButtonClicked="deleteTitleField"
        :class="{ dpiV3_Space3: showDeleteButton }"
      />
      <div v-if="titleError" class="dpiV3_errorMessage">
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{ titleError }}</span>
      </div>
      <Dropdown
        @update:modelValue="handleChecksumValue($event)"
        dropdownWidth="large"
        type="inputField"
        v-model="checksumDropdownVal"
        :inputFieldProps="{
          addOnText: false,
          initialHintText: false,
          datePicker: false,
          infoIcon: false,
          preIcon: false,
          label: 'Algorithmus',
          dropdown_dpiV3: true,
          placeholder: 'Geben Sie den Algorithmus ein...',
          inputFieldSize: 'large',
          modelValue: checksumDropdownVal,
          defaultInput: true,
        }"
        :data="checksumOptions"
        @deleteDropdownField="deleteModifiedField"
      />
      <!-- <ButtonV3
      v-if="showDeleteButton"
        class="dpiV3_tempAddMore"
        buttonText="Löschen"
        size="small"
        iconStart="trash"
        variant="tertiary"
        @click="confirmDelete(accessService)"
      />-->
    </div> 
    <div v-if="algorithmError" class="dpiV3_errorMessage">
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular">{{ algorithmError }}</span>
    </div>
  </div>
</template>

<script setup>
import { useFormValues } from "../../../composables/useDpiFormValues";
import { getChecksumAlgorithms } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import ButtonV3 from "../ButtonV3.vue";
import TextButtonSmall from "../TextButtonSmall.vue";
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

const checksumOptions = ref([]);
const { formValues } = useFormValues();
const algorithmError = ref(null);
const titleError = ref(null);

onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getChecksumAlgorithms(env.api.baseUrl);
    checksumOptions.value = response.map((item) => ({
      "@value": item.value,
      label: item.label,
      selected: false,
      uri: item.resource,
    }));
  } catch (error) {
    console.error("Failed to load checksum algorithms:", error);
  }
});


const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  checksumText: { type: String, default: "" },
  checksumDropdownText: { type: String, default: "" },
  uri: { type: String, default: "" },
  distIndex: { type: Number, required: false },
  asCard: { type: Boolean, required: false, default: false },
});

const checkSumTitleText = ref(props.checksumText);
const plannedAvailabilityOptions = ref([]);
const checksumDropdownVal = ref(props.checksumDropdownText);

const emits = defineEmits(["addChecksum", "deleteButtonClicked"]);

let uri = "";

// Validate that if either field is filled, both must be filled
const validateFields = () => {
  const hasTitle =
    checkSumTitleText.value && checkSumTitleText.value.trim() !== "";
  const hasAlgorithm =
    checksumDropdownVal.value && checksumDropdownVal.value.trim() !== "";

  let hasErrors = false;

  // If either field is filled, both must be filled
  if (hasTitle || hasAlgorithm) {
    if (!hasTitle) {
      titleError.value = "Prüfsumme ist erforderlich";
      hasErrors = true;
    } else {
      titleError.value = null;
    }

    if (!hasAlgorithm) {
      algorithmError.value = "Algorithmus ist erforderlich";
      hasErrors = true;
    } else {
      algorithmError.value = null;
    }
  } else {
    // Both empty is valid
    titleError.value = null;
    algorithmError.value = null;
  }

  return !hasErrors;
};

// Validate all - returns true if there are errors
const validateAllItems = () => {
  return !validateFields();
};

const handleChecksumTitle = (event) => {
  checkSumTitleText.value = event.target.value;
  emits(
    "addChecksum",
    checkSumTitleText.value,
    checksumDropdownVal.value,
    props.distributionId
  );
  validateFields();

  // No validation on input - only on modal button click
};

const handleChecksumValue = (event) => {
  checksumDropdownVal.value = event;
  uri =
    checksumOptions.value.find((item) => item["@value"] === event)?.uri || "";
  emits(
    "addChecksum",
    checkSumTitleText.value,
    checksumDropdownVal.value,
    props.distributionId,
    uri
  );
  validateFields();
  // No validation on selection - only on modal button click
};

watch(
  () => props.checksumDropdownText,
  (newVal) => {
    checksumDropdownVal.value = newVal;
    // No validation on watch
  }
);

watch(
  () => props.checksumText,
  (newVal) => {
    checkSumTitleText.value = newVal;
    // No validation on watch
  }
);

const deleteModifiedField = () => {
  formValues.value.DistributionSimple["dcat:distribution"][
    props.distIndex
  ].checksum.uri = "";
  formValues.value.DistributionSimple["dcat:distribution"][
    props.distIndex
  ].checksum["spdx:checksum"] = "";

  checksumDropdownVal.value = "";

  // Clear error when field is cleared
  algorithmError.value = null;
};

const deleteTitleField = () => {
  formValues.value.DistributionSimple["dcat:distribution"][
    props.distIndex
  ].checksum.title = "";
  checkSumTitleText.value = "";

  // Clear error when field is cleared
  titleError.value = null;
};

// Expose validation methods for parent component
defineExpose({
  validateAllItems,
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
  margin-top: -10px;
  margin-bottom: -8px;
}

.dpiV3_errorMessage svg {
  color: var(--text-error, #a9242c);
  flex-shrink: 0;
}
.dpiV3_docAsCard {
  background-color: white;
  margin-bottom: var(--Spacing-3, 8px);
}
</style>
