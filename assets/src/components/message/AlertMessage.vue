<script setup>
import { computed } from 'vue';
import { alertStore } from '../../store/alert';

const state = computed(() => alertStore.state);
</script>

<template>
    <v-alert v-if="state.visible" :type="state.type" dismissible @click:close="alertStore.clearAlert()">
        {{ state.message }}
    </v-alert>
</template>
