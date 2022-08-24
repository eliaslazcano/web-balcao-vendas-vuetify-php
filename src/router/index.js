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
    path: '/clientes',
    name: 'clientes',
    component: () => import('../views/ClientesLista.vue')
  },
  {
    path: '/cliente/:id',
    name: 'cliente',
    props: true,
    component: () => import('../views/ClienteFicha.vue')
  },
  {
    path: '/produtos',
    name: 'produtos',
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
    path: '/despesas',
    name: 'despesas',
    component: () => import('../views/DespesasLista.vue')
  },
  {
    path: '/relatorio/produtos_vendidos',
    name: 'relatorio-produtos',
    component: () => import('../views/relatorios/ProdutosVendidos.vue')
  },
  {
    path: '/relatorio/fluxocaixa',
    name: 'relatorio-caixa',
    component: () => import('../views/relatorios/FluxoCaixa.vue')
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
