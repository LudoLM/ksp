<template>
  <div class="coursCard_Wrapper">
    <div class="coursCard">
      <div class="card_infos">
        <div class="card_title">
          <h3>{{ info.typeCours.libelle }}</h3>
        </div>
        <div class="card_dateDebut">
          Le {{ formattedDate }} à {{ formattedHour }}
        </div>
        <div>
          Durée: {{ info.duree }} minutes
        </div>
        <div class="card_ville">
          À : {{ info.description }}
        </div>
        <div class="flex">
          <router-link :to="{ name: 'CoursDetail', params: { id: info.id } }" class="mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">+ d'infos</router-link>
          <button @click="subscription" class="mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">S'inscrire</button>
        </div>
      </div>
    </div>
    <div :class="['card_status', info.statusCoursCssClass]">
      {{ info.statusCours.libelle }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useDateFormat } from '@vueuse/core';

// Props
const props = defineProps({
  info: {
    type: Object,
    required: true,
  },
});

// Convertir la dateDebut en objet Date si ce n'est pas déjà le cas
const dateDebut = computed(() => new Date(props.info.dateCours));

// Propriété calculée pour formater la date
const formattedDate = computed(() => useDateFormat(dateDebut.value, 'DD/MM/YYYY').value);
const formattedHour = computed(() => useDateFormat(dateDebut.value, 'HH:mm').value);

// Méthodes
const subscription = () => {
  console.log('subscription');
};
</script>

<style lang="scss" scoped>
$mainColor: #A289CC;

.coursCard_Wrapper {
  width: 200px;
  height: 300px;
  position: relative;
}

.coursCard {
  width: 100%;
  height: 100%;
  font-family: poppins;
  background: #fff;
}

.buttons {
  height: 25%;
}

.sortieCard_Wrapper:hover .card_infos {
  color: #fff;
  top: 30%;
}

.card_status {
  position: absolute;
  width: 100px;
  height: 40px;
  background: $mainColor;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  top: 30px;
  right: -30px;
  transition: all 0.3s ease-in-out;
}

.card_infos_back {
  color: #fff;
}

.sortieCard_Wrapper:hover .sortieCardBack {
  bottom: 0;
}
</style>
