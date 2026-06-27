import {
  store, configureModules,
  ResolvedConfig
} from '@piveau/piveau-hub-ui-modules';
import { App } from 'vue';

import DatasetDetailsHeader from "../components/datasetDetails/DatasetDetailsHeader.vue";
import Distribution from "../components/datasetDetails/distributions/Distribution.vue";
import DistributionDetails from "../components/datasetDetails/distributions/DistributionDetails.vue";
import DatasetDetailsDescription from "../components/datasetDetails/DatasetDetailsDescription.vue";
import DatasetDetailsProperties from "../components/datasetDetails/DatasetDetailsProperties.vue";
import DatasetDetailsFeatureHeader from "../components/datasetDetails/DatasetDetailsFeatureHeader.vue";
import DatasetDetailsFeatures from "../components/datasetDetails/DatasetDetailsFeatures.vue";

export function setupPiveauHubUiModules(app: App, userConfig: ResolvedConfig) {
  return configureModules(app, store, {
    components: {
      DatasetDetailsHeader,
      DatasetDetailsDescription,
      DistributionDetails,
      Distribution,
      DatasetDetailsProperties,
      DatasetDetailsFeatureHeader,
      DatasetDetailsFeatures
    },
    serviceParams: {
      baseUrl: userConfig.api.baseUrl,
      qualityBaseUrl: userConfig.api.qualityBaseUrl,
      similarityBaseUrl: userConfig.api.similarityBaseUrl,
      gazetteerBaseUrl: userConfig.api.gazetteerBaseUrl,
      hubUrl: userConfig.api.hubUrl,
      keycloak: userConfig.authentication.keycloak,
      rtp: userConfig.authentication.rtp,
      useAuthService: userConfig.authentication.useService,
      authToken: userConfig.authentication.authToken,
      defaultScoringFacets: userConfig.content.datasets.facets.scoringFacets.defaultScoringFacets,
    }
  });
}
