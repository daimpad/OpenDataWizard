<template>
  <div class="dpiV3_documentationsWrap" :class="{ dpiV3_docAllAsCard: asCard }">
    <div
      v-for="conformsToItem in conformsToItems"
      :key="conformsToItem.id"
      class="dpiV3AutoCompleteWrap"
      :class="{ dpiV3_docAsCard: asCard }"
    >
      <div class="dpiV3_LinkAndMetadata">
        <InputField
          @input="
            updateConformsToItem($event, 'dcat:downloadURL', conformsToItem.id)
          "
          @blur="validateUrlField(conformsToItem.id)"
          :addOnText="false"
          :datePicker="false"
          :infoIcon="false"
          placeholder="Bitte URL eingeben..."
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          label="URL"
          :showEndIcon="false"
          v-model="conformsToItem['dcat:downloadURL']"
        />
        <div
          v-if="urlErrors[conformsToItem.id]"
          class="dpiV3_errorMessage"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">Bitte geben Sie eine gültige URL ein.</span>
        </div>
        
        <InputField
          @input="updateConformsToItem($event, 'dct:title', conformsToItem.id)"
          @blur="validateUrlField(conformsToItem.id)"
          :addOnText="false"
          :datePicker="false"
          :infoIcon="false"
          placeholder="Bitte Titel eingeben..."
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          :label="
            $t(
              'message.dataupload.datasets.dcat:distribution.distribution-title.label'
            )
          "
          :showEndIcon="false"
          v-model="conformsToItem['dct:title']"
          :defaultInput="!showDeleteButton"
          @deleteButtonClicked="deleteTitleField(conformsToItem)"
        />
        <p
          v-if="minimumDocError && conformsToItems.length === 1"
          class="copy-mini-regular dpiV3_text_error"
        >
          Mindestens eine URL muss vorhanden sein.
        </p>
      </div>
      <ButtonV3
        class="dpiV3_tempAddMore"
        :buttonText="
          $t(
            'message.dataupload.datasets.dcat:distribution.advanced.documentation.delete'
          )
        "
        size="small"
        iconStart="trash"
        variant="tertiary"
        @click="confirmDelete(conformsToItem)"
      />
    </div>
    <div v-if="!asCard" class="dpiV3_tempAddMore">
      <ButtonV3
        buttonText="Weitere Standards hinzufügen"
        size="small"
        iconStart="plus"
        variant="tertiary"
        @click="addConformsToItem"
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
import { ref, defineProps, defineEmits, onMounted, reactive, computed } from "vue";
import ButtonV3 from "../ButtonV3.vue";
import ModalSimpleV3 from "../ModalSimpleV3.vue";
import InputField from "../InputField.vue";
import { PhWarning } from "@phosphor-icons/vue";

let modalConf = ref({});
const props = defineProps({
  conformsToItems: { type: Array, required: true },
  distributionId: { type: Number, required: true },
  asCard: { type: Boolean, required: false, default: false },
  showDeleteButton: { type: Boolean, required: false, default: false}
});

const emit = defineEmits(["update", "validationChange"]);

const minimumDocError = ref(false);
const conformsToItemToDelete = ref(null);
const urlErrors = reactive({});
const validationTimeouts = reactive({}); // Store debounce timeouts

let activeV3Modal = ref(false);

// Computed property to check if there are any validation errors
const hasValidationErrors = computed(() => {
  return Object.values(urlErrors).some(error => error === true);
});

// Watch for validation changes and emit to parent
const emitValidationState = () => {
  emit("validationChange", hasValidationErrors.value);
};

onMounted(() => {
  console.log(
    "Component Mounted: conformsToItems received for distributionId:",
    props.distributionId
  );
  console.log("conformsToItems:", props.conformsToItems);

  if (props.conformsToItems.length === 0) {
    console.log("No conformsToItems found, initializing first documentation.");
    emit("update", [
      {
        id: 1,
        "dcat:downloadURL": "",
        "dct:title": "",
      },
    ]);
  }
});

const isValidUrl = (urlString) => {
  if (!urlString || urlString.trim() === '') return false;
  
  const trimmedUrl = urlString.trim();
  
  // Check if the URL contains a dot followed by at least 2 characters (domain extension)
  // This matches .com, .de, .org, .co.uk, etc.
  const domainPattern = /\.[a-zA-Z]{2,}$/;
  
  return domainPattern.test(trimmedUrl);
};

const validateUrlField = (docId) => {
  const item = props.conformsToItems.find(doc => doc.id === docId);
  
  if (item) {
    const hasTitle = item['dct:title'] && item['dct:title'].trim() !== '';
    const urlValue = item['dcat:downloadURL'] ? item['dcat:downloadURL'].trim() : '';
    
    // If title is filled
    if (hasTitle) {
      // URL is empty
      if (!urlValue) {
        urlErrors[docId] = 'Bitte geben Sie eine gültige URL ein.';
      }
      // URL exists but is not valid format
      else if (!isValidUrl(urlValue)) {
        urlErrors[docId] = 'Bitte geben Sie eine gültige URL ein (z.B. https://example.com)';
      }
      // URL is valid
      else {
        urlErrors[docId] = null;
      }
    } 
    // No title
    else {
      // If URL exists but is invalid format
      if (urlValue && !isValidUrl(urlValue)) {
        urlErrors[docId] = 'Bitte geben Sie eine gültige URL ein (z.B. https://example.com)';
      } else {
        urlErrors[docId] = null;
      }
    }
  }
};

// Validate all items
const validateAllItems = () => {
  let hasErrors = false;
  props.conformsToItems.forEach(item => {
    const hasTitle = item['dct:title'] && item['dct:title'].trim() !== '';
    const urlValue = item['dcat:downloadURL'] ? item['dcat:downloadURL'].trim() : '';
    
    if (hasTitle) {
      // Title is filled - URL must exist AND be valid
      if (!urlValue) {
        urlErrors[item.id] = 'Bitte geben Sie eine gültige URL ein.';
        hasErrors = true;
      } else if (!isValidUrl(urlValue)) {
        urlErrors[item.id] = 'Bitte geben Sie eine gültige URL ein.';
        hasErrors = true;
      }
    } else if (urlValue && !isValidUrl(urlValue)) {
      // No title but URL exists - URL must be valid
      urlErrors[item.id] = 'Bitte geben Sie eine gültige URL ein.';
      hasErrors = true;
    }
  });
  return hasErrors;
};

const handleButtonAction = (action) => {
  switch (action) {
    case "delete":
      deleteConformsToItem();
      break;
  }
};

const addConformsToItem = () => {
  // Validate all items before adding a new one
  if (validateAllItems()) {
    // Don't add new item if there are validation errors
    return;
  }

  const maxId = props.conformsToItems.length
    ? Math.max(...props.conformsToItems.map((doc) => doc.id))
    : 0;

  const newConformsToItem = {
    id: maxId + 1,
    "dcat:downloadURL": "",
    "dct:title": "",
  };
  emit("update", [...props.conformsToItems, newConformsToItem]);
};


const debouncedValidation = (docId) => {
  // Clear existing timeout for this field
  if (validationTimeouts[docId]) {
    clearTimeout(validationTimeouts[docId]);
  }
  
  // Set new timeout
  validationTimeouts[docId] = setTimeout(() => {
    validateUrlField(docId);
  }, 800); // Wait 800ms after user stops typing
};

const updateConformsToItem = (event, field, docId) => {
  let inputValue = event?.target?.value ?? event;

  const updatedConformsToItems = props.conformsToItems.map((doc) =>
    doc.id === docId ? { ...doc, [field]: event.target.value } : doc
  );
  emit("update", updatedConformsToItems);
  
  // Validate after update
  debouncedValidation(docId);
};

const confirmDelete = (conformsToItem) => {
  modalConf.value = {
    button: "Löschen",
    header: "Standard löschen",
    text: "Sind Sie sicher, dass Sie diesen Standard löschen wollen?",
    action: "delete",
    optionalString_1: conformsToItem["dct:title"],
    optionalString_2: conformsToItem["dcat:downloadURL"],
  };
  activeV3Modal.value = true;
  conformsToItemToDelete.value = conformsToItem;
};

const cancelDelete = () => {
  conformsToItemToDelete.value = null;
};

const deleteConformsToItem = () => {
  // if (props.conformsToItems.length === 1) {
  //   minimumDocError.value = true;
  //   return;
  // }

  const updatedConformsToItems = props.conformsToItems.filter(
    (doc) => doc.id !== conformsToItemToDelete.value.id
  );

  // Remove error for deleted item
  delete urlErrors[conformsToItemToDelete.value.id];
  
  emit("update", updatedConformsToItems);
  emitValidationState();
};

const deleteTitleField = (conformsToItem) => {
  conformsToItem['dct:title'] = "";
  // Revalidate when title is cleared
  validateUrlField(conformsToItem.id);
};

// Expose validation method for parent component
defineExpose({
  validateAllItems,
  hasValidationErrors
});
</script>

<style scoped>
.dpiV3AutoCompleteWrap {
  margin-bottom: var(--Spacing-3, 8px);
}

.dpiV3_text_error {
  color: var(--text-error, #a9242c);
  margin-bottom: 0px;
}

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