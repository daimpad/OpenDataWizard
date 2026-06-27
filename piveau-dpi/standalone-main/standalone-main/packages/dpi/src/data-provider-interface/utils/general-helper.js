import { isEmpty, isNil, has, cloneDeep } from "lodash";
import axios from "axios";

export function getRepresentativeLocaleOf(prop, userLocale, fallbacks) {
  if (!prop || isNil(prop) || (!isObject(prop) && !isString(prop))) return undefined;
  // Check if prop is only a string without translations
  if (isString(prop)) return prop;
  // Use language setting of user
  if (has(prop, userLocale)) return userLocale;
  // Iterate over given fallback languages
  if (fallbacks && isArray(fallbacks)) {
    const foundLang = fallbacks.find(lang => lang
      && isString(lang)
      && has(prop, lang.toLowerCase()));
    if (foundLang) return foundLang;
  }
  // Use the first language in the given property if none of the languages is present
  const key = Object.keys(prop)[0];
  if (key) return key;
  // Use default text if prop does not have any items
  return undefined;
}

/**
 * @description         Checks if a translation for the given prop parameter is available and returns it in the following priority order:
 *                      1. User set locale
 *                      2. Given fallback languages
 *                      3. Any available language
 * @param { Object }    prop - The object that should contain the translations
 * @param { String }    userLocale - The currently set locale.
 * @param { [String] }  fallbacks - The fallback languages to check for, when given locale is not available in given prop
 * @returns { String }  A translated text.
 */
export function getTranslationFor(prop, userLocale, fallbacks) {
  const locale = getRepresentativeLocaleOf(prop, userLocale, fallbacks);
  return locale
    ? prop[locale]
    : undefined;
}

/**
 * Merges multiple Objects nested within an object into one main objects with al key-value-pairs originally located within the nested objects
 * @param {Object} data Object containing nested objects
 * @returns Object with key-value pairs merged from nested objects
 */
function mergeNestedObjects(data) {
  let mergedObject = {};
  for (const key in data) {
    mergedObject = Object.assign(mergedObject, data[key]);
  }
  return mergedObject;
}

/**
 *
 * @param {*} prefix
 * @returns
 */
function addNamespace(prefix, dpiConfig) {
  // the prefix had the following format: namespace:property (e.g. dct:title)
  // the short version of the namespace noe should be replaced by the long version (e.g. http://purl.org/dc/terms/title)
  let fullDescriptor;
  const colonIndex = prefix.indexOf(":");

  // there are also prefixes with no namespace which should sty the same
  if (colonIndex !== -1) {
    const namespaceAbbreviation = prefix.substr(0, colonIndex);
    const propertyName = prefix.substr(colonIndex + 1);

    // the long version of the namespace is saved within the context.json (config)
    // there is an object containing the namespace abbreviation(key) and the corresponding value is the long version of the namespace

    const longNamespace = dpiConfig.prefixes[namespaceAbbreviation];
    fullDescriptor = `${longNamespace}${propertyName}`;
  } else {
    fullDescriptor = prefix;
  }

  return fullDescriptor;
}

/**
 * Removes long namespace and replaces it with the abbreviation of the namespace
 * @param {*} longValue Long value with long namespace (e.g. https://....#type)
 * @returns Returns value with short namespace (e.g. rdf:type)
 */
function removeNamespace(longValue, dpiConfig) {
  let lastIndex;

  // long namespace either ends with an # or a \
  if (longValue.includes("#")) {
    lastIndex = longValue.lastIndexOf("#");
  } else {
    lastIndex = longValue.lastIndexOf("/");
  }

  const shortValue = longValue.substr(lastIndex + 1);
  const longPrefix = longValue.substr(0, lastIndex + 1);
  const shortPrefix = Object.keys(dpiConfig.prefixes).find(
    (key) => dpiConfig.prefixes[key] === longPrefix
  );

  return `${shortPrefix}:${shortValue}`;
}

/**
 * Returns list of keys as shortned version from given data
 * @param {*} data An array of quads with keys as predicate
 * @returns Array of shortened keys
 */
function getNestedKeys(data, dpiConfig) {
  const keys = [];

  for (let el of data) {
    keys.push(removeNamespace(el.predicate.value, dpiConfig));
  }

  return keys;
}

/**
 * Creates a random string
 * @param {*} length Length of string to be created
 * @returns String formed of random characters with given length
 */
function makeId(length) {
  var result = "";
  var characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  var charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}

/**
 * Methods checks if given string is an Url
 * @param {*} string String to test
 * @returns Boolean determining if given string is an Url
 */
function isUrl(string) {
  let url;
  try {
    url = new URL(string);
  } catch (_) {
    return false;
  }
  return url.protocol === "http:" || url.protocol === "https:";
}

/**
 * Fetches data from given endpoint using token and returns data
 * @param {*} url Endpoint from where to fetch the data
 * @param {*} token User token for authentication (if needed)
 * @returns Returns promise of fetched data
 */
async function fetchLinkedData(endpoint, token) {
  let response;
  let requestOptions;

  // if token is given, provide token (for drafts and other non-public elements)
  if (token !== "") {
    requestOptions = {
      method: "GET",
      headers: {
        Authorization: `Bearer ${token}`,
      },
      url: endpoint,
    };
  } else {
    requestOptions = {
      method: "GET",
      url: endpoint,
    };
  }

  try {
    response = fetch(endpoint, requestOptions)
      .then((response) => {
        const reader = response?.body?.getReader();
        return new ReadableStream({
          start(controller) {
            // The following function handles each data chunk
            function push() {
              // "done" is a Boolean and value a "Uint8Array"
              reader?.read().then(({ done, value }) => {
                // If there is no more data to read
                if (done) {
                  controller.close();
                  return;
                }
                // Get the data and send it to the browser via the controller
                controller.enqueue(value);
                // Check chunks by logging to the console
                push();
              });
            }

            push();
          },
        });
      })
      .then((stream) =>
        new Response(stream, {
          headers: { "Content-Type": "text/html" },
        }).text()
      );
  } catch (err) {
    // TODO: Handle (network) errors
    throw Error(`Error occured during fetching endpoint: ${endpoint}`);
  }
  return response;
}

/**
 * Exracts keynames (e.g. dct:title) using the page-content-config for each element
 * @param {*} property Property (datasets/distributions/catalogues)
 * @param {*} formDefinitions Form definition of properties including name
 * @param {*} pageContent Config file containing definition of which property will be displayed on which page
 * @returns Object containing keys of properties for each page
 */
function getPagePrefixedNames(property, formDefinitions, pageContent) {
  const prefixedNames = {
    datasets: {},
    distributions: {},
    catalogues: {},
  };

  // get property keys for each page
  for (let pageName in pageContent[property]) {
    prefixedNames[property][pageName] = [];
    const propertyKeys = pageContent[property][pageName];
    for (
      let propertyindex = 0;
      propertyindex < propertyKeys.length;
      propertyindex++
    ) {
      const propertyName = propertyKeys[propertyindex];

      console.log(formDefinitions);

      const prefixedName = formDefinitions[property]?.[propertyName]?.name || undefined;
      
      if (prefixedName !== undefined)
        prefixedNames[property][pageName].push(prefixedName);
    }
  }

  return prefixedNames;
}

function isValid(id) {
  return /^[a-z0-9-]*$/.test(id);
}

/**
 * Get file id from accessUrl, if it is a file upload url.
 * accessUrls are file upload urls, iff they start with fileUploadUrl.
 * @param {string} accessUrl
 * @param {string} fileUploadUrl
 * @returns {string|null}
 */
function getFileIdByAccessUrl({ accessUrl, fileUploadUrl }) {
  const accessUrlWithTrailingSlash = accessUrl.endsWith("/")
    ? accessUrl
    : `${accessUrl}/`;
  const fileUploadUrlWithTrailingSlash = fileUploadUrl.endsWith("/")
    ? fileUploadUrl
    : `${fileUploadUrl}/`;

  // Check if accessUrl starts with fileUploadApi
  if (accessUrlWithTrailingSlash.startsWith(fileUploadUrlWithTrailingSlash)) {
    const accessUrlParts = accessUrlWithTrailingSlash.split("/");
    const fileId = accessUrlParts[accessUrlParts.length - 2];

    return fileId || null;
  }

  return null;
}

/**
 * Adds given key to format type
 * @param {String} key
 * @param {String} format
 * @param {String} property
 * @param {Object} typeDefinition
 */
function addKeyToFormatType(key, format, property, typeDefinition) {
  typeDefinition[format][property].push(key);
}

/**
 * Removes key from format type
 * @param {String} key
 * @param {String} format
 * @param {String} property
 * @param {Object} typeDefinition
 */
function removeKeyFromFormatType(key, format, property, typeDefinition) {
  typeDefinition[format][property].splice(
    typeDefinition[format][property].indexOf(key),
    1
  );
}

function propertyObjectHasValues(objectData) {
  let objectHasValues = false;
  if (!isNil(objectData) && !isEmpty(objectData)) {
    // language tag is always given
    let copiedData = cloneDeep(objectData);

    if (has(copiedData, "@language")) {
      delete copiedData["@language"];
    }

    // removing all falsy values (undefined, null, "", '', NaN, 0)
    const actualValues = Object.values(copiedData).filter((el) => el); // filters all real values
    if (!isEmpty(actualValues)) {
      // there are keys containing an object or array as value
      for (let valueIndex = 0; valueIndex < actualValues.length; valueIndex++) {
        // if at least one elemnt within the array is set, return true
        const currentValue = actualValues[valueIndex];

        // testing content of array
        if (Array.isArray(currentValue)) {
          // there are only objects wihtin those arrays
          for (let arrIndex = 0; arrIndex < currentValue.length; arrIndex++) {
            if (propertyObjectHasValues(currentValue[arrIndex]))
              objectHasValues = true;
          }
        } else if (typeof currentValue === "object") {
          // testing content of object
          if (propertyObjectHasValues(currentValue)) objectHasValues = true;
        } else {
          objectHasValues = true;
        }
      }
    }
  }

  return objectHasValues;
}

function propertyHasValue(data) {
  let isSet = false;

  if (data !== undefined && data !== "" && !isEmpty(data) && !isNil(data)) {
    // testing array data
    if (Array.isArray(data)) {
      // there are arreay of objects or arrays of values
      if (data.every((el) => typeof el === "string")) {
        isSet = !isEmpty(data.filter((el) => el));
      } else if (data.every((el) => typeof el === "object")) {
        for (let index = 0; index < data.length; index++) {
          // if at least one array element is set, return true
          if (propertyObjectHasValues(data[index])) isSet = true;
        }
      }
    } else if (typeof data === "object") {
      // testing object data
      isSet = propertyObjectHasValues(data);
    } else {
      isSet = true;
    }
  }

  return isSet;
}

/**
 *
 */
async function requestUriLabel(uri, dpiConfig, envs) {
  // get vocabulary by finding vocab-url within given URI
  const voc = Object.keys(dpiConfig.vocabPrefixes).find((key) =>
    uri.includes(dpiConfig.vocabPrefixes[key])
  );

  try {
    let req;

    // vocabularies for spdx checksum and inana-media-types are structured differently in the backend then other vocabularies
    if (voc === "iana-media-types" || voc === "spdx-checksum-algorithm") {
      req = `${envs.api.baseUrl}vocabularies/${voc}`;
    } else {
      const value = uri.replace(dpiConfig.vocabPrefixes[voc], "");
      req = `${envs.api.baseUrl}vocabularies/${voc}/${value}`;
    }

    return new Promise((resolve, reject) => {
      axios
        .get(req)
        .then((res) => {
          resolve(res);
        })
        .catch((err) => {
          reject(err);
        });
    });
  } catch (error) {
    //
  }
}

/**
 *
 */
async function getUriLabel(uri, dpiConfig, locale, envs) {
  let URIlabel;

  const voc = Object.keys(dpiConfig.vocabPrefixes).find((key) =>
    uri.includes(dpiConfig.vocabPrefixes[key])
  );

  // if vocabulary iana media type or spdx checksum endpoint returns values in a different way
  let vocMatch =
    voc === "iana-media-types" || voc === "spdx-checksum-algorithm";

  await requestUriLabel(uri, dpiConfig, envs).then((response) => {
    let result = vocMatch
      ? response.data.result.results
          .filter((dataset) => dataset.resource === uri)
          .map((dataset) => dataset.pref_label)[0].en
      : getTranslationFor(response.data.result.pref_label, locale, []);

    URIlabel = result;
  });

  return URIlabel;
}

export default {
  mergeNestedObjects,
  addNamespace,
  makeId,
  isUrl,
  fetchLinkedData,
  getPagePrefixedNames,
  getNestedKeys,
  removeNamespace,
  getFileIdByAccessUrl,
  addKeyToFormatType,
  removeKeyFromFormatType,
  propertyHasValue,
  getUriLabel,
};
