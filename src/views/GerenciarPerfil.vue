<template>
  <async-container :loading="loading">
    <v-card width="24rem" class="mx-auto" :loading="loadingCardPerfil">
      <v-form ref="form-perfil" :disabled="loadingCardPerfil" @submit.prevent="submitPerfil">
        <v-card-title>Seu perfil</v-card-title>
        <v-card-text>
          <v-text-field label="Nome" v-model="iptNome" outlined dense :rules="rulesNome"></v-text-field>
          <v-text-field label="Login" v-model="iptLogin" outlined dense :rules="rulesLogin"></v-text-field>
          <v-text-field label="E-mail" v-model="iptEmail" outlined dense :rules="rulesEmail" placeholder="Não possui" persistent-placeholder></v-text-field>
        </v-card-text>
        <v-card-actions class="justify-center pt-0">
          <v-btn small depressed color="primary" type="submit" :disabled="loadingCardPerfil">Salvar alterações</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
    <v-card width="24rem" class="mx-auto my-5" :loading="loadingCardSenha">
      <v-form ref="form-senha" :disabled="loadingCardSenha" @submit.prevent="submitSenha">
        <v-card-title>Trocar senha</v-card-title>
        <v-card-text>
          <v-text-field
            label="Senha atual"
            v-model="iptSenhaAtual"
            outlined
            dense
            type="password"
            autocomplete="current-password"
            :rules="[v => (!!v && !!v.trim()) || 'Informe a senha atual']"
          ></v-text-field>
          <v-text-field
            label="Senha nova"
            v-model="iptSenhaNova"
            outlined
            dense
            type="password"
            autocomplete="new-password"
            :rules="rulesSenha"
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="justify-center pt-0">
          <v-btn small depressed color="primary" outlined type="submit" :disabled="loadingCardSenha">Substituir</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </async-container>
</template>

<script>
import AsyncContainer from '@/components/AsyncContainer';
import http from '@/plugins/axios';
export default {
  name: 'GerenciarPerfil',
  components: {AsyncContainer},
  data: () => ({
    webclient: http(),
    loading: true,
    loadingCardPerfil: false,
    loadingCardSenha: false,
    iptNome: '',
    iptLogin: '',
    iptEmail: '',
    iptSenhaAtual: '',
    iptSenhaNova: '',
    rulesNome: [
      v => (!!v && !!v.trim()) || 'Informe o nome',
    ],
    rulesLogin: [
      v => (!!v && !!v.trim()) || 'Informe o login',
      v => (!!v && (!/\s/g.test(v))) || 'Não usar espaços',
    ],
    rulesEmail: [
      v => !v || /.+@.+\..+/.test(v) || 'E-mail deve ser válido',
    ],
    rulesSenha: [
      v => (!!v && !!v.trim()) || 'Informe a senha nova',
      v => (!!v && (!/\s/g.test(v))) || 'Não usar espaços',
      v => (v && v.length >= 4) || 'Precisa ter pelo menos 4 caracteres',
    ],
  }),
  methods: {
    async loadData() {
      let success = null;
      this.loadingCardPerfil = true;
      try {
        const {data} = await this.webclient.get('usuarios');
        this.iptNome = data.nome ? data.nome : '';
        this.iptLogin = data.login;
        this.iptEmail = data.email ? data.email : '';
        success = true;
      } catch (e) {
        success = false;
      } finally {
        this.loadingCardPerfil = false;
        this.loading = false;
      }
      return success;
    },
    async submitPerfil() {
      if (!this.$refs['form-perfil'].validate()) return;
      this.loadingCardPerfil = true;
      try {
        const dados = {
          'nome': this.iptNome.trim().toUpperCase(),
          'login': this.iptLogin.trim().toLowerCase(),
          'email': this.iptEmail.trim().toLowerCase()
        };
        await this.webclient.post('usuarios', dados);
        await this.loadData();
        this.$store.commit('showSnackbar', {color: 'success', text: 'As alterações foram salvas!'});
      } finally {
        this.loadingCardPerfil = false;
      }
    },
    async submitSenha() {
      if (!this.$refs['form-senha'].validate()) return;
      this.loadingCardSenha = true;
      try {
        await this.webclient.put('usuarios', {atual: this.iptSenhaAtual, nova: this.iptSenhaNova});
        this.$store.commit('showSnackbar', {color: 'success', text: 'Senha trocada!'});
        this.$refs['form-senha'].reset();
      } finally {
        this.loadingCardSenha = false;
      }
    },
  },
  async created() {
    const r = await this.loadData();
    if (!r) await this.$router.push('/');
  }
}
</script>
