<template>
  <div class="fancy-card">
    <div class="fancy-card__header">
      <slot name="icon">
        <div class="fancy-card__icon"></div>
      </slot>
    </div>
    <div class="fancy-card__body">
      <slot name="body">
        <h4 class="fancy-card__title">
          <slot name="title">Default Title</slot>
        </h4>
        <div class="fancy-card__subtitle">
          <slot name="subtitle">Default Subtitle</slot>
        </div>
        <div class="fancy-card__desc">
          <slot></slot>
        </div>
      </slot>
    </div>
    <div class="fancy-card__footer">
      <slot name="footer">Default Footer Content</slot>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  setup() {
    return {}
  }
})
</script>

<style lang="scss" scoped>
.fancy-card {
  display: flex;
  flex-direction: column;
  height: 568px;
  min-width: 288px;
  max-width: 384px;
  width: 100%;
  flex: 1 0 0;
  padding: 2rem;
  background: linear-gradient(180deg, rgba(14, 46, 70, 1) 0%, transparent 100%);

  &__header,
  &__footer,
  &__title,
  &__subtitle {
    flex-shrink: 0;
    flex-grow: 0;
  }

  &__header {
    display: grid;
    place-items: center;
    width: 100%;
    margin: 2rem 0;
  }

  &__icon {
    width: 4rem;
    height: 4rem;
    background-color: yellow;
    border-radius: 50%;
  }

  &__body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    margin-top: 2rem;
  }

  &__title {
    margin-bottom: .5rem;
  }

  &__subtitle {
    margin-bottom: 2rem;
  }

  &__desc {
    flex-grow: 1;
  }

  &__footer {
    display: grid;
    place-items: end;
    width: 100%;
  }
}
</style>
