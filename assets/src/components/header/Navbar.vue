<template>
    <div class="hamburger">
        <Hamburger
            @click="toggleNavLinks"
            :isClose="isNavOpen"
        />
    </div>
    <nav
        v-if="isAdminPath"
        id="nav_links"
        :class="{ active: isNavOpen }"
        class="gap-8 justify-start"
    >
        <router-link :to="{ name: 'Statistiques' }">
            Tableau de bord
        </router-link>
        <div class="relative" ref="target">
            <router-link
                class="flex items-center gap-4"
                to=""
                @click.prevent="dropdownOpen = !dropdownOpen"
            >
        <span class="hidden text-right lg:block">
          <a class="block text-black dark:text-white">Cours</a>
        </span>
                <svg
                    :class="{ 'rotate-180': dropdownOpen }"
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
            </router-link>
            <div
                v-show="dropdownOpen"
                class="absolute left-0 mt-4 flex w-62.5 flex-col rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark"
                @mouseleave="dropdownOpen = false"
            >
                <ul class="flex flex-col gap-5 border-b border-stroke px-6 py-7.5 dark:border-strokedark">
                    <li>
                        <router-link
                            :to="{ name: 'CoursAdmin' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                        >
                            Liste des cours
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            :to="{ name: 'CreateCours' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                        >
                            Créer un cours
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            :to="{ name: 'CreateTypeCours' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                        >
                            Créer un type de cours
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            :to="{ name: 'EditTypeCours' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                        >
                            Modifier un type de cours
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            :to="{ name: 'CreateWeekType' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                        >
                            Créer une semaine type
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            :to="{ name: 'ControlUser' }"
                            class="flex items-center gap-3.5 text-sm font-medium duration-300 ease-in-out lg:text-base"
                        >
                            Gestion des utilisateurs
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <nav v-else id="nav_links" :class="{ active: isNavOpen }">
        <router-link
            v-for="route in routes"
            :key="route.name + route.path"
            :to="route.path"
            @click="closeNavLinks"
        >
            {{ route.name }}
        </router-link>

        <div class="loginLinks" v-if="!userId">
            <router-link :to="{name: 'Login'}" @click="closeNavLinks">Se connecter</router-link>
            <router-link :to="{name: 'Register'}" @click="closeNavLinks">Créer un compte</router-link>
        </div>
        <div v-else class="loginLinks">
            <a @click="logout">Se déconnecter</a>
            <router-link :to="{name: 'Packs'}" @click="closeNavLinks">Acheter packs</router-link>
        </div>
        <div class="mailSocialPin">
            <div class="icon">
                <a href="">
                    <img src="../../../icons/facebook.svg" alt=""/>
                </a>
                <a :href="'mailto:' + store.fullMail">
                    <img src="../../../icons/mail.svg" alt=""/>
                </a>
                <a href="https://maps.app.goo.gl/ApZ1E35srhDT2ynK7">
                    <div class="flex gap-3">
                        <img src="../../../icons/pin.svg"/>
                    </div>
                </a>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import {useUserStore} from "../../store/user";
import { onClickOutside } from "@vueuse/core";
import Hamburger from "../Hamburger.vue";
import { infos } from "../../store/index";

const dropdownOpen = ref(false);
const isNavOpen = ref(false);
const route = useRoute();
const router = useRouter();
const target = ref(null);
const userStore = useUserStore();
const userId = computed(() => userStore.userId);
const store = infos();

const isAdminPath = computed(() => route.path.startsWith("/admin"));

onClickOutside(target, () => {
    dropdownOpen.value = false;
});


const routes = computed(() =>
    router.getRoutes().filter((r) =>
            r.name && ![
                "CoursDetails",
                "CreateCours",
                "Login",
                "Register",
                "AcheterCours",
                "Merci",
                "Profile",
                "EditCours",
                "CreateTypeCours",
                "EditTypeCours",
                "admin",
                "CoursAdmin",
                "Statistiques",
                "Cours",
                "AdminProfile",
                "AdminCoursDetails",
                "ResetPassword",
                "EditProfile",
                "CreateWeekType"
            ].includes(r.name)
    )
);

const toggleNavLinks = () => {
    isNavOpen.value = !isNavOpen.value;
};

const closeNavLinks = () => {
    isNavOpen.value = false;
};

const logout = () => {
    userStore.logout();
    closeNavLinks();
    router.push({name: 'Accueil'});
};

</script>

<style lang="scss" scoped>
.hamburger {
    display: none;
}

#nav_links {
    display: flex;
    justify-content: space-around;
    align-items: center;
    height: 100%;

    .loginLinks, .mailSocialPin {
        display: none;
    }

    @media (max-width: 900px) {
        position: absolute;
        flex-direction: column;
        justify-content: flex-start;
        top: 70px;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 1);
        width: 100%;
        height: 100vh;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;
        padding-top: 50px;

        > a {
            display: flex;
            width: 80%;
            height: 10vh;
            border-bottom: 1px solid #575656;
        }

        .loginLinks {
            width: 80%;
            height: 10vh;
            border-bottom: 1px solid #575656;
            display: flex;
            justify-content: flex-start;
            gap: 40px;
            color: #e2a945;
        }

        .mailSocialPin{
            width: 80%;
            height: 5vh;
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;

            .icon{
                display: flex;
                justify-content: flex-start;
                align-items: center;

                a{
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    width: 50px;
                    height: 50px;
                    padding: 10px;
                    border-radius: 50%;
                    border: 2px solid #000;
                    margin-right: 10px;
                    transition: border .2s ease-in-out;
                }
            }

        }
    }
}

a.router-link-exact-active {
    font-weight: 700;
    color: #4f2794;
}

#nav_links a {
    font-weight: 900;
    font-size: clamp(0.8rem, 1.2vw, .8rem);
    text-transform: uppercase;
    text-decoration: none;
    position: relative;
    display: flex;
    justify-content: start;
    align-items: center;

    &.router-link-exact-active {
        color: #4f2794;
    }

    @media (min-width: 900px) {
        &::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 0;
            height: 2px;
            background: #4f2794;
            transition: width 0.3s ease-in-out, left 0.3s ease-in-out;
        }

        &:hover::after {
            left: 0;
            width: 100%;
        }
    }
}

.fixed {
    height: 70px;
    background: rgba(255, 255, 255, 1);
}

nav {
    width: 50vw;
    color: #3c3c3c;
}

.hamburger {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-left: 15px;
}

.nav_wrapper.fixed {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
}

@media (min-width: 900px) {
    .hamburger {
        display: none;
    }
}

@media (max-width: 900px) {
    #nav_links.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        transition: opacity 0.3s ease, transform 0.3s ease;
        align-items: flex-start;
        padding-left: 50px;
        border-bottom: 1px solid #000;
    }

    #nav_links:not(.active) {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;
    }
}

@media (min-width: 768px) {
    nav {
        display: flex;
    }
}

@media (max-width: 768px) {
    #icon {
        display: none;
    }
}
</style>
