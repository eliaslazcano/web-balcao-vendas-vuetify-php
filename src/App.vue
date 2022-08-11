<template>
  <v-app>
    <v-app-bar app clipped-left dense dark :color="$vuetify.theme.dark ? 'secondary' : 'primary'">
      <v-app-bar-nav-icon @click.stop="mostrarMenu = !mostrarMenu" />
      <v-spacer></v-spacer>
      <v-btn to="/suporte" text>
        <span class="mr-2">Suporte</span>
        <v-icon>mdi-open-in-new</v-icon>
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
          <v-list-item @click="$vuetify.theme.dark = !$vuetify.theme.dark">
            <v-list-item-icon>
              <v-icon v-if="$vuetify.theme.dark">mdi-white-balance-sunny</v-icon>
              <v-icon v-else>mdi-weather-night</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Modo escuro</v-list-item-title>
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
              <v-icon>mdi-home</v-icon>
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
          <v-list-item to="/venda">
            <v-list-item-icon>
              <v-icon>mdi-cart-plus</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Registrar venda</v-list-item-title>
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
                <v-icon>mdi-shopping</v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list-group>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <router-view/>
    </v-main>
  </v-app>
</template>

<script>

export default {
  name: 'App',
  data: () => ({
    mostrarMenu: null,
  }),
};
</script>
