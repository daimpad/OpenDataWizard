<script setup>
import { useAsyncState, useDebounce } from '@vueuse/core'
import axios from 'axios'
import $ from 'jquery'
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { useRuntimeEnv } from '../../composables/useRuntimeEnv.ts'
import AppLink from '../../widgets/AppLink.vue'

import ButtonV3 from './ui/ButtonV3.vue'
import TableRow from './ui/TableRowV3.vue'

const env = useRuntimeEnv()
const store = useStore()
const router = useRouter()

const isDuplication = localStorage.getItem('dpi_duplicate')
const duplicatedID = ref('')

let userCatIDList = computed(() => store.getters['auth/getUserCatalogIds'])

const modalProps = ref({
  loading: false,
  message: '',
  confirm: () => null,
})

// TODO locale should be dynamic! Currently its only in german.
const getUserDrafts = computed(() => store.getters['auth/getUserDrafts'].sort((a, b) => (a.title.de ?? '').localeCompare(b.title.de ?? '', 'de', { sensitivity: 'base', numeric: true })));
console.log(getUserDrafts);

const getUserData = computed(() => store.getters['auth/getUserData'])
const token = computed(() => getUserData.value.rtpToken)

const setIsDraft = value => store.dispatch('auth/setIsDraft', value)
const updateUserDrafts = () => store.dispatch('auth/updateUserDrafts')
const showSnackbar = payload => store.dispatch('snackbar/showSnackbar', payload)

// ========= Async state management for published datasets

async function getPublishedDatasets(catalogues) {
  const promises = catalogues.map((catalogue) => {
    return axios.get(`${env.api.baseUrl}search?facets={%22catalog%22:[%22${catalogue}%22]}&filters=dataset&includes=title,modified,id,description,catalog&limit=1000`)
  })
  const results = await Promise.all(promises)
  // TODO locale should be dynamic! Currently its only in german.
 let sortedResults = results.flatMap(result => result.data.result.results).sort((a, b) => (a.title.de ?? '').localeCompare(b.title.de ?? '', 'de', { sensitivity: 'base', numeric: true }));

  return sortedResults
}

const {
  state: publishedDatasets,
  execute: loadPublishedDatasets,
  isLoading: isLoadingPublishedDatasets,
  error,
} = useAsyncState(async () => {
  return getPublishedDatasets(userCatIDList.value)
}, [], { immediate: false })

watch(userCatIDList, () => {
  if (userCatIDList.value?.length > 0)
    loadPublishedDatasets()
}, { immediate: true })

watch(error, () => {
  console.error(error)
  showSnackbar({
    message: error.message,
    color: 'error',
  })
})

// Artifically delay loading state reduce flashing UI
const loading = useDebounce(isLoadingPublishedDatasets, 500)

// ===========================================

// function createLinkedMetricsURL(id, catalog, format) {
//   return {
//     path: `/dpi/draft/${id}.${format}`,
//     query: {
//       useNormalizedId: true,
//       locale: router.currentRoute.value.query.locale,
//       catalogue: catalog,
//     },
//   }
// }

// watch(userCatIDList, (newValue, oldValue) => {
// if (newValue.length > 0 && newValue.length != oldValue.length) {

//   getpublishedDatasets(newValue);
// }
// });
// watch(publishedDatasets, (newValue) => {

// if (newValue.length > 0) {
//   console.log(publishedDatasets);

//   publishedDatasets = newValue
// }
// });

// function handleEdit(id, catalog) {
//   setIsDraft(true)
//   localStorage.removeItem('dpi_duplicate')
//   router.push({ name: 'DataProviderInterface-Edit', params: { catalog, property: 'datasets', id }, query: { locale: router.currentRoute.value.query.locale } }).catch(() => { })
// }

// async function handleDelete(id, catalog) {
//   await doRequest('auth/deleteUserDraftById', { id, catalog })
//   $('#modal').modal('hide')
//   showSnackbar({
//     message: 'Draft gelöscht',
//     variant: 'success',
//   })
// }

// async function handlePublish(id, catalog) {
//   await doRequest('auth/publishUserDraftById', { id, catalog })
//   $('#modal').modal('hide')
//   showSnackbar({
//     message: 'Dataset veröffentlicht',
//     variant: 'success',
//   })
//   router.push({ name: 'DatasetDetailsDataset', params: { ds_id: id }, query: { locale: router.currentRoute.value.query.locale } }).catch(() => { })
//   setTimeout(() => {
//     localStorage.removeItem('dpi_duplicate')
//     router.go()
//   })
// }

// function handleConfirmPublish(id, catalog) {
//   modalProps.value.message = 'Dataset veröffentlichen'
//   modalProps.value.confirm = () => handlePublish(id, catalog)
//   $('#modal').modal('show')
//   localStorage.removeItem('dpi_duplicate')
// }

// function handleConfirmDelete(id, catalog) {
//   modalProps.value.message = 'Entwurf löschen'
//   modalProps.value.confirm = () => handleDelete(id, catalog)
//   $('#modal').modal('show')
//   localStorage.removeItem('dpi_duplicate')
// }

// function handleConfirmDuplication(id, catalog) {
//   setIsDraft(true)
//   localStorage.setItem('dpi_duplicate', true)
//   router.push({ name: 'DataProviderInterface-Edit', params: { catalog, property: 'datasets', id }, query: { locale: router.currentRoute.value.query.locale } }).catch(() => { })
// }

// async function doRequest(action, payload) {
//   modalProps.value.loading = true
//   try {
//     await store.dispatch(action, payload)
//   }
//   catch (ex) {
//     showSnackbar({
//       message: ex.message,
//       color: 'error',
//     })
//   }
//   finally {
//     modalProps.value.loading = false
//   }
// }
</script>

<template>
  <div class="dpiV3_dpiHome V3-typography">
    <div class="dpiV3_homeHeadWrap">
      <h2 class="dpiV3_tableHeader">
        Ihre Datensätze
      </h2>
      <p v-if="publishedDatasets.length === 0 && getUserDrafts.length === 0" class="copy-large-semi-bold landingText">
        Willkommen im Data Provider Interface ✨ <br>Beginnen Sie mit der Erstellung
        Ihres ersten Datensatzes!
      </p>

      <div class="interactionButtonsDPIHome">
        <AppLink
          :to="{ name: 'DataProviderInterface-Input', query: { locale: 'de', edit: false }, params: { property: 'datasets' } }"
        >
          <ButtonV3 button-text="Neuen Datensatz erstellen" size="large" />
        </AppLink>
      </div>
    </div>

    <div class="dpiV3_table" v-if="publishedDatasets.length > 0 || getUserDrafts.length > 0">
      <div v-if="loading" class="loading-screen">
        <p>Lädt Daten...</p>
      </div>
      <div v-else class="dpiV3_innerTable">
        <div class="dpiV3_tableHeadWrap">
          <div class="dpiV3_thWrap">
            <div scope="col">
              <span>Datensätze</span>
            </div>
            <div scope="col" class="dpiV3_statusHead">
              <span>Status</span>
            </div>
          </div>
          <div class="dpiV3_blankCell" />
        </div>
        <div>
          <TableRow
            v-for="dataset in getUserDrafts" :id="dataset.id" :key="dataset.id" :data-cy="dataset"
            :catalogue="dataset.catalog" :text="dataset.title.de || dataset.title.en"
            :date="dataset.modified ? new Date(dataset.modified).toDateString() : '-'" draft="true" :dataset="dataset"
          />
          <TableRow
            v-for="dataset, index in publishedDatasets" :key="dataset.id"
            :text="dataset.title?.de || dataset.title?.en || 'Kein Titel in englisch oder deutsch vohanden'"
            :date="dataset.modified ? new Date(dataset.modified).toDateString() : '-'" :dataset="dataset"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
body {
  background: var(--neutral5, #FAFAFB) !important;
}

.dpiV3_table {
  display: flex;
  max-width: 1280px;
  width: 100%;
  padding: 24px;
  /* flex-direction: column; */
  /* align-items: center; */
  align-self: center;
  background: var(--neutral0, #FFFF);
  background: var(--neutral0, #FFFF);

  table {

    max-width: 1280px;
    padding: 24px;
    flex-direction: column;
    align-items: flex-start;
    align-self: stretch;
  }

  thead {
    height: 64px;
    max-width: 1232px;
    padding: var(--Spacing-2, 8px);
    flex-direction: column;
    align-items: flex-start;
    gap: var(--Spacing-2, 8px);
    align-self: stretch;
  }
}

.dpiV3_statusHead {
  min-width: 176px;
  max-width: 200px;
  padding: 0px var(--Spacing-2, 8px);
}
.interactionButtonsDPIHome{
  width: fit-content;
}
.dpiV3_thWrap {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1 0 0;
  align-self: stretch;

  div {
    color: var(--Colour-neutral-Neutral60, #687178);
    /* Headlines/Caption */
    font-family: Inter;
    font-size: 12px;
    font-style: normal;
    font-weight: 700;
    line-height: 150%;
    /* 18px */
    text-transform: uppercase;

    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1 0 0;
    align-self: stretch;

  }
}

.dpiV3_innerTable {
  width: -webkit-fill-available;
  width: -moz-available;
}

.dpiV3_tableHeader {
  color: var(--Colour-blue-Blue100, #003F6F);
  /* Headlines/Headline-2 */
  /* font-family: "Space Grotesk"; */
  font-size: 48px;
  font-style: normal;
  font-weight: 700;
  line-height: 56px;
  /* 116.667% */
}

.dpiV3_homeHeadWrap {
  display: flex;
  max-width: 1280px;
  width: 100%;
  flex-direction: column;
  /* align-items: flex-start; */
  gap: var(--Spacing-6, 48px);
  align-self: center;

}

.dpiV3_tableHeadWrap {
  display: flex;
  height: 64px;
  max-width: 1232px;
  padding: var(--Spacing-2, 8px);
  /* flex-direction: column; */
  align-items: flex-start;
  gap: var(--Spacing-2, 8px);
  align-self: stretch;
  border-bottom: 1px solid var(--Colour-neutral-Neutral30, #D5D7DA);

  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1 0 0;
  align-self: stretch;
}

.dpiV3_dpiHome {
  display: flex;

  padding: var(--Spacing-11, 120px) var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: center;
  gap: var(--Spacing-10, 80px);
  background: var(--neutral5, #FAFAFB);
}

.dpiV3_blankCell {
  display: flex;
  width: 80px;
  padding: 0px 8px;
  justify-content: flex-end;
  align-items: center;
  gap: 24px;
  align-self: stretch;
}

.dpiV3_dropdownWrapper {
  width: unset !important;
}
.landingText{
 color: var(--neutral-100, #0B1A25);

}
</style>
