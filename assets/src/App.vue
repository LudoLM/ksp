<script setup>
import {provide, watch} from 'vue';
import { createAlertStore } from './store/alert';
import AlertMessage from "./components/message/AlertMessage.vue";
import {useUserStore} from "./store/user";

const alertStore = createAlertStore();
provide('alertStore', alertStore);

</script>

<template>
    <AlertMessage style="z-index: 10000" />
    <router-view />
</template>
