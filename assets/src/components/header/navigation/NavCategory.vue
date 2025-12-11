<template>
    <!-- Desktop: Dropdown -->
    <Dropdown v-if="!isMobile">
        <template v-slot:default="{ isOpen, toggle }">
            <button
                class="nav-button"
                @click="toggle"
            >
                <span>{{ category.meta.navLabel }}</span>
                <ChevronIcon :isOpen="isOpen" />
            </button>
        </template>

        <template v-slot:content="{ close }">
            <ul class="dropdown-menu">
                <li v-for="route in children" :key="route.name">
                    <router-link
                        :to="{ name: route.name }"
                        class="dropdown-item"
                        @click="close"
                    >
                        {{ route.meta.navLabel }}
                    </router-link>
                </li>
            </ul>
        </template>
    </Dropdown>

    <!-- Mobile: Accordion -->
    <div v-else class="accordion-item">
        <button
            class="accordion-header"
            @click="$emit('toggle')"
        >
            <span>{{ category.meta.navLabel }}</span>
            <ChevronIcon :isOpen="isOpen" />
        </button>

         <Transition name="accordion">
             <div v-show="isOpen" class="accordion-content">
                 <ul>
                     <li v-for="route in children" :key="route.name">
                         <router-link
                             :to="{ name: route.name }"
                             class="accordion-link"
                             @click="$emit('close')"
                         >
                             {{ route.meta.navLabel }}
                         </router-link>
                     </li>
                 </ul>
             </div>
         </Transition>
    </div>
</template>

<script setup>
import Dropdown from "../Dropdown.vue";
import ChevronIcon from "../../../../icons/ChevronIcon.vue";

defineProps({
    category: {
        type: Object,
        required: true
    },
    children: {
        type: Array,
        default: () => []
    },
    isMobile: {
        type: Boolean,
        default: false
    },
    isOpen: {
        type: Boolean,
        default: false
    }
});

defineEmits(['toggle', 'close']);
</script>

<style lang="scss" scoped>

@use "../../../styles/_mixins.scss" as *;

// === Breakpoints ===
$breakpoint-mobile: 900px;

// === Colors ===
$color-primary: #4f2794;
$color-text-light: #666666;
$color-border: #575656;

.nav-button,
.accordion-header {
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

.dropdown-menu {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
}

.dropdown-item {
    font-size: 0.875rem;
    text-transform: capitalize;
    font-weight: 500;
    transition: color 0.2s ease;

    &:hover {
        color: var(--template-main-color, $color-primary);
    }
}

// === Mobile Accordion Styles ===
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
    color: $color-text-light;
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
