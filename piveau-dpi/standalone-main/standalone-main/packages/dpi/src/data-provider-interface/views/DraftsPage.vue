<template>
  <!-- TODO Add a Mobile Version of that overview (pref with Icons)-->
  <div class="d-flex flex-column bg-transparent container-fluid justify-content-between content draftWrapper">
    <div class="d-flex mt-3">

      <div class="logoDPIPiveau">
        <h2>DPI</h2>
        <div class="dpiLogoSeperator"></div>
        <h4 class="" >{{ $t('message.dataupload.menu.draftDatasets') }}</h4>
      </div>
    </div>

    <div class="infoBox">
      <div class="d-flex">
        <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" fill="currentColor"
          class="bi bi-info-circle mr-3 mb-3 infoboxI " viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
          <path
            d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
        </svg>
        <p v-html="$t('message.dataupload.drafts.intro')">
        </p>
      </div>

    </div>
    <div class="d-flex align-items-center justify-content-center">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">{{ $t('message.dataupload.menu.Dataset') }} ID</th>
            <th scope="col">{{ $t('message.dataupload.menu.Catalogue') }} ID</th>
            <th scope="col">{{ $t('message.dataupload.menu.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="{ id, catalog } in getUserDrafts" :key="`draft@${id}`" :data-cy="`draft@${id}`">
            <td class="w33">{{ id }}</td>
            <td class="w33">{{ catalog }}</td>
            <td class="buttonWrapper">
              <button type="button" class="btn btn-secondary dropDownWrap">
                <app-link class="dropdown-toggle text-nowrap text-decoration-none" fragment="#" role="button"
                  id="linkedDataDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span :title="$t('message.metadata.linkedData')" data-bs-toggle="tooltip" data-placement="top">
                    {{ $t('message.metadata.linkedData') }}
                  </span>
                </app-link>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="linkedDataDropdown">
                  <app-link :to="createLinkedMetricsURL(id, catalog, 'rdf')" target="_blank" class="dropdown-item">
                    <div class="px-2 py-1">RDF/XML</div>
                  </app-link>
                  <app-link :to="createLinkedMetricsURL(id, catalog, 'ttl')" target="_blank" class="dropdown-item">
                    <div class="px-2 py-1">Turtle</div>
                  </app-link>
                  <app-link :to="createLinkedMetricsURL(id, catalog, 'n3')" target="_blank" class="dropdown-item">
                    <div class="px-2 py-1">Notation3</div>
                  </app-link>
                  <app-link :to="createLinkedMetricsURL(id, catalog, 'nt')" target="_blank" class="dropdown-item">
                    <div class="px-2 py-1">N-Triples</div>
                  </app-link>
                  <app-link :to="createLinkedMetricsURL(id, catalog, 'jsonld')" target="_blank" class="dropdown-item">
                    <div class="px-2 py-1">JSON-LD</div>
                  </app-link>

                </div>
              </button>
              <button type="button" class="btn btn-secondary" @click="handleEdit(id, catalog)">{{
                $t('message.dataupload.menu.edit') }}</button>
              <button type="button" class="btn btn-primary" @click="handleConfirmPublish(id, catalog)">{{
                $t('message.dataupload.menu.publish') }}</button>
              <button type="button" class="btn btn-primary" @click="handleConfirmDuplication(id, catalog)">{{
                $t('message.dataupload.menu.duplicate') }}</button>
              <button type="button" class="btn btn-danger" @click="handleConfirmDelete(id, catalog)">{{
                $t('message.dataupload.menu.delete') }}</button>

            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <app-confirmation-dialog id="modal" confirm="Confirm" :loading="modalProps.loading" @confirm="modalProps.confirm">
      {{ modalProps.message }}
    </app-confirmation-dialog>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import $ from 'jquery';
import AppLink from "../../widgets/AppLink.vue";
import { useRuntimeEnv } from "../../composables/useRuntimeEnv.ts";

export default {
  props: [],
  components: {
    AppLink
  },
  data() {
    return {
      env: useRuntimeEnv(),
      values: {},
      isDuplication: localStorage.getItem('dpi_duplicate'),
      duplicatedID: '',
      modalProps: {
        loading: false,
        message: this.$t('message.dataupload.modal.deleteDraft'),
        confirm: () => null,
      },
    };
  },
  computed: {
    ...mapGetters('auth', [
      'getUserDrafts',
      'getUserData',

    ]),
    token() {
      return this.getUserData.rtpToken;
    },
  },
  methods: {
    ...mapActions('auth', [
      'setIsDraft',
      'updateUserDrafts',
      'setIsEditMode',
    ]),
    ...mapActions('snackbar', [
      'showSnackbar',
    ]),
    ...mapActions('dpiStore', [
      'convertToRDF',
      'clearAll',
      'convertToInput',
      // 'deleteDistribution',
      // 'setDeleteDistributionInline',
    ]),
    createLinkedMetricsURL(id, catalog, format) {
      return {
        path: `/dpi/draft/${id}.${format}`,
        query: {
          useNormalizedId: true,
          locale: this.$route.query.locale,
          catalogue: catalog,
        },
      };
    },
    handleEdit(id, catalog) {
      this.setIsDraft(true);
      localStorage.removeItem('dpi_duplicate')
      this.$router.push({ name: 'DataProviderInterface-Edit', params: { catalog, property: 'datasets', id }, query: { locale: this.$route.query.locale } }).catch(() => { });
    },
    async handleDelete(id, catalog) {
      await this.doRequest('auth/deleteUserDraftById', { id, catalog });
      $('#modal').modal('hide');
      this.showSnackbar({
        message: this.$t('message.dataupload.snackBar.draftDeleted'),
        variant: 'success',
      });

    },
    async handlePublish(id, catalog) {
      await this.doRequest('auth/publishUserDraftById', { id, catalog });
      $('#modal').modal('hide');
      this.showSnackbar({
        message: this.$t('message.dataupload.snackBar.datasetPublished'),
        variant: 'success',
      });
      this.$router.push({ name: 'DatasetDetailsDataset', params: { ds_id: id }, query: { locale: this.$route.query.locale } }).catch(() => { });
      setTimeout(() => {
        localStorage.removeItem('dpi_duplicate')
        this.$router.go();
      });

    },
    handleConfirmPublish(id, catalog) {

      this.modalProps.message = this.$t('message.dataupload.modal.publishDataset');

      this.modalProps.confirm = () => this.handlePublish(id, catalog);
      $('#modal').modal('show');
      localStorage.removeItem('dpi_duplicate')
    },
    handleConfirmDelete(id, catalog) {

      this.modalProps.message = this.$t('message.dataupload.modal.deleteDraft');
      this.modalProps.confirm = () => this.handleDelete(id, catalog);
      $('#modal').modal('show');
      localStorage.removeItem('dpi_duplicate')
    },
    handleConfirmDuplication(id, catalog) {
      this.setIsDraft(true);
      localStorage.setItem('dpi_duplicate', true)
      this.$router.push({ name: 'DataProviderInterface-Edit', params: { catalog, property: 'datasets', id }, query: { locale: this.$route.query.locale } }).catch(() => { console.log(error); });

    },
    async doRequest(action, { id, newId, catalog, url, token }) {
      this.$Progress.start();
      this.modalProps.loading = true;
      try {
        await this.$store.dispatch(action, { id, newId, catalog, url, token });
        this.$Progress.finish();
      } catch (ex) {
        // Show snackbar
        this.showSnackbar({
          message: ex.message,
          color: 'error',
        });
        this.$Progress.fail();
      } finally {
        await new Promise(resolve => setTimeout(resolve, 500));
        this.modalProps.loading = false;
      }
    },
  },
  created() {
    this.updateUserDrafts();
  },
};
</script>

<style lang="scss" scoped>
@media (min-width: 1140px) {}

.nav-link {
  text-decoration: underline;
}

.active {
  text-decoration: none;
  font-weight: 700;
}

.dropdown-item {
  &:active {
    background-color: #868e96;
  }
}

#linkedDataDropdown {

  color: #3f3f3f;

  &:hover {
    color: white;
  }
}

.buttonWrapper button {
  margin: 0.2rem;
}

.buttonWrapper {
  width: 100%;
  display: flex;
}

.newIdField {
  margin: 1rem 0 !important;
  box-shadow: none;
  border-radius: 0;
  border-bottom: 1px solid #001D85;
  transition: all 100ms ease-in-out;
}

.table td {
  border-top: none;
  vertical-align: middle;
}

.table tbody tr:not(:last-of-type) {

  border-bottom: 1px solid lightgray;
  // box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
  // transition: all 0.3s cubic-bezier(.25, .8, .25, 1);

  // &:hover {
  //   box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
  // }
}

.w33 {
  width: 33%;
}

.draftWrapper {
  margin-bottom: 120px;
}

.btn {
  background: none;
  color: #3f3f3f;

  &:hover {

    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
  }
}

.dropDownWrap {
  min-width: 175px;
  position: relative;
}

.dropdown-toggle {
  display: inline-block;
  width: 100%;

  &:active {
    border: none;
  }
}

.dropdown-menu.show {
  padding: 0 !important;
  transform: none !important;
  top: 35px !important;
  width: 100%;
  z-index: 1;
}

.infoBox {
  margin-bottom: 40px;
}

.logoDPIPiveau {
  display: flex;
  align-items: center;
  width: 100%;
  justify-content: space-between;

  img {
    width: 10rem;
  }

  h2 {

    color: #3f3f3f;
  }
}

.dpiLogoSeperator {
  height: 1px;
  background-color: #3f3f3f;
  width: 100%;
  margin: 0 1rem;
}
</style>
