<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from "../store/user";
import CustomInput from "../components/CustomInput.vue";
import CustomButton from "../components/CustomButton.vue";
import Banner from "../components/Banner.vue";

// Instancier le store en dehors de la fonction handleLogin
const userStore = useUserStore();
const username = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();

const handleLogin = async () => {
  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ username: username.value, password: password.value }),
    });

    if (!response.ok) {
      const responseError = await response.json();

      throw new Error(responseError.message);
    }

    const data = await response.json();
    // Stocker le token et mettre à jour le store
    localStorage.setItem('token', data.token);

    // Décoder le token pour récupérer l'email de l'utilisateur
    const payload = data.token.split('.')[1];
    const decoded = atob(payload);
    const elements = JSON.parse(decoded);

    // Mettre à jour le store avec l'email de l'utilisateur et l'état d'authentification
    userStore.setUserEmail(elements.username);
    userStore.setUserId(elements.id);
    userStore.setUserPrenom(elements.prenom);
    userStore.setUserJWTExp(elements.exp);

    // Redirige vers la page d'accueil après la connexion réussie
    await router.push('/');
  } catch (err) {
    error.value = "Les informations d'identification sont incorrectes";
  }
};
</script>

<template>
    <Banner
        title="Authentification"
        :textColor="'rgba(30, 27, 75, .9)'"
        backgroundHeight="40vh"
        :hasButton=false
    />
  <div class="login-container">
    <form @submit.prevent="handleLogin">

      <CustomInput item="Email" type="text" id="username" v-model="username" required/>
      <CustomInput item="Mot de passe" type="password" id="password" v-model="password" required/>
      <p v-if="error" class="error-message">{{ error }}</p>
      <div class="flex justify-center gap-4">
        <CustomButton type="submit">Login</CustomButton>
        <CustomButton @click.prevent="router.push('/register')">Créer un compte</CustomButton>
      </div>
    </form>

  </div>

</template>

<style scoped>
.login-container {
  max-width: 400px;
  margin: auto;
}


.error-message {
  color: red;
}
</style>
