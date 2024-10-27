<template>
  <div class="container">
    <!-- Affichage d'un message de validation -->
    <v-alert v-model="alertVisible" :type="alertType" dismissible>
      {{ alertMessage }}
    </v-alert>
    <h1>prochains cours</h1>

    <div class="buttonsFilters">
      <router-link to="/cours/add"><CustomButton v-if="role === 'ROLE_ADMIN'">Ajouter un cours</CustomButton></router-link>
      <CoursFilters
          :uniqueTypeCours="uniqueTypeCours"
          :selectedCoursId="selectedCoursId"
          :selectedDate="selectedDate"
          @update:selectedCoursId="updateSelectedCoursId"
          @update:selectedDate="updateSelectedDate"
          @resetInfos="resetInfos"
      />
    </div>

    <div class="gridCards">
      <ul>
        <li v-for="info in paginatedInfos" :key="info.id">
          <CoursCard
              :info="info"
              @subscriptionResponse="handleSubscriptionResponse"
              @deleteCoursResponse="handleDeleteCoursResponse"
              @cancelCoursResponse="handleCancelCoursResponse"
          />
        </li>
      </ul>
    </div>
    <div class="pagination">
      <button class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800" @click="prevPage" :disabled="currentPage === 1">Précédent</button>
      <span>Page {{ currentPage }} sur {{ totalPages }}</span>
      <button class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800" @click="nextPage" :disabled="currentPage === totalPages">Suivant</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import CoursCard from "../components/CoursCard.vue";
import {VAlert} from "vuetify/components";
import CoursFilters from "../components/CoursFilters.vue";
import useGetElementsToken from "../utils/useGetElementsToken";
import { useRoute } from "vue-router";
import {useGetCours} from "../utils/useActionCours";
import CustomButton from "../components/CustomButton.vue";

const infos = ref([]);
const selectedCoursId = ref(null);
const selectedDate = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(9);
const route = useRoute();
const role = localStorage.getItem('token') ? useGetElementsToken().roles[0] : null;


// Appel de fetchData lors du montage
onMounted(() => {
  alertMessage.value = route.query.alertMessage || '';
  alertType.value = route.query.alertType || 'success';
  alertVisible.value = route.query.alertVisible || false;

  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);
  useGetCours(role, infos);

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
const updateSelectedCoursId = (value) => {
  selectedCoursId.value = value;
};

const updateSelectedDate = (value) => {
  selectedDate.value = value;
};

// Propriétés calculées pour filtrer et paginer les cours
const uniqueTypeCours = computed(() => {
  const uniqueTypeCours = [];
  const seenIds = new Set();

  infos.value.forEach(info => {
    if (info.typeCours && !seenIds.has(info.typeCours.id)) {
      seenIds.add(info.typeCours.id);
      uniqueTypeCours.push(info.typeCours);
    }
  });

  return uniqueTypeCours;
});

const filteredInfos = computed(() => {
  let filtered = infos.value;

  if (selectedCoursId.value !== null && selectedCoursId.value !== "0") {
    filtered = filtered.filter(info => info.typeCours.id === selectedCoursId.value);
  }

  if (selectedDate.value) {
    filtered = filtered.filter(info => info.dateCours >= selectedDate.value);
  }

  return filtered;
});

const paginatedInfos = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredInfos.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredInfos.value.length / itemsPerPage.value);
});

// Méthodes
const resetInfos = () => {
  selectedCoursId.value = null;
  selectedDate.value = '';
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};
</script>


<style scoped>

.buttonsFilters {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 5rem;
}

</style>
