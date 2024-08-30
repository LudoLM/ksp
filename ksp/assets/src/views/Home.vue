<template>
  <div class="home">
    <h1>Kiné Sports Santé à Chavagne (35) : la méthode simple et efficace pour vous débarrasser de vos douleurs !</h1>
  </div>
  <div class="container">
    <div id="form-wrapper" ref="form">
      <label for="listeCours">Cours</label>
      <select name="listeCours" id="listeCours" v-model="selectedCoursId">
        <option value="0">Choisissez un cours</option>
        <option v-for="typeCours in uniqueTypeCours" :key="typeCours.id" :value="typeCours.id">{{ typeCours.libelle }}</option>
      </select>
      <label for="dateCours">Date</label>
      <input type="date" name="dateCours" id="dateCours" v-model="selectedDate">
      <button @click="resetInfos">Reset</button>
    </div>

    <div class="gridCards">
      <ul>
        <li v-for="info in paginatedInfos" :key="info.id">
          <CoursCard :info="info" />
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

// Propriétés calculées
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
/* Ajoutez ici vos styles */
</style>
