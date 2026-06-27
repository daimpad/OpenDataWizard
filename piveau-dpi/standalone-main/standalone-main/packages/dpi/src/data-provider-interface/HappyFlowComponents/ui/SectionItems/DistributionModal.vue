<template>
  <div class="dpiV3_innerRapModalWrapper">
    <innerRapModalItem>
      <!-- <Dropdown
        @update:modelValue="
          handleChangeLicenseVal($event, 'dcterms:license', 1)
        "
        dropdownWidth="large"
        @input="validateLicenseInput"
        type="inputField"
        v-model="changeLicenseDropdownVal"
        :inputFieldProps="{
          addOnText: false,
          initialHintText: false,
          datePicker: false,
          infoIcon: false,
          preIcon: false,
          label: 'Lizenz',
          dropdown_dpiV3: true,
          placeholder:
             formValues['DistributionSimple']?.['dct:license']?.[0]?.[
              'dcterms:license'
            ] || formValues['DistributionSimple']?.['dcat:distribution']?.[0]?.[
              'dct:license'
            ]?.[0]?.['dcterms:license'] ||
            'Lizenz wählen...',
          inputFieldSize: 'large',
          defaultInput: true,
          showError: false,
        }"
        :data="licenseOptions"
        @deleteButtonClicked="deleteModifiedField"
        class="gap5BottomAsMargin"
      />
      <InputField
      v-if="showNameInput"
        :label="
          $t(
            'message.dataupload.datasets.dcat:distribution.advanced.dcatde:licenseAttributionByText'
          )
        "
        :infoIcon="false"
        :preIcon="false"
        :initialHintText="true"
        supporting-hint-message="Dieser Namensnennungstext stellt sicher, dass die Lizenzbedingungen eingehalten werden."
        :showEndIcon="false"
        :placeholder="
        formValues['DistributionSimple']?.['dcat:distribution']?.[0]?.['dcatde:licenseAttributionByText']  || formValues['DistributionSimple']?.['dct:license']?.[0]?.['attribution'] || 'Nennung des Datenbereitstellers'
        "
        v-model="arr[0]['attribution']"
      /> -->
    </innerRapModalItem>
    <innerRapModalItem>
      <DistributionSimplePage
        :context="context"
        :inRap="true"
      ></DistributionSimplePage>
    </innerRapModalItem>
  </div>
</template>
<script setup>
import { filterGeocodingURIs } from "../../services/dpiV3_apis";
import Dropdown from "../Dropdown.vue";
import DistributionSimplePage from "../../../components/DistributionSimplePage.vue";
import { ref, onMounted, getCurrentInstance, watch, computed } from "vue";
import { getNode } from "@formkit/core";
import InputField from "../InputField.vue";
import { useFormValues } from "../../../composables/useDpiFormValues";
import { getLicenses } from "../../../HappyFlowComponents/services/dpiV3_apis";

let licenseTitle = ref();
let changeLicenseDropdownVal = ref("");
let URIList = ref([]);
let instance = getCurrentInstance().appContext.app.config.globalProperties.$env;
let licenseOptions = ref([]);
const { formValues } = useFormValues();
const licenseErrorMessage = ref("");
let individualLicense = ref(false)

let validInputs = ref({ 1: false, 2: false });
const arr = ref([
  { isValid: "unset", "dcterms:license": "", title: "", uri: "" },
]);
const handleChangeLicenseVal = (e, namespace, iIndex) => {
  validateLicense(e, namespace, iIndex,'',true);
};
let showNameInput = ref(false);

const validateLicenseInput = (event) => {
  // Ensure we're getting the actual string value
  const inputValue =
    typeof event === "object" && event.target ? event.target.value : event;
  validateLicense(inputValue, "dcterms:license", 1);
};
const handleChangeLicenseTitle = (e, namespace, iIndex) => {

  if (e.target.value != "") {
    validInputs.value[iIndex] = true;
    if (validInputs.value[1] && validInputs.value[2]) {
      arr.value[0].isValid = true;
    }
  } else {
    validInputs.value[iIndex] = false;
    if (!validInputs.value[1] || !validInputs.value[2]) {
      arr.value[0].isValid = false;
    }
  }

  const newValue = e.target.value;
  // Aktualisiere nur den Wert des entsprechenden Namensraums
  arr.value = arr.value.map((item) => {
    if (namespace in item) {
      return { ...item, [namespace]: newValue }; // Aktualisiere nur den spezifischen Wert
    }
    return item; // Behalte andere Objekte unverändert
  });
  // !!!!!!CAUTION!!!!! THIS IS SETTING THE LICENSE TO EVERY DISTRIBUTION AUTOMATICALLY AND NEEDS TO BE ADJUSTED
  // for (
  //   let index = 0;
  //   index < formValues.value["DistributionSimple"]["dcat:distribution"].length;
  //   index++
  // ) {
  //   formValues.value["DistributionSimple"]["dcat:distribution"][index][
  //     "dct:license"
  //   ] = arr.value;
  // }
  formValues.value["DistributionSimple"]["dct:license"] = arr.value;
};
const validateLicense = (value, namespace, iIndex, uri, trigger) => {
 
  // Check if the license exists in licenseOptions
  const isValidLicense =
    value === "" ||
    licenseOptions.value.some((option) => option["@value"] === value);
  let title = ref();
  if (licenseOptions.value.some((option) => option["@value"] === value)) {
    title.value = licenseOptions.value.find(
      (option) => option["@value"] === value
    ).label;
  }

  if (!isValidLicense && value !== "") {
    licenseErrorMessage.value =
      "Bitte wählen Sie eine gültige Lizenz aus der Liste";
    validInputs.value[iIndex] = false;
    arr.value[0].isValid = false;
  } else {
    licenseErrorMessage.value = "";
     if (trigger) {
    // Only show name input for CC BY or DL-BY-DE licenses
    const requiresAttribution =
      value !== "" && (value.includes("cc-by") || value.includes("dl-by-de"));
    showNameInput.value = requiresAttribution;
  } else {
    arr[0]['attribution'] = ''
    showNameInput.value = false
  } 
    if (value !== "") {
      validInputs.value[iIndex] = true;
      if (value !== "" && validInputs.value[2]) {
        arr.value[0].isValid = true;
      }
    } else {
      validInputs.value[iIndex] = false;
      arr.value[0].isValid = false;
    }

    // Update the model
    arr.value = arr.value.map((item) => {
      if (namespace in item) {
        return { ...item, [namespace]: value, uri: uri, title: title };
      }      
      return item;
    });
  
  
  }
 
  formValues.value["DistributionSimple"]["dct:license"] = arr.value;
  // props.context.node.input(arr.value);
};

watch(formValues.value["DistributionSimple"].length, (newVal)=>{
  if (formValues.value["DistributionSimple"].length > 1) {
    console.log('halo');
  }
 console.log('halo Outer');

});
// watch(changeLicenseDropdownVal, (newValue) => {
//   // console.log("Dropdown value changed:", newValue);
//   // This will trigger whenever the dropdown value changes

//   console.log("##############");

//   let uri = licenseOptions.value.find(
//     (item) => item["@value"] === newValue
//   ).uri;

//   if (newValue !== undefined) {
//     validateLicense(newValue, "dcterms:license", 1, uri);
//   }
// });
const props = defineProps({
  context: Object,
  newValues: Object,
});
onMounted(async () => {
  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getLicenses(env.api.baseUrl);
    licenseOptions.value = response.map((item) => ({
      "@value": item.value,
      label: item.label,
      uri: item.uri,
    }));
    licenseOptions.value.sort((a, b) => a["@value"].localeCompare(b["@value"]));
  } catch (error) {
    console.error("Failed to load licenses", error);
  }
});

const isValid = computed(() => {
  const distData = formValues.value?.['DistributionSimple']?.['dcat:distribution'];

  if (!distData || !Array.isArray(distData) || distData.length === 0) {
    return false;
  }

  return distData.every(dist => dist.isValid === true);
});

const getValidationErrorMessages = () => {
  const distData = formValues.value?.['DistributionSimple']?.['dcat:distribution'];
  const errors = [];

  if (!distData || !Array.isArray(distData) || distData.length === 0) {
    errors.push("Bitte fügen Sie mindestens eine Distribution hinzu.");
  } else {
    // Check specific fields if distributions exist
    const hasMissingUrl = distData.some(dist => {
        const url = dist['dcat:accessURL']?.[0]?.['@value'];
        return !url || url.length === 0;
    });

    if (hasMissingUrl) {
      errors.push("Alle Distributionen müssen eine Zugriffs-URL (Access URL) haben.");
    }
  }

  return errors;
};

defineExpose({
  isValid,
  getValidationErrorMessages
});
</script>
<style scoped></style>
