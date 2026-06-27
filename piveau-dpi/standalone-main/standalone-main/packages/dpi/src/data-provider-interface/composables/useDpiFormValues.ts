import { createSharedComposable } from '@vueuse/core'
import { ref } from 'vue'

export const useFormValues = createSharedComposable(() => {
  const formValues = ref({})

  return {
    formValues
  }
})