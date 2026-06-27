<template>
  <article class="px-0 py-0 my-0">
    <section class="px-7 px-xl-9 py-14 m-0">
      <div style="max-width:704px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100 mb-5">Nutzungshinweise für das Portal „open bydata"</h2>
      </div>
    </section>
    <section class="m-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width:704px" class="mx-auto">
        <h3 class="by-heading-3 text-by-blue-100 mb-4">1. Geltungsbereich</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          Die Nutzungshinweise gelten für das Portal „open bydata“ unter der Domain „open.bydata.de“
          bzw. „opendata.bayern“ (im Folgenden: <b>„open bydata“</b> oder „das Portal“).
        </p>

        <h3 class="by-heading-3 text-by-blue-100 mb-4">2. Betreiber und Hosting</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          Betreiber des Portals ist die byte – BAYERISCHE AGENTUR FÜR DIGITALES GMBH.
          Die technische Umsetzung sowie das Hosting erfolgen durch die Firma
          <a href="https://www.dksr.city/">DKSR GMBH</a> und
          <a href="https://www.fokus.fraunhofer.de/">FRAUNHOFER FOKUS</a>.
        </p>

        <h3 class="by-heading-3 text-by-blue-100 mb-4">3. Beschreibung des Angebots</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          (1) open bydata bietet einen einfachen Zugang zu Daten und Dokumenten mit Bezug
          zum Freistaat Bayern von verschiedenen Anbietern aus dem öffentlichen und privaten Sektor.
          Zentraler Bestandteil von open bydata ist ein systematisierter Datenkatalog.
          Über dessen standardisierte Metadaten, d.h. eine einheitliche Beschreibung,
          sind die Daten und Dokumente einfach auffindbar und zugänglich.
        </p>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          (2) Die Daten und Dokumente selbst werden nicht von open bydata,
          sondern dezentral von den jeweiligen Anbietern angeboten und vorgehalten (gehostet).
          Die im Datenkatalog enthaltenen Metadaten sind verlinkt auf das jeweilige Webangebot
          des Daten- oder Dokumentenanbieters.
          Etwaige rechtliche Beziehungen des Nutzers entstehen im Fall eines Zugriffs
          oder Downloads zum Daten- oder Dokumentenanbieter, nicht zu open bydata.
          Der Daten- oder Dokumentenanbieter entscheidet eigenständig über die Nutzungsbedingungen
          der von ihm angebotenen Daten oder Dokumente.
        </p>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          (3) Das Portal wird laufend weiterentwickelt.
          Der Betreiber behält sich vor, die auf dem Portal angebotenen Dienste zu ändern,
          abweichende Dienste anzubieten oder diese ganz einzustellen und
          gewährleistet keine Erreichbarkeit seines Servers.
        </p>

        <h3 class="by-heading-3 text-by-blue-100 mb-4">4. Haftung</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          (1) Der Betreiber kann vor Aufnahme eines Links auf die Daten und Dokumente
          eines Anbieters in open bydata lediglich stichprobenartig den Inhalt der von
          dem Anbieter auf seiner Webseite bereitgestellten Daten und Dokumente prüfen.
          Er haftet für etwaige Rechtsverletzungen auf die verlinkten Seiten nach den
          allgemeinen Grundsätzen erst, wenn er hiervon Kenntnis erlangt und nicht entsprechend reagiert.
        </p>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          (2) Bei einem Widerspruch gegen die Veröffentlichung von Daten,
          beispielsweise wegen eines vermuteten Personenbezugs,
          die auf einer Webseite eines Daten- oder Dokumentenanbieters erfolgt,
          muss die Beschwerde an den Daten- oder Dokumentenanbieter gerichtet werden.
        </p>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          (3) Der Betreiber haftet nicht für Schäden, die durch die Nutzung von Informationen entstehen,
          die auf den Webseiten von Daten- oder Dokumentenanbietern zugänglich gemacht werden,
          oder die beim Aufrufen oder Herunterladen von Daten durch Computerviren
          oder die Installation oder Nutzung von Software von diesen Webseiten verursacht werden.
        </p>

        <h3 class="by-heading-3 text-by-blue-100 mb-4">5. Änderung dieser Nutzungshinweise</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          Der Betreiber behält sich vor, diese Nutzungshinweise jederzeit ohne Nennung von Gründen zu ändern.
        </p>
      </div>
    </section>
  </article>
</template>
<script>

export default {
  name: "TermsOfUse",
  metaInfo() {
    const title = "Nutzungsbedingungen - open bydata";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>

<style scoped>
@import "../styles/static_pages.scss";
</style>
