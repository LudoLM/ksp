<template>
  <header>
    <div class="banner">
    </div>
  </header>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import {useUserStore} from '../store/user';
import {infos} from '../store/index';

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

<style lang="scss" scoped>

  .banner {
    background: url("../../images/banner.jpg") no-repeat center center;
    background-size: cover;
    width: 100%;
    height: 50vh;
    display: flex;
    justify-content: space-between;
    position: relative;
  }
</style>
