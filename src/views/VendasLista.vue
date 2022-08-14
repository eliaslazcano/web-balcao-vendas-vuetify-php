<template>
  <async-container :loading="loading">
    <v-card>
      <v-card-title>Vendas</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataInicial" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-right"></date-picker-br>
          </v-col>
          <v-col cols="12" md="6">
            <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataFinal" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-left"></date-picker-br>
          </v-col>
        </v-row>
        <v-text-field label="Pesquisar" class="mt-3" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details autofocus></v-text-field>
      </v-card-text>
      <v-data-table :headers="tableHeaders" :items="tableItemsFiltered" :search="tableSearch" sort-by="id" sort-desc>
        <template v-slot:[`item.criado_em`]="{item}">{{ formatarData(item.criado_em) }}</template>
        <template v-slot:[`item.cliente`]="{item}">
          <span v-if="item.cliente">{{ item.cliente }}</span>
          <span v-else class="grey--text">SEM NOME</span>
        </template>
        <template v-slot:[`item.debito`]="{item}">
          <span v-if="item.credito >= item.debito" class="success--text">R$ {{ formatoMonetario(item.credito) }}</span>
          <span v-else-if="item.credito > 0" class="warning--text">R$ {{ formatoMonetario(item.credito) }} / {{ formatoMonetario(item.debito) }}</span>
          <span v-else class="red--text">R$ {{ formatoMonetario(item.debito) }}</span>
        </template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-btn color="primary" small icon :to="'/venda/' + item.id">
            <v-icon>mdi-open-in-new</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>
    <v-btn color="success" fab fixed right bottom to="/venda">
      <v-icon>mdi-plus</v-icon>
    </v-btn>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import StringHelper from '@/helper/StringHelper';
import DatePickerBr from '@/components/DatePickerBr';
import moment from '@/plugins/moment';
export default {
  name: 'VendasLista',
  components: {DatePickerBr, AsyncContainer},
  data: () => ({
    loading: true,
    tableHeaders: [
      {value: 'id', text: 'Nº', width: '7rem'},
      {value: 'criado_em', text: 'DATA', width: '12rem'},
      {value: 'cliente', text: 'CLIENTE'},
      {value: 'debito', text: 'VALOR'},
      {value: 'acoes', text: 'ABRIR', width: '6rem', sortable: false},
    ],
    tableItems: [],
    tableSearch: '',
    iptDataInicial: moment().format('YYYY-MM-01'),
    iptDataFinal: moment().format('YYYY-MM-DD'),
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
    },
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
  },
  computed: {
    tableItemsFiltered() {
      return this.tableItems.filter(i => i.criado_em.substr(0, 10) >= this.iptDataInicial && i.criado_em.substr(0, 10) <= this.iptDataFinal);
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
