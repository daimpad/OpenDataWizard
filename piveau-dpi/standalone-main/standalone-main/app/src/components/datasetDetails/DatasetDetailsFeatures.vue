<template>
    <div class="flex-column dsd-features">
      <dataset-details-keywords
        class="dsd-keywords-feature"
        v-if="showObjectArray(getKeywords) && keywordsisVisible"
        :showKeyword="showKeyword"
      />
      <dataset-details-categories-key
        class="dsd-categories-feature"
        v-if="showObjectArray(getCategories)"
        :trackGoto="trackGoto"
      />
      <dataset-details-subject
       class="dsd-subject-feature"
        v-if="showObjectArray(getSubject)"
      />

      <!-- Documentation -->
      <dataset-details-pages
        class="dsd-pages-feature"
        v-if="showObjectArray(getPages)"
        :pages="pages"
        :increaseNumDisplayedPages="increaseNumDisplayedPages"
        :nonOverflowingIncrementsForPages="nonOverflowingIncrementsForPages"
        :trackGoto="trackGoto"
      />

      <dataset-details-visualisations
        class="dsd-visualisations-feature"
        v-if="showObjectArray(getVisualisations)"
        :trackGoto="trackGoto"
      />

      <dataset-details-data-services
        class="dsd-data-services-feature"
        v-if="showObjectArray(getDataServices)"
        :getDataServices="getDataServices"
        :nonOverflowingIncrementsForPages="nonOverflowingIncrementsForPages"
        :increaseNumDisplayedPages="increaseNumDisplayedPages"
        :trackGoto="trackGoto"
      >
        <template #data-service-description="{ dataService }">
          <table class="table table-borderless table-responsive" role="tablist">
            <tbody>
              <tr
                :key="key"
                v-for="({key, label}) in
                  ['applicableLegislation', 'contactPoint', 'page', 'rights'].map((v, i) => {
                    return {
                      key: v,
                      label: ['Anwendbare Gesetzgebung', 'Kontakt', 'Webseite', 'Zugangsrechte'][i]
                    }
                  })
              ">
                <td class="d-block by-caption pb-0">{{ label }}</td>
                <td class="d-block">
                  <template v-if="key === 'applicableLegislation'">
                    <div v-for="url in dataService.applicableLegislation">
                      <a :href="url" target="_blank" rel="noopener noreferrer">{{ url }}</a>
                    </div>
                  </template>
                  <template v-else-if="key === 'contactPoint'">
                    <div v-for="contactPoint in dataService.contactPoint">
                      <div>{{ contactPoint.name }}</div>
                      <div>{{ contactPoint.type }}</div>
                      <!-- email -->
                      <div v-if="contactPoint.email">
                        <a :href="'mailto:' + contactPoint.email">{{ contactPoint.email }}</a>
                      </div>
                      <div v-for="url in dataService.contactPoint.url">
                        <a :href="url" target="_blank" rel="noopener noreferrer">{{ url }}</a>
                      </div>
                    </div>
                  </template>
                  <template v-else-if="key === 'page'">
                    <div v-for="url in dataService.page">
                      <a :href="url.resource" target="_blank" rel="noopener noreferrer">{{ url.resource }}</a>
                    </div>
                  </template>
                  <template v-else-if="key === 'rights'">
                    <div v-for="rights in dataService.rights">
                      {{ rights.label }}
                    </div>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </template>
      </dataset-details-data-services>

      <dataset-details-is-used-by
        class="dsd-is-used-by"
        v-if="showObject(getExtendedMetadata)"
      />

      <dataset-details-relations
        class="dsd-relations-feature"
        v-if="showArray(getRelations)"
      />

      <!-- Dissable Map -->
      <!-- Warning: if enabled, it may load cookies due to it loading external map resources -->
      <dataset-details-map
        class="dsd-map-feature"
        v-if="showObjectArray(getSpatial) && false"
      />
    </div>
  </template>

  <script>
  import {
    DatasetDetailsKeywords,
    DatasetDetailsSubject,
    DatasetDetailsPages,
    DatasetDetailsVisualisations,
    DatasetDetailsDataServices,
    DatasetDetailsIsUsedBy,
    DatasetDetailsRelations,
    DatasetDetailsMap,
    // DatasetDetailsCategoriesKey
  } from "@piveau/piveau-hub-ui-modules"

  import DatasetDetailsCategoriesKey from "./DatasetDetailsCategoriesKey.vue"
  import {mapGetters} from "vuex";
  import {has, isEmpty} from "lodash";

  export default {
    name: "DatasetDetailsFeatures",
    components: {
      DatasetDetailsMap,
      DatasetDetailsCategoriesKey,
      DatasetDetailsRelations,
      DatasetDetailsIsUsedBy,
      DatasetDetailsDataServices,
      DatasetDetailsVisualisations,
      DatasetDetailsPages,
      DatasetDetailsSubject,
      DatasetDetailsKeywords,
    },
    props: {
      getKeywords: Array,
      pages: Object,
      increaseNumDisplayedPages: Function,
      nonOverflowingIncrementsForPages: Function,
      showKeyword: Function,
      trackGoto: Function,
      showObjectArray: Function,
      // Checks if data is an array and not empty
      showArray: {
        type: Function,
        default: (data) => Array.isArray(data) && data.length > 0
      },
      showObject: Function
    },
    data() {
      return {
        keywordsisVisible: (this.$env.content.datasetDetails.keywords.isVisible === false) ? false : true,
      }
    },
    computed: {
      ...mapGetters('datasetDetails', [
        'getSubject',
        'getPages',
        'getVisualisations',
        'getDistributions',
        'getExtendedMetadata',
        'getRelations',
        'getCategories',
        'getSpatial'
      ]),
      getDataServices() {
        if (this.getDistributions) {
          const accessServiceList = this.getDistributions
            .filter(distribution => has(distribution, 'accessService') && !isEmpty(distribution.accessService))
            .map(distribution => ({
              endpoint_url: distribution.accessService[0].endpoint_url,
              title: distribution.accessService[0].title,
              description: distribution.accessService[0].description,
              availability: has(distribution.accessService[0], 'availability') ? distribution.accessService[0].availability : {},   // field added for DCAT-AP.de
              applicableLegislation: distribution.accessService[0].applicable_legislation,
              contactPoint: distribution.accessService[0].contact_point,
              page: distribution.accessService[0].page,
              rights: distribution.accessService[0].rights,
              hvdCategory: distribution.accessService[0].hvdCategory,
            }));
          const uniqueAccessServiceList = [...new Map(
            accessServiceList
              .filter(accessService => accessService?.endpoint_url?.length)
              .map(accessService => [
                accessService.endpoint_url[0], accessService,
              ]),
          ).values()];
          return uniqueAccessServiceList;
        }
        return [{}];
      },
    },
  }
  </script>
  <style lang="scss">

  </style>
