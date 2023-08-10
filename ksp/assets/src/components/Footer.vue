<template>
  <footer>
    <div class="box_columns">
      <div class="footer_column">
        <h4>Rubriques</h4>

        <div class="links">
          <MyNavLinks direction="column"/>
        </div>


      </div>
      <div class="footer_column">
        <h4>Contact</h4>
        <div>{{ $store.state.coordonnees.name }}</div>
        <b>{{ $store.state.coordonnees.adresse }}</b><br>
        <b>{{ $store.getters.fullAdress }}</b><br>
        <i class="fas fa-phone-alt"></i><b>{{ $store.state.coordonnees.phone}}</b><br>
        <a :href="`mailto:` + $store.state.coordonnees.mail ">{{ $store.state.coordonnees.mail }}</a>
      </div>
      <div class="footer_column">
        <h4>Suivez-moi</h4>
        <div class="social">
          <i class="fab fa-facebook"></i>
          <i class="fab fa-instagram"></i>
          <i class="fab fa-twitter"></i>
        </div>
      </div>
      <div class="footer_column">
        <h4>A propos</h4>
      </div>
    </div>
  </footer>
</template>

<script>
import MyNavLinks from "./NavLinks.vue"
export default {
  name: "Myfooter",
  components: {
    MyNavLinks
  }
}
</script>

<style lang="scss">

</style>