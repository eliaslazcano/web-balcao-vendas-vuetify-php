import Vue from 'vue';
import VueRouter from 'vue-router';
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/LoginView.vue';
import {config} from '@/config';
import store from '@/store';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/login',
    name: 'login',
    meta: { hideAppBar: true, hideNavigationDrawer: true },
    component: LoginView
  },
  {
    path: '/perfil',
    name: 'perfil',
    meta: { requiresAuth: true },
    component: () => import('../views/GerenciarPerfil.vue')
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

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!store.state.token) next('/login');
  }
  if (config.login) {
    if (store.state.token) {
      if (to.name === 'login') next('/');
      else next();
    } else {
      if (to.name === 'login') next();
      else next('/login');
    }
  } else {
    if (to.name === 'login') next('/');
    else next();
  }
});

export default router;
