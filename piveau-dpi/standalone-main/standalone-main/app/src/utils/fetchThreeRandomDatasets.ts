/* eslint-disable @typescript-eslint/no-explicit-any */
import { from, of } from 'rxjs'
import { catchError, filter, map, mergeMap, switchMap, take, tap, toArray } from 'rxjs/operators'
import axios, { type AxiosInstance } from 'axios'

const allLicenses = [
  "http://dcat-ap.de/def/licenses/cc-by/4.0",
  "http://dcat-ap.de/def/licenses/cc-zero",
  "http://dcat-ap.de/def/licenses/dl-by-de/2.0",
  "http://dcat-ap.de/def/licenses/cc-by",
  "http://dcat-ap.de/def/licenses/cc-by-sa/4.0",
  "http://dcat-ap.de/def/licenses/cc-by-sa",
  "http://dcat-ap.de/def/licenses/odcpddl",
  "http://dcat-ap.de/def/licenses/cc-by-de/3.0",
  "http://dcat-ap.de/def/licenses/odbl",
  "http://dcat-ap.de/def/licenses/other-open",
  "https://creativecommons.org/licenses/by/4.0/",
  "http://dcat-ap.de/def/licenses/odby",
  "http://opendatacommons.org/licenses/odbl/",
  "http://dcat-ap.de/def/licenses/dl-by-de/1.0",
  "https://creativecommons.org/licenses/by-nd/4.0",
  "https://w3id.org/mdp/schema/license%23NO_LICENSE"
] as const;

const allFormats = [
  "CSV",
  "XML",
  "XLS",
  "XLSX",
  "WMS_SRVC",
  "ZIP",
  "GEOJSON",
  "Web Page",
  "ArcGIS GeoServices REST API",
  "KML",
  "OGC WFS",
  "JSON",
  "Download",
  "N3",
  "SHP",
  "WFS_SRVC",
  "ATOM",
  "HTML",
  "Information",
  "PDF",
  "TXT",
  "ODS",
  "shape",
  "GPKG",
  "JSON_LD",
  "OCTET",
  "GZIP",
  "Kontakt",
  "ODP",
  "GPX",
  "RSS",
  "7Z",
  "SVG",
  "GEOTIFF",
  "HDF",
  "JPEG",
  "MPEG4",
  "NAS",
  "PNG",
  "REST",
  "TAR",
  "Testdaten"
] as const

export interface InterestingDataset {
  id: string
  title: string
  description: string
  catalog: string
  formats: string[]
}

export function formatDataset(ds: any): InterestingDataset {
  return {
    id: ds.id || '',
    title: ds.title?.de || '',
    description: ds.description?.de || '',
    catalog: typeof ds.publisher === 'string'
      ? ds.publisher
      : ds.publisher?.name?.de || ds.publisher?.name || '',
    formats: [
      ...new Set(
        ds.distributions?.map((dist: any) => dist?.format?.label || null).filter(Boolean),
      ),
    ] as string[],
  }
}

// Step 1: Fetch all catalog IDs
function fetchCatalogs(api: AxiosInstance) {
  return from(api.get('/catalogues')).pipe(
    map(response => response.data),
    catchError((error) => {
      console.error('Error fetching catalogs', error)
      return of([]) // Gracefully handle the error
    }),
  )
}

// Step 2: Fetch datasets from a given catalog with pagination
function fetchRandomDatasetFromCatalog(catalogId: string, api: AxiosInstance) {
  const limit = 100
  // First, fetch the total count of datasets (from page 0)
  return from(
    api.get(`/search?filter=dataset&facets={"catalog": ["${catalogId}"],"license":${JSON.stringify(allLicenses)},"format":${JSON.stringify(allFormats)}}&page=0&limit=${limit}&includes=id,title.de,description.de,publisher,distributions.format.label`),
  ).pipe(
    // tap(() => console.log(`Fetched datasets count from catalog ${catalogId}`)),
    filter(response => response.data.result.count > 0), // Filter catalogs with datasets
    mergeMap((response) => {
      const totalDatasets = response.data.result.count
      // Select a random dataset index
      const randomDatasetIndex = Math.floor(Math.random() * totalDatasets)

      // Calculate the page and the index on that page for the random dataset
      const randomPage = Math.floor(randomDatasetIndex / limit)
      const datasetIndexOnPage = randomDatasetIndex % limit

      const pageZeroResults = response

      // Fetch the page that contains the random dataset
      return from(
        randomPage === 0 ? [pageZeroResults] : api.get(`/search?filter=dataset&facets={"catalog": ["${catalogId}"],"license":${JSON.stringify(allLicenses)},"format":${JSON.stringify(allFormats)}}&page=${randomPage}&limit=${limit}&includes=id,title.de,description.de,publisher,distributions.format.label`),
      ).pipe(
        tap(() => console.log(`Fetched datasets from catalog ${catalogId} at random page ${randomPage}`)),
        map(response => response.data.result.results),
        filter(datasets => datasets.length > datasetIndexOnPage), // Ensure the dataset exists on this page
        map(datasets => datasets[datasetIndexOnPage]), // Pick the random dataset from the page
        catchError((error) => {
          console.error(`Error fetching datasets from catalog ${catalogId} on page ${randomPage}`, error)
          return of(null) // Gracefully handle errors
        }),
      )
    }),
    catchError((error) => {
      console.error(`Error fetching datasets count from catalog ${catalogId}`, error)
      return of(null) // Gracefully handle errors
    }),
  )
}

// Step 4: Select three random valid catalogs and one random dataset from each
function selectRandomDatasets(baseUrl: string) {

  const api = axios.create({
    baseURL: baseUrl,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  })

  return fetchCatalogs(api).pipe(
    mergeMap((catalogIds: string[]) => {
      const blacklist = ['wendelstein', 'wendelstein-subcatalog']

      // Use filter to exclude catalogId 'wendelstein'
      const resolvedCatalogIds = catalogIds.filter(catalogId => !blacklist.includes(catalogId.trim().toLowerCase()))
      // Shuffle catalog IDs for randomness
      const shuffledCatalogIds = resolvedCatalogIds.sort(() => 0.5 - Math.random())

      return from(shuffledCatalogIds).pipe(
        mergeMap(catalogId =>
          fetchRandomDatasetFromCatalog(catalogId, api).pipe(
            map(formatDataset),
          ), 3), // Ensure up to 3 requests are happening in parallel
        take(3), // We only need 3 valid catalogs with datasets
        toArray(), // Collect the results into an array
      )
    }),
  )
}

// Step 5: Execute the selection process and log results
export function fetchThreeRandomDatsets(baseUrl: string) {
  // return observable as promise
  return new Promise<InterestingDataset[]>((resolve, reject) => {
    selectRandomDatasets(baseUrl).subscribe({
      next: results => resolve(results),
      error: err => reject(err),
    })
  })
}
