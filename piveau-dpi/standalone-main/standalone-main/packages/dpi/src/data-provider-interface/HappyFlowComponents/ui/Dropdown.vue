<template>
  <div class="dpiV3_dropdownV3">
    <TextButtonSmall
      v-if="props.type === 'textButtonSmall'"
      :buttonText="props.buttonText"
      @click="toggleDropdownHandler"
      ref="accActive"
    ></TextButtonSmall>
    <MoreButton
      v-if="props.type === 'moreButton'"
      @click="toggleDropdownHandler"
      ref="accActive"
    ></MoreButton>
    <InputField
      v-if="props.type === 'inputField'"
      v-bind="props.inputFieldProps"
      @click="toggleDropdownHandler"
      @keydown.space.prevent="toggleDropdownHandler"
      @keydown.enter="toggleDropdownHandler"
      @keydown.arrow-down.prevent="focusFirstItem"
      @input="handleInputEvent"
      @deleteButtonClicked="deleteField"
      :modelValue="inputText"
      :dropDownExpanded="toggleDropdown"
      ref="accActive"
    ></InputField>

    <div
      class="dpiV3_dropdownWrapper"
      v-if="toggleDropdown"
      :class="{
        alignRight: props.alignment === 'right',
      }"
      :style="{ width: dropdown_width }"
    >
      <!-- Loading state message -->
      <div v-if="isLoading" class="dpiV3_loading">
        <div class="dpiV3_loading-spinner"></div>
        <span>Daten werden geladen...</span>
      </div>

      <!-- Empty state message when no results -->
      <div v-else-if="isDataEmpty" class="dpiV3_empty-state">
        <span>Keine Ergebnisse gefunden</span>
      </div>

      <!-- Multi-selection dropdown list -->
      <ul v-else-if="props.multi" class="dpiV3_dropdown dpiV3_dropdownMulti">
        <div class="dpiV3_multiInnerWrap" v-for="(item, i) in data">
          <div class="dpiV3_multiHeader">
            {{
              $t(
                "message.dataupload.datasets.dcatde:politicalGeocodingURI.titles." +
                  item.headers
              )
            }}
          </div>

          <div v-for="(itemInner, i) in item.list" class="dpiV3_btnWrap">
            <button
              @click="
                handleMultiClick(
                  itemInner,
                  $event,
                  parentCategory(item.headers)
                )
              "
              @keydown.enter="handleKeyDownMulti(itemInner, $event)"
              @keydown.space.prevent="handleKeyDownMulti(itemInner, $event)"
              @keyup="handleKeyUpMulti(itemInner, $event)"
              @keydown.arrow-down="handleKeyDownMulti(itemInner, $event)"
              @keydown.arrow-up="handleKeyDownMulti(itemInner, $event)"
            >
              {{ itemInner.alt_label["de"] }}
            </button>
          </div>
          <div
            v-if="i - 1 != Object.values(item).length"
            class="dpiV3_multiSeperator"
          ></div>
        </div>
      </ul>

      <!-- Single-selection dropdown list -->
      <ul v-else class="dpiV3_dropdown">
        <button
          v-for="(item, i) in data"
          :key="item['@value'] || i"
          @click="handleClick(item, $event)"
          @keydown="handleKeyDown(item, $event)"
          @keyup="handleKeyUp(item, $event)"
          :class="{ dpiV3_selected: item['selected'] }"
          :style="{
            background: itemBackgroundColors[item['@value']] || 'initial',
          }">
          {{ item["@value"] || item['pref_label']['de'] || item['pref_label']['en'] }}
        </button>
      </ul>
    </div>
  </div>
</template>
<script setup>
import { ref, watch, computed, nextTick } from "vue";
import { onClickOutside } from "@vueuse/core";
import TextButtonSmall from "./TextButtonSmall.vue";
import MoreButton from "./MoreButton.vue";
import InputField from "./InputField.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const accActive = ref();
let fill = ref("#0172AD");
let toggleDropdown = ref(false);
const itemBackgroundColors = ref({});
const itemBackgroundMultiColors = ref([]);
const focusedIndex = ref(0);
const isLoading = ref(false); // Loading state for the dropdown

const emit = defineEmits([
  "update:modelValue",
  "input",
  "deleteDropdownField",
  "valueSent",
  "chosenVocItem",
  "clickOutside",
]);
const parentCategory = (item) => {
  return t(
    "message.dataupload.datasets.dcatde:politicalGeocodingURI.titles." + item
  );
};
const props = defineProps({
  buttonText: {
    type: String,
    required: true,
  },
  notDraft: {
    type: Boolean,
  },
  text: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    required: true,
  },
  data: {
    type: Object,
    required: true,
  },
  multi: {
    type: Boolean,
  },
  alignment: {
    type: Boolean,
  },
  inputFieldProps: {
    type: Object,
    default: () => ({}),
  },
  dropdownWidth: {
    type: String,
    default: "medium",
    validator: (value) => ["medium", "large"].includes(value),
  },
  isDisabled: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: Boolean,
    default: false,
  },
  modelValue: { type: String, default: "" },
  loading: { type: Boolean, default: false }, // Loading prop from parent
});

// Track if data is empty
const isDataEmpty = computed(() => {
  if (props.multi) {
    // For multi, check if there are any lists with items
    if (!props.data || !Array.isArray(props.data) || props.data.length === 0) {
      return true;
    }

    // Check if any category has items
    for (const item of props.data) {
      if (item.list && item.list.length > 0) {
        return false;
      }
    }
    return true;
  } else {
    // For single selection, check if array is empty
    return !props.data || !Array.isArray(props.data) || props.data.length === 0;
  }
});

// Watch for changes in the loading prop from parent
watch(
  () => props.loading,
  (newValue) => {
    isLoading.value = newValue;
  }
);

const inputText = ref(props.modelValue);

watch(
  () => props.modelValue,
  (newVal) => {
    inputText.value = newVal;
  }
);

const deleteField = () => {
  toggleDropdownHandler();
  emit("deleteDropdownField");
};

const handleInputEvent = (event) => {
  inputText.value = event.target.value;
  // Always emit the current text value for validation
  nextTick(() => {
    emit("input", inputText.value);
  });

  // Dropdown visibility logic
  if (props.autocomplete && inputText.value.length > 0) {
    toggleDropdown.value = true;
  } else if (inputText.value.length === 0) {
    toggleDropdown.value = false;
  }
};

const dropdown_width = computed(() => {
  if (props.autocomplete) {
    return;
  }
  return props.dropdownWidth === "large" ? "100%" : "161px";
});

const toggleDropdownHandler = (event) => {
  if (props.autocomplete) {
    return;
  }
  if (!props.isDisabled) {
    toggleDropdown.value = !toggleDropdown.value;
  }
};

const focusFirstItem = (e) => {
  if (toggleDropdown.value && !isLoading.value && !isDataEmpty.value) {
    focusedIndex.value--;
    const items = Array.from(
      document.querySelectorAll(".dpiV3_dropdown button")
    );
    if (items.length > 0) {
      e.preventDefault();
      focusedIndex.value = (focusedIndex.value + 1) % items.length;
      items[focusedIndex.value].focus();
    }
  }
};

const handleKeyDownMulti = (item, e) => {
  e.preventDefault();
  if (e.key === "Enter" || e.key === " " || e.key === "Spacebar") {
    focusedIndex.value = 0;

    if (!itemBackgroundColors.value[item]) {
      itemBackgroundColors.value[item] = {};
    }

    itemBackgroundColors.value[item] = "var(--neutral-10, #fafafb)";
  }

  if (e.key === "Escape") {
    focusedIndex.value = 0;
    toggleDropdown.value = !toggleDropdown.value;
  }

  const items = Array.from(
    document.querySelectorAll(".dpiV3_dropdownMulti .dpiV3_btnWrap button")
  );
  const currentIndex = items.findIndex((el) => el === document.activeElement);

  if (e.key === "ArrowDown") {
    e.preventDefault();
    const nextIndex = (currentIndex + 1) % items.length;
    items[nextIndex].focus();
    focusedIndex.value = nextIndex;
  } else if (e.key === "ArrowUp") {
    e.preventDefault();
    const prevIndex = (currentIndex - 1 + items.length) % items.length;
    items[prevIndex].focus();
    focusedIndex.value = prevIndex;
  }
};

const handleKeyUpMulti = (itemInner, e) => {
  e.preventDefault();
  if (e.key === "Enter" || e.key === " " || e.key === "Spacebar") {
    props.data.forEach((header) => {
      for (let key in header) {
        header[key].forEach((itemInner) => {
          itemInner.selected = false;
        });
      }
    });
    itemInner["selected"] = !itemInner["selected"];

    inputText.value = itemInner["@value"];
    toggleDropdown.value = false;
  }
};

const handleKeyDown = (item, e) => {
  e.preventDefault();
  if (e.key === "Enter" || e.key === " " || e.key === "Spacebar") {
    itemBackgroundColors.value[item["@value"]] = "var(--neutral-10, #f1f1f3)";
    focusedIndex.value = 0;
  }

  if (e.key === "Escape") {
    focusedIndex.value = 0;
    toggleDropdown.value = !toggleDropdown.value;
  }

  const items = Array.from(document.querySelectorAll(".dpiV3_dropdown button"));
  if (e.key === "ArrowDown") {
    e.preventDefault();
    focusedIndex.value = (focusedIndex.value + 1) % items.length;
    items[focusedIndex.value].focus();
  } else if (e.key === "ArrowUp") {
    e.preventDefault();
    focusedIndex.value = (focusedIndex.value - 1 + items.length) % items.length;
    items[focusedIndex.value].focus();
  } else if (e.key === "Tab" && e.shiftKey) {
    e.preventDefault();
    focusedIndex.value = (focusedIndex.value - 1 + items.length) % items.length;
    items[focusedIndex.value].focus();
  } else if (e.key === "Tab") {
    e.preventDefault();
    focusedIndex.value = (focusedIndex.value + 1) % items.length;
    items[focusedIndex.value].focus();
  }
};

const handleKeyUp = (item, e) => {
  e.preventDefault();
  if (e.key === "Enter" || e.key === " " || e.key === "Spacebar") {
    props.data.forEach((element) => {
      element["selected"] = false;
    });
    item["selected"] = !item["selected"];
    itemBackgroundColors.value[item["@value"]] = "var(--neutral-5, #fafafb)";
    inputText.value = item["@value"];
    toggleDropdown.value = false;
  }
};

const handleMultiClick = (item, e, header) => {
  emit("valueSent", item, header);
  inputText.value = item.alt_label["de"] + " (" + header + ")";
  toggleDropdown.value = false;
};

const handleClick = (item, e) => {
  console.log(props.notDraft);

  if (e.type === "keydown") {
    e.preventDefault();
  }

  if (e.type === "click") {
    if (props.multi) {
      props.data.forEach((headers) => {
        for (let key in headers) {
          headers[key].forEach((item) => {
            item.selected = false;
          });
        }
      });
      inputText.value = item["@value"];
      item["selected"] = !item["selected"];
    } else {
      // flush the array to deselect the items
      props.data.forEach((element) => {
        element["selected"] = false;
      });
      item["selected"] = !item["selected"];
      inputText.value = item["@value"];
      // console.log(item);
    }

    toggleDropdown.value = false;

    if (item["@value"] === undefined) {
      inputText.value = item.pref_label["de"] || item.pref_label["en"];
      emit("chosenVocItem", item);
    }
    if (item["@value"] === 'Löschen' && props.notDraft) {
      inputText.value = 'LöschenPublished';
      emit("chosenVocItem", item);
    }

    emit("update:modelValue", item["@value"], item);
  }
};

onClickOutside(accActive, (event) => {
  toggleDropdown.value = false;
});
</script>

<style scoped>
.alignRight {
  right: 0;
}

.dpiV3_selected {
  background: var(--blue-0, #aedff8) !important;
}

.dpiV3_btnWrap {
  display: flex;
  flex-direction: column;
}

button {
  all: unset;
  margin: 0 !important;
}

.dpiV3_dropdownWrapper {
  position: absolute;
  top: calc(100% + 4px);
  background: var(--neutral-5, #fafafb);
  z-index: 999;
  width: 100%;
  max-height: 300px;
  overflow: hidden;
  border-radius: var(--Border-Radius, 8px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
    0 4px 6px -2px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(0, 0, 0, 0.05);
}

.dpiV3_active {
  rotate: 180deg;
}

.dpiV3_dropdownButton {
  width: 100%;
}

/* Loading state styling */
.dpiV3_loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: var(--neutral-5, #fafafb);
}

.dpiV3_loading-spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(0, 159, 227, 0.3);
  border-radius: 50%;
  border-top-color: var(--blue-60, #009fe3);
  animation: spin 1s linear infinite;
  margin-bottom: 8px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Empty state styling */
.dpiV3_empty-state {
  display: flex;
  padding: 16px;
  justify-content: center;
  align-items: center;
  background: var(--neutral-5, #fafafb);
  color: var(--neutral-60, #687178);
  font-family: var(--font-family-secondary);
  font-size: var(--copy-small-regular-font-size);
}

.dpiV3_dropdown {
  margin-bottom: 0 !important;
  display: flex;
  width: 100%;
  padding: var(--Spacing-2, 8px) 0px;
  flex-direction: column;
  align-items: flex-start;
  max-height: 300px;
  overflow-y: auto;

  /* Elevation light/1 */
  box-shadow: var(--elevation-light-1);
  list-style: none;

  button {
    display: flex;
    padding: var(--Spacing-2, 8px) var(--Spacing-4, 24px);
    align-items: flex-start;
    gap: var(--Spacing-3, 16px);
    align-self: stretch;

    &:hover {
      background: var(--neutral-20, #e6e7e9) !important;
    }

    &:active {
      background: var(--neutral-10, #f1f1f3) !important;
      outline: none !important;
    }

    &:focus {
      z-index: 99;
      outline: 2px solid var(--Focused, #0196d8);
    }
  }
}

.dpiV3_dropdownMulti {
  button {
    display: flex;
    flex-direction: column;

    span {
      display: flex;
      flex-direction: column;
    }
  }
}

.dpiV3_dropdownV3 {
  width: 100%;
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  cursor: pointer;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;

  &:focus {
    border-radius: 4px;
    border: 2px solid var(--Focused, #0196d8);
  }

  button {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-large-regular-font-size);
    font-style: normal;
    font-weight: var(--copy-large-regular-font-weight);
    line-height: var(--copy-large-regular-line-height);
  }
}

.dpiV3_multiHeader {
  display: flex;
  padding: var(--Spacing-2, 8px) var(--Spacing-4, 24px);
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
  color: var(--neutral-100, #0b1a25);

  font-style: normal;
  font-family: var(--font-family-secondary);
  font-size: var(--copy-large-semi-bold-font-size);
  line-height: var(--copy-large-semi-bold-line-height);
  font-weight: var(--copy-large-semi-bold-font-weight);
}

.dpiV3_multiInnerWrap {
  width: 100%;
  display: flex;
  flex-direction: column;

  button {
    display: flex;
    padding: var(--Spacing-2, 8px) var(--Spacing-4, 24px) var(--Spacing-2, 8px)
      var(--Spacing-6, 48px);
    align-items: flex-start;
    gap: var(--Spacing-3, 16px);
    align-self: stretch;
  }
}

.dpiV3_multiInnerWrap:last-child {
  border-bottom: none;
  margin-bottom: none !important;
}

.dpiV3_multiSeperator {
  height: 1px;
  width: 100%;
  background-color: var(--neutral-20);
  margin: var(--Spacing-2) 0;
  display: flex;
}
</style>
