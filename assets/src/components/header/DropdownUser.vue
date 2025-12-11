<script setup>
import { computed } from 'vue'
import { useUserStore } from "../../store/user"
import { useRoute } from 'vue-router'
import { storeToRefs } from "pinia"
import Dropdown from "./Dropdown.vue"
import TypeMode from "../../../icons/adminActions/TypeMode.vue"
import LogoutIcon from "../../../icons/userActions/LogoutIcon.vue"
import UserIcon from "../../../icons/userActions/UserIcon.vue"
import ChevronIcon from "../../../icons/ChevronIcon.vue";

const userStore = useUserStore()
const { userId, userPrenom, userNom, userEmail, userNombreCours, isAdmin } = storeToRefs(userStore)
const route = useRoute()

const isAdminRoute = computed(() => route.path.startsWith('/admin'))

const modeLink = computed(() => {
    return isAdminRoute.value ? { name: 'Accueil' } : { name: 'AdminDashboard' }
})

const modeLabel = computed(() => {
    return isAdminRoute.value ? 'Mode Utilisateur' : 'Mode Admin'
})

const logout = async () => {
    await userStore.logout()
}
</script>

<template>
    <div id="compte">
        <!-- Utilisateur connecté -->
        <Dropdown v-if="userId">
            <template v-slot:default="{ isOpen, toggle, close }">
                <div class="profil flex gap-6">
                    <a
                    class="flex items-center gap-4 cursor-pointer"
                    @click.prevent="toggle"
                    >
                    <span class="pastille h-12 w-12 rounded-full flex justify-center items-center text-lg uppercase font-medium">
                            {{ userPrenom.charAt(0) }}
                            <span class="quantityCours">{{ userNombreCours }}</span>
                        </span>

                    <span v-if="userPrenom" class="hidden text-right lg:block">
                            <span class="block text-sm font-medium text-black dark:text-white">
                                {{ userPrenom }}
                            </span>
                        </span>

                    <ChevronIcon
                        class="hidden sm:block"
                        :is-open="isOpen"
                    />
                    </a>
                </div>
            </template>

            <template v-slot:content="{ close }">
                <ul class="flex flex-col gap-6 border-b border-stroke px-6 py-7.5">
                    <!-- User info -->
                    <li class="mb-4">
                        <div class="text-sm font-bold text-black">
                            {{ userPrenom + " " + userNom }}
                        </div>
                        <div class="text-xs font-medium text-black">
                            {{ userEmail }}
                        </div>
                        <div class="text-sm font-medium text-black mt-4 text-right">
                            <span class="font-bold text-lg">{{ userNombreCours }}</span>
                            crédit{{ userNombreCours > 1 ? 's' : '' }}
                        </div>
                    </li>

                    <!-- Mode Admin -->
                    <li v-if="isAdmin">
                        <router-link
                            :to="modeLink"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out"
                            @click="close"
                        >
                        <span class="flex items-center gap-4">
                            <TypeMode class="icon" size="18" />
                            {{ modeLabel }}
                        </span>
                        </router-link>
                    </li>

                    <!-- Profile -->
                    <li>
                        <router-link
                            :to="{ name: route.path.startsWith('/admin') ? 'AdminProfile' : 'Profile' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out"
                            @click="close"
                        >
                        <span class="flex items-center gap-4">
                            <UserIcon class="icon" size="18" />
                            Mon profil
                        </span>
                        </router-link>
                    </li>

                    <!-- Logout -->
                    <li>
                        <router-link
                            :to="{ name: 'Accueil' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out"
                            @click="logout(); close()"
                        >
                        <span class="flex items-center gap-4">
                            <LogoutIcon class="icon" size="18" />
                            Déconnexion
                        </span>
                        </router-link>
                    </li>
                </ul>
            </template>

        </Dropdown>

        <!-- Utilisateur non connecté -->
        <div v-else class="loginButtons">
            <router-link :to="{ name: 'Register' }" class="createCount">
                Créer un compte
            </router-link>
            <router-link :to="{ name: 'Login' }" class="identifier">
                <span class="identifier_text">Se connecter</span>
            </router-link>
        </div>
    </div>
</template>

<style scoped lang="scss">
.pastille {
    border: 1px solid rgba(0, 0, 0, 0.4);
    color: rgba(0, 0, 0, 0.4);
    position: relative;

    .quantityCours {
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

.icon {
    box-sizing: border-box;
    border: 1px solid rgba(0, 0, 0, 0.4);
    color: rgba(0, 0, 0, 0.4);
    border-radius: 50%;
    padding: 10px;
    width: 40px;
    height: 40px;
    transition: all 0.3s ease;

    &:hover {
        color: #000;
        border-color: #000;
    }
}

.profil {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-right: 8px;
}

.loginButtons {
    display: flex;
    gap: 10px;
}

.createCount {
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

.identifier {
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

:deep(.dropdown-wrapper) {
    .dropdown {
        right: 0;
        transform: translateX(0%) translateY(0);
    }
}


@media (max-width: 1100px) {
    .createCount {
        display: none;
    }
}

@media (max-width: 768px) {
    .loginButtons {
        display: none;
    }
}
</style>
