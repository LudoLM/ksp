<script setup>
import {inject, onMounted, ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import CustomTextarea from "../components/forms/CustomTextarea.vue";
import CustomInput from "../components/forms/CustomInput.vue";
import CustomSelect from "../components/forms/CustomSelect.vue";
import CustomButton from "../components/forms/CustomButton.vue";
import {useValidationForm} from "../utils/useValidationForm";
import {useGetTypesCours} from "../utils/useActionCours";
import {apiFetch} from "../utils/useFetchInterceptor";
import Banner from "../components/Banner.vue";
import CustomValidationButton from "../components/forms/CustomValidationButton.vue";
import CustomCheckboxPriority from "../components/forms/CustomCheckboxPriority.vue";


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
    specialNote: null,
  },
)



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

const tzoffset = (new Date()).getTimezoneOffset() * 60000;
// Ajout d'un jour pour la date par défaut (l'utilisateur ne peut pas créer un cours le jour même)
const ajoutJour = 60 * 60 * 1000 * 24;
const localISOTime = (new Date(Date.now() - tzoffset + ajoutJour)).toISOString().slice(0, 16);
const alertStore = inject('alertStore');

const formData = ref({
  typeCours: null,
  dateCours: localISOTime,
  dureeCours: coursData.value ? coursData.value.dureeCours : 60,
  nbInscriptionMax: 12,
  specialNote: "Cours sympathique",
  hasPriority: true,
  hasLimitOfOneCoursPerWeek: true,
});

  const handleSubmit = async (event) => {
    event.preventDefault();

    const data = {
      typeCours: parseInt(formData.value.typeCours),
      dateCours: formData.value.dateCours,
      dureeCours: parseInt(formData.value.dureeCours),
      nbInscriptionMax: parseInt(formData.value.nbInscriptionMax),
      specialNote: formData.value.specialNote,
      hasPriority: formData.value.hasPriority,
      hasLimitOfOneCoursPerWeek: formData.value.hasLimitOfOneCoursPerWeek,
    };

    try {
      const response = await apiFetch(origin.id ? urlEdition : urlCreation, {
        method: origin.id ? "PUT" : "POST",
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (!response.ok) {
        await useValidationForm(result, errors);
      }

      if (response.status === 200){
        await router.push({
            name: "CoursAdmin",
        });

        alertStore.setAlert(origin.id ? "Cours modifié avec succès" : "Cours ajouté avec succès", "success");
      } else {
        console.error("Erreur lors de la création du cours:", result);
      }
    } catch (error) {
        alertStore.setAlert(error.message, error.type);
    }
  };


  const getCoursData = async () => {
    try {
      const response = await apiFetch("/api/getCours/" + origin.id, {
        method: "GET"
      });

      const data = await response.json();
      const coursData = JSON.parse(data);

      formData.value.typeCours = coursData.typeCours.id;
      formData.value.dateCours = coursData.dateCours ? coursData.dateCours.slice(0, 16) : formData.value.dateCours;
      formData.value.dureeCours = coursData.duree;
      formData.value.nbInscriptionMax = coursData.nbInscriptionMax;
      formData.value.specialNote = coursData.specialNote;
      formData.value.hasPriority = coursData.hasPriority;
      formData.value.hasLimitOfOneCoursPerWeek = coursData.hasLimitOfOneCoursPerWeek


    } catch (error) {
        alertStore.setAlert(error.message, error.type);
    }
  };

</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-screen p-4 dark:bg-gray-900">
        <div class="w-full max-w-2xl">
            <!-- Banner -->
            <Banner
                :title="(origin.id ? 'Modifier' : 'Ajouter') + ' un cours'"
                :textColor="'rgba(30, 27, 75, .9)'"
                backgroundHeight="20vh"
                :hasButton="false"
            />

            <!-- Buttons -->
            <div class="flex justify-end mt-4 gap-2">
                <router-link :to="{name: 'CreateTypeCours'}">
                    <CustomButton>Ajouter un type de Cours</CustomButton>
                </router-link>
                <router-link :to="{name: 'EditTypeCours'}">
                    <CustomButton>Modifier un type de Cours</CustomButton>
                </router-link>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <CustomSelect item="Type de cours" id="typeCours" :error="errors.typeCours" v-model="formData.typeCours" :options="typeCoursList" required />
                    <CustomInput item="Durée (minutes)" type="number" id="dureeCours" :error="errors.dureeCours" v-model="formData.dureeCours" required />
                    <CustomInput item="Date" type="datetime-local" id="dateCours" :error="errors.dateCours" v-model="formData.dateCours" required />
                    <CustomInput item="Nombre de places" type="number" id="nbInscriptionMax" :error="errors.nbInscriptionMax" v-model="formData.nbInscriptionMax" required />
                </div>
                <CustomTextarea item="Note" id="specialNote" :error="errors.specialNote" v-model="formData.specialNote" class="w-full" />
                <div class="flex justify-around items-center">
                    <CustomCheckboxPriority
                        v-model="formData.hasPriority"
                        id="hasPriority"
                    >
                        Prioritaire aux abonnés
                    </CustomCheckboxPriority>
                    <CustomCheckboxPriority
                        v-model="formData.hasLimitOfOneCoursPerWeek"
                        id="hasLimitOfOneCoursPerWeek"
                    >
                        Limite 2 cours/semaine
                    </CustomCheckboxPriority>
                </div>
                <div class="mt-4 flex justify-center">
                    <CustomValidationButton
                        class="w-full"
                    >
                        Valider
                    </CustomValidationButton>
                </div>
            </form>
        </div>
    </div>
</template>


<style scoped lang="scss">

</style>
