<template>
  <div ref="overview-page" class="col-12">
    <!-- LANGUAGE SELECTOR -->
    <div class="mt-5 mb-0">
      <div class="row">
        <div class="col-10">
          {{ $t("message.dataupload.info.selectDisplayLanguage") }}:
          <LanguageSelector class="ml-1" v-model="dpiLocale"></LanguageSelector>
        </div>
      </div>
    </div>
    <div class="mb-3" v-if="showDatasetsOverview && overviewPageIsVisible">
      <DatasetOverview :dpiLocale="dpiLocale" :key="dpiLocale" />
    </div>
    <div class="mb-3" v-if="showCatalogsOverview && overviewPageIsVisible">
      <CatalogueOverview :dpiLocale="dpiLocale" :key="dpiLocale" />
    </div>
  </div>
  <!-- Legal notice *** Checks for ANNIF to determine that this UI is DEU -->
  <div
    v-if="instance.content.dataProviderInterface.annifIntegration"
    class="legalnotice py-5"
    style="width: 90%; margin: 0 auto"
  >
    <div class="d-flex align-items-start">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="30px"
        height="30px"
        fill="currentColor"
        class="bi bi-info-circle mx-3 mb-3 mt-1 infoboxI"
        viewBox="0 0 16 16"
      >
        <path
          d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"
        />
        <path
          d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"
        />
      </svg>
      <div class="w-80">
        <p>
          For <strong>European</strong>&nbsp;<strong
            >Commission's datasets</strong
          >, bear in mind that&nbsp;<a
            class="external-link"
            href="https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX:32011D0833"
            target="_blank"
            rel="nofollow noopener"
            ><ins>Decision 2011/833/EU</ins></a
          >
          allows for their commercial reuse without prior authorisation, except
          for the material subject to the third party intellectual property
          rights. This Decision has been implemented under the&nbsp;<a
            class="external-link"
            href="https://ec.europa.eu/transparency/documents-register/detail?ref=C(2019)1655&amp;lang=en"
            target="_blank"
            rel="nofollow noopener"
            ><ins>Decision C(2019) 1655 final</ins></a
          >
          by which Creative Commons Attribution 4.0 International Public License
          (CC BY 4.0) is adopted as an open licence for the Commission's reuse
          policy. Additionally, raw data, metadata or other documents of
          comparable nature may alternatively be distributed under the
          provisions of the Creative Commons Universal Public Domain Dedication
          deed (CC0 1.0).
        </p>
        <p>
          The&nbsp;<strong>Council</strong>&nbsp;and the&nbsp;<strong
            >European Court of Auditors</strong
          >&nbsp;have approved similar decisions on reuse. It is advisable that
          you check&nbsp;<strong>the reuse policy of your organisation</strong
          >&nbsp;before publishing or submitting your dataset.
        </p>
      </div>
    </div>
    <!-- <p>&nbsp;</p>
     <hr />
    <p>&nbsp;</p> -->

    <div class="d-flex align-items-start mt-4">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="30px"
        height="30px"
        fill="currentColor"
        class="bi bi-info-circle mx-3 mb-3 mt-1 infoboxI"
        viewBox="0 0 16 16"
      >
        <path
          d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"
        />
        <path
          d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"
        />
      </svg>
      <div class="w-80">
        <p>
          As owner of your dataset, you guarantee that it does not violate the
          copyright, other intellectual property or privacy rights of any third
          party. In particular, if third party material is included in the
          dataset, you must ensure that all necessary permissions have been
          obtained and appropriate acknowledgment is given, if necessary.
        </p>
        <p>
          If you need further information regarding
          <strong>licenses or copyright</strong> issues, please contact us
          at&nbsp;<span class="nobr"
            ><a
              class="external-link"
              href="mailto:op-copyright@publications.europa.eu"
              target="_blank"
              rel="nofollow noopener"
              >op-copyright@publications.europa.eu</a
            ></span
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable no-restricted-syntax,guard-for-in */
import axios from "axios";
import { mapActions, mapGetters } from "vuex";

import LanguageSelector from "../components/LanguageSelector.vue";
import DatasetOverview from "./OverviewPage/DatasetOverview.vue";
import CatalogueOverview from "./OverviewPage/CatalogueOverview.vue";

import { useIntersectionObserver } from "@vueuse/core";
import { ref, useTemplateRef } from "vue";
import { getCurrentInstance } from "vue";

export default {
  components: {
    LanguageSelector,
    DatasetOverview,
    CatalogueOverview,
  },
  props: {
    property: {
      type: String,
    },
    context: {
      type: Object,
    },
  },
  data() {
    return {
      instance:
        getCurrentInstance().appContext.app.config.globalProperties.$env,
      dpiLocale:
        this.$route.query?.locale ||
        this.$i18n.locale ||
        this.$i18n.fallbackLocale ||
        "en",
    };
  },
  computed: {
    ...mapGetters("auth", ["getIsEditMode"]),
    ...mapGetters("dpiStore", ["getData"]),
    showDatasetsOverview() {
      return this.property === "datasets";
    },
    showCatalogsOverview() {
      return this.property === "catalogues";
    },
  },
  methods: {
    ...mapActions("dpiStore", ["clearAll", "saveLocalstorageValues"]),
    clear() {
      this.clearAll();
    },
    /*** Overview Page checker functionality ***/
    // checkDatasetMandatory() {
    //   if (!JSON.parse(localStorage.getItem('dpi_mandatory'))['datasets']) {
    //     this.$router.push({
    //       name: 'DataProviderInterface-Input',
    //       params: {
    //         property: 'datasets',
    //         page: 'step1'
    //       },
    //       query: {
    //         error: 'mandatoryDataset',
    //         locale: this.$route.query.locale
    //       }
    //     });
    //   }
    // },
    // checkDistributionMandatory() {
    //   if (!JSON.parse(localStorage.getItem('dpi_mandatory'))['distributions'].length > 0 && !JSON.parse(localStorage.getItem('dpi_mandatory'))['distributions'].every(el => el === true)) {
    //     this.$router.push({
    //       name: 'DataProviderInterface-Input',
    //       path: '/dpi/datasets/distoverview',
    //       params: {
    //         property: 'datasets',
    //         page: 'distoverview',
    //       },
    //       query: {
    //         error: 'mandatoryDistribution',
    //         locale: this.$route.query.locale
    //       },
    //     });
    //   }
    // },
    // checkCatalogueMandatory() {
    //   if (!JSON.parse(localStorage.getItem('dpi_mandatory'))['catalogues']) {
    //     this.$router.push({
    //       name: 'DataProviderInterface-Input',
    //       params: {
    //         property: 'catalogues',
    //         page: 'step1'
    //       },
    //       query: {
    //         error: 'mandatoryCatalog',
    //         locale: this.$route.query.locale
    //       }
    //     });
    //   }
    // },
    checkID(property) {
      // Check uniqueness of Dataset ID
      if (!this.getIsEditMode) {
        this.checkUniqueID(property).then((isUniqueID) => {
          if (!isUniqueID) {
            // Dataset ID not unique / taken in meantime --> Redirect to step1 where the user can choose a new ID
            this.$router.push({
              name: "DataProviderInterface-Input",
              params: {
                property: property,
                page: "step1",
              },
              query: {
                error: "id",
                locale: this.$route.query.locale,
              },
            });
          }
        });
      }
    },
    checkUniqueID(property) {
      return new Promise((resolve) => {
        if (this.getData(property)["@id"] !== "") {
          const request = `${this.$env.api.hubUrl}${property}/${
            this.getData(property)["@id"]
          }?useNormalizedId=true`;
          axios
            .head(request)
            .then(() => {
              resolve(false);
            })
            .catch(() => {
              resolve(true);
            });
        }
      });
    },
  },
  created() {
    this.$nextTick(() => {
      if (this.property === "datasets") {
        // this.checkID('datasets');
        // this.checkDatasetMandatory();
        // this.checkDistributionMandatory();
      }

      if (this.property === "catalogues") {
        // this.checkID('catalogues')
        // this.checkCatalogueMandatory();
      }
    });
  },
  setup() {
    const target = useTemplateRef("overview-page");
    const overviewPageIsVisible = ref(false);

    // Workaround.
    // Ensure the individual overview pages are only mounted whenever this overview page is visible.
    // For some reason, the form values are not reactive so on initial load, the distribution values are not displayed properly.
    // This workaround delays the initial mount of the overview page until the overview page is visible.
    // We use the IntersectionObserver to check if the overview page is visible, but we could also use more proper datamodels to determine this.
    // todo: figure out why the form values are not reactive on initial load
    useIntersectionObserver(
      target,
      ([{ isIntersecting }]) => {
        overviewPageIsVisible.value = isIntersecting;
      },
      {
        rootMargin: "999999px 999999px 999999px 999999px",
      }
    );

    return { overviewPageIsVisible };
  },
};
</script>

<style lang="scss" scoped>
.heading,
.description,
.arrow {
  cursor: pointer;
}

.options,
.download {
  .dropdown-menu {
    min-width: 300px;

    .dropdown-item {
      &:hover {
        color: initial;
        background-color: initial;
      }
    }
  }
}

.legalnotice {
  a {
    color: blue;
  }

  padding: 1rem;
  background-color: rgb(171, 225, 165);
}

.infoboxI {
  width: 5%;
}

.w-80 {
  width: 80%;
}
</style>
