/**
 * @created 11.05.2017
 * @description Contains helper functions for general purposes needed in the Application.
 */

import {
  has,
  isArray,
  isNil,
  isObject,
  isString,
} from 'lodash-es'

/**
 * @description         Returns an array that contains unique values
 *                      of the given properties in the given array.
 * @param {string}    prop   - The key of {array} which values will be unique.
 * @param {[object]}  array - The array to take keys from.
 * @returns { [*] }     A new Array containing unique values of the {arrays} {key} property
 */
function unique(prop, array) {
  // Filter elements in {array} that do not have a {prop} key.
  return [...new Set(array.filter(
    // Remove duplicates by creating a Set and remove items where {prop} has no value.
    item => Object.prototype.hasOwnProperty.call(item, prop) && !!item[prop],
  )
    // Create a new array containing the {prop} values of each given item.
    .map(item => item[prop]))]
}

function getRepresentativeLocaleOf(prop, userLocale, fallbacks) {
  if (!prop || isNil(prop) || (!isObject(prop) && !isString(prop)))
    return undefined
  // Check if prop is only a string without translations
  if (isString(prop))
    return prop
  // Use language setting of user
  if (has(prop, userLocale))
    return userLocale
  // Iterate over given fallback languages
  if (fallbacks && isArray(fallbacks)) {
    const foundLang = fallbacks.find(lang => lang
      && isString(lang)
      && has(prop, lang.toLowerCase()))
    if (foundLang)
      return foundLang
  }
  // Use the first language in the given property if none of the languages is present
  const key = Object.keys(prop)[0]
  if (key)
    return key
  // Use default text if prop does not have any items
  return undefined
}

/**
 * @description         Checks if a translation for the given prop parameter is available and returns it in the following priority order:
 *                      1. User set locale
 *                      2. Given fallback languages
 *                      3. Any available language
 * @param {object}    prop - The object that should contain the translations
 * @param {string}    userLocale - The currently set locale.
 * @param {[string]}  fallbacks - The fallback languages to check for, when given locale is not available in given prop
 * @returns {string}  A translated text.
 */
function getTranslationFor(prop, userLocale, fallbacks) {
  const locale = getRepresentativeLocaleOf(prop, userLocale, fallbacks)
  return locale
    ? prop[locale]
    : undefined
}

/**
 * @description Returns the translation for a facet item
 * @param  {string} fieldId
 * @param {string} facetId
 * @param {string} userLocale
 * @param {string} fallback
 * @returns {string} The translated facet item, if available
 */
function getFacetTranslation(fieldId, facetId, userLocale, fallback) {
  if (fieldId === 'country') {
    const locale = userLocale === 'no' ? 'nb' : userLocale
    const name = getName(facetId, locale)
    // Translation missing because this.$t('message.catalogFacets.euInstitutions') is not working here
    // return isNil(name)
    //   ? (fallback === 'EU institutions' ? this.$t('message.catalogFacets.euInstitutions') : fallback)
    //   : name;
    return isNil(name)
      ? fallback
      : name
  }
  if (isObject(fallback)) {
    return has(fallback, userLocale)
      ? fallback[userLocale]
      : has(fallback, 'en')
        ? fallback.en
        : Object.keys(fallback).length > 0
          ? fallback[0]
          : 'No title available'
  }
  return isNil(fallback)
    ? 'No title available'
    : (isString(fallback)
        ? fallback
        : fallback.toString())
}

/**
 * Truncates a String to a maximum character count of maxChars
 * @param text
 * @param maxChars
 * @param noAppend
 */
function truncate(text, maxChars, noAppend) {
  if (!text)
    return ''
  const trunc = text.substring(0, maxChars)
  if (noAppend || text.length <= maxChars)
    return trunc
  return `${trunc}...`
}

/**
 * normalizing the dataset id
 * @param str string to be normalized
 */
function normalize(str) {
  const normalized = str.normalize('NFKD')
  return normalized.replace('%', '').replace('\\W', '-').replace('-+', '-').toLowerCase()
}

/**
 * remove mailto or tel
 * @param str string
 */
function removeMailtoOrTel(str) {
  return str.replace(/^(mailto|tel):/, '')
}

function replaceHttp(str) {
  try {
    const url = new URL(str)
    if (url.protocol === 'http:') {
      url.protocol = 'https:'
    }
    return url.href
  }
  catch (ex) {
    // Return original string if it is not a valid URL
    return str
  }
}

/**
 * Returns a function that takes an object and returns a modified object
 * where for each dstProp in dstProps holds: object.dstProp === object.srcProp
 *
 * This function aims to help maintain stability against DCAT-AP schema changes
 * by providing alternative keys names for access
 *
 * @example
 * const foo = { foo: 'hello world' };
 * const mirrorFooAsBar = mirrorPropertyFn('foo', 'bar');
 * const mirrored = mirrorFooAsBar(foo);
 * log(mirrored.foo) // -> 'hello world'
 * log(mirrored.bar) // -> 'hello world'
 * @param {string} srcProp
 * @param {string | Array<string>} dstProps
 * @returns {Function}
 */
function mirrorPropertyFn(srcProp, dstProps) {
  const dstPropsArray = isArray(dstProps)
    ? dstProps
    : [dstProps]

  // Return function that returns a proxy that does the mirroring when
  // accessing dstProps
  return obj => new Proxy({
    // Add preliminary dstProps to object so lodash _.has won't return false
    ...obj,
    ...dstPropsArray.reduce((acc, prop) => {
      acc[prop] = obj[srcProp]
      return acc
    }, {}),
  }, {
    get(target, prop, receiver) {
      // If accessing dstProp, return srcProp value
      const foundTargetProp = dstPropsArray.includes(srcProp)
      const maybeRedirectedProp = foundTargetProp
        ? srcProp
        : prop
      return Reflect.get(target, maybeRedirectedProp, receiver)
    },
  })
}

/**
 * @description Function for determining of given data is of type object
 * @param {*} data
 * @returns Boolean determining, if data is object
 */
function matchesObjectStructure(data) {
  const dataKeys = Object.keys(data)
  const firstValue = data[dataKeys[0]]

  if (typeof firstValue === 'string' || typeof firstValue === 'number') {
    return true
  }
  return false
}

/**
 * @description Function to search for all properties in inputconfiguration which provide a 'source'-property.
 * Each name provided by a 'source'-property is saved inside the propertyNamesArray and returned as an array of property-names.
 * @param {Array} inputConfigArray Array of inputconfiguration containing information about components to render.
 * @param {Array} propertyNamesArray Array of names retrieved from components with a 'source'-property.
 * @returns {Array} Array with all names retireved (popertyNamesArray)
 */
function findPropertiesWithSources(inputConfigArray, propertyNamesArray) {
  for (const index in inputConfigArray) {
    // only subcomponents without a 'children'-property contain a 'source'-property
    // if there is a 'children'-property the current component isn't a subcomponent so
    // the function needs be called again on this component to get to the subcomponent
    if (Object.keys(inputConfigArray[index]).includes('children')) {
      findPropertiesWithSources(inputConfigArray[index].children, propertyNamesArray)
    }
    else if (inputConfigArray[index].type === 'conditional-input') {
      const conditionalInputKeys = Object.keys(inputConfigArray[index].data)
      for (const inputKeyIndex in conditionalInputKeys) {
        findPropertiesWithSources(inputConfigArray[index].data[conditionalInputKeys[inputKeyIndex]], propertyNamesArray)
      }
    }
    else {
      // not all subcomponents contain a 'source'-property
      if (Object.keys(inputConfigArray[index]).includes('source')) {
        // the 'source'-property provides a name which links to further information in the generalconfig-file
        if (!propertyNamesArray.includes(inputConfigArray[index].source.name)) {
          propertyNamesArray.push(inputConfigArray[index].source.name)
        }
      }
    }
  }

  return propertyNamesArray
}

/**
 *
 * @param {*} dataset
 * @param {*} properties
 * @param {*} translatableProperties
 */
function setTranslation(dataset, properties, translatableProperties, languageInformation) {
  for (const propertyIndex in properties) {
    const propertyName = properties[propertyIndex]
    const propertyValue = dataset[propertyName]

    if (translatableProperties.includes(propertyName)) {
      const translationSelectoren = Object.keys(languageInformation.translation[languageInformation.locale].message.dataupload[propertyName])

      if (propertyValue !== '') {
        if (translationSelectoren.includes(propertyValue)) {
          dataset[propertyName] = languageInformation.translation[languageInformation.locale].message.dataupload[propertyName][propertyValue]
        }
      }
    }
  }
}

/**
 * @description Appends current locale to an url
 * @param {string} url url
 */
function appendCurrentLocaleToURL(url) {
  try {
    const urlHost = new URL(url).host
    const baseUrlHost = new URL(this.$env.api.baseUrl).host
    const isOurHostname = urlHost === baseUrlHost
    if (isOurHostname) {
      return `${url}?locale=${this.$route.query.locale}`
    }
    return url
  }
  catch {
    // when there is no hostname then it should link to our website
    return `${url}?locale=${this.$route.query.locale}`
  }
}

/**
 * @description Add preceding zero for numbers < 100 (if not already existing)
 * @param {string} value value
 */
function addPrecedingZero(value) {
  return `${Number.parseInt(value, 10) < 10 ? 0 : ''}${Number.parseInt(value, 10)}`
}

/**
 * @description Formatting temporalResolution property into human-readable format
 * @param {string} datetime datetime in temporalResolution format
 */
function formatDatetime(datetime) {
  let date = datetime.split('T')[0].substr(1)

  const year = date.split('Y')[0]
  date = date.split('Y')[1]

  const month = addPrecedingZero(date.split('M')[0])
  date = date.split('M')[1]

  const day = addPrecedingZero(date.split('D')[0])

  let time = datetime.split('T')[1]

  const hour = addPrecedingZero(time.split('H')[0])
  time = time.split('H')[1]

  const minute = addPrecedingZero(time.split('M')[0])
  time = time.split('M')[1]

  const second = addPrecedingZero(time.split('S')[0])

  return `${hour}:${minute}:${second} - ${day}.${month}.${year}`
}

/**
 * @description Creates a navigation guard that adds a locale to the query if it is not present.
 */
function createStickyLocale(fallbackLocale: string = 'en') {
  return function stickyLocale(to: any, from: any, next: Function) {
    const toLocale = to.query.locale
    const fromLocale = from.query.locale

    const newLocale = toLocale ?? fromLocale ?? fallbackLocale

    newLocale !== toLocale
      ? next({ ...to, query: { ...to.query, locale: newLocale } })
      : next()
  }
}

// Export all functions as default export.
export {
  addPrecedingZero,
  appendCurrentLocaleToURL,
  createStickyLocale,
  findPropertiesWithSources,
  formatDatetime,
  getFacetTranslation,
  getRepresentativeLocaleOf,
  getTranslationFor,
  matchesObjectStructure,
  mirrorPropertyFn,
  normalize,
  removeMailtoOrTel,
  replaceHttp,
  setTranslation,
  truncate,
  unique,
}
