<template>
  <div v-if="showTopModal" class="dpiV3_modalBackground">
    <div
      class="dpiV3_modalWrap"
      :class="{
        dpiV3_optionalInfo:
          props.optionalInfoView || selectedItem.key === 'foaf:page',
      }"
    >
      <div
        class="dpiV3_modalMainWrap"
        :class="{ dpiV3_modalLine: props.optionalInfoView }"
      >
        <div class="dpiV3_modalHeaderWrap">
          <h2 :class="{ dpiV3_optionalInfoHeader: props.optionalInfoView }">
            {{ props.headerText }}
          </h2>
          <CrossOutButton class="dpiv3_modalCrossout" @click="closeModal" />
        </div>
        <span class="dpiV3_modalText">{{ props.text }}</span>
        <div
          v-if="
            !props.optionalInfoView &&
            (props.title || props.distributionLink || props.format)
          "
          class="dpiV3AutoCompleteWrapDeleteModal copy-large-semi-bold"
        >
          <div class="dpiV3_LinkInfos">
            <a v-if="props.title" :href="props.distributionLink" target="_blank"
              ><div class="dpiV3_title">{{ props.title }}</div></a
            >
            <a
              v-if="props.distributionLink && !props.title"
              :href="props.distributionLink"
              target="_blank"
              ><div class="dpiV3_title">{{ props.distributionLink }}</div>
            </a>
            <div
              v-if="props.format"
              class="dpiV3_File-Format copy-small-regular"
            >
              {{ props.format }}
            </div>
          </div>
        </div>
      </div>
      <div v-if="props.showButtons" class="dpiV3_modalButtonWrap">
        <TextButtonSmall
          :buttonText="
            $t('message.dataupload.datasets.dcat:distribution.modal.cancel')
          "
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
      <!--------------------------------------------------------------->
      <!---------------- Optional Information Modal ------------------->
      <!--------------------------------------------------------------->
      <div class="dpiV3_optionalInfoContent" v-if="props.optionalInfoView">
        <div class="dpiV3_ListMetaData">
          <!-- sections -->
          <template v-for="(section, index) in sections" :key="index">
            <!-- Title Section -->
            <div class="dpiV3_title-description">
              <div class="dpiV3_group-title">
                <div>{{ section.title }}</div>
                <div class="dpiV3_caret">
                  <CloseOpenButtonV3
                    @click="toggleSection(index)"
                    :expanded="!expandedSections[index]"
                  />
                </div>
              </div>
            </div>
            <!-- list items -->
            <template v-if="expandedSections[index]">
              <template
                v-for="(item, itemIndex) in section.items"
                :key="itemIndex"
              >
                <div class="dpiV3_itemDivider"></div>
                <div
                  class="dpiV3_itemOptional"
                  @click="openSecondModal(index, itemIndex)"
                >
                  {{ item }}
                </div>
              </template>
            </template>
            <div
              v-if="index < sections.length - 1"
              class="dpiV3_sectionDivider"
            ></div>
          </template>
        </div>
      </div>
    </div>
  </div>

  <!-------------------------------------------------------------------------------->
  <!---------------------- Additional Modal (Dynamic) ------------------------------>
  <!-------------------------------------------------------------------------------->
  <div v-if="selectedItem.key" class="dpiV3_modalBackground">
    <div
      class="dpiV3_modalWrap"
      :class="{
        dpiV3_removeOverflow:
          selectedItem.key === 'dcatap:availability' ||
          selectedItem.key === 'dcat:mediaType' ||
          selectedItem.key === 'dcat:compressFormat' ||
          selectedItem.key === 'dcat:packageFormat' ||
          selectedItem.key === 'spdx:checksum' ||
          selectedItem.key === 'adms:status',
      }"
    >
      <div class="dpiV3_modalHeaderWrap">
        <h2>
          {{ selectedItem.text }}
          {{
            $t("message.dataupload.datasets.dcat:distribution.modal.add_small")
          }}
        </h2>
        <CrossOutButton class="dpiv3_modalCrossout" @click="closeSecondModal" />
      </div>

      <p
        class="dpiV3_modalContent"
        v-if="selectedItem.key === 'dcatap:availability'"
      >
        {{
          $t(
            "message.dataupload.datasets.dcat:distribution.modal.availability_paragraph",
          )
        }}
      </p>
      <p class="dpiV3_modalContent" v-if="selectedItem.key === 'dcat:byteSize'">
        {{
          $t(
            "message.dataupload.datasets.dcat:distribution.modal.byte-size-paragraph",
          )
        }}
      </p>
      <p class="dpiV3_modalContent" v-if="selectedItem.key === 'foaf:page'">
        {{
          $t(
            "message.dataupload.datasets.dcat:distribution.modal.documentation-paragraph",
          )
        }}
      </p>

      <!---------------------------------------------------------------------------->
      <!-------------------- dct:modified, Aktualisierungsdatum -------------------->
      <div class="dpiV3_modified" v-if="selectedItem.key === 'dct:modified'">
        <ModifiedDateV3
          :modelValue="modifiedDate"
          @addModifiedDate="handleModifiedDate"
        />
      </div>
      <!---------------------------------------------------------------------------->
      <!------------- Veröffentlichungsdatum, dct:issued ---------------->
      <div class="dpiV3_modified" v-if="selectedItem.key === 'dct:issued'">
        <IssuedDateV3
          :modelValue="issuedDate"
          :labelText="selectedItem.text + ' (optional)'"
          @addIssuedDate="handleIssuedDate"
        />
      </div>
      <!----------------------------------------------------------------->
      <!-------------------- dct:description, Description -------------------->
      <div class="dpiV3_modified" v-if="selectedItem.key === 'dct:description'">
        <DescriptionV3
          @addDescription="addDescription"
          :descriptionText="descriptionText"
        />
      </div>
      <!----------------------------------------------------------------->
      <!------------------------------------------------------------------------------->
      <!--------------- Namensnennungstext, dcatde:licenseAttributionByText --------------------->
      <!-- <div
        class="dpiV3_modified"
        v-if="selectedItem.key === 'dcatde:licenseAttributionByText'"
      >
        <LicenseAttributionByText
          @addLicenseAttrByText="handleLicenseAttrByText"
          :nameTextByClauses="nameTextByClauses"
        />
      </div> -->

      <!------------------------------------------------------------------------------->
      <!--------------- Grad der Zugänglichkeit, dct:accessRights --------------------->
      <div
        class="dpiV3_modified"
        v-if="selectedItem.key === 'dct:accessRights'"
      >
        <AccessRightsV3
          @addAccessRights="handleAccessRights"
          :accessRightsProp="accessRightsText"
        />
      </div>
      <!----------------------------------------------------------------->
      <!--------- Dokumentation (multi inputfields) key: foaf:page ------>
      <div
        class="dpiV3_DocumentationV3_Wrap"
        v-if="selectedItem.key === 'foaf:page'"
      >
        <DocumentationsV3
          ref="documentationsV3Ref"
          :documentations="
            documentationsByDistribution[props.distributionId] || []
          "
          :fileTypes="formatTypes"
          @update="
            (updatedDocs) =>
              updateDocumentations(props.distributionId, updatedDocs)
          "
          :distributionId="props.distributionId"
        />
      </div>

      <!----------------------------------------------------------------->
      <!--------- Access service (multi inputfields) key: dcat:accessService ------>
      <div
        class="dpiV3_DocumentationV3_Wrap"
        v-if="selectedItem.key === 'dcat:accessService'"
      >
        <AccessServiceV3
          ref="accessServiceV3Ref"
          :accessServices="
            accessServicesByDistribution[props.distributionId] || []
          "
          @update="
            (updatedAccessServices) =>
              updateAccessServices(props.distributionId, updatedAccessServices)
          "
          :distributionId="props.distributionId"
        />
      </div>

      <!----------------------------------------------------------------->
      <!----------------------- Size in Bytes --------------------------->
      <ByteSizeV3
        v-if="selectedItem.key === 'dcat:byteSize'"
        :byteSizeProp="byteSizeText"
        @addByteSize="handleByteSize"
      />
      <!------------------- Availability, Verfügbarkeit ------------------>
      <AvailabilityV3
        v-if="selectedItem.key === 'dcatap:availability'"
        :availabilityText="availabilityText"
        @addAvailability="handleAvailability"
      />

      <!------------------- Language, dct:language ------------------>
      <LanguageV3
        v-if="selectedItem.key === 'dct:language'"
        :languageText="languageText"
        :distributionId="distributionId"
        @addLanguage="handleLanguage"
      />

      <!---------------------------------------------------------------------------->
      <!--------- Konform zu Standard (multi inputfields) key: dct:conformsTo ------>
      <div
        class="dpiV3_DocumentationV3_Wrap"
        v-if="selectedItem.key === 'dct:conformsTo'"
      >
        <ConformsToV3
          ref="conformsToV3Ref"
          :conformsToItems="
            conformsToItemsByDistribution[props.distributionId] || []
          "
          @update="
            (updatedConformsTo) =>
              updateConformsTo(props.distributionId, updatedConformsTo)
          "
          :asCard="false"
          :distributionId="props.distributionId"
        />
      </div>

      <!---------------------------------------------------------------------------->
      <!--------- Regelwerk (multi inputfields) key: odrl:hasPolicy ------>
      <div
        class="dpiV3_DocumentationV3_Wrap"
        v-if="selectedItem.key === 'odrl:hasPolicy'"
      >
        <PolicyV3
          ref="policyV3Ref"
          :policyItems="policyItemsByDistribution[props.distributionId] || []"
          @update="
            (updatedPolicyItems) =>
              updatePolicyItems(props.distributionId, updatedPolicyItems)
          "
          :asCard="false"
          :distributionId="props.distributionId"
        />
      </div>
      <!---------------------------------------------------------------------------->
      <!-------------- File Formats, package format , compress....------------------>
      <FileFormatV3
        v-if="selectedItem.key === 'dcat:mediaType'"
        @addMediaType="handleFileFormat"
        :fileTypes="fileTypes"
        fileFormatType="dcat:mediaType"
        :fileFormatText="mediaTypeText"
      />
      <FileFormatV3
        v-if="selectedItem.key === 'dcat:compressFormat'"
        @addCompressFormat="handleFileFormat"
        :fileTypes="fileTypes"
        fileFormatType="dcat:compressFormat"
        :compressFormatText="compressFormatText"
      />
      <FileFormatV3
        v-if="selectedItem.key === 'dcat:packageFormat'"
        @addPackageFormat="handleFileFormat"
        :fileTypes="fileTypes"
        fileFormatType="dcat:packageFormat"
        :packageFormatText="packageFormatText"
      />

      <!------------------- Status, adms:status ------------------>
      <StatusV3
        v-if="selectedItem.key === 'adms:status'"
        :statusText="statusText"
        @addStatus="handleStatus"
      />
      <!------------------- Prüfsumme, spdx:checksum ------------------>
      <ChecksumV3
        ref="checksumV3Ref"
        v-if="selectedItem.key === 'spdx:checksum'"
        @addChecksum="handleChecksum"
        :checksumText="checksumText"
        :checksumDropdownText="checksumDropdownText"
        :asCard="false"
      />

      <!------------------- Change License, dcterms:license ------------------>
      <ChangeLicenseV3
        ref="changeLicenseV3Ref"
        v-if="selectedItem.key === 'dcterms:license'"
        @addChangeLicense="handleChangeLicense"
        :changeLicenseText="changeLicenseText"
        :changeLicenseDropdownText="changeLicenseDropdownText"
      />

      <!--------------- Cancel and Add Buttons ---------------->
      <div class="dpiV3_modalButtonWrap">
        <ButtonV3
          @click="closeSecondModal"
          buttonText="Zurück"
          size="large"
          iconStart="CaretLeft"
          variant="tertiary"
        />
        <ButtonV3
          @click="addToDistribution"
          buttonText="Hinzufügen"
          size="large"
        />
      </div>
    </div>
  </div>

  <!--------------------------------------------------------------->
  <!------------------ Delete Confirmation Modal ------------------>
  <!--------------------------------------------------------------->
  <div v-if="confirmDeleteModal" class="dpiV3_modalBackground">
    <div class="dpiV3_modalWrap dpiV3_confirmationDialog">
      <div class="dpiV3_modalHeaderWrap">
        <h2>Dokumentation löschen</h2>
        <CrossOutButton class="dpiv3_modalCrossout" @click="cancelDelete" />
      </div>
      <div class="dpiV3_modalContent">
        <p>Möchten Sie diese Dokumentation wirklich löschen?</p>
      </div>
      <div class="dpiV3AutoCompleteWrap copy-large-semi-bold">
        <div class="dpiV3_LinkInfos">
          <div class="dpiV3_title" v-if="documentationTitleToDelete">
            {{ documentationTitleToDelete }}
          </div>
          <div
            class="dpiV3_title"
            v-if="documentationUrlToDelete && documentationTitleToDelete === ''"
          >
            {{ documentationUrlToDelete }}
          </div>
          <div
            v-if="
              documentationUrlToDelete === '' &&
              documentationTitleToDelete === ''
            "
            class="dpiV3_title"
          >
            Es wurde keine URL angegeben!
          </div>
          <!--div v-if="props.format" class="dpiV3_File-Format copy-small-regular">
            {{ props.format }}
          </div-->
        </div>
      </div>

      <div class="dpiV3_modalButtonWrap">
        <ButtonV3
          @click="cancelDelete"
          buttonText="Abbrechen"
          size="large"
          variant="tertiary"
        />
        <ButtonV3
          @click="confirmDeleteAction"
          buttonText="Löschen"
          size="large"
        />
      </div>
    </div>
  </div>
</template>
<script setup>
import {
  ref,
  defineProps,
  defineEmits,
  watch,
  onMounted,
  getCurrentInstance,
} from "vue";
import TextButtonSmall from "./TextButtonSmall.vue";
import ButtonV3 from "./ButtonV3.vue";
import CrossOutButton from "./CrossOutButton.vue";
import InputField from "./InputField.vue";
import Dropdown from "./Dropdown.vue";
import TextAreaV3 from "./TextAreaV3.vue";
import CloseOpenButtonV3 from "./CloseOpenButtonV3.vue";
import { getPlannedAvailability, getLanguages } from "../services/dpiV3_apis";
import DocumentationsV3 from "./OptionalInformation/DocumentationsV3.vue";
import ModifiedDateV3 from "./OptionalInformation/ModifiedDateV3.vue";
import DescriptionV3 from "./OptionalInformation/DescriptionV3.vue";
// import LicenseAttributionByText from "./OptionalInformation/LicenseAttributionByText.vue";
import AvailabilityV3 from "./OptionalInformation/AvailabilityV3.vue";
import ByteSizeV3 from "./OptionalInformation/ByteSizeV3.vue";
import IssuedDateV3 from "./OptionalInformation/IssuedDateV3.vue";
import LanguageV3 from "./OptionalInformation/LanguageV3.vue";
import AccessRightsV3 from "./OptionalInformation/AccessRightsV3.vue";
import FileFormatV3 from "./OptionalInformation/FileFormatV3.vue";
import StatusV3 from "./OptionalInformation/StatusV3.vue";
import ChecksumV3 from "./OptionalInformation/ChecksumV3.vue";
import ConformsToV3 from "./OptionalInformation/ConformsToV3.vue";
import PolicyV3 from "./OptionalInformation/PolicyV3.vue";
import AccessServiceV3 from "./OptionalInformation/AccessServiceV3.vue";
import ChangeLicenseV3 from "./OptionalInformation/ChangeLicenseV3.vue";

const props = defineProps({
  headerText: { type: String, required: true },
  text: { type: String, required: true },
  title: { type: String, required: false },
  format: { type: String, required: false },
  distributionLink: { type: String, required: false },
  buttons: { type: Object, required: true },
  showButtons: { type: Boolean, default: true },
  optionalInfoView: { type: Boolean, default: false },
  sections: { type: Array, required: false },
  action: String,
  fileTypes: { type: Array, required: false },
  context: Object,
  formatTypes: { type: Array, required: false },
  distributionId: { type: Number, required: false },
  checksumURI: { type: String, required: false },
  documentations: { type: Array, required: true },
  accessServices: { type: Array, required: true },
  modifiedDate: { type: String, required: false },
  issuedDate: { type: String, required: false },
  descriptionText: { type: String, required: false },
  nameTextByClauses: { type: String, required: false },
  accessRightsText: { type: String, required: false },
  availabilityText: { type: String, required: false },
  statusText: { type: String, required: false },
  checksumText: { type: String, required: false },
  checksumDropdownText: { type: String, required: false },
  changeLicenseText: { type: String, required: false },
  changeLicenseDropdownText: { type: String, required: false },
  languageText: { type: String, required: false },
  byteSizeText: { type: String, required: false },

  mediaTypeText: { type: String, required: false },
  compressFormatText: { type: String, required: false },
  packageFormatText: { type: String, required: false },
  conformsToItems: { type: Array, required: false },
  policyItems: { type: Array, required: false },
});

const conformsToV3Ref = ref(null);
const documentationsV3Ref = ref(null);
const accessServiceV3Ref = ref(null);
const checksumV3Ref = ref(null);
const changeLicenseV3Ref = ref(null);
const policyV3Ref = ref(null);

const emit = defineEmits([
  "close",
  "actionHandling",
  "addDocumentations",
  "addAccessServices",
  "addConformsToItems",
  "addPolicyItems",
  "addModifiedDate",
  "addIssuedDate",
  "addDescription",
  "addLicenseAttrByText",
  "addAccessRights",
  "addAvailability",
  "addLanguage",
  "addByteSize",
  "addMediaType",
  "addCompressFormat",
  "addPackageFormat",
  "addStatus",
  "addChecksum",
  "addChangeLicense",
]);

const wasDocumentationsUpdated = ref(false);
const wasAccessServicesUpdated = ref(false);
const wasConformsToItemsUpdated = ref(false);
const wasPolicyItemsUpdated = ref(false);
const wasModifiedDateUpdated = ref(false);
const wasIssuedDateUpdated = ref(false);
const wasDescriptionUpdated = ref(false);
const wasLicenseAttrUpdated = ref(false);
const wasAccessRightsUpdated = ref(false);
const wasAvailabilityUpdated = ref(false);
const wasLanguageUpdated = ref(false);
const wasByteSizeUpdated = ref(false);
const wasMediaTypeUpdated = ref(false);
const wasCompressFormatUpdated = ref(false);
const wasPackageFormatUpdated = ref(false);
const wasStatusUpdated = ref(false);
const wasChecksumUpdated = ref(false);
const wasChangeLicenseUpdated = ref(false);

const expandedSections = ref([]);
const selectedItem = ref({ key: "", text: "" });
const documentationDeleteId = ref(null);
const confirmDeleteModal = ref(false);
const documentationTitleToDelete = ref(null);
const documentationUrlToDelete = ref(null);
const filteredData = ref([...props.fileTypes]);
const plannedAvailabilityOptions = ref([]);
const languageOptions = ref([]);
const selectedAvailability = ref(null);
const minimumDocError = ref(false);
const modifiedDateVal = ref("");
const issuedDateVal = ref("");
const descriptionVal = ref("");
// const licenseAttributionByTextVal = ref("");
const accessRightsVal = ref("");
const documentationsByDistribution = ref({});
const accessServicesByDistribution = ref({});
const conformsToItemsByDistribution = ref({});
const policyItemsByDistribution = ref({});
const availabilityVal = ref("");
const statusVal = ref("");
const languageVal = ref("");
const byteSizeVal = ref(0);
const mediaTypeVal = ref("");
const compressFormatVal = ref("");
const packageFormatVal = ref("");
const checksumTitleVal = ref("");
const checksumDropdownVal = ref("");
const checksumInnerURI = ref("");
const changeLicenseTitleVal = ref("");
const changeLicenseDropdownVal = ref("");

const showTopModal = ref(true);
const showMiddleLayerModal = ref(true);

const openSecondModal = (sectionIndex, itemIndex) => {
  showTopModal.value = false;
  selectedItem.value = {
    key: props.sections[sectionIndex].keys[itemIndex],
    text: props.sections[sectionIndex].items[itemIndex],
  };

  if (!documentationsByDistribution.value[props.distributionId]) {
    documentationsByDistribution.value[props.distributionId] = [];
  }

  if (!accessServicesByDistribution.value[props.distributionId]) {
    accessServicesByDistribution.value[props.distributionId] = [];
  }

  if (!conformsToItemsByDistribution.value[props.distributionId]) {
    conformsToItemsByDistribution.value[props.distributionId] = [];
  }

  if (!policyItemsByDistribution.value[props.distributionId]) {
    policyItemsByDistribution.value[props.distributionId] = [];
  }
};

const closeSecondModal = () => {
  showTopModal.value = true;
  selectedItem.value = { key: "", text: "" };
};

const addToDistribution = () => {
  // Helper function to validate URL format (define once at the top)
  const isValidUrl = (urlString) => {
    if (!urlString || urlString.trim() === "") return false;
    const trimmedUrl = urlString.trim();
    // Check if the URL contains a dot followed by at least 2 characters (domain extension)
    const domainPattern = /\.[a-zA-Z]{2,}$/;
    return domainPattern.test(trimmedUrl);
  };

  // Validate ConformsTo items
  if (wasConformsToItemsUpdated.value) {
    const conformsToItems =
      conformsToItemsByDistribution.value[props.distributionId] || [];

    // Check if any item has validation errors
    const hasValidationError = conformsToItems.some((item) => {
      const hasTitle = item["dct:title"] && item["dct:title"].trim() !== "";
      const urlValue = item["dcat:downloadURL"]
        ? item["dcat:downloadURL"].trim()
        : "";

      // Title exists but URL is missing or invalid
      if (hasTitle && (!urlValue || !isValidUrl(urlValue))) {
        return true;
      }

      // URL exists but is invalid (regardless of title)
      if (urlValue && !isValidUrl(urlValue)) {
        return true;
      }

      return false;
    });

    if (hasValidationError) {
      // Trigger validation in the ConformsToV3 component to show errors
      if (conformsToV3Ref.value) {
        conformsToV3Ref.value.validateAllItems();
      }
      // Don't proceed - keep modal open
      return;
    }

    // Filter out items where both title and URL are empty
    const validConformsToItems = conformsToItems.filter((item) => {
      const hasTitle = item["dct:title"] && item["dct:title"].trim() !== "";
      const hasValidUrl =
        item["dcat:downloadURL"] &&
        item["dcat:downloadURL"].trim() !== "" &&
        isValidUrl(item["dcat:downloadURL"].trim());
      // Only include items that have at least one field filled with valid data
      return hasTitle || hasValidUrl;
    });

    // Only emit if there are valid items
    if (validConformsToItems.length > 0) {
      emit("addConformsToItems", props.distributionId, validConformsToItems);
    }
    wasConformsToItemsUpdated.value = false;
  }

  if (wasPolicyItemsUpdated.value) {
    const policyItems =
      policyItemsByDistribution.value[props.distributionId] || [];

    // Check if any item has validation errors
    const hasValidationError = policyItems.some((item) => {
      const urlValue = item["dcat:downloadURL"]
        ? item["dcat:downloadURL"].trim()
        : "";

      // URL exists but is invalid
      if (urlValue && !isValidUrl(urlValue)) {
        return true;
      }

      return false;
    });

    if (hasValidationError) {
      // Trigger validation in the PolicyV3 component to show errors
      if (policyV3Ref.value) {
        policyV3Ref.value.validateAllItems();
      }
      return; // Don't proceed - keep modal open
    }

    // Filter out items where URL is empty
    const validPolicyItems = policyItems.filter((item) => {
      const hasValidUrl =
        item["dcat:downloadURL"] &&
        item["dcat:downloadURL"].trim() !== "" &&
        isValidUrl(item["dcat:downloadURL"].trim());
      return hasValidUrl;
    });

    // Only emit if there are valid items
    if (validPolicyItems.length > 0) {
      emit("addPolicyItems", props.distributionId, validPolicyItems);
    }
    wasPolicyItemsUpdated.value = false;
  }

  // if (wasPolicyItemsUpdated.value) {
  //   emit(
  //     "addPolicyItems",
  //     props.distributionId,
  //     policyItemsByDistribution.value[props.distributionId] || []
  //   );
  //   wasPolicyItemsUpdated.value = false;
  // }

  // Validate Documentations
  if (wasDocumentationsUpdated.value) {
    const documentations =
      documentationsByDistribution.value[props.distributionId] || [];

    // Check if any other field in the documentation is filled
    const hasOtherFieldsFilled = (documentation) => {
      const hasTitle =
        documentation["dct:title"] && documentation["dct:title"].trim() !== "";
      const hasDescription =
        documentation["dct:description"] &&
        documentation["dct:description"].trim() !== "";
      const hasFormat =
        documentation["dct:format"] &&
        documentation["dct:format"].trim !== "" &&
        (typeof documentation["dct:format"] === "string"
          ? documentation["dct:format"].trim() !== ""
          : true);

      return hasTitle || hasDescription || hasFormat;
    };

    // Check if any item has validation errors
    const hasValidationError = documentations.some((documentation) => {
      const urlValue = documentation["dcat:accessURL"]
        ? documentation["dcat:accessURL"].trim()
        : "";
      const otherFieldsFilled = hasOtherFieldsFilled(documentation);

      // Any other field is filled but URL is missing or invalid
      if (otherFieldsFilled && (!urlValue || !isValidUrl(urlValue))) {
        return true;
      }

      // URL exists but is invalid (regardless of other fields)
      if (urlValue && !isValidUrl(urlValue)) {
        return true;
      }

      return false;
    });

    if (hasValidationError) {
      // Trigger validation in the DocumentationsV3 component to show errors
      if (documentationsV3Ref.value) {
        documentationsV3Ref.value.validateAllItems();
      }
      // Don't proceed - keep modal open
      return;
    }

    // Filter out items where all fields are empty
    const validDocumentations = documentations.filter((documentation) => {
      const hasTitle =
        documentation["dct:title"] && documentation["dct:title"].trim() !== "";
      const hasDescription =
        documentation["dct:description"] &&
        documentation["dct:description"].trim() !== "";
      const hasFormat =
        documentation["dct:format"] &&
        documentation["dct:format"].trim !== "" &&
        (typeof documentation["dct:format"] === "string"
          ? documentation["dct:format"].trim() !== ""
          : true);
      const hasValidUrl =
        documentation["dcat:accessURL"] &&
        documentation["dcat:accessURL"].trim() !== "" &&
        isValidUrl(documentation["dcat:accessURL"].trim());

      // Only include items that have at least one field filled with valid data
      return hasTitle || hasDescription || hasFormat || hasValidUrl;
    });

    // Only emit if there are valid items
    if (validDocumentations.length > 0) {
      emit("addDocumentations", props.distributionId, validDocumentations);
    }
    wasDocumentationsUpdated.value = false;
  }

  // Validate Access Services
  if (wasAccessServicesUpdated.value) {
    const accessServices =
      accessServicesByDistribution.value[props.distributionId] || [];

    // Check if any other field in the access service is filled
    const hasOtherFieldsFilled = (accessService) => {
      const hasTitle =
        accessService["dct:title"] && accessService["dct:title"].trim() !== "";
      const hasDescription =
        accessService["dct:description"] &&
        accessService["dct:description"].trim() !== "";

      return hasTitle || hasDescription;
    };

    // Check if any item has validation errors
    const hasValidationError = accessServices.some((accessService) => {
      const urlValue = accessService["dcat:downloadURL"]
        ? accessService["dcat:downloadURL"].trim()
        : "";
      const otherFieldsFilled = hasOtherFieldsFilled(accessService);

      // Any other field is filled but URL is missing or invalid
      if (otherFieldsFilled && (!urlValue || !isValidUrl(urlValue))) {
        return true;
      }

      // URL exists but is invalid (regardless of other fields)
      if (urlValue && !isValidUrl(urlValue)) {
        return true;
      }

      return false;
    });

    if (hasValidationError) {
      // Trigger validation in the AccessServiceV3 component to show errors
      if (accessServiceV3Ref.value) {
        accessServiceV3Ref.value.validateAllItems();
      }
      // Don't proceed - keep modal open
      return;
    }

    // Filter out items where all fields are empty
    const validAccessServices = accessServices.filter((accessService) => {
      const hasTitle =
        accessService["dct:title"] && accessService["dct:title"].trim() !== "";
      const hasDescription =
        accessService["dct:description"] &&
        accessService["dct:description"].trim() !== "";
      const hasValidUrl =
        accessService["dcat:downloadURL"] &&
        accessService["dcat:downloadURL"].trim() !== "" &&
        isValidUrl(accessService["dcat:downloadURL"].trim());

      // Only include items that have at least one field filled with valid data
      return hasTitle || hasDescription || hasValidUrl;
    });

    // Only emit if there are valid items
    if (validAccessServices.length > 0) {
      emit("addAccessServices", props.distributionId, validAccessServices);
    }
    wasAccessServicesUpdated.value = false;
  }

  if (wasModifiedDateUpdated.value) {
    emit("addModifiedDate", modifiedDateVal.value, props.distributionId);
    wasModifiedDateUpdated.value = false;
  }

  if (wasIssuedDateUpdated.value) {
    emit("addIssuedDate", issuedDateVal.value, props.distributionId);
    wasIssuedDateUpdated.value = false;
  }

  if (wasDescriptionUpdated.value) {
    emit("addDescription", descriptionVal.value, props.distributionId);
    wasDescriptionUpdated.value = false;
  }

  if (wasAccessRightsUpdated.value) {
    emit(
      "addAccessRights",
      accessRightsVal.value,
      props.distributionId,
      props.uri,
    );
    wasAccessRightsUpdated.value = false;
  }

  if (wasAvailabilityUpdated.value) {
    emit(
      "addAvailability",
      availabilityVal.value,
      props.distributionId,
      props.uri,
    );
    wasAvailabilityUpdated.value = false;
  }

  if (wasStatusUpdated.value) {
    emit("addStatus", statusVal.value, props.distributionId);
    wasStatusUpdated.value = false;
  }

  // Validate Checksum
  if (wasChecksumUpdated.value) {
    const hasTitle =
      checksumTitleVal.value && checksumTitleVal.value.trim() !== "";
    const hasAlgorithm =
      checksumDropdownVal.value && checksumDropdownVal.value.trim() !== "";

    // If either field is filled, both must be filled
    if ((hasTitle || hasAlgorithm) && (!hasTitle || !hasAlgorithm)) {
      // Trigger validation in the ChecksumV3 component to show errors
      if (checksumV3Ref.value) {
        checksumV3Ref.value.validateAllItems();
      }
      // Don't proceed - keep modal open
      return;
    }

    // Only emit if both fields are filled
    if (hasTitle && hasAlgorithm) {
      emit(
        "addChecksum",
        checksumTitleVal.value,
        checksumDropdownVal.value,
        props.distributionId,
        checksumInnerURI.value,
      );
    }
    wasChecksumUpdated.value = false;
  }

  // Validate Change License (for "by" licenses)
  if (wasChangeLicenseUpdated.value) {
    const licenseValue =
      typeof changeLicenseDropdownVal.value === "string"
        ? changeLicenseDropdownVal.value.trim()
        : "";
    const attributionText =
      typeof changeLicenseTitleVal.value === "string"
        ? changeLicenseTitleVal.value.trim()
        : "";

    // If license contains 'by-', attribution is required
    const requiresAttribution =
      licenseValue &&
      (licenseValue.toLowerCase().includes("cc-by") ||
        licenseValue.toLowerCase().includes("cc by"));

    // Validation errors
    const hasError =
      (requiresAttribution && !attributionText) || // by- license without attribution
      (attributionText && !licenseValue); // attribution without license

    if (hasError) {
      // Trigger validation in the ChangeLicenseV3 component to show errors
      if (changeLicenseV3Ref.value) {
        changeLicenseV3Ref.value.validate();
      }
      // Don't proceed - keep modal open
      return;
    }

    // Emit values if either is filled (both will be present due to validation above)
    if (licenseValue || attributionText) {
      emit(
        "addChangeLicense",
        changeLicenseTitleVal.value,
        changeLicenseDropdownVal.value,
        props.distributionId,
      );
    }
    wasChangeLicenseUpdated.value = false;
  }

  if (wasLanguageUpdated.value) {
    emit("addLanguage", languageVal.value, props.distributionId, props.uri);
    wasLanguageUpdated.value = false;
  }

  if (wasByteSizeUpdated.value) {
    emit("addByteSize", byteSizeVal.value, props.distributionId);
    wasByteSizeUpdated.value = false;
  }

  if (wasMediaTypeUpdated.value) {
    emit(
      "addMediaType",
      mediaTypeVal.value,
      "dcat:mediaType",
      props.distributionId,
    );
    wasMediaTypeUpdated.value = false;
  }

  if (wasCompressFormatUpdated.value) {
    emit(
      "addCompressFormat",
      compressFormatVal.value,
      "dcat:compressFormat",
      props.distributionId,
    );
    wasCompressFormatUpdated.value = false;
  }

  if (wasPackageFormatUpdated.value) {
    emit(
      "addPackageFormat",
      packageFormatVal.value,
      "dcat:packageFormat",
      props.distributionId,
    );
    wasPackageFormatUpdated.value = false;
  }

  closeSecondModal();
};

watch(
  () => props.sections,
  (newSections) => {
    if (Array.isArray(newSections) && newSections.length > 0) {
      expandedSections.value = newSections.map((_, index) => index === 0);
    }
  },
  { immediate: true },
);

const toggleSection = (index) => {
  expandedSections.value[index] = !expandedSections.value[index];
};

const closeModal = () => {
  emit("close");
};
const confirmAction = () => {
  emit("actionHandling", props.action);
  closeModal();
};

onMounted(async () => {
  documentationsByDistribution.value[props.distributionId] =
    props.documentations;

  accessServicesByDistribution.value[props.distributionId] =
    props.accessServices;

  conformsToItemsByDistribution.value[props.distributionId] =
    props.conformsToItems;

  policyItemsByDistribution.value[props.distributionId] = props.policyItems;

  const instance = getCurrentInstance();
  const env = instance.appContext.app.config.globalProperties.$env;

  try {
    const response = await getPlannedAvailability(env.api.baseUrl);
    plannedAvailabilityOptions.value = response.map((item) => ({
      "@value": item.value,
      label: item.label,
    }));
  } catch (error) {
    console.error("Failed to load planned availability data", error);
  }

  const userLocale =
    instance.appContext.app.config.globalProperties.$i18n.locale;

  try {
    const response = await getLanguages(env.api.baseUrl, userLocale);

    languageOptions.value = response.map((item) => ({
      "@value": item.label,
      label: item.label,
    }));
  } catch (error) {
    console.error("Failed to load language data", error);
  }
});

const updateDocumentations = (distributionId, updatedDocs) => {
  console.log(updatedDocs);

  documentationsByDistribution.value[distributionId] = updatedDocs;
  wasDocumentationsUpdated.value = true;
};

const updateAccessServices = (distributionId, updatedAccessServices) => {
  accessServicesByDistribution.value[distributionId] = updatedAccessServices;
  wasAccessServicesUpdated.value = true;
};

const updateConformsTo = (distributionId, updatedItems) => {
  conformsToItemsByDistribution.value[distributionId] = updatedItems;
  wasConformsToItemsUpdated.value = true;
};

const updatePolicyItems = (distributionId, updatedItems) => {
  policyItemsByDistribution.value[distributionId] = updatedItems;
  wasPolicyItemsUpdated.value = true;
};

const handleModifiedDate = (value) => {
  modifiedDateVal.value = value;
  wasModifiedDateUpdated.value = true;
};

const handleIssuedDate = (value) => {
  issuedDateVal.value = value;
  wasIssuedDateUpdated.value = true;
};

const addDescription = (value) => {
  descriptionVal.value = value;
  wasDescriptionUpdated.value = true;
};

// const handleLicenseAttrByText = (value) => {
//   licenseAttributionByTextVal.value = value;
//   wasLicenseAttrUpdated.value = true;
// };

const handleAccessRights = (value, uri) => {
  console.log(value, uri);

  accessRightsVal.value = value;
  wasAccessRightsUpdated.value = true;
};

const handleAvailability = (value) => {
  availabilityVal.value = value;
  wasAvailabilityUpdated.value = true;
};

const handleStatus = (value) => {
  statusVal.value = value;
  wasStatusUpdated.value = true;
};

const handleChecksum = (titleValue, checksumValue) => {
  console.log(props.checksumURI);

  wasChecksumUpdated.value = true;
  checksumDropdownVal.value = checksumValue;
  checksumTitleVal.value = titleValue;
};

const handleChangeLicense = (titleValue, changeLicenseValue, id, uri) => {
  console.log(titleValue, changeLicenseValue, id, uri);

  wasChangeLicenseUpdated.value = true;
  changeLicenseDropdownVal.value = changeLicenseValue;
  changeLicenseTitleVal.value = titleValue;
};

const handleLanguage = (value) => {
  languageVal.value = value;
  wasLanguageUpdated.value = true;
};

const handleFileFormat = (value, type) => {
  switch (type) {
    case "dcat:mediaType":
      wasMediaTypeUpdated.value = true;
      mediaTypeVal.value = value;
      break;
    case "dcat:compressFormat":
      wasCompressFormatUpdated.value = true;
      compressFormatVal.value = value;
      break;
    case "dcat:packageFormat":
      wasPackageFormatUpdated.value = true;
      packageFormatVal.value = value;
      break;
    default:
      break;
  }
};

const handleByteSize = (value) => {
  byteSizeVal.value = value;
  wasByteSizeUpdated.value = true;
};

const openConfirmDelete = (id) => {
  confirmDeleteModal.value = true;
  documentationDeleteId.value = id;

  const documentation = documentations.value.find((doc) => doc.id === id);
  if (documentation) {
    documentationTitleToDelete.value = documentation["dct:title"];
    documentationUrlToDelete.value = documentation["dcat:downloadURL"];
  }
};

const cancelDelete = () => {
  confirmDeleteModal.value = false;
  documentationDeleteId.value = null;
};

const confirmDeleteAction = () => {
  confirmDeleteModal.value = false;

  minimumDocError.value = false;
};

const handleInput = (event, field, docId = null) => {
  if (field === "dct:format") {
    filteredData.value = [...props.fileTypes]; //reset bec. used in more dropdowns

    const inputValue =
      typeof event === "string"
        ? event.trim().toUpperCase()
        : event?.target?.value?.trim().toUpperCase();

    if (inputValue.length > 0) {
      filteredData.value = props.fileTypes.filter((item) =>
        item["@value"].toUpperCase().startsWith(inputValue),
      );
    } else {
      filteredData.value = [...fileTypes.value];
    }
  } else {
  }
};
</script>
<style>
/* remove number input spinner, Chrome, Safari, Edge, Opera */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* remove number input spinner, Firefox */
input[type="number"] {
  -moz-appearance: textfield;
}

.dpiV3_text_error {
  color: var(--text-error, #a9242c);
  margin-bottom: 0px;
}

.dpiV3_modified {
  width: 100%;
}

.dpiV3_DocumentationV3_Wrap {
  width: 100%;
}

.dpiV3_firstRow {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-4, 24px);
  align-self: stretch;
}

.dpiV3_firstRow_inner {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: var(--Spacing-2, 8px);
}

.dpiV3_LinkInfos {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  flex-wrap: wrap;
}

.dpiV3_title {
  color: var(--blue-80);
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  display: inline-block;
  max-width: 100%;
}

.dpiV3_File-Format {
  display: flex;
  height: 32px;
  padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
  align-items: center;
  gap: var(--Spacing-2, 8px);
  border-radius: var(--Button-Radius, 24px);
  background: var(--blue-20, #d4edfc);
  color: var(--neutral-100);
}

.dpiV3_tempAddMore {
  width: 100%;
  display: flex;
  justify-content: end;
}

.dpiV3_LinkAndMetadata {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: flex-end;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
}

.dpiV3_itemOptional {
  align-self: flex-start;
  text-align: left;
  width: 100%;
  padding-left: 8px;
  position: relative;
}
.dpiV3_itemOptional:hover {
  background-color: var(--neutral-20);
  box-shadow: 0 0 0 25px var(--neutral-20);
  clip-path: inset(-25px 0px -25px 0px);
  cursor: pointer;
}
.dpiV3_caret {
  display: flex;
  width: 48px;
  height: 48px;
  justify-content: center;
  align-items: center;
}
.dpiV3_itemDivider {
  width: 100%;
  height: 1px;
  background-color: var(--neutral-20);
}

.dpiV3_sectionDivider {
  width: 624px;
  height: 1px;
  background-color: var(--neutral-20);
  margin-right: 0px !important;
  padding-right: 0px !important;
}

.dpiV3_group-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  align-self: stretch;
  color: var(--neutral-80, #3d4952);
  width: 560px;
}

.dpiV3_title-description {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
  font-family: var(--font-family-secondary);
  font-size: var(--headline-5-font-size);
  line-height: var(--headline-5-line-height);
  font-weight: var(--headline-5-font-weight);
}

.dpiV3_optionalInfoContent {
  display: flex;
  width: 584px;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.dpiV3_ListMetaData {
  display: flex;
  padding-bottom: var(--Spacing-4, 24px);
  flex-direction: column;
  align-items: center;
  gap: var(--Spacing-4, 24px);
  align-self: stretch;
  padding-left: 40px;
}

.dpiV3_modalLine {
  width: 100%;
  font-weight: bold;
  font-size: 1.2rem;

  border-bottom: 1px solid #e0e0e0;
}

.dpiv3_modalCancelButton {
  margin-top: auto;
  margin-bottom: auto;
}

.dpiV3_modalText {
  align-self: stretch;
  color: var(--neutral-60, #687178);

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
  background: var(--neutral-0, #fff);
  height: fit-content;
  position: relative;
  /* max-height: 80vh; */
  overflow-y: auto;
}

.dpiV3_removeOverflow {
  overflow-y: visible;
}

.dpiV3_optionalInfo {
  padding-left: 0px !important;
  padding-right: 0px !important;
}
.dpiV3_optionalInfoHeader {
  padding-left: var(--Spacing-5, 32px);
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
    color: var(--neutral-80, #3d4952);
    text-overflow: ellipsis;

    /* Headlines/Headline-4 */
    font-family: Inter !important;
    font-size: 24px !important;
    font-style: normal !important;
    font-weight: 700 !important;
    line-height: 36px !important;
    /* 150% */
  }
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
</style>
<style scoped>
.dpiV3AutoCompleteWrapDeleteModal {
  display: flex;
  min-width: 416px;
  max-width: 600px;
  padding: var(--Spacing-4, 24px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;

  border-radius: var(--Modal-Radius, 32px);
  background: var(--blue-10, #f3fbff);
}
</style>
