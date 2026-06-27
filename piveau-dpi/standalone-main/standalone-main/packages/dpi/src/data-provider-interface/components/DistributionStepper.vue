<template>
  <FormKit type="form" :actions="false" :plugins="[stepPlugin]">
    <div name="distribution-stepper" class="singleDistributions">
      <div class="disInfoWrapper" v-if="!isCollapsed">
        <ul class="steps">
          <li v-for="(step, stepName, index) in steps" :key="index" class="step"
            :data-step-active="activeStep === stepName" :data-step-valid="step.valid && step.errorCount === 0" :class="{
              activeItem: activeStep === stepName, inactiveStep: stepName != activeStep, 'has-errors': checkStepValidity(stepName)
            }" @click="activeStep = stepName; indexOfDis = index + 1">

            <div class="stepBubbleWrap">
              <div class="circle stepCircle">{{ convertToRoman(index + 1) }}</div>
              <span v-if="checkStepValidity(stepName)" class="step--errors"
                v-text="step.errorCount + step.blockingCount" />
              {{ $t('message.dataupload.steps.' + stepName + 'Step') }}
            </div>
            <div
              v-if="index + 1 != Object.keys(getNavSteps($env.content.dataProviderInterface.specification).distributions).length"
              class="seperatorHorizontalStepper">
            </div>
            <div v-if="activeStep === 'overview'" class="seperatorHorizontalStepper"></div>
          </li>
          <li class="step inactiveStep" v-if="activeStep === 'overview'">
            <div class="circle stepCircle"></div>
          </li>
        </ul>
        <div class="d-flex flex-column w-100">
          <div v-for="(stepName, index) in getNavSteps($env.content.dataProviderInterface.specification).distributions"
            :key="index">
            <InputPageStep :name="stepName">
              <!-- <PropertyChooser></PropertyChooser> -->
              <FormKitSchema :schema="schema[stepName]" :library="library" />
              <p v-if="stepName === 'Mandatory'" class="p-1"> <b>*</b> {{ $t('message.dataupload.steps.MandatoryStep')
                }}</p>
            </InputPageStep>
          </div>
        </div>
      </div>
    </div>
  </FormKit>

  <div class="m-3 d-flex justify-content-end">

    <button v-if="indexOfDis > 1" type="button" class="btn btn-secondary"
      @click="goToPreviousStep(); scrollToTop(); indexOfDis = indexOfDis - 1">{{
        $t('message.dataupload.steps.previousDisStep')
      }}</button>
    <button v-if="indexOfDis < 4" type="button" class="btn btn-secondary ml-3"
      @click="goToNextStep(); scrollToTop(); indexOfDis = indexOfDis + 1">{{
        $t('message.dataupload.steps.nextDisStep')
      }}</button>
  </div>
</template>

<script>
import { defineComponent, markRaw } from 'vue';
import { mapGetters } from 'vuex';
import { useDpiStepper } from '../composables/useDpiStepper';
import InputPageStep from './InputPageStep.vue';
import SelectControlledGroup from './SelectControlledGroup.vue';
import { useWindowScroll } from '@vueuse/core'


export default defineComponent({
  props: {
    name: {
      type: String,
      default: '',
    },
    index: {
      required: true
    },
    schema: {
      required: true,
      type: Object,
    },
    context: {
      type: Object,
    },
    distributionIsCollapsed: {
      type: Boolean,
    },
    collapseDistributions: {
      type: Function,
    },
    deleteDistribution: {
      type: Function,
    },
  },
  components: {
    InputPageStep,
  },
  data() {
    return {
      isCollapsed: false,
      camel2title: (str) =>
        str
          .replace(/([A-Z])/g, (match) => ` ${match}`)
          .replace(/^./, (match) => match.toUpperCase())
          .trim(),
      isActive: false,
      indexOfDis: 1
    }
  },
  methods: {
    editDistribution() {
      this.isCollapsed = !this.isCollapsed;
      this.collapseDistributions(this.index);
    },
    scrollToTop() {
      let { x, y } = useWindowScroll({ behavior: 'smooth' })
      y.value = 0

    },
    convertToRoman(element) {
      if (element === 1) {
        return "A"
      }
      if (element === 2) {
        return "B"
      }
      if (element === 3) {
        return "C"
      }
      if (element === 4) {
        return "D"
      }
    }
  },
  computed: {
    ...mapGetters('dpiStore', [
      'getNavSteps'
    ]),
    getName() {
      return this.name
        || values['Distributions']['distributionList'][this.name - 1]['Mandatory']['dcat:accessURL'][0]['@id'];
    },
  },
  watch: {
    distributionIsCollapsed: {
      handler(newValue) {
        this.isCollapsed = newValue;
      },
    },
  },
  created() { },
  setup() {
    const {
      steps,
      activeStep,
      visitedSteps,
      previousStep,
      nextStep,
      stepPlugin,
      goToNextStep,
      goToPreviousStep,
    } = useDpiStepper();

    const checkStepValidity = (stepName) => {
      return (steps[stepName].errorCount > 0 || steps[stepName].blockingCount > 0) && visitedSteps.value.includes(stepName)
    }

    const library = markRaw({
      SelectControlledGroup,
    })

    return {
      steps,
      visitedSteps,
      activeStep,
      previousStep,
      nextStep,
      stepPlugin,
      checkStepValidity,
      goToNextStep,
      goToPreviousStep,

      library,
    }
  }
});
</script>

<style></style>