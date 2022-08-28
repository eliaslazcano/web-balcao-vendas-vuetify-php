<template>
  <async-container :loading="loading">
    <v-card width="72rem" class="mx-auto mb-12">
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
            <v-list-item @click="ocultarEncerradas = !ocultarEncerradas">
              <v-list-item-icon>
                <v-icon>{{ ocultarEncerradas ? 'mdi-lock-open-check' : 'mdi-lock-minus' }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ ocultarEncerradas ? 'Mostrar vendas encerradas' : 'Ocultar vendas encerradas' }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item @click="mostrarTotal = !mostrarTotal">
              <v-list-item-icon>
                <v-icon>{{ mostrarTotal ? 'mdi-currency-usd-off' : 'mdi-currency-usd' }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ mostrarTotal ? 'Ocultar total' : 'Mostrar total' }}</v-list-item-title>
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
            <v-list-item v-if="rfid_disponivel" @click="dialogRfidLista = true">
              <v-list-item-icon>
                <v-icon>mdi-contactless-payment-circle</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Dispositivos de aproximação em uso</v-list-item-title>
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
        <v-slide-y-transition>
          <v-row v-if="iptFiltrarData" class="mb-2">
            <v-col cols="12" md="6">
              <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataInicial" label="Data inicial" prepend-inner-icon="mdi-calendar-arrow-right" :disabled="!iptFiltrarData"></date-picker-br>
            </v-col>
            <v-col cols="12" md="6">
              <date-picker-br inline no-buttons outlined dense hide-details v-model="iptDataFinal" label="Data limite" prepend-inner-icon="mdi-calendar-arrow-left" :disabled="!iptFiltrarData"></date-picker-br>
            </v-col>
          </v-row>
        </v-slide-y-transition>
        <v-text-field
          label="Pesquisar"
          prepend-inner-icon="mdi-magnify"
          placeholder="Nome do cliente ou número da venda"
          v-model="tableSearch"
          :autofocus="$vuetify.breakpoint.mdAndUp"
          :hint="$vuetify.breakpoint.mdAndUp && rfid_disponivel ? 'Pressione F2 para buscar por aproximação' : undefined"
          @keydown="(x) => {if (rfid_disponivel && x.code === 'F2') dialogRfid = true}"
        >
          <template v-slot:append-outer>
            <v-btn color="primary" small @click="dialogRfid = true" v-if="rfid_disponivel">
              <v-icon>mdi-contactless-payment</v-icon>
            </v-btn>
          </template>
        </v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItemsFiltered"
        :search="tableSearch"
        :loading="tableLoading"
        :dense="tableDense"
        :footer-props="{'items-per-page-options': [10, 15, 25, 50]}"
        :mobile-breakpoint="0"
        no-data-text="Nenhuma venda encontrada"
        no-results-text="Nenhuma venda encontrada"
        sort-by="id"
        sort-desc
      >
        <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
        <template v-slot:[`item.cliente`]="{item}">
          <span v-if="item.cliente">{{ item.cliente }}</span>
          <span v-else class="grey--text">SEM NOME</span>
          <v-icon color="red" class="ml-1" small v-if="item.encerrado_em !== null">mdi-lock</v-icon>
        </template>
        <template v-slot:[`item.debito`]="{item}">
          <span v-if="!item.debito && !item.credito" class="grey--text">R$ {{ formatoMonetario(item.credito) }}</span>
          <span v-else-if="item.credito >= item.debito" class="success--text">R$ {{ formatoMonetario(item.credito) }}</span>
          <span v-else-if="item.credito > 0" class="warning--text">R$ {{ formatoMonetario(item.credito) }} / {{ formatoMonetario(item.debito) }}</span>
          <span v-else class="red--text">R$ {{ formatoMonetario(item.debito) }}</span>
        </template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-btn color="primary" small icon :to="'/venda/' + item.id">
            <v-icon>mdi-open-in-new</v-icon>
          </v-btn>
        </template>
        <template v-slot:foot>
          <tfoot v-if="mostrarTotal">
          <tr>
            <th colspan="5" class="text-center">
              <div class="d-flex align-center justify-center">
                <span class="success--text">Arrecadado R$ {{formatoMonetario(totalArrecadado)}}</span>
                <span class="mx-2">/</span>
                <span class="error--text">Pendente R$ {{formatoMonetario(totalPendente)}}</span>
                <v-btn icon x-small color="primary" @click="mostrarTotal = false" class="ml-2">
                  <v-icon>mdi-eye-off</v-icon>
                </v-btn>
              </div>
            </th>
          </tr>
          </tfoot>
        </template>
      </v-data-table>
    </v-card>
    <v-btn color="success" fab fixed right bottom to="/venda">
      <v-icon>mdi-plus</v-icon>
    </v-btn>
    <v-dialog v-model="dialogRfid" width="24rem" :persistent="buscandoRfid">
      <v-card>
        <v-form @submit.prevent="buscarRfid" :disabled="buscandoRfid">
          <v-card-text class="pb-0">
            <div class="text-center mb-3">
              <v-avatar color="primary" size="96">
                <v-icon size="64" color="white">mdi-contactless-payment</v-icon>
              </v-avatar>
            </div>
            <p class="text-center title">Consulta por aproximação</p>
            <v-text-field
              label="Identificador RFID"
              prepend-inner-icon="mdi-contactless-payment"
              placeholder="Encoste o dispositivo no sensor"
              hint="Aproxime!"
              v-model="iptRfid"
              outlined
              dense
              autofocus
            ></v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed @click="dialogRfid = false" :loading="buscandoRfid">Fechar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogRfidLista" width="56rem">
      <v-card>
        <v-card-title style="font-size: 1rem">Dispositivos de aproximação em uso</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="dialogRfidListaHeaders"
          :items="dialogRfidListaItems"
          :loading="dialogRfidListaLoading"
          no-data-text="Não há dispositivos vinculados a nenhuma venda"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.venda`]="{item}">
            <router-link :to="'/venda/' + item.venda">{{ item.venda }}</router-link>
          </template>
          <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-icon color="red" @click="desvincularRfid(item.rfid)" :disabled="dialogRfidListaLoading">mdi-delete-circle</v-icon>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="primary" outlined small @click="dialogRfidLista = false">Fechar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import StringHelper from '@/helper/StringHelper';
import DatePickerBr from '@/components/DatePickerBr';
import moment from '@/plugins/moment';
import {config} from '@/config';
export default {
  name: 'VendasLista',
  components: {DatePickerBr, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    tableLoading: true,
    tableHeaders: [
      {value: 'id', text: 'VENDA Nº'},
      {value: 'criado_em', text: 'DATA', width: '11rem', filterable: false},
      {value: 'cliente', text: 'CLIENTE'},
      {value: 'debito', text: 'VALOR', cellClass: 'text-no-wrap', filterable: false},
      {value: 'acoes', text: 'ABRIR', align: 'center', sortable: false, filterable: false},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
    iptDataInicial: moment().format('YYYY-01-01'),
    iptDataFinal: moment().format('YYYY-MM-DD'),
    iptFiltrarData: true,
    ocultarEncerradas: localStorage.getItem('VendasLista_ocultarEncerradas') === '1',
    rfid_disponivel: config.rfid,
    dialogRfid: false,
    buscandoRfid: false,
    iptRfid: '',
    dialogRfidLista: false,
    dialogRfidListaHeaders: [
      {value: 'venda', text: 'VENDA Nº'},
      {value: 'cliente', text: 'CLIENTE'},
      {value: 'rfid', text: 'DISPOSITIVO'},
      {value: 'descricao', text: 'DESCRIÇÃO'},
      {value: 'criado_em', text: 'VINCULADO EM', cellClass: 'text-no-wrap'},
      {value: 'acoes', text: 'DESVINCULAR', sortable: false, align: 'center'},
    ],
    dialogRfidListaItems: [],
    dialogRfidListaLoading: false,
    mostrarTotal: localStorage.getItem('VendasLista_mostrarTotal') === '1',
  }),
  computed: {
    tableItemsFiltered() {
      if (this.ocultarEncerradas) return this.tableItems.filter(i => !i.encerrado_em);
      else return this.tableItems;
    },
    totalArrecadado() {
      return this.tableItemsFiltered.reduce((previousValue, currentValue) => previousValue + currentValue.credito, 0);
    },
    totalPendente() {
      return this.tableItemsFiltered.reduce(
        (previousValue, currentValue) => previousValue + (currentValue.credito >= currentValue.debito ? 0 : (currentValue.debito - currentValue.credito))
        , 0);
    },
  },
  methods: {
    imprimir() {
      setTimeout(() => window.print(), 500);
    },
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
    async buscarRfid() {
      if (!this.rfid_disponivel || !this.iptRfid) return;
      this.buscandoRfid = true;
      try {
        const webclient = http();
        const {data} = await webclient.get(`venda_rfids?rfid=${this.iptRfid}`);
        if (data && data.venda) {
          await this.$router.push('/venda/' + data.venda);
        }
        else this.$store.commit('showSnackbar', {color: 'error', text: 'Nenhuma venda vinculada ao dispositivo!'});
      } finally {
        this.dialogRfid = false;
        this.buscandoRfid = false;
      }
    },
    async buscarTodosRfids() {
      if (!this.rfid_disponivel) return;
      this.dialogRfidListaLoading = true;
      try {
        const webclient = http();
        const {data} = await webclient.patch('venda_rfids');
        this.dialogRfidListaItems = data;
      } finally {
        this.dialogRfidListaLoading = false;
      }
    },
    async desvincularRfid(rfid) {
      if (!confirm('Deseja desvincular esse dispositivo?')) return;
      this.dialogRfidListaLoading = true;
      try {
        const webclient = http();
        await webclient.delete(`venda_rfids?rfid=${rfid}`);
        await this.buscarTodosRfids();
      } finally {
        this.dialogRfidListaLoading = false;
      }
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
    },
    dialogRfid(v) {
      if (!v) this.iptRfid = '';
    },
    dialogRfidLista(v) {
      if (v) this.buscarTodosRfids();
    },
    mostrarTotal(v) {
      localStorage.setItem('VendasLista_mostrarTotal', (v ? '1' : '0'));
    },
    ocultarEncerradas(v) {
      localStorage.setItem('VendasLista_ocultarEncerradas', (v ? '1' : '0'));
    },
  },
}
</script>
