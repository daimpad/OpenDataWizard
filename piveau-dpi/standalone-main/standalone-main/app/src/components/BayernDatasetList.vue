<template>
  <DatasetList v-if="!loading" id="bayern-dataset-results" as="div" v-slot="{ dataset, index }" :datasets="datasets" :locale="locale || 'en'">
    <PvDataInfoBox
      :key="dataset.id"
      :to="`/datasets/${dataset.id}`"
      :src="dataset.src"
      :dataset="dataset.dataset"
      :description-max-length="1000"
      :data-cy="`dataset@${dataset.id}`"
      class="bayern-data-info-box"
    >
      <template #header>
        <div data-cy="dataset-title" class="dataset-info-box-header bayern-data-info-box__header">
          <h2 class="card-title">{{ dataset.dataset.title }}</h2>
        </div>
      </template>

      <template #right="{ formats }">
        <small class="d-block font-weight-bold"> Dateiformate </small>
        <PvDataInfoBoxFormats :formats="formats.map(({ id, label, resource }) => ({ id, label: id ? label : 'Unbekannt', resource }))"></PvDataInfoBoxFormats>
      </template>

      <template #footer>
        <div class="w-100 dataset-info-box-footer bayern-data-info-box__footer">
          <div class="bayern-data-info-box__metadata bayern-data-info-box__metadata--updated px-2 w-100"><div class="bayern-data-info-box__footer-caption">{{ messages.updated }}</div><div class="bayern-data-info-box__footer-data-value">{{ footerData[index].updated || '—' }}</div></div>
          <div class="bayern-data-info-box__metadata bayern-data-info-box__metadata--category px-2 w-100"><div class="bayern-data-info-box__footer-caption">{{ messages.category }}</div><div class="bayern-data-info-box__footer-data-value">{{ footerData[index].category || '—' }}</div></div>
          <div class="bayern-data-info-box__metadata bayern-data-info-box__metadata--creator px-2 w-100"><div class="bayern-data-info-box__footer-caption">{{ messages.creator }}</div><div class="bayern-data-info-box__footer-data-value">{{ footerData[index].creator || '—' }}</div></div>
          <div class="bayern-data-info-box__metadata bayern-data-info-box__metadata--license px-2 w-100"><div class="bayern-data-info-box__footer-caption">{{ messages.license }}</div><div class="bayern-data-info-box__footer-data-value">{{ footerData[index].license || '—' }}</div></div>
        </div>
      </template>
    </PvDataInfoBox>
  </DatasetList>
</template>

<script>
import { defineComponent } from 'vue';
import {
  DatasetList,
  PvDataInfoBox,
  PvDataInfoBoxFormats,
} from '@piveau/piveau-hub-ui-modules'

export default defineComponent({
  props: ['loading', 'datasets', 'locale', 'messages'],
  components: {
    DatasetList,
    PvDataInfoBox,
    PvDataInfoBoxFormats,
  },
  computed: {
    footerData() {
      return this.datasets.map(dataset => {
        const categories = [...new Set(dataset?.categories?.map(category => category?.label?.de || category?.label?.en || ''))].join(', ')
        const licenses = [...new Set(dataset?.distributions?.map(distribution => distribution?.licence?.label || (distribution?.licence?.homepage ? distribution?.licence?.homepage : distribution?.licence?.resource) || ''))].join(', ')

        const maybeModificationDate = new Date(dataset?.modificationDate)
        const isValidDate = maybeModificationDate && maybeModificationDate.getTime() && !isNaN(maybeModificationDate.getTime())

        return {
          updated: (isValidDate && maybeModificationDate.toLocaleDateString('de', { year: 'numeric', month: '2-digit', day: '2-digit' })) || undefined,
          category: categories.length > 0 ? categories : undefined,
          creator: dataset?.publisher?.name || undefined,
          license: licenses.length > 0 ? licenses : undefined,
        }
      })
    }
  },
})
</script>

<style lang="scss">
  // specified in styles/by/_bayern-dataset-results.scss
</style>
