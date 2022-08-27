<template>
  <v-app>
    <v-app-bar
      v-if="!$route.meta.hideAppBar"
      app
      clipped-left
      dense
      dark
      :color="$vuetify.theme.dark ? 'secondary' : 'primary'"
      class="d-print-none"
    >
      <v-app-bar-nav-icon @click.stop="mostrarMenu = !mostrarMenu" />
      <v-toolbar-title v-if="config.nome_empresa">{{ config.nome_empresa }}</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn to="/venda" text>
        <span class="mr-2" v-if="$vuetify.breakpoint.mdAndUp">Vender</span>
        <v-icon>mdi-cart</v-icon>
      </v-btn>
      <v-menu left bottom offset-y close-on-content-click class="d-print-none">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-on="on" v-bind="attrs">
            <v-icon>mdi-dots-vertical</v-icon>
          </v-btn>
        </template>
        <v-list class="py-0" dense>
          <v-list-item to="/perfil">
            <v-list-item-icon>
              <v-icon>mdi-account</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Seu perfil</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item @click="$vuetify.theme.dark = !$vuetify.theme.dark">
            <v-list-item-icon>
              <v-icon v-if="$vuetify.theme.dark">mdi-white-balance-sunny</v-icon>
              <v-icon v-else>mdi-weather-night</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Modo {{ $vuetify.theme.dark ? 'claro' : 'escuro' }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/creditos">
            <v-list-item-icon>
              <v-icon>mdi-trophy-award</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Créditos</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item v-if="config.login" @click="$store.dispatch('logout')">
            <v-list-item-icon>
              <v-icon color="error">mdi-connection</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Desconectar</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-navigation-drawer app clipped v-model="mostrarMenu" v-if="!$route.meta.hideNavigationDrawer">
      <template v-if="config.login" v-slot:prepend>
        <v-list-item dense to="/perfil">
          <v-list-item-avatar class="justify-center">
            <v-avatar color="primary">
              <v-icon color="white">mdi-account</v-icon>
            </v-avatar>
          </v-list-item-avatar>
          <v-list-item-content v-if="$store.getters.sessionPayload">
            <v-list-item-title>{{ $store.getters.sessionPayload.nome }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-divider></v-divider>
      </template>
      <v-list nav dense>
        <v-list-item-group color="primary">
          <v-list-item to="/">
            <v-list-item-icon>
              <v-icon>mdi-storefront-outline</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Inicio</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/clientes">
            <v-list-item-icon>
              <v-icon>mdi-account-multiple</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Clientes</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/produtos">
            <v-list-item-icon>
              <v-icon>mdi-liquor</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Produtos</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/vendas">
            <v-list-item-icon>
              <v-icon>mdi-format-list-bulleted</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Vendas</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/despesas">
            <v-list-item-icon>
              <v-icon>mdi-playlist-edit</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Despesas</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/rfid" v-if="config.rfid">
            <v-list-item-icon>
              <v-icon>mdi-contactless-payment-circle</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Dispositivos RFID</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-group>
            <template v-slot:activator>
              <v-list-item-icon>
                <v-icon>mdi-file-document-multiple</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Relatórios</v-list-item-title>
              </v-list-item-content>
            </template>
            <v-list-item to="/relatorio/produtos_vendidos">
              <v-list-item-icon>
                <v-icon>mdi-menu-right</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Produtos Vendidos</v-list-item-title>
              </v-list-item-content>
              <v-list-item-icon>
                <v-icon>mdi-bottle-soda-classic-outline</v-icon>
              </v-list-item-icon>
            </v-list-item>
            <v-list-item to="/relatorio/fluxocaixa">
              <v-list-item-icon>
                <v-icon>mdi-menu-right</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Fluxo de caixa</v-list-item-title>
              </v-list-item-content>
              <v-list-item-icon>
                <v-icon>mdi-plus-minus-variant</v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list-group>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <transition name="fade-transition" @after-leave="$vuetify.goTo(1)" mode="out-in">
        <router-view/>
      </transition>
    </v-main>

    <v-snackbar
      app
      :timeout="3000"
      :color="$store.state.snackbarColor"
      :value="$store.state.snackbarShow"
      @input="$store.commit('hideSnackbar')"
    >
      <v-icon size="20" class="mr-2" v-if="$store.state.snackbarColor === 'warning'">mdi-alert</v-icon>
      <v-icon size="20" class="mr-2" v-else-if="$store.state.snackbarColor === 'success'">mdi-check</v-icon>
      <v-icon size="20" class="mr-2" v-else-if="$store.state.snackbarColor === 'error'">mdi-close</v-icon>
      <v-icon size="20" class="mr-2" v-else-if="$store.state.snackbarColor === 'info'">mdi-information</v-icon>
      <strong style="text-transform: uppercase">{{$store.state.snackbarText}}</strong>
    </v-snackbar>
  </v-app>
</template>

<script>
import {config} from '@/config';
export default {
  name: 'App',
  data: () => ({
    config,
    mostrarMenu: null,
  }),
  computed: {
    darkMode() {
      return this.$vuetify.theme.dark;
    }
  },
  watch: {
    darkMode() {
      this.refreshTheme();
    }
  },
  methods: {
    refreshTheme() {
      document.querySelector('meta[name="theme-color"]').setAttribute('content',  this.$vuetify.theme.dark ? '#272727' : '#1976D2');
      localStorage.setItem('darkmode', this.$vuetify.theme.dark ? '1' : '0');
    },
  },
  created() {
    const darkmode = localStorage.getItem('darkmode');
    if (darkmode != null) this.$vuetify.theme.dark = darkmode === '1';
  },
};
</script>

<style>
.v-application--is-ltr .v-data-footer__select div.v-select {
  margin-left: 8px;
}
.v-application--is-ltr div.v-data-footer__select {
  margin-right: 0;
}
.v-application--is-ltr div.v-data-footer__pagination {
  margin-left: 6px;
  margin-right: 0;
}
thead.v-data-table-header th {
  white-space: nowrap;
}
.v-dialog {
  margin: .8rem !important;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
