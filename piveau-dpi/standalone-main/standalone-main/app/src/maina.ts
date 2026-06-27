import '@formkit/themes/genesis'

import { createApp } from "vue";
import Appa from './Appa.vue';
import { createStore } from 'vuex'
import {
  // dateFilters,
  AppSnackbar,
  AppConfirmationDialog,
  bulkDownloadCorsProxyService ,
  corsProxyService,
  runtimeConfigurationService,
  // store,
  // InfoSlot,
  // ConditionalInput,
  // AutocompleteInput,
  // CustomNumber,
  // CustomURL,
  // UniqueIdentifierInput,
  // Groupedinput,
  // FileUpload,
  // DatePicker,
  // DateTimePicker,
  SelectedFacetsOverview,
  // vueKeycloak,
  helpers,
} from '@piveau/piveau-hub-ui-modules';
import '@piveau/piveau-hub-ui-modules/styles';

import { userConfigShimPlugin, store } from '@piveau/dpi'

import '#dpi-css';
import '@fontsource-variable/inter';
import '@fontsource-variable/space-grotesk';
import './styles/styles.scss';

import 'jquery';
import '@popperjs/core';
import 'bootstrap';
// import 'leaflet/dist/leaflet.css';


import runtimeConfig from '../config/runtime-config';
import { glueConfig as GLUE_CONFIG, i18n as messages } from '../config/user-config';
const stickyLocale = helpers.createStickyLocale('de');

import VueClickAway from "vue3-click-away";
import { i18n } from "./setup/i18n";
import { setupFontawesome } from "./setup/fontawesome";
import { setupProgressbar } from "./setup/progressbar";
import { setupMeta } from "./setup/meta";
import { setupPiwik } from "./setup/piwik";
import { setupSkeletor } from "./setup/vue-skeletor";
import { setupHeaderFooter } from "./setup/setupHeaderFooter";
// import { setupPiveauHubUiModules } from "./setup/setupPiveauHubUiModules";
import { setupFormKit } from "./setup/formkit";
import keycloakPlugin from './services/keycloakService';
import { createPvRouter } from './setup/router';


const app = createApp(Appa)

app.use(runtimeConfigurationService, runtimeConfig, { baseConfig: GLUE_CONFIG, debug: true, useExperimentalRuntimeParser: true });

const userConfig = app.config.globalProperties.$env;
app.use(userConfigShimPlugin, userConfig)

const hubSearchApiBaseUrl = userConfig.api.baseUrl;
const host = window.location.host;
const router = await createPvRouter({
  hubSearchApiBaseUrl,
  host,
  userConfig,
});
router.beforeEach(stickyLocale);
app.use(router);
setupFontawesome(app);
setupProgressbar(app);
setupSkeletor(app);
setupMeta(app);
setupHeaderFooter(app);
// setupPiveauHubUiModules(app, userConfig);
setupFormKit(app);
setupPiwik(app, router, userConfig)

app.use(VueClickAway);
// Vue.use(corsProxyService, env.api.vueAppCorsproxyApiUrl);

app.use(corsProxyService, userConfig.api.corsproxyApiUrl)
app.use(bulkDownloadCorsProxyService, userConfig, userConfig.api.corsproxyApiUrl);

console.log('store', store)
app.use(store);

const i18nInstance = i18n({
  locale: userConfig.languages.locale,
  fallbackLocale: userConfig.languages.fallbackLocale,
  messages,
})
app.use(i18nInstance);


// app.component('AppSnackbar', AppSnackbar);
// app.component('AppConfirmationDialog', AppConfirmationDialog);
// app.component('SelectedFacetsOverview', SelectedFacetsOverview);

app.config.globalProperties.i18n = i18nInstance;

if (userConfig.authentication.useService) {
  app.use(keycloakPlugin, {
    config: {
      rtp: userConfig.authentication.rtp,
      ...userConfig.authentication.keycloak,
    },
    init: {
      ...userConfig.authentication.keycloakInit,
      onLoad: 'login-required',
      pkceMethod: 'S256',
      flow: 'standard',
      silentCheckSsoRedirectUri: undefined,
      responseMode: 'fragment',
      checkLoginIframe: false,
    },
    onReady: async () => {
      console.log("Keycloak uloaded");
      app.mount('#app');
    },
    onInitError: async (error: Error) => {
      console.log("Keycloak dloaded", error);
      app.mount('#app');
    }
  });
} else {
  app.mount('#app');
}
