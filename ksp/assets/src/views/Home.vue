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
      <button @click="prevPage" :disabled="currentPage === 1">Précédent</button>
      <span>Page {{ currentPage }} sur {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages">Suivant</button>
    </div>
  </div>
</template>

<script>
import CoursCard from "../components/CoursCard";
import { ref } from 'vue'

const currentPage = ref(1)

export default {
  name: 'Home',
  components: {
    CoursCard,
  },
  data() {
    return {
      infos: [],
      selectedCoursId: null,
      selectedDate: '',
      currentPage: 1,
      itemsPerPage: 9
    }
  },
  computed: {
    uniqueTypeCours() {
      const uniqueTypeCours = [];
      const seenIds = new Set();

      for (const info of this.infos) {
        if (info.typeCours) {
          const typeCours = info.typeCours;
          if (!seenIds.has(typeCours.id)) {
            seenIds.add(typeCours.id);
            uniqueTypeCours.push(typeCours);
          }
        }
      }

      return uniqueTypeCours;
    },
    filteredInfos() {
      let filtered = this.infos;

      if (this.selectedCoursId !== null && this.selectedCoursId !== "0") {
        filtered = filtered.filter(info => info.typeCours.id === this.selectedCoursId);
      }

      if (this.selectedDate) {
        filtered = filtered.filter(info => info.dateCours >= this.selectedDate);
      }

      return filtered;
    },
    paginatedInfos() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredInfos.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredInfos.length / this.itemsPerPage);
    }
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      fetch("/api/getCours", { method: "GET" })
          .then(response => response.json())
          .then(data => {
            this.infos = data;
          })
          .catch(error => {
            console.error(error);
          });
    },
    resetInfos() {
      this.selectedCoursId = null;
      this.selectedDate = '';
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    }
  }
}
</script>

<style scoped>
/* Ajoutez ici vos styles */
</style>
