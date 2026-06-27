<template>
  <div>
    <div class="dist-list">
      <h3>{{ $t('message.metadata.distributions') }}</h3>
      <hr>
      <ul class="list-unstyled" v-if="getData('distributions').length > 0">
        <li v-for="(dist, i) in getData('distributions')" :key="i" class="dist-item">
          
          <div class="dist-details">
            <div class="dist-info">
              <!-- accessURL is always an array of URIs, first one might be enough for displaying -->
              <span v-if="accessExists(dist)" style="font-weight: bold">{{ dist['dcat:accessURL'][0]['@id']
              }}</span><br />

              <!-- title and description always an array, first valu emights be enough for displaying-->
              <span v-if="titleExists(dist)" style="color: #868e96">{{ dist['dct:title'][0]['@value'] }}<br /></span>
              <span v-if="descriptionExists(dist)" style="color: #868e96">{{ dist['dct:description'][0]['@value']
              }}</span>
            </div>
            <div class="dist-edit justify-content-end">
              <span @click="redirectToDistributionForm(i)" class="dist-edit-button p-2"><svg
                  xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                  class="bi bi-pencil-square" viewBox="0 0 16 16">
                  <path
                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                  <path fill-rule="evenodd"
                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                </svg></span>
              <span @click="triggerDeleteModal(i)" class="dist-delete-button p-2"><svg xmlns="http://www.w3.org/2000/svg"
                  width="25" height="25" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                  <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                </svg></span>
            </div>
          </div>
        </li>
      </ul>
      <ul v-else class="list-unstyled">
        <li>{{ $t('message.dataupload.noDistributions') }}</li>
      </ul>
      <button class="default" v-if="distributionOverviewPage" @click="createDistribution">{{
        $t('message.dataupload.newDistribution') }}</button>
    </div>
    <div class="modal fade" id="deleteDistributionModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
      aria-hidden="true" data-cy="citation-modal">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title" id="deleteModalLabel">
              {{ $t('message.dataupload.deletemodal.deleteDistribution') }}
            </h2>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h3>{{ $t('message.dataupload.deletemodal.areyousure') }}</h3>
            <div class="mt-3 d-flex justify-content-start align-items-center">
            </div>
          </div>
          <div class="modal-footer">
            <button data-dismiss="modal" id="cancel">{{ $t('message.datasetDetails.datasets.modal.cancel') }}</button>
            <button @click="deleteDist()" id="delete">{{ $t('message.datasetDetails.delete') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import $ from 'jquery';
import { isEmpty, has } from 'lodash-es';
import { mapActions, mapGetters } from 'vuex';
import { truncate } from '../../utils/helpers';

export default {
  props: ['distributionOverviewPage'],
  data() {
    return {
      distributionToDelete: 0,
      distributionData: [],
    };
  },
  computed: {
    ...mapGetters('dpiStore', [
      'getNumberOfDistributions',
      'getNavSteps',
      'getData',
    ]),
  },
  methods: {
    ...mapActions('dpiStore', [
      'addDistribution',
      'saveLocalstorageValues',
      'deleteDistribution',
      'setDeleteDistributionInline',
    ]),
    truncate,
    deleteDist() {
      this.deleteDistribution(this.distributionToDelete);
      this.setDeleteDistributionInline(true);
      $('#deleteDistributionModal').modal('hide');
      this.$router.push({ path: `${this.$env.content.dataProviderInterface.basePath}/datasets/distoverview`, query: { locale: this.$route.query.locale } }).catch(() => { });
    },
    triggerDeleteModal(index) {
      this.distributionToDelete = index;
      $('#deleteDistributionModal').modal({ show: true });
    },
    getDistributionFormat(dist) {
      if (!isEmpty(dist['dct:format'])) {
        return dist['dct:format'].substring(dist['dct:format'].lastIndexOf('/') + 1);
      } else {
        return 'UNKNOWN';
      }
    },
    redirectToDistributionForm(distributionIndex) {
      const firstDistPage = this.getNavSteps(this.$env.content.dataProviderInterface.specification).distributions[0];
      this.$router.push({
        path: `${this.$env.content.dataProviderInterface.basePath}/distributions/${firstDistPage}/${distributionIndex}`,
        query: {
          locale: this.$route.query.locale,
          edit: distributionIndex
         }
      }).catch(() => { });
    },
    createDistribution() {
      // create an new distribution within store
      this.addDistribution();
      const distNumber = this.getNumberOfDistributions;
      const distIndex = distNumber - 1; // distributions are stored within an array and indexed by their position in array
      const firstDistPage = this.getNavSteps(this.$env.content.dataProviderInterface.specification).distributions[0];

      // direct to distribution input form
      this.$router.push(`${this.$env.content.dataProviderInterface.basePath}/distributions/${firstDistPage}/${distIndex}?locale=${this.$i18n.locale}`);
    },
    titleExists(data) {
      return !isEmpty(data['dct:title']) && !isEmpty(data['dct:title'][0]) && has(data['dct:title'][0], '@value') && !isEmpty(data['dct:title'][0]['@value']);
    },
    descriptionExists(data) {
      return !isEmpty(data['dct:description']) && !isEmpty(data['dct:description'][0]) && has(data['dct:description'][0], '@value') && !isEmpty(data['dct:description'][0]['@value']);
    },
    accessExists(data) {
      return !isEmpty(data['dcat:accessURL']) && !isEmpty(data['dcat:accessURL'][0]) && has(data['dcat:accessURL'][0], '@id') && !isEmpty(data['dcat:accessURL'][0]['@id']);
    },
  },
};
</script>

<style lang="scss" scoped>
.dist {
  &-list {
    margin: 50px;
  }

  &-details {
    display: flex;
    flex: 1;
  }

  &-edit {
    width: 25%;
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    margin-left: 5px;

    &-button {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    &-button:hover {
      opacity: 0.5;
      transform: scale(1.1);
      color: green;
      transition: all 0.2s ease;
    }
  }

  &-info {
    width: 75%;
  }

  &-item {
    margin: 5px;
    display: flex;
    padding: 20px;
    align-items: center;
    border-bottom: .1em solid #e5e5e5;
  }

  &-delete {
    &-button {
      cursor: pointer;
      transition: all 0.2s ease;

    }
    &-button:hover {
      transform: scale(1.1);
      opacity: 0.5;
      color: red;
      transition: all 0.2s ease;
    }
  }
}

button {
  &#delete {
    background-color: #B30519;
    color: #fff;
    border-color: #B30519;
  }

  &#cancel {
    background-color: #767676;
    border-color: #767676;
    color: #fff;
  }

  &.default {
    background-color: #001d85;
    border-color: #001d85;
    color: #fff;
  }

  border-radius: 0.3em;
  font-size: 16px;
  font-family: "Ubuntu";
  padding: 0.75em;
  font-weight: 100;
}
</style>
