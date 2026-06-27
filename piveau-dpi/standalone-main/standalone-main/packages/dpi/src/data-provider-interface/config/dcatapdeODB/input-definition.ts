import inputDefinition, { type InputDefinition } from '../dcatapde/input-definition';

/**
 * Copy of inputDefinition of dcatapde spec with adjusted properties for Open Data Bayern
 */
const inputDefinitionOdb = JSON.parse(JSON.stringify(inputDefinition)) as InputDefinition;
inputDefinitionOdb.distributions.accessURL = {
  identifier: 'accessUrl',
  $formkit: 'repeatable',
  name: 'dcat:accessURL',
  class: 'property inDistribution',
  children: [
    {
      identifier: 'accessUrl',
      validationType: 'url',
      $formkit: 'simpleAccessURLInput',
    }
  ]
}

export default inputDefinitionOdb;
