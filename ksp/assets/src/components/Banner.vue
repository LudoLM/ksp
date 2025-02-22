<script setup>
const props = defineProps({
    title: {
        type: String,
        default: 'Kiné Sport Santé'
    },
    image: {
        type: String,
    },
    backgroundColor: {
        type: String,
        default: "transparent"
    },
    backgroundHeight: {
        type: String,
        default: "50vh",
    },
    textColor: {
        type: String,
        default: "white"
    },
    hasButton: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div class="banner" :style="{height: backgroundHeight}">
        <div class="overlay" :style="{backgroundColor: backgroundColor}"></div>
        <img v-if="image !== undefined" :src="image" alt="">
        <div class="title">
            <h1 :style="{color: textColor}">{{ title }}</h1>
            <button :class="hasButton ? 'visible' : 'invisible'">
                <router-link :to="{name:'Calendrier'}">S'inscrire aux cours</router-link>
            </button>
        </div>
    </div>
</template>

<style scoped>

    .banner {
        img{
            object-position: 50% 80%;
        }

        @media (max-width: 900px) {
            height: 40vh !important;
        }
    }


    .overlay{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
</style>
