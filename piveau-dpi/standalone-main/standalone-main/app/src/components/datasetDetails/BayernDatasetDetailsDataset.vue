<template>
  <DatasetDetailsDataset
    :distributionVisibleContent="[
      'updateDate',
      'licenseAttributionByText',
    ]"
    :distributionExpandedContent="[
      'license',
      'releaseDate',
      'language',
      'availability',
      'status',
      'rights',
      'mediaType',
      'byteSize',
      'checksum',
      'pages',
      'type',
      'compressFormat',
      'packageFormat',
      'hasPolicy',
      'conformsTo',
      'spatialResolutionInMeters',
      'temporalResolution',
    ]"
  />
</template>

<script lang="ts">
  // @ts-nocheck
  /* eslint-disable no-confusing-arrow, no-nested-ternary, no-return-assign, no-confusing-arrow */
  import { DatasetDetailsDataset } from '@piveau/piveau-hub-ui-modules';

  export default {
    name: 'BayernDatasetDetailsDataset',
    components: {
      DatasetDetailsDataset
    }
  };
</script>

<style lang="scss" scoped>
  // specified in styles/by/_bayern-dataset-details-page.scss
</style>
