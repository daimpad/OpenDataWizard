<template>
  <div id="odp-bayern" class="app">
    <div class="app-snackbar position-fixed fixed-bottom m-3 m-md-5 py-5 d-flex justify-content-center w-100">
      <app-snackbar />
    </div>
    <vue-progress-bar />
    <div class="site-wrapper">
      <!-- enable-authentication -> false -> remove login button -->
      <piveau-header
        :show-catalogues="true"
        :show-metadata-quality="false"
        :use-language-selector="false"
        :enable-authentication="authEnabled"
        :authenticated="isAuthenticated"
        :nav-items-hook="menuItems"
        @login="login"
        @logout="logout"
      >
        <template #logo>
          <router-link v-if="catalogId" class="odp-logo" :to="{ name: 'CatalogueNDetails' }">
            <div>{{ catalogTitleLogoCased }}</div>
            <div class="odp-logo--bydata">bydata</div>
          </router-link>
          <a v-else href="https://open.bydata.de"><img src="/static/bydata_logo_wordmark_medium.svg" alt="open.bydata" class="odb-logo img-fluid"></a>
        </template>

        <template v-if="catalogId" #nav-item="{ navItem }">
          <router-link
            v-if="navItem.to && navItem.show === true"
            :to="navItem.href"
            class="nav-link"
            active-class="active"
            exact-path
          >
            {{ navItem.title }}
          </router-link>
          <a
            v-else-if="navItem.show === true"
            :href="navItem.href"
            :class="{ active: $route.path.includes(navItem.path) || navItem.path.includes($route.path)}"
            class="nav-link"
          >
            {{ navItem.title }}
          </a>
        </template>
      </piveau-header>

      <keep-alive include="BayernLandingPage,OdpLandingPage">
        <!-- <router-view
            class="content"
            :key="computedRouteKey"
        /> -->
      </keep-alive>

      <section v-if="catalogId" class="odp-ref-container">
        <div class="odp-ref-content">
          <span>{{ catalogTitleLogoCased }}.bydata ist Teil von</span>
          <span>
            <img src="/static/bydata_logo_wordmark_medium.svg" alt="">
          </span>
          <a href="https://open.bydata.de/datasets" class="by-link-light d-flex flex-row" target="_blank">
            <span>Alle offenen Daten Bayerns entdecken</span>
            <PhArrowRight class="ml-2" />
          </a>
        </div>
      </section>

      <piveau-footer
        :enable-authentication="authEnabled"
        :authenticated="isAuthenticated"
        @login="login"
        @logout="logout"
      >
      </piveau-footer>
      <!-- <div class="bg-primary text-light p-3">
        Put a footer here. Made with <span class="text-danger">♥</span> by <a href="https://www.piveau.eu" class="text-light">piveau</a>.
      </div> -->
    </div>
    <dpi-menu v-if="isAuthenticated"></dpi-menu>
  </div>

</template>

<script>
/* eslint-disable no-underscore-dangle */
import { computed, nextTick, ref } from 'vue';
import { mapGetters, mapActions } from 'vuex';
import { isNumber } from 'lodash';
import { getSubdomainCatalogIdFromUrl, patchAxiosDatasetSearchWithOpenDataPresenceFacet } from './utils/utils';
import axios from 'axios';
import PhArrowRight from '~icons/ph/arrow-right';


// import CookieConsent from '@deu/deu-cookie-consent';
import '@deu/deu-cookie-consent/dist/deu-cookie-consent.css';
import {
  DpiMenu,
  usePiwikSuspendFilter,
} from '@piveau/piveau-hub-ui-modules';
import { useCatalogId } from './composables/useCatalogId';
import { useRuntimeEnv } from './composables/useRuntimeEnv';
import { useRoute } from 'vue-router';

export default {
  name: 'app',
  components: {
    // CookieConsent,
    DpiMenu,
    PhArrowRight,
  },
  mixins: [
    usePiwikSuspendFilter,
  ],
  metaInfo() {
    return {
      titleTemplate: (chunk) => {
        if (this.$route.name === 'LandingPage') {
          return 'open.bydata - Das Open-Data-Portal für Bayern';
        }

        const hasCatalogId = Boolean(getSubdomainCatalogIdFromUrl(window.location.href));
        const titleChunk = chunk ? `${chunk} - ${this.baseTitle}` : this.baseTitle;

        return hasCatalogId ? this.baseTitle : titleChunk;
      },
      meta: [
        { name: 'description', vmid: 'description', content: 'Open Data aus Bayern: Kostenlose, offene Daten für innovative Projekte - und Unterstützung, um noch mehr Daten zu teilen.' },
        { name: 'keywords', vmid: 'keywords', content: this.$env.metadata.keywords },
        ...this.metaPropertiesOg,
        ...this.metaPropertiesTwitter,
      ],
      htmlAttrs: {
        lang: this.$route.query.locale,
      },
    };
  },
  data() {
    return {
      tracker: null,
      matomoURL: this.$env.tracker.trackerUrl,
      piwikId: this.$env.tracker.siteId,
      lastRoute: null,
      keycloak: this.$keycloak,
      showSparql: this.$env.routing.navigation.showSparql,
      title: '',
      metaDescriptionContent: '',
    };
  },
  computed: {
    ...mapGetters('auth', [
      'securityAuth',
      'getRTPToken',
      'getKeycloak',
    ]),
    authEnabled() {
      return this.$env?.authentication?.useService && this.$env.authentication?.login?.useLogin;
    },
    isAuthenticated() {
      return this.authEnabled && this.keycloak?.authenticated;
    },

    baseTitle() {

      let maybeCatalogId = getSubdomainCatalogIdFromUrl(window.location.href);
      let capitalizedCatalogId ="";
      // if the catalogue is a Landkreis the id starts with lk-, e.g. lk-regensburg
      // we do not want Lk-regensburg in the title, but Lk Regensburg
      if(maybeCatalogId?.startsWith("lk-")){
        maybeCatalogId = maybeCatalogId?.slice(3);
        capitalizedCatalogId = "Lk ";
      }
      const parts = maybeCatalogId?.split("-")
      for (let i = 0; i < parts.length; i++) {
        if(i >0){ capitalizedCatalogId += "-"; }
        capitalizedCatalogId += parts[i].charAt(0).toUpperCase() + parts[i].slice(1);
      }
      return maybeCatalogId
        ? `${capitalizedCatalogId}.bydata`
        : this.$env.metadata.title;
    },

    // Some computed properties to generate social media meta tags.
    metaDescription() {
      return this.$metaInfo.meta.find(meta => meta.vmid === 'description')?.content;
    },

    metaPropertiesOg() {
      return [
        { property: 'og:title', vmid: 'og:title', content: this.title },
        { property: 'og:description', vmid: 'og:description', content: this.metaDescriptionContent },
        { property: 'og:url', vmid: 'og:url', content: window.location.href },
      ]
    },
    metaPropertiesTwitter() {
      return [
        { name: 'twitter:title', vmid: 'twitter:title', content: this.title },
        { name: 'twitter:description', vmid: 'twitter:description', content: this.metaDescriptionContent },
      ]
    },
  },
  watch: {
    // Basically makes this.metaDescriptionContent a computed property of the
    // current meta description but prevents infinite recursion by only doing
    // things when the actual content has changed
    metaDescription: {
      immediate: true,
      handler(newVal, oldVal) {
        if (newVal !== oldVal) {
          this.metaDescriptionContent = newVal;
        }
      }
    }
  },
  methods: {
    ...mapActions('auth', [
      'authLogin',
      'authLogout',
      'rtpToken',
      'setKeycloak',
    ]),
    resume() {
      if (typeof this.$piwik?.resume === "function") this.$piwik.resume();
    },
    isNumber,
    login() {
      this.$keycloak.loginFn();
    },
    logout() {
      this.$keycloak.logoutFn();
    },
    handleFollowClick(url) {
      if (typeof this.$piwik?.resume === "function") this.$piwik.trackOutlink(url);
    },
    menuItems(items) {
      return this.catalogId
        ? [
          // Replace nav items with OC items
          // see https://gitlab.fokus.fraunhofer.de/open-data-bayern/utilities/piveau-header-footer/-/blob/master/src/components/Header.vue?ref_type=heads
          // todo: align with actual routes
          // todo: ensure route-active class works properly in dev (http://localhost:8080/oc/sharing...) and prod (oc.bydata.de/sharing)
          {
            title: 'Startseite',
            to: true,
            href: { name: 'CatalogueNDetails' },
            path: ['/cataloguesn'],
            show: true,
          },
          {
            title: 'Alle Datensätze',
            to: true,
            href: { name: 'CatalogueNDetailsDatasetsSearch' },
            path: ['/oc/connecting'],
            show: true,
          },
          {
            title: 'Über diese Präsenz',
            to: true,
            href: { name: 'CatalogueNDetailsPresence' },
            path: ['/oc/using'],
            show: true,
          },
        ]
        : items;
    },
  },
  setup() {
    const env = useRuntimeEnv();
    const route = useRoute();
    const catalogId = useCatalogId();
    // augsburg -> Augsburg
    // augsburg-land -> Augsburg-Land
    const catalogTitleLogoCased = computed(() => {
      return catalogId.value.split('-').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join('-')
    });

    const nearestRouteCatalogMeta = computed(() => route.matched.slice().reverse().find((r) => r.meta?.key)?.meta.key || '');
    // const routerKey = computed(() => route.matched.reverse().find((r) => r.meta?.key)?.meta.key || route.fullPath);
    const routerKey = computed(() => nearestRouteCatalogMeta.value || route.fullPath);

    // Patch axios instance to add openDataPresence facet to all dataset search requests
    patchAxiosDatasetSearchWithOpenDataPresenceFacet({ axiosInstance: axios, openDataPresence: `${catalogId.value}.bydata` ,  hubSearchApiBaseUrl: env.api.baseUrl });

    const computedRouteKey = computed(() => {
      return `${route.fullPath}`
    })

    return {
      catalogId,
      catalogTitleLogoCased,
      computedRouteKey,
    }
  }
};
</script>

<style lang="scss">
// Normalizes default css rules. See: https://github.com/necolas/normalize.css
@import './styles/utils/normalize.css';

// @font-face {
//   font-family: "Ubuntu";
//   src: local("Ubuntu"), url(../public/static/fonts/Ubuntu/Ubuntu-Regular.ttf) format("truetype");
// }

// Hides 'editorial content' tab.
// todo: remove this when it is configurable in modules.
#myTab > div.d-flex.cursor-pointer > li:nth-child(3) > a {
  display: none;
}

* {
  box-sizing: border-box;
}

.site-wrapper header {
  display: initial;
}

.container-fluid {
  max-width: 1340px !important;
}
</style>

<style lang="scss" scoped>

.app {
  background-color: transparent;
  max-width: 100%;
  overflow-x: clip;
}

.site-wrapper {
  border: 0;
  max-width: none;
  box-shadow: none;
  display: initial;

  .content {
    padding: 30px 30px 0 30px;
    margin-top: 15px;
    margin-bottom: 15px;
    background-color: transparent;
  }
}

.app-snackbar {
  z-index: 9999;
  pointer-events: none;
}

.odp-logo {
  padding-right: .5rem;
  color: var(--blue-primary-blue-70, #0196D8);
  font-family: "Space Grotesk Variable";
  font-size: 20px;
  font-style: normal;
  font-weight: 700;
  line-height: 1.25;

  text-decoration: none;

  &--bydata {
    color: var(--blue-primary-blue-100-secondary, #003F6F);
    font-family: "Space Grotesk Variable";
    font-size: 20px;
    font-style: normal;
    font-weight: 700;
    line-height: 1.25
  }
}

.odp-ref-container {
  display: grid;
  padding-top: 2rem;
  padding-bottom: 2rem;
  background-color: #FFFFFF;
  width: 100%;
  place-items: center;
  padding-left: 2rem;
  padding-right: 2rem;
  overflow: hidden;

  .odp-ref-content {
    display: flex;
    max-width: 1280px;
    width: 100%;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    align-self: stretch;
  }
}
</style>
