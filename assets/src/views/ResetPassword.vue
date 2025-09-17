<script setup>

    import { ref, inject } from 'vue';
    import CustomPassword from "../components/forms/CustomPassword.vue";
    import {useRoute, useRouter} from "vue-router";
    import SideBannerAuth from "../components/SideBannerAuth.vue";
    import {useUserStore} from "../store/user";

    const password = ref('');
    const confirmPassword = ref('');
    const error = ref('');
    const success = ref('');
    const route = useRoute();
    const alertStore = inject('alertStore');
    const userStore = useUserStore();
    const router = useRouter();

    const token = route.params.token;
    const userId = route.params.id;

    const handleSubmit = async () => {
        try {
            if (password.value !== confirmPassword.value) {
               error.value = "Les mots de passe ne correspondent pas";
               return;
            }
            const response = await fetch('/api/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    userId: userId,
                    password: password.value,
                    token: token,
                }),
            });

            if (!response.ok) {
                const responseError = await response.json();
                if (responseError.violations) {
                    error.value = responseError.violations[0].title;
                    return;
                }
                else{
                    error.value = responseError.detail;
                    return;
                }
            }

            const data = await response.json();
            success.value = data.message;
            alertStore.setAlert(success.value, 'success');
            router.push("/");
        } catch (err) {
            error.value = err.message;
            alertStore.setAlert(error.value, 'error');
        }
    };

</script>
<template>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div
            class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900"
        >
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                Réinitialiser votre mot de passe
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Entrez un nouveau mot de passe de 8 caractères minimum
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
                                    <CustomPassword
                                        v-model="password"
                                        label="Mot de passe"
                                        placeholder="Nouveau mot de passe"
                                        required
                                    />
                                    <CustomPassword
                                        v-model="confirmPassword"
                                        label="Confirmation"
                                        placeholder="Confirmer mot de passe"
                                        required
                                    />
                                    <input type="hidden" name="token" :value="token" />
                                    <input type="hidden" name="userId" :value="userId" />
                                    <div class="error-message text-red-500 text-xs h-4">
                                        {{ error }}
                                    </div>
                                </div>
                                <!-- Button -->
                                <div>
                                    <button
                                        type="submit"
                                        class="flex items-center justify-center w-full px-4 py-3 mt-10 text-sm font-medium text-white transition rounded-lg shadow-theme-xs hover:bg-brand-600"
                                    >
                                        Réinitialiser le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <SideBannerAuth/>
        </div>
    </div>
</template>

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
</style>

