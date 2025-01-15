<template>
  <div class="container">
    <!-- Affichage d'un message de validation -->
    <v-alert v-model="alertVisible" :type="alertType" dismissible>
      {{ alertMessage }}
    </v-alert>
   <HeroBanner v-if="!isAdminPath"/>
    <div class="title_wrapper">
      <h2>{{ title }}</h2>
    </div>


    <div class="buttonsFilters">
      <router-link :to="{name: 'CreateCours'}"><CustomButton v-if="isAdminPath">Ajouter un cours</CustomButton></router-link>
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
              :isAdminPath="isAdminPath"
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
import {ref, onMounted, computed, watch} from 'vue';
import CoursCard from "../components/CoursCard.vue";
import {VAlert} from "vuetify/components";
import CoursFilters from "../components/CoursFilters.vue";
import { useRoute } from "vue-router";
import {useGetCours, useGetStatusCours, useGetTypesCours} from "../utils/useActionCours";
import CustomButton from "../components/CustomButton.vue";
import HeroBanner from "../components/user/HeroBanner.vue";

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
const isAdminPath = computed(() => route.path.startsWith('/admin'));
const title = isAdminPath ? 'Liste des cours' : 'Les cours à venir';


// Appel de fetchData lors du montage
onMounted(async () => {
  alertMessage.value = route.query.alertMessage || '';
  alertType.value = route.query.alertType || 'success';
  alertVisible.value = route.query.alertVisible === 'true';

  setTimeout(() => {
    alertVisible.value = false;
  }, 5000);


  await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  uniqueTypeCoursList.value = await useGetTypesCours();
  uniqueStatusCoursList.value = await useGetStatusCours();
  if (!isAdminPath.value) {
      uniqueStatusCoursList.value = uniqueStatusCoursList.value.filter(status => status.id !== 4 && status.id !== 6 && status.id !== 7);
  }
});

watch(isAdminPath, async () => {
    uniqueStatusCoursList.value = await useGetStatusCours();
    if (!isAdminPath.value) {
        uniqueStatusCoursList.value = uniqueStatusCoursList.value.filter(status => status.id !== 4 && status.id !== 6 && status.id !== 7);
    }
    await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
});

watch(() => route.query, () => {
    window.location.reload();
});

// Déclaration des variables pour l'alerte
const alertVisible = ref(false);
const alertType = ref('success');
const alertMessage = ref('');

// Fonction pour gérer l'événement subscriptionResponse
const handleSubscriptionResponse = ({ type, message }) => {
  alertType.value = type;
  alertMessage.value = message;
  alertVisible.value = true;

  // Masquer l'alerte après 3 secondes
  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);
};

const handleDeleteCoursResponse = ({ type, message, id }) => {
  alertType.value = type;
  alertMessage.value = message;
  alertVisible.value = true;

  // Masquer l'alerte après 3 secondes
  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);

  // Supprimer le cours de la liste
  infos.value = infos.value.filter(info => info.id !== id);
};

const handleCancelCoursResponse = ({ type, message }) => {
  alertType.value = type;
  alertMessage.value = message;
  alertVisible.value = true;

  // Masquer l'alerte après 3 secondes
  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);
};


// Mise à jour des filtres lorsqu'un événement est reçu
const updateSelectedCoursList = async (value) => {
  selectedCoursId.value = value;
  currentPage.value = 1;
  await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

const updateSelectedDateList = async (value) => {
  selectedDate.value = value;
  currentPage.value = 1;
  await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

const updateStatusCoursList = async (value) => {
  selectedStatusId.value = value;
  currentPage.value = 1;
  await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};


// Méthodes pour la pagination
const resetInfos = () => {
  selectedCoursId.value = null;
  selectedDate.value = '';
};
const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  }
};

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    await useGetCours(isAdminPath.value, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
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
