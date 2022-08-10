<template>
  <div style="height: 100%">
    <transition
      mode="out-in"
      :name="loading ? transition : spinnerTransition"
      :appear="appear"
      :leave-class="loading ? transition + '-leave' : spinnerTransition + '-leave'"
      :leave-to-class="loading ? transition + '-leave-to' : spinnerTransition + '-leave-to'"
      :leave-active-class="loading ? transition + '-leave-active' : spinnerTransition + '-leave-active'"
      :enter-class="loading ? spinnerTransition + '-enter' : transition + '-enter'"
      :enter-to-class="loading ? spinnerTransition + '-enter-to' : transition + '-enter-to'"
      :enter-active-class="loading ? spinnerTransition + '-enter-active' : transition + '-enter-active'"
      @after-leave="$emit('after-leave', loading)"
      @after-enter="$emit('after-enter', loading)"
    >
      <div v-if="loading" :key="1" class="d-flex flex-column grow justify-center align-center" style="height: 100%">
        <v-progress-circular indeterminate :color="color"></v-progress-circular>
        <p class="mb-0 mt-2 grey--text text--darken-1" v-if="text">{{ text }}</p>
      </div>
      <v-container v-else :key="2" :fluid="fluid" :class="{'pa-0': noPadding}">
        <slot/>
      </v-container>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'AsyncContainer',
  props: {
    loading: {type: Boolean, default: false},
    color: {type: String, default: 'blue'},
    transition: {type: String, default: 'scroll-x-transition'},
    spinnerTransition: {type: String, default: 'fade-transition'},
    fluid: {type: Boolean, default: false},
    appear: {type: Boolean, default: true},
    text: {type: String, default: ''},
    noPadding: {type: Boolean, default: false},
  },
}
</script>
