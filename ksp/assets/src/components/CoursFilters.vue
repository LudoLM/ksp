<script setup>
import {ref, watch} from 'vue';
import CustomSelect from "./CustomSelect.vue";
import CustomInput from "./CustomInput.vue";
import CustomButton from "./CustomButton.vue";

defineProps({
  uniqueTypeCours: {
    type: Array,
    required: true
  },

  uniqueStatusCours: {
    type: Array,
    required: true
  }
});


// Déclare la fonction d'émission d'événements
const emit = defineEmits(['update:selectedCoursId', 'update:selectedDate', 'update:selectedStatusId']);

// Modèle pour stocker la date sélectionnée et le cours sélectionné
const selectedCoursId = ref(0);
const selectedDate = ref("");
const selectedStatusCours = ref(0);

// Émettre les valeurs à chaque changement
watch(selectedCoursId, (newValue) => {
  emit('update:selectedCoursId', newValue);
});

watch(selectedDate, (newValue) => {
  emit('update:selectedDate', newValue);
});

watch(selectedStatusCours, (newValue) => {
  emit('update:selectedStatusId', newValue);
});

const resetInfos = () => {
  selectedCoursId.value = 0;
  selectedDate.value = "";
  emit('update:selectedCoursId', 0);
  emit('update:selectedDate', "");
  emit('update:selectedStatusId', 0);
};
</script>

<template>

  <div id="form-wrapper" ref="form" class="space-y-4">
    <div class="selects">
      <!-- Sélection du type de cours -->
      <div class="form-item">
        <CustomSelect
            :options="uniqueTypeCours"
            v-model="selectedCoursId"
            item="Cours"
            id="Tous les cours"
        />
      </div>
      <!-- Sélection de la date de cours -->
      <div class="form-item">
        <CustomInput
            type="date"
            item="A partir de"
            v-model="selectedDate"
            id="dateCours"
        />
      </div>
      <!-- Sélection du status de cours -->
      <div class="form-item">
        <CustomSelect
            :options="uniqueStatusCours"
            v-model="selectedStatusCours"
            item="Statuts"
            id="Tous les statuts"
        />
      </div>
    </div>
    <div class="reset">
      <!-- Reset-->
      <div class="form-item form-group mb-4">
        <CustomButton
            @click="resetInfos"
            color="red"
            class="self-center"
        >
          Reset
        </CustomButton>
      </div>
    </div>
  </div>
</template>

<style scoped>

#form-wrapper {

  display: flex;
  justify-content: center;
  align-items: center;
  gap: 5%;
  margin-right: 5%;

  .selects {
    display: flex;
    justify-items: center;
    align-items: baseline;
    gap: 2%;
  }

  .reset{
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
  }
}
</style>
