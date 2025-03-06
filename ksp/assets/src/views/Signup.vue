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
                            Créer un compte
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Renseignez les champs suivants
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
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
                                    text="Valider"
                                    class="w-full"
                                />
                            </div>
                        </form>
                        <div class="mt-5">
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
            <div
                class="banner relative items-center hidden w-full lg:w-1/2 h-full lg:grid lg:p-10"
            >
                <div class="overlay flex items-center justify-center">
                    <div class="logoWrapper flex flex-col items-center max-w-xs">
                        <router-link to="/" class="block mb-4">
                            <h3>Kiné Sport Santé</h3>
                        </router-link>
                        <p class="text-center text-gray-300 dark:text-white/70">
                            Soulagez vos douleurs et renforcez votre bien-être.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import CustomInput from "../components/forms/CustomInput.vue";
import CustomPassword from "../components/forms/CustomPassword.vue";
import {useValidationForm} from "../utils/useValidationForm";
import {useUserStore} from "../store/user";
import CustomValidationButton from "../components/forms/CustomValidationButton.vue";

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
const userStore = useUserStore();

const handleRedirection = () => {
    router.go(-1);
}

const handleSubmit = async () => {
    try {
        const response = await fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                prenom: firstName.value,
                nom: lastName.value,
                email: email.value,
                password: password.value,
                adresse: adress.value,
                cp: cp.value,
                commune: city.value,
                telephone: phone.value,
            }),
        });
        const data = await response.json();

        if (!response.ok) {
            await useValidationForm(data, errors);
        }
        else{
            localStorage.setItem('token', data.token);
            const payload = data.token.split('.')[1];
            const decoded = atob(payload);
            const dataToken = JSON.parse(decoded);
            userStore.setUserEmail(dataToken.username);
            userStore.setUserId(dataToken.id);
            userStore.setUserPrenom(dataToken.prenom);
            await router.push('/');
        }

    } catch (err) {
        console.error(err);
    }
}
</script>

<style scoped>


h1 {
    text-align: start;
}
.banner {
    background: url('../../images/imageBanner13.jpg') no-repeat center center / cover;

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
