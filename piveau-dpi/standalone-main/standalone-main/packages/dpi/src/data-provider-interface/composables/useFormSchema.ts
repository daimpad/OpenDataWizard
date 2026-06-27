import { createSharedComposable } from '@vueuse/core'
import { computed, type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { useDpiContext } from './useDpiContext'
import { useI18n } from 'vue-i18n'
import translate from '../utils/translation-helper'

const useFormSchemaGlobal = createSharedComposable(() => {
  const state = ref({
    schema: {
      datasets: {},
      distributions: {},
      catalogues: {}
    },
    usersCatalogs: {}
  })

  /**
   * Retrieves the schema for a given property.
   *
   * @param property - The property whose schema should be retrieved.
   *                  Can be a string or a ref to a string.
   * @returns A computed ref to the schema of the given property.
   */
  const getSchema = (property: MaybeRefOrGetter<string>) => computed(() => {
    const _property = toValue(property)

    if (property === 'catalogues' || property === 'datasets' || property === 'distributions') {
      return state.value.schema[_property as keyof typeof state.value.schema]
    }

    return undefined
  })

  return { state, getSchema }
})

export interface UseFormSchemaOptions {
  t?: (key: string) => string
  te?: (key: string) => boolean
}

export function useFormSchema(options?: UseFormSchemaOptions) {
  const dpiContext = useDpiContext()
  const { t, te } = options || useI18n({ useScope: 'global' })
  const { state, getSchema } = useFormSchemaGlobal()

  const dpiConfig = computed(() => dpiContext.value.specification)

  function createSchema({ property, page }: { property: string, page: string }) {
    const pageProperties = dpiConfig.value.pageConent?.[property as keyof typeof dpiConfig.value.pageConent][page] as unknown as string[]
    const propertyDefinitions = dpiConfig.value.inputDefinition?.[property as keyof typeof dpiConfig.value.inputDefinition]

    // Create copy of state because extractSchema does in-place modifications.
    // Just to be safe.
    const stateCopy = JSON.parse(JSON.stringify(state.value))
    extractSchema({ state: stateCopy, pageProperties, propertyDefinitions, property, page })

    state.value = stateCopy
  }

  function extractSchema({
    state,
    pageProperties,
    propertyDefinitions,
    property,
    page,
  }: {
    state: any
    pageProperties: string[]
    propertyDefinitions: any
    property: string
    page: string
  }) {
    // important: create new empty schema each time so already existing schema will be overwritten on route/view-change
    const newSchema = [];

    for (let index = 0; index < pageProperties.length; index += 1) {
      const propertyKey = pageProperties[index];
      try {
        newSchema.push(propertyDefinitions[propertyKey]);
      } catch (err) {
        console.warn(
          `DCATAP doens't include a property called: ${propertyKey}`
        );
      }
    }

    state.schema[property][page] = newSchema;
  }

  function translateSchema({ property, page }: { property: string, page: string }) {
    const schemaCopy = { ...state.value.schema }
    // @ts-ignore
    translate(schemaCopy[property][page], property, t, te);

    // Update the global state with the new schema
    // @ts-ignore
    state.value.schema[property][page] = schemaCopy[property][page]
  }

  return { createSchema, translateSchema, getSchema }

}
