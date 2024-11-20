<script>
import './assets/scss/style.scss';
import Mymain from "./components/Main";
import Myfooter from "./components/Footer";
import MyNavLinks from "./components/NavLinks.vue";

export default {
  name: 'App',
  components: {
    MyNavLinks,
    Mymain,
    Myfooter
  }
}

</script>

<template>
  <div class="nav_wrapper">
    <MyNavLinks direction="row"/>
  </div>
  <Mymain/>
  <Myfooter/>
</template>

<style>

</style>