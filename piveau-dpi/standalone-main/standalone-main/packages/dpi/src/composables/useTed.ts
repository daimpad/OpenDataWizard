import { computed, type MaybeRefOrGetter, toValue } from 'vue';
import { useI18n } from 'vue-i18n'

/**
 * A shorthand for `i18n.te(key) ? i18n.t(key) : def`.
 *
 * @example
 * const ted = useTed()
 * const label = ted('my-key', 'My Label')
 * // label is computed and returns the translation for 'my-key' if it exists, otherwise 'My Label'
 *
 * @param {MaybeRefOrGetter<string>} key The key to translate
 * @param {MaybeRefOrGetter<string>} def The default value if the key does not exist
 * @returns {ComputedRef<string>} The translated value
 */
export function useTed() {
  const { t, te } = useI18n();

  const ted = (key: MaybeRefOrGetter<string>, def: MaybeRefOrGetter<string>) => computed(() => te(toValue(key)) ? t(toValue(key)) : toValue(def))

  return ted
}
