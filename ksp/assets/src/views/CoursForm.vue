<script setup>
import {onMounted, ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import CustomTextarea from "../components/CustomTextarea.vue";
import CustomInput from "../components/CustomInput.vue";
import CustomSelect from "../components/CustomSelect.vue";
import CustomButton from "../components/CustomButton.vue";
import {useValidationForm} from "../utils/useValidationForm";
import {useGetTypesCours} from "../utils/useActionCours";
import {apiFetch} from "../utils/useFetchInterceptor";
import {VAlert} from "vuetify/components";

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
  },
)

const alertVisible = ref(false);
const alertType = ref('info');
const alertMessage = ref('');


const typeCoursList = ref([]);
  const fetchData = async () => {
    try {
      typeCoursList.value = await useGetTypesCours();
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
  dateCours: new Date().toISOString().slice(0, 16),
  dureeCours: coursData.value ? coursData.value.dureeCours : 60,
  nbInscriptionMax: 12,
  description: "Cours sympathique",
});

  const handleSubmit = async (event) => {

    event.preventDefault();

    const data = {
      typeCours: parseInt(formData.value.typeCours),
      dateCours: formData.value.dateCours,
      dureeCours: parseInt(formData.value.dureeCours),
      nbInscriptionMax: parseInt(formData.value.nbInscriptionMax),
      description: formData.value.description,
    };

    try {
      const response = await apiFetch(origin.id ? urlEdition : urlCreation, {
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
        await router.push({
            name: "CoursAdmin",
            query: {alertMessage: "Cours ajouté avec succès", alertType: "success", alertVisible: true}});
      } else {
        console.error("Erreur lors de la création du cours:", response);
      }
    } catch (error) {
        alertVisible.value = true;
        alertType.value = error.type;
        alertMessage.value = error.message;

        setTimeout(() => {
            alertVisible.value = false;
        }, 5000);
    }
  };

  const getCoursData = async () => {
    try {
      const response = await apiFetch("/api/getCours/" + origin.id, {
        method: "GET",
        headers: { "Authorization": "Bearer " + localStorage.getItem("token")}}
      );

      const data = await response.json();
      const coursData = JSON.parse(data);

      formData.value.typeCours = coursData.typeCours.id;
      formData.value.dateCours = coursData.dateCours ? coursData.dateCours.slice(0, 16) : formData.value.dateCours;
      formData.value.dureeCours = coursData.dureeCours;
      formData.value.nbInscriptionMax = coursData.nbInscriptionMax;
      formData.value.description = coursData.description;


    } catch (error) {
        alertVisible.value = true;
        alertType.value = error.type;
        alertMessage.value = error.message;

        setTimeout(() => {
            alertVisible.value = false;
        }, 5000);
    }
  };

</script>

<template>
  <div>
      <v-alert v-model="alertVisible" :type="alertType" dismissible>
          {{ alertMessage }}
      </v-alert>
      <div class="title_wrapper">
          <h2>{{ origin.id ? "Modifier" : "Ajouter" }} un cours</h2>
      </div>

    <div class="buttonsFilters flex justify-start">
      <router-link :to="{name: 'CreateTypeCours'}"><CustomButton>Ajouter un type de Cours</CustomButton></router-link>
      <router-link :to="{name: 'EditTypeCours'}"><CustomButton>Modifier un type de Cours</CustomButton></router-link>
    </div>
    <form>
      <div class="grid grid-cols-2 gap-4">
        <CustomSelect item="Type de cours" id="typeCours" :error="errors.typeCours" v-model="formData.typeCours" :options="typeCoursList" required/>
        <CustomInput item="Durée (minutes)" type="Number" id="dureeCours" :error="errors.dureeCours" v-model="formData.dureeCours" required/>
        <CustomInput item="Date" type="datetime-local" id="dateCours" dureeCours :error="errors.dateCours" v-model="formData.dateCours" required/>
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
