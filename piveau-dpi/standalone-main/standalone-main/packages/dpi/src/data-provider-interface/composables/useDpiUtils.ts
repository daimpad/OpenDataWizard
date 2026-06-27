import { useStore } from 'vuex'
import { type Router, useRoute, useRouter } from 'vue-router';
import { computed, type  MaybeRefOrGetter, toValue } from 'vue';
import { useI18n } from 'vue-i18n';

export interface ToDpiEditModeOptions extends ToDpiCreateModeOptions {
  /**
   * The ID of the dataset to edit
   */
  id: string;
  /**
   * The ID of the catalog the dataset belongs to
   */
  catalogId: string;
  /**
   * Whether the target dataset ID is a draft
   */
  isDraft?: boolean
}

export interface ToDpiCreateModeOptions {
  locale?: MaybeRefOrGetter<string>
  router?: Router
}

export function useDpiUtils() {
  const router = useRouter()
  const route = useRoute()
  const store = useStore()
  const { locale, fallbackLocale } = useI18n()

  const computedLocale = computed<string>(() => (route.query.locale as string) || locale.value as string || fallbackLocale.value as string || 'en')
  const computedRpt = computed(() =>
    store.getters['auth/getUserData']?.rtpToken || ''
  )

  function cleanupDpi() {
    localStorage?.removeItem('dpi_duplicate')
    localStorage?.removeItem('dpi_catalogues')
    localStorage?.removeItem('dpi_editmode')
    localStorage?.removeItem('dpi_datasets')
    localStorage?.removeItem('dpi_draftmode')
  }

  /**
   * Fetches the dataset with the specified ID and navigates to DPI with pre-filled data for editing.
   *
   * @example
   * ```ts
   * const { toEditMode } = useDpiUtils()
   * toEditMode({ id: 'kleeblatt10', catalogId: 'hof' })
   * ```
   *
   */
  function toEditMode(options: ToDpiEditModeOptions) {
    const { id, catalogId, isDraft = false, locale, router: customRouter = router } = options

    if (!computedRpt.value)
      console.warn('No rpt token found. DPI cannot be opened in edit mode.')

    cleanupDpi()

    if (isDraft)
      store.dispatch('auth/setIsDraft', true)

    localStorage?.removeItem('dpi_duplicate')
    customRouter.push({ name: 'DataProviderInterface-Input', params: { property: 'datasets' }, query: {
      locale: toValue(locale) || computedLocale.value,
      edit: 'true',
      id,
      catalog: catalogId,
      ...(isDraft && { fromDraft: 'true' })
    } })
  }

  /**
   * Navigates to DPI in create mode.
   *
   * @example
   * ```ts
   * const { toCreateMode } = useDpiUtils()
   * toCreateMode()
   * ```
   *
   */
  function toCreateMode(options?: ToDpiCreateModeOptions) {
    const { locale, router: customRouter = router } = options || {}

    cleanupDpi()
    customRouter.push({ name: 'DataProviderInterface-Input', params: { property: 'datasets' }, query: {
      locale: toValue(locale) || computedLocale.value,
    } })
  }

  return {
    toEditMode,
    toCreateMode,
    cleanupDpi,
  }
}
