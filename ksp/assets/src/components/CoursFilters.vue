<script setup>
import {ref, watch} from 'vue';

// Props : données passées depuis le parent
defineProps({
  uniqueTypeCours: {
    type: Array,
    required: true
  },
});

// Déclare la fonction d'émission d'événements
const emit = defineEmits(['update:selectedCoursId', 'update:selectedDate']);

// Modèle pour stocker la date sélectionnée et le cours sélectionné
const selectedCoursId = ref(0);
const selectedDate = ref("");

// Émettre les valeurs à chaque changement
watch(selectedCoursId, (newValue) => {
  emit('update:selectedCoursId', newValue);
});

watch(selectedDate, (newValue) => {
  emit('update:selectedDate', newValue);
});

const resetInfos = () => {
  selectedCoursId.value = 0;
  selectedDate.value = "";
  emit('update:selectedCoursId', 0);
  emit('update:selectedDate', "");
};
</script>

<template>
  <div id="form-wrapper" ref="form" class="space-y-4">
    <!-- Sélection du type de cours -->
    <div>
      <label for="listeCours" class="block text-sm font-medium text-gray-700">Cours</label>
      <select
          class="block bg-gray-200 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-indigo-500"
          name="listeCours"
          id="listeCours"
          v-model="selectedCoursId"
      >
        <option value="0">Tous les cours</option>
        <option v-for="typeCours in uniqueTypeCours" :key="typeCours.id" :value="typeCours.id">{{
            typeCours.libelle
          }}
        </option>
      </select>
    </div>

    <div>
      <label for="dateCours" class="block text-sm font-medium text-gray-700">À partir de</label>
      <input
          type="date"
          name="dateCours"
          id="dateCours"
          v-model="selectedDate"
          class="block bg-gray-200 border border-gray-300 text-gray-700 py-2 px-3 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-indigo-500"
      />
    </div>

    <div>
      <button
          @click="resetInfos"
          class="mt-4 bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none"
      >
        Reset
      </button>
    </div>
  </div>
</template>



<style scoped>
/* Ajouter un peu de styles personnalisés si nécessaire */

#form-wrapper {
  display: flex;
  justify-content: flex-end;
  align-items: flex-end;
  gap: 2%;
}
</style>
