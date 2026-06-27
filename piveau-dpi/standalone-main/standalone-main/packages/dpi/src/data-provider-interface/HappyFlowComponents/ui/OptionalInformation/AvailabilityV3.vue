<template>
  <Dropdown
    @update:modelValue="handleInput($event)"
    dropdownWidth="large"
    type="inputField"
    v-model="availabiltyTextVal"
    :inputFieldProps="{
      addOnText: false,
      initialHintText: false,
      datePicker: false,
      infoIcon: false,
      preIcon: false,
      label:
        t(
          'message.dataupload.datasets.dcat:distribution.advanced.dcatap:availability'
        ) + ' (optional)',
      dropdown_dpiV3: true,
      placeholder: t(
        'message.dataupload.datasets.dcat:distribution.advanced.availability-placeholder'
      ),
      inputFieldSize: 'large',
      modelValue: availabiltyTextVal,
      defaultInput: !showDeleteButton,
    }"
    :data="plannedAvailabilityOptions"
    @deleteDropdownField="deleteAvailability"
  />
</template>
<script setup>
import { getPlannedAvailability } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import {
  onMounted,
  getCurrentInstance,
  ref,
  defineProps,
  defineEmits,
  watch,
} from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getPlannedAvailability(env.api.baseUrl);
    console.log(response);
    

    plannedAvailabilityOptions.value = response.map((item) => ({
      "@value": item.label,
      label: item.label,
      uri: item.uri,
    }));
    console.log(plannedAvailabilityOptions.value);
  } catch (error) {
    console.error("Failed to load planned availability data", error);
  }
});

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  availabilityText: { type: String, default: "" },
});

const plannedAvailabilityOptions = ref([]);
const availabiltyTextVal = ref(props.availabilityText);
let uri = plannedAvailabilityOptions.value.uri;

const emits = defineEmits(["addAvailability", "deleteButtonClicked"]);

const handleInput = (event) => {

  let uri =
    plannedAvailabilityOptions.value.find((item) => item["@value"] === event)
      .uri || "";
  let label =
    plannedAvailabilityOptions.value.find((item) => item["@value"] === event)
      .label || "";
 console.log(label);
 
  emits("addAvailability", event, props.distributionId, uri, label);
};

watch(
  () => props.availabilityText,
  (newVal) => {
    availabiltyTextVal.value = newVal;
  }
);

const deleteAvailability = () => {
  emits("deleteButtonClicked", props.distributionId);
};
</script>
