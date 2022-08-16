<template>
  <v-app>
    <v-app-bar app clipped-left dense dark :color="$vuetify.theme.dark ? 'secondary' : 'primary'">
      <v-app-bar-nav-icon @click.stop="mostrarMenu = !mostrarMenu" />
      <v-toolbar-title v-if="nome_empresa">{{ nome_empresa }}</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn to="/venda" text>
        <span class="mr-2">Vender</span>
        <v-icon>mdi-cart-plus</v-icon>
      </v-btn>
      <v-menu left bottom offset-y class="d-print-none">
        <template v-slot:activator="{ on: menu, attrs }">
          <v-tooltip bottom>
            <template v-slot:activator="{ on: tooltip }">
              <v-btn icon v-on="{ ...tooltip, ...menu }" v-bind="attrs">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <span>Opções</span>
          </v-tooltip>
        </template>
        <v-list class="py-0" dense>
          <v-list-item @click="changeDarkMode">
            <v-list-item-icon>
              <v-icon v-if="$vuetify.theme.dark">mdi-white-balance-sunny</v-icon>
              <v-icon v-else>mdi-weather-night</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Modo escuro</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/suporte">
            <v-list-item-icon>
              <v-icon>mdi-face-agent</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Suporte</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-navigation-drawer app clipped v-model="mostrarMenu">
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
              <v-list-item-content>
                <v-list-item-title>Produtos Vendidos</v-list-item-title>
              </v-list-item-content>
              <v-list-item-icon>
                <v-icon>mdi-bottle-soda-classic-outline</v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list-group>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <router-view/>
    </v-main>

    <v-snackbar
      app
      :timeout="3000"
      :color="$store.state.snackbarColor"
      :value="$store.state.snackbarShow"
      @input="$store.commit('hideSnackbar')"
    >{{$store.state.snackbarText}}</v-snackbar>
  </v-app>
</template>

<script>
import http from '@/plugins/axios';
export default {
  name: 'App',
  data: () => ({
    mostrarMenu: null,
    nome_empresa: null,
  }),
  methods: {
    changeDarkMode() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark;
      localStorage.setItem('darkmode', this.$vuetify.theme.dark ? '1' : '0');
    },
    async carregarConfiguracoesGlobais() {
      const webclient = http();
      try {
        const {data} = await webclient.get('configuracoes');
        if (data && data.nome_empresa) this.nome_empresa = data.nome_empresa;
      } catch (e) {
        //
      }
    },
  },
  created() {
    const darkmode = localStorage.getItem('darkmode');
    if (darkmode != null) this.$vuetify.theme.dark = darkmode === '1';

    this.carregarConfiguracoesGlobais();
  },
};
</script>
