<script setup>
import { getNode } from '@formkit/core'
import { getCurrentInstance, onMounted, ref, computed } from 'vue'
import { getDatasetCategories } from '../../services/dpiV3_apis'
import Chip from '../Chip.vue'
import { useFormValues } from '../../../composables/useDpiFormValues'

const props = defineProps({
  context: Object,
})
const URIList = ref([])
const instance = getCurrentInstance().appContext.app.config.globalProperties.$env

const { formValues } = useFormValues()

// Computed property to get the selected items (excluding validation object)
const selectedItems = computed(() => {
  const discoverabilityItems = formValues.value.Discoverability?.discoverabilityPage || []
  // Filter out validation objects and return only actual data items
  return discoverabilityItems.filter(item => item.id)
})

const checkIfSelected = (item) => {
  try {
    return selectedItems.value.find(obj => obj.id === item.id)
  }
  catch (error) {
    return false
  }
}

async function getDataFromEndpoint() {
  try {
    URIList.value = await getDatasetCategories(instance.api.baseUrl)
    // Sortiere die URIList alphabetisch nach dem pref_label.de
    URIList.value.sort((a, b) => {
      return a.pref_label.de.localeCompare(b.pref_label.de)
    })
  }
  catch (error) {
    console.log(error)
  }
}

function addTolist(item) {
  // Ensure the Discoverability.discoverabilityPage array exists
  if (!formValues.value.Discoverability) {
    formValues.value.Discoverability = {}
  }
  if (!formValues.value.Discoverability.discoverabilityPage) {
    formValues.value.Discoverability.discoverabilityPage = [{ isValid: true }]
  }

  const currentItems = formValues.value.Discoverability.discoverabilityPage
  const itemExists = currentItems.find(obj => obj.id === item.id)

  if (itemExists) {
    // Remove the item
    formValues.value.Discoverability.discoverabilityPage = currentItems.filter(obj => obj.id !== item.id)
  }
  else {
    // Add the item
    const newItem = { 
      id: item.id, 
      uri: item.resource, 
      label: item.pref_label.de 
    }
    formValues.value.Discoverability.discoverabilityPage.push(newItem)
  }

  // Update validation status
  const dataItems = formValues.value.Discoverability.discoverabilityPage.filter(obj => obj.id)
  const validationObj = formValues.value.Discoverability.discoverabilityPage.find(obj => obj.hasOwnProperty('isValid'))
  
  if (validationObj) {
    validationObj.isValid = dataItems.length > 0
  } else {
    // If no validation object exists, add one
    formValues.value.Discoverability.discoverabilityPage.unshift({ isValid: dataItems.length > 0 })
  }

  console.log('Updated discoverabilityPage:', formValues.value.Discoverability.discoverabilityPage)
}

onMounted(() => {
  getDataFromEndpoint()
})
</script>

<template>
  <div class="dpiV3_fChipWrap">
    <Chip
      v-for="(item, index) in URIList" 
      :key="index" 
      :text="item.pref_label.de"
      :data="{ '@value': item.value, 'URI': item.URI }"
      :setup="{ '@type': 'select', '@inTable': false, '@findability': true, '@selected': checkIfSelected(item) }"
      @click="addTolist(item)"
    />
  </div>
</template>

<style scoped>
.dpiV3_fChipWrap {
    display: flex;
    align-items: flex-start;
    align-content: flex-start;
    width: 412px;
    /* gap: 8px var(--Spacing-2, 8px); */
    align-self: stretch;
    flex-wrap: wrap;
}
</style>