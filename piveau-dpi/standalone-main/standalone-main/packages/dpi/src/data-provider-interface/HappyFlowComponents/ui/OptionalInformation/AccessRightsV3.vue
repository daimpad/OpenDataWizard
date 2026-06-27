<template>
  <!-- <InputField
    @input="handleInput($event, 'dct:accessRights')"
    v-model="accessRightsText"
    :addOnText="false"
    :datePicker="false"
    :infoIcon="false"
    :placeholder="'Bitte ' + 'Grad der Zugänglichkeit' + ' eingeben...'"
    :preIcon="false"
    inputFieldSize="large"
    :initialHintText="false"
    label="Grad der Zugänglichkeit (URL)"
    :showEndIcon="false"
    :defaultInput=!showDeleteButton
    @deleteButtonClicked="deleteAccessRightsField"
  /> -->
  <Dropdown
    @update:modelValue="handleInput($event)"
    dropdownWidth="large"
    type="inputField"
    v-model="rightsTextVal"
    :inputFieldProps="{
      addOnText: false,
      initialHintText: false,
      datePicker: false,
      infoIcon: false,
      preIcon: false,
      label:
        t(
          'message.dataupload.datasets.dcat:distribution.advanced.dct:accessRights'
        ) + ' (optional)',
      dropdown_dpiV3: true,
      placeholder: t(
        'message.dataupload.datasets.dcat:distribution.advanced.dct:accessRights'
      )+' hinzufügen',
      inputFieldSize: 'large',
      modelValue: rightsTextVal,
      defaultInput: !showDeleteButton,
    }"
    :data="rightsOptions"
    @deleteDropdownField="deleteAccessRightsField"
  />
</template>
<script setup>
import InputField from "../InputField.vue";
import {
  onMounted,
  getCurrentInstance,
  ref,
  defineProps,
  defineEmits,
  watch,
} from "vue";
import { getAccessRights } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import { useI18n } from "vue-i18n";
import { useFormValues } from "../../../composables/useDpiFormValues";
const { t } = useI18n();
const { formValues } = useFormValues();
const rightsOptions = ref([]);
onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getAccessRights(env.api.baseUrl);
    console.log(response);
    rightsOptions.value = response.map((item) => ({
      "@value": item.pref_label['de'],
      label: item.pref_label['de'],
      uri: item.resource,
    }));
    // console.log(rightsOptions.value);
  } catch (error) {
    console.error("Failed to load planned availability data", error);
  }
});

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  accessRightsProp: { type: String, default: "" },
});


const rightsTextVal = ref(props.accessRightsProp);
const emits = defineEmits(["addAccessRights", "deleteButtonClicked"]);

const handleInput = (event, field) => {

let value = {
  'uri' : rightsOptions.value.find((item) => item["@value"] === event).uri || "",
  'label': rightsOptions.value.find((item) => item["@value"] === event).label || ""
}
  emits("addAccessRights", value, props.distributionId);
};

watch(
  () => props.accessRightsProp,
  (newVal) => {
    rightsTextVal.value = newVal;
  }
);

const deleteAccessRightsField = () => {
  console.log("delete clicked");
  emits("deleteButtonClicked", props.distributionId);
};
</script>
