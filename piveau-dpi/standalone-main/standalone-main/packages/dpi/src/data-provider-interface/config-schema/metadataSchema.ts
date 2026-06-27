import * as z from 'zod'

export const metadataSchema = z.object({
  title: z.string().default('piveau Hub-UI'),
  description: z.string().default('A modern and customizable web application for data management of extensive data catalogs.'),
  keywords: z.string().default('Open Data'),
}).default({})
