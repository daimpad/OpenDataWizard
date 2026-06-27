<template>
  <div class="bayern-odp-landing-page p-0">
    <section
      class="bayern-odp-landing-page__section-container bayern-odp-landing-page__section-container--one position-relative"
      :class="{
        'bayern-odp-landing-page__section-container--one-fallback-hero': useFallbackHero
      }"
    >
      <div
        class="bayern-odp-landing-page__section bayern-odp-landing-page__section--one"
        :class="{
          'bayern-odp-landing-page__section--one-fallback-hero': useFallbackHero
        }"
      >
        <div class="d-flex flex-column">
          <div v-if="!useFallbackHero && !!assets.icon">
            <img class="presence-icon" :src="assets.icon" :alt="`${catalogTitle} Wappen`" @error="handleImgError">
          </div>
          <h1 class="bayern-odp-landing-page__title by-heading-2 text-by-blue-100 mt-4 mb-0">Entdecke<br/>{{ catalogTitle }}<br/>by data</h1>
        </div>
        <span class="hero-description by-copy-large-regular">Hier finden Datenbegeisterte freie Datensätze und weitere Informationen zu diesem Datenbereitsteller.</span>
        <router-link :to="{ name: 'CatalogueNDetailsDatasetsSearch'}" class="by-btn-primary-large-light link-to-dataset">Datensätze durchsuchen <PhCaretRight class="ml-2" /></router-link>
      </div>
      <div class="hero-splash">
        <div class="griddy-grid-fallback d-none d-lg-block" v-if="useFallbackHero">
          <div class="griddy-grid-fallback__primary" v-if="useFallbackHero">
            <svg width="338" height="386" viewBox="0 0 338 386" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M113 33L169 1V65M113 33L57 65M113 33L169 65M113 33V97M57 65L1 97M57 65L113 97M57 65V129M1 97L57 129M1 97V161M113 97L57 129M113 97L169 65M113 97L169 129M113 97V161M113 97L1 161M57 129L1 161M57 129L113 161M57 129V193M1 161L57 193M1 161V225M1 161L113 225M1 161V289M169 65V129M169 129L113 161M169 129V193M113 161L57 193M113 161L169 193M113 161V225M57 193L1 225M57 193L113 225M57 193V257M1 225L57 257M1 225V289M113 225L57 257M113 225L169 193M113 225L169 257M113 225V289M113 225L1 289M113 225L225 289M57 257L1 289M57 257L113 289M57 257V321M1 289L57 321M1 289L113 353M169 193L225 225M169 193V257M169 257L113 289M169 257L225 225M169 257L225 289M169 257V321M113 289L57 321M113 289L169 321M113 289V353M57 321L113 353M113 353L169 321M113 353L169 385M113 353L225 289M169 321L225 289M169 321L225 353M169 321V385M169 385L225 353M225 225L281 257M225 225V289M281 257L225 289M281 257L337 289L281 321M281 257V321M225 289L281 321M225 289V353M281 321L225 353" stroke="#67C5F0"/>
            </svg>
          </div>
          <div class="griddy-grid-fallback__secondary" v-if="useFallbackHero">
            <svg width="338" height="194" viewBox="0 0 338 194" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M113 33L57 65M113 33L169 1M113 33L169 65M113 33V97M113 33L1 97M113 33L225 97M57 65L1 97M57 65L113 97M57 65V129M1 97L57 129M1 97L113 161M169 1L225 33M169 1V65M169 65L113 97M169 65L225 33M169 65L225 97M169 65V129M113 97L57 129M113 97L169 129M113 97V161M57 129L113 161M113 161L169 129M113 161L169 193M113 161L225 97M169 129L225 97M169 129L225 161M169 129V193M169 193L225 161M225 33L281 65M225 33V97M281 65L225 97M281 65L337 97L281 129M281 65V129M225 97L281 129M225 97V161M281 129L225 161" stroke="#AEDFF8"/>
            </svg>
          </div>
        </div>
        <div v-else class="masked-image-grid">
          <svg class="griddy-grid" width="506" height="514" viewBox="0 0 506 514" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M113 1L57 33M113 1L169 33M113 1V65M57 33L1 1V65M57 33L113 65M57 33V97M1 65L57 97M1 65V129M57 97L1 129M57 97L113 65M57 97L113 129M57 97V161M57 97L169 33M57 97L169 161M1 129L57 161M1 129V193M113 65L169 33M113 65L169 97M113 65V129M113 129L57 161M113 129L169 97M113 129L169 161M113 129V193M57 161L1 193M57 161L113 193M57 161V225M1 193L57 225M1 193V257M57 225L113 257M57 225V289M57 225L169 289M1 257L57 289M1 257V321M113 193L169 225M113 193V257M113 257L57 289M113 257L169 225M113 257L169 289M113 257V321M57 289L1 321M57 289L113 321M57 289V353M1 321L57 353M1 321V385M57 353L1 385M57 353L113 321M57 353L113 385M57 353V417M57 353L169 289M57 353L169 417M1 385L57 417M113 321L169 289M113 321L169 353M113 321V385M113 385L169 417M113 385V449M57 417L113 449M113 449L169 417M113 449L169 481M225 1L169 33M225 1L281 33M225 1V65M169 33L225 65M169 33V97M169 33L281 97M169 33V161M281 33L225 65M281 33V97M225 65L169 97M225 65L281 97M225 65V129M169 97L225 129M169 97V161M281 97V161M225 129L281 161M225 129V193M169 161L225 193M169 161V225M169 161L281 225M169 161V289M281 161L225 193M281 161V225M225 193L281 225M225 193V257M169 225L225 257M169 225V289M281 225L225 257M281 225L337 257M281 225V289M281 225L169 289M281 225L393 289M225 257L169 289M225 257L281 289M225 257V321M169 289L225 321M169 289V353M169 289L281 353M169 289V417M337 257L393 289M337 257V321M281 289L337 321M281 289V353M225 321L281 353M225 321V385M169 353L225 385M169 353V417M281 353L337 321M281 353L337 385M281 353V417M281 353L393 289M281 353L393 417M225 385L169 417M225 385L281 417M225 385V449M169 417L225 449M169 417V481M169 417L281 481M337 321L393 289M337 321L393 353M337 321V385M337 385L281 417M337 385L393 353M337 385L393 417M337 385V449M281 417L225 449M281 417L337 449M281 417V481M225 449L169 481M225 449L281 481M225 449V513M169 481L225 513M281 481L225 513M281 481L337 449M281 481L393 417M337 449L393 417M393 289L449 321M393 289V353M393 289L505 353M393 289V417M449 321L393 353M449 321L505 353M449 321V385M393 353L449 385M393 353V417M505 353L449 385" stroke="#AEDFF8"/>
          </svg>
          <div class="mask-group">
            <svg class="mask-polygon" width="0" height="0" viewBox="0 0 448 512" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <clipPath id="maskShape">
                  <path d="M224 0H448V384L224 512L0 384V128L224 0Z" fill="black"/>
                </clipPath>
              </defs>
            </svg>
            <img v-if="!!assets.hero" class="hero-img" :src="assets.hero" :alt="`Repräsentatives Bild aus ${catalogTitle}`" @error="handleImgError">
          </div>
        </div>
      </div>
    </section>

    <section :key="debouncedWindowWidth" v-if="!disableViz" class="bayern-odp-landing-page__section-container bayern-odp-landing-page__section-container--two">
      <div class="bayern-odp-landing-page__section bayern-odp-landing-page__section--two bg-by-neutral-10">
        <div class="viz-container">
          <div
            v-for="(chart) in visualisationSelection"
            :key="chart"
            class="dataviz"
            :class="{
              'dataviz--kfz': chart === 'KFZ',
              'dataviz--mobile': chart === 'Mobilfunk',
              'dataviz--energie': chart === 'Energie',
              'dataviz--bevoelkerung': chart === 'Bevoelkerung',
            }"
          >
            <div class="dataviz__header">
              <h5 class="dataviz__title by-heading-5 m-0 text-by-neutral-100">
                <template v-if="chart === 'KFZ'">Kraftfahrzeuge nach Antriebsart</template>
                <template v-if="chart === 'Mobilfunk'">Mobilfunknetzabdeckung</template>
                <template v-if="chart === 'Energie'">Strom aus erneuerbaren Energien nach Energieträgern [%]</template>
                <template v-if="chart === 'Bevoelkerung'">Bevölkerungsentwicklung</template>
              </h5>
              <div class="d-flex flex-column">
                <a :href="visualisationData[chart].link" target="_blank" class="by-copy-mini-regular text-by-neutral-60 align-self-start text-truncate mw-100">Aus bayernweitem Datensatz</a>
                <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" class="by-copy-mini-regular text-by-neutral-60 align-self-start text-truncate mw-100">
                  <template v-if="chart === 'KFZ'">CC BY 4.0 - Bayerisches Landesamt für Statistik</template>
                  <template v-if="chart === 'Mobilfunk'">CC BY - MIG, BMDV. Datenstand 01.07.2022</template>
                  <template v-if="chart === 'Energie'">CC BY - Bayerisches Landesamt für Umwelt, www.lfu.bayern.de</template>
                  <template v-if="chart === 'Bevoelkerung'">CC BY 4.0 - Bayerisches Landesamt für Statistik</template>
                </a>
              </div>
            </div>
            <EchartBevoelkerung class="dataviz__diagram" :key="chart" :data="visualisationData[chart]" v-if="chart === 'Bevoelkerung'" />
            <EchartMobile class="dataviz__diagram" :key="chart" :data="visualisationData[chart]" v-if="chart === 'Mobilfunk'" />
            <EchartEnergie class="dataviz__diagram" :key="chart" :data="visualisationData[chart]" v-if="chart === 'Energie'" />
            <EchartKfz class="dataviz__diagram" :key="chart" :data="visualisationData[chart]" v-if="chart === 'KFZ'" />
            <!-- <div class="dataviz__footer">
              <a :href="visualisationData[chart].link" target="_blank" rel="" class="dataviz__to-dataset by-btn-tertiary-small-light"><span>Zum gesamten Datensatz</span> <PhCaretRight /></a>
            </div> -->
          </div>
        </div>
      </div>
    </section>

    <section class="bayern-odp-landing-page__section-container bayern-odp-landing-page__section-container--three">
      <div class="bayern-odp-landing-page__section bayern-odp-landing-page__section--three">
        <div name="additional-datsets-header" class="d-flex flex-column">
          <h2 class="by-heading-2 text-by-blue-100">Empfohlene Datensätze</h2>
          <div class="mt-4">
            <p>Eine Auswahl spannender Datensätze – getroffen vom Bereitsteller.</p>
            <p>Auf der Suche nach mehr? <router-link :to="{ name: 'CatalogueNDetailsDatasetsSearch' }" class="by-link-light d-inline-flex flex-row"><span>Alle Datensätze anzeigen</span><PhArrowRight class="ml-2" /></router-link></p>
          </div>
        </div>
        <div class="additional-datasets-cards">
          <SuggestedDatasetCard
            v-for="(ds) in interestingDatasetsView"
            :key="ds.id"
            :id="ds.id"
            :title="ds.title"
            :description="ds.description"
            :formats="ds.formats"
            :catalog="ds.catalog"
            class="w-100"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, inject, ref, type ComputedRef } from 'vue';
import { useRoute } from 'vue-router';
import SuggestedDatasetCard from '@/components/SuggestedDatasetCard.vue';
import PhCaretRight from '~icons/ph/caret-right';
import PhArrowRight from '~icons/ph/arrow-right';
import TestEchart from '@/components/TestEchart.vue'
import EchartEnergie from '@/components/echarts/EchartEnergie.vue'
import EchartBevoelkerung from '@/components/echarts/EchartBevoelkerung.vue'
import EchartKfz from '@/components/echarts/EchartKfz.vue'
import EchartMobile from '@/components/echarts/EchartMobile.vue'
import { useLogoCase } from '@/composables/useLogoCase';
import { useDebounce, useWindowSize } from '@vueuse/core';

export default defineComponent({
  name: 'OdpLandingPage',
  components: {
    SuggestedDatasetCard,
    PhCaretRight,
    PhArrowRight,
    TestEchart,
    EchartEnergie,
    EchartBevoelkerung,
    EchartKfz,
    EchartMobile
  },
  setup() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const injectedPresenceData = inject('presenceData') as any
    if (!injectedPresenceData) {
      throw new Error('[OdpLandingPage] presenceData not provided. Make sure to wrap the component in a <PresencePage> component')
    }

    const route = useRoute();
    const forceFallbackHero = !!route?.query?.fallback
    const forceDisableDataViz = !!route?.query?.disableViz
    const forceMockViz = !!route?.query?.mockViz

    const {
      isReady,
      catalogId,
      // catalog,
      enhancedCatalog,
      // interestingDatasets,
      enhancedInterestingDatasets,
      errorCatalog,
      errorInterestingDatasets,
    } = injectedPresenceData


    const catalogTitle = computed(() => {
      return enhancedCatalog?.value?.title?.de || catalogId.split('-').map((word: string) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
    })

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const interestingDatasetsView: ComputedRef<{
      id: string;
      title: string;
      description: string;
      formats: string[];
      catalog: string;
    }[]> = computed(() => enhancedInterestingDatasets?.value?.map((ds: any) => {
      return {
        id: ds.id || '',
        title: ds.title || '',
        description: ds.description || '',
        catalog: typeof ds.publisher === 'string'
          ? ds.publisher
          : ds.publisher?.name?.de || ds.publisher?.name || '',
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        formats: [...new Set(ds.distributions?.map((dist: any) => dist?.format?.label || null).filter(Boolean)),]
      }
    }));

    // Image data
    const assets = computed(() => ({
      icon: enhancedCatalog?.value?.catalogueFavIcon?.[0] || undefined,
      hero: enhancedCatalog?.value?.catalogueBackground?.[0] || undefined,
    }))

    // Data visualization
    const chartTypes = ['Energie', 'KFZ', 'Bevoelkerung', 'Mobilfunk']
    const byChartTypeOnly = (chart: string) => chartTypes.includes(chart)
    const visualisationSelection = computed(() =>
      forceMockViz
        ? ['Mobilfunk', 'Energie']
        : (enhancedCatalog?.value?.visualisation_selection?.filter(byChartTypeOnly).sort((a: any, b: any) => {
          const order = ['KFZ', 'Mobilfunk', 'Energie', 'Bevoelkerung']
          return order.indexOf(a) - order.indexOf(b)
        }) || [])
      )
    const visualisationData = computed(() =>
    forceMockViz
      ? {
        "Energie": {
          "Wind": 0,
          "Solar": 22,
          "Biomasse": 26.5,
          "Wasser": 51.5,
          "Anteil": 12,
          "link": "https://www.karten.energieatlas.bayern.de/start/?c=677751,5422939&z=8.01&l=atkis,37864384-e4fe-47de-8227-619bd33e1eda&t=energie"
        },
        "KFZ": {
          "Jahr": [
            2018,
            2019,
            2020,
            2021,
            2022,
            2023
          ],
          "Benzin": [
            57050,
            59373,
            60391,
            56102,
            54044,
            53632
          ],
          "Diesel": [
            35355,
            34035,
            31774,
            29436,
            27638,
            27632
          ],
          "Hybrid": [
            2372,
            2848,
            6745,
            9239,
            11433,
            12873
          ],
          "Elektro": [
            210,
            972,
            928,
            2047,
            3252,
            4086
          ],
          "Anteil": {
            "Benzin": 54,
            "Diesel": 28,
            "Hybrid": 13,
            "Elektro": 4
          },
          "link": "https://open.bydata.de/datasets?query=kraftfahrzeugbestand&locale=de&catalog=lfstat&page=1&limit=10&categories=TRAN"
        },
        "Bevoelkerung": {
          "Jahr": [
            2013,
            2014,
            2015,
            2016,
            2017,
            2018,
            2019,
            2020,
            2021,
            2022,
            2023
          ],
          "Gesamt": [
            128045,
            129370,
            131405,
            132140,
            133974,
            135538,
            137408,
            136831,
            137314,
            138537,
            141515
          ],
          "Entwicklung": [
            1506,
            1325,
            2035,
            735,
            1834,
            1564,
            1870,
            -577,
            483,
            1223,
            2978
          ],
          "link": "https://open.bydata.de/datasets/12411-000-d?locale=de"
        },
        "Mobilfunk": {
          "2G": 100,
          "4G": 100,
          "5G": 98.7,
          "link": "https://open.bydata.de/datasets/6b0b713d-1a68-4a78-8730-83823d4407bf~~1?locale=de"
        }
      }
      : JSON.parse(enhancedCatalog?.value?.visualisation_data) || {}
    )

    // Error handling/states
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    function handleImgError(e: any) {
      console.error(e)
      isImgError.value = true
    }
    const isImgError = ref(false)
    const isNetworkError = computed(() => errorCatalog.value || errorInterestingDatasets.value)
    const useFallbackHero = computed(() =>
      forceFallbackHero ||
      !isReady.value ||
      isImgError.value ||
      !assets.value.hero
    )
    const disableViz = computed(() =>
      forceDisableDataViz ||
      visualisationSelection.value.length === 0 ||
      !visualisationData.value
    )

    const catalogTitleLogoCased = useLogoCase(catalogId)

    // Debounced window size change handling to re-render the charts
    const { width } = useWindowSize()
    const debouncedWindowWidth = useDebounce(width, 300)

    return {
      catalogTitle,
      catalogId,
      interestingDatasetsView,
      assets,
      handleImgError,
      isImgError,
      isNetworkError,
      useFallbackHero,
      disableViz,
      visualisationSelection,
      visualisationData,
      catalogTitleLogoCased,
      debouncedWindowWidth,
      enhancedInterestingDatasets,
    }
  }
});
</script>

<style lang="scss">
// Override Styles
.site-wrapper .content.bayern-landing-page {
  margin-top: 0 !important;
  margin-bottom: 0 !important;
}
</style>

<style scoped lang="scss">
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';
@import '../../styles/custom_theme.scss';

.bayern-odp-landing-page {
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  background: #fff;

  &__title {
    max-width: 560px;
  }

  &__section-container {
    width: 100%;
    display: grid;
    place-items: center;
    padding-left: 2rem;
    padding-right: 2rem;
    background-color: $by-neutral-5;
    overflow: hidden;

    &--one,
    &--two,
    &--three,
    &--four {
      padding-top: 7rem;
      padding-bottom: 7rem;
    }

    &--one {
      padding-top: 2.5rem;
      padding-bottom: 6rem;
    }

    &--one-fallback-hero {
      background: linear-gradient(78deg, rgba(0, 163, 255, 0.00) 42.14%, rgba(0, 163, 255, 0.04) 99.28%), #FAFAFB;
    }

    &--two {
      padding-top: 2rem;
      padding-bottom: 2rem;
      background-color: $by-neutral-10;
    }

    &--three {
      padding-top: 7rem;
      padding-bottom: 7rem;
    }

    &--four {
      padding-top: 2rem;
      padding-bottom: 2rem;
      background-color: $by-neutral-0;
    }
  }

  &__section {
    position: relative;
    background-color: $by-neutral-5;
    width: 100%;
    max-width: 1280px;
    margin-left: auto;
    margin-right: auto;

    &--one {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 40px;
      align-self: stretch;
      max-width: 1280px;
      z-index: 1;
      background: transparent;

      .hero-description {
        max-width: 100%;

        @include media-breakpoint-up(sm) {
          max-width: 50ch;
        }

        @include media-breakpoint-up(xl) {
          max-width: 60ch;
        }
      }
    }

    &--one-fallback-hero {
      background-color: transparent;
    }

    &--two {
      align-self: stretch;
      justify-content: center;
      align-items: center;
      display: flex;
      gap: 1.5rem;
      flex-direction: column;
      flex: none;
      flex-shrink: 0;
      overflow-x: auto;

      @include media-breakpoint-up(sm) {
        flex-direction: row;
        justify-content: start;
      }
    }

    &--three {
      display: flex;
      max-width: 1280px;
      flex-direction: column;
      align-items: flex-start;
      gap: 3rem;
      align-self: stretch;
    }

    &--four {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: var(--Border-Radius, 8px);
      align-self: stretch;
      background: var(--Neutral-0, #FFF);
    }

    .odb-ref {
      display: flex;
      max-width: 1280px;
      flex-direction: column;
      align-items: flex-start;
      gap: 16px;
      align-self: stretch;
    }

    // @include media-breakpoint-up(xl) {
    //   padding-left: 2rem;
    //   padding-right: 2rem;
    // }
  }

  .hero-splash {
    position: absolute;
    top: 0;
    left: 50%;
    width: 100%;
    max-width: 1920px;
    height: 100%;
    transform: translateX(-50%);
    margin-right: auto;
    margin-left: auto;
  }

  .presence-icon {
    height: 64px;
  }

  .additional-datasets-cards {
    display: flex;
    max-width: 1280px;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--Spacer-4, 24px);
    align-self: stretch;

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

  .masked-image-grid {
    display: none;
    position: absolute;
    right: -144px;
    top: -40px;
    width: 560px;
    height: 512px;
    z-index: 10;

    @include media-breakpoint-up(lg) {
      display: block;
    }

    @include media-breakpoint-up(xl) {
      right: -40px;
      top: -40px;
    }

    @media (min-width: 1440px) {
      right: 154px;
      top: -40px;
    }

    .griddy-grid {
      width: 504px;
      height: 512px;
      flex-shrink: 0;
    }

    .mask-group {
      position: absolute;
      // top: -33px;
      top: 0;
      margin-left: 112px;
      flex-shrink: 0;

      .mask-polygon {
        position: absolute;
        top: 0;
        // left: 112px;
        flex-shrink: 0;
      }

      .hero-img {
        width: 477px;
        height: 512px;
        object-fit: cover;
        -webkit-clip-path: url(#maskShape);
        clip-path: url(#maskShape);
      }
    }
  }

  .griddy-grid-fallback {
    &__primary {
      position: absolute;
      width: 336px;
      height: 384px;
      right: -8px;
      top: 42px;

      @include media-breakpoint-up(xl) {
        right: 96px;
        top: 42px;
      }

      @include media-breakpoint-up(xxl) {
        right: 290px;
        top: 42px;
      }
    }

    &__secondary {
      position: absolute;
      width: 336px;
      height: 192px;
      left: 304px;
      bottom: -112px;

      @include media-breakpoint-up(xl) {
        left: 408px;
        bottom: -112px;
      }

      @include media-breakpoint-up(xxl) {
        left: 602px;
        bottom: -112px;
      }
    }
  }

  .viz-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 1280px;
    width: 100%;
    flex: 1 0 0;

    @include media-breakpoint-up(sm) {
      flex-direction: row;
      width: 1280px;
      min-width: 1280px;
      gap: 1.5rem;
    }
  }

  .dataviz {
    flex: 0 0 auto;
    display: flex;
    gap: 1rem;
    flex-direction: column;
    width: 100%;
    height: 360px;
    padding: 1rem;
    background: white;
    border-radius: .5rem;

    &__header {
      display: flex;
      flex-direction: column;
      gap: 0;
      width: 100%;
    }

    &__title {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    &__diagram {
      position: relative;
      flex: 0 1 auto;
      width: calc(100% + 1rem);
      height: 100%;
      margin-left: -1rem;

      &--debug {
        display: grid;
        place-items: center;
        outline: 1px solid red;
      }

      &--mock {
        display: grid;
        place-items: center;
        background: #ebebeb;
      }
    }

    &__footer {
      display: flex;
      justify-content: flex-end;
      width: 100%;
      // height: 3.5rem;
    }

    &__to-dataset {
      display: flex;
      flex-wrap: nowrap;
      gap: .5rem;
      justify-content: center;
      align-items: center;
    }

    @include media-breakpoint-up(sm) {
      flex-shrink: 0;
      flex-grow: 0;
      flex-basis: 360px;
    }

    &--kfz {
      @include media-breakpoint-up(sm) {
        flex-basis: 367px;
        max-width: 367px;
      }
    }

    &--mobile {
      @include media-breakpoint-up(sm) {
        flex-basis: 360px;
        max-width: 360px;
      }
    }

    &--energie {
      @include media-breakpoint-up(sm) {
        flex-basis: 372px;
        max-width: 372px;
      }
    }

    &--bevoelkerung {
      @include media-breakpoint-up(sm) {
        flex-basis: 376px;
        max-width: 376px;
      }
    }
  }

  .dataviz-mock {
    flex: 0 1 auto;
    display: grid;
    place-items: center;
    width: 100%;
    height: 380px;
    min-width: 296px;
    min-height: 380px;
    border-radius: var(--Border-Radius, 8px);
    background: #FFF;
    padding: 1rem;

    > * {
      width: calc(100%);
      height: calc(100%);
    }

    @include media-breakpoint-up(sm) {
      flex-shrink: 0;
      flex-grow: 0;
      flex-basis: 360px;
    }

    &--kfz {
      @include media-breakpoint-up(sm) {
        flex-basis: 367px;
      }
    }

    &--mobile {
      @include media-breakpoint-up(sm) {
        flex-basis: 360px;
      }
    }

    &--energie {
      @include media-breakpoint-up(sm) {
        flex-basis: 437px;
      }
    }

    &--bevoelkerung {
      @include media-breakpoint-up(sm) {
        width: 376px;
      }
    }
  }
}
</style>
