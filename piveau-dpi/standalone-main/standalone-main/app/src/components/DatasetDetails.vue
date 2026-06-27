<template>
    <div
      class="by-dataset-details-container d-flex flex-column bg-transparent site-wrapper"
      :data-cy="getRepresentativeLocaleOf(getTitle, $route.query.locale, getLanguages)
      && `dataset@${getRepresentativeLocaleOf(getTitle, $route.query.locale, getLanguages)}`"
    >
    <!-- Navigation not wanted from bayern -->
    <!-- <dataset-details-navigation v-if="topTitle" :dataset-id="getID"/> -->
    <div class="by-dataset-details-content-container container-fluid mb-5 pt-1 content dsd-content">
        <dataset-details-header :previous-route="previousRoute"/>
        <!-- <dataset-details-navigation v-if="!topTitle" :dataset-id="getID"/> -->
        <router-view name="datasetDetailsSubpages"></router-view>
    </div>
  </div>
</template>

  <script>
  import { mapGetters } from 'vuex';
  import DatasetDetailsHeader from "./datasetDetails/DatasetDetailsHeader.vue";
  import { DatasetDetailsNavigation, helpers, head } from "@piveau/piveau-hub-ui-modules";
  const { getRepresentativeLocaleOf, getTranslationFor } = helpers;

  export default {
    name: 'datasetDetails',
    components: {
      DatasetDetailsHeader,
      DatasetDetailsNavigation,
    },
    data() {
      return {
        topTitle: this.$env.content.datasetDetails.header.navigation === "top",
        previousRoute: null,
      };
    },
    props: {
      activeTab: {
        type: Number,
        default: 0
      },
      citationStyle: String
    },
    computed: {
      ...mapGetters('datasetDetails', [
        'getID',
        'getLanguages',
        'getTitle',
        'getDescription',
      ]),
    },
    methods: {
      getRepresentativeLocaleOf,
      getTranslationFor,
    },
    created () {
      console.log('back', this.$router.options.history.state)
      this.previousRoute = this.$router.options.history.state?.back;
    },
    setup() {
      head.useDatasetDetailsHead();
    }
  };
  </script>

<style lang="scss">
  .site-wrapper.by-dataset-details-container {
    padding: 32px 32px 0 !important;
    margin: 0 !important;
  }

  @media screen and (min-width: 1200px) {
    .site-wrapper.by-dataset-details-container {
      padding: 32px 80px 0 !important;
    }
  }
</style>
