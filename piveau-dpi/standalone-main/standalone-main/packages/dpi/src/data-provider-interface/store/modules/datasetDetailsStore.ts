// @ts-nocheck
/* eslint-disable no-param-reassign,no-console */

import {
    has,
    isObject,
    isArray,
} from 'lodash';
import {getTranslationFor, mirrorPropertyFn} from '../../../utils/helpers';
// import { SimilarDatasetsQuery } from 'lib/services/datasetService';

// The helper functions below stabilize the store against API changes without changing everything
// throughout the whole project.
// See https://gitlab.fokus.fraunhofer.de/piveau/organisation/piveau-scrum-board/-/issues/1761
// TODO: ideally, we want to get rid of using these functions, but they require significant effort

// Example: mirrorLabelAsTitle({ label: "hello world" }) == { label: "hello world", title: "hello world" }
const mirrorLabelAsTitle = mirrorPropertyFn('label', 'title');

/**
 * @property dataset
 * @type JSON
 * @description A dataset object.
 */
const state = {
    dataset: {
        // DCAT-AP.de
        availability: {},
        applicableLegislation: [{}],
        politicalGeocodingLevelURI: [{}],
        politicalGeocodingURI: [{}],
        contributorID: [{}],
        geocodingDescriptionDe: {},
        hvdCategory: [{}],
        legalBasis: {},
        qualityProcessURI: '',
        typeDe: '',
        references: '',
        contributor: [],
        originator: [],
        maintainer: [],
        //
        accessRights: '',
        accrualPeriodicity: '',
        admsIdentifiers: [],
        attributes: [],
        catalog: {},
        categories: [],
        conformsTo: [],
        contactPoints: [],
        country: {},
        creator: {},
        dateIncorrect: false,
        deadline: '',
        description: {},
        dimensions: [],
        distributions: [],
        distributionFormats: [],
        documentations: [],
        frequency: {},
        geocodingDescription: {},
        hasQualityAnnotations: [],
        hasVersion: [],
        id: '',
        identifiers: [],
        idName: '',
        isHvd: false,
        isReferencedBy: [],
        isVersionOf: [],
        keywords: [],
        landingPages: [],
        languages: [],
        licences: [],
        modificationDate: '',
        numSeries: 0,
        originalLanguage: '',
        otherIdentifiers: [],
        pages: [],
        provenances: [],
        publisher: {},
        qualifiedAttributions: [],
        qualifiedRelations: [],
        relations: [],
        relatedResources: [],
        releaseDate: '',
        resource: '',
        sample: [],
        similarDatasetsRequested: '',
        similarDatasets: [],
        sources: [],
        spatial: [],
        spatialResource: [],
        spatialResolutionInMeters: 0,
        statUnitMeasures: [],
        subject: [],
        temporal: [],
        temporalResolution: [],
        theme: [],
        translationMetaData: {},
        title: {},
        type: {},
        versionInfo: '',
        versionNotes: {},
        visualisations: [],
        wasGeneratedBy: [],
        qualityDataRequested: '',
        qualityData: [''],
        qualityDistributionData: [''],
        isDQVDataRequested: '',
        isDQVDataRDFAvailable: false,
        isDQVDataTTLAvailable: false,
        isDQVDataN3Available: false,
        isDQVDataNTAvailable: false,
        isDQVDataJSONLDAvailable: false,
        catalogRecord: {},
        isUsedBy: {},
        extendetMetadata: {},
        distributionDownloadAs: {},
        distributionDownloadAsOptions: [],
        descriptionHeight: 0,
    },
    activeNavigationTab: 0,
    loading: false,
};

const getters = {
    getProperty: state => property => state.dataset[property],
    // DCAT-AP.de fields
    getAvailability: state => state.dataset.availability,
    getPoliticalGeocodingLevelURI: state => state.dataset.politicalGeocodingLevelURI,
    getPoliticalGeocodingURI: state => state.dataset.politicalGeocodingURI,
    getContributorID: state => state.dataset.contributorID,
    getGeocodingDescriptionDe: state => state.dataset.geocodingDescriptionDe,
    getLegalBasis: state => state.dataset.legalBasis,
    getQualityProcessURI: state => state.dataset.qualityProcessURI,
    getTypeDe: state => state.dataset.typeDe,
    getReferences: state => state.dataset.references,
    getContributor: state => state.dataset.contributor,
    getOriginator: state => state.dataset.originator,
    getMaintainer: state => state.dataset.maintainer,
    //
    getAccessRights: state => state.dataset.accessRights,
    getAccrualPeriodicity: state => state.dataset.accrualPeriodicity,
    getAdmsIdentifiers: state => state.dataset.admsIdentifiers,
    getAttributes: state => state.dataset.attributes,
    getCatalog: state => state.dataset.catalog,
    getCategories: state => (state.dataset.categories.length && state.dataset.categories.map(mirrorLabelAsTitle)) || [],
    getConformsTo: state => (state.dataset.conformsTo.length && state.dataset.conformsTo.map(mirrorLabelAsTitle)) || [],
    getContactPoints: state => state.dataset.contactPoints,
    getCountry: state => state.dataset.country,
    getCreator: state => state.dataset.creator,
    getDateIncorrect: state => state.dataset.dateIncorrect,
    getDeadline: state => state.dataset.deadline,
    getDescription: state => state.dataset.description,
    getDimensions: state => state.dataset.dimensions,
    getDistributions: state => state.dataset.distributions,
    getDistributionFormats: state => state.dataset.distributionFormats,
    getDocumentations: state => state.dataset.documentations,
    getFrequency: state => state.dataset.frequency,
    getGeocodingDescription: state => state.dataset.geocodingDescription,
    getHasQualityAnnotations: state => state.dataset.hasQualityAnnotations,
    getHasVersion: state => state.dataset.hasVersion,
    getID: state => state.dataset.id,
    getIdentifiers: state => state.dataset.identifiers,
    getIdName: state => state.dataset.idName,
    getIsReferencedBy: state => state.dataset.isReferencedBy,
    getIsVersionOf: state => state.dataset.isVersionOf,
    getKeywords: state => (state.dataset.keywords.length && state.dataset.keywords.map(mirrorLabelAsTitle)) || [],
    getSubject: state => (state.dataset.subject.length && state.dataset.subject.map(mirrorLabelAsTitle)) || [],
    getLandingPages: state => state.dataset.landingPages,
    getLanguages: state => state.dataset.languages,
    getLicences: state => state.dataset.licences,
    getModificationDate: state => state.dataset.modificationDate,
    getNumSeries: state => state.dataset.numSeries,
    getOriginalLanguage: state => state.dataset.originalLanguage,
    getOtherIdentifiers: state => state.dataset.otherIdentifiers,
    getPages: state => state.dataset.pages,
    getProvenances: state => state.dataset.provenances,
    getPublisher: state => state.dataset.publisher,
    getQualifiedAttributions: state => state.dataset.qualifiedAttributions,
    getQualifiedRelations: state => state.dataset.qualifiedRelations,
    getRelations: state => state.dataset.relations,
    getRelatedResources: state => state.dataset.relatedResources,
    getReleaseDate: state => state.dataset.releaseDate,
    getSimilarDatasetsRequested: state => state.dataset.similarDatasetsRequested,
    getSimilarDatasets: state => state.dataset.similarDatasets,
    getSample: state => state.dataset.sample,
    getSources: state => state.dataset.sources,
    getSpatial: state => state.dataset.spatial,
    getSpatialResource: state => (state.dataset.spatialResource.length && state.dataset.spatialResource.map(mirrorLabelAsTitle)) || [],
    getSpatialResolutionInMeters: state => state.dataset.spatialResolutionInMeters,
    getStatUnitMeasures: state => state.dataset.statUnitMeasures,
    getTheme: state => state.dataset.theme,
    getTemporal: state => state.dataset.temporal,
    getTemporalResolution: state => state.dataset.temporalResolution,
    getTranslationMetaData: state => state.dataset.translationMetaData,
    getTitle: state => state.dataset.title,
    getType: state => state.dataset.type,
    getResource: state => state.dataset.resource,
    getVersionInfo: state => state.dataset.versionInfo,
    getVersionNotes: state => state.dataset.versionNotes,
    getVisualisations: state => state.dataset.visualisations,
    getWasGeneratedBy: state => state.dataset.wasGeneratedBy,
    getLoading: state => state.loading,
    getQualityDataRequested: state => state.dataset.qualityDataRequested,
    getQualityData: state => state.dataset.qualityData,
    getQualityDistributionData: state => state.dataset.qualityDistributionData,
    getIsDQVDataRequested: state => state.dataset.isDQVDataRequested,
    getIsDQVDataRDFAvailable: state => state.dataset.isDQVDataRDFAvailable,
    getIsDQVDataTTLAvailable: state => state.dataset.isDQVDataTTLAvailable,
    getIsDQVDataN3Available: state => state.dataset.isDQVDataN3Available,
    getIsDQVDataNTAvailable: state => state.dataset.isDQVDataNTAvailable,
    getIsDQVDataJSONLDAvailable: state => state.dataset.isDQVDataJSONLDAvailable,
    getCatalogRecord: state => state.dataset.catalogRecord,
    getExtendedMetadata: state => state.dataset.extendetMetadata,
    getDistributionDownloadAs: state => state.dataset.distributionDownloadAs,
    getDistributionDownloadAsOptions: state => state.dataset.distributionDownloadAsOptions,
    getDatasetDescriptionHeight: state => state.dataset.descriptionHeight,

    getIsHvd: state => state.dataset.isHvd,
    getApplicableLegislation: state => state.dataset.applicableLegislation,
    // getHvdCategory: state => state.dataset.hvdCategory,
    getHvdCategories: state => (state.dataset.hvdCategory.length && state.dataset.hvdCategory.map(mirrorLabelAsTitle)) || [],

};

const actions = {
    /**
     * @description Loads details for the dataset with the given ID.
     * @param commit
     * @param state
     * @param id {String} The dataset ID.
     */
    loadDatasetDetails({ state, commit }, id) {
        return new Promise((resolve, reject) => {
            commit('SET_LOADING', true);
            commit('SET_ID', id);
            this.$datasetService.getSingle(id)
                .then((response) => {
                    // DCAT-AP.de
                    commit('SET_AVAILABILITY', response.availability);
                    commit('SET_POLITICAL_GEOCODING_LEVEL_URL', response.politicalGeocodingLevelURI);
                    commit('SET_POLITICAL_GEOCODING_URL', response.politicalGeocodingURI);
                    commit('SET_CONTRIBUTOR_ID', response.contributorID);
                    commit('SET_GEOCODING_DESCRIPTION_DE', response.geocodingDescriptionDe);
                    commit('SET_LEGAL_BASIS', response.legalBasis);
                    commit('SET_QUALITY_PROCESS_URI', response.qualityProcessURI);
                    commit('SET_TYPE_DE', response.typeDe);
                    commit('SET_REFERENCES', response.references);
                    commit('SET_CONTRIBUTER', response.contributor);
                    commit('SET_ORIGINATOR', response.originator);
                    commit('SET_MAINTAINER', response.maintainer);
                    //
                    commit('SET_ACCESS_RIGHTS', response.accessRights);
                    commit('SET_ACCRUAL_PERIODICITY', response.accrualPeriodicity);
                    commit('SET_ATTRIBUTES', response.attributes);
                    commit('SET_catalog', response.catalog);
                    commit('SET_CATEGORIES', response.categories);
                    commit('SET_CONFORMS_TO', response.conformsTo);
                    commit('SET_CONTACT_POINTS', response.contactPoints);
                    commit('SET_COUNTRY', response.country);
                    commit('SET_CREATOR', response.creator);
                    commit('SET_DESCRIPTION', response.description);
                    commit('SET_DIMENSIONS', response.dimensions);
                    commit('SET_DISTRIBUTIONS', response.distributions);
                    commit('SET_DISTRIBUTION_FORMATS', response.distributionFormats);
                    commit('SET_DOCUMENTATIONS', response.documentations);
                    commit('SET_FREQUENCY', response.frequency);
                    commit('SET_HAS_QUALITY_ANNOTATIONS', response.hasQualityAnnotations);
                    commit('SET_HAS_VERSION', response.hasVersion);
                    commit('SET_IDENTIFIERS', response.identifiers);
                    commit('SET_ID_NAME', response.idName);
                    commit('SET_IS_REFERENCED_BY', response.isReferencedBy);
                    commit('SET_IS_VERSION_OF', response.isVersionOf);
                    commit('SET_KEYWORDS', response.keywords);
                    commit('SET_LANDING_PAGES', response.landingPages);
                    commit('SET_LANGUAGES', response.languages);
                    commit('SET_LICENCES', response.licences);
                    commit('SET_MODIFICATION_DATE', response.modificationDate);
                    commit('SET_NUM_SERIES', response.numSeries);
                    commit('SET_ORIGINAL_LANGUAGE', response.originalLanguage);
                    commit('SET_OTHER_IDENTIFIERS', response.otherIdentifiers);
                    commit('SET_PAGES', response.pages);
                    commit('SET_PROVENANCES', response.provenances);
                    commit('SET_PUBLISHER', response.publisher);
                    commit('SET_RELATED_RESOURCES', response.relatedResources);
                    commit('SET_RELEASE_DATE', response.releaseDate);
                    commit('SET_RESOURCE', response.resource);
                    commit('SET_SOURCES', response.sources);
                    commit('SET_SPATIAL', response.spatial);
                    commit('SET_SPATIAL_RESOURCE', response.spatialResource);
                    commit('SET_STAT_UNIT_MEASURES', response.statUnitMeasures);
                    commit('SET_TEMPORAL', response.temporal);
                    commit('SET_TRANSLATION_META_DATA', response.translationMetaData);
                    commit('SET_TITLE', response.title);
                    commit('SET_VERSION_INFO', response.versionInfo);
                    commit('SET_VERSION_NOTES', response.versionNotes);
                    commit('SET_VISUALISATIONS', response.visualisations);
                    commit('SET_WAS_GENERATED_BY', response.wasGeneratedBy);
                    commit('SET_CATALOG_RECORD', response.catalogRecord);
                    commit('SET_ADMS_IDENTIFIERS', response.admsIdentifiers);
                    commit('SET_DEADLINE', response.deadline);
                    commit('SET_GEOCODING_DESCRIPTION', response.geocodingDescription);
                    commit('SET_QUALIFIED_ATTRIBUTIONS', response.qualifiedAttributions);
                    commit('SET_QUALIFIED_RELATIONS', response.qualifiedRelations);
                    commit('SET_RELATIONS', response.relations);
                    commit('SET_SAMPLE', response.sample);
                    commit('SET_SPATIAL_RESOLUTION_IN_METERS', response.spatialResolutionInMeters);
                    commit('SET_SUBJECT', response.subject);
                    commit('SET_TEMPORAL_RESOLUTION', response.temporalResolution);
                    commit('SET_THEME', response.theme);
                    commit('SET_TYPE', response.type);
                    commit('SET_EXTENDET_METADATA', response.extendetMetadata);
                    commit('SET_IS_HVD', response.isHvd);
                    commit('SET_HVD_CATEGORY', response.hvdCategory);
                    commit('SET_LOADING', false);

                    commit('SET_IS_HVD', response.isHvd);
                    commit('SET_APPLICABLE_LEGISLATION', response.applicableLegislation);
                    commit('SET_HVD_CATEGORY', response.hvdCategory);

                    resolve();
                })
                .catch((err) => {
                    console.error(err);
                    commit('SET_LOADING', false);
                    reject(err);
                });
        });
    },
    /**
     * @description Loads details for the dataset with the given ID. But unlike loaddatasetDetails,
     * the Mutations after the request differ because this function is meant to fetch details for similar datasets of another dataset.
     * @param commit
     * @param state
     * @param id {String} The dataset ID.
     */
    loadSimilarDatasetDetails({ state, commit }, id) {
        commit('SET_LOADING', true);
        return new Promise((resolve, reject) => {
            this.$datasetService.getSingle(id)
                .then((response) => {
                    commit('SET_SD_DESCRIPTION', { id, description: response.description });
                    commit('SET_SD_TITLE', { id, title: response.title });
                    commit('SET_SD_DISTRIBUTION_FORMATS', { id, distributionFormats: response.distributionFormats })
                    commit('SET_LOADING', false);
                    resolve(response);
                })
                .catch((err) => {
                    console.error(err);
                    commit('SET_LOADING', false);
                    reject(err);
                });
        });
    },
    /**
     * @description Fetches similar datasets of the provided dataset id
     * @param idOrPayload {string | LoadSimilarDatasetsPayload} - id or payload
     */
    loadSimilarDatasets({ state, commit }, idOrPayload: string | LoadSimilarDatasetsPayload) {
        let id;
        let query;
        let description;
        if (typeof idOrPayload === "string") {
            id = idOrPayload;
            query = {};
        } else if (isObject(idOrPayload)) {
            id = idOrPayload.id;
            query = idOrPayload?.query;
            description = idOrPayload?.description;
        } else {
            throw new Error('invalid payload argument passed to method loadSimilarDatasets: '
              + JSON.stringify(idOrPayload))
        }
        commit('SET_LOADING', true);
        return new Promise((resolve, reject) => {
            commit('SET_ID', id);
            commit('SET_SIMILAR_DATASETS_REQUESTED', id);
            const properties: {description:string, title:string, keywords?:[string]} = {
                description: getTranslationFor(state.dataset.description, "en", "en"),
                title: getTranslationFor(state.dataset.title, "en", "en"),
            };
            if (state.dataset.keywords) {
                properties.keywords = state.dataset.keywords.map(item => item.label)
            }
            this.$datasetService.getSimilarDatasets(id, properties, query)
                .then((response) => {
                    const result = response.data?.result;
                    if(result){
                        //new similarity service
                        const prefix = "http://data.europa.eu/88u/dataset/";
                        // const prefix = "<http://dataeuropaeu/88u/dataset/";
                        result.forEach(item => {
                            item.id = item.uri.substring(prefix.length);
                            item.uri = "https://data.europa.eu/88u/dataset/" + item.id;
                            // item.id = item.uri.substring(prefix.length, item.uri.length - 2);
                            // item.uri = "https://data.europa.eu/88u/dataset/" + item.id;
                        });
                        commit('SET_SIMILAR_DATASETS', result);
                        commit('SET_LOADING', false);
                        resolve(result);
                    }else{
                        // old similarity service
                        commit('SET_SIMILAR_DATASETS', response.data);
                        commit('SET_LOADING', false);
                        resolve(response.data);
                    }

                })
                .catch((err) => {
                    console.error(err);
                    commit('SET_LOADING', false);
                    reject(err);
                });
        });
    },
    loadQualityData({ commit }, id) {
        commit('SET_LOADING', true);
        return new Promise((resolve, reject) => {
            commit('SET_ID', id);
            commit('SET_QUALITY_DATA_REQUESTED', id);
            this.$datasetService.getQualityData(id)
                .then((response) => {
                    commit('SET_QUALITY_DATA', response.data);
                    commit('SET_LOADING', false);
                    resolve(response.data);
                })
                .catch((err) => {
                    console.error(err);
                    commit('SET_LOADING', false);
                    reject(err);
                });
        });
    },
    loadQualityDistributionData({ commit }, id) {
        commit('SET_LOADING', true);
        return new Promise((resolve, reject) => {
            commit('SET_ID', id);
            commit('SET_QUALITY_DATA_REQUESTED', id);
            this.$datasetService.getQualityDistributionData(id)
                .then((response) => {
                    commit('SET_QUALITY_DISTRIBUTION_DATA', response.data);
                    commit('SET_LOADING', false);
                    resolve(response.data);
                })
                .catch((err) => {
                    console.error(err);
                    commit('SET_LOADING', false);
                    reject(err);
                });
        });
    },
    /**
     * @description load dqv data
     * @param commit
     * @param id {String}
     * @param formats {Array}
     * @param locale {String}
     */
    loadDQVData({ commit }, { id, formats, locale }) {
        return new Promise((resolve, reject) => {
            commit('SET_ID', id);
            commit('SET_IS_DQV_DATA_REQUESTED', id);

            this.$datasetService.getDQVDataHead(id, locale)
                .then((response) => {
                    const isAvailable = response.status === 200;
                    formats.forEach(format => commit(`SET_IS_DQV_DATA_${format.toUpperCase()}_AVAILABLE`, isAvailable));
                    resolve(response);
                })
                .catch((err) => {
                    formats.forEach(format => commit(`SET_IS_DQV_DATA_${format.toUpperCase()}_AVAILABLE`, false));
                    reject(err);
                })
        });
    },
    setLoading({ commit }, isLoading) {
        commit('SET_LOADING', isLoading);
    },
    /**
     * @description Selects distribution for download as (format convertion) service, sets available format options.
     * @param commit
     * @param distribution - Selected distribution.
     * @param selectOptions - Available file formats for convertion
     */
    selectDistributionForDownloadAs({ commit }, {distribution, selectOptions}) {
        commit('SET_DISTRIBUTION_DOWNLOAD_AS', distribution);
        commit('SET_DISTRIBUTION_DOWNLOAD_AS_OPTIONS', selectOptions);
    },
    /**
    * @description Sets datasetDescription height
    * @param commit
    * @param height
    */
    setDatasetDescriptionHeight({ commit }, height) {
      commit('SET_DATASET_DESCRIPTION_HEIGHT', height)
    },
    /**
    * @description Sets dateIncorrect to true if date is not plausible
    * @param commit
    */
    setDateIncorrect({ commit }) {
        commit('SET_DATE_INCORRECT')
    },
};

const mutations = {
    // DCAT-AP.de
    SET_AVAILABILITY(state, availability) {
        state.dataset.availability = availability;
    },
    SET_POLITICAL_GEOCODING_LEVEL_URL(state, politicalGeocodingLevelURI) {
        state.dataset.politicalGeocodingLevelURI = politicalGeocodingLevelURI;
    },
    SET_POLITICAL_GEOCODING_URL(state, politicalGeocodingURI) {
        state.dataset.politicalGeocodingURI = politicalGeocodingURI;
    },
    SET_CONTRIBUTOR_ID(state, contributorID) {
        state.dataset.contributorID = contributorID;
    },
    SET_LEGAL_BASIS(state, legalBasis) {
        state.dataset.legalBasis = legalBasis;
    },
    SET_QUALITY_PROCESS_URI(state, qualityProcessURI) {
        state.dataset.qualityProcessURI = qualityProcessURI;
    },
    SET_TYPE_DE(state, typeDe) {
        state.dataset.typeDe = typeDe;
    },
    SET_REFERENCES(state, references) {
        state.dataset.references = references;
    },
    SET_CONTRIBUTER(state, contributor) {
        state.dataset.contributor = contributor;
    },
    SET_GEOCODING_DESCRIPTION_DE(state, geocodingDescriptionDe) {
        state.dataset.geocodingDescriptionDe = geocodingDescriptionDe;
    },
    SET_ORIGINATOR(state, originator) {
        state.dataset.originator = originator;
    },
    SET_MAINTAINER(state, maintainer) {
        state.dataset.maintainer = maintainer;
    },
    //
    SET_ACCESS_RIGHTS(state, accessRights) {
        state.dataset.accessRights = accessRights;
    },
    SET_ACCRUAL_PERIODICITY(state, accrualPeriodicity) {
        state.dataset.accrualPeriodicity = accrualPeriodicity;
    },
    SET_ATTRIBUTES(state, attributes) {
        state.dataset.attributes = attributes;
    },
    SET_catalog(state, catalog) {
        state.dataset.catalog = catalog;
    },
    SET_CATEGORIES(state, categories) {
        state.dataset.categories = categories;
    },
    SET_CONFORMS_TO(state, conformsTo) {
        state.dataset.conformsTo = conformsTo;
    },
    SET_CONTACT_POINTS(state, contactPoints) {
        state.dataset.contactPoints = contactPoints;
    },
    SET_COUNTRY(state, country) {
        state.dataset.country = country;
    },
    SET_CREATOR(state, creator) {
        state.dataset.creator = creator;
    },
    SET_DATE_INCORRECT(state) {
        state.dataset.dateIncorrect = true;
    },
    SET_DESCRIPTION(state, description) {
        state.dataset.description = description;
    },
    SET_DIMENSIONS(state, dimensions) {
        state.dataset.dimensions = dimensions;
    },
    SET_DISTRIBUTIONS(state, distributions) {
        state.dataset.distributions = distributions;
    },
    SET_DISTRIBUTION_FORMATS(state, distributionFormats) {
        state.dataset.distributionFormats = distributionFormats;
    },
    SET_DOCUMENTATIONS(state, documentations) {
        state.dataset.documentations = documentations;
    },
    SET_FREQUENCY(state, frequency) {
        state.dataset.frequency = frequency;
    },
    SET_HAS_QUALITY_ANNOTATIONS(state, hasQualityAnnotations) {
        state.dataset.hasQualityAnnotations = hasQualityAnnotations;
    },
    SET_HAS_VERSION(state, hasVersion) {
        state.dataset.hasVersion = hasVersion;
    },
    SET_ID(state, id) {
        state.dataset.id = id;
    },
    SET_IDENTIFIERS(state, identifiers) {
        state.dataset.identifiers = identifiers;
    },
    SET_ID_NAME(state, idName) {
        state.dataset.idName = idName;
    },
    SET_IS_REFERENCED_BY(state, isReferencedBy) {
        state.dataset.isReferencedBy = isReferencedBy;
    },
    SET_IS_VERSION_OF(state, isVersionOf) {
        state.dataset.isVersionOf = isVersionOf;
    },
    SET_KEYWORDS(state, keywords) {
        state.dataset.keywords = keywords;
    },
    SET_LANDING_PAGES(state, landingPages) {
        state.dataset.landingPages = landingPages;
    },
    SET_LANGUAGES(state, languages) {
        state.dataset.languages = languages;
    },
    SET_LICENCES(state, licences) {
        state.dataset.licences = licences;
    },
    SET_MODIFICATION_DATE(state, date) {
        state.dataset.modificationDate = date;
    },
    SET_NUM_SERIES(state, numSeries) {
        state.dataset.numSeries = numSeries;
    },
    SET_ORIGINAL_LANGUAGE(state, originalLanguage) {
        state.dataset.originalLanguage = originalLanguage;
    },
    SET_OTHER_IDENTIFIERS(state, otherIdentifiers) {
        state.dataset.otherIdentifiers = otherIdentifiers;
    },
    SET_PAGES(state, pages) {
        state.dataset.pages = pages;
    },
    SET_PROVENANCES(state, provenances) {
        state.dataset.provenances = provenances;
    },
    SET_PUBLISHER(state, publisher) {
        state.dataset.publisher = publisher;
    },
    SET_RELATED_RESOURCES(state, relatedResources) {
        state.dataset.relatedResources = relatedResources;
    },
    SET_RELEASE_DATE(state, date) {
        state.dataset.releaseDate = date;
    },
    SET_RESOURCE(state, resource) {
        state.dataset.resource = resource;
    },
    SET_SOURCES(state, sources) {
        state.dataset.sources = sources;
    },
    SET_SPATIAL(state, spatial) {
        state.dataset.spatial = spatial;
    },
    SET_SPATIAL_RESOURCE(state, spatialResource) {
        state.dataset.spatialResource = spatialResource;
    },
    SET_STAT_UNIT_MEASURES(state, statUnitMeasures) {
        state.dataset.statUnitMeasures = statUnitMeasures;
    },
    SET_TEMPORAL(state, temporal) {
        state.dataset.temporal = temporal;
    },
    SET_TRANSLATION_META_DATA(state, translationMetaData) {
        state.dataset.translationMetaData = translationMetaData;
    },
    SET_TITLE(state, title) {
        state.dataset.title = title;
    },
    SET_VERSION_INFO(state, versionInfo) {
        state.dataset.versionInfo = versionInfo;
    },
    SET_VERSION_NOTES(state, versionNotes) {
        state.dataset.versionNotes = versionNotes;
    },
    SET_VISUALISATIONS(state, visualisations) {
        state.dataset.visualisations = visualisations;
    },
    SET_WAS_GENERATED_BY(state, wasGeneratedBy) {
        state.dataset.wasGeneratedBy = wasGeneratedBy;
    },
    SET_SIMILAR_DATASETS_REQUESTED(state, similarDatasetsRequested) {
        state.dataset.similarDatasetsRequested = similarDatasetsRequested;
    },
    SET_SIMILAR_DATASETS(state, similarDatasets) {
        state.dataset.similarDatasets = similarDatasets;
    },
    SET_SD_DESCRIPTION(state, payload) {
        if (has(payload, 'id') && has(payload, 'description')) {
            const id = payload.id;
            const description = payload.description;
            if (isArray(state.dataset.similarDatasets)) {
                const similarDataset = state.dataset.similarDatasets.filter(el => el.id === id)[0];
                if (isObject(similarDataset)) similarDataset['description'] = description;
            }
        }
    },
    SET_SD_TITLE(state, payload) {
        if (has(payload, 'id') && has(payload, 'title')) {
            const id = payload.id;
            const title = payload.title;
            if (isArray(state.dataset.similarDatasets)) {
                const similarDataset = state.dataset.similarDatasets.filter(el => el.id === id)[0];
                if (isObject(similarDataset)) similarDataset['title'] = title;
            }
        }
    },
    SET_SD_DISTRIBUTION_FORMATS(state, payload) {
        if (has(payload, 'id') && has(payload, 'distributionFormats')) {
            const id = payload.id;
            const distributionFormats = payload.distributionFormats;
            if (isArray(state.dataset.similarDatasets)) {
                const similarDataset = state.dataset.similarDatasets.filter(el => el.id === id)[0];
                if (isObject(similarDataset)) {
                    similarDataset['distributionFormats'] = distributionFormats;
                }
            }
        }
    },
    SET_ACTIVE_NAVIGATION_TAB(state, tabIndex) {
        state.activeNavigationTab = tabIndex;
    },
    SET_LOADING(state, isLoading) {
        state.loading = isLoading;
    },
    SET_QUALITY_DATA_REQUESTED(state, qualityDataRequested) {
        state.dataset.qualityDataRequested = qualityDataRequested;
    },
    SET_QUALITY_DATA(state, qualityData) {
        state.dataset.qualityData = qualityData;
    },
    SET_QUALITY_DISTRIBUTION_DATA(state, qualityDistributionData) {
        state.dataset.qualityDistributionData = qualityDistributionData;
    },
    SET_IS_DQV_DATA_REQUESTED(state, isDQVDataRequested) {
        state.dataset.isDQVDataRequested = isDQVDataRequested;
    },
    SET_IS_DQV_DATA_RDF_AVAILABLE(state, isDQVDataRDFAvailable) {
        state.dataset.isDQVDataRDFAvailable = isDQVDataRDFAvailable;
    },
    SET_IS_DQV_DATA_TTL_AVAILABLE(state, isDQVDataTTLAvailable) {
        state.dataset.isDQVDataTTLAvailable = isDQVDataTTLAvailable;
    },
    SET_IS_DQV_DATA_N3_AVAILABLE(state, isDQVDataN3Available) {
        state.dataset.isDQVDataN3Available = isDQVDataN3Available;
    },
    SET_IS_DQV_DATA_NT_AVAILABLE(state, isDQVDataNTAvailable) {
        state.dataset.isDQVDataNTAvailable = isDQVDataNTAvailable;
    },
    SET_IS_DQV_DATA_JSONLD_AVAILABLE(state, isDQVDataJSONLDAvailable) {
        state.dataset.isDQVDataJSONLDAvailable = isDQVDataJSONLDAvailable;
    },
    SET_CATALOG_RECORD(state, catalogRecord) {
        state.dataset.catalogRecord = catalogRecord;
    },
    SET_ADMS_IDENTIFIERS(state, admsIdentifiers) {
        state.dataset.admsIdentifiers = admsIdentifiers;
    },
    SET_DEADLINE(state, deadline) {
        state.dataset.deadline = deadline;
    },
    SET_GEOCODING_DESCRIPTION(state, geocodingDescription) {
        state.dataset.geocodingDescription = geocodingDescription;
    },
    SET_QUALIFIED_ATTRIBUTIONS(state, qualifiedAttributions) {
        state.dataset.qualifiedAttributions = qualifiedAttributions;
    },
    SET_QUALIFIED_RELATIONS(state, qualifiedRelations) {
        state.dataset.qualifiedRelations = qualifiedRelations;
    },
    SET_RELATIONS(state, relations) {
        state.dataset.relations = relations;
    },
    SET_SAMPLE(state, sample) {
        state.dataset.sample = sample;
    },
    SET_SPATIAL_RESOLUTION_IN_METERS(state, spatialResolutionInMeters) {
        state.dataset.spatialResolutionInMeters = spatialResolutionInMeters;
    },
    SET_SUBJECT(state, subject) {
        state.dataset.subject = subject;
    },
    SET_TEMPORAL_RESOLUTION(state, temporalResolution) {
        state.dataset.temporalResolution = temporalResolution;
    },
    SET_THEME(state, theme) {
        state.dataset.theme = theme;
    },
    SET_TYPE(state, type) {
        state.dataset.type = type;
    },
    SET_EXTENDET_METADATA(state, extendetMetadata) {
        state.dataset.extendetMetadata = extendetMetadata;
    },
    SET_DISTRIBUTION_DOWNLOAD_AS(state, distribution) {
        state.dataset.distributionDownloadAs = distribution;
    },
    SET_DISTRIBUTION_DOWNLOAD_AS_OPTIONS(state, selectOptions) {
        state.dataset.distributionDownloadAsOptions = selectOptions;
    },
    SET_DATASET_DESCRIPTION_HEIGHT (state, height) {
        state.dataset.descriptionHeight = height;
    },
    SET_IS_HVD(state, isHvd) {
        state.dataset.isHvd = isHvd;
    },
    SET_APPLICABLE_LEGISLATION(state, applicableLegislation) {
        state.dataset.applicableLegislation = [...applicableLegislation];
    },
    SET_HVD_CATEGORY(state, hvdCategory) {
        state.dataset.hvdCategory = [...hvdCategory];
    },
};

const module = {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
};

export default module;

export interface LoadSimilarDatasetsPayload {
    id: string,
    // query?: SimilarDatasetsQuery,
    description?: string
}
