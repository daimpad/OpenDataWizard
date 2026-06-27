<template>
  <div class="dpiV3_documentationsWrap" :class="{ dpiV3_docAllAsCard: asCard }">
    <div
      v-for="policyItem in policyItems"
      :key="policyItem.id"
      class="dpiV3AutoCompleteWrap"
      :class="{ dpiV3_docAsCard: asCard }"
    >
 
      <div class="dpiV3_LinkAndMetadata">
        <InputField
          @input="
            updatePolicyItem($event, 'dcat:downloadURL', policyItem.id)
          "
          :addOnText="false"
          :datePicker="false"
          :infoIcon="false"
          placeholder="Bitte URL eingeben..."
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          label="URL"
          :showEndIcon="false"
          v-model="policyItem['dcat:downloadURL']"
        />
        <p
          v-if="urlError[policyItem.id]"
          class="copy-mini-regular dpiV3_text_error"
        >
          {{ urlError[policyItem.id] }}
        </p>
        <p
          v-if="minimumDocError && policyItems.length === 1"
          class="copy-mini-regular dpiV3_text_error"
        >
          Mindestens eine URL muss vorhanden sein.
        </p>
      </div>
      <!-- <ButtonV3
        class="dpiV3_tempAddMore"
        :buttonText="
          $t(
            'message.dataupload.datasets.dcat:distribution.advanced.documentation.delete'
          )
        "
        size="small"
        iconStart="trash"
        variant="tertiary"
        @click="confirmDelete(policyItem)"
      /> -->
    </div>
    <!-- <div v-if="!asCard" class="dpiV3_tempAddMore">
      <ButtonV3
        buttonText="Weiteres Regelwerk hinzufügen"
        size="small"
        iconStart="plus"
        variant="tertiary"
        @click="addPolicyItem"
      />
    </div> -->
    <ModalSimpleV3
      v-if="activeV3Modal"
      :buttons="modalConf.button"
      :headerText="modalConf.header"
      :text="modalConf.text"
      @close="activeV3Modal = false"
      :action="modalConf.action"
      @actionHandling="handleButtonAction($event)"
      :optionalString_1="modalConf.optionalString_1"
    />
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted } from "vue";
import ButtonV3 from "../ButtonV3.vue";
import ModalSimpleV3 from "../ModalSimpleV3.vue";
import InputField from "../InputField.vue";
import { useFormValues } from "../../../composables/useDpiFormValues";

const { formValues } = useFormValues();

let modalConf = ref({});
const urlError = ref({});

const props = defineProps({
  policyItems: { type: Array, required: true },
  distributionId: { type: Number, required: true },
  asCard: { type: Boolean, required: false, default: false },
});

const emit = defineEmits(["update"]);

const minimumDocError = ref(false);
const policyItemToDelete = ref(null);

let activeV3Modal = ref(false);

onMounted(() => {
  console.log(
    "Component Mounted: policyItems received for distributionId:",
    props.distributionId
  );

  if (props.policyItems.length === 0) {
    emit("update", [
      {
        id: 1,
        "dcat:downloadURL": "",
      },
    ]);
  }
});

const handleButtonAction = (action) => {
  switch (action) {
    case "delete":
      deletePolicyItem();
      break;
  }
};

const validateAllItems = () => {
  const isValidUrl = (urlString) => {
    if (!urlString || urlString.trim() === '') return false;
    const trimmedUrl = urlString.trim();
    const domainPattern = /\.[a-zA-Z]{2,}$/;
    return domainPattern.test(trimmedUrl);
  };

  props.policyItems.forEach(item => {
    const urlValue = item['dcat:downloadURL'] ? item['dcat:downloadURL'].trim() : '';
    
    if (urlValue && !isValidUrl(urlValue)) {
      urlError.value[item.id] = 'Bitte geben Sie eine gültige URL ein.';
    } else {
      delete urlError.value[item.id];
    }
  });
};

defineExpose({ validateAllItems });

const addPolicyItem = () => {
  const maxId = props.policyItems.length
    ? Math.max(...props.policyItems.map((doc) => doc.id))
    : 0;

  const newPolicyItem = {
    id: maxId + 1,
    "dcat:downloadURL": "",
  };
  emit("update", [...props.policyItems, newPolicyItem]);
};

const updatePolicyItem = (event, field, docId) => {
 
let activeDist = ref(formValues.value.DistributionSimple['dcat:distribution'].find(o => o.id === docId));

if (activeDist.value['policyItems'][0] === undefined) {
  activeDist.value['policyItems'] = [{'dcat:downloadURL': ''}] 
}

activeDist.value['policyItems'][0]['dcat:downloadURL'] = event.target.value
  
  // const updatedPolicyItems = props.policyItems.map((doc) =>
  //   doc.id === docId ? { ...doc, [field]: event.target.value } : doc
  // );
  // emit("update", updatedPolicyItems);
};

const confirmDelete = (policyItem) => {
  modalConf.value = {
    button: "Löschen",
    header: "Regelwerk löschen",
    text: "Sind Sie sicher, dass Sie dieses Regelwerk löschen wollen?",
    action: "delete",
    optionalString_1: policyItem["dcat:downloadURL"],
  };
  activeV3Modal.value = true;
  policyItemToDelete.value = policyItem;
};

const cancelDelete = () => {
  policyItemToDelete.value = null;
};

const deletePolicyItem = () => {
  if (props.policyItems.length === 1) {
    minimumDocError.value = true;
    return;
  }

  const updatedPolicyItems = props.policyItems.filter(
    (doc) => doc.id !== policyItemToDelete.value.id
  );

  emit("update", updatedPolicyItems);
};
</script>

<style scoped>
.dpiV3AutoCompleteWrap {
  margin-bottom: var(--Spacing-3, 8px);
}

.dpiV3_text_error {
  color: var(--text-error, #a9242c);
  margin-bottom: 0px;
}

.dpiV3_tempAddMore {
  width: 100%;
  display: flex;
  justify-content: end;
}

.dpiV3_docAllAsCard {
  background: var(--Colour-blue-Blue10, #f3fbff);
  width: 100%;
}

.dpiV3_docAsCard {
  background-color: white;
  margin-bottom: var(--Spacing-3, 8px);
}
</style>
