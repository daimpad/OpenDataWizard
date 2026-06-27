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
      <h1>Wie beschreibe ich Datensätze?</h1>
      <p>
        Um offene Daten bereitzustellen, ist es zunächst notwendig, geeignete Datensätze zu
        identifizieren,
        sowie diese lizenztechnisch abzuklären. Wie sehen nun die technischen Schritte aus,
        um die Daten an Open Data Bayern anzubinden?
      </p>
      <p>
        Zunächst ist es wichtig, dass auf Open Data Bayern der eigentliche Datensatz mithilfe
        von „Metadaten“ beschrieben wird. Diese geben wertvolle Informationen über Inhalt und
        des Datensatzes und sind notwendig, damit dieser auf der Plattform korrekt angezeigt wird
        und richtig verwendet werden kann. Dazu zählen zum Beispiel Felder wie der Autor,
        das Dateiformat oder die gewählte Lizenz.
        Optional können auch weitere Felder bereitgestellt werden,
        wie etwa die Aktualität der Daten oder ein dazugehöriger Standort.
      </p>
      <p>
        Es gibt verschiedene Standards, die für solche Metadaten verwendet werden.
        Auf Open Data Bayern und meistens in Deutschland wird der Metadatenstandard DCAT-AP.de
        verwendet.
        Dieser definiert eine standardisierte Struktur und Elemente für die Beschreibung von
        Metadaten.
        Man unterscheidet zwischen Pflichtfelder, die bei einem Datensatz zwingend angegeben werden
        müssen,
        zwischen empfohlenen Feldern, die für eine optimale Nutzung angegeben werden sollten,
        und optionalen Felder, die freiwillig angegeben werden können.
        Beispiele für Pflichtfelder sind die gewählte Lizenz und der Herausgeber.
        Der Einsatz des DCAT-AP.de-Standards erleichtert die Interoperabilität zwischen
        verschiedenen Datenkatalogen und fördert die einheitliche Veröffentlichung von Metadaten in
        Deutschland.
        Unten verlinkt ist die Dokumentation von DCAT-AP.de, in dem die einzelnen Felder gesondert
        dokumentiert sind.
      </p>
      <p>
        Für die Anbindung von Datensätzen an Open Data Bayern (ODB) gibt es zwei verschiedene
        Möglichkeiten.
        Eine Option ist es, den Datensatz manuell direkt im ODB-Portal anzulegen.
        Dabei werden die Metadaten selbstständig ausgefüllt, was bedeutet,
        dass die auszufüllenden Felder automatisch in DCAP-AP.de vorgegeben werden.
        Gleichzeitig ist es bei dieser Methode aber auch wichtig, die Informationen aktuell zu
        halten,
        weswegen sie eine gewisse Pflege und regelmäßige Aktualisierung der Metadaten erfordert.
      </p>
      <p>
        Alternativ kann die Anbindung der Datensätze automatisch erfolgen.
        Hierbei wird eine Schnittstelle (API) genutzt. Dies ist oft der Fall,
        wenn ein bestehendes Datensystem verwendet wird, die den Export im DCAT-AP.de-Format
        ermöglicht.
        Dadurch kann man einfach Daten direkt an Open Data Bayern anbinden, sowie diese stets
        aktuell halten,
        sobald eine Änderung erfolgt.
        Die technische Spezifikation der API von Open Data Bayern ist ebenfalls unten verlinkt.
      </p>
      <p>
        Was auf den ersten Blick schwer erscheinen kann, ist oft weniger kompliziert, als es
        aussieht.
        Aus diesem Grund bieten wir auch ein Beratungsangebot,
        um Sie bei der technischen Anbindung der Daten an Open Data Bayern zu unterstützen.
        So können Fragen zur Datenstruktur, Dateiformaten,
        Schnittstellen oder zur Integration mit bestehenden Systemen geklärt werden.
        Das Beratungsangebot hilft dabei, mögliche technische Hürden zu überwinden und
        sicherzustellen,
        dass die Veröffentlichung der Daten in einer geeigneten technischen Form erfolgt,
        um einen reibungslosen Zugriff und eine optimale Nutzbarkeit zu gewährleisten.
        Kommen Sie gerne auf uns zu!
      </p>
      <h2>Weitere Ressourcen / Quellen</h2>
      <ul>
        <li>
          <a href="https://www.dcat-ap.de/def/">Dokumentation von DCAT-AP.de</a>
        </li>
        <li>
          <a href="/api/hub/search/">Link zu unserer Schnittstelle (API)</a>
        </li>
      </ul>
    </article>
  </div>
</template>

<script>
export default {
  name: "Metadata",
  metaInfo() {
    const title = "Wie können Datensätze und deren Beschreibung technisch veröffentlicht werden?";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
