<template>
  <DistributionContent
    v-show="!distributionIsExpanded(distribution.id)"
    class="distribution-visible-content"
    :contentList="contentList"
    :distribution="distribution"
    :distributionIsExpanded="distributionIsExpanded"
    :showLicensingAssistant="showLicensingAssistant"
    :showLicence="showLicence"
    :filterDateFormatEU="filterDateFormatEU"
    :showArray="showArray"
    :showNumber="showNumber"
    :showObject="showObject"
    :showObjectArray="showObjectArray"
    :appendCurrentLocaleToURL="appendCurrentLocaleToURL"
  />
</template>

<script>
import {
  has,
  isNil
} from 'lodash';
import {
  AppLink,
  Tooltip,
  getTranslationFor,
} from '@piveau/piveau-hub-ui-modules';
import DistributionContent from './DistributionContent.vue';

export default {
  name: "DistributionVisibleContent",
  components: {
    AppLink,
    Tooltip,
    DistributionContent
  },
  props: [
    'contentList',
    'distribution',
    'distributionIsExpanded',
    'showLicensingAssistant',
    'showLicence',
    'filterDateFormatEU',
    'showArray',
    'showNumber',
    'showObject',
    'showObjectArray',
    'appendCurrentLocaleToURL',
  ],
  methods: {
    has,
    isNil,
    getTranslationFor
  }
}
</script>

<style scoped>

</style>
