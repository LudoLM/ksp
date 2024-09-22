<script>
import './assets/scss/style.scss';
import Myheader from "./components/Header";
import Mymain from "./components/Main";
import Myfooter from "./components/Footer";
import MyNavLinks from "./components/NavLinks.vue";
import {store} from "core-js/internals/reflect-metadata";

export default {
  name: 'App',
  methods: {store},
  components: {
    MyNavLinks,
    Myheader,
    Mymain,
    Myfooter
  }
}

//Calcul le scroll pr reduire la taille de la navbar
document.addEventListener('scroll', () => {
  if (window.scrollY > document.querySelector('.nav_wrapper').offsetHeight) {
    document.querySelector('.nav_wrapper').classList.add('fixed');
  } else {
    document.querySelector('.nav_wrapper').classList.remove('fixed');
  }
});

</script>

<template>
  <div class="nav_wrapper">
    <MyNavLinks direction="row" :store="store"/>
  </div>
  <Myheader/>
  <Mymain/>
  <Myfooter/>
</template>

<style>

</style>