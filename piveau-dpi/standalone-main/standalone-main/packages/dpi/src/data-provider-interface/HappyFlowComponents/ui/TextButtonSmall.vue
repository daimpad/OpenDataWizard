<template>
  <button
    type="button"
    tabindex="0"
    :class="[buttonClass, { 'dpiV3_text-button-pressed': isPressed }]"
    :disabled="disabled"
    @mousedown="onPress"
    @mouseup="isPressed = false"
    @keydown="onKeyPress"
    @keyup="onKeyRelease"
    @mouseover="isHovered = true"
    @mouseleave="
      () => {
        isHovered = false;
        isPressed = false;
      }
    "
    v-bind="$attrs"
  >
    <span v-if="iconStart" class="dpiV3_icon-small">
      <PhTrash v-if="iconName === 'trash'" :size="24" :color="iconColor" />
      <PhCopySimple v-if="iconName === 'copy'" :size="24" :color="iconColor" />
      <PhCaretLeft v-if="iconName === 'caretLeft'" :size="24" :color="iconColor" />
    </span>
    <span v-if="buttonText">{{ buttonText }}</span>
    <span v-if="iconEnd" class="dpiV3_icon-small">
      <PhTrash :size="24" :color="iconColor" />
    </span>
  </button>
</template>

<script>
import { defineComponent } from "vue";
import "../../config/styles/variables.css";
import "../../config/styles/typography.css";
import { PhTrash, PhCopySimple,PhCaretLeft } from "@phosphor-icons/vue";

export default defineComponent({
  name: "TextButtonSmall",
  components: {
    PhTrash,
    PhCopySimple,
    PhCaretLeft
  },
  props: {
    buttonText: {
      type: String,
      required: false,
    },
    size: {
      type: String,
      default: "small",
    },
    iconStart: {
      type: Boolean,
      default: null,
    },
    iconEnd: {
      type: Boolean,
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    iconName: {
      type: String,
      default: "trash",
    },
  },
  data() {
    return {
      isHovered: false,
      isPressed: false,
    };
  },
  computed: {
    buttonClass() {
      const externalClasses = this.$attrs.class || "";
      return [
        "dpiV3_text-button-small",
        { "dpiV3_text-button-disabled": this.disabled },
        externalClasses,
      ];
    },
    iconSizeClass() {
      const externalClasses = this.$attrs.class || "";
      return ["dpiV3_icon-small", externalClasses];
    },
    iconColor() {
      if (this.disabled) {
        return "#67C5F0"; // Color for disabled state
      } else if (this.isPressed) {
        return "#009FE3"; // Color for pressed state
      } else if (this.isHovered) {
        return "#003F6F"; // Color for hover state
      } else {
        return "#0172AD"; // Default color
      }
    },
  },
  methods: {
    async onPress(event) {
      event.preventDefault();
      this.isPressed = true;
    },
    onKeyPress(event) {
      if (
        event.key === "Enter" ||
        event.key === " " ||
        event.key === "Spacebar"
      ) {
        event.preventDefault();
        this.isPressed = true;
      }
    },
    onKeyRelease(event) {
      if (
        event.key === "Enter" ||
        event.key === " " ||
        event.key === "Spacebar"
      ) {
        event.preventDefault();
        this.isPressed = false;

        if (!this.disabled) {
          this.$emit("click", event);
        }
      }
    },
  },
});
</script>

<style scoped>
.dpiV3_text-button-small {
  position: relative;
  padding: 0;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  font-family: var(--font-family-secondary) !important;
  text-align: center;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--blue-80, #0172ad);
  font-size: var(--copy-small-regular-font-size, 15px);
  font-style: normal;
  font-weight: var(--copy-small-semi-bold-font-weight, 400);
  line-height: var(--copy-small-regular-line-height, 24px); /* 160% */
}

.dpiV3_text-button-pressed {
  color: var(--blue-60);
}

.dpiV3_text-button-small:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--Focused, #0196d8);
  background: none;
  border-radius: var(--Border-Radius, 8px);
}

.dpiV3_text-button-small:hover {
  color: var(--blue-100);
}

.dpiV3_text-button-small:active {
  color: var(--blue-60);
}

.dpiV3_text-button-disabled {
  color: var(--blue-40);
  cursor: not-allowed;
  pointer-events: none;
}

.dpiV3_icon-small {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: var(--Spacing-1, 4px);
}
</style>
