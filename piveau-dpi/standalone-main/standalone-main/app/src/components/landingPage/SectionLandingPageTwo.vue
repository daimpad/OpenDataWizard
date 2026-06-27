<template>
  <SectionLandingPageBase class="bayern-landing-page__section bayern-landing-page__section--two">
    <template #title>
      Datensätze zum Loslegen
    </template>
    <div class="additional-datasets-cards">
      <SuggestedDatasetCard
        class="w-100"
        v-for="(ds) in interestingDatasets"
        :key="ds.id"
        :id="ds.id"
        :title="ds.title"
        :description="ds.description"
        :formats="ds.formats"
        :catalog="ds.catalog"
        dark
      ></SuggestedDatasetCard>
    </div>
    <div v-if="false" class="fancy-card-container">
      <FancyCard v-for="(card, idx) in cardData" :key="idx">
        <template #icon>
          <img :src="card.icon" :alt="card.title"/>
        </template>

        <template #title>
          <span class="by-heading-4 text-by-blue-10">
            {{ card.title }}
          </span>
        </template>

        <template #subtitle>
          <span class="by-copy-small-bold text-by-neutral-30">
            {{ card.subtitle }}
          </span>
        </template>

        <p class="by-copy-small-regular text-by-neutral-30">
          {{ card.description }}
        </p>

        <template #footer>
          <router-link class="by-btn-tertiary-medium-dark by-copy-large-semibold link-to-dataset"
                       :to="card.to">Zum Datensatz
          </router-link>
        </template>
      </FancyCard>
    </div>
    <div class="stats-container">
      <div class="stats-stack">
        <div v-for="(stat, key) of statsModel" :key="key"  class="stat">
          <span class="stat-value by-heading-1">{{ stat.count }}</span>
          <span class="stat-desc by-copy-large-bold">{{ stat.description }}</span>
        </div>
      </div>
    </div>

    <DataVizualisation class="data-viz w-100" :data="vizData"/>
    <div class="w-100">
      <h3 class="by-heading-4 text-by-blue-20 text-center mb-2">Daten über unsere Schnittstellen
        nutzen</h3>
    </div>

    <div class="container-fluid api-container">
      <div class="row align-items-center">
        <div class="col-12 col-lg api-documentation">
          <p class="by-copy-large-regular text-by-neutral-10 m-0">Neben der Datensuche stellen wir
            drei Schnittstellen
            frei zur Verfügung – für Suchen und zur Datenverarbeitung sowie den direkten Zugriff
            über unsere
            SPARQL-Schnittstelle.
          </p>
          <a href="/api" class="by-btn-secondary-large-dark">
            Zur API-Dokumentation</a>
        </div>
      </div>
    </div>
  </SectionLandingPageBase>
</template>

<script lang="ts">
import { defineComponent, getCurrentInstance, ref, type PropType } from 'vue';
import SectionLandingPageBase from './SectionLandingPageBase.vue';
import DataVizualisation from './DataVizualisation.vue';
import FancyCard from '../FancyCard.vue';

import { useStatsQuery } from '@/composables/landingPageQueries';
import { DEFAULT_STATS, DEFAULT_VISUALIZATION_STATS } from '@/utils/constants';
import type { InterestingDataset } from '@/utils/fetchThreeRandomDatasets';
import { useRuntimeEnv } from '@/composables/useRuntimeEnv';
import SuggestedDatasetCard from '../SuggestedDatasetCard.vue';

export default defineComponent({
  components: {
    SectionLandingPageBase,
    FancyCard,
    DataVizualisation,
    SuggestedDatasetCard,
  },
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    interestingDatasets: {
      type: Array as PropType<InterestingDataset[]>,
      default: () => []
    }
  },
  setup(props) {

    const env = useRuntimeEnv();
    const baseUrl = env.api.baseUrl;

    // Use relative path for SPARQL queries to prevent CORS issues,
    // just make sure the proxy is configured correctly
    const sparqlUrl = '/api/sparql/';

    const { statsModel, vizData } = useStatsQuery({
      hubSearchUrl: `${baseUrl}search?q=&filter=dataset&limit=0`,
      sparqlUrl: `${sparqlUrl}?default-graph-uri=&query=prefix+dcat%3A++%3Chttp%3A%2F%2Fwww.w3.org%2Fns%2Fdcat%23%3E+%0D%0Aselect+count%28%3Fd%29+where+%7B%0D%0A++%3Fd+a+dcat%3ADistribution+.%0D%0A%7D+&format=application%2Fsparql-results%2Bjson&timeout=0&signal_void=on`,
      defaultStats: DEFAULT_STATS,
      defaultVisualizationStats: DEFAULT_VISUALIZATION_STATS,
    });

    const cardData = ref([
      {
        icon: '/static/tree.svg',
        title: 'Bäume in der Stadt Würzburg',
        subtitle: 'Stadt Würzburg',
        description: 'Infomationen zu über 40.000 Bäumen auf öffentlichen Flächen.',
        to: {
          name: 'DatasetDetailsDataset',
          params: {'ds_id': 'baumkataster_stadt_wuerzburg-wuerzburg'}
        }
      },
      {
        icon: '/static/solar-panel.svg',
        title: 'Freiflächen für Photovoltaik',
        subtitle: 'Bayerisches Landesamt für Umwelt',
        description: 'Freiflächen, die die Eigentümer für die Errichtung von Photovoltaik-Anlagen für Dritte zur Verfügung stellen.',
        to: {
          name: 'DatasetDetailsDataset',
          params: {'ds_id': 'c16717f4-a6fa-4632-bd68-87cb73ce992a'}
        }
      },
      {
        icon: '/static/dublin-castle.svg',
        title: '3-D Gebäudemodelle',
        subtitle: 'Landesamt für Digitalisierung, Breitband und Vermessung',
        description: 'Blockmodell anhand 15 Attributen im Level of Detail 2 (LoD2) für bayerische Gebäude.',
        to: {
          name: 'DatasetDetailsDataset',
          params: {'ds_id': 'c6a7f0be-35b7-43a5-8dc2-458fe913821c'}
        }
      }
    ]);

    const showVideo = ref(true);

    return {
      statsModel,
      cardData,
      vizData,
      showVideo,
    }
  }
});

</script>

<style lang="scss" scoped>
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';

.additional-datasets-cards {
    display: flex;
    max-width: 1280px;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--Spacer-4, 24px);
    align-self: stretch;
    margin-bottom: 10rem;

    @include media-breakpoint-up(lg) {
      display: flex;
      max-width: 1280px;
      flex-direction: row;
      align-items: flex-start;
      align-content: flex-start;
      gap: 24px var(--Spacer-4, 24px);
      align-self: stretch;
      flex-wrap: wrap;
    }
  }

.bayern-landing-page__section--two {
  .fancy-card-container {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: flex-start;
    gap: 1.5rem;
    // align-self: stretch;
    flex-wrap: wrap;
    background-color: transparent;
    margin-bottom: 10rem;

    // For small screens and up, apply horizontal scroll behavior
    @include media-breakpoint-up(sm) {
      overflow-x: auto;
      flex-wrap: nowrap;
      justify-content: flex-start;
      align-items: flex-start;
      // Hide scrollbars in a somewhat cross-browser way
      scrollbar-width: none; // Firefox

      &::-webkit-scrollbar {
        display: none; // Chrome, Safari, Edge
      }

      .fancy-card {
        flex: 0 0 auto;
        width: 336px;
      }
    }

    // for lg, set width of fancy-cards to 289px
    @include media-breakpoint-up(lg) {
      .fancy-card {
        flex: 1 1 0;
      }
    }

    @include media-breakpoint-up(xl) {
      & {
        justify-content: space-around;
      }
    }

  }

  .stats-container {
    display: grid;
    place-items: center;
    width: 100%;
  }

  .stats-stack {
    padding: 0rem 2rem;
    // grid with three rows and one column
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: repeat(3, 1fr);
    gap: 3rem;
    width: 100%;

    @include media-breakpoint-up(sm) {
      // grid with two rows and two columns
      grid-template-columns: repeat(2, 1fr);
      grid-template-rows: repeat(2, 1fr);
      // horizontal gap should be 0, vertical gap should be 3rem
      gap: 3rem 0;
    }

    @include media-breakpoint-up(lg) {
      // grid with one row and three cols, all spaced evenly like justify-between
      // meaning the first element should be left, the middle ones centered, and the last ones right
      grid-template-columns: repeat(3, 1fr);
      grid-template-rows: 1fr;
      gap: 0;

      .stat:nth-child(1) {
        justify-self: start;
      }

      .stat:nth-child(2) {
        justify-self: center;
      }

      .stat:nth-child(3) {
        justify-self: end;
      }
    }
  }


  .stat {
    display: flex;
    padding: 0rem 1.5rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;

    .stat-value,
    .stat-desc {
      color: var(--blue-blue-20-tertiary, #D4EDFC);
      margin: 0;
    }
  }

  .data-viz {
    margin-bottom: 10rem;
  }

  .img-container {
    position: relative;
    height: 22rem;
    width: 100%;
    margin-bottom: 2rem;
  }

  .api-container {
    margin-bottom: 10rem;
  }

  .img-apis {
    height: 22rem;
    min-width: 25rem;
    left: 50%;
    margin-left: -12.5rem;
    position: absolute;
  }

  .img-api {
    width: 64px;
    height: 64px;
  }

  .api-documentation {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2rem;
  }

  .link-to-dataset {
    display: flex;
    align-items: center;
    gap: 0.5rem;

    &::after {
      content: '';
      width: 24px;
      height: 24px;
      background: url("../../assets/img/caret-circle-right.svg") no-repeat;
    }
  }

  .video-container {
    margin-bottom: 5rem;
    width: 100%;

    .fake-video {
      display: grid;
      place-items: center;
      width: 100%;
      height: 169px;
      background: var(--by-blue-blue-80, #0172AD);

      & > div {
        // play button
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--by-blue-blue-20, #D4EDFC);
      }

      @include media-breakpoint-up(sm) {
        height: 293px;
      }

      @include media-breakpoint-up(lg) {
        height: 524px;
      }
    }
  }
}
</style>
