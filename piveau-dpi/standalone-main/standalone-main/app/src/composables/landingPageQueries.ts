import { ref, shallowRef, computed } from 'vue';
import { type ComputedRef } from 'vue';
import axios from 'axios';
import * as z from 'zod';

export function useSafeAxiosFetch<T extends z.ZodTypeAny>(schema: T, url: string) {
  const data = shallowRef<z.infer<typeof schema> | null>(null);
  const isLoading = ref(false);
  const isError = ref(false);
  const error = ref<unknown | null>(null);

  (async () => {
    isLoading.value = true;

    try {
      const response = await axios.get(url);
      const parsedResponse = schema.parse(response.data);

      data.value = parsedResponse;
    } catch (e) {
      isError.value = true;
      error.value = e;
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  })();

  return {
    data,
    isLoading,
    isError,
    error
  };
}

/**
 * Returns a reactive object containing live statistics for the landing page
 */
export function useStatsQuery({
  hubSearchUrl,
  sparqlUrl,
  defaultStats,
  defaultVisualizationStats,
}: {
  hubSearchUrl: string,
  sparqlUrl: string,
  defaultStats: Readonly<{ numOfDatasets: number, numOfDistributions: number, numOfPublishers: number }>,
  defaultVisualizationStats: Readonly<{ value: number, short: string, long: string }[]>
}) {
  const simpleSparqlResponseSchema = z.object({
    results: z.object({
      bindings: z.array(
        z.object({
          'callret-0': z.object({
            type: z.any(),
            datatype: z.any(),
            value: z.string().transform((value) => parseInt(value, 10))
          })
        }).passthrough()
      )
    })
  });

  const simpleDatasetSchema = z.object({
    result: z.object({
      count: z.number().gte(0),
      facets: z.array(
        z.object({
          id: z.string(),
          items: z.array(z.any())
        }).passthrough()
      )
    })
  }).passthrough();

  const sparqlQuery =  useSafeAxiosFetch(simpleSparqlResponseSchema, sparqlUrl);
  const searchQuery = useSafeAxiosFetch(simpleDatasetSchema, hubSearchUrl);

  const numPublishers = computed(() => {
    if (searchQuery.data.value) {
      return searchQuery.data.value.result.facets.find(facet => facet.id === 'publisher')?.items.length || 0;
    }
    return null;
  });

  const numDatasets = computed(() => {
    if (searchQuery.data.value) {
      return searchQuery.data.value.result.count;
    }
    return null;
  });

  const numDistributions = computed(() => {
    if (sparqlQuery.data.value) {
      return sparqlQuery.data.value.results.bindings[0]['callret-0'].value;
    }
    return null;
  });

  const statsModel = computed(() => ({
    datasets: {
      count: numDatasets.value
        ? `${numDatasets.value}`
        : `${defaultStats.numOfDatasets}+`,
      description: 'Datensätze'
    },
    distributions: {
      count: numDistributions.value
        ? `${numDistributions.value}`
        : `${defaultStats.numOfDistributions}+`,
      description: 'Distributionen'
    },
    publishers: {
      count: numPublishers.value
        ? `${numPublishers.value}`
        : `${defaultStats.numOfPublishers}+`,
      description: 'Datenbereitsteller'
    }
  }))

  const vizData = computed(() => {
    if (searchQuery.data.value) {
      const categories = searchQuery.data.value.result.facets.find(facet => facet.id === 'categories')?.items;
      if (categories) {
        return categories.map((category) => ({
          value: category.count,
          short: category.id,
          long: category.title.de
        }));
      }
    }
    return [ ...defaultVisualizationStats ];
  }) as ComputedRef<{
    value: number;
    short: string;
    long: string;
  }[]>;


  return {
    statsModel,
    vizData,
    searchQuery,
    sparqlQuery,
    numPublishers,
    numDatasets,
    numDistributions,
  }
}
