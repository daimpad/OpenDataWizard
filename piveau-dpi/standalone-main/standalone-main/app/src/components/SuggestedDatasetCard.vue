<template>
  <RouterLink
    :to="{ name: 'DatasetDetailsDataset', params: { 'ds_id': id } }"
    :class="{
      'suggested-dataset-card suggested-dataset-card--dark': dark,
      'suggested-dataset-card': !dark
    }"
  >
    <slot name="category">
      <div v-if="false" class="suggested-dataset-card__category d-flex flex-row align-items-center by-copy-small-regular text-by-neutral-60">
        <PhStar class="suggested-dataset-card__category__img text-by-neutral-60" />
        <span class="ml-2">Empfehlung</span>
      </div>
    </slot>
    <slot name="body">
      <div class="suggested-dataset-card__body-footer">
        <div class="suggested-dataset-card__body">
          <div class="suggested-dataset-card__headline">
            <h4 class="suggested-dataset-card__title by-heading-4">{{ title }}</h4>
            <p class="suggested-dataset-card__subtitle by-copy-small-semibold">{{ catalog }}</p>
          </div>
          <p class="by-copy-small-regular suggested-dataset-card__body__description">{{ description }}</p>
          </div>
          <div class="suggested-dataset-card__body__chips">
            <div
              v-for="(format, i) in formats"
              :key="i"
              class="by-chip by-chip-static"
              :class="{
                'by-chip-dark': dark
              }"
            >
            {{ format }}
          </div>
          </div>
        <div class="suggested-dataset-card__footer">
          <div
            :class="{
              'by-btn-tertiary-medium-light': !dark,
              'by-btn-tertiary-medium-dark': dark
            }"
          >
            Zum Datensatz
            <PhCaretRight />
          </div>
        </div>
      </div>
    </slot>
  </RouterLink>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import PhCaretRight from '~icons/ph/caret-right';
import PhStar from '~icons/ph/star';

export default defineComponent({
  props: {
    id: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    catalog: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    formats: {
      type: Array,
      default() {
        return [];
      },
    },
    dark: {
      type: Boolean,
      default: false,
    }
  },
  components: {
    PhCaretRight,
    PhStar,
  },
});
</script>

<style scoped lang="scss">
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';
@import '../styles/custom_theme.scss';

.suggested-dataset-card {
  display: flex;
  min-height: 520px;
  height: 520px;
  max-height: 520px;
  min-width: 260px;
  padding: 32px;
  flex-direction: column;
  align-items: flex-start;
  gap: 64px;
  flex: 1 0 0;

  text-decoration: none;

  border-radius: var(--Border-Radius, 8px);
  border-bottom: 1px solid var(--Neutral-10, #F1F1F3);
  background: var(--Neutral-0, #FFF);

  &__headline {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__title {
    width: 100%;
    margin: 0;
    display: -webkit-box;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    overflow: hidden;
    color: $by-blue-100;

    word-break: break-word;
    word-wrap: break-word;
  }

  &__subtitle {
    color: $by-neutral-60;
  }

  &__body__description {
    color: $by-neutral-60;
  }

  &__category {
    &__img {
      width: var(--Spacer-Custom-1, 32px);
      height: var(--Spacer-Custom-1, 32px);
    }
  }

  &__body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-start;
    flex: 1 0 0;
    align-self: stretch;

    &__description {
      display: -webkit-box;
      max-width: 100%;
      -webkit-line-clamp: 5;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    &__chips {
      display: flex;
      align-items: flex-start;
      align-content: flex-start;
      gap: 8px;
      align-self: stretch;
      flex-wrap: wrap;
      // Ensure that the chips don't take more than two rows
      max-height: 4.5rem; // === 2 rows of chips
      overflow: hidden;
    }
  }

  &__footer {
    div {
      display: flex;
      padding: 4px 16px;
      align-items: center;
      gap: 8px;
      color: $by-blue-80;
    }
  }

  &__body-footer {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--Spacer-Custom-1, 32px);
    flex: 1 0 0;
    align-self: stretch;
  }

  .to-dataset {
    border: 0 !important;
    border-radius: 24px !important;
    padding: 7px 20px 7px 20px !important;
  }

  &:hover {
    .suggested-dataset-card__title {
      color: $by-blue-60!important;
    }
  }

  .to-dataset:hover {
    background-color: #D4EDFC !important;
    color: #0172AD !important;
    -webkit-text-decoration: none !important;
    text-decoration: none !important;
  }

  &--dark {
    background: rgba(243, 251, 255, 0.06);
    border: none;

    .suggested-dataset-card {
      &__title {
        color: $by-blue-10;
      }

      &__subtitle {
        color: $by-neutral-30;
      }

      &__body__description {
        color: $by-neutral-30;
      }
    }

    .to-dataset {
      color: $by-blue-40;

      &:hover {
        background-color: $by-blue-90 !important;
        color: $by-blue-30 !important;
      }
    }

    &:hover {
    .suggested-dataset-card__title {
        color: $by-blue-30!important;
      }
    }
  }
}
</style>
