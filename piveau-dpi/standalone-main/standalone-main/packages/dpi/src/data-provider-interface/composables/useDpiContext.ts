import { type FormKitLibrary } from "@formkit/core"
import { computed, type ComputedRef, getCurrentInstance, inject, type InjectionKey, type MaybeRefOrGetter, provide, toValue } from "vue"

export interface DpiSpecification {
  pageConent?: object
  inputDefinition: object
  prefixes: object
  formatTypes: object
  vocabPrefixes: object
}

export interface DpiContext {
  specification: DpiSpecification
  specificationName: string
  edit?: {
    enabled?: boolean
    catalog?: string
    id?: string
    fromDraft?: boolean
  }
}

export type ComputedDpiContext = ComputedRef<DpiContext>

export const dpiContextKey: InjectionKey<ComputedDpiContext> = Symbol('dpiContext')

export function useDpiContext(): ComputedDpiContext | false {
  const dpiContext = inject(dpiContextKey)

  if (!dpiContext) {
    return false
    // throw new Error('[useDpiContext] DPI Context not found. Did you forget to inject it?')
  }

  return dpiContext
}

export function setupDpiContext(context: MaybeRefOrGetter<DpiContext>) {
  provide(dpiContextKey, computed(() => toValue(context)))
}