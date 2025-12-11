<template>
    <nav id="nav_links" :class="{ active: isOpen }">
        <!-- Routes principales -->
        <router-link
            v-for="route in userRoutes"
            :key="route.name + route.path"
            :to="route.path"
            @click="$emit('close')"
            class="nav-item"
        >
            {{ route.name }}
        </router-link>

        <!-- Auth Links -->
        <div class="loginLinks">
            <template v-if="!userId">
                <router-link
                    :to="{ name: 'Login' }"
                    @click="$emit('close')"
                    class="auth-link"
                >
                    Se connecter
                </router-link>
                <router-link
                    :to="{ name: 'Register' }"
                    @click="$emit('close')"
                    class="auth-link"
                >
                    Créer un compte
                </router-link>
            </template>

            <template v-else>
                <a
                    @click="$emit('logout')"
                    class="auth-link cursor-pointer"
                >
                    Se déconnecter
                </a>
                <router-link
                    :to="{ name: 'Packs' }"
                    @click="$emit('close')"
                    class="auth-link"
                >
                    Acheter packs
                </router-link>
            </template>
        </div>

        <!-- Social Icons (Mobile only) -->
        <div class="mailSocialPin">
            <div class="icon">
                <a href="#" aria-label="Facebook">
                    <img src="../../../../icons/facebook.svg" alt="Facebook"/>
                </a>
                <a :href="'mailto:' + store.fullMail" aria-label="Email">
                    <img src="../../../../icons/mail.svg" alt="Email"/>
                </a>
                <a
                    href="https://maps.app.goo.gl/ApZ1E35srhDT2ynK7"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Localisation"
                >
                    <img src="../../../../icons/pin.svg" alt="Localisation"/>
                </a>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { infos } from "../../../store/index.js";

defineProps({
    isOpen: Boolean,
    userId: [String, Number, null]
});

defineEmits(['close', 'logout']);

const router = useRouter();
const store = infos();

const userRoutes = computed(() =>
    router.getRoutes().filter((r) =>
        r.meta.displayInNav &&
        !r.path.startsWith('/admin') &&
        !r.path.includes(':')
    )
);
</script>

<style lang="scss" scoped>
@use "../../../styles/_mixins.scss" as *;

// === Breakpoints ===
$breakpoint-mobile: 900px;
$breakpoint-tablet: 768px;

// === Colors ===
$color-primary: #4f2794;
$color-gold: #e2a945;
$color-text: #3c3c3c;
$color-border: #575656;

// === Navigation principale ===
nav {
    width: 50vw;
    color: $color-text;

    @media (min-width: $breakpoint-tablet) {
        display: flex;
    }
}

#nav_links {
    display: flex;
    justify-content: space-around;
    align-items: center;
    height: 100%;
    gap: 2rem;

    .loginLinks,
    .mailSocialPin {
        display: none;
    }
}

// === Navigation Items ===
.nav-item {
    @include nav-item-base;
    @include hover-underline;
}

// === Liens actifs ===
a.router-link-exact-active {
    font-weight: 700;
    color: $color-primary;
}

// === Auth Links ===
.auth-link {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

// === Mobile Navigation ===
@media (max-width: $breakpoint-mobile) {
    #nav_links {
        position: absolute;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        top: 70px;
        left: 0;
        right: 0;
        width: 100%;
        height: 100vh;
        padding-top: 50px;
        padding-left: 50px;
        background: rgba(255, 255, 255, 1);
        gap: 0;

        // État fermé
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;

        // État ouvert
        &.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
            border-bottom: 1px solid #000;
        }

        // Items principaux
        .nav-item {
            width: 80%;
            height: 10vh;
            border-bottom: 1px solid $color-border;
            margin: 0;

            &::after {
                display: none;
            }
        }

        // Liens de connexion
        .loginLinks {
            display: flex;
            justify-content: flex-start;
            gap: 40px;
            width: 80%;
            height: 10vh;
            border-bottom: 1px solid $color-border;
            color: $color-gold;
            margin: 0;
        }

        // Icônes sociales
        .mailSocialPin {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            width: 80%;
            height: 5vh;
            margin-top: 20px;

            .icon {
                display: flex;
                gap: 10px;

                a {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    width: 50px;
                    height: 50px;
                    padding: 10px;
                    border: 2px solid #000;
                    border-radius: 50%;
                    transition: border 0.2s ease-in-out;

                    &:hover {
                        border-color: $color-primary;
                    }
                }
            }
        }
    }
}
</style>
