<template>
   <div class="d-flex w-100">
          <h2 class="dsd-feature-title by-heading-4">{{ title }}</h2>
    </div>
  </template>
  
  <script>
  export default {
    name: "DatasetDetailsFeatureHeader",
    props: {
      onClick: Function,
      title: String,
      tag: String,
      arrowDown: Boolean
    }
  }
  </script>
  
  <style lang="scss">

  .dsd-feature-title {
    color: var(--bayern);
    line-height: 36px !important;
  }
  </style>
  