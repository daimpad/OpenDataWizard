<template>
  <InputField
    @input="handleInput($event, 'dcatde:licenseAttributionByText')"
    v-model="nameTextAttrByClauses"
    :addOnText="false"
    :datePicker="false"
    :infoIcon="false"
    :placeholder="$t(
        'message.dataupload.datasets.dcat:distribution.advanced.licenseAttrByText-placeholder'
      )"
    :preIcon="false"
    inputFieldSize="large"
    :initialHintText="false"
    :label="
      $t(
        'message.dataupload.datasets.dcat:distribution.advanced.dcatde:licenseAttributionByText'
      )+ ' (optional)'
    "
    :showEndIcon="false"
    :defaultInput=!showDeleteButton
    @deleteButtonClicked="deleteModifiedField"
  />
</template>
<script setup>
import InputField from "../InputField.vue";
import {ref, defineProps, defineEmits, watch } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  nameTextByClauses: { type: String, default: "" },
});

const nameTextAttrByClauses = ref(props.nameTextByClauses)

const emits = defineEmits(["addLicenseAttrByText", "deleteButtonClicked"]);

const handleInput = (event, field) => {
  emits("addLicenseAttrByText", event.target.value, props.distributionId);
};

watch(() => props.nameTextByClauses, (newVal) => {
  nameTextAttrByClauses.value = newVal;
});

const deleteModifiedField = () => {
  console.log("delete clicked");
  emits("deleteButtonClicked", props.distributionId);
};
</script>

