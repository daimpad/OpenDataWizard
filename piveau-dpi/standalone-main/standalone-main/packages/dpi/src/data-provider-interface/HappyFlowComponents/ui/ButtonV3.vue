<template>
  <div class="dpiV3_V3-typography" :class="externalClass" v-bind="$attrs">
    <button tabindex="0" type="button" v-if="buttonText" :class="[
      dpiV3_buttonClass,
      { activeStatePrimary: isPrimaryActive && variant === 'primary' },
      { activeStateSecondary: isSecondaryActive && variant === 'secondary' },
      { activeStateTertiary: isTertiaryActive && variant === 'tertiary' },
    ]" :disabled="disabled" @mousedown="removeFocus" @mouseup="removeActiveState" :style="focusStyle"
      @focus="isFocused = true" @blur="isFocused = false" @keydown.enter="handleKeyPress"
      @keydown.space.prevent="handleKeyPress" @keyup.enter="removeActiveStateKeyUp"
      @keyup.space="removeActiveStateKeyUp">
      <span v-if="iconStart" class="dpiV3_icon" :class="iconSizeClass">
        <PhArrowCounterClockwise :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'reset'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhPlusCircle :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'plus'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhTrash :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'trash'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhMagnifyingGlass :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'MagnifyingGlass'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhCheck :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'Check'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhCaretLeft :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'CaretLeft'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhPlusSquare :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'PlusSquare'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
      </span>
      <span>{{ buttonText }}</span>
      <span v-if="iconEnd" class="dpiV3_icon" :class="iconSizeClass">
        <PhMagnifyingGlass :size="size === 'small' ? '16px' : '24px'" v-if="iconEnd === 'MagnifyingGlass'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhCheck :size="size === 'small' ? '16px' : '24px'" v-if="iconEnd === 'Check'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhCaretRight :size="size === 'small' ? '16px' : '24px'" v-if="iconEnd === 'CaretRight'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
           <PhArrowSquareOut :size="size === 'small' ? '16px' : '24px'" v-if="iconEnd === 'Out'" :color="variant === 'primary'
          ? '#0172AD'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
      </span>
    </button>
    <!-- Icon-only button -->
    <div tabindex="0" v-if="!buttonText" :class="[
      'dpiV3_button-icon-only',
      iconSizeClass,
      { 'dpiV3_button-icon-only-large': size === 'large' },
      { activeStatePrimary: isPrimaryActive && variant === 'primary' },
      { activeStateSecondary: isSecondaryActive && variant === 'secondary' },
      { activeStateTertiary: isTertiaryActive && variant === 'tertiary' },
    ]" :disabled="disabled" @mousedown="removeFocus" @mouseup="removeActiveState" :style="focusStyle"
      @focus="isFocused = true" @blur="isFocused = false" @keydown.enter="handleKeyPress"
      @keydown.space.prevent="handleKeyPress" @keyup.enter="removeActiveStateKeyUp"
      @keyup.space="removeActiveStateKeyUp">
      <span v-if="!buttonText" class="dpiV3_icon">
        <PhMagnifyingGlass :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'MagnifyingGlass'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhCheck :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'Check'" :color="variant === 'primary'
          ? '#FFFFFF'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
        <PhPencilSimple :size="size === 'small' ? '16px' : '24px'" v-if="iconStart === 'pen'" :color="variant === 'secondary'
          ? '#0172AD'
          : variant === 'secondary'
            ? '#0B1A25'
            : '#0172AD'
          " />
         
      </span>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import "../../config/styles/variables.css";
import "../../config/styles/typography.css";

import {
  PhX,
  PhCheck,
  PhMagnifyingGlass,
  PhCaretRight,
  PhCaretLeft,
  PhPlusCircle,
  PhTrash,
  PhPlusSquare, PhArrowCounterClockwise, PhPencilSimple, PhCopySimple,PhArrowSquareOut
} from "@phosphor-icons/vue";

export default defineComponent({
  name: "ButtonV3",
  components: {
    PhX,
    PhCheck,
    PhMagnifyingGlass,
    PhCaretRight,
    PhCaretLeft,
    PhPlusCircle,
    PhTrash,
    PhPlusSquare,
    PhArrowCounterClockwise,
    PhPencilSimple,
    PhCopySimple,
    PhArrowSquareOut
  },
  props: {
    buttonText: {
      type: String,
      required: false,
      default: null,
    },
    size: {
      type: String,
      default: "medium",
      validator: (value) => ["small", "medium", "large"].includes(value),
    },
    iconStart: {
      type: String,
      default: null,
    },
    iconEnd: {
      type: String,
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    variant: {
      type: String,
      default: "primary",
    },
    gapColor: {
      type: String,
      default: "white",
    },
  },
  data() {
    return {
      iconSize: "24px",
      isFocused: false,
      isPrimaryActive: false,
      isSecondaryActive: false,
      isTertiaryActive: false,
    };
  },
  computed: {
    focusStyle() {
      if (this.isFocused && this.variant === "tertiary") {
        return {
          boxShadow: `0 0 0 2px var(--Focused, #FF5733)`,
        };
      } else if (this.isFocused) {
        return {
          boxShadow: `0 0 0 2px ${this.gapColor}, 0 0 0 4px var(--Focused, #0196d8)`,
        };
      }
      return {};
    },
    externalClass() {
      const externalClasses = this.$attrs.class || "";
      return [externalClasses];
    },
    dpiV3_buttonClass() {
      return [
        `dpiV3_button-base-${this.variant}`,
        `dpiV3_button-${this.size}`,
        { "dpiV3_button-disabled": this.disabled },
      ];
    },
    iconSizeClass() {
      return [
        `dpiV3_icon-${this.size}`,
        `dpiV3_icon-${this.size}-${this.variant}`,
      ];
    },
  },
  methods: {
    removeFocus(event) {
      event.preventDefault();
      this.addActiveState();
    },
    handleKeyPress() {
      if (!this.disabled) {
        if (this.variant === "primary") {
          this.isPrimaryActive = true;
        } else if (this.variant === "secondary") {
          this.isSecondaryActive = true;
        } else if (this.variant === "tertiary") {
          this.isTertiaryActive = true;
        }
      }
    },
    removeActiveState() {
      this.isPrimaryActive = false;
      this.isSecondaryActive = false;
      this.isTertiaryActive = false;
    },
    removeActiveStateKeyUp() {
      this.isPrimaryActive = false;
      this.isSecondaryActive = false;
      this.isTertiaryActive = false;

      this.$emit("click");
    },
    addActiveState() {
      if (!this.disabled) {
        if (this.variant === "primary") {
          this.isPrimaryActive = true;
        } else if (this.variant === "secondary") {
          this.isSecondaryActive = true;
        } else if (this.variant === "tertiary") {
          this.isTertiaryActive = true;
        }
      }
    },
  },
});
</script>

<style scoped>
.dpiV3_button-base-primary {
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  border: none;
  cursor: pointer;
  border-radius: var(--Button-Radius, 24px);
  background: var(--background-primary-default, #003f6f);
  color: var(--text-primary-all, #fff);
  font-family: var(--font-family-secondary);
  font-style: normal;
  font-weight: var(--copy-button-ml-font-weight, 600);
  text-align: center;
}

.dpiV3_button-base-secondary {
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  border: none;
  cursor: pointer;
  border-radius: var(--Button-Radius, 24px);
  background: var(--background-secondary-default);
  color: var(--text-primary-secondary-default, #0b1a25);
  font-family: var(--font-family-secondary);
  font-weight: var(--copy-button-ml-font-weight, 600);
  text-align: center;
}

.dpiV3_button-base-tertiary {
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  color: var(--text-primary-tertiary-default);
  font-family: var(--font-family-secondary);
  font-weight: var(--copy-button-ml-font-weight, 600);
  text-align: center;
  background: none;
  border: none;
  cursor: pointer;
  border-radius: var(--Button-Radius, 24px);
}

.dpiV3_button-base-tertiary.dpiV3_button-small {
  font-size: 14px;
  line-height: 16px;
  display: flex;
  padding: var(--Spacing-2, 8px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
}

.dpiV3_button-base-tertiary.dpiV3_button-medium {
  font-size: 16px;
  line-height: 32px;
  display: flex;
  padding: var(--Spacing-1, 4px) 12px;
  align-items: center;
  gap: var(--Spacing-2, 8px);
}

.dpiV3_button-base-tertiary.dpiV3_button-large {
  font-size: 16px;
  line-height: 32px;
  display: flex;
  padding: var(--Border-Radius, 8px) var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
}

.dpiV3_button-base-secondary:focus {
  outline: none;
}

.dpiV3_button-base-primary:focus {
  outline: none;
}

.dpiV3_button-base-tertiary:focus {
  outline: none;
}

.dpiV3_button-base-primary:hover {
  background: var(--background-primary-hover);
  box-shadow: none;
}

.dpiV3_button-base-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
}

.dpiV3_button-base-tertiary:hover {
  background: var(--background-tertiary-hover);
  cursor: pointer;
  box-shadow: none;
}

.dpiV3_button-base-primary:active {
  background: var(--background-primary-pressed);
  box-shadow: none;
}

.dpiV3_button-base-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

.dpiV3_button-base-tertiary:active {
  background: var(--background-tertiary-pressed);
  box-shadow: none;
}

.activeStatePrimary {
  background: var(--background-primary-pressed, #002a4d) !important;
  box-shadow: none;
}

.activeStateSecondary {
  background: var(--background-secondary-pressed) !important;
  box-shadow: none;
}

.activeStateTertiary {
  background: var(--background-tertiary-pressed) !important;
  box-shadow: none;
}

.dpiV3_button-small {
  padding: var(--Spacing-2, 8px) var(--Spacing-3, 16px);
  font-size: var(--copy-button-s-font-size, 14px);
  line-height: var(--copy-button-s-line-height, 16px);
  transition: none !important;
}

.dpiV3_button-medium {
  padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
  font-size: var(--copy-button-ml-font-size, 16px);
  line-height: var(--copy-button-ml-line-height, 32px);
  transition: none !important;
}

.dpiV3_button-large {
  padding: var(--Border-Radius, 8px) var(--Spacing-4, 24px);
  font-size: var(--copy-button-ml-font-size, 16px);
  line-height: var(--copy-button-ml-line-height, 32px);
  transition: none !important;
}

.dpiV3_button-base-primary:disabled,
.dpiV3_button-disabled {
  background: var(--neutral-30);
  color: var(--text-primary-all);
  cursor: not-allowed;
  pointer-events: none;
}

.dpiV3_button-base-secondary:disabled,
.dpiV3_button-disabled {
  background: var(--neutral-10);
  color: var(--text-primary-secondary-disabled);
  cursor: not-allowed;
  pointer-events: none;
}

.dpiV3_button-base-tertiary:disabled,
.dpiV3_button-disabled {
  color: var(--text-primary-tertiary-disabled);
  background: none;
  cursor: not-allowed;
  pointer-events: none;
}

.dpiV3_button-icon-only {
  display: inline-flex;
  padding: var(--Spacing-2, 8px);
  justify-content: center;
  align-items: center;
  border-radius: var(--Button-Radius, 24px);
  background: var(--background-primary-default, #003f6f);
  cursor: pointer;
}

.dpiV3_button-icon-only-large {
  width: 48px;
  height: 48px;
  padding: 12px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  border-radius: var(--Button-Radius, 24px);
  background: var(--background-primary-default, #003f6f);
}

.dpiV3_icon {
  display: inline-flex;
  align-items: center;
}

.dpiV3_icon-small img {
  width: 16px;
  height: 16px;
}

.dpiV3_icon-small-primary:hover {
  background: var(--background-primary-hover);
  box-shadow: none;
}

.dpiV3_icon-small-tertiary:hover {
  background: var(--background-tertiary-hover);

  box-shadow: none;
}

.dpiV3_icon-small-primary:active {
  background: var(--background-primary-pressed);
  box-shadow: none;
}

.dpiV3_icon-medium-primary:hover {
  background: var(--background-primary-hover);
  box-shadow: none;
}

.dpiV3_icon-medium-primary:active {
  background: var(--background-primary-pressed);
  box-shadow: none;
}

.dpiV3_icon-large-primary:hover {
  background: var(--background-primary-hover);
  box-shadow: none;
}

.dpiV3_icon-large-primary:active {
  background: var(--background-primary-pressed);
  box-shadow: none;
}

.dpiV3_icon-small-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
}

.dpiV3_icon-small-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

.dpiV3_icon-medium-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
}

.dpiV3_icon-medium-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

.dpiV3_icon-large-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
}

.dpiV3_icon-large-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

.dpiV3_icon-medium img {
  width: 24px;
  height: 24px;
}

.dpiV3_icon-large img {
  width: 24px;
  height: 24px;
}

.dpiV3_button-icon-only.dpiV3_icon-small.dpiV3_icon-small-secondary {
  background: var(--background-secondary-default, #d4edfc);
}

.dpiV3_button-icon-only.dpiV3_icon-small.dpiV3_icon-small-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-small.dpiV3_icon-small-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-medium.dpiV3_icon-medium-secondary {
  background: var(--background-secondary-default, #d4edfc);
}

.dpiV3_button-icon-only.dpiV3_icon-medium.dpiV3_icon-medium-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-medium.dpiV3_icon-medium-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-large.dpiV3_icon-large-secondary {
  background: var(--background-secondary-default, #d4edfc);
}

.dpiV3_button-icon-only.dpiV3_icon-large.dpiV3_icon-large-secondary:hover {
  background: var(--background-secondary-hover);
  box-shadow: none;
  border: none;
}

.dpiV3_button-icon-only.dpiV3_icon-large.dpiV3_icon-large-secondary:active {
  background: var(--background-secondary-pressed);
  box-shadow: none;
}

/**** Tertiary */
.dpiV3_button-icon-only.dpiV3_icon-small.dpiV3_icon-small-tertiary {
  background: none;
}

.dpiV3_button-icon-only.dpiV3_icon-small.dpiV3_icon-small-tertiary:hover {
  background: var(--background-tertiary-hover);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-small.dpiV3_icon-small-tertiary:active {
  background: var(--background-tertiary-pressed);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-medium.dpiV3_icon-medium-tertiary {
  background: none;
}

.dpiV3_button-icon-only.dpiV3_icon-medium.dpiV3_icon-medium-tertiary:hover {
  background: var(--background-tertiary-hover);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-medium.dpiV3_icon-medium-tertiary:active {
  background: var(--background-tertiary-pressed);
  box-shadow: none;
}

.dpiV3_button-icon-only.dpiV3_icon-large.dpiV3_icon-large-tertiary:hover {
  background: var(--background-tertiary-hover);
  box-shadow: none;
  border: none;
}

.dpiV3_button-icon-only.dpiV3_icon-large.dpiV3_icon-large-tertiary:active {
  background: var(--background-tertiary-pressed);
  box-shadow: none;
}

.dpiV3_button-icon-only:focus {
  outline: none;
}

.dpiV3_button-icon-only.dpiV3_icon-large.dpiV3_icon-large-tertiary {
  background: none;
}
</style>
