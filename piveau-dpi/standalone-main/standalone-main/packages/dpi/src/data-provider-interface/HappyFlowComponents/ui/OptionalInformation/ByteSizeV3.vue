<template>
  <InputField
    @input="handleInput($event, 'dcat:byteSize')"
    v-model="ByteSizeText"
    :addOnText="true"
    :trailingText="true"
    :placeholder="$t(
        'message.dataupload.datasets.dcat:distribution.advanced.byteSize-placeholder'
      )"
    :label="
      $t(
        'message.dataupload.datasets.dcat:distribution.advanced.dcat:byteSize'
      )+ ' (optional)'
    "
    :datePicker="false"
    :infoIcon="false"
    trailing_text="bytes"
    :showEndIcon="false"
    :initialHintText="false"
    inputType="number"
    :defaultInput=!showDeleteButton
    @deleteButtonClicked="deleteModifiedField"
  />
</template>
<script setup>
import InputField from "../InputField.vue";
import { ref, defineProps, defineEmits, watch } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  byteSizeProp: { type: String, default: "" },
});

const ByteSizeText = ref(props.byteSizeProp);

const emits = defineEmits(["addByteSize", "deleteButtonClicked"]);

const handleInput = (event, field) => {
  emits("addByteSize", event.target.value, props.distributionId);
};

watch(
  () => props.byteSizeProp,
  (newVal) => {
    ByteSizeText.value = newVal;
  }
);

const deleteModifiedField = () => {
  console.log("delete clicked");
  emits("deleteButtonClicked", props.distributionId);
};
</script>
