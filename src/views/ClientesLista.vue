<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto">
      <v-card-title>Clientes</v-card-title>
      <v-card-text>
        <v-text-field
          label="Pesquisar"
          prepend-inner-icon="mdi-magnify"
          v-model="tableSearch"
          @keydown="(x) => {if (x.code === 'F2') pesquisarCadastroPorDigital()}"
          hide-details autofocus
        >
          <template v-slot:append-outer>
            <v-btn color="primary" small @click="pesquisarCadastroPorDigital" v-if="existeCadastroComDigital && biometriaDisponivel">
              <v-icon>mdi-fingerprint</v-icon>
            </v-btn>
          </template>
        </v-text-field>
      </v-card-text>
      <v-data-table :headers="tableHeaders" :items="tableItems" :search="tableSearch" sort-by="nome">
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
    <v-dialog v-model="dialogEnderecos" width="64rem">
      <v-card>
        <v-card-title>Endereços</v-card-title>
        <v-data-table
          :headers="tableEnderecosHeaders"
          :items="tableEnderecosItems"
          no-data-text="Nenhum endereço cadastrado"
          sort-by="criado_em"
          sort-desc
          dense
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
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEmails" width="40rem">
      <v-card>
        <v-card-title>Emails</v-card-title>
        <v-data-table
          :headers="tableEmailsHeaders"
          :items="tableEmailsItems"
          no-data-text="Nenhum e-mail cadastrado"
          sort-by="criado_em"
          sort-desc
          dense
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
export default {
  name: 'ClientesLista',
  components: {LoadingDialog, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    biometriaDisponivel: false,
    tableHeaders: [
      {value: 'id', text: 'COD.', width: '6rem'},
      {value: 'criado_em', text: 'DATA CADASTRO', width: '9.2rem'},
      {value: 'nome', text: 'NOME'},
      {value: 'acoes', text: 'AÇÕES', width: '9.2rem', align: 'center', sortable: false},
    ],
    tableItems: [],
    tableSearch: '',
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
    dialogEmails: false,
    tableEmailsHeaders: [
      {value: 'criado_em', text: 'DATA'},
      {value: 'email', text: 'E-MAIL', sortable: false},
      {value: 'acoes', text: 'EXCLUIR', width: '9.2rem', align: 'center', sortable: false},
    ],
    tableEmailsItems: [],
  }),
  methods: {
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
      this.dialogEnderecos = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/enderecos?cadastro=${idCliente}`);
        this.tableEnderecosItems = data;
      } catch (e) {
        this.dialogEnderecos = false;
      }
    },
    async visualizarEmails(idCliente) {
      this.dialogEmails = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/emails?cadastro=${idCliente}`);
        this.tableEmailsItems = data;
      } catch (e) {
        this.dialogEmails = false;
      }
    },
    async excluirEndereco(idEndereco) {
      const webclient = http();
      await webclient.delete(`clientes/enderecos?id=${idEndereco}`);
      const {data} = await webclient.get(`clientes/enderecos?cadastro=${this.iptId}`);
      this.tableEnderecosItems = data;
    },
    async excluirEmail(idEmail) {
      const webclient = http();
      await webclient.delete(`clientes/emails?id=${idEmail}`);
      const {data} = await webclient.get(`clientes/emails?cadastro=${this.iptId}`);
      this.tableEmailsItems = data;
    },
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
}
</script>
