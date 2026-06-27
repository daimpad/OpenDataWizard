# config-schema

This directory contains the user configuration that is used across all Vue components. We use [zod](https://zod.dev/) to declare a schema as the single point of truth for user configuration. Zod schemas are parsed during runtime to

1. validate user configurations
2. fill in default values for optional configuration keys


## TypeScript

Type definitions are inferred from the user configuration schema. These types are declared for the runtime configuration object when using `this.$env` via the runtimeConfigurationService.

## Contributing

Refer to the [contribution guide](/CONTRIBUTING.md).