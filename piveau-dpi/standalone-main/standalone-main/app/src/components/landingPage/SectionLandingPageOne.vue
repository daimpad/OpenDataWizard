<template>
  <SectionLandingPageBase big class="bayern-landing-page__section bayern-landing-page__section--one">
    <template #title>
      Offene Daten aus Bayern
    </template>
    <div class="hero-content">
      <form class="input-group input-dark-container mb-2" @submit.prevent="submitSearch">
        <input v-model="query" type="text" class="h-auto form-control input-dark align-items-start"
          placeholder="Datensätze suchen" aria-label="Suchbegriff" aria-describedby="basic">
        <div class="input-group-append">
          <button class="btn btn-outline-secondary rounded-circle search-button" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path
                d="M21.5306 20.4693L16.8365 15.7762C18.1971 14.1428 18.8755 12.0478 18.7307 9.92691C18.5859 7.80604 17.629 5.82265 16.0591 4.38932C14.4892 2.95599 12.4271 2.18308 10.3019 2.23138C8.17663 2.27968 6.15181 3.14547 4.64864 4.64864C3.14547 6.15181 2.27968 8.17663 2.23138 10.3019C2.18308 12.4271 2.95599 14.4892 4.38932 16.0591C5.82265 17.629 7.80604 18.5859 9.92691 18.7307C12.0478 18.8755 14.1428 18.1971 15.7762 16.8365L20.4693 21.5306C20.539 21.6003 20.6218 21.6556 20.7128 21.6933C20.8038 21.731 20.9014 21.7504 21 21.7504C21.0985 21.7504 21.1961 21.731 21.2871 21.6933C21.3782 21.6556 21.4609 21.6003 21.5306 21.5306C21.6003 21.4609 21.6556 21.3782 21.6933 21.2871C21.731 21.1961 21.7504 21.0985 21.7504 21C21.7504 20.9014 21.731 20.8038 21.6933 20.7128C21.6556 20.6218 21.6003 20.539 21.5306 20.4693ZM3.74997 10.5C3.74997 9.16495 4.14585 7.8599 4.88755 6.74987C5.62925 5.63984 6.68345 4.77467 7.91686 4.26378C9.15026 3.75289 10.5075 3.61922 11.8168 3.87967C13.1262 4.14012 14.3289 4.78299 15.2729 5.727C16.2169 6.671 16.8598 7.87374 17.1203 9.18311C17.3807 10.4925 17.247 11.8497 16.7362 13.0831C16.2253 14.3165 15.3601 15.3707 14.2501 16.1124C13.14 16.8541 11.835 17.25 10.5 17.25C8.71037 17.248 6.99463 16.5362 5.72919 15.2707C4.46375 14.0053 3.75195 12.2896 3.74997 10.5Z"
                fill="#FAFAFB" />
            </svg>
          </button>
        </div>
      </form>
      <div class="category-shortcuts by-copy-small-bold mb-5">
        <router-link v-for="(category, index) in categories" :key="index" :to="{ name: 'Datasets', query: { query: category.query, locale: 'de' } }"
          class="category-shortcuts__link link-primary">
          {{ category.label }}
        </router-link>
      </div>
      <p class="by-copy-large-regular text-by-neutral-20">
        Hier finden Datenbegeisterte freie Datensätze und Unterstützung, um noch mehr Daten zu teilen. Damit
        schaffen wir gemeinsam – Verwaltung, Unternehmen, aber auch Wissenschaft und Zivilgesellschaft – Mehrwert für uns
        alle.
      </p>
    </div>
  </SectionLandingPageBase>
</template>

<script>
import { defineComponent } from 'vue';
import SectionLandingPageBase from './SectionLandingPageBase.vue';

export default defineComponent({
  components: {
    SectionLandingPageBase
  },
  data() {
    return {
      categories: [
        { label: 'Verkehr', query: 'verkehr' },
        { label: 'Windenergie', query: 'windenergie' },
        { label: 'Schlösser', query: 'schlösser' }
      ],
      query: '',
    }
  },
  methods: {
    submitSearch() {
      this.$router.push({ name: 'Datasets', query: { query: this.query, locale: 'de' } });
    }
  }
});

</script>

<style lang="scss">
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';

.bayern-landing-page__section--one {
  .bayern-landing-page-section-content {
    position: relative;

    &> * {
      position: relative;
      z-index: 1;
    }

    &::before {
      position: absolute;
      width: 711px;
      height: 948px;
      z-index: 0;
      opacity: 0.5;
      background-image: url('@/assets/img/top-illu--web.svg');
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
      content: '';

      left: -236px;
      right: auto;
      top: -217px;

      @include media-breakpoint-up(sm) {
        left: auto;
        right: -180px;
        top: -284px;
      }

      @include media-breakpoint-up(lg) {
        left: auto;
        right: -180px;
        top: -289px;
        opacity: 1;
      }

      @include media-breakpoint-up(xl) {
        left: auto;
        right: -228px;
        top: -302px;
      }
    }
  }

  .hero-content {
    max-width: initial;

    @include media-breakpoint-up(lg) {
      max-width: 50%;
    }
  }

  .input-dark-container {
    .input-dark {
      border-radius: var(--border-radius, 8px);
      border: 1px solid var(---by-neutral-60, #687178);
      background: var(---by-neutral-100, #0B1A25);
      height: 100%;

      color: var(--neutral-30);
    }
  }


  .search-button {
    margin-left: .5rem;
    display: flex;
    width: 52px;
    height: 52px;
    padding: 12px;
    justify-content: center;
    align-items: center;
    gap: 5px;
    border-radius: 26px;
    background: var(--by-blue-blue-100-secondary, #003F6F);
  }

  .category-shortcuts {
    display: flex;
    gap: 1rem;
    align-items: flex-start;

    &__link::before {
      // arrow
      content: '-> ';
    }
  }
}

.community-strength-container {
  gap: 2.5rem;
}

.avatar-container {
  gap: 2rem;

  .avatar {
    width: 6rem;
    height: 6rem;
    border-radius: 50%;
    background-color: #C4C4C4;
  }
}
</style>
