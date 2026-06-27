<template>
  <CatalogPage class="bayern-catalog-page">
    <template #card-datasets>
      <div class="tab-content mt-3">
        <BayernDatasetsPage :fixed-catalog-filter="catalogId"></BayernDatasetsPage>
      </div>
    </template>
  </CatalogPage>
</template>

<style lang="scss">
  // specified in styles/by/_bayern-catalog-page.scss and imported in styles.scss
</style>

<script>
import { CatalogPage } from '@piveau/piveau-hub-ui-modules';
import BayernDatasetsPage from './BayernDatasetsPage.vue';

import { getSubdomainCatalogIdFromUrl } from '../utils/utils'

export default {
  name: 'BayernCatalogPage',
  components: {
    CatalogPage,
    BayernDatasetsPage
  },
  data() {
    return {
      catalogId: this.$route.params?.ctlg_id || getSubdomainCatalogIdFromUrl(window.location.href) ||  '',
    };
  },
}
</script>
