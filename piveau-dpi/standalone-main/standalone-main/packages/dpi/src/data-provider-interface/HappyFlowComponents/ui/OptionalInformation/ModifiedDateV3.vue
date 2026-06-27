<template>
  <InputField
    @input="handleInput($event, 'dct:modified')"
    v-model="modifiedDate"
    :defaultInput="!showDeleteButton"
    :addOnText="false"
    :eraseable="false"
    :datePicker="true"
    :infoIcon="false"
    placeholder="TT/MM/JJJJ"
    :label="
      $t(
        'message.dataupload.datasets.dcat:distribution.recommended.dct:modified'
      )+ ' (optional)'
    "
    :preIcon="false"
    :initialHintText="false"
    :showEndIcon="false"
    inputType="date"
    @deleteButtonClicked="deleteModifiedField"
  ></InputField>
</template>
<script setup>
import InputField from "../InputField.vue";
import { defineProps, defineEmits, watch, ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  modelValue: { type: String, default: "" },
});
const modifiedDate = ref(props.modelValue);

const emits = defineEmits(["addModifiedDate", "deleteButtonClicked"]);

const handleInput = (event, field) => {
  emits("addModifiedDate", event.target.value, props.distributionId);
};

const deleteModifiedField = () => {
  emits("deleteButtonClicked", props.distributionId);
};

watch(() => props.modelValue, (newVal) => {
  modifiedDate.value = newVal;
});
</script>
