<template>
    <div v-if="isReady" class="bayern-odp-article-page p-0">
      <section class="bayern-odp-article-page__block">
        <div class="bayern-odp-article-page__title-block">
          <h1 class="by-heading-2 text-by-blue-100">{{ pageTitle }}</h1>
          <div class="d-flex flex-column w-100 align-items-center justify-content-center">
            <div v-if="imageUrl"><img class="bayern-odp-article-page__catalog-img bayern-odp-article-page__text-content" :src="imageUrl" alt="" @error="handleCatalogImgError"></div>
            <div class="mt-4">
              <AppMarkdownContent class="bayern-odp-article-page__text-content" :text="description" />
            </div>
          </div>
        </div>
      </section>

      <section class="bayern-odp-article-page__block bayern-odp-article-page__block--emphasized">
        <div class="bayern-odp-article-page__title-block">
          <div class="bayern-odp-article-page__text-content">
            <h2 class="by-heading-2 text-by-blue-100">Kontaktieren Sie uns!</h2>
            <div class="bayern-odp-article-page__body">
              <div class="mt-5 font-weight-bold by-heading-5">
                <div>Fragen zu dieser open-bydata-Präsenz?</div>
                <div>Interesse an weiteren Daten?</div>
              </div>
              <div class="mt-3" v-if="contact">
                  <p class="font-weight-bold">{{ contact.name }}</p>
                  <p v-if="contact.address">
                      <span v-if="contact.address.street">{{ contact.address.street }}<br/></span>
                      <span v-if="contact.address.postalCode && contact.address.locality">{{ contact.address.postalCode }} {{ contact.address.locality }}<br/></span>
                      <span v-if="contact.address.email"><a :href="`mailto:${contact.address.email}`">{{ contact.address.email }}</a></span>
                  </p>
                  <p v-if="contact.homepage"><a :href="contact.homepage">{{ contact.homepage }}</a></p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <div v-else class="bayern-odp-article-page--placeholder">
    </div>
  </template>

<script lang="ts">
  import { computed, defineComponent, inject } from 'vue';
  import { AppMarkdownContent } from '@piveau/piveau-hub-ui-modules'
  import PhCaretRight from '~icons/ph/caret-right';
  import PhArrowRight from '~icons/ph/arrow-right';
import { useLogoCase } from '@/composables/useLogoCase';

  export default defineComponent({
    name: 'OdpAboutPage',
    components: {
      PhCaretRight,
      PhArrowRight,
      AppMarkdownContent,
    },
    setup() {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const injectedPresenceData = inject('presenceData') as any
      if (!injectedPresenceData) {
        throw new Error('[OdpAboutPage] presenceData not provided. Make sure to wrap the component in a <PresencePage> component')
      }

      const {
      isReady,
      catalogId,
      enhancedCatalog,
    } = injectedPresenceData

    const pageTitle = computed(() => `${enhancedCatalog.value?.publisher?.name || enhancedCatalog.value?.title || catalogId}`)
    const imageUrl = computed(() => enhancedCatalog.value?.catalogueProfile?.[0] || undefined)
    const description = computed(() => enhancedCatalog.value?.description || enhancedCatalog.value?.description || undefined)
    const publisher = computed(() => enhancedCatalog.value?.publisher || undefined)
    const contact = computed(() => ({
      name: pageTitle.value,
      address: {
        street: publisher.value?.address?.street || undefined,
        postalCode: publisher.value?.address?.postalCode || undefined,
        locality: publisher.value?.address?.locality || undefined,
        email: publisher.value.email.startsWith("mailto:")
          ? publisher.value.email.slice(7)
          : publisher.value.email
      },
      homepage: enhancedCatalog?.value?.publisher?.homepage || undefined,
    }))

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    function handleCatalogImgError(e: any) {
      e.target.style.display = 'none'
    }

    const catalogTitleLogoCased = useLogoCase(catalogId)

    return {
      isReady,
      catalogId,
      enhancedCatalog,
      pageTitle,
      imageUrl,
      description,
      contact,
      handleCatalogImgError,
      catalogTitleLogoCased,
    }
  },
  });
</script>

<style lang="scss">
  // Override Styles
  .site-wrapper .content.bayern-landing-page {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }
</style>

<style scoped lang="scss">
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';
@import '../../styles/custom_theme.scss';

.bayern-odp-article-page {
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: $by-neutral-5;

  &--placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    width: 100%;
  }

  @include media-breakpoint-up(sm) {
    padding: 0 2rem;
  }

  &__body {
    display: flex;
    flex-direction: column;
    gap: 40px;
  }

  &__block {
    width: 100%;
    display: flex;
    padding: 112px var(--Spacer-Custom-1, 32px);
    flex-direction: column;
    align-items: center;
    gap: 80px;
    background: $by-neutral-5;

    &--emphasized {
      background: $by-neutral-10;
    }
  }

  &__title-block {
    display: flex;
    max-width: 1280px;
    flex-direction: column;
    gap: 48px;
    justify-content: center;
    align-items: center;
    width: 100%;

    @include media-breakpoint-up(md) {
      width: auto;
    }
  }

  &__text-content {
    align-self: stretch;

    @include media-breakpoint-up(md) {
      max-width: 624px;
      width: 624px;
    }
  }

  &__catalog-img {
    height: auto;
    max-width: 100%;
    align-self: stretch;
    object-fit: contain;

    @include media-breakpoint-up(xl) {
      max-width: 624px;
    }
  }

  &__section-container {
    width: 100%;
    display: grid;
    place-items: center;
    padding-left: 2rem;
    padding-right: 2rem;
    background-color: $by-neutral-5;

    &--one,
    &--two,
    &--three,
    &--four {
      padding-top: 7rem;
      padding-bottom: 7rem;
    }

    &--one {
      padding-top: 2.5rem;
      padding-bottom: 6rem;
    }

    &--two {
      padding-top: 2rem;
      padding-bottom: 2rem;
    }

    &--three {
      padding-top: 7rem;
      padding-bottom: 7rem;
      background-color: $by-neutral-10;
    }

    &--four {
      padding-top: 2rem;
      padding-bottom: 2rem;
      background-color: $by-neutral-0;
    }
  }

  &__section {
    position: relative;
    background-color: $by-neutral-5;
    width: 100%;
    max-width: 1280px;

    &--one {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 20px;
      align-self: stretch;
      max-width: 1280px;
    }

    &--two {
      gap: 50px;
      max-width: 600px;

      @include media-breakpoint-up(sm) {
        flex-direction: row;
        justify-content: start;
      }

      img {
        max-width:100%;
        height: auto;
      }
    }

    &--three {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 3rem;
      align-self: stretch;
      max-width: 600px;
      background-color: $by-neutral-10;
    }

    &--four {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: var(--Border-Radius, 8px);
      align-self: stretch;
      background: var(--Neutral-0, #FFF);
    }

    .odb-ref {
      display: flex;
      max-width: 1280px;
      flex-direction: column;
      align-items: flex-start;
      gap: 16px;
      align-self: stretch;
    }
  }
}
</style>
