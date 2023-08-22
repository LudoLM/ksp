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
    </div>
    <div class="gridCards">
      <ul>
        <div v-for="info in filteredInfos" :key="info.id">
          <li>
            <CoursCard :info="info" />
          </li>
        </div>
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
      if (this.selectedCoursId === null) {
        return this.infos;
      }

      return this.infos.filter(info => info.TypeCours.id === this.selectedCoursId);
    },
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      fetch("/api/getCours", { method: "GET" })
          .then(response => response.json())
          .then(data => {
            this.infos = data.cours;
          })
          .catch(error => {
            console.error(error);
          });
    },
  }
}
</script>
