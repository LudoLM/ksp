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
          Durée: {{ info.duree }} mns
        </div>
        <div class="card_ville">
          À : {{ info.description }}
        </div>
      </div>
      <div class="sortieCardBack">
        <div class="card_infos_back">
          <div class="buttons">
          </div>
        </div>
      </div>
    </div>
    <div :class="['card_status', info.statusCoursCssClass]">
      {{ info.statusCours.libelle }}
    </div>
  </div>
</template>

<script setup>
import {computed} from 'vue';
import {useDateFormat} from '@vueuse/core';

// Props
const props = defineProps({
  info: {
    type: Object,
    required: true,
  },
  computed: {
    formattedDate: String,
    formattedHour: String,
  },
});

// Convertir la dateDebut en objet Date si ce n'est pas déjà le cas
const dateDebut = computed(() => {
  return new Date(props.info.dateCours);
});

// Propriété calculée pour formater la date
const formattedDate = computed(() => {
  return useDateFormat(dateDebut.value, 'DD/MM/YYYY').value;
});
const formattedHour = computed(() => {
  return useDateFormat(dateDebut.value, 'HH:mm').value;
});
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

.card_infos {
  position: absolute;
  width: 100%;
  height: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.3s ease-in-out;
  z-index: 1000;
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


.buttons {
  position: absolute;
  width: 100%;
  height: 200px;
  bottom: -100px;
  display: flex;
  justify-content: center;
  gap: 30px;
  align-items: center;
  transition: all 0.2s ease-in-out;
  transition-delay: 0.2s;
}

.sortieCard_Wrapper:hover .buttons {
  bottom: 0;
}

.buttons a {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 35%;
  height: 50px;
  background: transparent;
  color: #fff;
  border: 2px solid #fff;
}

.card_infos_back {
  color: #fff;
}

.sortieCard_Wrapper:hover .sortieCardBack {
  bottom: 0;
}
</style>
