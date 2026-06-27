// @ts-nocheck
/* eslint-disable no-param-reassign,no-unused-vars,no-console,consistent-return */

const RESULTS_PER_PAGE = 10;
// const RESULTS_PER_PAGE = GLUE_CONFIG.routing.pagination.defaultItemsPerPage;

const state = {
  datasets: [],
  loading: false,
  searchParameters: {
    query: '',
    fields: undefined, 
    limit: RESULTS_PER_PAGE,
    offset: 0,
    // The Facets to filter for
    facets: {},
    facetOperator: 'AND',
    facetGroupOperator: 'AND',
    dataServices: 'false',
    superCatalogue: undefined, // !!!!! Project specific (Bayern). Should be removed as soon as possible !!!!
    datasetGeoBounds: undefined,
    sort: 'relevance+desc, modified+desc, title.en+asc',
  },
  availableFacets: [],
  page: 1,
  pageCount: 1,
  datasetsCount: 0,
  dataScope: undefined,
  minScoring: undefined,
  scoringFacets: [
    {
      id: 'excellentScoring',
      title: 'Excellent',
      count: 0,
      minScoring: 351,
      maxScoring: 405,
    },
    {
      id: 'goodScoring',
      title: 'Good',
      count: 0,
      minScoring: 221,
      maxScoring: 350,
    },
    {
      id: 'sufficientScoring',
      title: 'Sufficient',
      count: 0,
      minScoring: 121,
      maxScoring: 220,
    },
    {
      id: 'anyScoring',
      title: 'Any',
      count: 0,
      minScoring: 0,
      maxScoring: 120,
    },
  ],
};

const GETTERS = {
  getDatasets: state => state.datasets,
  getDatasetsCount: state => state.datasetsCount,
  getQuery: state => state.searchParameters.query,
  getFields: state => state.searchParameters.fields,
  getLimit: state => state.searchParameters.limit,
  getLoading: state => state.loading,
  getOffset: state => state.searchParameters.offset,
  getFacets: (state) => {
    const preparedFacets = {...state.searchParameters.facets};
    if (preparedFacets.categories) preparedFacets.categories = preparedFacets.categories.map(c => c.toUpperCase());
    if (preparedFacets.dataScope) delete preparedFacets.dataScope;
    return preparedFacets;
  },
  getFacetOperator: state => state.searchParameters.facetOperator,
  getFacetGroupOperator: state => state.searchParameters.facetGroupOperator,
  getDataServices: state => state.searchParameters.dataServices,
  getSuperCatalogue_DEPRECATED: state => state.searchParameters.superCatalogue, // !!!!! Project specific (Bayern). Should be removed as soon as possible !!!!
  getDatasetGeoBounds: state => state.searchParameters.datasetGeoBounds,
  getAvailableFacets: state => state.availableFacets,
  // Hacky solutiohn to insert special facets
  getAllAvailableFacets: (state) => {
    const allAvailableFacets = [...state.availableFacets];
    const indexOfScoring = allAvailableFacets.findIndex(facet => facet.id === 'scoring');
    allAvailableFacets.splice(indexOfScoring, 0,
      {
        id: 'dataServices',
        items: [
          { count: undefined, id: 'true', title: 'yes' },
          { count: undefined, id: 'false', title: 'no' },
        ],
        title: 'Data services',
      });
    // allAvailableFacets.push({
    //   id: 'erpd',
    //   items: [
    //     { count: undefined, id: 'true', title: 'yes' },
    //     { count: undefined, id: 'false', title: 'no' },
    //   ],
    //   title: 'ERPD',
    // });
    return allAvailableFacets;
  },
  getPage: state => state.page,
  getPageCount: state => state.pageCount,
  getSort: state => state.searchParameters.sort,
  getMinScoring: state => state.minScoring,
  getScoringFacets: state => state.scoringFacets,
  getDataScope: state => state.dataScope,
};

const actions = {
  /**
   * @description Load all datasets matching the given parameters.
   * @param commit
   * @param state
   * @param options {Object} - Given search parameters
   * @param options.query {String} - The given query string
   * @param options.fields {String} - The given fields
   * @param options.facets {Array} - The active facets
   * @param options.limit {Number} - The maximum amount of datasets to fetch
   * @param options.page {Number} - The current page
   * @param options.sort {String} - The sort method to use
   * @param options.geoBounds {Array} - The given bounds to setup a spatial search for
   * @param options.append {Boolean} - Decides whether current datasets in state will be replaced or fetched datasets appended.
   */
  loadDatasets(
    { commit, state },
    {
      locale,
      query = GETTERS.getQuery(state),
      fields = GETTERS.getFields(state),
      limit = GETTERS.getLimit(state),
      page = GETTERS.getPage(state),
      sort = GETTERS.getSort(state),
      facetOperator = GETTERS.getFacetOperator(state),
      facetGroupOperator = GETTERS.getFacetGroupOperator(state),
      dataServices = GETTERS.getDataServices(state),
      facets = GETTERS.getFacets(state),
      geoBounds = GETTERS.getDatasetGeoBounds(state),
      minScoring = GETTERS.getMinScoring(state),
      dataScope = GETTERS.getDataScope(state),
      append = false,
    },
  ) {

    commit('SET_LOADING', true);

    const gb = geoBounds;
    if (gb instanceof Array && gb[0] && gb[1]) {
      geoBounds = `${gb[0][0]},${gb[0][1]},${gb[1][0]},${gb[1][1]}`;
    } else {
      geoBounds = undefined;
    }

    if (facets.catalog[0] === 'erpd') { // Special case: do not load datasets of catalog but rather all datasets of all sub-catalogs
      facets.catalog = [];
      facets.superCatalog = ['erpd'];
    }

    return new Promise((resolve, reject) => {
      this.$datasetService.get(query, fields, locale, limit, page, sort, facetOperator, facetGroupOperator, dataServices, facets, geoBounds, minScoring, dataScope)
        .then((response) => {
          commit('SET_FIEELDS', response.fields);
          commit('SET_AVAILABLE_FACETS', response.availableFacets);
          commit('SET_SCORING_COUNT', response.scoringCount);
          commit('SET_DATASETS_COUNT', response.datasetsCount);
          if (append) commit('ADD_DATASETS', response.datasets);
          else commit('SET_DATASETS', response.datasets);
          commit('SET_LOADING', false);
          resolve();
        })
        .catch((error) => {
          console.error(error);
          commit('SET_LOADING', false);
          reject(error);
        });
    });
  },

    /**
   * @description Loads more datasets.
   * @param commit
   * @param state
   * @param {number} amount - The amount of datasets to add.
   */
  loadSingleDataset({ commit }, id) {
    return new Promise((resolve, reject) => {
      this.$datasetService.getSingle(id)
        .then((response) => {
          resolve(response);
        })
        .catch((error) => {
          console.error(error);
          reject(error);
        });
    });
  },

  /**
   * @description Loads more datasets.
   * @param commit
   * @param state
   * @param {number} amount - The amount of datasets to add.
   */
  loadAdditionalDatasets({ commit, state }) {
    const page = GETTERS.getPage(state);
    // const datasetsCount = GETTERS.getDatasetsCount(state);
    actions.loadDatasets({ commit, state }, { page, append: true });
  },

  /**
   * @description Autocomplete a query String by using a autocompletion service
   * @param commit
   * @param q {String} The Query to autocomplete
   */
  autocompleteQuery({ commit }, q) {
    // If autocomplete function does not exist in this service -> Abort
    if (typeof this.$datasetService.autocomplete !== 'function') return;
    return new Promise((resolve, reject) => {
      this.$datasetService.autocomplete(q)
        .then((response) => {
          resolve(response);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },

  /**
   * @description Replace the current state facets by the given facets
   * @param commit
   * @param facets {Array} - The given facets
   */
  setFacets({ commit }, facets) {
    if (facets) commit('SET_FACETS', facets);
  },

  /**
   * @description Add the given facet to the states facets.
   * @param commit
   * @param params {Object} - The wrapped action parameters.
   * @param params.field {String} - The field of the given facet
   * @param params.facet {String} - The facet to add
   */
  addFacet({ commit }, { field, facet }) {
    commit('ADD_FACET', { field, facet });
  },

  /**
   * @description Remove the given facet from the states facets.
   * @param commit
   * @param params {Object} - The wrapped action parameters.
   * @param params.field {String} - The field of the given facet
   * @param params.facet {String} - The facet to remove
   */
  removeFacet({ commit }, { field, facet }) {
    commit('REMOVE_FACET', { field, facet });
  },

  /**
   * @description Remove the given facet from the states facets.
   * @param commit
   * @param operator {String} - The facet operator to set. Possible Operators : ['AND', 'OR'].
   */
  setFacetOperator({ commit }, operator) {
    commit('SET_FACET_OPERATOR', operator);
  },

  /**
   * @description Remove the given facet from the states facets.
   * @param commit
   * @param operator {String} - The facet operator to set. Possible Operators : ['AND', 'OR'].
   */
  setFacetGroupOperator({ commit }, operator) {
    commit('SET_FACET_GROUP_OPERATOR', operator);
  },

  /**
   * @description Remove the given facet from the states facets.
   * @param commit
   * @param operator {String} - The data services to set. Possible Operators : ['true', 'false'].
   */
  setDataServices({ commit }, dataServices) {
    commit('SET_DATA_SERVICES', dataServices);
  },

  /**
   * @description Remove the given facet from the states facets.
   * @param commit
   * @param operator {String} - Only Erpd data. Possible Operators : ['true', 'false'].
   */
  setSuperCatalogue_DEPRECATED({ commit }, superCatalogue) { // !!!!! Project specific (Bayern). Should be removed as soon as possible !!!!
    commit('SET_SUPER_CATALOGUE', superCatalogue);
  },
  setPage({ commit }, page) {
    commit('SET_PAGE', page);
  },
  setPageCount({ commit }, count) {
    commit('SET_PAGE_COUNT', count);
  },
  setQuery({ commit }, query) {
    commit('SET_QUERY', query);
  },
  setFields({ commit }, fields) {
    commit('SET_FIELDS', fields);
  },
  setSort({ commit }, sort) {
    commit('SET_SORT', sort);
  },
  setLimit({ commit }, amount = RESULTS_PER_PAGE) {
    commit('SET_LIMIT', amount);
  },
  setDatasetGeoBounds({ commit }, bounds) {
    commit('SET_DATASET_GEO_BOUNDS', bounds);
  },
  setLoading({ commit }, isLoading) {
    commit('SET_LOADING', isLoading);
  },
  setMinScoring({ commit }, minScoring) {
    commit('SET_MIN_SCORING', minScoring);
  },
  setDataScope({ commit }, dataScope) {
    commit('SET_DATA_SCOPE', dataScope);
  },
  setAvailableFacets({ commit }, facets) {
    commit('SET_AVAILABLE_FACETS', facets);
  },
  setScoringCount({ commit }, count) {
    commit('SET_SCORING_COUNT', count);
  },
  setDatasetsCount({ commit }, count) {
    commit('SET_DATASETS_COUNT', count);
  },
  setDatasets({ commit }, datasets) {
    commit('SET_DATASETS', datasets);
  },
};

const mutations = {
  SET_DATASETS(state, data) {
    state.datasets = data;
  },
  ADD_DATASETS(state, data) {
    state.datasets = state.datasets.concat(data);
  },
  SET_LIMIT(state, limit) {
    state.searchParameters.limit = limit;
  },
  SET_OFFSET(state, offset) {
    state.searchParameters.offset = offset;
  },
  SET_FACETS(state, facets) {
    state.searchParameters.facets = {...facets};
  },
  ADD_FACET(state, { field, facet }) {
    // If the facetField is not defined in state..
    if (!Object.prototype.hasOwnProperty.call(state.searchParameters.facets, field)) {
      // ..define it by assigning an array containing {value} in it
      state.searchParameters.facets[field] = [facet];
    } else {
      state.searchParameters.facets[field].push(facet);
    }
  },
  REMOVE_FACET(state, { field, facet }) {
    const index = state.searchParameters.facets[field].indexOf(facet);
    state.searchParameters.facets[field].splice(index, 1);
  },
  SET_DATASET_GEO_BOUNDS(state, bounds) {
    state.searchParameters.datasetGeoBounds = bounds;
  },
  SET_AVAILABLE_FACETS(state, facets) {
    state.availableFacets = facets;
  },
  SET_SCORING_COUNT(state, scoringCount) {
    state.scoringFacets.forEach((scoringFacet) => {
      scoringFacet.count = scoringCount[scoringFacet.id];
    });
  },
  SET_DATASETS_COUNT(state, datasetsCount) {
    state.datasetsCount = datasetsCount;
  },
  SET_FACET_OPERATOR(state, operator) {
    state.searchParameters.facetOperator = operator;
  },
  SET_FACET_GROUP_OPERATOR(state, operator) {
    state.searchParameters.facetGroupOperator = operator;
  },
  SET_DATA_SERVICES(state, dataServices) {
    state.searchParameters.dataServices = dataServices;
  },
  SET_SUPER_CATALOGUE(state, superCatalogue) { // !!!!! Project specific (Bayern). Should be removed as soon as possible !!!!
    state.searchParameters.superCatalogue = superCatalogue;
  },
  SET_PAGE(state, page) {
    state.page = page;
  },
  SET_PAGE_COUNT(state, count) {
    state.pageCount = count;
  },
  SET_QUERY(state, query) {
    state.searchParameters.query = query;
  },
  SET_FIELDS(state, fields) {
    state.searchParameters.fields = fields;
  },
  SET_SORT(state, sort) {
    state.searchParameters.sort = sort;
  },
  SET_LOADING(state, isLoading) {
    state.loading = isLoading;
  },
  SET_MIN_SCORING(state, minScoring) {
    state.minScoring = minScoring;
  },
  SET_DATA_SCOPE(state, dataScope) {
    state.dataScope = dataScope;
  },
};

const module = {
  namespaced: true,
  state,
  actions,
  mutations,
  getters: GETTERS,
};

export default module;
