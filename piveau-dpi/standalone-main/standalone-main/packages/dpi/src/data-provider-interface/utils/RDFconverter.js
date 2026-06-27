import N3 from 'n3';
import { isEmpty } from 'lodash';
import { has } from 'lodash';

import generalDpiConfig from '../config/dpi-spec-config';

import generalHelper from './general-helper';

/**
 * Converts all properties for given data from form input data into RDF (N-Triples)
 * @param {Object} data Data given within an object. Data stored as follows { datasets: {...}, distributions: [{...},...], catalogues: {...}} 
 * @param {String} property Name of property which should be converted (either 'datasets' or 'catalogues')
 * @returns String of converted data in RDF format (N-Triples)
 */
function convertToRDF(data, property, specification) {

    let finishedRDFdata;

    let dpiConfig = specification;

    // writer for adding data as quads
    const RDFdata = new N3.Writer({ prefixes: dpiConfig.prefixes, format: 'N-Triples' });
    // datasetURI also needed for distribution creation (add distributionURI to dataset (dcat:distribution))
    const datasetURI = `https://piveau.eu/set/data/${data.datasets.datasetID}`;

    // convert values for datasets/catalogues
    convertPropertyValues(RDFdata, data[property], property, '', '', true, datasetURI, dpiConfig); // datasets and catalogues

    // include distribution data into same graph
    // differentiation neccessary because datasets also include distributions
    if (property === 'datasets') {
        // multiple distributions possible -> [{data of distribution 1}, {data of distribution 2}, ...]
        for (let index = 0; index < data.distributions.length; index += 1) {
            convertPropertyValues(RDFdata, data.distributions[index], 'distributions', '', '', true, datasetURI, dpiConfig);
        }
    }

    RDFdata.end((error, result) => finishedRDFdata = result);
    return finishedRDFdata;
}

/**
 * Converts each single value of given form data based on type into RDF
 * @param {N3.Writer} RDFdataset Dataset the converted RDF data should be added to
 * @param {Object} data Object containing data as key-value pairs
 * @param {String} property String determining which property is handled (datasets/distributions/catalogues)
 * @param {DataFactory} preMainURI (can be undefined) Could be a namedNode or BlankNode containing an URI
 * @param {DataFactory} preMainType (can be undefined) NamedNode determining the type of the current property (e.g. dcat:Dataset (as object))
 * @param {Boolean} setMain Value determining if additional values should be set (type, id, sample...)
 * @param {String} datasetURI URI of dataset for use in distribution conversion 
 */
function convertPropertyValues(RDFdataset, data, property, preMainURI, preMainType, setMain, datasetURI, dpiConfig) {

    const formatTypes = dpiConfig.formatTypes;

    // method can be called recursively for nested properties
    // need to access id of parent node for later use as subject -> provide via method parameters (preMainURI & preMainType)
    let mainURI;
    let mainType;

    // parent method can be called recursively for nested values
    // if called on non-nested values a overall id and type muste be set (setMain -> true)
    if (setMain) {
        if (property === 'datasets') {
            mainType = generalHelper.addNamespace('dcat:Dataset', dpiConfig);
            mainURI = N3.DataFactory.namedNode(datasetURI); // datasetID should never be empty because of frontend checking
        } else if (property === 'catalogues') {
            mainType = generalHelper.addNamespace('dcat:Catalog', dpiConfig);
            mainURI = N3.DataFactory.namedNode(`https://piveau.eu/set/data/${data.datasetID}`); // datasetID should never be empty because of frontend checking
        } else {
            mainType = generalHelper.addNamespace('dcat:Distribution', dpiConfig);
            const randomId = generalHelper.makeId(10);
            // distribution id can be random, will be overwritten by backend on saving data
            mainURI = N3.DataFactory.namedNode(`https://piveau.eu/set/data/${randomId}`);
        }

        // parent method can be called recursively to convert nested values
        // but setting the overal type and id of a dataset/catalogue is only required once at the beginning
        // -> only set additional properties when setMain === true
        setAdditionalProperties(RDFdataset, data, mainURI, mainType, property, datasetURI, dpiConfig);
    } else {
        // called on nested properties with already given URI and type which should used in the following conversion process
        mainURI = preMainURI;
        mainType = preMainType;
    }

    // distributions may have download URLs, if no downloadURL is provided -> provided accessUrls will be also set as downloadUrls
    // accessUrl is a required property and therefore always provided (made sure by the frontend)
    // const downloadUrlsProvided = has(data, 'dcat:downloadURL') && !isEmpty(data['dcat:downloadURL']) && data['dcat:downloadURL'].map(el => !isEmpty(el['@id'])).reduce((a, b) => b);

    // loop trough all keys within data object and convert values (or nested values) to RDF
    const valueKeys = Object.keys(data);
    for (let index = 0; index < valueKeys.length; index += 1) {
        const key = valueKeys[index]; // key format: either a normal name for special properties (e.g. datasetID) or namespaced keys (e.g. dct:title)

        if(generalHelper.propertyHasValue(data[key])) {
            // all properties are sorted by their format (see .../data-provider-interface/config/format-types.js)
            // depending on the format the corresponding conversion-method is used, writing the result to the overall RDF-writer
            if (formatTypes.singularString[property].includes(key)) {
                convertSingularString(RDFdataset, mainURI, data, key, dpiConfig);
            } else if (formatTypes.singularURI[property].includes(key)) {
                convertSingularURI(RDFdataset, mainURI, data, key, dpiConfig);
            } else if (formatTypes.multipleURI[property].includes(key)) {
                // if no dowloadURL is provided, set accessUrls as downloadUrls
                // if (!downloadUrlsProvided && key === 'dcat:accessURL') {
                //     // copy accessurl array to donwloadurl array and convert data

                //     data['dcat:downloadURL'] = cloneDeep(data['dcat:accessURL']);
                //     convertMultipleURI(RDFdataset, mainURI, data, 'dcat:downloadURL', property, dpiConfig);
                // }

                convertMultipleURI(RDFdataset, mainURI, data, key, property, dpiConfig);
            } else if (formatTypes.typedStrings[property].includes(key)) {
                convertTypedString(RDFdataset, mainURI, data, key, dpiConfig);
            } else if (formatTypes.multilingualStrings[property].includes(key)) {
                convertMultilingual(RDFdataset, mainURI, data, key, dpiConfig);
            } else if (formatTypes.groupedProperties[property].includes(key)) {

                // grouped properties are properties provided by the form which consist of multiple properties (e.g contactPoint)
                // the properties values are stored within an object located within an array
                // for repeatable properties there are multiple objects in this array, otherwise there is just one
                
                let actualData;
                // vcard:hasAdress is an object as well as dct:creator and skos:notation
                if (key === 'vcard:hasAddress' || key === 'dct:creator' || key === 'skos:notation' || key === 'spdx:checksum' ) actualData = [data[key]];
                else actualData = data[key];

                // looping trough all existing objects within the array
                for (let groupId = 0; groupId < actualData.length; groupId += 1) {
                    let currentGroupData = actualData[groupId];

                    if (!isEmpty(currentGroupData)) {
                        if (key === 'skos:notation') {
                            // property skos:notation work a little bit different then other properties
                            // the form provides a value and a type from two seperated fields ({'@value': '...', '@type': '...'})
                            // the resulting RDF should merge these values into a typed literal (value^^type)
                            if (has(currentGroupData, '@value') && !isEmpty(currentGroupData['@value'])) {
                                let notationValue;

                                // if a type is given, use to form typed literal
                                // if no type is given, only use value to create literal
                                if (has(currentGroupData, '@type') && !isEmpty(currentGroupData['@type'])) { // typed literal
                                    notationValue = N3.DataFactory.literal(currentGroupData['@value'], N3.DataFactory.namedNode(currentGroupData['@type'].resource));
                                } else { // literal
                                    notationValue = N3.DataFactory.literal(currentGroupData['@value']);
                                }

                                // add type for adms:identifier
                                RDFdataset.addQuad(N3.DataFactory.quad(
                                    mainURI,
                                    N3.DataFactory.namedNode(generalHelper.addNamespace('rdf:type', dpiConfig)),
                                    N3.DataFactory.namedNode(generalHelper.addNamespace('adms:Identifier', dpiConfig))
                                ))

                                // save quadruple with typed or untyped literal
                                RDFdataset.addQuad(N3.DataFactory.quad(
                                    mainURI,
                                    N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                                    notationValue
                                ))

                                // resulting rdf quads should look like this:
                                // datasetId  adms:identifier  admsIdentifierUtl
                                //  admsIdentifierUrl  rdf:type  adms:Identifier
                                //  admsIdentifierUrl  skos:notation  value^^type
                            }
                        } else {
                            let groupBlankNode;

                            // because grouped properties have a list of nested properties we need an initial quadruple stating the parent property
                            // using a blank node as object which later serves as subject for the nested properties
                            // RDF example:
                            // datasetID  dct:contactPoint  blankNodeId
                            //   blankNodeId foaf:mbox  email@exmaple.com
                            //   blankNodeId  fn:name  InsitutionName ...

                            // some form fields provide an URL which should serves as namedNode for other nested values (e.g. conformsTo)
                            // RDF example:
                            // datasetID  dct:conformsTo  conformsToURI
                            //  conformsToURI  dct:title  conformsTitle
                            if ((key === 'foaf:page' || key === 'adms:identifier' || key === 'dct:conformsTo') && has(currentGroupData, '@id')) {
                                groupBlankNode = N3.DataFactory.namedNode(currentGroupData['@id']);
                            }
                            // all properties that don't provide an URL serving as namedNode for nested values need to define a blank node

                            // page gets type but also has multilingual fields with preseleted langauge
                            // don't create blank node if there is not data for page beside the preselected language
                            let emptyPage = false;

                            if (key === 'foaf:page') {

                                // if page has title and/or description property given, check if there are values given 
                                const hasTitle = has(currentGroupData, 'dct:title');
                                const hasDescription = has(currentGroupData, 'dct:description');

                                let hasNoValueKeysTitle = true;
                                let hasEmptyValueTitle = true;
                                let hasNoValueKeysDescription = true;
                                let hasEmptyValueDescription = true;

                                if (hasTitle) {
                                    hasNoValueKeysTitle = !currentGroupData['dct:title'].every(el => has(el, '@value'));
                                    hasEmptyValueTitle = currentGroupData['dct:title'].every(el => isEmpty(el['@value']));
                                }

                                if (hasDescription) {
                                    hasNoValueKeysDescription = !currentGroupData['dct:description'].every(el => has(el, '@value'));
                                    hasEmptyValueDescription = currentGroupData['dct:description'].every(el => isEmpty(el['@value']));
                                }

                                // page should be handled as empty if:
                                // no title and/or no description given
                                // if properties given: no value given or value empty
                                if ((hasNoValueKeysTitle || hasEmptyValueTitle) && (hasNoValueKeysDescription || hasEmptyValueDescription)) emptyPage = true;
                            }

                            if (!emptyPage) {
                                if (!groupBlankNode) groupBlankNode = N3.DataFactory.blankNode('');

                                // save inital quadruple using the named or blank node as object
                                // e.g.  datasetId  dct:contactPoint  blankNode/namedNode
                                RDFdataset.addQuad(N3.DataFactory.quad(
                                    mainURI,
                                    N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                                    groupBlankNode
                                ))

                                // some properties provide additional types
                                if (has(formatTypes.additionalPropertyTypes, key)) {
                                    RDFdataset.addQuad(N3.DataFactory.quad(
                                        groupBlankNode,
                                        N3.DataFactory.namedNode(generalHelper.addNamespace('rdf:type', dpiConfig)),
                                        N3.DataFactory.namedNode(generalHelper.addNamespace(formatTypes.additionalPropertyTypes[key], dpiConfig))
                                    ))
                                }

                                // temporal values nested inside another object: "dct:temporal": [{ "dct:temporal": { "dcat:startDate": "...", "dcat:endDate": "..." } }] 
                                if (key === 'dct:temporal') {
                                    if (has(currentGroupData, 'dct:temporal')) {
                                        currentGroupData = currentGroupData['dct:temporal'];
                                    }
                                }

                                // convert all nested values provided by form
                                convertPropertyValues(RDFdataset, currentGroupData, property, groupBlankNode, mainType, false, dpiConfig, dpiConfig);
                            }
                        }
                    }
                }
            } else if (formatTypes.conditionalProperties[property].includes(key)) {
                // publisher either is an URI or a group with multiple values (name, homepage, email)
                // license either is an URI or a group with multiple values ()
                if (key === 'dct:publisher' || key === 'dct:license') {

                    // data contains either {resource: '...', name: '...'} or object containing other keys
                    if (has(data[key], 'resource')) {
                        convertSingularURI(RDFdataset, mainURI, data, key, dpiConfig);
                    } else {
                        const groupBlankNode = N3.DataFactory.blankNode('');

                        // some properties provide additional types
                        if (has(formatTypes.additionalPropertyTypes, key)) {
                            RDFdataset.addQuad(N3.DataFactory.quad(
                                groupBlankNode,
                                N3.DataFactory.namedNode(generalHelper.addNamespace('rdf:type', dpiConfig)),
                                N3.DataFactory.namedNode(generalHelper.addNamespace(formatTypes.additionalPropertyTypes[key], dpiConfig))
                            ))
                        }

                        // save inital quadruple using the named or blank node as object
                        // e.g.  datasetId  dct:contactPoint  blankNode/namedNode
                        RDFdataset.addQuad(N3.DataFactory.quad(
                            mainURI,
                            N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                            groupBlankNode
                        ))

                        convertPropertyValues(RDFdataset, data[key], property, groupBlankNode, mainType, false, dpiConfig, dpiConfig);
                    }
                    

                }
            } else if (key === 'dcat:temporalResolution') {
                // temporal resolution is displayed as group of input forms for each property (year, month, day, ...)
                // the form provides the data as following: [ { 'Year': '...', 'Month': '...', ... } ]
                // the final format of this property should look like this: P?Y?M?DT?H?M?S
                // not all values must be filled and therefore be present -> default behavior if not given: value = 0

                const resolutionValues = data[key];
                const valueString = `P${resolutionValues.Year ? resolutionValues.Year : 0}Y${resolutionValues.Month ? resolutionValues.Month : 0}M${resolutionValues.Day ? resolutionValues.Day : 0}DT${resolutionValues.Hour ? resolutionValues.Hour : 0}H${resolutionValues.Minute ? resolutionValues.Minute : 0}M${resolutionValues.Second ? resolutionValues.Second : 0}S`;

                // frontend always provides temporalResolution even if there is no value resulting in P0Y0M0DT0H0M0S
                // don't save if value is equal to P0Y0M0DT0H0M0S
                if (valueString !== "P0Y0M0DT0H0M0S") {
                    RDFdataset.addQuad(N3.DataFactory.quad(
                        mainURI,
                        N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                        N3.DataFactory.literal(valueString, N3.DataFactory.namedNode(generalHelper.addNamespace('xsd:duration', dpiConfig)))
                    ))
                }
            } else if (key === 'dct:identifier') {
                // form provides data as array of objects with strings: [ { '@value': 'string1' }, { '@value': 'string2' }, ... ]
                // create quadruple for each given object in the array
                for (let valueId = 0; valueId < data[key].length; valueId += 1) {
                    const currentValue = data[key][valueId];
                    if (has(currentValue, '@value') && !isEmpty(currentValue['@value'])) {
                        RDFdataset.addQuad(N3.DataFactory.quad(
                            mainURI,
                            N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                            N3.DataFactory.literal(currentValue['@value'])
                        ))
                    }
                }
            } else if (key === 'dct:rights') {
                // rights has a static type (RightsStatement) which needs to be added to linked data as additional node
                // therefore we need to create an initial quadruple for with 'rights' being the predicate having a blank node
                // blank node serves as subject for the following quadruples which contain the type and actual value of the form field
                // RDF:
                // datasetID  dct:rights  blankNodeID
                //   blankNodeId rdf:type RightsStatement
                //   blankNodeId  rdfs:label  LabelValue

                // blank node as object for inital quadruple and also as subject for following quadruples
                const rightsBlankNode = N3.DataFactory.blankNode('');

                RDFdataset.addQuad(N3.DataFactory.quad(
                    mainURI,
                    N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                    rightsBlankNode
                ))

                // add additional type declaration
                RDFdataset.addQuad(N3.DataFactory.quad(
                    rightsBlankNode,
                    N3.DataFactory.namedNode(generalHelper.addNamespace('rdf:type', dpiConfig)),
                    N3.DataFactory.namedNode(generalHelper.addNamespace('dct:RightsStatement', dpiConfig))
                ))

                // rights is a conditional property and provides either an URI or a string ( { rdfs:label : 'URL/string' } )
                let rightsValue;

                if (data[key]['@type'] === 'url') {
                    rightsValue = N3.DataFactory.namedNode(data[key]['rdfs:label']);
                } else {
                    rightsValue = N3.DataFactory.literal(data[key]['rdfs:label']);
                }

                // add actual value
                RDFdataset.addQuad(N3.DataFactory.quad(
                    rightsBlankNode,
                    N3.DataFactory.namedNode(generalHelper.addNamespace('rdfs:label', dpiConfig)),
                    rightsValue
                ))
           
            } else if (key === 'rdf:type') {
                // some properties have additional type information which needs to be added to graph
                // e.g contactPoint -> vcard:Individual
                RDFdataset.addQuad(N3.DataFactory.quad(
                    mainURI,
                    N3.DataFactory.namedNode(generalHelper.addNamespace('rdf:type', dpiConfig)),
                    N3.DataFactory.namedNode(generalHelper.addNamespace(data[key], dpiConfig))
                ))
            }
        }  
    }
}

/**
 * Creates additional quads depending on given property and saves them to given RDF dataset
 * @param {N3.Writer} RDFdataset RDF dataset the quads are saved to
 * @param {Object} data Object containing data as key-value pairs
 * @param {DataFactory.namedNode} mainURI NamedNode defining URI of current property graph
 * @param {DataFactory} mainType NamedNode defining type of current property graph
 * @param {String} property String determining which property is converted (datasets/distributions/catalogues)
 * @param {String} datasetURI URI of dataset used to add distribution URI to dct:distribution within dataset graph
 */
function setAdditionalProperties(RDFdataset, data, mainURI, mainType, property, datasetURI, dpiConfig) {

    // adding id and type of property
    RDFdataset.addQuad(N3.DataFactory.quad(
        mainURI,
        N3.DataFactory.namedNode(generalHelper.addNamespace('rdf:type', dpiConfig)),
        N3.DataFactory.namedNode(mainType)
    ))

    // catalogues always have to contain the property dct:type with the value 'dcat-ap'
    if (property === 'catalogues') {
        RDFdataset.addQuad(N3.DataFactory.quad(
            mainURI,
            N3.DataFactory.namedNode(generalHelper.addNamespace('dct:type', dpiConfig)),
            N3.DataFactory.literal('dcat-ap')
        ))
    }

    // add distribution id to dataset graph (dcat:distribution)
    if (property === 'distributions') {
        RDFdataset.addQuad(N3.DataFactory.quad(
            N3.DataFactory.namedNode(datasetURI),
            N3.DataFactory.namedNode(generalHelper.addNamespace('dcat:distribution', dpiConfig)),
            mainURI
        ))
    }
}

//-----------------------------------------------------------------------------------------------------
//            basic conversion (input to RDF) methods for different categories of data
//-----------------------------------------------------------------------------------------------------
// seems unnecessary at first but if we want to convert nested properties as well, we need these 
// methods (especially to provide the correct parent URI)

/**
 * Converts provided data to quad and saves it to given dataset
 * @param {N3.Writer} RDFdataset Dataset the converter saves the converted data to
 * @param {DataFactory} id Node (Blank or Named) used as subject in quad
 * @param {String} data Data given by form used as object in quad
 * @param {String} key Name of current value (e.g. dct:title) used as predicate in quad
 */
function convertSingularString(RDFdataset, id, data, key, dpiConfig) {
    if (!isEmpty(data[key])) {
        RDFdataset.addQuad(N3.DataFactory.quad(
            id,
            N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
            N3.DataFactory.literal(data[key])
        ))
    }
}

/**
 * Converts provided data to quad and saves it to given dataset
 * @param {N3.Writer} RDFdata Dataset the converter saves the converted data to
 * @param {DataFactory} id Node (Blank or Named) used as subject in quad
 * @param {String} data Data given by form used as object in quad
 * @param {String} key Name of current value (e.g. dct:title) used as predicate in quad
 */
function convertSingularURI(RDFdataset, id, data, key, dpiConfig) {
    // there are two different formats the frontend delivers URIs
    // 1: 'URI' or 2: {'name': 'abc', 'resource': 'URI'}

    // URIs can either be a normal URL or an email address
    // mail addresses typicall include '@' which is used to determine if the given string is a normal URL or an email address
    if (!isEmpty(data[key])) {

        let singleURI;

        if (typeof data[key] === 'object') {
            if (has(data[key], 'resource')) {
                singleURI = data[key].resource;
            }
        } else {
            if (data[key].includes('@')) {
                // mail address
                singleURI = `mailto:${data[key]}`;
            } else {
                // normal URL
                singleURI = data[key];
            }
        }

        // save quad to dataset
        RDFdataset.addQuad(N3.DataFactory.quad(
            id,
            N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
            N3.DataFactory.namedNode(singleURI)
        ));
    }
}

/**
 * Converts provided data to quads and saves them to given dataset
 * @param {N3.Writer} RDFdataset Dataset the converter saves the converted data to
 * @param {DataFactory} id Node (Blank or Named) used as subject in quads
 * @param {Array} data Array containing URLs used as object within quads
 * @param {String} key Name of current value (e.g. dct:title) used as predicate in quads
 * @param {String} property Determining which property is concerted (datasets/distributions/catalogues)
 */
function convertMultipleURI(RDFdataset, id, data, key, property, dpiConfig) {
    // there are two different formats the frontend delivers multiple URIs
    // 1: [ {"name": '...', "resource": 'URI'}, {...} ] -> multi-autocomplete fields
    // 2: [ { "@id": "URI1" }, { "@id": "URI2" } ] repeatable fields

    for (let uriIndex = 0; uriIndex < data[key].length; uriIndex += 1) {

        let currentURI;
        const valueObject = data[key][uriIndex];
        if (!isEmpty(valueObject)) {
            if (has(valueObject, 'resource')) currentURI = valueObject.resource;
            else if (has(valueObject, '@id')) currentURI = valueObject['@id'];
        }

        // save quad to dataset
        RDFdataset.addQuad(N3.DataFactory.quad(
            id,
            N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
            N3.DataFactory.namedNode(currentURI)
        ));
    }
}

/**
 * Converts provided data to quad and saves them to given dataset
 * @param {N3.Writer} RDFdataset Dataset the converter saves the converted data to
 * @param {DataFactory} id Node (Blank or Named) used as subject in quad
 * @param {String} data String data used as object with type in quad
 * @param {String} key Name of current value (e.g. dct:title) used as predicate in quad and to determine quad-object type
 */
function convertTypedString(RDFdataset, id, data, key, dpiConfig) {
    if (!isEmpty(data[key])) {

        // there is a variety of properties which can have different types
        // issued and motified already provide a type definition ({'@type': 'date/datetime', '@value': '...'})
        if (key === 'dct:issued' || key === 'dct:modified') {
            if (has(data[key], '@value') && !isEmpty(data[key]['@value'])) {
                const imValueType = data[key]['@type'] === 'date' ? data[key]['@type'] : 'dateTime';
                const valueType = generalHelper.addNamespace(`xsd:${imValueType}`, dpiConfig);

                /// save quad to dataset
                RDFdataset.addQuad(N3.DataFactory.quad(
                    id,
                    N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                    N3.DataFactory.literal(data[key]['@value'], N3.DataFactory.namedNode(valueType))
                ));
            }
        } else {
            // all other properties are given as a simple string
            let valueType;
            if (key === 'dcat:endDate' || key === 'dcat:startDate') {
                // dcat:endDate and dcat:startDate are xsd:dateTime
                valueType = generalHelper.addNamespace('xsd:dateTime', dpiConfig);
            } else if (key === 'dcat:spatialResolutionInMeters' || key === "dcat:byteSize") {
                // dcat:spatialResolutionInMeters and dcat:byteSize are xsd:decimal
                valueType = generalHelper.addNamespace('xsd:decimal', dpiConfig);
            }

            /// save quad to dataset
            RDFdataset.addQuad(N3.DataFactory.quad(
                id,
                N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                N3.DataFactory.literal(data[key], N3.DataFactory.namedNode(valueType))
            ));
        }
    }
}

/**
 * Converts provided data to quads and saves them to given dataset
 * @param {N3.Writer} RDFdataset Dataset the converter saves the converted data to
 * @param {DataFactory} id Node (Blank or Named) used as subject in quads
 * @param {Array} data Array of objects containing data and language converted into literal with language for quad
 * @param {String} key Name of current value (e.g. dct:title) used as predicate in quads
 */
function convertMultilingual(RDFdataset, id, data, key, dpiConfig) {
    // multilingual fields mostly provide data as followed
    // [ { '@value': '....', '@language': '...' }, ... ]
    // only the licence title provides no language

    if (!isEmpty(data[key])) {

        // licence title
        if (!Array.isArray(data[key])) {
            RDFdataset.addQuad(N3.DataFactory.quad(
                id,
                N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                N3.DataFactory.literal(data[key])
            ))
        } else {
            for (let langIndex = 0; langIndex < data[key].length; langIndex += 1) {
                const currentData = data[key][langIndex];
                // only save data if a value is given (forntend provides preselected language which don't need to be saved if there is no actaul value)
                if (!isEmpty(currentData) && has(currentData, '@value') && !isEmpty(currentData['@value'])) {
                    let languageTag;
    
                    // if there is no langauge given, set language to english
                    if (!has(currentData, '@language') || isEmpty(currentData, '@language')) {
                        languageTag = 'en';
                    } else {
                        // if language is given, use given tag
                        languageTag = currentData['@language'];
                    }
    
                    // saving quad to dataset
                    RDFdataset.addQuad(N3.DataFactory.quad(
                        id,
                        N3.DataFactory.namedNode(generalHelper.addNamespace(key, dpiConfig)),
                        N3.DataFactory.literal(currentData['@value'], languageTag)
                    ))
                }
            }
        }
    }
}

export default {
    convertToRDF,
};