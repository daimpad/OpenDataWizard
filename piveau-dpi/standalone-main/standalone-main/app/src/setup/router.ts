import type { ResolvedConfig } from '@piveau/piveau-hub-ui-modules'

import type { Router, RouteRecordRaw } from 'vue-router'

import {
  ComponentLibrary,
  DataFetchingComponent,
  DataProviderInterface,
  DPIHome,
  DraftsPage,
  InputPage,
  LinkedDataViewer,
  UserCataloguesPage,
  UserProfilePage,
} from '@piveau/dpi'
import {
  Auth,
  CatalogPage,
  Catalogues,
  DatasetDetailsCategories,
  DatasetDetailsDataset,
  DatasetDetailsQuality,
  DatasetDetailsSimilarDatasets,
  Datasets,
  // Imprint,
  // PrivacyPolicy,
  decode,
  // DatasetDetails,
  MapBasic,
  MapBoundsReceiver,
  NotFound,
  SparqlSearch,
} from '@piveau/piveau-hub-ui-modules'
import { createRouter, createWebHistory } from 'vue-router'
import BayernDataProviderInterface from '@/pages/BayernDataProviderInterface.vue'
import { getLeftmostSubdomain, isOdp } from '@/utils/utils'
import { glueConfig as GLUE_CONFIG } from '../../config/user-config'
import DatasetDetails from '../components/DatasetDetails.vue'
import BayernDatasetDetailsDataset from '../components/datasetDetails/BayernDatasetDetailsDataset.vue'
import Accessibility from '../pages/Accessibility.vue'
import API from '../pages/API.vue'
import BayernCatalogPage from '../pages/BayernCatalogPage.vue'
import BayernDatasetsPage from '../pages/BayernDatasetsPage.vue'
import OdpPresencePage from '../pages/cataloguesn/OdpAboutPage.vue'
import OdpDatasetSearchPage from '../pages/cataloguesn/OdpDatasetSearchPage.vue'
import OdpLandingPage from '../pages/cataloguesn/OdpLandingPage.vue'
import DataProvider from '../pages/DataProvider.vue'
import Definition from '../pages/Definition.vue'
import Hvd from '../pages/Hvd.vue'
import Imprint from '../pages/Imprint.vue'
import Info from '../pages/Info.vue'
import Inventory from '../pages/Inventory.vue'
import LandingPage from '../pages/LandingPage.vue'
import Licenses from '../pages/Licenses.vue'

import Metadata from '../pages/Metadata.vue'
import More from '../pages/More.vue'
import Odd24 from '../pages/Odd24.vue'
import Partners from '../pages/Partners.vue'

// ODPv2
import PresencePage from '../pages/PresencePage.vue'
import PrivacyPolicy from '../pages/PrivacyPolicy.vue'

import Provisioning from '../pages/Provisioning.vue'
import TermsOfUse from '../pages/TermsOfUse.vue'
import Usage from '../pages/Usage.vue'

const title = GLUE_CONFIG.metadata?.title

function generateRoutes({ isOdp = true }) {
  let routes = []
  const defaultMeta = {
    title,
  }

  const datasetDetailsChildren = [
    {
      path: '',
      name: 'DatasetDetailsDataset',
      components: {
        datasetDetailsSubpages: BayernDatasetDetailsDataset,
      },
      meta: defaultMeta,
    },
    {
      path: 'categories',
      name: 'DatasetDetailsCategories',
      components: {
        datasetDetailsSubpages: DatasetDetailsCategories,
      },
      meta: defaultMeta,
    },
    {
      path: 'similarDatasets',
      name: 'DatasetDetailsSimilarDatasets',
      components: {
        datasetDetailsSubpages: DatasetDetailsSimilarDatasets,
      },
      meta: defaultMeta,
    },
    {
      path: 'quality',
      name: 'DatasetDetailsQuality',
      components: {
        datasetDetailsSubpages: DatasetDetailsQuality,
      },
      meta: defaultMeta,
    },
    // Uncomment if needed
    // {
    //   path: 'activityStream',
    //   name: 'DatasetDetailsActivityStream',
    //   component: {
    //     datasetDetailsSubpages: DatasetDetailsActivityStream,
    //   },
    //   meta: defaultMeta,
    // },
    // {
    //   path: 'distributions/:dist_id',
    //   name: 'DistributionDetails',
    //   component: DistributionDetails,
    //   meta: defaultMeta,
    // },
  ]

  const defaultRoutes: RouteRecordRaw[] = [
    {
      path: '/datasets/:ds_id',
      component: DatasetDetails,
      children: datasetDetailsChildren,
      meta: defaultMeta,
    },
  ]

  if (!isOdp) {
    routes = [
      {
        path: '/',
        redirect: { name: 'LandingPage' },
        meta: defaultMeta,
      },
      {
        path: '/home',
        name: 'LandingPage',
        component: LandingPage,
        meta: defaultMeta,
      },
      {
        path: '/dataprovider', // Relic from MVP, therefore redirect
        redirect: { name: 'Info' },
        meta: defaultMeta,
      },
      {
        path: '/datasets',
        name: 'Datasets',
        component: BayernDatasetsPage,
        meta: defaultMeta,
      },
      {
        path: '/catalogues',
        name: 'Catalogues',
        component: Catalogues,
        meta: defaultMeta,
      },
      {
        path: '/catalogues/:ctlg_id',
        name: 'CatalogueDetails',
        component: BayernCatalogPage,
        meta: defaultMeta,
      },
      {
        path: '/cataloguesn/:ctlg_id',
        component: PresencePage,
        children: [
          {
            path: '',
            name: 'CatalogueNDetails',
            component: OdpLandingPage,
            meta: { ...defaultMeta, key: 'catalog' },
          },
          {
            path: 'datasets',
            name: 'CatalogueNDetailsDatasetsSearch',
            component: OdpDatasetSearchPage,
            meta: { ...defaultMeta, key: 'catalog' },
          },
          {
            path: 'presence',
            name: 'CatalogueNDetailsPresence',
            component: OdpPresencePage,
            meta: { ...defaultMeta, key: 'catalog' },
          },
        ],
      },
      {
        path: '/imprint',
        name: 'Imprint',
        component: Imprint,
        meta: defaultMeta,
      },
      {
        path: '/privacypolicy',
        name: 'PrivacyPolicy',
        component: PrivacyPolicy,
        meta: defaultMeta,
      },
      {
        path: '/de/legal-notice',
        redirect: { name: 'PrivacyPolicy' },
        meta: defaultMeta,
      },
      {
        path: '/accessibility',
        name: 'Accessibility',
        component: Accessibility,
        meta: defaultMeta,
      },
      {
        path: '/info/definition',
        name: 'Definition',
        component: Definition,
        meta: defaultMeta,
      },
      {
        path: '/info/licenses',
        name: 'Licenses',
        component: Licenses,
        meta: defaultMeta,
      },
      {
        path: '/info/metadata',
        name: 'Metadata',
        component: Metadata,
        meta: defaultMeta,
      },
      {
        path: '/info/provisioning',
        name: 'Provisioning',
        component: Provisioning,
        meta: defaultMeta,
      },
      {
        path: '/usage',
        name: 'API',
        component: Usage,
        meta: defaultMeta,
      },
      {
        path: '/info',
        name: 'Info',
        component: Info,
        meta: defaultMeta,
      },
      {
        path: '/termsofuse',
        name: 'TermsOfUse',
        component: TermsOfUse,
        meta: defaultMeta,
      },
      {
        path: '/maps',
        name: 'MapBasic',
        component: MapBasic,
        meta: defaultMeta,
      },
      {
        path: '/mapsBoundsReceiver',
        name: 'MapBoundsReceiver',
        component: MapBoundsReceiver,
        meta: defaultMeta,
      },
      {
        path: '/logout',
        name: 'Logout',
        component: Auth,
        meta: defaultMeta,
      },
      {
        path: '/404',
        alias: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFound,
        meta: defaultMeta,
      },
      {
        path: '/sparql',
        name: 'SparqlSearch',
        component: SparqlSearch,
      },
      {
        path: '/partners',
        component: Partners,
        meta: defaultMeta,
      },
      {
        path: '/info/hvd',
        component: Hvd,
      },
      {
        path: '/events/open-data-day-muenchen-2024',
        component: Odd24,
      },
      {
        path: '/events/odd24',
        redirect: { name: 'Odd24' },
        meta: defaultMeta,
      },
      {
        path: '/info/inventory',
        component: Inventory,
      },
      {
        path: '/api',
        component: API,
      },
      {
        path: '/more',
        component: More,
      },
      ...defaultRoutes,
    ]
  }
  else {
    routes = [
      {
        path: '/',
        component: PresencePage,
        children: [
          {
            path: '',
            name: 'CatalogueNDetails',
            component: OdpLandingPage,
            meta: { ...defaultMeta, key: 'catalog' },
          },
          {
            path: 'datasets',
            name: 'CatalogueNDetailsDatasetsSearch',
            component: OdpDatasetSearchPage,
            meta: { ...defaultMeta, key: 'catalog' },
          },
          {
            path: 'presence',
            name: 'CatalogueNDetailsPresence',
            component: OdpPresencePage,
            meta: { ...defaultMeta, key: 'catalog' },
          },
        ],
      },
      ...defaultRoutes,
    ]
  }

  return routes
}

export async function createPvRouter({ userConfig }: { userConfig: ResolvedConfig }): Promise<Router> {
  const router = createRouter({
    history: createWebHistory('/'),
    linkActiveClass: 'active',
    // scrollBehavior(to, from, savedPosition) {
    //   if (to.matched.some(route => route.meta.scrollTop)) return {left: 0, top: 0};
    //   else if (savedPosition) return savedPosition;
    //   else return {left: 0, top: 0};
    // },
    // add scrollbehavior for vue 3 router
    scrollBehavior(to, _from, savedPosition) {
      if (to.hash) {
        return {
          el: to.hash,
          behavior: 'smooth',
        }
      }
      else if (to.matched.some(route => route.meta.scrollTop)) {
        return { left: 0, top: 0 }
      }
      else if (savedPosition) {
        return savedPosition
      }
      else {
        return { left: 0, top: 0 }
      }
    },
    routes: [
    {
      path: '/',
      redirect: { name: 'DPI-Home-HappyFlow' },
    },
    {
      path: '/logout',
      name: 'Logout',
      component: Auth,
    },
  ]
})

  if (userConfig.content.dataProviderInterface.useService) {
    router.addRoute({
      path: '/dpi/draft',
      name: 'DataProviderInterface-Draft',
      component: DraftsPage,
      meta: {
        requiresAuth: true,
      },
    }),
    router.addRoute({
      path: '/dpi/draft/:name.:format',
      name: 'DataProviderInterface-LinkedData',
      component: LinkedDataViewer,
      props: true,
      meta: {
        requiresAuth: true,
      },
    }),
    router.addRoute({
      path: '/dpi/user/',
      name: 'DataProviderInterface-UserProfile',
      component: UserProfilePage,
      meta: {
        requiresAuth: true,
      },
    }),
    router.addRoute({
      path: '/dpi/user-catalogues',
      name: 'DataProviderInterface-UserCatalogues',
      component: UserCataloguesPage,
      meta: {
        requiresAuth: true,
      },
    }),
    router.addRoute({
      path: '/dpi/edit/:catalog/:property/:id',
      name: 'DataProviderInterface-Edit',
      component: DataFetchingComponent,
      props: true,
    }),
    router.addRoute({
      path: '/dpi/home',
      name: 'DPI-Home-HappyFlow',
      component: DPIHome,
      props: true,
    }),
    router.addRoute({
      path: '/dpi/CompLib',
      name: 'DPI-Comp-Lib',
      component: ComponentLibrary,
      props: true,
    }),
    router.addRoute({
      path: '/dpi',
      name: 'DataProviderInterface',
      component: BayernDataProviderInterface,
      meta: {
        requiresAuth: true,
      },
      children: [
        {
          path: ':property',
          name: 'DataProviderInterface-Input',
          component: InputPage,
          props: true,
        },
        // {
        //   path: ":property/overview",
        //   name: "DataProviderInterface-Overview",
        //   component: OverviewPage,
        //   props: true
        // },
        // {
        //   path: ":property/:page",
        //   name: "DataProviderInterface-Input",
        //   component: InputPage,
        //   props: true,
        //   children: [
        //     {
        //       path: ":id",
        //       name: "DataProviderInterface-ID",
        //       component: InputPage,
        //       props: true,
        //     },
        //   ],
        // },
      ],
    })
  }

  // router.beforeEach((to, from, next) => {
  //   // Hash mode backward-compatibility
  //   // Fixes https://gitlab.fokus.fraunhofer.de/viaduct/organisation/issues/432
  //   if (to?.redirectedFrom?.fullPath.substring(0, 3) === '/#/') {
  //     let path = to.redirectedFrom.fullPath.substring(2);
  //     const base = `${?.routing?.routerOptions?.base}/`;
  //     if (path.startsWith(base)) {
  //       // Restore standard Vue behavior when navigated to '/#/base'
  //       // so you are redirected to '/base' instead of '/base/base'
  //       path = '/';
  //     }
  //     next({path, replace: true});
  //     return;
  //   }

  //   let isLinkedDataRequest = false;

  //   // RDF|N3|JSON-LD redirects
  //   if (/^\/(data\/)?datasets\/[a-z0-9-_]+(\.rdf|\.n3|\.jsonld|\.ttl|\.nt)/.test(to.path)) {
  //     isLinkedDataRequest = true;
  //     let locale = to.query.locale ? `&locale=${to.query.locale}` : '';
  //     window.location.href = `${userConfig.api.hubUrl}${to.path}?useNormalizedId=true${locale}`;
  //   }

  //   if (/^\/(data\/)?api\/datasets\/[a-z0-9-_]+(\.rdf|\.n3|\.jsonld|\.ttl|\.nt)/.test(to.path)) {
  //     isLinkedDataRequest = true;
  //     let locale = to.query.locale ? `?locale=${to.query.locale}` : '';
  //     let returnPath = to.path.replace('/api', '')
  //       .replace(/(\.rdf|\.n3|\.jsonld|\.ttl|\.nt)/, '')
  //       .replace('?useNormalizedId=true', '');
  //     window.location.href = `${window.location.protocol}//${window.location.host}${userConfig.routing?.routerOptions?.base}${returnPath}${locale}`;
  //   }

  //   if (isLinkedDataRequest) {
  //     // Redirect to the same page but without linked data file ending suffix
  //     // to prevent the 404 redirection due to app trying to fetch the wrong dataset id
  //     const datasetIdWithoutSuffix = (to.params?.ds_id as string).replace(/(\.rdf|\.n3|\.jsonld|\.ttl|\.nt)/, '');
  //     const newRoute = {...to, params: {...to.params, ds_id: datasetIdWithoutSuffix}};
  //     next(newRoute);
  //     return;
  //   }

  //   // Authentication
  //   if (to.matched.some(record => record.meta.requiresAuth)) {
  //     const auth = userConfig.authentication.useService
  //       ? app.config.globalProperties.$keycloak?.authenticated
  //       : null;
  //     if (!auth) {
  //       // TODO: Show unauthorized page here
  //     } else {
  //       app.config.globalProperties.$keycloak?.getRtpToken().then((rtpToken) => {
  //         const decodedAccessToken = decode(rtpToken);
  //         let isAuthenticated = false;
  //         decodedAccessToken.authorization.permissions.forEach((permission) => {
  //           if (permission.scopes.find(scope => scope === 'dataset:create')) isAuthenticated = true;
  //         });
  //         isAuthenticated
  //           ? next()
  //           : next({name: 'Datasets'});
  //       });
  //     }
  //   } else if (!to.query.locale && from.query.locale) {
  //     const pathWithCurrentLocale = `${to.path}?locale=${from.query.locale}`;
  //     next({path: pathWithCurrentLocale});
  //   } else {
  //     // document.title = to.meta.title;
  //     next();
  //   }
  // });

  return router
}
