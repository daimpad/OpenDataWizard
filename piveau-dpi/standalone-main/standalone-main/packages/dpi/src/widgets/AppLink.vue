<!-- This component should be used for every link that is present in our application (internal & external)  -->

/**
Vue router does not support external links yet and will only append a url to its current root path.
You can/should replace the usage of this component when Vue Router supports external links by itself.
13.06.18
**/

<template>
  <a @click="trackLink" @mousedown.middle="trackLink" :download="download" :href="url" :rel="rel" :target="target" :type="type" v-if="isExternal">
    <slot></slot>
  </a>
  <router-link :to="to" :rel="rel" v-else :exact="exact">
    <slot></slot>
  </router-link>
</template>

<script>
  export default {
    props: {
      to: {
        default: '',
        required: false,
      },
      path: {
        default: '',
        required: false,
      },
      query: {
        default: '',
        required: false,
      },
      fragment: {
        default: '',
        required: false,
      },
      type: {
        default: null,
        required: false,
      },
      target: {
        default: '_self',
        required: false,
      },
      exact: {
        default: false,
        required: false,
      },
      rel: {
        default: 'noopener',
        required: false,
      },
      download: {
        default: null,
        required: false,
      },
      // Use this if you want to track a link explicitly as download instead of an external link.
      matomoTrackDownload: [Boolean, Object],
      // Use this to manually report clicked links to Matomo
      // Useful for URL destinations, that are on the same domain but not inside this application, so that Matomo is unable to track page views (e.g. RSS feed).
      matomoTrackPageView: [Boolean, Object],
    },
    computed: {
      readPath: {
        get() {
          return (this.path && typeof this.path === 'string') ? this.path : '';
        },
      },
      readQuery: {
        get() {
          if (!this.query) return '';
          if (typeof this.query === 'object') return `?${Object.keys(this.query).map(key => `${key}=${this.query[key]}`).join('&')}`;
          if (typeof this.query === 'string') return /^\?([a-zA-Z]+[=].*)([&][a-zA-Z]+[=].*)*$/.test(this.query) ? this.query : '';
          return '';
        },
      },
      readFragment: {
        get() {
          return (this.fragment && typeof this.fragment === 'string') ? `${this.fragment}` : '';
        },
      },
      isExternal: {
        get() {
          return !(typeof this.to === 'object') && (/^(http(s)?|ftp|mailto|tel):/.test(this.to) || /^\//.test(this.readPath) || /^\?/.test(this.readQuery) || /^#/.test(this.readFragment));
        },
      },
      url: {
        get() {
          return `${this.to}${this.readPath}${this.readQuery}${this.readFragment}`;
        },
      },
    },
    methods: {
      trackLink() {
        /* eslint-disable */
        if (this.matomoTrackPageView) {
          this.$piwik.trackPageView(`${window.location.origin}${this.path}`, '', {});
          /* eslint-disable */
         return;
        }
        // Do not track links as external when navigating within the same domain.
        if (!this.isExternal) return;
        if (this.to === '') return;

        if (this.matomoTrackDownload) {
          // window._paq.push(['trackLink', this.url, 'download']);
          this.$piwik.trackDownload(
            this.url,
            typeof this.matomoTrackDownload === 'object'
              ? this.matomoTrackDownload
              : undefined,
          );
        } else {
          // window._paq.push(['trackLink', this.url, 'link']);
          this.$piwik.trackOutlink(
            this.url,
            typeof this.matomoTrackPageView === 'object'
              ? this.matomoTrackPageView
              : undefined,
          );
        }
        // Create custom click event
        this.$emit('after-click');
      },
    },
  };
</script>

<style lang="scss" scoped>
</style>
