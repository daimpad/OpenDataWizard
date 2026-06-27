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
      <h1>Was ist Open Data?</h1>
      <p>
        Daten sind das Fundament des digitalen Zeitalters:
        egal ob bei der Entwicklung neuer Technologien oder bei der Entwicklung innovativer Geschäftsmodelle.
        Sie werden deswegen auch in immer größerer Menge generiert und gesammelt,
        was gleichzeitig mit Fragen und Herausforderungen verbunden ist.
        Betrachtet man speziell Daten, die für die Öffentlichkeit interessant sind,
        ist es zudem oft unklar, welche Daten überhaupt verfügbar sind und wie auf diese zugegriffen werden kann.
        Außerdem muss auch geklärt werden, welche Daten durch den Nutzer überhaupt weiterverwendet werden können.
      </p>
      <p>
        Die Antwort darauf sind offene Daten (Englisch: Open Data),
        welche in diesem Artikel genauer betrachtet werden.
        Um diese genauer zu definieren, können wir die Definition des Begriffs heranziehen:
      </p>
      <div class="callout">
        <p>
          „Daten sind offen, wenn sie von allen frei genutzt, weiterverwendet und geteilt werden können -
          eingeschränkt höchstens durch Maßnahmen, die Ursprung und Offenheit der Daten bewahren.“
          – nach der <a href="https://opendefinition.org/">Open Definition</a>
        </p>
      </div>
      <p>
        Die Betonung liegt dabei auf der Offenheit und Freiheit bei der Nutzung,
        Weiterverbreitung und Bearbeitung der Daten.
        Das bedeutet, dass Nutzerinnen und Nutzer die Möglichkeit haben,
        die Daten in verschiedensten Formen zu verwenden, sei es für persönliche Zwecke,
        kommerzielle Anwendungen, wissenschaftliche Forschung oder zur Entwicklung neuer Innovationen.
      </p>
      <p>
        Oftmals gibt es auch einen gesetzlichen Auftrag zur Bereitstellung von offenen Daten,
        etwa durch Transparenzgesetze oder Richtlinien. Regierungen, Behörden und öffentliche Institutionen
        spielen hier eine wichtige Rolle, indem sie Daten, die im öffentlichen Interesse liegen,
        der Öffentlichkeit zugänglich machen.
        Dies kann dazu beitragen, die Transparenz und Rechenschaftspflicht in Regierungen zu stärken,
        die Bürgerbeteiligung zu fördern und Innovationen voranzutreiben.
      </p>
      <p>
        Unser Ziel ist es ist es, mit Open Data Bayern eine Plattform bereitzustellen,
        auf der wir offene Daten aus Bayern sammeln, die für jeden zugänglich sind.
        Durch die Zusammenstellung und Bereitstellung von offenen Daten möchten wir die Chancen und Vorteile nutzen,
        die sich aus der Veröffentlichung und Nutzung offener Daten ergeben.
      </p>
      <p>
        Aus welchem Bereich offene Daten stammen können, ist praktisch unbegrenzt.
        Einige dieser Beispiele sind Ihnen bestimmt auch schon begegnet:
      </p>
      <ul>
        <li>
          <b>Regierungsdaten</b> fördern die Transparenz und die politische Rechenschaftspflicht.
          Dazu zählen etwa Bevölkerungsstatistiken oder Wahlergebnisse.
        </li>
        <li>
          <b>Geografische Daten</b>, wie Landkarten, Standortinformationen und Geoinformationssysteme
          sind wichtig für den Betrieb von Navigations-Apps, bei städtischer Planung oder bei der Umweltüberwachung.
        </li>
        <li>
          <b>Wetterdaten</b>, wie Temperatur, Niederschlagsmengen oder Luftqualität, können in Wettervorhersagen,
          Klimaanalysen und Umweltstudien genutzt werden.
        </li>
        <li>
          <b>Gesundheitsdaten</b> geben nicht-personenbezogene Informationen über Krankheitsstatistiken,
          medizinische Versorgungseinrichtungen oder Gesundheitsrisiken, wodurch Forscher,
          Gesundheitsexperten und die Öffentlichkeit auf wichtige Informationen zugreifen,
          um die Gesundheit und das Wohlbefinden der Bevölkerung zu verbessern.
        </li>
      </ul>
      <p>
        Auch auf Open Data Bayern kann auf Daten aus diesen Bereichen zugegriffen werden,
        indem man diese nach der jeweiligen Kategorie filtert.
      </p>
      <p>
        Ein großer Vorteil offener Daten ist, dass diese für alle beteiligten Parteien von Nutzen sind.
        So wird für die Bürgerinnen und Bürger selbst Transparenz geschaffen,
        da diese mit Hilfe offener Daten politische Entscheidungen nachvollziehen
        und damit die demokratische Teilhabe stärken können.
        Für die Wirtschaft ergibt sich ein großes Potential,
        da mit offenen Daten neue Investitionen ermöglicht werden können.
        Auch die Verwaltung profitiert durch einen erleichterten Informationszugang,
        wodurch die Zusammenarbeit mit anderen Behörden erleichtert wird
        und die Kommunen für den Bürger sichtbar sind.
        Zuletzt gewinnt die Forschung durch die Bereitstellung offener Daten
        eine enorme Palette von Informationen, die für Innovationen notwendig sind
        und den wissenschaftlichen Fortschritt vorantreibt sind.
      </p>

      <p>
        Ein konkretes Beispiel für das Zusammenspiel der Beitragenden ist das
        Copernicus-Programm der EU,
        welches satellitengestützte Erdbeobachtungsdaten öffentlich bereitstellt.
        Durch Bildanalysen kann in der Landwirtschaft algorithmisch vorhergesagt werden,
        wie viel Dünger für eine Fläche benötigt wird, was für Landwirte von großem Nutzen ist und Ressourcen schont.
      </p>
      <p>
        Bevor man offene Daten bereitstellt, gibt es noch ein paar Punkte, die man beachten sollte.
        Am wichtigsten ist, dass die Daten frei nutzbar sind und weiterverarbeitet werden können,
        was durch offene Lizenzen geregelt wird. Dies wird durch in unserem
        <router-link :to="`/info/licenses`">gesonderten Beitrag</router-link> genauer erklärt.
        Ein weiterer Faktor ist die technische Umsetzung. Wichtig ist zum Beispiel,
        dass die Daten maschinenlesbar sind und so automatisiert verarbeitet werden können.
        Auch für dieses Thema haben wir <router-link :to="`/info/provisioning`">einen Beitrag</router-link> vorbereitet.
      </p>
      <p>
        Das Ziel von Open Data Bayern ist es, eine Schnittstelle für alle Beteiligten zur Verfügung zu stellen
        und unsere Vision zu verwirklichen.
        Gemeinsam können wir dafür sorgen, dass durch offene Daten ein besseres Bayern geschaffen wird:
        Einfach, vertrauenswürdig und vernetzt.
      </p>
      <p>
        Hoffentlich haben wir auch Sie in diesem Beitrag von offenen Daten überzeugt.
        Haben Sie weitere Fragen oder möchten Sie sogar schon selbst ihre Daten offen bereitstellen?
        Kommen Sie auf uns zu! Wir haben ein offenes Ohr und unterstützen Sie gerne.
        Weitere Beratungsmöglichkeiten finden Sie auch in unserem <a href="/info">Beratungsangebot</a>
      </p>

      <h2>Weitere Ressourcen / Quellen</h2>
      <ul>
        <li>
          <a href="https://opendefinition.org/od/2.1/de/">Open Definition</a>
        </li>
        <li>
          <a href="https://www.bpb.de/themen/daten/opendata/64055/was-sind-offene-daten/">bpb - Was sind offene Daten?</a>
        </li>
        <li>
          <a href="https://www.copernicus.eu/de">Copernicus - Europas Auge für die Erde</a>
        </li>
      </ul>
    </article>
  </div>
</template>

<script>
export default {
  name: "Definition",
  metaInfo() {
    const title = "Was ist eigentlich Open Data?";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
