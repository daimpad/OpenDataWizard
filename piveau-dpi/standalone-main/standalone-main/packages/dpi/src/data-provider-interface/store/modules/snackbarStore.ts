import { type Commit } from "vuex";

type Snack = {
  delay: number,
  autohide: boolean,
  animation: boolean,
  variant: string,
  message: string,
}

export default {
  namespaced: true,

  state: {
    delay: 5000,
    autohide: true,
    animation: true,
    variant: '',
    message: '',
  },

  mutations: {
    SHOW_MESSAGE(state: Snack, payload: Snack) {
      state.message = payload.message;
      state.autohide = payload.autohide;
      state.animation = payload.animation;
      state.variant = payload.variant;
    },
  },

  actions: {
    /**
     * Commits a message to the store.
     * @param {*} commit
     * @param {*} toastOptions - Object containing the message and the toast options
     */
    showSnackbar({ commit }: { commit: Commit },
                 { message = '', variant = 'error', timeout = -1 }) {
      commit('SHOW_MESSAGE', {
        timeout,
        message,
        variant,
      });
    },
    /**
     * Commits an error message to the store.
     * @param {*} commit
     * @param {String} message The message
     */
    showError({ commit }: { commit: Commit }, message: string) {
      const payload = {
        message,
        timeout: -1,
        variant: 'error',
      };
      commit('SHOW_MESSAGE', payload);
    },
  },
};
