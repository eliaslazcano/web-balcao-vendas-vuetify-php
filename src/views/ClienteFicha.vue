<template>
  <async-container :loading="loading">
    <div style="width: 64rem; max-width: 100%" class="mx-auto">
      <v-card class="mb-5" :loading="loadingBasico">
        <v-card-title class="justify-space-between">
          Dados pessoais
          <v-menu left bottom offset-y class="d-print-none">
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list class="py-0" dense>
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
        <v-form ref="form-dadospessoais" :disabled="loadingBasico" @submit.prevent="salvarDadosPessoais">
          <v-card-text>
            <v-text-field
              outlined
              dense
              label="Nome"
              v-model="iptNome"
            ></v-text-field>
            <v-select
              label="Categoria"
              v-model="iptCategoria"
              :items="iptCategoriaItems"
              item-text="nome"
              item-value="id"
              placeholder="Nenhuma"
              persistent-placeholder
              outlined
              dense
            ></v-select>
            <v-text-field
              v-if="biometriaDisponivel"
              label="Digital"
              :value="iptDigital ? 'Digital coletada!' : ''"
              :success="!!iptDigital"
              placeholder="Não possui"
              persistent-placeholder
              hide-details
              :append-icon="iptDigital ? 'mdi-close' : undefined"
              @click:append="iptDigital = null"
              :append-outer-icon="biometriaDisponivel && $vuetify.breakpoint.mdAndUp ? 'mdi-fingerprint' : undefined"
              @click:append-outer="coletarDigital"
              readonly
              outlined
              dense
            >
            </v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center d-print-none">
            <v-btn color="primary" depressed small type="submit" :disabled="loadingBasico">
              <v-icon small class="mr-1">mdi-micro-sd</v-icon> Salvar
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
      <v-card class="mb-5">
        <v-card-title>Telefones</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="tbTelefonesHeaders"
          :items="tbTelefonesItems"
          :loading="tbTelefonesLoading"
          no-data-text="Nenhum telefone cadastrado"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.tipo`]="{item}">
            <v-icon v-if="item.tipo === 1">mdi-phone</v-icon>
            <v-icon v-else-if="item.tipo === 2">mdi-cellphone</v-icon>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn icon small color="red" @click="excluirTelefone(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
      <v-card class="mb-5">
        <v-card-title>E-mails</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="tbEmailsHeaders"
          :items="tbEmailsItems"
          :loading="tbEmailsLoading"
          no-data-text="Nenhum e-mail cadastrado"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn icon small color="red" @click="excluirEmail(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
      <v-card class="mb-5">
        <v-card-title>Endereços</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="tbEnderecosHeaders"
          :items="tbEnderecosItems"
          :loading="tbEnderecosLoading"
          no-data-text="Nenhum endereço cadastrado"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn icon small color="red" @click="excluirEndereco(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
      <v-card class="mb-5">
        <v-card-title>Observações</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="tbObsHeaders"
          :items="tbObsItems"
          :loading="tbObsLoading"
          no-data-text="Nenhuma observação anotada"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn icon small color="red" @click="excluirObservacao(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
    </div>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import moment from '@/plugins/moment';
import {config} from '@/config';
import BiometriaNitgen from '@/http/BiometriaNitgen';
export default {
  name: 'ClienteFicha',
  components: {AsyncContainer},
  props: {
    'id': { type: [Number, String], required: true },
  },
  data: () => ({
    moment,
    biometriaDisponivel: config.biometria,
    webclient: http(),
    loading: true,
    loadingBasico: false,
    iptNome: '',
    iptCategoria: null,
    iptCategoriaItems: [],
    iptDigital: null,

    tbTelefonesHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'tipo', text: 'TIPO', width: '5rem'},
      {value: 'numero', text: 'NÚMERO', cellClass: 'text-no-wrap', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', cellClass: 'text-no-wrap', sortable: false, align: 'center'},
    ],
    tbTelefonesItems: [],
    tbTelefonesLoading: false,

    tbEmailsHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'email', text: 'E-MAIL', cellClass: 'text-no-wrap', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', cellClass: 'text-no-wrap', sortable: false, align: 'center'},
    ],
    tbEmailsItems: [],
    tbEmailsLoading: false,

    tbEnderecosHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'cep', text: 'CEP', sortable: false},
      {value: 'uf', text: 'UF', sortable: false},
      {value: 'cidade', text: 'CIDADE', sortable: false},
      {value: 'bairro', text: 'BAIRRO', sortable: false},
      {value: 'logradouro', text: 'LOGRADOURO', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', width: '9.2rem', align: 'center', sortable: false},
    ],
    tbEnderecosItems: [],
    tbEnderecosLoading: false,

    tbObsHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'observacao', text: 'TEXTO', sortable: false, filterable: false},
      {value: 'acoes', text: 'AÇÕES', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tbObsItems: [],
    tbObsLoading: false,
  }),
  methods: {
    async loadBasico() {
      this.loadingBasico = true;
      const {data} = await this.webclient.get('clientes', {params: {'id': this.id.toString()}});
      this.iptNome = data.nome;
      this.iptCategoria = data.categoria;
      this.iptCategoriaItems = data.categorias;
      this.iptDigital = data.digital;
      this.loadingBasico = false;
    },
    async loadTelefones() {
      this.tbTelefonesLoading = true;
      const {data} = await this.webclient.get('clientes/telefones', {params: {'cadastro': this.id.toString()}});
      this.tbTelefonesItems = data;
      this.tbTelefonesLoading = false;
    },
    async loadEmails() {
      this.tbEmailsLoading = true;
      const {data} = await this.webclient.get('clientes/emails', {params: {'cadastro': this.id.toString()}});
      this.tbEmailsItems = data;
      this.tbEmailsLoading = false;
    },
    async loadEnderecos() {
      this.tbEnderecosLoading = true;
      const {data} = await this.webclient.get('clientes/enderecos', {params: {'cadastro': this.id.toString()}});
      this.tbEnderecosItems = data;
      this.tbEnderecosLoading = false;
    },
    async loadObservacoes() {
      this.tbObsLoading = true;
      const {data} = await this.webclient.get('clientes/observacoes', {params: {'cadastro': this.id.toString()}});
      this.tbObsItems = data;
      this.tbObsLoading = false;
    },
    async excluirTelefone(idTelefone) {
      if (!confirm(`Tem certeza que vai apagar o telefone?`)) return;
      this.tbTelefonesLoading = true;
      await this.webclient.delete(`clientes/telefones?id=${idTelefone}`);
      await this.loadTelefones();
    },
    async excluirEmail(idEmail) {
      if (!confirm(`Tem certeza que vai apagar o e-mail?`)) return;
      this.tbEmailsLoading = true;
      await this.webclient.delete(`clientes/emails?id=${idEmail}`);
      await this.loadEmails();
    },
    async excluirEndereco(idEndereco) {
      if (!confirm(`Tem certeza que vai apagar o endereço?`)) return;
      this.tbEnderecosLoading = true;
      await this.webclient.delete(`clientes/enderecos?id=${idEndereco}`);
      await this.loadEnderecos();
    },
    async excluirObservacao(idObservacao) {
      if (!confirm(`Tem certeza que vai apagar a observação?`)) return;
      this.tbObsLoading = true;
      await this.webclient.delete(`clientes/observacoes?id=${idObservacao}`);
      await this.loadObservacoes();
    },
    async salvarDadosPessoais() {
      if (!this.$refs['form-dadospessoais'].validate()) return;
      this.loadingBasico = true;
      try {
        const cadastro = {
          id: this.id,
          nome: this.iptNome,
          categoria: this.iptCategoria,
          digital: this.iptDigital,
        };
        await this.webclient.post('clientes', cadastro);
        await this.loadBasico();
        this.$store.commit('showSnackbar', {color: 'success', text: 'Informação registrada!'});
      } finally {
        this.loadingBasico = false;
      }
    },
    coletarDigital() {
      if (this.iptDigital && !confirm('Isso irá substituir a digital atual do cadastro, tem certeza?')) return;
      const biometriaNitgen = new BiometriaNitgen();
      biometriaNitgen.capturaCompleta()
        .then(digital => {
          if (digital) this.iptDigital = digital;
          else alert('Parece que você cancelou a coleta ou ocorreu um problema.');
        });
    },
    imprimir() {
      setTimeout(() => window.print(), 500);
    },
  },
  async created() {
    try {
      await Promise.all([this.loadBasico(), this.loadTelefones(), this.loadEmails(), this.loadEnderecos(), this.loadObservacoes()]);
      this.loading = false;
    } catch (e) {
      await this.$router.push('/');
    }
  }
}
</script>
