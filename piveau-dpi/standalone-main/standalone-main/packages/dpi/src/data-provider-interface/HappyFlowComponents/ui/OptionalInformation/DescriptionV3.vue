<template>
  <div class="dpiV3_modified">
    <TextAreaV3
      @input="handleInput"
      @deleteClicked="handleDelete"
      :showDeleteButton="showDeleteButton"
      :hint="false"
      :label="
      $t(
        'message.dataupload.datasets.dcat:distribution.recommended.dct:description'
      )+ ' (optional)'
    "
      :placeholder="$t(
        'message.dataupload.datasets.dcat:distribution.recommended.description-placeholder'
      )"
      v-model="descriptionV3"
    ></TextAreaV3>
  </div>
</template>
<script setup>
import TextAreaV3 from "../TextAreaV3.vue";
import { ref, defineEmits, defineProps, watch } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const emits = defineEmits(["addDescription", "deleteDescription"]);
const props = defineProps({
     descriptionText: { type: String, default: "" },
     distributionId: { type: Number, required: false },
     showDeleteButton: {type: Boolean, default: false}
})

const descriptionV3 = ref(props.descriptionText)
const handleInput = (event) => {
    descriptionV3.value = event.target.value;
    
    emits("addDescription", descriptionV3.value, props.distributionId); 
};

const handleDelete = () => {
    emits("deleteDescription")
}

watch(() => props.descriptionText, (newVal) => {
    descriptionV3.value = newVal;
});
</script>
