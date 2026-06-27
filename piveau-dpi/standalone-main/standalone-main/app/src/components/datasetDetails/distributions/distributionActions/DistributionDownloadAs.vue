<template>
   <span v-if="ifFormatMatches" class="dropdown-item d-block px-3 d-flex justify-content-end align-items-center">
      <a @click="openModal()" class="download-btn text-dark text-decoration-none px-2" data-toggle="modal" data-target="#downloadAsModal">{{ $t('message.datasetDetails.datasets.modal.downloadAs') }}</a>
   </span>
</template>
<script>

import { isEmpty } from 'lodash';
import { mapActions } from 'vuex'

export default {
  name: "DownloadAs",
  props: {
    distribution: Object
  },
  data() {
    return {
      ifFormatMatches: false,
      selectOptions: [],
      conversionFormats: this.$env.content.datasetDetails.downloadAs.conversionFormats
    }
  },
  mounted() {
    this.ifMatchesFormat(this.distribution.format.id);
  },
  methods: {
    ...mapActions('datasetDetails', [
      'selectDistributionForDownloadAs'
    ]),
    ifMatchesFormat(format) {
      if (isEmpty(format)) {
        return false
      } else {
        this.conversionFormats.forEach(convertionFormat => {
          if (convertionFormat.sourceFileFormat.toLowerCase() === format.toLowerCase()) {
            this.ifFormatMatches = true;
            this.selectOptions = convertionFormat.targetFileFormat;
          }
      });
     }
    },
    openModal() {
      const distribution = this.distribution;
      const selectOptions = this.selectOptions;
      this.selectDistributionForDownloadAs({distribution, selectOptions});
    }
  },
}
</script>
<style scoped lang="scss">
  .download-btn {font-size: 13px; cursor: pointer;}
</style>

