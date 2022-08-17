<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto mb-4">
      <v-card-title>{{ id ? 'Venda Nº' + id : 'Registrar Venda' }}</v-card-title>
      <v-card-subtitle v-if="!!criado_em">Criado em {{ moment(criado_em).format('DD/MM/YYYY HH:mm') }}</v-card-subtitle>
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
          @keydown="(x) => {if (x.code === 'F2' && !cadastro) dialogPesquisarCadastro = true}"
          @click:clear="desvincularCadastro"
          :clearable="!cadastro"
          :autofocus="$vuetify.breakpoint.mdAndUp"
          outlined
          dense
        ></v-text-field>
        <v-text-field label="Nota" v-model="iptNota" outlined dense placeholder="Observações.."></v-text-field>
        <text-field-monetary
          label="Valor Pago"
          v-model="iptCredito"
          :disabled="enviandoVenda"
          type="tel"
          prefix="R$"
          outlined
          dense
        ></text-field-monetary>
      </v-card-text>
    </v-card>
    <v-card width="64rem" class="mx-auto">
      <v-card-title>Lista de Produtos</v-card-title>
      <v-card-text class="pb-0">
        <v-autocomplete
          label="Adicionar produto"
          v-model="iptProduto"
          :items="iptProdutoItems"
          item-value="id"
          item-text="nome"
          no-data-text="Nenhum produto correspondente"
          prepend-inner-icon="mdi-plus-circle"
          :disabled="enviandoVenda"
          :filter="iptProdutoFiltro"
          auto-select-first
          hide-details
          outlined
          dense
        >
          <template v-slot:item="{item}">
            <v-list-item-content>
              <v-list-item-title>{{item.nome}} <span v-if="item.codigo" class="grey--text"> - Cod. {{item.codigo}}</span></v-list-item-title>
              <v-list-item-subtitle>R$ {{item.valor.toFixed(2)}}</v-list-item-subtitle>
            </v-list-item-content>
          </template>
        </v-autocomplete>
      </v-card-text>
      <v-divider class="mt-3"></v-divider>
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
            <span style="white-space: nowrap">R$ {{ item.valor ? parseFloat(item.valor).toFixed(2) : '0.00' }}</span>
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
        <template v-slot:[`item.acoes`]="{item}">
          <v-icon color="red" @click="removerItem(item)">mdi-close-circle</v-icon>
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
            <td></td>
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
          <v-text-field
            label="Pesquisar"
            prepend-inner-icon="mdi-magnify"
            v-model="tableCadastrosSearch"
            @keydown="(x) => {if (x.code === 'F2') pesquisarCadastroPorDigital()}"
            :autofocus="$vuetify.breakpoint.mdAndUp"
            hide-details dense outlined
          >
            <template v-slot:append-outer>
              <v-btn color="primary" small @click="pesquisarCadastroPorDigital" v-if="existeCadastroComDigital && biometriaDisponivel">
                <v-icon>mdi-fingerprint</v-icon>
              </v-btn>
            </template>
          </v-text-field>
        </v-card-text>
        <v-data-table
          :headers="tableCadastrosHeaders"
          :items="tableCadastrosItems"
          :search="tableCadastrosSearch"
          :loading="tableCadastrosLoading"
          :footer-props="{'items-per-page-options': [5, 10, 15]}"
          sort-by="id"
          sort-desc
          dense
          @click:row="selecionarCadastro"
        ></v-data-table>
      </v-card>
    </v-dialog>
    <loading-dialog v-model="dialogLoading" :text="dialogLoadingText" width="400"></loading-dialog>
  </async-container>
</template>

<script>
import http from '@/plugins/axios';
import moment from '@/plugins/moment';
import AsyncContainer from '@/components/AsyncContainer';
import BiometriaNitgen from '@/http/BiometriaNitgen';
import TextFieldMonetary from '@/components/TextFieldMonetary';
import LoadingDialog from '@/components/LoadingDialog';
export default {
  name: 'VendaFormulario',
  props: {
    id: {type: [String, Number], default: null},
  },
  components: {LoadingDialog, TextFieldMonetary, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    cadastro: null, //Cadastro vinculado a esta venda
    criado_em: null,
    iptClienteId: null,
    iptCliente: '',
    iptNota: '',
    iptCredito: '0',
    iptProduto: null,
    iptProdutoItems: [],
    tableHeaders: [
      {value: 'produto_nome', text: 'PRODUTO'},
      {value: 'quantidade', text: 'QUANTIDADE'},
      {value: 'valor', text: 'VALOR', width: '10rem'},
      {value: 'acoes', text: 'REMOVER', sortable: false, filterable: false, align: 'center'},
    ],
    tableItems: [],
    tableSearch: '',
    enviandoVenda: false,
    dialogPesquisarCadastro: false,
    biometriaDisponivel: false,
    tableCadastrosHeaders: [
      {value: 'id', text: 'COD.', width: '6rem'},
      {value: 'nome', text: 'NOME'},
    ],
    tableCadastrosItems: [],
    tableCadastrosSearch: '',
    tableCadastrosLoading: false,
    dialogLoading: false,
    dialogLoadingText: '',
    itemsRemover: [],
  }),
  computed: {
    valorTotal() {
      return this.tableItems.reduce((previousValue, currentValue) => previousValue + parseFloat(currentValue.valor), 0);
    },
    existeCadastroComDigital() {
      return this.tableCadastrosItems.findIndex(i => !!i.digital) !== -1;
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
          this.criado_em = data.criado_em;
          this.iptCliente = data.cliente;
          this.iptNota = data.nota;
          this.iptCredito = data.credito;
          this.tableItems = data.itens;
        } else {
          this.cadastro = null;
          this.criado_em = null;
          this.iptCliente = '';
          this.iptNota = '';
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
      this.biometriaDisponivel = data.biometria_nitgen;
      this.tableCadastrosItems = data.dados;
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
          nota: this.iptNota,
          credito: this.iptCredito,
          itens: this.tableItems,
          remover: this.itemsRemover
        };
        await webclient.post('vendas', payload);

        if (this.id) this.$store.commit('showSnackbar', {color: 'success', text: 'Venda atualizada'});
        else this.$store.commit('showSnackbar', {color: 'success', text: 'Venda registrada'});

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
    async pesquisarCadastroPorDigital() {
      if (!this.existeCadastroComDigital || !this.biometriaDisponivel) return;
      const biometriaNitgen = new BiometriaNitgen();
      try {
        const cadastros = this.tableCadastrosItems.filter(i => !!i.digital).map(i => ({id: i.id, digital: i.digital}));
        this.dialogLoading = true;
        this.dialogLoadingText = 'Enviando todas as biometrias para a memória..';
        const resultado = await biometriaNitgen.identificar(cadastros);
        if (resultado === 0) alert('Nenhum cadastro pôde ser encontrado com esta digital escaneada.');
        else if (resultado > 0) {
          const cadastro = this.tableCadastrosItems.find(i => i.id === resultado);
          if (cadastro) this.selecionarCadastro(cadastro);
        }
      } catch (e) {
        alert('Parece que não foi possível coletar a digital');
      } finally {
        this.dialogLoading = false;
      }
    },
    async removerItem(item) {
      if (!confirm(`Remover "${item.produto_nome}"?`)) return;
      if (item.id) {
        const index = this.tableItems.findIndex(i => i.id === item.id);
        this.tableItems.splice(index, 1);
        this.itemsRemover.push(item.id);
      } else {
        const index = this.tableItems.findIndex(i => i.produto === item.produto);
        this.tableItems.splice(index, 1);
      }
      console.log(item);
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
