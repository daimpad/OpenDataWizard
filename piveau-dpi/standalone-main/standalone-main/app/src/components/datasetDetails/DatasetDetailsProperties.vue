<template>
  <div class="dsd-properties">
    <div class="mb-2 dsd-properties-list">
      <dataset-details-feature-header class="mb-3" :title="$t('message.datasetDetails.additionalInfo')"
        :arrowDown="!infoVisible" tag="additional-information-toggle" :onClick="toggleInfo" />
      <div class="position-relative additional-information by-copy-small-regular" data-cy="additional-information"
        v-show="infoVisible">
        <table class="table table-borderless table-responsive" ref="dsdProperties" id="myTab" role="tablist">
          <tr v-if="showArray(getLandingPagesResource)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.landingPage')">
                {{ $t('message.metadata.landingPage') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(landingPage, i) of getLandingPagesResource" :key="i">
                <app-link class="text-by-blue-80" v-if="!isNil(landingPage)" :to="landingPage">
                  {{ truncate(landingPage, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getSources)">
            <td class=" by-caption">{{ $t('message.metadata.sources') }}</td>
            <td>
              <div v-for="(source, i) of getSources" :key="i">
                <app-link class="text-by-blue-80" v-if="!isNil(source) && isString(source)" :to="source">
                  {{ source }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getLanguages)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.language')">
                {{ $t('message.metadata.languages') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(language, i) of getLanguages" :key="i">
                <app-link class="text-by-blue-80"
                  v-if="!isNil(language) && isString(language.label) && isString(language.resource)"
                  :to="language.resource">{{ (language.id && localeCodeToGermanMini[language.id]) || language.label ||
                    '-' }}</app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showObject(getPublisher) &&
            ((has(getPublisher, 'name') && !isNil(getPublisher.name))
              || has(getPublisher, 'email') && !isNil(getPublisher.email)
              || has(getPublisher, 'homepage') && !isNil(getPublisher.homepage)
            )">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.publisher')">
                {{ $t('message.metadata.publisher') }}
              </tooltip>
            </td>
            <td>
              <div v-if="has(getPublisher, 'name') && !isNil(getPublisher.name)">
                {{ getPublisher.name }}
              </div>
              <div v-if="has(getPublisher, 'email') && !isNil(getPublisher.email)">
                <app-link class="text-by-blue-80" :to="`mailto:${removeMailtoOrTel(getPublisher.email)}`">{{
                  removeMailtoOrTel(getPublisher.email) }}</app-link>
              </div>
              <div v-if="has(getPublisher, 'homepage') && !isNil(getPublisher.homepage)">
                <app-link class="text-by-blue-80" :to="getPublisher.homepage">
                  {{ truncate(getPublisher.homepage, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getContactPoints) && showContactPoint(getContactPoints)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.contactPoints')">
                {{ $t('message.metadata.contactPoints') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(contactPoint, i) in getContactPoints" :key="i">
                <div v-if="has(contactPoint, 'name') && !isNil(contactPoint.name)">
                  {{ contactPoint.name }}
                </div>
                <div v-if="has(contactPoint, 'email') && !isNil(contactPoint.email)">
                  <app-link class="text-by-blue-80" :to="`mailto:${removeMailtoOrTel(contactPoint.email)}`">{{
                    removeMailtoOrTel(contactPoint.email) }}</app-link>
                </div>
                <div v-if="has(contactPoint, 'telephone') && !isNil(contactPoint.telephone)">
                  <app-link class="text-by-blue-80" :to="`tel:${removeMailtoOrTel(contactPoint.telephone)}`">{{
                    removeMailtoOrTel(contactPoint.telephone) }}</app-link>
                </div>
                <div v-if="has(contactPoint, 'address') && !isNil(contactPoint.address)">
                  {{ contactPoint.address }}
                </div>
                <div v-if="has(contactPoint, 'url') && showArray(contactPoint.url)">
                  <div>
                    <div v-for="(url, i) of contactPoint.url" :key="i">
                      <div v-if="showString(url)">
                        <app-link class="text-by-blue-80" :to="url">
                          {{ truncate(url, 75) }}
                          <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g id="ArrowSquareOut">
                              <path id="Vector"
                                d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                                fill="currentColor" />
                            </g>
                          </svg>
                        </app-link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <!-- Add new fields for DCAT-AP.de -->
          <tr v-if="showObjectArray(getPoliticalGeocodingLevelURI)">
            <td class="by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.politicalGeocodingLevelURI')">
                {{ $t('message.metadata.politicalGeocodingLevelURI') }}
              </tooltip>
            </td>
            <td v-for="(element, i) in getPoliticalGeocodingLevelURI" :key="`PoliticalGeocodingLevelURI-` + i">
              <div v-if="has(element, 'label') && !isNil(element.label)">{{ element.label }}</div>
              <div v-if="has(element, 'resource') && !isNil(element.resource)">
                <a class="text-by-blue-80" :href="element.resource">
                  {{ element.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getPoliticalGeocodingURI)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.politicalGeocodingURI")'>
                {{ $t('message.metadata.politicalGeocodingURI') }}
              </tooltip>
            </td>
            <td v-for="(element, i) in getPoliticalGeocodingURI" :key="`PoliticalGeocodingURI-` + i">
              <div v-if="has(element, 'label') && !isNil(element.label)">{{ element.label }}</div>
              <div v-if="has(element, 'resource') && !isNil(element.resource)">
                <a class="text-by-blue-80" :href="element.resource">
                  {{ element.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="showObject(getAvailability)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.availabilityDE")'>
                {{ $t('message.metadata.availability') }}
              </tooltip>
            </td>
            <td>
              <div v-if="has(getAvailability, 'label') && !isNil(getAvailability.label)">{{ getAvailability.label }}
              </div>
              <div v-if="has(getAvailability, 'resource') && !isNil(getAvailability.resource)">
                <a class="text-by-blue-80" :href="getAvailability.resource">
                  {{ getAvailability.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getContributorID)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.contributorID")'>
                {{ $t('message.metadata.contributorID') }}
              </tooltip>
            </td>
            <td v-for="(element, i) in getContributorID" :key="`ContributorID-` + i">
              <div v-if="has(element, 'label') && !isNil(element.label)">{{ element.label }}</div>
              <div v-if="has(element, 'resource') && !isNil(element.resource)">
                <a class="text-by-blue-80" :href="element.resource">
                  {{ element.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="showObject(getGeocodingDescriptionDe)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.geocodingDescription")'>
                {{ $t('message.metadata.geocodingDescription') }}
              </tooltip>
            </td>
            <td>
              <div> {{ getTranslationFor(getGeocodingDescriptionDe, $route.query.locale || 'de') }}</div>
            </td>
          </tr>
          <tr v-if="showObject(getLegalBasis)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.legalBasis")'>
                {{ $t('message.metadata.legalBasis') }}
              </tooltip>
            </td>
            <td>
              <div> {{ getTranslationFor(getLegalBasis, $route.query.locale || 'de') }}</div>
            </td>
          </tr>
          <tr v-if="showString(getQualityProcessURI)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.qualityProcessURI")'>
                {{ $t('message.metadata.qualityProcessURI') }}
              </tooltip>
            </td>
            <td>
              <a class="text-by-blue-80" :href="getQualityProcessURI">
                {{ getQualityProcessURI }}
                <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <g id="ArrowSquareOut">
                    <path id="Vector"
                      d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                      fill="currentColor" />
                  </g>
                </svg>
              </a>
            </td>
          </tr>
          <tr v-if="showString(getTypeDe)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.type")'>
                {{ $t('message.metadata.type') }}
              </tooltip>
            </td>
            <td>
              {{ getTypeDe }}
            </td>
          </tr>
          <tr v-if="showString(getReferences)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.references")'>
                {{ $t('message.metadata.references') }}
              </tooltip>
            </td>
            <td>
              <a class="text-by-blue-80" :href="getReferences">
                {{ getReferences }}
                <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <g id="ArrowSquareOut">
                    <path id="Vector"
                      d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                      fill="currentColor" />
                  </g>
                </svg>
              </a>
            </td>
          </tr>
          <tr v-if="showObjectArray(getContributor)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.contributor")'>
                {{ $t('message.metadata.contributor') }}
              </tooltip>
            </td>
            <td v-for="(element, i) in getContributor" :key="`Contributor-` + i">
              <div v-if="has(element, 'name') && !isNil(element.name)">{{ element.name }}</div>
              <div v-if="has(element, 'type') && !isNil(element.type)">{{ element.type }}</div>
              <div v-if="has(element, 'homepage') && !isNil(element.homepage)">
                <a class="text-by-blue-80" :href="element.homepage">
                  {{ element.homepage }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
              <div v-if="has(element, 'email') && !isNil(element.email)"><a class="text-by-blue-80"
                  :href="'mailto:' + element.email">{{ element.email }}</a></div>
              <div v-if="has(element, 'resource') && !isNil(element.resource)">
                <a class="text-by-blue-80" :href="element.resource">
                  {{ element.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getOriginator)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.originator")'>
                {{ $t('message.metadata.originator') }}
              </tooltip>
            </td>
            <td v-for="(element, i) in getOriginator" :key="`Originator-` + i">
              <div v-if="has(element, 'name') && !isNil(element.name)">{{ element.name }}</div>
              <div v-if="has(element, 'type') && !isNil(element.type)">{{ element.type }}</div>
              <div v-if="has(element, 'homepage') && !isNil(element.homepage)">
                <a class="text-by-blue-80" :href="element.homepage">
                  {{ element.homepage }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
              <div v-if="has(element, 'email') && !isNil(element.email)"><a class="text-by-blue-80"
                  :href="'mailto:' + element.email">{{ element.email }}</a></div>
              <div v-if="has(element, 'resource') && !isNil(element.resource)">
                <a class="text-by-blue-80" :href="element.resource">
                  {{ element.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getMaintainer)">
            <td class=" by-caption">
              <tooltip :title='$t("message.tooltip.datasetDetails.maintainer")'>
                {{ $t('message.metadata.maintainer') }}
              </tooltip>
            </td>
            <td v-for="(element, i) in getMaintainer" :key="`Maintainer-` + i">
              <div v-if="has(element, 'name') && !isNil(element.name)">{{ element.name }}</div>
              <div v-if="has(element, 'type') && !isNil(element.type)">{{ element.type }}</div>
              <div v-if="has(element, 'homepage') && !isNil(element.homepage)">
                <a class="text-by-blue-80" :href="element.homepage">
                  {{ element.homepage }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
              <div v-if="has(element, 'email') && !isNil(element.email)"><a class="text-by-blue-80"
                  :href="'mailto:' + element.email">{{ element.email }}</a></div>
              <div v-if="has(element, 'resource') && !isNil(element.resource)">
                <a class="text-by-blue-80" :href="element.resource">
                  {{ element.resource }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
          <!-- ### END DCAT-AP.de fields ### -->
          <tr v-if="showString(getModificationDate)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.updated')">
                {{ $t('message.metadata.updated') }}
              </tooltip>
            </td>
            <td>{{ formatDatetime(getModificationDate) || 'Unbekannt' }}</td>
          </tr>
          <tr v-if="showString(getReleaseDate)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.created')">
                {{ $t('message.metadata.created') }}
              </tooltip>
            </td>
            <td>{{ formatDatetime(getReleaseDate) || 'Unbekannt' }}</td>
          </tr>
          <!-- <tr v-if="showObject(getCatalogRecord)">
                              <td class=" by-caption">
                                <tooltip :title="$t('message.tooltip.catalogRecord')" >
                                  {{ $t('message.metadata.catalogRecord') }}
                                </tooltip>
                              </td>
                              <td>
                                <div v-if="getCatalogRecord.issued" class="catalogue-label">{{ `${$t('message.metadata.addedToDataEuropaEU')}:\n${filterDateFormatEU(getCatalogRecord.issued)}` }}</div>
                                <div v-if="getCatalogRecord.modified" class="catalogue-label" :class="{'mt-1': getCatalogRecord.issued}">{{ `${$t('message.metadata.updatedOnDataEuropaEU')}:\n${filterDateFormatEU(getCatalogRecord.modified)}` }}</div>
                              </td>
                            </tr> -->
          <tr v-if="showObjectArray(getSpatial)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.spatial')">
                {{ $t('message.metadata.spatial') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(spatial, i) of getSpatial" :key="i">
                <div v-if="has(spatial, 'coordinates') && !isNil(spatial.coordinates)">
                  {{ $t('message.metadata.coordinates') }}:
                  {{ spatial.coordinates }}
                </div>
                <div v-if="has(spatial, 'type') && !isNil(spatial.type)">
                  {{ $t('message.metadata.type') }}:
                  {{ spatial.type }}
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getSpatialResource)">
            <td class=" by-caption">{{ $t('message.metadata.spatialResource') }}</td>
            <td>
              <div v-for="(spatialResource, i) of getSpatialResource.map(s => s.resource || '')" :key="i">
                <app-link class="text-by-blue-80" v-if="!isNil(spatialResource)" :to="spatialResource">
                  {{ truncate(spatialResource, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getConformsTo)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.conformsTo')">
                {{ $t('message.metadata.conformsTo') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(conformTo, i) in getConformsTo" :key="i">
                <div v-if="has(conformTo, 'title') && !isNil(conformTo.title)">
                  {{ $t('message.metadata.label') }}:
                  {{ conformTo.title }}
                </div>
                <div v-if="has(conformTo, 'resource') && !isNil(conformTo.resource)">
                  {{ $t('message.metadata.resource') }}:
                  <app-link class="text-by-blue-80" :to="conformTo.resource" target="_blank"
                    @click="$emit('track-link', conformTo.resource, 'link')">
                    {{ truncate(conformTo.resource, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getProvenances)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.provenance')">
                {{ $t('message.metadata.provenances') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(provenance, i) in getProvenances" :key="i">
                <div v-if="has(provenance, 'label') && !isNil(provenance.label)">
                  {{ $t('message.metadata.label') }}:
                  {{ provenance.label }}
                </div>
                <div v-if="has(provenance, 'resource') && !isNil(provenance.resource)">
                  {{ $t('message.metadata.resource') }}:
                  <app-link class="text-by-blue-80" :to="provenance.resource">
                    {{ truncate(provenance.resource, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getRelatedResources)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.relatedResource')">
                {{ $t('message.metadata.relatedResources') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(resource, i) of getRelatedResources" :key="i">
                <app-link class="text-by-blue-80" v-if="!isNil(resource)" :to="resource">
                  {{ truncate(resource, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getIdentifiers)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.identifier')">
                {{ $t('message.metadata.identifiers') }}
                <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <g id="ArrowSquareOut">
                    <path id="Vector"
                      d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                      fill="currentColor" />
                  </g>
                </svg>
              </tooltip>
            </td>
            <td>
              <div v-for="(identifier, i) of getIdentifiers" :key="i">
                <app-link class="text-by-blue-80" :to="appendCurrentLocaleToURL(identifier)"
                  v-if="showString(identifier)">
                  {{ truncate(identifier, 75) }}
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getOtherIdentifiers)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.otherIdentifier')">
                {{ $t('message.metadata.otherIdentifiers') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(otherIdentifier, i) of getOtherIdentifiers" :key="i">
                <div v-if="has(otherIdentifier, 'identifier') && !isNil(otherIdentifier.identifier)">
                  {{ $t('message.metadata.identifier') }}:
                  <app-link class="text-by-blue-80" :to="otherIdentifier.resource || otherIdentifier.identifier">
                    {{ otherIdentifier.identifier }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
                <div v-if="has(otherIdentifier, 'scheme') && !isNil(otherIdentifier.scheme)">
                  {{ $t('message.metadata.scheme') }}:
                  <app-link class="text-by-blue-80" :to="otherIdentifier.scheme">
                    {{ otherIdentifier.scheme }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <!-- <tr v-if="showString(getResource)" class="dsd-properties-uriref">
                            <td class=" by-caption">
                              <tooltip :title="$t('message.tooltip.datasetDetails.uriRef')">
                                URIref
                              </tooltip>
                            </td>
                            <td>
                              <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(getResource)">{{ truncate(getResource, 75) }}</a>
                            </td>
                          </tr> -->
          <tr v-if="showArray(getDocumentations)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.documentation')">
                {{ $t('message.metadata.documentations') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(documentation, i) of getDocumentations" :key="i">
                <app-link class="text-by-blue-80" v-if="!isNil(documentation)" :to="documentation">
                  {{ truncate(documentation, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showObject(getFrequency)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.frequency')">
                {{ $t('message.metadata.frequency') }}
              </tooltip>
            </td>
            <td>
              <div v-if="has(getFrequency, 'title') && !isNil(getFrequency.title)">
                {{ getFrequency.title }}
              </div>
              <div v-if="has(getFrequency, 'resource') && !isNil(getFrequency.resource)">
                <app-link class="text-by-blue-80" :to="getFrequency.resource">
                  {{ truncate(getFrequency.resource, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showObject(getAccessRights)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.distributions.rights')">
                {{ $t('message.metadata.accessRights') }}
              </tooltip>
            </td>
            <td v-if="has(getAccessRights, 'label') && !isNil(getAccessRights.label)">{{ getAccessRights.label }}</td>
          </tr>
          <tr v-if="showString(getAccrualPeriodicityLabel)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.frequency')">
                {{ $t('message.metadata.accrualPeriodicity') }}
              </tooltip>
            </td>
            <td v-if="!isNil(getAccrualPeriodicityLabel)">{{ getAccrualPeriodicityLabel }}</td>
          </tr>
          <tr v-if="showObject(getCreator)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.creator')">
                {{ $t('message.metadata.creator') }}
              </tooltip>
            </td>
            <td>
              <div v-if="has(getCreator, 'name') && !isNil(getCreator.name)">
                {{ $t('message.metadata.name') }}:
                {{ getCreator.name }}
              </div>
              <div v-if="has(getCreator, 'email') && !isNil(getCreator.email)">
                {{ $t('message.metadata.email') }}:
                <app-link class="text-by-blue-80" :to="`mailto:${removeMailtoOrTel(getCreator.email)}`">
                  {{ truncate(removeMailtoOrTel(getCreator.email), 75) }}
                </app-link>
              </div>
              <div v-if="has(getCreator, 'homepage') && !isNil(getCreator.homepage)">
                {{ $t('message.metadata.homepage') }}:
                <app-link class="text-by-blue-80" :to="getCreator.homepage">
                  {{ truncate(getCreator.homepage, 75) }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </app-link>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getHasVersion)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.hasVersion')">
                {{ $t('message.metadata.hasVersion') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(hasVersion, i) of getHasVersion" :key="i">
                <div v-if="!isNil(hasVersion) && isString(hasVersion)">
                  <app-link class="text-by-blue-80" :to="hasVersion">
                    {{ truncate(hasVersion, 75) }}
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getIsVersionOf)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.versionOf')">
                {{ $t('message.metadata.isVersionOf') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(isVersionOf, i) of getIsVersionOf" :key="i">
                <div v-if="!isNil(isVersionOf) && isString(isVersionOf)">
                  <app-link class="text-by-blue-80" :to="isVersionOf">{{ truncate(isVersionOf, 75) }}</app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getTemporal)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.distributions.temporalResolution')">
                {{ $t('message.metadata.temporal') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(temporal, i) of getTemporal" :key="i">
                <div v-if="has(temporal, 'gte') && !isNil(temporal.gte) && has(temporal, 'lte') && !isNil(temporal.lte)">{{ formatDatetime(temporal.gte) }} - {{ formatDatetime(temporal.lte) }}</div>
                <div v-if="!(has(temporal, 'gte') && !isNil(temporal.gte)) && has(temporal, 'lte') && !isNil(temporal.lte)"><em>Unbekannt</em> - {{ formatDatetime(temporal.lte) }}</div>
                <div v-if="has(temporal, 'gte') && !isNil(temporal.gte) && !(has(temporal, 'lte') && !isNil(temporal.lte))">{{ formatDatetime(temporal.gte) }} - <em>Unbekannt</em></div>
              </div>
            </td>
          </tr>
          <tr v-if="showString(getVersionInfo)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.versionInfo')">
                {{ $t('message.metadata.versionInfo') }}
              </tooltip>
            </td>
            <td v-if="!isNil(getVersionInfo)">{{ getVersionInfo }}</td>
          </tr>
          <tr v-if="showObject(getVersionNotes)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.versionNotes')">
                {{ $t('message.metadata.versionNotes') }}
              </tooltip>
            </td>
            <td>{{ getTranslationFor(getVersionNotes, $route.query.locale || 'de') }}</td>
          </tr>
          <tr v-if="showArray(getAttributes)">
            <td class=" by-caption">{{ $t('message.metadata.attributes') }}</td>
            <td>
              <div v-for="(attribute, i) of getAttributes" :key="i">
                <div v-if="showString(attribute)">
                  <app-link class="text-by-blue-80" :to="attribute">
                    {{ truncate(attribute, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getDimensions)">
            <td class=" by-caption">{{ $t('message.metadata.dimensions') }}</td>
            <td>
              <div v-for="(dimension, i) of getDimensions" :key="i">
                <div v-if="showString(dimension)">
                  <app-link class="text-by-blue-80" :to="dimension">
                    {{ truncate(dimension, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showNumber(getNumSeries)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.numSeries')">
                {{ $t('message.metadata.numSeries') }}
              </tooltip>
            </td>
            <td>
              {{ getNumSeries }}
            </td>
          </tr>
          <tr v-if="showArray(getHasQualityAnnotations)">
            <td class=" by-caption">{{ $t('message.metadata.qualityAnnotations') }}</td>
            <td>
              <div v-for="(hasQualityAnnotation, i) of getHasQualityAnnotations" :key="i">
                <div v-if="showString(hasQualityAnnotation)">
                  <app-link class="text-by-blue-80" :to="hasQualityAnnotation">
                    {{ truncate(hasQualityAnnotation, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getStatUnitMeasures)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.unitsOfMeasurement')">
                {{ $t('message.metadata.unitsOfMeasurement') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(statUnitMeasure, i) of getStatUnitMeasures" :key="i">
                <div v-if="showString(statUnitMeasure)">
                  <app-link class="text-by-blue-80" :to="statUnitMeasure">
                    {{ truncate(statUnitMeasure, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </app-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getIsReferencedBy)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.isReferencedBy')">
                {{ $t('message.metadata.isReferencedBy') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(reference, i) of getIsReferencedBy" :key="i">
                <div v-if="showString(reference)">
                  <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(reference)">
                    {{ truncate(reference, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </a>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getQualifiedAttributions)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.qualifiedAttribution')">
                {{ $t('message.metadata.qualifiedAttribution') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(qualifiedAttribution, i) of getQualifiedAttributions" :key="i">
                <div v-if="showString(qualifiedAttribution)">
                  <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(qualifiedAttribution)">
                    {{ truncate(qualifiedAttribution, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </a>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getWasGeneratedBy)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.wasGeneratedBy')">
                {{ $t('message.metadata.wasGeneratedBy') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(wasGeneratedBy, i) of getWasGeneratedBy" :key="i">
                <div v-if="showString(wasGeneratedBy)">
                  <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(wasGeneratedBy)">
                    {{ truncate(wasGeneratedBy, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </a>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getQualifiedRelations)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.qualifiedRelation')">
                {{ $t('message.metadata.qualifiedRelation') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(qualifiedRelation, i) of getQualifiedRelations" :key="i">
                <div v-if="has(qualifiedRelation, 'relation') && !isNil(qualifiedRelation.relation)">
                  {{ $t('message.metadata.relation') }}:
                  <div v-for="(relation, i) of qualifiedRelation.relation" :key="i" class="d-inline-table">
                    <div v-if="showString(relation)">
                      <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(relation)">
                        {{ truncate(relation, 75) }}
                        <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <g id="ArrowSquareOut">
                            <path id="Vector"
                              d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                              fill="currentColor" />
                          </g>
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
                <div v-if="has(qualifiedRelation, 'had_role') && !isNil(qualifiedRelation.had_role)">
                  {{ $t('message.metadata.role') }}:
                  <div v-for="(role, i) of qualifiedRelation.had_role" :key="i" class="d-inline-table">
                    <div v-if="showString(role)">
                      <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(role)">
                        {{ truncate(role, 75) }}
                        <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <g id="ArrowSquareOut">
                            <path id="Vector"
                              d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                              fill="currentColor" />
                          </g>
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getSample)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.sample')">
                {{ $t('message.metadata.sample') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(sample, i) of getSample" :key="i">
                <div v-if="showString(sample)">
                  <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(sample)">
                    {{ truncate(sample, 75) }}
                    <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <g id="ArrowSquareOut">
                        <path id="Vector"
                          d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                          fill="currentColor" />
                      </g>
                    </svg>
                  </a>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="showArray(getSpatialResolutionInMeters)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.spatialResolutionInMeters')">
                {{ $t('message.metadata.spatialResolutionInMeters.label') }}
              </tooltip>
            </td>
            <td>
              <div v-if="showNumber(getSpatialResolutionInMeters[0])">
                {{ $t('message.metadata.spatialResolutionInMeters.value', { number: getSpatialResolutionInMeters[0] })
                }}
              </div>
            </td>
          </tr>
          <template v-if="false">
            <tr v-if="showObject(getType)">
              <td class=" by-caption">
                <tooltip :title="$t('message.tooltip.datasetDetails.type')">
                  {{ $t('message.metadata.type') }}
                  <svg class="ml-1 pb-1" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="ArrowSquareOut">
                      <path id="Vector"
                        d="M21 9.75C21 9.94891 20.921 10.1397 20.7803 10.2803C20.6397 10.421 20.4489 10.5 20.25 10.5C20.0511 10.5 19.8603 10.421 19.7197 10.2803C19.579 10.1397 19.5 9.94891 19.5 9.75V5.56125L13.2816 11.7806C13.1408 11.9214 12.95 12.0004 12.7509 12.0004C12.5519 12.0004 12.361 11.9214 12.2203 11.7806C12.0796 11.6399 12.0005 11.449 12.0005 11.25C12.0005 11.051 12.0796 10.8601 12.2203 10.7194L18.4387 4.5H14.25C14.0511 4.5 13.8603 4.42098 13.7197 4.28033C13.579 4.13968 13.5 3.94891 13.5 3.75C13.5 3.55109 13.579 3.36032 13.7197 3.21967C13.8603 3.07902 14.0511 3 14.25 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75V9.75ZM17.25 12C17.0511 12 16.8603 12.079 16.7197 12.2197C16.579 12.3603 16.5 12.5511 16.5 12.75V19.5H4.5V7.5H11.25C11.4489 7.5 11.6397 7.42098 11.7803 7.28033C11.921 7.13968 12 6.94891 12 6.75C12 6.55109 11.921 6.36032 11.7803 6.21967C11.6397 6.07902 11.4489 6 11.25 6H4.5C4.10218 6 3.72064 6.15804 3.43934 6.43934C3.15804 6.72064 3 7.10218 3 7.5V19.5C3 19.8978 3.15804 20.2794 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H16.5C16.8978 21 17.2794 20.842 17.5607 20.5607C17.842 20.2794 18 19.8978 18 19.5V12.75C18 12.5511 17.921 12.3603 17.7803 12.2197C17.6397 12.079 17.4489 12 17.25 12Z"
                        fill="currentColor" />
                    </g>
                  </svg>
                </tooltip>
              </td>
              <td>
                <div v-if="has(getType, 'label') && !isNil(getType.label)">
                  {{ getType.label }}
                </div>
                <div v-if="has(getType, 'resource') && !isNil(getType.resource)">
                  <a class="text-by-blue-80" :href="appendCurrentLocaleToURL(getType.resource)">
                    {{ truncate(getType.resource, 75) }}
                  </a>
                </div>
              </td>
            </tr>
          </template>
          <tr v-if="showArray(getTemporalResolution)">
            <td class=" by-caption">
              <tooltip :title="$t('message.tooltip.datasetDetails.temporalResolution')">
                {{ $t('message.metadata.temporalResolution') }}
              </tooltip>
            </td>
            <td>
              <div v-for="(temporalResolution, i) of getTemporalResolution" :key="i">
                {{ formatDatetime(temporalResolution) || 'Unbekannt' }}
              </div>
            </td>
          </tr>
          <tr v-if="showObjectArray(getApplicableLegislation)">
            <td class="by-caption">
              Anwendbare Gesetzgebung
            </td>
            <td>
              <ul>
                <li v-for="(legislation, i) in getApplicableLegislation" :key="i">
                  <a class="text-by-blue-80" :href="legislation">
                    {{ truncate(legislation || '', 75) }}
                  </a>
                </li>
              </ul>
            </td>
          </tr>
          <tr v-if="showArray(getHvdCategory)">
            <td class="by-caption">
              HVD Kategorien
            </td>
            <td>
              <ul>
                <li v-for="(category, i) of getHvdCategory" :key="i">
                  <a class="text-by-blue-80" :href="category.resource">
                    {{ getTranslationFor(category.label, $route.query.locale || 'de') }}
                  </a>
                </li>
              </ul>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <!-- <pv-show-more
                    v-if="showMoreVisible"
                    :label="expanded? $t('message.metadata.showLess') : $t('message.metadata.showMore')"
                    :upArrow="expanded"
                    :action="toggleExpanded"
                    class="row text-primary"
                    /> -->
  </div>
</template>

<script>
import { isArray, isNil, isString, has, isNumber, isObject } from "lodash-es";
import { mapGetters } from "vuex";
import DatasetDetailsFeatureHeader from "./DatasetDetailsFeatureHeader.vue";
import {
  truncate,
  getTranslationFor,
  Tooltip,
  AppLink,
  dateFilters
} from "@piveau/piveau-hub-ui-modules";
import { formatDatetime, localeCodeToGermanMini } from '../../utils/utils';
// import { ArrowSquareOut } from "phosphor-vue";

export default {
  name: "DatasetDetailsProperties",
  components: { DatasetDetailsFeatureHeader, Tooltip, AppLink },
  data() {
    return {
      infoVisible: true,
      initialHeight: 0,
      restrictedHeight: 200,
      expanded: false,
      localeCodeToGermanMini
    };
  },
  computed: {
    ...mapGetters('datasetDetails', [
      // DCAT-AP.de
      'getAvailability',
      'getPoliticalGeocodingLevelURI',
      'getPoliticalGeocodingURI',
      'getContributorID',
      'getGeocodingDescriptionDe',
      'getLegalBasis',
      'getQualityProcessURI',
      'getTypeDe',
      'getReferences',
      'getContributor',
      'getOriginator',
      'getMaintainer',
      //
      'getSources',
      'getAccessRights',
      'getAccrualPeriodicity',
      'getAttributes',
      // 'getCatalogRecord',
      'getConformsTo',
      'getContactPoints',
      'getCreator',
      'getDimensions',
      'getDocumentations',
      'getFrequency',
      'getHasQualityAnnotations',
      'getHasVersion',
      'getIdentifiers',
      'getIsVersionOf',
      'getIsReferencedBy',
      'getLandingPages',
      'getLanguages',
      'getModificationDate',
      'getNumSeries',
      'getOtherIdentifiers',
      'getProvenances',
      'getPublisher',
      'getRelatedResources',
      'getReleaseDate',
      'getResource',
      'getSample',
      'getSpatial',
      'getSpatialResolutionInMeters',
      'getSpatialResource',
      'getStatUnitMeasures',
      'getTemporal',
      'getTemporalResolution',
      'getType',
      'getVersionInfo',
      'getVersionNotes',
      'getQualifiedAttributions',
      'getQualifiedRelations',
      'getWasGeneratedBy',

      // hvd
      'getApplicableLegislation',
      'getHvdCategory',
    ]),
    // Provides resource data only of landing pages
    // Example: [{ format: 'bar', resource: 'foo' }, ...] -> ['foo']
    getLandingPagesResource() {
      return isArray(this.getLandingPages) && this.getLandingPages.map(value => value && value.resource);
    },
    // Returns the label property of accrual periodicity
    getAccrualPeriodicityLabel() {
      return !isNil(this.getAccrualPeriodicity) && has(this.getAccrualPeriodicity, 'label') ? this.getAccrualPeriodicity.label : '';
    },
    showMoreVisible() {
      return this.initialHeight > this.restrictedHeight;
    }
  },
  methods: {
    isNil,
    truncate,
    isString,
    has,
    // removeMailtoOrTel,
    // appendCurrentLocaleToURL,
    getTranslationFor,
    // formatDatetime,
    removeMailtoOrTel(str) {
      return str.replace(/^(mailto|tel):/, '');
    },
    appendCurrentLocaleToURL(url) {
      try {
        const urlHost = new URL(url).host;
        const baseUrlHost = new URL(this.$env.api.baseUrl).host;
        const isOurHostname = urlHost === baseUrlHost;
        if (isOurHostname) {
          return `${url}?locale=${this.$route.query.locale}`;
        }
        return url;
      } catch {
        // when there is no hostname then it should link to our website
        return `${url}?locale=${this.$route.query.locale}`;
      }
    },
    addPrecedingZero(value) {
      return `${parseInt(value, 10) < 10 ? 0 : ''}${parseInt(value, 10)}`;
    },
    formatDatetime,
    toggleInfo() {
      this.infoVisible = !this.infoVisible;
    },
    showContactPoint(contactPoints) {
      return Object.keys(contactPoints[0]).filter(contactPoint => contactPoint !== 'resource' && contactPoint !== 'type').length > 0;
    },
    filterDateFormatEU(date) {
      return dateFilters.formatEU(date)
    },
    /* ABSTRACT SHOW FUNCTIONS */
    showString(string) {
      return !isNil(string) && isString(string);
    },
    showNumber(number) {
      return !isNil(number) && isNumber(number);
    },
    showObject(object) {
      return !isNil(object) && isObject(object) && !Object.values(object).reduce((keyUndefined, currentValue) => keyUndefined && currentValue === undefined, true);
    },
    showArray(array) {
      return !isNil(array) && isArray(array) && array.length > 0;
    },
    showObjectArray(objectArray) {
      return this.showArray(objectArray) && !objectArray.reduce((objectUndefined, currentObject) => objectUndefined && Object.values(currentObject).reduce((keyUndefined, currentValue) => keyUndefined && currentValue === undefined, true), true);
    },
    // toggleExpanded() {
    //   this.expanded = ! this.expanded;
    //   this.adaptHeight();
    // },
    // adaptHeight() {
    //   this.$refs.dsdProperties.style['flex'] = this.expanded ? "0 0 100%": `0 0 ${this.restrictedHeight}px`;
    //   this.$refs.dsdProperties.style['max-height'] = this.expanded ? "100%": `${this.restrictedHeight}px`;
    //   // this.$refs.dsdProperties.style['overflow-y'] = this.expanded ? "auto": "hidden";
    // }
  },
  watch: {
    'getApplicableLegislation': {
      handler() {
        console.log('getApplicableLegislation', this.getApplicableLegislation, this.showArray(this.getApplicableLegislation));
      },
      immediate: true
    }
  }
  // mounted() {
  //   this.initialHeight = this.$refs.dsdProperties.clientHeight;
  //   this.$refs.overlay.style.bottom = (this.$refs.dsdProperties.offsetHeight - this.$refs.dsdProperties.clientHeight) + "px";
  //   this.adaptHeight();
  // }
}
</script>

<style scoped lang="scss">
.dsd-properties-list {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding: 47px 0 136px 0;
  margin-bottom: 0 !important;
}

.dsd-item.additional-information {
  // padding: 0 15px;
  margin-left: 0;
}

.additional-information>table {
  border-collapse: separate;
  border-spacing: 0 24px;
}

.additional-information>table>tr>td {
  display: block;
  padding: 0;
  padding-bottom: 8px;
}


td.by-caption {
  // background-color: red;
  padding-bottom: 4px !important;
}

@media screen and (max-width: 991px) {

  // less than lg
  .dsd-properties {
    // padding: 0 15px
  }

  .dsd-item.additional-information {
    margin-left: 0 !important;
  }
}
</style>
