<template>
  <header>
    <div id="header_container">
      <img src="../../images/logo.png" alt="logo de kine sport santé">
      <div class="infos">
        <div id="compte">
          <span><i class="fas fa-user"></i></span>
          <span v-if="user">
            <a href="/logout" title="Logout">{{ user.prenom }}</a>
          </span>
          <span v-else>
            <a href="/login">Login</a>
          </span>
        </div>
        <div id="phone">
          Une question? Contactez-moi <span>{{ store.fullPhone }}</span>
        </div>
      </div>
    </div>
    <div class="nav_wrapper">
      <MyNavLinks direction="row" :store="store"/>
    </div>
    <div class="banner">
      <img src="../../images/banner.jpg" alt="exercices de travail">
    </div>
  </header>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import {useUserStore} from '../store/user';
import {infos} from '../store/index';
import MyNavLinks from './NavLinks.vue';

// Initialize the stores
const userStore = useUserStore();
const store = infos();
const user = ref(null);

// Fetch user data only if already authenticated
onMounted(async () => {
  if (userStore.getIsAutenticated === false) {
    try {
      const response = await fetch('/api/user');
      if (response.ok) {
        user.value = await response.json();
        userStore.setUser(user.value);
        userStore.setIsAutenticated();
      } else {
        console.log('Not authenticated');
      }
    } catch (error) {
      console.error('Error fetching user:', error);
    }
  }
  else {
    user.value = userStore.getUser;
  }
});

</script>
