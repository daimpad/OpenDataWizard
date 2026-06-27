<template>
  <label ref="wrapper" :class="{
    dpiV3_customCheckbox: props.type === 'checkbox',
    dpiV3_customRadio: props.type === 'radio',
    dpiV3_deactivated: deactivatedInput,
    dpiV3_idleCheckbox: state === 'idle',
  }">
    <input :type="props.type" :disabled="deactivatedInput === true" :checked="state === 'checked' || state === 'idle'"
      v-model="isChecked" @change="emitChange" />
    <span class="dpiV3_checkmark" :class="{ dpiV3_focusable: !deactivatedInput, dpiV3_active: isActive }"
      :tabindex="!deactivatedInput ? '0' : '-1'" @keydown.space.prevent="handleKeydown"
      @keydown.enter.prevent="handleKeydown" @keyup.space="handleKeyup" @keyup.enter="handleKeyup">
      <PhCheckFat v-if="isChecked && state !== 'idle' && props.type === 'checkbox'" weight="fill"
        class="dpiV3_checkImg" />
      <PhMinus v-if="state === 'idle'" class="dpiV3_checkImg" weight="bold" />
      <PhCircle v-if="isChecked && state !== 'idle' && props.type === 'radio'" weight="fill"
        class="dpiV3_checkImgCircle" />
    </span>
    <div v-if="props.text != undefined" class="dpiV3_labelWrap">
      <span>{{ props.text.label }}</span>
      <span class="dpiV3_subtext">{{ props.text.subtext }}</span>
    </div>
  </label>
</template>
<script setup>
import { ref, computed } from "vue";
import { PhCheckFat, PhMinus, PhCircle } from "@phosphor-icons/vue";
const emit = defineEmits();
const wrapper = ref();
const deactivatedInput = computed(() => props.state === "disabled" || false);

const isActive = ref(false);
let isKeyPressed = false;

const handleKeydown = () => {
  if (!isKeyPressed) {
    isActive.value = true;
    toggleChecked();
    isKeyPressed = true;
  }
};

const handleKeyup = () => {
  isActive.value = false;
  isKeyPressed = false;
};

const props = defineProps({
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
  state: {
    type: String,
    required: true,
  },
});
const isChecked = ref(props.state === "checked");
const emitChange = () => {
  emit('change', isChecked.value);
};

const toggleChecked = () => {
  isChecked.value = !isChecked.value;
};
</script>
<style scoped>
.dpiV3_focusable {
  &:focus-visible {
    outline: 2px solid var(--blue-70, #0196d8);
  }
}

.dpiV3_deactivated {
  cursor: unset;

  span,
  div {
    opacity: 0.6;
  }

  .dpiV3_checkmark {
    &:hover {
      background: var(--blue-20, #d4edfc);
    }
  }
}

.dpiV3_customCheckbox,
.dpiV3_customRadio {
  cursor: pointer;
  color: var(--neutral80, #3d4952);
  white-space: nowrap;
  /* Copy/Copy-Small-Semibold */
  font-family: var(--font-family-secondary);
  font-size: var(--copy-small-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-small-semi-bold-font-weight, 600);
  line-height: var(--copy-small-semi-bold-line-height);

  /* 24px */
  display: inline-flex;
  gap: var(--Spacing-3, 16px);
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;

  div {
    .dpiV3_subtext {
      display: block;
      color: var(--neutral80, #3d4952);
      font-family: var(--text-default-font-family);
      font-size: var(--copy-small-regular-font-size);
      font-style: normal;
      font-weight: var(--copy-small-regular-font-weight);
      line-height: var(--copy-small-regular-line-height);
      /* 160% */
    }
  }

  .dpiV3_checkmark {
    width: 24px;
    height: 24px;
    border-radius: var(--Border-Radius, 8px);
    background: var(--blue-20, #d4edfc);

    &:hover {
      /* background: var(--blue-30, #AEDFF8); */
    }

    &:active {
      background: var(--blue-40, #67c5f0);
    }
  }
}

.dpiV3_customCheckbox.deactivated,
.dpiV3_customRadio.deactivated {
  cursor: unset;

  .dpiV3_checkmark {
    cursor: unset;

    &:hover {
      background: var(--blue-20, #d4edfc);
    }
  }
}

.dpiV3_customRadio {
  display: inline-flex;
  cursor: pointer;
  color: var(--neutral80, #3d4952);
  border-radius: var(--Button-Radius, 24px);
  gap: var(--Spacing-3, 16px);

  .dpiV3_checkmark {
    width: 24px;
    height: 24px;
    border-radius: var(--Button-Radius, 24px);
    background: var(--blue-20, #d4edfc);

    &:hover {
      /* background: var(--blue30, #AEDFF8); */
    }
  }
}

.dpiV3_customRadio input {
  display: none;
}

.dpiV3_customCheckbox input {
  display: none;
}

.dpiV3_checkmark {
  display: inline-flex;
  cursor: pointer;
  width: 24px;
  height: 24px;
  border-radius: var(--Border-Radius, 8px);
  background: var(--blue-20, #d4edfc);

  &:hover {
    background: var(--blue-30, #aedff8);
  }
}

.dpiV3_checkmark.active {
  background: var(--blue-40, #67c5f0);
  /* Active background color */
}

.dpiV3_checkImg {
  width: 16px;
  height: 16px;
  margin: auto;
  fill: var(--blue-70);
}

.dpiV3_checkImgCircle {
  width: 18px;
  height: 18px;
  margin: auto;
  fill: var(--blue-70);
}

.dpiV3_labelWrap {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  flex: 1 0 0;
}
</style>
