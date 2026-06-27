<template>
  <div v-if="showGeoLink(distribution)">
    <app-link class="text-decoration-none d-flex justify-content-between w-100 text-white"
      :to="getGeoLink"
      target="_blank"
      @after-click="$emit('track-link', getGeoLink, 'link')"
      v-if="showGeoLink(distribution)">
        <button class="primary-button distribution-btn">
          <small class="px-2">{{ $t('message.datasetDetails.geoVisualisation') }}</small>
          <img src="../../../../assets/icon/icon-arrow-square-out-neutral.svg" alt="Square out Icon" />
        </button>
    </app-link>
  </div>
</template>

<script>
// import Dropdown from "@/modules/widgets/Dropdown";
// import AppLink from "@/modules/widgets/AppLink";
import {has, isNil} from "lodash";
import {mapGetters} from "vuex";
import {Dropdown, AppLink} from "@piveau/piveau-hub-ui-modules"


export default {
  name: "DistributionOptionsDropdown",
  components: {
    Dropdown,
    AppLink
  },
  props: [
    'showTooltipVisualiseButton',
    'isUrlInvalid',
    'getVisualisationLink',
    'distribution',
    'openIfValidUrl',
    'previewLinkCallback'
  ],
  data() {
    return {
      visualisationLinkFormats: [
        // 'csv',
        // 'xlsx',
        // 'xls',
      ],
      geoLinkFormats: {
        wms: 'WMS',
      },
      geoLink: this.$env?.datasetDetails?.distributions?.geoLink,
    };
  },
  computed: {
    ...mapGetters('datasetDetails', [
      'getCatalog',
      'getID'
    ]),
    getGeoLink() {
      const format = this.distribution.format.label;
      let f = format.toLowerCase();
      // Use correct Case Sensitive strings
      f = this.geoLinkFormats[f];
      if (this.geoLink) {
        const geoLinkVariables = {
          catalog: this.getCatalog.id,
          dataset: this.getID,
          distribution: this.distribution.id,
          type: f,
          lang: this.$route.query.locale,
          accessUrl: this.distribution?.accessUrl[0],
          title: this.distribution?.title[this.$route.query.locale],
        }
        // Inject variables into geo link
        for (let linkVariable in geoLinkVariables) {
          this.geoLink = this.geoLink.replace(`{${linkVariable}}`, geoLinkVariables[linkVariable]);
        }
        // Return Geo Visualisation Link
        return this.geoLink;
        // return `/geo-viewer/?dataset=${distributionID}&type=${f}&lang=${this.$route.query.locale}`;
      }
      // Return default Geo Visualisation Link if no link in user-config provided
      return `/geo-viewer/?catalog=${this.getCatalog.id}&dataset=${this.getID}&distribution=${this.distribution.id}&type=${f}&lang=${this.$route.query.locale}`;
    }
  },
  methods: {
    showOptionsDropdown(distribution) {
      return this.showVisualisationLink(distribution) || this.showGeoLink(distribution);
    },
    showGeoLink(distribution) {
      if (!has(distribution, 'format.label') || isNil(distribution.format.label) || !has(distribution, 'id') || isNil(distribution.id) || !has(distribution, 'accessUrl[0]')) return false;
      const f = distribution.format.label.toLowerCase();
      return Object.keys(this.geoLinkFormats).includes(f);
    },
    showVisualisationLink(distribution) {
      if (!has(distribution, 'format.label') || isNil(distribution?.format?.label)
        || (isNil(distribution?.downloadUrls[0]) && isNil(distribution?.accessUrl[0]))) return false;
      const f = distribution?.format?.id?.toLowerCase();
      return f && this.visualisationLinkFormats.includes(f);
    }
  }
}
</script>

<style scoped lang="scss">
  .disabled {
    cursor: not-allowed;
  }

  .primary-button.distribution-btn {
    padding: 0 !important ;
    margin: initial;
    height: 30px;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    background-color: transparent;
    color: var(--bayern);
    font-size: 13px;
    font-weight: 600 !important;
    line-height: 18px;
    border: 2px solid var(--bayern);
    white-space: nowrap;
  }

  a.distribution-link {
    padding: 0;
  }
  a.distribution-link:hover {
    text-decoration: none;
  }
  .primary-button.distribution-btn small {
    color: var(--bayern);
    font-size: 13px;
    font-weight: 600 !important;
    line-height: 18px;
  }

</style>
