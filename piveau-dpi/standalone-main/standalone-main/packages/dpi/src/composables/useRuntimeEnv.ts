import { injectionKey } from "../data-provider-interface/plugins/userConfigShimPlugin";
import { inject } from "vue";

/**
 * Utility function for retrieving the runtime configuration object.
 */
export function useRuntimeEnv() {
  const env = inject(injectionKey);

  if (!env) {
    throw new Error("No runtime environment found. Did you forget to install the runtime configuration service?");
  }

  return env;
}