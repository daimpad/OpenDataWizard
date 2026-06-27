<template>
  <article class="px-0 py-0 my-0">
    <section class="px-7 px-xl-9 py-14 mb-0">
      <div style="max-width:1280px" class="mx-auto">
        <h1 class="by-heading-2 text-by-blue-100 mb-5">Daten nutzen</h1>
        <p class="by-copy-large-semibold text-by-neutral-100 mb-0 restrict-max-width-xl">
          Um es allen zu ermöglichen, auch automatisiert auf Bayerns offene Daten zuzugreifen,
          bieten
          wir frei verfügbare Schnittstellen (APIs) an.
        </p>
      </div>
    </section>
    <section class="my-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">Unsere Schnittstellen</h2>
        <p class="text-by-neutral-100 by-copy-large-regular mb-9 restrict-max-width">
          Aktuell bietet open bydata drei verschiedene APIs an – ohne Anmeldung oder Registrierung.
          <br>
          Lass uns gerne wissen, für welche Projekte du die Schnittstellen nutzt!
          <br>
          <br>
          Um deinen Einstieg mit unseren APIs so einfach wie möglich zu machen, haben wir <a class="by-link-light" href="https://github.com/byte-bayern/API-Examples-OpenByData" target="_blank">Jupyter Notebooks für einen Quick Start</a> geschrieben.
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
          gerne
          einfach eine Mail und wir schauen, wie wir helfen können.
        </p>
        <div class="d-flex">
          <a href="mailto:openbydata@byte.bayern"
             class="by-btn-primary-large-light mt-7 btn-mail">
            Eine Nachricht schreiben</a>
        </div>
      </div>
    </section>
     <section class="my-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width: 1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">Events</h2>
        <p class="text-by-neutral-100 by-copy-large-regular mb-9 restrict-max-width">
          Trefft uns und Open Data Communities aus ganz Bayern, bring dich ein in die Welt der offenen Daten. Wir freuen uns auf den Austausch!
          <br>
          Du hast Interesse an unseren Events rund Open Data und möchtest direkt über Termine auf dem Laufenden gehalten werden? Hier kannst Du Dich für den Erhalt von Einladungen zu unseren Events <a href="https://www.byte.bayern/was-wir-machen/open-data-bayern/open-data-infos">anmelden</a>.
        </p>
        <div class="row">
          <div class="col-xs-12 col-xl-4 mb-7 mb-xl-0">
            <div class="h-100 p-4 bg-white d-flex flex-column justify-content-between" style="box-shadow: 0px 1px 0px 0px #E6E7E9; ">
              <img width="48" class="mb-4" aria-hidden="true" src="../assets/icon/icon-sparkle.svg"
                   alt="Sparkle Icon"/>
              <h4 class="mb-4 by-heading-4 text-by-blue-100">Open Data Day München 2024</h4>
              <p class="mb-5 by-copy-small-regular text-by-neutral-60">Einen Tag lang eintauchen in die Welt der offenen Daten. Ein Treffen von Open Data
              Communities aus Zivilgesellschaft, Wissenschaft, Wirtschaft und Verwaltung.</p>
              <div class="d-flex mt-auto">
                <a href="/events/open-data-day-muenchen-2024"
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
  name: "Daten nutzen",
  metaInfo() {
    const title = "Daten nutzen";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
