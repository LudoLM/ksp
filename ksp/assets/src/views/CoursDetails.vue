<template>
  <div class="coursDetail">
    <div class="container w-full">
      <div v-if="cours" class="details_wrapper">
        <div class="img_wrapper">
          <img :src="require(`../../images/uploads/${cours.typeCours.thumbnail}`)" alt="">
        </div>
        <div class="infos_wrapper flex flex-col">
          <p>Type de cours: {{ cours.typeCours.libelle }}</p>
          <p>Date: {{ formattedDate }}</p>
          <p>Heure: {{ formattedHour }}</p>
          <p>Durée: {{ cours.duree }} minutes</p>
          <p>Description: {{ cours.description }}</p>
          <div class="button flex justify-center">
            <div>
              <CustomButton @click="subscription">S'inscrire</CustomButton>
            </div>
            <div>
              <router-link :to="{ name: 'Accueil' }"><CustomButton>Retour</CustomButton></router-link>
            </div>
          </div>
        </div>
      </div>
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

<style scoped lang="scss">

.coursDetail {
  background-color: #fff;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.details_wrapper {
  display: flex;
  width: 100%; /* S'assurer que le conteneur utilise toute la largeur disponible */
}

.img_wrapper {
  width: 80%;
  flex-shrink: 0;
}

.img_wrapper img {
  width: 100%; /* L'image prend toute la largeur de son conteneur */
  height: auto; /* Garder le ratio de l'image */
}

.infos_wrapper {
  width: 20%;
  padding: 20px;
}

</style>
