<template>
  <div class=" position-relative d-inline-block ml-1 mb-1">
    <div class="distribution-download-btns-container d-flex">
        <!-- accessURL button -->
        <button class="by-btn-primary-medium-light"
                type="button"
            v-if="showAccessUrls(distribution) && distribution.accessUrl[0] !== distribution.downloadUrls[0] && (getDistributionFormat(distribution) !== 'WMS' && getDistributionFormat(distribution) !== 'WFS' && getDistributionFormat(distribution) !== 'Atom Feed')">
          <app-link class="text-decoration-none d-flex justify-content-between w-100 text-white"
                    :to="replaceHttp(distribution.accessUrl[0])"
                    target="_blank"
                    rel="dcat:distribution noopener"
                    :matomo-track-download="{ format: distribution?.format?.id }"
                    @after-click="$emit('trackGoto')">
                    {{ "Zum Download" }}
                    <img class="ml-2" src="../../../../assets/icon/icon-arrow-square-out-neutral.svg" alt="Download Icon" />
                  </app-link>
        </button>
        <!-- DownloadURLs button(s) -->
        <button class="by-btn-primary-medium-light"
          v-for="(downloadURL, i) in distribution.downloadUrls"
          :key="i"
          type="button"
          v-if="(getDistributionFormat(distribution) !== 'WMS' && getDistributionFormat(distribution) !== 'WFS' && getDistributionFormat(distribution) !== 'Atom Feed')">
          <app-link class="text-decoration-none d-flex justify-content-between w-100 text-white"
                    :to="replaceHttp(downloadURL)"
                    target="_blank"
                    :matomo-track-download="{ format: distribution?.format?.id }"
                    @after-click="$emit('trackGoto')">
              {{ "Zum Download" }}
              <img class="ml-2" src="../../../../assets/icon/icon-arrow-square-out-neutral.svg" alt="Download Icon" />
            </app-link>
        </button>
    </div>
  </div>
</template>

<script>
// import DistributionDropdownDownload from "@/modules/datasetDetails/distributions/distributionActions/DistributionDropdownDownload";
// import DistributionDownloadAs from "@/modules/datasetDetails/distributions/distributionActions/DistributionDownloadAs";
// import AppLink from "@/modules/widgets/AppLink";
import {
  AppLink,
  // DistributionDownloadAs,
  // DistributionDropdownDownload
} from "@piveau/piveau-hub-ui-modules"

import DistributionDownloadAs from "./DistributionDownloadAs.vue"
import DistributionDropdownDownload from "./DistributionDropdownDownload.vue"

export default {
  name: "DistributionDownload",
  props: [
    "getDownloadUrl",
    "showAccessUrls",
    "isOnlyOneUrl",
    "trackGoto",
    "replaceHttp",
    "getDistributionFormat",
    "distribution"
  ],
  components: {
    DistributionDropdownDownload,
    DistributionDownloadAs,
    AppLink
  },
  data() {
    return {

    }
  },
  methods: {
    setClipboard(value) {
      const input = document.createElement('INPUT');
      // input.style = "position: absolute; left: -1000px; top: -1000px";
      input.value = value;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
    },
  }
}
</script>

<style scoped lang="scss">
  .access-url-btn {
    padding-right: 8px !important;
  }
</style>
