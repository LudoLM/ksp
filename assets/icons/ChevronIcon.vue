<script setup>

defineProps({
    isOpen: {
        type: Boolean,
        default: false

    }
});

</script>

<template>
    <svg
        :class="{ 'rotate-180': isOpen }"
        class="fill-current transition-transform chevron-icon"
        width="12"
        height="8"
        viewBox="0 0 12 8"
    >
        <path d="M0.4 0.9c0.33-.33.86-.33 1.18 0L6 5.32l4.41-4.41c.32-.33.85-.33 1.18 0 .33.32.33.86 0 1.19L6.59 7.09c-.32.33-.85.33-1.18 0L0.4 2.09c-.33-.33-.33-.86 0-1.18z"/>
    </svg>
</template>

<style scoped lang="scss">
</style>
