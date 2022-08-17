<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto mb-10">
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
          </v-list>
        </v-menu>
      </v-card-title>
      <v-card-text>
        <v-text-field label="Pesquisar" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details :autofocus="$vuetify.breakpoint.mdAndUp"></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        :dense="tableDense"
        :footer-props="{'items-per-page-options': [10, 15, 25]}"
        :mobile-breakpoint="0"
        no-data-text="Nenhum produto encontrado"
        no-results-text="Nenhum produto encontrado"
        sort-by="nome"
      >
        <template v-slot:[`item.valor`]="{item}">
          <span style="white-space: nowrap">R$ {{ formatoMonetario(item.valor) }}</span>
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
            <v-text-field label="Codigo" v-model="iptCodigo" outlined dense></v-text-field>
            <text-field-monetary label="Valor" v-model="iptValor" prefix="R$" outlined dense></text-field-monetary>
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
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import TextFieldMonetary from '@/components/TextFieldMonetary';
import StringHelper from '@/helper/StringHelper';
export default {
  name: 'ProdutosLista',
  components: {TextFieldMonetary, AsyncContainer},
  data: () => ({
    loading: true,
    tableHeaders: [
      {value: 'nome', text: 'NOME'},
      {value: 'valor', text: 'VALOR UN.'},
      {value: 'codigo', text: 'CODIGO'},
      {value: 'acoes', text: 'AÇÕES', width: '6rem', sortable: false, filterable: false},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
    iptId: null,
    iptCodigo: '',
    iptNome: '',
    iptValor: '',
    dialogEditarProduto: false,
    salvandoProduto: false,
    deletandoProduto: false,
  }),
  methods: {
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
    async loadData() {
      const webclient = http();
      const {data} = await webclient.get('produtos');
      this.tableItems = data;
      this.loading = false;
    },
    criarProduto() {
      this.iptId = null;
      this.iptCodigo = '';
      this.iptNome = '';
      this.iptValor = '';
      if (this.$refs['form-produto']) this.$refs['form-produto'].resetValidation();
      this.dialogEditarProduto = true;
    },
    editarProduto(item) {
      this.iptId = item.id;
      this.iptCodigo = item.codigo;
      this.iptNome = item.nome;
      this.iptValor = item.valor.toString();
      this.dialogEditarProduto = true;
    },
    async salvarProduto() {
      if (!this.$refs['form-produto'].validate()) return;
      try {
        this.salvandoProduto = true;
        const produto = {id: this.iptId, codigo: this.iptCodigo, nome: this.iptNome, valor: this.iptValor};
        const webclient = http();
        await webclient.post('produtos', produto);
        await this.loadData();
        this.dialogEditarProduto = false;
        this.$store.commit('showSnackbar', {color: 'success', text: 'Produto salvo'});
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
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Produto apagado'});
      } finally {
        this.deletandoProduto = false;
      }
    }
  },
  async created() {
    try {
      await this.loadData();
    } catch (e) {
      await this.$router.push('/');
    }
  },
}
</script>
