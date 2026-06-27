import RepeatableDataService from './src/components/RepeatableDataService.vue';
import { DefaultConfigOptions } from '@formkit/vue';
import { inputDefinitions } from '@piveau/dpi';
import { empty } from '@formkit/utils';

import { FormKitValidationRule } from '@formkit/validation'

/**
 * Determine if the given input's value is a URL.
 * @param context - The FormKitValidationContext
 * @public
 */
const url: FormKitValidationRule = function url({ value }, ...stack) {
  try {
    const protocols = stack.length ? stack : ['http:', 'https:']
    const url = new URL(String(value))
    if (!protocols.includes(url.protocol)) {
      return false
    }
    // Check for whitespace characters in the query string
    if (/\s/.test(String(value))) {
      return false
    }
    return true
  } catch {
    return false
  }
}


  /**
   * This rule is used to check if an adms:identifier URI is required if at least one sibling has a skos:notation
   * Only works for adms:identifier input field.
   *
   * @param node - The FormKitNode
   * @public
   */
const admsIdentifierUriRequiredIfSibling: FormKitValidationRule = function(node) {
  // Check if parent node is of type group
  const parent = node.at('$parent')
  if (!parent || !parent.value || parent.type !== 'group' || parent.children.length === 1) return true

  // todo: generalize this to work for other types than adms:identifier.
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const isSiblingType = !empty((parent.value as any)?.['skos:notation']?.['@type']?.name) || !empty((parent.value as any)?.['skos:notation']?.['@type']?.resource)
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const isSiblingValue = !empty((parent.value as any)?.[`skos:notation`]?.[`@value`])

  if (isSiblingType || isSiblingValue) return  !empty(node.value)

  return true

}

admsIdentifierUriRequiredIfSibling.skipEmpty = false;

const config: DefaultConfigOptions = {
  inputs: {
    ...inputDefinitions,
    repeatableDataService: {
      type: 'list',
      component: RepeatableDataService,
    }
  },
  rules: {
    url,
    admsIdentifierUriRequiredIfSibling,
  }
}

export default config
