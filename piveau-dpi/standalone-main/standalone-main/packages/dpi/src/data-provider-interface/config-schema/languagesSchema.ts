import * as z from 'zod'

export const languagesSchema = z.object({
  useLanguageSelector: z.boolean().default(true),
  locale: z.string().default('en'),
  fallbackLocale: z.string().default('en'),
}).default({})
