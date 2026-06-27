import type { MaybeRefOrGetter } from "@vueuse/core";
import { computed, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";

export function useHvdFacet(initialValue?: MaybeRefOrGetter<boolean>) {
  const hvdModel = ref(initialValue ?? false);

  const router = useRouter();
  const route = useRoute();

  const isHvdQuery = computed(() => {
    return route.query["is_hvd"] === "true";
  })

  hvdModel.value = isHvdQuery.value;

  function toggleFacet() {
    hvdModel.value = !hvdModel.value;
  }

  watch(hvdModel, (newValue) => {
    if (newValue) {
      router.push({ query: { ...route.query, is_hvd: "true" } });
    } else {
      const query = { ...route.query };
      delete query["is_hvd"];
      router.push({ query });
    }
  })

  return {
    hvdModel,
    toggleFacet,
  };

}
