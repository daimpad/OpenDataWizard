<template>
  <button
    tabindex="0"
    @mousedown="removeFocus"
    class="dpiV3_more-button"
    :class="[
      { dpiV3_activeState: isActive },
    ]"
    @click="handleClick"
    @focus="isFocused = true"
    @blur="isFocused = false"
    @keydown.enter="isActive = true"
    @keydown.space.prevent="isActive = true"
    @keyup.enter="isActive = false"
    @keyup.space="isActive = false"
  >
    <PhDotsThreeVertical :size="36" color="#3D4952" />
  </button>
</template>

<script>
import "../../config/styles/variables.css";
import { PhDotsThreeVertical } from "@phosphor-icons/vue";

export default {
  name: "MoreButton",
  emits: ["click"],
  components: {
    PhDotsThreeVertical,
  },
  data() {
    return {
      isFocused: false,
      isActive: false,
    };
  },
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
.dpiV3_more-button {
  display: flex;
  width: 48px;
  height: 48px;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  border-radius: var(--Button-Radius, 24px);
  background: none;
  border: none;
  cursor: pointer;
}

.dpiV3_more-button:hover {
  background: var(--neutral-30) !important;
}

.dpiV3_more-button:active,
.dpiV3_activeState{
  background: var(--neutral-20) !important;
}

.dpiV3_more-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--Focused, #0196d8);
}
</style>
