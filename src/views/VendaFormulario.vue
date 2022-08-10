<template>
  <async-container :loading="loading">
    <v-card>
      <v-card-title>Registrar Venda</v-card-title>
      <v-card-text>
        <v-row dense>
          <v-col cols="12" md="6">
            <v-text-field label="Cliente" v-model="iptCliente" :disabled="enviandoVenda" placeholder="Nome do cliente" persistent-placeholder></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Valor Pago" v-model="iptCredito" :disabled="enviandoVenda"></v-text-field>
          </v-col>
        </v-row>
        <v-autocomplete
          label="Adicionar produto"
          v-model="iptProduto"
          :items="iptProdutoItems"
          item-value="id"
          item-text="nome"
          no-data-text="Nenhum produto corresponde"
          :disabled="enviandoVenda"
        ></v-autocomplete>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        no-data-text="Nenhum produto adicionado"
      >
        <template v-slot:top>
          <div class="mx-4">
            <p class="subtitle">Lista de produtos</p>
          </div>
        </template>
        <template v-slot:[`item.valor`]="{item}">R$ {{ item.valor ? item.valor.toFixed(2) : '0.00' }}</template>
        <template v-slot:foot>
          <tfoot>
          <tr>
            <td>
              <p class="subtitle-2 primary--text mb-0">TOTAL</p>
            </td>
            <td></td>
            <td>
              <p class="subtitle-2 primary--text mb-0">R$ {{ valorTotal.toFixed(2) }}</p>
            </td>
          </tr>
          </tfoot>
        </template>
      </v-data-table>
      <v-card-actions class="justify-center">
        <v-btn small depressed color="success" :loading="enviandoVenda" @click="salvarVenda">
          <v-icon dense class="mr-1">mdi-content-save</v-icon> Salvar
        </v-btn>
      </v-card-actions>
    </v-card>
  </async-container>
</template>

<script>
import http from '@/plugins/axios';
import AsyncContainer from '@/components/AsyncContainer';
export default {
  name: 'VendaFormulario',
  props: {
    id: {type: [String, Number], default: null},
  },
  components: {AsyncContainer},
  data: () => ({
    loading: true,
    iptCliente: '',
    iptCredito: '0',
    iptProduto: null,
    iptProdutoItems: [],
    tableHeaders: [
      {value: 'produto_nome', text: 'PRODUTO'},
      {value: 'quantidade', text: 'QUANTIDADE'},
      {value: 'valor', text: 'VALOR'},
    ],
    tableItems: [],
    tableSearch: '',
    enviandoVenda: false,
  }),
  computed: {
    valorTotal() {
      return this.tableItems.reduce((previousValue, currentValue) => previousValue + currentValue.valor, 0);
    },
  },
  methods: {
    async loadData(spinner = true) {
      try {
        if (spinner) this.loading = true;
        const webclient = http();
        const {data} = await webclient.get('produtos');
        if (this.id) {
          const {data} = await webclient.get(`vendas?id=${this.id}`);
          this.iptCliente = data.cliente;
          this.iptCredito = data.credito;
          this.tableItems = data.itens;
        }
        this.iptProdutoItems = data;
      } catch (e) {
        await this.$router.push('/');
      } finally {
        this.loading = false;
      }
    },
    async salvarVenda() {
      try {
        this.enviandoVenda = true;
        const webclient = http();
        const {data} = await webclient.post('vendas', {id: this.id, cliente: this.iptCliente, credito: this.iptCredito, itens: this.tableItems});
        if (!this.id || this.id.toString() !== data.id.toString()) await this.$router.push('/venda/' + data.id);
        else await this.loadData(false);
      } finally {
        this.enviandoVenda = false;
      }
    },
  },
  created() {
    this.loadData();
  },
  watch: {
    iptProduto(v) {
      if (v) {
        const produto = this.iptProdutoItems.find(i => i.id === v);
        const pedidoItemIndex = this.tableItems.findIndex(i => i.produto === v);
        if (pedidoItemIndex === -1) {
          this.tableItems.push({
            'id': null, 'produto': produto.id, 'produto_nome': produto.nome, 'quantidade': 1, 'valor': produto.valor
          });
        } else {
          this.tableItems[pedidoItemIndex].quantidade += 1;
          this.tableItems[pedidoItemIndex].valor += produto.valor;
        }
        this.$nextTick(() => this.iptProduto = null);
      }
    },
    id(v) {
      if (v) this.loadData();
    },
  }
}
</script>
