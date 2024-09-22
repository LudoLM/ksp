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

<template>
      <div id="header_container">
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
        </div>
      </div>
</template>

<style scoped lang="scss">

#header_container {

  div {
    width: 100%;

    img {
      float: left;
      margin-left: 15px;
    }
  }

  .infos {
    display: flex;
    flex-direction: column;
    font-weight: 700;

    #compte {
      display: flex;
      align-items: center;
      justify-content: flex-end;

      span {
        margin-right: 10px;
      }
    }
  }
}

</style>