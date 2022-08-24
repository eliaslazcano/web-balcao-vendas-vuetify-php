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
        <v-form ref="form-dadospessoais" :disabled="loadingBasico" @submit.prevent="salvarBasico">
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
              no-data-text="Nenhuma categoria foi cadastrada"
              clearable
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
        <v-card-title class="justify-space-between">
          Telefones
          <v-btn color="success" small depressed @click="cadastrarTelefone">Adicionar</v-btn>
        </v-card-title>
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
        <v-card-title class="justify-space-between">
          E-mails
          <v-btn color="success" small depressed @click="cadastrarEmail">Adicionar</v-btn>
        </v-card-title>
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
        <v-card-title class="justify-space-between">
          Endereços
          <v-btn color="success" small depressed @click="cadastrarEndereco">Adicionar</v-btn>
        </v-card-title>
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
        <v-card-title class="justify-space-between">
          Observações
          <v-btn color="success" small depressed @click="cadastrarObservacao">Adicionar</v-btn>
        </v-card-title>
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
      <v-card class="mb-5">
        <v-card-title>Vendas</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="tbVendasHeaders"
          :items="tbVendasItems"
          :loading="tbVendasLoading"
          no-data-text="Nenhuma venda realizada"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.id`]="{item}">
            {{item.id}}
            <v-icon color="red" class="ml-1" small v-if="item.encerrado_em !== null">mdi-lock</v-icon>
          </template>
          <template v-slot:[`item.debito`]="{item}">
            <span v-if="!item.debito && !item.credito" class="grey--text">R$ {{ formatoMonetario(item.credito) }}</span>
            <span v-else-if="item.credito >= item.debito" class="success--text">R$ {{ formatoMonetario(item.credito) }}</span>
            <span v-else-if="item.credito > 0" class="warning--text">R$ {{ formatoMonetario(item.credito) }} / {{ formatoMonetario(item.debito) }}</span>
            <span v-else class="red--text">R$ {{ formatoMonetario(item.debito) }}</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn icon small color="primary" :to="'/venda/' + item.id">
              <v-icon>mdi-open-in-new</v-icon>
            </v-btn>
          </template>
          <template v-slot:foot>
            <tfoot>
            <tr>
              <th colspan="5" class="text-center">
                <span class="success--text">Arrecadado R$ {{formatoMonetario(totalArrecadado)}}</span> /
                <span class="error--text">Pendente R$ {{formatoMonetario(totalPendente)}}</span>
              </th>
            </tr>
            </tfoot>
          </template>
        </v-data-table>
      </v-card>
    </div>
    <v-dialog v-model="dialogTelefone" width="32rem" :persistent="tbTelefonesLoading">
      <v-card>
        <v-form ref="form-telefone" @submit.prevent="cadastrarTelefone" :disabled="tbTelefonesLoading">
          <v-card-title>Cadastrar telefone</v-card-title>
          <v-card-text>
            <v-select
              label="Tipo"
              :items="[{value: 1, text: 'Fixo'}, {value: 2, text: 'Celular'}]"
              placeholder="Selecione"
              persistent-placeholder
              outlined
              dense
              v-model="iptTelefoneTipo"
              :rules="[v => !!v || 'Selecione o tipo de telefone']"
            ></v-select>
            <v-text-field
              label="Número do telefone com DDD"
              class="mt-3"
              placeholder="(00) 0000-0000"
              persistent-placeholder
              v-mask="['(##) ####-####', '(##) #####-####']"
              outlined
              dense
              v-model="iptTelefoneNumero"
              :rules="[v => !!v || 'Insira o número de telefone', v => (!!v && v.length >= 14) || 'Complete o telefone']"
            ></v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed @click="dialogTelefone = false" :disabled="tbTelefonesLoading">
              Cancelar
            </v-btn>
            <v-btn color="primary" small depressed type="submit" :loading="tbTelefonesLoading">
              Gravar
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEndereco" width="32rem" :persistent="tbEnderecosLoading">
      <v-card>
        <v-form ref="form-endereco" :disabled="tbEnderecosLoading" @submit.prevent="cadastrarEndereco">
          <v-card-title>Cadastrar endereço</v-card-title>
          <v-card-text>
            <v-text-field label="CEP" v-model="iptCep" v-mask="'########'" :loading="iptCepLoading"></v-text-field>
            <v-text-field label="UF" v-model="iptUf" v-mask="'AA'"></v-text-field>
            <v-text-field label="Bairro" v-model="iptBairro"></v-text-field>
            <v-text-field label="Cidade" v-model="iptCidade" :rules="[v => (!!v && !!v.trim()) || 'Insira a rua e número neste campo']"></v-text-field>
            <v-text-field label="Logradouro" v-model="iptLogradouro" :rules="iptLogradouroRules"></v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed @click="dialogEndereco = false" :disabled="tbEnderecosLoading">Cancelar</v-btn>
            <v-btn color="primary" small depressed type="submit" :loading="tbEnderecosLoading">Salvar</v-btn>
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
import {config} from '@/config';
import BiometriaNitgen from '@/http/BiometriaNitgen';
import StringHelper from '@/helper/StringHelper';
import CepWebClient from '@/http/CepWebClient';
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
      {value: 'acoes', text: 'EXCLUIR', width: '5.4rem', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tbTelefonesItems: [],
    tbTelefonesLoading: false,

    tbEmailsHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'email', text: 'E-MAIL', cellClass: 'text-no-wrap', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', width: '5.4rem', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
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
      {value: 'acoes', text: 'EXCLUIR', width: '5.4rem', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tbEnderecosItems: [],
    tbEnderecosLoading: false,

    tbObsHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'observacao', text: 'TEXTO', sortable: false, filterable: false},
      {value: 'acoes', text: 'EXCLUIR', width: '5.4rem', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tbObsItems: [],
    tbObsLoading: false,

    tbVendasHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
      {value: 'id', text: 'VENDA Nº'},
      {value: 'debito', text: 'VALOR', cellClass: 'text-no-wrap'},
      {value: 'acoes', text: 'ABRIR', width: '5.4rem', align: 'center', sortable: false, filterable: false},
    ],
    tbVendasItems: [],
    tbVendasLoading: false,

    dialogTelefone: false,
    iptTelefoneTipo: null,
    iptTelefoneNumero: '',

    dialogEndereco: false,
    iptCep: '',
    iptCepLoading: false,
    iptUf: '',
    iptBairro: '',
    iptCidade: '',
    iptLogradouro: '',
    iptLogradouroRules: [
      v => (!!v && !!v.trim()) || 'Insira a rua e número neste campo',
      v => (!!v && StringHelper.extractNumbers(v).length > 0) || 'O logradouro está sem o número',
    ],
  }),
  computed: {
    totalArrecadado() {
      return this.tbVendasItems.reduce((previousValue, currentValue) => previousValue + currentValue.credito, 0);
    },
    totalPendente() {
      return this.tbVendasItems.reduce(
        (previousValue, currentValue) => previousValue + (currentValue.credito >= currentValue.debito ? 0 : (currentValue.debito - currentValue.credito))
        , 0);
    },
  },
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
    async loadVendas() {
      this.tbVendasLoading = true;
      const {data} = await this.webclient.get('clientes/vendas', {params: {'cadastro': this.id.toString()}});
      this.tbVendasItems = data;
      this.tbVendasLoading = false;
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
    async salvarBasico() {
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
    async cadastrarTelefone() {
      if (!this.dialogTelefone) {
        this.dialogTelefone = true;
        return;
      }
      if (!this.$refs['form-telefone'].validate()) return;
      this.tbTelefonesLoading = true;
      try {
        const dados = {
          cadastro: this.id,
          numero: StringHelper.extractNumbers(this.iptTelefoneNumero),
          tipo: this.iptTelefoneTipo
        };
        await this.webclient.post('clientes/telefones', dados);
        await this.loadTelefones();
        this.dialogTelefone = false;
      } finally {
        this.tbTelefonesLoading = false;
      }
    },
    async cadastrarEmail() {
      const email = prompt('Digite o endereço de e-mail');
      if (!email || email.trim().length === 0) return;
      if (!/.+@.+\..+/.test(email)) {
        this.$store.commit('showSnackbar', {color: 'error', text: 'E-mail inválido'});
        return;
      }
      this.tbEmailsLoading = true;
      try {
        await this.webclient.post('clientes/emails', {cadastro: this.id, email: email.trim().toLowerCase()})
        await this.loadEmails();
      } finally {
        this.tbEmailsLoading = false;
      }
    },
    async cadastrarEndereco() {
      if (!this.dialogEndereco) {
        this.dialogEndereco = true;
        return;
      }
      if (!this.$refs['form-endereco'].validate()) return;
      this.tbEnderecosLoading = true;
      try {
        const dados = {
          cadastro: this.id,
          cep: this.iptCep ? StringHelper.extractNumbers(this.iptCep) : '',
          uf: this.iptUf,
          bairro: this.iptBairro.trim().toUpperCase(),
          cidade: this.iptCidade.trim().toUpperCase(),
          logradouro: this.iptLogradouro.trim().toUpperCase(),
        };
        await this.webclient.post('clientes/enderecos', dados);
        await this.loadEnderecos();
        this.dialogEndereco = false;
      } finally {
        this.tbEnderecosLoading = false;
      }
    },
    async cadastrarObservacao() {
      const texto = prompt('Digite sua observação');
      if (!texto || texto.trim().length === 0) return;
      this.tbObsLoading = true;
      try {
        await this.webclient.post('clientes/observacoes', {cadastro: this.id, observacao: texto.trim()});
        await this.loadObservacoes();
      } finally {
        this.tbObsLoading = false;
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
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
  },
  async created() {
    try {
      await Promise.all([this.loadBasico(), this.loadTelefones(), this.loadEmails(), this.loadEnderecos(), this.loadObservacoes(), this.loadVendas()]);
      this.loading = false;
    } catch (e) {
      await this.$router.push('/');
    }
  },
  watch: {
    dialogTelefone(v) {
      if (!v && this.$refs['form-telefone']) this.$refs['form-telefone'].reset();
    },
    dialogEndereco(v) {
      if (!v && this.$refs['form-endereco']) this.$refs['form-endereco'].reset();
    },
    iptCep(v) {
      if (v && StringHelper.extractNumbers(v).length === 8) {
        this.iptCepLoading = true;
        CepWebClient.smart(StringHelper.extractNumbers(v))
          .then(x => {
            if (x.estado) this.iptUf = x.estado;
            if (x.cidade) this.iptCidade = x.cidade;
            if (x.bairro) this.iptBairro = x.bairro;
            if (x.logradouro) this.iptLogradouro = x.logradouro;
          })
          .finally(() => this.iptCepLoading = false);
      }
    },
  }
}
</script>
