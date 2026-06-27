<template>
  <div class="position-relative d-inline-block ml-1 mb-1">
    <div>
        <!-- <button @click="openDropdown" class="btn btn-sm btn-primary p-0 pl-2 w-100 rounded-lg btn-color dropdown-button d-flex justify-content-between"
          type="button"
          aria-haspopup="true"
          aria-expanded="false">
          <span data-toggle="tooltip"
            :title="title"
            data-placement="top">
          {{ message }}
          </span>
          <i class="material-icons small-icon float-right align-bottom">keyboard_arrow_down</i>
        </button> -->
        <div v-if="open" v-click-away="away" class="dropdownMenu" :class="{ bglight: bgLight }">
          <slot></slot>
        </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable */
import $ from 'jquery';
import { mixin as clickaway } from 'vue3-click-away';
import { helpers, AppLink } from '@piveau/piveau-hub-ui-modules';
// import AppLink from "../../../widgets/AppLink";

export default {
  mixins: [clickaway],
  name: 'DistributionDropdownDownload',
  props: ['distribution', 'title', 'message', 'bgLight', 'isOnlyOneUrl', 'getDownloadUrl'],
  components: {
    AppLink,
  },
  methods: {
    openDropdown() {
      this.open = true;
      setTimeout(() => {
        $('[data-toggle="tooltip"]').tooltip({
          container: 'body',
        });
      }, 500);
    },
    away() {
      this.open = false;
    },
    replaceHttp: helpers.replaceHttp,
    setClipboard(value) {
      const input = document.createElement('INPUT');
      // input.style = "position: absolute; left: -1000px; top: -1000px";
      input.value = value;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
    },
  },
  data() {
    return {
      open: false,
    };
  },
};
</script>
<style lang="scss" scoped>
  .material-icons.small-icon {
    font-size: 20px;
  }
  .dropdownMenu {
    z-index: 1000;
    max-width: 15rem;
    padding: 0.5rem 0;
    margin: 0.125rem 0;
    font-size: 1rem;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.15);
    transform-origin: top right;
    position: absolute;
    right: 0;
  }
  button:hover {
    background-color: #196fd2;
    border-color: #196fd2;
  }
  .bglight {
    background:#f8f9fa;
  }
</style>
