<template>
  <div class="coursDetail">
    <h2>Détails du cours</h2>
    <div>
        <router-link :to="{ name: 'Accueil' }"><CustomButton>Retour</CustomButton></router-link>
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
      <CustomButton @click="subscription">S'inscrire</CustomButton>
    </div>
  </div>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue';
import {useRoute} from 'vue-router';
import {useDateFormat} from '@vueuse/core';
import {useGetCoursById} from "../utils/useActionCours";
import CustomButton from "../components/CustomButton.vue";

const route = useRoute();
const coursId = route.params.id;
const cours = ref(null);

const coursDetails = async () => {
  const result = await useGetCoursById(coursId);
  cours.value = JSON.parse(result);
}

onMounted(coursDetails);


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
