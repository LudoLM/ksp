<script setup>
import { onMounted, ref } from "vue";
import CustomButton from "../components/CustomButton.vue";
import CustomInput from "../components/CustomInput.vue";
import CustomSelect from "../components/CustomSelect.vue";
import { useRouter } from 'vue-router';


const formData = ref({
  nom: null,
  image: null,
});

const router = useRouter();
const typeCoursId = ref(null);
const typeCoursList = ref([]);
const origin = window.location.pathname.split("/").pop();
const existingImage = ref(null);
const imagePreview = ref(null);

const urlCreation = "/api/typeCours/create";
const urlEdition = "/api/typeCours/edit/";

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
  const selectedCours = typeCoursList.value.find(cours => cours.id == typeCoursId.value);
  if (selectedCours) {
    formData.value.nom = selectedCours.libelle;
    formData.value.image = null;
    existingImage.value = selectedCours.thumbnail;
    document.querySelector("#image").value = null;
    imagePreview.value = null;
  }
};

// Récupérer la liste des types de cours
const fetchData = async () => {
  try {
    const response = await fetch("/api/getTypeCours", {
      method: "GET",
      headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
    });
    typeCoursList.value = await response.json();

    if (origin === "edit" && typeCoursList.value.length > 0) {
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


  const url = origin === "edit" ? urlEdition + typeCoursId.value : urlCreation;

  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Authorization": "Bearer " + localStorage.getItem("token")
    },
    body: data
  });

  await response.json();
  router.go(-1);

};
</script>

<template>
  <h1>{{ origin === "edit" ? "modifier" : "ajouter" }} un type de cours</h1>

  <form @submit="handleSubmit" enctype="multipart/form-data">
    <div class="grid grid-cols-2 gap-4">
      <CustomSelect
          v-if="origin === 'edit'"
          item="Type de cours à modifier"
          id="selectTypeCours"
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
          required
      />

      <CustomInput
          type="file"
          item="Nouvelle image"
          id="image"
          @change="onFileChange"
          class="w-full"
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
      <CustomButton>{{ origin === "add" ? "Ajouter" : "Modifier" }}</CustomButton>
    </div>
  </form>
</template>

<style scoped lang="scss">
</style>
