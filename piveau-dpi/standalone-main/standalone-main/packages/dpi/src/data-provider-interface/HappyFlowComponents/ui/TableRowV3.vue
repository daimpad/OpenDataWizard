<template>
  <button
    class="dpiV3_tableRowWrapper"
    :class="{
      dpiV3_pressedTableRow: keydownState,
      tRowInDraft: props.fromDraft,
    }"
    @keydown.space.prevent="handleKeydown($event)"
    @keydown.enter.prevent="handleKeydown($event)"
    @keyup.space="handleKeyup($event)"
    @keyup.enter="handleKeyup($event)"
  >
    <div class="dpiV3_tableRowInner">
      <div
        class="dpiV3_tableRowContent"
        @mousedown.prevent="handleClick($event, true)"
        @mouseup.prevent="handleClick($event, false)"
        @click="dropDownHandler('Bearbeiten')"
      >
        <div class="dpiV3_TableRowDescContainer">
          <span class="dpiV3_dsDesc">
            {{ props.text }}
          </span>
        </div>
        <div class="dpiV3_TableRowStatus">
          <StateTag
            v-if="!props.draft"
            label="Veröffentlicht"
            state="published"
          />
          <StateTag v-else label="Entwurf" state="draft" />
          <!-- <span class="dpiV3_TableRowUpdated">
            Updated am: {{ props.date }}
          </span> -->
        </div>
      </div>

      <div class="dpiV3_TableRowButtonWrap">
        <Dropdown
          v-if="!isDraft.edit.enabled"
          @click="dropDownHandler"
          type="moreButton"
          :notDraft="!props.draft"
          alignment="right"
          :text="{
            support: 'This is a supporting message',
          }"
          :data="dropdownData()"
        />
      </div>
    </div>
  </button>
  <ModalSimpleV3
    v-if="activeV3Modal"
    :buttons="modalConf.button"
    :headerText="modalConf.header"
    :text="modalConf.text"
    @close="activeV3Modal = false"
    :action="modalConf.action"
    @actionHandling="handleButtonAction($event)"
  />
  <Toast
    v-if="activeV3Toast"
    :type="toastConf.type"
    :text="toastConf.text"
    class="dpiV3_tableToast"
    :button="toastConf.button"
    :action="toastConf.action"
    @button-clicked="handleToastClick()"
    @mouseenter="isHoveringToast = true"
    @mouseleave="toastFade"
  />
</template>
<script setup>
import { ref, computed, onMounted, watch } from "vue";
import StateTag from "./StateTag.vue";
import Dropdown from "./Dropdown.vue";
import ModalSimpleV3 from "./ModalSimpleV3.vue";
import Toast from "./Toast.vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { useDpiUtils } from "../../composables/useDpiUtils";
import { useDpiContext } from "../../composables/index";
import axios from "axios";
import { useRuntimeEnv } from "../../../composables/useRuntimeEnv";

const store = useStore();
const router = useRouter();
let activeV3Modal = ref(false);
let activeV3Toast = ref(false);
let isHoveringToast = ref(false);
let modalConf = ref({});
let toastConf = ref({});
let isDraft = ref({ edit: { fromDraft: false } });
let toastButtonClicked = ref(false);
if (useDpiContext()) {
  isDraft = useDpiContext();
}

const env = useRuntimeEnv();
const userData = computed(() => store.getters["auth/getUserData"]);
const rtpToken = computed(() => userData.value && userData.value.rtpToken);

const props = defineProps({
  text: {
    type: String,
    required: true,
  },
  draft: {
    type: Boolean,
  },
  date: {
    type: Object,
    required: true,
  },
  fromDraft: Boolean,
  id: String,
  catalogue: String,
  dataset: Object,
});

const { toEditMode } = useDpiUtils();

const navigate = () => {
  const isDraft = props.draft;
  const catalogId = props.catalogue;
  const id = props.id || props.dataset.id || props.catalogue.id;
  toEditMode({ id, catalogId, isDraft, locale: "de" });
};
const handleToastClick = () => {
  switch (toastConf.value.action) {
    case "goToDataset":
      toastButtonClicked.value = true;
      const dsId = props.id || props.dataset.id;
      const base = new URL(env.api.baseUrl).origin + "/";
      isHoveringToast.value = false;
      activeV3Toast.value = false;
      setTimeout(() => {
        window.location.href = base + "datasets/" + dsId + "?locale=de";
      }, 500);
      break;
    case "revertDeletion":
      toastButtonClicked.value = true;
      toastFade();
      setTimeout(() => {
        toastButtonClicked.value = false;
      }, 200);
      break;
    case "revertToDraft":
      toastButtonClicked.value = true;
      toastFade();
      setTimeout(() => {
        toastButtonClicked.value = false;
      }, 200);
      break;
  }
};
const depublishDataset = (id, catalog, title, description) => {
  const tryDepublish = async () => {
    await store.dispatch("auth/putDatasetToDraft", {
      id,
      catalog,
      title,
      description,
    });
    router.go();
  };

  const interval = setInterval(() => {
    console.log(
      !activeV3Toast.value,
      !isHoveringToast.value,
      !toastButtonClicked.value
    );

    if (toastButtonClicked.value) {
      clearInterval(interval);
      return;
    }
    if (
      !activeV3Toast.value &&
      !isHoveringToast.value &&
      !toastButtonClicked.value
    ) {
      clearInterval(interval);
      tryDepublish();
    }
  }, 100);
};
const publishDraft = (id, catalog) => {
  const tryPublish = async () => {
    await store.dispatch("auth/publishUserDraftById", { id, catalog });
    if (!toastButtonClicked.value) {
      router.go()
    }
  };

  const interval = setInterval(() => {
    if (
      toastButtonClicked.value ||
      (!activeV3Toast.value && !isHoveringToast.value)
    ) {
      clearInterval(interval);
      tryPublish();
    }
  }, 100);
};

const deleteDatasetHttp = (id, catalog) => {
  const datasetId =
    id ||
    (props.dataset && props.dataset.id) ||
    (props.catalogue && props.catalogue.id);
  const catalogueId = catalog || props.catalogue;
  const baseUrl = env.api.hubUrl;
  const isDraftDs = !!props.draft;
  const path = isDraftDs
    ? `drafts/datasets/${datasetId}?catalogue=${catalogueId}`
    : `datasets/${datasetId}?useNormalizedId=true&catalogue=${catalogueId}`;
  const endpoint = `${baseUrl}${path}`;

  const tryDelete = async () => {
    await axios.delete(endpoint, {
      headers: {
        "Content-Type": "text/turtle",
        Authorization: `Bearer ${rtpToken.value}`,
      },
    });
    router.go();
  };

  const interval = setInterval(() => {
    if (toastButtonClicked.value) {
      clearInterval(interval);
      return;
    }
    if (
      !activeV3Toast.value &&
      !isHoveringToast.value &&
      !toastButtonClicked.value
    ) {
      clearInterval(interval);
      tryDelete();
    }
  }, 100);
};

const toastFade = () => {
  isHoveringToast.value = false;
  setTimeout(() => {
    activeV3Toast.value = false;
  }, 2000);
};

watch(activeV3Toast, (newValue) => {
  if (newValue) {
    // Wenn activeV3Toast true wird, setze es nach 2 Sekunden zurück
    const timeoutId = setTimeout(() => {
      if (!isHoveringToast.value) {
        activeV3Toast.value = false;
      }
    }, 2000);
  }
});
const dropdownData = () => {
  if (!props.draft) {
    return [
      { "@value": "Bearbeiten", selected: false },
      { "@value": "Veröffentlichung aufheben", selected: false },
      { "@value": "Löschen", selected: false },
    ];
  } else
    return [
      { "@value": "Bearbeiten", selected: false },
      { "@value": "Veröffentlichen", selected: false },
      { "@value": "Löschen", selected: false },
    ];
};
const handleButtonAction = (action) => {
  switch (action) {
    case "publishDataset":
      // Logik zum Veröffentlichen des Datensatzes
      publishDraft(props.id, props.catalogue);

      console.log("Datensatz wird veröffentlicht");
      activeV3Toast.value = true;
      toastConf.value = {
        type: "success",
        text: "Der Datensatz wurde veröffentlicht",
        button: "Ansehen",
        action: "goToDataset",
      };

      break;
    case "deleteDataset":
      (async () => {
        try {
          if (props.draft) await deleteDatasetHttp(props.id, props.catalogue);
          else
            await deleteDatasetHttp(props.dataset.id, props.dataset.catalog.id);
          activeV3Toast.value = true;
          toastConf.value = {
            type: "success",
            text: "Der Datensatz wurde erfolgreich gelöscht",
            button: "Rückgängig machen",
            action: "revertDeletion",
          };
        } catch (ex) {
          activeV3Toast.value = true;
          toastConf.value = {
            type: "error",
            text: "Das Löschen des Datensatzes ist fehlgeschlagen",
            button: "Schließen",
          };
          // eslint-disable-next-line no-console
          console.error(ex);
        }
      })();
      break;
    case "setToDraft":
      depublishDataset(
        props.dataset.id,
        props.dataset.catalog.id,
        props.dataset.title,
        props.dataset.description
      );
      activeV3Toast.value = true;
      toastConf.value = {
        type: "success",
        text: "Der Datensatz wurde erfolgreich zu einem Entwurf zurückgesetzt",
        button: "Rückgängig machen",
        action: "revertToDraft",
      };
    // Weitere Fälle nach Bedarf
  }
};
const dropDownHandler = (event) => {
  let selectedValue = "";

  if (typeof event === "string") {
    selectedValue = event;
  } else selectedValue = event.target.innerText;
  console.log(selectedValue);
  switch (selectedValue) {
    case "Bearbeiten":
      navigate();
      break;
    case "Veröffentlichen":
      modalConf.value = {
        button: "Veröffentlichen",
        header: "Datensatz veröffentlichen",
        text: "Sind Sie sicher, dass Sie diesen Datensatz veröffentlichen möchten?",
        action: "publishDataset",
      };
      activeV3Modal.value = true;
      break;
    case "Löschen":
      modalConf.value = {
        button: "Ja, löschen",
        header: "Datensatz endgültig löschen",
        text: "Möchten Sie diesen Datensatz wirklich endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden. Alle damit verbundenen Daten werden entfernt.",
        action: "deleteDataset",
      };
      activeV3Modal.value = true;
      break;
    case "LöschenPublished":
      modalConf.value = {
        button: "Ja, löschen",
        header: "Datensatz endgültig löschen",
        text: "Möchten Sie diesen Datensatz wirklich endgültig löschen? Diese Aktion kann nicht rückgängig gemacht werden. Alle damit verbundenen Daten werden entfernt.",
        action: "deletePublishedDataset",
      };
      activeV3Modal.value = true;
      break;
    case "Veröffentlichung aufheben":
      modalConf.value = {
        button: "Ja",
        header: "Veröffentlichung aufheben",
        text: "Sind Sie sicher, dass Sie diesen Datensatz zu einem Entwurf zurücksetzen möchten?",
        action: "setToDraft",
      };
      activeV3Modal.value = true;

      break;
  }
};

let keydownState = ref(false);
const handleClick = (e, state) => {
  if (state) {
    keydownState.value = true;
  } else keydownState.value = false;
};
const handleKeydown = (e) => {
  if (e.target.className != "dpiV3_more-button dpiV3_activeState")
    keydownState.value = true;
};
const handleKeyup = (e) => {
  if (e.target.className != "dpiV3_more-button dpiV3_activeState") {
    keydownState.value = false;
  }
};
</script>
<style scoped>
.dpiV3_tableToast {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2000;
}

.dpiV3_TableRowButtonWrap {
  display: flex;
  padding-left: var(--Spacing-5, 32px);
  justify-content: flex-end;
  align-items: center;
}

.dpiV3_tableRowContent {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1 0 0;
  align-self: stretch;
}

.dpiV3_TableRowUpdated {
  color: var(--neutral-90, #25333d);
  line-height: var(--copy-mini-regular-line-height);
  font-family: Inter;
  font-size: var(--copy-mini-regular-font-size);
  font-style: normal;
  font-weight: var(--copy-mini-regular-font-weight);
}

.dpiV3_TableRowStatus {
  display: flex;
  min-width: 176px;
  max-width: 200px;
  /* padding: 0px var(--Spacing-2, 8px); */
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  flex: 1 0 0;
  align-self: stretch;
}

.dpiV3_dsDesc {
  overflow: hidden;
  color: var(--neutral-80, #3d4952);
  text-overflow: ellipsis;
  font-family: Inter;
  font-size: var(--copy-large-semi-bold-font-size);
  font-style: normal;
  font-weight: var(--copy-large-semi-bold-font-weight);
  line-height: var(--copy-large-semi-bold-line-height);
}

.dpiV3_TableRowDescContainer {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  flex: 1 0 0;
}

.dpiV3_tableRowWrapper {
  all: unset;
  display: flex;
  width: -webkit-fill-available;
  width: -moz-available;
  max-width: 1232px;
  padding: var(--Spacing-2, 8px);
  align-items: flex-start;

  border-bottom: 1px solid var(--neutral-30, #d5d7da);
  background: var(--neutral-0, #fff);

  &:focus {
    outline: 2px solid var(--blue-70, #0196d8);
  }

  &:hover {
    background: var(--neutral-10, #f1f1f3);
    cursor: pointer;
  }
}

.dpiV3_pressedTableRow {
  background: var(--neutral-5, #fafafb) !important;
}

.dpiV3_tableRowInner {
  display: flex;
  height: 80px;
  justify-content: space-between;
  align-items: center;
  flex: 1 0 0;
}
.tRowInDraft {
  border-bottom: none !important;

  &:hover {
    background: unset;
    cursor: unset;
  }
  .dpiV3_TableRowDescContainer {
    display: none;
  }
  .dpiV3_tableRowInner {
    height: unset;
  }
  .dpiV3_TableRowStatus {
    flex-direction: row;
    justify-content: unset;
    gap: var(--Spacing-3, 16px);
    align-items: center;
    max-width: unset;
    flex: unset;
  }
}
</style>
