<template>
  <div class="col-12 w-100 p-0 m-0">
    <div class="loading-spinner mx-auto" v-if="!similarDatasetsFetched"></div>

    <div id="similar-datasets-carousel"
        v-if="similarDatasetsPresent && similarDatasetsFetched && similarDatasets.length > 0"
        class="carousel slide p-0 m-0 w-100 similar-datasets-carousel"
        data-touch="true"
        data-ride="carousel"
        data-interval="false">

      <div class="carousel-inner">
        <div class="carousel-item" v-for="(similarDataset, i) in similarDatasets" :key="i" :class="{ active: i === 0}">
          <pv-data-info-box
            v-if="has(similarDataset, 'title') && has(similarDataset, 'description')"
            :to="`/datasets/${similarDataset.id}`"
            :dataset="{
              title: getTranslationFor(similarDataset.title, $route.query.locale, similarDataset.languages) || similarDataset.id,
              description: getTranslationFor(similarDataset.description, $route.query.locale, similarDataset.languages),
              catalog: '',
              formats: similarDataset.distributionFormats
            }"
            :description-max-length="1000"
            :data-cy="`dataset@${similarDataset.id}`"
            class="d-block w-100"
          >
            <template #footer>
              <div class="d-none"></div>
            </template>
          </pv-data-info-box>
        </div>
      </div>

      <div class="similar-datasets-carousel-controls">
        <button class="by-btn-tertiary-medium-light carousel-control-prev"
          type="button"
          data-target="#similar-datasets-carousel"
          data-slide="prev"
          v-if="similarDatasets.length > 1"
          >
          <img src="../../assets/icon/icon-caretleft.svg" />
          <span class="sr-only">Previous</span>
        </button>
        <ol class="carousel-indicators similar-datasets-carousel-indicators">
          <li
            v-for="(similarDataset, i) in similarDatasets"
            :key="i"
            data-target="#similar-datasets-carousel"
            v-bind="{ 'data-slide-to': i }"
            :class="{ active: i === 0}">
          </li>
        </ol>
        <button class="by-btn-tertiary-medium-light carousel-control-next"
          type="button"
          data-target="#similar-datasets-carousel"
          data-slide="next"
          v-if="similarDatasets.length > 1"
          >
          <img src="../../assets/icon/icon-caretright.svg" />
          <span class="sr-only">Next</span>
        </button>
    </div>

    </div>

  </div>
</template>

<script>
  import { mapActions, mapGetters } from 'vuex';
  import { has } from 'lodash-es';
  import { PvDataInfoBox, getTranslationFor, helpers } from '@piveau/piveau-hub-ui-modules';

  const appendCurrentLocaleToURL = helpers.appendCurrentLocaleToURL;

  export default {
    name: 'datasetDetailsSimilarDatasetsCarousel',
    dependencies: 'DatasetService',
    components: {
      PvDataInfoBox,
    },
    data() {
      return {
        similarDatasetsFetched: false,
        similarDatasetsPresent: false,
      };
    },
    computed: {
      // import store-getters
      ...mapGetters('datasetDetails', [
        'getKeywords',
        'getLanguages',
        'getSimilarDatasets',
        'getTitle',
      ]),
      similarDatasets() {
        return this.getSimilarDatasets;
      },
    },
    methods: {
      // import store-actions
      ...mapActions('datasetDetails', [
        'loadDatasetDetails',
        'loadSimilarDatasets',
        'loadSimilarDatasetDetails',
        'useService',
      ]),
      has,
      getTranslationFor,
      appendCurrentLocaleToURL,
      /**
       * Update all similar datasets with additional data
       */
      updateSimilarDatasets() {
        this.similarDatasets.forEach(this.getDatasetDetails);
      },
      /**
       * Get dataset details by id
       */
      getDatasetDetails(similarDataset) {
        this.loadSimilarDatasetDetails(similarDataset.id);
      },
    },
    created() {
      this.useService(this.DatasetService);
      this.$nextTick(() => {
        this.$Progress.start();
        this.loadDatasetDetails(this.$route.params.ds_id)
          .then(() => {
            this.loadSimilarDatasets({
              id: this.$route.params.ds_id,
              query: { limit: 3 }
            })
            .then((response) => {
              this.$nextTick(() => {
                this.updateSimilarDatasets();
                this.similarDatasetsFetched = true;
                this.similarDatasetsPresent = response.length > 0;
              });
              this.$Progress.finish();
            })
            .catch(() => {
              this.similarDatasetsFetched = true;
              this.$Progress.fail();
            });
          })
          .catch(() => {
            this.$Progress.fail();
            this.$router.replace({
              name: 'NotFound',
              query: { locale: this.$route.query.locale, dataset: this.$route.params.ds_id },
            });
          });
      });
    },
  };
</script>

