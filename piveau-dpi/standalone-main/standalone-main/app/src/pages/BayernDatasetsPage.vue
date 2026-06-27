<template>
  <Datasets id="bayern-datasets-page" :fixed-catalog-filter="fixedCatalogFilter">
    <template #title>
      <span aria-hidden="true">
        <!-- Intentionally empty -->
      </span>
    </template>
    <template #content="{ loading, locale, datasets, datasetsCount, facets, availableFacets: getAvailableFacets }">
      <div class="row">
        <div class="col-12 col-md-4" aria-hidden="true">
          <!-- Intentionally empty -->
        </div>
        <section class="col-12 col-md-8 mb-0">
          <h1 v-show="!useOdpLayout" class="row page-title col-12 by-heading-2 text-secondary">{{ $t('message.header.navigation.data.datasets') }}</h1>
          <DatasetsFilters>
            <template #search-bar="{ query, bind, on, searchFn }">
              <form class="input-group" :class="{ 'input-group--odp': useOdpLayout }" @submit="searchFn(query)">
                <input
                  v-bind="bind"
                  @input="on.input($event.target.value || '')"
                  class="search-field form-control"
                  type="search"
                  :aria-label="($t('message.datasets.searchBar.placeholder'))"
                  :placeholder="($t('message.datasets.searchBar.placeholder'))"
                >
                <div class="input-group-append ml-2">
                  <button type="submit" class="by-btn-primary-large-light d-none d-sm-flex ds-input" @click="searchFn(query)">
                    Suchen
                  </button>
                  <button class="d-sm-none by-btn-primary-large-light search-button--mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                      <path d="M21.5306 20.4693L16.8365 15.7762C18.1971 14.1428 18.8755 12.0478 18.7307 9.92691C18.5859 7.80604 17.629 5.82265 16.0591 4.38932C14.4892 2.95599 12.4271 2.18308 10.3019 2.23138C8.17663 2.27968 6.15181 3.14547 4.64864 4.64864C3.14547 6.15181 2.27968 8.17663 2.23138 10.3019C2.18308 12.4271 2.95599 14.4892 4.38932 16.0591C5.82265 17.629 7.80604 18.5859 9.92691 18.7307C12.0478 18.8755 14.1428 18.1971 15.7762 16.8365L20.4693 21.5306C20.539 21.6003 20.6218 21.6556 20.7128 21.6933C20.8038 21.731 20.9014 21.7504 21 21.7504C21.0985 21.7504 21.1961 21.731 21.2871 21.6933C21.3782 21.6556 21.4609 21.6003 21.5306 21.5306C21.6003 21.4609 21.6556 21.3782 21.6933 21.2871C21.731 21.1961 21.7504 21.0985 21.7504 21C21.7504 20.9014 21.731 20.8038 21.6933 20.7128C21.6556 20.6218 21.6003 20.539 21.5306 20.4693ZM3.74997 10.5C3.74997 9.16495 4.14585 7.8599 4.88755 6.74987C5.62925 5.63984 6.68345 4.77467 7.91686 4.26378C9.15026 3.75289 10.5075 3.61922 11.8168 3.87967C13.1262 4.14012 14.3289 4.78299 15.2729 5.727C16.2169 6.671 16.8598 7.87374 17.1203 9.18311C17.3807 10.4925 17.247 11.8497 16.7362 13.0831C16.2253 14.3165 15.3601 15.3707 14.2501 16.1124C13.14 16.8541 11.835 17.25 10.5 17.25C8.71037 17.248 6.99463 16.5362 5.72919 15.2707C4.46375 14.0053 3.75195 12.2896 3.74997 10.5Z" fill="#FAFAFB"/>
                    </svg>
                  </button>
                </div>
              </form>
            </template>
            <template #filters-tabs>
              <div class="container-fluid p-0 mx-0 mx-md-auto w-100 w-md-auto sort-buttons d-flex align-items-end justify-content-between">
                <div class="row m-0 d-flex align-items-center w-100">
                  <div class="col-12 col-md order-md-1 order-2 mb-4 mb-md-0 px-0 px-md-auto">
                    <div class="d-flex align-items-center justify-content-between text-neutral-content by-copy-small-bold search-results-count-message pt-0 pt-md-2 px-0 px-md-2">
                      {{ loading ? $t('message.datasets.loadingMessage') : `${datasetsCount.toLocaleString('fi')}
                                    ${$t('message.datasets.countMessage')}` }}
                      <button
                        class="by-btn-secondary-medium-light by-copy-large-bold d-flex ds-input d-md-none"
                        @click="isFacetsSidebarVisible = !isFacetsSidebarVisible"
                        >
                        Filter
                      </button>
                    </div>
                  </div>
                  <div class="datasets-filters-tabs-container col-12 col-md order-md-2 order-1 px-0 px-md-auto">
                    <DatasetsFiltersTabs :use-sort="true" :use-catalogs="false" :locale="$route.query.locale" />
                  </div>
                </div>

              </div>
            </template>

          </DatasetsFilters>
          <!-- <div class="datasets-found alert alert-primary mt-3 d-flex flex-row" role="status"
            :class="{ 'alert-danger': datasetsCount <= 0 && !loading }">
            <div>
              {{ loading ? $t('message.datasets.loadingMessage') : `${datasetsCount.toLocaleString('fi')}
                            ${$t('message.datasets.countMessage')}` }}
            </div>
            <div class="loading-spinner ml-3" v-if="loading"></div>
          </div> -->
          <div>
            <SelectedFacetsOverview v-if="facets" :selected-facets="facets" :available-facets="getAvailableFacets">
              <template #default="{ index, facet, facetId, findFacetTitle, removeSelectedFacet }">
                <button
                  :key="index"
                  class="by-chip by-chip-interactive by-chip-interactive--cross mx-1"
                  @click="removeSelectedFacet(facet.field, facetId)"
                  :title="findFacetTitle(facet.field, facetId)"
                >
                  <template v-if="facet.field === 'is_hvd'">{{ facetId === 'true' ? $t('message.metadata.yes') : $t('message.metadata.no') }}</template>
                  <template v-else>{{ truncate(findFacetTitle(facet.field, facetId), 20) }}</template>
                </button>
              </template>
            </SelectedFacetsOverview>
          </div>
        </section>
      </div>

      <div class="row datasets-facets-list-container">
        <DatasetsFacets
          class="bayern-facets bayern-facets--desktop col-4 mt-3 mt-md-0 px-0 d-none d-md-block"
          :available-facets="getAvailableFacets"
          :fixed-catalog-filter="fixedCatalogFilter"
        >
        <template #facet="{ field }">
          <div v-if="field.id === 'is_hvd'" class="list-group w-100 radio-facet" role="group" aria-labelledby="hvd">
            <facet-title
              title="Nur High-Value Datasets"
              tooltip="Nur High-Value Datasets"
              title-id="hvd"
            />
            <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
              <div class="custom-control custom-switch">

                <input type="checkbox" class="custom-control-input" id="hvdSwitch" v-model="hvdModel">
                <label class="custom-control-label pl-3" for="hvdSwitch">Nur HVD</label>
              </div>
            </div>
          </div>
        </template>
        </DatasetsFacets>
        <DatasetsFacets
          class="bayern-facets bayern-facets--mobile col-12 col-md-3 mt-3 px-0 d-md-none"
          v-show="isFacetsSidebarVisible"
          :available-facets="getAvailableFacets"
          :fixed-catalog-filter="fixedCatalogFilter"
        ></DatasetsFacets>
        <div class="col-12 col-md-8">
          <BayernDatasetList
            :loading="loading"
            :datasets="datasets"
            :locale="locale"
            :messages="datasetResultMessages"
          />
        </div>
      </div>
    </template>
  </Datasets>
</template>

<script>
import { defineComponent } from 'vue';
import {
  Datasets,
  DatasetsFacets,
  DatasetList,
  DatasetsFilters,

  FacetTitle,

  PvDataInfoBox,
  PvDataInfoBoxFooter,
  PvDataInfoBoxFormats,

  SelectedFacetsOverview,
  DatasetsFiltersTabs,
} from '@piveau/piveau-hub-ui-modules'

import BayernDatasetList from '@/components/BayernDatasetList.vue';
import { useHvdFacet } from '@/composables/useHvdFacet';

export default defineComponent({
  props: {
    fixedCatalogFilter: {
      type: String,
      default: '',
    },
    useOdpLayout: {
      type: Boolean,
      default: true,
    }
  },
  components: {
    Datasets,
    DatasetsFacets,
    DatasetList,
    DatasetsFilters,
    PvDataInfoBox,
    PvDataInfoBoxFooter,
    PvDataInfoBoxFormats,
    SelectedFacetsOverview,
    DatasetsFiltersTabs,
    BayernDatasetList,
    FacetTitle,
  },
  data() {
    return {
      isFacetsSidebarVisible: false,
    };
  },
  computed: {
    datasetResultMessages() {
      return {
        updated: this.$t('message.metadata.updated'),
        category: this.$t('message.dataupload.datasets.subject.label'),
        creator: 'Bereitsteller',
        license: this.$t('message.metadata.license'),
      };
    }
  },
  methods: {
    truncate(str, n) {
      if (!str || typeof str !== 'string') {
        return '';
      }
      return (str.length > n) ? str.slice(0, n) + '…' : str;
    },
  },
  setup() {
    const { hvdModel } = useHvdFacet();

    return {
      hvdModel,
    }
  }
})
</script>

<style lang="scss" scoped>
  // specified in styles/by/_bayern-datasets-page.scss
</style>
