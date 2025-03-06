<script setup>
import {inject, onMounted, ref, watch} from "vue";
import CustomInput from "../components/forms/CustomInput.vue";
import CustomSelect from "../components/forms/CustomSelect.vue";
import { useRouter } from 'vue-router';
import { useRoute } from 'vue-router';
import {useValidationForm} from "../utils/useValidationForm";
import {useGetTypesCours} from "../utils/useActionCours";
import {apiFetch} from "../utils/useFetchInterceptor";
import CustomTextarea from "../components/forms/CustomTextarea.vue";
import Banner from "../components/Banner.vue";
import CustomValidationButton from "../components/forms/CustomValidationButton.vue";
import CustomFileInput from "../components/forms/CustomFileInput.vue";

const formData = ref({
    nom: null,
    descriptif: null,
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
const alertStore = inject('alertStore');

const errors = ref({
    libelle: null,
    descriptif: null,
    thumbnail: null,
});

watch(() => route.name, (newRoute) => {
    origin.value = newRoute;
    formData.value.nom = null;
    formData.value.descriptif = null;
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
        formData.value.descriptif = selectedCours.descriptif;
        formData.value.image = null;
        existingImage.value = selectedCours.thumbnail;
        imagePreview.value = null;
    }
};

// Récupérer la liste des types de cours
const fetchData = async () => {
    try {
        await apiFetch("/api/getTypeCours", {
            method: "GET",
            headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
        });
        typeCoursList.value = await useGetTypesCours();

        if (origin.value === "EditTypeCours" && typeCoursList.value.length > 0) {
            typeCoursId.value = typeCoursList.value[0].id;
            formData.value.nom = typeCoursList.value[0].libelle;
            formData.value.descriptif = typeCoursList.value[0].descriptif;
            existingImage.value = typeCoursList.value[0].thumbnail;
        }
    } catch (error) {
        alertStore.setAlert(error.message, error.type);
    }
};

onMounted(() => {
    fetchData();
});

const handleSubmit = async (event) => {
    event.preventDefault();

    try {
        const data = new FormData();
        data.append("libelle", formData.value.nom);
        data.append("descriptif", formData.value.descriptif);

        // Si un fichier a été sélectionné, on l'ajoute
        if (formData.value.image) {
            data.append("image", formData.value.image);
        }

        const url = origin.value === "EditTypeCours" ? urlEdition + typeCoursId.value : urlCreation;

        const response = await apiFetch(url, {
            method: "POST",
            headers: {
                "Accept": "application/json",
            },
            body: data,
        });

        const result = await response.json();

        if (!response.ok) {
            await useValidationForm(result, errors);
        } else {
            router.push({ name: "CreateCours" });
        }
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
                :title="(origin.id ? 'Modifier' : 'Ajouter') + ' un type de cours'"
                :textColor="'rgba(30, 27, 75, .9)'"
                backgroundHeight="20vh"
                :hasButton="false"
            />

            <form @submit="handleSubmit" enctype="multipart/form-data">
                <div class="flex flex-col gap-4">
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

                    <CustomTextarea
                        type="text"
                        item="Descriptif"
                        id="descriptif"
                        v-model="formData.descriptif"
                        class="w-full"
                        :error="errors.descriptif"
                        required
                    />

                    <CustomFileInput
                        item="Nouvelle image"
                        class="w-full"
                        @change="onFileChange"
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
                <CustomValidationButton
                    :title="origin === 'CreateTypeCours' ? 'Ajouter' : 'Modifier'"
                    class="w-full"
                />
            </form>
        </div>
    </div>
</template>

<style scoped lang="scss">
</style>
