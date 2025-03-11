<script setup>
import { inject, computed } from 'vue';

const alertStore = inject('alertStore');

if (!alertStore) {
  console.error('Alert store is not provided');
}
const state = computed(() => alertStore.state);
</script>

<template>
  <v-alert v-if="state.visible" :type="state.type" dismissible>
    {{ state.message }}
  </v-alert>
</template>
