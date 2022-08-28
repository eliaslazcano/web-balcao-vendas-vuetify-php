<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto mb-4">
      <v-card-title class="justify-space-between">
        {{ id ? 'Venda Nº' + id : 'Registrar Venda' }}
        <v-menu left bottom offset-y class="d-print-none" v-if="encerrado_em !== null || rfidDisponivel">
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon v-bind="attrs" v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list v-if="encerrado_em !== null" class="py-0" dense>
            <v-list-item @click="desbloquearVenda">
              <v-list-item-icon>
                <v-icon>mdi-lock-open</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Desbloquear a venda</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
          <v-list v-if="rfidDisponivel && !encerrado_em" class="py-0" dense>
            <v-list-item @click="dialogRfid = true">
              <v-list-item-icon>
                <v-icon>mdi-access-point-plus</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Vincular dispositivo de aproximação</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item @click="dialogRfidLista = true">
              <v-list-item-icon>
                <v-icon>mdi-contactless-payment-circle</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Dispositivos de vinculados</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-card-title>
      <v-card-subtitle v-if="!!criado_em">Criado em {{ moment(criado_em).format('DD/MM/YYYY HH:mm') }}</v-card-subtitle>
      <v-card-text>
        <v-alert v-if="visitas >= 1" type="info" dense text>Esta {{ id ? 'é' : 'será' }} a {{ visitas }}ª venda deste cliente</v-alert>
        <v-alert v-if="encerrado_em !== null" type="warning" dense text icon="mdi-lock">Venda encerrada em {{ moment(encerrado_em).format('DD/MM/YYYY HH:mm') }}</v-alert>
        <v-form @submit.prevent :disabled="enviandoVenda || encerrandoVenda">
          <v-row dense>
            <v-col cols="4" sm="3" md="2">
              <v-text-field
                label="Cod. Cliente"
                :value="cadastro || iptClienteId"
                :hint="cadastro || iptClienteId ? '' : 'Não possui'"
                persistent-placeholder
                placeholder="N/P"
                persistent-hint
                outlined
                dense
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="8" sm="9" md="10">
              <v-text-field
                v-if="!cadastro"
                label="Cliente"
                v-model="iptCliente"
                placeholder="Nome do cliente"
                persistent-placeholder
                append-outer-icon="mdi-account-search"
                @click:append-outer="dialogPesquisarCadastro = true"
                @keydown="(x) => {if (x.code === 'F2') dialogPesquisarCadastro = true}"
                :hint="$vuetify.breakpoint.mdAndUp ? 'Pressione F2 para buscar um cliente cadastrado' : undefined"
                clearable
                @click:clear="desvincularCadastro"
                :autofocus="$vuetify.breakpoint.mdAndUp"
                :readonly="!!iptClienteId || encerrado_em !== null"
                outlined
                dense
              ></v-text-field>
              <v-text-field
                v-else
                label="Cliente"
                v-model="iptCliente"
                placeholder="Sem nome"
                persistent-placeholder
                readonly
                outlined
                dense
              >
                <template v-slot:append-outer>
                  <v-tooltip left>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon small color="primary" @click="$router.push('/cliente/' + cadastro)" v-bind="attrs" v-on="on">
                        <v-icon>mdi-account-eye</v-icon>
                      </v-btn>
                    </template>
                    <span>Ver cadastro</span>
                  </v-tooltip>
                </template>
              </v-text-field>
            </v-col>
          </v-row>
          <v-text-field
            label="Nota"
            v-model="iptNota"
            class="mt-2"
            outlined
            dense
            placeholder="Nenhuma observação"
            persistent-placeholder
            :readonly="encerrado_em !== null"
          ></v-text-field>
          <text-field-monetary
            label="Valor Pago"
            v-model="iptCredito"
            type="tel"
            prefix="R$"
            outlined
            dense
            :hint="(valorTotal > 0 && iptCredito < valorTotal) ? 'Falta pagar R$ ' + formatoMonetario(valorTotal - iptCredito) : undefined"
            persistent-hint
            :success="!!valorTotal && !!iptCredito && valorTotal <= iptCredito"
            :readonly="encerrado_em !== null"
          ></text-field-monetary>
        </v-form>
      </v-card-text>
    </v-card>
    <v-card width="64rem" class="mx-auto">
      <v-card-title class="justify-space-between">
        Lista de Produtos
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
      <v-card-text class="pb-0">
        <v-autocomplete
          v-if="encerrado_em === null"
          label="Adicionar produto"
          v-model="iptProduto"
          :items="iptProdutoItems"
          item-value="id"
          item-text="nome"
          no-data-text="Nenhum produto correspondente"
          placeholder="Nome ou código do produto"
          prepend-inner-icon="mdi-plus-circle"
          :disabled="enviandoVenda || encerrandoVenda"
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
        :dense="tableDense"
        :mobile-breakpoint="0"
        disable-pagination
        hide-default-footer
        no-data-text="Nenhum produto adicionado"
        sort-by="produto_nome"
      >
        <template v-slot:[`item.quantidade`]="{item}">
          <div class="d-flex flex-nowrap" v-if="encerrado_em === null">
            <v-icon color="red" size="20" @click="() => { item.quantidade--; onUpdateQuantidadeOuValorUnitario(item, false) }">mdi-minus-box</v-icon>
            <v-edit-dialog
              :return-value.sync="item.quantidade"
              large
              cancel-text="Cancelar"
              save-text="Salvar"
              @save="onUpdateQuantidadeOuValorUnitario(item)"
            >
              <span class="mx-3 d-block text-center" style="min-width: 2rem">{{ item.quantidade }}</span>
              <template v-slot:input>
                <text-field-monetary
                  v-model="item.quantidade"
                  single-line
                  :rules="[v => !!v || 'Insira a quantidade']"
                  :options="{precision: 0}"
                  :min="1"
                ></text-field-monetary>
              </template>
            </v-edit-dialog>
            <v-icon color="blue" size="20" @click="() => { item.quantidade++; onUpdateQuantidadeOuValorUnitario(item, false) }">mdi-plus-box</v-icon>
          </div>
          <span v-else>{{ item.quantidade }}</span>
        </template>
        <template v-slot:[`item.valor_un`]="{item}">
          <v-edit-dialog
            v-if="encerrado_em === null"
            :return-value.sync="item.valor_un"
            large
            cancel-text="Cancelar"
            save-text="Salvar"
            @save="onUpdateQuantidadeOuValorUnitario(item)"
          >
            <span style="white-space: nowrap">R$ {{ item.valor_un ? formatoMonetario(item.valor_un) : '0,00' }}</span>
            <template v-slot:input>
              <text-field-monetary
                v-model="item.valor_un"
                single-line
                :rules="[v => !!v || 'Insira o valor unitário']"
                prefix="R$"
              ></text-field-monetary>
            </template>
          </v-edit-dialog>
          <span v-else style="white-space: nowrap">R$ {{ item.valor_un ? formatoMonetario(item.valor_un) : '0,00' }}</span>
        </template>
        <template v-slot:[`item.valor`]="{item}">
          <v-edit-dialog
            v-if="encerrado_em === null"
            :return-value.sync="item.valor"
            large
            cancel-text="Cancelar"
            save-text="Salvar"
            @save="onUpdateValorTotal(item)"
          >
            <span style="white-space: nowrap">R$ {{ item.valor ? formatoMonetario(item.valor) : '0,00' }}</span>
            <template v-slot:input>
              <text-field-monetary
                v-model="item.valor"
                single-line
                :rules="[v => !!v || 'Insira o valor']"
                prefix="R$"
              ></text-field-monetary>
            </template>
          </v-edit-dialog>
          <span v-else style="white-space: nowrap">R$ {{ item.valor ? formatoMonetario(item.valor) : '0,00' }}</span>
        </template>
        <template v-slot:[`item.acoes`]="{item}">
          <v-icon color="red" @click="removerItem(item)" :disabled="encerrado_em !== null">mdi-close-circle</v-icon>
        </template>
        <template v-slot:foot>
          <tfoot>
          <tr>
            <td colspan="3">
              <p class="subtitle-2 primary--text mb-0">TOTAL</p>
            </td>
            <td colspan="2">
              <p class="subtitle-2 primary--text mb-0 text-no-wrap">R$ {{ formatoMonetario(valorTotal) }}</p>
            </td>
          </tr>
          </tfoot>
        </template>
      </v-data-table>
    </v-card>
    <div class="text-center my-4" v-if="encerrado_em === null">
      <v-btn color="success" small :loading="enviandoVenda" :disabled="encerrandoVenda" @click="salvarVenda">
        <v-icon dense class="mr-1">mdi-content-save</v-icon>
        {{ id ? 'Salvar alterações' : 'Salvar' }}
      </v-btn>
      <v-btn v-if="id" color="error" class="ml-2" small :disabled="enviandoVenda || encerrandoVenda" @click="dialogEncerrar = true">
        <v-icon dense class="mr-1">mdi-lock</v-icon>
        Encerrar
      </v-btn>
    </div>
    <v-dialog v-model="dialogPesquisarCadastro" width="40rem">
      <v-card>
        <v-card-title>Pesquisar Cliente</v-card-title>
        <v-card-text class="px-3">
          <p class="subtitle-2 blue--text">
            <v-icon>{{ $vuetify.breakpoint.xs ? 'mdi-gesture-tap' : 'mdi-cursor-default-click' }}</v-icon>
            Clique no cliente desejado da lista
          </p>
          <v-text-field
            label="Filtrar"
            prepend-inner-icon="mdi-magnify"
            v-model="tableCadastrosSearch"
            @keydown="(x) => {if (x.code === 'F2') pesquisarCadastroPorDigital()}"
            :autofocus="$vuetify.breakpoint.mdAndUp"
            placeholder="Nome ou código"
            persistent-placeholder hide-details dense outlined
          >
            <template v-slot:append-outer>
              <v-btn color="primary" small @click="pesquisarCadastroPorDigital" v-if="existeCadastroComDigital && biometriaDisponivel">
                <v-icon>mdi-fingerprint</v-icon>
              </v-btn>
            </template>
          </v-text-field>
        </v-card-text>
        <v-divider class="mb-1"></v-divider>
        <v-data-table
          :headers="tableCadastrosHeaders"
          :items="tableCadastrosItems"
          :search="tableCadastrosSearch"
          :loading="tableCadastrosLoading"
          :footer-props="{'items-per-page-options': [5, 10, 15]}"
          :mobile-breakpoint="0"
          no-data-text="Nenhum cliente cadastrado"
          no-results-text="Nenhum cliente encontrado"
          sort-by="nome"
          @click:row="selecionarCadastro"
        ></v-data-table>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogRfid" width="24rem" :persistent="vinculandoRfid">
      <v-card>
        <v-form @submit.prevent="vincularRfid" :disabled="vinculandoRfid || !id">
          <v-card-text class="pb-0">
            <v-alert type="error" v-if="!id">Você precisa gravar a venda primeiro!</v-alert>
            <div class="text-center mb-3">
              <v-avatar color="primary" size="96">
                <v-icon size="64" color="white">mdi-contactless-payment</v-icon>
              </v-avatar>
            </div>
            <p class="text-center title">Vincular dispositivo de aproximação</p>
            <v-text-field
              label="Identificador RFID"
              prepend-inner-icon="mdi-contactless-payment"
              placeholder="Encoste o dispositivo no sensor"
              hint="Aproxime!"
              v-model="iptRfid"
              outlined
              dense
              autofocus
            ></v-text-field>
          </v-card-text>
          <v-card-actions class="justify-center">
            <v-btn color="secondary" small depressed @click="dialogRfid = false" :loading="vinculandoRfid">Fechar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogRfidLista" width="40rem" :persistent="vinculandoRfid">
      <v-card>
        <v-card-title style="font-size: 1rem">Dispositivos de aproximação vinculados</v-card-title>
        <v-divider></v-divider>
        <v-data-table
          :headers="tableRfidHeaders"
          :items="tableRfiditems"
          :loading="vinculandoRfid"
          no-data-text="Nenhum dispositivo vinculado a esta venda"
        >
          <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
          <template v-slot:[`item.acoes`]="{item}">
            <v-icon color="red" @click="desvincularRfid(item.rfid)" :disabled="vinculandoRfid">mdi-close-circle</v-icon>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn color="primary" small depressed @click="dialogRfidLista = false">Fechar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEncerrar" width="32rem" :persistent="encerrandoVenda">
      <v-card>
        <v-toolbar color="error" dense dark flat>
          <v-toolbar-title>Encerrar a venda</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-toolbar-items>
            <v-btn @click="dialogEncerrar = false" icon>
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <v-card-text class="pb-0">
          <div class="text-center my-4">
            <v-icon size="64" color="error">mdi-shield-lock</v-icon>
          </div>
          <p class="body-1 text-justify">Isso torna a venda <strong class="primary--text">protegida</strong>
            para não sofrer mais nenhuma <strong class="primary--text">alteração</strong>. Mas ela vai permanecer
            <span class="green--text">disponível para consulta</span>.</p>
          <p class="body-1 red--text text-center">Tem certeza que deseja prosseguir?</p>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn color="primary" small depressed outlined :disabled="encerrandoVenda" @click="dialogEncerrar = false">Cancelar</v-btn>
          <v-btn color="error" small depressed :loading="encerrandoVenda" @click="encerrarVenda">Prosseguir</v-btn>
        </v-card-actions>
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
import StringHelper from '@/helper/StringHelper';
import {config} from '@/config';
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
    visitas: null, //numero que representa a sequencia desta venda dentre todas as vendas deste cliente.
    criado_em: null,
    encerrado_em: null,
    iptClienteId: null,
    iptCliente: '',
    iptNota: '',
    iptCredito: '0',
    iptProduto: null,
    iptProdutoItems: [],
    tableHeaders: [
      {value: 'produto_nome', text: 'PRODUTO', cellClass: 'text-no-wrap'},
      {value: 'quantidade', text: 'QUANTIDADE'},
      {value: 'valor_un', text: 'VALOR UN.', cellClass: 'text-no-wrap'},
      {value: 'valor', text: 'TOTAL', cellClass: 'text-no-wrap'},
      {value: 'acoes', text: 'REMOVER', align: 'center', cellClass: 'text-no-wrap', sortable: false, filterable: false},
    ],
    tableItems: [],
    tableSearch: '',
    tableDense: false,
    enviandoVenda: false,
    dialogPesquisarCadastro: false,
    biometriaDisponivel: config.biometria,
    rfidDisponivel: config.rfid,
    tableCadastrosHeaders: [
      {value: 'id', text: 'COD.', width: '6rem', cellClass: 'cursor-pointer'},
      {value: 'nome', text: 'NOME', cellClass: 'cursor-pointer text-no-wrap'},
      {value: 'vendas', text: 'VENDAS', cellClass: 'cursor-pointer', filterable: false},
    ],
    tableCadastrosItems: [],
    tableCadastrosSearch: '',
    tableCadastrosLoading: false,
    dialogLoading: false,
    dialogLoadingText: '',
    itemsRemover: [],
    dialogRfid: false,
    iptRfid: '',
    vinculandoRfid: false,
    dialogRfidLista: false,
    tableRfidHeaders: [
      {value: 'criado_em', text: 'VINCULADO EM', cellClass: 'text-no-wrap'},
      {value: 'rfid', text: 'DISPOSITIVO'},
      {value: 'descricao', text: 'DESCRIÇÃO'},
      {value: 'acoes', text: 'DESVINCULAR', align: 'center', sortable: false, filterable: false},
    ],
    tableRfiditems: [],
    dialogEncerrar: false,
    encerrandoVenda: false,
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
        this.iptProdutoItems = data.produtos;
        if (this.id) {
          const {data} = await webclient.get(`vendas?id=${this.id}`);
          this.cadastro = data.cadastro;
          this.visitas = data.visita;
          this.criado_em = data.criado_em;
          this.encerrado_em = data.encerrado_em;
          this.iptCliente = data.cliente;
          this.iptNota = data.nota;
          this.iptCredito = data.credito;
          this.tableItems = data.itens;
        } else {
          this.cadastro = null;
          this.visitas = null;
          this.criado_em = null;
          this.encerrado_em = null;
          this.iptCliente = '';
          this.iptNota = '';
          this.iptCredito = '0';
          this.tableItems = [];
        }
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
      this.tableCadastrosItems = data.clientes;
      this.tableCadastrosLoading = false;
    },
    async salvarVenda() {
      if (this.tableItems.length === 0 && !confirm('A venda está vazia, sem produto nenhum. Tem certeza?')) return;
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
        const {data} = await webclient.post('vendas', payload);

        if (this.id) this.$store.commit('showSnackbar', {color: 'success', text: 'Alterações salvas!'});
        else this.$store.commit('showSnackbar', {color: 'success', text: 'Venda registrada!'});

        if (!this.id && data.id) await this.$router.push('/venda/' + data.id);
        else await this.$router.push('/vendas');
      } finally {
        this.enviandoVenda = false;
      }
    },
    async encerrarVenda() {
      if (!this.id) return;
      this.encerrandoVenda = true;
      try {
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
        await webclient.post('vendas', payload); //primeiro atualiza a venda
        await webclient.put('vendas', {'id': this.id});
        this.encerrado_em = moment().format('YYYY-MM-DD HH:mm:ss');
        this.dialogEncerrar = false;
      } finally {
        this.encerrandoVenda = false;
      }
    },
    async desbloquearVenda() {
      if (!this.id) return;
      if (!confirm('A venda já estava encerrada para não ser modificada, tem certeza que é para desbloquear?')) return;
      this.encerrandoVenda = true;
      try {
        const webclient = http();
        await webclient.delete('vendas', {params: {'id': this.id}});
        this.encerrado_em = null;
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Você desbloqueou a venda'});
      } finally {
        this.encerrandoVenda = false;
      }
    },
    iptProdutoFiltro(item, queryText, itemText) {
      if (item.codigo && (item.codigo === queryText)) return true;
      if (item.codigo && item.codigo.replace(/\D/g,'').length > 0 && parseInt(item.codigo) === parseInt(queryText)) return true;
      return itemText.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
    },
    selecionarCadastro(item) {
      this.iptClienteId = item.id;
      this.iptCliente = item.nome;
      this.visitas = item.vendas + 1;
      this.dialogPesquisarCadastro = false;
    },
    desvincularCadastro() {
      this.iptClienteId = null;
      this.iptCliente = '';
      this.visitas = null;
    },
    async pesquisarCadastroPorDigital() {
      if (!this.existeCadastroComDigital || !this.biometriaDisponivel) return;
      const biometriaNitgen = new BiometriaNitgen();
      try {
        const cadastros = this.tableCadastrosItems.filter(i => !!i.digital).map(i => ({id: i.id, digital: i.digital}));
        this.dialogLoading = true;
        this.dialogLoadingText = 'Carregando todas as biometrias na memória..';
        const resultado = await biometriaNitgen.identificar(cadastros);
        if (resultado === 0) alert('Nenhum cadastro pôde ser encontrado com esta digital escaneada.');
        else if (resultado > 0) {
          const cadastro = this.tableCadastrosItems.find(i => i.id === resultado);
          if (cadastro) this.selecionarCadastro(cadastro);
        }
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
    },
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
    },
    async loadRfids() {
      if (this.id) {
        this.vinculandoRfid = true;
        try {
          const webclient = http();
          const {data} = await webclient.put('venda_rfids', {venda: this.id});
          this.tableRfiditems = data;
        } finally {
          this.vinculandoRfid = false;
        }
      }
    },
    async vincularRfid() {
      if (!this.rfidDisponivel || !this.iptRfid) return;
      this.vinculandoRfid = true;
      try {
        const webclient = http();
        const {data} = await webclient.get(`venda_rfids?rfid=${this.iptRfid}`);
        if (data && data.venda !== Number(this.id)) {
          const duracao = this.moment.duration(this.moment().diff(this.moment(data.criado_em))).humanize();
          const clienteStr = data.cliente ? ` do cliente "${data.cliente}"` : '';
          const txt = `Atenção!\nO dispositivo apresentado está atualmente vinculado uma venda${clienteStr} a aproximadamente ${duracao} atrás.\nSe você continuar irá DESVINCULAR daquela venda, tem certeza?`;
          if (window.confirm(txt)) await webclient.delete(`venda_rfids?rfid=${this.iptRfid}`);
          else return;
        }
        const {data: r} = await webclient.post('venda_rfids', {rfid: this.iptRfid, venda: this.id});
        if (r.mensagem) this.$store.commit('showSnackbar', {color: 'warning', text: r.mensagem});
        else this.$store.commit('showSnackbar', {color: 'success', text: 'Dispositivo vinculado!'});
      } finally {
        this.dialogRfid = false;
        this.vinculandoRfid = false;
      }
    },
    async desvincularRfid(rfid) {
      if (!confirm('Deseja desvincular esse RFID?')) return;
      this.vinculandoRfid = true;
      try {
        const webclient = http();
        await webclient.delete(`venda_rfids?rfid=${rfid}`);
        await this.loadRfids();
      } finally {
        this.vinculandoRfid = false;
      }
    },
    onUpdateQuantidadeOuValorUnitario(item, emitirSnack = true) {
      if (item.quantidade < 1) item.quantidade = 1;
      item.valor = item.valor_un * item.quantidade;
      if (emitirSnack) this.$store.commit('showSnackbar', {color: 'info', text: 'VALOR TOTAL FOI RECALCULADO'});
    },
    onUpdateValorTotal(item) {
      item.valor_un = parseFloat(Number(item.valor / item.quantidade).toFixed(2));
      const novoValor = item.valor_un * item.quantidade;
      if (novoValor === item.valor) this.$store.commit('showSnackbar', {color: 'info', text: 'VALOR UNITÁRIO FOI RECALCULADO'});
      else {
        this.$nextTick(() => setTimeout(() => {
          item.valor = item.valor_un * item.quantidade;
          this.$store.commit('showSnackbar', {
            color: 'info',
            text: 'VALOR TOTAL E UNITÁRIO FORAM RECALCULADOS PARA O MAIS PRÓXIMO POSSÍVEL'
          });
        }, 600));
      }
    }
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
            'id': null,
            'produto': produto.id,
            'produto_nome': produto.nome,
            'quantidade': 1,
            'valor_un': produto.valor,
            'valor': produto.valor,
          });
        } else {
          this.tableItems[pedidoItemIndex].quantidade = parseFloat(this.tableItems[pedidoItemIndex].quantidade) + 1;
          this.tableItems[pedidoItemIndex].valor = parseFloat(this.tableItems[pedidoItemIndex].valor) + parseFloat(this.tableItems[pedidoItemIndex].valor_un);
        }
        this.$nextTick(() => this.iptProduto = null);
      }
    },
    id() {
      this.loadData();
    },
    dialogPesquisarCadastro(v) {
      if (v && this.tableCadastrosItems.length === 0) this.loadCadastros();
    },
    dialogRfid(v) {
      if (!v) this.iptRfid = '';
    },
    dialogRfidLista(v) {
      if (v) this.loadRfids();
    },
  }
}
</script>
