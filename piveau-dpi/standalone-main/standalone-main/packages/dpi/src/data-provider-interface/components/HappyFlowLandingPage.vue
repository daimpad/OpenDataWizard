<template>
  <div class="dpiV3_Wizard-Intro V3-typography">
    <!-- <div class="dpiV3_Progress-Bar">
      <div class="dpiV3_bydata_logo_wordmark">
        <LogoV3 />
      </div>
    div class="dpiV3_Progress-Steps"></div
      <div class="dpiV3_Illustration-Start">
        <Illustration illustrationName="start" />
      </div>
    </div> -->
    <div class="dpiV3_Content">
      <div class="dpiV3_Frame_38">
        <div class="dpiV3_Frame_50">
          <h4 class="dpiV3_title">
            {{ $t("message.dataupload.datasets.landingPage.title") }}
          </h4>
          <div class="copy-large-regular dpiV3_large_text">
            {{ $t("message.dataupload.datasets.landingPage.intro-text") }}
          </div>
        </div>
        <div class="dpiV3_Card_Tips">
          <div class="dpiV3_Icon_Title">
            <PhInfo :size="32" color="#009fe3" />
            <div class="dpiV3_Info-Text copy-large-semi-bold">
              {{
                $t("message.dataupload.datasets.landingPage.requirements-title")
              }}
            </div>
          </div>
          <ul class="dpiV3_CT-Content">
            <li>
              <span class="copy-small-semi-bold dpiV3_bold-text">
                {{
                  $t(
                    "message.dataupload.datasets.landingPage.requirements-link"
                  )
                }}
              </span>
              <span class="copy-small-regular dpiV3_regular-text">
                {{
                  $t(
                    "message.dataupload.datasets.landingPage.requirements-text"
                  )
                }}
              </span>
            </li>
          </ul>
        </div>
        <div class="copy-large-regular dpiV3_regular-text">
          {{ $t("message.dataupload.datasets.landingPage.start-message") }}
        </div>
      </div>
      <!-- <div class="dpiV3_CTA">
        <ButtonV3 buttonText="Abbrechen" size="large" variant="tertiary" />
        <ButtonV3 buttonText="Datenbereitstellung starten" size="large"  @click="navTrigger('next')"/>
      </div> -->
    </div>
  </div>
</template>

<script>
import { useI18n } from "vue-i18n";
import "../config/styles/variables.css";
import "../config/styles/typography.css";
import LogoV3 from "../HappyFlowComponents/ui/LogoV3.vue";
import { PhInfo } from "@phosphor-icons/vue";
import ButtonV3 from "../HappyFlowComponents/ui/ButtonV3.vue";
import Illustration from "../HappyFlowComponents/ui/Illustration.vue";

export default {
  components: {
    LogoV3,
    PhInfo,
    ButtonV3,
    Illustration
  },
  methods: {
    navTrigger(value) {
      this.$emit('handleNav', value);  // Event emitten
    }
  },
  setup() {
    const { t } = useI18n();

    const goBack = () => { };

    const startProcess = () => { };

    return {
      t,
      goBack,
      startProcess,
    };
  },
};
</script>

<style scoped>
.dpiV3_Wizard-Intro {
  /* display: flex;
  width: 992px;
  padding: var(--Spacing-6, 48px) var(--Spacing-5, 32px) 0 var(--Spacing-5, 32px);
  justify-content: center;
  align-items: flex-start;
  gap: var(--Spacing-6, 48px);

  background: var(--neutral-0, #fff); */
}

.dpiV3_Progress-Bar {
  display: flex;
  padding: var(--Spacing-6, 48px) 0px var(--Spacing-10, 80px) var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-11, 120px);
  background: var(--neutral-0, #fff);
}

.dpiV3_bydata_logo_wordmark {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
}

.dpiV3_Progress-Steps {
  display: flex;
  width: 286px;
  padding-right: var(--Spacing-5, 32px);
  align-items: flex-start;
  background: var(--neutral-0, #fff);
}

.dpiV3_Illustration-Start {
  display: flex;
  width: 285.999px;
  height: 252.001px;
  justify-content: center;
  align-items: center;
}

.dpiV3_Content {
  display: flex;
  /* min-width: 512px;
  max-width: 696px; */
  /* padding: var(--Spacing-5, 32px) var(--Spacing-6, 48px); */
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-8, 64px);
  flex: 1 0 0;
}

.dpiV3_Frame_38 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-6, 48px);
  align-self: stretch;
}

.dpiV3_Frame_50 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  color: var(--neutral-80, #3d4952);
}

.dpiV3_title {
  color: var(--neutral-80, #3d4952);
}

.dpiV3_large_text {
  font-style: normal;
  color: var(--neutral-80, #3d4952);
  align-self: stretch;
}

.dpiV3_CTA {
  display: flex;
  justify-content: space-between;
  align-items: center;
  align-self: stretch;
}

.dpiV3_Card_Tips {
  display: flex;
  min-width: 416px;
  max-width: 600px;
  padding: var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  border-radius: var(--Modal-Radius, 32px);
  background: var(--blue-10, #f3fbff);
}

.dpiV3_Icon_Title {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_Info-Text {
  color: var(--neutral-80, #3d4952);
  font-style: normal;
}

.dpiV3_CT-Content {
  display: flex;
  padding-left: var(--Spacing-6, 48px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  flex: 1 0 0;
  align-self: stretch;
  margin-bottom: 0px;

  list-style-type: none;

  margin-left: 15px;
  /** distance border to bullet */
}

.dpiV3_CT-Content li {
  position: relative;
  padding-left: var(--Spacing-2, 8px);
  color: var(--neutral-80, #3d4952);
  /** color of the bullet */
  text-indent: 0;
}

.dpiV3_CT-Content li::before {
  content: "•";
  position: absolute;
  left: calc(var(--Spacing-2, 8px) * -1);
  color: inherit;
  line-height: inherit;
}

.dpiV3_bold-text {
  color: var(--neutral-80, #3d4952);
  align-self: stretch;
}

.dpiV3_regular-text {
  color: var(--neutral-80, #3d4952);
  align-self: stretch;
}
</style>
