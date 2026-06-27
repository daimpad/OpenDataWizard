<template>
  <div class="d-flex flex-column align-items-start px-0 dsd-header-title">
    <button v-if="previousRoute" class="dsd-back-button by-btn-tertiary-small-light mb-5" @click="$router.push(previousRoute)" type="button"
      aria-label="Previous">
      <img class="" src="../../assets/icon/icon-caretleft.svg" />
      <span>Zurück</span>
    </button>
    <div class="d-lg-block">
      <span class="by-copy-small-regular text-by-blue-100">
        {{ $t('message.metadata.dataset').toUpperCase() }}
      </span>
      <div v-if="isHvdDataset" class="ml-2 by-chip by-chip-static">
        High Value Dataset (HVD)
      </div>
    </div>
    <h1 v-if="getTitle" class="by-heading-2 text-by-blue-100 dataset-details-title" data-cy="dataset-title">{{ getTranslationFor(getTitle,
      $route.query.locale, getLanguages) }}</h1>
    <h1 v-else class="by-heading-2 text-by-blue-100 dataset-details-title" data-cy="dataset-title">{{ getID }}</h1>
  </div>
</template>

<script>
import { getTranslationFor } from "@piveau/piveau-hub-ui-modules";
import { mapGetters } from "vuex";

export default {
  name: "DatasetDetailsHeaderTitle",
  props: {
    previousRoute: {
      type: String,
      default: ''
    }
  },
  methods: {
    getTranslationFor,
  },
  computed: {
    ...mapGetters('datasetDetails', [
      'getLanguages',
      'getTitle',
      'getID',
      'getIsHvd',
    ]),
    previousRouteName() {
      return this.previousRoute ? this.previousRoute.name : null;
    },
    isHvdDataset() {
      return this.getIsHvd || false;
    }
  },
};
</script>

<style scoped lang="scss"></style>

