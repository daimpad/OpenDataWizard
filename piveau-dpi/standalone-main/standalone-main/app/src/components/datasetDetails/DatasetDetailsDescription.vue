<template>
  <div class="row dsd-description" data-cy="dataset-description">

    <ol v-if="!getLoading && similarDatasets && similarDatasets.length > 0 && similarDatasets[0].hasOwnProperty('id')"
      class="nav nav-tabs nav-justified col-12 p-0 dataset-details-tabs"
      id="dataset-details-tabs"
      role="tablist"
      >
      <li class="nav-item" role="presentation">
        <button class="nav-link dataset-details-tab active" id="about-tab" data-toggle="tab" data-target="#about-dataset" type="button" role="tab" aria-controls="about-dataset" aria-selected="true">
          <span class="m-0" data-alt-text="Info"><span>Über diesen Datensatz</span></span>
        </button>
      </li>
      <li v-if="similarDatasets.length > 0" class="nav-item" role="presentation">
        <button class="nav-link dataset-details-tab" id="similar-tab" data-toggle="tab" data-target="#similar-datasets" type="button" role="tab" aria-controls="similar-datasets" aria-selected="false">
          <span class="m-0" data-alt-text="Ähnliche"><span>Ähnliche Datensätze</span></span>
        </button>
      </li>
    </ol>

    <div  class="dsd-description-content tab-content" id="dataset-details-tab-content">
      <div class="tab-pane fade show active" id="about-dataset" role="tabpanel" aria-labelledby="about-tab">
        <h5 class="by-heading-4 text-secondary">Über diesen Datensatz</h5>
        <div v-if="getDatasetDescription" property="dc:description">
          <app-markdown-content
            v-if="$env.content.datasetDetails.description.enableMarkdownInterpretation"
            class="by-copy-small-regular text-by-neutral-60"
            :text="truncate(getDatasetDescription, datasetDescriptionLength)">
          </app-markdown-content>
          <p v-else style="word-wrap:break-word;">
            <span class="mr-2">{{ truncate(getDatasetDescription, datasetDescriptionLength) | stripHtml }}</span>
          </p>
          <pv-show-more
            v-if="isDatasetDescriptionExpanded || (datasetDescriptionLength < getDatasetDescriptionLength)"
            :label="isDatasetDescriptionExpanded? 'Weniger lesen' : 'Mehr lesen'"
            :upArrow="isDatasetDescriptionExpanded"
            :action="toggleDatasetDescription"
            class="row text-primary dsd-description-show-more"
          />
        </div>
        <div v-else class="col-12 by-copy-small-regular text-muted font-italic">
          <p style="word-wrap:break-word;">
            {{ $t('message.catalogsAndDatasets.noDescriptionAvailable') }}
          </p>
        </div>
      </div>

      <div v-if="similarDatasets && similarDatasets.length > 0"
        class="tab-pane fade"
        id="similar-datasets"
        role="tabpanel"
        aria-labelledby="similar-tab"
        >
        <dataset-details-similar-datasets-carousel />
      </div>
    </div>

  </div>
</template>

<script>
import { AppMarkdownContent, getTranslationFor, truncate } from "@piveau/piveau-hub-ui-modules";
import { mapGetters } from "vuex";
import filtersMixin from '../../mixins/filters.ts'
import DatasetDetailsSimilarDatasetsCarousel from './DatasetDetailsSimilarDatasetsCarousel'

export default {
  name: "DatasetDetailsDescription",
  components: {
    AppMarkdownContent,
    DatasetDetailsSimilarDatasetsCarousel
  },
  mixins: [filtersMixin],
  data() {
    return {
      isDatasetDescriptionExpanded: false,
      datasetDescriptionLength: 500,
      INITIAL_DATASET_DESCRIPTION_LENGTH: 500,
      MAX_DATASET_DESCRIPTION_LENGTH: 100000
    }
  },
  computed: {
    ...mapGetters('datasetDetails', [
      'getLoading',
      'getDescription',
      'getLanguages',
      'getSimilarDatasets'
    ]),
    getDatasetDescription() {
      return getTranslationFor(this.getDescription, this.$route.query.locale, this.getLanguages);
    },
    getDatasetDescriptionLength() {
      return this.getDatasetDescription ? this.getDatasetDescription.length : 0;
    },
    similarDatasets() {
      return this.getSimilarDatasets;
    }
  },
  methods: {
    truncate,
    toggleDatasetDescription() {
      this.isDatasetDescriptionExpanded = !this.isDatasetDescriptionExpanded;
      if (this.datasetDescriptionLength === this.INITIAL_DATASET_DESCRIPTION_LENGTH) this.datasetDescriptionLength = this.MAX_DATASET_DESCRIPTION_LENGTH;
      else this.datasetDescriptionLength = this.INITIAL_DATASET_DESCRIPTION_LENGTH;
    }
  }
}
</script>

<style lang="scss" scoped>
@media screen and (max-width: 576px) {
  [data-alt-text] > span {
    display: none;
  }

  [data-alt-text]::before {
    content: attr(data-alt-text);
  }
}
</style>
