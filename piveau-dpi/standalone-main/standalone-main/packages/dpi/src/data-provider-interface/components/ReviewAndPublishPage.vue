<script setup>
import { createNode, getNode } from "@formkit/core";

import { useFormKitNodeById } from "@formkit/vue";
import { PhWarning } from "@phosphor-icons/vue";
import {
  computed,
  getCurrentInstance,
  onMounted,
  reactive,
  ref,
  watch,
} from "vue";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import { useDpiContext } from "../composables";
import { useFormValues } from "../composables/useDpiFormValues";
import config from "../config/dcatapdeHappyFlow/page-content-config";
import { getConvertedFormkitData } from "../HappyFlowComponents/services/dpiV3_apis";
import ButtonV3 from "../HappyFlowComponents/ui/ButtonV3.vue";
import Chip from "../HappyFlowComponents/ui/Chip.vue";
import ModalSimpleV3 from "../HappyFlowComponents/ui/ModalSimpleV3.vue";
import RapModal from "../HappyFlowComponents/ui/RapModal.vue";
import StateTag from "../HappyFlowComponents/ui/StateTag.vue";
import TableRow from "../HappyFlowComponents/ui/TableRowV3.vue";
import TextButtonSmall from "../HappyFlowComponents/ui/TextButtonSmall.vue";
import "../config/styles/variables.css";
import "../config/styles/typography.css";

const props = defineProps({
  context: Object,
});
const { t } = useI18n();
const dpiContext = useDpiContext();
const isEditMode = computed(() => {
  return !!dpiContext.value.edit?.enabled;
});

let isActiveDraft = ref(
  dpiContext.value.edit.enabled && !dpiContext.value.edit.fromDraft
);
// console.log(isActiveDraft);
const eraseTempres = () => {
  formValues.value.Covering["dcat:temporalResolution"] = {};
};

let chosenItems = ref([{ isValid: true }]);
let isEditPage = ref(false);
const store = useStore();

const container = ref(null);

let activeSimpleModal = ref(false);
let modalSimpleConf = ref({});

const formkitIsAvailable = ref(false);
const { formValues } = useFormValues();
useFormKitNodeById("dpiForm", (node) => {
  formkitIsAvailable.value = true;
});

function generateDate(data) {
  let year, month, day, hour, minute, second;

  data.forEach((item) => {
    if (item.Year) year = Number.parseInt(item.Year);
    if (item.Month) month = Number.parseInt(item.Month) - 1; // Monate sind 0-indexiert
    if (item.Day) day = Number.parseInt(item.Day);
    if (item.Hour) hour = Number.parseInt(item.Hour);
    if (item.Minute) minute = Number.parseInt(item.Minute);
    if (item.Second) second = Number.parseInt(item.Second);
  });

  return new Date(
    year || 2025,
    month || 0,
    day || 1,
    hour || 0,
    minute || 0,
    second || 0
  ).toLocaleString("de", {
    timeZone: "Europe/Berlin",
    timeZoneName: "short",
  });
}
const dynamicSec = ref();
const isModalVisible = ref(false);

function isActualProperty(obj) {
  return !(
    Object.keys(obj).includes("isValid") && Object.keys(obj).length === 1
  );
}

// Computed properties for accessing Discoverability data
const discoverabilityCategories = computed(() => {
  const discoverabilityPage =
    formValues.value?.Discoverability?.discoverabilityPage;
  return Array.isArray(discoverabilityPage)
    ? discoverabilityPage?.filter(isActualProperty) || []
    : [];
});

const hvdPage = computed(() => {
  return formValues.value?.Discoverability?.hvdPage || [];
});

// Computed properties for accessing BasicInfos data
const basicInfoTitle = computed(() => {
  const titles = formValues.value?.BasicInfos?.["dct:title"];
  const germanTitle = titles?.find((title) => title["@language"] === "de");
  return germanTitle ? germanTitle["@value"] : null; // Gibt den @value zurück oder null, wenn nicht gefunden
});

const basicInfoDescription = computed(() => {
  return (
    formValues.value?.BasicInfos?.["dct:description"]?.[0]?.["@value"] || ""
  );
});

const basicInfoModified = computed(() => {
  const modifiedDate =
    formValues.value?.BasicInfos?.["dct:modified"]?.[0]?.["@value"];
  return modifiedDate
    ? new Date(modifiedDate).toLocaleDateString("de-DE", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      })
    : "";
});

const basicInfoPublisher = computed(() => {
  return formValues.value?.BasicInfos?.["dct:publisher"]?.[0] || {};
});

const basicInfoContactPoint = computed(() => {
  return formValues.value?.BasicInfos?.["dcat:contactPoint"]?.[0] || {};
});

// Computed properties for Coverage section
const coveringGeopolitical = computed(() => {
  return (
    formValues.value?.Covering?.["dcatde:politicalGeocodingURI"]?.filter(
      isActualProperty
    )?.[0] || {}
  );
});

const coveringTemporal = computed(() => {
  return formValues.value?.Covering?.["dcat:temporalResolution"] || {};
});

const eraseGeopolitical = () => {
  formValues.value.Covering["dcatde:politicalGeocodingURI"] = [{ isValid: true }];
};

const hasCoveringGeopolitical = computed(() => {
  return Object.keys(coveringGeopolitical.value).length > 1;
});

const hasCoveringTemporal = computed(() => {
  return (
    coveringTemporal.value["dct:temporal"]?.length > 0 &&
    coveringTemporal.value["dct:temporal"][0]["dcat:startDate"] != "" &&
    coveringTemporal.value["dct:temporal"][0]["dcat:endDate"] != ""
  );
});
let distributionLicense = ref();
// Computed properties for Distribution section
distributionLicense = computed(() => {
  // Taking the license of the first distribution and set is as the standard
  return (
    formValues.value?.DistributionSimple?.["dcat:distribution"]?.[0]?.[
      "dct:license"
    ]?.[0] || {}
  );
});

const distributions = computed(() => {
  return formValues.value?.DistributionSimple?.["dcat:distribution"] || [];
});

// Computed properties for Additionals section
const additionals = computed(() => {
  return formValues.value?.Additionals || {};
});

const additionalsKeys = computed(() => {
  Object.keys(additionals.value).forEach((element) => {
    if (additionals.value.hasOwnProperty(element)) {
      const value = additionals.value[element];
      if (value === undefined || (Array.isArray(value) && value.length === 0)) {
        delete additionals.value[element]; // Entfernen des Key-Value-Paares
        // console.log(`${element} has been removed.`);
      }
    }
  });

  return Object.keys(additionals.value);
});

const parseISOToGermanDate = (iso) => {
  const parts = iso.split("-");
  if (parts.length !== 3)
    throw new Error("Ungültiges Format, erwartet YYYY-MM-DD");
  let [y, m, d] = parts;
  d = d.padStart(2, "0");
  m = m.padStart(2, "0");
  if (!/^\d{4}$/.test(y) || !/^\d{2}$/.test(m) || !/^\d{2}$/.test(d))
    throw new Error("Ungültige Datenteile");
  return `${d}.${m}.${y}`;
};

const hasAdditionals = computed(() => {
  return additionalsKeys.value.length > 0;
});

function openModal(type) {
  dynamicSec.value = type;
  isModalVisible.value = true;
  // Prevent background scrolling
  document.body.style.overflow = "hidden";
}

function closeModal() {
  // Restore background scrolling
  document.body.style.overflow = "";
  isModalVisible.value = false;
  // console.log("Modal unsichtbar:", isModalVisible.value);
}

// if (!isEditMode.value) props.context.node.input(chosenItems);

function addAdditionals() {}
function handleModalSimpleButtonAction(item) {
  delete formValues.value.Additionals[item];
}

function eraseItem(item, section) {
  activeSimpleModal.value = true;
  modalSimpleConf.value = {
    button: "Löschen",
    header: `${t(`message.dataupload.additionals.${item}`)} löschen`,
    text: `Sind Sie sicher, dass Sie das optionale Feld ${t(
      `message.dataupload.additionals.${item}`
    )} löschen wollen?`,
    action: item,
  };
  // delete formValues[section][item]
}

const isMounted = ref(false);
onMounted(() => {
  isMounted.value = true;
});
</script>

<template>
  <div v-if="isMounted && formkitIsAvailable">
    <RapModal
      v-if="isModalVisible"
      :context="context"
      :active-section="dynamicSec"
      @close="closeModal"
    />

    <div
      v-if="true || chosenItems[0].isValid"
      class="dpiV3InnerComponentWrap V3-typography"
    >
      <div class="w-100">
        <div class="dpiV3_Frame_831">
          <div v-if="dpiContext.edit.fromDraft || dpiContext.edit.enabled">
            <TableRow
              :id="formValues.value?.BasicInfos?.datasetID"
              :key="formValues.value?.BasicInfos?.datasetID"
              :data-cy="formValues.value"
              catalogue="test-catalog"
              :text="
                formValues.value?.BasicInfos?.title.de ||
                formValues.value?.BasicInfos?.title.en
              "
              :date="basicInfoModified"
              :draft="!isActiveDraft"
              :dataset="dataset"
              from-draft="true"
            />
          </div>
          <div v-else class="dpiV3_Frame_840">
            <h4 class="dpiV3_title" style="margin-bottom: 32px;">
              {{ $t("message.dataupload.datasets.rap.title") }}
            </h4>
            <div class="dpiV3_intro copy-large-regular">
              {{ $t("message.dataupload.datasets.rap.intro-text") }}
            </div>
          </div>
        </div>
      </div>
      <!-- Findability -->
      <div v-if="true" class="dpiV3Card rapFindability">
        <div class="firstSec">
          <span>{{
            $t("message.dataupload.datasets.rap.findability.title")
          }}</span>
          <ButtonV3
            size="large"
            icon-start="pen"
            variant="secondary"
            @click="openModal('findabilityHvd')"
          />
        </div>
        <div class="secondSec">
          <div class="copy-large-regular" style="margin-bottom: 5px">{{
            $t("message.dataupload.datasets.rap.findability.categoryHeader")
          }}</div>
          <!-- Slicing the first Index to ignore the isvalid attribute -->
          <Chip
            v-for="(item, index) in discoverabilityCategories"
            :key="index"
            :text="item.label"
            :data="{ '@value': item.value, URI: item.URI }"
            :setup="{
              '@type': 'static',
              '@inTable': false,
              '@rapfindability': true,
            }"
          />
        </div>
        <div class="thirdSec" v-if="hvdPage?.[0]?.label">
          <StateTag label="HVD" state="hvd" size="page" />
          <h5 class="headline-5">
            {{ hvdPage?.[0]?.label }}
          </h5>
        </div>
      </div>
      <!-- Basic infos -->
      <div v-if="true" class="dpiV3Card rapEssentials">
        <div class="firstSec">
          <span>{{
            $t("message.dataupload.datasets.rap.essentials.title")
          }}</span>
          <ButtonV3
            size="large"
            icon-start="pen"
            variant="secondary"
            @click="openModal('essentials')"
          />
        </div>
        <div class="secondSec">
          <!-- dct:title -->
          <h3 class="heading-3">
            {{ basicInfoTitle }}
          </h3>
        </div>
        <div class="thirdSec">
          <!-- dct:description -->
          <span>{{ basicInfoDescription }}</span>
        </div>
        <div class="fourthSec flexSec">
          <span class="copy-large-regular">{{
            $t("message.dataupload.datasets.rap.essentials.modifiedHeader")
          }}</span>
          <!-- dct:modified -->
          <span class="copy-large-semi-bold">{{ basicInfoModified }}</span>
        </div>
        <div class="fifthSec flexSec">
          <span class="copy-large-regular">{{
            $t("message.dataupload.datasets.rap.essentials.publisherHeader")
          }}</span>
          <h5 class="headline-5">
            {{ basicInfoPublisher["foaf:name"] || "" }}
          </h5>
          <a class="copy-large-regular">{{
            basicInfoPublisher["foaf:mbox"] || ""
          }}</a>
          <a
            class="copy-large-regular"
            :href="basicInfoPublisher['foaf:homepage'] || ''"
            >{{ basicInfoPublisher["foaf:homepage"] || "" }}</a
          >
        </div>
        <div class="sixthSec flexSec">
          <span class="copy-large-regular">{{
            $t("message.dataupload.datasets.rap.essentials.contactHeader")
          }}</span>
          <h5 class="headline-5">
            {{ basicInfoContactPoint["vcard:fn"] || "" }}
          </h5>
          <a class="copy-large-regular">{{
            basicInfoContactPoint["vcard:hasEmail"] || ""
          }}</a>
          <span class="copy-large-regular">{{
            basicInfoContactPoint["vcard:hasTelephone"] || ""
          }}</span>
        </div>
      </div>
      <!-- Coverage -->
      <template v-if="true">
        <div class="dpiV3Card rapCoverage">
          <div class="firstSec">
            <span>{{
              $t("message.dataupload.datasets.rap.coverage.title")
            }}</span>
            <ButtonV3
              size="large"
              icon-start="pen"
              variant="secondary"
              @click="openModal('coverage')"
            />
          </div>
          <div v-if="hasCoveringGeopolitical" class="secondSec flexSec">
            <div class="rapHeadWrap">
              <span class="copy-large-regular">{{
                $t("message.dataupload.datasets.rap.coverage.geopolTitle")
              }}</span>
              <TextButtonSmall button-text="löschen" @click="eraseGeopolitical" />
            </div>
            <div class="coveragePlaceWrap">
              <h5 class="headline-5">
                {{ coveringGeopolitical.label || "" }}
              </h5>
              <StateTag
                :label="coveringGeopolitical.inVoc?.toUpperCase() || ''"
                state="geopolitical"
                size="page"
              />
            </div>
          </div>
          <div v-if="hasCoveringTemporal" class="thirdSec">
            <div class="rapHeadWrap">
              <span class="copy-large-regular">{{
                $t("message.dataupload.datasets.rap.coverage.tempCoverage")
              }}</span>
              <TextButtonSmall button-text="löschen" @click="eraseTempres" />
            </div>
            <div class="dpiV3_RapInnerCardWrapper">
              <div
                v-for="item in coveringTemporal['dct:temporal'] || []"
                class="dpiV3_RapInnerCard"
              >
                <div class="copy-small-regular">
                  Von
                  <div>
                    <span>{{
                      new Date(item["dcat:startDate"]).toLocaleDateString(
                        "de-DE",
                        {
                          day: "2-digit",
                          month: "2-digit",
                          year: "numeric",
                        }
                      )
                    }}</span>
                    <span v-if="item.startTime != undefined">
                      {{ item.startTime }}
                    </span>
                  </div>
                </div>
                <div class="copy-small-regular">
                  Bis
                  <div>
                    <span>
                      {{
                        new Date(item["dcat:endDate"]).toLocaleDateString(
                          "de-DE",
                          {
                            day: "2-digit",
                            month: "2-digit",
                            year: "numeric",
                          }
                        )
                      }}
                    </span>
                    <span v-if="item.endTime != undefined">
                      {{ item.endTime }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Distribution -->
        <div class="dpiV3Card rapDistribution">
          <div class="firstSec">
            <span>{{ $t("message.metadata.distributions") }}</span>
            <ButtonV3
              size="large"
              icon-start="pen"
              variant="secondary"
              @click="openModal('distributions')"
            />
          </div>
          <div class="secondSec flexSec">
            <span class="copy-large-regular">{{
              $t("message.dataupload.steps.dct:license")
            }}</span>
            <h5 class="headline-5">
              {{
                distributionLicense["dcterms:license"] ||
                formValues["DistributionSimple"]?.["dct:license"]?.[0]?.[
                  "dcterms:license"
                ] ||
                "Keine Lizenz angegeben"
              }}
            </h5>
          </div>
          <div class="thirdSec flexSec" v-if="formValues['DistributionSimple']?.['dct:license']?.[0]?.title || formValues?.DistributionSimple?.['dcat:distribution']?.[0]?.['dcatde:licenseAttributionByText']">
            <span class="copy-large-regular">{{
              $t(
                "message.dataupload.datasets.dcat:distribution.advanced.dcatde:licenseAttributionByText"
              )
            }}</span>
            <h5 class="headline-5">
              {{
                formValues["DistributionSimple"]?.["dct:license"]?.[0]
                  ?.title ||
                  formValues?.DistributionSimple?.["dcat:distribution"]?.[0]?.[
                  "dcatde:licenseAttributionByText"
                ]
              }}
            </h5>
          </div>
          <div class="fourthSec flexSec">
            <span class="copy-large-regular">{{
              $t("message.dataupload.steps.dcat:distribution")
            }}</span>
            <div v-for="distribution in distributions" class="dpiV3_distCard">
              <a class="copy-large-semi-bold" :href="distribution['dcat:accessURL']" target="_blank">{{
                distribution["dct:title"] || distribution["dcat:accessURL"]
              }}</a>
              <formatbubble>{{
                distribution["dct:format"]?.label || "Kein Format angegeben"
              }}</formatbubble>
            </div>
          </div>
        </div>
        <!-- Additionals -->
        <div v-if="hasAdditionals" class="dpiV3Card rapDistribution">
          <div class="firstSec">
            <span>{{ $t("message.metadata.additionals") }}</span>
            <!-- Disabled the edit button -- ToDo -->
            <!-- <ButtonV3 size="large" iconStart="pen" variant="secondary" @click="openModal('')" /> -->
          </div>
          <div
            v-for="additionalItem in additionalsKeys"
            class="secondSec flexSec"
          >
            <div class="dpiV3_optionalSpanHeadWrap">
              <span class="dpiV3_optionalSpan copy-large-regular">
                {{ $t(`message.dataupload.additionals.${additionalItem}`) }}
                (optional)</span
              >
              <TextButtonSmall
                button-text="löschen"
                @click="eraseItem(additionalItem, 'Additionals')"
              />
            </div>
            <div v-if="additionalItem === 'dcat:temporalResolution'">
              <span>{{ generateDate(additionals[additionalItem] || []) }}</span>
            </div>
            <!-- {{ additionals[additionalItem] }} -->
            <div
              class="additionalSubPropsWrapper"
              v-for="innerAddItem in additionals[additionalItem] || []"
              v-if="additionalItem != 'dcat:temporalResolution'"
              :key="
                innerAddItem
                  ? innerAddItem['@id'] ||
                    innerAddItem['@value'] ||
                    innerAddItem['rdfs:label']||
                    innerAddItem['skos:notation']
                  : null
              "
            >
              <span v-if="innerAddItem && innerAddItem['rdf:type']">{{
                innerAddItem["rdf:type"]
              }}</span>
              <span
                v-if="
                  innerAddItem &&
                  innerAddItem['@type'] ===
                    'http://www.w3.org/2001/XMLSchema#date'
                "
                >{{ parseISOToGermanDate(innerAddItem["@value"]) }}</span
              >
              <span
                v-if="
                  innerAddItem &&
                  innerAddItem['@value'] &&
                  innerAddItem['@type'] !==
                    'http://www.w3.org/2001/XMLSchema#date'
                "
                >{{ innerAddItem["@value"] }}</span
              >
              <span v-if="innerAddItem && innerAddItem['dct:title']">{{
                innerAddItem["dct:title"]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['skos:notation']">{{
                innerAddItem["skos:notation"][0]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['dct:description']">{{
                innerAddItem["dct:description"]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['@id']">{{
                innerAddItem["@id"]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['rdfs:label']">{{
                innerAddItem["rdfs:label"]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['foaf:name']">{{
                innerAddItem["foaf:name"]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['foaf:homepage']">{{
                innerAddItem["foaf:homepage"]
              }}</span>
              <span v-if="innerAddItem && innerAddItem['foaf:mbox']">{{
                innerAddItem["foaf:mbox"]
              }}</span>

              <!-- <span v-if="innerAddItem['@language']">Sprache:</span> -->
              <!-- <span v-if="innerAddItem['@id']">Id: {{ innerAddItem['@id'] }}</span> -->
              <!-- <h5 v-if="innerAddItem['@value']" class="dpiV3_optionalTextSpan headline-5">{{ innerAddItem['@value'] }}</h5> -->
            </div>
            <!-- Need to form the Date by hand -->
          </div>
          <div class="dpiV3_tempAddMore">
            <ButtonV3
              button-text="Optionale Informationen hinzufügen"
              size="large"
              icon-start="plus"
              variant="tertiary"
              @click="openModal('additionals')"
            />
          </div>
        </div>
        <div v-if="!hasAdditionals" class="dpiV3_tempAddMore">
          <ButtonV3
            button-text="Optionale Informationen hinzufügen"
            size="large"
            icon-start="plus"
            variant="tertiary"
            @click="openModal('additionals')"
          />
        </div>
      </template>
    </div>
    <ModalSimpleV3
      v-if="activeSimpleModal"
      :buttons="modalSimpleConf.button"
      :header-text="modalSimpleConf.header"
      :text="modalSimpleConf.text"
      :action="modalSimpleConf.action"
      @close="activeSimpleModal = false"
      @action-handling="handleModalSimpleButtonAction($event)"
    />
  </div>
  <ModalSimpleV3
    v-if="activeSimpleModal"
    :buttons="modalSimpleConf.button"
    :header-text="modalSimpleConf.header"
    :text="modalSimpleConf.text"
    :action="modalSimpleConf.action"
    @close="activeSimpleModal = false"
    @action-handling="handleModalSimpleButtonAction($event)"
  />
</template>

<style scoped>
.additionalSubPropsWrapper {
  span {
    display: block;
  }
}
.firstSec {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;

  span {
    color: var(--Colour-blue-Blue80, #0172ad);
    /* Headlines/Caption */
    font-family: Inter;
    font-size: 12px;
    font-style: normal;
    font-weight: 700;
    line-height: 150%;
    /* 18px */
    text-transform: uppercase;
    flex: 1 0 0;
  }
}

.rapHeadWrap {
  display: flex;
  align-items: center;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;

  span {
    height: 26px;
    flex: 1 0 0;
  }
}

.rapCoverage {
  .coveragePlaceWrap {
    display: flex;
    align-items: center;
    align-content: center;
    gap: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
    align-self: stretch;
    flex-wrap: wrap;
  }

  .thirdSec {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--Spacing-1, 4px);
    align-self: stretch;

    .dpiV3_RapInnerCardWrapper {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: var(--Spacing-4, 24px);
      align-self: stretch;
    }

    .dpiV3_RapInnerCard {
      gap: var(--Spacing-4, 24px);

      div {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        flex: 1 0 0;
        color: var(--Colour-neutral-Neutral60, #687178);

        span {
          color: var(--neutral-80, #3d4952);
          font-family: var(--font-family-secondary);
          font-size: var(--headline-5-font-size);
          line-height: var(--headline-5-line-height);
          font-weight: var(--headline-5-font-weight);
        }
      }
    }
  }
}

.dpiV3_RapInnerCard {
  display: flex;
  padding: var(--Spacing-4, 24px);
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
  border-radius: var(--Inside-Modal-Radious, 16px);
  background: var(--Colour-neutral-Neutral0, #fff);
}

.flexSec {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-1, 4px);
  align-self: stretch;

  h5 {
    color: var(--neutral-80, #3d4952);
    margin: 0;
  }

  a {
    cursor: pointer;
    color: var(--blue-80, #0172ad) !important;
  }
}

.dpiV3Card {
  display: flex;
  min-width: 416px;
  max-width: 600px;
  padding: var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;

  border-radius: var(--Modal-Radius, 32px);
  background: var(--blue-10, #f3fbff);
}

.rapEssentials {
  .secondSec {
    h3 {
      color: var(--neutral-80, #3d4952);
    }
  }

  .thirdSec {
    display: flex;
    align-items: center;
    align-content: center;
    gap: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
    align-self: stretch;
    flex-wrap: wrap;
  }

  .fourthSec {
  }

  .fifthSec {
    h5 {
      color: var(--neutral-80, #3d4952);
      margin: 0;
    }
  }
}

.rapFindability {
  .secondSec {
    span {
      color: var(--neutral-80, #3d4952);
    }
  }

  .thirdSec {
    display: flex;
    align-items: center;
    align-content: center;
    gap: var(--Spacing-1, 4px) var(--Spacing-2, 8px);
    align-self: stretch;
    flex-wrap: wrap;

    h5 {
      color: var(--neutral-80, #3d4952);
      margin: 0;
    }
  }
}

.dpiV3_distCard {
  display: flex;
  padding: var(--Spacing-4, 24px);
  flex-direction: row;
  align-items: flex-start;
  gap: var(--Spacing-3, 16px);
  align-self: stretch;
  border-radius: var(--Inside-Modal-Radious, 16px);
  background: var(--neutral-0, #fff);
  margin-bottom: var(--Spacing-3, 16px);

  a {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    flex: 1 0 0;
    overflow: hidden;
    color: var(--blue-80, #0172ad);
    text-overflow: ellipsis;
  }

  formatBubble {
    display: flex;
    height: 32px;
    padding: var(--Spacing-1, 4px) var(--Spacing-3, 16px);
    align-items: center;
    gap: var(--Spacing-2, 8px);
    border-radius: var(--Button-Radius, 24px);
    background: var(--blue-20, #d4edfc);
  }
}

.dpiV3_optionalSpan {
  color: var(--Colour-neutral-Neutral80, #3d4952);
  flex: 1 0 0;
}

.dpiV3_optionalSpanHeadWrap {
  display: flex;
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
}

.dpiV3_optionalTextSpan {
  color: var(--neutral-80, #3d4952);
}
.dpiV3_TableRowDescContainer {
  display: none;
}
</style>
