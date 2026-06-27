<template>
  <div class="bg-white my-0">
    <article class="by-static-info-page">
      <div class="d-flex flex-column align-items-start back-button">
        <a class="by-btn-tertiary-small-light by-link-light"
           @click="$router.go(-1)"
           type="button"
           aria-label="Go back"
        >
          <img style="height: 24px; width: 24px !important; margin: 0;"
               src="../assets/icon/icon-caretleft.svg"/>
          <span class="by-copy-small-semibold">Zurück</span>
        </a>
      </div>
      <h1>Welche Lizenzen sollte ich vergeben?</h1>
      <p>
        Staatliche Stellen, die Daten veröffentlichen kommen
        <ol>
          <li>ihrem gesetzlichen Auftrag nach</li>
          <li>fördern Wirtschaft, Forschung und Zivilgesellschaft, weil dadurch Geschäftsmodelle,
            Produkte und innovative Services entstehen können
          </li>
          <li>werden sichtbar als transparente und moderne Einheiten.</li>
        </ol>
        Diese Unterlage soll dabei helfen, die richtige Lizenz für die
        eigenen offenen Daten auszuwählen, damit der maximale Nutzen sowohl für
        die Datennachnutzung als auch für den Datenbereitsteller selbst entsteht.
      </p>

      <h2>Warum eine Lizenz vergeben?</h2>
      <p>
        Eine Lizenz gibt sowohl dem Datenbereitsteller als auch den Datennutzenden Rechtssicherheit.
        Sie legt fest, welche Bedingungen für die Nutzung der Daten gelten,
        wie zum Beispiel die Nennung des Datenbereitstellers oder die Einschränkung
        der kommerziellen Nutzung. Fehlt eine solche Lizenz,
        ist eine rechtssichere Nachnutzung der Daten nicht möglich,
        da Nutzende nicht prüfen können, welche Bedingungen gelten,
        und welche Rechte der Datenbereitsteller hat.
      </p>

      <p>
        Aus diesen Gründen ist es besonders für offene Daten wichtig,
        eine geeignete Lizenz auszuwählen. Grundsätzlich gilt:
        Je offener die Lizenz, desto besser ist sie auch für den Datenbereitsteller selbst.
        Eine offene Lizenz ermöglicht eine freie Nachnutzung der Daten,
        was zu einer höheren Sichtbarkeit und einem höheren Mehrwert für alle Beteiligten führt.
      </p>

      <div class="callout">
        <h3>Wann habe ich Rechte an Daten?</h3>
        <p>
          Sobald es eine „Schöpfungshöhe“ gibt, also geistige Leistung in Daten oder ein Werk
          geflossen sind,
          gilt Urheberrechtsschutz oder Leistungsschutzrecht. Bei einem Gemälde oder einem Song ist
          das eindeutig.
          Bei Daten, gerade aus dem staatlichen Umfeld, ist das oft weniger klar, denn:
          Reine Fakteninformationen unterliegen meist nicht diesem Schutz
          und sollten daher möglichst frei veröffentlicht werden.
        </p>
      </div>

      <h2>Welche Lizenzen kommen in Frage?</h2>
      <h3>Creative Commons (CC)</h3>

      <p>
        Die “Creative Commons” sind wohl die bekanntesten und am weitesten verbreiteten
        Lizenzen für offene Daten. So populär sind sie vor allem, weil sie sowohl für Nutzende,
        als auch für Bereitsteller durch ihren modularen, klaren Aufbau leicht verständlich sind.
        Diese Möglichkeiten gibt es:
      </p>

      <ul>
        <li>
          <a href="https://creativecommons.org/publicdomain/zero/1.0/deed.de"><b>CC 0 „Creative
            Commons Zero“: </b></a>
          Diese Lizenz erlaubt es Nutzenden, die Daten für beliebige Zwecke verwenden
          (auch kommerziell), ohne Einschränkungen und ohne die Notwendigkeit,
          den Datenbereitsteller zu nennen.
        </li>
        <li>
          <a href="https://creativecommons.org/licenses/by/4.0/deed.de"><b>CC BY „Creative Commons
            Namensnennung“: </b></a>
          Diese Lizenz erlaubt es Nutzenden, die Daten für beliebige Zwecke zu verwenden
          (auch kommerziell), solange der Datenbereitsteller korrekt genannt wird.
          Änderungen müssen dabei deutlich gemacht werden.
        </li>
        <li>
          <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.de"><b>CC BY-SA „Creative
            Commons Namensnennung - Weitergabe unter gleichen Bedingungen“: </b></a>
          Diese Lizenz erlaubt es Nutzenden, die Daten für beliebige Zwecke zu verwenden
          (auch kommerziell), solange der Datenbereitsteller korrekt genannt wird.
          Änderungen müssen dabei deutlich gemacht werden.
          Wenn aus dem veränderten Material neue Beiträge entstehen,
          müssen diese außerdem unter der gleichen Lizenz verfügbar gemacht werden.
        </li>
      </ul>

      <p>
        Es ist auch wichtig zu beachten, dass es verschiedene Versionen der Lizenzen gibt.
        Gegebenenfalls können sich diese untereinander unterscheiden,
        weswegen möglichst auf die aktuelle Version zurückgegriffen werden sollte.
      </p>


      <h3>Datenlizenzen Deutschland</h3>
      <p>
        Weitere Lizenzen, die für offene Daten in Deutschland relevant sind,
        sind die Datenlizenzen Deutschland.
        Auch diese bietet verschiedene Optionen für die Lizenzierung offener Daten:
      </p>

      <ul>
        <li>
          <a href="https://www.govdata.de/dl-de/zero-2-0"><b>dl-zero-de „Datenlizenz Deutschland
            Zero“: </b></a>
          Diese Lizenz erlaubt es Nutzenden, die Daten für beliebige Zwecke verwenden
          (auch kommerziell), ohne Einschränkungen und ohne die Notwendigkeit,
          den Datenbereitsteller zu nennen.
        </li>
        <li>
          <a href="https://www.govdata.de/dl-de/by-2-0"><b>dl-by-de „Datenlizenz Deutschland
            Namensnennung“: </b></a>
          Diese Lizenz erlaubt es Nutzenden, die Daten für beliebige Zwecke zu verwenden
          (auch kommerziell), solange der Datenbereitsteller korrekt genannt wird.
          Änderungen müssen dabei deutlich gemacht werden.
        </li>
      </ul>

      <p>
        Der Unterschied zwischen den Creative-Commons-Lizenzen
        und der Datenlizenz Deutschland besteht darin,
        dass die Datenlizenz Deutschland explizit für die Verwendung von Daten
        in Deutschland entwickelt wurde.
        In Deutschland anwendbar sind aber beide Lizenztypen.
        Auch bei den Datenlizenzen Deutschland gibt es eine Versionierung,
        auf die geachtet werden sollte.
      </p>

      <h3>Weitere Einschränkungen</h3>

      <p>
        Es gibt auch weitere Lizenzen, die zusätzliche Einschränkungen für die Nutzung
        der Daten vorsehen. Ein Beispiel hierfür ist die Creative-Commons-Lizenz mit
        der Option "BY-NC" (nicht-kommerziell) oder "BY-ND" (keine Bearbeitung).
        Diese Einschränkungen bedeuten, dass die Daten nur für nicht-kommerzielle Zwecke
        genutzt werden dürfen bzw. dass die Daten nicht verändert werden dürfen.
      </p>

      <p>
        Wichtig ist aber: die Daten werden dann nicht mehr als offen angesehen,
        weil durch solche Bestimmungen die Nachnutzung der Daten massiv erschwert wird.
        Das kann dazu führen, dass die veröffentlichten Daten gar nicht genutzt werden (können),
        was auch für den Datenbereitsteller Nachteile hat.
      </p>

      <h2>Was empfiehlt Open Data Bayern?</h2>
      <p>
        Wir empfehlen die Verwendung von entweder CC 0 oder CC BY.
        Der Grund dafür ist, dass diese Lizenzen international etabliert sind
        und eine größtmögliche Freiheit bei der Nachnutzung von Daten bieten,
        was wiederum den maximalen Nutzen für alle Beteiligten bedeutet.
      </p>
      <p>
        Hier sollte allerdings beachtet werden, dass selbst bei der Verwendung
        der CC BY-Lizenz die Nachnutzung manchmal schwierig sein kann.
        Zum Beispiel müsste ein Chatbot, der Daten mit CC BY verarbeitet,
        jedes Mal umfangreiche Quellenangaben machen, was in der Praxis kaum durchführbar ist.
        Aus diesem Grund ist es wichtig, bei der Wahl der Lizenz nicht nur
        die Grundsätze der Offenheit zu berücksichtigen,
        sondern auch die praktischen Anforderungen im Hinblick auf die Nachnutzung der Daten.
        Grundsätzlich gilt: Wer darauf setzt, dass aus eigenen Daten Mehrwert,
        Geschäftsmodelle und neue Produkte entstehen,
        sollte die Daten so frei wie möglich veröffentlichen.
      </p>

      <h2>Wie vergebe ich eine Lizenz?</h2>
      <p>
        Um eine Lizenz für offene Daten zu vergeben,
        reicht es die Lizenzangabe entweder auf der Website oder innerhalb der Datei anzugeben.
        Bei Open Data Bayern wird die Lizenz in der Beschreibung des Datensatzes erfasst.
        Diese ist standardisiert und maschinenlesbar,
        sodass den Nutzern bei der Auswahl und Nachnutzung
        der Daten auf Open Data Bayern geholfen wird.
      </p>

      <h2>Weitere Ressourcen / Quellen</h2>
      <ul>
        <li>
          <a href="https://creativecommons.org/licenses/?lang=de">Creative Commons - Mehr über
            Lizenzen</a>
        </li>
        <li>
          <a href="https://www.govdata.de/lizenzen">GovData - Datenlizenz Deutschland</a>
        </li>
      </ul>
    </article>
  </div>
</template>
<script>
export default {
  name: "Licenses",
  metaInfo() {
    const title = "Welche Lizenz ist die richtige für meine offene Daten?";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
