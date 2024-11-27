<script setup>
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/user';
import CustomButton from "../components/CustomButton.vue";
import CustomInput from "../components/CustomInput.vue";
import {useValidationForm} from "../utils/useValidationForm";

const route = useRouter();

const prenom = ref('');
const nom = ref('');
const email = ref('');
const password = ref('');
const adresse = ref('');
const cp = ref('');
const ville = ref('');
const telephone = ref('');
const errors = ref({
  prenom: null,
  nom: null,
  email: null,
  password: null,
  adresse: null,
  cp: null,
  ville: null,
  telephone: null,
});
const userStore = useUserStore();

const handleRegister = async () => {
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
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
       useValidationForm(response, errors);
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
          <CustomInput item="Prénom" type="text" id="prenom" :error="errors.prenom" required v-model="prenom"/>
          <CustomInput item="Nom" type="text" id="nom" :error="errors.nom" required v-model="nom"/>
          <CustomInput item="Email" type="email" id="email" :error="errors.email" required v-model="email"/>
          <CustomInput item="Mot de passe" type="password" id="password" :error="errors.password" required v-model="password"/>
        </div>
        <div class="column">
          <CustomInput item="Adresse" type="text" id="adresse" :error="errors.adresse" required v-model="adresse"/>
          <CustomInput item="Code Postal" type="text" id="cp" :error="errors.cp" required v-model="cp"/>
          <CustomInput item="Ville" type="text" id="ville" :error="errors.ville" required v-model="ville"/>
          <CustomInput item="Téléphone" type="text" id="telephone" :error="errors.telephone" required v-model="telephone"/>
        </div>
      </div>
      <div class="flex justify-center gap-4">
        <CustomButton>
          <router-link to="/">
            Retour
          </router-link>
        </CustomButton>
        <CustomButton type="submit">
          S'inscrire
        </CustomButton>
      </div>
    </form>
  </div>
</template>

<style scoped lang="scss">
</style>
