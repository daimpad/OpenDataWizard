<template>
  <BayernDatasetsPage :fixed-catalog-filter="catalogId" />
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue'
import BayernDatasetsPage from '../BayernDatasetsPage.vue'

export default defineComponent({
  components: {
    BayernDatasetsPage,
  },
  setup () {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const injectedPresenceData = inject('presenceData') as any
    if (!injectedPresenceData) {
      throw new Error('[OdpDatasetSearchPage] presenceData not provided. Make sure to wrap the component in a <PresencePage> component')
    }

    const {
      catalogId,
    } = injectedPresenceData

    return {
      catalogId,
    }
  }
})
</script>

<style scoped>

</style>
