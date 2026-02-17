<template>
    <div class="home">
        <HomeHero />

        <div
            ref="scrollIndicator"
            class="scroll-indicator"
            @click="scrollToContent"
            :class="{ 'scroll-hidden': hasScrolled }"
        >
            <span class="scroll-text">Scroll</span>
            <div class="scroll-wave">
                <div class="wave-line wave-line-1"></div>
                <div class="wave-line wave-line-2"></div>
                <div class="wave-line wave-line-3"></div>
            </div>
        </div>

        <HomeCenter />
        <HomeCoach />
        <HomeAtouts />

        <section class="section section-cta">
            <div class="container">
                <div v-reveal="{ delay: 0.1 }" class="cta-content">
                    <h2 class="cta-title">Prêt(e) à vous lancer ?</h2>
                    <p class="cta-text">
                        Rejoignez-nous dès maintenant et découvrez les bienfaits
                        du Sport Santé. Votre premier cours est offert !
                    </p>
                    <div class="cta-actions">
                        <router-link
                            to="/calendar"
                            class="cta-btn"
                            >Découvrir les cours</router-link
                        >
                        <router-link to="/packs" class="cta-btn"
                            >Découvrir les packs</router-link
                        >
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { alertStore } from "../store/alert";
import HomeHero from "../components/home/HomeHero.vue";
import HomeCenter from "../components/home/HomeCenter.vue";
import HomeCoach from "../components/home/HomeCoach.vue";
import HomeAtouts from "../components/home/HomeAtouts.vue";

const route = useRoute();
const hasScrolled = ref(false);
const scrollIndicator = ref(null);
let scrollIndicatorTimeout = null;

if (route.query.alert === "logout") {
    alertStore.setAlert("Vous êtes déconnecté(e).", "info");
}

const scrollToContent = () => {
    document
        .getElementById("home-center")
        ?.scrollIntoView({ behavior: "smooth" });
};

const checkScrollPosition = () => {
    if (window.scrollY > 100) {
        hasScrolled.value = true;
    }
};

onMounted(() => {
    scrollIndicatorTimeout = setTimeout(() => {
        scrollIndicator.value?.classList.add("animate-in");
    }, 800);

    window.addEventListener('scroll', checkScrollPosition);
    checkScrollPosition();
});

onUnmounted(() => {
    if (scrollIndicatorTimeout) {
        clearTimeout(scrollIndicatorTimeout);
    }
    window.removeEventListener('scroll', checkScrollPosition);
});
</script>

<style scoped lang="scss">
.container {
    max-width: 1250px;
    margin: 0 auto;
    padding: 0 2rem;
}

.scroll-indicator {
    position: fixed;
    bottom: 3rem;
    left: 50%;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    opacity: 0;
    cursor: pointer;
    transform: translateX(-50%) translateY(100px);
    transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);

    &.animate-in {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    &.scroll-hidden {
        opacity: 0;
        visibility: hidden;
    }
}

.scroll-text {
    color: #fff;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    opacity: 0.7;
}

.scroll-wave {
    position: relative;
    display: flex;
    justify-content: center;
    width: 24px;
    height: 40px;
    overflow: hidden;
}

.wave-line {
    position: absolute;
    width: 2px;
    height: 100%;
    border-radius: 1px;
    background: linear-gradient(
        to bottom,
        transparent 0%,
        rgba(128, 128, 128, 0.2) 20%,
        rgba(128, 128, 128, 0.8) 50%,
        rgba(128, 128, 128, 0.2) 80%,
        transparent 100%
    );

    &-1 {
        animation: wave 2.5s ease-in-out infinite;
    }

    &-2 {
        opacity: 0.5;
        animation: wave 2.5s ease-in-out infinite 0.3s;
    }

    &-3 {
        opacity: 0.3;
        animation: wave 2.5s ease-in-out infinite 0.6s;
    }
}

.section {
    &-cta {
        position: relative;
        overflow: hidden;
        padding: 8rem 0;
        background: #27272a;
    }
}

.cta-content {
    position: relative;
    z-index: 1;
    max-width: 700px;
    margin: 0 auto;
    padding: 0 1rem;
    text-align: center;
}

.cta-title {
    margin-bottom: 1.5rem;
    color: #fff;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 300;
}

.cta-text {
    margin-bottom: 2.5rem;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    line-height: 1.7;
}

.cta-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

:deep(.cta-btn) {
    display: inline-block;
    padding: 1rem 2.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    transition: all 0.3s ease;
    border: 2px solid #fff;
    background: transparent;
    color: #fff;

    &:hover {
        background: #fff;
        color: theme('colors.templateMainColor');
        transform: translateY(-2px);
    }
}

@keyframes wave {
    0%,
    100% {
        opacity: 0;
        transform: translateY(-100%) scaleY(0.8);
    }

    20%,
    80% {
        opacity: 1;
    }

    50% {
        transform: translateY(0) scaleY(1.2);
    }

    100% {
        transform: translateY(100%) scaleY(0.8);
    }
}

@media (max-width: 768px) {
    .section-cta {
        padding: 4rem 0;
    }

    .cta-content {
        padding: 0 1.25rem;
    }

    .cta-title {
        margin-bottom: 1rem;
        font-size: clamp(1.45rem, 6vw, 1.85rem);
    }

    .cta-text {
        margin-bottom: 1.25rem;
        font-size: 0.95rem;
        line-height: 1.55;
    }

    .cta-actions {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .section-cta .cta-btn {
        width: 100%;
        max-width: 260px;
        padding: 0.75rem 1.1rem;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
    }
}
</style>
