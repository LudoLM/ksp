<template>
  <div class="container">
    <div class="title_wrapper">
      <h2>{{ title }}</h2>
    </div>


    <div class="buttonsFilters">
      <router-link :to="{name: 'CreateCours'}"><CustomButton>Ajouter un cours</CustomButton></router-link>
      <CoursFilters
          :uniqueTypeCours="uniqueTypeCoursList"
          :uniqueStatusCours="uniqueStatusCoursList"
          :selectedCoursId="selectedCoursId"
          :selectedDate="selectedDate"
          :selectedStatusId="selectedStatusId"
          @update:selectedCoursId="updateSelectedCoursList"
          @update:selectedDate="updateSelectedDateList"
          @update:selectedStatusId="updateStatusCoursList"
          @resetInfos="resetInfos"
      />
    </div>

    <div class="gridCards p-12">
      <ul v-if="totalItems > 0">
        <li v-for="info in infos" :key="info.id">
          <CoursCard
              :info="info"
              @subscriptionResponse="handleSubscriptionResponse"
              @deleteCoursResponse="handleDeleteCoursResponse"
              @cancelCoursResponse="handleCancelCoursResponse"
          />
        </li>
      </ul>
      <p v-else class="flex justify-center font-bold">Il n'y a pas de cours correspondant à la recherche</p>
    </div>
    <div class="pagination">
      <CustomButton :class="currentPage === 1 ? 'invisible' : 'visible'" :color="'gray'" @click="prevPage" :disabled="currentPage === 1">Précédent</CustomButton>
      <span>Page {{ currentPage }} sur {{ totalPages }}</span>
      <CustomButton :class="currentPage === totalPages ? 'invisible' : 'visible'" :color="'gray'" @click="nextPage" :disabled="currentPage === totalPages">Suivant</CustomButton>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, watch, inject} from 'vue';
import CoursCard from "../components/CoursCard.vue";
import CoursFilters from "../components/CoursFilters.vue";
import { useRoute } from "vue-router";
import {useGetCours, useGetStatusCours, useGetTypesCours} from "../utils/useActionCours";
import CustomButton from "../components/CustomButton.vue";

const infos = ref([]);
const selectedCoursId = ref(null);
const selectedDate = ref(null);
const selectedStatusId = ref(null);
const currentPage = ref(1);
const uniqueTypeCoursList = ref([]);
const uniqueStatusCoursList = ref([]);
const route = useRoute();
const totalItems = ref(0);
const maxPerPage = ref(window.innerWidth > 1460 ? 20 : window.innerWidth > 1110 ? 12 : 10);
const totalPages = ref(1);
const alertStore = inject('alertStore');
const title = 'Liste des cours';
const routeGetCours = ref("getCours");



// Appel de fetchData lors du montage
onMounted(async () => {

  await useGetCours(routeGetCours, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  uniqueTypeCoursList.value = await useGetTypesCours();
  uniqueStatusCoursList.value = await useGetStatusCours();
});


watch(() => route.query, () => {
    window.location.reload();
});


// Fonction pour gérer l'événement subscriptionResponse
const handleSubscriptionResponse = ({ type, message }) => {
    alertStore.setAlert(message, type);
};

const handleDeleteCoursResponse = ({ type, message, id }) => {
    alertStore.setAlert(message, type);
    // Supprimer le cours de la liste
    infos.value = infos.value.filter(info => info.id !== id);
};

const handleCancelCoursResponse = ({ type, message }) => {
    alertStore.setAlert(message, type);
};


// Mise à jour des filtres lorsqu'un événement est reçu
const updateSelectedCoursList = async (value) => {
  selectedCoursId.value = value;
  currentPage.value = 1;
  await useGetCours(routeGetCours, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

const updateSelectedDateList = async (value) => {
  selectedDate.value = value;
  currentPage.value = 1;
  await useGetCours(routeGetCours, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

const updateStatusCoursList = async (value) => {
  selectedStatusId.value = value;
  currentPage.value = 1;
  await useGetCours(routeGetCours, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};


// Méthodes pour la pagination
const resetInfos = () => {
  selectedCoursId.value = null;
  selectedDate.value = '';
};
const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    await useGetCours(routeGetCours, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  }
};

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    await useGetCours(routeGetCours, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  }
};
</script>


<style scoped>

.container {
  min-width: 100%;
}

.buttonsFilters {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 5rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 5rem;
  margin-bottom: 5rem;
  padding: 0 5rem;
    button {
      width: 200px;
    }
}
</style>
