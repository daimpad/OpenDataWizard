<template>
    <div>
      <div v-if="distributionDescriptionIsExpanded(distribution.id)" class="distribution-description-container">
        <p class="by-copy-small-regular text-by-neutral-60">
          <app-markdown-content
            :text="resolvedDistributionDescription">
          </app-markdown-content>
          <span class="details-link" @click="toggleDistributionDescription(distribution.id)">
            {{ $t('message.metadata.readLess') }}
          </span>
        </p>
        <p v-if="(getDistributionFormat(distribution) == 'WMS' || getDistributionFormat(distribution) == 'WFS' || getDistributionFormat(distribution) == 'Atom Feed')"
           class="by-copy-small-regular distribution-description-url-container">
          <svg class="distribution-description-url-copy-icon"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              @click="copyURL">
            <g id="CopySimple">
              <path id="Vector" d="M17.25 6H3.75C3.55109 6 3.36032 6.07902 3.21967 6.21967C3.07902 6.36032 3 6.55109 3 6.75V20.25C3 20.4489 3.07902 20.6397 3.21967 20.7803C3.36032 20.921 3.55109 21 3.75 21H17.25C17.4489 21 17.6397 20.921 17.7803 20.7803C17.921 20.6397 18 20.4489 18 20.25V6.75C18 6.55109 17.921 6.36032 17.7803 6.21967C17.6397 6.07902 17.4489 6 17.25 6ZM16.5 19.5H4.5V7.5H16.5V19.5ZM21 3.75V17.25C21 17.4489 20.921 17.6397 20.7803 17.7803C20.6397 17.921 20.4489 18 20.25 18C20.0511 18 19.8603 17.921 19.7197 17.7803C19.579 17.6397 19.5 17.4489 19.5 17.25V4.5H6.75C6.55109 4.5 6.36032 4.42098 6.21967 4.28033C6.07902 4.13968 6 3.94891 6 3.75C6 3.55109 6.07902 3.36032 6.21967 3.21967C6.36032 3.07902 6.55109 3 6.75 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75Z" fill="currentColor"/>
            </g>
          </svg>
          <span>{{ distribution.accessUrl[0] }}</span>
        </p>
      </div>
      <div v-else-if="!distributionDescriptionIsExpandable(resolvedDistributionDescription)" class="distribution-description-container">
        <p class="by-copy-small-regular text-by-neutral-60">
          <app-markdown-content
            :text="resolvedDistributionDescription">
          </app-markdown-content>
        </p>
        <p v-if="(getDistributionFormat(distribution) == 'WMS' || getDistributionFormat(distribution) == 'WFS' || getDistributionFormat(distribution) == 'Atom Feed')"
           class="by-copy-small-regular distribution-description-url-container">
          <svg class="distribution-description-url-copy-icon"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              @click="copyURL">
            <g id="CopySimple">
              <path id="Vector" d="M17.25 6H3.75C3.55109 6 3.36032 6.07902 3.21967 6.21967C3.07902 6.36032 3 6.55109 3 6.75V20.25C3 20.4489 3.07902 20.6397 3.21967 20.7803C3.36032 20.921 3.55109 21 3.75 21H17.25C17.4489 21 17.6397 20.921 17.7803 20.7803C17.921 20.6397 18 20.4489 18 20.25V6.75C18 6.55109 17.921 6.36032 17.7803 6.21967C17.6397 6.07902 17.4489 6 17.25 6ZM16.5 19.5H4.5V7.5H16.5V19.5ZM21 3.75V17.25C21 17.4489 20.921 17.6397 20.7803 17.7803C20.6397 17.921 20.4489 18 20.25 18C20.0511 18 19.8603 17.921 19.7197 17.7803C19.579 17.6397 19.5 17.4489 19.5 17.25V4.5H6.75C6.55109 4.5 6.36032 4.42098 6.21967 4.28033C6.07902 4.13968 6 3.94891 6 3.75C6 3.55109 6.07902 3.36032 6.21967 3.21967C6.36032 3.07902 6.55109 3 6.75 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75Z" fill="currentColor"/>
            </g>
          </svg>
          <span>{{ distribution.accessUrl[0] }}</span>
        </p>
      </div>
      <div v-else class="distribution-description-container">
        <p class="by-copy-small-regular text-by-neutral-60">
          <app-markdown-content
            :text="truncate(resolvedDistributionDescription, 350)">
          </app-markdown-content>
          <span class="details-link" @click="toggleDistributionDescription(distribution.id)">
            {{ $t('message.metadata.readMore') }}
          </span>
        </p>
        <p v-if="(getDistributionFormat(distribution) == 'WMS' || getDistributionFormat(distribution) == 'WFS' || getDistributionFormat(distribution) == 'Atom Feed')"
           class="by-copy-small-regular distribution-description-url-container">
          <svg class="distribution-description-url-copy-icon"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              @click="copyURL">
            <g id="CopySimple">
              <path id="Vector" d="M17.25 6H3.75C3.55109 6 3.36032 6.07902 3.21967 6.21967C3.07902 6.36032 3 6.55109 3 6.75V20.25C3 20.4489 3.07902 20.6397 3.21967 20.7803C3.36032 20.921 3.55109 21 3.75 21H17.25C17.4489 21 17.6397 20.921 17.7803 20.7803C17.921 20.6397 18 20.4489 18 20.25V6.75C18 6.55109 17.921 6.36032 17.7803 6.21967C17.6397 6.07902 17.4489 6 17.25 6ZM16.5 19.5H4.5V7.5H16.5V19.5ZM21 3.75V17.25C21 17.4489 20.921 17.6397 20.7803 17.7803C20.6397 17.921 20.4489 18 20.25 18C20.0511 18 19.8603 17.921 19.7197 17.7803C19.579 17.6397 19.5 17.4489 19.5 17.25V4.5H6.75C6.55109 4.5 6.36032 4.42098 6.21967 4.28033C6.07902 4.13968 6 3.94891 6 3.75C6 3.55109 6.07902 3.36032 6.21967 3.21967C6.36032 3.07902 6.55109 3 6.75 3H20.25C20.4489 3 20.6397 3.07902 20.7803 3.21967C20.921 3.36032 21 3.55109 21 3.75Z" fill="currentColor"/>
            </g>
          </svg>
          <span>{{ distribution.accessUrl[0] }}</span>
        </p>
      </div>
    </div>
  </template>

  <script>
import { truncate, AppMarkdownContent} from "@piveau/piveau-hub-ui-modules";

  export default {
    name: "DistributionDescription",
    props: [
      'distribution',
      'distributions',
      'distributionDescriptionIsExpanded',
      'getDistributionDescription',
      'toggleDistributionDescription',
      'distributionDescriptionIsExpandable',
      'getDistributionFormat'
    ],
    components: {
      AppMarkdownContent,
    },
    computed: {
      resolvedDistributionDescription() {
        const noDescriptionAvailable = `*${this.$t('message.catalogsAndDatasets.noDescriptionAvailable')}*`
        if (!this.distribution || !this.distribution.id) return noDescriptionAvailable
        return this.getDistributionDescription(this.distribution, '') || noDescriptionAvailable
      }
    },
    methods: {
      truncate,
      copyURL() {
        navigator.clipboard.writeText(
          this.distribution.accessUrl[0]);
      }
    }
  }
  </script>

  <style lang="scss" scoped>
  </style>
