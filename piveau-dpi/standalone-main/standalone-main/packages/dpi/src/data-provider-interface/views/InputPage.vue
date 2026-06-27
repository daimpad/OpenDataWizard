<script>
import { getNode } from "@formkit/core";
import { defaultConfig, plugin } from "@formkit/vue";
import {
  PhCheckCircle,
  PhLightbulb,
  PhNumberCircleFive,
} from "@phosphor-icons/vue";
import { useWindowScroll } from "@vueuse/core";
import $ from "jquery";
import { has, isEmpty, isNil } from "lodash";
import {
  computed,
  defineComponent,
  getCurrentInstance,
  markRaw,
  ref,
} from "vue";
import { useI18n } from "vue-i18n";
import { mapActions, mapGetters } from "vuex";
import InputPageStep from "../components/InputPageStep.vue";
import Navigation from "../components/Navigation.vue";
import {
  dpiContextKey,
  useDpiContext,
  useEditModeInfo,
  useFormSchema,
} from "../composables/index";
import { useFormValues } from "../composables/useDpiFormValues";
import { useDpiStepper } from "../composables/useDpiStepper";
import sendDataToAPI from "../config/dcatapdeHappyFlow/converter";
import Circle from "../HappyFlowComponents/img/Circle.svg";
import CircleFill from "../HappyFlowComponents/img/CircleFill.svg";
import Selected1 from "../HappyFlowComponents/img/Selected1.svg";
import Selected1_2 from "../HappyFlowComponents/img/Selected1_2.svg";
import Selected2 from "../HappyFlowComponents/img/Selected2.svg";
import Selected2_2 from "../HappyFlowComponents/img/Selected2_2.svg";
import Selected3 from "../HappyFlowComponents/img/Selected3.svg";
import Selected3_2 from "../HappyFlowComponents/img/Selected3_2.svg";
import Selected4 from "../HappyFlowComponents/img/Selected4.svg";
import Selected4_2 from "../HappyFlowComponents/img/Selected4_2.svg";
import Selected5 from "../HappyFlowComponents/img/Selected5.svg";
import Selected5_2 from "../HappyFlowComponents/img/Selected5_2.svg";
import { eventBus } from "../HappyFlowComponents/services/eventBus";
import ButtonV3 from "../HappyFlowComponents/ui/ButtonV3.vue";
import Illustration from "../HappyFlowComponents/ui/Illustration.vue";
import LogoV3 from "../HappyFlowComponents/ui/LogoV3.vue";
import ModalSimpleV3 from "../HappyFlowComponents/ui/ModalSimpleV3.vue";
import TextButtonSmall from "../HappyFlowComponents/ui/TextButtonSmall.vue";
import DistributionInputPage from "./DistributionInputPage.vue";
import OverviewPage from "./OverviewPage.vue";
import PropertyChooser from "./PropertyChooser.vue";
import "../config/styles/variables.css";
import "../config/styles/typography.css";

export default defineComponent({
  props: {
    property: {
      required: true,
      type: String,
    },
    context: {},
    id: {
      type: String,
    },
  },
  data() {
    return {
      circles: [Circle, CircleFill],
      images: [Selected1, Selected2, Selected3, Selected4, Selected5],
      selectedImages: [
        Selected1_2,
        Selected2_2,
        Selected3_2,
        Selected4_2,
        Selected5_2,
      ],
      heightActiveSec: "10vh",
      // formValues: {},
      offsetTopStepper: "60px",
      info: {},
      catalogues: [],
      substepCounter: 0,
      stepCounter: 0,
      byte: true,
      expandall: true,

      modalSimpleConf: {
        button: "Ja, abbrechen",
        header: "Wollen Sie wirklich abbrechen?",
        text: "Beim Abbrechen gehen alle eingegebenen Daten unwiderruflich verloren und können nicht wiederhergestellt werden.",
        action: "cancel",
      },
      // activeSubStep: "",

      // steps:{},
      camel2title: (str) =>
        str
          .replace(/([A-Z])/g, (match) => ` ${match}`)
          .replace(/^./, (match) => match.toUpperCase())
          .trim(),
    };
  },
  components: {
    InputPageStep,
    DistributionInputPage,
    PropertyChooser,
    Navigation,
    LogoV3,
    ButtonV3,
    TextButtonSmall,
    Illustration,
    PhLightbulb,
    PhCheckCircle,
    PhNumberCircleFive,
    ModalSimpleV3,
  },
  computed: {
    ...mapGetters("auth", [
      "getIsEditMode",
      "getUserCatalogIds",
      "getUserData",
    ]),
    ...mapGetters("dpiStore", ["getNavSteps", "getDeleteDistributionInline"]),
    getTitleStep() {
      return Object.keys(this.formValues).filter((key) =>
        has(this.formValues[key], "dct:title")
      )[0];
    },
    createIDFromTitle() {
      const title =
        this.formValues[this.getTitleStep]["dct:title"][0]["@value"];

      if (title != undefined) {
        return title.toLowerCase().replace(/ /g, "-");
      } else {
      }
    },
    getFirstTitleFromForm() {
      try {
        const allValues = this.formValues[this.getTitleStep];

        return has(allValues, "dct:title") &&
          allValues["dct:title"].length > 0 &&
          has(allValues["dct:title"][0], "@value") &&
          !isNil(allValues["dct:title"][0], "@value")
          ? allValues["dct:title"][0]["@value"]
          : "";
      } catch (error) {}
    },
    isInput() {
      return (
        this.$route.params.page !== "overview" &&
        this.$route.params.page !== "distoverview"
      );
    },
  },

  methods: {
    ...mapActions("auth", ["setIsEditMode", "setIsDraft"]),
    ...mapActions("dpiStore", [
      "saveFormValues",
      "saveLocalstorageValues",
      "clearAll",
    ]),
    update() {
      this.$forceUpdate();
    },
    // Route to the RAP Page
    goToRAP() {
      this.fillData();
      this.activeStep = "ReviewAndPublish";
      this.activeSubStep = Object.keys(getNode(this.activeStep).value)[0];
    },
    getCircles(index) {
      if (index === this.substepCounter) {
        return this.circles[1];
      } else {
        return this.circles[0];
      }
    },
    validateStep() {
      this.formValues[this.activeStep]?.[this.activeSubStep]?.[0]?.isValid ===
        true ||
      this.formValues[this.activeStep][this.activeSubStep]?.isValid === true
        ? this.navTrigger("next")
        : (this.formValues[this.activeStep][this.activeSubStep]?.[0]
            ? (this.formValues[this.activeStep][
                this.activeSubStep
              ][0].isValid = false)
            : (this.formValues[this.activeStep][
                this.activeSubStep
              ].isValid = false),
          this.handleDistributionsValidation(this.activeSubStep));
    },
    handleDistributionsValidation(data) {
      eventBus.emit("nextClicked", data);
    },
    gotToHome() {
      window.location.href = this.$router.resolve({
        name: "DPI-Home-HappyFlow",
      }).href;
    },
    navTrigger(value, i) {
      try {
        // Check if step is valid
        let steplist = [];
        Object.keys(this.steps).forEach((key, index) => {
          let item = {
            step: key,
            substeps: this.subSteps[index],
            substepCount: Object.keys(this.subSteps[index]).length,
          };
          steplist.push(item);
        });
        if (value === "next") {
          if (steplist[this.stepCounter].substepCount > 1) {
            if (
              this.substepCounter + 1 ===
              steplist[this.stepCounter].substepCount
            ) {
              this.stepCounter++;
              this.activeStep = steplist[this.stepCounter].step;
              this.activeSubStep = Object.keys(
                steplist[this.stepCounter].substeps
              )[0];
              this.substepCounter = 0;
            } else {
              this.substepCounter++;
              this.activeSubStep = Object.keys(
                steplist[this.stepCounter].substeps
              )[this.substepCounter];
            }
            this.handleDistributionsValidation(this.activeSubStep);
            // Das ist hochexperimentell - setze den review and publish step hier auf valid um sicherzustellen, dass Daten vorhanden sind bevor diese geladen werden
            if (this.activeStep === "ReviewAndPublish") {
              this.formValues["ReviewAndPublish"]["reviewAndPublishPage"] = [
                { isValid: true },
              ];
              // getNode(this.activeStep).value[
              //   this.activeSubStep
              // ][0].isValid = true
            }
          } else {
            if (this.stepCounter != steplist.length - 1) {
              this.stepCounter++;
              this.activeSubStep = Object.keys(
                steplist[this.stepCounter].substeps
              )[0];
              this.activeStep = steplist[this.stepCounter].step;
            }
          }
        }
        if (value === "prev") {
          if (steplist[this.stepCounter].substepCount > 1) {
            if (this.substepCounter > 0) {
              this.substepCounter--;
              this.activeSubStep = Object.keys(
                steplist[this.stepCounter].substeps
              )[this.substepCounter];
            } else {
              this.stepCounter--;
              this.activeStep = steplist[this.stepCounter].step;
              this.substepCounter =
                Object.keys(steplist[this.stepCounter].substeps).length - 1;
              this.activeSubStep = Object.keys(
                steplist[this.stepCounter].substeps
              )[this.substepCounter];
            }
          } else {
            this.substepCounter =
              Object.keys(steplist[this.stepCounter - 1].substeps).length - 1;
            this.stepCounter--;
            this.activeSubStep = Object.keys(
              steplist[this.stepCounter].substeps
            )[this.substepCounter];
            this.activeStep = steplist[this.stepCounter].step;
          }
        }
        if (value === "publish") {
          let actionName = "auth/createDataset";
          sendDataToAPI(
            this.formValues,
            this.dpiContext,
            this.getUserData,
            this.$env.api.hubUrl
          )
            .then((data) => {
              this.dispatchDataToDPI(
                data.body,
                actionName,
                data.actionParams,
                "publish"
              );
            })
            .catch((error) => {
              console.error("Fehler beim Senden der Daten:", error);
            });
          setTimeout(() => {
            window.location.href = this.$router.resolve({
              name: "DPI-Home-HappyFlow",
            }).href;
          }, 1000);
        }
        if (value === "depublish") {
          let actionName = "auth/depublishById";
          sendDataToAPI(
            this.formValues,
            this.dpiContext,
            this.getUserData,
            this.$env.api.hubUrl
          )
            .then((data) => {
              this.dispatchDataToDPI(
                data.body,
                actionName,
                data.actionParams,
                "depublish"
              );
            })
            .catch((error) => {
              console.error("Fehler beim Senden der Daten:", error);
            });
          setTimeout(() => {
            window.location.href = this.$router.resolve({
              name: "DPI-Home-HappyFlow",
            }).href;
          }, 1000);
        }
        if (value === "draft") {
          // let uploadUrl = `${this.$env.api.hubUrl}drafts/datasets/${datasetId}?catalogue=${catalogId}`;
          let actionName = "auth/createUserDraft";

          sendDataToAPI(
            this.formValues,
            this.dpiContext,
            this.getUserData,
            this.$env.api.hubUrl
          )
            .then((data) => {
              this.dispatchDataToDPI(
                data.body,
                actionName,
                data.actionParams,
                "draft"
              );
            })
            .catch((error) => {
              console.error("Fehler beim Senden der Daten:", error);
            });
          setTimeout(() => {
            // Seitenreload und Routing zu DPI-Home-HappyFlow
            window.location.href = this.$router.resolve({
              name: "DPI-Home-HappyFlow",
            }).href;
          }, 1000);
        }
      } catch (error) {
        console.log(error);
      }
    },
    async dispatchDataToDPI(body, actionName, actionParams, mode) {
      // console.log("body:", body, actionName, "params:", actionParams, mode);

      try {
        //  need to check if the ID is unique
        const idIsUnqiue =
          // this.idunique(datasetId)
          true;

        if (idIsUnqiue) {
          if (mode === "publish") {
            actionParams.token = this.getUserData.rtpToken;

            let publishDraft =
              localStorage.getItem("dpi_draftmode") === "true" &&
              localStorage.getItem("dpi_editmode") === "true";

            if (publishDraft) {
              actionParams.url = `${this.$env.api.hubUrl}catalogues/${actionParams.catalog}/datasets/origin?originalId=${actionParams.id}`;
              await this.$store.dispatch("auth/createUserDraft", actionParams);
              // This is to save the draft before it gets published
              actionParams.url = `${this.$env.api.hubUrl}drafts/datasets/publish/${actionParams.id}?catalogue=${actionParams.catalog}`;
            } else {
              actionParams.url = `${this.$env.api.hubUrl}catalogues/${actionParams.catalog}/datasets/origin?originalId=${actionParams.id}`;
            }
            await this.$store.dispatch(actionName, actionParams);
          }
          if (mode === "draft") {
            await this.$store.dispatch(actionName, actionParams);
          }
          if (mode === "depublish") {
            // await this.$store.dispatch(actionName, actionParams);

            await this.$store.dispatch("auth/putDatasetToDraft", {
              id: actionParams.id,
              catalog: actionParams.catalog,
              title: actionParams.title,
              description: actionParams.description,
            });
            // Need to overwrite the depublished Dataset EXPERIMENTAL
            actionParams.url = `${this.$env.api.hubUrl}catalogues/${actionParams.catalog}/datasets/origin?originalId=${actionParams.id}`;
            await this.$store.dispatch("auth/createUserDraft", actionParams);
          }
          // await new Promise(resolve => setTimeout(resolve, 250));

          this.$Progress?.finish();

          // if (mode === "createcatalogue") this.createCatalogue(datasetId);
          // if (mode === "dataset") this.createDataset(datasetId);
          // if (mode === "draft") this.createDraft();

          // store needs to be reset
          this.clearAll();
        } else {
          this.$Progress?.fail();
          this.handleIDError();
        }
      } catch (err) {
        console.error(err);
        this.$Progress?.fail();
        // this.showSnackbar({ message: "Network Error", variant: "error" });
      }
    },
    dropdownCLick() {
      const h4Elements = document.querySelectorAll(".formkitProperty h4");

      if (this.expandall) {
        h4Elements.forEach((h4Element, index) => {
          h4Element.classList.add("dpiChevUp");
          h4Element.nextElementSibling.classList.remove("d-none");
        });
      }
      if (!this.expandall) {
        h4Elements.forEach((h4Element, index) => {
          h4Element.classList.remove("dpiChevUp");
          h4Element.nextElementSibling.classList.add("d-none");
        });
      }
    },
    clearForm() {
      this.$formkit.reset("dpi");
    },
    scrollToTop() {
      window.scrollTo(0, 0);
    },
    initInputPage() {
      this.activeSubStep = this.activeSubStep || "Landing";
      const instance = getCurrentInstance();
      const env = instance.appContext.app.config.globalProperties.$env;
      // adding validation of modified and issued based on edit mode
      // no validation in edit mode

      // get step name where issued and modified are included
      const initialSchema = this.getSchema(this.property).value;
      const stepWithDates = Object.keys(initialSchema).find(
        (key) =>
          initialSchema[key].map((el) => el.name).includes("dct:issued") ||
          initialSchema[key].map((el) => el.name).includes("dct:modified")
      );

      this.$formkit.setLocale("de");
      // console.log(this.$formkit);

      // this.$formkit.getValidationMessages()
      if (
        localStorage.getItem("dpi_editmode") === "true" &&
        stepWithDates != undefined
      ) {
        initialSchema[stepWithDates].forEach((el) => {
          if (el.identifier === "issued" || el.identifier === "modified") {
            el.children[1].props.else.validation = "";
            el.children[1].props.else["validation-visibility"] = "";

            el.children[1].props.then.validation = "";
            el.children[1].props.then["validation-visibility"] = "";
            el.children[1].props.then.validation = "";
            el.children[1].props.then["validation-visibility"] = "";
          }
        });
      }

      if (localStorage.getItem("dpi_editmode") === "false") {
        this.setIsDraft(false);
        this.setIsEditMode(false);
      }

      // this.saveLocalstorageValues(this.property); // saves values from localStorage to vuex store
      // const existingValues = this.$store.getters["dpiStore/getRawValues"]({
      //   property: this.property,
      // });

      // only overwrite empty object if there are values
      // if (!isEmpty(existingValues)) this.formValues = existingValues;

      this.$nextTick(() => {
        $('[data-bs-toggle="tooltip"]').tooltip({
          container: "body",
        });
        setTimeout(() => {
          const h4Elements = document.querySelectorAll(".formkitProperty h4");
          h4Elements.forEach((h4Element, index) => {
            // Added the clickeffect to the headers of the individual properties

            if (
              !h4Element.parentElement.parentElement.classList.contains(
                "formkitWrapRepeatable"
              )
            ) {
              if (index != 0 && index != 1 && index != 2 && index != 3) {
                h4Element.nextElementSibling.classList.toggle("d-none");
              }
              h4Element.addEventListener("click", () => {
                h4Element.classList.toggle("dpiChevUp");
                h4Element.nextElementSibling.classList.toggle("d-none");
              });
            }
          });
          // Observe the validity of the individual properties
          const elements = document.querySelectorAll(".formkitProperty");
          const attributeChangedCallback = (mutationsList) => {
            for (const mutation of mutationsList) {
              if (mutation.type === "attributes") {
                if (mutation.target.getAttribute("data-invalid") === "true") {
                  try {
                    if (
                      mutation.target.parentNode.parentNode.parentNode
                        .previousElementSibling.tagName === "H4"
                    ) {
                      mutation.target.parentNode.parentNode.parentNode.previousElementSibling.classList.add(
                        "isInvalidProperty"
                      );
                    }
                  } catch (error) {}
                  try {
                    if (
                      mutation.target.previousElementSibling.tagName === "H4"
                    ) {
                      mutation.target.previousElementSibling.classList.add(
                        "isInvalidProperty"
                      );
                    }
                  } catch (error) {}
                  try {
                    if (
                      mutation.target.parentNode.previousElementSibling
                        .tagName === "H4"
                    ) {
                      mutation.target.parentNode.previousElementSibling.classList.add(
                        "isInvalidProperty"
                      );
                    }
                  } catch (error) {}
                }
                if (
                  mutation.target.getAttribute("data-invalid") === null ||
                  mutation.target.getAttribute("data-invalid") === "false"
                ) {
                  try {
                    if (
                      mutation.target.parentNode.parentNode.parentNode
                        .previousElementSibling.tagName === "H4"
                    ) {
                      mutation.target.parentNode.parentNode.parentNode.previousElementSibling.classList.remove(
                        "isInvalidProperty"
                      );
                    }
                  } catch (error) {}
                  try {
                    if (
                      mutation.target.previousElementSibling.tagName === "H4"
                    ) {
                      mutation.target.previousElementSibling.classList.remove(
                        "isInvalidProperty"
                      );
                    }
                  } catch (error) {}
                  try {
                    if (
                      mutation.target.parentNode.previousElementSibling
                        .tagName === "H4"
                    ) {
                      mutation.target.parentNode.previousElementSibling.classList.remove(
                        "isInvalidProperty"
                      );
                    }
                  } catch (error) {}
                }
              }
            }
          };
          // MutationObserver
          // const observer = new MutationObserver(attributeChangedCallback);
          // const config = { attributes: true };
          // let allMatchingElements = [];

          // elements.forEach((element, index) => {
          //   const matchingChildren = element.querySelectorAll('.formkit-outer');
          //   allMatchingElements = allMatchingElements.concat(Array.from(matchingChildren));
          //   observer.observe(allMatchingElements[index], config);
          // });
        });
      });
    },
    handlePublishedRoute() {
      const dsId = this.formValues[this.getTitleStep].datasetID;
      const base = new URL(this.$env.api.baseUrl).origin + "/";
      window.location.href = base + "datasets/" + dsId + "?locale=de";
    },
    createDatasetID() {
      const valueObject = this.formValues[this.getTitleStep];
      if (!has(valueObject, "datasetID") || isNil(valueObject.datasetID)) {
        // console.log('in if');
        this.formValues[this.getTitleStep].datasetID = this.createIDFromTitle;
      } else {
        if (
          this.createIDFromTitle.startsWith(valueObject.datasetID) ||
          valueObject.datasetID.startsWith(this.createIDFromTitle)
        ) {
          // console.log('in else');
          this.formValues[this.getTitleStep].datasetID = this.createIDFromTitle;
        }
      }
    },
    generateandTranslateSchema(property) {
      for (
        let index = 0;
        index <
        this.getNavSteps(this.$env.content.dataProviderInterface.specification)[
          property
        ].length;
        index++
      ) {
        this.createSchema({
          property,
          page: this.getNavSteps(
            this.$env.content.dataProviderInterface.specification
          )[property][index],
          specification: this.$env.content.dataProviderInterface.specification,
        });
        this.translateSchema({
          property,
          page: this.getNavSteps(
            this.$env.content.dataProviderInterface.specification
          )[property][index],
        });
      }
    },
  },
  created() {
    // Needs to be reworked
    if (this.$route.query.edit === "false") {
      this.clearAll();
      // localStorage.clear();
    }

    // create schema for datasets or catalogues
    this.generateandTranslateSchema(this.property);

    // for datasets also create schema for distributions
    if (this.property === "datasets") {
      this.generateandTranslateSchema("distributions");
    }
  },
  mounted() {
    this.initInputPage();
  },
  watch: {
    activeStep: {
      handler() {
        this.scrollToTop();
      },
    },
    getFirstTitleFromForm: {
      handler(newVal, oldVal) {
        if (newVal === oldVal) return;
        if (localStorage.getItem("dpi_editmode") === "false") {
          this.setIsDraft(false);
          this.setIsEditMode(false);
        }
        // only create id from title if the user is not editing an existing dataset with an existing datasetID
        if (!this.isEditMode) {
          // this.createDatasetID();
        }
      },
    },
    // the schema is a computed value which gets computed only once so on language change this value must be re-computed
    "$i18n.locale": {
      handler() {
        this.generateandTranslateSchema(this.property);
        if (this.property === "datasets")
          this.generateandTranslateSchema("distributions");
      },
    },
  },
  beforeRouteEnter(to, from, next) {
    // Always clear storage when entering DPI
    next((vm) => {
      if (from.name && !from.name.startsWith("DataProviderInterface")) {
        vm.clearAll();
      }
    });
  },
  setup() {
    const dpiContext = useDpiContext();
    const { isEditMode } = useEditModeInfo();
    const {
      steps,
      activeStep,
      subSteps,
      visitedSteps,
      previousStep,
      nextStep,
      stepPlugin,
      goToNextStep,
      goToPreviousStep,
    } = useDpiStepper();

    const { formValues } = useFormValues();

    const { t, te } = useI18n();
    const activeSimpleModal = ref(false);
    const { translateSchema, createSchema, getSchema } = useFormSchema({
      t,
      te,
    });

    const scrollToTop = () => {
      let { x, y } = useWindowScroll({ behavior: "smooth" });
      y.value = 0;
    };

    const checkStepValidity = (stepName) => {
      return (
        (steps[stepName].errorCount > 0 || steps[stepName].blockingCount > 0) &&
        visitedSteps.value.includes(stepName)
      );
    };

    const library = markRaw({ OverviewPage });

    const activeSubStep = ref("");
    if (isEditMode.value) {
      activeStep.value = "ReviewAndPublish";
      activeSubStep.value = "reviewAndPublishPage";
    }

    return {
      steps,
      visitedSteps,
      activeStep,
      activeSubStep,
      subSteps,
      previousStep,
      nextStep,
      stepPlugin,
      checkStepValidity,
      goToNextStep,
      goToPreviousStep,
      scrollToTop,
      library,
      isEditMode,
      translateSchema,
      createSchema,
      getSchema,
      dpiContext,
      formValues,
      activeSimpleModal,
    };
  },
});
</script>

<template>
  <div class="form-container V3-typography">
    <div v-if="isInput" ref="fkInputContainer" class="inputContainer">
      <div class="formContainer formkit">
        <!-- <details>
          <pre>{{ JSON.stringify(formValues, null, 2) }}</pre>
        </details> -->
        <FormKit
          id="dpiForm"
          v-model="formValues"
          type="form"
          :actions="false"
          :plugins="[stepPlugin]"
          class="d-flex"
          @submit.prevent=""
        >
          <div v-if="dpiContext.edit.fromDraft || dpiContext.edit.enabled">
            <div class="dpiV3_stepper draftStepper">
              <TextButtonSmall
                button-text="Übersicht"
                icon-start="CaretLeft"
                icon-name="caretLeft"
                @click="gotToHome"
              />
              <div>
                <div
                  v-if="dpiContext.edit.enabled && !dpiContext.edit.fromDraft"
                  class="draftCTA"
                >
                  <ButtonV3
                    button-text="Veröffentlichung aufheben"
                    size="large"
                    @click="navTrigger('depublish')"
                  />
                  <ButtonV3
                    button-text="Veröffentlicht ansehen"
                    variant="secondary"
                    size="large"
                    icon-end="Out"
                    @click="handlePublishedRoute()"
                  />
                </div>
                <div v-else class="draftCTA">
                  <ButtonV3
                    button-text="Veröffentlichen"
                    size="large"
                    @click="navTrigger('publish')"
                  />
                </div>
              </div>
            </div>
          </div>
          <div v-else>
            <div
              v-if="
                $env.content.dataProviderInterface.specification ===
                  'dcatapdeHappyFlow' && activeStep !== 'Landing'
              "
              class="dpiV3_stepper"
            >
              <LogoV3 />
              <ul class="dpiV3_steps">
                <li
                  v-for="(step, stepName, index) in steps"
                  v-show="stepName !== 'Landing' && stepName !== 'Additionals'"
                  :key="step"
                  class="dpiV3_step"
                  :data-step-active="activeStep === stepName"
                  :data-step-valid="step.valid && step.errorCount === 0"
                  :class="{
                    dpiV3_activeItem: activeStep === stepName,
                    inactiveStep: stepName != activeStep,
                    'has-errors': checkStepValidity(stepName),
                  }"
                >
                  <div class="dpiV3_stepBubbleWrap">
                    <div
                      class="firstRowWrapper"
                      :class="{
                        dpiV3_activeStepName: stepName === activeStep,
                        formerlyVisitedStep: stepCounter > index,
                        'copy-small-regular': stepCounter > index,
                      }"
                    >
                      <img
                        v-if="activeStep === stepName && index < 5"
                        class="dpiV3_circle dpiV3_stepCircle"
                        :src="selectedImages[index - 1]"
                        alt="Selected Icon"
                      />
                      <PhNumberCircleFive
                        v-if="activeStep === stepName && index === 5"
                        :size="28"
                        color="#009FE3"
                        weight="fill"
                      />
                      <PhCheckCircle
                        v-if="index < stepCounter"
                        :size="28"
                        color="#70CC44"
                        weight="fill"
                      />

                      <img
                        v-if="index > stepCounter"
                        class="dpiV3_circle dpiV3_stepCircle"
                        :src="images[index - 1]"
                        alt="Selected Icon"
                      />

                      <span
                        v-if="checkStepValidity(stepName)"
                        class="dpiV3_step--errors"
                        v-text="step.errorCount + step.blockingCount"
                      />

                      <span style="vertical-align: middle">{{
                        $t(`message.dataupload.steps.${stepName}Step`)
                      }}</span>
                    </div>
                    <div class="dpiV3_subStepWrapper">
                      <!-- Object.keys(steps).length-1 because we have "Additionals" in the schema -->
                      <div
                        v-if="
                          index + 1 != Object.keys(steps).length - 1 &&
                          index >= stepCounter
                        "
                        class="dpiV3_seperatorHorizontalStepper"
                      />
                      <div
                        v-if="
                          index + 1 != Object.keys(steps).length - 1 &&
                          index < stepCounter
                        "
                        class="dpiV3_seperatorHorizontalStepperFat"
                      />
                      <div v-if="activeStep === stepName" class="dpiV3_subStep">
                        <div
                          v-for="(item, innerIndex) in Object.keys(
                            subSteps[index]
                          )"
                          v-if="stepCounter < 5"
                          class="dpiV3_subStepInner"
                        >
                          <div class="dpiV3_subStepCircleWrap">
                            <PhCheckCircle
                              v-if="substepCounter > innerIndex"
                              :size="20"
                              color="#009FE3"
                            />
                            <img
                              v-else
                              :src="getCircles(innerIndex)"
                              alt="circle"
                            />
                            <div
                              v-if="
                                innerIndex !=
                                Object.keys(subSteps[index]).length - 1
                              "
                              class="dpiV3_seperatorHorizontalStepperInner"
                              :class="{
                                dpiV3_seperatorHorizontalStepperInnerVisited:
                                  substepCounter > innerIndex,
                              }"
                            />
                          </div>
                          <div
                            style="
                              height: 20px;
                              display: flex;
                              align-items: center;
                            "
                          >
                            <span
                              class="large-regular dpiV3_substepText"
                              :class="{
                                dpiV3_activeStepDesc: item === activeSubStep,
                                formerlyVisitedStep:
                                  substepCounter > innerIndex,
                              }"
                            >
                              {{
                                $t(
                                  `message.dataupload.steps.${item.replace(
                                    /_\d+$/,
                                    ""
                                  )}`
                                )
                              }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <li
                  v-if="activeStep === 'Overview'"
                  class="dpiV3_step dpiV3_inactiveStep"
                >
                  <div class="dpiV3_circle dpiV3_stepCircle" />
                </li>
              </ul>
            </div>
            <div v-else class="dpiV3_stepperLanding">
              <div class="dpiV3_bydata_logo_wordmark">
                <LogoV3 />
              </div>
              <!-- div class="dpiV3_Progress-Steps"></div -->
              <div class="dpiV3_Illustration-Start">
                <Illustration illustration-name="start" />
              </div>
            </div>
          </div>
          <!-- DPIv2 Stepper -->
          <ul
            v-if="
              $env.content.dataProviderInterface.specification !=
              'dcatapdeHappyFlow'
            "
            class="steps"
          >
            <li
              v-for="(step, stepName, index) in steps"
              :key="step"
              class="step"
              :data-step-active="activeStep === stepName"
              :data-step-valid="step.valid && step.errorCount === 0"
              :class="{
                activeItem: activeStep === stepName,
                inactiveStep: stepName != activeStep,
                'has-errors': checkStepValidity(stepName),
              }"
              @click="
                activeStep = stepName;
                update();
                scrollToTop();
              "
            >
              <div class="stepBubbleWrap">
                <div class="circle stepCircle">
                  {{ index + 1 }}
                </div>
                <span
                  v-if="checkStepValidity(stepName)"
                  class="step--errors"
                  v-text="step.errorCount + step.blockingCount"
                />{{ $t(`message.dataupload.steps.${stepName}Step`) }}
              </div>
              <div
                v-if="index != Object.keys(steps).length"
                class="seperatorHorizontalStepper"
              />
            </li>
            <li v-if="activeStep === 'Overview'" class="step inactiveStep">
              <div class="circle stepCircle" />
            </li>
          </ul>

          <InputPageStep
            v-for="(stepName, index) in getNavSteps(
              $env.content.dataProviderInterface.specification
            )[property]"
            :key="index"
            :name="stepName"
          >
            <!-- DPIv2 Elements -->

            <!-- <div v-if="stepName !== 'Distributions' && stepName !== 'Overview'"
                    class="w-100 d-flex justify-content-between"> -->
            <!-- <h1 style="min-width:80%">{{ $t('message.dataupload.steps.' + stepName) }}</h1> -->
            <!-- <a class="standardButtonDPI" @click="dropdownCLick(); expandall = !expandall"><span
                        v-if="expandall">{{ $t('message.dataupload.info.expand') }}</span> -->
            <!-- <span v-else>{{ $t('message.dataupload.info.hide') }}</span></a> -->
            <!-- </div> -->
            <!-- <hr v-if="stepName !== 'Distributions'"> -->

            <!-- Need to edit the way the DPI is handling the Schema.
                   In this new approach, every property out of the page-content-config.js gets loaded separately.
                   This is for the substepper to work correctly -->
            <div
              v-for="(item, innerIndex) in Object.keys(
                getSchema(property).value[stepName]
              ).length"
              v-show="
                Object.values(
                  getSchema(property).value[stepName][innerIndex]
                )[0] === activeSubStep.replace(/_\d+$/, '') ||
                stepName === 'Landing'
              "
              style="width: 100%"
            >
              <FormKitSchema
                v-if="
                  stepName !== 'Distributions' && stepName !== 'Additionals'
                "
                :schema="getSchema(property).value[stepName][innerIndex]"
                :library="library"
                :final-values="formValues"
                @handle-nav="() => {}"
              />
            </div>
            <div v-if="activeStep === 'Landing'" class="dpiV3_CTALanding">
              <ButtonV3
                button-text="Abbrechen"
                size="large"
                variant="tertiary"
                @click="
                  activeSimpleModal = true;
                  console.log(activeSimpleModal);
                "
              />
              <ButtonV3
                button-text="Datenbereitstellung starten"
                size="large"
                @click="navTrigger('next')"
              />
            </div>
            <div v-if="activeStep === 'ReviewAndPublish'" class="dpiV3_CTA_RaP">
              <div class="dpiV3_CTANav">
                <ButtonV3
                  v-if="dpiContext.edit.enabled && !dpiContext.edit.fromDraft"
                  button-text="Veröffentlichung aufheben"
                  size="large"
                  variant="secondary"
                  @click="navTrigger('depublish')"
                />
                <ButtonV3
                  v-else
                  button-text="Als Entwurf speichern"
                  size="large"
                  variant="secondary"
                  @click="navTrigger('draft')"
                />

                <ButtonV3
                  button-text="Veröffentlichen"
                  size="large"
                  @click="
                    formValues[activeStep][activeSubStep][0].isValid === true
                      ? navTrigger('publish')
                      : (formValues[activeStep][
                          activeSubStep
                        ][0].isValid = false)
                  "
                />
              </div>
            </div>
            <div
              v-if="
                activeStep !== 'Landing' && activeStep !== 'ReviewAndPublish'
              "
              class="dpiV3_CTA"
              :class="{
                dpiV3_activeInfobox: activeSubStep.includes('hvdPage'),
              }"
            >
              <ButtonV3
                button-text="Abbrechen"
                size="large"
                variant="tertiary"
                @click="activeSimpleModal = true"
              />
              <div class="dpiV3_CTANav">
                <ButtonV3
                  v-if="activeStep !== 'Landing'"
                  icon-start="CaretLeft"
                  button-text="Zurück"
                  size="large"
                  variant="secondary"
                  @click="navTrigger('prev')"
                />

                <ButtonV3
                  icon-end="CaretRight"
                  button-text="Weiter"
                  size="large"
                  @click="validateStep()"
                />
              </div>
            </div>
            <div
              v-if="activeSubStep.includes('hvdPage')"
              class="dpiV3_Content_InputPage"
            >
              <div class="dpiV3_Card_Tips">
                <div class="dpiV3_Icon_Title">
                  <PhLightbulb :size="32" color="#009fe3" />
                  <div class="dpiV3_Info-Text dpiV3_activeStepName">
                    {{ $t("message.dataupload.datasets.hvdPage.tips-hvd") }}
                  </div>
                </div>
                <div class="dpiV3_CT-Content">
                  <div class="dpiV3_copy_large_regular dpiV3_hvd_frame3846">
                    {{ $t("message.dataupload.datasets.hvdPage.tips-text") }}
                  </div>
                  <div class="dpiV3_hvd_frame3846">
                    <div class="dpiV3_copy_large_semi_bold">
                      1.
                      {{
                        $t(
                          "message.dataupload.datasets.hvdPage.tips-question-1"
                        )
                      }}
                    </div>
                    <ul class="dpiV3_copy_large_regular">
                      <li>
                        {{
                          $t(
                            "message.dataupload.datasets.hvdPage.hvd-category-1"
                          )
                        }}
                      </li>
                      <li>
                        {{
                          $t(
                            "message.dataupload.datasets.hvdPage.hvd-category-2"
                          )
                        }}
                      </li>
                      <li>
                        {{
                          $t(
                            "message.dataupload.datasets.hvdPage.hvd-category-3"
                          )
                        }}
                      </li>
                      <li>
                        {{
                          $t(
                            "message.dataupload.datasets.hvdPage.hvd-category-4"
                          )
                        }}
                      </li>
                      <li>
                        {{
                          $t(
                            "message.dataupload.datasets.hvdPage.hvd-category-5"
                          )
                        }}
                      </li>
                      <li>
                        {{
                          $t(
                            "message.dataupload.datasets.hvdPage.hvd-category-6"
                          )
                        }}
                      </li>
                    </ul>
                  </div>
                  <div class="dpiV3_hvd_frame3846">
                    <div class="dpiV3_copy_large_semi_bold">
                      2.
                      {{
                        $t(
                          "message.dataupload.datasets.hvdPage.tips-question-2"
                        )
                      }}
                    </div>
                    <p class="dpiV3_copy_large_regular compact_margin_bottom">
                      {{
                        $t("message.dataupload.datasets.hvdPage.tips-example-2")
                      }}
                    </p>
                  </div>
                  <div class="dpiV3_hvd_frame3846">
                    <div class="dpiV3_copy_large_semi_bold">
                      3.
                      {{
                        $t(
                          "message.dataupload.datasets.hvdPage.tips-question-3"
                        )
                      }}
                    </div>
                    <p class="dpiV3_copy_large_regular compact_margin_bottom">
                      {{
                        $t("message.dataupload.datasets.hvdPage.tips-example-3")
                      }}
                    </p>
                  </div>
                  <div class="dpiV3_hvd_frame3846">
                    <div class="dpiV3_copy_large_semi_bold">
                      4.
                      {{
                        $t(
                          "message.dataupload.datasets.hvdPage.tips-question-4"
                        )
                      }}
                    </div>
                    <p class="dpiV3_copy_large_regular">
                      {{
                        $t("message.dataupload.datasets.hvdPage.tips-example-4")
                      }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <DistributionInputPage
              v-if="stepName === 'Distributions'"
              :schema="getSchema('distributions')"
              :values="formValues"
            />
            <p v-if="stepName === 'Mandatory'" class="p-1">
              <b>*</b> {{ $t("message.dataupload.info.mandatory") }}
            </p>
          </InputPageStep>

          <!-- DPIv2 Nav -->
          <!-- <Navigation :steps="steps" :nextStep="nextStep" :previousStep="previousStep" :goToNextStep="goToNextStep"
            :goToPreviousStep="goToPreviousStep"></Navigation> -->
        </FormKit>
        <ModalSimpleV3
          v-if="activeSimpleModal"
          :buttons="modalSimpleConf.button"
          :header-text="modalSimpleConf.header"
          :text="modalSimpleConf.text"
          :action="modalSimpleConf.action"
          @close="activeSimpleModal = false"
          @action-handling="gotToHome()"
        />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
// media Query
// @media only screen and (max-width: 992px) {
//   .dpiV3_dpi {
//     .inputContainer {
//       #dpiForm {
//         display: flex;
//         width: 992px;
//         padding: var(--Spacing-6, 48px) var(--Spacing-5, 32px);
//         justify-content: center;
//         align-items: flex-start;
//         gap: var(--Spacing-6, 48px);
//       }
//     }
//   }
// }

// @media only screen and (max-width: 1200px) and (min-width: 993px) {
//   .dpiV3_dpi {
//     .inputContainer {
//       #dpiForm {
//         display: flex;
//         width: 1200px;
//         // height: 982px;
//         padding: var(--Spacer-5, 48px) var(--Spacer-Custom-1, 32px);
//         justify-content: center;
//         align-items: flex-start;
//         gap: var(--Spacer-5, 48px);
//       }
//     }
//   }
// }

// @media only screen and (min-width: 1201px) {
//   .dpiV3_dpi {
//     .inputContainer {
//       #dpiForm {
//         display: flex;
//         width: 1764px;
//         // height: 982px;
//         padding: var(--Spacer-5, 48px) var(--Spacer-Custom-1, 32px);
//         justify-content: center;
//         align-items: flex-start;
//         gap: var(--Spacer-5, 48px);
//       }
//     }
//   }
// }

.large-regular {
  font-family: var(--font-family-secondary);
  font-size: var(--copy-large-regular-font-size);
  line-height: var(--copy-large-regular-line-height);
  font-weight: var(--copy-large-regular-font-weight);
  color: var(--neutral-80, #3d4952);
}

.dpiV3_dpi {
  .inputContainer {
    align-items: center;
    justify-content: center;

    #dpiForm {
      display: flex;
      // height: 982px;
      justify-content: center;
      align-items: flex-start;
    }

    .activeSection {
      display: flex;
      min-width: 512px;
      max-width: 696px;
      padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px);
      flex-direction: column;
      align-items: flex-start;
      // gap: var(--Spacing-8, 64px);
      flex: 1 0 0;
    }
  }
}
.dpiV3_stepCircle {
  // margin-right: 4px;‚
}
.dpiV3_substepText {
  color: var(--neutral-60, #687178);
}

.formerlyVisitedStep {
  color: var(--neutral-80, #3d4952);
}

.dpiV3_GoodExample {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
}

.dpiV3_hvd_frame3846 ul {
  list-style-position: inside;
  margin: 0;
  margin: 0;
}

.dpiV3_hvd_frame3846 li {
  color: var(--neutral-80, #6c757d);
}

.dpiV3_hvd_frame3846 p {
  color: var(--neutral-80, #6c757d);
  padding-left: var(--Spacing-3, 16px);
  margin-left: 5px;
}

.dpiV3_copy_large_regular {
  font-family: var(--font-family-secondary);
  font-size: var(--copy-large-regular-font-size);
  line-height: var(--copy-large-regular-line-height);
  font-weight: var(--copy-large-regular-font-weight);

  align-self: stretch;
  color: var(--neutral-80, #3d4952);
  padding-left: 1.5em;
}

.dpiV3_CT-Content {
  display: flex;
  padding-left: var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  margin-bottom: 0px;
  color: var(--neutral-80, #3d4952);
}

.dpiV3_hvd_frame3846 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
  padding: 0;
  margin: 0;
}

.dpiV3_Content_InputPage {
  display: flex;
  // min-width: 512px;
  // max-width: 636px;
  // padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-6, 48px);
  flex: 1 0 0;
}

.dpiV3_Card_Tips {
  display: flex;
  min-width: 416px;
  max-width: 600px;
  padding: var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
  background: var(--blue-10, #f3fbff);
  border-radius: var(--Modal-Radius, 32px);
}

.dpiV3_Info-Text {
  color: var(--neutral-80, #3d4952);
  font-style: normal;
  font-family: var(--font-family-secondary);
  font-size: var(--copy-large-semi-bold-font-size);
  line-height: var(--copy-large-semi-bold-line-height);
  font-weight: var(--copy-large-semi-bold-font-weight);
}

.dpiV3_Icon_Title {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_bold-text {
  color: var(--neutral-80, #3d4952);
  align-self: stretch;
}

.dpiV3_activeStepName {
  color: var(--neutral-80, #3d4952);

  font-family: var(--font-family-secondary);
  font-size: var(--copy-small-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-small-semi-bold-font-weight, 600);
  line-height: var(--copy-small-semi-bold-line-height);
}

.dpiV3_copy_large_semi_bold {
  font-family: var(--font-family-secondary);
  font-size: var(--copy-small-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-small-semi-bold-font-weight, 600);
  line-height: var(--copy-small-semi-bold-line-height);
  text-indent: -1.2em;
  padding-left: 1.5em;
}

.dpiV3_activeStepDesc {
  color: var(--neutral-80, #3d4952);
  font-style: normal;
  font-family: var(--font-family-secondary);
  font-size: var(--copy-large-semi-bold-font-size);
  line-height: var(--copy-large-semi-bold-line-height);
  font-weight: var(--copy-large-semi-bold-font-weight);
}

.dpiV3_CTA {
  display: flex;
  justify-content: space-between;
  align-items: center;
  align-self: stretch;
  margin-top: var(--Spacing-8, 64px);
  // padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px);
}

.dpiV3_activeInfobox {
  margin-bottom: var(--Spacing-6, 48px);
}

// .dpiV3_CTALandingInner {
//   min-width: 570px;
//   max-width: 696px;
//   display: flex;
//   justify-content: space-between;
//   padding: var(--Spacing-5, 32px)
// }
.firstRowWrapper {
  display: flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  span {
    white-space: nowrap;
  }
}
.dpiV3_CTALanding {
  margin-top: var(--Spacing-8, 64px);
  display: flex;
  justify-content: space-between;
  width: -webkit-fill-available;
}

.dpiV3_CTANav {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
}

.dpiV3_CT-Content {
  display: flex;
  padding-left: var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  margin-bottom: 0px;
  color: var(--neutral-80, #3d4952);
}

.dpiV3_hvd_frame3846 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
  padding: 0;
  margin: 0;
}

.dpiV3_subStepCircleWrap {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.dpiV3_seperatorHorizontalStepperInner {
  min-height: 12px;
  width: 1px;
  border-radius: var(--Border-Radius, 8px);
  background: var(--Colour-neutral-Neutral30, #d5d7da);
}

.dpiV3_subStepInner {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
}

.dpiV3_subStep {
  display: flex;
  padding: var(--Spacing-2, 8px) 0px var(--Spacing-4, 24px) 0px;
  flex-direction: column;
  align-items: flex-start;
  flex: 1 0 0;

  img {
    margin: auto 0;
    width: 20px;
    height: 20px;
  }
}

.dpiV3_subStepWrapper {
  display: flex;
  align-items: center;
  gap: 21px;
  align-self: stretch;
}

.dpiV3_seperatorHorizontalStepper {
  margin-left: 13px;
  min-height: 24px;
  width: 2px;
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);
  background: var(--neutral30, #d5d7da);
}

.dpiV3_seperatorHorizontalStepperFat {
  margin-left: 13px;
  min-height: 24px;
  width: 2px;
  align-self: stretch;
  border-radius: var(--Border-Radius, 8px);
  background: var(--neutral60, #687178);
}

.dpiV3_circle {
}

.dpiV3_stepBubbleWrap {
  color: var(--neutral-60, #687178);
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  align-self: stretch;
}

.dpiV3_step {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  align-self: stretch;
}

.dpiV3_steps {
  padding: 0;
  display: flex;
  width: 254px;
  flex-direction: column;
  align-items: flex-start;
  margin: 0;
}

.dpiV3_stepper {
  display: flex;
  width: 334px;
  min-width: 334px;
  padding: var(--Spacing-6, 48px) var(--Spacing-5, 32px) var(--Spacing-8, 64px)
    var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-10, 80px);
  flex-shrink: 0;
}

.dpiV3_stepperLanding {
  display: flex;
  padding: var(--Spacing-6, 48px) 0px var(--Spacing-10, 80px)
    var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-11, 120px);
}

.standardButtonDPI {
  background-color: #fff;
  color: #000;
  border-radius: 0.3em;
  font-size: 16px;
  padding: 0.75em;
  display: inline-flex;
  align-items: center;
  height: 50px;
  border: 1px solid lightgray;
  transition: all 100ms ease-in-out;
  cursor: pointer;

  &:hover {
    transform: scale(98%);
    background-color: #f4f4f4;
    border-color: gray;
    text-decoration: none;
  }
}

:root {
  --gray: #ccccd7;
  --gray-l: #eeeef4;
}

.formkit-form {
  max-width: 500px;
  flex-shrink: 0;

  background: #fff;
  color: #000;
  border: 1px solid var(--gray);
  border-radius: 0.5em;
  box-shadow: 0.25em 0.25em 1em 0 rgba(0, 0, 0, 0.1);
  padding: var(--Spacing-6, 48px) var(--Spacing-5, 32px);
  gap: var(--Spacing-6, 48px);
}

#app .source-content {
  padding: 2em;
  background: transparent;
}

.steps {
  list-style-type: none;
  margin: 0;
  display: flex;
  padding-left: 0;
  background: var(--gray-l);
  border-radius: 0.4em 0.4em 0 0;
  overflow: hidden;
  border-bottom: 1px solid var(--gray);
}

.dpiV3_steps {
  display: flex;
  width: 254px;
  flex-direction: column;
  align-items: flex-start;
}

.step {
  font-size: 14px;
  display: flex;
  align-items: center;
  padding: 16px 20px;
  background: var(--gray-l);
  border-right: 1px solid var(--gray);
  color: gray;
  flex-grow: 0;
  flex-shrink: 0;
  position: relative;
}

.step:last-child {
  box-shadow: 0.1em -0.1 0.1em 0 rgba(0, 0, 0, 0.33);
}

.step:hover {
  cursor: pointer;
}

[data-step-active="true"] {
  color: black;
  background: white !important;
  border-bottom: none;
  position: relative;
}

.step--errors,
[data-step-valid="true"]:after {
  content: "âœ“";
  background-color: #54a085;
  position: absolute;
  top: 4px;
  right: 4px;
  height: 18px;
  width: 18px;
  border-radius: 50%;
  z-index: 10;
  display: flex;
  font-size: 10px;
  flex-direction: column;
  justify-content: center;
  text-align: center;
  color: white;
}

.step--errors {
  background-color: #ff4949;
  color: #fff;
  z-index: 100;
}

.step-nav {
  display: flex;
  margin-top: 2em;
  margin-bottom: 1em;
  justify-content: space-between;
}

.form-body {
  padding: 2em;
}

.next {
  margin-left: auto;
}

.formkit-outer[data-type="submit"] .formkit-wrapper {
  padding: 0 2em 1em 2em;
  display: flex;
}

.formkit-outer[data-type="submit"] .formkit-input {
  margin-left: auto;
  margin-right: 0;
}

details {
  border: 1px solid var(--gray);
  background: var(--gray-l);
  border-radius: 0.15em;
  padding: 1em;
}

.formkit-form > .formkit-messages {
  // DPIv3 - need to disable the messages
  display: none;
  padding: 0 2em 0em 2em;
}

.formkit-form > .formkit-messages:last-child {
  padding: 0 2em 2em 2em;
}

[data-errors="true"] .formkit-label {
  color: #ff4949;
}

.formkit-wrapper {
  max-width: 100%;
}

button:hover,
summary {
  cursor: pointer;
}

@media (max-width: 438px) {
  h1 {
    font-size: 1.15em;
  }

  #app .source-content {
    padding: 0.5em;
  }

  .steps {
    flex-direction: column;
  }

  .step {
    border-bottom: 1px solid var(--gray);
    border-right: none;
  }

  .step:last-child {
    border-bottom: none;
  }

  .form-body {
    padding: 1em;
  }

  .formkit-outer[data-type="submit"] .formkit-wrapper {
    padding: 0 1em 1em 1em;
    display: flex;
  }

  .formkit-form > .formkit-messages {
    padding: 0 1em 0em 1em;
  }

  .formkit-form > .formkit-messages:last-child {
    padding: 0 1em 1em 1em;
  }
}

.repeatableWrap,
.formkitCmpWrap {
  margin: 2rem 0 !important;
}

.isInvalidProperty {
  background-color: #ffd9d9 !important;
}

.activeItem {
  flex-grow: 1;

  .seperatorHorizontalStepper {
    height: 100%;
  }
}

select {
  line-height: unset !important;
}

.form-container {
  padding-top: 20px;
  margin-top: 30px;
  border: solid 1px #d5d5d5;
  border-radius: 3px;
  margin-bottom: 20px;
}

.inputContainer {
  display: flex;
}

.form-container {
  position: absolute;
  z-index: 9999;
  background: white;
  min-height: 100vh;
  width: 100vw;
  top: 0;
  margin-top: 0;
  left: 0;
}

.distributionPage0 {
  display: block;
}

.distributionPage1 {
  display: none;
}

.distributionPage2 {
  display: none;
}

.grid2r2c {
  .formkit-input-group-repeatable {
    display: grid;
    grid-template-columns: 70% 28%;
    grid-template-rows: auto auto;
    grid-gap: 10px;
    background-color: transparent;
  }
}

.grid1r2c {
  .formkit-input-group-repeatable {
    display: grid;
    grid-template-columns: 70% 28%;
    grid-template-rows: auto;
    grid-gap: 10px;
    background-color: transparent;
  }
}

.row1 {
  grid-row-start: 1;
  grid-row-end: 2;
}

.row2 {
  grid-row-start: 2;
  grid-row-end: 3;
}

.grow3 {
  grid-row-start: 3;
  grid-row-end: 4;
}

.column1 {
  grid-column-start: 1;
  grid-column-end: 2;
}

.column2 {
  grid-column-start: 2;
  grid-column-end: 3;
}

.columnboth {
  grid-column-start: 1;
  grid-column-end: 3;
}

.display-none {
  display: none;
}

.activeSection {
  > .formkitProperty {
    > div {
    }
  }
}

.formkitCmpWrap {
  border-left: 1px solid lightgray;
}

.formkitProperty {
  > h4 {
    background-color: #f5f5f5;
    padding: 1rem;
    cursor: pointer;
    position: relative;
    transition: all ease-in-out 200ms;

    &:hover {
      background-color: lightgray;

      &:before {
        rotate: -45deg;
      }

      &:after {
        rotate: 45deg;
      }
    }

    &:before {
      transition: all ease-in-out 200ms;
      content: "";
      width: 15px;
      height: 1.5px;
      background-color: black;
      position: absolute;
      top: 30px;
      right: 15px;
      rotate: 45deg;
    }

    &:after {
      transition: all ease-in-out 200ms;
      content: "";
      width: 15px;
      height: 1.5px;
      background-color: black;
      position: absolute;
      top: 30px;
      right: 25px;
      rotate: -45deg;
    }
  }

  .formkitProperty {
    h4 {
      padding: 0;
      background-color: unset !important;

      &::before {
        display: none !important;
      }

      &:after {
        display: none !important;
      }
    }
  }
}

.dpiChevUp {
  &:before {
    rotate: -45deg !important;
  }

  &:after {
    rotate: 45deg !important;
  }
}

.dpiV3_seperatorHorizontalStepperInnerVisited {
  background: var(--neutral-60, #687178);
}
.dpiV3_CTA_RaP {
  display: flex;
  justify-content: end;
  align-items: center;
  align-self: stretch;
  margin-top: var(--Spacing-8, 64px);
  // padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px);
}
.draftStepper {
  gap: var(--Spacing-6, 48px);
  padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px);
}
.draftCTA {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
}
.compact_margin_bottom {
  margin-bottom: 0 !important;
}
</style>
