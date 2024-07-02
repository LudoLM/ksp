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
        <li v-for="info in filteredInfos" :key="info.id">
          <CoursCard :info="info" />
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import HelloWorld from '../components/HelloWorld.vue'
import CoursCard from "../components/CoursCard";

export default {
  name: 'Home',
  components: {
    CoursCard,
    HelloWorld
  },
  data() {
    return {
      infos: [],
      selectedCoursId: null,
      selectedDate: ''
    }
  },
  computed: {
    uniqueTypeCours() {
      const uniqueTypeCours = [];
      const seenIds = new Set();

      for (const info of this.infos) {
        if (info.TypeCours) {
          const typeCours = info.TypeCours;
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
        filtered = filtered.filter(info => info.TypeCours.id === this.selectedCoursId);
      }

      if (this.selectedDate) {
        filtered = filtered.filter(info => info.dateCours >= this.selectedDate);
      }

      return filtered;
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
    }
  }
}
</script>

<style scoped>
/* Ajoutez ici vos styles */
</style>
