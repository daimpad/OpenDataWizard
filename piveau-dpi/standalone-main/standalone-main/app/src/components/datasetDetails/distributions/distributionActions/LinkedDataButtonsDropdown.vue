<template>
  <dropdown
            :title="$t('message.tooltip.datasetDetails.distributions.linkedData')"
            :message="$t('message.metadata.linkedData')"
  >
    <resource-details-linked-data-button
      v-for="format in formats"
      class="by-dropdown-item bglight"
      resources="distributions"
      :key="format.id"
      :format="format.id"
      :text="format.label"
      :resources-id="distribution.id" />
  </dropdown>
</template>

<script>
import { ResourceDetailsLinkedDataButton } from "@piveau/piveau-hub-ui-modules";
import Dropdown from "@/components/widgets/Dropdown.vue";

export default {
  name: "LinkedDataButtonsDropdown",
  components: {
    Dropdown,
    ResourceDetailsLinkedDataButton
  },
  props: [
    "distributions",
    "distribution"
  ],
  data: () => ({
    formats:
      [
        {id: "rdf", label: "RDF/XML"},
        {id: "ttl", label: "Turtle"},
        {id: "n3", label: "Notation3"},
        {id: "nt", label: "N-Triples"},
        {id: "jsonld", label: "JSON-LD"}
      ]
  })
}
</script>

<style scoped>

</style>
