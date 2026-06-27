// @ts-nocheck
// dynamic imports are somehow diffcult so we need to import everything :(

// import for DCAT-AP
import pageContent from './dcatap/page-content-config';
import inputDefinition from './dcatap/input-definition';
import prefixes from './dcatap/prefixes';
import formatTypes from './dcatap/format-types';
import vocabPrefixes from './dcatap/vocab-prefixes';

// import DCAT-AP.de
import pageContentDCATAPDE from './dcatapde/page-content-config';
import inputDefinitionDCATAPDE from './dcatapde/input-definition';
import prefixesDCATAPDE from './dcatapde/prefixes';
import formatTypesDCATAPDE from './dcatapde/format-types';
import vocabPrefixesDCATAPDE from './dcatapde/vocab-prefixes';

// import DCAT-AP.de for ODB
import pageContentDCATAPDEODB from './dcatapdeODB/page-content-config';
import inputDefinitionDCATAPDEODB from './dcatapdeODB/input-definition';
import prefixesDCATAPDEODB from './dcatapdeODB/prefixes';
import formatTypesDCATAPDEODB from './dcatapdeODB/format-types';
import vocabPrefixesDCATAPDEODB from './dcatapdeODB/vocab-prefixes';

// import DCAT-AP.de for HappyFlow
import pageContentDCATAPDEHAPPYFLOW from './dcatapdeHappyFlow/page-content-config';
import inputDefinitionDCATAPDEHAPPYFLOW from './dcatapdeHappyFlow/input-definition';
import prefixesDCATAPDEHAPPYFLOW from './dcatapdeHappyFlow/prefixes';
import formatTypesDCATAPDEHAPPYFLOW from './dcatapdeHappyFlow/format-types';
import vocabPrefixesDCATAPDEHAPPYFLOW from './dcatapdeHappyFlow/vocab-prefixes';



export const config = {
    dcatap: {
        pageConent: pageContent,
        inputDefinition: inputDefinition,
        formatTypes: formatTypes,
        prefixes: prefixes,
        vocabPrefixes: vocabPrefixes,
    },
    dcatapde: {
        pageConent: pageContentDCATAPDE,
        inputDefinition: inputDefinitionDCATAPDE,
        formatTypes: formatTypesDCATAPDE,
        prefixes: prefixesDCATAPDE,
        vocabPrefixes: vocabPrefixesDCATAPDE,
    },
    dcatapdeODB: {
        pageConent: pageContentDCATAPDEODB,
        inputDefinition: inputDefinitionDCATAPDEODB,
        formatTypes: formatTypesDCATAPDEODB,
        prefixes: prefixesDCATAPDEODB,
        vocabPrefixes: vocabPrefixesDCATAPDEODB,
    },
    dcatapdeHappyFlow: {
        pageConent: pageContentDCATAPDEHAPPYFLOW,
        inputDefinition: inputDefinitionDCATAPDEHAPPYFLOW,
        formatTypes: formatTypesDCATAPDEHAPPYFLOW,
        prefixes: prefixesDCATAPDEHAPPYFLOW,
        vocabPrefixes: vocabPrefixesDCATAPDEHAPPYFLOW,
    }

};

export default config;