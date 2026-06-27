// @ts-nocheck
/* eslint-disable no-param-reassign, no-shadow, no-console */
import N3 from 'n3';

import { isEmpty, has } from 'lodash';
import datasetFactory from '@rdfjs/dataset';

import generalHelper from '../../utils/general-helper';
import toRDF from '../../utils/RDFconverter';
import toInput from '../../utils/inputConverter';

const state = {
    datasets: {},
    distributions: [],
    catalogues: {},
    deleteDistributionInline: false,
};

const getters = {
    /**
     * Returns raw values for given property, page and distribution
     * @param state 
     * @param0 Object containing property, page and distribution id
     * @returns Object conatining form values for given property, distribution and page
     */
    getRawValues: (state) => ({ property }) => {
        return state[property];
    },
    /**
     * Provides property data
     * @param state 
     * @param property Property of wanted data
     * @returns Object containing all values of given property
     */
    getData: (state) => (property) => {
        let data;
        if (property === 'distributions') {
            data = [];
            for (let index = 0; index < state[property].length; index += 1) {
                const currentDistributionData = generalHelper.mergeNestedObjects(state[property][index]);
                data.push(currentDistributionData);
            }

        } else {
            data = generalHelper.mergeNestedObjects(state[property])
        }
       
        
        return data;
    },
};

const actions = {
    /**
     * Saves values from input form to vuex store
     * @param param0 
     * @param param1 Object containing property, page, distrbution id and form values
     */
    saveFormValues({ commit }, { property, values }) {
console.log('###change');

        commit('saveFormValuesToStore', { property, values });

    },
    /**
     * Saving existing values from localStorage to vuex store
     * @param {*} param0
     * @param {*} param1 Object containing property (datasets/catalogues) and subpage (when curren tprofile is distributions)
     */
    saveLocalstorageValues({ commit }, property) {
        commit('saveFromLocalstorage', property);
    },
    /**
     * Fetches data, writes it to a dataset and calls method for actual conversion to input format
     * @param param0 
     * @param param1 Object containing endpoint and token for data fetching as well as property
     */
    async convertToInput({ commit, rootGetters }, { endpoint, token, property }) {
        const specification = rootGetters['dpiStore/getSpecification'];
        const fetchedData = await generalHelper.fetchLinkedData(endpoint, token).then((response) => {
            return response;
        });

        const parser = new N3.Parser();
        const data = datasetFactory.dataset();

        // adding quads to dataset
        await parser.parse(fetchedData, (error, quad, prefixes) => {
            if (quad) data.add(quad);
        })

        commit('saveLinkedDataToStore', { property, data, specification });
    },
    /**
     * Merges store data and converts the given input values into RDF format
     * @param state 
     * @param property Object containing all values within nested objects for each page of the frontend
     * @returns Data values in RDF format
     */
    convertToRDF({ state, rootGetters }, { property }) {
        const specification = rootGetters['dpiStore/getSpecification'];

        // merging objects with nested objects containing the values of each page into one main object containing all values for the given property
        const data = {
            datasets: generalHelper.mergeNestedObjects(state.datasets),
            distributions: [],
            catalogues: generalHelper.mergeNestedObjects(state.catalogues)
        };

        // merging each distribution object within the overall array of distributions
        if (has(state.datasets, 'Distributions') && has(state.datasets.Distributions, 'distributionList') && !isEmpty(state.datasets.Distributions.distributionList)) {
            for (let index = 0; index < state.datasets.Distributions.distributionList.length; index++) {
                data.distributions.push(generalHelper.mergeNestedObjects(state.datasets.Distributions.distributionList[index]))
            }
        }

        const RDFdata = toRDF.convertToRDF(data, property, specification);

        return RDFdata;
    },
    /**
     * Calls mutation to clear values and store
     * @param param0 
     */
    clearAll({ commit }) {
        commit('resetStore');
    },  
};

const mutations = {
    /**
     * Saves input values from form into vuex as well as into localStorage of browser
     * @param state 
     * @param param1 Object containing the property, page and values of input form
     */
    saveFormValuesToStore(state, { property, values }) {

        state[property] = values;

        // save to local storage
        localStorage.setItem(`dpi_${property}`, JSON.stringify(state[property]));
    },
    /**
    * Save input form values from localStorage into vuex store
    * @param {*} state
    * @param {String} property Property name the data should be saved to (datasets/distributions/catalogues)
    */
    saveFromLocalstorage(state, property) {
        let valueName;

        if (property === 'catalogues') {
            valueName = 'dpi_catalogues';
        } else {
            valueName = 'dpi_datasets';
        }

        // extract catalogues or datasets data
        if (Object.keys(localStorage).includes(valueName)) {
            const localStorageData = JSON.parse(localStorage.getItem(valueName));
            if (property === 'catalogues') state[property] = localStorageData;
            else state.datasets = localStorageData;
        }
    },
    /**
     * Converts RDF data into input form data
     * @param state 
     * @param param1 Object containing data and property and state
     */
    saveLinkedDataToStore(state, { property, data, specification }) {

        const dpiConfig = specification;
        toInput.convertToInput(state, property, data, dpiConfig );

        localStorage.setItem(`dpi_${property}`, JSON.stringify(state[property]));
    },
    resetStore(state) {
        // remove dpi values from local store
        localStorage.removeItem('dpi_datasets');
        localStorage.removeItem('dpi_catalogues');

        // resetting all store data properties
        state.datasets = {};
        state.catalogues = {};

        // edit and draft mode not within this store so resetting via local storage
        localStorage.setItem('dpi_editmode', false);
        localStorage.setItem('dpi_draftmode', false);
    },
};

const conversionModule = {
    state,
    getters,
    actions,
    mutations
};

export default conversionModule;
