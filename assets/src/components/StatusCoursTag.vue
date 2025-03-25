<script setup>

const colors = {
    'En cours': 'bg-lime-300',
    'Ouvert': 'bg-emerald-300',
    'Complet': 'bg-blue-300',
    'Annulé': 'bg-yellow-300',
    'En création': 'bg-purple-300',
    'Passé': 'bg-red-300',
    'Archivé': 'bg-stone-300',
};

const props = defineProps({
    statusCours: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div :class="['card_status', colors[statusCours.libelle]]">
        {{ statusCours.libelle }}
    </div>
</template>

<style scoped lang="scss">

.card_status {
    width: clamp(65px, 10vw, 100px);
    height: clamp(30px, 5vw, 40px);
    font-size: clamp(0.7rem, 1vw, 1rem);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 3px;
    transition: all 0.3s ease-in-out;
}
</style>
