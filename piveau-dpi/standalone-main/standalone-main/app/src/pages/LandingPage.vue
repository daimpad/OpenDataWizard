<template>
  <div class="bayern-landing-page p-0 text-white">
    <!-- Hero banner -->
    <SectionLandingPageOne
    />

    <!-- "Datensätze zum loslegen" -->
    <KeepAlive>
      <SectionLandingPageTwo
        :loading="isLoading"
        :interesting-datasets="interestingDatasets"
      />
    </KeepAlive>

    <!-- "Daten bereitstellen" -->
    <SectionLandingPageThree />

    <!-- "Ein besseres Bayern durch Daten" -->
    <SectionLandingPageFour />
  </div>
</template>

<script lang="ts">
// Landing Page Section Imports
import { defineComponent, nextTick, onMounted, ref, watch } from 'vue';
import SectionLandingPageOne from '@/components/landingPage/SectionLandingPageOne.vue';
import SectionLandingPageTwo from '@/components/landingPage/SectionLandingPageTwo.vue';
import SectionLandingPageThree from '@/components/landingPage/SectionLandingPageThree.vue';
import SectionLandingPageFour from '@/components/landingPage/SectionLandingPageFour.vue';
import { fetchThreeRandomDatsets } from '@/utils/fetchThreeRandomDatasets';
import { useRuntimeEnv } from '@/composables/useRuntimeEnv';
import { useAsyncState } from '@vueuse/core';

export default defineComponent({
  name: 'BayernLandingPage',
  components: {
    SectionLandingPageOne,
    SectionLandingPageTwo,
    SectionLandingPageThree,
    SectionLandingPageFour
  },
  setup() {
    const { api } = useRuntimeEnv();
    const { baseUrl } = api;

    const {
      isLoading,
      isReady,
      error: errorInterestingDatasets,
      state: interestingDatasets,
      execute
    } = useAsyncState(() => fetchThreeRandomDatsets(baseUrl), [], { resetOnExecute: false })

    // do execute every 1s
    // onMounted(() => {
    //   const a = setInterval(async () => {
    //     console.log('yy')
    //     await nextTick()
    //     await execute(1000)
    //   }, 1000)
    // })

    // watch(interestingDatasets, () => {
    //   console.log(interestingDatasets.value)
    //   console.log(interestingDatasets.value.map((i) => i.catalog))
    // })

    return {
      isLoading,
      isReady,
      interestingDatasets,
      errorInterestingDatasets,
      execute,
    }
  }
});
</script>

<style lang="scss">
// Bootstrap SCSS Imports
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';

// Override Styles
.site-wrapper .content.bayern-landing-page {
  margin-top: 0 !important;
  margin-bottom: 0 !important;
}

// Component Styles
.bayern-landing-page {
  position: relative;
  overflow: hidden;
  background: linear-gradient(180deg, #0B1A25 0%, #093150 71.31%, #003F6F 100%);

  &__section {
    position: relative;
    margin-bottom: 0;
    // z-index: 10;
    padding-left: 2rem;
    padding-right: 2rem;

    &--one,
    &--two,
    &--three,
    &--four {
      padding-top: 7rem;
      padding-bottom: 7rem;
    }

    &--one {
      padding-top: 10rem;
      padding-bottom: 10rem;
    }

    &--three {
      padding-bottom: 5rem;
    }

    &--four {
      padding-top: 5rem;
    }

    @include media-breakpoint-up(xl) {
      padding-left: 5rem;
      padding-right: 5rem;
    }
  }
}
</style>
