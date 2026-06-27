<template>
  <div id="bayern-presence-page">
    <router-view />
  </div>
</template>

<script lang="ts">
import { useSafeAxiosFetch } from '@/composables/landingPageQueries';
import { useRuntimeEnv } from '@/composables/useRuntimeEnv';
import { computed, defineComponent, provide, watch, unref } from 'vue'
import { useAsyncState } from '@vueuse/core'
import { z } from 'zod';
import axios from 'axios';
import { useCatalogId } from '@/composables/useCatalogId';
import { helpers } from '@piveau/piveau-hub-ui-modules';
import { useLocale } from '@/composables/useLocale';

export default defineComponent({
  setup() {
    const catalogId = unref(useCatalogId()) || '';
    const env = useRuntimeEnv();

    const baseUrl = env?.api?.baseUrl;
    if (!baseUrl) {
      throw new Error('[LandingPage] $env.api.baseUrl not found');
    }

    const maybeTrailingSlash = baseUrl.endsWith('/') ? '' : '/';

    const {
      data: catalog,
      isLoading: isLoadingCatalog,
      isError: errorCatalog,
    } = useSafeAxiosFetch(z.any(), `${baseUrl}${maybeTrailingSlash}catalogues/${catalogId}`)

    const locale = useLocale('de');
    const enhancedCatalog = computed(() => {
      const translate = helpers.getTranslationFor

      return {
        ...catalog.value?.result,
        title: translate(catalog.value?.result?.title, 'en' || locale.value, 'de'),
        description: translate(catalog.value?.result?.description, 'en' || locale.value, 'de'),
      }
    })

    const interestingDatasetIds = computed(() => catalog?.value?.result?.catalogueInterestingDatasets || [])

    const {
      execute: fetchInterestingDatasets,
      state: interestingDatasets,
      isLoading: isLoadingInterestingDatasets,
      isReady: isReadyInterestingDatasets,
      error: errorInterestingDatasets
    } = useAsyncState(async () => {
      return Promise.all(interestingDatasetIds.value.map(async (id: string) => {
        try {
          const response = await axios.get(`${baseUrl}${maybeTrailingSlash}datasets/${id}`)
          return response.data?.result
        } catch (ex) {
          console.error(ex)
          return false
        }
      }))
    }, undefined, {
      immediate: false,
    })

    watch(interestingDatasetIds, (value: string[]) => {
      if (value.length > 0)
        fetchInterestingDatasets()
    }, { immediate: true })

    const params = {
      q: '',
      filter: 'dataset',
      limit: '3',
      page: '0',
      sort: 'relevance+desc,+modified+desc,+title.de+asc',
      facetOperator: 'AND',
      facetGroupOperator: 'AND',
      dataServices: 'false',
      includes: 'id,title.de,description.de,languages,modified,issued,catalog.id,catalog.title,catalog.country.id,distributions.id,distributions.format.label,distributions.format.id,distributions.license,categories.label,publisher',
      facets: JSON.stringify({
        open_data_presence: [`${catalogId}.bydata`],
      }),
    }
    const encodedParams = new URLSearchParams(params).toString();

    const { data: datasets } = useSafeAxiosFetch(z.any(), `${baseUrl}${maybeTrailingSlash}/search?${encodedParams}`);

    const interestingDatasetsFallback = computed(() => {
      return datasets?.value?.result?.results || []
    })

    const resolvedInterestingDatasets = computed(() => {
      const combinedDatasets = [...interestingDatasets.value?.filter(Boolean) || [], ...interestingDatasetsFallback.value?.filter(Boolean) || []]

      const uniqueDatasets = []
      const seenIds = new Set()

      for (const dataset of combinedDatasets) {
        if (!seenIds.has(dataset.id)) {
          uniqueDatasets.push(dataset)
          seenIds.add(dataset.id)
        }
      }

      return uniqueDatasets.splice(0, 3)
    })

    const enhancedInterestingDatasets = computed(() => {
      const translate = helpers.getTranslationFor
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      return resolvedInterestingDatasets.value?.map((dataset: any) => {
        return {
          ...dataset,
          title: translate(dataset.title, 'en' || locale.value, 'de'),
          description: translate(dataset.description, 'en' || locale.value, 'de'),
        }
      })
    })

    const isReady = computed(() => !!catalog.value && !!resolvedInterestingDatasets.value)

    provide('presenceData', {
      isReady,
      catalogId,
      catalog,
      interestingDatasets: resolvedInterestingDatasets,
      enhancedCatalog,
      enhancedInterestingDatasets,
      errorCatalog,
      errorInterestingDatasets,
    })

    return {
      enhancedCatalog,
      enhancedInterestingDatasets,
      interestingDatasetIds,
      interestingDatasets,
      interestingDatasetsFallback,
      resolvedInterestingDatasets,
    }
  }
})

</script>

<style lang="scss">

#bayern-presence-page.content {
  margin-top: 0 !important;
  margin-bottom: 0 !important;
  padding: 0 !important;
}

</style>
