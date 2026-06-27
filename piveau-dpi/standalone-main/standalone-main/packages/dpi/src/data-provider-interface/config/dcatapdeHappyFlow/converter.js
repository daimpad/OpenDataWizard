import axios from "axios";
import { asSomeArray } from "../../composables/useDpiSimpleLoader";

// ################## ################## ################## ##########
// Sidenotes:
// - Dates only allow days and not time at the moment
// - Language is set to de at default, need to adjust that in the case of multilinguality
// -
// ################## ################## ################## ##########

let refinedData = { distribution: [], dataset: {}, meta: {} };
let hubUrl = "";

// ################## Checks if the ID is uniqe - returns true when its already there >ToDo: is always true ################## ##########
function checkUniqueID(property) {
  return new Promise((resolve) => {
    if (refinedData.dataset["@id"] !== "") {
      const request = `${hubUrl}${property}/${refinedData.dataset["@id"]}?useNormalizedId=true`;
      axios
        .head(request)
        .then(() => {
          resolve(false);
        })
        .catch(() => {
          resolve(true);
        });
    }
  });
}
// Need to refactor the title to a ID
function cleanString(input) {
  return input
    .replace(/[^a-z0-9\s]/gi, "") // Alle nicht-alphanumerischen Zeichen entfernen
    .replace(/\s+/g, "-") // Mehrere Leerzeichen durch ein einzelnes "-" ersetzen
    .toLowerCase(); // In Kleinbuchstaben umwandeln
}

async function sendDataToAPI(formValues, dpiContext, userData, hubURL) {
  hubUrl = hubURL;
  const prefixes = dpiContext.specification.prefixes;

  // Creates a UID for the Distribution
  let distUid = [];

  // Representing all the steps of the DPI - need this to iterate over the individual properties inside of those steps
  const keysOfTheWizard = Object.keys(formValues);

  // ################## ################## ################## ##########
  // ################## Start individual Datahandling ##################
  // ################## ################## ################## ##########

  // ################## Link all distributions to dataset - ToDo: if ID set, don't set new one! ##################
  refinedData.dataset["dcat:distribution"] = [];
  for (
    let index = 0;
    index < formValues.DistributionSimple["dcat:distribution"].length;
    index++
  ) {
    distUid.push(crypto.randomUUID());
    refinedData.dataset["dcat:distribution"].push({
      "@id": `https://piveau.io/set/distribution/${distUid[index]}`,
    });
  }

  // ################## ################## ################## ##########
  // ################## Get URL Parameters ################## ##########
  // ################## ################## ################## ##########
  const urlParams = new URLSearchParams(window.location.search);
  const isEditmode = urlParams.get("edit");
  let datasetId = "";

  try {
    for (let index = 0; index < keysOfTheWizard.length; index++) {
      if (keysOfTheWizard[index] === "Discoverability") {
        // skips the first Index for it's just for the validation of the fields
        let cache = [];
        for (
          let index = 1;
          index < formValues.Discoverability.discoverabilityPage.length;
          index++
        ) {
          cache.push({
            "@id": formValues.Discoverability.discoverabilityPage[index].uri,
          });
        }
        refinedData.dataset["dcat:theme"] = cache;

        if (
          formValues.Discoverability.hvdPage &&
          Object.keys(formValues.Discoverability.hvdPage).length !== 0 &&
          formValues.Discoverability.hvdPage[0].label !== undefined
        ) {
          refinedData.dataset["dcatap:hvdCategory"] = {
            "@id": formValues.Discoverability?.hvdPage?.[0].uri,
          };
          refinedData.dataset["dcatap:applicableLegislation"] = {
            "@id": "http://data.europa.eu/eli/reg_impl/2023/138/oj",
          };
        }
      }
      if (keysOfTheWizard[index] === "BasicInfos") {
        // Makes sure, that we can determine, that this Dataset is from the V3
        refinedData.dataset["dpi:isDPIv3"] = true;
        for (
          let innerIndex = 0;
          innerIndex < Object.keys(formValues.BasicInfos).length;
          innerIndex++
        ) {
          if (Object.keys(formValues.BasicInfos)[innerIndex] === "dct:title") {
            // There can be multiple Titles (for the future we need to make sure that this will be considered)
            refinedData.dataset["dct:title"] =
              formValues.BasicInfos["dct:title"][0] || "";

            // Adding the datasetID here -- ToDo: need to check for doubles and if Editmode

            // If no ID is set in the Data -- can be ignored
            // refinedData["dataset"]["@id"] =
            //   "https://piveau.io/set/data/" +
            //   cleanString(formValues.BasicInfos["dct:title"][0]["@value"]);
            datasetId = cleanString(
              formValues.BasicInfos["dct:title"][0]["@value"],
            );

            // if Editmode is enabled
            if (isEditmode === "true") {
              datasetId = formValues.BasicInfos.datasetID;
            }
          }
          if (
            Object.keys(formValues.BasicInfos)[innerIndex] === "dct:description"
          ) {
            refinedData.dataset["dct:description"] =
              formValues.BasicInfos["dct:description"][0] || "";
          }
          if (
            Object.keys(formValues.BasicInfos)[innerIndex] === "dct:modified"
          ) {
            formValues.BasicInfos["dct:modified"]?.[0]
              ? (refinedData.dataset["dct:modified"] = {
                  ...formValues.BasicInfos["dct:modified"]?.[0],
                  "@type":
                    formValues.BasicInfos["dct:modified"]?.[0]?.["@type"] ||
                    "http://www.w3.org/2001/XMLSchema#date",
                })
              : (refinedData.dataset["dct:modified"] = undefined);
          }
          if (
            Object.keys(formValues.BasicInfos)[innerIndex] === "dct:publisher"
          ) {
            formValues.BasicInfos["dct:publisher"]?.[0]
              ? (refinedData.dataset["dct:publisher"] = {
                  ...formValues.BasicInfos["dct:publisher"]?.[0],
                  "@type":
                    formValues.BasicInfos["dct:publisher"]?.[0]?.["@type"] ||
                    "foaf:Agent",
                })
              : (refinedData.dataset["dct:publisher"] = undefined);
          }
          if (
            Object.keys(formValues.BasicInfos)[innerIndex] ===
            "dcat:contactPoint"
          ) {
            formValues.BasicInfos["dcat:contactPoint"]?.[0]
              ? (refinedData.dataset["dcat:contactPoint"] = {
                  ...formValues.BasicInfos["dcat:contactPoint"]?.[0],
                  "@type":
                    formValues.BasicInfos["dcat:contactPoint"]?.[0]?.[
                      "@type"
                    ] || "vcard:Organization",
                })
              : (refinedData.dataset["dcat:contactPoint"] = undefined);
          }
          // if (Object.keys(formValues.BasicInfos)[innerIndex] === "datasetID") {
          //   refinedData["dataset"]["@id"] =
          //     formValues.BasicInfos["datasetID"] || "";
          // }
          // Set type to "dataset" need to adjust that for catalog-creation and future TwinBy Stuff
          refinedData.dataset["@type"] = "dcat:Dataset";

          // set the catalog for the dataset

          refinedData.meta["dcat:catalog"] = userData.permissions[0].rsname;

          // set type of the record (is this correct?)
          refinedData.meta["@type"] = "dcat:CatalogRecord";
        }
      }
      if (
        keysOfTheWizard[index] === "Covering" &&
        Object.keys(formValues.Covering).length > 1
      ) {
        for (
          let innerIndex = 0;
          innerIndex < Object.keys(formValues.Covering).length;
          innerIndex++
        ) {
          if (
            Object.keys(formValues.Covering)[innerIndex] ===
              "dcat:temporalResolution" &&
            formValues.Covering["dcat:temporalResolution"] !== undefined
          ) {
            if (
              Object.keys(formValues.Covering?.["dcat:temporalResolution"])
                .length > 0
            ) {
              if (
                formValues.Covering?.["dcat:temporalResolution"]?.type !==
                undefined
              ) {
                refinedData.dataset["dct:temporal"] = [];
                for (
                  let tempIndex = 0;
                  tempIndex <
                  formValues.Covering["dcat:temporalResolution"]["dct:temporal"]
                    .length;
                  tempIndex++
                ) {
                  if (
                    formValues.Covering["dcat:temporalResolution"].type !=
                      undefined &&
                    formValues.Covering["dcat:temporalResolution"][
                      "dct:temporal"
                    ][tempIndex].dataType === "date" &&
                    formValues.Covering["dcat:temporalResolution"][
                      "dct:temporal"
                    ][tempIndex]["dcat:startDate"] !== ""
                  ) {
                    refinedData.dataset["dct:temporal"][tempIndex] = {
                      "@type":
                        formValues.Covering["dcat:temporalResolution"].type ||
                        "",
                      "dcat:endDate": {
                        "@value":
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dcat:endDate"] || "",
                        "@type":
                          "http://www.w3.org/2001/XMLSchema#" +
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dataType"],
                      },
                      "dcat:startDate": {
                        "@value":
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dcat:startDate"] || "",
                        "@type":
                          "http://www.w3.org/2001/XMLSchema#" +
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dataType"],
                      },
                    };
                  }
                  if (
                    formValues.Covering["dcat:temporalResolution"].type !=
                      undefined &&
                    formValues.Covering["dcat:temporalResolution"][
                      "dct:temporal"
                    ][tempIndex].dataType === "dateTime" &&
                    formValues.Covering["dcat:temporalResolution"][
                      "dct:temporal"
                    ][tempIndex]["dcat:startDate"] !== ""
                  ) {
                    refinedData.dataset["dct:temporal"][tempIndex] = {
                      "@type":
                        formValues.Covering["dcat:temporalResolution"].type ||
                        "",
                      "dcat:endDate": {
                        "@value":
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dcat:endDate"] +
                            "T" +
                            formValues.Covering["dcat:temporalResolution"][
                              "dct:temporal"
                            ][tempIndex]["endTime"] || "",
                        "@type":
                          "http://www.w3.org/2001/XMLSchema#" +
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dataType"],
                      },
                      "dcat:startDate": {
                        "@value":
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dcat:startDate"] +
                            "T" +
                            formValues.Covering["dcat:temporalResolution"][
                              "dct:temporal"
                            ][tempIndex]["startTime"] || "",
                        "@type":
                          "http://www.w3.org/2001/XMLSchema#" +
                          formValues.Covering["dcat:temporalResolution"][
                            "dct:temporal"
                          ][tempIndex]["dataType"],
                      },
                    };
                  }
                }
              }
            }
          }
          if (
            Object.keys(formValues.Covering)[innerIndex] ===
              "dcatde:politicalGeocodingURI" &&
            formValues.Covering?.["dcatde:politicalGeocodingURI"]?.[0]?.uri !=
              undefined
          ) {
            refinedData.dataset["dcatde:politicalGeocodingURI"] = {
              "@id":
                formValues.Covering["dcatde:politicalGeocodingURI"]?.[0]?.uri ||
                "",
            };
            if (refinedData.dataset["dct:spatial"] === undefined) {
              refinedData.dataset["dct:spatial"] = [];
            }
            refinedData.dataset["dct:spatial"].push({
              "@id":
                formValues.Covering["dcatde:politicalGeocodingURI"]?.[0]?.uri ||
                "",
            });
          }
        }
      }
      if (keysOfTheWizard[index] === "DistributionSimple") {
        // ################## ############ ##################
        // ################## Distribution ##################
        // ################## ############ ##################
        for (
          let distIndex = 0;
          distIndex < formValues.DistributionSimple["dcat:distribution"].length;
          distIndex++
        ) {
          // New Object for every Distribution
          refinedData.distribution[distIndex] = {};

          // if HVD is enabled, add to all Distributions

          if (formValues.Discoverability?.hvdPage[0]?.uri != undefined) {
            refinedData.distribution[distIndex][
              "dcatap:applicableLegislation"
            ] = {
              "@id": "http://data.europa.eu/eli/reg_impl/2023/138/oj",
            };
          }

          // ################## Type ##################
          refinedData.distribution[distIndex]["@type"] = "dcat:Distribution";

          // ################## Distribution ID ##################
          // ################## the UID has to change when there are several Distributions ToDo ##################
          refinedData.distribution[distIndex]["@id"] =
            `https://piveau.io/set/distribution/${distUid[distIndex]}`;

          // ################## dct:identifier ##################
          // ################## Is this needed? ##################
          // refinedData["distribution"]["dct:identifier"] = "randomString123";

          // ################## AccessURL ##################
          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcat:accessURL"
            ]
          ) {
            refinedData.distribution[distIndex]["dcat:accessURL"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcat:accessURL"
                ],
            };

            // refinedData.distribution[distIndex]["dcat:downloadURL"] = {
            //   "@id":
            //     formValues.DistributionSimple["dcat:distribution"][distIndex][
            //       "dcat:accessURL"
            //     ].startsWith("https://www.") ||
            //     formValues.DistributionSimple["dcat:distribution"][distIndex][
            //       "dcat:accessURL"
            //     ].startsWith("http://www.")
            //       ? formValues.DistributionSimple["dcat:distribution"][
            //           distIndex
            //         ]["dcat:accessURL"] // Wert zuweisen wenn true
            //       : `https://www.${formValues.DistributionSimple["dcat:distribution"][distIndex]["dcat:accessURL"]}`, // Standardwert wenn beide false
            // };
          }

          // ################## DownloadURL ##################

          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dcat:downloadURL"
              ],
            ).length !== 0 &&
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcat:downloadURL"
            ][0]["@id"] !== ""
          ) {
            refinedData.distribution[distIndex]["dcat:downloadURL"] = [];
            for (
              let index = 0;
              index <
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dcat:downloadURL"
              ].length;
              index++
            ) {
              refinedData.distribution[distIndex]["dcat:downloadURL"][index] = {
                "@id":
                  formValues.DistributionSimple["dcat:distribution"][distIndex][
                    "dcat:downloadURL"
                  ][index]["@id"],
              };
            }
          }
          // ################## odrl:hasPolicy ##################

          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex]?.[
                "policyItems"
              ],
            ).length !== 0 &&
            formValues.DistributionSimple["dcat:distribution"][distIndex]?.[
              "policyItems"
            ][0]["dcat:downloadURL"] !== ""
          ) {
            refinedData.distribution[distIndex]["odrl:hasPolicy"] = [];
            for (
              let index = 0;
              index <
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "policyItems"
              ].length;
              index++
            ) {
              refinedData.distribution[distIndex]["odrl:hasPolicy"][index] = {
                "@id":
                  formValues.DistributionSimple["dcat:distribution"][distIndex][
                    "policyItems"
                  ][index]["dcat:downloadURL"],
              };
            }
          }
          // ################## dct:license  & licenseAttributionByText ##################
          // let maybeLicense =
          //   formValues.DistributionSimple?.["dct:license"]?.[0];
          // if (maybeLicense === undefined) {
          //   maybeLicense =
          //     formValues.DistributionSimple?.["dcat:distribution"]?.[0]?.[
          //       "dct:license"
          //     ]?.[0];
          // }
          // refinedData.distribution[distIndex]["dct:license"] = {
          //   "@id": maybeLicense?.uri || "",
          // };
          // refinedData.distribution[distIndex][
          //   "dcatde:licenseAttributionByText"
          // ] = {
          //   "@language": "de",
          //   "@value": maybeLicense?.attribution || "",
          // };

          // New
          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex]?.[
                "dct:license"
              ],
            ).length !== 0
          ) {
            refinedData.distribution[distIndex]["dct:license"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dct:license"
                ].uri || formValues.DistributionSimple["dct:license"][0].uri,
            };
            if (
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dct:license"
              ]["dcterms:license"] === "" &&
              formValues.DistributionSimple["dct:license"][0].title !== ""
            ) {
              refinedData.distribution[distIndex][
                "dcatde:licenseAttributionByText"
              ] = {
                "@language": "de",
                "@value": formValues.DistributionSimple["dct:license"][0].title,
              };
            }
            if (
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dct:license"
              ].uri != "" &&
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dct:license"
              ].title !== ""
            ) {
              refinedData.distribution[distIndex][
                "dcatde:licenseAttributionByText"
              ] = {
                "@language": "de",
                "@value":
                  formValues.DistributionSimple["dcat:distribution"][distIndex][
                    "dct:license"
                  ].title,
              };
            }
          }

          // ################## dct:format ##################
          const maybeFormat =
            formValues.DistributionSimple?.["dcat:distribution"]?.[distIndex]?.[
              "dct:format"
            ];

          if (maybeFormat) {
            refinedData.distribution[distIndex]["dct:format"] = {
              "@id": maybeFormat?.uri || "",
            };
          }
          // ################## dcatap:applicableLegislation ##################
          if (
            formValues.DistributionSimple?.["dcat:distribution"]?.[distIndex]?.[
              "dcatap:applicableLegislation"
            ] != undefined
          ) {
            refinedData.distribution[distIndex]["dct:format"] =
              formValues.DistributionSimple?.["dcat:distribution"]?.[
                distIndex
              ]?.["dcatap:applicableLegislation"]["@id"];
          }
          // ################## conformsTo Dist ##################
          const maybeCT =
            formValues.DistributionSimple?.["dcat:distribution"]?.[distIndex]?.[
              "conformsToItems"
            ] || [];
          if (maybeCT.length !== 0) {
            refinedData.distribution[distIndex]["dct:conformsTo"] = asSomeArray(
              maybeCT,
            )
              .filter(
                (item) =>
                  item && (item["dcat:downloadURL"] || item["dct:title"]),
              )
              .map((item) => ({
                "@id": item["dcat:downloadURL"] || "",
                "rdfs:label": item["dct:title"],
                "@type": "dct:Standard",
              }));
          }
          // ################## dcat:mediaType ##################
          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dcat:mediaType"
              ],
            ).length !== 0 &&
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcat:mediaType"
            ].uri !== ""
          ) {
            refinedData.distribution[distIndex]["dcat:mediaType"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcat:mediaType"
                ].uri || "",
            };
          }

          // ################## dcat:byteSize ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcat:byteSize"
            ]
          ) {
            refinedData.distribution[distIndex]["dcat:byteSize"] = {
              "@value":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcat:byteSize"
                ] || "",
              "@type": "http://www.w3.org/2001/XMLSchema#decimal",
            };
          }

          // ################## dcatde:licenseAttributionByText ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcatde:licenseAttributionByText"
            ]
          ) {
            refinedData.distribution[distIndex][
              "dcatde:licenseAttributionByText"
            ] = {
              "@language": "de",
              "@value":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcatde:licenseAttributionByText"
                ] || "",
            };
          }

          // ################## dcatap:availability ###################

          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dcatap:availability"
              ],
            ).length !== 0 &&
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcatap:availability"
            ].uri !== ""
          ) {
            refinedData.distribution[distIndex]["dcatap:availability"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcatap:availability"
                ].uri || "",
            };
          }

          // ################## dct:issued ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dct:issued"
            ]
          ) {
            refinedData.distribution[distIndex]["dct:issued"] = {
              "@value":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dct:issued"
                ] || "",
              "@type": "http://www.w3.org/2001/XMLSchema#date",
            };
          }

          // ################## dct:modified ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dct:modified"
            ]
          ) {
            refinedData.distribution[distIndex]["dct:modified"] = {
              "@value":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dct:modified"
                ] || "",
              "@type": "http://www.w3.org/2001/XMLSchema#date",
            };
          }

          // ################## dct:description ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dct:description"
            ]
          ) {
            refinedData.distribution[distIndex]["dct:description"] = {
              "@language": "de",
              "@value":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dct:description"
                ],
            };
          }

          // ################## dct:title ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dct:title"
            ]
          ) {
            refinedData.distribution[distIndex]["dct:title"] = {
              "@language": "de",
              "@value":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dct:title"
                ],
            };
          }

          // ################## dct:accessRights ##################

          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"]?.[distIndex]?.[
                "dct:accessRights"
              ] || {}
            ).length !== 0
          ) {
            refinedData.distribution[distIndex]["dct:accessRights"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dct:accessRights"
                ].uri,
            };
          }
          // ################## dcat:compressFormat ##################

          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcat:compressFormat"
            ] != undefined &&
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex]?.[
                "dcat:compressFormat"
              ] || {}
            ).length !== 0 &&
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "dcat:compressFormat"
            ].uri !== ""
          ) {
            refinedData.distribution[distIndex]["dcat:compressFormat"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcat:compressFormat"
                ].uri || "",
            };
          }
          // ################## dcat:packageFormat ##################

          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"]?.[distIndex]?.[
                "dcat:packageFormat"
              ] || {}
            ).length !== 0 && formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dcat:packageFormat"
              ].uri !== ""
          ) {
            refinedData.distribution[distIndex]["dcat:packageFormat"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "dcat:packageFormat"
                ].uri || "",
            };
          }

          // ################## spdx:checksum ##################
          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex]
              .checksum.uri !== ""
          ) {
            refinedData.distribution[distIndex]["spdx:checksum"] = {
              "@type": "spdx:Checksum",
              "spdx:checksumValue":
                formValues.DistributionSimple["dcat:distribution"][distIndex]
                  .checksum.title || "",
              "spdx:algorithm": {
                "@id":
                  formValues.DistributionSimple["dcat:distribution"][distIndex]
                    .checksum.uri || "",
              },
            };
          }

          // ################## dct:language ##################

          if (
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"]?.[distIndex]?.[
                "dct:language"
              ] || {}
            ).length !== 0
          ) {
            refinedData.distribution[distIndex]["dct:language"] = [];
            for (
              let index = 0;
              index <
              formValues.DistributionSimple["dcat:distribution"][distIndex][
                "dct:language"
              ].length;
              index++
            ) {
              refinedData.distribution[distIndex]["dct:language"][index] = {
                "@id":
                  formValues.DistributionSimple["dcat:distribution"][distIndex][
                    "dct:language"
                  ][index].uri,
              };
            }
          }
          // ################## adms:status ##################
          if (
            formValues.DistributionSimple["dcat:distribution"][distIndex][
              "adms:status"
            ].uri !== "" &&
            Object.keys(
              formValues.DistributionSimple["dcat:distribution"][distIndex]?.[
                "adms:status"
              ] || {}
            ).length !== 0
          ) {
            refinedData.distribution[distIndex]["adms:status"] = {
              "@id":
                formValues.DistributionSimple["dcat:distribution"][distIndex][
                  "adms:status"
                ].uri || "",
            };
          }

          // ################## dcat:accessService ##################
          const maybeAS =
            formValues.DistributionSimple?.["dcat:distribution"]?.[distIndex]
              ?.accessServices || [];
          if (maybeAS.length !== 0) {
            refinedData.distribution[distIndex]["dcat:accessService"] =
              asSomeArray(maybeAS)
                ?.filter(
                  (accessService) =>
                    accessService &&
                    (accessService["dct:title"] ||
                      accessService["dct:description"] ||
                      accessService["dcat:downloadURL"] ||
                      accessService["dcat:endpointURL"]),
                )
                .map((accessService) => {
                  const entry = {
                    "@type": "dcat:DataService",
                    "dct:title": {
                      "@language": "de",
                      "@value": accessService["dct:title"],
                    },
                  };
                  if (accessService?.["dct:description"] !== undefined) {
                    entry["dct:description"] = {
                      "@language": "de",
                      "@value": accessService["dct:description"],
                    };
                  }
                  const endpoint =
                    accessService["dcat:downloadURL"] ||
                    accessService["dcat:endpointURL"];
                  if (endpoint !== undefined) {
                    entry["dcat:endpointURL"] = {
                      "@id": endpoint,
                    };
                  }
                  return entry;
                });
          }
          // ################## documentations === foaf:page ##################

          if (!refinedData.distribution[distIndex]["foaf:page"]) {
            refinedData.distribution[distIndex]["foaf:page"] = [];
          }

          if (refinedData.distribution[distIndex]["foaf:page"].length !== 0) {
            for (
              let pageIndex = 0;
              pageIndex <
              formValues.DistributionSimple?.["dcat:distribution"]?.[distIndex]
                ?.documentations.length;
              pageIndex++
            ) {
              const documentation =
                formValues.DistributionSimple["dcat:distribution"][distIndex]
                  .documentations[pageIndex];

              if (documentation?.["dct:title"] !== undefined) {
                const pageEntry = {
                  "@type": "foaf:Document",
                  "dct:title": {
                    "@language": "de",
                    "@value": documentation["dct:title"],
                  },
                };

                if (documentation?.["dct:description"] !== undefined) {
                  pageEntry["dct:description"] = {
                    "@language": "de",
                    "@value": documentation["dct:description"],
                  };
                }

                if (documentation?.["dcat:accessURL"] !== undefined) {
                  pageEntry["dcat:accessURL"] = {
                    "@id": documentation["dcat:accessURL"],
                  };
                }

                if (documentation?.["dct:format"] !== undefined) {
                  pageEntry["dct:format"] = {
                    "@id": documentation.formatUri,
                  };
                }

                refinedData.distribution[distIndex]["foaf:page"][pageIndex] =
                  pageEntry;
              }
            }
          }
        }
      }
      if (keysOfTheWizard[index] === "ReviewAndPublish") {
      }
      if (keysOfTheWizard[index] === "Additionals") {
        try {
          for (
            let innerIndex = 0;
            innerIndex < Object.keys(formValues.Additionals).length;
            innerIndex++
          ) {
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcat:landingPage"
            ) {
              refinedData.dataset["dcat:landingPage"] = [
                ...formValues.Additionals["dcat:landingPage"],
              ];
            }
            // ################## dct:issued ##################

            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dct:issued"
            ) {
              if (refinedData.dataset["dct:issued"] === undefined) {
                refinedData.dataset["dct:issued"] = [];
              }
              refinedData.dataset["dct:issued"].push({
                "@value":
                  formValues.Additionals["dct:issued"][0]["@value"]
                    .split(".")
                    .reverse()
                    .map((p, i) => (i < 2 ? p.padStart(2, "0") : p))
                    .join("-") || "",
                "@type": "http://www.w3.org/2001/XMLSchema#date",
              });
            }
            // ################## dcatap:availability ###################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcatap:availability"
            ) {
              for (
                let index = 0;
                index < formValues.Additionals["dcatap:availability"].length;
                index++
              ) {
                if (refinedData.dataset["dcatap:availability"] === undefined) {
                  refinedData.dataset["dcatap:availability"] = [];
                }
                refinedData.dataset["dcatap:availability"].push({
                  "@id":
                    formValues.Additionals["dcatap:availability"][index]["uri"],
                });
              }
            }

            // ############################### dcat:keyword ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dcat:keyword"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let keyWordIndex = 0;
                keyWordIndex < formValues.Additionals["dcat:keyword"].length;
                keyWordIndex++
              ) {
                if (refinedData.dataset["dcat:keyword"] === undefined) {
                  refinedData.dataset["dcat:keyword"] = [];
                }

                refinedData.dataset["dcat:keyword"].push({
                  "@value":
                    formValues.Additionals["dcat:keyword"][keyWordIndex][
                      "@value"
                    ],
                  "@language":
                    formValues.Additionals["dcat:keyword"]?.[keyWordIndex]?.[
                      "@language"
                    ] || "de",
                });
              }
            }
            // ############################### dct:references ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:references"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let referencesIndex = 0;
                referencesIndex <
                formValues.Additionals["dct:references"].length;
                referencesIndex++
              ) {
                if (refinedData.dataset["dct:references"] === undefined) {
                  refinedData.dataset["dct:references"] = [];
                }
                refinedData.dataset["dct:references"].push({
                  "@id":
                    formValues.Additionals["dct:references"][referencesIndex][
                      "@id"
                    ],
                });
              }
            }
            // ############################### dct:spatial ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
                "dct:spatial" &&
              formValues.Additionals["dct:spatial"]["@id"] !== ""
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let spatialIndex = 0;
                spatialIndex < formValues.Additionals["dct:spatial"].length;
                spatialIndex++
              ) {
                if (refinedData.dataset["dct:spatial"] === undefined) {
                  refinedData.dataset["dct:spatial"] = [];
                }
                refinedData.dataset["dct:spatial"].push({
                  "@id":
                    formValues.Additionals["dct:spatial"][spatialIndex]["@id"],
                });
              }
            }
            // ############################### dcatde:geocodingDescription ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcatde:geocodingDescription"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let geoDescIndex = 0;
                geoDescIndex <
                formValues.Additionals["dcatde:geocodingDescription"].length;
                geoDescIndex++
              ) {
                if (
                  refinedData.dataset["dcatde:geocodingDescription"] ===
                  undefined
                ) {
                  refinedData.dataset["dcatde:geocodingDescription"] = [];
                }
                refinedData.dataset["dcatde:geocodingDescription"].push({
                  "@value":
                    formValues.Additionals["dcatde:geocodingDescription"][
                      geoDescIndex
                    ]["@value"],
                  "@language": "de",
                });
              }
            }
            // ############################### dct:identifier ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:identifier"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let identifierIndex = 0;
                identifierIndex <
                formValues.Additionals["dct:identifier"].length;
                identifierIndex++
              ) {
                if (refinedData.dataset["dct:identifier"] === undefined) {
                  refinedData.dataset["dct:identifier"] = [];
                }
                refinedData.dataset["dct:identifier"].push({
                  "@value":
                    formValues.Additionals["dct:identifier"][identifierIndex][
                      "@value"
                    ],
                });
              }
            }
            // ############################### adms:identifier ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "adms:identifier"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let admsIdentIndex = 0;
                admsIdentIndex <
                formValues.Additionals["adms:identifier"].length;
                admsIdentIndex++
              ) {
                if (refinedData.dataset["adms:identifier"] === undefined) {
                  refinedData.dataset["adms:identifier"] = [];
                }
                refinedData.dataset["adms:identifier"].push({
                  // "@id":
                  //   formValues.Additionals["adms:identifier"][admsIdentIndex][
                  //     "@id"
                  //   ],
                  "@type": "adms:Identifier",
                  "skos:notation": {
                    "@value":
                      formValues.Additionals["adms:identifier"][admsIdentIndex][
                        "@id"
                      ],
                  },
                });
              }
            }
            // ############################### dct:language ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dct:language"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let admsIdentIndex = 0;
                admsIdentIndex < formValues.Additionals["dct:language"].length;
                admsIdentIndex++
              ) {
                if (refinedData.dataset["dct:language"] === undefined) {
                  refinedData.dataset["dct:language"] = [];
                }
                refinedData.dataset["dct:language"].push({
                  "@id":
                    formValues.Additionals["dct:language"][admsIdentIndex][
                      "uri"
                    ],
                });
              }
            }
            // ############################### adms:versionNotes ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "adms:versionNotes"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let admsIdentIndex = 0;
                admsIdentIndex <
                formValues.Additionals["adms:versionNotes"].length;
                admsIdentIndex++
              ) {
                if (refinedData.dataset["adms:versionNotes"] === undefined) {
                  refinedData.dataset["adms:versionNotes"] = [];
                }
                refinedData.dataset["adms:versionNotes"].push({
                  "@value":
                    formValues.Additionals["adms:versionNotes"][admsIdentIndex][
                      "@value"
                    ],
                });
              }
            }
            // ############################### dcatde:legalBasis ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcatde:legalBasis"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );

              for (
                let index = 0;
                index < formValues.Additionals["dcatde:legalBasis"].length;
                index++
              ) {
                if (refinedData.dataset["dcatde:legalBasis"] === undefined) {
                  refinedData.dataset["dcatde:legalBasis"] = [];
                }
                refinedData.dataset["dcatde:legalBasis"].push({
                  "@value":
                    formValues.Additionals["dcatde:legalBasis"][index][
                      "@value"
                    ],
                  "@language":
                    formValues.Additionals?.["dcatde:legalBasis"]?.[index]?.[
                      "@language"
                    ] || "de",
                });
              }
            }
            // ############################### dct:relation ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dct:relation"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:relation"].length;
                index++
              ) {
                if (refinedData.dataset["dct:relation"] === undefined) {
                  refinedData.dataset["dct:relation"] = [];
                }
                refinedData.dataset["dct:relation"].push({
                  "@id": formValues.Additionals["dct:relation"][index]["@id"],
                });
              }
            }
            // ############################### dcat:landingPage ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcat:landingPage"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dcat:landingPage"].length;
                index++
              ) {
                if (refinedData.dataset["dcat:landingPage"] === undefined) {
                  refinedData.dataset["dcat:landingPage"] = [];
                }
                refinedData.dataset["dcat:landingPage"].push({
                  "@id":
                    formValues.Additionals["dcat:landingPage"][index]["@id"],
                });
              }
            }
            // ############################### dct:conformsTo ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:conformsTo"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:conformsTo"].length;
                index++
              ) {
                if (refinedData.dataset["dct:conformsTo"] === undefined) {
                  refinedData.dataset["dct:conformsTo"] = [];
                }
                refinedData.dataset["dct:conformsTo"].push({
                  "rdfs:label":
                    formValues.Additionals["dct:conformsTo"][index][
                      "rdfs:label"
                    ],
                  "@id": formValues.Additionals["dct:conformsTo"][index]["@id"],
                });
              }
            }
            // ############################### dct:provenance ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:provenance"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:provenance"].length;
                index++
              ) {
                if (refinedData.dataset["dct:provenance"] === undefined) {
                  refinedData.dataset["dct:provenance"] = [];
                }
                refinedData.dataset["dct:provenance"].push({
                  "rdfs:label":
                    formValues.Additionals["dct:provenance"][index][
                      "rdfs:label"
                    ],
                });
              }
            }
            // ############################### prov:wasGeneratedBy ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "prov:wasGeneratedBy"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["prov:wasGeneratedBy"].length;
                index++
              ) {
                if (refinedData.dataset["prov:wasGeneratedBy"] === undefined) {
                  refinedData.dataset["prov:wasGeneratedBy"] = [];
                }
                refinedData.dataset["prov:wasGeneratedBy"].push({
                  "@id":
                    formValues.Additionals["prov:wasGeneratedBy"][index]["@id"],
                });
              }
            }
            // ############################### prov:qualifiedAttribution ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "prov:qualifiedAttribution"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index <
                formValues.Additionals["prov:qualifiedAttribution"].length;
                index++
              ) {
                if (
                  refinedData.dataset["prov:qualifiedAttribution"] === undefined
                ) {
                  refinedData.dataset["prov:qualifiedAttribution"] = [];
                }
                refinedData.dataset["prov:qualifiedAttribution"].push({
                  "@id":
                    formValues.Additionals["prov:qualifiedAttribution"][index][
                      "@id"
                    ],
                });
              }
            }
            // ############################### dcat:qualifiedRelation ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcat:qualifiedRelation"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dcat:qualifiedRelation"].length;
                index++
              ) {
                if (
                  refinedData.dataset["dcat:qualifiedRelation"] === undefined
                ) {
                  refinedData.dataset["dcat:qualifiedRelation"] = [];
                }
                refinedData.dataset["dcat:qualifiedRelation"].push({
                  "@id":
                    formValues.Additionals["dcat:qualifiedRelation"][index][
                      "@id"
                    ],
                });
              }
            }
            // ############################### dct:isReferencedBy ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:isReferencedBy"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:isReferencedBy"].length;
                index++
              ) {
                if (refinedData.dataset["dct:isReferencedBy"] === undefined) {
                  refinedData.dataset["dct:isReferencedBy"] = [];
                }
                refinedData.dataset["dct:isReferencedBy"].push({
                  "@id":
                    formValues.Additionals["dct:isReferencedBy"][index]["@id"],
                });
              }
            }
            // ############################### dct:source ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dct:source"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:source"].length;
                index++
              ) {
                if (refinedData.dataset["dct:source"] === undefined) {
                  refinedData.dataset["dct:source"] = [];
                }
                refinedData.dataset["dct:source"].push({
                  "@id": formValues.Additionals["dct:source"][index]["@id"],
                });
              }
            }
            // ############################### dct:hasVersion ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:hasVersion"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:hasVersion"].length;
                index++
              ) {
                if (refinedData.dataset["dct:hasVersion"] === undefined) {
                  refinedData.dataset["dct:hasVersion"] = [];
                }
                refinedData.dataset["dct:hasVersion"].push({
                  "@id": formValues.Additionals["dct:hasVersion"][index]["@id"],
                });
              }
            }
            // ############################### dct:isVersionOf ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:isVersionOf"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:isVersionOf"].length;
                index++
              ) {
                if (refinedData.dataset["dct:isVersionOf"] === undefined) {
                  refinedData.dataset["dct:isVersionOf"] = [];
                }
                refinedData.dataset["dct:isVersionOf"].push({
                  "@id":
                    formValues.Additionals["dct:isVersionOf"][index]["@id"],
                });
              }
            }
            // ############################### dct:creator ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dct:creator"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:creator"].length;
                index++
              ) {
                if (refinedData.dataset["dct:creator"] === undefined) {
                  refinedData.dataset["dct:creator"] = [];
                }
                let type;
                if (
                  formValues.Additionals["dct:creator"][index]["rdf:type"] ===
                  "Person"
                ) {
                  type = "foaf:Agent";
                }
                if (
                  formValues.Additionals["dct:creator"][index]["rdf:type"] ===
                  "Organisation"
                ) {
                  type = "foaf:Organization";
                }
                refinedData.dataset["dct:creator"].push({
                  "@type": type,
                  "foaf:name":
                    formValues.Additionals["dct:creator"][index]["foaf:name"],
                  "foaf:mbox":
                    formValues.Additionals["dct:creator"][index]["foaf:mbox"],
                  "foaf:homepage":
                    formValues.Additionals["dct:creator"][index][
                      "foaf:homepage"
                    ],
                });
              }
            }
            // ############################### foaf:page ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "foaf:page"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["foaf:page"].length;
                index++
              ) {
                if (refinedData.dataset["foaf:page"] === undefined) {
                  refinedData.dataset["foaf:page"] = [];
                }
                refinedData.dataset["foaf:page"].push({
                  "dct:format": {
                    "@id": formValues.Additionals["foaf:page"]?.[index]?.uri,
                  },
                  "dct:title":
                    formValues.Additionals["foaf:page"][index]["dct:title"],
                  "dct:description":
                    formValues.Additionals["foaf:page"][index][
                      "dct:description"
                    ],
                  "foaf:homepage":
                    formValues.Additionals["foaf:page"][index]["foaf:homepage"],
                });
              }
            }
            // ############################### dct:contributor ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:contributor"
            ) {
              console.log(
                "#####################",
                Object.keys(formValues.Additionals)[innerIndex],
              );
              for (
                let index = 0;
                index < formValues.Additionals["dct:contributor"].length;
                index++
              ) {
                if (refinedData.dataset["dct:contributor"] === undefined) {
                  refinedData.dataset["dct:contributor"] = [];
                }
                let type;
                if (
                  formValues.Additionals["dct:contributor"][index][
                    "rdf:type"
                  ] === "Person"
                ) {
                  type = "foaf:Agent";
                }
                if (
                  formValues.Additionals["dct:contributor"][index][
                    "rdf:type"
                  ] === "Organisation"
                ) {
                  type = "foaf:Organization";
                }
                console.log(type);

                refinedData.dataset["dct:contributor"].push({
                  "@type": type,
                  "foaf:name":
                    formValues.Additionals["dct:contributor"][index][
                      "foaf:name"
                    ],
                  "foaf:mbox":
                    formValues.Additionals["dct:contributor"][index][
                      "foaf:mbox"
                    ],
                  "foaf:homepage":
                    formValues.Additionals["dct:contributor"][index][
                      "foaf:homepage"
                    ],
                });
              }
            }
            // ############################### owl:versionInfo ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "owl:versionInfo"
            ) {
              refinedData.dataset["owl:versionInfo"] = {
                "@value":
                  formValues.Additionals["owl:versionInfo"][0]["@value"],
              };
            }
            // ############################### dcatde:contributorID ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcatde:contributorID"
            ) {
              refinedData.dataset["dcatde:contributorID"] = {
                "@id": formValues.Additionals["dcatde:contributorID"][0].uri,
              };
            }
            // ############################### dct:accessRights ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:accessRights"
            ) {
              refinedData.dataset["dct:accessRights"] = {
                "@id": formValues.Additionals["dct:accessRights"][0].uri,
              };
            }
            // ############################### dct:accrualPeriodicity ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dct:accrualPeriodicity"
            ) {
              refinedData.dataset["dct:accrualPeriodicity"] = {
                "@id": formValues.Additionals["dct:accrualPeriodicity"][0].uri,
              };
            }
            // ############################### dct:type ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] === "dct:type"
            ) {
              refinedData.dataset["dct:type"] = {
                "@id": formValues.Additionals["dct:type"][0].uri,
              };
            }
            // ############################### dcat:spatialResolutionInMeters ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcat:spatialResolutionInMeters"
            ) {
              refinedData.dataset["dcat:spatialResolutionInMeters"] = {
                "@value":
                  formValues.Additionals["dcat:spatialResolutionInMeters"][0][
                    "@value"
                  ] || "",
                "@type": "http://www.w3.org/2001/XMLSchema#decimal",
              };
            }
            // ############################### dcat:temporalResolution ###############################
            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcat:temporalResolution"
            ) {
              console.log(formValues.Additionals["dcat:temporalResolution"]);

              // ToDo Need to make sure, that the String will generated correctly if some properties arent set!
              refinedData.dataset["dcat:temporalResolution"] = {
                "@type": "http://www.w3.org/2001/XMLSchema#duration",
                "@value": `P${formValues.Additionals["dcat:temporalResolution"][0].Year}Y${formValues.Additionals["dcat:temporalResolution"][1].Month}M${formValues.Additionals["dcat:temporalResolution"][2].Day}D${formValues.Additionals["dcat:temporalResolution"][3].Hour}H${formValues.Additionals["dcat:temporalResolution"][4].Minute}M${formValues.Additionals["dcat:temporalResolution"][5].Second}S`,
              };
            }
            // ############################### dcatde:politicalGeocodingLevelURI ###############################

            if (
              Object.keys(formValues.Additionals)[innerIndex] ===
              "dcatde:politicalGeocodingLevelURI"
            ) {
              for (
                let index = 0;
                index <
                formValues.Additionals["dcatde:politicalGeocodingLevelURI"]
                  .length;
                index++
              ) {
                if (
                  refinedData.dataset["dcatde:politicalGeocodingLevelURI"] ===
                  undefined
                ) {
                  refinedData.dataset["dcatde:politicalGeocodingLevelURI"] = [];
                }
                refinedData.dataset["dcatde:politicalGeocodingLevelURI"].push({
                  "@id":
                    formValues.Additionals[
                      "dcatde:politicalGeocodingLevelURI"
                    ]?.[index]?.uri ||
                    formValues.Additionals[
                      "dcatde:politicalGeocodingLevelURI"
                    ]?.[index]?.["@id"] ||
                    "",
                });
              }
            }
          }
        } catch (error) {
          console.error("Validation Error:", error.message);
          throw error;
        }
      }
    }
  } catch (error) {
    console.log(error);
  }

  let body = transformToJSONLD(refinedData, prefixes);

  // console.log("#############", refinedData["dataset"]);

  let actionParams = {
    id: datasetId,
    catalog: userData.permissions[0].rsname,
    body,
    title: refinedData.dataset["dct:title"]["@value"],
    description: refinedData.dataset["dct:description"]["@value"],
  };

  return { actionParams, body };
}
function transformToJSONLD(data, prefixes) {
  const resultObject = {};
  const propertyKeys = Object.keys(data);
  for (let index = 0; index < propertyKeys.length; index++) {
    if (propertyKeys[index] === "dct:title" || "dct:description") {
      // console.log(resultObject, "Prop:", propertyKeys[index]);
      resultObject[propertyKeys[index]] = data[propertyKeys[index]];
    }
  }
  const graphArray = [
    {
      ...resultObject.dataset,
    },
    resultObject.distribution,
    {
      ...resultObject.meta,
    },
  ];

  let jsonldData = {
    "@graph": graphArray,
    "@context": prefixes, // Füge die Prefixes hinzu
  };
  console.log(jsonldData);
  return jsonldData;
}
export default sendDataToAPI;
