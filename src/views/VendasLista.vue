<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto">
      <v-card-title class="justify-space-between">
        Vendas
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
            <v-list-item @click="iptFiltrarData = !iptFiltrarData">
              <v-list-item-icon>
                <v-icon>{{ iptFiltrarData ? 'mdi-calendar-remove' : 'mdi-calendar-check' }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ iptFiltrarData ? 'Não filtrar a data' : 'Filtrar a data' }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-card-title>
      <v-card-text>
        <v-slide-y-transition>
          <v-row v-if="iptFiltrarData">
            <v-col cols="12" md="6">
              <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataInicial" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-right" :disabled="!iptFiltrarData"></date-picker-br>
            </v-col>
            <v-col cols="12" md="6">
              <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataFinal" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-left" :disabled="!iptFiltrarData"></date-picker-br>
            </v-col>
          </v-row>
        </v-slide-y-transition>
        <v-text-field label="Pesquisar" class="mt-3" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details autofocus></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        :loading="tableLoading"
        :dense="tableDense"
        :footer-props="{'items-per-page-options': [10, 15, 25]}"
        :items-per-page="15"
        no-data-text="Nenhuma venda encontrada"
        no-results-text="Nenhuma venda encontrada"
        sort-by="id"
        sort-desc
      >
        <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
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
    moment,
    loading: true,
    tableLoading: true,
    tableHeaders: [
      {value: 'id', text: 'Nº', width: '7rem'},
      {value: 'criado_em', text: 'DATA', width: '12rem'},
      {value: 'cliente', text: 'CLIENTE'},
      {value: 'debito', text: 'VALOR', filterable: false},
      {value: 'acoes', text: 'ABRIR', width: '6rem', sortable: false, filterable: false},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: true,
    iptDataInicial: moment().format('YYYY-01-01'),
    iptDataFinal: moment().format('YYYY-MM-DD'),
    iptFiltrarData: true,
  }),
  methods: {
    async loadData() {
      this.tableLoading = true;
      const webclient = http();
      const params = {
        data_inicio: this.iptFiltrarData ? `${this.iptDataInicial} 00:00:00` : null,
        data_fim: this.iptFiltrarData ? `${this.iptDataFinal} 23:59:59` : null,
      };
      const {data} = await webclient.get('vendas', {params});
      this.tableItems = data;
      this.tableLoading = false;
      this.loading = false;
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
    }
  },
  watch: {
    iptDataInicial(v) {
      if (v) this.loadData();
    },
    iptDataFinal(v) {
      if (v) this.loadData();
    },
    iptFiltrarData() {
      this.loadData();
    }
  },
}
</script>
