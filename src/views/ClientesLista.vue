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
          placeholder="Nome do cliente ou código"
          persistent-placeholder
          v-model="tableSearch"
          :autofocus="$vuetify.breakpoint.mdAndUp"
          :hint="biometriaDisponivel && $vuetify.breakpoint.mdAndUp ? 'Pressione F2 para buscar por digital' : undefined"
          @keydown="(x) => {if (x.code === 'F2') pesquisarCadastroPorDigital()}"
        >
          <template v-slot:append-outer>
            <v-btn color="primary" small @click="pesquisarCadastroPorDigital" v-if="biometriaDisponivel && $vuetify.breakpoint.mdAndUp">
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
        :loading="tableLoading"
        :footer-props="{'items-per-page-options': [10, 15, 25, 50]}"
        :mobile-breakpoint="0"
        no-data-text="Nenhum cliente cadastrado"
        no-results-text="Nenhum cliente encontrado"
        sort-by="nome"
      >
        <template v-slot:[`item.nome`]="{item}">
          <router-link :to="'/cliente/' + item.id">{{ item.nome }}</router-link>
          <v-icon v-if="biometriaDisponivel && !!item.digital" dense class="ml-1">mdi-fingerprint</v-icon>
        </template>
        <template v-slot:[`item.criado_em`]="{item}">
          <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY') }}</span>
          <span v-else class="grey--text">NÃO POSSUI</span>
        </template>
        <template v-slot:[`item.acoes`]="{item}">

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="orange" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="editarCadastro(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <span>Editar</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="green" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="visualizarEnderecos(item.id)">
                <v-icon>mdi-home-map-marker</v-icon>
              </v-btn>
            </template>
            <span>Endereços</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="info" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="visualizarEmails(item.id)">
                <v-icon>mdi-at</v-icon>
              </v-btn>
            </template>
            <span>E-mails</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="indigo" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="visualizarTelefones(item.id)">
                <v-icon>mdi-phone</v-icon>
              </v-btn>
            </template>
            <span>Telefones</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="deep-purple" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="visualizarObservacoes(item.id)">
                <v-icon>mdi-comment-text</v-icon>
              </v-btn>
            </template>
            <span>Observações</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="teal" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="visualizarVendas(item.id)">
                <v-icon>mdi-handshake</v-icon>
              </v-btn>
            </template>
            <span>Vendas</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="red" small icon v-bind="attrs" v-on="on" :disabled="tableLoading" @click="deletarCadastro(item)">
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
    <loading-dialog v-model="dialogLoading" :text="dialogLoadingText" width="400"></loading-dialog>
    <v-dialog v-model="dialogEditarCadastro" width="32rem" :persistent="tableLoading">
      <v-card>
        <v-form ref="form-cadastro" @submit.prevent="salvarCadastro" :disabled="tableLoading">
          <v-card-title class="justify-space-between">
            <span>{{ iptId ? 'Editar' : 'Criar' }} cadastro</span>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon color="primary" v-bind="attrs" v-on="on" :to="'/cliente/' + iptId">
                  <v-icon>mdi-open-in-new</v-icon>
                </v-btn>
              </template>
              <span>Ver a ficha completa</span>
            </v-tooltip>
          </v-card-title>
          <v-card-subtitle v-if="!!iptId">Cod. {{ iptId }}</v-card-subtitle>
          <v-card-text>
            <v-text-field
              label="Nome"
              v-model="iptNome"
              outlined
              dense
              :rules="[v => (!!v && !!v.trim()) || 'Coloque o nome']"
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
              :value="iptDigital ? 'Coletada!' : ''"
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
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed :disabled="tableLoading" @click="dialogEditarCadastro = false">
              Fechar
            </v-btn>
            <v-btn color="primary" small depressed :loading="tableLoading" type="submit">Confirmar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEnderecos" width="72rem">
      <v-card>
        <v-card-title>Endereços</v-card-title>
        <v-data-table
          :headers="tableGenericHeaders"
          :items="tableGenericItems"
          :loading="tableGenericLoading"
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
            <v-icon small class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogCadastrarEndereco" width="32rem" :persistent="dialogCadastrarEnderecoLoading">
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
    <v-dialog v-model="dialogEmails" width="40rem">
      <v-card>
        <v-card-title>Emails</v-card-title>
        <v-data-table
          :headers="tableGenericHeaders"
          :items="tableGenericItems"
          :loading="tableGenericLoading"
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
            <v-icon small class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogTelefones" width="32rem">
      <v-card>
        <v-card-title>Telefones</v-card-title>
        <v-data-table
          :headers="tableGenericHeaders"
          :items="tableGenericItems"
          :loading="tableGenericLoading"
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
            <v-btn color="red" small icon @click="excluirTelefone(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="secondary" small depressed @click="dialogTelefones = false">Fechar</v-btn>
          <v-btn color="success" small depressed @click="cadastrarTelefone">
            <v-icon small class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogCadastrarTelefone" width="32rem" :persistent="tableGenericLoading">
      <v-card>
        <v-form ref="form-telefone" @submit.prevent="cadastrarTelefone" :disabled="tableGenericLoading">
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
            <v-btn color="secondary" small depressed @click="dialogCadastrarTelefone = false" :disabled="tableGenericLoading">
              Cancelar
            </v-btn>
            <v-btn color="primary" small depressed type="submit" :loading="tableGenericLoading">
              Gravar
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogObservacoes" width="64rem">
      <v-card>
        <v-card-title>Observações</v-card-title>
        <v-data-table
          :headers="tableGenericHeaders"
          :items="tableGenericItems"
          :loading="tableGenericLoading"
          no-data-text="Nenhuma observação encontrada"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">
            <span v-if="item.criado_em">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</span>
            <span v-else class="grey--text">NÃO POSSUI</span>
          </template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-btn color="red" small icon @click="excluirObservacao(item.id)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="secondary" small depressed @click="dialogObservacoes = false">Fechar</v-btn>
          <v-btn color="success" small depressed @click="cadastrarObservacao">
            <v-icon small class="mr-1">mdi-plus-circle</v-icon> Cadastrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogVendas" width="32rem">
      <v-card>
        <v-card-title>
          <v-icon large class="mr-2">mdi-handshake</v-icon> Vendas
        </v-card-title>
        <v-data-table
          :headers="tableGenericHeaders"
          :items="tableGenericItems"
          :loading="tableGenericLoading"
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
import StringHelper from '@/helper/StringHelper';
import CepWebClient from '@/http/CepWebClient';
import {config} from '@/config';
export default {
  name: 'ClientesLista',
  components: {LoadingDialog, AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    biometriaDisponivel: config.biometria,
    tableHeaders: [
      {value: 'id', text: 'COD.', width: '5.2rem'},
      {value: 'nome', text: 'NOME', cellClass: 'text-no-wrap'},
      {value: 'vendas', text: 'VENDAS', cellClass: 'text-no-wrap', filterable: false},
      {value: 'criado_em', text: 'DATA CADASTRO', cellClass: 'text-no-wrap', filterable: false},
      {value: 'acoes', text: 'AÇÕES', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
    tableLoading: false,

    dialogEditarCadastro: false,
    iptId: null,
    iptNome: '',
    iptCategoria: null,
    iptCategoriaItems: [],
    iptDigital: '',

    dialogLoading: false,
    dialogLoadingText: '',
    dialogEnderecos: false,
    dialogEmails: false,
    dialogTelefones: false,
    dialogObservacoes: false,
    dialogVendas: false,

    tableGenericHeaders: [],
    tableGenericItems: [],
    tableGenericLoading: false,

    dialogCadastrarTelefone: false,
    iptTelefoneTipo: null,
    iptTelefoneNumero: '',

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
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
    async loadData() {
      const webclient = http();
      const {data} = await webclient.get('clientes');
      this.tableItems = data.clientes;
      this.iptCategoriaItems = data.categorias;
      this.loading = false;
    },
    criarCadastro() {
      this.iptId = null;
      this.iptNome = '';
      this.iptCategoria = null;
      this.iptDigital = '';
      if (this.$refs['form-cadastro']) this.$refs['form-cadastro'].resetValidation();
      this.dialogEditarCadastro = true;
    },
    editarCadastro(item) {
      this.iptId = item.id;
      this.iptNome = item.nome;
      this.iptCategoria = item.categoria;
      this.iptDigital = item.digital;
      this.dialogEditarCadastro = true;
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
    async salvarCadastro() {
      if (!this.$refs['form-cadastro'].validate()) return;
      try {
        this.tableLoading = true;
        const cadastro = {
          id: this.iptId,
          nome: this.iptNome,
          categoria: this.iptCategoria,
          digital: this.iptDigital
        };
        const webclient = http();
        await webclient.post('clientes', cadastro);
        await this.loadData();
        this.dialogEditarCadastro = false;
        if (this.iptId) this.$store.commit('showSnackbar', {color: 'success', text: 'Cadastro atualizado!'});
        else this.$store.commit('showSnackbar', {color: 'success', text: 'Cliente registrado!'});
      } finally {
        this.tableLoading = false;
      }
    },
    async deletarCadastro(item) {
      if (!confirm(`Tem certeza que vai apagar '${item.nome}'?`)) return;
      try {
        this.tableLoading = true;
        const webclient = http();
        await webclient.delete('clientes?id=' + item.id);
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Cadastro apagado!'});
      } finally {
        this.tableLoading = false;
      }
    },
    async pesquisarCadastroPorDigital() {
      if (!this.biometriaDisponivel) return;
      if (!this.existeCadastroComDigital) {
        alert('Nenhum cliente possui digital cadastrada!');
        return;
      }
      this.dialogLoadingText = 'Enviando todas as biometrias para a memória e escaneando';
      this.dialogLoading = true;
      try {
        const cadastros = this.tableItems.filter(i => !!i.digital).map(i => ({id: i.id, digital: i.digital}));
        const biometriaNitgen = new BiometriaNitgen();
        const resultado = await biometriaNitgen.identificar(cadastros);
        if (resultado === 0) alert('Nenhum cadastro pôde ser encontrado com esta digital escaneada.');
        else if (resultado > 0) {
          const cadastro = this.tableItems.find(i => i.id === resultado);
          if (cadastro) {
            this.tableSearch = cadastro.nome;
            this.editarCadastro(cadastro);
          }
        }
      } catch (e) {
        alert('Parece que não foi possível coletar a digital');
      } finally {
        this.dialogLoading = false;
      }
    },
    async visualizarEnderecos(idCliente) {
      this.tableGenericHeaders = [
        {value: 'criado_em', text: 'DATA'},
        {value: 'cep', text: 'CEP', sortable: false},
        {value: 'uf', text: 'UF', sortable: false},
        {value: 'cidade', text: 'CIDADE', sortable: false},
        {value: 'bairro', text: 'BAIRRO', sortable: false},
        {value: 'logradouro', text: 'LOGRADOURO', sortable: false},
        {value: 'acoes', text: 'EXCLUIR', width: '9.2rem', align: 'center', sortable: false},
      ];
      this.tableGenericItems = [];
      this.tableGenericLoading = true;
      this.dialogEnderecos = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/enderecos?cadastro=${idCliente}`);
        this.tableGenericItems = data;
      } catch (e) {
        this.dialogEnderecos = false;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async visualizarEmails(idCliente) {
      this.tableGenericHeaders = [
        {value: 'criado_em', text: 'DATA', cellClass: 'text-no-wrap'},
        {value: 'email', text: 'E-MAIL', cellClass: 'text-no-wrap', sortable: false},
        {value: 'acoes', text: 'EXCLUIR', cellClass: 'text-no-wrap', sortable: false, align: 'center'},
      ];
      this.tableGenericItems = [];
      this.tableGenericLoading = true;
      this.dialogEmails = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/emails?cadastro=${idCliente}`);
        this.tableGenericItems = data;
      } catch (e) {
        this.dialogEmails = false;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async visualizarTelefones(idCliente) {
      this.tableGenericHeaders = [
        {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
        {value: 'tipo', text: 'TIPO', width: '5rem'},
        {value: 'numero', text: 'NÚMERO', cellClass: 'text-no-wrap', sortable: false},
        {value: 'acoes', text: 'EXCLUIR', cellClass: 'text-no-wrap', sortable: false, align: 'center'},
      ];
      this.tableGenericItems = [];
      this.tableGenericLoading = true;
      this.dialogTelefones = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/telefones?cadastro=${idCliente}`);
        this.tableGenericItems = data;
      } catch (e) {
        this.dialogTelefones = false;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async visualizarObservacoes(idCliente) {
      this.tableGenericHeaders = [
        {value: 'criado_em', text: 'DATA', cellClass: 'text-no-wrap'},
        {value: 'observacao', text: 'TEXTO', sortable: false, filterable: false},
        {value: 'acoes', text: 'AÇÕES', cellClass: 'text-no-wrap', sortable: false, filterable: false},
      ];
      this.tableGenericItems = [];
      this.tableGenericLoading = true;
      this.dialogObservacoes = true;
      this.iptId = idCliente;
      try {
        const webclient = http();
        const {data} = await webclient.get(`clientes/observacoes?cadastro=${idCliente}`);
        this.tableGenericItems = data;
      } catch (e) {
        this.dialogObservacoes = false;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async visualizarVendas(idCliente) {
      this.tableGenericHeaders = [
        {value: 'criado_em', text: 'DATA', width: '9.2rem', cellClass: 'text-no-wrap'},
        {value: 'id', text: 'COD. VENDA'},
        {value: 'debito', text: 'VALOR', cellClass: 'text-no-wrap'},
        {value: 'acoes', text: 'ABRIR', width: '5.4rem', align: 'center', sortable: false, filterable: false},
      ];
      this.tableGenericItems = [];
      this.tableGenericLoading = true;
      this.dialogVendas = true;
      try {
        const webclient = http();
        const {data} = await webclient.get('clientes/vendas', {params: {'cadastro': idCliente.toString()}});
        this.tableGenericItems = data;
      } catch (e) {
        this.dialogObservacoes = false;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async excluirEndereco(idEndereco) {
      if (!confirm(`Tem certeza que vai apagar o endereço?`)) return;
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        await webclient.delete(`clientes/enderecos?id=${idEndereco}`);
        const {data} = await webclient.get(`clientes/enderecos?cadastro=${this.iptId}`);
        this.tableGenericItems = data;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async excluirEmail(idEmail) {
      if (!confirm(`Tem certeza que vai apagar o email?`)) return;
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        await webclient.delete(`clientes/emails?id=${idEmail}`);
        const {data} = await webclient.get(`clientes/emails?cadastro=${this.iptId}`);
        this.tableGenericItems = data;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async excluirTelefone(idTelefone) {
      if (!confirm(`Tem certeza que vai apagar o telefone?`)) return;
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        await webclient.delete(`clientes/telefones?id=${idTelefone}`);
        const {data} = await webclient.get(`clientes/telefones?cadastro=${idTelefone}`);
        this.tableGenericItems = data;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async excluirObservacao(idObservacao) {
      if (!confirm(`Tem certeza que vai apagar a observação?`)) return;
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        await webclient.delete(`clientes/observacoes?id=${idObservacao}`);
        const {data} = await webclient.get(`clientes/observacoes?cadastro=${this.iptId}`);
        this.tableGenericItems = data;
      } finally {
        this.tableGenericLoading = false;
      }
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
        this.tableGenericItems = data;
        this.dialogCadastrarEndereco = false;
      } finally {
        this.dialogCadastrarEnderecoLoading = false;
      }
    },
    async cadastrarEmail() {
      const email = prompt('Digite o endereço de e-mail');
      if (!email || email.trim().length === 0) return;
      if (!/.+@.+\..+/.test(email)) {
        this.$store.commit('showSnackbar', {color: 'error', text: 'E-mail inválido'});
        return;
      }
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        await webclient.post('clientes/emails', {cadastro: this.iptId, email: email.trim().toLowerCase()})
        const {data} = await webclient.get(`clientes/emails?cadastro=${this.iptId}`);
        this.tableGenericItems = data;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async cadastrarTelefone() {
      if (!this.dialogCadastrarTelefone) {
        this.dialogCadastrarTelefone = true;
        return;
      }
      if (!this.$refs['form-telefone'].validate()) return;
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        const dados = {
          cadastro: this.iptId,
          numero: StringHelper.extractNumbers(this.iptTelefoneNumero),
          tipo: this.iptTelefoneTipo
        };
        await webclient.post('clientes/telefones', dados);
        const {data} = await webclient.get(`clientes/telefones?cadastro=${this.iptId}`);
        this.tableGenericItems = data;
        this.dialogCadastrarTelefone = false;
      } finally {
        this.tableGenericLoading = false;
      }
    },
    async cadastrarObservacao() {
      const texto = prompt('Digite sua observação');
      if (!texto || texto.trim().length === 0) return;
      this.tableGenericLoading = true;
      try {
        const webclient = http();
        await webclient.post('clientes/observacoes', {cadastro: this.iptId, observacao: texto.trim()});
        const {data} = await webclient.get(`clientes/observacoes?cadastro=${this.iptId}`);
        this.tableGenericItems = data;
      } finally {
        this.tableGenericLoading = false;
      }
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
    },
    dialogCadastrarTelefone(v) {
      if (!v) this.$refs['form-telefone'].reset();
    },
  },
}
</script>
