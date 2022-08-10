<template>
  <async-container :loading="loading">
    <v-card>
      <v-card-title>Produtos</v-card-title>
      <v-card-text>
        <v-text-field label="Pesquisar" prepend-inner-icon="mdi-magnify" v-model="tableSearch" hide-details></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        sort-by="nome"
      >
        <template v-slot:[`item.valor`]="{item}">R$ {{ item.valor ? item.valor.toFixed(2) : '0.00' }}</template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-btn color="yellow darken-4" small icon @click="editarProduto(item)">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-btn color="red" small icon @click="deletarProduto(item)" :disabled="deletandoProduto">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table>
      <v-card-actions class="justify-center">
        <v-btn color="primary" small depressed @click="criarProduto">
          <v-icon>mdi-plus</v-icon> Cadastrar produto
        </v-btn>
      </v-card-actions>
    </v-card>
    <v-dialog v-model="dialogEditarProduto" width="32rem">
      <v-card>
        <v-form ref="form-produto" @submit.prevent="salvarProduto" :disabled="salvandoProduto">
          <v-card-title>Editar produto</v-card-title>
          <v-card-text>
            <v-text-field label="Nome" v-model="iptNome" outlined dense :rules="[v => (!!v && !!v.trim()) || 'Coloque o nome']"></v-text-field>
            <v-text-field label="Codigo" v-model="iptCodigo" outlined dense></v-text-field>
            <v-text-field label="Valor" v-model="iptValor" outlined dense type="tel"></v-text-field>
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
export default {
  name: 'ProdutosLista',
  components: {AsyncContainer},
  data: () => ({
    loading: true,
    tableHeaders: [
      {value: 'nome', text: 'NOME'},
      {value: 'valor', text: 'VALOR UN.'},
      {value: 'acoes', text: 'AÇÕES', width: '6rem'},
    ],
    tableItems: [],
    tableSearch: '',
    iptId: null,
    iptCodigo: '',
    iptNome: '',
    iptValor: '',
    dialogEditarProduto: false,
    salvandoProduto: false,
    deletandoProduto: false,
  }),
  methods: {
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
