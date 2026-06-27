<template>
  <button
  type="button"
    tabindex="0"
    @mousedown="removeFocus"
    class="dpiV3_open-close-button"
    :class="[{ dpiV3_activeState: isActive }]"
    @click="handleClick"
    @focus="isFocused = true"
    @blur="isFocused = false"
    @keydown.enter="isActive = true"
    @keydown.space.prevent="isActive = true"
    @keyup.enter="isActive = false"
    @keyup.space="isActive = false"
  >
    <PhCaretDown
      v-if="!expanded"
      :color="'var(--neutral-60, #3D4952)'"
      :size="32"
    />
    <PhCaretUp
      v-if="expanded"
      :color="'var(--neutral-60, #3D4952)'"
      :size="32"
    />
  </button>
</template>

<script>
import "../../config/styles/variables.css";
import { PhCaretDown, PhCaretUp } from "@phosphor-icons/vue";

export default {
  name: "CloseOpenButtonV3",
  emits: ["click"],
  components: {
    PhCaretDown,
    PhCaretUp,
  },
  data() {
    return {
      isFocused: false,
      isActive: false,
    };
  },
  props: {
    expanded: {
      type: Boolean,
      required: true,
    },
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
.dpiV3_open-close-button {
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

.dpiV3_open-close-button:hover {
  background: var(--neutral-10) !important;
}

.dpiV3_open-close-button:active,
.dpiV3_activeState {
  background: var(--neutral-20) !important;
}

.dpiV3_open-close-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--Focused, #0196d8);
}
</style>
