import {
  uploadService,
  datasetService,
  catalogService,
  gazetteerService
} from '@piveau/piveau-hub-ui-modules';

import i18n from './i18n';

const glueConfig = {
  api: {
    baseUrl: 'https://staging.bydata.de/api/hub/search/',
    // baseUrl: 'https://ppe.data.europa.eu/api/hub/search/',
    // baseUrl: 'https://piveau-hub-search-data-europa-eu.apps.osc.fokus.fraunhofer.de/',

    hubUrl: 'https://staging.bydata.de/api/hub/repo/',
    // hubUrl: 'https://data.europa.eu/api/hub/repo/',
    // hubUrl: 'https://ppe.data.europa.eu/api/hub/repo/',
    // hubUrl: 'https://piveau-hub-repo-data-europa-eu.apps.osc.fokus.fraunhofer.de/',

    qualityBaseUrl: 'https://data.europa.eu/api/mqa/cache/',
    // qualityBaseUrl: 'https://ppe.data.europa.eu/api/mqa/cache/',
    // qualityBaseUrl: 'https://piveau-metrics-cache-data-europa-eu.apps.osc.fokus.fraunhofer.de/',

    similarityBaseUrl: 'https://opendata.bayern/api/metrics/similarity/similarity/',
    // similarityBaseUrl: 'https://ppe.data.europa.eu/api/similarities/',
    // similarityBaseUrl: 'https://piveau-metrics-dataset-similarities-data-europa-eu.apps.osc.fokus.fraunhofer.de/',


    similarityServiceName: 'similarity',

    fileUploadUrl: 'https://data.europa.eu/api/hub/store/',
    // fileUploadUrl: 'https://ppe.data.europa.eu/api/hub/store/',
    // fileUploadUrl: 'https://piveau-hub-store-data-europa-eu.apps.osc.fokus.fraunhofer.de/',

    sparqlUrl: 'https://data.europa.eu/sparql',
    gazetteerBaseUrl: 'https://data.europa.eu/api/hub/search/gazetteer/',
    catalogBaseUrl: 'https://europeandataportal.eu/',
    vueAppCorsproxyApiUrl: 'https://piveau-corsproxy-piveau.apps.osc.fokus.fraunhofer.de',
  },
  authentication: {
    useService: true,
    login: {
      useLogin: true,

      loginTitle: 'Login',
      loginURL: '/login',
      loginRedirectUri: '/',

      logoutTitle: 'Abmelden',
      logoutURL: '/logout',
      logoutRedirectUri: '/',
    },
    keycloak: {
      realm: 'piveau',
      clientId: 'piveau-hub-ui',
      url: 'https://keycloak.staging.bydata.de',

      // TODO: Do we need to include these properties? They seem to be default values that never change #2763
      'ssl-required': 'external',
      'public-client': true,
      'verify-token-audience': true,
      'use-resource-role-mappings': true,
      'confidential-port': 0,
    },
    rtp: {
      grand_type: 'urn:ietf:params:oauth:grant-type:uma-ticket',
      audience: 'piveau-hub-repo',
    },
    authToken: '',
  },
  routing: {
    routerOptions: {
      base: '/', // TODO: Include piveau-header-footer instead of deu-header-footer to make test app working with default base path #2765
      mode: 'history',
    },
    navigation: {
      showSparql: false,
    },
    pagination: {
      usePagination: true,
      usePaginationArrows: true,
      useItemsPerPage: true,
      defaultItemsPerPage: 10, // TODO: Make use of this property #2764
      defaultItemsPerPageOptions: [5, 10, 25, 50],
    },
  },
  metadata: {
    title: 'open bydata',
    description: 'Das Open-Data-Portal für Bayern: Offene Daten für innovative Projekte - und Unterstützung für die Verwaltung, um noch mehr Daten zu teilen.',
    keywords: 'Open Data',
  },
  datasetDetails: {
    distributions: {
      geoLink: 'https://atlas.bayern.de/?l=atkis,{accessUrl}||{title}',
    }
  },
  content: {
    datasets: {
      useSort: true,
      useFeed: true,
      useCatalogs: true,
      followKeywordLinks: 'nofollow',
      maxKeywordLength: 15,
      facets: {
        useDatasetFacets: true,
        useDatasetFacetsMap: false,
        showClearButton: false,
        showFacetsTitle: false,
        cutoff: 6 ,
        MIN_FACET_LIMIT: 5,
        MAX_FACET_LIMIT: 150,
        FACET_OPERATORS: Object.freeze({ or: 'OR', and: 'AND' }),
        FACET_GROUP_OPERATORS: Object.freeze({ or: 'OR', and: 'AND' }),
        defaultFacetOrder: ['categories', 'publisher', 'format', 'license', 'catalog', 'country', 'is_hvd'],
        scoringFacets: {
          useScoringFacets: false, // TODO: Make use of this property #2764
          defaultScoringFacets: {
            excellentScoring: {
              id: 'excellentScoring',
              title: 'Excellent',
              count: 0,
              minScoring: 351,
              maxScoring: 405,
            },
            goodScoring: {
              id: 'goodScoring',
              title: 'Good',
              count: 0,
              minScoring: 221,
              maxScoring: 350,
            },
            sufficientScoring: {
              id: 'sufficientScoring',
              title: 'Sufficient',
              count: 0,
              minScoring: 121,
              maxScoring: 220,
            },
            badScoring: {
              id: 'badScoring',
              title: 'Any',
              count: 0,
              minScoring: 0,
              maxScoring: 120,
            },
          },
        },
      },
    },
    catalogs: {
      useSort: true, // TODO: Make use of this property #2764
      useCatalogCountries: true,
      defaultCatalogImagePath: '/flags',
      defaultCatalogCountryID: 'eu',
      defaultCatalogID: 'european-union-open-data-portal',
      facets: {
        useCatalogFacets: true,
        showClearButton: false,
        showFacetsTitle: false,
        cutoff: 5,
        MIN_FACET_LIMIT: 50,
        MAX_FACET_LIMIT: 100,
        FACET_OPERATORS: Object.freeze({ or: 'OR', and: 'AND' }),
        FACET_GROUP_OPERATORS: Object.freeze({ or: 'OR', and: 'AND' }),
        defaultFacetOrder: ['country'],
      },
    },
    datasetDetails: {
      header: {
        navigation: "top",
        hidePublisher: false,
        hideDate: false
      },
      keywords: {
        isVisible: false,
        showTitle: false,
        collapsed: true,  // displayAll
      },
      categoriesKey: {
        isVisible: true,
        collapsed: true,  // displayAll
      },
      description: {
        enableMarkdownInterpretation: true,
      },
      distributions: {
        displayAll: false,
        displayCount: 7,
        incrementSteps: [10, 50],
        descriptionMaxLines: 3,
        descriptionMaxChars: 1000,
        showValidationButton: false, // TODO: Make use of this property #2764
        geoLink: 'https://geoportal.bayern.de/bayernatlas/?layers={type}||{accessUrl}',

      },
      downloadAs: {
        // If true, enable it
        enable: false,
        // Corsproxy url
        proxyUrl: 'https://piveau-corsproxy-piveau.apps.osc.fokus.fraunhofer.de',
        // Convertion url
        url: 'https://piveau-fifoc-piveau.apps.osc.fokus.fraunhofer.de/v1/convert',
        // Converion formats
        conversionFormats: [
          { sourceFileFormat: 'HTML', targetFileFormat: [ 'html', 'pdf', 'docx', 'json', 'odt', 'rtf' ]},
          { sourceFileFormat: 'CSV', targetFileFormat: [ 'csv', 'docx', 'html', 'json', 'odt', 'rtf', 'xls', 'xlsx', 'xml']},
          { sourceFileFormat: 'JSON', targetFileFormat: [ 'json', 'xml', ]},
          { sourceFileFormat: 'ODT', targetFileFormat: [ 'odt', 'docx', 'html', 'json', 'rtf' ]},
          { sourceFileFormat: 'DOCX', targetFileFormat: [ 'docx', 'pptx', 'odt', 'pdf', 'txt', 'html', 'json', 'odt', 'rtf']},
          { sourceFileFormat: 'XLSX', targetFileFormat: [ 'xlsx', 'csv',]},
          { sourceFileFormat: 'XLS', targetFileFormat: [ 'xls', 'csv',]},
          { sourceFileFormat: 'PDF', targetFileFormat: [ 'pdf', 'txt',]}
        ]
      },

      pages: {
        isVisible: false,
        displayAll: false,
        displayCount: 7,
        incrementSteps: [10, 50],
        descriptionMaxLines: 3,
        descriptionMaxChars: 250,
      },
      visualisations: {
        isVisible: false,
        displayAll: false,
        displayCount: 7,
        incrementSteps: [10, 50],
        descriptionMaxLines: 3,
        descriptionMaxChars: 250,
      },
      dataServices: {
        isVisible: true,
        displayAll: false,
        displayCount: 7,
        incrementSteps: [10, 50],
        descriptionMaxLines: 3,
        descriptionMaxChars: 250,
      },
      isUsedBy: {
        isVisible: false,
      },
      relatedResources: {
        isVisible: true,
      },
      bulkDownload: {
        buttonPosition: "top",
        MAX_FILE_TITLE_LENGTH: 80,
        MAX_REQUESTS_COUNT: 5, // TODO: Make use of this property #2764
        INTERVAL_MS: 10, // TODO: Make use of this property #2764
        TIMEOUT_MS: 10000,
      },
      quality: {
        displayAll: false,
        numberOfDisplayedQualityDistributions: 5,
        csvLinter: {
          enable: false,
          displayAll: false,
          numberOfDisplayedValidationResults: 5,
        },
      }
    },
    maps: {
      mapVisible: false,
      useAnimation: true,
      location: [[52.526, 13.314], 10],
      spatialType: 'Point',
      height: '400px',
      width: '100%',
      mapContainerId: 'mapid',
      urlTemplate: '/',
      geoBoundsId: 'ds-search-bounds',
      sender: {
        startBounds: [[34.5970, -9.8437], [71.4691, 41.4843]],
        height: '200px',
        width: '100%',
        mapContainerId: 'modalMap',
      },
      receiver: {
        startBounds: [[34.5970, -9.8437], [71.4691, 41.4843]],
        height: '250px',
        width: '100%',
        mapContainerId: 'mapid',
        attributionPosition: 'topright',
      },
      options: {
        id: '',
        accessToken: '',
        attribution: '',
      },
      mapStyle: {
        color: 'red',
        fillColor: 'red',
        fillOpacity: 0.5,
        weight: 2,
        radius: 1,
      },
    },
    dataProviderInterface: {
      useService: true,
      specification: "dcatapdeODB",
      basePath: '/dpi',
      buttons: {
        Dataset: true,
        Catalogue: false,
      },
      doiRegistrationService: {
        persistentIdentifierType: 'eu-ra-doi',
      },
    },
  },
  languages: {
  useLanguageSelector: false, // TODO: Make use of this property by passing it to the Header-Footer in App.vue #2766
    locale: 'de',
    fallbackLocale: 'en',
  },
  services: {
    datasetService,
    catalogService,
    uploadService,
    gazetteerService,
  },
  themes: {
    header: 'dark',
  },
  tracker: {
    // TODO: Implement disable tracker option based on condition #2767
    isPiwikPro: false, // true: PiwikPro | false: Matomo
    siteId: '1',
    trackerUrl: '//matomo.byte.bayern/'
  },
};

export { glueConfig, i18n };
