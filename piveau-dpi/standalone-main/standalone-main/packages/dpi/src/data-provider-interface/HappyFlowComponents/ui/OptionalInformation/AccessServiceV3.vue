<template>
  <div class="dpiV3_documentationsWrap" :class="{ 'dpiV3_docAllAsCard': asCard }">
    <div
      v-for="accessService in accessServices"
      :key="accessService.id"
      class="dpiV3AutoCompleteWrap"
      :class="{ 'dpiV3_docAsCard': asCard }"
    >
      <div class="dpiV3_LinkAndMetadata">
        <InputField
          @input="
            updateAccessService($event, 'dcat:downloadURL', accessService.id)
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
          v-model="accessService['dcat:downloadURL']"
        />
        <div
          v-if="urlErrors[accessService.id]"
          class="dpiV3_errorMessage"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ urlErrors[accessService.id] }}</span>
        </div>
        
        <InputField
          @input="updateAccessService($event, 'dct:title', accessService.id)"
          :addOnText="false"
          :datePicker="false"
          :infoIcon="false"
          @deleteButtonClicked="accessService['dct:title'] = ''"
          placeholder="Bitte Titel eingeben..."
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          label="Titel (optional)"
          :showEndIcon="false"
          v-model="accessService['dct:title']"
          :defaultInput="!showDeleteButton"
        />
        <TextAreaV3
          @input="
            updateAccessService($event, 'dct:description', accessService.id)
          "
          :hint="false"
          @deleteClicked="accessService['dct:description'] = ''"
          label="Beschreibung (optional)"
          placeholder="Bitte Beschreibung eingeben..."
          v-model="accessService['dct:description']"
          :showDeleteButton="showDeleteButton"
        />
        <p
          v-if="minimumDocError && accessServices.length === 1"
          class="copy-mini-regular dpiV3_text_error"
        >
          Mindestens eine URL muss vorhanden sein.
        </p>
      </div>
      <ButtonV3
        class="dpiV3_tempAddMore"
        buttonText="Löschen"
        size="small"
        iconStart="trash"
        variant="tertiary"
        @click="confirmDelete(accessService)"
      />
    </div>
    <div v-if="!asCard" class="dpiV3_tempAddMore">
      <ButtonV3
        buttonText="Weiteren Service hinzufügen"
        size="small"
        iconStart="plus"
        variant="tertiary"
        @click="addAccessService"
      />
    </div>
    <ModalSimpleV3
      v-if="activeV3Modal"
      :buttons="modalConf.button"
      :headerText="modalConf.header"
      :text="modalConf.text"
      @close="activeV3Modal = false"
      :action="modalConf.action"
      @actionHandling="handleButtonAction($event)"
      :optionalString_1="modalConf.optionalString_1"
      :optionalString_2="modalConf.optionalString_2"
    />
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted, reactive } from "vue";
import TextAreaV3 from "../TextAreaV3.vue";
import ButtonV3 from "../ButtonV3.vue";
import ModalSimpleV3 from "../ModalSimpleV3.vue";
import InputField from "../InputField.vue";
import { PhWarning } from "@phosphor-icons/vue";

let modalConf = ref({});
const props = defineProps({
  accessServices: { type: Array, required: true },
  distributionId: { type: Number, required: true },
  asCard: { type: Boolean, required: false, default: false },
  showDeleteButton: { type: Boolean, required: false, default: false}
});

const emit = defineEmits(["update"]);

const minimumDocError = ref(false);
const accessServiceToDelete = ref(null);
const urlErrors = reactive({});

let activeV3Modal = ref(false);

onMounted(() => {
  console.log("Component Mounted: accessServices received for distributionId:", props.distributionId);
  console.log("accessServices:", props.accessServices);

  if (props.accessServices.length === 0) {
    console.log("No accessServices found, initializing first service.");
    emit("update", [
      {
        id: 1,
        "dcat:downloadURL": "",
        "dct:title": "",
        "dct:description": "",
      },
    ]);
  }
});

// URL validation function - checks for domain extension
const isValidUrl = (urlString) => {
  if (!urlString || urlString.trim() === '') return false;
  
  const trimmedUrl = urlString.trim();
  
  // Check if the URL contains a dot followed by at least 2 characters (domain extension)
  const domainPattern = /\.[a-zA-Z]{2,}$/;
  
  return domainPattern.test(trimmedUrl);
};

// Check if any other field in the access service is filled
const hasOtherFieldsFilled = (accessService) => {
  const hasTitle = accessService['dct:title'] && accessService['dct:title'].trim() !== '';
  const hasDescription = accessService['dct:description'] && accessService['dct:description'].trim() !== '';
  
  return hasTitle || hasDescription;
};

const validateUrlField = (serviceId) => {
  const accessService = props.accessServices.find(service => service.id === serviceId);
  
  if (accessService) {
    const urlValue = accessService['dcat:downloadURL'] ? accessService['dcat:downloadURL'].trim() : '';
    const otherFieldsFilled = hasOtherFieldsFilled(accessService);
    
    // If any other field is filled
    if (otherFieldsFilled) {
      // URL is empty
      if (!urlValue) {
        urlErrors[serviceId] = 'Bitte geben Sie eine gültige URL ein.';
      }
      // URL exists but is not valid format
      else if (!isValidUrl(urlValue)) {
        urlErrors[serviceId] = 'Bitte geben Sie eine gültige URL ein.';
      }
      // URL is valid
      else {
        urlErrors[serviceId] = null;
      }
    } 
    // No other fields filled
    else {
      // If URL exists but is invalid format
      if (urlValue && !isValidUrl(urlValue)) {
        urlErrors[serviceId] = 'Bitte geben Sie eine gültige URL ein.';
      } else {
        urlErrors[serviceId] = null;
      }
    }
  }
};

// Validate all items - returns true if there are errors
const validateAllItems = () => {
  let hasErrors = false;
  props.accessServices.forEach(accessService => {
    const urlValue = accessService['dcat:downloadURL'] ? accessService['dcat:downloadURL'].trim() : '';
    const otherFieldsFilled = hasOtherFieldsFilled(accessService);
    
    if (otherFieldsFilled) {
      if (!urlValue) {
        urlErrors[accessService.id] = 'Please enter a valid URL.';
        hasErrors = true;
      } else if (!isValidUrl(urlValue)) {
        urlErrors[accessService.id] = 'Bitte geben Sie eine gültige URL ein.';
        hasErrors = true;
      }
    } else if (urlValue && !isValidUrl(urlValue)) {
      urlErrors[accessService.id] = 'Bitte geben Sie eine gültige URL ein.';
      hasErrors = true;
    }
  });
  return hasErrors;
};

const handleButtonAction = (action) => {
  switch (action) {
    case "delete":
      deleteAccessService();
      break;
  }
};

const addAccessService = () => {
  // Validate all items before adding a new one
  if (validateAllItems()) {
    return;
  }

  const maxId = props.accessServices.length
    ? Math.max(...props.accessServices.map((doc) => doc.id))
    : 0;

  const newAccessService = {
    id: maxId + 1,
    "dcat:downloadURL": "",
    "dct:title": "",
    "dct:description": "",
  };
  emit("update", [...props.accessServices, newAccessService]);
};

const updateAccessService = (event, field, docId) => {
  const updatedAccessServices = props.accessServices.map((doc) =>
    doc.id === docId ? { ...doc, [field]: event.target.value } : doc
  );
  emit("update", updatedAccessServices);
  
  // Validate immediately on input for real-time feedback
  setTimeout(() => {
    validateUrlField(docId);
  }, 0);
};

const confirmDelete = (accessService) => {
  modalConf.value = {
    button: "Löschen",
    header: "Datenservice löschen",
    text: "Sind Sie sicher, dass Sie den Service löschen wollen?",
    action: "delete",
    optionalString_1: accessService['dct:title'],
    optionalString_2: accessService['dcat:downloadURL']
  };
  activeV3Modal.value = true;
  accessServiceToDelete.value = accessService;
};

const cancelDelete = () => {
  accessServiceToDelete.value = null;
};

const deleteAccessService = () => {
  // if (props.accessServices.length === 1) {
  //   minimumDocError.value = true;
  //   return;
  // }

  const updatedDocs = props.accessServices.filter(
    (doc) => doc.id !== accessServiceToDelete.value.id
  );

  // Remove error for deleted item
  delete urlErrors[accessServiceToDelete.value.id];

  emit("update", updatedDocs);
};

// Expose validation methods for parent component
defineExpose({
  validateAllItems
});
</script>

<style scoped>
.dpiV3_errorMessage {
  display: flex;
  align-items: center;
  gap: 4px;
  color: var(--text-error, #a9242c);
  margin-top: 4px;
  margin-bottom: 8px;
}

.dpiV3_errorMessage svg {
  color: var(--text-error, #a9242c);
  flex-shrink: 0;
}

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
    background: var(--Colour-blue-Blue10, #F3FBFF);
    width: 100%;
}

.dpiV3_docAsCard {
    background-color: white;
    margin-bottom: var(--Spacing-3, 8px);
}
</style>