<template>
  <Dropdown
  @update:modelValue="handleInput($event)"
    dropdownWidth="large"
    type="inputField"
    v-model="statusTextVal"
    :inputFieldProps="{
      addOnText: false,
      initialHintText: false,
      datePicker: false,
      infoIcon: false,
      preIcon: false,
      label: 'Status (optional)',
      dropdown_dpiV3: true,
      placeholder: 'Geben Sie den Status ein...',
      inputFieldSize: 'large',
      modelValue: statusTextVal,
      defaultInput: !showDeleteButton
    }"
    :data="datasetStatusOptions"
    @deleteDropdownField="deleteStatus"
  />
</template>
<script setup>
import { getDatasetStatus } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import {onMounted, getCurrentInstance, ref, defineProps, defineEmits, watch } from "vue";

const datasetStatusOptions = ref([]);

onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getDatasetStatus(env.api.baseUrl);
    // datasetStatusOptions.value = response.map((item) => ({
    //   "@value": item.label, 
    //   value: item.value,    
    //   selected: false,     
    // }));
    console.log(response);
    
    datasetStatusOptions.value = response.flatMap(item => {
  if (item.value === 'OP_DATPRO') return []; // überspringen
  if (item.value === 'DEVELOP') return []; // überspringen
  if (item.value === 'DISCONT') return []; // überspringen
  return [{
    "@value": item.label,
    uri: item.resource,
    selected: false,
  }];
});
  } catch (error) {
    console.error("Failed to load dataset status data:", error);
  }
});

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  statusText: { type: String, default: "" },
});

const statusTextVal = ref(props.statusText);

const emits = defineEmits(["addStatus", "deleteButtonClicked"]);

const handleInput = (event) => {
  
  const item = datasetStatusOptions.value.find(x => x['@value'] === event);
  console.log(item,datasetStatusOptions);
  emits("addStatus", item, props.distributionId);
};

watch(
  () => props.statusText,
  (newVal) => {
    statusTextVal.value = newVal;
  }
);

const deleteStatus = () => {
  emits("deleteButtonClicked", props.distributionId);
};
</script>
