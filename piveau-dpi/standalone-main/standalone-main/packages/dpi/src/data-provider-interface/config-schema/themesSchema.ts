import * as z from 'zod'

export const themesSchema = z.object({
  header: z.string().default('dark'),
}).default({})
