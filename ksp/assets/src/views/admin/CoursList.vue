<script setup>

import {onMounted, ref} from "vue";
import {useGetCours} from "../../utils/useActionCours";
const coursList = ref([]);
const infos = ref([]);
const selectedCoursId = ref(null);
const selectedDate = ref(null);
const selectedStatusId = ref(null);
const currentPage = ref(1);
const totalItems = ref(0);
const maxPerPage = ref(window.innerWidth > 1460 ? 20 : window.innerWidth > 1110 ? 12 : 10);
const totalPages = ref(1);
const isAdminPath = route.path.startsWith('/admin');

onMounted(async () => {
  await useGetCours(isAdminPath, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
});


</script>

<template>

    <h1>Liste de cours</h1>


</template>

<style scoped lang="scss">

</style>
