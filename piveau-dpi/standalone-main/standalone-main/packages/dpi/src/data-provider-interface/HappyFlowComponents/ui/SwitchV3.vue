<template>
  <label
    style="margin-bottom: 0px !important"
    class="dpiV3_switch"
    @mousedown="handleMouseDown"
    @mouseup="handleMouseUp"
    @mouseover="onHover"
    @mouseleave="onLeave"
  >
    <input
      tabindex="0"
      type="checkbox"
      v-model="isChecked"
      :disabled="disabled"
      @change="emitSwitchState"
      @keydown="handleKeydown"
      @keyup="handleKeyup"
      @focus="handleFocus"
      @blur="handleBlur"
    />
    <span
      class="dpiV3_slider"
      :style="{
        backgroundColor: sliderBackgroundColor,
        padding: sliderPadding,
        border: sliderBorder,
      }"
    >
      <!-- Hover Circle -->
      <span
        v-if="!disabled && showHoverCircle"
        class="dpiV3_hover-circle"
        :style="{
          backgroundColor: hoverCircleColor,
          left: leftHoverCircle,
          top: topHoverCircle,
          'z-index': zHoverCircle,
        }"
      ></span>

      <span
        class="dpiV3_icon-wrapper"
        :class="{
          'icon-present': hasIcon,
          'icon-absent': !hasIcon,
          active: isChecked,
        }"
        :style="{
          backgroundColor: circleColor,
          padding: circlePadding,
          height: circleHeight,
          width: circleWidth,
          opacity: circleOpacity,
        }"
      >
        <div class="dpiV3_phosphor-icon-wrapper">
          <PhCheck
            v-if="isChecked && hasIcon && !disabled"
            :size="16"
            :color="blue60"
            weight="bold"
          />
        </div>

        <div class="dpiV3_phosphor-icon-wrapper">
          <PhX
            size="16px"
            weight="bold"
            v-if="!isChecked && hasIcon && !disabled"
            :color="neutral10"
          />
        </div>

        <div class="dpiV3_phosphor-icon-wrapper">
          <PhX
            size="16px"
            weight="bold"
            v-if="!isChecked && hasIcon && disabled"
            :color="neutral30"
          />
        </div>
        <div class="dpiV3_phosphor-icon-wrapper">
          <PhCheck
            v-if="isChecked && hasIcon && disabled"
            :size="16"
            :color="neutral30"
            weight="bold"
          />
        </div>
      </span>
    </span>
  </label>
</template>

<script>
import "../../config/styles/variables.css";
import { PhX, PhCheck } from "@phosphor-icons/vue";

export default {
  name: "SwitchV3",
  components: {
    PhX,
    PhCheck,
  },
  data() {
    return {
      isChecked: this.defaultChecked,
      circleColor: "var(--neutral-0, #ffffff)",
      sliderBackgroundColor: "var(--neutral-20, #e6e7e9)",
      hoverCircleColor: "",
      showHoverCircle: false,
      leftHoverCircle: "16px",
      topHoverCircle: "-4px",
      zHoverCircle: "-1",
      stateLock: false,
      sliderPadding: "0 6px",
      circlePadding: "12px",
      circleHeight: "2px",
      circleWidth: "2px",
      sliderBorder: "",
      circleOpacity: "",
      blue60: "",
      neutral10: "",
      neutral30: "",
      interactionType: null, // mouse or keyboard
      pressedState: false,
      sliderOnHoverState: false,
    };
  },
  props: {
    defaultChecked: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    hasIcon: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    emitSwitchState() {
      this.$emit("switch-toggled", this.isChecked);
    },
    handleFocus() {
      this.updateHoverStyles();
    },
    handleBlur() {
      if (this.interactionType === "keyboard") {
        this.sliderOnHoverState = false;

        this.resetStyles();
      }
    },
    handleKeydown(event) {
      this.interactionType = "keyboard";

      if (
        event.key === "Enter" ||
        event.key === " " ||
        event.key === "Spacebar"
      ) {
        event.preventDefault();
        this.handlePressState();
      }
    },
    handleKeyup(event) {
      this.interactionType = "keyboard";
      this.pressedState = false;
      if (event.key === "Tab") {
        event.preventDefault();
        this.updateHoverStyles();
      } else if (
        event.key === "Enter" ||
        event.key === " " ||
        event.key === "Spacebar"
      ) {
        event.preventDefault();
        this.pressedState = false;
        this.isChecked = !this.isChecked;
        this.emitSwitchState()
        setTimeout(() => {
          this.updateHoverStyles();
        }, 10);
      }
    },
    handleMouseDown() {
      if (this.disabled || this.stateLock) return;
      this.interactionType = "mouse";

      this.handlePressState();
    },
    handlePressState() {
      this.pressedState = true;
      this.circlePadding = "14px";

      if (this.isChecked) {
        this.sliderPadding = "0px 2px";
        this.hoverCircleColor = "var( --state-layer-primary-opacity-12)";
      } else {
        this.sliderPadding = "0px 10px 0px 0px";
        this.hoverCircleColor = "var( --state-layer-on-surface-opacity-12)";
        if (!this.hasIcon) {
          this.leftHoverCircle = "-6px";
        }
      }
    },
    handleMouseUp() {
      this.pressedState = false;
      if (this.disabled || this.stateLock) return;

      if (this.sliderOnHoverState) {
        setTimeout(() => {
          this.updateHoverStyles();
        }, 10);
      } else {
        this.resetStyles();
      }
    },
    onHover() {
      this.sliderOnHoverState = true;

      if (!this.pressedState) {
        if (this.stateLock || this.disabled) return;

        this.showHoverCircle = true;

        this.updateHoverStyles();
      }
    },
    onLeave() {
      this.sliderOnHoverState = false;
      if (this.stateLock || this.disabled) return;

      this.showHoverCircle = false;

      this.resetStyles();
    },
    resetStyles() {
      this.showHoverCircle = false;
      this.pressedState = false;

      if (this.isChecked) {
        this.circleColor = "var(--neutral-0, #ffffff)";
        this.sliderBackgroundColor = "var(--blue-60, #009fe3)";
        this.circlePadding = "12px";
        this.sliderPadding = "0 var(--Spacing-1, 4px)";
      } else {
        this.circleColor = "var(--neutral-60, #687178)";
        this.sliderBackgroundColor = "var(--neutral-20, #e6e7e9)";
        this.circlePadding = "8px";
        this.sliderPadding = "0 6px";
        // OFF and has icon
        if (this.hasIcon) {
          this.circlePadding = "12px";
          this.sliderPadding = "0 2px";
        }
      }
    },
    updateHoverStyles() {
      this.showHoverCircle = true;

      if (this.isChecked) {
        this.circlePadding = "12px";
        this.sliderPadding = "0 var(--Spacing-1, 4px)";
        this.circleColor = "var(--blue-20, rgba(0, 159, 227, 0.2))";

        if (this.interactionType === "mouse")
          this.hoverCircleColor =
            "var(--state-layer-primary-opacity-8, rgba(0, 159, 227, 0.08))";
        else
          this.hoverCircleColor =
            "var(--state-layer-primary-opacity-12, rgba(0, 159, 227, 0.12))";

        this.leftHoverCircle = "16px";
        this.topHoverCircle = "-4px";
        this.sliderBackgroundColor = "var(--blue-60, #009fe3)";
        this.zHoverCircle = "-1";
      }
      // dpiV3_switch OFF
      else {
        this.circlePadding = "8px";
        this.sliderPadding = "0 6px";
        this.circleColor = "var(--neutral-60, #687178)";

        if (this.interactionType === "mouse")
          this.hoverCircleColor =
            "var(--state-layer-on-surface-opacity-8, rgba(11, 26, 37, 0.08))";
        else
          this.hoverCircleColor =
            "var(--state-layer-on-surface-opacity-12, rgba(11, 26, 37, 0.12))";

        this.leftHoverCircle = "-6px";
        this.topHoverCircle = "-6px";
        this.sliderBackgroundColor = "var(--neutral-0, #ffffff)";
        this.circleColor = "var(--neutral-80)";
        this.zHoverCircle = "100";

        if (this.hasIcon) {
          this.circlePadding = "12px";
          this.sliderPadding = "0 2px";
          this.leftHoverCircle = "-6px";
          this.topHoverCircle = "-6px";
        }
      }
    },
  },
  mounted() {
    this.blue60 = getComputedStyle(document.documentElement)
      .getPropertyValue("--blue-60")
      .trim();
    this.neutral10 = getComputedStyle(document.documentElement)
      .getPropertyValue("--neutral-10")
      .trim();
    this.neutral30 = getComputedStyle(document.documentElement)
      .getPropertyValue("--neutral-30")
      .trim();

    // dpiV3_switch ON
    if (this.defaultChecked) {
      this.isChecked = true;
      this.circleColor = "var(--neutral-0, #ffffff)";
      this.sliderBackgroundColor = "var(--blue-60, #009fe3)";
      this.sliderPadding = "0 var(--Spacing-1, 4px)";
    } else {
      /** dpiV3_switch OFF */
      this.isChecked = false;
      this.circleColor = "var(--neutral-60, #687178)";
      this.sliderBackgroundColor = "var(--neutral-20, #e6e7e9)";
      this.circlePadding = "8px";

      // OFF and has icon
      if (this.hasIcon) {
        this.circlePadding = "12px";
        this.sliderPadding = "0 2px";
      }
    }

    if (this.disabled) {
      this.circleColor = "var(--neutral-5, #d9d9d9)";
      this.sliderBackgroundColor = "var(--neutral-30, #f0f0f0)";
      this.sliderBorder = "var(--neutral-30)";
      if (!this.isChecked) {
        this.circleColor = "var(--neutral-60, #d9d9d9)";
        this.sliderBackgroundColor = "var(--neutral-10)";
        this.sliderBorder = "2px solid var(--neutral-30, #D5D7DA)";
        this.circleOpacity = "0.38";
      }
    }
  },
};
</script>

<style scoped>
.dpiV3_phosphor-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 24px;
  width: 24px;
}

.dpiV3_switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 32px;
  z-index: 1;
}

.dpiV3_switch input {
  display: block; 
  position: absolute;
  opacity: 0; 
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
}

.dpiV3_slider {
  position: relative;
  cursor: pointer;
  width: 52px;
  height: 32px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding: 0 var(--Spacing-1, 4px);
  background: var(--blue-60, #009fe3);
  border-radius: var(--Button-Radius, 24px);
  transition: background-color 0.2s ease, justify-content 0.2s ease,
    padding 0.2s ease;
}

/* Hover Circle */
.dpiV3_hover-circle {
  position: absolute;
  top: -4px;
  left: 16px;
  width: 40px;
  height: 40px;
  background-color: var(--blue-20, rgba(0, 159, 227, 0.2));
  border-radius: 50%;
  pointer-events: none;
  z-index: -1;
}

/* Circle (inside the dpiV3_slider) */
.dpiV3_icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  background-color: var(--neutral-0, #ffffff);
  border-radius: var(--Button-Radius, 24px);
  z-index: 2;
  transition: transform 0.2s ease, padding 0.1s ease;
}

/** Circle, icon present, not active */
.dpiV3_icon-wrapper.icon-present {
  height: 2px;
  width: 2px;
  padding: 12px;
  background-color: var(--neutral-60, #687178);
  transition: background-color 0.2s ease, padding 0.2s ease;
}

/** Circle: icon present active */
.dpiV3_icon-wrapper.icon-present.active {
  height: 2px;
  width: 2px;
  padding: 12px;
  background-color: var(--neutral-0, #ffffff);
  transition: background-color 0.2s ease, padding 0.2s ease;
}

/* circle, icon not present */
.dpiV3_icon-wrapper.icon-absent {
  padding: 12px;
  background-color: var(--neutral-60, #687178);
  transition: background-color 0.2s ease, padding 0.2s ease;
}

/* circle, icon not present but active */
.dpiV3_icon-wrapper.icon-absent.active {
  padding: 12px;
  background-color: var(--neutral-0, #ffffff);
  transition: background-color 0.2s ease, padding 0.2s ease;
}

.dpiV3_icon-wrapper img {
  height: 16px;
  width: 16px;
  pointer-events: none;
}

/* checked followed by class dpiV3_slider */
input:checked + .dpiV3_slider {
  justify-content: flex-end;
  position: relative;
  border: none;
  transition: box-shadow 0.2s ease, background-color 0.2s ease,
    justify-content 0.2s ease, padding 0.2s ease;
}

/** needed to keep border when sliding */
input:checked + .dpiV3_slider::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: inherit;
  box-shadow: inset 0 0 0 2px var(--blue-60, #009fe3);
  pointer-events: none;
  transition: box-shadow 0.4s ease, padding 0.2s ease;
}

/* Prevent ::before styles when disabled */
input:disabled + .dpiV3_slider::before {
  cursor: not-allowed;
  content: none;
  box-shadow: none;
}

/* not "checked", must be followed by class dpiV3_slider */
input:not(:checked) + .dpiV3_slider {
  background: var(--neutral-20, #e6e7e9);
  border-radius: var(--Button-Radius, 24px);
  border: 2px solid var(--neutral-60, #687178);
}

/* focus styles */
input:focus + .dpiV3_slider {
  outline: none;
  position: relative;
}

/* Focus state for dpiV3_slider when not checked */
input:not(:checked):focus-visible + .dpiV3_slider::after {
  content: "";
  position: absolute;
  width: 60px;
  height: 40px;
  top: -6px;
  left: -6px;
  border-radius: inherit;
  border: 2px solid var(--Focused, rgba(0, 159, 227, 0.3));
  background-color: transparent;
  pointer-events: none;
  z-index: 1000;
  transition: transform 0.2s ease, padding 0.2s ease;
}

/* Focus ring for the checked state */
input:checked:focus-visible + .dpiV3_slider::after {
  content: "";
  position: absolute;
  width: 60px;
  height: 40px;
  top: -4px;
  left: -4px;
  border-radius: inherit;
  border: 2px solid var(--Focused, rgba(0, 159, 227, 0.3));
  background-color: transparent;
  pointer-events: none;
  z-index: 1000;
  transition: all 0.2s ease;
  transition: transform 0.2s ease, padding 0.2s ease;
}
</style>
