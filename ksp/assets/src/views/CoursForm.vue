<script setup>
import {onMounted, ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import CustomTextarea from "../components/CustomTextarea.vue";
import CustomInput from "../components/CustomInput.vue";
import CustomSelect from "../components/CustomSelect.vue";
import CustomButton from "../components/CustomButton.vue";
import {useValidationForm} from "../utils/useValidationForm";

const router = useRouter();
const origin = useRoute().params;
const coursData = ref(null);
const urlCreation = "/api/cours/create";
const urlEdition = "/api/cours/edit/" + origin.id;
const errors = ref(
  {
    typeCours: null,
    dateCours: null,
    dureeCours: null,
    nbInscriptionMax: null,
    description: null,
    dateLimiteInscription: null,
  },
)


const typeCoursList = ref([]);
  const fetchData = async () => {
    try {
      const response = await fetch("/api/getTypeCours", { method: "GET", headers: { "Authorization": "Bearer " + localStorage.getItem("token") } });
      typeCoursList.value = await response.json();
      formData.value.typeCours = typeCoursList.value[0].id;
    } catch (error) {
      console.error("Erreur lors de la récupération des cours:", error);
    }
  };

  onMounted(() => {
    fetchData();

    // Si on est sur la page d'édit
    if (origin.id){
      getCoursData();
    }
  });

const formData = ref({
  typeCours: null,
  dateCours:
      new Date().getFullYear() + "-" +
      (new Date().getMonth() < 10 ? '0' + new Date().getMonth() : new Date().getMonth()) + "-" +
      (new Date().getDate() < 10 ? '0' + new Date().getDate() : new Date().getDate()) + "T" +
      (new Date().getHours() < 10 ? '0' + new Date().getHours() : new Date().getHours()) + ":" +
      (new Date().getMinutes() < 10 ? '0' + new Date().getMinutes() : new Date().getMinutes()),
  dureeCours: coursData.value ? coursData.value.dureeCours : 60,
  nbInscriptionMax: 12,
  description: "Cours sympathique",
  dateLimiteInscription:
      new Date().getFullYear() + "-" +
      (new Date().getMonth() < 10 ? '0' + new Date().getMonth() : new Date().getMonth()) + "-" +
      (new Date().getDate() < 10 ? '0' + new Date().getDate() : new Date().getDate()) + "T" +
      (new Date().getHours() < 10 ? '0' + new Date().getHours() : new Date().getHours()) + ":" +
      (new Date().getMinutes() < 10 ? '0' + new Date().getMinutes() : new Date().getMinutes())
});

//Functions
  const handleSubmit = async (event) => {
    event.preventDefault();
    const data = {
      typeCours: parseInt(formData.value.typeCours),
      dateCours: formData.value.dateCours,
      dureeCours: parseInt(formData.value.dureeCours),
      nbInscriptionMax: parseInt(formData.value.nbInscriptionMax),
      description: formData.value.description,
      dateLimiteInscription: formData.value.dateLimiteInscription
    };
    try {
      const response = await fetch(origin.id ? urlEdition : urlCreation, {
        method: origin.id ? "PUT" : "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": "Bearer " + localStorage.getItem("token"),
        },
        body: JSON.stringify(data),
      });
      if (!response.ok) {
        await useValidationForm(response, errors);
      }
      if (response.status === 200){
        await router.push("/");
      } else {
        console.error("Erreur lors de la création du cours:", response);
      }
    } catch (error) {
      console.error("Erreur lors de la création du cours:", error);
    }
  };

  const getCoursData = async () => {
    try {
      const response = await fetch("/api/getCours/" + origin.id, {
        method: "GET",
        headers: { "Authorization": "Bearer " + localStorage.getItem("token")}}
      );

      const data = await response.json();
      const coursData = JSON.parse(data);

      formData.value.typeCours = coursData.typeCours.id;
      formData.value.dateCours = coursData.dateCours ? coursData.dateCours.slice(0, 16) : formData.value.dateCours;
      formData.value.dureeCours = coursData.duree;
      formData.value.nbInscriptionMax = coursData.nbInscriptionMax;
      formData.value.description = coursData.description;
      formData.value.dateLimiteInscription = coursData.dateLimiteInscription ? coursData.dateLimiteInscription.slice(0, 16) : formData.value.dateLimiteInscription;


    } catch (error) {
      console.error("Erreur lors de la récupération du cours:", error);
    }
  };

</script>

<template>
  <div>
    <h1>{{ origin.id ? "modifier" : "ajouter" }} un cours</h1>

    <div class="buttonsFilters flex justify-start mb-10">
      <router-link to="/coursType/add"><CustomButton>Ajouter un type de Cours</CustomButton></router-link>
      <router-link to="/coursType/edit"><CustomButton>Modifier un type de Cours</CustomButton></router-link>
    </div>
    <form>
      <div class="grid grid-cols-2 gap-4">
        <CustomSelect item="Type de cours" id="typeCours" :error="errors.typeCours" v-model="formData.typeCours" :options="typeCoursList" required/>
        <CustomInput item="Durée (minutes)" type="number" id="dureeCours" :error="errors.dureeCours" v-model="formData.dureeCours" required/>
        <CustomInput item="Date" type="datetime-local" id="dateCours" dureeCours :error="errors.dateCours" v-model="formData.dateCours" required/>
        <CustomInput item="Date limite d'inscription" type="datetime-local" id="dateLimiteInscription" :error="errors.dateLimiteInscription" v-model="formData.dateLimiteInscription" required/>
        <CustomInput item="Nombre de places" type="number" id="description" :error="errors.nbInscriptionMax" v-model="formData.nbInscriptionMax" required/>
      </div>
        <CustomTextarea item="Description" id="description" v-model="formData.description" :error="errors.description" required class="w-full" />
      <div class="mt-4 flex justify-center">
        <CustomButton type="submit" @click="handleSubmit">
          {{ origin.id ? "Modifier" : "Ajouter" }}
        </CustomButton>
      </div>
    </form>
  </div>

</template>

<style scoped lang="scss">

</style>