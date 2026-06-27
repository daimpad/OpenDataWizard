import { getSubdomainCatalogIdFromUrl } from "../utils/utils";
import { computed } from "vue";
import { useRoute } from "vue-router";

export function useCatalogId() {
  return computed(() => {
    return useRoute()?.params?.ctlg_id || getSubdomainCatalogIdFromUrl(window.location.href) ||  '';
  })
}
