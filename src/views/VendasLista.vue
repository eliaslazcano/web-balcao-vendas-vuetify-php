<template>
  <async-container :loading="loading">
    <v-card>
      <v-card-title>Vendas</v-card-title>
      <v-card-text>
        <v-text-field label="Pesquisar" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        sort-by="id"
        sort-desc
      >
        <template v-slot:[`item.criado_em`]="{item}">{{ formatarData(item.criado_em) }}</template>
        <template v-slot:[`item.cliente`]="{item}">
          <span v-if="item.cliente">{{ item.cliente }}</span>
          <span v-else class="grey--text">SEM NOME</span>
        </template>
        <template v-slot:[`item.debito`]="{item}">
          <span v-if="item.credito >= item.debito" class="success--text">R$ {{item.credito.toFixed(2)}}</span>
          <span v-else-if="item.credito > 0" class="warning--text">R$ {{item.debito.toFixed(2)}} ({{item.credito.toFixed(2)}})</span>
          <span v-else class="red--text">R$ {{item.debito.toFixed(2)}}</span>
        </template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-btn color="primary" small icon :to="'/venda/' + item.id">
            <v-icon>mdi-open-in-new</v-icon>
          </v-btn>
        </template>
      </v-data-table>
      <v-card-actions class="justify-center">
        <v-btn color="primary" small depressed to="/venda">
          <v-icon>mdi-plus</v-icon> Registrar venda
        </v-btn>
      </v-card-actions>
    </v-card>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import StringHelper from '@/helper/StringHelper';
export default {
  name: 'VendasLista',
  components: {AsyncContainer},
  data: () => ({
    loading: true,
    tableHeaders: [
      {value: 'id', text: 'Nº', width: '8rem'},
      {value: 'criado_em', text: 'DATA', width: '12rem'},
      {value: 'cliente', text: 'CLIENTE'},
      {value: 'debito', text: 'VALOR'},
      {value: 'acoes', text: 'ABRIR', width: '6rem', sortable: false},
    ],
    tableItems: [],
    tableSearch: '',
  }),
  methods: {
    async loadData() {
      const webclient = http();
      const {data} = await webclient.get('vendas');
      this.tableItems = data;
      this.loading = false;
    },
    formatarData(x) {
      return StringHelper.formatDate(x, '/');
    }
  },
  async created() {
    try {
      await this.loadData();
    } catch (e) {
      await this.$router.push('/');
    }
  },
}
</script>
