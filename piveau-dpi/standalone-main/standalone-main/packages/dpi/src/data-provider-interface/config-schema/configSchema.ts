import * as z from 'zod'
import {
  apiSchema,
} from './apiSchema'

import { authenticationSchema } from './authenticationSchema'
import { contentSchema } from './contentSchema'
import { languagesSchema } from './languagesSchema'
import { metadataSchema } from './metadataSchema'
import { routingSchema } from './routingSchema'
import { themesSchema } from './themesSchema'
import { trackerSchema } from './trackerSchema'

import type { DeepPartial } from './utils'

export const configSchema = z.object({
  api: apiSchema,
  authentication: authenticationSchema,
  /**
   * Routing options (optional).
   */
  routing: routingSchema,

  /**
   * Metadata options (optional).
   */
  metadata: metadataSchema,

  /**
   * Content options (optional).
   */
  content: contentSchema,

  /**
   * Language options (optional).
   */
  languages: languagesSchema,

  /**
   * Theming options (optional).
   */
  themes: themesSchema,

  /**
   * Tracker options (optional).
   */
  tracker: trackerSchema,
})

export type ResolvedConfig = z.infer<typeof configSchema>
export type Config = DeepPartial<ResolvedConfig> & {
  api: ResolvedConfig['api']
}
