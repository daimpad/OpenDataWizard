<template>
  <div class="dpiV3_input-field">
    <div v-if="!addOnText" class="dpiV3_InputFieldBase">
      <div class="dpiV3_Top">
        <div class="dpiV3_Lebel">
          <label class="dpiV3_label" v-if="label">{{ label }}</label>
          <span class="dpiV3_Tooltip_Wrapper">
            <PhInfo
              v-if="infoIcon"
              weight="fill"
              class="dpiV3_labelIcon"
              :size="16"
              :color="'var(--neutral-60, #3D4952)'"
              @mouseenter="showTooltip = true"
              @mouseleave="showTooltip = false"
            />
            <span v-if="showTooltip" class="dpiV3_toolTip copy-mini-regular">
              {{ tooltip_text }}
            </span>
          </span>
        </div>
        <TextButtonSmall
          v-if="!defaultInput"
          buttonText="löschen"
          @click="eraseText()"
          class="dpiV3_deleteButton"
          tabindex="0"
        />
      </div>

      <div
        class="dpiV3_Input_box"
        :class="{
          dpiV3_disabled: isDisabled,
          'calendar-focused': isCalendarFocused,
          'error-state': showError && !isInput_boxFocused && !isFilled,
          'focused-error-state': showError && isInput_boxFocused,
          'filled-error-state': showError && isFilled,
        }"
        :style="{ 'box-shadow': input_box_box_shadow }"
        @mouseenter="handleMouseEnterInput_box"
        @mouseleave="handleMouseLeaveInput_box"
        @mousedown="handleMouseDownInput_box"
      >
        <div class="dpiV3_Content">
          <PhMagnifyingGlass
            v-if="preIcon"
            class="dpiV3_iconLeft"
            :size="24"
            :color="'var(--neutral-60, #3D4952)'"
          />
          <input
            v-if="!dropdown_dpiV3"
            ref="inputRef"
            v-model="inputText"
            :type="inputType"
            class="dpiV3_Text"
            :disabled="isDisabled"
            :class="{
              'error-state': showError,
              dpiV3_input_disabled: isDisabled,
            }"
            :placeholder="placeholder"
            @focus="handleInputFocus"
            @blur="handleInputBlur"
            @mousedown="handleInputMouseDown"
          />

          <input
            v-if="dropdown_dpiV3"
            ref="inputRef"
            v-model="inputText"
            :type="inputType"
            class="dpiV3_Text"
            :disabled="isDisabled"
            :class="{
              'error-state': showError,
              dpiV3_input_disabled: isDisabled,
              'no-cursor': dropdown_dpiV3 && !autocomplete,
            }"
            :placeholder="placeholder"
            @focus="handleInputFocus"
            @blur="handleInputBlur"
            @mousedown="handleInputMouseDown"
          />
          <PhQuestion
            v-if="showEndIcon && !showError && !dropdown_dpiV3"
            class="dpiV3_iconRight"
            :size="24"
            :color="'var(--neutral-60, #3D4952)'"
          />
          <PhWarningCircle
            v-if="showError"
            class="dpiV3_iconRight"
            :size="24"
            :color="'var(--fill-error, #E53B46)'"
          />
          <PhCaretDown
            v-if="showEndIcon && dropdown_dpiV3 && !dropDownExpanded"
            class="dpiV3_iconRight"
            :color="'var(--neutral-60, #3D4952)'"
            :size="24"
          />
          <PhCaretUp
            v-if="showEndIcon && dropdown_dpiV3 && dropDownExpanded"
            class="dpiV3_iconRight"
            :color="'var(--neutral-60, #3D4952)'"
            :size="24"
          />
        </div>
        <div v-if="props.datePicker" class="dpiV3_inputButton">
          <PhCalendar
            class="dpiV3_calendarIcon"
            :size="24"
            :color="questionColor"
          />
          <div
            @click="openDatepicker"
            class="dpiV3_Rectangle1"
            @focus.stop="handleButtonFocus"
            @mousedown="calendarButtonPressed = true"
            @mouseup="calendarButtonPressed = false"
            @mouseleave="calendarButtonPressed = false"
            @keydown.space.prevent="calendarButtonPressed = true"
            @keyup.space="calendarButtonPressed = false"
            @keydown.enter.prevent="calendarButtonPressed = true"
            @keyup.enter="calendarButtonPressed = false"
            :class="{ dpiV3_Pressed: calendarButtonPressed }"
          ></div>
        </div>
        <div v-if="props.timePicker" class="dpiV3_inputButton">
          <PhClock
            class="dpiV3_calendarIcon"
            :size="24"
            :color="questionColor"
          />
          <div
            @click="openTimepicker"
            class="dpiV3_Rectangle1"
            @focus.stop="handleButtonFocus"
            @mousedown="calendarButtonPressed = true"
            @mouseup="calendarButtonPressed = false"
            @mouseleave="calendarButtonPressed = false"
            @keydown.space.prevent="calendarButtonPressed = true"
            @keyup.space="calendarButtonPressed = false"
            @keydown.enter.prevent="calendarButtonPressed = true"
            @keyup.enter="calendarButtonPressed = false"
            :class="{ dpiV3_Pressed: calendarButtonPressed }"
          ></div>
        </div>
      </div>
      <div class="dpiV3_hintText" v-if="!showError && initialHintText">
        <span>{{ supportingHintMessage }}</span>
      </div>
      <div class="dpiV3_errorText" v-if="showError && initialHintText">
        <span>{{ error_message }}</span>
      </div>
    </div>
    <!------------------------------------------------------>
    <!--------------------  Add On Text ------------------->
    <!------------------------------------------------------>
    <div v-if="addOnText" class="dpiV3_InputFieldBase">
      <div class="dpiV3_Top_leading_text">
        <div class="dpiV3_Lebel">
          <label class="dpiV3_label">{{ label }}</label>
          <PhInfo
            v-if="infoIcon"
            weight="fill"
            class="dpiV3_labelIcon"
            :size="16"
            :color="'var(--neutral-60, #3D4952)'"
          />
        </div>
        <TextButtonSmall
          v-if="!defaultInput"
          buttonText="löschen"
          @click="eraseText()"
          class="dpiV3_deleteButton"
          tabindex="0"
        />
      </div>
      <div class="dpiV3_Input">
        <div
          v-if="!trailingText"
          class="dpiV3_Add-on"
          :class="{
            dpiV3_input_disabled: isDisabled,
          }"
        >
          <div class="dpiV3_Text-Add-On">
            {{ addOnLeadingText }}
          </div>
        </div>
        <!---------------------------------->
        <!-- Text_input, leading text -->
        <div
          v-if="!trailingText"
          class="dpiV3_Text_input"
          :class="{
            dpiV3_input_disabled: isDisabled,
            'calendar-focused': isCalendarFocused,
            'error-state': showError && !isInput_boxFocused && !isFilled,
            'focused-error-state': showError && isInput_boxFocused,
            'filled-error-state': showError && isFilled,
          }"
          :style="{
            'box-shadow': input_box_box_shadow,
            border: text_input_border_leading_text,
          }"
          @mouseenter="handleMouseEnterInput_box"
          @mouseleave="handleMouseLeaveInput_box"
          @mousedown="handleMouseDownInput_box"
        >
          <div class="dpiV3_Content-leading-Text">
            <input
              ref="inputRef"
              v-model="inputText"
              :type="inputType"
              class="dpiV3_Text"
              :disabled="isDisabled"
              :class="{
                'error-state': showError,
                dpiV3_input_disabled: isDisabled,
              }"
              :placeholder="placeholder"
              @focus="handleInputFocus"
              @blur="handleInputBlur"
              @mousedown="handleInputMouseDown"
            />
          </div>
          <PhWarningCircle
            v-if="showError"
            class="dpiV3_iconRight"
            :size="24"
            :color="'var(--fill-error, #E53B46)'"
          />
          <PhQuestion
            v-if="showEndIcon && !showError"
            class="dpiV3_iconRight"
            :size="24"
            :color="'var(--neutral-60, #3D4952)'"
          />
        </div>
        <!---------------------------------->
        <!-- Text_input, trailing text -->
        <div
          v-if="trailingText"
          class="dpiV3_Text_input-trailing"
          :class="{
            dpiV3_input_disabled: isDisabled,
            'calendar-focused': isCalendarFocused,
            'error-state': showError && !isInput_boxFocused && !isFilled,
            'focused-error-state': showError && isInput_boxFocused,
            'filled-error-state': showError && isFilled,
          }"
          :style="{
            'box-shadow': input_box_box_shadow,
            border: text_input_border_leading_text,
          }"
          @mouseenter="handleMouseEnterInput_box"
          @mouseleave="handleMouseLeaveInput_box"
          @mousedown="handleMouseDownInput_box"
        >
          <div class="dpiV3_Content-leading-Text">
            <input
              ref="inputRef"
              v-model="inputText"
              :type="inputType"
              class="dpiV3_Text"
              :disabled="isDisabled"
              :class="{
                'error-state': showError,
                dpiV3_input_disabled: isDisabled,
              }"
              :placeholder="placeholder"
              @focus="handleInputFocus"
              @blur="handleInputBlur"
              @mousedown="handleInputMouseDown"
            />
          </div>
          <PhWarningCircle
            v-if="showError"
            class="dpiV3_iconRight"
            :size="24"
            :color="'var(--fill-error, #E53B46)'"
          />
          <PhQuestion
            v-if="showEndIcon && !showError"
            class="dpiV3_iconRight"
            :size="24"
            :color="'var(--neutral-60, #3D4952)'"
          />
        </div>
        <div
          v-if="trailingText"
          class="dpiV3_Add-on-trailing"
          :class="{
            dpiV3_input_disabled: isDisabled,
          }"
        >
          <div class="dpiV3_Text-Add-On">{{ trailing_text }}</div>
        </div>
      </div>
      <!------------- addOn text finished -------------->
      <!------------------------------------------------>

      <div class="dpiV3_hintText" v-if="!showError && initialHintText">
        <span>This is a supporting message.</span>
      </div>
      <div class="dpiV3_errorText" v-if="showError && initialHintText">
        <span>{{ error_message }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  PhInfo,
  PhMagnifyingGlass,
  PhQuestion,
  PhCalendar,
  PhWarningCircle,
  PhCaretDown,
  PhCaretUp,
  PhClock,
} from "@phosphor-icons/vue";
import TextButtonSmall from "./TextButtonSmall.vue";
import { ref, defineEmits, watch } from "vue";
import "../../config/styles/variables.css";
import "../../config/styles/typography.css";
import { computed } from "vue";

let questionColor = ref("var(--neutral-60)");
let isInput_boxFocused = ref(false);
let calendarButtonPressed = ref(false);
let isFilled = ref(false);
const inputRef = ref(null);
let isCalendarFocused = ref(false);
let input_box_box_shadow = ref("inset 0 0 0 1px var(--neutral-30, #687178)");
let isInputFocused = ref(false);
let isInput_box_hovered = ref(false);
let text_input_border_leading_text = ref();
const showTooltip = ref(false);

const props = defineProps({
  supportingHintMessage: { type: String, default: "hint message" },
  isDisabled: {
    type: Boolean,
    default: false,
  },
  inputType: {
    type: String,
    default: "text",
  },
  datePicker: {
    type: Boolean,
  },
  timePicker: {
    type: Boolean,
  },
  infoIcon: {
    type: Boolean,
  },
  eraseable: {
    type: Boolean,
  },
  addOnText: {
    type: Boolean,
  },
  trailingText: {
    type: Boolean,
    default: false,
  },
  defaultInput: {
    type: Boolean,
    default: true,
  },
  showError: {
    type: Boolean,
    default: false,
  },
  initialHintText: {
    type: Boolean,
    default: true,
  },
  preIcon: {
    type: Boolean,
    default: true,
  },
  label: {
    type: String,
    default: "Label",
  },
  dropdown_dpiV3: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: "Standard_input",
  },
  inputFieldSize: {
    type: String,
    default: "medium",
    validator: (value) => ["medium", "large"].includes(value),
  },
  modelValue: { type: String, default: "" },
  dropDownExpanded: {
    type: Boolean,
    default: false,
  },
  showEndIcon: {
    type: Boolean,
    default: true,
  },
  addOnLeadingText: {
    type: String,
    default: "http://",
  },
  trailing_text: {
    type: String,
    default: "text",
  },
  autocomplete: {
    type: Boolean,
    default: false,
  },
  tooltip_text: {
    type: String,
    default: "info",
  },
  error_message: {
    type: String,
    default: "error",
  },
});

const emit = defineEmits(["update:modelValue", "deleteButtonClicked"]);

const inputText = ref(props.modelValue);

watch(
  () => props.modelValue,
  (newValue) => {
    inputText.value = newValue;
  }
);

watch(inputText, (newValue) => {
  emit("update:modelValue", newValue);
});

const openDatepicker = () => {
  if (inputRef.value) {
    inputRef.value.showPicker();
  }
};
const openTimepicker = () => {
  if (inputRef.value) {
    inputRef.value.showPicker();
  }
};

const eraseText = () => {
  //inputText.value = "";
  emit("deleteButtonClicked");
};

const handleInputFocus = () => {
  isCalendarFocused.value = false;
  isInputFocused.value = true;
  isInput_boxFocused.value = true;
  input_box_box_shadow.value = "0 0 0 2px var(--Focused)";

  if (props.addOnText) {
    input_box_box_shadow.value = "0 0 0 2px var(--blue-60, #009FE3)";
    text_input_border_leading_text = "none";
  }

  // avoids text selection when focused
  if (inputRef.value) {
    inputRef.value.selectionStart = inputRef.value.selectionEnd;
  }

  //logVarStatus("handleInputFocus");
};

const handleInputBlur = () => {
  isInputFocused.value = false;
  isInput_boxFocused.value = false;
  input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-30, #687178)";

  //logVarStatus("handleInputBlur");
};

const handleButtonFocus = () => {
  isCalendarFocused.value = true;
  isInput_boxFocused.value = false;
  if (isInput_box_hovered.value) {
    input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-60, #687178)";
  }

  //logVarStatus("handleButtonFocus");
};

const handleMouseEnterInput_box = () => {
  isInput_box_hovered.value = true;
  // show hover style
  input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-60, #687178)";

  if (props.addOnText) {
    text_input_border_leading_text = "none";
  }

  // show focus style
  if (isInputFocused.value) {
    input_box_box_shadow.value = "0 0 0 2px var(--Focused)";
    if (props.addOnText) {
      input_box_box_shadow.value = "0 0 0 2px var(--blue-blue-60, #009FE3)";
      text_input_border_leading_text = "none";
    }
  }

  //logVarStatus("handleMouseEnterInput_box");
};

const handleMouseLeaveInput_box = () => {
  isInput_box_hovered.value = false;
  input_box_box_shadow.value = "inset 0 0 0 1px var(--neutral-30, #687178)";

  if (props.addOnText) {
    text_input_border_leading_text = "none";
  }

  if (isInputFocused.value) {
    input_box_box_shadow.value = "0 0 0 2px var(--Focused, #687178)";
    if (props.addOnText) {
      input_box_box_shadow.value = "0 0 0 2px var(--blue-blue-60, #009FE3)";
      text_input_border_leading_text = "none";
    }
  }

  //logVarStatus("handleMouseLeaveInput_box");
};

const handleMouseDownInput_box = () => {
  if (isInput_boxFocused.value) {
    input_box_box_shadow.value = "0 0 0 2px var(--Focused)";
    if (props.addOnText) {
      input_box_box_shadow.value = "0 0 0 2px var(--blue-blue-60, #009FE3)";
      text_input_border_leading_text = "none";
    }
  }

  //logVarStatus("handleMouseDownInput_box");
};

/* mouse down on input_field */
const handleInputMouseDown = () => {
  input_box_box_shadow.value = "0 0 0 2px var(--Focused)";
  if (props.addOnText) {
    input_box_box_shadow.value = "0 0 0 2px var(--blue-blue-60, #009FE3)";
    text_input_border_leading_text = "none";
  }

  //logVarStatus("handleInputMouseDown");
};

const logVarStatus = (methodName) => {
  console.log("****-------------****");
  console.log(" METHOD --- " + methodName + " ---");
  console.log("isInput_boxFocused:" + isInput_boxFocused.value);
  console.log("isInputFocused:" + isInputFocused.value);
  console.log("isInput_box_hovered:" + isInput_box_hovered.value);
};
</script>

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
  display: none;
  -webkit-appearance: none;
}
input[type=date]::-webkit-calendar-picker-indicator {
  visibility: hidden;
}

input[type="date"] {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background: none;
}
@supports (-moz-appearance: none) {
  .dpiV3_inputButton {
    display: none !important;
  }
}

.dpiV3_Input {
  display: flex;
  height: 48px;
  align-items: flex-start;
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);
  width: 100%;
  background: var(--neutral-0, #fff);
}

.dpiV3_Add-on {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: center;
  align-self: stretch;
  border-radius: 8px 0px 0px 8px;
  border-top: 1px solid var(--neutral-30, #d5d7da);
  border-bottom: 1px solid var(--neutral-30, #d5d7da);
  border-left: 1px solid var(--neutral-30, #d5d7da);
}

.dpiV3_Add-on-trailing {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: center;
  align-self: stretch;
  border-radius: 0px 8px 8px 0px;
  border-top: 1px solid var(--neutral-30, #d5d7da);
  border-bottom: 1px solid var(--neutral-30, #d5d7da);
  border-right: 1px solid var(--neutral-30, #d5d7da);
}

.dpiV3_Text-Add-On {
  color: var(--neutral-60, #687178);

  /* Copy/Copy-Large-Regular */
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-large-regular-font-size, 16px);
  font-weight: var(--copy-large-regular-font-weight, 400);
  line-height: var(--copy-large-regular-line-height, 26px); /* 162.5% */

  font-style: normal;
}

.dpiV3_Text_input {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
  flex: 1 0 0;
  align-self: stretch;

  border-radius: 0px 8px 8px 0px;

  background: var(--neutral-0, #fff);
}

.dpiV3_Text_input-trailing {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
  flex: 1 0 0;
  align-self: stretch;

  border-radius: 8px 0px 0px 8px;

  background: var(--neutral-0, #fff);
  z-index: 99;
}

.dpiV3_input-field {
  display: flex;
  width: 100%;
  flex-direction: column;
  align-items: flex-start;
  flex-shrink: 0;
  overflow: visible;
}

.dpiV3_InputFieldBase {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  align-self: stretch;
  overflow: visible;
}

.dpiV3_Top {
  position: relative;
  width: 100%;
  display: flex;
  align-items: flex-end;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  overflow: visible;
}

.dpiV3_Top_leading_text {
  position: relative;
  width: 100%;
  display: flex;
  align-items: flex-end;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  overflow: visible;
}

.dpiV3_Pressed {
  background-color: var(--neutral-30, #e0e4e8);
  transition: background-color 0.1s ease, box-shadow 0.1s ease;
}

.dpiV3_Lebel {
  display: flex;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
}

.dpiV3_label {
  color: var(--neutral-80, #3d4952);
  margin-bottom: 0px;

  /* Copy/Copy-Small-Regular */
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px); /* 160% */
}

.dpiV3_labelIcon {
  width: 16px;
  height: 16px;
}

.dpiV3_deleteButton {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  padding-bottom: 0px;
  padding-top: 0px;
  margin: 0px;
  outline: none;
  border: none;
  background: none;
  cursor: pointer;
}

.dpiV3_Input_box {
  display: flex;
  height: 48px;
  padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);
  border: none;
  background: var(--neutral-l0, #fff);
  box-shadow: inset 0 0 0 1px var(--neutral-30, #d5d7da);
}

.dpiV3_Text.no-cursor {
  caret-color: transparent;
  cursor: pointer;
}

.dpiV3_Rectangle1:focus-visible {
  border: 2px solid var(--Focused, #0196d8);
}

.dpiV3_Input_box:focus-within {
  box-shadow: 0 0 0 2px var(--Focused, #0196d8);
  background: var(--neutral-0, #fff);
}
.dpiV3_Input_box:hover {
  box-shadow: inset 0 0 0 1px var(--neutral-60, #687178); /* Hover-specific */
  background: var(--neutral-0, #fff);
}

.dpiV3_Input_box.calendar-focused {
  box-shadow: inset 0 0 0 1px var(--neutral-30, #d5d7da); /* Calendar-focused */
}

.dpiV3_Input_box:hover.calendar-focused {
  box-shadow: inset 0 0 0 1px var(--neutral-60, #687178); /* Both hovered and focused */
}

/* Default error state */
.dpiV3_Input_box.error-state {
  box-shadow: inset 0 0 0 1px var(--fill-error) !important;
  background: var(--neutral-0, #fff);
}

/* leading text error state */
.dpiV3_Text_input.error-state {
  box-shadow: inset 0 0 0 1px var(--fill-error, #e53b46) !important;
}

/* trailing text error state */
.dpiV3_Text_input-trailing.error-state {
  box-shadow: inset 0 0 0 1px var(--fill-error, #e53b46) !important;
}

/* leading text error state focused */
.dpiV3_Text_input.focused-error-state {
  box-shadow: 0 0 0 2px var(--fill-error, #e53b46) !important;
}

/* trailing text error state focused */
.dpiV3_Text_input-trailing.focused-error-state {
  box-shadow: 0 0 0 2px var(--fill-error, #e53b46) !important;
}

/* Focused error state */
.dpiV3_Input_box.focused-error-state {
  box-shadow: 0 0 0 2px var(--fill-error, #e53b46) !important;
  background: var(--neutral-0, #fff);
}

/* Filled error state */
.dpiV3_Input_box.filled-error-state {
  box-shadow: inset 0 0 0 1px var(--fill-error, #e53b46) !important;
  background: var(--neutral-0, #fff);
}

.dpiV3_Text.error-state {
  caret-color: var(--red-70);
}

.dpiV3_Content {
  display: flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  flex: 1 0 0;
  align-self: stretch;
}

.dpiV3_Content-leading-Text {
  display: flex;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
}

.dpiV3_iconLeft {
  width: 24px;
  height: 24px;
}

.dpiV3_Text {
  padding: 0px;
  display: flex;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
  color: var(--neutral-100, #0b1a25);
  width: 100%;
  /* Copy/Copy-Large-Regular */
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-large-regular-font-size, 16px);
  font-style: normal;
  font-weight: var(--copy-large-regular-font-weight, 400);
  line-height: var(--copy-large-regular-line-height, 26px); /* 162.5% */

  border: none;
  outline: none;

  caret-color: var(--blue-60);
}

.dpiV3_iconRight {
  width: 24px;
  height: 24px;
}

.dpiV3_inputButton {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: var(--Button-Radius);
  padding: 0;
  margin: 0;
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}

.dpiV3_Rectangle1 {
  position: absolute;
  width: 40px;
  height: 40px;
  border-radius: var(--Button-Radius);
  background-color: transparent;
  z-index: 1;
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
  outline: none;
}

.dpiV3_Rectangle1:hover {
  background-color: var(--neutral-10);
}

.dpiV3_Rectangle1:active,
.dpiV3_Rectangle1.dpiV3_Pressed {
  background-color: var(--neutral-30);
}

.dpiV3_Rectangle1:focus-visible {
  border-radius: var(--Button-Radius, 24px);
  border: 2px solid var(--Focused, #0196d8);
}

.dpiV3_calendarIcon {
  position: relative;
  z-index: 2;
  pointer-events: none;
}

.dpiV3_hintText {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: flex-start;
  align-self: stretch;

  color: var(--neutral-80, #3d4952);

  /* Copy/Copy-Small-Regular */
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px); /* 160% */
}

.dpiV3_errorText {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: flex-start;
  align-self: stretch;

  /* Copy/Copy-Small-Regular */
  font-family: var(--font-family-secondary, Inter);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px); /* 160% */

  color: var(--text-error, #a9242c);
}

.dpiV3_disabled {
  display: flex;
  height: 48px;
  padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);
  background: var(--neutral-10, #f1f1f3);

  pointer-events: none;
  user-select: none;
  outline: none;
  tabindex: -1;
}

.dpiV3_input_disabled {
  background: var(--neutral-10, #f1f1f3);
  pointer-events: none;
  user-select: none;
  outline: none;
  tabindex: -1;
}
input[type="time"]::-webkit-calendar-picker-indicator {
  display: none;
  /* Versteckt das Uhr-Icon */
}

.dpiV3_Tooltip_Wrapper {
  display: flex;
  align-items: center;
}

.dpiV3_toolTip {
  position: absolute;
  bottom: 120%;
  left: 25%;
  transform: translateX(-50%);
  background: black;
  color: var(--Text-Tooltip, #fff);
  padding: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
  justify-content: center;
  align-items: center;
  width: 200px;
  max-width: 200px;
  text-align: center;
  z-index: 1000;
  border-radius: var(--Border-Radius, 8px);
  background: var(--Background-Tooltip-Tooltip, #25333d);
}
</style>
