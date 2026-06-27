import axios from "axios";

const getConvertedFormkitData = async (baseUrl, id, art) => {};

const getHvdCategories = async (baseUrl) => {
  try {
    let req = `${baseUrl}vocabularies/hvd-category`;
    const res = await axios.get(req);
    return res.data.result.results;
  } catch (error) {
    console.error("Error fetching HVD categories:", error);
    throw error;
  }
};

const getDatasetCategories = async (baseUrl) => {
  try {
    let req = `${baseUrl}vocabularies/data-theme`;
    const res = await axios.get(req);
    return res.data.result.results;
  } catch (error) {
    console.error("Error fetching Dataset categories:", error);
    throw error;
  }
};
const getAccessRights = async (baseUrl) => {
  try {
    let req = `${baseUrl}vocabularies/access-right`;
    const res = await axios.get(req);
    return res.data.result.results;
  } catch (error) {
    console.error("Error fetching Dataset categories:", error);
    throw error;
  }
};
const getFileTypes = async (baseUrl) => {
  try {
    let req = `${baseUrl}vocabularies/iana-media-types`;
    const res = await axios.get(req, {
      headers: { Accept: "application/json" },
    });
    // console.log(res.data.result);

    return res.data.result;
  } catch (error) {
    console.error("Error fetching file types:", error);
    throw error;
  }
};
const getFormatTypes = async (baseUrl) => {
  try {
    let req = `${baseUrl}vocabularies/file-type`;
    const res = await axios.get(req, {
      headers: { Accept: "application/json" },
    });
    // console.log(res.data.result);

    return res.data.result;
  } catch (error) {
    console.error("Error fetching file types:", error);
    throw error;
  }
};
const getGeocodingURIs = async (baseUrl) => {
  let listHeaders = [
    "district-key",
    "government-district-key",
    "municipal-association-key",
    "municipality-key",
    "regional-key",
    "state-key",
  ];
  let itemList = [];
  let innerRequest = [];
  try {
    let req = `${baseUrl}vocabularies/political-geocoding-level`;
    const res = await axios.get(req);
    for (let index = 0; index < res.data.result.results.length; index++) {
      innerRequest.push(
        await axios.get(
          `${baseUrl}vocabularies/political-geocoding-` + listHeaders[index]
        )
      );
      // console.log(innerRequest);

      // TODO need to fix that, locale should be added dinamically
      itemList.push({
        headers: listHeaders[index],
        list: innerRequest[index].data.result.results,
      });
    }
    return itemList;
  } catch (error) {
    console.error("Error fetching Dataset categories:", error);
    throw error;
  }
};
const filterGeocodingURIs = async (text, baseUrl) => {
  let items = await getGeocodingURIs(baseUrl);
  // Filtere die Items basierend auf dem Text
  const filteredItems = items
    .map((item) => {
      // Filtere die list, um nur diejenigen zu behalten, die den Text enthalten
      const filteredList = item.list.filter(
        (listItem) =>
          listItem.alt_label &&
          listItem.alt_label["de"] &&
          listItem.alt_label["de"].includes(text)
      );
      // Rückgabe des Objekts mit der gefilterten list
      return { ...item, list: filteredList };
    })
    .filter((item) => item.list.length > 0); // Filtere leere Listen heraus

  // console.log(filteredItems);
  return filteredItems;
};

const getOptionalURIs = async (text, baseUrl, voc) => {
  let itemList = [];
  try {
    let req = `${baseUrl}vocabularies/${voc}`;
    const res = await axios.get(req);
    itemList = res.data.result.results;
    // console.log(itemList);

    return itemList;
  } catch (error) {
    console.error("Error fetching Dataset categories:", error);
    throw error;
  }
  // console.log(text, baseUrl, voc);
};

const getPlannedAvailability = async (baseUrl) => {
  try {
    let req = `${baseUrl}vocabularies/planned-availability`;
    const res = await axios.get(req);
    return res.data.result.results.map((item) => ({
      value: item.id,
      label: item.pref_label.de,
      uri: item.resource,
    }));
  } catch (error) {
    console.error("Error fetching planned availability:", error);
    throw error;
  }
};

const getLanguages = async (baseUrl, locale = "de") => {
  try {
    let req = `${baseUrl}vocabularies/language`;
    const res = await axios.get(req);

    let languages = res.data.result.results.map((item) => ({
      value: item.id,
      label: `${item.pref_label[locale] || item.pref_label["en"] || item.id} (${
        item.id
      })`,
      uri: item.resource,
    }));

    languages.sort((a, b) => a.label.localeCompare(b.label, locale));

    return languages;
  } catch (error) {
    console.error("Error fetching languages:", error);
    throw error;
  }
};

const getLicenses = async (baseUrl, locale = "de") => {
  try {
    let req = `${baseUrl}vocabularies/licenses`;
    const res = await axios.get(req);

    // console.log(res.data);

    let licenses = res.data.result.results.map((item) => ({
      value: item.id,
      label: item.pref_label[locale] || item.pref_label["en"] || item.id,
      homepage: item.extensions?.foaf_homepage || null,
      uri: item.resource,
    }));

    return licenses;
  } catch (error) {
    console.error("Error fetching licenses:", error);
    throw error;
  }
};

const getDatasetStatus = async (baseUrl, locale = "de") => {
  try {
    let req = `${baseUrl}vocabularies/adms`;
    const res = await axios.get(req);

    
    
    const filteredArray = res.data.result.results.filter(
      (filterItem) => filterItem['in_scheme'] === "http://purl.org/adms/status/1.0" &&
      filterItem.resource !== "http://purl.org/adms/status/UnderDevelopment"
    )

    
    let datasetStatus = filteredArray.map((item) => ({
      value: item.id,
      label: item.pref_label[locale] || item.pref_label["en"] || item.id,
      resource: item.resource || null,
    }));
    // console.log("datatsetstatus: ", datasetStatus);
    return datasetStatus;
  } catch (error) {
    console.error("Error fetching dataset status:", error);
    throw error;
  }
};

const getChecksumAlgorithms = async (baseUrl, locale = "en") => {
  try {
    let req = `${baseUrl}vocabularies/spdx-checksum-algorithm`;
    const res = await axios.get(req);

    let checksumAlgorithms = res.data.result.results.map((item) => ({
      value: item.id,
      label: item.pref_label[locale] || item.pref_label["en"] || item.id,
      resource: item.resource || null,
    }));

    return checksumAlgorithms;
  } catch (error) {
    console.error("Error fetching checksum algorithms:", error);
    throw error;
  }
};

export {
  getGeocodingURIs,
  getChecksumAlgorithms,
  getDatasetStatus,
  getLicenses,
  getLanguages,
  getPlannedAvailability,
  getHvdCategories,
  getDatasetCategories,
  filterGeocodingURIs,
  getFileTypes,
  getOptionalURIs,
  getFormatTypes,
  getConvertedFormkitData,
  getAccessRights
};
