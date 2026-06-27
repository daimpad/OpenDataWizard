import { http, HttpResponse } from 'msw'

// Import all vocabulary JSON files
import accessRight from './vocabularies-search/access-right.json'
import adms from './vocabularies-search/adms.json'
import continent from './vocabularies-search/continent.json'
import contributors from './vocabularies-search/contributors.json'
import corporateBody from './vocabularies-search/corporate-body.json'
import country from './vocabularies-search/country.json'
import dataTheme from './vocabularies-search/data-theme.json'
import datasetStatus from './vocabularies-search/dataset-status.json'
import datasetType from './vocabularies-search/dataset-type.json'
import datasetTypes from './vocabularies-search/dataset-types.json'
import distributionType from './vocabularies-search/distribution-type.json'
import fileType from './vocabularies-search/file-type.json'
import frequency from './vocabularies-search/frequency.json'
import hashAlgorithms from './vocabularies-search/hash-algorithms.json'
import hvdCategory from './vocabularies-search/hvd-category.json'
import ianaMediaTypes from './vocabularies-search/iana-media-types.json'
import language from './vocabularies-search/language.json'
import licence from './vocabularies-search/licence.json'
import licenses from './vocabularies-search/licenses.json'
import place from './vocabularies-search/place.json'
import plannedAvailability from './vocabularies-search/planned-availability.json'
import politicalGeocodingDistrictKey from './vocabularies-search/political-geocoding-district-key.json'
import politicalGeocodingGovernmentDistrictKey from './vocabularies-search/political-geocoding-government-district-key.json'
import politicalGeocodingLevel from './vocabularies-search/political-geocoding-level.json'
import politicalGeocodingMunicipalAssociationKey from './vocabularies-search/political-geocoding-municipal-association-key.json'
import politicalGeocodingMunicipalityKey from './vocabularies-search/political-geocoding-municipality-key.json'
import politicalGeocodingRegionalKey from './vocabularies-search/political-geocoding-regional-key.json'
import politicalGeocodingStateKey from './vocabularies-search/political-geocoding-state-key.json'
import spdxChecksumAlgorithm from './vocabularies-search/spdx-checksum-algorithm.json'

// Map dataset names to their JSON data
export const vocabularyMap: Record<string, { result: { count: number; results: { resource: string, pref_label: object }[] } }> = {
  'access-right': accessRight,
  'adms': adms,
  'continent': continent,
  'contributors': contributors,
  'corporate-body': corporateBody,
  'country': country,
  'dataset-status': datasetStatus,
  'dataset-type': datasetType,
  'dataset-types': datasetTypes,
  'data-theme': dataTheme,
  'file-type': fileType,
  'frequency': frequency,
  'hash-algorithms': hashAlgorithms,
  'hvd-category': hvdCategory,
  'language': language,
  'licence': licence,
  'licenses': licenses,
  'place': place,
  'planned-availability': plannedAvailability,
  'political-geocoding-district-key': politicalGeocodingDistrictKey,
  'political-geocoding-government-district-key': politicalGeocodingGovernmentDistrictKey,
  'political-geocoding-level': politicalGeocodingLevel,
  'political-geocoding-municipal-association-key': politicalGeocodingMunicipalAssociationKey,
  'political-geocoding-municipality-key': politicalGeocodingMunicipalityKey,
  'political-geocoding-regional-key': politicalGeocodingRegionalKey,
  'political-geocoding-state-key': politicalGeocodingStateKey,
  'spdx-checksum-algorithm': spdxChecksumAlgorithm,
  'iana-media-types': ianaMediaTypes,
  'distribution-type': distributionType,
}

export const createHandlers = (baseUrl: string) => {
  const normalizedBaseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl

  return [
    http.get(`${normalizedBaseUrl}/vocabularies/:vocabulary`, ({ params, request }) => {
      const { vocabulary } = params
      const data = vocabularyMap[vocabulary as string]
      if (!data) {
        return new HttpResponse(null, {
          status: 404,
          statusText: `Vocabulary '${vocabulary}' not found`,
          headers: { 'X-MSW': 'true' },
        })
      }

      const url = new URL(request.url)
      const q = url.searchParams.get('q')

      if (!q) {
        return HttpResponse.json(data, { headers: { 'X-MSW': 'true' } })
      }

      const searchLower = q.toLowerCase()
      const filteredResults = data.result.results.filter((item) => {
        const labels = Object.values(item.pref_label)
        return labels.some((label) =>
          label.toLowerCase().includes(searchLower)
        )
      })

      return HttpResponse.json({
        result: {
          ...data.result,
          count: filteredResults.length,
          results: filteredResults,
        },
      }, { headers: { 'X-MSW': 'true' } })
    }),

    http.get(`${normalizedBaseUrl}/vocabularies/:vocabulary/vocable`, ({ params, request }) => {
      const { vocabulary } = params
      const url = new URL(request.url)
      const vocab = url.searchParams.get('resource')

      if (!vocab) {
        return new HttpResponse(null, {
          status: 404,
          statusText: `Vocabulary '${vocabulary}' not found`,
          headers: { 'X-MSW': 'true' },
        })
      }

      const data = vocabularyMap[vocabulary as string]?.result?.results?.find(item => item.resource === vocab)

      if (data) {
        return HttpResponse.json({
          result: data,
        }, { headers: { 'X-MSW': 'true' } })
      }

      return new HttpResponse(null, {
        status: 404,
        statusText: `Vocable '${vocab}' not found in vocabulary '${vocabulary}'`,
        headers: { 'X-MSW': 'true' },
      })
    }),
  ]
}

export const handlers = createHandlers('http://msw')
