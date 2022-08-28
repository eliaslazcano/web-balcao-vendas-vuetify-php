<template>
  <async-container :loading="loading">
    <v-card width="72rem" class="mx-auto mb-12">
      <v-card-title class="justify-space-between">
        Produtos Vendidos
        <v-menu left bottom offset-y class="d-print-none">
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon v-bind="attrs" v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list class="py-0" dense>
            <v-list-item @click="tableDense = !tableDense">
              <v-list-item-icon>
                <v-icon>{{ tableDense ? 'mdi-arrow-expand-vertical' : 'mdi-arrow-collapse-vertical' }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ tableDense ? 'Visualização expandida' : 'Visualização compacta' }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item @click="imprimir">
              <v-list-item-icon>
                <v-icon>mdi-printer</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Imprimir</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-card-title>
      <v-card-text>
        <v-row dense>
          <v-col cols="12" md="6">
            <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataInicio" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-right"></date-picker-br>
          </v-col>
          <v-col cols="12" md="6">
            <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataFim" label="Data limite" prepend-inner-icon="mdi-calendar-arrow-right"></date-picker-br>
          </v-col>
        </v-row>
        <v-text-field label="Pesquisar" class="mt-3" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        :loading="tableLoading"
        :dense="tableDense"
        sort-by="quantidade"
        sort-desc
        no-data-text="Nenhum produto vendido"
        :footer-props="{'items-per-page-options': [10, 15, 25, 50]}"
      >
        <template v-slot:[`item.valor`]="{item}">
          <span style="white-space: nowrap">R$ {{ item.valor ? formatoMonetario(item.valor) : '0,00' }}</span>
        </template>
        <template v-slot:foot>
          <tfoot>
          <tr>
            <td colspan="2">
              <div class="subtitle-2 primary--text d-flex justify-space-between">
                <span>TOTAL</span>
                <span v-if="$vuetify.breakpoint.xsOnly">R$ {{ formatoMonetario(valorTotal) }}</span>
              </div>
            </td>
            <td v-if="!$vuetify.breakpoint.xsOnly">
              <p class="subtitle-2 primary--text mb-0">R$ {{ formatoMonetario(valorTotal) }}</p>
            </td>
          </tr>
          </tfoot>
        </template>
      </v-data-table>
    </v-card>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import moment from '@/plugins/moment';
import DatePickerBr from '@/components/DatePickerBr';
import StringHelper from '@/helper/StringHelper';
export default {
  name: 'ProdutosVendidos',
  components: {DatePickerBr, AsyncContainer},
  data: () => ({
    loading: true,
    iptDataInicio: moment().format('YYYY-MM-01'),
    iptDataFim: moment().format('YYYY-MM-DD'),
    tableLoading: false,
    tableHeaders: [
      {value: 'nome', text: 'PRODUTO'},
      {value: 'quantidade', text: 'QUANTIDADE'},
      {value: 'valor', text: 'ARRECADAÇÃO'},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
  }),
  computed: {
    valorTotal() {
      return this.tableItems.reduce((previousValue, currentValue) => previousValue + parseFloat(currentValue.valor), 0);
    },
  },
  methods: {
    imprimir() {
      setTimeout(() => window.print(), 500);
    },
    async loadData() {
      this.tableLoading = true;
      const webclient = http();
      const params = {data_inicio: this.iptDataInicio, data_fim: this.iptDataFim};
      const {data} = await webclient.get('relatorios/produtos_vendidos', {params});
      this.tableItems = data;
      this.tableLoading = false;
    },
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
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