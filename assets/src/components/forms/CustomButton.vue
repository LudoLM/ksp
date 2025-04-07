<script setup>
defineProps({
    color: {
        type: String,
        default: "purple"
    }
});

const colors = {
    purple: {
        base: "bg-purple-950",
        hover: "hover:bg-purple-900",
        focus: "focus-visible:outline-purple-900",
    },
    red: {
        base: "bg-red-800",
        hover: "hover:bg-red-600",
        focus: "focus-visible:outline-red-600",
    },
    gray: {
        base: "bg-gray-300",
        hover: "hover:bg-gray-300",
        focus: "focus-visible:outline-gray-300",
    },
};
</script>

<template>
    <button
        class="color-button block px-3 py-2 text-center text-sm text-white shadow-sm bg-brand-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
        :class="[
      colors[color]?.base,
      colors[color]?.hover,
      colors[color]?.focus
    ]"
    >
        <slot></slot>
    </button>
</template>

<style scoped lang="scss">
    button {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: clamp(0.8rem, 1vw, .9rem);
        border-radius: 3px;
    }
</style>
