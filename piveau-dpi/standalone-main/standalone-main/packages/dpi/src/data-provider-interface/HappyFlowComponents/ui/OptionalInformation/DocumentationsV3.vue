<template>
  <div class="dpiV3_documentationsWrap" :class="{ dpiV3_docAllAsCard: asCard }">
    <div
      v-for="documentation in documentations"
      :key="documentation.id"
      class="dpiV3AutoCompleteWrap"
      :class="{ dpiV3_docAsCard: asCard }"
    >
      <div class="dpiV3_LinkAndMetadata">
        <InputField
          @input="
            updateDocumentation($event, 'dcat:accessURL', documentation.id)
          "
          :addOnText="false"
          :datePicker="false"
          :infoIcon="false"
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:distribution.advanced.documentation.URL-label-placeholder'
            )
          "
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          :label="
            $t(
              'message.dataupload.datasets.dcat:distribution.advanced.documentation.dcat:downloadURL-label'
            )
          "
          :showEndIcon="false"
          v-model="documentation['dcat:accessURL']"
        />
        <div
          v-if="urlErrors[documentation.id]"
          class="dpiV3_errorMessage"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{ urlErrors[documentation.id] }}</span>
        </div>
        
        <InputField
          @input="updateDocumentation($event, 'dct:title', documentation.id)"
          :addOnText="false"
          :datePicker="false"
          :infoIcon="false"
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:distribution.advanced.documentation.title-placeholder'
            )
          "
          :preIcon="false"
          inputFieldSize="large"
          :initialHintText="false"
          :label="
            $t(
              'message.dataupload.datasets.dcat:distribution.distribution-title.label'
            )
          "
          :showEndIcon="false"
          v-model="documentation['dct:title']"
          :defaultInput="!showDeleteButton"
          @deleteButtonClicked="deleteField('dct:title', documentation)"
        />
        <TextAreaV3
          @input="
            updateDocumentation($event, 'dct:description', documentation.id)
          "
          :hint="false"
          :label="
            $t(
              'message.dataupload.datasets.dcat:distribution.advanced.documentation.description'
            )
          "
          :placeholder="
            $t(
              'message.dataupload.datasets.dcat:distribution.advanced.documentation.placeholder'
            )
          "
          v-model="documentation['dct:description']"
          :showDeleteButton="showDeleteButton"
          @deleteClicked="deleteField('dct:description', documentation)"
        />
        <Dropdown
          @update:modelValue="handleFormatValue($event, documentation.id)"
          @input="handleInput($event, 'documentations')"
          dropdownWidth="large"
          type="inputField"
          v-model="documentation['dct:format']"
          :inputFieldProps="{
            addOnText: false,
            initialHintText: false,
            datePicker: false,
            infoIcon: false,
            preIcon: true,
            showEndIcon: false,
            label:
              $t('message.dataupload.datasets.dcat:distribution.format.label') +
              ' (optional)',
            dropdown_dpiV3: true,
            placeholder: $t(
              'message.dataupload.datasets.dcat:distribution.format.placeholder'
            ),
            inputFieldSize: 'large',
            autocomplete: 'true',
            modelValue: documentation['dct:format']['uri'],
            defaultInput: !showDeleteButton,
          }"
          :data="filteredData"
          :autocomplete="true"
          @deleteDropdownField="deleteField('dct:format', documentation)"
        />
        <p
          v-if="minimumDocError && documentations.length === 1"
          class="copy-mini-regular dpiV3_text_error"
        >
          {{
            $t(
              "message.dataupload.datasets.dcat:distribution.advanced.documentation.error-message"
            )
          }}
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
        @click="confirmDelete(documentation)"
      />
    </div>
    <div v-if="!asCard" class="dpiV3_tempAddMore">
      <ButtonV3
        :buttonText="
          $t(
            'message.dataupload.datasets.dcat:distribution.advanced.documentation.add-another'
          )
        "
        size="small"
        iconStart="plus"
        variant="tertiary"
        @click="addDocumentation"
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
import Dropdown from "../Dropdown.vue";
import TextAreaV3 from "../TextAreaV3.vue";
import ButtonV3 from "../ButtonV3.vue";
import ModalSimpleV3 from "../ModalSimpleV3.vue";
import InputField from "../InputField.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { useFormValues } from "../../../composables/useDpiFormValues";

const { formValues } = useFormValues();

let modalConf = ref({});
const props = defineProps({
  documentations: { type: Array, required: true },
  fileTypes: { type: Array, required: false, default: () => [] },
  distributionId: { type: Number, required: true },
  asCard: { type: Boolean, required: false, default: false },
  showDeleteButton: { type: Boolean, required: false, default: false },
});

const emit = defineEmits(["update"]);

const filteredData = ref([...props.fileTypes]);
const minimumDocError = ref(false);
const documentationToDelete = ref(null);
const urlErrors = reactive({});

let activeV3Modal = ref(false);

onMounted(() => {
  if (props.documentations.length === 0) {
    emit("update", [
      {
        id: 1,
        "dcat:accessURL": "",
        "dct:format": "",
        "dct:title": "",
        "dct:description": "",
        "formatUri:": "",
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

// Check if any other field in the documentation is filled
const hasOtherFieldsFilled = (documentation) => {
  const hasTitle = documentation['dct:title'] && documentation['dct:title'].trim() !== '';
  const hasDescription = documentation['dct:description'] && documentation['dct:description'].trim() !== '';
  const hasFormat = documentation['dct:format'] && documentation['dct:format'].trim !== '' && 
                    (typeof documentation['dct:format'] === 'string' ? documentation['dct:format'].trim() !== '' : true);
  
  return hasTitle || hasDescription || hasFormat;
};

const validateUrlField = (docId) => {
  const documentation = props.documentations.find(doc => doc.id === docId);
  
  if (documentation) {
    const accessUrl = documentation['dcat:accessURL'];
    const urlValue = (typeof accessUrl === 'string') ? accessUrl.trim() : '';
    const otherFieldsFilled = hasOtherFieldsFilled(documentation);
    
    if (otherFieldsFilled) {
      if (!urlValue) {
        urlErrors[docId] = 'Bitte geben Sie eine gültige URL ein.';
      }
      else if (!isValidUrl(urlValue)) {
        urlErrors[docId] = 'Bitte geben Sie eine gültige URL ein.';
      }
      else {
        urlErrors[docId] = null;
      }
    } 
    else {
      if (urlValue && !isValidUrl(urlValue)) {
        urlErrors[docId] = 'Bitte geben Sie eine gültige URL ein.';
      } else {
        urlErrors[docId] = null;
      }
    }
  }
};

const validateAllItems = () => {
  let hasErrors = false;
  props.documentations.forEach(documentation => {
    const accessUrl = documentation['dcat:accessURL'];
    const urlValue = (typeof accessUrl === 'string') ? accessUrl.trim() : '';
    const otherFieldsFilled = hasOtherFieldsFilled(documentation);
    
    if (otherFieldsFilled) {
      if (!urlValue) {
        urlErrors[documentation.id] = 'Bitte geben Sie eine gültige URL ein.';
        hasErrors = true;
      } else if (!isValidUrl(urlValue)) {
        urlErrors[documentation.id] = 'Bitte geben Sie eine gültige URL ein.';
        hasErrors = true;
      }
    } else if (urlValue && !isValidUrl(urlValue)) {
      urlErrors[documentation.id] = 'Bitte geben Sie eine gültige URL ein.';
      hasErrors = true;
    }
  });
  return hasErrors;
};

const handleButtonAction = (action) => {
  switch (action) {
    case "delete":
      deleteDocumentation();
      break;
  }
};

const handleInput = (event, field, docId = null) => {
  filteredData.value = [...props.fileTypes];
  const inputValue =
    typeof event === "string"
      ? event.trim().toUpperCase()
      : event?.target?.value?.trim().toUpperCase();

  if (inputValue.length > 0) {
    filteredData.value = props.fileTypes.filter((item) =>
      item["@value"].toUpperCase().startsWith(inputValue)
    );
  } else {
    filteredData.value = [...props.fileTypes];
  }
};

const addDocumentation = () => {
  // Validate all items before adding a new one
  if (validateAllItems()) {
    return;
  }

  const maxId = props.documentations.length
    ? Math.max(...props.documentations.map((doc) => doc.id))
    : 0;

  const newDocumentation = {
    id: maxId + 1,
    "dcat:accessURL": "",
    "dct:format": "",
    "dct:title": "",
    "dct:description": "",
    "formatUri:": "",
  };
  emit("update", [...props.documentations, newDocumentation]);
};

const handleFormatValue = (value, docId) => {
  let updatedDocs = "";
  filteredData.value = [...props.fileTypes]; //reset bec. used in more dropdowns

  const foundEntry = filteredData.value.find(
    (entry) => entry["@value"] === value
  );
  const currentDist = ref(
    formValues.value["DistributionSimple"]["dcat:distribution"].find(
      (entry) => entry["id"] === props.distributionId
    )
  );
  const currentDocumentation = ref(
    currentDist.value.documentations.find((entry) => entry["id"] === docId)
  );
  if (currentDocumentation.value === undefined) {
  } else currentDocumentation.value.formatUri = foundEntry.uri;
  updateDocumentation(value,'dct:format',docId);
};

const updateDocumentation = (event, field, docId) => {
  let updatedDocs = "";
  
  if (field === "dcat:accessURL") {
    const inputValue = typeof event === 'string' ? event : (event?.target?.value ?? '');
  
    updatedDocs = props.documentations.map((doc) =>
      doc.id === docId ? { ...doc, [field]: inputValue } : doc
    );
    
  } else if (field === "dct:format") {
    filteredData.value = [...props.fileTypes]; //reset bec. used in more dropdowns

    const inputValue =
      typeof event === "string"
        ? event.trim().toUpperCase()
        : event?.target?.value?.trim().toUpperCase();

    if (inputValue.length > 0) {
      filteredData.value = props.fileTypes.filter((item) =>
        item["@value"].toUpperCase().startsWith(inputValue)
      );
    } else {
      filteredData.value = [...props.fileTypes];
    }
        
    const foundEntry = filteredData.value.find(
      (entry) => entry["@value"] === event
    );

    updatedDocs = props.documentations.map((doc) =>
      doc.id === docId ? { ...doc, [field]: foundEntry['@value'], ['formatUri']: foundEntry['uri'] } : doc
    );
    
  } else {
    updatedDocs = props.documentations.map((doc) =>
      doc.id === docId ? { ...doc, [field]: event.target.value } : doc
    );
  }

  emit("update", updatedDocs);
  
  // Validate immediately on input for real-time feedback
  setTimeout(() => {
    validateUrlField(docId);
  }, 0);
};

const confirmDelete = (documentation) => {
  modalConf.value = {
    button: "Löschen",
    header: "Dokumentation löschen",
    text: "Sind Sie sicher, dass Sie die Dokumentation löschen wollen?",
    action: "delete",
    optionalString_1: documentation["dct:title"],
    optionalString_2: documentation["dcat:accessURL"],
  };
  activeV3Modal.value = true;
  documentationToDelete.value = documentation;
};

const cancelDelete = () => {
  documentationToDelete.value = null;
};

const deleteDocumentation = () => {
  // if (props.documentations.length === 1) {
  //   minimumDocError.value = true;
  //   return;
  // }

  const updatedDocs = props.documentations.filter(
    (doc) => doc.id !== documentationToDelete.value.id
  );

  // Remove error for deleted item
  delete urlErrors[documentationToDelete.value.id];

  emit("update", updatedDocs);
};

const deleteField = (field, documentation) => {
  switch (field) {
    case "dct:description":
      documentation["dct:description"] = "";
      break;
    case "dct:title":
      documentation["dct:title"] = "";
      break;
    case "dct:format":
      documentation["dct:format"] = "";
      break;
    default:
      break;
  }
  
  // Revalidate when field is cleared
  setTimeout(() => {
    validateUrlField(documentation.id);
  }, 0);
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

.validation-error {
  color: var(--text-error, #a9242c);
  font-size: var(--copy-small-regular-font-size, 15px);
  margin-top: 4px;
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
  background: var(--Colour-blue-Blue10, #f3fbff);
  width: 100%;
}

.dpiV3_docAsCard {
  background-color: white;
  margin-bottom: var(--Spacing-3, 8px);
}
</style>