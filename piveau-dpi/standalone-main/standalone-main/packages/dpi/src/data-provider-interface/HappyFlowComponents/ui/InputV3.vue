<template>
  <label for="input-container">
    <div class="stretch">
      <div class="labelWrap">
        <div class="lebel">
          <span>{{ text.property }}</span>
          <!-- <img :src="Info" alt="Info Icon for tooltip" width="16px"> -->
          <PhInfo :size="16" :color="toolTipColor" weight="fill" />
        </div>
        <TextButtonSmall
          v-if="!isDisabled && eraseable"
          buttonText="löschen"
          @click="eraseText()"
          class="eraseButton"
        />
      </div>
      <div class="inputFieldWrapper">
        <div
          :class="{
            pPrefix: inputType.prefixType === 'set',
            ePrefix: inputType.prefixType === 'edit',
            dPrefix: inputType.prefixType === 'drop',
          }"
        >
          <div
            v-if="inputType.prefixType === 'set'"
            class="protocolPrefix"
            :class="{ disabled: isDisabled }"
          >
            {{ inputType.prefix }}
          </div>

          <div
            v-if="
              inputType.iType !== undefined && inputType.iType !== 'textarea'
            "
            class="input-container"
            :class="{
              activeInputV3: activeInputTrigger,
              disabled: isDisabled,
              invalidInput: !valid,
              invalidFocus: !valid && activeInputTrigger,
              urlInput: inputType.prefixType === 'set',
            }"
          >
            <div class="preWrap">
              <PhMagnifyingGlass
                v-if="validation.type != 'url'"
                :size="24"
                :color="toolTipColor"
                alt="Prefix"
              />
              <div
                v-if="
                  inputType.prefixType === 'edit' ||
                  inputType.prefixType === 'drop'
                "
                class="ddWrap"
              >
                <span
                  class="neutral80"
                  v-if="
                    inputType.prefixType === 'edit' ||
                    inputType.prefixType === 'drop'
                  "
                  >{{ inputType.prefix }}</span
                >
                <PhCaretDown
                  v-if="inputType.prefixType === 'drop'"
                  :size="20"
                  class="pointer"
                />
              </div>
            </div>
            <div class="d-flex w-100">
              <input
                :disabled="isDisabled"
                ref="inputField"
                @click="handleHighlight"
                @input="validateInput"
                type="text"
                v-model="inputText"
                :placeholder="text.placeholder"
                @focus="activeInputTrigger = true"
                @blur="activeInputTrigger = false"
              />
              <PhQuestion
                v-if="!isDatepicker"
                :size="24"
                :color="questionColor"
                alt="Suffix"
              />
              <button
                v-if="isDatepicker"
                class="calendarInput"
                :class="{ activeCalendar: activeCal }"
                @click="activeCal = !activeCal"
              >
                <PhCalendar
                  :size="24"
                  :color="questionColor"
                  @mouseover="addBG('enter', $event.target)"
                  @mouseleave="addBG('leave', $event.target)"
                />
              </button>
            </div>
          </div>
          <div
            v-else
            class="input-container inputTextarea"
            :class="{
              activeInputV3: activeInputTrigger,
              invalidInput: !valid,
              invalidFocus: !valid && activeInputTrigger,
            }"
          >
            <textarea
              v-model="inputText"
              ref="inputField"
              @focus="activeInputTrigger = true"
              @blur="activeInputTrigger = false"
              @input="validateInput"
              @click="handleHighlight"
              :placeholder="text.placeholder"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <span class="supportText" :class="{ invalid: !valid }">
      <span v-if="valid"> {{ text.support }} </span>
      <span v-else>{{ validation.message }}</span>
    </span>
  </label>
</template>
<script setup>
import { onClickOutside } from "@vueuse/core";
import { ref, watch } from "vue";
import iconRight from "../img/iconRight.svg";
import iconRightError from "../img/iconRightError.svg";
import TextButtonSmall from "./TextButtonSmall.vue";
import Info from "../img/InfoFillGray.svg";
import {
  PhInfo,
  PhQuestion,
  PhMagnifyingGlass,
  PhCalendar,
  PhCaretDown,
} from "@phosphor-icons/vue";

// Props Description
//
// Text: { placeholder: '(String) Placeholder text', property: '(String) Label for Property', support: '(String) Subtext under the inputfield' }
// Type: (String) disabled - disables the field and prevents inputs | toDo
// Data: { todo }
// Validation: { type: '(String) url | text | number ', message: '(String) error message', state: (Boolean) true|false - meant to determine if the validations triggers  }
//

const props = defineProps({
  text: {
    type: Object,
    required: true,
  },
  isDisabled: {
    type: Boolean,
  },
  data: {
    type: Object,
    required: true,
  },
  validation: {
    type: Object,
    required: true,
  },
  inputType: {
    type: Object,
  },
  datePicker: {
    type: Boolean,
  },
});
let activeInputTrigger = ref();
let inputText = ref();
let inputField = ref();
let valid = ref(true);
let iconSrcRight = ref(iconRight);
let eraseable = ref(true);
let toolTipColor = ref("var(--neutral-60)");
let questionColor = ref("var(--neutral-60)");
let isDatepicker = ref(props.datePicker);
let activeCal = ref(false);
watch(valid, (newValue, oldValue) => {
  if (newValue) {
    questionColor.value = "var(--neutral-60)";
  } else {
    questionColor.value = "var(--fill-error)";
  }
});
const addBG = (trigger, e) => {};
const eraseText = () => {
  inputText.value = "";
  activeInputTrigger.value = true;
  valid.value = true;
  // eraseable.value = false
};
const handleHighlight = () => {
  activeInputTrigger.value = true;
};
const validateInput = () => {
  // eraseable.value = true
  let regex;
  if (inputText.value === "") {
    valid.value = true;
  } else if (props.validation.type === "number") {
    if (inputText != "") {
      regex = /^[0-9+\-.,]+$/;
      valid.value = regex.test(inputText.value);
    }
  } else if (props.validation.type === "url") {
    if (inputText != "") {
      regex =
        /\b(?!http:\/\/|https:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/[^\s]*)?\b/;
      if (inputText.value.endsWith(" ")) {
        valid.value = false;
      } else valid.value = regex.test(inputText.value);
    }
  }
};
onClickOutside(inputField, (event) => {
  activeInputTrigger.value = false;
});
</script>
<style scoped>
.pointer {
  cursor: pointer;
}

.stretch {
  align-self: stretch;
}

.neutral80 {
  color: var(--neutral-80);
}

.ddWrap {
  display: flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
}

.eraseButton {
  margin: 0 !important;
  padding: 0 Im !important;
}

.pPrefix {
  display: flex;
}

.ePrefix > .input-container {
  gap: var(--Spacing-1, 4px);
}

.calendarInput {
  padding: 0;
  margin: auto;
  cursor: pointer;
  border: none;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  background: unset;
  border-radius: var(--Button-Radius, 24px);

  &:hover {
    background: var(--neutral-10);
  }

  &:active {
    background: var(--neutral-30);
  }

  &:focus-visible {
    outline: 2px solid var(--Focused);
  }
}

.activeCalendar {
  background: var(--neutral-20);
}

.preWrap {
  display: flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  /* flex: 1 0 0; */
  align-self: stretch;
}

.labelWrap {
  display: flex;
  align-items: flex-end;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
}

label {
  color: var(--neutral-80);
  /* Copy/Copy-Small-Regular */
  font-family: var(--text-default-font-family);
  font-size: var(--copy-small-regular-font-size);
  font-style: normal;
  font-weight: var(--copy-small-regular-font-weight);
  line-height: var(--copy-small-regular-line-height);
  /* 160% */

  display: flex;
  width: 320px;
  /* height: 178px; */
  flex-direction: column;
  align-items: flex-start;
}

.lebel {
  display: flex;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
}

.supportText {
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: flex-start;
  align-self: stretch;
  white-space: nowrap;
  color: var(--Colour-neutral-Neutral80, #3d4952);
}

.input-container {
  display: flex;
  height: 48px;
  padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);
  border: 1px solid var(--neutral-30, #d5d7da);
  background: var(--neutral-0, #fff);

  &:focus {
    border-radius: var(--Border-Radius, 8px);
    border: 2px solid var(--blue-70, #0196d8);
    background: var(--neutral-0, #fff);
    caret-color: var(--blue-70, #0196d8);
  }

  input {
    display: flex;
    align-items: center;
    gap: var(--Spacing-1, 4px);
    flex: 1 0 0;
    border: none;

    &:focus-visible {
      outline: none;
    }
  }
}

.activeInputV3 {
  border-radius: var(--Border-Radius, 8px);
  outline: 2px solid var(--blue-70);
  background: var(--neutral-0, #fff);
  caret-color: var(--blue-70, #0196d8);
}

.disabled {
  border-radius: var(--Border-Radius, 8px);
  border: 1px solid var(--neutral-30, #d5d7da);
  background: var(--neutral-10, #f1f1f3);

  input {
    background: var(--neutral-10, #f1f1f3);
  }
}

.invalidInput {
  border: 1px solid var(--fill-error, #e53b46);
  caret-color: var(--fill-error, #e53b46);
}

.invalidFocus {
  border-radius: var(--Border-Radius, 8px);
  outline: 2px solid var(--fill-error, #e53b46);
  background: var(--neutral-0, #fff);
}

.invalid {
  color: var(--text-error, #a9242c);
}

.protocolPrefix {
  cursor: pointer;
  color: var(--Colour-neutral-Neutral60, #687178);
  display: flex;
  padding: 0px var(--Spacing-3, 16px);
  align-items: center;
  align-self: stretch;
  border-radius: 8px 0px 0px 8px;
  border-top: 1px solid var(--neutral-30, #d5d7da);
  border-bottom: 1px solid var(--neutral-30, #d5d7da);
  border-left: 1px solid var(--neutral-30, #d5d7da);
}

.urlInput {
  border-radius: 0px 8px 8px 0px;
}

.inputTextarea {
  height: unset;
  padding: var(--Spacing-2, 8px) var(--Spacing-3, 16px);
}

textarea {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
  align-self: stretch;
  border-radius: 8px;
  resize: none;
  border: none;
  display: flex;

  &:focus-visible {
    outline: 0;
  }

  background: var(--Colour-neutral-Neutral0, #fff);
}
</style>
