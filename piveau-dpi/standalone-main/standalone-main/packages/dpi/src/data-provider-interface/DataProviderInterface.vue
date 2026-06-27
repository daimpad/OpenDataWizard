<script lang="ts">
import type { PropType } from 'vue'
import type { AutocompleteInstance } from './composables/aucotomplete'
import type { DpiContext } from './composables/useDpiContext'
import { useAsyncState, useDebounce, watchOnce, whenever } from '@vueuse/core'
import { computed, defineAsyncComponent, defineComponent, nextTick, provide, ref, toRef, toRefs, toValue, watch, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mapActions, mapGetters, useStore } from 'vuex'
import { useRuntimeEnv } from '../composables/useRuntimeEnv'
import TheErrorDialog from './components/TheErrorDialog.vue'
import { autocompleteKey, defaultAutocompleteAdapter, useAutocomplete } from './composables/aucotomplete'
import { setupDpiContext } from './composables/useDpiContext'
import { useDpiEditMode } from './composables/useDpiEditMode'
import { useFormValues } from './composables/useDpiFormValues'
import { useDpiSimpleLoader } from './composables/useDpiSimpleLoader'
import { useErrorDialog } from './composables/useErrorDialog'
import dpiSpecs from './config/dpi-spec-config'
import ButtonV3 from './HappyFlowComponents/ui/ButtonV3.vue'
import DpiModalBase from './HappyFlowComponents/ui/DpiModalBase.vue'

export default defineComponent({
  name: 'DataProviderInterface',
  components: {
    ButtonV3,
    InputPage: defineAsyncComponent(() => import('./views/InputPage.vue')),
    DpiModalBase,
    TheErrorDialog,
  },
  props: {
    name: {
      type: String,
      default: '',
    },
    dpiContext: {
      type: Object as PropType<DpiContext>,
      default: () => undefined,
    },
    autocomplete: {
      type: Object as PropType<AutocompleteInstance>,
      default: () => undefined,
    },
  },
  metaInfo() {
    return {
      title: `${this.$t('message.metadata.upload')} | ${this.$t('message.header.navigation.data.datasets')}`,
      meta: [
        { name: 'description', vmid: 'description', content: `${this.$t('message.datasets.meta.description')}` },
        { name: 'keywords', vmid: 'keywords', content: `${this.$env.metadata.keywords} ${this.$t('message.datasets.meta.description')}}` },
        { name: 'robots', content: 'noindex, follow' },
      ],
    }
  },
  data() {
    return {
      property: this.$route.params.property,
      id: this.$route.params.id,
    }
  },
  computed: {
    ...mapGetters('auth', [
      'getIsEditMode',
    ]),
    mode() {
      return this.property === 'catalogues'
        ? this.getIsEditMode
          ? this.$t('message.dataupload.menu.editCatalogue')
          : this.$t('message.dataupload.createNewCatalogue')
        : this.property === 'datasets'
          ? this.getIsEditMode
            ? this.$t('message.dataupload.menu.editDataset')
            : this.$t('message.dataupload.createNewDataset')
          : 'Edit Distribution'
    },
  },
  methods: {
    ...mapActions('dpiStore', [
      'saveLocalstorageValues',
    ]),
    ...mapActions('auth', [
      'populateDraftAndEdit',
    ]),
    getClearPath() {
      return `${this.$env.content.dataProviderInterface.basePath}/${this.property}?locale=${this.$i18n.locale}&clear=true`
    },
    handleScroll() {
      try {
        if (document.getElementById('stepperAnchor')?.offsetTop || 0 >= 35) {
          document.getElementById('stepperAnchor')?.classList.add('border-bottom-lightgray')
        }
        else {
          document.getElementById('stepperAnchor')?.classList.remove('border-bottom-lightgray')
        }
      }
      catch (error) {

      }
    },

  },
  created() {
    window.addEventListener('scroll', this.handleScroll)
    this.populateDraftAndEdit()
  },
  mounted() {
    this.saveLocalstorageValues(this.property)
  },
  unmounted() {
    window.removeEventListener('scroll', this.handleScroll)
  },
  setup(props) {
    const route = useRoute()
    const store = useStore()
    const env = useRuntimeEnv()
    const userSpec = env.content.dataProviderInterface.specification as 'dcatap' | 'dcatapde' | 'dcatapdeODB'
    const fallbackSpec = dpiSpecs[userSpec]
    const dpiContext = toRef(props, 'dpiContext')
    const { formValues } = useFormValues()
    const { openErrorDialog } = useErrorDialog()

    const resolvedDpiContext = computed<DpiContext>(() => {
      const _dpiContext = toValue(dpiContext)

      return {
        specification: fallbackSpec,
        specificationName: userSpec,
        edit: {
          enabled: route.query.edit === 'true',
          id: route.query.id as string || undefined,
          catalog: route.query.catalog as string || undefined,
          fromDraft: route.query.fromDraft === 'true',
        },
        ..._dpiContext,
      }
    })

    const specification = computed(() => {
      return resolvedDpiContext.value.specification
    })

    const specificationName = computed(() => {
      return resolvedDpiContext.value.specificationName
    })

    setupDpiContext(resolvedDpiContext)
    const defaultAutocompleteInstance = defaultAutocompleteAdapter({
      envs: env,
      dpiContext: resolvedDpiContext,
    })
    provide(autocompleteKey, props.autocomplete || defaultAutocompleteInstance.adapter)

    watchEffect(() => {
      store.dispatch('dpiStore/setSpecification', specification.value)
      store.dispatch('dpiStore/setSpecificationname', specificationName.value)
    })

    const key = computed(() => {
      return `${route.query.key}@${specificationName.value}`
    })

    const { isReady: isEditReady, result, inEditModeAndRptAvailable, parsingErrors, fetchError, isSimpleLoaderReady, isMaterialized, isLoading, jsonld } = useDpiEditMode(resolvedDpiContext)
    const formValuesReady = ref(false)
    watchEffect(async () => {
      if (formValuesReady.value)
        return
      if (inEditModeAndRptAvailable.value && isEditReady.value) {
        await nextTick()
        await nextTick()
        formValues.value = result.value
        console.log('resultus', result.value)

        // delay 2s before settings ready
        // setTimeout(() => {
        // }, 5000)

        formValuesReady.value = true
        // formValuesReady.value = true
      }
    })

    const isReady = computed(() => {
      return resolvedDpiContext.value.edit?.enabled
        ? isEditReady.value && formValuesReady.value
        : isEditReady.value
    })

    const showErrorModal = ref(false)
    const currentError = ref<{ code: string, message: string } | null>(null)

    // Check if we're in development mode based on the URL or environment
    // This is a simple heuristic that should work in most cases
    const isDevelopment = ref(true)
    // const isDevelopment = ref(import.meta.env.MODE === 'development')

    // Watch simpleLoaderErrors and show modal when there's at least one error
    watchEffect(() => {
      if (parsingErrors.value && parsingErrors.value.length > 0) {
        currentError.value = parsingErrors.value[0]
        showErrorModal.value = true
        openErrorDialog(new Error(parsingErrors.value[0].message))
      }
      else {
        showErrorModal.value = false
        currentError.value = null
      }
    })
    whenever(fetchError, () => {
      currentError.value = {
        code: 'fetch_failed',
        message: JSON.stringify(fetchError.value, null, 2) || 'Failed to fetch data',
      }
      showErrorModal.value = true
      openErrorDialog(new Error(JSON.stringify(fetchError.value, null, 2) || 'Failed to fetch data'), `Der Datensatz '${route.query?.id}' scheint nicht verfügbar zu sein.`)
    }, { immediate: true })

    // if (import.meta.env.DEV) {
    //   const debouncedFormValues = useDebounce(formValues, 500)
    //   watch(debouncedFormValues, () => {
    //     console.log('Form values changed:', debouncedFormValues.value)
    //   })
    // }

    return {
      result,
      resolvedDpiContext,
      key,
      isReady,
      formValues,
      showErrorModal,
      currentError,
      isDevelopment,
      fetchError,
      isSimpleLoaderReady,
      isMaterialized,
      isLoading,
      jsonld,
      inEditModeAndRptAvailable
    }
  },
})
</script>

<template>
  <!-- START for developing purposes - delete afterwards -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet"
  >
  <!-- END -->

  <div
    :key="property"
    class="dpi dpiV3_dpi"
  >
    <div>
      <TheErrorDialog />
    </div>
    <!-- CONTENT -->
    <router-view v-if="isReady" ref="view" :key="key" />
    <div v-else class="d-flex justify-content-center align-items-center" style="height: 100dvh;width: 100%;">
      <!-- todo spinner -->
    </div>
  </div>
</template>

<style lang="scss">
// Modal styling
.error-textarea {
  width: 100%;
  min-height: 150px;
  font-family: monospace;
  padding: 8px;
  border: 1px solid var(--neutral30, #D5D7DA);
  border-radius: 4px;
  background-color: var(--neutral5, #FAFAFB);
  resize: vertical;
  overflow: auto;
  white-space: pre;
  font-size: 14px;
  line-height: 1.4;
}

.dpiV3_modalTitle {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: var(--text-primary-default, #0B1A25);
  font-family: var(--font-family-primary);
  line-height: 1.2;
}

.development-notice {
  width: 100%;
  font-size: 0.875rem;
  color: #666;
  padding: 0.5rem;
  background-color: transparent;
  border-radius: 4px;
}

.dpiV3_modalErrorContent {
  display: flex;
  flex-direction: column;
  min-height: 150px;
  height: 100%;
  justify-content: space-between;
  margin-bottom: 1.5rem;

  .dpiV3_modalErrorMain {
    flex-grow: 1;

    .error-message {
      font-weight: 500;
      margin-bottom: 1rem;
      font-size: 1rem;
      line-height: 1.5;
    }
  }

  .dpiV3_modalErrorFooter {
    margin-top: 1rem;
  }
}

.dpiV3_modalActions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  justify-content: flex-end;
}

.border-bottom-lightgray {
  border-bottom: 1px solid lightgray;
}

.stickyStepper {
  position: sticky;
  top: 0;
  background: #ffffff;
  z-index: 999;
}

.stickyStepper .SSfirstRow {
  margin: 1vh 0;
  display: flex;
  align-items: center;
}

.stickyStepper .stickyNav {
  border-left: 1px solid black;
  margin-left: 1vh;
  padding-left: 1vh;
}

#stepper {
  width: 100% !important;
}

#input {
  padding: 10px;
}

.small-headline {
  font-size: 1.5rem;
  min-width: max-content;
}

.property {
  margin: 20px;
  background-color: #ffffff;
  border: solid 0.5px rgb(225, 225, 225);
  margin-top: 30px;
}

.infoBox .material-icons {
  font-size: 20px;
  vertical-align: text-bottom;
  margin-right: 5px;
  margin-bottom: 1px;
}

.infoBox {
  width: 100%;
  height: 30%;
  background-color: #f7f7f7;
  padding: 5%;
  border-radius: 0.25rem;
  margin-top: 20px;

  .input_subpage_nav {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    padding: 15px;
  }
}

.besides {
  .formkit-input-group-repeatable {
    display: flex;
    flex-direction: row;
    background-color: transparent;
    padding: 0px;
  }
}

.main {
  width: 75%;
  margin: 0 5px 0 5px;
}

.sub {
  width: 20%;
  margin: 0 5px 0 5px;
}

#subStepperBox {
  position: sticky;
  top: 154px;
  z-index: 10;
  width: 100%;
  padding: 0 10%;

  .step-progress__bar {
    border-top: none !important;
  }

  .step-progress__step--active {
    .step-progress__step-label {
      background-color: lightsteelblue !important;
    }

  }
}

.step-progress__step span {

  color: white !important;

}

// Stepper Customizing -------------

// #stepper,
// #subStepper {
//   .step-progress__step {
//     display: flex;
//     align-items: center;
//     justify-content: center;
//     height: 70%;
//     width: 20%;
//     display: flex;
//     align-items: center;
//     z-index: 1;

//     span {
//       color: grey;
//       font-size: 18px;
//       display: none;
//     }

//     div {
//       padding: 1rem;
//       height: 100%;
//       display: flex;
//       align-items: center;
//       color: white;
//       font-weight: 300;

//     }

//     .step-progress__step-label {

//       background: lightgrey;
//       background-size: 400% 400%;
//       background-position: 100% 0%;
//       transition: all 300ms ease-in-out;
//       border-right: 1px white solid;
//       font-size: 14px;
//       display: flex;
//       align-items: center;
//       justify-content: center;

//     }

//     .step-progress__step-label:hover {
//       background-position: 65% 0%;
//       color: black;

//     }

//   }

//   .step-progress__step--active {

//     z-index: 7 !important;

//     span {
//       color: black;
//     }

//     div {
//       background: white;
//     }

//     .step-progress__step-label {
//       background: rgb(236, 236, 236);
//       background-position: 50% 0%;
//       box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2), 0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12) !important;
//       transform: scale(1.1);
//       z-index: 8;
//       font-size: 16px;
//     }

//   }

//   .step-progress__step--valid {
//     div {
//       color: white;

//     }

//     .step-progress__step-label {
//       background: rgba(0, 235, 0, 0.2);
//       color: lightslategrey;

//     }

//     .step-progress__step-label:hover {
//       color: black;
//     }
//   }

//   .step-progress__step--active .step-progress__step-label {
//     color: rgb(31, 31, 31);
//   }

//   .step-progress__wrapper-after {
//     display: none;
//   }

//   .step-progress__step-icon {
//     display: none !important;
//   }

//   .step-progress__bar {
//     margin: 0;
//     height: 5rem;
//     border-top: 1px solid lightslategray;

//   }

//   .step-progress__step-label {
//     position: unset;
//     transform: unset;
//     flex-grow: 1;
//   }

//   .step-progress__step {}

//   .step-progress__wrapper-before {
//     display: none !important;
//   }

//   #stepper .step-progress__step::after {
//     display: none !important;
//   }
// }
#stepper .step-progress__step {
  border: solid white 20px;
}

#stepper .step-progress__step-icon,
#subStepper .step-progress__step-icon {
  font-size: 25px;
}

.step-progress__step-label {
  cursor: pointer;
}

// Input Form Margins & Borders ----

.formkit-input[data-classification=group] [data-is-repeatable] {
  border: none;
  padding: 1em 1em 1em 0em;
}

.formkit-input[data-classification=group] [data-is-repeatable] .formkit-input-group-repeatable {
  border-bottom: none;
}

.formkit-input-element--checkbox {
  margin-right: 5px;
}

.formkit-input-wrapper {
  font-family: "Ubuntu";
}

.formkit-input[data-classification=button] button[data-ghost] {
  font-weight: 400;
}

.formkit-input-error {
  color: #e13737 !important;
  font-weight: 400 !important;
}

// General Formkit Styling ----

.formkit {
  &-input {
    .formkit {
      &-input {
        &-element {
          max-width: 100%;
        }

        &-error {
          font-weight: bold;
        }
      }
    }
  }

  .formkit-input-group-add-more {
    display: flex;
    justify-content: flex-end;

    button {
      border: black;
    }
  }

  .formkit-input {
    &[data-classification="text"] .formkit-input-wrapper {
      display: flex;
      flex-direction: column;
    }

    &[data-classification="select"] .formkit-input-wrapper {
      display: flex;
      flex-direction: column;
    }
  }

  .formkit-input[data-classification="button"] {
    button {
      &[data-ghost] {
        color: white;
        background-color: #001d85;
        border-color: #001d85;
        border-radius: 1.875rem;

        &:hover {
          background-color: #196fd2;
          border-color: #196fd2;
        }
      }
    }
  }
}

.formkit-input.besides {
  border-bottom: 1px solid lightgrey !important;
}

.formkit-input-label {
  font-weight: 500 !important;
}

.formkit-input-element {

  &--textarea {
    width: 100%;
  }
}

.formkit-input-element--group {
  display: block !important;
}

.formkit-input.besides>.formkit-input-wrapper>.formkit-input-label {

  text-decoration: underline !important;
}

// #stepper,
// #subStepper {

//   .step-progress__step::after {
//     display: none;
//   }

//   .step-progress__step-label {
//     cursor: pointer;
//   }
// }
.tooltip-inner {
  all: unset;
  display: flex;
  min-height: 24px;
  padding: var(--Spacing-1, 4px) var(--Spacing-2, 8px) !important;
  justify-content: center;
  align-items: center;
  border-radius: var(--Border-Radius, 8px) !important;
  background: var(--background-tooltip, #25333D) !important;
  color: var(--text-tooltip, #FFF) !important;

  /* Copy/Copy-Mini-Regular */
  font-family: Inter;
  font-size: var(--copy-mini-regular-font-size);
  font-style: normal;
  font-weight: var(--copy-mini-regular-font-weight);
  line-height: var(--copy-mini-regular-line-height) !important;
  /* 133.333% */
}

.bs-tooltip-top {
  padding-bottom: 4px !important;
}

.tooltip {

  .arrow {
    display: none !important;
  }
}
</style>
