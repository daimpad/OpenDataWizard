<template>
  <div class="distribution-details">
    <div class="dsd-distribution-details-header d-flex">
      <h3 class="dsd-distribution-details-header-title m-0">{{ getDistributionTitle(distribution) }}</h3>
      <distribution-format
      class="d-none d-md-block"
      :distribution="distribution"
      :getDistributionFormat="getDistributionFormat"
      :distributionFormatTruncated="distributionFormatTruncated"
      />
    </div>

    <div class="dsd-distribution-details-content">
      <div class="dsd-distribution-details-description">
        <distribution-description
        :distribution="distribution"
        :distributions="distributions"
        :distributionDescriptionIsExpanded="distributionDescriptionIsExpanded"
        :getDistributionDescription="getDistributionDescription"
        :toggleDistributionDescription="toggleDistributionDescription"
        :distributionDescriptionIsExpandable="distributionDescriptionIsExpandable"
        :getDistributionFormat="getDistributionFormat"
        />
        <distribution-format
        class="d-block d-md-none"
        :distribution="distribution"
        :getDistributionFormat="getDistributionFormat"
        :distributionFormatTruncated="distributionFormatTruncated"
        />
      </div>
      <div class="dsd-distribution-details-expandable-content">
        <distribution-expanded-content
        :contentList="distributionVisibleExpandedContent"
        :distribution="distribution"
        :distributionIsExpanded="distributionIsExpanded"
        :showLicensingAssistant="showLicensingAssistant"
        :showLicence="showLicence"
        :filterDateFormatEU="filterDateFormatDE"
        :showArray="showArray"
        :showNumber="showNumber"
        :showObject="showObject"
        :showObjectArray="showObjectArray"
        :appendCurrentLocaleToURL="appendCurrentLocaleToURL"
        />
        <distribution-visible-content
        :contentList="distributionVisibleContent"
        :distribution="distribution"
        :distributionIsExpanded="distributionIsExpanded"
        :showLicensingAssistant="showLicensingAssistant"
        :showLicence="showLicence"
        :filterDateFormatEU="filterDateFormatDE"
        :showArray="showArray"
        :showNumber="showNumber"
        :showObject="showObject"
        :showObjectArray="showObjectArray"
        :appendCurrentLocaleToURL="appendCurrentLocaleToURL"
        />
        <distribution-expand
        :distribution="distribution"
        :distributionCanShowMore="distributionCanShowMore"
        :toggleDistribution="toggleDistribution"
        :distributionIsExpanded="distributionIsExpanded"
        />
      </div>
    </div>

  </div>
</template>

<script>
import {
  has,
  isNil,
} from 'lodash';
import { getTranslationFor } from "@piveau/piveau-hub-ui-modules";
import DistributionExpandedContent from './DistributionExpandedContent.vue';
import DistributionVisibleContent from './DistributionVisibleContent.vue';
import DistributionExpand from "../distributions/DistributionExpand.vue";
import DistributionDescription from "../distributions/DistributionDescription.vue";
import DistributionFormat from "../distributions/DistributionFormat.vue";

export default {
  name: "DistributionDetails",
  components: {
    DistributionExpand,
    DistributionVisibleContent,
    DistributionExpandedContent,
    DistributionDescription,
    DistributionFormat,
  },
  props: [
  "getDistributionTitle",
  "getDistributionFormat",
  "distributionFormatTruncated",
  "distribution",
  "distributions",
  "distributionDescriptionIsExpanded",
  "getDistributionDescription",
  "toggleDistributionDescription",
  "distributionDescriptionIsExpandable",
  "distributionIsExpanded",
  "distributionVisibleContent",
  "distributionExpandedContent",
  "showLicensingAssistant",
  "showLicence",
  "filterDateFormatEU",
  "showArray",
  "showNumber",
  "showObject",
  "showObjectArray",
  "appendCurrentLocaleToURL",
  "toggleDistribution"
  ],
  computed: {
    distributionVisibleExpandedContent() {
      return [...new Set(this.distributionVisibleContent
      .concat(this.distributionExpandedContent))];
    },
    distributionExtraContent() {
      return this.distributionExpandedContent
      .filter(item => !this.distributionVisibleContent.includes(item))
    },
  },
  methods: {
    filterDateFormatDE(date) {
      return new Date(date)
      .toLocaleDateString('de',{
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      }) || undefined;
    },
    distributionCanShowMore(distribution) {
      return this.distributionExtraContent.reduce((accu, item) => {
        switch (item) {
          case 'license': return accu || (has(distribution, 'licence'))
          case 'licenseAttributionByText': return accu || has(distribution , 'licenseAttributionByText') && (!isNil(distribution.licenseAttributionByText) && !isNil(getTranslationFor(distribution.licenseAttributionByText)))
          case 'modificationDate': return accu || has(distribution, 'modificationDate') && !isNil(distribution.modificationDate)
          case 'releaseDate': return accu || (has(distribution, 'releaseDate') && !isNil(distribution.releaseDate))
          case 'availability': return accu || (has(distribution, 'availability') && this.showObject(distribution.availability) && !isNil(distribution.availability.label))
          case 'status': return accu || (has(distribution, 'status') && this.showObject(distribution.status))
          case 'rights': return accu || (has(distribution, 'rights') && this.showObject(distribution.rights))
          case 'mediaType': return accu || (has(distribution, 'mediaType') && !isNil(distribution.mediaType))
          case 'byteSize': return accu || (has(distribution, 'byteSize') && !isNil(distribution.byteSize))
          case 'checksum': return accu || (has(distribution, 'checksum') && !isNil(distribution.checksum) && has(distribution.checksum, 'algorithm') && !isNil(distribution.checksum.algorithm) && has(distribution.checksum, 'checksum_value') && !isNil(distribution.checksum.checksum_value))
          case 'pages': return accu || (has(distribution, 'pages') && this.showObjectArray(distribution.pages))
          case 'languages': return accu || (has(distribution, 'languages') && this.showArray(distribution.languages))
          case 'compressFormat': return accu || (has(distribution, 'compressFormat') && this.showObject(distribution.compressFormat))
          case 'packageFormat': return accu || (has(distribution, 'packageFormat') && this.showObject(distribution.packageFormat))
          case 'hasPolicy': return accu || (has(distribution, 'hasPolicy') && !isNil(distribution.hasPolicy))
          case 'conformsTo': return accu || (has(distribution, 'conformsTo') && this.showObjectArray(distribution.conformsTo))
          case 'spatialResolutionInMeters': return accu || (has(distribution, 'spatialResolutionInMeters') && this.showArray(distribution.spatialResolutionInMeters))
          case 'temporalResolution': return accu || (has(distribution, 'temporalResolution') && this.showArray(distribution.temporalResolution))
          default: return accu
        }
      }, false);
    },
  }
}
</script>

<style scoped>

@media screen and (min-width:992px) {
  .distribution-details-container {
    padding: 10px 0 10px 50px;
  }
}

.dsd-distribution-details-header {
  margin-bottom: 20px;
}
</style>
