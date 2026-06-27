<template>
  <section class="d-flex flex-col justify-content-center align-items-center w-100">
    <div class="bayern-landing-page-section-content">
      <div class="w-100">
        <component :is="big ? 'h1' : 'h2'"
          :class="computedHeadingClass"
        >
          <slot name="title">
            title
          </slot>
        </component>
      </div>
      <slot>
        content
      </slot>
    </div>
  </section>
</template>

<script lang="ts">
import { computed, defineComponent } from 'vue';

export default defineComponent({
  props: {
    small: {
      type: Boolean,
      default: false,
    },
    big: {
      type: Boolean,
      default: false,
    },
    odp: {
      type: Boolean,
      default: false,
    }
  },
  setup(props) {
    // const computedHeadingClass = computed(() => {
    //   if (props.big)
    //     return 'by-heading-1';

    //   if (props.small)
    //     return 'by-heading-4 text-center';

    //   return 'by-heading-2 text-center';
    // })

    // return { computedHeadingClass }

    const computedHeadingClass = computed(() => ([
      {
        'by-heading-1': props.big && !props.odp,
        'by-heading-2': props.big && props.odp,
        'by-heading-4 text-center': props.small	,
        'by-heading-2 text-center': !props.big && !props.small,
        'text-by-blue-10': !props.odp
      }
    ]))

    return { computedHeadingClass }
  }
})
</script>

<style lang="scss" scoped>
.bayern-landing-page-section-content {
  display: flex;
  max-width: 1280px;
  width: 100%;
  flex-direction: column;
  align-items: flex-start;
  align-self: stretch;

  .by-heading-1 {
    margin-bottom: 2rem;
  }

  .by-heading-2, .by-heading-4 {
    margin-bottom: 64px;
  }
}
</style>
