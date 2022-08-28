<template>
  <async-container :loading="loading">
    <v-card width="72rem" class="mx-auto mb-12">
      <v-card-title class="justify-space-between">
        Produtos
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
            <v-list-item @click="visualizarCategorias">
              <v-list-item-icon>
                <v-icon>mdi-folder-edit</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Gerenciar categorias</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item @click="ocultarCategorias = !ocultarCategorias">
              <v-list-item-icon>
                <v-icon>{{ ocultarCategorias ? 'mdi-folder-eye' : 'mdi-folder-remove'}}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ ocultarCategorias ? 'Exibir' : 'Ocultar' }} categorias</v-list-item-title>
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
        <v-select
          v-if="iptCategoriaItems.length > 0 && !ocultarCategorias"
          label="Mostrar produtos da categoria:"
          v-model="iptCategoriaFiltro"
          :items="[{id: null, nome: 'TODAS AS CATEGORIAS'}, ...iptCategoriaItems]"
          item-text="nome"
          item-value="id"
          :clearable="!!iptCategoriaFiltro"
          outlined
          dense
        ></v-select>
        <v-text-field
          label="Pesquisar"
          prepend-inner-icon="mdi-magnify"
          placeholder="Nome do produto ou código"
          v-model="tableSearch"
          hide-details
          :autofocus="$vuetify.breakpoint.mdAndUp"
          outlined
          dense
        ></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItemsFiltered"
        :search="tableSearch"
        :dense="tableDense"
        :footer-props="{'items-per-page-options': [10, 15, 25, 50]}"
        :mobile-breakpoint="0"
        no-data-text="Nenhum produto cadastrado"
        no-results-text="Nenhum produto encontrado"
        sort-by="nome"
      >
        <template v-slot:[`item.categoria`]="{item}">
          <span v-if="item.categoria">{{ iptCategoriaItems.find(i => i.id === item.categoria).nome }}</span>
          <small v-else class="grey--text">NÃO POSSUI</small>
        </template>
        <template v-slot:[`item.valor`]="{item}">
          <span style="white-space: nowrap">R$ {{ formatoMonetario(item.valor) }}</span>
        </template>
        <template v-slot:[`item.codigo`]="{item}">
          <span v-if="item.codigo">{{ item.codigo }}</span>
          <small v-else class="grey--text">NÃO POSSUI</small>
        </template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-btn color="yellow darken-4" small icon @click="editarProduto(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn color="red" small icon @click="deletarProduto(item)" :disabled="deletandoProduto">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>
    <v-fab-transition>
      <v-btn color="success" fab fixed right bottom @click="criarProduto" v-if="!dialogEditarProduto">
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-fab-transition>
    <v-dialog v-model="dialogEditarProduto" width="32rem">
      <v-card>
        <v-form ref="form-produto" @submit.prevent="salvarProduto" :disabled="salvandoProduto">
          <v-card-title>{{ iptId ? 'Editar' : 'Criar' }} produto</v-card-title>
          <v-card-text>
            <v-text-field label="Nome" v-model="iptNome" outlined dense :rules="[v => (!!v && !!v.trim()) || 'Coloque o nome']"></v-text-field>
            <v-text-field label="Codigo" class="mt-2" v-model="iptCodigo" outlined dense hint="O código é opcional, mas facilita adicionar o produto numa venda." persistent-hint></v-text-field>
            <v-select
              label="Categoria"
              class="mt-2"
              v-model="iptCategoria"
              :items="iptCategoriaItems"
              item-text="nome"
              item-value="id"
              placeholder="Nenhuma"
              persistent-placeholder
              no-data-text="Nenhuma categoria foi cadastrada"
              clearable
              outlined
              dense
            ></v-select>
            <text-field-monetary label="Valor" class="mt-2" v-model="iptValor" prefix="R$" outlined dense></text-field-monetary>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed :disabled="salvandoProduto" @click="dialogEditarProduto = false">
              Fechar
            </v-btn>
            <v-btn color="primary" small depressed :loading="salvandoProduto" type="submit">Confirmar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogCategorias" width="40rem">
      <v-card>
        <v-card-title>Categorias</v-card-title>
        <v-card-subtitle>As categorias ajudam organizar os produtos em subdivisões</v-card-subtitle>
        <v-divider></v-divider>
        <v-data-table
          :headers="tableCategoriasHeaders"
          :items="iptCategoriaItems"
          :loading="tableCategoriasLoading"
          no-data-text="Não existe categoria, cadastre a primeira.."
          sort-by="nome"
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <small v-else class="grey--text">NÃO POSSUI</small>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn color="orange" icon small @click="cadastrarCategoria(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn color="red" icon small @click="excluirCategoria(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="primary" small outlined @click="dialogCategorias = false">Fechar</v-btn>
          <v-btn color="primary" small depressed @click="cadastrarCategoria(null)">
            <v-icon small class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import TextFieldMonetary from '@/components/TextFieldMonetary';
import StringHelper from '@/helper/StringHelper';
import moment from '@/plugins/moment';
export default {
  name: 'ProdutosLista',
  components: {TextFieldMonetary, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    ocultarCategorias: localStorage.getItem('ProdutosLista_ocultarCategorias') === '1',
    iptCategoriaFiltro: null,
    iptCategoriaItems: [],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
    iptId: null,
    iptCodigo: '',
    iptNome: '',
    iptCategoria: null,
    iptValor: '',
    dialogEditarProduto: false,
    salvandoProduto: false,
    deletandoProduto: false,
    dialogCategorias: false,
    tableCategoriasHeaders: [],
    tableCategoriasLoading: false,
  }),
  computed: {
    tableHeaders() {
      const headers = [
        {value: 'nome', text: 'NOME', cellClass: 'text-no-wrap'},
        {value: 'categoria', text: 'CATEGORIA', cellClass: 'text-no-wrap'},
        {value: 'valor', text: 'VALOR UN.', filterable: false},
        {value: 'codigo', text: 'CODIGO'},
        {value: 'acoes', text: 'AÇÕES', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
      ];
      if (this.ocultarCategorias) return headers.filter(i => i.value !== 'categoria');
      else return headers;
    },
    tableItemsFiltered() {
      return this.tableItems.filter(i => this.iptCategoriaFiltro === null || i.categoria === this.iptCategoriaFiltro);
    },
  },
  methods: {
    imprimir() {
      setTimeout(() => window.print(), 500);
    },
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
    async loadData() {
      const webclient = http();
      const {data} = await webclient.get('produtos');
      this.tableItems = data.produtos;
      this.iptCategoriaItems = data.categorias;
      this.loading = false;
    },
    criarProduto() {
      this.iptId = null;
      this.iptCodigo = '';
      this.iptNome = '';
      this.iptCategoria = null;
      this.iptValor = '';
      if (this.$refs['form-produto']) this.$refs['form-produto'].resetValidation();
      this.dialogEditarProduto = true;
    },
    editarProduto(item) {
      this.iptId = item.id;
      this.iptCodigo = item.codigo;
      this.iptNome = item.nome;
      this.iptCategoria = item.categoria;
      this.iptValor = item.valor.toString();
      this.dialogEditarProduto = true;
    },
    async salvarProduto() {
      if (!this.$refs['form-produto'].validate()) return;
      try {
        this.salvandoProduto = true;
        const produto = {id: this.iptId, codigo: this.iptCodigo, nome: this.iptNome, valor: this.iptValor, categoria: this.iptCategoria};
        const webclient = http();
        await webclient.post('produtos', produto);
        await this.loadData();
        this.dialogEditarProduto = false;
        this.$store.commit('showSnackbar', {color: 'success', text: 'Produto salvo!'});
      } finally {
        this.salvandoProduto = false;
      }
    },
    async deletarProduto(item) {
      if (!confirm(`Tem certeza que vai apagar '${item.nome}'?`)) return;
      try {
        this.deletandoProduto = true;
        const webclient = http();
        await webclient.delete('produtos?id=' + item.id);
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Produto apagado!'});
      } finally {
        this.deletandoProduto = false;
      }
    },
    async visualizarCategorias() {
      this.tableCategoriasHeaders = [
        {value: 'id', text: 'COD.'},
        {value: 'nome', text: 'NOME', cellClass: 'text-no-wrap'},
        {value: 'criado_em', text: 'CRIADA EM', cellClass: 'text-no-wrap', filterable: false},
        {value: 'acoes', text: 'AÇÕES', cellClass: 'text-no-wrap', align: 'center', sortable: false, filterable: false},
      ];
      this.tableCategoriasLoading = true;
      this.dialogCategorias = true;
      try {
        const webclient = http();
        const {data} = await webclient.get('produtos/categorias');
        this.iptCategoriaItems = data;
      } catch (e) {
        this.dialogCategorias = false;
      } finally {
        this.tableCategoriasLoading = false;
      }
    },
    async cadastrarCategoria(item = null) {
      const nome = prompt(
        !item ? 'Dê um nome para a categoria, digite.' : 'Digite o novo nome desejado para a categoria',
        !item ? '' : item.nome
      );
      if (!nome || nome.trim().length === 0) return;
      this.tableCategoriasLoading = true;
      try {
        const webclient = http();
        await webclient.post('produtos/categorias', {'nome': nome.trim().toUpperCase(), 'id': item ? item.id : null})
        const {data} = await webclient.get('produtos/categorias');
        this.iptCategoriaItems = data;
      } finally {
        this.tableCategoriasLoading = false;
      }
    },
    async excluirCategoria(categoria) {
      if (!confirm(`Apagar a categoria "${categoria.nome}"?`)) return;
      this.tableCategoriasLoading = true;
      try {
        const webclient = http();
        await webclient.delete(`produtos/categorias?id=${categoria.id}`);
        const {data} = await webclient.get('produtos/categorias');
        this.iptCategoriaItems = data;
      } finally {
        this.tableCategoriasLoading = false;
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
    ocultarCategorias(v) {
      localStorage.setItem('ProdutosLista_ocultarCategorias', (v ? '1' : '0'));
      if (v) this.iptCategoriaFiltro = null;
    },
  }
}
</script>
