import UniversalPiwik from '@piveau/piveau-universal-piwik';

function getCookie(cname) {
  const name = cname + "=";
  const decodedCookie = decodeURIComponent(document.cookie);
  const ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

export function setupPiwik(app, router, env) {
  const { isPiwikPro, siteId, trackerUrl } = env.tracker;
  app.use(UniversalPiwik, {
    router,
    isPiwikPro,
    trackerUrl,
    siteId,
    debug: process.env.NODE_ENV === 'development',
    useSuspendFeature: true,
    pageViewOptions: {
      // Set this to true as long as navigating to the /datasets/ route
      // adds a 'minScore' query to prevent duplicated tracking
      useDatasetsMinScoreFix: false,
      // Send empty dataset metadata for every page view
      // See https://gitlab.fokus.fraunhofer.de/piveau/organisation/piveau-scrum-board/-/issues/2098
      beforeTrackPageView: (to, from, tracker) => {
        if (to.name !== 'DatasetDetailsDataset') {
          tracker.trackDatasetDetailsPageView(null, null, {
            dataset_AccessRights: '',
            dataset_AccrualPeriodicity: '',
            dataset_Catalog: '',
            dataset_ID: '',
            dataset_Publisher: '',
            dataset_Title: '',
          });
        }
        if (getCookie('noTracking') == "true") {
          window._paq.push(['requireCookieConsent'])
        }
      },
    },
  });
}
