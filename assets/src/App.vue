<script setup>
import {provide, watch} from 'vue';
import { createAlertStore } from './store/alert';
import AlertMessage from "./components/message/AlertMessage.vue";
import {useUserStore} from "./store/user";
import {getUser} from "./utils/useActionUser";

const alertStore = createAlertStore();
provide('alertStore', alertStore);

const userStore = useUserStore();
// Call api/user
if (userStore.getUserId !== null) {
    getUser();
}


</script>

<template>
    <AlertMessage style="z-index: 10000" />
    <router-view />
</template>
