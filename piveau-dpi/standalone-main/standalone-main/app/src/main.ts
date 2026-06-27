// import VueProgressBar from 'vue-progressbar';
import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';
// import VueFormulate from '@braid/vue-formulate';
// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
// import Meta from 'vue-meta';
// import VeeValidate from 'vee-validate';
// import PiveauHeaderFooter from '@open-data-bayern/piveau-header-footer';
// import UniversalPiwik from '@piveau/piveau-universal-piwik';
// import AppToast from '@/components/AppToast';
// Import v-select
// Import i18n validation messages for vueformulate
// import {
// ca, cs, da, nl, de, en, fr, hu, it, lt, nb, pl, pt, ru, sr, sk, es, tr, sv,
// } from '@braid/vue-formulate-i18n';

import '@fontsource-variable/inter';
import '@fontsource-variable/space-grotesk';

import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faGoogle,
  faGooglePlus,
  faGooglePlusG,
  faFacebook,
  faFacebookF,
  faInstagram,
  faTwitter,
  faLinkedinIn,
} from '@fortawesome/free-brands-svg-icons';
import {
  faComment,
  faExternalLinkAlt,
  faPlus,
  faMinus,
  faArrowDown,
  faArrowUp,
  faInfoCircle,
  faExclamationTriangle,
} from '@fortawesome/free-solid-svg-icons';
// import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
// import VuePositionSticky from 'vue-position-sticky';
// Import main user configurations (glueConfig) and i18n configurations
import { glueConfig as GLUE_CONFIG, i18n as I18N_CONFIG } from '../config/user-config';
import runtimeConfig from '../config/runtime-config';
import { createRouter } from './router';
import App from './App.vue';
import {
  dateFilters,
  AppSnackbar,
  AppConfirmationDialog,
 // vueKeycloak,
  bulkDownloadCorsProxyService,
  corsProxyService,
  runtimeConfigurationService,
  store,
  InfoSlot,
  ConditionalInput,
  AutocompleteInput,
  UniqueIdentifierInput,
  CustomNumber,
  CustomURL,
  FileUpload,
  DatePicker,
  DateTimePicker,
  configureModules,
  SelectedFacetsOverview
} from '@piveau/piveau-hub-ui-modules';
import DatasetDetailsHeader from "./components/datasetDetails/DatasetDetailsHeader.vue";
import Distribution from "./components/datasetDetails/distributions/Distribution.vue";
import DistributionDetails from "./components/datasetDetails/distributions/DistributionDetails.vue";
import DatasetDetailsDescription from "./components/datasetDetails/DatasetDetailsDescription.vue";
import DatasetDetailsProperties from "./components/datasetDetails/DatasetDetailsProperties.vue";
import DatasetDetailsFeatureHeader from "./components/datasetDetails/DatasetDetailsFeatureHeader.vue";
import DatasetDetailsFeatures from "./components/datasetDetails/DatasetDetailsFeatures.vue";

// Import custom services
import vueKeycloak from "./services/keycloakService"

const app = createApp(App);

// app.config.devtools = true;

app.use(runtimeConfigurationService, runtimeConfig, { baseConfig: GLUE_CONFIG, debug: false, useExperimentalRuntimeParser: true });
const env = Vue.prototype.$env;

configureModules({
  components: {
    DatasetDetailsHeader: DatasetDetailsHeader,
    DatasetDetailsDescription: DatasetDetailsDescription,
    DistributionDetails: DistributionDetails,
    Distribution: Distribution,
    DatasetDetailsProperties: DatasetDetailsProperties,
    DatasetDetailsFeatureHeader: DatasetDetailsFeatureHeader,
    DatasetDetailsFeatures: DatasetDetailsFeatures
  },
  services: GLUE_CONFIG.services,
  serviceParams: {
    baseUrl: env.api.baseUrl,
    qualityBaseUrl: env.api.qualityBaseUrl,
    similarityBaseUrl: env.api.similarityBaseUrl,
    similarityServiceName: env.api.similarityServiceName,
    gazetteerBaseUrl: env.api.gazetteerBaseUrl,
    hubUrl: env.api.hubUrl,
    keycloak: env.authentication.keycloak,
    rtp: env.authentication.rtp,
    useAuthService: env.authentication.useService,
    authToken: env.authentication.authToken,
    defaultScoringFacets: env.content.datasets.facets.scoringFacets.defaultScoringFacets,
  }
});


Vue.component('InfoSlot', InfoSlot);
Vue.component('ConditionalInput', ConditionalInput);
Vue.component('AutocompleteInput', AutocompleteInput);
Vue.component('UniqueIdentifierInput', UniqueIdentifierInput);
Vue.component('CustomNumber', CustomNumber);
Vue.component('CustomURL', CustomURL)
Vue.component('FileUpload', FileUpload);
Vue.component('DatePicker', DatePicker);
Vue.component('DateTimePicker', DateTimePicker);

// Vue.component('AppToast', AppToast);
Vue.component('AppSnackbar', AppSnackbar);
Vue.component('AppConfirmationDialog', AppConfirmationDialog);

// DEU Redesign Components
Vue.component('SelectedFacetsOverview', SelectedFacetsOverview);

// eslint-disable-next-line @typescript-eslint/no-var-requires

import VueCookie from 'vue-cookie';

Vue.use(VueCookie);

Vue.use(VueFormulate, {
  // plugins: [ca, cs, da, nl, de, en, fr, hu, it, lt, nb, pl, pt, ru, sr, sk, es, tr, sv],
  validationNameStrategy: vm => vm.context.label,
  // Define our custom slot component(s)
  slotComponents: {
    label: 'InfoSlot',
  },
  // Define any props we want to pass to our slot component
  slotProps: {
    label: ['info', 'collapsed'],
  },
  components: {
    ConditionalInput,
  },
  library: {
    fileupload: {
      classification: 'text',
      component: 'FileUpload',
    },
    'conditional-input': {
      classification: 'text',
      component: 'ConditionalInput',
      slotProps: {
        component: ['data'],
      },
    },
    'autocomplete-input': {
      classification: 'text',
      component: 'AutocompleteInput',
      slotProps: {
        component: ['voc', 'multiple'],
      },
    },
    'unique-identifier-input': {
      classification: 'text',
      component: 'UniqueIdentifierInput',
    },
    'custom-url': {
      classification: 'text',
      component: 'CustomURL',
      slotProps: {
        component: ['context'],
      },
    },
    'custom-number': {
      classification: 'text',
      component: 'CustomNumber',
      slotProps: {
        component: ['context'],
      },
    },
    'date-picker': {
      classification: 'date',
      component: 'DatePicker',
    },
    'datetime-picker': {
      classification: 'datetime-local',
      component: 'DateTimePicker',
    },
  },
});

Vue.use(corsProxyService, env.api.vueAppCorsproxyApiUrl);

Vue.use(bulkDownloadCorsProxyService, GLUE_CONFIG, env.api.vueAppCorsproxyApiUrl);

function getCookie(cname) {
  const name = cname + "=";
  const decodedCookie = decodeURIComponent(document.cookie);
  const ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

// Configured language
const LOCALE = env.languages.locale;
const FALLBACKLOCALE = env.languages.fallbackLocale;

// eslint-disable-next-line
export const i18n = createI18n({
  locale: LOCALE,
  fallbackLocale: FALLBACKLOCALE,
  messages: I18N_CONFIG,
  silentTranslationWarn: true,
});

// Make i18n globally available
Vue.use(i18n);
Vue.i18n = i18n;

// Set locale for dateFilters
dateFilters.setLocale(LOCALE);

// Vue-meta setup
Vue.use(Meta, {
  refreshOnceOnNavigation: true,
  debounceWait: 100,
});

// Bootstrap requirements to use js-features of bs-components
import('popper.js');

import('bootstrap');

import('@open-data-bayern/piveau-header-footer/dist/piveau-header-footer.css');
import('./styles/styles.scss');
import('./styles/dpi_styles.scss');

import('../node_modules/@piveau/piveau-hub-ui-modules/dist/piveau-hub-ui-modules.css')

import('@fortawesome/fontawesome-free/css/all.css');


// OpenStreetMaps popup styles
import('leaflet/dist/leaflet.css');

// Vue-progressbar setup
const progressBarOptions = {
  thickness: '5px',
  autoRevert: false,
  transition: {
    speed: '1.0s',
    opacity: '0.5s',
    termination: 1000,
  },
};
Vue.use(VueProgressBar, progressBarOptions);

// Vee-validate (form validation) setup
Vue.use(VeeValidate, { errorBagName: 'vee_validator_errors' });

// Vue-inject setup
Vue.use(injector, { components: true });

Vue.use(PiveauHeaderFooter);

Vue.use(VuePositionSticky);

// Sync store and router
// sync(store, router);

// Add Font Awesome Icons
library.add(faGoogle, faGooglePlus, faGooglePlusG, faFacebook, faFacebookF, faInstagram, faTwitter, faLinkedinIn, faComment, faExternalLinkAlt, faPlus, faMinus, faArrowDown, faArrowUp, faInfoCircle, faExclamationTriangle);
Vue.component('font-awesome-icon', FontAwesomeIcon);

Vue.config.productionTip = false;

// Creates the root Vue instance
const createVueApp = async () => {
  const hubSearchApiBaseUrl = env.api.baseUrl;
  const router = await createRouter(hubSearchApiBaseUrl);


  const { siteId, trackerUrl } = env.tracker;
  Vue.use(UniversalPiwik, {
    router,
    isPiwikPro: false,
    trackerUrl,
    siteId,
    immediate: false,
    verbose: true,
    debug: import.meta.env.MODE === 'development',
    useSuspendFeature: false,
    requireConsent: 'cookieConsent',
    pageViewOptions: {
      // Set this to true as long as navigating to the /datasets/ route
      // adds a 'minScore' query to prevent duplicated tracking
      useDatasetsMinScoreFix: false,
      // Send empty dataset metadata for every page view
      // See https://gitlab.fokus.fraunhofer.de/piveau/organisation/piveau-scrum-board/-/issues/2098
      beforeTrackPageView: (to, from, tracker) => {
        // if (to.name !== 'DatasetDetailsDataset') {
        //   tracker.trackDatasetDetailsPageView(null, null, {
        //     dataset_AccessRights: '',
        //     dataset_AccrualPeriodicity: '',
        //     dataset_Catalog: '',
        //     dataset_ID: '',
        //     dataset_Publisher: '',
        //     dataset_Title: '',
        //   });
        // }
        if (getCookie('noTracking') == "true") {
          _paq.push(['requireCookieConsent'])
        }
      },
      documentTitleResolver(to, from, title) {
        return `${document.domain}/${title}`
      }
    },
  });

  const app = new Vue({
    router,
    store,
    i18n,
    render: h => h(App),
  });

  return app;
};

// Promise that timeouts after x seconds
let waitTimeoutHandle;
const wait = ms => new Promise((resolve, reject) => waitTimeoutHandle = setTimeout(() => {
  reject(new Error(`Keycloak failed to load after a timeout of ${ms} ms`));
}, ms));

const useVueWithKeycloakPromise = () => new Promise((resolve, reject) => {
  Vue.use(vueKeycloak, {
    config: {
      rtp: env.authentication.rtp,
      ...env.authentication.keycloak,
    },
    init: {
      ...window.Cypress && { checkLoginIframe: !window.Cypress },
      onLoad: 'check-sso',
      silentCheckSsoRedirectUri: `${window.location.origin}${process.env.buildconf.BASE_PATH}static/silent-check-sso.html`,
    },
    onReady: () => {
      resolve();
      if (waitTimeoutHandle) clearTimeout(waitTimeoutHandle);
    },
    onInitError: reject
  });
});

// Race promises
// Timeouts after ms seconds for error handling
// This is a workaround to keycloak-js adapter not passing exceptions properly in silent sso mode
// This issue is fixed via keycloak-js@15
// See https://github.com/keycloak/keycloak/pull/8161
// todo: refactor this when keycloak 15.x or higher backend is installed
const useVueWithKeycloakWithTimeout = ms => Promise.race([
  useVueWithKeycloakPromise(),
  wait(ms),
]);

// Attempt to load Vue with Keycloak using recover mechanism
(async () => {
  if (!env.authentication.useService) {
    (await createVueApp()).$mount('#app');
    return {};
  }

  try {
    // Load Keycloak
    await useVueWithKeycloakWithTimeout(window.Cypress ? 200 : 1000);
  } catch (ex) {
    // eslint-disable-next-line no-console
    console.error(ex);
  } finally {
    // Initialize Vue, even if Keycloak failed to load
    (await createVueApp()).$mount('#app');
  }

  return {};
})();
