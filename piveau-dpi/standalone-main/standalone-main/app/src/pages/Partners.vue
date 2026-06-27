<template>
  <article class="py-0 px-0 my-0 text-center bg-white">
    <div class="py-11 mx-7">
      <h2 class="by-heading-2 text-by-blue-100">Unsere starken Partner</h2>
      <div class="mx-auto" style="max-width: 1280px;">
        <div class="mt-11">
          <h4 class="by-heading-4 text-by-blue-70 mb-0">open bydata wird ermöglicht durch</h4>
          <div class="partner-image-container row align-items-center">
            <div class="col-12 col-xl-4">
              <a href="https://www.bayern.de/">
                <img style="max-width: 336px !important;" src="../assets/img/partnerpage/Logo_col_Staatsregierung@2x.png">
              </a>
            </div>
            <div class="col-12 col-xl-4 d-none d-xl-inline-block">
              <a href="https://www.byte.bayern/"> <span class="byte-logo"/></a>
            </div>
            <div class="col-12 col-xl-4 vertical-margin">
              <a href="https://www.stmd.bayern.de"> <span class="stmd-logo"/></a>
            </div>
            <div class="col-12 col-xl-4 d-xl-none">
              <a href="https://www.byte.bayern/"> <span class="byte-logo"/></a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12 col-lg-6 mt-10">
            <h4 class="by-heading-4 text-by-blue-70 mb-0">Von Beginn an unterstützt von</h4>
            <div class="partner-image-container row align-items-center">
              <div class="col-12 col-sm-6 bottom-margin">
                <a href="https://www.finanzministerium.bayern.de/">
                  <img src="../assets/img/partnerpage/Logo_col_StMFH@2x.png">
                </a>
              </div>
              <div class="col-12 col-sm-6">
                <a href="https://geoportal.bayern.de/geoportalbayern/">
                  <img src="../assets/img/partnerpage/Logo_col_GDI@2x.png">
                </a>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-6 mt-10">
            <h4 class="by-heading-4 text-by-blue-70 mb-0">Entwickelt zusammen mit</h4>
            <div class="partner-image-container row align-items-center">
              <div class="col-12 col-sm-6 bottom-margin">
                <a href="https://www.dksr.city/en/home-en-2/">
                  <img src="../assets/img/partnerpage/Logo_col_DKSR@2x.png">
                </a>
              </div>
              <div class="col-12 col-sm-6">
                <a href="https://www.fokus.fraunhofer.de/">
                  <img src="../assets/img/partnerpage/Logo_col_Fraunhofer-Fokus@2x.png">
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="pt-11 pb-14 bg-by-neutral-5">
      <h4 class="by-heading-4 text-by-blue-80">Datenbereitsteller</h4>
      <div class="continuous-scrolling-text mt-8">
        <div class="marquee">
          <p>
            <span>Bayerisches Landesamt für Umwelt</span>
            <span>Universitätsbibliothek der Technischen Universität München</span>
            <span>Landkreis Cham</span>
            <span>Bayerische Staatsregierung</span>
            <span>Landesamt für Digitalisierung, Breitband und Vermessung</span>
            <span>Stadt Würzburg</span>
            <span>Bayerisches Staatsministerium für Unterricht und Kultus</span>
            <span>Bayerische Landesanstalt für Wald und Forstwirtschaft (LWF)</span>
            <span>Bayerisches Staatsministerium für Gesundheit, Pflege und Prävention</span>
            <span>Oberste Baubehörde im Bayerischen Staatsministerium des Innern, für Bau und Verkehr</span>
            <span>Bayerisches Breitbandzentrum Amberg</span>
            <span>Bayerisches Staatsministerium der Finanzen und für Heimat</span>
            <span>Stadt München</span>
            <span>Bayern Innovativ</span>
            <span>Landkreis Regensburg</span>
            <span>Bayerisches Landesamt für Statistik</span>
          </p>
          <p>
            <span>Bayerisches Landesamt für Umwelt</span>
          </p>
        </div>
      </div>
      <div class="text-by-neutral-100 by-copy-small-semibold mt-5">
        Schließen Sie sich verschiedensten Datenbereitstellern aus ganz Bayern an!
      </div>
      <div class="d-flex justify-content-center">
        <a
          href="/info"
          class="by-btn-secondary-medium-light mt-7">
          <span class="by-copy-large-semibold">Selbst Daten bereitstellen</span>
        </a>
      </div>
    </div>
  </article>
</template>
<script>
export default {
  name: "Partners",
  metaInfo() {
    const title = "Partner - open bydata";
    return {
      titleTemplate(chunk) {
        return title;
      },
    };
  }
}
</script>
