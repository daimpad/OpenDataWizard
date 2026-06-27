<template>
  <SectionLandingPageBase class="bayern-landing-page__section bayern-landing-page__section--three">
    <template #title>
      Daten bereitstellen
    </template>
    <div class="container-fluid">
      <div class="row publish-data-container">
        <div class="col-lg px-0">
          <div class="open-data-basis">
            <p class="text-by-neutral-5 by-copy-large-regular">
              Offene Daten schaffen die Basis für innovative Produkte und Services. Das open bydata competence center unterstützt bei der Veröffentlichung von Daten mit individueller Beratung und Services.
            </p>
            <a href="https://oc.bydata.de/sharing" class="by-btn-secondary-large-dark">
              <span class="d-lg-none">Mehr Informationen</span>
              <span class="d-none d-lg-inline">Mehr Informationen zur Datenbereitstellung</span>
            </a>
          </div>
        </div>
        <div class="col-lg hand-container px-0"><img src="/static/provide-illu.svg" alt="" class="img-hand"></div>
      </div>
      <div class="row">
        <div class="col px-0">
          <div class="d-flex align-items-center flex-wrap flex-lg-nowrap">
            <p class="by-copy-large-regular text-by-neutral-5 mb-0 mr-3">Eine Vielzahl verschiedener Partner ist schon dabei:</p>
            <a href="/partners" class="by-link-dark by-copy-large-semibold">Zu unseren Partnern</a>
          </div>
        </div>
      </div>
    </div>
  </SectionLandingPageBase>
</template>

<script>
import { defineComponent } from 'vue';
import SectionLandingPageBase from './SectionLandingPageBase.vue';

export default defineComponent({
  components: {
    SectionLandingPageBase
  }
});

</script>

<style lang="scss" scoped>

.bayern-landing-page__section--three {
  .open-data-basis {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
  }

  .publish-data-container {
    gap: 1rem;
    margin-bottom: 1rem;
    align-items: center;
  }

  .hand-container {
    display: grid;
    place-items: center;
    position: relative;
    width: 100%;
    height: 15rem;

    .img-hand {
      position: absolute;
      width: 534.055px;
      height: 448px;
      flex-shrink: 0;
    }
  }

  .link-partners {
    display: flex;
    align-items: center;
    gap: 0.5rem;

    &::after {
      content: '';
      width: 24px;
      height: 24px;
      background: url("../../assets/img/caret-circle-right.svg") no-repeat;
    }
  }

}
</style>
