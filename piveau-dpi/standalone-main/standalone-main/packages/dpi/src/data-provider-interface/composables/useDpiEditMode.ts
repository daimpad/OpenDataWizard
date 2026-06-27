import { useRoute } from "vue-router";
import { useDpiContext, type DpiContext } from "./useDpiContext";
import { computed, type MaybeRefOrGetter, type Ref, ref, toRef, toValue, watch, watchEffect } from "vue";
import { useStore } from "vuex";
import { useRuntimeEnv } from "../../composables/useRuntimeEnv";
import { useAsyncState } from "@vueuse/core";

import axios from 'axios'
import { useDpiSimpleLoader } from "./useDpiSimpleLoader";

export interface UseDpiLoaderParams {
  enabled?: MaybeRefOrGetter<boolean>
  hubSearchUrl: string
}

  /**
   * Use this composable in the DataProviderInterface to fetch a dataset from the Hub API and
   * convert it to a form input via localStorage. This composable is used when the user navigates to a dataset
   * by clicking on the "Edit" on the DatasetDetails page or in DraftsPage
   *
   *
   * @param dpiContext - The DPI context as returned by `useDpiContext`.
   * @returns The computed properties described above.
   */
export function useDpiEditMode(dpiContext: MaybeRefOrGetter<DpiContext>) {
  const route = useRoute();
  const store = useStore();
  const env = useRuntimeEnv();

  const editQuery = computed(() => {
    return toValue(dpiContext)?.edit?.enabled ?? route.query.edit === 'true';
  });

  const editIdQuery = computed(() => {
    return toValue(dpiContext)?.edit?.id ?? route.query.id;
  });

  const editFromDraft = computed(() => {
    return toValue(dpiContext)?.edit?.fromDraft ?? route.query.fromDraft ?? store.getters["auth/getIsDraft"];
  });

  // For legacy purposes, set editmode to false if editQuery is false
  if (!!editQuery.value) {
    localStorage.setItem('dpi_editmode', 'false');
    store.dispatch("auth/setIsEditMode", false);
  }

  const requestParams = computed(() => {
    const isDraft = editFromDraft.value;
    const token = store.getters["auth/getUserData"]?.rtpToken;
    const property = route.params.property;
    const id = route.params.id;

    const endpoint = isDraft
      ? `${env.api.hubUrl}drafts/datasets/${editIdQuery.value}.jsonld?catalogue=${route.query.catalog}`
      : route.params.property === "catalogues"
      ? `${env.api.hubUrl}catalogues/${route.query.catalog}.jsonld`
      : `${env.api.hubUrl}datasets/${editIdQuery.value}.jsonld?useNormalizedId=true`;
    return { endpoint, token, property, id };
  });

  const { state: jsonld, execute, isLoading, error } = useAsyncState(
    async () => {
      const res = await  axios.get(requestParams.value.endpoint, {
        headers: {
          Accept: "application/ld+json",
          Authorization: `Bearer ${requestParams.value.token}`,
        },
      })

      return res.data;
      // return (await generalHelper.fetchLinkedData(requestParams.value.endpoint, requestParams.value.token));
    },
    undefined,
    {
      immediate: false,
    }
  );

  watchEffect(() => {
    if (jsonld.value) {
      console.log('the json.value  = ', jsonld.value);
    }
  })

  const inEditModeAndRptAvailable = computed(() => !!editIdQuery.value && !!requestParams.value.token)
  watch(inEditModeAndRptAvailable, () => {
    if (!inEditModeAndRptAvailable.value) return;
    const isDraft = editFromDraft.value;
    store.dispatch("auth/setIsEditMode", true);
    store.dispatch("auth/setIsDraft", isDraft);
    execute();
  }, { immediate: true });


  // const { result, isMaterialized } = useDpiLoader(jsonld, {
  //   enabled: inEditModeAndRptAvailable,
  //   hubSearchUrl: env.api.baseUrl
  // })
  const { isReady: isSimpleLoaderReady, result, isMaterialized, errors: simpleLoaderErrors } = useDpiSimpleLoader(jsonld, {
    enabled: inEditModeAndRptAvailable,
    hubSearchUrl: env.api.baseUrl
  })

  // Ensure dpiStore contains a specification before rendering the input page.
  // Maybe it's not needed but better safe than sorry.
  const isReady = computed(() => {
    if (!toValue(dpiContext).edit?.enabled) return true
    return !!store.getters["dpiStore/getSpecificationName"] && !isLoading.value && isSimpleLoaderReady.value && isMaterialized.value;
  });

  const isEditMode = computed(() => {

  })

  return {
    inEditModeAndRptAvailable,
    isReady,
    result,
    isSimpleLoaderReady,
    isMaterialized,
    fetchError: error as Ref<Error>,
    parsingErrors: simpleLoaderErrors,
    isLoading,
    jsonld,
  }
}

export function useEditModeInfo() {
  const route = useRoute();
  const store = useStore();
  const dpiContext = useDpiContext();
  const editIdQuery = computed(() => {
    return toValue(dpiContext)?.edit?.id ?? route.query.id;
  });

  const editFromDraft = computed(() => {
    return toValue(dpiContext)?.edit?.fromDraft ?? route.query.fromDraft ?? store.getters["auth/getIsDraft"];
  });

  const editQuery = computed(() => {
    return toValue(dpiContext)?.edit?.enabled ?? route.query.edit;
  });

  const isEditMode = computed(() => {
    return !!toValue(dpiContext).edit?.enabled;
  });

  return {
    editIdQuery,
    editFromDraft,
    editQuery,
    isEditMode
  }
}
