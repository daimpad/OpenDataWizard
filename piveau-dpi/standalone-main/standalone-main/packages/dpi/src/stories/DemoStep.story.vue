<script setup lang="ts">
import Chip from '../data-provider-interface/HappyFlowComponents/ui/Chip.vue'
import InputField from '../data-provider-interface/HappyFlowComponents/ui/InputField.vue'
</script>

<template>
  <Story title="Step Components/DemoStep" icon="carbon:tag">
    <div class="dpiV3_page V3-typography">
      <section class="funny-section">
        <h4>
          Fügen Sie optionale Daten hinzu
        </h4>
        <span class="copy-large-regular">Für viele Projekte sind weitere Informationen vorhanden und für lesende
          Personen hilfreich. Fügen Sie hier bei Bedarf weitere Informationen hinzu.</span>
      </section>
      <section class="funny-section">
        <h5>
          Partner
        </h5>
        <InputField
          label="Input with Info Icon" placeholder="Enter text here"
          tooltip-text="This is helpful information" :add-on-text="false" :date-picker="false" :info-icon="true"
        />
        <InputField
          label="Input with Info Icon" placeholder="Enter text here"
          tooltip-text="This is helpful information" :add-on-text="false" :date-picker="false" :info-icon="true"
        />
        <span class="copy-large-regular">Wenn Sie mit einem oder mehreren Partnern zusammengearbeitet haben, können Sie
          diese hier angeben.</span>
      </section>
      <div class="dpiV3_Card_Tips">
        hello world
      </div>
    </div>
  </Story>
</template>

<style>
:root {
  /* Typography - Font Sizes, Weights, and Line Heights */
  --font-family-primary: "Space Grotesk", Arial, Helvetica, sans-serif;
  --font-family-secondary: "Inter", Arial, Helvetica, sans-serif;

  /* Headlines */
  --headline-1-font-size: 4.5rem;
  /* 72px */
  --headline-1-line-height: 4.75rem;
  /* 76px */
  --headline-1-font-weight: 700;
  /* Bold */

  --headline-2-font-size: 3rem;
  /* 48px */
  --headline-2-line-height: 3.5rem;
  /* 56px */
  --headline-2-font-weight: 700;
  /* Bold */

  --headline-3-font-size: 2.5rem;
  /* 40px */
  --headline-3-line-height: 3rem;
  /* 48px */
  --headline-3-font-weight: 700;
  /* Bold */

  --headline-4-font-size: 1.5rem;
  /* 24px */
  --headline-4-line-height: 2rem;
  /* 36px */
  --headline-4-font-weight: 700;
  /* Bold */

  --headline-5-font-size: 1.25rem;
  /* 20px */
  --headline-5-line-height: 1.75rem;
  /* 28px */
  --headline-5-font-weight: 600;
  /* Semi-Bold */

  /* Caption */
  --caption-font-size: 0.75rem;
  /* 12px */
  --caption-line-height: 1.125rem;
  /* 18px */
  --caption-font-weight: 700;
  /* Bold */

  /* General Settings */
  --text-default-font-family: var(--font-family-secondary);
  --text-default-font-weight: 400;
  /* Regular */

  /* Font Sizes, Weights, and Line Heights for Text Copy */
  --copy-large-regular-font-size: 1rem;
  /* 16px */
  --copy-large-regular-line-height: 1.625rem;
  /* 26px */
  --copy-large-regular-font-weight: 400;
  /* Regular */

  --copy-large-semi-bold-font-size: 1rem;
  /* 16px */
  --copy-large-semi-bold-line-height: 1.625rem;
  /* 26px */
  --copy-large-semi-bold-font-weight: 600;
  /* Semi-Bold */

  --copy-small-regular-font-size: 0.9375rem;
  /* 15px */
  --copy-small-regular-line-height: 1.5rem;
  /* 24px */
  --copy-small-regular-font-weight: 400;
  /* Regular */

  --copy-small-semi-bold-font-size: 0.9375rem;
  /* 15px */
  --copy-small-semi-bold-line-height: 1.5rem;
  /* 24px */
  --copy-small-semi-bold-font-weight: 600;
  /* Semi-Bold */

  --copy-mini-semi-bold-font-size: 12px;
  --copy-mini-semi-bold-line-height: 16px;
  --copy-mini-semi-bold-font-weight: 600;
  /* Semi-Bold */

  --copy-mini-regular-font-size: 0.75rem;
  /* 12px */
  --copy-mini-regular-line-height: 1rem;
  /* 16px */
  --copy-mini-regular-font-weight: 400;
  /* Regular */

  --copy-button-ml-font-size: 1rem;
  /* 16px */
  --copy-button-ml-line-height: 2rem;
  /* 32px */
  --copy-button-ml-font-weight: 600;
  /* Semi-Bold */

  --copy-button-s-font-size: 0.875rem;
  /* 14px */
  --copy-button-s-line-height: 1rem;
  /* 16px */
  --copy-button-s-font-weight: 600;
  /* Semi-Bold */

  /* Hyperlink Colors */
  --link-color-focused: --blue-80;
  --link-text-decoration-focused: underline;

  /************** Mobile Screens *************/
  /* Mobile Headlines */
  --mobile-headline-1-font-size: 3.5rem;
  /* 56px */
  --mobile-headline-1-line-height: 3.5rem;
  /* 56px */
  --mobile-headline-1-font-weight: 700;
  /* Bold */

  --mobile-headline-2-font-size: 2rem;
  /* 32px */
  --mobile-headline-2-line-height: 2.5rem;
  /* 40px */
  --mobile-headline-2-font-weight: 700;
  /* Bold */

  --mobile-headline-3-font-size: 1.75rem;
  /* 28px */
  --mobile-headline-3-line-height: 2.375rem;
  /* 38px */
  --mobile-headline-3-font-weight: 700;
  /* Bold */

  --mobile-headline-4-font-size: 1.375rem;
  /* 22px */
  --mobile-headline-4-line-height: 2rem;
  /* 32px */
  --mobile-headline-4-font-weight: 700;
}
</style>

<style scoped lang="scss">
.funny-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.dpiV3_page {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  flex: 1 0 0;
  gap: 64px;
  align-self: stretch;
  padding: 32px 48px;

  border: 1px dotted var(--neutral-30, #D5D7DA);
}

.dpiV3_Card_Tips {
  display: flex;
  // min-width: 416px;
  // max-width: 600px;
  padding: var(--Spacing-5, 32px);
  flex-direction: column;
  align-items: flex-start;
  gap: var(--Spacing-5, 32px);
  align-self: stretch;
  border-radius: var(--Modal-Radius, 32px);
  background: var(--blue-10, #f3fbff);
}

/****************************************************/
/* Typography Tokens and Component-Specific Styles */
/****************************************************/
.V3-typography {
  h1 {
    font-family: var(--font-family-primary);
    font-size: var(--headline-1-font-size);
    line-height: var(--headline-1-line-height);
    font-weight: var(--headline-1-font-weight);
  }

  h2 {
    font-family: var(--font-family-primary);
    font-size: var(--headline-2-font-size);
    line-height: var(--headline-2-line-height);
    font-weight: var(--headline-2-font-weight);
  }

  h3 {
    font-family: var(--font-family-secondary);
    font-size: var(--headline-3-font-size);
    line-height: var(--headline-3-line-height);
    font-weight: var(--headline-3-font-weight);
  }

  h4 {
    font-family: var(--font-family-secondary);
    font-size: var(--headline-4-font-size);
    line-height: var(--headline-4-line-height);
    font-weight: var(--headline-4-font-weight);
    margin-bottom: 0px;
    color: var(--neutral-80, #3D4952);
  }

  h5 {
    font-family: var(--font-family-secondary);
    font-size: var(--headline-5-font-size);
    line-height: var(--headline-5-line-height);
    font-weight: var(--headline-5-font-weight);
  }

  .copy-large-regular {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-large-regular-font-size);
    line-height: var(--copy-large-regular-line-height);
    font-weight: var(--copy-large-regular-font-weight);
    color: var(--neutral-80, #3D4952);
  }

  .copy-large-semi-bold {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-large-semi-bold-font-size);
    line-height: var(--copy-large-semi-bold-line-height);
    font-weight: var(--copy-large-semi-bold-font-weight);
  }

  .copy-small-regular {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-small-regular-font-size);
    line-height: var(--copy-small-regular-line-height);
    font-weight: var(--copy-small-regular-font-weight);
  }

  .copy-small-semi-bold {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-small-semi-bold-font-size);
    line-height: var(--copy-small-semi-bold-line-height);
    font-weight: var(--copy-small-semi-bold-font-weight);
  }

  .copy-mini-regular {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-mini-regular-font-size);
    line-height: var(--copy-mini-regular-line-height);
    font-weight: var(--copy-mini-regular-font-weight);
  }

  .copy-mini-semi-bold {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-mini-regular-font-size);
    font-style: normal;
    font-weight: var(--copy-button-s-font-weight);
    line-height: var(--copy-button-s-line-height);
  }

  .copy-button-ml {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-button-ml-font-size);
    line-height: var(--copy-button-ml-line-height);
    font-weight: var(--copy-button-ml-font-weight);
  }

  .copy-button-s {
    font-family: var(--font-family-secondary);
    font-size: var(--copy-button-s-font-size);
    line-height: var(--copy-button-s-line-height);
    font-weight: var(--copy-button-s-font-weight);
  }
}
</style>
