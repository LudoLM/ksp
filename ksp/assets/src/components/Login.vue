<script setup>
import { computed, onMounted } from 'vue';
import { useUserStore } from '../store/user';
import { useRouter } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();

const userEmail = computed(() => userStore.userEmail);
const userId = computed(() => userStore.userId);
const userPrenom = computed(() => userStore.userPrenom);

const logout = () => {
  userStore.logout();
  router.push('/');
};

onMounted(() => {
  const token = localStorage.getItem('token');
  if (token) {
    const payload = token.split('.')[1];
    const decoded = atob(payload);
    const data = JSON.parse(decoded);
    userStore.setUserEmail(data.username);
    userStore.setUserId(data.id);
    userStore.setUserPrenom(data.prenom);
  }
});
</script>


<template>
  <div id="header_container">
    <div class="infos">
      <div id="compte">
        <span><i class="fas fa-user"></i></span>
        <span v-if="userId">
          <a @click="logout" title="Logout">{{ userPrenom }}</a>
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
