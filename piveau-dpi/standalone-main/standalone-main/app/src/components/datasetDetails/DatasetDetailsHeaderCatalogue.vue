<template>
    <div class="d-flex align-items-center dsd-header-catalogue by-copy-small-semibold">
      <div>
        <app-link
          class="by-copy-small-semibold by-link-light by-dataset-details-header-catalog"
          :to="getCatalogLink(getCatalog)"
          :title="$t('message.tooltip.datasetDetails.catalogue')"
          data-toggle="tooltip"
          data-placement="top">
          {{ getTranslationFor(getCatalog.title, $route.query.locale, getLanguages) }}
        </app-link>
      </div>
    </div>
  </template>

  <script>
  import {has, isNil} from "lodash";
  import {AppLink, getTranslationFor, getCountryFlagImg} from "@piveau/piveau-hub-ui-modules";
  import {mapGetters} from "vuex";

  export default {
    name: "DatasetDetailsHeaderCatalogue",
    components: {AppLink},
    computed: {
      ...mapGetters('datasetDetails', [
        'getCatalog',
        'getCountry',
        'getLanguages'
      ]),
    },
    methods: {
      getTranslationFor,
      getCountryFlagImg,
      showCountryFlag(country) {
        return has(country, 'id') && !isNil(country.id);
      },
      getCatalogLink(catalog) {
        return `/datasets?catalog=${catalog.id}&showcatalogdetails=true&locale=${this.$route.query.locale}`;
      },
    }
  }
  </script>
