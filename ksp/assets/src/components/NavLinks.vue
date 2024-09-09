<template>
  <div :class="direction" id="nav_links">
    <router-link v-for="route in routes" :key="route.path" :to="route.path">{{ route.name }}</router-link>
  </div>
</template>

<script>
export default {
  name: "MyNavLinks",
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

<style lang="scss">
</style>
