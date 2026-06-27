<template>
  <InputField
    @input="handleInput($event, 'dct:issued')"
    v-model="issuedDate"
    :defaultInput=!showDeleteButton
    :addOnText="false"
    :eraseable="false"
    :datePicker="true"
    :infoIcon="false"
    placeholder="TT/MM/JJJJ"
    :label="
      $t(
        'message.dataupload.datasets.dcat:distribution.advanced.dct:issued'
      )+ ' (optional)'
    "
    :preIcon="false"
    :initialHintText="false"
    :showEndIcon="false"
    inputType="date"
    @deleteButtonClicked="deleteIssuedField"
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

const issuedDate = ref(props.modelValue);

const emits = defineEmits(["addIssuedDate", "deleteButtonClicked"]);

const handleInput = (event, field) => {
  emits("addIssuedDate", event.target.value, props.distributionId);
};

const deleteIssuedField = () => {
    console.log("delete clicked")
    emits("deleteButtonClicked", props.distributionId)
}

watch(() => props.modelValue, (newVal) => {
  issuedDate.value = newVal;
});
</script>
