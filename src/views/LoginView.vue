<template>
  <v-container class="d-flex flex-column justify-center align-center">
    <v-btn color="primary" fab fixed top right @click="$vuetify.theme.dark = !$vuetify.theme.dark">
      <v-icon>mdi-lightbulb-on</v-icon>
    </v-btn>
    <v-card shaped width="24rem" class="mt-5">
      <v-form ref="form-login" @submit.prevent="submitLogin" :disabled="loadingForm">
        <v-card-text class="pb-0">
          <div class="text-center">
            <v-img src="android-chrome-192x192.png" height="4rem" width="4rem" class="mx-auto mb-3"></v-img>
            <p class="title">{{ config.nome_empresa }}</p>
          </div>
          <v-text-field
            label="Usuário"
            prepend-inner-icon="mdi-account"
            :rules="rulesUsuario"
            :placeholder="!autofilled ? ' ' : undefined"
            :persistent-placeholder="!autofilled"
            autocomplete="username"
            v-model="iptLogin"
            outlined
            dense
          ></v-text-field>
          <v-text-field
            label="Senha"
            class="mt-2"
            prepend-inner-icon="mdi-key"
            :rules="rulesSenha"
            :placeholder="!autofilled ? ' ' : undefined"
            :persistent-placeholder="!autofilled"
            :type="mostrarSenha ? 'text' : 'password'"
            :append-icon="mostrarSenha ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append.stop="mostrarSenha = !mostrarSenha"
            autocomplete="current-password"
            v-model="iptSenha"
            outlined
            dense
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-btn
            color="primary"
            small
            depressed
            block
            type="submit"
            :loading="loadingForm"
          >Entrar</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-container>
</template>

<script>
import http from '@/plugins/axios';
import {config} from '@/config';
export default {
  name: 'LoginView',
  data: () => ({
    config,
    autofilled: false, //Countera o bug do chrome fazer autofill, sobrepondo os labels.
    iptLogin: '',
    iptSenha: '',
    mostrarSenha: false,
    loadingForm: false,
    rulesUsuario: [
      v => !!v || 'Insira o nome de usuário',
      v => (!/\s/g.test(v)) || 'Espaços não são permitidos',
      v => (v && v.length >= 3) || 'O nome está pequeno demais'
    ],
    rulesSenha: [
      v => !!v || 'Insira a senha',
      v => (!/\s/g.test(v)) || 'A senha não pode conter espaços',
      v => (v && v.length >= 4) || 'Precisa ter pelo menos 4 caracteres',
    ],
  }),
  methods: {
    async submitLogin() {
      if (!this.$refs['form-login'].validate()) return;
      this.loadingForm = true;
      try {
        const webclient = http();
        const dados = {'login': this.iptLogin, 'senha': this.iptSenha, 'agente': navigator.userAgent};
        const {data} = await webclient.post('login', dados);
        if (data.token) this.$store.commit('setToken', data.token);
        await this.$router.push('/');
      } finally {
        this.loadingForm = false;
      }
    },
  },
  watch: {
    iptLogin() {
      this.autofilled = true;
    },
  }
}
</script>
