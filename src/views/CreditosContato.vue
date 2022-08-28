<template>
  <v-container>
    <v-card width="32rem" class="mx-auto">
      <v-card-title>Créditos</v-card-title>
      <v-card-text>
        <v-list>
          <v-list-item>
            <v-list-item-icon>
              <v-icon>mdi-account-tie</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Desenvolvedor</v-list-item-title>
              <v-list-item-subtitle>Elias Lazcano Castro Neto</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <v-list-item-icon>
              <v-icon>mdi-email</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>E-mail</v-list-item-title>
              <v-list-item-subtitle>elias304162@gmail.com</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <v-list-item-icon>
              <v-icon>mdi-phone</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Telefone</v-list-item-title>
              <v-list-item-subtitle>(67) 9 8172-9464</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-row dense class="mt-1">
          <v-col cols="12" sm="4">
            <v-btn
              outlined
              block
              color="blue"
              dark
              href="https://www.linkedin.com/in/elias-lazcano-castro-neto-852361146"
              style="text-transform: none"
              target="_blank"
            >
              <v-icon class="mr-1">mdi-linkedin</v-icon> LinkedIn
            </v-btn>
          </v-col>
          <v-col cols="12" sm="4">
            <v-btn
              outlined
              block
              color="green"
              dark
              href="https://api.whatsapp.com/send?phone=556182290606"
              style="text-transform: none"
              target="_blank"
            >
              <v-icon class="mr-1">mdi-whatsapp</v-icon> WhatsApp
            </v-btn>
          </v-col>
          <v-col cols="12" sm="4">
            <v-btn
              outlined
              block
              href="https://github.com/eliaslazcano"
              style="text-transform: none"
              target="_blank"
            >
              <v-icon class="mr-1">mdi-github</v-icon> Github
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: 'CreditosContato'
}
</script>
