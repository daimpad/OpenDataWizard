import { createI18n } from "vue-i18n";

export function i18n({ locale, fallbackLocale, messages }: { locale: string; fallbackLocale: string; messages: any; }) {
  return createI18n({
    locale: locale,
    fallbackLocale: fallbackLocale,
    messages: messages || {},
    allowComposition: true,
    legacy: false,
    globalInjection: true,
    fallbackWarn: false,
    silentFallbackWarn: true,
    silentTranslationWarn: true,
    warnHtmlMessage: false,
  })
}