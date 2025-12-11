<template>
    <nav id="nav_links" :class="{ active: isOpen }">
        <!-- Dashboard -->
        <router-link
            :to="{ name: 'AdminDashboard' }"
            class="nav-item"
            @click="$emit('close')"
        >
            Tableau de bord
        </router-link>

        <!-- Categories avec Dropdown ou Accordion -->
        <NavCategory
            v-for="(category, idx) in adminCategories"
            :key="category.meta.navGroup"
            :category="category"
            :children="getChildren(category.meta.navGroup)"
            :isMobile="isMobile"
            :isOpen="openCategories.has(idx)"
            @toggle="toggleCategory(idx)"
            @close="$emit('close')"
        />
    </nav>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import NavCategory from "./NavCategory.vue";

const props = defineProps({
    isOpen: Boolean,
    isMobile: Boolean
});

const emit = defineEmits(['close']);

const router = useRouter();
const openCategories = ref(new Set());

// Computed
const adminRoutes = computed(() =>
    router.getRoutes().filter(r => r.path.startsWith('/admin'))
);

const adminCategories = computed(() =>
    adminRoutes.value.filter(r => r.meta?.isCategory)
);

// Methods
const getChildren = (group) =>
    adminRoutes.value.filter(
        r => r.meta?.navGroup === group &&
            r.meta.displayInNav &&
            !r.meta?.isCategory &&
            !r.path.includes(':') &&
            r.name
    ).sort((a, b) => (a.meta.order || 0) - (b.meta.order || 0));

const toggleCategory = (idx) => {
    if (openCategories.value.has(idx)) {
        openCategories.value.delete(idx);
    } else {
        openCategories.value.add(idx);
    }
};
</script>

<style lang="scss" scoped>
@use "../../../styles/_mixins.scss" as *;

// === Breakpoints ===
$breakpoint-mobile: 900px;
$breakpoint-tablet: 768px;

// === Colors ===
$color-primary: #4f2794;
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
            color: #e2a945;
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

        // === Accordion Styles ===
        .accordion-item {
            display: flex;
            flex-direction: column;
            width: 80%;
            border-bottom: 1px solid $color-border;
        }

        .accordion-header {
            height: 10vh;
            background: transparent;
            border: none;
            text-align: left;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-weight: 900;
            text-transform: uppercase;
            font-size: clamp(0.8rem, 1.2vw, 0.8rem);
            cursor: pointer;
            transition: color 0.2s ease;

            &:hover {
                color: $color-primary;
            }
        }

        .accordion-content {
            overflow: hidden;

            ul {
                padding: 1rem 0;
                margin: 0;
                list-style: none;
            }
        }

        .accordion-link {
            display: block;
            padding: 0.5rem 0 0.5rem 1.25rem;
            font-size: 0.875rem;
            text-transform: capitalize;
            color: #666666;
            transition: color 0.2s ease;

            &:hover,
            &.router-link-exact-active {
                color: $color-primary;
            }

            &.router-link-exact-active {
                font-weight: 700;
            }

            // Désactiver le ::after du hover effect
            &::after {
                display: none !important;
            }
        }
    }
}

// === Accordion Transitions ===
.accordion-enter-active,
.accordion-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.accordion-enter-from,
.accordion-leave-to {
    opacity: 0;
    max-height: 0;
}

.accordion-enter-to,
.accordion-leave-from {
    opacity: 1;
    max-height: 500px;
}
</style>
