<template>
  <div class="dpiV3InnerComponentWrap">
    <h4>{{ $t("message.dataupload.datasets.dct:description.title") }}</h4>
    <div class="copy-large-regular">
      {{ $t("message.dataupload.datasets.dct:description.description") }}
    </div>
    <TextAreaV3
      @input="handleInput"
      class="dpiV3_input-field"
      :hint="false"
      :isDescription="true"
      :label="$t('message.dataupload.datasets.dct:description.input.title')"
      :placeholder="
        $t('message.dataupload.datasets.dct:description.input.placeholder')
      "
      :showError="chosenItems[0].isValid === false"
    ></TextAreaV3>
    <div
      class="dpiV3_errormsgWrapper"
      v-if="chosenItems.find((obj) => obj.isValid === false)"
    >
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular"
        >Bitte geben Sie eine Beschreibung ein, bevor Sie fortfahren.</span
      >
    </div>
  </div>
</template>
<script setup>
import TextAreaV3 from "../HappyFlowComponents/ui/TextAreaV3.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { ref } from "vue";
import { useEditModeInfo } from "../composables";

const props = defineProps({
  context: Object,
});
let chosenItems = ref([{ isValid: "unset", "@value": "", "@language": "de" }]);
const handleInput = (e) => {
  if (e.target.value != "") {
    chosenItems.value[0].isValid = true;
  } else chosenItems.value[0].isValid = false;
  chosenItems.value[0]["@value"] = e.target.value;
};

const { isEditMode } = useEditModeInfo();
if (!isEditMode.value) props.context.node.input(chosenItems);
</script>
<style scoped>
.formkit-input {
  all: unset !important;
}

.dpiV3_input-field {
  display: flex;
  height: 268px;
  flex-direction: column;
  align-items: flex-start;
  align-self: stretch;
  width: unset;
}

.dpiV3_errormsgWrapper {
  display: flex;
  gap: 6px;
  width: auto;
  position: absolute;
  right: 50px;
  bottom: 104px;
  color: var(--text-error, #a9242c);
}

.dpiV3_errormsgWrapper span {
  color: var(--text-error, #a9242c);
  text-align: right;
}
</style>
