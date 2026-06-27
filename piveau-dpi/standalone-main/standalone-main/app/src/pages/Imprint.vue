<template>
  <article class="px-0 py-0 my-0">
    <section class="px-7 px-xl-9 py-14 m-0">
      <div style="max-width:704px" class="mx-auto">
        <h2 class="by-heading-2 text-by-blue-100 mb-5">Impressum</h2>
        <p class="mb-0 by-copy-large-regular text-by-neutral-100">
          byte – Bayerische Agentur für Digitales GmbH<br><br>
          Oskar-von-Miller-Ring 3580333 München<br>
          0162 / 2019793<br>
          <a href="mailto:kontakt@byte.bayern">kontakt@byte.bayern</a><br><br>
          Vertretungsberechtigte:<br>
          Antonia Zierer (Geschäftsführerin)<br>
          Thorsten Kühnel (Geschäftsführer)<br><br>
          Registergericht: Amtsgericht München, HRB 273196<br>
          USt-Identifikationsnummer (gemäß § 27 a Umsatzsteuergesetz):<br>
          DE354823776
        </p>
      </div>
    </section>
    <section class="m-0 py-11 px-7 px-xl-9 bg-by-neutral-10">
      <div style="max-width:704px" class="mx-auto">
        <h3 class="by-heading-3 text-by-blue-100 mb-4">Verantwortlich für den Inhalt</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          Antonia Zierer (Geschäftsführerin)<br>
          Thorsten Kühnel (Geschäftsführer)<br><br>
          <a href="mailto:kontakt@byte.bayern">kontakt@byte.bayern</a><br><br>
          Namentlich gekennzeichnete Internetseiten geben die Auffassungen und Erkenntnisse der
          genannten Personen wieder.
        </p>
        <h3 class="by-heading-3 text-by-blue-100 mb-4">Bildnachweis</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          Die Bildrechte für die auf dieser Webseite verwendeten Bilder sind geklärt. Quellenangaben
          erfolgen in der Bildunterschrift oder im Alternativtext zum Bild. Bilder ohne explizite
          Quellenangaben sind grundsätzlich mit Quelle „byte – Bayerische Agentur für Digitales
          GmbH” zu führen. Ihre Nutzung bedarf der Zustimmung der byte – Bayerische Agentur für
          Digitales GmbH. Sollten Sie sich, trotz aller Sorgfalt unserer Arbeit, in einem Bildrecht
          verletzt fühlen, wenden Sie sich bitte an <a href="mailto:kontakt@byte.bayern">kontakt@byte.bayern</a>.
          <br><br>
          Auf dieser Website wird das Icon-Paket "Phosphor" von <a href="https://helenazhang.com/" target="_blank">Helena Zhang</a> und <a href="https://tobiasfried.com/" target="_blank">Tobias Fried</a> genutzt, das unter der <a href="https://raw.githubusercontent.com/phosphor-icons/phosphor-home/master/LICENSE" target="_blank">MIT License</a> steht, sowie das "Carbon Design System" unter der <a href="https://github.com/carbon-design-system/carbon/blob/main/LICENSE" target="_blank">Apache 2.0 License</a>.
        </p>
        <h3 class="by-heading-3 text-by-blue-100 mb-4">Links</h3>
        <p class="text-by-neutral-100 by-copy-small-regular mb-9">
          Von unseren eigenen Inhalten sind Querverweise („Links“) auf die Webseiten anderer
          Anbieter zu unterscheiden. Durch diese Links ermöglichen wir lediglich den Zugang zur
          Nutzung fremder Inhalte nach § 8 Telemediengesetz. Bei der erstmaligen Verknüpfung mit
          diesen Internetangeboten haben wir diese fremden Inhalte in zumutbarem Umfang daraufhin
          überprüft, ob durch sie eine mögliche zivilrechtliche oder strafrechtliche
          Verantwortlichkeit ausgelöst wird. Wir können diese fremden Inhalte aber nicht ständig auf
          Veränderungen überprüfen und daher auch keine Verantwortung dafür übernehmen. Für
          illegale, fehlerhafte oder unvollständige Inhalte und insbesondere für Schäden, die aus
          der Nutzung oder Nichtnutzung von Informationen Dritter entstehen, haftet allein der
          jeweilige Anbieter der Seite.
        </p>
      </div>
    </section>
  </article>
</template>
<script>

export default {
  name: "Imprint",
  metaInfo() {
    const title = "Impressum - open bydata";
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
