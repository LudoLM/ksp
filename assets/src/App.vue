<script setup>
import { provide } from 'vue';
import { createAlertStore } from './store/alert';
import AlertMessage from "./components/message/AlertMessage.vue";

const alertStore = createAlertStore();
provide('alertStore', alertStore);
</script>

<template>
    <AlertMessage style="z-index: 10000" />
    <router-view />
</template>
