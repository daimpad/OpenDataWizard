import { useRoute } from "vue-router"
import { useRuntimeEnv } from "./useRuntimeEnv"
import { computed, getCurrentInstance } from "vue"
import { toValue, type MaybeRefOrGetter } from "@vueuse/core"
import { useI18n } from "vue-i18n"

/**
 * A composable that returns the current locale.
 *
 * The locale is determined as follows:
 *
 * 1. If there is a query parameter `locale`, use that
 * 2. If there is a locale set in the i18n plugin, use that
 * 3. If there is a locale set in the runtime environment, use that
 * 4. If there is a fallback locale set in the i18n plugin, use that
 * 5. If there is a fallback locale set in the runtime environment, use that
 * 6. If there is a fallback locale passed as an argument, use that
 *
 * @param fallback - The fallback locale to use if no other locale is found
 * @returns - The current locale
 */
export function useLocale(fallback?: MaybeRefOrGetter<string>) {
  const route = useRoute()
  const env = useRuntimeEnv()
  const { locale: l, fallbackLocale: fl } = useI18n()
  const i18nLocale = computed(() => l || fl)

  const locale = computed(() => (route.query.locale as string) || i18nLocale || env.languages.locale || env.languages.locale || env.languages.fallbackLocale || toValue(fallback))
  return locale
}
