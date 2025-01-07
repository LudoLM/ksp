<script setup>
import {onMounted, ref, watch} from "vue";
import CustomButton from "../components/CustomButton.vue";
import CustomInput from "../components/CustomInput.vue";
import CustomSelect from "../components/CustomSelect.vue";
import { useRouter } from 'vue-router';
import { useRoute } from 'vue-router';
import {useValidationForm} from "../utils/useValidationForm";
import {useGetTypesCours} from "../utils/useActionCours";


const formData = ref({
  nom: null,
  image: null,
});

const router = useRouter();
const route = useRoute();
const typeCoursId = ref(null);
const typeCoursList = ref([]);
const origin = ref(route.name);
const existingImage = ref(null);
const imagePreview = ref(null);


const urlCreation = "/api/typeCours/create";
const urlEdition = "/api/typeCours/edit/";


const errors = ref({
  libelle: null,
  thumbnail: null,
});

watch(() => route.name, (newRoute) => {
  origin.value = newRoute;
  formData.value.nom = null;
  existingImage.value = null;
  imagePreview.value = null;
  fetchData();
});

// Fonction pour capturer le fichier
const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    formData.value.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const onTypeCoursChange = (event) => {
    typeCoursId.value = event.target.value;
    const selectedCours = typeCoursList.value.find(cours => cours.id === parseInt(typeCoursId.value));
    if (selectedCours) {
        formData.value.nom = selectedCours.libelle;
        formData.value.image = null;
        existingImage.value = selectedCours.thumbnail;
        imagePreview.value = null;
    }
};

// Récupérer la liste des types de cours
const fetchData = async () => {
  try {
    await fetch("/api/getTypeCours", {
      method: "GET",
      headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
    });
    typeCoursList.value = await useGetTypesCours();

    if (origin.value === "EditTypeCours" && typeCoursList.value.length > 0) {
      typeCoursId.value = typeCoursList.value[0].id;
      formData.value.nom = typeCoursList.value[0].libelle;
      existingImage.value = typeCoursList.value[0].thumbnail;
    }
  } catch (error) {
    console.error("Erreur lors de la récupération des cours:", error);
  }
};

onMounted(() => {
  fetchData();
});

const handleSubmit = async (event) => {
  event.preventDefault();

  const data = new FormData();
  data.append("nom", formData.value.nom);
  data.append("image", formData.value.image);

  const url = origin.value === "EditTypeCours" ? urlEdition + typeCoursId.value : urlCreation;

  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Authorization": "Bearer " + localStorage.getItem("token")
    },
    body: data
  });

  if (!response.ok) {
    await useValidationForm(response, errors);
  }
  else{
      router.push({name:"CreateCours"});

  }
};
</script>

<template>
  <h1>{{ origin === "EditTypeCours" ? "modifier" : "ajouter" }} un type de cours</h1>

  <form @submit="handleSubmit" enctype="multipart/form-data">
    <div class="grid grid-cols-2 gap-4">
      <CustomSelect
          v-if="origin === 'EditTypeCours'"
          item="Type de cours à modifier"
          v-model="typeCoursId"
          @change="onTypeCoursChange"
          :options="typeCoursList"
          required
      />

      <CustomInput
          type="text"
          item="Nouveau nom"
          id="nouveauNom"
          v-model="formData.nom"
          class="w-full"
          :error="errors.libelle"
          required
      />

      <CustomInput
          type="file"
          item="Nouvelle image"
          id="image"
          @change="onFileChange"
          class="w-full"
          :error="errors.thumbnail"
      />

      <div v-if="imagePreview">
        <p>Image sélectionnée :</p>
        <img :src="imagePreview" alt="Image sélectionnée" class="max-w-xs" />
      </div>

      <div v-else-if="existingImage">
        <p>Image actuelle :</p>
        <img :src="require(`../../images/uploads/${existingImage}`)" alt="Image actuelle" class="max-w-xs" />
      </div>
    </div>

    <div class="flex justify-center">
      <CustomButton>{{ origin === "CreateTypeCours" ? "Ajouter" : "Modifier" }}</CustomButton>
    </div>
  </form>
</template>

<style scoped lang="scss">
</style>
