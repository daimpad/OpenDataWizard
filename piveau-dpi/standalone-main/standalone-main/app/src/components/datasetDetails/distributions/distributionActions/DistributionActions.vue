<template>
    <div class="distribution-actions">
       <distribution-download
        v-if="showDownloadDropdown(distribution) && showAccessUrls(distribution)  && (getDistributionFormat(distribution) !== 'WMS' && getDistributionFormat(distribution) !== 'WFS' && getDistributionFormat(distribution) !== 'Atom Feed')"
        :getDownloadUrl="getDownloadUrl"
        :showAccessUrls="showAccessUrls"
        :isOnlyOneUrl="isOnlyOneUrl"
        :trackGoto="trackGoto"
        :getDistributionFormat="getDistributionFormat"
        :replaceHttp="replaceHttp"
        :distribution="distribution"
        class="distribution-action-btn distribution-download-btn"
      />
      <distribution-options-dropdown
        :showTooltipVisualiseButton="showTooltipVisualiseButton"
        :isUrlInvalid="isUrlInvalid"
        :getVisualisationLink="getVisualisationLink"
        :distribution="distribution"
        :openIfValidUrl="openIfValidUrl"
        :previewLinkCallback="previewLinkCallback"
        class="distribution-action-btn distribution-options-dropdown-btn"
      />
      <linked-data-buttons-dropdown
        :distributions="distributions"
        :distribution="distribution"
        class="distribution-action-btn distribution-linked-data-buttons-dropdown"
      />
      <div v-if="showValidateButton">
        <app-link v-if="showValidateButton" class="btn-sm pt-0" :to="{ name: 'DatasetDetailsQuality', query: { locale: $route.query.locale, validate: distribution.id }}">
            Validate
        </app-link>
      </div>
    </div>
  </template>

  <script>
  import { AppLink } from "@piveau/piveau-hub-ui-modules"
  import DistributionOptionsDropdown from "./DistributionOptionsDropdown.vue"
  import DistributionDownload from "./DistributionDownload.vue"
  import LinkedDataButtonsDropdown from "./LinkedDataButtonsDropdown.vue"

  export default {
    name: "DistributionActions",
    components: {AppLink, LinkedDataButtonsDropdown, DistributionDownload, DistributionOptionsDropdown},
    props: {
      distribution: Object,
      distributions: Object,
      isUrlInvalid: Function,
      getVisualisationLink: Function,
      showTooltipVisualiseButton: Function,
      previewLinkCallback: Function,
      openIfValidUrl: Function,
      showDownloadDropdown: Function,
      getDownloadUrl: Function,
      showAccessUrls: Function,
      isOnlyOneUrl: Function,
      trackGoto: Function,
      getDistributionFormat: Function,
      replaceHttp: Function,
    },
    computed: {
      showValidateButton() {
        return this.$env?.datasetDetails?.distributions?.showValidationButton;
      }
    }
  }
  </script>

  <style lang="scss">

  </style>
