<template>
  <div class="dpiV3_innerRapModalWrapper">
    <innerRapModalItem>
      <Dropdown
        dropdownWidth="large"
        type="inputField"
        @input="filterVocabularies($event)"
        :inputFieldProps="{
          addOnText: false,
          initialHintText: false,
          datePicker: false,
          infoIcon: false,
          preIcon: true,
          showEndIcon: false,
          label: 'Geopolitische Abdeckung (optional)',
          dropdown_dpiV3: true,
          placeholder: 'Geben Sie die geopolitische Abdeckung ein..',
          inputFieldSize: 'large',
          autocomplete: 'true',
        }"
        @valueSent="handleValue"
        :data="URIList"
        multi="true"
        :autocomplete="true"
        :modelValue="
          formValues.Covering?.['dcatde:politicalGeocodingURI']?.[0].label
        "
      />
    </innerRapModalItem>
    <innerRapModalItem>
      <TempResPageInModal
        ref="tempResPageRef"
        :context="context"
        :inRap="true"
        class="dpiV3_tempresCard"
      ></TempResPageInModal>
    </innerRapModalItem>
  </div>
</template>
<script setup>
import { filterGeocodingURIs } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import TempResPageInModal from "../../../components/TempResPageInModal.vue";
import { ref } from "vue";
import { getCurrentInstance } from "vue";
import { getNode } from "@formkit/core";
import { useFormValues } from "../../../composables/useDpiFormValues";

const tempResPageRef = ref(null);

let URIList = ref([]);
let instance = getCurrentInstance().appContext.app.config.globalProperties.$env;
let debounceTimer;
const { formValues } = useFormValues();

const props = defineProps({
  context: Object,
  newValues: Object,
});

// Init Tempres if not there
if (
  formValues.value["Covering"]?.["dcat:temporalResolution"]?.[
    "dct:temporal"
  ] === undefined
) {
  formValues.value["Covering"]["dcat:temporalResolution"] = {
    isValid: true,
    type: "dct:PeriodOfTime",
    "dct:temporal": [
      {
        dataType: "date",
        "dcat:startDate": "",
        "dcat:endDate": "",
      },
    ],
  };
}

let coverageObject = ref();

let emptyValues = ref({
  "dcatde:politicalGeocodingURI": [
    {
      isValid: true,
      uri: "",
      id: "",
      label: "",
      inVoc: "",
    },
  ],
  "dcat:temporalResolution": [
    {
      isValid: true,
      type: "dct:PeriodOfTime",
      "dct:temporal": [
        {
          dataType: "date",
          "dcat:startDate": "",
          "dcat:endDate": "",
        },
      ],
    },
  ],
});
// Check if entries are present
// formValues.value["Covering"] = emptyValues;

console.log(formValues);

// props.context.node.input(chosenItems)
// let chosenItems = ref(getNode("BasicInfos").value);

const newObj = {};

// Über die Werte des Objekts iterieren
// Object.values(chosenItems.value).forEach((item, index) => {
//   const { isValid, ...rest } = item; // isValid ignorieren
//   newObj[`obj${index + 1}`] = rest; // Neues Objekt ohne isValid hinzufügen
// });

console.log(newObj);
const handleValue = (item, header) => {
  formValues.value["Covering"][Object.keys(formValues.value["Covering"])[0]] = [
    {
      isValid: true,
      uri: item.resource,
      id: item.id,
      label: item.alt_label["de"],
      inVoc: header,
    },
  ];
  // set Geocoding Link as Spatial
  // if (formValues.value.Additionals["dct:spatial"] === undefined) {
  //   formValues.value.Additionals["dct:spatial"] = [];
  //   formValues.value.Additionals["dct:spatial"].push({ "@id": item.resource });
  // } else
  //   formValues.value.Additionals["dct:spatial"].push({ "@id": item.resource });
};

const filterVocabularies = async (e) => {
  clearTimeout(debounceTimer); // Lösche den vorherigen Timer

  // Setze einen neuen Timer
  debounceTimer = setTimeout(async () => {
    if (e !== "") {
      try {
        console.log(e);
        URIList.value = await filterGeocodingURIs(e, instance.api.baseUrl);
      } catch (error) {
        console.log(error);
      }
    }
  }, 500); // Warte 500 ms nach der letzten Eingabe
};
const validateAllItems = () => {
  if (tempResPageRef.value && tempResPageRef.value.validateAllItems) {
    return tempResPageRef.value.validateAllItems();
  }
  return false; // no errors if component not available
};
defineExpose({
  validateAllItems
});
</script>
<style scoped></style>
