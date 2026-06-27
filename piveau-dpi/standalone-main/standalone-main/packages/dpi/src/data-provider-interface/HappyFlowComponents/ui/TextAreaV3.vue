<template>
  <div class="dpiV3_input-field V3-typography">
    <div class="dpiV3_InputFieldBase">
      <div class="dpiV3_InputWithLabel">
        <div class="dpiV3_Top">
          <div class="dpiV3_Label">
            <label class="dpiV3_label copy-small-regular">{{
              props.label
            }}</label>
            <TextButtonSmall
              v-if="showDeleteButton"
              buttonText="löschen"
              @click="deleteButton"
              tabindex="0"
            />
          </div>
        </div>
        <div
          class="dpiV3_Input"
          :class="{
            isDescription: isDescription,
            dpiV3_disabled: isDisabled,
            'error-state': showError && !isInput_boxFocused && !isFilled,
            'focused-error-state': showError && isInput_boxFocused,
            'filled-error-state': showError && isFilled,
          }"
          @mouseenter="handleMouseEnterInput_box"
          @mouseleave="handleMouseLeaveInput_box"
          @mousedown="handleMouseDownInput_box"
          :style="{ 'box-shadow': input_box_box_shadow }"
        >
          <textarea
            ref="inputRef"
            v-model="inputText"
            type="text"
            class="dpiV3_textArea copy-large-regular"
            :disabled="isDisabled"
            :class="{
              'error-state': showError,
              dpiV3_input_disabled: isDisabled,
            }"
            :placeholder="placeholder"
            @focus="handleInputFocus"
            @blur="handleInputBlur"
            @mousedown="handleInputMouseDown"
          ></textarea>
        </div>
      </div>

      <div class="dpiV3_hintText copy-small-regular" v-if="hint && !showError">
        <span>This is a supporting message.</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineEmits, defineProps } from "vue";
import "../../config/styles/variables.css";
import "../../config/styles/typography.css";
import TextButtonSmall from "./TextButtonSmall.vue";

let isInput_boxFocused = ref(false);

let isFilled = ref(false);
const inputRef = ref(null);
let isCalendarFocused = ref(false);
let input_box_box_shadow = ref("inset 0 0 0 1px var(--neutral-30, #687178)");
let isInputFocused = ref(false);
let isInput_box_hovered = ref(false);

const props = defineProps({
  text: {
    type: Object,
    default: () => {},
  },
  isDisabled: {
    type: Boolean,
    default: false,
  },
  isDescription: {
    type: Boolean,
    default: false,
  },
  showError: {
    type: Boolean,
    default: false,
  },
  label: {},
  placeholder: {},
  hint: {},
  modelValue: { type: String, default: "" },
  showDeleteButton: { type: Boolean, default: false },
});

const inputText = ref(props.modelValue);
const emit = defineEmits(["update:modelValue", "deleteClicked"]);

watch(
  () => props.modelValue,
  (newVal) => {
    inputText.value = newVal;
  }
);

watch(inputText, (newVal) => {
  emit("update:modelValue", newVal);
});

const deleteButton = () => {
  //inputText.value = ""
  emit("deleteClicked");
};

const handleInputFocus = () => {
  isCalendarFocused.value = false;
  isInputFocused.value = true;
  isInput_boxFocused.value = true;
  input_box_box_shadow.value = "0 0 0 2px var(--blue-70, #009FE3)";

  // logVarStatus("handleInputFocus");
};

const handleInputBlur = () => {
  isInputFocused.value = false;
  isInput_boxFocused.value = false;
  input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-30, #687178)";

  // logVarStatus("handleInputBlur");
};

const handleMouseEnterInput_box = () => {
  isInput_box_hovered.value = true;
  // show hover style
  input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-60, #687178)";

  // show focus style
  if (isInputFocused.value) {
    input_box_box_shadow.value = "0 0 0 2px var(--blue-70, #009FE3)";
  }

  // logVarStatus("handleMouseEnterInput_box");
};

const handleMouseLeaveInput_box = () => {
  isInput_box_hovered.value = false;
  input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-30, #687178)";

  if (isInputFocused.value) {
    input_box_box_shadow.value = "0 0 0 2px var(--blue-70, #009FE3)";
  }

  // logVarStatus("handleMouseLeaveInput_box");
};

const handleMouseDownInput_box = () => {
  if (isInput_boxFocused.value) {
    input_box_box_shadow.value = "0 0 0 2px var(--blue-70, #009FE3)";
  }

  // logVarStatus("handleMouseDownInput_box");
};

/* mouse down on input field */
const handleInputMouseDown = () => {
  input_box_box_shadow.value = "inset 0 0 0 2px var(--Focused)";

  // logVarStatus("handleInputMouseDown");
};

const logVarStatus = (methodName) => {
  console.log("****-------------****");
  console.log(" METHOD --- " + methodName + " ---");
  console.log("isInput_boxFocused:" + isInput_boxFocused.value);
  console.log("isInputFocused:" + isInputFocused.value);
  console.log("isInput_box_hovered:" + isInput_box_hovered.value);

  console.log("input_box_box_shadow.value : " + input_box_box_shadow.value);
};
</script>

<style scoped>
.isDescription{
  width: 600px;
}
.dpiV3_Input {
  display: flex;
  padding: var(--Spacing-2, 8px) var(--Spacing-3, 16px);
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  flex: 1 0 0;
  align-self: stretch;

  border-radius: var(--Border-Radius, 8px);
  background: var(--neutral-0, #fff);
}

.dpiV3_InputWithLabel {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
  align-self: stretch;
}

.dpiV3_input-field {
  overflow: visible;
  display: flex;
  width: 100%;
  height: 178px;
  flex-direction: column;
  align-items: flex-start;
  flex-shrink: 0;
}

.dpiV3_InputFieldBase {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
  align-self: stretch;
}

.dpiV3_Top {
  display: flex;
  align-items: flex-end;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
}

.dpiV3_Label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}
.dpiV3_label {
  color: var(--neutral-80, #3d4952);
  margin-bottom: 0px;
  font-style: normal;
}

/* Default error state */
.dpiV3_Input.error-state {
  box-shadow: inset 0 0 0 1px var(--fill-error) !important;
  background: var(--neutral-0, #fff);
}

/* Focused error state */
.dpiV3_Input.focused-error-state {
  box-shadow: 0 0 0 2px var(--fill-error, #e53b46) !important;
  background: var(--neutral-0, #fff);
}

.dpiV3_textArea.error-state {
  caret-color: var(--red-70);
}

.dpiV3_textArea {
  padding: 0px;
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
  align-self: stretch;
  color: var(--neutral-100, #687178);

  font-style: normal;
  border: none;
  outline: none;
  resize: none;
  caret-color: var(--blue-60);
}

.dpiV3_textArea::placeholder {
  color: var(--neutral-60, #687178);
}

.dpiV3_hintText {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: flex-start;
  align-self: stretch;
  color: var(--neutral-80, #3d4952);
  font-style: normal;
}

.dpiV3_errorText {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: flex-start;
  align-self: stretch;
  font-style: normal;
  color: var(--text-error, #a9242c);
}

.dpiV3_disabled {
  display: flex;
  padding: var(--Spacing-2, 8px) var(--Spacing-3, 16px);
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  flex: 1 0 0;
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);

  background: var(--neutral-10, #f1f1f3);
  pointer-events: none;
  user-select: none;
  outline: none;
  tabindex: -1;
}

.dpiV3_input_disabled {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
  align-self: stretch;
  pointer-events: none;
  user-select: none;
  outline: none;
  tabindex: -1;
  background: var(--neutral-10, #f1f1f3);
}
</style>
