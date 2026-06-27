<template>
  <!------------------- dcat:mediaType: Medientyp ------------------>
  <Dropdown
    v-if="fileFormatType === 'dcat:mediaType'"
    @input="handleInput($event, 'dcat:mediaType')"
    @update:modelValue="handleChosenInput($event, 'dcat:mediaType')"
    dropdownWidth="large"
    type="inputField"
    v-model="fileFormatTextVal"
    :inputFieldProps="{
      addOnText: false,
      initialHintText: false,
      datePicker: false,
      infoIcon: false,
      preIcon: false,
      label: 'Medientyp (optional)',
      dropdown_dpiV3: true,
      placeholder: 'Format wählen...',
      inputFieldSize: 'large',
      autocomplete: 'true',
      modelValue: fileFormatTextVal,
      defaultInput: !showDeleteButton
    }"
    :data="filteredData"
   
     @deleteDropdownField="handleDelete('dcat:mediaType')"
  />

  <!------------------- dcat:compressFormat: Compression Format ------------------>
  <Dropdown
    v-if="fileFormatType === 'dcat:compressFormat'"
    @input="handleInput($event, 'dcat:compressFormat')"
    @update:modelValue="handleChosenInput($event, 'dcat:compressFormat')"
    dropdownWidth="large"
    type="inputField"
    v-model="compressFormatTextVal"
    :inputFieldProps="{
      addOnText: false,
      initialHintText: false,
      datePicker: false,
      infoIcon: false,
      preIcon: false,
      label: 'Kompressionsformat (optional)',
      dropdown_dpiV3: true,
      placeholder: 'Format wählen...',
      inputFieldSize: 'large',
      autocomplete: 'true',
      modelValue: compressFormatTextVal,
      defaultInput: !showDeleteButton
    }"
    :data="filteredData"
 
     @deleteDropdownField="handleDelete('dcat:compressFormat')"
  />

  <!------------------- dcat:packageFormat: Paketformat ------------------>
  <Dropdown
    v-if="fileFormatType === 'dcat:packageFormat'"
    @input="handleInput($event, 'dcat:packageFormat')"
    @update:modelValue="handleChosenInput($event, 'dcat:packageFormat')"
    dropdownWidth="large"
    type="inputField"
    v-model="packageFormatTextVal"
    :inputFieldProps="{
      addOnText: false,
      initialHintText: false,
      datePicker: false,
      infoIcon: false,
      preIcon: false,
      label: 'Paketformat (optional)',
      dropdown_dpiV3: true,
      placeholder: 'Format wählen...',
      inputFieldSize: 'large',
      autocomplete: 'true',
      modelValue: packageFormatTextVal,
      defaultInput: !showDeleteButton
    }"
    :data="filteredData"
 
    @deleteDropdownField="handleDelete('dcat:packageFormat')"
  />
</template>
<script setup>
import Dropdown from "../Dropdown.vue";
import {
  ref,
  defineProps,
  defineEmits,
  watch,
} from "vue";

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  fileFormatText: { type: String, default: "" },
  compressFormatText: { type: String, default: "" },
  packageFormatText: { type: String, default: "" },
  fileFormatType: { type: String, default: "" },
  fileTypes: { type: Array, required: false },
});
const fileFormatTextVal = ref(props.fileFormatText);
const compressFormatTextVal = ref(props.compressFormatText);
const packageFormatTextVal = ref(props.packageFormatText);

const filteredData = ref([...props.fileTypes]);

const emits = defineEmits([
  "addMediaType",
  "addCompressFormat",
  "addPackageFormat",
  "deleteButtonClicked",
]);

const handleInput = (event, field, docId = null) => {
  filteredData.value = [...props.fileTypes];
  const inputValue =
    typeof event === "string"
      ? event.trim().toUpperCase()
      : event?.target?.value?.trim().toUpperCase();

  if (inputValue.length > 0) {
    filteredData.value = props.fileTypes.filter((item) =>
      item["@value"].toUpperCase().startsWith(inputValue)
    );
  } else {
    filteredData.value = [...fileTypes.value];
  }
};

const handleChosenInput = (event, field, docId = null) => {
  switch (field) {
    case "dcat:mediaType":
      emits("addMediaType", event, field, props.distributionId);
      break;
    case "dcat:compressFormat":
      emits("addCompressFormat", event, field, props.distributionId);
      break;
    case "dcat:packageFormat":
      emits("addPackageFormat", event, field, props.distributionId);
      break;
    default:
      break;
  }
};

const handleDelete = (field, docId = null) => {
    emits("deleteButtonClicked", field, props.distributionId);
};

watch(
  () => props.fileFormatText,
  (newVal) => {
    fileFormatTextVal.value = newVal;
  }
);
watch(
  () => props.compressFormatText,
  (newVal) => {
    compressFormatTextVal.value = newVal;
  }
);
watch(
  () => props.packageFormatText,
  (newVal) => {
    packageFormatTextVal.value = newVal;
  }
);
</script>
