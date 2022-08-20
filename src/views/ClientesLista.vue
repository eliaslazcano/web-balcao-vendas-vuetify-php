<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto mb-12">
      <v-card-title class="justify-space-between">
        Clientes
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
        <v-text-field
          label="Pesquisar"
          prepend-inner-icon="mdi-magnify"
          v-model="tableSearch"
          @keydown="(x) => {if (x.code === 'F2') pesquisarCadastroPorDigital()}"
          :autofocus="$vuetify.breakpoint.mdAndUp"
          hide-details
        >
          <template v-slot:append-outer>
            <v-btn color="primary" small @click="pesquisarCadastroPorDigital" v-if="existeCadastroComDigital && biometriaDisponivel">
              <v-icon>mdi-fingerprint</v-icon>
            </v-btn>
          </template>
        </v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :search="tableSearch"
        :dense="tableDense"
        :footer-props="{'items-per-page-options': [10, 15, 25, 50]}"
        :mobile-breakpoint="0"
        no-data-text="Nenhum cliente cadastrado"
        no-results-text="Nenhum cliente encontrado"
        sort-by="nome"
      >
        <template v-slot:[`item.criado_em`]="{item}">
          <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY') }}</span>
          <span v-else class="grey--text">NÃO POSSUI</span>
        </template>
        <template v-slot:[`item.acoes`]="{item}">

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="yellow darken-4" small icon v-bind="attrs" v-on="on" @click="editarCadastro(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <span>Editar</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" small icon v-bind="attrs" v-on="on" @click="visualizarEnderecos(item.id)">
                <v-icon>mdi-home-map-marker</v-icon>
              </v-btn>
            </template>
            <span>Endereços</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="info" small icon v-bind="attrs" v-on="on" @click="visualizarEmails(item.id)">
                <v-icon>mdi-at</v-icon>
              </v-btn>
            </template>
            <span>E-mails</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="red" small icon v-bind="attrs" v-on="on" @click="deletarCadastro(item)" :disabled="deletandoCadastro">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
            <span>Apagar</span>
          </v-tooltip>

        </template>
      </v-data-table>
    </v-card>
    <v-fab-transition>
      <v-btn color="success" fab fixed right bottom @click="criarCadastro" v-if="!dialogEditarCadastro">
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-fab-transition>
    <v-dialog v-model="dialogEditarCadastro" width="32rem">
      <v-card>
        <v-form ref="form-cadastro" @submit.prevent="salvarCadastro" :disabled="salvandoCadastro">
          <v-card-title>{{ iptId ? 'Editar' : 'Criar' }} cadastro</v-card-title>
          <v-card-subtitle v-if="!!iptId">Cod. {{ iptId }}</v-card-subtitle>
          <v-card-text>
            <v-text-field
              label="Nome"
              v-model="iptNome"
              outlined
              dense
              :rules="[v => (!!v && !!v.trim()) || 'Coloque o nome']"
            ></v-text-field>
            <v-text-field
              v-if="biometriaDisponivel"
              label="Digital"
              :value="iptDigital ? 'Coletada!' : ''"
              :success="!!iptDigital"
              placeholder="Não possui"
              persistent-placeholder
              hide-details
              append-icon="mdi-fingerprint"
              @click:append="coletarDigital"
              readonly
              outlined
              dense
            >
            </v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed :disabled="salvandoCadastro" @click="dialogEditarCadastro = false">
              Fechar
            </v-btn>
            <v-btn color="primary" small depressed :loading="salvandoCadastro" type="submit">Confirmar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <loading-dialog v-model="dialogLoading" :text="dialogLoadingText" width="400"></loading-dialog>
    <v-dialog v-model="dialogEnderecos" width="72rem">
      <v-card>
        <v-card-title>Endereços</v-card-title>
        <v-data-table
          :headers="tableEnderecosHeaders"
          :items="tableEnderecosItems"
          :loading="tableEnderecosLoading"
          no-data-text="Nenhum endereço cadastrado"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn color="red" small icon @click="excluirEndereco(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="secondary" small depressed @click="dialogEnderecos = false">Fechar</v-btn>
          <v-btn color="success" small depressed @click="dialogCadastrarEndereco = true">
            <v-icon dense class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEmails" width="40rem">
      <v-card>
        <v-card-title>Emails</v-card-title>
        <v-data-table
          :headers="tableEmailsHeaders"
          :items="tableEmailsItems"
          :loading="tableEmailsLoading"
          no-data-text="Nenhum e-mail cadastrado"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn color="red" small icon @click="excluirEmail(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="secondary" small depressed @click="dialogEmails = false">Fechar</v-btn>
          <v-btn color="success" small depressed @click="cadastrarEmail">
            <v-icon dense class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogCadastrarEndereco" width="32rem">
      <v-card>
        <v-form ref="form-endereco" :disabled="dialogCadastrarEnderecoLoading" @submit.prevent="cadastrarEndereco">
          <v-card-title>Cadastrar endereço</v-card-title>
          <v-card-text>
            <v-text-field label="CEP" v-model="iptCep" v-mask="'########'" :loading="iptCepLoading"></v-text-field>
            <v-text-field label="UF" v-model="iptUf" v-mask="'AA'"></v-text-field>
            <v-text-field label="Bairro" v-model="iptBairro"></v-text-field>
            <v-text-field label="Cidade" v-model="iptCidade" :rules="[v => (!!v && !!v.trim()) || 'Insira a rua e número neste campo']"></v-text-field>
            <v-text-field label="Logradouro" v-model="iptLogradouro" :rules="iptLogradouroRules"></v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed @click="dialogCadastrarEndereco = false" :disabled="dialogCadastrarEnderecoLoading">Cancelar</v-btn>
            <v-btn color="primary" small depressed type="submit" :loading="dialogCadastrarEnderecoLoading">Salvar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import BiometriaNitgen from '@/http/BiometriaNitgen';
import moment from '@/plugins/moment';
import LoadingDialog from '@/components/LoadingDialog';
import StringHelper from '@/helper/StringHelper';
import CepWebClient from '@/http/CepWebClient';
export default {
  name: 'ClientesLista',
  components: {LoadingDialog, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    biometriaDisponivel: false,
    tableHeaders: [
      {value: 'id', text: 'COD.', width: '6rem'},
      {value: 'nome', text: 'NOME'},
      {value: 'criado_em', text: 'DATA CADASTRO', width: '9.2rem'},
      {value: 'acoes', text: 'AÇÕES', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
    dialogEditarCadastro: false,
    salvandoCadastro: false,
    deletandoCadastro: false,
    iptId: null,
    iptNome: '',
    iptDigital: '',
    dialogLoading: false,
    dialogLoadingText: '',
    dialogEnderecos: false,
    tableEnderecosHeaders: [
      {value: 'criado_em', text: 'DATA'},
      {value: 'cep', text: 'CEP', sortable: false},
      {value: 'uf', text: 'UF', sortable: false},
      {value: 'cidade', text: 'CIDADE', sortable: false},
      {value: 'bairro', text: 'BAIRRO', sortable: false},
      {value: 'logradouro', text: 'LOGRADOURO', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', width: '9.2rem', align: 'center', sortable: false},
    ],
    tableEnderecosItems: [],
    tableEnderecosLoading: false,
    dialogEmails: false,
    tableEmailsHeaders: [
      {value: 'criado_em', text: 'DATA', width: '9.2rem'},
      {value: 'email', text: 'E-MAIL', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', width: '9.2rem', align: 'center', sortable: false},
    ],
    tableEmailsItems: [],
    tableEmailsLoading: false,
    dialogCadastrarEndereco: false,
    dialogCadastrarEnderecoLoading: false,
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
  methods: {
    imprimir() {
      setTimeout(() => window.print(), 500);
    },
    async loadData() {
      const webclient = http();
      const {data} = await webclient.get('clientes');
      this.tableItems = data.dados;
      this.biometriaDisponivel = data.biometria_nitgen;
      this.loading = false;
    },
    criarCadastro() {
      this.iptId = null;
      this.iptNome = '';
      this.iptDigital = '';
      if (this.$refs['form-cadastro']) this.$refs['form-cadastro'].resetValidation();
      this.dialogEditarCadastro = true;
    },
    editarCadastro(item) {
      this.iptId = item.id;
      this.iptNome = item.nome;
      this.iptDigital = item.digital;
      this.dialogEditarCadastro = true;
    },
    async coletarDigital() {
      if (this.iptDigital) {
       if (!confirm('Isso irá substituir a digital atual do cadastro, tem certeza?')) return;
      }
      const biometriaNitgen = new BiometriaNitgen();
      try {
       const digital = await biometriaNitgen.capturaCompleta();
       if (!digital) {
         alert('Parece que você cancelou a coleta ou ocorreu um problema.');
         return;
       }
       this.iptDigital = digital;
      } catch (e) {
        alert('Parece que não foi possível coletar a digital');
      }
    },
    async salvarCadastro() {
      if (!this.$refs['form-cadastro'].validate()) return;
      try {
        this.salvandoCadastro = true;
        const cadastro = {id: this.iptId, codigo: this.iptCodigo, nome: this.iptNome, digital: this.iptDigital, valor: this.iptValor};
        const webclient = http();
        await webclient.post('clientes', cadastro);
        await this.loadData();
        this.dialogEditarCadastro = false;
        if (this.iptId) this.$store.commit('showSnackbar', {color: 'success', text: 'Cadastro atualizado'});
        else this.$store.commit('showSnackbar', {color: 'success', text: 'Cliente registrado'});
      } finally {
        this.salvandoCadastro = false;
      }
    },
    async deletarCadastro(item) {
      if (!confirm(`Tem certeza que vai apagar '${item.nome}'?`)) return;
      try {
        this.deletandoCadastro = true;
        const webclient = http();
        await webclient.delete('clientes?id=' + item.id);
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Cadastro apagado'});
      } finally {
        this.deletandoCadastro = false;
      }
    },
    async pesquisarCadastroPorDigital() {
      if (!this.existeCadastroComDigital || !this.biometriaDisponivel) return;
      const biometriaNitgen = new BiometriaNitgen();
      try {
        const cadastros = this.tableItems.filter(i => !!i.digital).map(i => ({id: i.id, digital: i.digital}));
        this.dialogLoading = true;
        this.dialogLoadingText = 'Enviando todas as biometrias para a memória..';
        const resultado = await biometriaNitgen.identificar(cadastros);
        if (resultado === 0) alert('Nenhum cadastro pôde ser encontrado com esta digital escaneada.');
        else if (resultado > 0) {
          const cadastro = this.tableItems.find(i => i.id === resultado);
          if (cadastro) this.editarCadastro(cadastro);
        }
      } catch (e) {
        alert('Parece que não foi possível coletar a digital');
      } finally {
        this.dialogLoading = false;
      }
    },
    async visualizarEnderecos(idCliente) {
      this.tableEnderecosItems = [];
      this.tableEnderecosLoading = true;
      this.dialogEnderecos = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/enderecos?cadastro=${idCliente}`);
        this.tableEnderecosItems = data;
      } catch (e) {
        this.dialogEnderecos = false;
      } finally {
        this.tableEnderecosLoading = false;
      }
    },
    async visualizarEmails(idCliente) {
      this.tableEmailsItems = [];
      this.tableEmailsLoading = true;
      this.dialogEmails = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/emails?cadastro=${idCliente}`);
        this.tableEmailsItems = data;
      } catch (e) {
        this.dialogEmails = false;
      } finally {
        this.tableEmailsLoading = false;
      }
    },
    async excluirEndereco(idEndereco) {
      if (!confirm(`Tem certeza que vai apagar o endereço?`)) return;
      const webclient = http();
      await webclient.delete(`clientes/enderecos?id=${idEndereco}`);
      const {data} = await webclient.get(`clientes/enderecos?cadastro=${this.iptId}`);
      this.tableEnderecosItems = data;
    },
    async excluirEmail(idEmail) {
      if (!confirm(`Tem certeza que vai apagar o email?`)) return;
      const webclient = http();
      await webclient.delete(`clientes/emails?id=${idEmail}`);
      const {data} = await webclient.get(`clientes/emails?cadastro=${this.iptId}`);
      this.tableEmailsItems = data;
    },
    async cadastrarEmail() {
      const email = prompt('Digite o endereço de e-mail');
      if (!email || email.trim().length === 0) return;
      const webclient = http();
      await webclient.post('clientes/emails', {cadastro: this.iptId, email: email.trim().toLowerCase()})
        .then(async () => {
          const {data} = await webclient.get(`clientes/emails?cadastro=${this.iptId}`);
          this.tableEmailsItems = data;
        });
    },
    async cadastrarEndereco() {
      if (!this.$refs['form-endereco'].validate()) return;
      this.dialogCadastrarEnderecoLoading = true;
      const dados = {
        cadastro: this.iptId,
        cep: this.iptCep ? StringHelper.extractNumbers(this.iptCep) : '',
        uf: this.iptUf,
        bairro: this.iptBairro.trim().toUpperCase(),
        cidade: this.iptCidade.trim().toUpperCase(),
        logradouro: this.iptLogradouro.trim().toUpperCase(),
      };
      try {
        const webclient = http();
        await webclient.post('clientes/enderecos', dados);
        const {data} = await webclient.get(`clientes/enderecos?cadastro=${this.iptId}`);
        this.tableEnderecosItems = data;
        this.dialogCadastrarEndereco = false;
      } finally {
        this.dialogCadastrarEnderecoLoading = false;
      }
    }
  },
  computed: {
    existeCadastroComDigital() {
      return this.tableItems.findIndex(i => !!i.digital) !== -1;
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
    }
  },
}
</script>
