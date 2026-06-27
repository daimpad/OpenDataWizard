<template>
  <article class="px-0 py-0 my-0">
    <section class="px-7 px-xl-9 py-14 mb-0">
      <div style="max-width:1280px" class="mx-auto">
        <h1 class="by-heading-2 text-by-blue-100 mb-5">Unsere Schnittstellen</h1>
        <p class="by-copy-large-semibold text-by-neutral-100 mb-0 restrict-max-width-xl">
          Um es allen zu ermöglichen, auch automatisiert auf Bayerns offene Daten zuzugreifen,
          bieten wir frei verfügbare Schnittstellen (APIs) an.
        </p>
      </div>
    </section>
    <section class="my-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">APIs</h2>
        <p class="text-by-neutral-100 by-copy-large-regular mb-9 restrict-max-width">
          Aktuell bietet open.bydata drei verschiedene APIs an – ohne Anmeldung oder Registrierung.
          <br>
          Lass uns gerne wissen, für welche Projekte du die Schnittstellen nutzt!
        </p>
        <div class="row">
          <div class="col-xs-12 col-xl-4 mb-7 mb-xl-0">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true"
                   src="../assets/icon/icon-magnifying-glass.svg" alt="Shuffle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Suchen über unsere Search-API</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">
                Diese API ermöglicht es, gezielt nach Datensätzen, Katalogen und Vokabularien zu
                suchen.
                Das ist vergleichbar mit der Suchfunktion auf der Webseite – das bietet sich also
                v.a.
                an, wenn noch nicht klar ist, welche Datensätze abgerufen werden sollen oder wenn
                ein
                Überblick benötigt wird.
              </p>
              <div class="d-flex mt-auto">
                <a href="/api/hub/search/" class="by-btn-secondary-large-light">
                  Zur Search-API
                </a>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-xl-4 mb-7 mb-xl-0">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true"
                   src="../assets/icon/icon-shuffle-simple.svg" alt="Shuffle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Komplexe Operationen über unsere
                Repo-API</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">
                Diese API ermöglicht keine direkte Suche, aber deutlich umfassendere Abfragen. So
                kann
                man Datensätze, Kataloge und Vokabulare abfragen. Wer Daten an uns liefert, kann
                über
                diese API auch die eigenen Datensätze, Distributionen, Kataloge bearbeiten bzw.
                löschen.
              </p>
              <div class="d-flex mt-auto">
                <a href="/api/hub/repo/"
                   class="by-btn-secondary-large-light">
                  Zur Repo-API</a>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-xl-4">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true" src="../assets/icon/icon-sparkle.svg"
                   alt="Sparkle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Direkte Abfragen mit unserer
                SPARQL-API</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">Unsere Metadaten werden im
                Tripel-Format in einer Graphendatenbank abgespeichert. Wir haben eine Schnittstelle
                eingerichtet, mit der man diese Datenbasis direkt abfragen kann. So wie relationale
                Datenbanken mit SQL abgefragt werden, wird eine Graphendatenbank mit SPARQL
                angesprochen.</p>
              <div class="d-flex mt-auto">
                <a href="/sparql"
                   class="by-btn-secondary-large-light">
                  Zur SPARQL-API
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
    <section class="px-7 px-xl-9 pt-11 pb-14 mb-0">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="mb-0 by-heading-4 text-by-blue-100">Noch Fragen zu unseren Schnittstellen?</h2>
        <p class="mt-4 text-by-neutral-100 by-copy-large-regular restrict-max-width">
          Über die bereitgestellten API-Dokumentationen hinaus bieten wir gerne unsere Hilfe an!
          <br><br>
          Du hast ein Projekt und möchtest dazu offene Daten aus Bayern nutzen? Dann schreib uns
          gerne einfach und wir sehen, wie wir helfen können.
        </p>
        <div class="d-flex">
          <a href="https://form.typeform.com/to/ewn4iItc" target="_blank"
             class="by-btn-primary-large-light mt-7 btn-mail">
            Eine Nachricht schreiben</a>
        </div>
      </div>
    </section>
     <section class="my-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">Quick Start</h2>
        <p class="text-by-neutral-100 by-copy-large-regular mb-9 restrict-max-width">
          Um den Einstieg in die Benutzung unserer Schnittstellen zu erleichtern haben wir kleine Jupyter Notebooks gebaut, die beispielhaft Abfragen zeigen. Wir freuen uns über weitere Ideen, wie wir das Loslegen vereinfachen können und natürlich gern eure eigenen Beispiele.
        </p>
        <div class="row">
          <div class="col-xs-12 col-xl-4 mb-7 mb-xl-0">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true" src="../assets/icon/icon-sparkle.svg"
                   alt="Sparkle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Jupyter Notebooks</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">Kleine Python Scripts, die mit unseren Schnittstellen eine beispielhafte Datenexploration machen.</p>
              <div class="d-flex mt-auto">
                <a href="https://github.com/byte-bayern/API-Examples-OpenByData"
                   class="by-btn-secondary-large-light">
                  Mehr Info
                </a>
              </div>
            </div>
          </div>
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
  name: "API",
  metaInfo() {
    const title = "Schnittstellen";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
