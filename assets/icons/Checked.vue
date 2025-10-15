<script setup>
defineProps(
    {
        size: {
            type: String,
            required: true
        }
    }
)
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="-5 0 24 24"
         stroke-width="1.5"
         stroke="currentColor"
         :width= size
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m4.5 12.75 6 6 9-13.5" />
    </svg>
</template>

<style scoped lang="scss">

</style>
