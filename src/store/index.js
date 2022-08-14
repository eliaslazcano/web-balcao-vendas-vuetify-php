import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    snackbarShow: false,
    snackbarColor: 'primary',
    snackbarText: '',
  },
  getters: {
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
  },
  actions: {
  },
  modules: {
  }
})
