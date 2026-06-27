import { type Ref, type InjectionKey } from "vue";

export const dpiStepperKey: InjectionKey<{
  steps: Record<string, any>;
  activeStep: Ref<string>;
  registerStep: (step: string) => void;
}> = Symbol('dpiStepperKey');
