<template>
  <div class="container">
    <!-- Affichage d'un message de validation -->
    <v-alert v-model="alertVisible" :type="alertType" dismissible>
      {{ alertMessage }}
    </v-alert>
    <h1>prochains cours</h1>

    <div class="buttonsFilters">
      <div class="home">
        <button>
          <router-link to="/cours/add" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Ajouter un cours</router-link>
        </button>
      </div>
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
          <CoursCard :info="info" @subscriptionResponse="handleSubscriptionResponse"/>
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

// Déclarations des refs et données
const infos = ref([]);
const selectedCoursId = ref(null);
const selectedDate = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(9);

// Fetch des données au montage du composant
const fetchData = async () => {
  try {
    const response = await fetch("/api/getCours", { method: "GET" });
    infos.value = await response.json();
  } catch (error) {
    console.error("Erreur lors de la récupération des cours:", error);
  }
};

// Appel de fetchData lors du montage
onMounted(() => {
  fetchData();
});

// Déclaration des variables pour l'alerte
const alertVisible = ref(false);
const alertType = ref('success');
const alertMessage = ref('');

// Fonction pour gérer l'événement subscriptionResponse
const handleSubscriptionResponse = ({ type, message }) => {
  alertType.value = type;      // 'success' ou 'error'
  alertMessage.value = message; // Message à afficher
  alertVisible.value = true;    // Afficher l'alerte

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

  .v-alert {
    opacity: .9;
    position: fixed;
    z-index: 10;
    top: 5%;
    left: 10%;
    right: 10%;
    height: 10%;
  }

  h1 {
    font-size: 4rem;
    font-weight: 900;
    font-style: italic;
    margin-bottom: 1rem;
  }
</style>
