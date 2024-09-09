<script setup>
import {onMounted, ref} from 'vue';

const typeCoursList = ref([]);
  const fetchData = async () => {
    try {
      const response = await fetch("/api/getTypeCours", { method: "GET" });
      typeCoursList.value = await response.json();
      formData.value.typeCours = typeCoursList.value[0].id;
    } catch (error) {
      console.error("Erreur lors de la récupération des cours:", error);
    }
  };

  onMounted(() => {
    fetchData();
  });

const formData = ref({
  typeCours: null,
  dateCours:
      new Date().getFullYear() + "-" +
      (new Date().getMonth() < 10 ? '0' + new Date().getMonth() : new Date().getMonth()) + "-" +
      (new Date().getDate() < 10 ? '0' + new Date().getDate() : new Date().getDate()) + "T" +
      (new Date().getHours() < 10 ? '0' + new Date().getHours() : new Date().getHours()) + ":" +
      (new Date().getMinutes() < 10 ? '0' + new Date().getMinutes() : new Date().getMinutes()),
  dureeCours: 90,
  nbInscriptionMax: 12,
  description: null,
  tarif: 15
});


  const handleSubmit = async (event) => {
    event.preventDefault();
    const data = {
      typeCours: formData.value.typeCours,
      dateCours: formData.value.dateCours,
      dureeCours: formData.value.dureeCours,
      nbInscriptionMax: formData.value.nbInscriptionMax,
      description: formData.value.description,
      tarif: formData.value.tarif,
      dateLimiteInscription: formData.value.dateLimiteInscription
    };

    console.log(JSON.stringify(data));
    try {
      const response = await fetch("/api/cours/create", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });
      const responseData = await response.json();
      console.log(responseData);
    } catch (error) {
      console.error("Erreur lors de la création du cours:", error);
    }
  };



</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-800">Ajouter un cours</h1>
    <form>
      <div class="mt-4">
        <label for="typeCours" class="block text-sm font-medium text-gray-700">Type de cours</label>
        <select id="typeCours" v-model="formData.typeCours" name="typeCours" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          <option v-for="cours in typeCoursList" :key="cours.id" :value="cours.id">{{ cours.libelle }}</option>
        </select>
      </div>
      <div class="mt-4">
        <label for="dateCours" class="block text-sm font-medium text-gray-700">Date</label>
        <input type="datetime-local" id="dateCours" v-model="formData.dateCours" name="dateCours" class="mt-1 block">
      </div>
      <div class="mt-4">
        <label for="dureeCours" class="block text-sm font-medium text-gray-700">Durée (minutes)</label>
        <input type="number" id="dureeCours" v-model="formData.dureeCours" name="dureeCours" class="mt-1 block">
      </div>
      <div class="mt-4">
        <label for="nbInscriptionMax" class="block text-sm font-medium text-gray-700">Nombre de places</label>
        <input type="number" id="nbInscriptionMax" v-model="formData.nbInscriptionMax" name="nbInscriptionMax" class="mt-1 block">
      </div>
      <div class="mt-4">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" v-model="formData.description" name="description" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
      </div>
      <div class="mt-4">
        <label for="tarif" class="block text-sm font-medium text-gray-700">Tarif</label>
        <input type="number" id="tarif" v-model="formData.tarif" name="tarif" class="mt-1 block">
      </div>
      <div class="mt-4">
        <label for="dateLimiteInscription" class="block text-sm font-medium text-gray-700">Date limite d'inscription</label>
        <input type="datetime-local" id="dateLimiteInscription" v-model="formData.dateLimiteInscription" name="dateLimiteInscription" class="mt-1 block">
      </div>
      <div class="mt-4">
        <button type="submit" @click="handleSubmit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Ajouter
        </button>
      </div>
    </form>
  </div>

</template>

<style scoped lang="scss">

</style>