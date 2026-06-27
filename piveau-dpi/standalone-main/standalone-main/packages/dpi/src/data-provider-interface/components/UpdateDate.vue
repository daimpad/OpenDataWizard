<template>
  <div class="V3-typography">
    <div class="dpiV3InnerComponentWrap">
      <h4>
        {{ $t("message.dataupload.datasets.dct:modified.title") }}
      </h4>
      <div class="copy-large-regular">
        {{ $t("message.dataupload.datasets.dct:modified.description") }}
      </div>
      <div class="input-container">
        <InputField
          @input="handleInput"
          :defaultInput="true"
          :addOnText="false"
          :eraseable="false"
          :datePicker="true"
          :infoIcon="false"
          placeholder="TT/MM/JJJJ"
          label="Aktualisierungsdatum"
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          :showEndIcon="false"
          inputType="date"
          :showError="chosenItems[0].isValid === false"
          :modelValue="todayDate"
        ></InputField>
        <div class="dpiV3_errormsgWrapper" v-if="chosenItems.find(obj => obj.isValid === false)">
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ errorMessage }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { PhWarning } from "@phosphor-icons/vue";
import { ref, onMounted } from "vue";
import InputField from "../HappyFlowComponents/ui/InputField.vue";
import { useEditModeInfo } from "../composables";

const props = defineProps({
  context: Object,
});

// today's date in YYYY-MM-DD format
const today = new Date();
const year = today.getFullYear();
const month = String(today.getMonth() + 1).padStart(2, '0');
const day = String(today.getDate()).padStart(2, '0');

// @type is always 'date' in this step (differs for most of the otehr date inputs)
let chosenItems = ref([{ isValid: 'unset' , '@type': 'http://www.w3.org/2001/XMLSchema#date'}]);
let errorMessage = ref("Bitte geben Sie ein Aktualisierungsdatum ein, bevor Sie fortfahren.");

const { isEditMode } = useEditModeInfo();
if (!isEditMode.value) props.context.node.input(chosenItems);

const handleInput = (e) => {
  const value = e.target.value;

  if (value === '') {
    chosenItems.value[0].isValid = false;
    errorMessage.value = "Bitte geben Sie ein Aktualisierungsdatum ein, bevor Sie fortfahren.";
    chosenItems.value[0]['@value'] = value;
    return;
  }

  const inputDate = new Date(value);
  if (isNaN(inputDate.getTime())) {
    chosenItems.value[0].isValid = false;
    errorMessage.value = "Bitte geben Sie ein gültiges Datum ein.";
    chosenItems.value[0]['@value'] = value;
    return;
  }
  
  // Check if year is before 1950
  if (inputDate.getFullYear() < 1950) {
    chosenItems.value[0].isValid = false;
    errorMessage.value = "Das Jahr darf nicht vor 1950 liegen.";
    chosenItems.value[0]['@value'] = value;
    return;
  }
  
  // Check if date is in the future
  const todayWithoutTime = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const inputWithoutTime = new Date(inputDate.getFullYear(), inputDate.getMonth(), inputDate.getDate());
  
  if (inputWithoutTime > todayWithoutTime) {
    chosenItems.value[0].isValid = false;
    errorMessage.value = "Das Datum darf nicht in der Zukunft liegen.";
    chosenItems.value[0]['@value'] = value;
    return;
  }
  
  chosenItems.value[0].isValid = true;
  chosenItems.value[0]['@value'] = value;
};
</script>

<style scoped>
.input-container {
  position: relative;
  width: 100%;
}

.dpiV3_errormsgWrapper {
  display: flex;
  align-items: center;
  gap: 6px;
  width: auto;
  position: absolute;
  right: 0;
  bottom: -25px;
  color: var(--text-error, #A9242C);

  span {
    color: var(--text-error, #A9242C);
    text-align: right;
  }
}
</style>