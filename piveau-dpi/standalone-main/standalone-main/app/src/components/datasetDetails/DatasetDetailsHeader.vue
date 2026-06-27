<template>
    <!-- HEADER -->
    <div class="dsd-header">
      <dataset-details-header-title :previous-route="previousRoute"/>
      <div class="d-flex flex-column flex-nowrap justify-content-between">
        <dataset-details-header-catalogue class="mb-5"/>
        <div class="d-flex dsd-header-properties by-copy-small-regular mb-5">
          <property-value
            v-if="!hidePublisher && showObject(getPublisher)"
            :property="$t('message.metadata.publisher')"
            :tooltip="$t('message.tooltip.datasetDetails.publisher')"
            :value="getPublisherName"
            class="dsd-header-property mr-3 w-100"
          />
          <property-value
            v-if="!hideDate"
            :property="$t('message.metadata.updated')"
            :tooltip="$t('message.tooltip.datasetDetails.updated')"
            :value="getModificationDate"
            :isDate="true"
            class="dsd-header-property w-100"
          />
        </div>
      </div>
    </div>
  </template>

  <script>
    // import Actions and Getters from Store Module
    import { mapGetters } from 'vuex';
    // import helper functions
    import { has, isNil, isObject } from 'lodash-es';
    import {
        DatasetDate,
        AppLink,
        dateFilters,
        getTranslationFor, getCountryFlagImg, truncate,
    } from "@piveau/piveau-hub-ui-modules"
    import PropertyValue from "@/components/widgets/PropertyValue.vue";
    import DatasetDetailsHeaderTitle from "./DatasetDetailsHeaderTitle.vue"
    import DatasetDetailsHeaderCatalogue from "./DatasetDetailsHeaderCatalogue.vue"

    export default {
      name: 'datasetDetailsHeader',
      components: {
        PropertyValue,
        DatasetDetailsHeaderCatalogue,
        DatasetDetailsHeaderTitle,
        DatasetDate,
        AppLink,
      },
      props: {
        previousRoute: {
          type: String,
          default: '',
        }
      },
      dependencies: 'DatasetService',
      data() {
        return {
          hidePublisher: this.$env.content.datasetDetails.header.hidePublisher,
          hideDate: this.$env.content.datasetDetails.header.hideDate,
        };
      },
      computed: {
        // import store-getters
        ...mapGetters('datasetDetails', [
          'getCatalog',
          'getCountry',
          'getLanguages',
          'getPublisher',
          'getModificationDate'
        ]),
        getPublisherName() {
          if (has(this.getPublisher, 'name') && !isNil(this.getPublisher.name)) {
            return this.getPublisher.name;
          } else {
            return "";
          }
        }
      },
      methods: {
        has,
        isNil,
        isObject,
        truncate,
        getTranslationFor,
        getCountryFlagImg,
        filterDateFormatUS(date) {
          return dateFilters.formatUS(date);
        },
        filterDateFormatEU(date) {
          return dateFilters.formatEU(date);
        },
        showObject(object) {
          return !isNil(object) && isObject(object) && !Object.values(object).reduce((keyUndefined, currentValue) => keyUndefined && currentValue === undefined, true);
        },
        filterDateFromNow(date) {
          return dateFilters.fromNow(date);
        }
      },
    };
  </script>

  <style lang="scss">
    .dsd-header-property > span:first-child  {
      // @extend .by-caption;
      width: fit-content;
      text-transform: uppercase;
      font-weight: 700;
      display: block;
      font-family: "Inter Variable", Arial, Helvetica, "Sans-Serif";
      font-size: 12px;
      line-height: 150%;
      margin-bottom: 4px;
    }

    @media screen and (max-width: 575px) {
      .dsd-header-properties {
        flex-direction: column;
      }
      .dsd-header-property:first-child {
        margin-bottom: 16px;
      }
    }

  </style>
