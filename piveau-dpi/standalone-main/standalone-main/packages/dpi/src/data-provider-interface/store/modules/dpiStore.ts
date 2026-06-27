// @ts-nocheck
/* eslint-disable no-param-reassign, no-shadow, no-console */
// import formModule from './modules/formSchemaStore';
import navigationModule from './navigationStore';

const state = {
  // DPI specification as root state to be shared between all modules
  specification: {},
  specificationName: '',
};
const getters = {
  getSpecification: state => state.specification,
  getSpecificationName: state => state.specificationName
};
const actions = {
  setSpecification: (context, specification) => {
    context.commit('setSpecification', specification);
  },
  setSpecificationname: (context, specificationName) => {
    context.commit('setSpecificationName', specificationName);
  }
};
const mutations = {
  setSpecification: (state, specification) => (state.specification = specification),
  setSpecificationName: (state, specificationName) => (state.specificationName = specificationName)
};

const module = {
  namespaced: true,
  state,
  actions,
  mutations,
  getters,
  modules: {
    // formModule,
    navigationModule
  }
};

export default module;
