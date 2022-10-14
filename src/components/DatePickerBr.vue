<template>
  <v-menu
    v-if="inline"
    ref="menu"
    v-model="modal"
    :close-on-content-click="false"
    :return-value.sync="date"
    transition="scale-transition"
    offset-y
    min-width="auto"
  >
    <template v-slot:activator="{ on, attrs }">
      <v-text-field
        :label="label"
        :value="dateFormatted"
        :rules="rules"
        :disabled="disabled"
        :outlined="outlined"
        :dense="dense"
        :hide-details="hideDetails"
        :prepend-inner-icon="prependInnerIcon ? prependInnerIcon : undefined"
        readonly
        v-bind="attrs"
        v-on="on"
      ></v-text-field>
    </template>
    <v-date-picker v-model="date" scrollable no-title :max="maxHoje ? hoje : null" @input="noButtons ? save() : undefined">
      <v-spacer></v-spacer>
      <div v-if="!noButtons">
        <v-btn text color="primary" @click="modal = false">Cancelar</v-btn>
        <v-btn text color="primary" @click="save">OK</v-btn>
      </div>
    </v-date-picker>
  </v-menu>
  <v-dialog v-else ref="dialog" v-model="modal" :return-value.sync="date" persistent width="290px">
    <template v-slot:activator="{ on, attrs }">
      <v-text-field
        :label="label"
        :value="dateFormatted"
        :rules="rules"
        :disabled="disabled"
        :outlined="outlined"
        :dense="dense"
        :hide-details="hideDetails"
        :prepend-inner-icon="prependInnerIcon ? prependInnerIcon : undefined"
        readonly
        v-bind="attrs"
        v-on="on"
      ></v-text-field>
    </template>
    <v-date-picker v-model="date" scrollable :max="maxHoje ? hoje : null" @input="noButtons ? save() : undefined">
      <v-spacer></v-spacer>
      <div v-if="!noButtons">
        <v-btn text color="primary" @click="modal = false">Cancelar</v-btn>
        <v-btn text color="primary" @click="save">OK</v-btn>
      </div>
    </v-date-picker>
  </v-dialog>
</template>

<script>
import moment from '@/plugins/moment';
export default {
  name: 'DatePickerBr',
  props: {
    'inline': {default: false, type: Boolean},
    'noButtons': {default: false, type: Boolean},
    'outlined': {default: false, type: Boolean},
    'dense': {default: false, type: Boolean},
    'disabled': {default: false, type: Boolean},
    'hideDetails': {default: false, type: Boolean},
    'prependInnerIcon': {default: null, type: String},
    'label': {default: '', type: String},
    'value': {default: '', type: String},
    'rules': {default: () => [], type: Array},
    'maxHoje': {default: false, type: Boolean},
  },
  data: () => ({
    modal: false,
    date: moment().format('YYYY-MM-DD'),
    hoje: moment().format('YYYY-MM-DD'),
  }),
  computed: {
    dateFormatted() {
      return moment(this.date).format('DD/MM/YYYY');
    }
  },
  methods: {
    save() {
      if (this.inline) this.$refs.menu.save(this.date);
      else this.$refs.dialog.save(this.date);
      this.$emit('input', this.date);
    },
  },
  watch: {
    value(v) {
      if (v !== this.date) this.date = v;
    },
  },
  created() {
    if (!this.value) this.$emit('input', this.date);
    else this.date = this.value;
  },
}
</script>
