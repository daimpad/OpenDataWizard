<template>
    <div class="flex-column dsd-feature">
      <div>
        <dataset-details-feature-header
        class="mb-7"
        :title="`${$t('message.datasetDetails.subnav.categories')}`"
        tag="keywords-toggle"
        />
      </div>
      <div v-if="isCategoriesAllDisplayed">
          <span
            v-for='(category, i) in getCategories'
            :key="i"
          >
            <app-link :to="getCategoryLink(category)" :rel="followCategoryLinks">
              <span class="d-none d-sm-inline-flex by-chip by-chip-interactive mr-2 mb-2"
                     :aria-label="getTranslationFor(category.title, $route.query.locale)"
                     :title="getTranslationFor(category.title, $route.query.locale)">
                {{ getTranslationFor(category.title, $route.query.locale) }}
              </span>
            </app-link>
            <app-link :to="getCategoryLink(category)" :rel="followCategoryLinks">
              <span class="d-inline-flex d-sm-none by-chip by-chip-interactive mr-2 mb-2"
                     :aria-label="getTranslationFor(category.title, $route.query.locale)"
                     :title="getTranslationFor(category.title, $route.query.locale)">
                {{ truncate(getTranslationFor(category.title, $route.query.locale), 28) }}
              </span>
            </app-link>
          </span>
      </div>
      <div>
      </div>
    </div>
  </template>

  <script>
  import DatasetDetailsFeatureHeader from "./DatasetDetailsFeatureHeader";
  import { AppLink, helpers } from "@piveau/piveau-hub-ui-modules";
  import {mapGetters} from "vuex";
  //   import $ from "jquery";
  
  const { getTranslationFor, truncate, sortAlphabetically } = helpers;

  export default {
  name: "DatasetDetailsCategoriesKey",
  components: {
    AppLink,
    DatasetDetailsFeatureHeader
  },
  props: {
    showCategory: Function
  },
  data() {
    return {
      defaultLocale: this.$env.languages.locale,
      defaultDisplayCount: 0,
      categories: {
        displayAll: this.$env.content.datasetDetails.categoriesKey.collapsed,
        displayCount: 24, // Should never exceed number of keywords
        incrementSteps: [12, 60],
      },
      maxCategoryLength: 15,
      followCategoryLinks: this.$env.content.datasets.followCategoryLinks
    }
  },
  computed: {
    ...mapGetters('datasetDetails', [
        "getCategories"
    ]),
    isCategoriesAllDisplayed() {
      // return this.categories.displayCount >= this.getCategories.length;
      return this.categories.displayAll;
    }
  },
  methods: {
    truncate,
    sortAlphabetically,
    // Increases the current number of keywords displayed
    // and clamps the result so that it never exceeds the number of all keywords.
    increaseNumDisplayedKeywords(increment) {
      const clampedSum = this.clamp(this.categories.displayCount + increment, 0, this.getCategories.length);
      this.categories.displayCount = clampedSum;
    },
    getTranslationFor,
    nonOverflowingIncrementsForKeywords(incrementStep) {
      return this.categories.displayCount + incrementStep <= this.getCategories.length;
    },
    categoryTruncated(category) {

      return getTranslationFor(category.title, this.defaultLocale).length > this.maxCategoryLength;

    },
    clamp(n, min, max) {
      return Math.min(Math.max(n, min), max);
    },
    getCategoryLink(category) {
        const categoryID = category.id.toUpperCase();
        return {
          path: `/datasets?categories=${categoryID}`,
          query: Object.assign({}, { locale: this.$route.query.locale }),
        };
      },
    // toggleDisplayCount() {
    //   $('body').tooltip({selector: '[data-toggle="tooltip"]'});
    //   if (this.categories.displayCount < this.getCategories.length) {
    //     this.categories.displayCount = this.getCategories.length;
    //   } else {
    //     this.categories.displayCount = this.defaultDisplayCount;
    //   }
    //   this.categories.displayAll = !this.categories.displayAll;
    // }
  }
  }
  </script>

  <style scoped lang="scss">
  .dsd-feature {
    padding: 47px 0 48px 0;
    margin-top: 0 !important;
  }
  </style>
