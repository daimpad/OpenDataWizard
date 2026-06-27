<template>
  <div class="dpiV3_findabilitySwitchWrapper">
    <SwitchV3
      @switch-toggled="onSwitchToggled"
      :hasIcon="false"
      :defaultChecked="switchState"
      :disabled="false"
      :key="switchKey"
    />
    <div class="dpiV3_Switch_Label copy-large-semi-bold">
      {{ $t("message.dataupload.datasets.hvdPage.switch-label") }}
    </div>
  </div>
  <Dropdown
    dropdownWidth="large"
    :isDisabled="!switchState"
    type="inputField"
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
    }"
    :data="dropdownTextArray"
    :modelValue="selectedDropdownValue"
    @click="handleClick($event)"
  />
</template>

<script setup>
import SwitchV3 from "../SwitchV3.vue";
import Dropdown from "../Dropdown.vue";
import { getHvdCategories } from "../../services/dpiV3_apis";
import { ref, onMounted } from "vue";
import { getCurrentInstance } from "vue";
import { useFormValues } from "../../../composables/useDpiFormValues";

const props = defineProps({
  context: Object,
});

const { formValues } = useFormValues();

const switchState = ref(false);
const switchKey = ref(0); // Key to force switch re-mount when needed
const selectedDropdownValue = ref("");
let chosenItems = ref([{ isValid: true }]);
const dropdownTextArray = ref([]);
const hvdCategories = ref([]);

const onSwitchToggled = (state) => {
  switchState.value = state;

  if (!state) {
    // If switch is turned off, clear the form data
    selectedDropdownValue.value = "";
    const deepCopy = formValues.value["Discoverability"];
    deepCopy[Object.keys(deepCopy)[1]] = [{ isValid: false }];

    // Clear selections in dropdown
    dropdownTextArray.value.forEach((item) => (item.selected = false));
  }
};

const fetchHvdCategories = async () => {
  try {
    const instance =
      getCurrentInstance().appContext.app.config.globalProperties.$env;
    const response = await getHvdCategories(instance.api.baseUrl);
    
    // Sort alphabetically
    const sortedResponse = response.sort((a, b) =>
      a.pref_label.de.localeCompare(b.pref_label.de, 'de')
    );
    
    hvdCategories.value = sortedResponse;
    dropdownTextArray.value = sortedResponse.map((item) => ({
      label: item.pref_label.de,
      uri: item.resource,
      "@value": item.pref_label.de,
      selected: false,
    }));

    // After categories are loaded, initialize from form values
    initializeFromFormValues();
  } catch (err) {
    console.error("Error in component:", err);
  }
};

const initializeFromFormValues = () => {
  const hvdData = formValues.value?.Discoverability?.hvdPage?.[0];

  if (hvdData && hvdData.isValid && hvdData.label) {
    // Set switch state
    switchState.value = true;

    // Find and select the matching dropdown item
    const matchingItem = dropdownTextArray.value.find(
      (item) => item.label === hvdData.label || item.uri === hvdData.uri
    );

    if (matchingItem) {
      // Reset all selections first
      dropdownTextArray.value.forEach((item) => (item.selected = false));
      // Set the matching item as selected
      matchingItem.selected = true;
      selectedDropdownValue.value = matchingItem.label;
    }

    // Force switch to re-mount with correct state
    switchKey.value++;
  }
};

const handleClick = (e) => {
  const foundItem = dropdownTextArray.value.find(
    (item) => item.label === e.target.innerHTML
  );

  if (foundItem) {
    // add HVD to Distributions
       
    if (
      formValues.value["DistributionSimple"][
        "hvdNotation"
      ] === undefined
    ) {
      formValues.value["DistributionSimple"]["hvdNotation"] =
        {};
    }
    formValues.value["DistributionSimple"]["hvdNotation"] = {
      hvdUri: foundItem.uri,
    };


    selectedDropdownValue.value = foundItem.label;
    const deepCopy = formValues.value["Discoverability"];
    deepCopy[Object.keys(deepCopy)[1]] = [
      {
        isValid: true,
        label: foundItem.label,
        uri: foundItem.uri,
      },
    ];
  }
};

onMounted(() => {
  fetchHvdCategories();
});
</script>
<style>
.dpiV3_findabilitySwitchWrapper {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}
</style>
