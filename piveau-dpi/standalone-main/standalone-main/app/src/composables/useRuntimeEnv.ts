import { getCurrentInstance } from 'vue';
import { configSchema } from '@piveau/piveau-hub-ui-modules'
import type { z } from 'zod';

export function useRuntimeEnv() {
  const vm = getCurrentInstance();

  if (!vm) {
    throw new Error("useRuntimeEnv must be called within a component");
  }

  type Config = z.infer<typeof configSchema>;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return (vm.proxy?.$root as unknown as { $env: Config })?.$env;
}
