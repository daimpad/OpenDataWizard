<template>
  <section :class="{ activeSection: isActive }" v-show="isActive">
    <FormKit type="group" :id="props.name" :name="props.name" :ref="props.name">
      <slot />
    </FormKit>
  </section>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';
import { dpiStepperKey } from '../utils/injectionKeys';

const props = defineProps<{
  name: string;
}>();

const dpiStepper = inject(dpiStepperKey);

if (!dpiStepper) {
  throw new Error('dpiStepper is not provided. Please make sure to use this component inside InputPage');
}

dpiStepper.registerStep(props.name);

const isActive = computed(() => dpiStepper.activeStep.value === props.name);

const {
  activeStep,
} = dpiStepper;
</script>

<style scoped></style>