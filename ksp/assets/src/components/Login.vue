<script setup>
import {computed} from 'vue';
import { useUserStore } from '../store/user';
import { useRouter } from 'vue-router';

const userStore = useUserStore();
const router = useRouter();

const userId = computed(() => userStore.userId);
const userPrenom = computed(() => userStore.userPrenom);

const handleProfile = () => {
  router.push('/profile');
};

const handleRegister = () => {
    router.push('/register');
};

const handleLogin = () => {
    router.push('/login');
};

const logout = () => {
  userStore.logout();
  router.push({name: 'Accueil'});
};

</script>


<template>
  <div id="header_container">
    <div class="infos">
      <div id="compte">
        <div v-if="userId" class="profil">
           <span >
             <a @click="handleProfile" title="Mon profil"><div class="user"><img src="../../icons/user.svg"/>{{ userPrenom }}</div></a>
           </span>
           <span>
             <a @click="logout" title="Déconnexion"><div class="logout"><img src="../../icons/logout.svg"/></div></a>
           </span>
        </div>
        <div v-else>
          <div class="loginButtons">
            <a class="createCount" @click="handleRegister">"Créer un compte</a>
            <a class="identifier" @click="handleLogin"><img src="../../icons/user.svg"/><span class="identifier_text">Me connecter</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped lang="scss">
#header_container {
  div {
    width: 100%;
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

  .profil {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-right: 8px;
    color: #5e2ca5;
  }

  .loginButtons{
    display: flex;
    gap: 10px;
  }

  .createCount, .user{
    display: flex;
    justify-content: center;
    align-items: center;
    color: #5e2ca5;
    border: 2px solid #5e2ca5;
    border-radius: 5px;
    width: 150px;
    height: 50px;
    font-weight: 400;
    font-size: .8rem;
  }

  .identifier{
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    width: 150px;
    height: 50px;
    background: #5e2ca5;
    border-radius: 5px;
    color: #dfdfdf;
    font-weight: 400;
    font-size: .8rem;
  }

  .logout{
    display: flex;
    justify-content: center;
    align-items: center;
    width: 35px;
    height: 35px;
    background: #5e2ca5;
    border-radius: 5px;
    color: #dfdfdf;
    font-weight: 400;
    font-size: .8rem;
  }

  .user{
    border: none;

    svg{
     fill: #5e2ca5;
    }
  }

  @media (min-width: 900px) {
    .createCount {
      display: flex;
    }
    .identifier_text{
      display: flex;
    }
  }

  @media (max-width: 900px) {
    .createCount {
      display: none;
    }
  }

  @media (max-width: 700px) {
    .identifier {
      width: 50px;
    }
    .identifier_text {
      display: none;
    }
  }

  @media (max-width: 1100px) {
    .createCount {
      display: none;
    }
  }

}

</style>
