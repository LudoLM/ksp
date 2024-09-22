<template>
  <div class="logo">
    <img src="../../images/logo.png" alt="logo de kine sport santé">
  </div>
  <div :class="direction" id="nav_links">
    <router-link v-for="route in routes" :key="route.path" :to="route.path">{{ route.name }}</router-link>
  </div>
  <Login/>
</template>

<script>
import Login from "./Login.vue";

export default {
  name: "MyNavLinks",
  components: {Login},
  props: {
    direction: String
  },
  computed: {
    routes() {
      // Utilise getRoutes() pour obtenir toutes les routes
      return this.$router.getRoutes().filter(route => route.name !== 'CoursDetail' && route.name !== 'createCours');
    }

  }
}
</script>

<style lang="scss" scoped>

.logo{
  width: 20vw;

  img{
    height: 100%;
  }
}
</style>
