import { createSharedComposable } from '@vueuse/core'
import { computed, ref } from 'vue'

const globalState = createSharedComposable(() => {
  const open = ref(false)
  const userMessage = ref('')
  const error = ref<Error | null>(null)
  const isDevelopment = ref(false)

  return {
    open,
    userMessage,
    error,
    isDevelopment
  }
})

export function useErrorDialog() {
  const { open, userMessage, error, isDevelopment } = globalState()

  const openErrorDialog = (_error: Error, _userMessage?: string) => {
    open.value = true
    error.value = _error
    userMessage.value = _userMessage || ''
  }

  const closeErrorDialog = () => {
    open.value = false
  }

  const isOpen = computed(() => !!open.value)
  const currentError = computed(() => error.value)

  return {
    open,
    isOpen,
    error,
    userMessage,
    currentError,
    isDevelopment,
    openErrorDialog,
    closeErrorDialog
  }
}
  