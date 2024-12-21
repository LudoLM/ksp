<template>
  <header class="flex justify-between items-center w-full">
      <div class="logo">
        <img src="../../images/logo.png" alt="logo de kine sport santé">
      </div>
      <nav id="nav_links">
        <router-link v-for="route in routes" :key="route.path" :to="route.path">{{ route.name }}</router-link>
      </nav>
    <div class="flex justify-between items-center space-x-4 ">
      <Login/>
      <div class="hamburger">
        <Hamburger @click="toggleNavLinks"/>
      </div>
    </div>

  </header>
</template>

<script>
import Login from "./Login.vue";
import Hamburger from "./Hamburger.vue";



export default {
  name: "MyNavLinks",
  components: { Hamburger, Login },
  props: {
    direction: {
      type: String,
      default: 'flex-row'
    }
  },
  computed: {
    routes() {
      return this.$router.getRoutes().filter(route =>
          !['CoursDetail', 'CreateCours', 'Login', 'Register', 'AcheterCours', 'Merci', 'Profile', 'EditCours', 'CreateTypeCours', 'EditTypeCours'].includes(route.name)
      );
    }
  },
  methods: {
    handleScroll() {
      const navWrapper = document.querySelector('.nav_wrapper');
      if (window.scrollY > navWrapper.offsetHeight) {
        navWrapper.classList.add('fixed');
      } else {
        navWrapper.classList.remove('fixed');
      }
    },
    toggleNavLinks() {
      const navLinks = document.getElementById('nav_links');
      navLinks.classList.toggle('active');
    }
  },
  mounted() {
    document.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    document.removeEventListener('scroll', this.handleScroll);
  }
};

</script>

<style lang="scss" scoped>
.logo {
  width: 210px;
  min-width: 150px;
}

/*.nav_wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  color: #000;
  background: rgba(255, 255, 255, 0.7);
  height: 123px;
  display: flex;
  justify-content: space-between;
  gap: 2%;
  align-items: center;
  padding: 0 2%;
  transition: all .3s ease-in-out;
}*/

.fixed{
  height: 70px;
  background: rgba(255, 255, 255, 1);
}

nav {
  width: 50vw;
  color: #3c3c3c;
}

.hamburger {
  width: 50px;
  height: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.nav_wrapper.fixed {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
}


@media (min-width: 900px) {
  .hamburger {
    display: none;
  }
}

@media (max-width: 900px) {

  #nav_links {
    position: absolute;
    flex-direction: column;
    top: 123px;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 1);
    width: 100%;
    height: 30vh;
    display: flex;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-20px);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;
  }

  .fixed #nav_links {
    top: 70px;
  }

  #nav_links.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    transition: opacity 0.3s ease, transform 0.3s ease;
    align-items: flex-start;
    padding-left: 50px;
    border-bottom: 1px solid #000;
  }

  #nav_links:not(.active) {
    opacity: 0;
    visibility: hidden;
    transform: translateY(-20px);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;
  }

}

@media (min-width: 768px) {
  nav {
    display: none;
  }
}

@media (max-width: 768px) {
  #icon {
    display: none;
  }
}
</style>
