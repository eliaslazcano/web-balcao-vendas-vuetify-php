<template>
  <v-container>
    <list-view :items="items"></list-view>
  </v-container>
</template>

<script>
import ListView from '@/components/ListView';
export default {
  name: 'HomeView',
  components: {ListView},
  data: () => ({
    items: [
      {id: 1, icon: 'liquor', title: 'Produtos', subtitle: 'Cadastro de produtos', to: '/produto/lista'},
      {id: 2, icon: 'format-list-bulleted', title: 'Vendas', subtitle: 'Histórico de vendas', to: '/vendas'},
      {id: 3, icon: 'cart-plus', title: 'Registrar Venda', subtitle: 'Cadastrar uma nova venda', to: '/venda'},
    ],
  }),
}
</script>
