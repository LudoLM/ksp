<!-- App.vue -->
<script setup>
import AlertMessage from "./components/message/AlertMessage.vue";
import { useUserStore } from "./store/user";
import { useActionsUser } from "./utils/composables/useActionsUser";


const userStore = useUserStore();
// Call api/user
if (userStore.getUserId !== null) {
    const { getUser } = useActionsUser();
    getUser();
}


</script>


<template>
    <AlertMessage style="z-index: 10000" />
    <router-view />
</template>
