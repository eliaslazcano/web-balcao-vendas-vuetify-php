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
        <div class="px-4 pb-4">
          <v-btn
            color="primary"
            small
            depressed
            block
            type="submit"
            :loading="loadingForm"
          >Entrar</v-btn>
          <v-btn
            v-if="config.smtp"
            outlined
            small
            depressed
            block
            class="mt-3"
            :disabled="loadingForm"
            @click="dialogRecuperarSenha = true"
          >Esqueci minha senha</v-btn>
        </div>
      </v-form>
    </v-card>
    <v-dialog v-model="dialogRecuperarSenha" persistent width="24rem">
      <v-card>
        <v-stepper v-model="stepperRecuperarSenha">
          <v-stepper-items>
            <v-stepper-content step="1" class="pa-0">
              <v-form ref="form-passwdrequest" @submit.prevent="solicitarRecuperacaoDeSenha" :disabled="dialogRecuperarSenhaLoading">
                <v-card-title>Solicitar recuperação de senha</v-card-title>
                <v-card-subtitle>Enviaremos um código de segurança para o seu e-mail</v-card-subtitle>
                <v-card-text class="pb-0">
                  <v-text-field
                    label="Usuário"
                    prepend-inner-icon="mdi-account"
                    :rules="rulesUsuario"
                    :placeholder="!autofilled ? ' ' : undefined"
                    :persistent-placeholder="!autofilled"
                    autocomplete="username"
                    v-model="iptUsuarioRecuperacao"
                    outlined
                    dense
                  ></v-text-field>
                </v-card-text>
                <v-card-actions class="justify-center">
                  <v-btn
                    color="primary"
                    small
                    outlined
                    :disabled="dialogRecuperarSenhaLoading"
                    @click="dialogRecuperarSenha = false"
                  >Cancelar</v-btn>
                  <v-btn
                    color="primary"
                    small
                    depressed
                    type="submit"
                    :loading="dialogRecuperarSenhaLoading"
                  >Solicitar</v-btn>
                </v-card-actions>
              </v-form>
            </v-stepper-content>
            <v-stepper-content step="2" class="pa-0">
              <v-form ref="form-passwdcode" @submit.prevent="alterarSenha" :disabled="dialogRecuperarSenhaLoading">
                <v-card-text class="pb-0">
                  <v-alert type="info" dense :icon="false" class="text-justify">
                    {{ dialogRecuperarSenhaMsg }}<br/>Insira o código abaixo.
                  </v-alert>
                  <v-text-field
                    label="Código de segurança"
                    placeholder="Coloque o código aqui"
                    persistent-placeholder
                    type="tel"
                    outlined
                    dense
                    counter
                    maxlength="6"
                    :rules="iptCodigoRecuperacaoRules"
                    v-model="iptCodigoRecuperacao"
                  ></v-text-field>
                  <v-text-field
                    label="Sua nova senha"
                    placeholder="Sua nova senha aqui"
                    persistent-placeholder
                    class="mt-2"
                    prepend-inner-icon="mdi-key"
                    :rules="rulesSenha"
                    :type="mostrarSenha ? 'text' : 'password'"
                    :append-icon="mostrarSenha ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append.stop="mostrarSenha = !mostrarSenha"
                    autocomplete="new-password"
                    v-model="iptSenhaNova"
                    outlined
                    dense
                  ></v-text-field>
                </v-card-text>
                <v-card-actions class="justify-center">
                  <v-btn
                    color="primary"
                    small
                    outlined
                    :disabled="dialogRecuperarSenhaLoading"
                    @click="dialogRecuperarSenha = false"
                  >Cancelar</v-btn>
                  <v-btn
                    color="primary"
                    small
                    depressed
                    type="submit"
                    :loading="dialogRecuperarSenhaLoading"
                  >Confirmar</v-btn>
                </v-card-actions>
              </v-form>
            </v-stepper-content>
          </v-stepper-items>
        </v-stepper>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import http from '@/plugins/axios';
import {config} from '@/config';
export default {
  name: 'LoginView',
  data: () => ({
    config,
    webclient: http(),
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

    dialogRecuperarSenha: false,
    dialogRecuperarSenhaLoading: false,
    dialogRecuperarSenhaMsg: '',
    stepperRecuperarSenha: 1,
    iptUsuarioRecuperacao: '',
    iptCodigoRecuperacao: '',
    iptCodigoRecuperacaoRules: [
      v => (!!v && !!v.trim()) || 'Este campo é obrigatório',
      v => (!!v && !v.replace(/\d/g,'')) || 'Digite somente números',
      v => (!!v && v.length === 6) || 'Complete os 6 digitos'
    ],
    iptSenhaNova: '',
  }),
  methods: {
    async submitLogin() {
      if (!this.$refs['form-login'].validate()) return;
      this.loadingForm = true;
      try {
        const dados = {'login': this.iptLogin, 'senha': this.iptSenha, 'agente': navigator.userAgent};
        const {data} = await this.webclient.post('login', dados);
        if (data.token) this.$store.commit('setToken', data.token);
        await this.$router.push('/');
      } finally {
        this.loadingForm = false;
      }
    },
    async solicitarRecuperacaoDeSenha() {
      if (!this.$refs['form-passwdrequest'].validate()) return;
      this.dialogRecuperarSenhaLoading = true;
      try {
        const {data} = await this.webclient.get('recuperar_senha', {params: {'login': this.iptUsuarioRecuperacao}});
        this.dialogRecuperarSenhaMsg = data.mensagem;
        this.stepperRecuperarSenha = 2;
      } finally {
        this.dialogRecuperarSenhaLoading = false;
      }
    },
    async alterarSenha() {
      if (!this.$refs['form-passwdcode'].validate()) return;
      this.dialogRecuperarSenhaLoading = true;
      try {
        await this.webclient.post('recuperar_senha', {codigo: this.iptCodigoRecuperacao, senha: this.iptSenhaNova});
        this.iptLogin = this.iptUsuarioRecuperacao;
        this.iptSenha = this.iptSenhaNova;
        this.dialogRecuperarSenha = false;
        this.$store.commit('showSnackbar', {color: 'success', text: 'Senha trocada!'});
      } finally {
        this.dialogRecuperarSenhaLoading = false;
      }
    },
  },
  watch: {
    iptLogin() {
      this.autofilled = true;
    },
    dialogRecuperarSenha(v) {
      if (!v) {
        if (this.$refs['form-passwdrequest']) this.$refs['form-passwdrequest'].reset();
        if (this.$refs['form-passwdcode']) this.$refs['form-passwdcode'].reset();
        this.stepperRecuperarSenha = 1;
      }
    }
  }
}
</script>
