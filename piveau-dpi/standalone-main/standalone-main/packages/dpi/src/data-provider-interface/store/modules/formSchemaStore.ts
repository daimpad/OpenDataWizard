// @ts-nocheck
/* eslint-disable no-param-reassign, no-shadow, no-console */
import { isEmpty } from 'lodash-es';

// external translation method
import translate from '../../utils/translation-helper';

const state = {
    schema: {
        datasets: {},
        distributions: {},
        catalogues: {}
    },
    usersCatalogs: {}
};

const getters = {
    getSchema: (state) => (property) => {    
        console.log(state.schema[property]);
                
        return state.schema[property];
    },
};

const actions = {
    /**
     * Commits page properties and related DCATAP property definitions for current view for later building of schema
     * @param {Object} param0
     * @param {Object} param1 Object containing property (datasets/catalogues), page (step1/step2/step3) and subpage (distribution1/distribution2/distribution3) of current view
     */
    createSchema({ commit, state, rootGetters }, { property, page }) {

        const dpiConfig = rootGetters['dpiStore/getSpecification'];
        const pageProperties = dpiConfig.pageConent[property][page]
        const propertyDefinitions = dpiConfig.inputDefinition[property]
        commit('extractSchema', { pageProperties, propertyDefinitions, property, page });

    },
    /**
     * Calls mutation function for translating translatable properties of schema
     * @param {*} param0
     */
    translateSchema({ commit }, { property, page }) {

        commit('translateSchemaProperties', { property, page });
    },
    /**
     * Commits current property and users catalogs to mutation function
     * @param {*} param0
     * @param {*} param1 Object containing curren tproperty (datasets/catalogues) and all catalog options the user has permissions for
     */
    addCatalogOptions({ commit }, { property, catalogs }) {
        commit('saveCatalogOptions', { property, catalogs });
    },
};

const mutations = {
    /**
     * Builds schema based on given pageProperties and their definitions provided by propertyDefinition
     * @param {Object} state
     * @param {Object} param1 Object containing the properties which should be displayed within the view
     * (pageProperties) and the form definitions of the properties defined by DCATAP (propertyDefinition)
     */
    extractSchema(state, { pageProperties, propertyDefinitions, property, page }) {
        // important: create new empty schema each time so already existing schema will be overwritten on route/view-change
        const newSchema = [];

        for (let index = 0; index < pageProperties.length; index += 1) {
            const propertyKey = pageProperties[index];
            try {
                newSchema.push(propertyDefinitions[propertyKey]);
            } catch (err) {
                console.warn(`DCATAP doens't include a property called: ${propertyKey}`);
            }
        }

        state.schema[property][page] = newSchema;
    },
    /**
     * All translatable properties within the existing schema will be translated and added to the schema
     * @param {*} state
     * @param {String} property String defining current property (datasets/ distribution/ catalogues)
     */
    translateSchemaProperties(state, { property, page }) {
        translate(state.schema[property][page], property);
    },
    /**
     * Saves all catalogs the current user has permissions for as options within the schema
     * @param {*} state
     * @param {*} param1 Object containing current property and the users catalog options
     */
    saveCatalogOptions(state, { property, catalogs }) {
        if (property === 'datasets') {

            let catalogSchema;
            for (let key in state.schema[property]) {
                const currentSchema = state.schema[property][key].filter(dataset => dataset.name === "dcat:catalog");
                if (!isEmpty(currentSchema)) catalogSchema = currentSchema[0];
            }

            if (!isEmpty(catalogSchema)) {
                const catalogOptions = {};
                for (let index = 0; index < catalogs.length; index += 1) {
                    const value = catalogs[index];
                    catalogOptions[value] = value;
                }
                catalogSchema.options = catalogOptions;
            }
        }
    },
};

const formModule = {
    state,
    getters,
    actions,
    mutations
};

export default formModule;
