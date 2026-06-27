// @ts-nocheck
/* eslint-disable no-param-reassign,no-console */

const state = {
  catalog: {},
};

const getters = {
  getCatalog: state => state.catalog,
};

const actions = {
  /**
   * @description Loads details for the dataset with the given ID.
   * @param commit
   * @param state
   * @param id {String} The dataset ID.
   */
  loadCatalog({ state, commit }, id) {
    return new Promise((resolve, reject) => {
      this.$catalogService.getSingle(id)
        .then((response) => {
          commit('SET_catalog', response);
          resolve(response);
        })
        .catch((err) => {
          console.error(err);
          reject(err);
        });
    });
  },
};

const mutations = {
  SET_catalog(state, catalog) {
    state.catalog = catalog;
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
