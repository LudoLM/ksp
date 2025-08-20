<script setup>
defineProps({
    title: {
        type: String,
        required: false,
    }
})
</script>

<template>
    <div>
        <slot />
    </div>
</template>
