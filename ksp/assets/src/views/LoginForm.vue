<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from "../store/user";

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
      throw new Error('Erreur de connexion');
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

    // Redirige vers la page d'accueil après la connexion réussie
    await router.push('/');
  } catch (err) {
    error.value = err.message;
  }
};
</script>

<template>
  <div class="login-container">
    <form @submit.prevent="handleLogin">
      <div class="form-group block text-gray-700 font-semibold mb-2">
        <label for="username">Email:</label>
        <input
            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            type="text"
            id="username"
            v-model="username"
            required
        />
      </div>
      <div class="form-group block text-gray-700 font-semibold mb-2">
        <label for="password">Password:</label>
        <input
            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            type="password"
            id="password"
            v-model="password"
            required
        />
      </div>
      <div class="flex justify-center gap-4">
        <button type="submit" class="bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Login</button>
        <button @click.prevent="router.push('/register')" class="bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Créer un compte</button>
      </div>
    </form>
    <p v-if="error" class="error-message">{{ error }}</p>

  </div>

</template>

<style scoped>
.login-container {
  max-width: 400px;
  margin: auto;
}

.form-group {
  margin-bottom: 1em;
}

.error-message {
  color: red;
}
</style>
