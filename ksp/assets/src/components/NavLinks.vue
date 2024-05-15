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
      return this.$router.options.routes;
    }
  }
}
</script>

<style lang="scss">
</style>
