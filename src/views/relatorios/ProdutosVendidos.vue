<template>
  <async-container :loading="loading">
    <v-card>
      <v-card-title>Produtos Vendidos</v-card-title>
      <v-card-text>
        <v-row dense>
          <v-col cols="12" md="6">
            <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataInicio" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-right"></date-picker-br>
          </v-col>
          <v-col cols="12" md="6">
            <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataFim" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-right"></date-picker-br>
          </v-col>
        </v-row>
        <v-text-field label="Pesquisar" class="mt-3" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        :loading="tableLoading"
        sort-by="quantidade"
        sort-desc
        no-data-text="Nenhum produto vendido"
      >
        <template v-slot:[`item.valor`]="{item}">R$ {{ item.valor ? parseFloat(item.valor).toFixed(2) : '0.00' }}</template>
      </v-data-table>
    </v-card>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import moment from '@/plugins/moment';
import DatePickerBr from '@/components/DatePickerBr';
export default {
  name: 'ProdutosVendidos',
  components: {DatePickerBr, AsyncContainer},
  data: () => ({
    loading: true,
    iptDataInicio: moment().subtract(1, 'months').format('YYYY-MM-DD'),
    iptDataFim: moment().format('YYYY-MM-DD'),
    tableLoading: false,
    tableHeaders: [
      {value: 'nome', text: 'PRODUTO'},
      {value: 'quantidade', text: 'QUANTIDADE'},
      {value: 'valor', text: 'VALOR'},
    ],
    tableItems: [],
    tableSearch: '',
  }),
  methods: {
    async loadData() {
      this.tableLoading = true;
      const webclient = http();
      const params = {data_inicio: this.iptDataInicio, data_fim: this.iptDataFim};
      const {data} = await webclient.get('relatorios/produtos_vendidos', {params});
      this.tableItems = data;
      this.tableLoading = false;
    },
  },
  async created() {
    try {
      await this.loadData();
    } catch (e) {
      await this.$router.push('/');
    } finally {
      this.loading = false;
    }
  },
  watch: {
    iptDataInicio(v) {
      if (v) this.loadData();
    },
    iptDataFim(v) {
      if (v) this.loadData();
    },
  },
}
</script>

<style scoped>

</style>