<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/user';

const route = useRouter();

const prenom = ref('');
const nom = ref('');
const email = ref('');
const password = ref('');
const adresse = ref('');
const cp = ref('');
const ville = ref('');
const telephone = ref('');
const userStore = useUserStore();

const handleRegister = async () => {
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        prenom: prenom.value,
        nom: nom.value,
        email: email.value,
        password: password.value,
        adresse: adresse.value,
        cp: cp.value,
        commune: ville.value,
        telephone: telephone.value,
      }),
    });

    if (!response.ok) {
      throw new Error('Erreur de création de compte');
    }

    const data = await response.json();
    localStorage.setItem('token', data.token);
    const payload = data.token.split('.')[1];
    const decoded = atob(payload);
    const dataToken = JSON.parse(decoded);
    userStore.setUserEmail(dataToken.username);
    userStore.setUserId(dataToken.id);
    userStore.setUserPrenom(dataToken.prenom);
    await route.push('/');
  } catch (err) {
    console.error(err);
  }
};

</script>

<template>
  <div class="login-container w-full flex items-center justify-center">
    <form  @submit.prevent="handleRegister" class="bg-white p-8 rounded-lg shadow-lg w-full">
      <h2 class="text-2xl font-bold text-center mb-6 text-gray-700">Créer un compte</h2>

      <div class="w-full flex justify-space-between gap-4">
        <div class="column">
          <div class="form-group mb-4 w-50">
            <label for="prenom" class="block text-gray-700 font-semibold mb-2">Prénom:</label>
            <input
                type="text"
                id="prenom"
                v-model="prenom"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="form-group mb-4">
            <label for="nom" class="block text-gray-700 font-semibold mb-2">Nom:</label>
            <input
                type="text"
                id="nom"
                v-model="nom"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="form-group mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-2">Email:</label>
            <input
                type="email"
                id="email"
                v-model="email"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="form-group mb-4">
            <label for="password" class="block text-gray-700 font-semibold mb-2">Mot de passe:</label>
            <input
                type="password"
                id="password"
                v-model="password"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
        <div class="column">
          <div class="form-group mb-4">
            <label for="adresse" class="block text-gray-700 font-semibold mb-2">Adresse:</label>
            <input
                type="text"
                id="adresse"
                v-model="adresse"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="form-group mb-4">
            <label for="cp" class="block text-gray-700 font-semibold mb-2">Code Postal:</label>
            <input
                type="text"
                id="cp"
                v-model="cp"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="form-group mb-4">
            <label for="ville" class="block text-gray-700 font-semibold mb-2">Ville:</label>
            <input
                type="text"
                id="ville"
                v-model="ville"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="form-group mb-4">
            <label for="telephone" class="block text-gray-700 font-semibold mb-2">Téléphone:</label>
            <input
                type="text"
                id="telephone"
                v-model="telephone"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>
      <div class="flex justify-center gap-4">
        <button class="bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
          <router-link to="/">
            Retour
          </router-link>
        </button>
        <button
            type="submit"
            class="bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
        >
          S'inscrire
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped lang="scss">
</style>
