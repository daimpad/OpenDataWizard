<template>
  <div
    :class="{
      repeatableOuter: !props.inOverview,
      dpiV3AutoCompleteWrap: !props.inOverview,
    }"
    v-for="(item, index) in listofitems"
    class="langDropdown" :key=index
  >
 
    <Dropdown
      @update:modelValue="handleInput($event, index)"
      dropdownWidth="large"
      type="inputField"
      v-model="listofitems[index]['label']"
      :inputFieldProps="{
        addOnText: false,
        initialHintText: false,
        datePicker: false,
        infoIcon: false,
        preIcon: false,
        label:
          t(
            'message.dataupload.datasets.dcat:distribution.advanced.dct:language'
          ) + ' (optional)',
        dropdown_dpiV3: true,
        placeholder: t(
          'message.dataupload.datasets.dcat:distribution.advanced.language-placeholder'
        ),
        inputFieldSize: 'large',
        modelValue: languageTexVal[index],
        defaultInput: !showDeleteButton,
      }"
      :data="filteredLanguageOptions"
      @deleteDropdownField="deleteLanguageField"
      @input="handleQuery"
      
    ></Dropdown>
    <TextButtonSmall
      v-if="!props.inOverview"
      button-text="Löschen"
      class="rightAlign"
      iconStart="trash"
      @click="listofitems.splice(index,1)"
    />
  </div>

  <div class="dpiV3_tempAddMore" v-if="!props.inOverview">
    <ButtonV3
      buttonText="Weitere hinzufügen"
      size="large"
      iconStart="plus"
      variant="tertiary"
      class="mt-3"
      @click="createOptional()"
    />
  </div>
</template>
<script setup>
import { getLanguages } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import ButtonV3 from "../ButtonV3.vue";
import TextButtonSmall from "../TextButtonSmall.vue";
import {
  onMounted,
  getCurrentInstance,
  ref,
  defineProps,
  defineEmits,
  watch,
  computed,
} from "vue";
import { useI18n } from "vue-i18n";
import { useFormValues } from "../../../composables/useDpiFormValues";

const props = defineProps({
  distributionId: { type: Number, required: false },
  showDeleteButton: { type: Boolean, default: false },
  languageText: { type: String, default: "" },
  inOverview: { type: Boolean, default: false },
});

let listofitems = ref([{ label: "", "@value": "", uri: "" }]);
let distribution = {};

const { formValues } = useFormValues();
const { t } = useI18n();
const createOptional = () => {
  listofitems.value.push({});
};
const instance = getCurrentInstance();
const env = instance.appContext.app.config.globalProperties.$env;
const userLocale = instance.appContext.app.config.globalProperties.$i18n.locale;
let languageTexVal = ref([]);

// Check for present Values
distribution =
  formValues.value.DistributionSimple["dcat:distribution"].find(
    (item) => item.id === props.distributionId
  ) || {};

onMounted(async () => {
  if (distribution["dct:language"].length > 0) {     
    listofitems.value = distribution["dct:language"];
  }
  languageTexVal.value = distribution["dct:language"]?.map((item) => item.label);

  try {
    const response = await getLanguages(env.api.baseUrl, userLocale);

    languageOptions.value = response.map((item) => ({
      "@value": item.label,
      label: item.label,
      uri: item.uri,
    }));
  } catch (error) {
    console.error("Failed to load language data", error);
  }
});


const languageOptions = ref([]);
let titelListe = ref(languageTexVal);
console.log("Titelliste", titelListe);

//  if (languageTexVal.value === '' && distribution['dct:language'][0] !== undefined) {
//     languageTexVal = distribution['dct:language'][0].label
//   }

const emits = defineEmits(["addLanguage", "deleteButtonClicked"]);

const handleInput = (event, i) => {
  if (listofitems.value.length > 1) {
    listofitems.value[i] = {
      '@value': event,
      label: event,
      uri:
        languageOptions.value.find((item) => item["@value"] === event).uri ||
        "",
    };
 console.log(distribution["dct:language"] , listofitems.value);
 
   distribution["dct:language"] = listofitems.value;
   console.log(distribution["dct:language"] , listofitems.value);
  //  debugger
  } else {
    
    listofitems.value[0] = {
      "@value": event,
      uri:
        languageOptions.value.find((item) => item["@value"] === event).uri ||
        "",
    };
    distribution["dct:language"][0] = listofitems.value[0];
    let uri =
      languageOptions.value.find((item) => item["@value"] === event).uri || "";
    // emits("addLanguage", event, props.distributionId, uri);
  }
};

const query = ref("");
const handleQuery = (event) => {
  query.value = event;
  console.log(query);
  
};

const filteredLanguageOptions = computed(() => {
  return languageOptions.value.filter((item) =>
    item["@value"]
      ?.toLowerCase()
      ?.trim()
      .startsWith(query.value?.toLowerCase()?.trim())
  );
});
watch(
  () => distribution["dct:language"],
  (newVal)=>{
    listofitems.value = distribution["dct:language"]
    
  },{ deep: true }
)
watch(
  () => props.languageText,
  (newVal) => {  
    languageTexVal.value = newVal;
  }
);

const deleteLanguageField = () => {
  console.log("delete clicked");
  emits("deleteButtonClicked", props.distributionId);
};
</script>
<style>

/* ignores last Item */
.langDropdown:has(~ .langDropdown){
  display: flex;
    gap: var(--Spacing-3, 16px);
    margin-bottom: var(--Spacing-3, 16px);
}
</style>
