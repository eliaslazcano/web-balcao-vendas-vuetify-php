<template>
  <div :class="{'fill-height align-center justify-center grow d-flex': centralizar}">
    <slot name="header"></slot>
    <template v-if="items && items.length > 0">
      <template v-if="card">
        <v-card
          v-for="(item, index) in itemsComputed"
          :key="item.id ? item.id : index"
          :to="item.to"
          :href="item.href"
          :target="item.href ? '_blank' : undefined"
          :class="item.class ? item.class : undefined"
          @click="itemClick(item)"
          class="mb-3 mx-auto"
          :max-width="maxWidth"
          :outlined="outlined"
        >
          <v-list :dense="dense" :class="listClass">
            <v-list-item>
              <v-list-item-avatar v-if="item.icon" :color="item.iconColor ? item.iconColor : 'primary'">
                <v-icon color="white">mdi-{{ item.icon }}</v-icon>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title v-if="item.title">{{ item.title }}</v-list-item-title>
                <v-list-item-subtitle v-if="item.subtitle">{{ item.subtitle }}</v-list-item-subtitle>
                <p v-if="item.text" class="text-body-2 ma-0">{{item.text}}</p>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </template>
      <template v-else>
        <v-card class="mx-auto" :max-width="maxWidth" :outlined="outlined">
          <v-list two-line class="py-0" :dense="dense" :class="listClass">
            <template v-for="(item, index) in itemsComputed">
              <v-list-item
                :to="item.to"
                :key="item.id ? item.id : index"
                :href="item.href"
                :target="item.href ? '_blank' : undefined"
                :class="item.class ? item.class : undefined"
                @click="itemClick(item)"
              >
                <v-list-item-avatar v-if="item.icon" :color="item.iconColor ? item.iconColor : 'primary'">
                  <v-icon color="white">mdi-{{ item.icon }}</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title v-if="item.title">{{ item.title }}</v-list-item-title>
                  <v-list-item-subtitle v-if="item.subtitle">{{ item.subtitle }}</v-list-item-subtitle>
                  <p v-if="item.text" class="text-body-2 ma-0">{{item.text}}</p>
                </v-list-item-content>
              </v-list-item>
              <v-divider v-if="divider && index < (itemsComputed.length - 1)" :key="(item.id ? item.id : index) + '_divider'"></v-divider>
            </template>
          </v-list>
        </v-card>
      </template>
    </template>
    <p v-else-if="!!noData" class="text-center">{{ noData }}</p>
    <slot name="footer"></slot>
  </div>
</template>

<script>
export default {
  name: 'ListView',
  props: {
    items: {type: Array, required: true}, // {to, icon, title, subtitle, text, href, class}
    divider: {type: Boolean, default: true},
    dense: {type: Boolean, default: false},
    card: {type: Boolean, default: false},
    outlined: {type: Boolean, default: false},
    noData: {type: String, default: null},
    maxWidth: {type: [Number, String]},
    listClass: {type: String, default: ''},
  },
  computed: {
    itemsComputed() {
      const items = this.items;
      return items.sort((a, b) => a.title === b.title ? 0 : (a.title > b.title ? 1 : -1));
    },
    centralizar() {
      return this.noData && this.items.length === 0;
    },
  },
  methods: {
    itemClick(item) {
      if (item.callback) item.callback();
      this.$emit('click', item);
    },
  }
}
</script>
