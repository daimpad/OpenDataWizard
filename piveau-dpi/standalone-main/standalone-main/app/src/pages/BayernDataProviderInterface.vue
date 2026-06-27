<script setup lang="ts">

import { DataProviderInterface } from '@piveau/dpi';
// const dcatapdeOdb = JSON.parse(JSON.stringify(dpi.dpiSpecConfig?.dcatapdeODB))
// dcatapdeOdb.inputDefinition.distributions.accessService.$formkit = 'repeatableDataService'
// const context = {
//   specification: dcatapdeOdb,
//   specificationName: 'dcatapde-odb-patched',
// }

// dcatapdeOdb.inputDefinition.datasets.keyword.children[0].children[0].identifier = 'language'
// dcatapdeOdb.inputDefinition.distributions.licence = {
//   $formkit: 'auto',
//   name: 'dct:license',
//   identifier: 'licence',
//   voc: 'licence',
//   class: 'property inDistribution',
// }
// dcatapdeOdb.inputDefinition.datasets.politicalGeocodingURI = {
//   identifier: 'politicalGeocodingURI',
//   $formkit: 'repeatable',
//   name: 'dcatde:politicalGeocodingURI',
//   vocabularyOnly: true,
//   props: {
//     vocabularyOnly: true,
//   },
//   children: [
//     {
//       $formkit: 'spatialinput',
//       name: 'dcatde:politicalGeocodingURI',
//       identifier: 'politicalGeocodingURI',
//       vocabularyOnly: true,
//     }]
// },
// dcatapdeOdb.inputDefinition.datasets.admsIdentifier.children[0].children[0].validation = 'admsIdentifierUriRequiredIfSibling|url'
// dcatapdeOdb.inputDefinition.datasets.admsIdentifier.children[0].children[0].validationMessages = {
//   admsIdentifierUriRequiredIfSibling: 'Eine URL ist erforderlich'
// }
// dcatapdeOdb.inputDefinition.datasets.admsIdentifier.children[0].children[0].validationVisibility = 'live'
// dcatapdeOdb.inputDefinition.datasets.admsIdentifier.children[0].validationVisibility = 'live'

// // dcatapdeOdb.inputDefinition.datasets.admsIdentifier.children[0].validation = '+admsIdentifierUriRequiredIfSibling'

// // remove dct:subject
// dcatapdeOdb.pageConent.datasets.Advised = (dcatapdeOdb.pageConent.datasets.Advised as string[]).filter((prop: string) => prop !== 'subject')

// // process through the nested dcatapdeOdb object recursively
// // recursion occurs whenever the object contains children, which contains an array of objects
// function processObject(obj: any) {
//   for (const [key, value] of Object.entries(obj)) {
//     // if object contains name === '@language' and '$formkit' === 'select', then set property value to 'de'
//     if (key === 'name' && value === '@language' && obj.$formkit === 'select') {
//       obj.value = 'de'
//     }

//     // translate date and datetime for modified and created
//     // options: { date: 'Date', datetime: 'Datetime' },
//     if (key === '$formkit' && value === 'select' && obj?.options?.date === 'Date' && obj?.options?.datetime === 'Datetime') {
//       obj.options.date = 'Datum'
//       obj.options.datetime = 'Datum & Uhrzeit'
//     }

//     if (typeof value === 'object') {
//       processObject(value)
//     }
//   }
// }

// processObject(dcatapdeOdb)
</script>

<template>
  <div>
    <DataProviderInterface id="bayern-data-provider-interface" :dpi-context="context" />
  </div>
</template>
