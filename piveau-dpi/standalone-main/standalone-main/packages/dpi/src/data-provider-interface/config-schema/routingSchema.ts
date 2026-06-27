import * as z from 'zod'

export const routingSchema = z.object({
  routerOptions: z.object({
    base: z.string().default('/'),
    mode: z.string().default('history'),
  }).default({}),
  navigation: z.object({
    showSparql: z.boolean().default(false),
  }).default({}),
  pagination: z.object({
    usePagination: z.boolean().default(true),
    usePaginationArrows: z.boolean().default(true),
    useItemsPerPage: z.boolean().default(true),
    defaultItemsPerPage: z.number().default(10),
    defaultItemsPerPageOptions: z.array(z.number()).default([5, 10, 25, 50]),
  }).default({}),
}).default({})
