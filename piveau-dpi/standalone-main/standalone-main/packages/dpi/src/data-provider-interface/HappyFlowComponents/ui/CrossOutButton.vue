<template>
  <button
    tabindex="0"
    :class="[
      { dpiV3_inToast: type === 'inToast' },
      { dpiV3_activeState: isActive },
    ]"
    @mousedown="removeFocus"
    class="dpiV3_crossout-button"
    @click="handleClick"
    @focus="isFocused = true"
    @blur="isFocused = false"
    @keydown.enter="isActive = true"
    @keydown.space.prevent="isActive = true"
    @keyup.enter="isActive = false"
    @keyup.space="isActive = false"
  >
    <PhX size="24px" color="#3D4952" />
  </button>
</template>

<script>
import "../../config/styles/variables.css";
import { PhX } from "@phosphor-icons/vue";

export default {
  name: "CrossOutButton",
  components: {
    PhX,
  },
  props: {
    type: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      isFocused: false,
      isActive: false,
    };
  },
  emits: ["click"],
  methods: {
    handleClick() {
      this.$emit("click");
    },
    removeFocus(event) {
      event.preventDefault();
    },
  },
};
</script>

<style scoped>
.dpiV3_crossout-button {
  display: flex;
  width: 48px;
  height: 48px;
  padding: 12px;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;

  border-radius: var(--Button-Radius, 24px);
  background: var(--neutral-10, #f1f1f3);
  border: none;
  cursor: pointer;
}

.dpiV3_crossout-button:hover {
  background: var(--neutral-30) !important;
}

.dpiV3_crossout-button:active,
.dpiV3_activeState {
  background: var(--neutral-20) !important;
}

.dpiV3_crossout-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--Focused, #0196d8);
  background: var(--neutral-10, #f1f1f3);
}

.dpiV3_inToast {
  background: none;
}
</style>
