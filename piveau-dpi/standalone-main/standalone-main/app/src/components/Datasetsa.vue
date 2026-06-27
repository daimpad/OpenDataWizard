<template>
  <div>
    I am datasetsa
  </div>
</template>

<script>
import { defineComponent } from 'vue';


export default defineComponent({
  metaInfo() {
    return {
      meta: [
        {
          name: 'aaa', vmid: 'baaa', content: 'badumtss',
        }
      ]
    }
  }
})

</script>