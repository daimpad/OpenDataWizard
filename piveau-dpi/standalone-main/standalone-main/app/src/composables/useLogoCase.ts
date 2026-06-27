import { toValue, type MaybeRefOrGetter } from "@vueuse/core";
import { computed } from "vue";

export function useLogoCase(str: MaybeRefOrGetter<string>) {
  return computed(() => toValue(str).split('-').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join('-'))
}
