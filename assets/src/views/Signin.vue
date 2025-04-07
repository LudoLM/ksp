<template>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div
            class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900"
        >
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="w-full max-w-md pt-10 mx-auto">
                    <div @click="handleRedirection" class="handleRedirection inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
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
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Retour
                    </div>
                </div>
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                Connectez-vous
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Entrez vos informations d'identification
                            </p>
                        </div>
                        <div>
                            <div class="relative py-3 mb-6 sm:py-5">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                                </div>
                            </div>
                            <form @submit.prevent="handleSubmit">
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <CustomInput
                                        item="Email"
                                        type="email"
                                        id="username"
                                        placeholder="info@gmail.com"
                                        v-model="username"
                                    />
                                    <!-- Password -->
                                    <CustomPassword
                                        v-model="password"
                                    />
                                    <div
                                        :class="error ? 'opacity-100' : 'opacity-0'"
                                        class="h-auto text-red-600 transition-opacity duration-300"
                                    >
                                        {{ error }}
                                    </div>
                                    <ModalResetPassword
                                        :isOpen="resetPasswordDialog"
                                        title="Réinitialiser votre mot de passe"
                                        message="Veuillez renseigner votre email."
                                    >
                                    Mot de passe oublié?
                                    </ModalResetPassword>
                                </div>
                                <!-- Button -->
                                <div>
                                    <button
                                        type="submit"
                                        class="flex items-center justify-center w-full px-4 py-3 mt-10 text-sm font-medium text-white transition rounded-lg shadow-theme-xs hover:bg-brand-600"
                                    >
                                        Connexion
                                    </button>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p class="text-sm font-normal text-gray-700 dark:text-gray-400 text-start">
                                    Vous n'avez pas de compte?
                                    <router-link
                                        to="/register"
                                    >Créez-en un </router-link
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <SideBannerAuth/>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'
import CustomInput from "../components/forms/CustomInput.vue";
import {useUserStore} from "../store/user";
import CustomPassword from "../components/forms/CustomPassword.vue";
import SideBannerAuth from "../components/SideBannerAuth.vue";
import ModalResetPassword from "../components/modal/ModalResetPassword.vue";


// Instancier le store en dehors de la fonction handleLogin
const username = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();
const resetPasswordDialog = ref(false);

const handleRedirection = () => {
    router.go(-1);
}

const handleSubmit = async () => {
    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username.value, password: password.value }),
        });

        if (!response.ok) {
            const responseError = await response.json();

            throw new Error(responseError.message);
        }

        const data = await response.json();
        // Stocker le token et mettre à jour le store
        localStorage.setItem('token', data.token);
        // Redirige vers la page d'accueil après la connexion réussie
        await router.push('/');
    } catch (err) {
        error.value = "Les informations d'identification sont incorrectes";
    }
}

</script>


<style scoped>
    h1 {
        text-align: start;
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

    .error{
        color: red;
    }
</style>
