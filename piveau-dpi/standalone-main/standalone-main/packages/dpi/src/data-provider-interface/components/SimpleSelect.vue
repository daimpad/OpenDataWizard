<template>
  <div class="formkitProperty">
    <h4>{{ props.context.label }}</h4>
    <div class="position-relative formkitCmpWrap">

      <div v-if="isReady">

        <!-- Hidden input that contains the actual catalog id. We rely on that to pass the catalog id to backend later -->
        <FormKit v-show="false" v-model="selectedCatalogId" :name="`${props.context.node.name}`" type="text" />

        <!-- User-facing input that displays the name of the selected catalog -->
        <FormKit
          class="autocompleteInputfield"
          v-model="selectedCatalogTitle"
          :placeholder="props.context.attrs.placeholder"
          type="text" 
          validation="required"
          mandatory="true"
          :validation-messages="{
            required: props.context.attrs.placeholder,
          }" :name="`${props.context.node.name}__displayedValue`"
          :disabled="isDisabled"
          @click="showList = !showList"
        />
        <ul ref="dropdownList" v-show="showList" class="autocompleteResultList selectListFK catSelectList">
          <li v-for="match in authorizedCatalogs" :key="match" @click="setvalue(match)"
            class="p-2 border-b border-gray-200 data-[selected=true]:bg-blue-100 choosableItemsAC">{{
              match.name }}
          </li>
          <li v-if="authorizedCatalogs.length === 0" v-for="idMatch in userCats" :key="idMatch" @click="setvalue(idMatch)"
            class="p-2 border-b border-gray-200 data-[selected=true]:bg-blue-100 choosableItemsAC">{{
              idMatch }}
          </li>
        </ul>
      </div>
    </div>
  </div>

</template>
<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useStore } from 'vuex';
import { getNode } from '@formkit/core'
import { onClickOutside } from '@vueuse/core'
import axios from 'axios'
import { useRuntimeEnv } from "../../composables/useRuntimeEnv.ts";
import { useI18n } from 'vue-i18n';
import { useAsyncState } from '@vueuse/core';
import { FormKit } from '@formkit/vue';
import { useDpiContext } from '../composables/useDpiContext.ts';

const props = defineProps({
  context: Object
})

const dpiContext = useDpiContext()
const { locale, fallbackLocale } = useI18n({ useScope: 'global' })

const userCats = computed(() => store.getters['auth/getUserCatalogIds']);
const showList = ref(false)
const store = useStore()
const dropdownList = ref(null)
const env = useRuntimeEnv()
const selectedCatalogTitle = ref('')
const selectedCatalogId = ref('')
const hasMounted = ref(false)

onClickOutside(dropdownList, event => showList.value = false)

const setvalue = async (e) => {
  if (e.id) {
    selectedCatalogId.value = e.id
    selectedCatalogTitle.value = e?.name || e.id
  } else {
    selectedCatalogId.value = e
    selectedCatalogTitle.value = e
  }

  showList.value = false
}

const { execute: filterCatList, state: catalogListData, isReady: isQueryReady, error } = useAsyncState(async () => {
  const catalogListData = await axios.get(env.api.baseUrl + 'search?filter=catalogue&limit=1000')
  return catalogListData
}, { data: { result: { results: [] } } }, { immediate: false })

// Wait until everything mounted and loaded tu ensure that the catalog list is available and selected catalog from edit mode is available
const isReady = computed(() => hasMounted.value && !!catalogListData.value && isQueryReady.value)

watch(error, () => {
  console.error(error.value)
})

const hasResults = computed(() => {
  return catalogListData?.value?.data?.result?.results?.length > 0
})

const hasOneResultOnly = computed(() => {
  return hasResults && catalogListData?.value?.data?.result?.results?.length === 1
})

/**
 * Computes list of users' authorized catalogs in { id: string; name: string } format
 */
const authorizedCatalogs = computed(() => {
  if (!hasResults.value || !isQueryReady.value) return []

  const allCatalogs = catalogListData.value?.data?.result?.results || []
  const authorizedCatalogs = allCatalogs.filter(catalog => userCats.value?.includes(catalog.id))

  // map to { id: string; name: string } pairs
  const authorizedCatalogsDataModel = authorizedCatalogs.map((catalog) => {
    const id = catalog?.id || ''
    let title = ''

    if (!catalog?.title) title = id
    else if (typeof catalog?.title === 'string') title = catalog?.title
    else if (typeof catalog?.title === 'object') title =
      catalog?.title[locale.value]
      || catalog?.title[fallbackLocale.value]
      || Object.values(catalog?.title)?.[0]
      || id

    return { id, name: title || id }
  });
  return authorizedCatalogsDataModel
})

watch(hasOneResultOnly, (yes) => {
  if (yes) {
    const result = catalogListData.value.data.result.results[0]
    setvalue({ id: result.id, name: result.name })
  }
}, { immediate: true })

onMounted(async () => {
  // When editing, we can restore the selected catalog id immediately, but we rely on fetching from hub-search for its catalog title.
  // So we need to wait until everything is fetched before proceeding further.
  await filterCatList()
  await nextTick()
  const catalogIdToLoadForEdit =
    dpiContext.value.edit?.catalog
    || getNode?.('catalog')?.value
    || getNode?.('dcat:catalog')?.value
    || getNode?.('dct:catalog')?.value
    || undefined
  const maybeFoundCatalogFromQuery = authorizedCatalogs.value?.find(item => item.id === catalogIdToLoadForEdit)
  if (maybeFoundCatalogFromQuery) {
    setvalue({ id: maybeFoundCatalogFromQuery.id, name: maybeFoundCatalogFromQuery.name })
  }
  await nextTick()
  hasMounted.value = true
});

// Disable select option if in edit mode
// Note: Decision is made due to a backend limitation that causes dataset duplicates to occur when changing a catalog
const isDisabled = computed(() => {
  const isInEditMode = dpiContext.value.edit?.enabled

  // If duplicate, do not disable select option so that they can duplicate datasets into different catalogs
  // todo: code debt due to code replication; usage of localStorage kind of weird here -> see DraftsPage for more on this.
  // We use localStorage to track if the intent is to duplicate or not
  const isDuplicate = localStorage?.getItem('dpi_duplicate')

  return isInEditMode && !isDuplicate
})

</script>
<style>
.catSelectList {
  width: 97.3% !important;
  margin: 0 1rem;
}

.selectListFK {

  max-height: 20rem;
  overflow: overlay;
  overflow-x: hidden;
}
</style>
