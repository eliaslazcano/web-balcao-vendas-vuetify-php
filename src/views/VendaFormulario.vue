<template>
  <async-container :loading="loading">
    <v-card class="mb-4">
      <v-card-title>{{ id ? 'Venda Nº' + id : 'Registrar Venda' }}</v-card-title>
      <v-card-text>
        <v-text-field
          label="Cliente"
          v-model="iptCliente"
          :disabled="enviandoVenda"
          :readonly="!!cadastro || !!iptClienteId"
          placeholder="Nome do cliente"
          persistent-placeholder
          :append-outer-icon="cadastro ? undefined : 'mdi-account-search'"
          @click:append-outer="dialogPesquisarCadastro = true"
          @click:clear="desvincularCadastro"
          :clearable="!cadastro"
          outlined
          dense
        ></v-text-field>
        <v-text-field
          label="Valor Pago"
          v-model="iptCredito"
          :disabled="enviandoVenda"
          type="tel"
          prefix="R$"
          outlined
          dense
        ></v-text-field>
      </v-card-text>
    </v-card>
    <v-card>
      <v-card-title>Lista de Produtos</v-card-title>
      <v-card-text class="pb-0">
        <v-autocomplete
          label="Adicionar produto"
          v-model="iptProduto"
          :items="iptProdutoItems"
          item-value="id"
          item-text="nome"
          no-data-text="Nenhum produto corresponde"
          :disabled="enviandoVenda"
          :filter="iptProdutoFiltro"
          auto-select-first
        >
          <template v-slot:item="{item}">
            <v-list-item-content>
              <v-list-item-title>{{item.nome}} <span v-if="item.codigo" class="grey--text"> - Cod. {{item.codigo}}</span></v-list-item-title>
              <v-list-item-subtitle>R$ {{item.valor.toFixed(2)}}</v-list-item-subtitle>
            </v-list-item-content>
          </template>
        </v-autocomplete>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        disable-pagination
        hide-default-footer
        no-data-text="Nenhum produto adicionado"
        sort-by="produto_nome"
      >
        <template v-slot:[`item.quantidade`]="{item}">
          <v-edit-dialog :return-value.sync="item.quantidade">
            {{ item.quantidade }}
            <template v-slot:input>
              <v-text-field
                v-model="item.quantidade"
                single-line
                type="tel"
                :rules="[v => !!v || 'Insira a quantidade']"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:[`item.valor`]="{item}">
          <v-edit-dialog :return-value.sync="item.valor">
            R$ {{ item.valor ? parseFloat(item.valor).toFixed(2) : '0.00' }}
            <template v-slot:input>
              <v-text-field
                prefix="R$"
                v-model="item.valor"
                single-line
                type="tel"
                :rules="[v => !!v || 'Insira o valor']"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
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
    </v-card>
    <div class="text-center my-4">
      <v-btn small color="success" :disabled="tableItems.length === 0" :loading="enviandoVenda" @click="salvarVenda">
        <v-icon dense class="mr-1">mdi-content-save</v-icon> Salvar
      </v-btn>
    </div>
    <v-dialog v-model="dialogPesquisarCadastro" width="40rem">
      <v-card>
        <v-card-title>Pesquisar Cliente</v-card-title>
        <v-card-text>
          <v-text-field label="Pesquisar" prepend-inner-icon="mdi-magnify" v-model="tableCadastrosSearch" hide-details dense outlined></v-text-field>
        </v-card-text>
        <v-data-table
          :headers="tableCadastrosHeaders"
          :items="tableCadastrosItems"
          :search="tableCadastrosSearch"
          dense
          @click:row="selecionarCadastro"
        ></v-data-table>
      </v-card>
    </v-dialog>
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
    cadastro: null, //Cadastro vinculado a esta venda
    iptClienteId: null,
    iptCliente: '',
    iptCredito: '0',
    iptProduto: null,
    iptProdutoItems: [],
    tableHeaders: [
      {value: 'produto_nome', text: 'PRODUTO'},
      {value: 'quantidade', text: 'QUANTIDADE'},
      {value: 'valor', text: 'VALOR', width: '10rem'},
    ],
    tableItems: [],
    tableSearch: '',
    enviandoVenda: false,
    dialogPesquisarCadastro: false,
    tableCadastrosHeaders: [
      {value: 'id', text: 'COD.', width: '6rem'},
      {value: 'nome', text: 'NOME'},
    ],
    tableCadastrosItems: [],
    tableCadastrosSearch: '',
    tableCadastrosLoading: false,
  }),
  computed: {
    valorTotal() {
      return this.tableItems.reduce((previousValue, currentValue) => previousValue + parseFloat(currentValue.valor), 0);
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
          this.cadastro = data.cadastro;
          this.iptCliente = data.cliente;
          this.iptCredito = data.credito;
          this.tableItems = data.itens;
        } else {
          this.iptCliente = '';
          this.iptCredito = '0';
          this.tableItems = [];
        }
        this.iptProdutoItems = data;
      } catch (e) {
        await this.$router.push('/');
      } finally {
        this.loading = false;
      }
    },
    async loadCadastros() {
      this.tableCadastrosLoading = true;
      const webclient = http();
      const {data} = await webclient.get('clientes');
      this.tableCadastrosItems = data;
      this.tableCadastrosLoading = false;
    },
    async salvarVenda() {
      try {
        this.enviandoVenda = true;
        const webclient = http();
        const payload = {
          id: this.id,
          cliente: this.iptCliente,
          cadastro: this.cadastro ? this.cadastro : this.iptClienteId,
          credito: this.iptCredito,
          itens: this.tableItems
        };
        await webclient.post('vendas', payload);
        await this.$router.push('/vendas');
      } finally {
        this.enviandoVenda = false;
      }
    },
    iptProdutoFiltro(item, queryText, itemText) {
      if (item.codigo && item.codigo === queryText) return true;
      return itemText.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
    },
    selecionarCadastro(item) {
      this.iptClienteId = item.id;
      this.iptCliente = item.nome;
      this.dialogPesquisarCadastro = false;
    },
    desvincularCadastro() {
      this.iptClienteId = null;
      this.iptCliente = '';
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
          this.tableItems[pedidoItemIndex].quantidade = parseFloat(this.tableItems[pedidoItemIndex].quantidade) + 1;
          this.tableItems[pedidoItemIndex].valor = parseFloat(this.tableItems[pedidoItemIndex].valor) + produto.valor;
        }
        this.$nextTick(() => this.iptProduto = null);
      }
    },
    id() {
      this.loadData();
    },
    dialogPesquisarCadastro(v) {
      if (v && this.tableCadastrosItems.length === 0) this.loadCadastros();
    }
  }
}
</script>
