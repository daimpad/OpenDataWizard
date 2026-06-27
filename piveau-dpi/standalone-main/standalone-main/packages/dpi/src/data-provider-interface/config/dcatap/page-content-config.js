const config = {
  datasets: {
    Mandatory: [ 'title', 'datasetID', 'description', 'catalog', 'publisher', 'theme', 'issued', 'modified' ],
    Advised: [ 'keyword', 'subject', 'contactPoint', 'landingPage', 'accrualPeriodicity', 'language', 'spatial', 'temporal', 'creator', 'identifier', 'admsIdentifier', 'page', 'accessRights' ],
    Recommended: [ 'type', 'isUsedBy', 'conformsTo', 'versionInfo', 'versionNotes', 'temporalResolution', 'spatialResolutionInMeters', 'relation', 'qualifiedRelation', 'isReferencedBy', 'hasVersion', 'isVersionOf', 'source', 'provenance', 'qualifiedAttribution', 'wasGeneratedBy' ],
    Distributions: [],
    Overview: [ 'overview' ]
  },
  distributions: {
    Mandatory: [ 'accessURL', 'title', 'description' ],
    Advised: [ 'downloadUrl', 'format', 'mediaType', 'licence', 'status', 'availability', 'issued', 'modified' ],
    Recommended: [ 'type', 'byteSize', 'checksum', 'compressFormat', 'packageFormat', 'language', 'page', 'conformsTo', 'rights' ,'hasPolicy' ,'temporalResolution' , 'spatialResolutionInMeters' ],
    DataService: [ 'accessService' ]
  },
  catalogues: {
    Mandatory: [ 'title', 'datasetID', 'description', 'publisher', 'language', 'homepage', 'licence' ],
    Advised: [ 'spatial', 'hasPart', 'isPartOf', 'rights', 'catalog', 'creator' ],
    Overview: ['overview']
  }
}

export default config;
