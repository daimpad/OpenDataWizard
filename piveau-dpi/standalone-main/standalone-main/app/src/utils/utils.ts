import type { AxiosInstance, AxiosStatic } from 'axios';
import { KNOWN_DISALLOWED_CATALOG_IDS, KNOWN_ODP_CATALOG_IDS } from './constants';

export function getSubdomainCatalogIdFromUrl(url: string, disallowedCatalogIds : string[] = KNOWN_DISALLOWED_CATALOG_IDS): string {
  const host = new URL(url).host;
  const maybeSubdomain = getLeftmostSubdomain(host);
  return disallowedCatalogIds.includes(maybeSubdomain) ? '' : maybeSubdomain;
}

export function formatDatetime(date: string) {
  // return new Date(this.value).toLocaleDateString('de', { year: 'numeric', month: '2-digit', day: '2-digit' });
  // validate and format date to dd.mm.yyyy
  const dateObj = new Date(date);
  if (isNaN(dateObj.getTime())) {
    return '';
  }

  return dateObj.toLocaleDateString('de', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

/**
 * Maps some locale codes to German region
 */
export const localeCodeToGermanMini = {
  // Just add a bunch of European countries.
  // We'll just need 'de' anyways, right?
  'de': 'Deutsch',
  'en': 'Englisch',
  'fr': 'Französisch',
  'es': 'Spanisch',
  'it': 'Italienisch',
  'pt': 'Portugiesisch',
  'nl': 'Niederländisch',
  'pl': 'Polnisch',
  'cs': 'Tschechisch',
  'da': 'Dänisch',
  'fi': 'Finnisch',
  'el': 'Griechisch',
  'hu': 'Ungarisch',
  'ga': 'Irisch',
  'lv': 'Lettisch',
  'lt': 'Litauisch',
  'mt': 'Maltesisch',
  'ro': 'Rumänisch',
  'sk': 'Slowakisch',
  'sl': 'Slowenisch',
  'sv': 'Schwedisch',
  'hr': 'Kroatisch',
  'bg': 'Bulgarisch',
  'et': 'Estnisch',
}

export function getLeftmostSubdomain(hostname: string) {
  // Strip port number if present
  const domainWithoutPort = hostname.split(':')[0];

  // Construct regex patterns
  const secondLevelPattern = '(co\\.uk|com\\.au|co\\.nz|co\\.za|com\\.br|staging\\.bydata\\.de|bydata\\.de)';
  // Update the generalPattern to match any subdomain before the main domain and TLD
  const generalPattern = new RegExp(`^(www\\.)?(.*?)\\.(?:[^\\.]+\\.${secondLevelPattern}|[^\\.]+\\.[^\\.]+)$`);
  const localPattern = /^([^.]+)\.local$/;

  // Match against .local pattern
  const localMatch = domainWithoutPort.match(localPattern);
  if (localMatch) {
      return localMatch[1];
  }

  // Match the subdomain for general domains
  const match = domainWithoutPort.match(generalPattern);

  if (match && match[2] && match[2] !== 'www') {
      // Split the subdomains and return the leftmost one
      return match[2].split('.')[0];
  }

  return '';
}

export async function isOdp(catalogId: string, hubSearchApiBaseUrl: string) {
  if (KNOWN_DISALLOWED_CATALOG_IDS.includes(catalogId)) {
    return false;
  }

  if (KNOWN_ODP_CATALOG_IDS.includes(catalogId)) {
    return true;
  }

  const url = `${hubSearchApiBaseUrl}catalogues/${catalogId}`;
  const response = await fetch(url, {method: 'GET'});
  return response.ok;
}

/**
 * Patches the Axios instance to include the openDataPresence facet in dataset search requests made to the hub search API.
 * @param axiosInstance - The Axios instance or static object.
 * @param hubSearchApiBaseUrl - The base URL of the hub search API.
 * @param openDataPresence - The value of the openDataPresence facet.
 */
export function patchAxiosDatasetSearchWithOpenDataPresenceFacet({ axiosInstance, hubSearchApiBaseUrl, openDataPresence }: { axiosInstance: AxiosInstance | AxiosStatic; hubSearchApiBaseUrl: string; openDataPresence: string; }) {
  const interceptor = axiosInstance.interceptors.request.use(async (config) => {
    const isHubSearchApi = config?.url?.includes(hubSearchApiBaseUrl);
    const searchParams = config.params
    const isDatasetSearch = searchParams?.filter === 'dataset';

    if (!searchParams || !hubSearchApiBaseUrl || !isHubSearchApi || !isDatasetSearch) return config;

    const facets = searchParams?.facets

    const superCatalogFacet = facets?.['superCatalog']?.[0] || '';

    const facetsWithOpenDataPresence = {
      ...facets,
      ...!!superCatalogFacet && { open_data_presence: [openDataPresence || superCatalogFacet] },
      // and also exclude superCatalog from the facets
      superCatalog: [],
    };

    config.params.facets = facetsWithOpenDataPresence;
    config.params.facetOperator = 'OR';

    return config;
  });

  return interceptor;
}
