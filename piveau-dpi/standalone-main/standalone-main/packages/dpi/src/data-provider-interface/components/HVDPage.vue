<template>
  <div class="V3-typography">
    <div class="dpiV3_Frame_831">
      <div class="dpiV3_Frame_840">
        <h4 class="dpiV3_title">
          {{ $t("message.dataupload.datasets.hvdPage.title") }}
        </h4>
        <div class="dpiV3_intro copy-large-regular">
          {{ $t("message.dataupload.datasets.hvdPage.intro-text") }}
        </div>

        <div class="dpiV3_Frame_830">
          <div class="dpiV3_Switch">
            <SwitchV3
              @switch-toggled="onSwitchToggled"
              :hasIcon="false"
              :defaultChecked="false"
              :disabled="false"
            />
          </div>

          <div class="dpiV3_Switch_Label copy-large-semi-bold">
            {{ $t("message.dataupload.datasets.hvdPage.switch-label") }}
          </div>
        </div>
        <div class="dpiV3_Sub_Info copy-large-regular">
          {{ $t("message.dataupload.datasets.hvdPage.sub-info") }}
        </div>
        <Dropdown
          :key="dropdownTextArray.length"
          dropdownWidth="large"
          :isDisabled="!switchState"
          type="inputField"
          v-model="hvdDropdownValue"
          :inputFieldProps="{
            isDisabled: !switchState,
            addOnText: false,
            initialHintText: false,
            datePicker: false,
            infoIcon: false,
            preIcon: false,
            label: 'HVD-Kategorie',
            dropdown_dpiV3: true,
            placeholder: 'Wählen Sie eine HVD-Kategorie',
            inputFieldSize: 'large',
            modelValue: hvdDropdownValue,
            showError:
              formValues?.Discoverability?.hvdPage?.[0]?.isValid === false,
          }"
          :data="dropdownOptions"
          @click="handleClick($event)"
        />
      </div>
      <div
        class="dpiV3_errormsgWrapper"
        v-if="formValues?.Discoverability?.hvdPage?.[0]?.isValid === false"
      >
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular"
          >Bitte wählen Sie eine HVD-Kategorie aus, bevor Sie fortfahren.</span
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import { ref, onMounted, getCurrentInstance, computed, watch } from "vue";
import { getHvdCategories } from "../HappyFlowComponents/services/dpiV3_apis";
import "../config/styles/variables.css";
import "../config/styles/typography.css";
import SwitchV3 from "../HappyFlowComponents/ui/SwitchV3.vue";
import Dropdown from "../HappyFlowComponents/ui/Dropdown.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { useFormValues } from "../composables/useDpiFormValues";

const { t } = useI18n();
const { formValues } = useFormValues();
const switchState = ref(false);
const hvdDropdownValue = ref("");

const hvdCategories = ref([]);
const error = ref(null);
const dropdownTextArray = ref([]);
let chosenItems = ref([{ isValid: true }]);

const props = defineProps({
  context: Object,
});

// Initialize form values if empty
if (Object.keys(formValues.value["Discoverability"]["hvdPage"]).length === 0) {
  formValues.value["Discoverability"]["hvdPage"] = chosenItems.value;
}

const dropdownOptions = computed(() => {
  const options = dropdownTextArray.value.map((text) => ({
    "@value": text.label,
    selected: false,
  }));
  return options;
});

const onSwitchToggled = () => {
  switchState.value = !switchState.value;

  if (!switchState.value) {
    // Switch is OFF - clear value and allow continuation
    formValues.value["Discoverability"]["hvdPage"] = [{ isValid: true }];
    chosenItems.value[0] = { isValid: true };
    hvdDropdownValue.value = "";
  } else {
    // Switch is ON - require selection before allowing continuation
    chosenItems.value[0] = { isValid: "unset" };
    hvdDropdownValue.value = "";
    formValues.value["Discoverability"]["hvdPage"] = [{ isValid: "unset" }];
  }

  console.log("switchStateValue: ", switchState.value);
  console.log("validation state: ", chosenItems.value[0].isValid);
};

const handleClick = (e) => {
  const foundItem = dropdownTextArray.value.find(
    (item) => item.label === e.target.innerHTML
  );

  if (foundItem) {
    // Valid selection made
    chosenItems.value[0] = {
      isValid: true,
      label: foundItem.label,
      uri: foundItem.uri,
    };

    formValues.value["Discoverability"]["hvdPage"] = [
      { isValid: true, label: foundItem.label, uri: foundItem.uri },
    ];

    console.log("HVD Category selected:", foundItem);
  }
};

// Watch for changes in dropdown value to ensure validation stays in sync
watch(hvdDropdownValue, (newValue) => {
  if (switchState.value) {
    // If switch is on and dropdown is cleared, mark as invalid
    if (newValue === "") {
      chosenItems.value[0].isValid = false;
      formValues.value["Discoverability"]["hvdPage"] = [{ isValid: false }];
      console.log(chosenItems);
    }
  }
});

// Computed property to easily check if form can proceed
const canProceed = computed(() => {
  if (!switchState.value) {
    // Switch is off - always valid
    return true;
  }

  // Switch is on - check if valid selection exists
  return (
    chosenItems.value[0]?.isValid === true &&
    chosenItems.value[0]?.label &&
    hvdDropdownValue.value !== ""
  );
});

// Method to validate before form submission
const validateHvdSelection = () => {
  if (switchState.value) {
    // HVD switch is ON - must have valid selection
    const hasValidSelection =
      hvdDropdownValue.value !== "" && chosenItems.value[0]?.isValid === true;

    if (!hasValidSelection) {
      chosenItems.value[0].isValid = false;
      formValues.value["Discoverability"]["hvdPage"] = [{ isValid: false }];
      return false;
    }
  }

  // Either switch is OFF or valid selection exists
  chosenItems.value[0].isValid = true;
  return true;
};

// Expose validation method to parent component
defineExpose({
  validateHvdSelection,
  canProceed,
});

const fetchHvdCategories = async () => {
  try {
    const instance = getCurrentInstance().appContext.app.config.globalProperties.$env;
    const response = await getHvdCategories(instance.api.baseUrl);

    const sortedResponse = response.sort((a, b) =>
      a.pref_label.de.localeCompare(b.pref_label.de, 'de')
    );
    hvdCategories.value = sortedResponse;
    dropdownTextArray.value = sortedResponse.map((item) => ({
      label: item.pref_label.de,
      uri: item.resource,
    }));
  } catch (err) {
    console.error("Error in component:", err);
    error.value = err;
  }
};

onMounted(() => {
  fetchHvdCategories();
});
</script>

<style scoped>
.dpiV3_Content {
  display: flex;
  /* min-width: 448px;
  max-width: 636px; */
  /* padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px); */
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-6, 48px);
  flex: 1 0 0;
}

.dpiV3_Frame_831 {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-8, 64px);
  align-self: stretch;
}

.dpiV3_Frame_840 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
}

.dpiV3_Frame_830 {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_title {
  color: var(--neutral-80, #3d4952);
  align-self: stretch;
  font-style: normal;
}

.dpiV3_intro {
  color: var(--neutral-80, #3d4952);
  align-self: stretch;
}

.dpiV3_Switch_Label {
  color: var(--neutral-80, #3d4952);
  font-style: normal;
}

.dpiV3_Switch {
  height: 32px;
}

.dpiV3_Sub_Info {
  color: var(--neutral-80, #3d4952);
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

.dpiV3_errormsgWrapper span {
  color: var(--text-error, #a9242c);
  text-align: right;
}
</style>
