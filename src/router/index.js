import Vue from 'vue';
import VueRouter from 'vue-router';
import HomeView from '../views/HomeView.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/produto/lista',
    name: 'produto-lista',
    component: () => import('../views/ProdutosLista.vue')
  },
  {
    path: '/vendas',
    name: 'vendas',
    component: () => import('../views/VendasLista')
  },
  {
    path: '/venda/:id?',
    name: 'venda',
    props: true,
    component: () => import('../views/VendaFormulario.vue')
  },
  {
    path: '/suporte',
    name: 'suporte',
    component: () => import('../views/SuporteContato.vue')
  }
];

const router = new VueRouter({
  routes
});

export default router;
