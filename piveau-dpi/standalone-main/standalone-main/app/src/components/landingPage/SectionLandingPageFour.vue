<template>
  <SectionLandingPageBase small class="bayern-landing-page__section bayern-landing-page__section--four">
    <template #title>
      oc.bydata, das open bydata competence center
    </template>
    <div class="community-strength-container d-flex flex-wrap flex-row text-center justify-content-center">
      <div class="community-strength w-100">
        <p class="by-copy-large-regular text-by-neutral-5 text-center">Als Kompetenzzentrum für das Thema Open Data in Bayern stärken
          wir Kompetenzen in der Verwaltung, entwickeln innovative Lösungen für vernetzte Daten im Sinne von Linked Open
          Data und schaffen gemeinsam mit Open Data Communities echten Mehrwert.
          <br><br>
          Arbeite mit uns gemeinsam an einem besseren Bayern durch Daten: Einfach, vertrauenswürdig, vernetzt.
          </p>
      </div>
      <div class="d-flex flex-column align-items-center justify-content-center">
        <div class="avatar-container avatar-ocbydata d-flex flex-wrap justify-content-center w-100">
          <img class="avatar" src="../../assets/img/Luis.png" alt="Luis Moßburger auf einem isometrischen Muster">
          <img class="avatar" src="../../assets/img/Ruth.png" alt="Ruth Neeser auf einem isometrischen Muster">
          <img class="avatar" src="../../assets/img/Mitarbyterin.png" alt="Ein isometrisches Muster">
          <img class="avatar" src="../../assets/img/Bianca.png" alt="Bianca Sammer auf einem isometrischen Muster">
        </div>
        <div class="avatar-container avatar-byte d-flex flex-wrap justify-content-center w-100 mt-5 mb-8">
          <img class="d-none avatar" src="../../assets/img/Kai.png" alt="Kai Wanschura auf einem blauen Kreis">
          <img class="avatar" src="../../assets/img/Julia.png" alt="Julia Radermacher auf einem blauen Kreis">
          <img class="avatar" src="../../assets/img/Patryk.png" alt="Patryk Brzoza auf einem blauen Kreis">
          <img class="d-none avatar" src="../../assets/img/Julian.png" alt="Julian Grote auf einem blauen Kreis">
          <img class="avatar" src="../../assets/img/Patrick.png" alt="Patrick Pöhner auf einem blauen Kreis">
        </div>
        <a class="by-btn-primary-large-dark" href="https://oc.bydata.de">Mehr zum Team</a>
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
.community-strength-container {
  gap: 2.5rem;
}

.avatar-container.avatar-ocbydata {
  gap: 8rem;
  padding: 0 2rem;
  margin-bottom: 2.5rem;

  .avatar {
    height: 7rem;
  }
}

.avatar-container.avatar-byte {
  gap: 6rem;
  padding: 0 2rem;
  margin-bottom: 2.5rem;

  .avatar {
    height: 6rem;
  }
}

.community-strength-container .avatar-container.avatar-ocbydata .avatar,
.community-strength-container .avatar-container.avatar-byte .avatar {
    display: grid;
    place-items: center;
    width: auto;
    background-color: transparent !important;
    border-radius: 0 !important;
}

.btn-mail {
  display: flex;
  align-items: center;
  background-color: var(--by-neutral-5);
  color: var(--by-neutral-100);

  &::before {
    content: '';
    width: 24px;
    height: 24px;
    margin-right: .5rem;
    background: url('../../assets/img/envelope.svg') no-repeat;
  }
}
</style>
