<template>
  <article class="px-0 py-0 my-0">
    <section class="section-daten-liefern px-7 px-xl-9 m-0">
      <div style="max-width:1280px" class="mx-auto">
        <h1 class="by-heading-2 text-by-blue-100 mb-5">Daten liefern</h1>
        <div class="introduction-container">
          <p class="restrict-max-width mb-0 by-copy-large-semibold text-by-neutral-100">Daten sind die Basis für Ideen,
            Produkte
            und Services, die Mehrwert für uns alle schaffen. Wir unterstützen die bayerische
            Verwaltung
            bei allen Fragen zum Thema Open Data.
          </p>
          <p class="d-flex flex-row align-items-center restrict-max-width mb-0 by-copy-small-regular text-by-neutral-100">
            Sie haben bereits einen Zugang zum Datenupload?
            <button class="btn-login by-btn-tertiary-small-light ml-3" @click="login">
                Zum Login
            </button>
          </p>
        </div>
      </div>
    </section>
    <section class="m-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width:1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">Ein erster Einstieg</h2>
        <p class="restrict-max-width text-by-neutral-100 by-copy-large-regular mb-9">
          Noch nicht mit Open Data beschäftigt? Diese Artikel sollten helfen, einen ersten Überblick
          zu den wichtigsten Themen zu bekommen.
        </p>
        <div class="row">
          <div class="article-card-hover-effect px-3 col-xs-12 col-md-6 mb-3 mt-0">
            <router-link :to="`/info/definition`">
            <div class="h-100 bg-white d-flex flex-column justify-content-between">
              <div>
                <div
                  class="embed-responsive embed-responsive-16by9 bg-by-blue-30 d-flex justify-content-center align-items-center">
                  <img style="width: 6rem" aria-hidden="true"
                       src="../assets/icon/icon-open.svg" alt="Open Icon"/>
                </div>
                <h4 class="px-4 pt-4 by-heading-4 text-by-blue-100 mb-3">Was ist Open Data?</h4>
              </div>

              <div class="px-4 pb-4">
                <p class="custom-article-truncate mb-3 by-copy-large-regular text-by-neutral-90">
                  Tauchen Sie ein in die Welt der offenen Daten: Erfahren Sie, wie Open Data das
                  Fundament des digitalen Zeitalters bildet, Transparenz und Innovation fördert und
                  wie open bydata eine Plattform für frei zugängliche Informationen aus Bayern
                  bietet.
                </p>
                <p class="by-copy-small-regular text-by-neutral-60 mb-3">
                  4 Min. Lesezeit
                </p>
                <router-link :to="`/info/definition`" class="by-copy-large-regular text-by-blue-80">
                  Artikel lesen
                </router-link>
              </div>
            </div>
          </router-link>
          </div>
          <div class="article-card-hover-effect px-3 col-xs-12 col-md-6 my-3 mt-md-0">
            <router-link :to="`/info/licenses`">
              <div class="h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                  <div
                    class="embed-responsive embed-responsive-16by9 bg-by-blue-30 d-flex justify-content-center align-items-center">
                    <img style="width: 6rem" aria-hidden="true"
                         src="../assets/icon/icon-license.svg" alt="License Icon"/>
                  </div>
                  <h4 class="px-4 pt-4 by-heading-4 text-by-blue-100 mb-3">Welche Lizenzen sollte
                    ich
                    vergeben?</h4>
                </div>
                <div class="px-4 mb-4">
                  <p class="custom-article-truncate mb-3 by-copy-large-regular text-by-neutral-90">
                    Finden Sie die passende Lizenz für Ihre
                    offenen Daten: Schaffen Sie Rechtssicherheit, fördern Sie Innovationen und
                    maximieren Sie den Nutzen für alle Beteiligten durch die richtige
                    Lizenzauswahl.
                  </p>
                  <p class="by-copy-small-regular text-by-neutral-60 mb-3">
                    6 Min. Lesezeit
                  </p>
                  <router-link :to="`/info/licenses`" class="by-copy-large-regular text-by-blue-80">
                    Artikel lesen
                  </router-link>
                </div>
              </div>
            </router-link>
          </div>
          <div class="article-card-hover-effect px-3 col-xs-12 col-md-6 my-3 mb-3 md-0">
            <router-link :to="`/info/metadata`">
              <div class="h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                  <div
                    class="embed-responsive embed-responsive-16by9 bg-by-blue-30 d-flex justify-content-center align-items-center">
                    <img style="width: 6rem" aria-hidden="true"
                         src="../assets/icon/icon-data.svg" alt="Data Icon"/>
                  </div>
                  <h4 class="px-4 pt-4 by-heading-4 text-by-blue-100 mb-3">Wie beschreibe ich
                    Datensätze?</h4>
                </div>
                <div class="px-4 pb-4">
                  <p class="custom-article-truncate mb-3 by-copy-large-regular text-by-neutral-90">
                    Technische Veröffentlichung von Datensätzen leicht gemacht: Erfahren Sie, wie
                    Sie
                    Metadaten effektiv nutzen, den DCAT-AP.de-Standard anwenden und Datensätze
                    automatisch oder manuell an open bydata anbinden können.
                  </p>
                  <p class="by-copy-small-regular text-by-neutral-60 mb-3">
                    4 Min. Lesezeit
                  </p>
                  <router-link :to="`/info/metadata`" class="by-copy-large-regular text-by-blue-80">
                    Artikel lesen
                  </router-link>
                </div>
              </div>
            </router-link>
          </div>
          <div class="article-card-hover-effect px-3 col-xs-12 col-md-6 mt-3 mb-md-0">
            <router-link :to="`/info/provisioning`">
              <div class="h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                  <div
                    class="embed-responsive embed-responsive-16by9 bg-by-blue-30 d-flex justify-content-center align-items-center">
                    <img style="width: 6rem" aria-hidden="true"
                         src="../assets/icon/icon-done.svg" alt="Done Icon"/>
                  </div>
                  <h4 class="px-4 pt-4 by-heading-4 text-by-blue-100 mb-3">Was macht gute Daten
                    aus?</h4>
                </div>
                <div class="px-4 pb-4">
                  <p class="custom-article-truncate mb-3 by-copy-large-regular text-by-neutral-90">
                    Erfahren Sie, wie Sie qualitativ hochwertige offene Daten identifizieren und
                    veröffentlichen können. Entdecken Sie das 5-Sterne-Modell für Datenqualität und
                    lernen Sie, wie Sie Ihre Daten optimal für open bydata aufbereiten.
                  </p>
                  <p class="by-copy-small-regular text-by-neutral-60 mb-3">
                    4 Min. Lesezeit
                  </p>
                  <router-link :to="`/info/provisioning`" class="by-copy-large-regular text-by-blue-80">
                    Artikel lesen
                  </router-link>
                </div>
              </div>
            </router-link>
          </div>
          <div class="article-card-hover-effect px-3 col-xs-12 col-md-6 my-3 mt-3 md-0">
            <router-link :to="`/info/hvd`">
              <div class="h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                  <div
                    class="embed-responsive embed-responsive-16by9 bg-by-blue-30 d-flex justify-content-center align-items-center">
                    <img style="width: 6rem" aria-hidden="true"
                         src="../assets/icon/icon-license.svg" alt="Document Icon"/>
                  </div>
                  <h4 class="px-4 pt-4 by-heading-4 text-by-blue-100 mb-3">Was ist die "High Value Datasets"-Verordnung?</h4>
                </div>
                <div class="px-4 mb-4">
                  <p class="custom-article-truncate mb-3 by-copy-large-regular text-by-neutral-90">
                    Die öffentliche Verwaltung muss hochwertige Daten 
                    ab 9. Juni 2024 auf eine bestimmte Weise bereistellen. 
                    Wir unterstützen Sie bei der Umsetzung der EU-Verordnung.
                  </p>
                  <p class="by-copy-small-regular text-by-neutral-60 mb-3">
                    5 Min. Lesezeit
                  </p>
                  <router-link :to="`/info/hvd`" class="by-copy-large-regular text-by-blue-80">
                    Mehr Informationen
                  </router-link>
                </div>
              </div>
            </router-link>
          </div>
          <div class="article-card-hover-effect px-3 col-xs-12 col-md-6 my-3 mt-3 md-0">
            <router-link :to="`/info/inventory`">
              <div class="h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                  <div
                    class="embed-responsive embed-responsive-16by9 bg-by-blue-30 d-flex justify-content-center align-items-center">
                    <img style="width: 6rem" aria-hidden="true"
                         src="../assets/icon/icon-open.svg" alt="Four Arrows Icon"/>
                  </div>
                  <h4 class="px-4 pt-4 by-heading-4 text-by-blue-100 mb-3">Wie mache ich eine Dateninventur?</h4>
                </div>
                <div class="px-4 mb-4">
                  <p class="custom-article-truncate mb-3 by-copy-large-regular text-by-neutral-90">
                    Verschaffen Sie sich ganz pragmatisch einen Überblick 
                    über Ihre Verwaltungsdaten mit einer Dateinventur und 
                    machen Sie so den ersten Schritt zum Thema Open Data.
                  </p>
                  <p class="by-copy-small-regular text-by-neutral-60 mb-3">
                    5 Min. Lesezeit
                  </p>
                  <router-link :to="`/info/inventory`" class="by-copy-large-regular text-by-blue-80">
                    Mehr Informationen
                  </router-link>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </section>
    <section class="m-0 pt-11 pb-9 px-7 px-xl-9 pb-9">
      <div style="max-width:1280px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100">Wir beraten gern!</h2>
        <p class="restrict-max-width mt-2 text-by-neutral-100 by-copy-large-regular mb-9">
          Lieber ein persönliches Gespräch? Wir beraten gerne zu allen Themen rund um Open Data - auch individuell.
        </p>
        <div class="row">
          <div class="col-xs-12 col-xl-4 px-3 mb-3 mt-0 my-xl-0">
            <div class="event-card">
              <div class="mx-0 row">
                <div class="col-12 col-md-6 col-xl-12">
                  <h4 class="by-heading-4 text-by-blue-100 mb-1 mb-md-0"
                      style="">Quick
                    Start Open Data</h4>
                </div>
                <div class="col-12 col-md-6 text-md-right text-xl-left">
                  <span class="by-copy-small-regular text-by-neutral-80">
                    <img class="mr-1"
                         style="width: 1.5rem; height: 1.5rem;"
                         src="../assets/icon/icon-clock.svg"/>30 min</span>
                </div>
              </div>
              <div class="mt-4 mt-md-5 mx-0 row">
                <div class="col-xs-12 px-0 pr-md-4 px-xl-0 col-md-6 col-xl-12 d-flex flex-column justify-content-between">
                  <p class="by-copy-small-regular text-by-neutral-60 mb-4">
                    Ein erster Überblick zu Open Data
                    und zu Möglichkeiten, mitzumachen.
                  </p>
                  <div class="d-flex">
                    <a href="mailto:openbydata@byte.bayern?subject=Quick%20Start%20Open%20Data&body=Guten%20Tag%2C%0D%0A%0D%0Aich%20bzw.%20wir%20sind%20daran%20interessiert%2C%20einen%20ersten%20%C3%9Cberblick%20zu%20Open%20Data%20und%20weiteren%20hiermit%20verbundenen%20M%C3%B6glichkeiten%20zu%20bekommen.%0D%0A%0D%0AEin%20m%C3%B6glicher%20Terminvorschlag%20w%C3%A4re%3A%0D%0A%5Bbitte%20hier%20einf%C3%BCgen%5D%0D%0A%0D%0AFreundliche%20Gr%C3%BC%C3%9Fe%0D%0A" class="by-btn-primary-large-light by-copy-large-semibold">Kostenfreie Terminanfrage</a>
                  </div>
                </div>
                <div class="col-xs-12 px-0 pl-md-4 px-xl-0 col-md-6 col-xl-12">
                  <p
                    class="mt-7 d-md-none d-xl-block by-copy-large-semibold text-by-neutral-100 mb-3">
                    Inhalt</p>
                  <ul class="content-list">
                    <li>Überblick zu Open Data</li>
                    <li>Vorteile der Datenveröffentlichung</li>
                    <li>Vorstellung unserer Beratungsangebote</li>
                    <li>Präsentation der eigenen Open-Data-Präsenz</li>
                    <li>Individuelle Beratung und Fragen</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-xl-4 px-3 my-3 my-xl-0">
            <div class="event-card">
              <div class="mx-0 row">
                <div class="col-12 col-md-6 col-xl-12">
                  <h4 class="by-heading-4 text-by-blue-100 mb-1 mb-md-0">Lizenzen & Daten</h4>
                </div>
                <div class="col-12 col-md-6 text-md-right text-xl-left">
              <span class="by-copy-small-regular text-by-neutral-80">
                <img class="mr-1"
                     style="width: 1.5rem; height: 1.5rem;"
                     src="../assets/icon/icon-clock.svg"/>60 min</span>
                </div>
              </div>
              <div class="mt-4 mt-md-5 mx-0 row">
                <div class="col-xs-12 px-0 pr-md-4 px-xl-0 col-md-6 col-xl-12 d-flex flex-column justify-content-between">
                  <p class="by-copy-small-regular text-by-neutral-60 mb-4">
                    Beratung zur Festlegung
                    einer Lizenz, um die eigenen Daten sicher nachnutzbar zu machen.</p>
                  <div class="d-flex">
                    <a href="mailto:openbydata@byte.bayern?subject=Lizenzen%20%26%20Daten&body=Guten%20Tag%2C%0D%0A%0D%0Aich%20bzw.%20wir%20sind%20an%20einer%20Beratung%20zur%20Festlegung%20einer%20Lizenz%2C%20um%20die%20eigenen%20Daten%20sicher%20nachnutzbar%20zu%20machen%2C%20interessiert.%0D%0A%0D%0AEin%20m%C3%B6glicher%20Terminvorschlag%20w%C3%A4re%3A%0D%0A%5Bbitte%20hier%20einf%C3%BCgen%5D%0D%0A%0D%0AFreundliche%20Gr%C3%BC%C3%9Fe%0D%0A" class="by-btn-primary-large-light by-copy-large-semibold">Kostenfreie Terminanfrage</a>
                  </div>
                </div>
                <div class="col-xs-12 px-0 pl-md-4 px-xl-0 col-md-6 col-xl-12">
                  <p
                    class="mt-7 d-md-none d-xl-block by-copy-large-semibold text-by-neutral-100 mb-3">
                    Inhalt</p>
                  <ul class="content-list">
                    <li>Überblick zu möglichen Datenlizenzen und ihrem Sinn</li>
                    <li>Vor- und Nachteile verschiedener Lizenzen</li>
                    <li>Erklärung zum Vergeben von Lizenzen in der Praxis</li>
                    <li>Eine passende Lizenz für eigene Daten auswählen</li>
                    <li>Individuelle Beratung und Fragen</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xs-12 col-xl-4 px-3 mt-3 mb-0 my-xl-0">
            <div class="event-card">
              <div class="mx-0 row">
                <div class="col-12 col-md-6 col-xl-12">
                  <h4 class="by-heading-4 text-by-blue-100 mb-1 mb-md-0">
                    Datenbereitstellung &
                    -qualität</h4>
                </div>
                <div class="col-12 col-md-6 text-md-right text-xl-left">
              <span class="by-copy-small-regular text-by-neutral-80">
                <img class="mr-1"
                     style="width: 1.5rem; height: 1.5rem;"
                     src="../assets/icon/icon-clock.svg"/>60 min</span>
                </div>
              </div>
              <div class="mt-4 mt-md-5 mx-0 row">
                <div class="col-xs-12 px-0 pr-md-4 px-xl-0 col-md-6 col-xl-12 d-flex flex-column justify-content-between">
                  <p class="by-copy-small-regular text-by-neutral-60 mb-4" style="height: 4.5rem;">
                    Ein Einblick in die Möglichkeiten, gute Daten automatisiert oder manuell zu
                    veröffentlichen.
                  </p>
                  <div class="d-flex">
                    <a href="mailto:openbydata@byte.bayern?subject=Datenbereitstellung%20%26%20-qualit%C3%A4t&body=Guten%20Tag%2C%0D%0A%0D%0Aich%20bzw.%20wir%20sind%20daran%20interessiert%2C%20einen%20Einblick%20zu%20bekommen%2C%20wie%20gute%20Daten%20automatisiert%20oder%20manuell%20ver%C3%B6ffentlicht%20werden%20k%C3%B6nnen.%0D%0A%0D%0AEin%20m%C3%B6glicher%20Terminvorschlag%20w%C3%A4re%3A%0D%0A%5Bbitte%20hier%20einf%C3%BCgen%5D%0D%0A%0D%0AFreundliche%20Gr%C3%BC%C3%9Fe%0D%0A" class="by-btn-primary-large-light by-copy-large-semibold">Kostenfreie Terminanfrage</a>
                  </div>
                </div>
                <div class="col-xs-12 px-0 pl-md-4 px-xl-0 col-md-6 col-xl-12">
                  <p
                    class="mt-7 d-md-none d-xl-block by-copy-large-semibold text-by-neutral-100 mb-3">
                    Inhalt</p>
                  <ul class="content-list">
                    <li>Information zur Datenbereitstellung via open bydata</li>
                    <li>Demonstration Datenerfassung mit Konto</li>
                    <li>Information zu automatisierter Bereitstellung über unsere Schnittstelle oder
                      Harvester
                    </li>
                    <li>Überblick zu nötigen und empfohlenen Angaben zu einem Datensatz</li>
                    <li>Überblick zum Metadatenstandard DCAT-AP.de</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="m-0 pt-9 pb-11 px-7 px-xl-9 pb-9 bg-by-neutral-5">
      <div style="max-width:1280px" class="mx-auto text-center">
        <h4 class="by-heading-4 text-by-blue-100 mb-5">Individuelle Beratung</h4>
        <p class="by-heading-5 text-by-neutral-60 mb-7 d-none d-md-block">
          "Ich sorge dafür, dass Ihre Daten unkompliziert nutzbar sind. Arbeiten Sie mit uns an
          einem besseren Bayern durch Daten!"
        </p>
        <p class="by-heading-4 text-by-neutral-60 mb-7 d-md-none">
          "Ich sorge dafür, dass Ihre Daten unkompliziert nutzbar sind. Arbeiten Sie mit uns an
          einem besseren Bayern durch Daten!"
        </p>
        <img src="../assets/img/luis_mossburger.jpg" class="mb-2 d-md-none"
             style="border-radius: 6rem; width: 6rem; height: 6rem;"/>
        <img src="../assets/img/luis_mossburger.jpg" class="mb-2 d-none d-md-inline d-xl-none"
             style="border-radius: 7.5rem; width: 7.5rem; height: 7.5rem;"/>
        <img src="../assets/img/luis_mossburger.jpg" class="mb-2 d-none d-xl-inline"
             style="border-radius: 10rem; width: 10rem; height: 10rem;"/>
        <p class="by-copy-small-semibold text-by-neutral-100 mb-0">
          Luis Moßburger
        </p>
        <p class="by-copy-small-regular text-by-neutral-100 mb-4">
          Product Owner für Open Data
        </p>
        <div class="d-inline-block">
          <a href="mailto:openbydata@byte.bayern "
             class="by-btn-primary-large-light mt-7 btn-mail">
            Eine Nachricht schreiben</a>
        </div>
      </div>
    </section>
    <section class="m-0 pt-11 pb-14 bg-by-neutral-10">
      <div class="mb-9">
        <div class="px-7 px-xl-9 text-left">
          <div class="mx-auto" style="max-width: 1280px;">
            <h2 class="by-heading-2 text-by-blue-100 mb-9">Die eigene Open-Data-Präsenz:
              Individualisiert und
              integriert auf open bydata.</h2>
          </div>
        </div>
      </div>
      <div
        class="embed-responsive embed-responsive-2by1 mb-9">
        <img class="embed-responsive-item" aria-hidden="true"
             src="../assets/img/preview.jpg" alt="License Icon"/>
      </div>
      <div class="px-7 px-xl-9">
        <div class="row mx-auto" style="max-width: 1280px;">
          <div class="px-0 pr-md-3 col-xs-12 col-md-6 col-lg-3 mb-7">
            <p class="by-copy-large-semibold text-by-blue-100 mb-2">Sichtbarkeit</p>
            <p class="by-copy-small-regular text-by-neutral-60 m-0">
              Ein eigener Bereich mit Ihren Texten, Bildern, Logo & Co. - zeigen Sie, was Ihre
              Organisation für Open Data tut!
            </p>
          </div>
          <div class="px-0 pl-md-3 px-xl-3 col-xs-12 col-md-6 col-lg-3 mb-7">
            <p class="by-copy-large-semibold text-by-blue-100 mb-2">Integration</p>
            <p class="by-copy-small-regular text-by-neutral-60 m-0">
              Ihre Daten sind direkt auf open bydata verfügbar, und damit automatisch auch im
              Bundesportal GovData und dem EU-Portal data.europa.eu.
            </p>
          </div>
          <div class="px-0 pr-md-3 px-xl-3 col-xs-12 col-md-6 col-lg-3 mb-7">
            <p class="by-copy-large-semibold text-by-blue-100 mb-2">Kosten</p>
            <p class="by-copy-small-regular text-by-neutral-60 m-0">
              Statt ein eigenes Portal
              aufzubauen, spart dieser Ansatz Personal und finanzielle Ressourcen - und Ihre Daten
              sind bei uns in guter Hand.
            </p>
          </div>
          <div class="px-0 pl-md-3 col-xs-12 col-md-6 col-lg-3 mb-4">
            <p class="by-copy-large-semibold text-by-blue-100 mb-2">State-of-the-Art-Technologie</p>
            <p class="by-copy-small-regular text-by-neutral-60 m-0">
              Wir nutzen piveau - Open Data, Open Source, Cloud-Native - und arbeiten laufend an der
              Verbesserung der Daten, gemeinsam mit Ihnen!
            </p>
          </div>
        </div>
        <div class="text-left text-md-center">
          <div class="d-inline-block ">
            <a href="mailto:openbydata@byte.bayern "
               class="by-btn-primary-large-light mt-7 btn-mail">
              Eine Nachricht schreiben</a>
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

.btn-login {
  &::after {
    content: '';
    width: 24px;
    height: 24px;
    margin-left: .5rem;
    background: url('../assets/icon/icon-caretright.svg') no-repeat;
  }
}

.introduction-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-daten-liefern {
  padding-top: 8.5rem;
  padding-bottom: 8.5rem;
}
</style>
<style>
.custom-article-truncate {
  display: -webkit-box;
  -webkit-line-clamp: 4; /* Number of lines to display */
  line-clamp: 4; /* Number of lines to display */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.embed-responsive-2by1::before {
  content: "";
  padding-top: 50%
}
</style>
<script>
export default {
  name: "Info",
  metaInfo() {
    const title = "Daten liefern - open bydata";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  },
  methods: {
    login() {
      if (!this.$keycloak) {
        console.error("Keycloak not initialized!");
        return;
      }
      
      this.$keycloak.loginFn();
    },
  }
}
</script>
