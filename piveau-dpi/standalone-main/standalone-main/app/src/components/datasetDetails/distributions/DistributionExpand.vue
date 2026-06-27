<template>
  <Accordion v-if="distributionCanShowMore(distribution)"
    :theme="'light'"
    :toggleAccordion="toggleAccordion"
    :isAccordionToggled="isAccordionToggled"
  />
</template>

<script>
  import Accordion from '@/components/Accordion.vue';
  export default {
    name: "DistributionExpand",
    components: { Accordion },
    props: [
        'distribution',
        'distributionCanShowMore',
        'toggleDistribution',
        'distributionIsExpanded'
    ],
    methods: {
      toggleAccordion() {
        return this.toggleDistribution(this.distribution.id);
      },
      isAccordionToggled() {
        return this.distributionIsExpanded(this.distribution.id);
      }
    }
  }
</script>

<style scoped>
</style>

