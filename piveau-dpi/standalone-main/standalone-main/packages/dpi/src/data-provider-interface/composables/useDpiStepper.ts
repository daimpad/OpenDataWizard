import { provide, ref, computed } from 'vue';
import { dpiStepperKey } from '../utils/injectionKeys';
import useSteps from '../utils/useSteps';

/**
 * Provides the stepper state and methods to manage stepper.
 */
export function useDpiStepper() {
  const { steps, activeStep, subSteps, stepPlugin, visitedSteps } = useSteps();
  const stepList = ref([] as string[]);
  const activeStepIndex = computed(() => stepList.value.indexOf(activeStep.value));

  const previousStep = computed(() => {
    const previousStepIndex = activeStepIndex.value - 1;
    if (previousStepIndex >= 0) {
      return stepList.value[previousStepIndex];
    }
    return '';
  });

  const nextStep = computed(() => {
    const nextStepIndex = activeStepIndex.value + 1;
    if (nextStepIndex < stepList.value.length) {
      return stepList.value[nextStepIndex];
    }
    return '';
  });

  const registerStep = (stepName: string) => {
    if (!stepList.value.includes(stepName)) {
      stepList.value.push(stepName);
    }
  };

  const goToNextStep = () => {
    const nextStepIndex = activeStepIndex.value + 1;
    if (nextStepIndex < stepList.value.length) {
      activeStep.value = stepList.value[nextStepIndex];
    }
  }

  const goToPreviousStep = () => {
    const previousStepIndex = activeStepIndex.value - 1;
    if (previousStepIndex >= 0) {
      activeStep.value = stepList.value[previousStepIndex];
    }
  }

  const goToStep = (stepName: string) => {
    if (stepList.value.includes(stepName)) {
      activeStep.value = stepName;
    }
  }

  const goToStepByIndex = (index: number) => {
    if (index >= 0 && index < stepList.value.length) {
      activeStep.value = stepList.value[index];
    }
  }

  provide(dpiStepperKey, {
    steps,
    activeStep,
    registerStep,
  });

  return {
    steps,
    subSteps,
    activeStep,
    activeStepIndex,
    previousStep,
    nextStep,
    visitedSteps,
    stepPlugin,
    stepList,
    registerStep,
    goToNextStep,
    goToPreviousStep,
    goToStep,
    goToStepByIndex,
  }
}
