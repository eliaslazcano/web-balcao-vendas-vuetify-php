<template>
  <async-container :loading="loading">
    <div style="max-width: 32rem" class="mx-auto">
      <p class="title">Fluxo de Caixa</p>
      <v-card width="32rem" class="mx-auto" :loading="loadingData">
        <v-card-title>Período</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6">
              <date-picker-br
                inline no-buttons outlined dense hide-details v-model="iptDataInicial" label="Data inicial"
                prepend-inner-icon="mdi-calendar-arrow-right" :disabled="loadingData"
              ></date-picker-br>
            </v-col>
            <v-col cols="12" sm="6">
              <date-picker-br
                inline no-buttons outlined dense hide-details v-model="iptDataFinal" label="Data limite"
                prepend-inner-icon="mdi-calendar-arrow-left" :disabled="loadingData"
              ></date-picker-br>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="justify-center pt-0" @click="loadData">
          <v-btn outlined small color="primary">
            <v-icon small class="mr-1">mdi-refresh</v-icon>
            Recalcular
          </v-btn>
        </v-card-actions>
      </v-card>
      <v-card :color="saldo > 0 ? 'success' : 'error'" dark class="my-5">
        <v-card-text class="d-flex py-3">
          <v-icon size="3rem" class="mr-2">{{ saldo > 0 ? 'mdi-plus-thick' : 'mdi-minus-thick' }}</v-icon>
          <div>
            <p class="subtitle-2 mb-2">Saldo teórico</p>
            <p class="mb-0" style="font-size: 2rem">R$ {{ formatoMonetario(saldo) }}</p>
          </div>
        </v-card-text>
      </v-card>
      <v-card class="my-3">
        <v-list outlined dense rounded class="my-5">
          <v-list-item>
            <v-list-item-avatar>
              <v-avatar color="success">
                <v-icon color="white">mdi-arrow-up-bold</v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title>{{ vendas.vendas }} Vendas arrecadadas</v-list-item-title>
              <v-list-item-subtitle>R$ {{ formatoMonetario(vendas.credito) }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item>
            <v-list-item-avatar>
              <v-avatar color="error">
                <v-icon color="white">mdi-arrow-down-bold</v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title> {{ despesas.despesas }} Despesas</v-list-item-title>
              <v-list-item-subtitle>R$ {{ formatoMonetario(despesas.debito) }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-card>
      <v-card class="my-3" v-if="!!credito || !!debito">
        <v-card-text>
          <p class="title">Comparativo</p>
          <apex-chart type="donut" :options="chartOptions" :series="series"></apex-chart>
        </v-card-text>
      </v-card>
    </div>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import DatePickerBr from '@/components/DatePickerBr';
import moment from '@/plugins/moment';
import http from '@/plugins/axios';
import StringHelper from '@/helper/StringHelper';
export default {
  name: 'FluxoCaixa',
  components: {DatePickerBr, AsyncContainer},
  data: () => ({
    loading: true,
    loadingData: true,
    iptDataInicial: moment().format('YYYY-01-01'),
    iptDataFinal: moment().format('YYYY-MM-DD'),
    credito: 0,
    debito: 0,
    vendas: 0,
    despesas: 0,
    options: {},
    series: [],
    chartOptions: {
      labels: ['Arrecadação','Despesa'],
      fill: {
        type: 'gradient',
      },
      colors: ['#4caf50', '#ff5252'],
      tooltip: {
        y: {
          formatter: (val) => 'R$ ' + Number(val).toLocaleString('pt-BR', {maximumFractionDigits: 2, minimumFractionDigits: 2})
        }
      }
    },
  }),
  computed: {
    saldo() {
      return this.credito - this.debito;
    },
  },
  methods: {
    async loadData() {
      this.loadingData = true;
      try {
        const webclient = http();
        const params = {'data_inicio': this.iptDataInicial, 'data_fim': this.iptDataFinal}
        const {data} = await webclient.get('relatorios/fluxo_caixa', {params});
        this.series = [data.credito, data.debito];
        this.credito = data.credito;
        this.debito = data.debito;
        this.vendas = data.vendas;
        this.despesas = data.despesas;
      } catch (e) {
        return false;
      } finally {
        this.loadingData = false;
        this.loading = false;
      }
      return true;
    },
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
  },
  created() {
    const x = this.loadData();
    if (!x) this.$router.push('/');
  }
}
</script>
