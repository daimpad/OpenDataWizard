<template>
  <div class="text-break">
    <span class="font-weight-bold"
          :title="tooltip"
          data-toggle="tooltip"
          data-placement="top">
          {{ property }}
    </span>
    <!-- <dataset-date
      v-if="isDate"
      :date="value"
    /> -->
    <div v-if="isDate">
      {{ formattedDate }}
    </div>
    <span v-else>
      {{ value }}
    </span>
  </div>
</template>

<script>
import { DatasetDate } from "@piveau/piveau-hub-ui-modules";
import { formatDatetime } from '../../utils/utils';
export default {
  name: "PropertyValue",
  components: {DatasetDate},
  props: {
    property: String,
    value: [String, Number],
    tooltip: String,
    isDate: Boolean
  },
  computed: {
    formattedDate() {
      // Example: 30.01.2024
      return formatDatetime(this.value) || 'Unbekannt'
    }
  }
}
</script>

<style scoped>

</style>
