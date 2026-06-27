<template>
  <button
    type="button"
    tabindex="0"
    :class="[dpiV3_buttonClass, { 'dpiV3_text-button-pressed': isPressed }]"
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
    <span v-if="iconStart" class="dpiV3_icon-large">
      <PhTrash :size="24" :color="iconColor" />
    </span>
    <span v-if="buttonText">{{ buttonText }}</span>
    <span v-if="iconEnd" class="dpiV3_icon-large">
      <PhTrash :size="24" :color="iconColor" />
    </span>
  </button>
</template>

<script>
import { defineComponent } from "vue";
import "../../config/styles/variables.css";
import "../../config/styles/typography.css";
import { PhTrash } from "@phosphor-icons/vue";

export default defineComponent({
  name: "TextButtonLarge",
  components: {
    PhTrash,
  },
  props: {
    buttonText: {
      type: String,
      required: false,
    },
    size: {
      type: String,
      default: "large",
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
  },
  data() {
    return {
      isHovered: false,
      isPressed: false,
    };
  },
  computed: {
    dpiV3_buttonClass() {
      const externalClasses = this.$attrs.class || "";
      return [
        "dpiV3_text-button-large",
        { "dpiV3_text-button-disabled": this.disabled },
        externalClasses,
      ];
    },
    iconSizeClass() {
      const externalClasses = this.$attrs.class || "";
      return ["dpiV3_icon-large", externalClasses];
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
.dpiV3_text-button-large {
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: var(--Spacing-1, 4px);
  font-family: var(--font-family-secondary);
  text-align: center;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--blue-80, #0172ad);
  font-size: var(--copy-button-ml-font-size, 16px);
  font-style: normal;
  font-weight: var(--copy-button-ml-font-weight, 600);
  line-height: var(--copy-button-ml-line-height, 32px); /* 160% */
}

.dpiV3_text-button-pressed {
  color: var(--blue-60);
}

.dpiV3_text-button-large:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--Focused, #0196d8);
  background: none;
  border-radius: var(--Border-Radius, 8px);
}

.dpiV3_text-button-large:hover {
  color: var(--blue-100);
}

.dpiV3_text-button-large:active {
  color: var(--blue-60);
}

.dpiV3_text-button-disabled {
  color: var(--blue-40);
  cursor: not-allowed;
  pointer-events: none;
}

.dpiV3_icon-large {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: var(--Spacing-1, 4px);
}
</style>
