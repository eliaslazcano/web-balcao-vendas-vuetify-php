<template>
  <async-container :loading="loading">
    <v-card width="64rem" class="mx-auto mb-4">
      <v-card-title class="justify-space-between">
        {{ id ? 'Venda Nº' + id : 'Registrar Venda' }}
        <v-menu left bottom offset-y class="d-print-none" v-if="rfidDisponivel">
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon v-bind="attrs" v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list class="py-0" dense>
            <v-list-item @click="dialogRfid = true">
              <v-list-item-icon>
                <v-icon>mdi-contactless-payment-circle</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Vincular RFID</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-card-title>
      <v-card-subtitle v-if="!!criado_em">Criado em {{ moment(criado_em).format('DD/MM/YYYY HH:mm') }}</v-card-subtitle>
      <v-card-text>
        <v-form @submit.prevent :disabled="enviandoVenda">
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
                label="Cliente"
                v-model="iptCliente"
                :readonly="!!cadastro || !!iptClienteId"
                placeholder="Nome do cliente"
                persistent-placeholder
                :append-outer-icon="cadastro ? undefined : 'mdi-account-search'"
                @click:append-outer="dialogPesquisarCadastro = true"
                @keydown="(x) => {if (x.code === 'F2' && !cadastro) dialogPesquisarCadastro = true}"
                :hint="$vuetify.breakpoint.mdAndUp && !cadastro ? 'Pressione F2 para buscar um cliente cadastrado' : undefined"
                @click:clear="desvincularCadastro"
                :clearable="!cadastro"
                :autofocus="$vuetify.breakpoint.mdAndUp"
                :append-icon="cadastro ? 'mdi-open-in-new' : undefined"
                @click:append="$router.push('/cliente/' + cadastro)"
                outlined
                dense
              ></v-text-field>
            </v-col>
          </v-row>
          <v-text-field label="Nota" v-model="iptNota" class="mt-2" outlined dense placeholder="Nenhuma observação" persistent-placeholder></v-text-field>
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
          label="Adicionar produto"
          v-model="iptProduto"
          :items="iptProdutoItems"
          item-value="id"
          item-text="nome"
          no-data-text="Nenhum produto correspondente"
          placeholder="Nome ou código do produto"
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
        :dense="tableDense"
        :mobile-breakpoint="0"
        disable-pagination
        hide-default-footer
        no-data-text="Nenhum produto adicionado"
        sort-by="produto_nome"
      >
        <template v-slot:[`item.quantidade`]="{item}">
          <div class="d-flex flex-nowrap">
            <v-icon color="red" size="20" @click="() => { item.quantidade--; onUpdateQuantidadeOuValorUnitario(item, false) }">mdi-minus-box</v-icon>
            <v-edit-dialog
              :return-value.sync="item.quantidade"
              large
              cancel-text="Cancelar"
              save-text="Salvar"
              @save="onUpdateQuantidadeOuValorUnitario(item)"
            >
              <span class="mx-3">{{ item.quantidade }}</span>
              <template v-slot:input>
                <text-field-monetary
                  v-model="item.quantidade"
                  single-line
                  :rules="[v => !!v || 'Insira a quantidade']"
                  :options="{precision: 0}"
                ></text-field-monetary>
              </template>
            </v-edit-dialog>
            <v-icon color="blue" size="20" @click="() => { item.quantidade++; onUpdateQuantidadeOuValorUnitario(item, false) }">mdi-plus-box</v-icon>
          </div>
        </template>
        <template v-slot:[`item.valor_un`]="{item}">
          <v-edit-dialog
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
        </template>
        <template v-slot:[`item.valor`]="{item}">
          <v-edit-dialog
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
            <td></td>
            <td colspan="2">
              <p class="subtitle-2 primary--text mb-0 text-no-wrap">R$ {{ formatoMonetario(valorTotal) }}</p>
            </td>
          </tr>
          </tfoot>
        </template>
      </v-data-table>
    </v-card>
    <div class="text-center my-4">
      <v-btn small color="success" :loading="enviandoVenda" @click="salvarVenda">
        <v-icon dense class="mr-1">mdi-content-save</v-icon> Salvar
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
    criado_em: null,
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
        this.iptProdutoItems = data;
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
        await webclient.post('vendas', payload);

        if (this.id) this.$store.commit('showSnackbar', {color: 'success', text: 'Venda atualizada!'});
        else this.$store.commit('showSnackbar', {color: 'success', text: 'Venda registrada!'});

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
        this.dialogLoadingText = 'Carregando todas as biometrias na memória..';
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
    },
    formatoMonetario(valor) {
      return StringHelper.monetaryFormat(valor);
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
    onUpdateQuantidadeOuValorUnitario(item, emitirSnack = true) {
      if (item.quantidade < 0) item.quantidade = 0;
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
  }
}
</script>
