<template>
  <async-container :loading="loading">
    <v-alert type="info" dense text>
      Você não precisa cadastrar os dispositivos nesta lista para usa-los no sistema, este procedimento é opcional.
    </v-alert>
    <v-card>
      <v-card-title>Dispositivos de aproximação</v-card-title>
      <v-card-subtitle>Monitoramento dos dispositivos</v-card-subtitle>
      <v-card-text v-if="tableItems.length > 0">
        <v-text-field label="Filtrar" placeholder="Dispositivo ou descrição" persistent-placeholder v-model="tableSearch" prepend-inner-icon="mdi-magnify" outlined dense hide-details></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="tableHeaders"
        :items="tableItems"
        :loading="tableLoading"
        :search="tableSearch"
        no-data-text="Nenhum dispositivo cadastrado"
      >
        <template v-slot:[`item.venda_atual`]="{item}">
          <router-link v-if="!!item.venda_atual" :to="'/venda/' + item.venda_atual">VENDA Nº{{ item.venda_atual }}</router-link>
          <span v-else class="grey--text">NÃO</span>
        </template>
        <template v-slot:[`item.ultimo_uso`]="{item}">
          <span v-if="!!item.ultimo_uso">{{ moment(item.ultimo_uso).format('DD/MM/YYYY HH:mm') }}</span>
          <span v-else class="grey--text">NUNCA</span>
        </template>
        <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
        <template v-slot:[`item.acoes`]="{item}">

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small color="primary" @click="verHistorico(item.id)" v-bind="attrs" v-on="on">
                <v-icon>mdi-text-box-search</v-icon>
              </v-btn>
            </template>
            <span>Ver histórico</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small color="red" @click="desvincularRfid(item)" v-bind="attrs" v-on="on" :disabled="!item.venda_atual">
                <v-icon>mdi-connection</v-icon>
              </v-btn>
            </template>
            <span>Desvincular uso</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small color="red" @click="deletarRfid(item.id)" v-bind="attrs" v-on="on" :disabled="!!item.venda_atual">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
            <span>Deletar cadastro</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>
    <v-btn color="success" fab fixed right bottom @click="dialogCadastrarDispositivo = true">
      <v-icon>mdi-plus</v-icon>
    </v-btn>
    <v-dialog v-model="dialogHistorico" width="64rem">
      <v-card>
        <v-card-title>Histórico de uso</v-card-title>
        <v-card-subtitle>Vinculações deste dispositivo</v-card-subtitle>
        <v-divider></v-divider>
        <v-data-table
          :headers="genericTableHeaders"
          :items="genericTableItems"
          :loading="genericTableLoading"
          no-data-text="Nenhum uso até o momento"
          sort-by="criado_em"
          sort-desc
        >
          <template v-slot:[`item.criado_em`]="{item}">{{ moment(item.criado_em).format('DD/MM/YYYY HH:mm') }}</template>
          <template v-slot:[`item.desvinculado_em`]="{item}">
            <span v-if="!!item.desvinculado_em">{{ moment(item.desvinculado_em).format('DD/MM/YYYY HH:mm') }}</span>
          </template>
          <template v-slot:[`item.venda`]="{item}">
            <router-link :to="'/venda/' + item.venda">{{ item.venda }}</router-link>
          </template>
        </v-data-table>
        <v-card-actions class="justify-center">
          <v-btn small depressed color="primary" @click="dialogHistorico = false">Fechar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogCadastrarDispositivo" width="32rem" :persistent="genericTableLoading">
      <v-card>
        <v-card-title>Cadastrar dispositivo</v-card-title>
        <v-card-text>
          <v-form ref="form-add-rfid" :disabled="genericTableLoading" @submit.prevent>
            <v-text-field label="Descrição" outlined dense v-model="iptDescricao"></v-text-field>
            <v-text-field label="RFID" outlined dense v-model="iptRfid" :rules="iptRfidRules" placeholder="Encoste o dispositivo no sensor" prepend-inner-icon="mdi-contactless-payment"></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions class="justify-center pt-0">
          <v-btn color="secondary" depressed small @click="dialogCadastrarDispositivo" :disabled="genericTableLoading">Cancelar</v-btn>
          <v-btn color="primary" depressed small @click="cadastrarRfid" :loading="genericTableLoading">Salvar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
import moment from '@/plugins/moment';
export default {
  name: 'RfidsLista',
  components: {AsyncContainer},
  data: () => ({
    moment,
    loading: true,
    webclient: http(),
    tableHeaders: [
      {value: 'rfid', text: 'DISPOSITIVO'},
      {value: 'descricao', text: 'DESCRICAO'},
      {value: 'venda_atual', text: 'EM USO', filterable: false},
      {value: 'usos', text: 'TOTAL DE USOS', filterable: false},
      {value: 'ultimo_uso', text: 'ÚLTIMO USO', filterable: false},
      {value: 'criado_em', text: 'CADASTRADO EM', filterable: false},
      {value: 'acoes', text: 'OPÇÕES', filterable: false, sortable: false},
    ],
    tableItems: [],
    tableLoading: false,
    tableSearch: '',
    dialogHistorico: false,
    genericTableHeaders: [],
    genericTableItems: [],
    genericTableLoading: false,
    dialogCadastrarDispositivo: false,
    iptDescricao: '',
    iptRfid: '',
  }),
  computed: {
    iptRfidRules() {
      const itemJaCadastrado = (v) => this.tableItems.find(i => i.rfid === v);
      return [
        v => !!v && !!v.trim() || 'Falta o código gerado ao aproximar',
        (v) => !itemJaCadastrado(v) || 'Esse dispositivo já está cadastrado'
      ];
    },
  },
  methods: {
    async loadData() {
      let success;
      this.tableLoading = true;
      try {
        const {data} = await this.webclient.get('dispositivos_rfid');
        this.tableItems = data;
        success = true;
      } catch (e) {
        success = false;
      } finally {
        this.tableLoading = false;
        this.loading = false;
      }
      return success;
    },
    async verHistorico(itemId) {
      this.genericTableHeaders = [
        {value: 'criado_em', text: 'VINCULADO EM'},
        {value: 'desvinculado_em', text: 'DESVINCULADO EM'},
        {value: 'venda', text: 'VENDA Nº'},
        {value: 'cliente', text: 'CLIENTE'},
      ];
      this.genericTableLoading = true;
      this.dialogHistorico = true;
      try {
        const {data} = await this.webclient.post('dispositivos_rfid', {id: itemId});
        this.genericTableItems = data;
      } finally {
        this.genericTableLoading = false;
      }
    },
    async desvincularRfid(item) {
      if (!confirm(`Desvincular esse dispositivo de VENDA Nº${item.venda_atual} ?`)) return;
      this.tableLoading = true;
      try {
        await this.webclient.delete(`venda_rfids?rfid=${item.rfid}`);
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'success', text: 'Dispositivo desvinculado!'});
      } finally {
        this.tableLoading = false;
      }
    },
    async cadastrarRfid() {
      if (!this.$refs['form-add-rfid'].validate()) return;
      this.genericTableLoading = true;
      try {
        await this.webclient.put('dispositivos_rfid', {rfid: this.iptRfid, descricao: this.iptDescricao.trim().toUpperCase()});
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'success', text: 'Dispositivo cadastrado!'});
        this.dialogCadastrarDispositivo = false;
      } finally {
        this.genericTableLoading = false;
      }
    },
    async deletarRfid(itemId) {
      if (!confirm('Tem certeza que deseja remover o cadastro deste dispositivo? Ele ainda irá funcionar normalmente.')) return;
      this.tableLoading = true;
      try {
        await this.webclient.delete('dispositivos_rfid', {params: {id: itemId}});
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'primary', text: 'Dispositivo removido!'});
      } finally {
        this.tableLoading = false;
      }
    }
  },
  created() {
    const s = this.loadData();
    if (!s) this.$router.push('/');
  },
  watch: {
    dialogCadastrarDispositivo(v) {
      if (!v) this.$refs['form-add-rfid'].reset();
    },
  }
}
</script>
