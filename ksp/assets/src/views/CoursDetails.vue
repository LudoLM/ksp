<template>
  <div className="coursDetail">
    <h2>Détails du cours</h2>
    <div>
      <router-link :to="{ name: 'Accueil' }" class="mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Retour</router-link>
    </div>
    <div v-if="cours">
      <p>Type de cours: {{ cours.typeCours.libelle }}</p>
      <p>Date: {{ formattedDate }}</p>
      <p>Heure: {{ formattedHour }}</p>
      <p>Durée: {{ cours.duree }} minutes</p>
      <p>Description: {{ cours.description }}</p>
      <p><strong>Liste des participants</strong></p>
      <ul>
        <li v-for="participant in cours.users" :key="participant.id">
          - {{ participant.nom }} {{ participant.prenom }}
        </li>
      </ul>
    </div>

    <div v-else>
      <p>Chargement...</p>
    </div>
    <div>
      <button @click="subscription" class="mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">S'inscrire</button>
    </div>
  </div>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue';
import {useRoute} from 'vue-router';
import {useDateFormat} from '@vueuse/core';

const route = useRoute();
const coursId = route.params.id;
const cours = ref(null);

const fetchCoursDetail = async () => {
  try {
    const response = await fetch(`/api/getCours/${coursId}`);
    const data = await response.json();
    cours.value = JSON.parse(data); // Parse JSON string into object
  } catch (error) {
    console.error('Error fetching cours details:', error);
  }
};

onMounted(fetchCoursDetail);

const dateDebut = computed(() => new Date(cours.value?.dateCours));

const formattedDate = computed(() => useDateFormat(dateDebut.value, 'DD/MM/YYYY').value);
const formattedHour = computed(() => useDateFormat(dateDebut.value, 'HH:mm').value);
</script>

<style scoped>
.coursDetail {
  background-color: #fff;
  padding: 20px;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>
