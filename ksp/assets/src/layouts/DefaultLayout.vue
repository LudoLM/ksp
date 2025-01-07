<script>
import '../assets/scss/style.scss';
import Mymain from "../components/Main";
import Myfooter from "../components/Footer";
import MyNavLinks from "../components/NavLinks.vue";

export default {
  name: 'DefaultLayout',
  components: {
    MyNavLinks,
    Mymain,
    Myfooter
  }
}

</script>

<template>
  <div class="nav_wrapper">
    <MyNavLinks/>
  </div>
  <Mymain/>
  <Myfooter/>
</template>

<style>

</style>