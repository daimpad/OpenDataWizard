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
      <h1>Was macht gute Daten aus?</h1>
      <p>
        Um offene Daten bereitzustellen, bedarf es oft nicht viel Aufwand.
        Dennoch gibt es einige Sachen, die davor geklärt werden müssen.
        Zunächst müssen die zu veröffentlichenden Daten selbst bestimmt werden.
        Auch ist es wichtig, in welchem Format und in welcher Qualität diese Daten vorliegen.
        In diesem Beitrag erklären wir die notwendigen Schritte bei der Datenidentifikation und
        bei der Qualitätsbewertung, sodass diese auf Open Data Bayern optimal genutzt werden können.
      </p>
      <p>
        Bei der Identifikation der Daten sind zunächst keine Grenzen gesetzt:
        für die Veröffentlichung geeignet sind im Prinzip alle Datensätze,
        die für die Allgemeinheit von Interesse sein können. Dabei ist es egal,
        ob es sich eher um generelle oder um spezifische Datensätze handelt.
        Gemäß der Richtlinie über offene Daten der EU, die unten verlinkt ist,
        sind die Mitgliedsstaaten außerdem gesetzlich verpflichtet,
        auf nationaler Ebene besonders hochwertige Datensätze als offene Daten zu teilen.
        Dieser Schritt zielt darauf ab, den Zugang zu bedeutenden Informationen zu erleichtern und
        die Transparenz sowie die Innovationsmöglichkeiten in der Gesellschaft zu fördern.
      </p>
      <p>
        Doch woran erkennt man, in was für einer Qualität ein Datensatz vorliegt?
        Generell ist es wichtig, die Daten so anzubieten, dass sie möglichst einfach verarbeitet werden können.
        Um bestimmen zu können, ob ein qualitativ hochwertiger Datensatz vorliegt,
        wurde von Tim Berners-Lee (dem Erfinder des World Wide Webs) das 5-Sterne-Modell entwickelt,
        mit welchem die Datenqualität bewertet werden kann.
        Dieses besteht aus den folgenden Punkten:
      </p>
      <ul>
        <li>
          <b>1 Stern:</b> Der wichtigste Punkt ist, dass die Daten unter einer offenen Lizenz vorliegen,
          die es anderen erlaubt, sie frei zu nutzen, zu verändern und weiterzuverbreiten.
          Mehr Dazu finden Sie in unserem <router-link :to="`/info/licenses`">Beitrag über Lizenzen</router-link>.
        </li>
        <li>
          <b>2 Sterne:</b> Die Daten liegen in einem strukturierten Format vor.
          Das bedeutet, dass sie in einer geordneten und einheitlichen Weise organisiert sind
          und somit leichter verarbeitet werden können.
          Ein Beispiel dafür ist das Anlegen einer Tabelle anstelle eines eingescannten Bildes.
          Am besten dafür geeignet sind maschinenlesbare Formate.
          Mehr Informationen dazu finden sie auch in unserem <router-link :to="`/info/metadata`">gesonderten Beitrag</router-link>.
        </li>
        <li>
          <b>3 Sterne:</b> Die Daten werden in einem offenen Format zur Verfügung gestellt.
          Dies bedeutet, dass man auf keine proprietäre  Software angewiesen ist,
          um auf das Datenset zugreifen zu können.
          Ein Beispiel dafür ist die Formatierung einer Tabelle im freien CSV-Format anstelle des XLS-Formats,
          welches meistens eine Excel-Installation voraussetzt.
        </li>
        <li>
          <b>4 Sterne:</b> Den Daten ist eine URI (Uniform Resource Identifier) zugewiesen,
          die es ermöglicht, sie über das Internet eindeutig zu identifizieren und zu verlinken.
          Dadurch kann auch extern auf das Datenset verlinkt werden.
          Im Prinzip heißt das einfach, dass man über einen Link direkt auf die Daten zugreifen kann.
        </li>
        <li>
          <b>5 Sterne:</b> Das Datenset enthält Verlinkungen zu anderen Daten,
          wodurch ein weiterer Kontext hergestellt wird. Dadurch lassen sich auch weitere Daten entdecken,
          die mit dem eigenen Datenset im Zusammenhang stehen.
        </li>
      </ul>
      <p>
        Damit ein Stern vergeben werden kann, muss das Datenset auch alle darunterliegenden Bedingungen erfüllen.
      </p>
      <p>
        Je höher man das eigene Datenset bewerten kann, desto besser.
        Jedoch sollte man sich auf keinen Fall dadurch beschränken lassen oder
        gar aus diesem Grund auf eine Veröffentlichung verzichten. Hier gilt:
        Es ist besser ein weniger optimiertes Datenset zu veröffentlichen, als es gar nicht zu tun,
        da alle Daten für potenzielle Nutzer wertvoll sind.
        Schließlich kann ein Datenset kann auch nachträglich noch optimiert werden.
        Wenn Sie weitere Fragen zur Identifikation oder Qualität von Daten auf Open Data Bayern haben,
        schreiben Sie uns gerne an! Wir helfen Ihnen in allen Fällen gerne weiter.
      </p>
      <h2>Weitere Ressourcen / Quellen</h2>
      <ul>
        <li>
          <a href="https://5stardata.info/de/">5 ★ OFFENE DATEN</a>
        </li>
        <li>
          <a href="https://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32019L1024">Richtlinie über offene Daten (EUR-Lex)</a>
        </li>
        <li>
          <a href="https://digital-strategy.ec.europa.eu/de/news/commission-defines-high-value-datasets-be-made-available-re-use">Pressemitteilung der EU über hochwertige Datensätze</a>
        </li>
      </ul>
    </article>
  </div>
</template>

<script>
export default {
  name: "Provisioning",
  metaInfo() {
    const title = "Was zeichnet gute offene Daten aus?";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
