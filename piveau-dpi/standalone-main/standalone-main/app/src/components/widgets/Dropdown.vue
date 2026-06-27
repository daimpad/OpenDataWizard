<template>
  <div class="position-relative d-inline-block ml-1 text-nowrap mb-1">
      <button @click="open=true" class="by-dropdown-button dsd-action-button dropdown-button"
              type="button"
              aria-haspopup="true"
              aria-expanded="false">
        <span data-toggle="tooltip"
          :title="title"
          data-placement="top">
          {{ message }}
        </span>
        <img class="by-dropdown-icon" src="../../assets/icon/icon-caretdown.svg" alt="Icon Caret down"/>
      </button>
    <div v-if="open" v-click-away="away" class="by-dropdown-menu dropdownMenu" :class="{ bglight: bgLight }">
     <slot></slot>
    </div>
  </div>
</template>

<script>
/* eslint-disable */
import { mixin as clickaway } from 'vue3-click-away';

export default {
  mixins: [clickaway],
  name: 'Dropdown',
  props: ['title', 'message', 'bgLight'],
  methods: {
    away() {
      this.open = false;
    },
  },
  data() {
    return {
      open: false,
    };
  },
};
</script>

<style lang="scss">
  // specified in styles/by/_bayern-dropdown.scss
</style>
