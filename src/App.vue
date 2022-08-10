<template>
  <v-app>
    <v-app-bar
      app
      color="primary"
      dark
    >
      <v-app-bar-nav-icon @click.stop="mostrarMenu = !mostrarMenu" />
      <v-spacer></v-spacer>
      <v-btn to="/suporte" text>
        <span class="mr-2">Suporte</span>
        <v-icon>mdi-open-in-new</v-icon>
      </v-btn>
    </v-app-bar>

    <v-navigation-drawer app v-model="mostrarMenu">
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
          <v-list-item to="/produto/lista">
            <v-list-item-icon>
              <v-icon>mdi-liquor</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Produtos</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item to="/venda">
            <v-list-item-icon>
              <v-icon>mdi-plus</v-icon>
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
