import * as z from 'zod'

export const trackerSchema = z.object({
  isPiwikPro: z.boolean().default(false),
  siteId: z.string().default(''),
  trackerUrl: z.string().url().default('https://piwik.example.org'),
}).default({})
