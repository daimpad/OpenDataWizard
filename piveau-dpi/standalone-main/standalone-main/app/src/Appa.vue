<template>
  <div id="odp-bayern" class="app">
    <vue-progress-bar />
    <div class="site-wrapper">
      <!-- enable-authentication -> false -> remove login button -->
      <piveau-header
        :show-catalogues="true"
        :show-metadata-quality="false"
        :use-language-selector="false"
        :enable-authentication="authEnabled"
        :authenticated="isAuthenticated"
        disable-init-locale
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

      <router-view
          class="content"
          :key="`${$route.fullPath}`"
      />

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
    <DpiMenu v-if="isAuthenticated"></DpiMenu>
  </div>

</template>

<script>

import { defineComponent, computed, watch, ref } from 'vue';
import { mapGetters, mapActions } from 'vuex';
import { isNumber } from 'lodash';
import { getSubdomainCatalogIdFromUrl } from './utils/utils';
import { useRoute } from 'vue-router';
import { useCatalogId } from './composables/useCatalogId';

import {
  usePiwikSuspendFilter,
  head,
  useRuntimeEnv,
  useRouteMetaBreadcrumbs,
} from '@piveau/piveau-hub-ui-modules';
import {
  DpiMenu
} from '@piveau/dpi';

import { useHead } from '@unhead/vue';
import { useLogoCase } from './composables/useLogoCase';
// import { active } from '@unhead/vue'

export default defineComponent({
  name: 'app',
  components: {
    // CookieConsent,
    DpiMenu,
  },
  mixins: [
    usePiwikSuspendFilter,
  ],
  // metaInfo() {
  //   return {
  //     titleTemplate: (chunk) => {
  //       if (this.$route.name === 'LandingPage') {
  //         return 'open bydata - Das Open-Data-Portal für Bayern';
  //       }

  //       const hasCatalogId = Boolean(getSubdomainCatalogIdFromUrl(window.location.href));
  //       const titleChunk = chunk ? `${chunk} - ${this.baseTitle}` : this.baseTitle;

  //       return hasCatalogId ? this.baseTitle : titleChunk;
  //     },
  //     meta: [
  //       { name: 'description', vmid: 'description', content: 'Open Data aus Bayern: Kostenlose, offene Daten für innovative Projekte - und Unterstützung für die Verwaltung, um noch mehr Daten zu teilen.' },
  //       { name: 'keywords', vmid: 'keywords', content: this.$env.metadata.keywords },
  //       { name: 'minh', vmid: 'minh', content: 'hello world' },
  //       // ...this.metaPropertiesOg,
  //       // ...this.metaPropertiesTwitter,
  //     ],
  //     htmlAttrs: {
  //       lang: this.$route.query.locale,
  //     },
  //   };
  // },
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
      const maybeCatalogId = getSubdomainCatalogIdFromUrl(window.location.href);
      let capitalizedCatalogId ="";
      // if the catalogue is a Landkreis the id starts with lk-, e.g. lk-regensburg
      // we do not want Lk-regensburg in the title, but Lk Regensburg
      if(maybeCatalogId?.startsWith("lk-")){
        const parts = maybeCatalogId?.split("-")
        for (let i = 0; i < parts.length; i++) {
          if(i >0){ capitalizedCatalogId += " "; }
          capitalizedCatalogId += parts[i].charAt(0).toUpperCase() + parts[i].slice(1);
        }
      }else{
        capitalizedCatalogId = maybeCatalogId?.charAt(0).toUpperCase() + maybeCatalogId?.slice(1);
      }
      return maybeCatalogId
        ? `${capitalizedCatalogId} by data`
        : this.$env.metadata.title;
    },
  },
  // watch: {
  //   // Basically makes this.metaDescriptionContent a computed property of the
  //   // current meta description but prevents infinite recursion by only doing
  //   // things when the actual content has changed
  //   metaDescription: {
  //     immediate: true,
  //     handler(newVal, oldVal) {
  //       if (newVal !== oldVal) {
  //         this.metaDescriptionContent = newVal;
  //       }
  //     }
  //   }
  // },
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
    const route = useRoute();
    const env = useRuntimeEnv();

    const { headData } = head.useRootHead(false);
    // const meta = head.useRootHead();
    // meta.




    // Some computed properties to generate social media meta tags.
    const metaDescription = computed(() => {
      return headData.value.meta.find(meta => meta.vmid === 'description')?.content;
    });

    const metaDescriptionContent = ref('');
    watch(metaDescription, (newVal, oldVal) => {
      if (newVal !== oldVal) {
        metaDescriptionContent.value = newVal;
      }
    });

    const metaPropertiesOg = computed(() => {
      return [
        { property: 'og:title', vmid: 'og:title', content: '' },
        { property: 'og:description', vmid: 'og:description', content: metaDescriptionContent },
        { property: 'og:url', vmid: 'og:url', content: window.location.href },
      ]
    });

    const metaPropertiesTwitter = computed(() => {
      return [
        { name: 'twitter:title', vmid: 'twitter:title', content: '' },
        { name: 'twitter:description', vmid: 'twitter:description', content: metaDescriptionContent },
      ]
    });

    const theHead = computed(() => {
      return {
        titleTemplate: (chunk) => {
          if (route.name === 'LandingPage') {
            return 'open bydata - Das Open-Data-Portal für Bayern';
          }

          const hasCatalogId = Boolean(getSubdomainCatalogIdFromUrl(window.location.href));
          const titleChunk = chunk ? `${chunk} - ${env.metadata.title}` : env.metadata.title;

          return hasCatalogId ? env.metadata.title : titleChunk;
        },
        meta: [
          ...headData.value.meta,
          ...metaPropertiesOg.value,
          ...metaPropertiesTwitter.value,
        ],
        htmlAttrs: {
          lang: route.query.locale,
        }
      }
    });

    useHead(theHead);

    const catalogId = useCatalogId();
    // augsburg -> Augsburg
    // augsburg-land -> Augsburg-Land
    const catalogTitleLogoCased = useLogoCase(catalogId)

    const nearestRouteCatalogMeta = computed(() => route.matched.slice().reverse().find((r) => r.meta?.key)?.meta.key || '');
    // const routerKey = computed(() => route.matched.reverse().find((r) => r.meta?.key)?.meta.key || route.fullPath);
    const routerKey = computed(() => nearestRouteCatalogMeta.value || route.fullPath);


    return {
      metaDescription,
      catalogId,
      catalogTitleLogoCased,
      routerKey,
    }


  }
});
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
  width: 160px;
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
</style>
