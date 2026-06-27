<template>
  <article class="px-0 py-0 my-0">
    <section class="px-7 px-xl-9 py-14 mb-0">
      <div style="max-width:1280px" class="mx-auto">
        <h1 class="by-heading-2 text-by-blue-100 mb-5">Unsere Services</h1>
        <p class="by-copy-large-semibold text-by-neutral-100 mb-0 restrict-max-width-xl">
          open.bydata ist ein Produkt des open bydata competence centers. Erfahre mehr darüber, wie wir beim Daten teilen und nutzen in Bayern unterstützen.
        </p>
        <div class="d-flex">
          <a href="https://oc.bydata.de"
              class="by-btn-primary-large-light mt-7">
            Mehr Informationen</a>
        </div>
      </div>
    </section>
    <section class="my-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">Wie wir helfen</h2>
        <p class="text-by-neutral-100 by-copy-large-regular mb-9 restrict-max-width">
          Wir unterstützen Verwaltung, aber auch Wirtschaft, Zivilgesellschaft und Forschung dabei, mit offenen Daten umzugehen, um gemeinsam Mehrwert zu schaffen.
        </p>
        <div class="row">
          <div class="col-xs-12 col-xl-4 mb-7 mb-xl-0">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true"
                   src="../assets/icon/icon-magnifying-glass.svg" alt="Shuffle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Unterstützung für's Daten teilen</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">
                Wir bieten Beratung, Infrastruktur und Material, um das Daten teilen in Bayern so einfach wie möglich zu machen - für Verwaltung und Organisationen darüber hinaus.
              </p>
              <div class="d-flex mt-auto">
                <a href="https://oc.bydata.de/sharing" class="by-btn-secondary-large-light">
                  Unsere Services
                </a>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-xl-4 mb-7 mb-xl-0">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true"
                   src="../assets/icon/icon-shuffle-simple.svg" alt="Shuffle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Innovative Lösungen</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">
                Wir entwickeln Produkte mit State-of-the-Art Technologie, um miteinander zu teilen, zu vernetzen und in einer datengetriebenen Welt sichtbar zu machen.
              </p>
              <div class="d-flex mt-auto">
                <a href="https://oc.bydata.de/connecting"
                   class="by-btn-secondary-large-light">
                  Unsere Produkte</a>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-xl-4">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true" src="../assets/icon/icon-sparkle.svg"
                   alt="Sparkle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Datennutzung stärken</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">Wir bringen Verwaltung, Wirtschaft, Forschung und Zivilgesellschaft an einen Tisch, um gemeinsam Daten sinnvoll einzusetzen. </p>
              <div class="d-flex mt-auto">
                <a href="https://oc.bydata.de/using"
                   class="by-btn-secondary-large-light">
                  Unser Angebot
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="px-7 px-xl-9 pt-11 pb-14 mb-0">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="mb-0 by-heading-4 text-by-blue-100">Ideen oder Fragen?</h2>
        <p class="mt-4 text-by-neutral-100 by-copy-large-regular restrict-max-width">
          Wir freuen uns, über Projektideen, neuen Daten oder Feedback ins Gespräch zu kommen. Zusammen finden wir Wege, Bayern immer weiter #openbydata zu machen!
        </p>
        <div class="d-flex">
          <a href="https://form.typeform.com/to/ewn4iItc" target="_blank"
             class="by-btn-primary-large-light mt-7 btn-mail">
            Eine Nachricht schreiben</a>
        </div>
      </div>
    </section>
  </article>
</template>
<style lang="scss" scoped>
.btn-mail {
  display: flex;
  padding: 11px 1.5rem;
  align-items: center;
  background-color: var(--by-neutral-5);
  color: var(--by-neutral-100);

  &::before {
    color: white;
    content: '';
    width: 24px;
    height: 24px;
    margin-right: .5rem;
    background: url('../assets/icon/icon-envelope.svg') no-repeat;
  }
}
</style>
<script>
export default {
  name: "More",
  metaInfo() {
    const title = "Services";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
