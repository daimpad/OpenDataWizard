import generalHelper from "./general-helper";
import { has, isEmpty } from "lodash";

/**
 * Converts given data for given property into input form format
 * @param {*} state state from store
 * @param {*} property Property to convert data for (datasets/catalogues)
 * @param {*} data Linked data within a dataset
 */
async function convertToInput(state, property, data, dpiConfig) {
  console.log(dpiConfig);

  let generalID;
  let namespaceKeys;
  let propertyQuads;

  if (property === "datasets") {
    propertyQuads = data.match(
      null,
      "http://www.w3.org/1999/02/22-rdf-syntax-ns#type",
      "http://www.w3.org/ns/dcat#Dataset",
      null
    );
  } else {
    propertyQuads = data.match(
      null,
      "http://www.w3.org/1999/02/22-rdf-syntax-ns#type",
      "http://www.w3.org/ns/dcat#Catalog",
      null
    );
  }

  // extract data for datasets/catalogues
  namespaceKeys = generalHelper.getPagePrefixedNames(
    property,
    dpiConfig.inputDefinition,
    dpiConfig.pageConent
  );
  state[property] = {};
  for (let el of propertyQuads) {
    // there should be only one dataset id
    generalID = el.subject.value;

    for (let pageName in namespaceKeys[property]) {
      state[property][pageName] = {};
      convertProperties(
        property,
        state[property][pageName],
        generalID,
        data,
        namespaceKeys[property][pageName],
        dpiConfig
      );
    }
  }

  // also add distribution data
  if (property === "datasets") {
    const distributionQuads = data.match(
      generalID,
      "http://www.w3.org/ns/dcat#distribution",
      null,
      null
    );
    namespaceKeys = generalHelper.getPagePrefixedNames(
      "distributions",
      dpiConfig.inputDefinition,
      dpiConfig.pageConent
    );
    state.datasets.Distributions["distributionList"] = [];
    for (let el of distributionQuads) {
      const currentDistribution = {};

      const distributionId = el.object.value;
      for (let pageName in namespaceKeys["distributions"]) {
        currentDistribution[pageName] = {};
        convertProperties(
          "distributions",
          currentDistribution[pageName],
          distributionId,
          data,
          namespaceKeys["distributions"][pageName],
          dpiConfig
        );
      }
      state.datasets.Distributions.distributionList.push(currentDistribution);
    }
  }
}

/**
 * Converts value for given property from RDF into input form format
 * @param {*} property Parent property of given value (datasets/distributions/catalogues)
 * @param {*} state State from store
 * @param {*} id Id of parent node which serves as subject
 * @param {*} data Linked data
 * @param {*} propertyKeys Keys of properties to check
 */
function convertProperties(property, state, id, data, propertyKeys, dpiConfig) {
  const formatType = dpiConfig.formatTypes;

  for (let index = 0; index < propertyKeys.length; index += 1) {
    const key = propertyKeys[index];
    let subData = data.match(
      id,
      generalHelper.addNamespace(key, dpiConfig),
      null,
      null
    );

    // console.log(dpiConfig, formatType);

    if (formatType.singularString[property].includes(key)) {
      convertSingularStrings(subData, state, key);
    } else if (formatType.singularURI[property].includes(key)) {
      convertSingularURI(subData, state, key, dpiConfig);
    } else if (formatType.multipleURI[property].includes(key)) {
      convertMultipleURI(subData, state, key, property, dpiConfig);
    } else if (formatType.typedStrings[property].includes(key)) {
      convertTypedString(subData, state, key);
    } else if (formatType.multilingualStrings[property].includes(key)) {
      convertMultilingual(subData, state, key);
    } else if (formatType.conditionalProperties[property].includes(key)) {
      // publisher either is an URI or a group with multiple values (name, homepage, email)
      if (key === "dct:publisher" || key === "dct:license") {
        for (let el of subData) {
          if (el.object.termType === "NamedNode") {
            state[key] = { name: el.object.value, resource: el.object.value };
          } else if (el.object.termType === "BlankNode") {
            // get keys for nested values without dct'title (special format)
            const nestedKeys = generalHelper
              .getNestedKeys(data.match(el.object, null, null, null), dpiConfig)
              .filter((el) => el !== "dct:title");
            const nestedProperties = {};

            // convert nested values
            if (key === "dct:license") {
              const licenceTitleQuad = data.match(
                el.object,
                generalHelper.addNamespace("dct:title", dpiConfig),
                null,
                null
              );
              for (let el of licenceTitleQuad) {
                nestedProperties["dct:title"] = el.object.value;
              }
            }

            convertProperties(
              property,
              nestedProperties,
              el.object,
              data,
              nestedKeys,
              dpiConfig
            );
            state[key] = nestedProperties;
          }
        }
      }
    } else if (formatType.groupedProperties[property].includes(key)) {
      if (subData.size > 0) {
        state[key] = [];
        // there could be multiple nodes with data for a property
        for (let el of subData) {
          let currentState = {};
          if (key === "skos:notation") {
            // skos notation behaves differently
            // there should be a typed literal given which should be seperated into @value and @type
            if (el.object.value) currentState["@value"] = el.object.value;
            if (el.object.datatypeString)
              currentState["@type"] = {
                name: el.object.datatypeString,
                resource: el.object.datatypeString,
              };
          } else {
            // some properties have a named node containing data, the value of this named node also is a value form the input form (typically @id)
            if (el.object.termType === "NamedNode")
              currentState["@id"] = el.object.value;
            // get keys of node properties
            const nestedKeys = generalHelper.getNestedKeys(
              data.match(el.object, null, null, null),
              dpiConfig
            );
            convertProperties(
              property,
              currentState,
              el.object,
              data,
              nestedKeys,
              dpiConfig
            );
          }
          // creator not an array
          if (
            key === "dct:creator" ||
            key === "vcard:hasAddress" ||
            key === "skos:notation" ||
            key === "spdx:checksum"
          )
            state[key] = currentState;
          else state[key].push(currentState);
        }
      }
    } else if (key === "dcat:temporalResolution") {
      // temporal resolution is displayed as group of input forms for each property (year, month, day, ...)
      // the form provides the data as following: [ { 'Year': '...', 'Month': '...', ... } ]
      // the linked data format of this property looks like this: P?Y?M?DT?H?M?S
      if (subData.size > 0) {
        state[key] = {};

        const shorts = ["Y", "M", "D", "H", "M", "S"];
        const forms = {
          0: "Year",
          1: "Month",
          2: "Day",
          3: "Hour",
          4: "Minute",
          5: "Second",
        };

        // should be oly one quad
        let resolutionValue;
        for (let el of subData) {
          resolutionValue = el.object.value;
        }

        // backend converts temporalResolution values without a date into seconds for time values
        if (!resolutionValue.startsWith("P")) {
          // setting year, month and day to 0
          state[key][forms[0]] = 0;
          state[key][forms[1]] = 0;
          state[key][forms[2]] = 0;

          // converting seconds into HH:MM:SS
          const data = new Date(resolutionValue * 1000)
            .toISOString()
            .slice(11, 19);
          state[key][forms[3]] = data.slice(0, 2);
          state[key][forms[4]] = data.slice(3, 5);
          state[key][forms[5]] = data.slice(7, 9);
        } else {
          // find index of letter for time period
          // extract substring until this index
          // extract number from string and set as according value for input
          for (let tempIndex = 0; tempIndex < shorts.length; tempIndex += 1) {
            const position = resolutionValue.indexOf(shorts[tempIndex]); // position of duration letter
            const subDuration = resolutionValue.substring(0, position); // substring until position of duration letter
            const value = subDuration?.match(/\d+/g)?.[0] ?? ""; // extract number
            resolutionValue = resolutionValue.substring(position); // overwrite resolution string with shortened version (missing the extracted part)
            state[key][forms[tempIndex]] = value; // write to result object
          }
        }
      }
    } else if (key === "dct:identifier") {
      if (subData.size > 0) {
        // identifier should be provided as array of strings
        // [{'@value': '...'}, {'@value': '...'}, ...]
        state[key] = [];

        for (let el of subData) {
          state[key].push({ "@value": el.object.value });
        }
      }
    } else if (key === "dct:rights") {
      // rights is conditional and gets a string
      // rights always includes a type so everything is located within a blank node
      // also rights is a singular property
      if (subData.size > 0) {
        let nodeData;
        // get id of blank node and associated label data
        for (let el of subData) {
          const rightsBlankNode = el.object;
          nodeData = data.match(
            rightsBlankNode,
            generalHelper.addNamespace("rdfs:label", dpiConfig),
            null,
            null
          );
          for (let label of nodeData) {
            if (generalHelper.isUrl(label.object.value))
              state[key] = { "@type": "url", "rdfs:value": label.object.value };
            else
              state[key] = {
                "@type": "text",
                "rdfs:value": label.object.value,
              };
          }
        }
      }
    } else if (key === "datasetID" && property !== "datatsets") {
      // id is given as complete URI
      // dataset-/catalogue-id is string following the last /
      state[key] = id.substr(id.lastIndexOf("/") + 1);
      state["hidden_datasetIDFormHidden"] = id.substr(id.lastIndexOf("/") + 1);
    } else if (key === "dcat:catalog" && property === "datasets") {
      // datasets also have a property called dcat:catalog (not valid DCAT-AP)
      // property is needed to determine catalog the dataset belongs to
      if (!(subData.size > 0)) {
        // bceause dcat:catalog is no valid DCAT-AP it is possible that the prefix is not resolved
        // therefore it is also possible to get the data by using the shortned key
        subData = data.match(id, "dcat:catalog", null, null);
      }

      state[key] = "";

      // there should only be one catalog
      for (let cat of subData) {
        state[key] = cat.object.value;
      }
    } else if (key === "rdf:type") {
      // some properties have a type which can be selected
      // the type also has a namespace and therefore need to be shortened ( e.g. from https://...Individual to vcard:Individual)
      if (subData.size > 0) {
        state[key] = "";

        // typically there is only on type provided for each property instance
        for (let el of subData) {
          state[key] = generalHelper.removeNamespace(
            el.object.value,
            dpiConfig
          );
        }
      }
    }
  }
}

//-----------------------------------------------------------------------------------------------------
//                  basic conversion methods for different categories of data
//-----------------------------------------------------------------------------------------------------
// seems at first to be unnecessary but if we want to convert nested properties as well, we need these
// methods (especially to provide the correct parent URI)

/**
 *
 * @param {*} data
 * @param {*} state
 * @param {*} key
 */
function convertSingularStrings(data, state, key) {
  if (data.size > 0) {
    state[key] = "";

    for (let el of data) {
      state[key] = el.object.value;
    }
  }
}

/**
 *
 * @param {*} data
 * @param {*} state
 * @param {*} key
 */
function convertSingularURI(data, state, key, dpiConfig) {
  const formatType = dpiConfig.formatTypes;

  if (data.size > 0) {
    state[key] = "";

    for (let el of data) {
      const value = el.object.value;

      if (value.startsWith("mailto:")) {
        state[key] = value.replace("mailto:", "");
      } else {
        if (formatType.URIformat.voc.includes(key))
          state[key] = { name: value, resource: value };
        else if (formatType.URIformat.string.includes(key)) state[key] = value;
        else state[key] = { "@id": value };
      }
    }
  }
}

/**
 *
 * @param {*} data
 * @param {*} state
 * @param {*} key
 */
function convertMultipleURI(data, state, key, property, dpiConfig) {
  // there are two different formats the frontend need to deliver multiple URIs
  // 1: [ "URI1", "URI2" ]
  // 2: [ { "@id": "URI1" }, { "@id": "URI2" } ]

  const formatType = dpiConfig.formatTypes;

  if (data.size > 0) {
    state[key] = [];
    for (let el of data) {
      if (formatType.URIformat.voc.includes(key))
        state[key].push({ name: el.object.value, resource: el.object.value });
      else if (formatType.URIformat.string.includes(key))
        state[key].push(el.object.value);
      else state[key].push({ "@id": el.object.value });
    }
  }
}

/**
 *
 * @param {*} data
 * @param {*} state
 * @param {*} key
 */
function convertTypedString(data, state, key) {
  // some properties have a type
  // normally this type is not used within the forntend form and therefore won't be saved to frontend values
  if (data.size > 0) {
    state[key] = "";
    for (let el of data) {
      if (key === "dcat:spatialResolutionInMeters" || key === "dcat:byteSize")
        state[key] = el.object.value;
      else if (key === "dcat:startDate" || key === "dcat:endDate") {
        state[key] = el.object.value;
      } else {
        let dateType;
        if (el.object.value.includes("T")) dateType = "dateTime";
        else dateType = "date";

        state[key] = { "@type": dateType, "@value": el.object.value };
      }
    }
  }
}

/**
 * Converts and writes multilingual data to store
 * @param {*} data DatasetCore containing an array of quads
 * @param {*} state State for current key
 * @param {*} key Name of current property (e.g: 'dct:title')
 */
function convertMultilingual(data, state, key) {
  // multilingual data is always stored within an array containing object with the value and it's language
  // [ {'@value': '...', '@language': '...'}, ...]
  if (data.size > 0) {
    state[key] = [];

    for (let el of data) {
      if (!el.object.language.includes("-")) {
        // machine translation language tags look like this "fi-t-en-t0-mtec" and should not be included
        const currentElement = {};
        currentElement["@value"] = el.object.value; // actual value
        currentElement["@language"] = el.object.language; // language of value
        state[key].push(currentElement);
      }
    }
  }
}

export default {
  convertToInput,
};
