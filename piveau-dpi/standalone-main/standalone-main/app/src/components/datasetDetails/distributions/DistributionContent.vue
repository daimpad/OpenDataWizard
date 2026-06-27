<template>
  <table class="col-12 table table-borderless table-responsive small pl-0 mt-2 mb-1 distribution-details-table">
    <template v-for="item in contentList">

      <tr class="distribution-license-tr distribution-content-tr" v-if="item === 'license'">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.licence')">
            {{ $t('message.metadata.license') }}
          </tooltip>
        </td>
        <td v-if="showObject(distribution.licence) && showLicence(distribution.licence)">
          <app-link :to="(distribution.licence.homepage ? distribution.licence.homepage : distribution.licence.resource)"
                    target="_blank"
                    data-toggle="tooltip"
                    data-placement="top"
                    :title="distribution.licence.description"
                    @click="$emit('track-link', (distribution.licence.homepage ? distribution.licence.homepage : distribution.licence.resource), 'link')">
            {{ distribution.licence.label ? distribution.licence.label : (distribution.licence.homepage ? distribution.licence.homepage : distribution.licence.resource)}}
            <svg class="ml-1" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g id="ArrowSquareOut">
                <path id="Vector" d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z" fill="currentColor"/>
              </g>
            </svg>
          </app-link>
          <app-link :to="distribution.licence.la_url"
                    target="_blank"
                    @click="$emit('track-link', distribution.licence.la_url, 'link')"
                    v-if="showLicensingAssistant(distribution)">
            {{ $t('message.distributionLicense.licensingAssistant') }}
            <svg class="ml-1" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g id="ArrowSquareOut">
                <path id="Vector" d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z" fill="currentColor"/>
              </g>
            </svg>
          </app-link>
          <app-link :to="distribution.licence.la_url"
                    target="_blank"
                    @click="$emit('track-link', distribution.licence.la_url, 'link')"
                    v-if="showLicensingAssistant(distribution)">
            <svg class="ml-1" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g id="ArrowSquareOut">
                <path id="Vector" d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z" fill="currentColor"/>
              </g>
            </svg>
          </app-link>
        </td>
        <td v-else>
          {{ $t('message.distributionLicense.notProvided') }}
        </td>
      </tr>

      <tr class="distribution-licenseAttributionByText-tr distribution-content-tr" v-if="item === 'licenseAttributionByText'
          && has(distribution , 'licenseAttributionByText')
          && (!isNil(distribution.licenseAttributionByText)
          && !isNil(getTranslationFor(distribution.licenseAttributionByText)))">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.licenseAttributionByText')">
            {{ $t('message.dataupload.distributions.licenseAttributionByText.label') }}
          </tooltip>
        </td>
        <td>{{ getTranslationFor(distribution.licenseAttributionByText) }}</td>
      </tr>

      <tr class="distribution-updateDate-tr distribution-content-tr" v-if="item === 'updateDate'">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.updated')">
            {{ $t('message.metadata.updated') }}
          </tooltip>
        </td>
        <td v-if="has(distribution, 'modificationDate') && !isNil(distribution.modificationDate)">
          {{ filterDateFormatEU(distribution.modificationDate) }}
        </td>
        <td v-else-if="has(distribution, 'releaseDate') && !isNil(distribution.releaseDate)">
          {{ filterDateFormatEU(distribution.releaseDate) }}
        </td>
        <td v-else>
          Unbekannt
        </td>
      </tr>

      <tr class="distribution-modificationDate-tr distribution-content-tr" v-if="item === 'modificationDate'
          && has(distribution, 'modificationDate')
          && !isNil(distribution.modificationDate)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.updated')">
            {{ $t('message.metadata.updated') }}
          </tooltip>
        </td>
        <td>{{ filterDateFormatEU(distribution.modificationDate) }}</td>
      </tr>

      <tr class="distribution-releaseDate-tr distribution-content-tr" v-if="item === 'releaseDate' && has(distribution, 'releaseDate') && !isNil(distribution.releaseDate)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.created')">
            {{ $t('message.metadata.created') }}
          </tooltip>
        </td>
        <td>
          {{ filterDateFormatEU(distribution.releaseDate) }}
        </td>
      </tr>

      <tr class="distribution-language-tr distribution-content-tr" v-if="item === 'language' && has(distribution, 'languages') && showArray(distribution.languages)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.language')">
            {{ $t('message.metadata.languages') }}
          </tooltip>
        </td>
        <td>
          <div v-for="(language, i) of distribution.languages" :key="i">
            <span>{{ localeCodeToGermanMini[language.id] || language.label || '-' }}</span>
          </div>
        </td>
      </tr>

      <tr class="distribution-availability-tr distribution-content-tr" v-if="item === 'availability' && has(distribution, 'availability') && showObject(distribution.availability) && !isNil(distribution.availability.label)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.availability')">
            {{ $t('message.metadata.availability') }}
          </tooltip>
        </td>
        <td>{{ distribution.availability.label }}</td>
      </tr>

      <tr class="distribution-status-tr distribution-content-tr" v-if="item === 'status' && has(distribution, 'status') && showObject(distribution.status)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.status')">
            {{ $t('message.metadata.status') }}
          </tooltip>
        </td>
        <td>
          <div v-if="!isNil(distribution.status.label)">
            {{ distribution.status.label }}
          </div>
        </td>
      </tr>

      <tr class="distribution-rights-tr distribution-content-tr" v-if="item === 'rights' && has(distribution, 'rights') && showObject(distribution.rights)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.rights')">
            {{ $t('message.datasetDetails.quality.rightsAvailability') }}
          </tooltip>
        </td>
        <td>
          <!-- if wen want to show label AND resource!
          <div v-for="(right, i) in distribution.rights" :key="i">
            <span>{{ truncate(right, 75) }}</span>
          </div>
          -->
          <div>{{ distribution.rights.label }}</div>
        </td>
      </tr>

      <tr class="distribution-mediaType-tr distribution-content-tr" v-if="item === 'mediaType' && has(distribution, 'mediaType') && !isNil(distribution.mediaType)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.mediaType')">
            {{ $t('message.metadata.mediaType') }}
          </tooltip>
        </td>
        <td>{{ distribution.mediaType }}</td>
      </tr>

      <tr class="distribution-byteSize-tr distribution-content-tr" v-if="item === 'byteSize' && has(distribution, 'byteSize') && !isNil(distribution.byteSize)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.byteSize')">
            {{ $t('message.metadata.byteSize') }}
          </tooltip>
        </td>
        <td>{{ distribution.byteSize }}</td>
      </tr>

      <tr class="distribution-checksum-tr distribution-content-tr" v-if="item === 'checksum' && has(distribution, 'checksum') && !isNil(distribution.checksum) && has(distribution.checksum, 'algorithm') && !isNil(distribution.checksum.algorithm) && has(distribution.checksum, 'checksum_value') && !isNil(distribution.checksum.checksum_value)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.checksum')">
            {{ $t('message.metadata.checksum') }}
          </tooltip>
        </td>
        <td>
          <div>{{ distribution.checksum.algorithm }}</div>
          <div>{{ distribution.checksum.checksum_value }}</div>
        </td>
      </tr>

      <tr class="distribution-pages-tr distribution-content-tr" v-if="item === 'pages' && has(distribution, 'pages') && showObjectArray(distribution.pages)">
        <td class="w-25 font-weight-bold d-flex align-items-end">
          {{ $t('message.metadata.pages') }}
          <svg class="ml-1" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g id="ArrowSquareOut">
              <path id="Vector" d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z" fill="currentColor"/>
            </g>
          </svg>
        </td>
        <td>
          <div v-for="(page, i) of distribution.pages" :key="i">
            <app-link :to="page.resource">{{ truncate(page.resource, 75) }}</app-link>
          </div>
        </td>
      </tr>

      <tr class="distribution-type-tr distribution-content-tr" v-if="item === 'type' && has(distribution, 'type') && showObject(distribution.type)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.type')">
            {{ $t('message.metadata.type') }}
          </tooltip>
        </td>
        <td>
          <div v-if="!isNil(distribution.type.label)">
            {{ distribution.type.label }}
          </div>
        </td>
      </tr>

      <tr class="distribution-compressFormat-tr distribution-content-tr" v-if="item === 'compressFormat' && has(distribution, 'compressFormat') && showObject(distribution.compressFormat)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.compressFormat')">
            {{ $t('message.metadata.compressFormat') }}
          </tooltip>
        </td>
        <td>
          <div v-if="!isNil(distribution.compressFormat.label)">
            {{ distribution.compressFormat.label }}
          </div>
          <div v-if="!isNil(distribution.compressFormat.resource)">
            {{ distribution.compressFormat.resource }}
          </div>
        </td>
      </tr>

      <tr class="distribution-packageFormat-tr distribution-content-tr" v-if="item === 'packageFormat' && has(distribution, 'packageFormat') && showObject(distribution.packageFormat)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.packageFormat')">
            {{ $t('message.metadata.packageFormat') }}
          </tooltip>
        </td>
        <td>
          <div v-if="!isNil(distribution.packageFormat.label)">
            {{ distribution.packageFormat.label }}
          </div>
          <div v-if="!isNil(distribution.packageFormat.resource)">
            {{ distribution.packageFormat.resource }}
          </div>
        </td>
      </tr>

      <tr class="distribution-hasPolicy-tr distribution-content-tr" v-if="item === 'hasPolicy' && has(distribution, 'hasPolicy') && !isNil(distribution.hasPolicy)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.distributions.hasPolicy')">
            {{ $t('message.metadata.hasPolicy') }}
          </tooltip>
        </td>
        <td>
          <div>
            <a :href="appendCurrentLocaleToURL(distribution.hasPolicy)">{{ truncate(distribution.hasPolicy, 75) }}</a>
          </div>
        </td>
      </tr>

      <tr class="distribution-conformsTo-tr distribution-content-tr" v-if="item === 'conformsTo' && has(distribution, 'conformsTo') && showObjectArray(distribution.conformsTo)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.conformsTo')">
            {{ $t('message.metadata.conformsTo') }}
          </tooltip>
        </td>
        <td>
          <div v-for="(conformTo, i) in distribution.conformsTo" :key="i">
            <div v-if="has(conformTo, 'label') && !isNil(conformTo.label)">
              {{ $t('message.metadata.label') }}:
              {{ conformTo.label }}
            </div>
            <div v-if="has(conformTo, 'resource') && !isNil(conformTo.resource)">
              {{ $t('message.metadata.resource') }}:
              <app-link :to="conformTo.resource"
                        target="_blank"
                        @click="$emit('track-link', conformTo.resource, 'link')">
                {{ truncate(conformTo.resource, 75) }}
              </app-link>
            </div>
          </div>
        </td>
      </tr>

      <tr class="distribution-spatialResolutionInMeters-tr distribution-content-tr" v-if="item === 'spatialResolutionInMeters' && has(distribution, 'spatialResolutionInMeters') && showArray(distribution.spatialResolutionInMeters)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.spatialResolutionInMeters')">
            {{ $t('message.metadata.spatialResolutionInMeters.label') }}
          </tooltip>
        </td>
        <td>
          <div v-if="showNumber(distribution.spatialResolutionInMeters[0])">
            {{ $t('message.metadata.spatialResolutionInMeters.value', {number: distribution.spatialResolutionInMeters[0]}) }}
          </div>
        </td>
      </tr>

      <tr class="distribution-temporalResolution-tr distribution-content-tr" v-if="item === 'temporalResolution' && has(distribution, 'temporalResolution') && showArray(distribution.temporalResolution)">
        <td class="w-25 font-weight-bold">
          <tooltip :title="$t('message.tooltip.datasetDetails.temporalResolution')">
            {{ $t('message.metadata.temporalResolution') }}
          </tooltip>
        </td>
        <td>
          <div v-for="(temporalResolution, i) of distribution.temporalResolution" :key="i">
            {{ formatDatetime(temporalResolution) }}
          </div>
        </td>
      </tr>

      <tr class="distribution-validate-tr distribution-content-tr" v-if="item === 'validate'">
          <td v-if="this.$env.content.datasetDetails.distributions.showValidationButton">
            <app-link
              class="ecl-button--secondary row pt-0 pb-0 pl-4 pr-4 mt-2 text-decoration-none"
              :to="{ path: `${getID}/quality/`, query: Object.assign({}, { locale: $route.query.locale }) }"
            >
              {{ $t('message.metadata.validate') }}
            </app-link>
          </td>
      </tr>
    </template>
  </table>
</template>

<script>
import {
  has,
  isNil
} from 'lodash';
import { mapGetters } from "vuex";
import {
  AppLink,
  Tooltip,
  helpers
} from '@piveau/piveau-hub-ui-modules';
import { localeCodeToGermanMini } from '../../../utils/utils'
const { formatDatetime, getTranslationFor, truncate } = helpers;

export default {
  name: "DistributionContent",
  components: {
    AppLink,
    Tooltip
  },
  data: () => {
    return {
      localeCodeToGermanMini,
    }
  },
  props: [
    'contentList',
    'distribution',
    'distributionIsExpanded',
    'showLicensingAssistant',
    'showLicence',
    'filterDateFormatEU',
    'showArray',
    'showNumber',
    'showObject',
    'showObjectArray',
    'appendCurrentLocaleToURL',
  ],
  computed: {
    ...mapGetters('datasetDetails', [
      'getID'
    ]),
  },
  methods: {
    has,
    isNil,
    truncate,
    formatDatetime,
    getTranslationFor
  }
}
</script>

<style scoped>

</style>
