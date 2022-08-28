import Vue from 'vue'
import Vuex from 'vuex'
import router from '@/router'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    snackbarShow: false,
    snackbarColor: 'primary',
    snackbarText: '',
    token: null,
  },
  getters: {
    sessionPayload(state) {
      if (!state.token) return null;
      const partes = state.token.split('.');
      if (partes.length !== 3) return null;
      try {
        return JSON.parse(window.atob(partes[1]));
      } catch (e) {
        return null;
      }
    }
  },
  mutations: {
    showSnackbar(state, snackbar) {
      state.snackbarColor = snackbar.color;
      state.snackbarText = snackbar.text;
      state.snackbarShow = true;
    },
    hideSnackbar(state) {
      state.snackbarShow = false;
    },
    setToken(state, token) {
      state.token = token;
    },
    initialiseStore(state) {
      if(sessionStorage.getItem('store')) {
        this.replaceState(Object.assign(state, JSON.parse(sessionStorage.getItem('store'))));
      }
      this.subscribe((mutation, newState) => {
        sessionStorage.setItem('store', JSON.stringify(newState));
      });
    },
  },
  actions: {
    logout({ commit }) {
      commit('setToken', null);
      return router.push('/login');
    },
  },
  modules: {
  }
})
