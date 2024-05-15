<script setup>
import {infos} from "../store/index";
const store = infos();
</script>
<script>
import MyNavLinks from './NavLinks.vue'
export default {
  name: "Myheader",
  components: {
    MyNavLinks
  }
}
</script>

<template>
  <header>
    <div id="header_container">
      <img src="../../images/logo.png" alt="logo de kine sport santé">
      <div class="infos">
        <div id="compte"><span><i class="fas fa-user"></i></span><p>Mon compte</p></div>
        <div id="phone">Une question? Contactez-moi <span>{{ store.fullPhone }}</span></div>
      </div>
    </div>
    <div class="nav_wrapper">
      <MyNavLinks direction="row" :store="store" />
    </div>
    <div class="banner">
      <img src="../../images/banner.jpg" alt="exercices de travail">
    </div>
  </header>

</template>

