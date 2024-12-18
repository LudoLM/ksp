<script setup>
defineProps({
    color: {
        type: String,
        default: "violet"
    }
});

const colors = {
    violet: {
        base: "bg-violet-800",
        hover: "hover:bg-violet-600",
        focus: "focus-visible:outline-violet-600",
    },
    red: {
        base: "bg-red-800",
        hover: "hover:bg-red-600",
        focus: "focus-visible:outline-red-600",
    },
    gray: {
        base: "bg-gray-800",
        hover: "hover:bg-gray-600",
        focus: "focus-visible:outline-gray-600",
    },
};
</script>

<template>
    <button
        class="color-button mt-3 mx-2 block px-3 py-2 text-center text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
        :class="[
      colors[color]?.base,
      colors[color]?.hover,
      colors[color]?.focus
    ]"
    >
        <slot></slot>
    </button>
</template>
