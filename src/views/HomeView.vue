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
      {id: 1, icon: 'account-multiple', title: 'Clientes', subtitle: 'Cadastro de clientes', to: '/clientes'},
      {id: 2, icon: 'liquor', title: 'Produtos', subtitle: 'Cadastro de produtos', to: '/produtos'},
      {id: 3, icon: 'format-list-bulleted', title: 'Vendas', subtitle: 'Histórico de vendas', to: '/vendas'},
    ],
  }),
}
</script>
