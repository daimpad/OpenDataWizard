<template>
  <div class="V3-typography dpiV3_Frame_3841">
    <h4>
      {{ $t("message.dataupload.datasets.dct:title.title") }}
    </h4>
    <div class="copy-large-regular">
      {{ $t("message.dataupload.datasets.dct:title.description") }}
    </div>

    <InputField
      @input="handleInput"
      :addOnText="false"
      :datePicker="false"
      :infoIcon="false"
      placeholder="Geben Sie Ihrem Datensatz einen Titel..."
      :preIcon="false"
      :initialHintText="false"
      label="Titel"
      :showEndIcon="false"
      :showError="chosenItems[0].isValid === false"
    ></InputField>
    <div
      class="dpiV3_errormsgWrapper"
      v-if="chosenItems.find((obj) => obj.isValid === false)"
    >
      <PhWarning :size="16" weight="fill" />
      <span class="copy-mini-regular"
        >Bitte geben Sie einen Titel ein, bevor Sie fortfahren.</span
      >
    </div>
  </div>
</template>
<script setup>
import { useI18n } from "vue-i18n";
import "../config/styles/variables.css";
import "../config/styles/typography.css";
import { ref } from "vue";
import InputField from "../HappyFlowComponents/ui/InputField.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { useEditModeInfo } from "../composables";

const { t } = useI18n();
const { isEditMode } = useEditModeInfo();

const props = defineProps({
  context: Object,
});
let chosenItems = ref([{ isValid: "unset", "@value": "", "@language": "de" }]);
if (!isEditMode.value) props.context.node.input(chosenItems);
const handleInput = (e) => {
  if (e.target.value != "") {
    chosenItems.value[0].isValid = true;
  } else chosenItems.value[0].isValid = false;
  chosenItems.value[0]["@value"] = e.target.value;
};
</script>

<style scoped>
.dpiV3_Frame_3841 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
}

.dpiV3_Content_Titel {
  display: flex;
  min-width: 448px;
  max-width: 636px;

  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-8, 64px);
  flex: 1 0 0;
  align-self: stretch;
  color: var(--neutral-80, #3d4952);
}

.dpiV3_errormsgWrapper {
  width: 350px;
  position: absolute;
  right: 10px;
  bottom: 104px;
  color: var(--text-error, #a9242c);

  span {
    color: var(--text-error, #a9242c);
    text-align: right;
  }
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
