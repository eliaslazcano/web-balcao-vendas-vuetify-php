<template>
  <div>
    <v-text-field
      type="tel"
      v-model="cmpValue"
      v-bind:label="label"
      v-bind:placeholder="placeholder"
      v-bind:persistent-placeholder="persistentPlaceholder"
      v-bind:readonly="readonly"
      v-bind:disabled="disabled"
      v-bind:outlined="outlined"
      v-bind:dense="dense"
      v-bind:hide-details="hideDetails"
      v-bind:error="error"
      v-bind:rules="rules"
      v-bind:clearable="clearable"
      v-bind:backgroundColor="backgroundColor"
      v-bind:prefix="prefix"
      v-bind:suffix="suffix"
      v-bind:hint="hint"
      v-bind:persistent-hint="persistentHint"
      v-bind:success="success"
      v-on:keypress="keyPress"
    ></v-text-field>
  </div>
</template>

<script>
export default {
  model: { prop: 'value', event: 'input' },
  props: {
    value: {type: [String, Number], default: '0'},
    label: { type: String, default: ''},
    placeholder: {type: String, default: undefined},
    persistentPlaceholder: {type: Boolean, default: false},
    hint: {type: String, default: undefined},
    persistentHint: {type: Boolean, default: false},
    readonly: {type: Boolean, default: false},
    dense: {type: Boolean, default: false},
    error: {type: Boolean, default: false},
    hideDetails: {type: [Boolean, String], default: false},
    rules: {type: [Array, String], default: () => []},
    disabled: {type: Boolean, default: false},
    outlined: {type: Boolean, default: false},
    clearable: {type: Boolean, default: false},
    backgroundColor: {type: String, default: undefined},
    prefix: {type: String, default: undefined},
    suffix: {type: String, default: undefined},
    success: {type: Boolean, default: false},
    valueWhenIsEmpty: {type: String, default: ''}, // "0" or "" or null
    options: {
      type: Object,
      default: function() {
        return {
          locale: "pt-BR",
          length: 11,
          precision: 2
        };
      },
    },
  },
  data: () => ({}),
  computed: {
    cmpValue: {
      get: function() {
        return this.value !== null && this.value !== '' ? this.humanFormat(this.value.toString()) : this.valueWhenIsEmpty;
      },
      set: function(newValue) {
        this.$emit('input', Number(this.machineFormat(newValue)));
      },
    },
  },
  methods: {
    humanFormat: function(number) {
      if (isNaN(number)) number = '';
      else {
        // number = Number(number).toLocaleString('pt-BR', {maximumFractionDigits: 2, minimumFractionDigits: 2});
        number = Number(number).toLocaleString(this.options.locale, {
          maximumFractionDigits: this.options.precision,
          minimumFractionDigits: this.options.precision,
        });
      }
      return number;
    },
    machineFormat(number) {
      if (number) {
        number = this.cleanNumber(number);
        number = number.padStart(parseInt(this.options.precision) + 1, "0");
        number =
          number.substring(0, number.length - parseInt(this.options.precision)) +
          "." +
          number.substring(number.length - parseInt(this.options.precision), number.length);
        if (isNaN(number)) number = this.valueWhenIsEmpty;
      } else {
        number = this.valueWhenIsEmpty;
      }
      if (this.options.precision === 0) {
        number = this.cleanNumber(number);
      }
      return number;
    },
    keyPress($event) {
      // console.log($event.keyCode); //keyCodes value
      let keyCode = $event.keyCode ? $event.keyCode : $event.which;
      // if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
      if (keyCode < 48 || keyCode > 57) {
        // 46 is dot
        $event.preventDefault();
      }
      if (this.targetLength()) {
        $event.preventDefault();
      }
    },
    cleanNumber: function(value) {
      let result = "";
      if (value) {
        let flag = false;
        let arrayValue = value.toString().split("");
        for (let i = 0; i < arrayValue.length; i++) {
          if (this.isInteger(arrayValue[i])) {
            if (!flag) {
              if (arrayValue[i] !== "0") {
                result = result + arrayValue[i];
                flag = true;
              }
            } else {
              result = result + arrayValue[i];
            }
          }
        }
      }
      return result;
    },
    isInteger(value) {
      let result = false;
      if (Number.isInteger(parseInt(value))) {
        result = true;
      }
      return result;
    },
    targetLength() {
      return Number(this.cleanNumber(this.value).length) >= Number(this.options.length);
    },
  },
};
</script>
