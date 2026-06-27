<template>
  <div>
    <div id="nav" class="d-flex ">
      <div class="left-form-nav w-25">
        <!-- PREVIOUS STEP -->
        <FormKit type="button" :label="$t('message.dataupload.preview')" @click="goToPreviousStep" v-if="previousStep"
          class="prev-btn mx-1 my-0"></FormKit>

        <!-- CLEAR FORM -->
        <FormKit type="button" :label="$t('message.dataupload.clear')" @click="handleClear" class="clear-btn"></FormKit>
      </div>
      <div class="right-form-nav w-75">

        <!-- DELETE DISTRIBUTION -->
        <!-- <FormKit type="button" label="Delete Distribution" @click="handleDeleteDistribution()"
          v-if="isDistribution" class="mx-1 my-0 delDisBtn"></FormKit> -->

        <!-- PUBLISH NEW CATALOGUE -->
        <FormKit type="button" @click="submit('createcatalogue')"
          v-if="!getIsEditMode && !getIsDraft && property === 'catalogues'" class="mr-2">
          <span v-if="uploading.createcatalogue" class="loading-spinner"></span>{{
            $t('message.dataupload.publishcatalogue') }}
        </FormKit>

        <!-- PUBLISH EDITED CATALOGUE -->
        <FormKit type="button" @click="submit('createcatalogue')"
          v-if="getIsEditMode && !getIsDraft && property === 'catalogues'" class="mx-1 my-0">
          <span v-if="uploading.createcatalogue" class="loading-spinner"></span>{{
            $t('message.dataupload.publishcatalogue') }}
        </FormKit>

        <!-- PUBLISH DATASET -->
        <FormKit type="button" v-if="property === 'datasets'" @click="submit('dataset')" :disabled="formErrorCount"
          class="mx-1 my-0">
          <span v-if="uploading.dataset" class="loading-spinner"></span>{{ $t('message.dataupload.publishdataset') }}
        </FormKit>

        <!-- SAVE AS DRAFT -->
        <FormKit type="button" v-if="property === 'datasets'" @click="submit('draft')" :disabled="formErrorCount"
          class="mx-1 my-0">
          <span v-if="uploading.draft" class="loading-spinner"></span>{{ $t('message.dataupload.saveasdraft') }}
        </FormKit>

        <!-- NEXT STEP -->
        <FormKit type="button" :label="$t('message.dataupload.next')" @click="goToNextStep" v-if="nextStep"></FormKit>
      </div>
    </div>

    <app-confirmation-dialog id="modal" :confirm="modal.confirm" @confirm="modal.callback" :close="modal.confirm">
      {{ modal.message }}
    </app-confirmation-dialog>
  </div>
</template>

<script>
/* eslint-disable */
import $ from 'jquery';
import { isNil } from 'lodash';
import { mapGetters, mapActions } from 'vuex';
import { useWindowScroll } from '@vueuse/core'
import axios from 'axios';
import { ref, nextTick } from 'vue'
import { getCurrentInstance } from "vue";
import { useI18n } from 'vue-i18n';
import { useDpiContext } from '../composables/useDpiContext';

export default {
  name: 'Navigation',
  props: ['steps', 'nextStep', 'previousStep', 'goToNextStep', 'goToPreviousStep'],
  data() {
    return {
      instance: getCurrentInstance().appContext.app.config.globalProperties.$env,
      uploading: {
        dataset: false,
        draft: false,
      },
      modal: {
        confirm: '',
        message: '',
        callback: '',
      },
      modals: {
        id: {
          confirm: '',
          message: 'Dataset ID already exists',
          callback: this.closeModal,
        },
        clear: {
          confirm: '',
          message: '',
          callback: this.clearStorage,
        },
      },
      property: this.$route.params.property,
      id: this.$route.params.id,
    };
  },
  setup() {
    const scrollToTop = () => {
      let { x, y } = useWindowScroll({ behavior: 'smooth' })
      y.value = 0

    };
    return {
      scrollToTop
    }
  },
  computed: {
    ...mapGetters('auth', [
      'getIsEditMode',
      'getIsDraft',
      'getUserData',
    ]),
    ...mapGetters('dpiStore', [
      'getData',
    ]),
    formErrorCount() {
      let errorCount = 0;
      Object.keys(this.steps).forEach(key => {
        errorCount = errorCount + this.steps[key].blockingCount
      })

      return errorCount > 0;
    }
  },
  created() {
    // this.$i18n.locale = this.$route.query.locale
    this.modals.clear.message = this.$t('message.dataupload.modal.resetForm')
    this.modals.clear.confirm = this.$t('message.dataupload.modal.confirmReset')

  },
  methods: {
    ...mapActions('auth', [
      'setIsEditMode',
      'setIsDraft',
    ]),
    ...mapActions('snackbar', [
      'showSnackbar',
    ]),
    ...mapActions('dpiStore', [
      'convertToRDF',
      'clearAll',
      // 'deleteDistribution',
      // 'setDeleteDistributionInline',
    ]),
    closeModal() {
      $('#modal').modal('hide');
    },
    handleIDError() {
      this.modal = this.modals.id;
      $('#modal').modal({ show: true });
    },
    handleClear() {
      this.modal = this.modals.clear;
      $('#modal').modal({ show: true });
    },
    clearStorage() {
      this.closeModal();
      this.$formkit.reset('dpiForm');
      this.clearAll();
    },
    async submit(mode) {
      this.uploading[mode] = true;
      this.$Progress?.start();

      const specification = this.$env.content.dataProviderInterface.specification;
      const RDFdata = await this.convertToRDF({ property: this.property, specification: specification }).then((response) => { return response; });
      const rtpToken = this.getUserData.rtpToken;

      const datasetId = this.getData(this.property)['datasetID'];
      const title = this.getData(this.property)['dct:title'];
      const description = this.getData(this.property)['dct:description'];
      const catalogId = 
        this.dpiContext.edit?.catalog
        || this.getData(this.property)['catalog']
        || this.getData(this.property)['dcat:catalog']
        || this.getData(this.property)['dct:catalog']

      let uploadUrl;
      let actionName;
      let actionParams = {
        id: datasetId,
        catalog: catalogId,
        body: RDFdata,
        title,
        description,
      };

      if (mode === 'dataset') {
        // if no edit mode: just publish dataset regularly
        // if edit mode but no draft: publish/save dataset regularly
        if (!this.getIsEditMode || (this.getIsEditMode && !this.getIsDraft) || (this.getIsEditMode && this.getIsDraft && localStorage.getItem('dpi_duplicate'))) {
          uploadUrl = `${this.$env.api.hubUrl}datasets?id=${datasetId}&catalogue=${catalogId}`;
          actionParams = { data: RDFdata, token: rtpToken, url: uploadUrl };
          actionName = 'auth/createDataset';
        } else {
          // if edit mode and draft: publish user draft (remove from draft database and add to dataset database)-> publishUserDraftById
          actionParams = { id: datasetId, catalog: catalogId };
          actionName = 'auth/publishUserDraftById';
        }

      } else if (mode === 'draft') {
        //if no edit mode: save draft regularly
        // if edit mode and draft: save draft regularly
        if (!this.getIsEditMode || (this.getIsEditMode && this.getIsDraft)) {
          uploadUrl = `${this.$env.api.hubUrl}drafts/datasets/${datasetId}?catalogue=${catalogId}`;
          actionName = 'auth/createUserDraft';
        } else {
          // if edit mode and no draft: save dataset as draft (remove from dataset database and add to draft database)-> putDatasetToDraft
          actionParams = { id: datasetId, catalog: catalogId, title, description };
          actionName = 'auth/putDatasetToDraft';
        }

      } else if (mode === 'createcatalogue') {
        uploadUrl = `${this.$env.api.hubUrl}catalogues/${datasetId}`;
        actionParams = { data: RDFdata, token: rtpToken, url: uploadUrl, id: datasetId };
        actionName = 'auth/createCatalogue';
      }

      try {
        // Dispatch the right action depending on the mode

        const idIsUnqiue = this.idunique(datasetId);

        if (idIsUnqiue) {
          await this.$store.dispatch(actionName, actionParams);
          // await new Promise(resolve => setTimeout(resolve, 250));

          this.$Progress?.finish();
          this.uploading[mode] = false;

          if (mode === 'createcatalogue') this.createCatalogue(datasetId);
          if (mode === 'dataset') this.createDataset(datasetId);
          if (mode === 'draft') this.createDraft();

          // store needs to be reset
          this.clearAll();
        }
        else {
          this.uploading[mode] = false;
          this.$Progress?.fail();
          this.handleIDError();
        }
      } catch (err) {
        console.error(err)
        this.uploading[mode] = false;
        this.$Progress?.fail();
        this.showSnackbar({ message: 'Network Error', variant: 'error' });
      }
    },
    createDataset(datasetId) {
      this.clearAll();
      this.showSnackbar({ message: this.$t('message.dataupload.snackBar.datasetPublished'), variant: 'success' });
      // this.$router.push({ name: 'DatasetDetailsDataset', params: { ds_id: datasetId }, query: { locale: this.$route.query.locale } }).catch(() => { });
      this.$router
        .push({ name: 'DatasetDetailsDataset', params: { ds_id: datasetId }, query: { locale: this.$route.query.locale } })
        .then(() => { this.$router.go(0) })
    },
    createDraft() {
      this.clearAll();
      this.showSnackbar({ message: this.$t('message.dataupload.snackBar.draftCreated'), variant: 'success' });
      this.$router.push({ name: 'DataProviderInterface-Draft', query: { locale: this.$route.query.locale } }).catch(() => { });
    },
    createCatalogue(datasetId) {
      this.clearAll();
      this.showSnackbar({ message: this.$t('message.dataupload.snackBar.catalogPublished'), variant: 'success' });
      this.$router.push({ name: 'CatalogueDetails', query: { locale: this.$route.query.locale }, params: { ctlg_id: datasetId } }).catch(() => { });
    },
    async idunique(id) {
      let isUniqueID = true;

      const draftIDs = this.$store.getters['auth/getUserDraftIds'];

      new Promise(() => {
        if (isNil(id) || id === '' || id === undefined) isUniqueID = true;
        else if (draftIDs.includes(id)) isUniqueID = false;
        else {
          const request = `${this.$env.api.hubUrl}datasets/${id}?useNormalizedId=true`;
          axios.head(request)
            .then(() => {
              isUniqueID = false;
            })
            .catch((e) => {
              isUniqueID = true;
            });
        }
      });
      return isUniqueID
    }
  },
  setup() {
    const dpiContext = useDpiContext()
    return { dpiContext }
  }
};
</script>

<style lang="scss">
// @import '../../../styles/bootstrap_theme';
// @import '../../../styles/utils/css-animations';</style>
