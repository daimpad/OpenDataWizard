<template>
  <additionalText>
    {{ item.help }}
  </additionalText>
  <activeItem>
    <!-- Repeatable items -->
    <div
      class="repeatableOuter dpiV3AutoCompleteWrap"
      v-if="item['$formkit'] === 'repeatable'"
      v-for="(repeatableItem, repeatIndex) in displayItems"
      :key="repeatIndex"
    >
      <itemWrapperAdditionals
        v-if="item['$formkit'] === 'repeatable'"
        v-for="(fields, index) in item.children[0].children.filter(
          (field) => field['$formkit'] !== 'group'
        )"
        :key="index"
      >
        <div class="dpiV3_LinkAndMetadata">
          <!-- URL inputs -->
          <InputField
            v-if="fields['$formkit'] === 'url' && item.name === '@id'"
            :label="fields.label"
            :placeholder="item.placeholder"
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            :show-error="
              !!validationErrors[`${item.identifier}-${repeatIndex}`]
            "
            @input="handleInput($event, item.name, repeatIndex, '@id')"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.['@id']"
            :v-model="localValues[repeatIndex]?.['@id']"
          />

          <InputField
            v-if="fields['$formkit'] === 'url' && item.name != '@id'"
            :label="fields.label"
            :placeholder="item.placeholder"
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            :show-error="false"
            @input="handleInput($event, item.name, repeatIndex, fields.name)"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.[fields.name]"
            :v-model="localValues[repeatIndex]?.[fields.name]"
          />
          <div
            v-if="
              fields['$formkit'] === 'url' &&
              fields.label != 'Homepage' &&
              fields.label != 'Contributor Homepage' &&
              item.name != '@id' &&
              validationErrors[`${item.identifier}-${repeatIndex}`]
            "
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              validationErrors[`${item.identifier}-${repeatIndex}`]
            }}</span>
          </div>

          <!-- String inputs -->
          <InputField
            v-if="fields['$formkit'] === 'text' && item.name === '@value'"
            :label="fields.label"
            :placeholder="item.placeholder"
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            :show-error="
              !!validationErrors[`${item.identifier}-${repeatIndex}`]
            "
            @input="handleInput($event, item.name, repeatIndex, '@value')"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.['@value']"
            :v-model="localValues[repeatIndex]?.['@value']"
          />
          <div
            v-if="
              fields['$formkit'] === 'text' &&
              item.name === '@value' &&
              validationErrors[`${item.identifier}-${repeatIndex}`]
            "
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              validationErrors[`${item.identifier}-${repeatIndex}`]
            }}</span>
          </div>

          <InputField
            v-if="fields['$formkit'] === 'text' && item.name != '@value'"
            :label="fields.label"
            :placeholder="item.placeholder"
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            :show-error="
              fields.name === 'foaf:name' &&
              !!validationErrors[`${item.identifier}-${repeatIndex}`]
            "
            @input="handleInput($event, item.name, repeatIndex, fields.name)"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.[fields.name]"
            :v-model="localValues[repeatIndex]?.[fields.name]"
          />
          <div
            v-if="
              fields['$formkit'] === 'text' &&
              fields.name === 'foaf:name' &&
              validationErrors[`${item.identifier}-${repeatIndex}`]
            "
            class="dpiV3_errorMessage"
          >
            <PhWarning :size="16" weight="fill" />
            <span class="copy-mini-regular">{{
              validationErrors[`${item.identifier}-${repeatIndex}`]
            }}</span>
          </div>

          <!-- Selects -->
          <Dropdown
            v-if="
              fields['$formkit'] === 'select' &&
              fields['identifier'] !== 'language'
            "
            dropdownWidth="large"
            type="inputField"
            @click="
              filterVocabularies(
                Object.values(fields['options']),
                $event,
                fields['$formkit']
              )
            "
            :v-model="localValues[repeatIndex]?.[fields.name]"
            :inputFieldProps="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,
              label: fields.label,
              dropdown_dpiV3: true,
              placeholder:
                localValues[repeatIndex]?.[fields.name] || fields.placeholder,
              inputFieldSize: 'large',
              autocomplete: 'true',
              modelValue: localValues[repeatIndex]?.[fields.name],
              showError:
                item.identifier === 'temporalResolution' &&
                !!validationErrors[`${item.identifier}-${repeatIndex}`],
            }"
            @update:modelValue="
              handleSelect($event, item.name, repeatIndex, fields.name)
            "
            :data="selectList"
          />

          <!-- Autocompletes -->
          <Dropdown
            v-if="fields['$formkit'] === 'auto'"
            dropdownWidth="large"
            type="inputField"
            @input="filterVocabularies(item.voc, $event, fields['$formkit'])"
            @click="
              filterVocabularies(item['voc'], 'prefill', item['$formkit'])
            "
            :inputFieldProps="{
              addOnText: false,
              initialHintText: false,
              datePicker: false,
              infoIcon: false,
              preIcon: false,

              dropdown_dpiV3: true,
              placeholder:
                localValues[repeatIndex]?.['@value'] || item.placeholder,
              inputFieldSize: 'large',
              autocomplete: 'true',
              modelValue: localValues[repeatIndex]?.['@value'],
              showError: false,
            }"
            @chosenVocItem="
              (chosenItem) =>
                handleRepeatableAutocomplete(chosenItem, repeatIndex, item.name)
            "
            :data="selectList"
          />

          <!-- Textareas -->
          <TextAreaV3
            v-if="fields['$formkit'] === 'textarea' && item.name === '@value'"
            :label="fields.label"
            :placeholder="
              localValues[repeatIndex]?.[fields.name] || item.placeholder
            "
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            @input="handleInput($event, item.name, repeatIndex, '@value')"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.['@value']"
          />

          <TextAreaV3
            v-if="fields['$formkit'] === 'textarea' && item.name != '@value'"
            :label="fields.label"
            :placeholder="
              localValues[repeatIndex]?.[fields.name] || item.placeholder
            "
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            @input="handleInput($event, item.name, repeatIndex, fields.name)"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.[fields.name]"
          />

          <!-- Emails -->
          <InputField
            v-if="fields['$formkit'] === 'email'"
            :label="fields.label"
            :placeholder="item.placeholder"
            :addOnText="false"
            :infoIcon="false"
            :preIcon="false"
            :showEndIcon="false"
            :show-error="false"
            @input="handleInput($event, item.name, repeatIndex, '@value')"
            :initialHintText="false"
            :model-value="localValues[repeatIndex]?.['@value']"
          />
        </div>
      </itemWrapperAdditionals>

      <!-- Error for temporalResolution after all fields -->
      <div
        v-if="
          item.identifier === 'temporalResolution' &&
          validationErrors[`${item.identifier}-${repeatIndex}`]
        "
        class="dpiV3_errorMessage"
      >
        <PhWarning :size="16" weight="fill" />
        <span class="copy-mini-regular">{{
          validationErrors[`${item.identifier}-${repeatIndex}`]
        }}</span>
      </div>

      <TextButtonSmall
        button-text="Löschen"
        icon-start="trash"
        class="rightAlign"
        @click="removeRepeatable(item.name, repeatIndex)"
      />
    </div>

    <!-- Non-repeatable items -->
    <itemWrapperAdditionals
      v-if="item['$formkit'] != 'repeatable' || item['$formkit'] != 'group'"
    >
      <div class="dpiV3_LinkAndMetadata">
        <InputField
          v-if="item['$formkit'] === 'simpleInput'"
          :label="item.label"
          :placeholder="item.placeholder"
          :infoIcon="false"
          :preIcon="false"
          :showEndIcon="false"
          :show-error="!!validationErrors[item.identifier]"
          @input="handleInput($event, item.name, 0, '@value')"
          :initialHintText="false"
          :model-value="localValues[0]?.['@value']"
        />
        <div
          v-if="
            item['$formkit'] === 'simpleInput' &&
            validationErrors[item.identifier]
          "
          class="dpiV3_errorMessage"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{
            validationErrors[item.identifier]
          }}</span>
        </div>

        <!-- Groups -->
        <InputField
          v-for="(fields, index) in item.children"
          v-if="
            item['$formkit'] === 'formkitGroup' &&
            item.children[0]['$formkit'] !== 'date'
          "
          :key="index"
          :label="fields.label"
          :placeholder="item.placeholder"
          :infoIcon="false"
          :preIcon="false"
          :showEndIcon="false"
          :show-error="!!validationErrors[item.identifier]"
          @input="handleInput($event, item.name, index, fields.name)"
          :initialHintText="false"
          :model-value="localValues[index]?.[fields.name]"
          :v-model="localValues[index]?.[fields.name]"
        />
        <div v-if="firstTemporalError" class="dpiV3_errorMessage">
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{
            firstTemporalError.message
          }}</span>
        </div>
        <!-- Dates in groups -->
        <InputField
          v-for="(fields, index) in item.children"
          v-if="
            item['$formkit'] === 'formkitGroup' &&
            item.children[0]['$formkit'] === 'date'
          "
          :key="index"
          :label="fields.label"
          :placeholder="item.placeholder"
          :infoIcon="false"
          :preIcon="false"
          :datePicker="true"
          inputType="date"
          :showEndIcon="false"
          :show-error="false"
          @input="handleInput($event, item.name, index, fields.name)"
          :initialHintText="false"
          :model-value="localValues[index]?.[fields.name]?.['@value']"
          :v-model="localValues[index]?.[fields.name]?.['@value']"
        />

        <!-- Autocompletes -->
        <Dropdown
          v-if="item['$formkit'] === 'auto'"
          dropdownWidth="large"
          type="inputField"
          @input="
            filterVocabularies(item['voc'], $event, item['$formkit']);
            isAutocomplete = true;
          "
          @click="
            filterVocabularies(item['voc'], 'prefill', item['$formkit']);
            isAutocomplete = true;
          "
          v-model="modelVal"
          :inputFieldProps="{
            addOnText: false,
            initialHintText: false,
            datePicker: false,
            infoIcon: false,
            preIcon: false,
            label: item.label,
            dropdown_dpiV3: true,
            placeholder: localValues[0]?.['@value'] || item.placeholder,
            inputFieldSize: 'large',
            autocomplete: 'true',
            modelValue: modelVal,
            showError: !!validationErrors[item.identifier],
          }"
          @chosenVocItem="handleValue"
          :data="selectList"
        />
        <div
          v-if="validationErrors[item.identifier]"
          class="dpiV3_errorMessage"
        >
          <PhWarning :size="16" weight="fill" />
          <span class="copy-mini-regular">{{
            validationErrors[item.identifier]
          }}</span>
        </div>
      </div>
    </itemWrapperAdditionals>
  </activeItem>

  <div class="dpiV3_tempAddMore" v-if="item['$formkit'] === 'repeatable'">
    <ButtonV3
      buttonText="Weitere hinzufügen"
      size="large"
      iconStart="plus"
      variant="tertiary"
      class="mt-3"
      @click="createOptional(item)"
    />
  </div>

  <interaction class="dpiV3_interactionWrap">
    <div class="dpiV3_actionButtonWrap">
      <ButtonV3
        @click="back"
        buttonText="Zurück"
        size="large"
        iconStart="CaretLeft"
        variant="tertiary"
      />
      <ButtonV3 @click="saveToStore" buttonText="Speichern" size="large" />
    </div>
  </interaction>
</template>

<script setup>
import ButtonV3 from "../ButtonV3.vue";
import InputField from "../InputField.vue";
import TextAreaV3 from "../TextAreaV3.vue";
import Dropdown from "../Dropdown.vue";
import { PhWarning } from "@phosphor-icons/vue";
import { defineEmits, reactive, computed, watch } from "vue";
import { getNode } from "@formkit/core";
import { ref } from "vue";
import { getOptionalURIs } from "../../services/dpiV3_apis";
import { getCurrentInstance } from "vue";
import { useFormValues } from "../../../composables/useDpiFormValues";
import TextButtonSmall from "../TextButtonSmall.vue";

const emit = defineEmits();
let inputValue = ref([{}]);
let selectList = ref([]);
let isAutocomplete = ref(false);
let modelVal = ref();
let instance = getCurrentInstance().appContext.app.config.globalProperties.$env;
const { formValues } = useFormValues();
const validationErrors = reactive({});

// Local state for temporary values
const localValues = ref([]);

const props = defineProps({
  context: Object,
  item: Object,
});

// Initialize local values from formValues
watch(
  () => formValues.value["Additionals"]?.[props.item.name],
  (newVal) => {
    if (newVal && newVal.length > 0) {
      localValues.value = JSON.parse(JSON.stringify(newVal));
    }
  },
  { immediate: true }
);

// Ensure at least one empty item for input
let displayItems = computed(() => {
  if (localValues.value.length === 0) {
    return [{}];
  }
  return localValues.value;
});

const hasAnyFieldFilled = (itemData) => {
  if (!itemData) return false;

  return Object.entries(itemData).some(([key, value]) => {
    if (typeof value === "string") {
      return value.trim() !== "";
    }
    if (typeof value === "object" && value !== null) {
      return hasAnyFieldFilled(value);
    }
    return false;
  });
};

const isValidUrl = (urlString) => {
  if (!urlString || urlString.trim() === "") return false;

  // const domainPattern = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?(\.[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?)*\.[a-zA-Z]{2,}(\/.*)?$/;
  const domainPattern =
    /^(https?|ftp):\/\/([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/[^\s]*)?$/;
  return domainPattern.test(urlString.trim());
};

const validateItem = (itemData, index = null) => {
  const identifier = props.item.identifier;
  let errorKey = index !== null ? `${identifier}-${index}` : identifier;
  validationErrors[errorKey] = null;

  console.log(identifier);
  if (!hasAnyFieldFilled(itemData)) {
    return true;
  }

  switch (identifier) {
    case "conformsTo":
      const conformsToUrl = itemData["@id"] || "";
      if (!conformsToUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(conformsToUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;
    // case "admsIdentifier":
    //   const admsIdentifierUrl = itemData["@id"] || "";
    //   if (!admsIdentifierUrl.trim()) {
    //     validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
    //     return false;
    //   }
    //   if (!isValidUrl(admsIdentifierUrl)) {
    //     validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
    //     return false;
    //   }
    //   break;
    case "spatial":
      const spatialUrl = itemData["@id"] || "";
      if (!spatialUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(spatialUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein";
        return false;
      }
      break;
    case "relation":
      const relationUrl = itemData["@id"] || "";
      if (!relationUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(relationUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;
    case "qualifiedRelation":
      const qualifiedRelationUrl = itemData["@id"] || "";
      if (!qualifiedRelationUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(qualifiedRelationUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
    case "qualifiedAttribution":
      const qualifiedAttributionUrl = itemData["@id"] || "";
      if (!qualifiedAttributionUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(qualifiedAttributionUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
    case "source":
      const sourceUrl = itemData["@id"] || "";
      if (!sourceUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(sourceUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
    case "hasVersion":
      const hasVersionUrl = itemData["@id"] || "";
      if (!hasVersionUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(hasVersionUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
    case "isVersionOf":
      const isVersionOfUrl = itemData["@id"] || "";
      if (!isVersionOfUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(isVersionOfUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }

      break;
    case "isReferencedBy":
      const isReferencedByUrl = itemData["@id"] || "";
      if (!isReferencedByUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(isReferencedByUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;
    case "references":
      const referencesUrl = itemData["@id"] || "";
      if (!referencesUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(referencesUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;
    case "wasGeneratedBy":
      const wasGeneratedByUrl = itemData["@id"] || "";
      if (!wasGeneratedByUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(wasGeneratedByUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;

    case "landingPage":
      const landingPageUrl = itemData["@id"] || "";
      if (!landingPageUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(landingPageUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;

    case "page":
      const PageUrl = itemData["foaf:homepage"] || "";
      if (!PageUrl.trim()) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      if (!isValidUrl(PageUrl)) {
        validationErrors[errorKey] = "Bitte geben Sie eine gültige URL ein.";
        return false;
      }
      break;

    case "temporalResolution":
      errorKey = "temporalResolution-0";
      // Merge all items from localValues to check all fields together
      const mergedData = Object.assign({}, ...localValues.value);

      const requiredFields = [
        "Year",
        "Month",
        "Day",
        "Hour",
        "Minute",
        "Second",
      ];
      const hasAnyContent = requiredFields.some((field) => {
        const value = mergedData[field];
        return value && typeof value === "string" && value.trim() !== "";
      });

      // If any field has content, all fields are required
      if (hasAnyContent) {
        const missingFields = requiredFields.filter((field) => {
          const value = mergedData[field];
          return !value || (typeof value === "string" && value.trim() === "");
        });

        if (missingFields.length > 0) {
          validationErrors[errorKey] = "Alle Zeiteinheiten sind erforderlich";
          return false;
        }
        validationErrors[errorKey] = null;
      }

      break;

    case "creator":
      const creatorName = itemData["foaf:name"] || itemData["@value"] || "";
      if (!creatorName.trim()) {
        validationErrors[errorKey] = "Name ist erforderlich";
        return false;
      }
      break;

    case "contributor":
      const contributorName = itemData["foaf:name"] || itemData["@value"] || "";
      if (!contributorName.trim()) {
        validationErrors[errorKey] = "Name ist erforderlich";
        return false;
      }
      break;
  }

  return true;
};

const validateAll = () => {
  let isValid = true;

  Object.keys(validationErrors).forEach((key) => {
    validationErrors[key] = null;
  });

  const identifier = props.item.identifier;
  const itemsToValidate = [
    "conformsTo",
    "temporalResolution",
    "creator",
    "contributor",
    "page",
    "landingPage",
    "admsIdentifier",
    "spatial",
    "relation",
    "qualifiedRelation",
    "qualifiedAttribution",
    "source",
    "hasVersion",
    "isVersionOf",
    "isReferencedBy",
    "references",
    "wasGeneratedBy"
  ];

  if (itemsToValidate.includes(identifier)) {
    localValues.value.forEach((itemData, index) => {
      if (!validateItem(itemData, index)) {
        isValid = false;
      }
    });
  }

  return isValid;
};

const createOptional = (item) => {
  localValues.value.push({});
};

const back = () => {
  emit("goBack", false);
};

const removeRepeatable = (item, index) => {
  localValues.value.splice(index, 1);
  delete validationErrors[`${props.item.identifier}-${index}`];
};

const firstTemporalError = computed(() => {
  if (props.item.identifier !== "temporalResolution") return null;

  // Check all possible indices
  for (let i = 0; i < localValues.value.length; i++) {
    const errorKey = `temporalResolution-${i}`;
    if (validationErrors[errorKey]) {
      return {
        key: errorKey,
        message: validationErrors[errorKey],
      };
    }
  }
  return null;
});

const handleRepeatableAutocomplete = (chosenItem, index, property) => {
  if (!localValues.value[index]) {
    localValues.value[index] = {};
  }

  localValues.value[index]["@value"] =
    chosenItem["pref_label"].de || chosenItem["pref_label"].en;
  localValues.value[index]["uri"] = chosenItem.resource;

  setTimeout(() => {
    validateItem(localValues.value[index], index);
  }, 0);
};

const handleInput = (e, identifier, index, key) => {
  const value = typeof e === "string" ? e : e?.target?.value ?? "";

  if (!localValues.value[index]) {
    localValues.value[index] = {};
  }

  if (identifier === "dct:issued") {
    localValues.value[index][key] = formatDateFromIso(value);
  } else {
    localValues.value[index][key] = value;
  }

  if (value != "") {
    inputValue.value[0][identifier] = value;
  }

  if (key === "@id" && value != "") {
    inputValue.value[0][identifier] = "https://" + value;
  }

  setTimeout(() => {
    validateItem(localValues.value[index], index);
  }, 0);
};

function formatDateFromIso(iso) {
  const [year, month, day] = iso.split("-");
  return `${day}.${month}.${year}`;
}

const handleSelect = (e, property, index, fieldname) => {
  if (!localValues.value[index]) {
    localValues.value[index] = {};
  }
  localValues.value[index][fieldname] = e;

  setTimeout(() => {    
    validateItem(localValues.value[index], index);
  }, 0);
};

const handleValue = (item) => {
  modelVal.value = item["pref_label"]["de"];
  inputValue.value = [
    { ["@value"]: item["pref_label"]["de"], uri: item.resource },
  ];
  return item;
};
async function sortAlphabetically(array){
  console.log(array);
  array
//  array.sort((a, b) =>{
//   return a.pref_label.de.localeCompare(b.pref_label.de, "de")
//  });
return array
}
const filterVocabularies = async (list, e, type) => {
  if (e === "prefill") {
    selectList.value = await getOptionalURIs(e, instance.api.baseUrl, list);
    console.log(selectList.value);
    selectList.value.sort((a, b) => {
  if (!a.pref_label?.de) return 1;
  if (!b.pref_label?.de) return -1;
  return a.pref_label.de.localeCompare(b.pref_label.de);
});
  }
  if (type === "select") {
    let cachelist = ref([]);
    list.forEach((element) => {
      cachelist.value.push({ ["@value"]: element, selected: false });
    });
    if (e !== "") {
      try {
        selectList.value = cachelist.value;
      } catch (error) {
        console.log(error);
      }
    }
  }
  if (type === "auto" && e !== "prefill") {
    let cachelist = ref(await getOptionalURIs(e, instance.api.baseUrl, list));
    if (e !== "") {
      try {
        const result = {
          label: cachelist.value.filter((item) => {
            const label = item["pref_label"]["de"] || item["pref_label"]["en"];
            return (
              label &&
              e.trim() &&
              label?.toLowerCase().startsWith(e.trim().toLowerCase())
            );
          }),
        };
        result.uri = cachelist.value.filter((item) => {
          const uri = item["resource"];
          return uri;
        });
        selectList.value = result.label;
      } catch (error) {
        console.log(error);
      }
    }
  }
};

const saveToStore = () => {
  if (!validateAll()) {
    return;
  }

  // Filter out empty items before saving
  const validItems = localValues.value.filter((item) =>
    hasAnyFieldFilled(item)
  );

  if (validItems.length > 0) {
    formValues.value["Additionals"][props.item.name] = validItems;
  }

  if (isAutocomplete.value) {
    formValues.value["Additionals"][props.item.name] = inputValue.value;
  }

  emit("sst", "close");
};
</script>

<style>
.dpiV3_actionButtonWrap {
  display: flex;
  align-items: center;
  gap: var(--Spacing-3, 16px);
}

.dpiV3_interactionWrap {
  margin-top: var(--Spacing-8, 64px);
  display: flex;
  justify-content: end;
  align-items: center;
  align-self: flex-end;
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

activeItem {
  .dpiV3_InputFieldBase {
    margin: var(--Spacing-5, 32px) 0 0 0;
  }

  itemWrapperAdditionals {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    align-self: stretch;
  }
}

.repeatableOuter {
  margin: var(--Spacing-5, 32px) 0 0 0;
  .dpiV3_InputFieldBase {
    margin: 0;
  }
}

.rightAlign {
  align-self: flex-end !important;
}
</style>
