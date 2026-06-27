// re-exports all schemas

import { type Config } from './configSchema'

export * from './apiSchema'
export * from './authenticationSchema'
export * from './contentSchema'
export * from './routingSchema'
export * from './metadataSchema'
export * from './languagesSchema'
export * from './themesSchema'
export * from './trackerSchema'
export * from './configSchema'

export function defineUserConfig(config: Config): Config {
  return config
}
