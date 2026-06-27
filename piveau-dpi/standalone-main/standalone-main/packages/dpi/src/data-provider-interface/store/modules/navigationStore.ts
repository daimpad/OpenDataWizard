// @ts-nocheck
/* eslint-disable no-param-reassign, no-shadow, no-console */

const state = {
    navigation: {
        datasets: [],
        distributions: [],
        catalogues: [],
    }
};

const getters = {
    getNavSteps: (state, _getters, _rootState, rootGetters) => (specification) => {
        const dpiConfig = rootGetters['dpiStore/getSpecification'];
        setConfig(dpiConfig);
       
        return state.navigation;
    },
};
function setConfig(specification) {
    state.navigation.datasets = Object.keys(specification.pageConent.datasets);
    state.navigation.distributions = Object.keys(specification.pageConent.distributions);
    state.navigation.catalogues = Object.keys(specification.pageConent.catalogues);
}

const actions = {};
const mutations = {};

const navigationModule = {
    state,
    getters,
    actions,
    mutations
};

export default navigationModule;