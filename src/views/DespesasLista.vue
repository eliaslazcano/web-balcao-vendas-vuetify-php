<template>
  <async-container :loading="loading">
    <v-card width="62rem" class="mx-auto">
      <v-card-title class="justify-md-space-between">
        Despesas
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
        <v-text-field
          label="Pesquisar"
          prepend-inner-icon="mdi-magnify"
          v-model="tableSearch"
          hide-details
          :autofocus="$vuetify.breakpoint.mdAndUp"
        ></v-text-field>
      </v-card-text>
      <v-data-table
        :loading="tableLoading"
        :items="tableItems"
        :headers="tableHeaders"
        :dense="tableDense"
        :search="tableSearch"
        :footer-props="{'items-per-page-options': [10, 15, 25]}"
        :mobile-breakpoint="0"
        no-data-text="Nenhuma despesa encontrada"
        no-results-text="Nenhuma despesa encontrada"
        sort-by="id"
        sort-desc
      >
        <template v-slot:[`item.valor`]="{item}">
          <span style="white-space: nowrap">R$ {{ formatoMonetario(item.valor) }}</span>
        </template>
        <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-btn color="yellow darken-4" small icon @click="editarItem(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn color="red" small icon @click="deletarItem(item)" :disabled="deletandoItem">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
        <template v-slot:foot>
          <tfoot>
          <tr v-if="$vuetify.breakpoint.name === 'xs'">
            <td>
              <div class="justify-space-between d-flex">
                <span class="subtitle-2 primary--text">TOTAL</span>
                <span class="subtitle-2 primary--text">R$ {{ valorTotal.toFixed(2) }}</span>
              </div>
            </td>
          </tr>
          <tr v-else>
            <td>
              <p class="subtitle-2 primary--text mb-0">TOTAL</p>
            </td>
            <td></td>
            <td>
              <p class="subtitle-2 primary--text mb-0">R$ {{ valorTotal.toFixed(2) }}</p>
            </td>
            <td></td>
            <td></td>
          </tr>
          </tfoot>
        </template>
      </v-data-table>
    </v-card>
    <v-fab-transition>
      <v-btn color="success" fab fixed right bottom @click="criarItem" v-if="!dialogEditar">
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-fab-transition>
    <v-dialog v-model="dialogEditar" width="32rem">
      <v-card>
        <v-form ref="form-despesa" @submit.prevent="salvarItem" :disabled="salvandoItem">
          <v-card-title>{{ iptId ? 'Editar' : 'Criar' }} produto</v-card-title>
          <v-card-text>
            <v-text-field label="Descrição" v-model="iptDescricao" outlined dense :rules="[v => (!!v && !!v.trim()) || 'Coloque a descrição']"></v-text-field>
            <text-field-monetary label="Valor" v-model="iptValor" prefix="R$" outlined dense></text-field-monetary>
            <v-textarea label="Observações" v-model="iptObservacao" outlined dense></v-textarea>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed :disabled="salvandoItem" @click="dialogEditar = false">
              Fechar
            </v-btn>
            <v-btn color="primary" small depressed :loading="salvandoItem" type="submit">Confirmar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import moment from '@/plugins/moment';
import TextFieldMonetary from '@/components/TextFieldMonetary';
import StringHelper from '@/helper/StringHelper';
import DatePickerBr from '@/components/DatePickerBr';
export default {
  name: 'DespesasLista',
  components: {DatePickerBr, TextFieldMonetary, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    tableLoading: true,
    tableItems: [],
    tableHeaders: [
      {value: 'id', text: 'COD.'},
      {value: 'descricao', text: 'DESCRICAO'},
      {value: 'valor', text: 'VALOR'},
      {value: 'criado_em', text: 'DATA'},
      {value: 'acoes', text: 'AÇÕES', sortable: false, filterable: false},
    ],
    tableDense: false,
    tableSearch: '',
    dialogEditar: false,
    iptId: null,
    iptDescricao: '',
    iptValor: 0,
    iptObservacao: '',
    salvandoItem: false,
    deletandoItem: false,
    iptDataInicial: moment().format('YYYY-01-01'),
    iptDataFinal: moment().format('YYYY-MM-DD'),
    iptFiltrarData: true,
  }),
  methods: {
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
    async loadData() {
      this.tableLoading = true;
      const webclient = http();
      const params = {
        data_inicio: this.iptFiltrarData ? `${this.iptDataInicial} 00:00:00` : null,
        data_fim: this.iptFiltrarData ? `${this.iptDataFinal} 23:59:59` : null,
      };
      const {data} = await webclient.get('despesas', {params});
      this.tableItems = data;
      this.tableLoading = false;
    },
    criarItem() {
      this.iptId = null;
      this.iptDescricao = '';
      this.iptValor = 0;
      this.iptObservacao = '';
      this.dialogEditar = true;
    },
    editarItem(item) {
      this.iptId = item.id;
      this.iptDescricao = item.descricao;
      this.iptValor = item.valor;
      this.loadItem(item.id);
      this.dialogEditar = true;
    },
    async loadItem(id) {
      const webclient = http();
      const {data} = await webclient.get(`despesas?id=${id}`);
      this.iptObservacao = data.observacao;
    },
    async salvarItem() {
      try {
        this.salvandoItem = true;
        this.tableLoading = true;
        const webclient = http();
        await webclient.post('despesas', {id: this.iptId, descricao: this.iptDescricao.trim().toUpperCase(), valor: this.iptValor, observacao: this.iptObservacao});
        await this.loadData();
        this.dialogEditar = false;
        this.$store.commit('showSnackbar', {color: 'success', text: 'Despesa registrada'});
      } finally {
        this.salvandoItem = false;
      }
    },
    async deletarItem(item) {
      if (!confirm(`Apagar "${item.descricao}"?`)) return;
      try {
        this.deletandoItem = true;
        this.tableLoading = true;
        const webclient = http();
        await webclient.delete('despesas?id=' + item.id);
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Despesa apagada'});
      } finally {
        this.deletandoItem = false;
      }
    }
  },
  computed: {
    valorTotal() {
      return this.tableItems.reduce((previousValue, currentValue) => previousValue + parseFloat(currentValue.valor), 0);
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
