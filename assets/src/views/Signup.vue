<template>
    <div class="relative p-0 bg-white z-1 dark:bg-gray-900 sm:p-0 h-screen overflow-hidden">
        <div
            class="relative flex flex-col justify-center w-full h-full lg:flex-row dark:bg-gray-900"
        >
            <div
                class="flex flex-col flex-1 w-full lg:w-1/2 h-full overflow-y-auto p-10 px-6"
            >
                <!-- Lien Retour -->
                <div class="w-full">
                    <div
                        @click="handleRedirection"
                        class="handleRedirection inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        <svg
                            class="stroke-current"
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M12.7083 5L7.5 10.2083L12.7083 15.4167"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <span class="ml-2">Retour</span>
                    </div>
                </div>

                <!-- Formulaire -->
                <div class="flex flex-col justify-center flex-1 w-full mx-auto mt-6">
                    <div class="mb-5 sm:mb-8">
                        <h1
                            class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md"
                        >
                            {{ isEditProfileRoute ? "Modifier votre compte" : "Créer un compte" }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ isEditProfileRoute ? "Modifier les champs souhaités" : "Renseignez les champs suivants" }}
                        </p>
                    </div>
                    <div>
                        <form @submit.prevent="handleSubmit">
                            <div class="space-y-5">
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <!-- Prénom -->
                                    <CustomInput
                                        item="Prénom"
                                        type="text"
                                        id="prenom"
                                        placeholder="Robert"
                                        v-model="firstName"
                                        :error="errors.prenom"
                                        isRequired
                                    />
                                    <!-- Nom -->
                                    <CustomInput
                                        item="Nom"
                                        type="text"
                                        id="nom"
                                        placeholder="Zimmerman"
                                        v-model="lastName"
                                        :error="errors.nom"
                                        isRequired
                                    />
                                </div>
                                <div
                                    v-if="!isEditProfileRoute"
                                    class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <!-- Email -->
                                    <CustomInput
                                        item="Email"
                                        type="email"
                                        id="email"
                                        placeholder="bobDylan@gmail.com"
                                        v-model="email"
                                        :error="errors.email"
                                        isRequired
                                    />
                                    <!-- Mot de passe -->
                                    <CustomPassword
                                        v-model="password"
                                        :error="errors.password"
                                        isRequired
                                    />
                                </div>
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <!-- Ville -->
                                    <CustomInput
                                        item="Ville"
                                        type="text"
                                        id="city"
                                        placeholder="Rennes"
                                        v-model="city"
                                        :error="errors.ville"
                                    />
                                    <!-- Code Postal -->
                                    <CustomInput
                                        item="Code Postal"
                                        type="text"
                                        id="cp"
                                        placeholder="35000"
                                        v-model="cp"
                                        :error="errors.cp"
                                    />
                                </div>
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <!-- Adresse -->
                                    <CustomInput
                                        item="Adresse"
                                        type="text"
                                        id="adresse"
                                        placeholder="12 rue de la paix"
                                        v-model="adress"
                                        :error="errors.adresse"
                                    />
                                    <!-- Téléphone -->
                                    <CustomInput
                                        item="Téléphone"
                                        type="text"
                                        id="phone"
                                        placeholder="06XXXXXXXX"
                                        v-model="phone"
                                        :error="errors.telephone"
                                        isRequired
                                    />
                                </div>
                                <!-- Bouton -->
                                <CustomValidationButton
                                    class="w-full"
                                >
                                {{ isEditProfileRoute ? "Modifier" : "Valider" }}
                                </CustomValidationButton>
                            </div>
                        </form>
                        <div class="mt-5" v-if="!isEditProfileRoute">
                            <p
                                class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start"
                            >
                                Déjà un compte ?
                                <router-link
                                    to="/login"
                                    class="text-brand-500 hover:text-brand-600 dark:text-brand-400"
                                >Connectez-vous</router-link
                                >
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section de Droite -->
            <SideBannerAuth/>
        </div>
    </div>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import CustomInput from "../components/forms/CustomInput.vue";
import CustomPassword from "../components/forms/CustomPassword.vue";
import {useValidationForm} from "../utils/useValidationForm";
import CustomValidationButton from "../components/forms/CustomValidationButton.vue";
import SideBannerAuth from "../components/SideBannerAuth.vue";
import {apiFetch} from "../utils/useFetchInterceptor";
import {useUserStore} from "../store/user";


const firstName = ref('')
const lastName = ref('')
const email = ref('')
const password = ref('')
const adress = ref('');
const cp = ref('');
const city = ref('');
const phone = ref('');
const errors = ref({
    firstName: null,
    lastName: null,
    email: null,
    password: null,
    adress: null,
    cp: null,
    city: null,
    phone: null,
});
const router = useRouter();
const route = useRoute();
const isEditProfileRoute =  ref(route.name === 'EditProfile' || route.name === 'AdminEditProfile');

const getUser = async (userId) => {
    const url = userId ? `/api/user/${userId}` : `/api/user`;
    const response = await apiFetch(url, {
        method: "GET",
    });
    return await response.json();

};


const handleRedirection = () => {
    router.go(-1);
}

const handleSubmit = async () => {
    const url = ref('');
    const data = ref({});
    if (isEditProfileRoute.value) {
        url.value = route.params.id ? `/api/edit-user/${route.params.id}` : `/api/edit-user`;
        data.value = {
            prenom: firstName.value,
            nom: lastName.value,
            adresse: adress.value,
            cp: cp.value,
            commune: city.value,
            telephone: phone.value,
        }
    } else {
        url.value = `/api/register`;
        data.value = {
            prenom: firstName.value,
            nom: lastName.value,
            email: email.value,
            password: password.value,
            adresse: adress.value,
            cp: cp.value,
            commune: city.value,
            telephone: phone.value,
        }
    }

    try {
        const fetchMethod = isEditProfileRoute.value ? apiFetch : fetch;
        const response = await fetchMethod(url.value, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data.value),
        });
        const result = await response.json();
        if (!response.ok) {
            await useValidationForm(result, errors);
        }
        else{
            //Si c'est une édition de profil et qu'on est admin, on retourne sur le profil de l'utilisateur édité
            if (route.params.id && isEditProfileRoute.value) {
                await getUser(route.params.id)
                await router.push({ name: 'AdminProfile', params: { id: route.params.id } });

            }
            //Sinon on retourne sur la page d'accueil ou le profil de l'utilisateur
            else{
                const user = await getUser()
                useUserStore().setUser(user);
                await router.push({ name: !isEditProfileRoute.value ? 'Accueil' : 'Profile' });
            }
        }

    } catch (err) {
        console.error(err);
    }
}


onMounted(async () => {
    if (isEditProfileRoute.value) {
        const userData = route.params.id ?  await getUser(route.params.id) : await getUser('');
        firstName.value = userData.prenom || "";
        lastName.value = userData.nom || "";
        adress.value = userData.adresse || "";
        cp.value = userData.codePostal || "";
        city.value = userData.commune || "";
        phone.value = userData.telephone || "";
    }
});

</script>

<style scoped>


h1 {
    text-align: start;
}
.banner {
    background: url('../../images/banners/imageBanner13.jpg') no-repeat center center / cover;

    .overlay {
        background: rgba(30, 27, 75, .9);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    h3{
        color: #fff;
    }

    p{
        color: #d9d9d9;
        font-size: clamp(0.6rem, 1.2vw, .8rem);
    };

}

button{
    background-color: #472371;

    &:hover{
        background-color: #5f3f71;
    }
}

a{
    color: #e2a945;
}

.handleRedirection{
    cursor: pointer;
}
</style>
