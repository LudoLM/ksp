<script setup>

const props = defineProps({
    title: {
        type: String,
        default: 'Kiné Sport Santé'
    },
});

</script>

<template>
    <div>
        <button
            type="submit"
            class="flex items-center justify-center w-full px-4 py-3 mt-10 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
        >
            Valider
        </button>
    </div>
</template>

<style scoped lang="scss">
    button{
        background-color: #472371;

        &:hover{
            background-color: #5f3f71;
        }
    }
</style>
