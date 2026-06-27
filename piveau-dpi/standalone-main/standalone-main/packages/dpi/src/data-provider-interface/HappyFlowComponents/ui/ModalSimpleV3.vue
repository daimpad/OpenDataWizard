<template>
  <div class="dpiV3_modalBackground">
    <div class="dpiV3_modalWrap">
      <div class="dpiV3_modalMainWrap">
        <div class="dpiV3_modalHeaderWrap">
          <h2>{{ props.headerText }}</h2>
          <CrossOutButton class="dpiv3_modalCrossout" @click="closeModal" />
        </div>
        <span class="dpiV3_modalText">{{ props.text }}</span>
      </div>

      <!----------------------------------->
      <!---------- optional values -------->
      <div
        v-if="!dataRange && (optionalString_1 || optionalString_2)"
        class="dpiV3AutoCompleteWrap copy-large-semi-bold"
      >
        <div class="dpiV3_LinkInfos">
          <div class="dpiV3_title" v-if="optionalString_1">
            {{ optionalString_1 }}
          </div>
          <div
            class="dpiV3_title"
            v-if="optionalString_2 && optionalString_1 === ''"
          >
            {{ optionalString_2 }}
          </div>
        </div>
      </div>
      <!---------- optional values -------->
      <!----------------------------------->

      <!--------- dataRange is true, modal shows start and endDate -->
      <div
        v-if="dataRange && (optionalString_1 || optionalString_2)"
        class="dpiV3AutoCompleteWrap copy-large-semi-bold"
      >
        <div class="dpiV3_LinkInfos">
          <div class="dpiV3_title" v-if="optionalString_1">
            <span class="dpiV3_fromTo">Von</span> <br />
            {{ optionalString_1 }}
          </div>
          <div
            class="dpiV3_title"
            v-if="optionalString_2"
            style="padding-right: 100px"
          >
            <span class="dpiV3_fromTo">Bis</span> <br />
            {{ optionalString_2 }}
          </div>
        </div>
      </div>
      <!----------------------------------------------------------->

      <div class="dpiV3_modalButtonWrap">
        <TextButtonSmall
          v-if="props.action === 'cancel'"
          buttonText="Zurück"
          class="dpiv3_modalCancelButton copy-button-ml modalBackButton"
          @click="closeModal"
          ref="accActive"
          iconStart="CaretLeft"
          iconName="caretLeft"
        />
        <TextButtonSmall
          v-else
          buttonText="Abbrechen"
          class="dpiv3_modalCancelButton"
          @click="closeModal"
          ref="accActive"
        />
        <ButtonV3
          @click="confirmAction"
          :buttonText="props.buttons"
          size="large"
        />
      </div>
    </div>
  </div>
</template>
<script setup>
import { defineEmits } from "vue";
import TextButtonSmall from "./TextButtonSmall.vue";
import ButtonV3 from "./ButtonV3.vue";
import CrossOutButton from "./CrossOutButton.vue";

const props = defineProps({
  headerText: {
    type: String,
    required: true,
  },
  text: {
    type: String,
    required: true,
  },
  buttons: {
    type: Object,
    required: true,
  },
  action: String,
  optionalString_1: {
    type: String,
    required: false,
  },
  optionalString_2: {
    type: String,
    required: false,
  },
  dataRange: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "actionHandling"]);

const closeModal = () => {
  emit("close");
};
const confirmAction = () => {
  emit("actionHandling", props.action);
  closeModal();
};
</script>
<style>
.dpiv3_modalCancelButton {
  margin-top: auto;
  margin-bottom: auto;
}
.dpiV3_modalText {
  align-self: stretch;
  color: var(--neutral60, #687178);

  /* Copy/Copy-Large-Regular */
  font-family: Inter;
  font-size: 16px;
  font-style: normal;
  font-weight: 400;
  line-height: 26px;
  /* 162.5% */
}

.dpiV3_modalBackground {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.8);
  z-index: 998;
  display: flex;
  justify-content: center;
}

.dpiv3_modalCrossout {
  position: absolute;
  right: 24px;
  top: 24px;
}

.dpiV3_modalWrap {
  margin-top: 5rem;
  display: flex;
  width: 624px;
  padding: var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);

  border-radius: var(--Modal-Radius, 32px);
  background: var(--Colour-neutral-Neutral0, #fff);
  /* height: fit-content; */
  position: relative;
}

.dpiV3_modalHeaderWrap {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;

  h2 {
    display: -webkit-box;
    width: 519px;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;

    overflow: hidden;
    color: var(--neutral80, #3d4952);
    text-overflow: ellipsis;

    /* Headlines/Headline-4 */
    font-family: Inter;
    font-size: 24px;
    font-style: normal;
    font-weight: 700;
    line-height: 36px;
    /* 150% */
  }
}
.modalBackButton {
  /* display: none; */
  display: flex !important;
  height: 48px !important;
  padding: var(--Spacing-2, 8px) var(--Spacing-3, 16px) !important;
  align-items: center !important;
  gap: var(--Spacing-2, 8px) !important;
}

.dpiV3_modalMainWrap {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_modalButtonWrap {
  display: flex;
  justify-content: flex-end;
  align-items: flex-end;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_fromTo {
  color: var(--Colour-neutral-Neutral60, #687178);
  font-weight: normal;
}
</style>
