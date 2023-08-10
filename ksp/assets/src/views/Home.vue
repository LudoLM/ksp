<template>
  <div class="home">
    <h1>Kiné Sports Santé à Chavagne (35) : la méthode simple et efficace pour vous débarrasser de vos douleurs !</h1>
  </div>
  <div class="container">
    <div v-for="info in infos" :key="info.id">
      <p>hello</p>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src
import HelloWorld from '../components/HelloWorld.vue'

export default {
  name: 'Home',
  components: {
    HelloWorld
  },
  data() {
    return {
      infos: []
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
            console.log(data);
          })
          .catch(error => {
            console.error(error);
          });
    }
  }
}
</script>
