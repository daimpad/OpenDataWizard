import * as z from 'zod'

const facetOrAndOperatorLabelSchema = z.object({
  or: z.string().default('OR'),
  and: z.string().default('AND'),
}).default({})

export const facetSchema = z.object({
  useDatasetFacets: z.boolean().default(true),
  useDatasetFacetsMap: z.boolean().default(true),
  showClearButton: z.boolean().default(false),
  showFacetsTitle: z.boolean().default(false),
  cutoff: z.coerce.number().default(5),
  MIN_FACET_LIMIT: z.coerce.number().default(10),
  MAX_FACET_LIMIT: z.coerce.number().default(50),
  FACET_OPERATORS: facetOrAndOperatorLabelSchema,
  FACET_GROUP_OPERATORS: facetOrAndOperatorLabelSchema,
  defaultFacetOrder: z.array(z.string()).default([
    'publisher',
    'format',
    'catalog',
    'categories',
    'keywords',
    'dataScope',
    'country',
    'dataServices',
    'scoring',
    'license',
  ]),
  scoringFacets: z.object({
    useScoringFacets: z.boolean().default(true),
    defaultScoringFacets: z.record(
      z.object({
        id: z.string(),
        title: z.string(),
        count: z.coerce.number(),
        minScoring: z.coerce.number(),
        maxScoring: z.coerce.number(),
      })).default({
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
    }),
  }).default({}),
}).default({})

const datasetsSchema = z.object({
  useSort: z.boolean().default(true),
  useFeed: z.boolean().default(true),
  useCatalogs: z.boolean().default(true),
  followKeywordLinks: z.string().default('nofollow'),
  maxKeywordLength: z.coerce.number().default(15),
  facets: facetSchema,
}).default({})

const catalogsSchema = z.object({
  useSort: z.boolean().default(true),
  useCatalogCountries: z.boolean().default(true),
  defaultCatalogImagePath: z.string().default('/flags'),
  defaultCatalogCountryID: z.string().default('eu'),
  defaultCatalogID: z.string().default('european-union-open-data-portal'),
  facets: z.object({
    useCatalogFacets: z.boolean().default(true),
    showClearButton: z.boolean().default(false),
    showFacetsTitle: z.boolean().default(false),
    cutoff: z.coerce.number().default(5),
    MIN_FACET_LIMIT: z.coerce.number().default(10),
    MAX_FACET_LIMIT: z.coerce.number().default(50),
    FACET_OPERATORS: facetOrAndOperatorLabelSchema,
    FACET_GROUP_OPERATORS: facetOrAndOperatorLabelSchema,
    defaultFacetOrder: z.array(z.string()).default(['country']),
    useDatasetFacetsMap: z.boolean().default(false)
  }).default({}),
}).default({})

const datasetDetailsHeaderSchema = z.object({
  navigation: z.string().default('top'),
  hidePublisher: z.boolean().default(false),
  hideDate: z.boolean().default(false),
}).default({})

const datasetDetailsKeywordsSchema = z.object({
  showTitle: z.boolean().default(false),
  isVisible: z.boolean().default(true),
  collapsed: z.boolean().default(false)
}).default({})

const datasetDetailsDescriptionSchema = z.object({
  enableMarkdownInterpretation: z.boolean().default(true),
}).default({})

const datasetDetailsDistributionsSchema = z.object({
  displayAll: z.boolean().default(false),
  displayCount: z.coerce.number().default(7),
  incrementSteps: z.array(z.coerce.number()).default([10, 50]),
  descriptionMaxLines: z.coerce.number().default(3),
  descriptionMaxChars: z.coerce.number().default(250),
  showValidationButton: z.boolean().default(false),
}).default({})

const datasetDetailsDownloadAsSchema = z.object({
  enable: z.boolean().default(true),
  proxyUrl: z.string().default('https://piveau-corsproxy-piveau.apps.osc.fokus.fraunhofer.de'),
  url: z.string().default('https://piveau-fifoc-piveau.apps.osc.fokus.fraunhofer.de/v1/convert'),
  conversionFormats: z.array(
    z.object({
      sourceFileFormat: z.string(),
      targetFileFormat: z.array(z.string()),
    }),
  ).default([
    { sourceFileFormat: 'HTML', targetFileFormat: ['html', 'pdf', 'docx', 'json', 'odt', 'rtf'] },
    { sourceFileFormat: 'CSV', targetFileFormat: ['csv', 'docx', 'html', 'json', 'odt', 'rtf', 'xls', 'xlsx', 'xml'] },
    { sourceFileFormat: 'JSON', targetFileFormat: ['json', 'xml'] },
    { sourceFileFormat: 'ODT', targetFileFormat: ['odt', 'docx', 'html', 'json', 'rtf'] },
    { sourceFileFormat: 'DOCX', targetFileFormat: ['docx', 'pptx', 'odt', 'pdf', 'txt', 'html', 'json', 'odt', 'rtf'] },
    { sourceFileFormat: 'XLSX', targetFileFormat: ['xlsx', 'csv'] },
    { sourceFileFormat: 'XLS', targetFileFormat: ['xls', 'csv'] },
    { sourceFileFormat: 'PDF', targetFileFormat: ['pdf', 'txt'] },
  ]),
}).default({})

const startEndSchema = z.object({
  start: z.coerce.number().gte(0),
  end: z.coerce.number().gte(0),
}).refine(obj => obj.start <= obj.end, {
  message: 'start must be less than or equal to end',
})

const datasetDetailsSimilarDatasetsSchema = z.object({
  useSimilarDatasets: z.boolean().default(true),
  breakpoints: z.object({
    verySimilar: startEndSchema.default({ start: 0, end: 20 }),
    similar: startEndSchema.default({ start: 20, end: 25 }),
    lessSimilar: startEndSchema.default({ start: 25, end: 35 }),
  }).default({}),
}).default({})

const datasetDetailsPagesSchema = z.object({
  isVisible: z.boolean().default(false),
  displayAll: z.boolean().default(false),
  displayCount: z.coerce.number().default(7),
  incrementSteps: z.array(z.coerce.number()).default([10, 50]),
  descriptionMaxLines: z.coerce.number().default(3),
  descriptionMaxChars: z.coerce.number().default(250),
}).default({})

const datasetDetailsVisualisationsSchema = datasetDetailsPagesSchema
const datasetDetailsDataServicesSchema = datasetDetailsPagesSchema

const datasetDetailsIsUsedBySchema = z.object({
  isVisible: z.boolean().default(false),
}).default({})

const datasetDetailsRelatedResourcesSchema = z.object({
  isVisible: z.boolean().default(false),
}).default({})

const datasetDetailsBulkDownloadSchema = z.object({
  buttonPosition: z.string().default('top'),
  MAX_FILE_TITLE_LENGTH: z.coerce.number().default(80),
  MAX_REQUESTS_COUNT: z.coerce.number().default(5),
  INTERVAL_MS: z.coerce.number().default(10),
  TIMEOUT_MS: z.coerce.number().default(10000),
}).default({})

const datasetDetailsQualitySchema = z.object({
  displayAll: z.boolean().default(false),
  numberOfDisplayedQualityDistributions: z.coerce.number().default(5),
  csvLinter: z.object({
    enable: z.boolean().default(true),
    displayAll: z.boolean().default(false),
    numberOfDisplayedValidationResults: z.coerce.number().default(5),
  }).default({}),
}).default({})

const embedSchema = z.object({
  enable: z.boolean().default(true),
  defaultWidth: z.number().default(900),
  defaultHeight: z.number().default(600),
  minRange: z.number().default(0),
  maxRange: z.number().default(9999),
}).default({});

const datasetDetailsSchema = z.object({
  properties: z.string().default(""),
  embed: embedSchema,
  header: datasetDetailsHeaderSchema,
  keywords: datasetDetailsKeywordsSchema,
  description: datasetDetailsDescriptionSchema,
  distributions: datasetDetailsDistributionsSchema,
  downloadAs: datasetDetailsDownloadAsSchema,
  similarDatasets: datasetDetailsSimilarDatasetsSchema,
  pages: datasetDetailsPagesSchema,
  visualisations: datasetDetailsVisualisationsSchema,
  dataServices: datasetDetailsDataServicesSchema,
  isUsedBy: datasetDetailsIsUsedBySchema,
  relatedResources: datasetDetailsRelatedResourcesSchema,
  bulkDownload: datasetDetailsBulkDownloadSchema,
  quality: datasetDetailsQualitySchema,
}).default({})

const locationSchema = z.tuple([z.array(z.coerce.number()), z.coerce.number()])
const locationBoundsSchema = z.tuple([z.tuple([z.coerce.number(), z.coerce.number()]), z.tuple([z.coerce.number(), z.coerce.number()])])
const controlPositions = z.union([
  z.literal('topleft'),
  z.literal('topright'),
  z.literal('bottomleft'),
  z.literal('bottomright'),
])

const mapsSchema = z.object({
  mapVisible: z.boolean().default(true),
  useAnimation: z.boolean().default(true),
  location: locationSchema.default([[52.526, 13.314], 10]),
  spatialType: z.union([z.literal('Point'), z.literal('Polygon')]).default('Point'),
  height: z.string().default('400px'),
  width: z.string().default('100%'),
  mapContainerId: z.string().default('mapid'),
  urlTemplate: z.string().default('https://gisco-services.ec.europa.eu/maps/wmts/1.0.0/WMTSCapabilities.xml/wmts/OSMCartoComposite/EPSG3857/{z}/{x}/{y}.png'),
  geoBoundsId: z.string().default('ds-search-bounds'),
  sender: z.object({
    startBounds: locationBoundsSchema.default([[34.5970, -9.8437], [71.4691, 41.4843]]),
    height: z.string().default('200px'),
    width: z.string().default('100%'),
    mapContainerId: z.string().default('modalMap'),
  }).default({}),
  receiver: z.object({
    startBounds: locationBoundsSchema.default([[34.5970, -9.8437], [71.4691, 41.4843]]),
    height: z.string().default('250px'),
    width: z.string().default('100%'),
    mapContainerId: z.string().default('mapid'),
    attributionPosition: controlPositions.default('topright'),
  }).default({}),
  options: z.object({
    id: z.string().default('mapbox/streets-v11'),
    // todo: potentially dumb
    accessToken: z.string().default('pk.eyJ1IjoiZmFiaWFwZmVsa2VybiIsImEiOiJja2x3MzlvZ3UwNG85MnBseXJ6aGI2MHdkIn0.bFs2g4bPMYULlvDSVsetJg'),
    attribution: z.string().default('&copy; <a href="https://ec.europa.eu/eurostat/web/gisco/">Eurostat - GISCO</a>'),
  }).default({}),
  mapStyle: z.object({
    color: z.string().default('red'),
    fillColor: z.string().default('red'),
    fillOpacity: z.coerce.number().default(0.5),
    weight: z.coerce.number().default(2),
    radius: z.coerce.number().default(1),
  }).default({}),
}).default({})

const dataProviderInterfaceSchema = z.object({
  uploadFileTypes: z.array(z.string()).default([]),
  useService: z.boolean().default(true),
  enableFileUploadReplace: z.boolean().default(false),
  basePath: z.string().default('/dpi'),
  specification: z.union([
    z.literal('dcatap'),
    z.literal('dcatapde'),
  ], {
    errorMap: (issue, ctx) => {
      if (issue.code === 'invalid_union')
        return { message: `Specification ${ctx.data} is not supported in Data Provider Interface.` }

      return { message: ctx.defaultError }
    },
  }).default('dcatap'),
  annifIntegration: z.boolean().default(false),
  annifLinkTheme: z.string().default(""),
  annifLinkSubject: z.string().default(""),
  buttons: z.object({
    Dataset: z.boolean().default(true),
    Catalogue: z.boolean().default(false),
  }).default({}),
  doiRegistrationService: z.object({
    persistentIdentifierType: z.string().default('eu-ra-doi'),
  }).default({}),
}).default({})

export const contentSchema = z.object({
  datasets: datasetsSchema,
  catalogs: catalogsSchema,
  datasetDetails: datasetDetailsSchema,
  maps: mapsSchema,
  dataProviderInterface: dataProviderInterfaceSchema,
}).default({})
