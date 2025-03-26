<template>
  <div class="nav_wrapper">
    <header :class="isMobile ? 'header_mobile' : 'header_desktop'">
      <template v-if="isMobile">
        <Navbar />
        <Logo />
        <DropdownUser />
      </template>
      <template v-else>
        <Logo />
        <Navbar />
        <div class="flex justify-between items-center space-x-4">
          <DropdownUser />
        </div>
      </template>
    </header>
  </div>
</template>

<script setup>
import Logo from "./header/Logo.vue";
import DropdownUser from "./header/DropdownUser.vue";
import Navbar from "./header/Navbar.vue";
import {computed, onBeforeUnmount, onMounted, ref} from "vue";


const windowWidth = ref(window.innerWidth);
const isMobile = computed(() => windowWidth.value <= 900);

const handleResize = () => {
  windowWidth.value = window.innerWidth;
};

// Gestion du défilement pour ajouter une classe fixe
const handleScroll = () => {
  const navWrapper = document.querySelector(".nav_wrapper");
  if (window.scrollY > navWrapper.offsetHeight) {
    navWrapper.classList.add("fixed");
  } else {
    navWrapper.classList.remove("fixed");
  }
};

// Écouteurs pour les événements
onMounted(() => {
  document.addEventListener("scroll", handleScroll, { passive: true });
  window.addEventListener("resize", handleResize);
});

onBeforeUnmount(() => {
  document.removeEventListener("scroll", handleScroll);
  window.removeEventListener("resize", handleResize);
});
</script>

<style>
.nav_wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    color: #000;
    background: rgba(255, 255, 255, 0.9);
    height: 123px;
    max-height: 15vh;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2vw;
    padding: 0 2%;
    transition: all .3s ease-in-out;
}

.header_desktop {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.header_mobile {
    display: none;
}

@media (min-width: 900px) {
  .fixed {
    height: 70px;
  }
}


@media (max-width: 900px) {
    .nav_wrapper {
        background: rgba(255, 255, 255, 1);
        height: 70px;
    }

    .header_desktop {
        display: none;
    }

    .header_mobile {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
}

</style>

