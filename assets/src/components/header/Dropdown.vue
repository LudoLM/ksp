<script setup>
import { onClickOutside } from '@vueuse/core'
import {inject, ref} from "vue"

const target = ref(null)
const dropdownOpen = ref(false)
const isScrolled = inject('isScrolled', ref(false));


const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
    dropdownOpen.value = false
}

onClickOutside(target, closeDropdown)
</script>

<template>
    <div
        ref="target"
        class="dropdown-wrapper relative min-h-[50px] flex items-center"
    >
        <slot
            :is-open="dropdownOpen"
            :toggle="toggleDropdown"
            :close="closeDropdown"
        />
        <transition name="dropdown-fade">
            <div
                v-show="dropdownOpen"
                :class="{ isScrolled }"
                class="dropdown flex w-62.5 flex-col border border-stroke bg-white shadow-default"
            >
            <slot
                name="content"
            />
            </div>

        </transition>
    </div>
</template>

<style scoped lang="scss">


.dropdown-wrapper {
    position: relative;

    // Styles génériques du dropdown
   .dropdown {
        position: absolute;
        right: 60%;
        transform: translateX(50%) translateY(0);
        top: 87px;
        z-index: 50;
        border-radius: 3px;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: top 0.3s ease-in-out, opacity 0.2s ease, transform 0.2s ease;

        // Support de la classe isScrolled
        &.isScrolled {
            top: 60px;
        }
    }

    // Transitions
    .dropdown-fade-enter-active,
    .dropdown-fade-leave-active {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .dropdown-fade-enter-from,
    .dropdown-fade-leave-to {
        opacity: 0;
        transform: translateX(50%) translateY(-10px);
    }
    .dropdown-fade-enter-to,
    .dropdown-fade-leave-from {
        opacity: 1;
        transform: translateX(50%) translateY(0);
    }
}

@media (max-width: 900px) {
    .dropdown-wrapper {
        .dropdown {
            top: 60px;
        }
    }

}
</style>
