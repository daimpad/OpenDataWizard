// @ts-nocheck
import { createStore } from 'vuex'

// Import store modules
import catalogs from './modules/cataloguesStore';
import catalogDetails from './modules/catalogueDetailsStore';
import datasets from './modules/datasetsStore';
import datasetDetails from './modules/datasetDetailsStore';
import auth from './modules/authStore';
import snackbar from './modules/snackbarStore';
import dpiStore from './modules/dpiStore';

const state = {};

const actions = {};

const mutations = {};

const getters = {
  /**
   * @description Returns the current route (name).
   * @param state
   */
  getCurrentRoute: state => state.route,
};

const store = createStore({
  state,
  actions,
  mutations,
  getters,
  modules: {
    catalogs,
    catalogDetails,
    datasets,
    datasetDetails,
    dpiStore,
    auth,
    snackbar,
  },
});

export default store;
