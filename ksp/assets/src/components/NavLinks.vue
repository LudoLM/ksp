<template>
  <header class="flex justify-between items-center w-full">
      <div class="logo">
        <img src="../../images/logo.png" alt="logo de kine sport santé">
      </div>
      <nav v-if="isAdminPath" id="nav_links" class="gap-8 justify-start">
          <router-link
              :to="{ name: 'Statistiques' }"
          >
              Tableau de bord
          </router-link>
          <div class="relative" ref="target">
              <router-link
                  class="flex items-center gap-4"
                  to="#"
                  @click.prevent="dropdownOpen = !dropdownOpen"
              >
                    <span class="hidden text-right lg:block">
                        <a class="block text-black dark:text-white">Cours</a>
                    </span>

                  <svg
                      :class="dropdownOpen && 'rotate-180'"
                      class="hidden fill-current sm:block"
                      width="12"
                      height="8"
                      viewBox="0 0 12 8"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                  >
                      <path
                          fill-rule="evenodd"
                          clip-rule="evenodd"
                          d="M0.410765 0.910734C0.736202 0.585297 1.26384 0.585297 1.58928 0.910734L6.00002 5.32148L10.4108 0.910734C10.7362 0.585297 11.2638 0.585297 11.5893 0.910734C11.9147 1.23617 11.9147 1.76381 11.5893 2.08924L6.58928 7.08924C6.26384 7.41468 5.7362 7.41468 5.41077 7.08924L0.410765 2.08924C0.0853277 1.76381 0.0853277 1.23617 0.410765 0.910734Z"
                          fill=""
                      />
                  </svg>
              </router-link>

              <!-- Dropdown Start -->
              <div
                  v-show="dropdownOpen"
                  class="absolute right-0 mt-4 flex w-62.5 flex-col rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark"
                  @mouseleave="dropdownOpen = false"
              >
                  <ul class="flex flex-col gap-5 border-b border-stroke px-6 py-7.5 dark:border-strokedark">
                      <li>
                          <router-link
                              :to='{name: "CoursAdmin"}'
                              class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                          >
                              Liste des cours
                          </router-link>
                      </li>
                      <li>
                          <router-link
                              :to='{name: "CreateCours"}'
                              class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                          >
                              Créer un cours
                          </router-link>
                      </li>
                      <li>
                          <router-link
                              :to='{name: "CreateTypeCours"}'
                              class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                          >
                              Créer un type de cours
                          </router-link>
                      </li>
                      <li>
                          <router-link
                              :to='{name: "EditTypeCours"}'
                              class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                          >
                              Modifier un type de cours
                          </router-link>
                      </li>
                  </ul>
              </div>
          </div>
      </nav>
      <nav v-else id="nav_links" class="justify-around">
          <router-link
              v-for="route in routes"
              :key="route.path"
              :to="route.path"
          >
              {{ route.name }}
          </router-link>
      </nav>
    <div class="flex justify-between items-center space-x-4 ">
      <DropdownUser/>
<!--      <Login/>-->
      <div class="hamburger">
        <Hamburger @click="toggleNavLinks"/>
      </div>
    </div>

  </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import DropdownUser from "./DropdownUser.vue";
import Hamburger from "./Hamburger.vue";
const dropdownOpen = ref(false)
import {onClickOutside} from "@vueuse/core";

const route = useRoute();
const router = useRouter();

// Identifie si l'utilisateur est sur une route admin
const isAdminPath = computed(() => route.path.startsWith("/admin"));
const target = ref(null)


onClickOutside(target, () => {
    dropdownOpen.value = false
});

// Filtrage des routes dynamiques
const routes = computed(() =>
    router.getRoutes().filter((r) =>
        ![
            "CoursDetail",
            "CreateCours",
            "Login",
            "Register",
            "AcheterCours",
            "Merci",
            "Profile",
            "EditCours",
            "CreateTypeCours",
            "EditTypeCours",
            "admin",
            "Dashboard",
            "CoursAdmin",
            "Statistiques",
            "Cours"
        ].includes(r.name)
    )
);


    // Fonction pour extraire les routes administratives
    const getAdminRoutes = (routes) => {
        return routes
            .filter((route) => route.meta?.requiresAdmin)
            .map((route) => ({
                ...route,
                children: route.children ? getAdminRoutes(route.children) : [],
            }));
    };

// Gestion des liens de navigation (responsive)
const toggleNavLinks = () => {
    const navLinks = document.getElementById("nav_links");
    navLinks.classList.toggle("active");
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
    document.addEventListener("scroll", handleScroll);
});

onBeforeUnmount(() => {
    document.removeEventListener("scroll", handleScroll);
});
</script>


<style lang="scss" scoped>


#nav_links {
    display: flex;
    align-items: center;
  height: 100%;
}


a.router-link-exact-active {
  font-weight: 700;
  color: #4f2794;
}

#nav_links a {

  height: 40%;
  font-weight: 900;
  font-size: 1rem;
  text-decoration: none;
  position: relative;
  display: flex;
  justify-content: start;
  align-items: center;

  &.router-link-exact-active {
    color: #4f2794;
  }

  &::after:not(.relative) {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #4f2794;
    transition: width .3s ease-in-out;
  }


  &:hover::after{
    width: 100%;
  }
}

.logo {
  width: 210px;
  min-width: 150px;
}

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
