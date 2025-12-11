
<template>
    <div class="navigation-wrapper">
        <div class="hamburger">
            <Hamburger
                @click="toggleNavLinks"
                :isClose="isNavOpen"
            />
        </div>

        <!-- Navigation Admin -->
        <AdminNav
            v-if="isAdminPath"
            :isOpen="isNavOpen"
            :isMobile="isMobileView"
            @close="closeNavLinks"
        />

        <!-- Navigation Utilisateur -->
        <UserNav
            v-else
            :isOpen="isNavOpen"
            :userId="userId"
            @close="closeNavLinks"
            @logout="handleLogout"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useUserStore } from "../../store/user";
import Hamburger from "../Hamburger.vue";
import AdminNav from "./navigation/AdminNav.vue";
import UserNav from "./navigation/UserNav.vue";

const isNavOpen = ref(false);
const isMobileView = ref(false);
const route = useRoute();
const router = useRouter();
const userStore = useUserStore();

const userId = computed(() => userStore.userId);
const isAdminPath = computed(() => route.path.startsWith("/admin"));

// Hooks
const checkMobileView = () => {
    isMobileView.value = window.innerWidth <= 900;
};

onMounted(() => {
    checkMobileView();
    window.addEventListener('resize', checkMobileView);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobileView);
});

// Actions
const toggleNavLinks = () => {
    isNavOpen.value = !isNavOpen.value;
};

const closeNavLinks = () => {
    isNavOpen.value = false;
};

const handleLogout = () => {
    userStore.logout();
    closeNavLinks();
    router.push({ name: 'Accueil' });
};
</script>

<style lang="scss" scoped>
$breakpoint-mobile: 900px;

.hamburger {
    display: none;
    width: 50px;
    height: 50px;
    justify-content: center;
    align-items: center;
    margin-left: 15px;

    @media (max-width: $breakpoint-mobile) {
        display: flex;
    }
}
</style>
