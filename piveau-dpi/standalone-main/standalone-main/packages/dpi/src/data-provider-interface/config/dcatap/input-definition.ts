import { type FormKitSchemaDefinition } from '@formkit/core';
import { markRaw } from 'vue';

import language from '../selector-languages.json';
import config from './page-content-config';

/**
 * Available properties for datasets.
 */
export type DcatApDatasetsProperty =
  // Append new properties here for accurate type checking
  'datasetID'
  | 'overview'
  | 'description'
  | 'title'
  | 'contactPoint'
  | 'subject'
  | 'keyword'
  | 'publisher'
  | 'spatial'
  | 'temporal'
  | 'theme'
  | 'accessRights'
  | 'creator'
  | 'conformsTo'
  | 'page'
  | 'accrualPeriodicity'
  | 'hasVersion'
  | 'isVersionOf'
  | 'source'
  | 'identifier'
  | 'isReferencedBy'
  | 'landingPage'
  | 'language'
  | 'admsIdentifier'
  | 'provenance'
  | 'qualifiedAttribution'
  | 'wasGeneratedBy'
  | 'qualifiedRelation'
  | 'relation'
  | 'issued'
  | 'modified'
  | 'spatialResolutionInMeters'
  | 'temporalResolution'
  | 'type'
  | 'versionInfo'
  | 'versionNotes'
  | 'catalog'
  | 'isUsedBy';

/**
 * Available properties for distributions.
 */
export type DcatApDistributionsProperty =
  // Append new properties here for accurate type checking
  'accessURL'
  | 'availability'
  | 'description'
  | 'format'
  | 'title'
  | 'byteSize'
  | 'conformsTo'
  | 'issued'
  | 'modified'
  | 'rights'
  | 'spatialResolutionInMeters'
  | 'temporalResolution'
  | 'mediaType'
  | 'downloadUrl'
  | 'accessService'
  | 'checksum'
  | 'compressFormat'
  | 'packageFormat'
  | 'page'
  | 'hasPolicy'
  | 'language'
  | 'licence'
  | 'conformsTo'
  | 'issued'
  | 'modified'
  | 'rights'
  | 'spatialResolutionInMeters'
  | 'temporalResolution'
  | 'type'
  | 'status';

export type DcatApCataloguesProperty =
  // Append new properties here for accurate type checking
  'datasetID'
  | 'overview'
  | 'title'
  | 'description'
  | 'publisher'
  | 'language'
  | 'licence'
  | 'spatial'
  | 'homepage'
  | 'hasPart'
  | 'isPartOf'
  | 'rights'
  | 'catalog'
  | 'creator';

export type InputDefinition = {
  datasets: Record<DcatApDatasetsProperty, FormKitSchemaDefinition>;
  distributions: Record<DcatApDistributionsProperty, FormKitSchemaDefinition>;
  catalogues: Record<DcatApCataloguesProperty, FormKitSchemaDefinition>;
}


const dcatapProperties: InputDefinition = {
  datasets: {
    overview: {
      $cmp: 'OverviewPage',
      props: {
        property: 'datasets'
      }
    },
    datasetID: {
      identifier: 'datasetID',
      $formkit: 'id',
      mandatory: true,
      name: 'datasetID',
      id: 'datasetID',
    },
    description: {
      identifier: 'description',
      $formkit: 'repeatable',
      name: 'dct:description',
      minimum: 1,
      children: [
        {
          identifier: 'datasetDescription',
          $formkit: 'group',
          name: 'dct:description',
          children: [
            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              options: language,
              validation: 'required',
              name: '@language',
              classes: {
                outer: 'w25-textfield'
              }
            },
            {
              identifier: 'description',
              $formkit: 'textarea',
              mandatory: true,
              name: '@value',
              validation: 'required',
              classes: {
                outer: 'w75-descField'
              }
            },
          ],
        }
      ]
    },
    title: {
      id: 'title',
      identifier: 'title',
      $formkit: 'repeatable',
      name: 'dct:title',
      children: [
        {
          identifier: 'datasetTitle',
          $formkit: 'group',
          name: 'dct:title',
          minimum: 1,
          children: [
            {
              identifier: 'dctTitle',
              $formkit: 'select',
              validation: 'required',
              options: language,
              value: 'en',
              name: '@language',
              classes: {
                outer: 'w25-textfield'
              }
            },
            {
              identifier: 'title',
              $formkit: 'text',
              name: '@value',
              validation: 'required',
              mandatory: true,
              classes: {
                outer: 'w75-textfield'
              }
            },
          ],
        }
      ]
    },
    contactPoint: {
      identifier: 'contactPoint',
      $formkit: 'repeatable',
      name: 'dcat:contactPoint',
      children: [
        {
          identifier: 'contactPoint',
          $formkit: 'group',
          name: 'dcat:contactPoint',
          children: [
            {
              identifier: 'contactPointType',
              $formkit: 'select',
              name: 'rdf:type',
              options: {
                '': '---',
                'vcard:Individual': 'Person',
                'vcard:Organization': 'Organization',
              },
              classes: {
                outer: 'w97-textfield'
              },
            },
            {
              identifier: 'contactPointName',
              $formkit: 'text',
              name: 'vcard:fn',
              classes: {
                outer: 'w97-textfield'
              },
            },
            {
              identifier: 'contactPointEmail',
              $formkit: 'email',
              name: 'vcard:hasEmail',
              validation: 'optional|email',
              classes: {
                outer: 'w97-textfield'
              },
            },
            {
              identifier: 'contactPointAddress',
              $formkit: 'group',
              name: 'vcard:hasAddress',
              classes: {
                outer: 'w97-textfield'
              },
              children: [
                {
                  identifier: 'contactPointAddressStreet',
                  $formkit: 'text',
                  name: 'vcard:street_address',
                  classes: {
                    outer: 'w97-textfield'
                  },
                },
                {
                  identifier: 'contactPointAddressPostcode',
                  $formkit: 'text',
                  name: 'vcard:postal_code',
                  classes: {
                    outer: 'w97-textfield'
                  },
                },
                {
                  identifier: 'contactPointAddressCity',
                  $formkit: 'text',
                  name: 'vcard:locality',
                  classes: {
                    outer: 'w97-textfield'
                  },
                },
                {
                  identifier: 'contactPointAddressCountry',
                  $formkit: 'text',
                  name: 'vcard:country_name',
                  classes: {
                    outer: 'w97-textfield'
                  },
                },
              ],
            },
            {
              identifier: 'contactPointTelephone',
              $formkit: 'tel',
              name: 'vcard:hasTelephone',
              classes: {
                outer: 'w97-textfield'
              },
            },
            {
              identifier: 'contactPointUrl',
              $formkit: 'url',
              name: 'vcard:hasURL',
              validation: 'optional|url',
              classes: {
                outer: 'w97-textfield'
              },
            },
            {
              identifier: 'contactPointOrganisationName',
              $formkit: 'text',
              name: 'vcard:hasOrganizationName',
              classes: {
                outer: 'w97-textfield'
              },
            },
          ],
        }
      ]
    },
    subject: {
      identifier: 'subject',
      $formkit: 'auto',
      multiple: true,
      annifTheme: true,
      voc: 'eurovoc',
      name: 'dct:subject',
      '@annifSuggestion': false,
      id: 'subjectDataset',
      classes: {
        outer: 'w88-textfield'
      }
    },
    keyword: {
      identifier: 'keyword',
      $formkit: 'repeatable',
      name: 'dcat:keyword',
      children: [
        {
          identifier: 'keywordHeader',
          $formkit: 'group',
          name: 'dcat:keyword',
          children: [
            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              name: '@language',
              options: language,
              classes: {
                outer: 'w25-textfield'
              }
            },
            {
              identifier: 'keyword',
              $formkit: 'text',
              name: '@value',
              classes: {
                outer: 'w75-textfield'
              }
            },
          ],
        }
      ]
    },
    publisher: {
      $formkit: 'auto',
      identifier: 'publisher',
      voc: 'corporate-body',
      property: 'dct:publisher',
      id: 'publisherDataset',
      name: 'dct:publisher',
    },
    spatial: {
      identifier: 'spatial',
      $formkit: 'repeatable',
      name: 'dct:spatial',
      children: [
        {
          $formkit: 'spatialinput',
          name: 'dct:spatial',
          identifier: 'spatial',
        }]
    },
    temporal: {
      identifier: 'temporal',
      $formkit: 'repeatable',
      name: 'dct:temporal',
      children: [
        {
          $formkit: 'group',
          name: 'dct:temporal',
          identifier: 'temporal',
          children: [
            {
              identifier: 'temporalStart',
              $formkit: 'datetime-local',
              name: 'dcat:startDate',
              end: 'dct:temporal',
              classes: {
                outer: 'w100-textfield'
              },
            },
            {
              identifier: 'temporalEnd',
              $formkit: 'datetime-local',
              name: 'dcat:endDate',
              start: 'dct:temporal',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    theme: {
      identifier: 'theme',
      $formkit: 'auto',
      multiple: true,
      annifTheme: true,
      voc: 'data-theme',
      '@annifSuggestion': false,
      name: 'dcat:theme',
      id: 'theme',
    },
    accessRights: {
      identifier: 'accessRights',
      $formkit: 'auto',
      voc: 'access-right',
      name: 'dct:accessRights',
      id: 'accessRights'
    },
    creator: {
      identifier: 'creator',
      $formkit: 'formkitGroup',
      name: 'dct:creator',
      children: [
        {
          identifier: 'creatorType',
          $formkit: 'select',
          name: 'rdf:type',
          options: {
            '': '---',
            'foaf:Person': 'Person',
            'foaf:Organization': 'Organization',
          },
        },
        {
          identifier: 'creatorName',
          $formkit: 'text',
          name: 'foaf:name',
        },
        {
          identifier: 'creatorEmail',
          $formkit: 'email',
          name: 'foaf:mbox',
          validation: 'optional|email',
        },
        {
          identifier: 'creatorHomepage',
          $formkit: 'url',
          name: 'foaf:homepage',
          validation: 'optional|url',
        },
      ],
    },
    conformsTo: {
      identifier: 'conformsTo',
      $formkit: 'repeatable',
      name: 'dct:conformsTo',
      children: [
        {
          identifier: 'conformsTo',
          $formkit: 'group',
          name: 'dct:conformsTo',
          children: [
            {
              identifier: 'conformsToTitle',
              $formkit: 'text',
              name: 'rdfs:label',
              classes: {
                outer: 'w100-textfield'
              }
            },
            {
              identifier: 'conformsToUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
    page: {
      identifier: 'page',
      $formkit: 'repeatable',
      name: 'foaf:page',
      children: [
        {
          identifier: 'page',
          $formkit: 'group',
          name: 'foaf:page',
          children: [
            {
              identifier: 'pageTitle',
              $formkit: 'repeatable',
              name: 'dct:title',
              children: [
                {
                  identifier: 'pageTitle',
                  $formkit: 'group',
                  name: 'dct:title',
                  children: [
                    {
                      identifier: 'language',
                      value: 'en',
                      $formkit: 'select',
                      options: language,
                      name: '@language',
                      classes: {
                        outer: 'w25-textfield'
                      },
                    },
                    {
                      identifier: 'pageTitleSub',
                      $formkit: 'text',
                      name: '@value',
                      classes: {
                        outer: 'w75-textfield'
                      },
                    },
                  ]
                }
              ]
            },
            {
              identifier: 'pageDescription',
              $formkit: 'repeatable',
              name: 'dct:description',
              children: [
                {
                  identifier: 'pageDescription',
                  $formkit: 'group',
                  name: 'dct:description',
                  children: [

                    {
                      identifier: 'language',
                      value: 'en',
                      $formkit: 'select',
                      options: language,
                      name: '@language',
                      classes: {
                        outer: 'w25-textfield'
                      },
                    },
                    {
                      identifier: 'pageDescription',
                      $formkit: 'textarea',
                      name: '@value',
                      classes: {
                        outer: 'w75-textfield'
                      },
                    },
                  ]
                }
              ]
            },
            {
              $formkit: 'auto',
              identifier: 'pageFormat',
              voc: 'file-type',
              class: "property",
              name: 'dct:format',
              id: 'pageFormat',
              classes: {

                outer: 'w97-textfield'
              }
            },
            {
              identifier: 'pageUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              class: "property",
              classes: {
                outer: 'w97-textfield'
              },
            },
          ],
        }
      ]
    },
    accrualPeriodicity: {
      identifier: 'accrualPeriodicity',
      $formkit: 'auto',
      voc: 'frequency',
      name: 'dct:accrualPeriodicity',
      id: 'accrualPeriodicity',
      classes: {
        outer: 'w88-textfield'
      },

    },
    hasVersion: {
      identifier: 'hasVersion',
      $formkit: 'repeatable',
      name: 'dct:hasVersion',
      children: [
        {
          $formkit: 'group',
          identifier: 'hasVersion',
          name: 'dct:hasVersion',
          children: [
            {
              identifier: 'hasVersionUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    isVersionOf: {
      identifier: 'isVersionOf',
      $formkit: 'repeatable',
      name: 'dct:isVersionOf',
      children: [
        {
          $formkit: 'group',
          identifier: 'isVersionOf',
          name: 'dct:isVersionOf',
          children: [
            {
              identifier: 'isVersionOfUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    source: {
      identifier: 'source',
      $formkit: 'repeatable',
      name: 'dct:source',
      children: [
        {
          $formkit: 'group',
          identifier: 'source',
          name: 'dct:source',
          children: [
            {
              name: '@id',
              identifier: 'sourceUrl',
              $formkit: 'url',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    identifier: {
      identifier: 'identifier',
      $formkit: 'repeatable',
      name: 'dct:identifier',
      children: [
        {
          $formkit: 'group',
          name: 'dct:identifier',
          identifier: 'identifier',
          children: [
            {
              identifier: 'identifier',
              name: '@value',
              $formkit: 'text',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
    isReferencedBy: {
      identifier: 'isReferencedBy',
      $formkit: 'repeatable',
      name: 'dct:isReferencedBy',
      id: 'dct:isReferencedBy',
      children: [
        {
          $formkit: 'group',
          identifier: 'isReferencedBy',
          name: 'dct:isReferencedBy',
          children: [
            {
              identifier: 'isReferencedByUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    landingPage: {
      identifier: 'landingPage',
      $formkit: 'repeatable',
      name: 'dcat:landingPage',
      children: [
        {
          $formkit: 'group',
          identifier: 'landingPage',
          name: 'dcat:landingPage',
          children: [
            {
              identifier: 'landingPageUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
    language: {
      identifier: 'language',
      $formkit: 'auto',
      name: 'dct:language',
      multiple: true,
      voc: 'language',
      id: 'language'
    },
    admsIdentifier: {
      identifier: 'admsIdentifier',
      $formkit: 'repeatable',
      name: 'adms:identifier',

      children: [
        {
          $formkit: 'group',
          name: 'adms:identifier',
          identifier: 'admsIdentifier',
          children: [
            {
              identifier: 'admsIdentifierUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {

                outer: 'w97-textfield'
              },
            },
            {
              identifier: 'admsIdentifierSkosNotation',
              $formkit: 'group',
              name: 'skos:notation',
              children: [
                {
                  identifier: 'admsIdentifierValue',
                  $formkit: 'text',
                  name: '@value',
                  classes: {

                    outer: 'w97-textfield'
                  },
                },
                {
                  // todo: check if this is correct
                  $formkit: 'auto',
                  identifier: 'admsIdentifierType',
                  voc: 'notation-type',
                  name: '@type',
                  id: 'admsIdentifierType',
                  classes: {

                    outer: 'w97-textfield'
                  },
                },
              ],
            },
          ],
        }
      ]
    },
    provenance: {
      identifier: 'provenanceGroup',
      $formkit: 'repeatable',
      name: 'dct:provenance',
      children: [
        {
          $formkit: 'group',
          identifier: 'provenanceGroup',
          name: 'dct:provenance',
          children: [
            {
              identifier: 'provenance',
              $formkit: 'text',
              name: 'rdfs:label',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    qualifiedAttribution: {
      identifier: 'qualifiedAttribution',
      $formkit: 'repeatable',
      name: 'prov:qualifiedAttribution',
      children: [
        {
          $formkit: 'group',
          identifier: 'qualifiedAttribution',
          name: 'prov:qualifiedAttribution',
          children: [
            {
              identifier: 'qualifiedAttributionUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    wasGeneratedBy: {
      identifier: 'wasGeneratedBy',
      $formkit: 'repeatable',
      name: 'prov:wasGeneratedBy',
      children: [
        {
          $formkit: 'group',
          identifier: 'wasGeneratedBy',
          name: 'prov:wasGeneratedBy',
          children: [
            {
              identifier: 'wasGeneratedByUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    qualifiedRelation: {
      identifier: 'qualifiedRelation',
      $formkit: 'repeatable',
      name: 'dcat:qualifiedRelation',
      children: [
        {
          $formkit: 'group',
          identifier: 'qualifiedRelation',
          name: 'dcat:qualifiedRelation',
          children: [
            {
              identifier: 'qualifiedRelationUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    relation: {
      identifier: 'relation',
      $formkit: 'repeatable',
      name: 'dct:relation',
      children: [
        {
          $formkit: 'group',
          identifier: 'relation',
          name: 'dct:relation',
          children: [
            {
              identifier: 'relationUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    issued: {
      identifier: 'issued',
      $formkit: 'formkitGroup',
      name: 'dct:issued',

      children: [
        {
          identifier: 'issued',
          id: 'issuedCondDataset',
          classes: {
            outer: 'w-100'
          },
          $formkit: 'select',
          name: '@type',
          options: { date: 'Date', datetime: 'Datetime' },

        },
        {
          identifier: 'issued',
          $cmp: 'FormKit',
          if: '$get(issuedCondDataset).value',
          props: {
            if: '$get(issuedCondDataset).value === date',
            then: {
              type: 'date',
              name: '@value',
              classes: {
                outer: 'w-100'
              },
              // validation: 'optional|date_after:' + new Date(new Date().getTime() - (24 * 60 * 60 * 1000)),
              // 'validation-visibility': 'live',
            },
            else: {
              type: 'datetime-local',
              name: '@value',
              classes: {
                outer: 'w-100'
              },
              // validation: 'optional|date_after:' + new Date(new Date().getTime() - (24 * 60 * 60 * 1000)),
              // 'validation-visibility': 'live',
            }
          }
        },
      ]
    },
    modified: {
      identifier: 'modified',
      $formkit: 'formkitGroup',
      name: 'dct:modified',
      children: [
        {
          identifier: 'modified',
          id: 'modifiedCondDataset',
          name: '@type',
          classes: {
            outer: 'w-100'
          },
          $formkit: 'select',
          options: { date: 'Date', datetime: 'Datetime' },
        },
        {
          identifier: 'modified',
          $cmp: 'FormKit',
          if: '$get(modifiedCondDataset).value',
          props: {
            name: 'dct:modified',
            if: '$get(modifiedCondDataset).value === date',
            then: {
              type: 'date',
              name: '@value',
              classes: {
                outer: 'w-100'
              },
              // validation: 'optional|date_after:' + new Date(new Date().getTime() - (24 * 60 * 60 * 1000)),
              // 'validation-visibility': 'live',
            },
            else: {
              type: 'datetime-local',
              name: '@value',
              classes: {
                outer: 'w-100'
              },
              // validation: 'optional|date_after:' + new Date(new Date().getTime() - (24 * 60 * 60 * 1000)),
              // 'validation-visibility': 'live',
            }
          }
        },
      ]
    },
    spatialResolutionInMeters: {
      identifier: 'spatialResolutionInMeters',
      $formkit: 'simpleInput',
      name: 'dcat:spatialResolutionInMeters',
      validationType: 'number',

    },
    temporalResolution: {
      identifier: 'temporalResolution',
      $formkit: 'formkitGroup',
      name: 'dcat:temporalResolution',
      children: [
        {
          identifier: 'temporalResolutionYear',
          $formkit: 'number',
          validation: 'min:1950|max:2100|optional',
          name: 'Year',
        },
        {
          identifier: 'temporalResolutionMonth',
          $formkit: 'number',
          validation: 'min:0|max:12|optional',
          name: 'Month',
        },
        {
          identifier: 'temporalResolutionDay',
          $formkit: 'number',
          validation: 'min:0|max:31|optional',
          name: 'Day',
        },
        {
          identifier: 'temporalResolutionHour',
          $formkit: 'number',
          validation: 'min:0|max:23|optional',
          name: 'Hour',
        },
        {
          identifier: 'temporalResolutionMinute',
          $formkit: 'number',
          validation: 'min:0|max:59|optional',
          name: 'Minute',
        },
        {
          identifier: 'temporalResolutionSecond',
          $formkit: 'number',
          validation: 'min:0|max:59|optional',
          name: 'Second',
        },
      ],
    },
    type: {
      identifier: 'type',
      $formkit: 'auto',
      voc: 'dataset-type',
      name: 'dct:type',
      id: 'type'
    },
    versionInfo: {
      identifier: 'versionInfo',
      $formkit: 'simpleInput',
      name: 'owl:versionInfo',
      validationType: 'number'
    },
    versionNotes: {
      identifier: 'versionNotes',
      $formkit: 'repeatable',
      name: 'adms:versionNotes',
      children: [
        {
          identifier: 'versionNotes',
          $formkit: 'group',
          name: 'adms:versionNotes',
          children: [
            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              name: '@language',
              options: language,
              classes: {
                outer: 'w25-textfield'
              },
            },
            {
              identifier: 'versionNotes',
              $formkit: 'textarea',
              name: '@value',
              classes: {
                outer: 'w75-textfield'
              },
            },
          ],
        }
      ]
    },
    catalog: {
      identifier: 'catalog',
      $formkit: 'simpleSelect',
      name: 'dcat:catalog',
      id: 'dcat:catalog',
      placeholder: 'Catalog',
      classes: { outer: 'formkitProperty formkitCmpWrap mx-0 my-3 p-3' }
    },
    isUsedBy: {
      identifier: 'isUsedBy',
      $formkit: 'repeatable',
      name: 'dext:metadataExtension',
      children: [
        {
          $formkit: 'group',
          identifier: 'isUsedBy',
          name: 'dext:metadataExtension',
          children: [
            {
              $formkit: 'url',
              identifier: 'isUsedBy',
              validation: 'optional|url',
              name: 'dext:isUsedBy',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
  },
  distributions: {
    accessURL: {
      identifier: 'accessUrl',
      $formkit: 'repeatable',
      name: 'dcat:accessURL',
      validation: 'required',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'accessUrl',
          name: 'dcat:accessURL',
          $formkit: 'fileupload',
        }
      ]
    },
    availability: {
      identifier: 'availability',
      $formkit: 'auto',
      voc: 'planned-availability',
      name: 'dcatap:availability',
      id: 'availability',
      class: 'property inDistribution',
    },
    description: {
      identifier: 'description',
      $formkit: 'repeatable',
      name: 'dct:description',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'description',
          $formkit: 'group',
          name: 'dct:description',
          mandatory: true,
          minimum: 1,
          children: [

            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              options: language,
              name: '@language',
              classes: {
                outer: 'w25-textfield'
              }
            },
            {
              identifier: 'description',
              $formkit: 'textarea',
              name: '@value',
              classes: {
                outer: 'w75-textfield'
              }
            },
          ],
        }
      ]
    },
    format: {
      identifier: 'format',
      $formkit: 'auto',
      voc: 'file-type',
      name: 'dct:format',
      class: 'property inDistribution',
      id: 'format',
      classes: {

        outer: 'w88-textfield'
      }
    },
    licence: {
      $formkit: 'simpleConditional',
      name: 'dct:license',
      identifier: 'licence',
      voc: 'licence',
      class: 'property inDistribution',
      options: { text: 'dct:title', textarea: 'skos:prefLabel', url: 'skos:exactMatch' },
      selection: { 1: 'vocabulary', 2: 'manually' }

    },
    title: {
      identifier: 'title',
      $formkit: 'repeatable',
      name: 'dct:title',
      class: 'property inDistribution langStringInput mandatory',
      children: [
        {
          identifier: 'title',
          $formkit: 'group',
          name: 'dct:title',
          mandatory: true,
          minimum: 1,
          children: [
            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              options: language,
              name: '@language',
              classes: {
                outer: 'w25-textfield'
              }
            },
            {
              identifier: 'title',
              $formkit: 'text',
              name: '@value',
              classes: {
                outer: 'w75-textfield'
              }

            },

          ],
        }
      ]
    },
    mediaType: {
      identifier: 'mediaType',
      $formkit: 'auto',
      voc: 'iana-media-types',
      name: 'dcat:mediaType',
      class: 'property inDistribution',
      id: 'mediaType'
    },
    downloadUrl: {
      identifier: 'downloadUrl',
      $formkit: 'repeatable',
      name: 'dcat:downloadURL',
      class: 'property inDistribution',
      children: [
        {
          $formkit: 'group',
          identifier: 'downloadUrl',
          name: 'dcat:downloadURL',
          children: [
            {
              identifier: 'downloadUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    accessService: {
      identifier: 'accessService',
      $formkit: 'repeatable',
      name: 'dcat:accessService',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'accessService',
          $formkit: 'group',
          name: 'dcat:accessService',
          children: [
            {
              identifier: 'accessServiceEndpointURL',
              $formkit: 'url',
              name: 'dcat:endpointURL',
              validation: 'optional|url',
            },
            {
              identifier: 'accessServiceTitle',
              $formkit: 'repeatable',
              name: 'dct:title',
              class: 'property langStringInput inDistribution',
              children: [
                {
                  identifier: 'accessServiceTitle',
                  $formkit: 'group',
                  name: 'dct:title',
                  children: [
                    {
                      identifier: 'language',
                      value: 'en',
                      $formkit: 'select',
                      name: '@language',
                      options: language,
                      classes: {
                        outer: 'w25-textfield'
                      }
                    },
                    {
                      identifier: 'title',
                      $formkit: 'text',
                      name: '@value',
                      classes: {
                        outer: 'w75-textfield'
                      }
                    },
                  ],
                }
              ]
            },
            {
              identifier: 'accessServiceDescription',
              $formkit: 'repeatable',
              name: 'dct:description',
              class: 'property langDescriptionInput inDistribution',
              children: [
                {
                  identifier: 'accessServiceDescription',
                  $formkit: 'group',
                  name: 'dct:description',
                  children: [
                    {
                      identifier: 'language',
                      value: 'en',
                      $formkit: 'select',
                      name: '@language',
                      options: language,
                      classes: {
                        outer: 'w25-textfield'
                      }
                    },
                    {
                      identifier: 'description',
                      $formkit: 'textarea',
                      name: '@value',
                      classes: {
                        outer: 'w75-descField'
                      }
                    }
                  ],
                }
              ]
            },
          ],
        }
      ]
    },
    byteSize: {
      identifier: 'byteSize',
      $formkit: 'simpleInput',
      name: 'dcat:byteSize',
      class: 'property inDistribution',
      validationType: 'number'
    },
    checksum: {
      $formkit: 'formkitGroup',
      identifier: 'checksum',
      class: 'property inDistribution',
      name: 'spdx:checksum',

      children: [
        {
          identifier: 'checksum',
          $formkit: 'text',
          name: 'spdx:checksumValue',
        },
        {
          label: 'test',
          $formkit: 'auto',
          identifier: 'checksumAlgorithm',
          voc: 'spdx-checksum-algorithm',
          name: 'spdx:algorithm',
        },
      ],
    },
    compressFormat: {
      identifier: 'compressFormat',
      $formkit: 'auto',
      class: 'property inDistribution',
      voc: 'iana-media-types',
      name: 'dcat:compressFormat',

    },
    packageFormat: {
      identifier: 'packageFormat',
      $formkit: 'auto',
      class: 'property inDistribution',
      voc: 'iana-media-types',
      name: 'dcat:packageFormat',

    },
    page: {
      identifier: 'page',
      $formkit: 'repeatable',
      name: 'foaf:page',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'page',
          $formkit: 'group',
          name: 'foaf:page',
          children: [
            {
              identifier: 'pageTitle',
              $formkit: 'repeatable',
              name: 'dct:title',
              children: [
                {
                  identifier: 'pageTitle',
                  $formkit: 'group',
                  name: 'dct:title',
                  children: [

                    {
                      identifier: 'language',
                      value: 'en',
                      $formkit: 'select',
                      options: language,
                      name: '@language',
                      classes: {
                        outer: 'w25-textfield'
                      },
                    },
                    {
                      identifier: 'pageTitle',
                      $formkit: 'text',
                      name: '@value',
                      classes: {
                        outer: 'w75-textfield'
                      },
                    },
                  ]
                }
              ]
            },
            {
              identifier: 'pageDescription',
              $formkit: 'repeatable',
              name: 'dct:description',
              children: [
                {
                  identifier: 'pageDescription',
                  $formkit: 'group',
                  name: 'dct:description',
                  children: [
                    {
                      identifier: 'language',
                      value: 'en',
                      $formkit: 'select',
                      options: language,
                      name: '@language',
                    },
                    {
                      identifier: 'pageDescription',
                      $formkit: 'textarea',
                      name: '@value',
                      classes: {
                        outer: 'w75-textfield'
                      },
                    }

                  ]
                }
              ]
            },
            {
              $formkit: 'auto',
              identifier: 'pageFormat',
              voc: 'file-type',
              name: 'dct:format',
              class: "property",
              classes: {

                outer: 'w88-textfield'
              }
            },
            {
              identifier: 'pageUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              class: "property",
            },
          ],
        }
      ]
    },
    hasPolicy: {
      identifier: 'hasPolicy',
      $formkit: 'repeatable',
      name: 'odrl:hasPolicy',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'hasPolicy',
          $formkit: 'group',
          name: 'odrl:hasPolicy',
          children: [
            {
              identifier: 'hasPolicyUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              },
            },
          ],
        }
      ]
    },
    language: {
      $formkit: 'auto',
      identifier: 'language',
      multiple: true,
      name: 'dct:language',
      class: 'property inDistribution',
      voc: 'language',

    },
    conformsTo: {
      identifier: 'conformsTo',
      $formkit: 'repeatable',
      name: 'dct:conformsTo',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'conformsTo',
          $formkit: 'group',
          name: 'dct:conformsTo',
          children: [
            {
              identifier: 'conformsToTitle',
              $formkit: 'text',
              name: 'rdfs:label',
              classes: {
                outer: 'w100-textfield'
              }
            },
            {
              identifier: 'conformsToUrl',
              $formkit: 'url',
              name: '@id',
              validation: 'optional|url',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
    issued: {
      identifier: 'issued',
      $formkit: 'formkitGroup',
      name: 'dct:issued',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'issued',
          $cmp: 'FormKit',
          props: {
            type: 'datetime-local',
            name: '@value',
            classes: {
              outer: 'w-100'
            },
            // validation: 'optional|date_after:' + new Date(new Date().getTime() - (24 * 60 * 60 * 1000)),
            // 'validation-visibility': 'live',
          }
        },
      ]
    },
    modified: {
      identifier: 'modified',
      $formkit: 'formkitGroup',
      name: 'dct:modified',
      class: 'property inDistribution',
      children: [

        {
          identifier: 'modified',
          $cmp: 'FormKit',
          props: {
            type: 'datetime-local',
            name: '@value',
            classes: {
              outer: 'w-100'
            },
            // validation: 'optional|date_after:' + new Date(new Date().getTime() - (24 * 60 * 60 * 1000)),
            // 'validation-visibility': 'live',
          }
        }
      ]
    },
    rights: {
      class: 'property inDistribution',
      identifier: 'rights',
      id: 'rightsCondDataset',
      $formkit: 'simpleConditional',
      name: 'dct:rights',
      options: { url: 'rdfs:label', text: 'rdfs:label' },
      selection: { 1: 'URL', 2: 'Text' }

      // {
      //   identifier: 'rights',
      //   $cmp: 'FormKit',
      //   if: '$get(rightsCondDataset).value',
      //   props: {
      //     if: '$get(rightsCondDataset).value === url',
      //     then: {
      //       identifier: 'rightsUrl',
      //       type: "url",
      //       label: "URL",
      //       name: 'rdfs:label'
      //     },
      //     else: {
      //       type: 'text',
      //       name: 'rdfs:label',
      //     }
      //   }
      // },

    },
    spatialResolutionInMeters: {
      identifier: 'spatialResolutionInMeters',
      $formkit: 'simpleInput',
      name: 'dcat:spatialResolutionInMeters',
      validation: 'number',
      class: 'property inDistribution',
      classes: { outer: 'formkitProperty formkitCmpWrap mx-0 my-3 p-3' },
    },
    temporalResolution: {
      identifier: 'temporalResolution',
      $formkit: 'formkitGroup',
      name: 'dcat:temporalResolution',
      class: 'property inDistribution',
      children: [
        {
          identifier: 'temporalResolutionYear',
          $formkit: 'number',
          validation: 'min:1950|max:2100|optional',
          name: 'Year',
        },
        {
          identifier: 'temporalResolutionMonth',
          $formkit: 'number',
          validation: 'min:1|max:12|optional',
          name: 'Month',
        },
        {
          identifier: 'temporalResolutionDay',
          $formkit: 'number',
          validation: 'min:1|max:31|optional',
          name: 'Day',
        },
        {
          identifier: 'temporalResolutionHour',
          $formkit: 'number',
          validation: 'min:0|max:23|optional',
          name: 'Hour',
        },
        {
          identifier: 'temporalResolutionMinute',
          $formkit: 'number',
          validation: 'min:0|max:59|optional',
          name: 'Minute',
        },
        {
          identifier: 'temporalResolutionSecond',
          $formkit: 'number',
          validation: 'min:0|max:59|optional',
          name: 'Second',
        },
      ],
    },
    type: {
      identifier: 'type',
      $formkit: 'auto',
      voc: 'distribution-type',
      class: 'property inDistribution',
      name: 'dct:type',

    },
    status: {
      identifier: 'status',
      $formkit: 'auto',
      voc: 'dataset-status',
      class: 'property inDistribution',
      name: 'adms:status',

    },
  },
  catalogues: {
    overview: {
      $cmp: 'OverviewPage',
      props: {
        property: 'catalogues'
      }
    },
    datasetID: {
      $formkit: 'id',
      identifier: 'datasetID',
      name: 'datasetID',
      mandatory: true,
    },
    title: {
      identifier: 'title',
      $formkit: 'repeatable',
      name: 'dct:title',
      children: [
        {
          identifier: 'title',
          $formkit: 'group',
          name: 'dct:title',
          mandatory: true,
          minimum: 1,
          children: [

            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              validation: 'required',
              options: language,
              name: '@language',
              classes: {
                outer: 'w25-textfield'
              }
            }, {
              identifier: 'title',
              $formkit: 'text',
              name: '@value',
              validation: 'required',
              mandatory: true,
              classes: {
                outer: 'w75-textfield'
              }
            },
          ],
        }
      ]
    },
    description: {
      identifier: 'description',
      $formkit: 'repeatable',
      name: 'dct:description',
      children: [
        {
          identifier: 'description',
          $formkit: 'group',
          name: 'dct:description',
          mandatory: true,
          minimum: 1,
          children: [

            {
              identifier: 'language',
              value: 'en',
              $formkit: 'select',
              options: language,
              validation: 'required',
              name: '@language',
              classes: {
                outer: 'w25-descField'
              }
            },
            {
              identifier: 'description',
              $formkit: 'textarea',
              name: '@value',
              validation: 'required',
              classes: {
                outer: 'w75-textfield'
              }
            },

          ],
        }
      ]
    },
    publisher: {
      $formkit: 'auto',
      identifier: 'publisher',
      name: 'dct:publisher',
      voc: 'corporate-body',
      id: 'publisher'
    },
    language: {
      identifier: 'language',
      $formkit: 'auto',
      multiple: true,
      name: 'dct:language',
      voc: 'language',
      id: 'language'
    },
    licence: {
      $formkit: 'simpleConditional',
      name: 'dct:license',
      identifier: 'licence',
      voc: 'licence',
      options: { text: 'dct:title', textarea: 'skos:prefLabel', url: 'skos:exactMatch' },
      selection: { 1: 'vocabulary', 2: 'manually' }

    },
    spatial: {
      identifier: 'spatial',
      $formkit: 'repeatable',
      name: 'dct:spatial',
      children: [
        {
          $formkit: 'spatialinput',
          name: 'dct:spatial',
          identifier: 'spatial',
        }]
    },
    homepage: {
      identifier: 'homepage',
      $formkit: 'simpleInput',
      name: 'foaf:homepage',
      validationType: 'url',
    },
    hasPart: {
      identifier: 'hasPart',
      $formkit: 'repeatable',
      name: 'dct:hasPart',
      children: [
        {
          $formkit: 'group',
          identifier: 'hasPart',
          name: 'dct:hasPart',
          children: [
            {
              identifier: 'hasPartURL',
              $formkit: 'url',
              name: '@id',
              validationType: 'url',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
    isPartOf: {
      identifier: 'isPartOf',
      $formkit: 'simpleInput',
      name: 'dct:isPartOf',
      validationType: 'url',
    },
    rights: {
      identifier: "rights",
      $formkit: 'simpleConditional',
      name: 'dct:rights',
      options: { url: 'rdfs:label', text: 'rdfs:label' },
      selection: { 1: 'URL', 2: 'Text' }
      // children: [
      //   {
      //     identifier: 'rights',
      //     $formkit: "select",
      //     name: '@type',
      //     options: { url: 'Provide an URL', str: 'String' },
      //     id: "rightsModeCatalogue"
      //   },
      //   {
      //     identifier: 'rights',
      //     $cmp: "FormKit",
      //     if: "$get(rightsModeCatalogue).value",
      //     props: {
      //       if: "$get(rightsModeCatalogue).value === url",
      //       then: {
      //         identifier: 'rightsUrl',
      //         type: "url",
      //         label: "URL",
      //         name: 'rdfs:label',
      //       },
      //       else: {
      //         type: "text",
      //         name: 'rdfs:label',
      //       }
      //     }
      //   }
      // ]
    },
    catalog: {
      identifier: 'catalog',
      $formkit: 'repeatable',
      name: 'dcat:catalog',
      children: [
        {
          $formkit: 'group',
          identifier: 'catalog',
          name: 'dcat:catalog',
          children: [
            {
              identifier: 'catalogURL',
              $formkit: 'url',
              validationType: 'url',
              name: '@id',
              classes: {
                outer: 'w100-textfield'
              }
            },
          ],
        }
      ]
    },
    creator: {
      identifier: 'creator',
      $formkit: 'formkitGroup',
      name: 'dct:creator',
      children: [
        {
          identifier: 'creatorType',
          $formkit: 'select',
          name: 'rdf:type',
          options: {
            '': '---',
            'foaf:Person': 'Person',
            'foaf:Organization': 'Organization',
          },
          classes: {
            outer: 'w100-textfield'
          }
        },
        {
          identifier: 'creatorName',
          $formkit: 'text',
          name: 'foaf:name',
          classes: {
            outer: 'w100-textfield'
          }
        },
        {
          identifier: 'creatorEmail',
          $formkit: 'email',
          name: 'foaf:mbox',
          validation: 'optional|email',
          classes: {
            outer: 'w100-textfield'
          }
        },
        {
          identifier: 'creatorHomepage',
          $formkit: 'url',
          name: 'foaf:homepage',
          validation: 'optional|url',
          classes: {
            outer: 'w100-textfield'
          }
        },
      ],
    },
  }
};


// Dynamically add a collapsed property to all fields that are component of
// a set of specific pages steps.
// ['datasets', 'distributions'].forEach((type) => {
//   [].concat(
//     // advised and additional fields for datasets/distributions
//     Object.keys(config?.[type].step2),
//     Object.keys(config?.[type].step3),
//   ).forEach((key) => {
//     dcatapProperties[type][key].collapsed = true;
//   });
// })

export default dcatapProperties;
