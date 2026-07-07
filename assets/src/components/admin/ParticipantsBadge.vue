<script setup lang="ts">

    const props = defineProps({
        countSubscribed: {
            type: Number,
            required: true
        },
        nbInscriptionMax: {
            type: Number,
            required: true
        }
    });

</script>

<template>
    <div class="participantsBadge">
        <div>
            {{ countSubscribed }}
        </div>
        <div>
            /
        </div>
        <div>
            {{ nbInscriptionMax }}
        </div>
    </div>
</template>

<style scoped lang="scss">

.participantsBadge{
    position: absolute;
    top: 40px;
    right: 10px;
    z-index: 1000;
    border: 2px solid #fff;
    color: #fff;
    font-weight: bold;
    width: clamp(30px, 5vw, 40px);
    height: clamp(30px, 5vw, 40px);
    font-size: clamp(0.7rem, .8vw, .8rem);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

</style>
