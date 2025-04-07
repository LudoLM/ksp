<script setup>
import { onClickOutside } from '@vueuse/core'
import {computed, onMounted, ref} from 'vue'
import {useUserStore} from "../../store/user";
import useGetElementsToken from "../../utils/useGetElementsToken";
import { useRouter, useRoute } from 'vue-router';
import TypeMode from "../../../icons/adminActions/TypeMode.vue";
import LogoutIcon from "../../../icons/userActions/LogoutIcon.vue";
import UserIcon from "../../../icons/userActions/UserIcon.vue";
import {apiFetch} from "../../utils/useFetchInterceptor";
import {storeToRefs} from "pinia";

const target = ref(null)
const dropdownOpen = ref(false)
const userStore = useUserStore();
const { userId, userPrenom, userNom, userEmail, userNombreCours } = storeToRefs(userStore);


const router = useRouter();
const route = useRoute();
const role = computed(() => useGetElementsToken().roles[0].split("_")[1].toLowerCase());

// Computed pour savoir si on est en mode admin
const isAdminRoute = computed(() => route.path.startsWith('/admin'))

// Destination dynamique
const modeLink = computed(() => {
    return isAdminRoute.value
        ? { name: 'Accueil' }
        : { name: 'Statistiques' }
})

// Label du bouton
const modeLabel = computed(() => {
    return isAdminRoute.value
        ? 'Mode Utilisateur'
        : 'Mode Admin'
})

const { isScrolled } = defineProps({
    isScrolled: {
        type: Boolean,
        default: false
    }
});

const logout = () => {
    userStore.logout();
    router.push({name: 'Accueil'});
};

onMounted(async () => {
    const token = localStorage.getItem('token');
    if (token){
        const result = await apiFetch("/api/user", {
            method: "GET",
        });

        const user = await result.json();
        // Mettre à jour le store avec l'email de l'utilisateur et l'état d'authentification
        userStore.setUserEmail(user.email);
        userStore.setUserId(user.id);
        userStore.setUserNom(user.nom);
        userStore.setUserNombreCours(user.nombreCours)
        userStore.setUserPrenom(user.prenom);
    }
});

onClickOutside(target, () => {
    dropdownOpen.value = false
});

</script>


<template>
    <div
        id="header_container"
        :class="{ isScrolled }"
    >
        <div id="compte">
            <div v-if="userId" class="profil flex gap-6">
                <div class="relative" ref="target">
                    <a
                        class="flex items-center gap-4"
                        @click.prevent="dropdownOpen = !dropdownOpen"
                    >
                    <span
                        v-if="userId"
                        class="pastille h-12 w-12 rounded-full flex justify-center items-center text-lg uppercase font-medium">
                        {{ userPrenom.charAt(0) }}
                        <span class="quantityCours">
                            {{ userNombreCours }}
                        </span>
                    </span>
                        <span
                            v-if="userPrenom"
                            class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-black dark:text-white">{{ userPrenom }}</span>
                    </span>

                        <svg
                            :class="dropdownOpen && 'rotate-180'"
                            class="hidden fill-current sm:block"
                            width="12"
                            height="8"
                            viewBox="0 0 12 8"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M0.410765 0.910734C0.736202 0.585297 1.26384 0.585297 1.58928 0.910734L6.00002 5.32148L10.4108 0.910734C10.7362 0.585297 11.2638 0.585297 11.5893 0.910734C11.9147 1.23617 11.9147 1.76381 11.5893 2.08924L6.58928 7.08924C6.26384 7.41468 5.7362 7.41468 5.41077 7.08924L0.410765 2.08924C0.0853277 1.76381 0.0853277 1.23617 0.410765 0.910734Z"
                                fill=""
                            />
                        </svg>
                    </a>

                    <transition name="dropdown-fade">
                    <!-- Dropdown Start -->
                    <div
                        v-show="dropdownOpen"
                        :class="{ isScrolled }"
                        class="dropdown absolute right-0 flex w-62.5 flex-col border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark"
                    >
                        <ul class="flex flex-col gap-6 border-b border-stroke px-6 py-7.5 dark:border-strokedark">
                            <li
                                v-if="userId"
                                class="mb-4"
                            >
                                <div class="text-sm font-bold text-black dark:text-white">
                                    {{ userPrenom + " " + userNom }}
                                </div>
                                <div class="text-xs font-medium text-black dark:text-white">
                                    {{ userEmail }}
                                </div>
                                <div class="text-sm font-medium text-black dark:text-white mt-4 text-right">
                                    <span class="font-bold text-lg">{{ userNombreCours }}</span> crédit{{ userNombreCours > 1 ? 's' : '' }}
                                </div>
                            </li>
                            <li>
                                <router-link
                                    v-if="role === 'admin'"
                                    :to="modeLink"
                                    class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                                >
                                    <span
                                        class="flex items-center gap-4">
                                        <TypeMode
                                            class="icon"
                                            size="18"
                                        />
                                        {{ modeLabel }}
                                    </span>
                                </router-link>
                            </li>
                            <li>
                                <router-link
                                    :to='{name: route.path.startsWith("/admin") ? "AdminProfile" : "Profile"}'
                                    class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                                >
                                    <span class="flex items-center gap-4">
                                        <UserIcon
                                            class="icon"
                                            size="18"
                                        />
                                        Mon profil
                                    </span>
                                </router-link>
                            </li>
                            <li>
                                <router-link
                                    :to="{ name: 'Accueil'}"
                                    class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                                    @click="logout"
                                >
                           <span
                               class="flex items-center gap-4"
                           >
                               <LogoutIcon
                                   class="icon"
                                   size="18"
                               />
                                Déconnexion
                           </span>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                    </transition>
                </div>
                <!-- Dropdown End -->
            </div>
            <div v-else>
                <div class="loginButtons">
                    <router-link :to='{name: "Register"}' class="createCount">Créer un compte</router-link>
                    <router-link :to='{name: "Login"}' class="identifier">
                        <img src="" alt="">
                        <span class="identifier_text">Se connecter</span></router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
#header_container {

    color: #2e2e2e;
    font-size: clamp(0.8rem, 1.4vw, 1.2rem);

    .dropdown{
        top: 87px;
        border-radius: 3px;
        transition: top 0.3s ease-in-out;
        background: rgba(255, 255, 255, 0.95);

        .text-sm{
            font-size: clamp(0.8rem, 1vw, 1rem);
        }
    }

    .pastille{
        border: 1px solid rgba(0, 0, 0, 0.4);
        color: rgba(0, 0, 0, 0.4);
        position: relative;

        .quantityCours{
            position: absolute;
            bottom: -3px;
            right: -3px;
            z-index: 10;
            background: radial-gradient(#551360, #472371);
            border-radius: 50%;
            color: #fff;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
        }
    }

    span{
        &:hover .icon{
            color: #000;
            border: 1px solid #000;
            cursor: pointer;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
        }
    }

    .isScrolled{
        top: 60px;
    }

    .profil {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-right: 8px;
    }

    .loginButtons{
        display: flex;
        gap: 10px;
    }

    .createCount, .user{
        display: flex;
        justify-content: center;
        align-items: center;
        color: #472371;
        border: 2px solid #472371;
        border-radius: 5px;
        width: 130px;
        height: 40px;
        font-weight: 400;
        font-size: clamp(.7rem, 1.2vw, .6rem);
    }

    .identifier{
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        width: 130px;
        height: 40px;
        background: #472371;
        border-radius: 5px;
        color: #dfdfdf;
        font-weight: 400;
        font-size: clamp(.7rem, 1.2vw, .6rem);
    }

    .logout{
        display: flex;
        justify-content: center;
        align-items: center;
        width: 35px;
        height: 35px;
        background: #5e2ca5;
        border-radius: 5px;
        color: #dfdfdf;
        font-weight: 400;
        font-size: clamp(0.8rem, 1.4vw, 1.2rem);
    }

    .user{
        border: none;

        svg{
            fill: #5e2ca5;
        }
    }


    .icon{
        box-sizing: border-box;
        border: 1px solid rgba(0, 0, 0, 0.4);
        color: rgba(0, 0, 0, 0.4);
        border-radius: 50%;
        padding: 10px;
        width: 40px;
        height: 40px;
    }

    .dropdown-fade-enter-active,
    .dropdown-fade-leave-active {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .dropdown-fade-enter-from,
    .dropdown-fade-leave-to {
        opacity: 0;
        transform: translateY(-10px);
    }
    .dropdown-fade-enter-to,
    .dropdown-fade-leave-from {
        opacity: 1;
        transform: translateY(0);
    }


    @media (min-width: 900px) {
        .createCount {
            display: flex;
        }
        .loginButtons{
            display: flex;
        }
    }

    @media (max-width: 900px) {
        .createCount {
            display: none;
        }
    }

    @media (max-width: 700px) {
        .loginButtons {
            display: none;
        }
    }

    @media (max-width: 1100px) {
        .createCount {
            display: none;
        }
    }

}

</style>



