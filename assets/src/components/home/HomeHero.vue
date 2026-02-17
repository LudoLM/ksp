<template>
    <section class="section hero">
        <div class="hero-bg">
            <img :src="bannerImage" alt="Kiné Sport Santé" />
        </div>
        <div class="hero-content">
            <h1 class="hero-title animate-hero" style="--delay: 0.2s">
                Kiné Sport Santé
            </h1>
            <p class="hero-subtitle animate-hero" style="--delay: 0.4s">
                Votre bien-être, notre expertise
            </p>
            <router-link
                to="/calendar"
                class="cta-btn animate-hero"
                style="--delay: 0.6s"
                >Découvrir les cours</router-link
            >

            <div class="hero-kpis animate-hero" style="--delay: 0.9s">
                <div class="hero-kpi animate-hero" style="--delay: 1s">
                    <span class="hero-kpi-number">8+</span
                    ><span class="hero-kpi-label">Années d'expérience</span>
                </div>
                <div
                    class="hero-kpi-divider animate-hero"
                    style="--delay: 1.1s"
                ></div>
                <div class="hero-kpi animate-hero" style="--delay: 1.2s">
                    <span class="hero-kpi-number">500+</span
                    ><span class="hero-kpi-label">Cours dispensés</span>
                </div>
                <div
                    class="hero-kpi-divider animate-hero"
                    style="--delay: 1.3s"
                ></div>
                <div class="hero-kpi animate-hero" style="--delay: 1.4s">
                    <span class="hero-kpi-number">10</span
                    ><span class="hero-kpi-label">Participants max</span>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import bannerImage from "@/../images/banners/banner4.jpg";
</script>

<style scoped lang="scss">
.section {
    padding: 6rem 0;
}

.hero {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    min-height: 600px;
    overflow: hidden;

    &-bg {
        position: absolute;
        inset: 0;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.1);
            animation: heroZoomOut 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)
                forwards;
        }

        &::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(71, 35, 113, 0.7);
        }
    }

    &-content {
        position: relative;
        z-index: 1;
        padding: 2rem;
        text-align: center;
        color: #fff;
    }

    &-title {
        margin-bottom: 1rem;
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 300;
        letter-spacing: -0.02em;
    }

    &-subtitle {
        margin-bottom: 2.5rem;
        font-size: clamp(1.1rem, 2.5vw, 1.5rem);
        font-weight: 300;
        opacity: 0.9;
    }

    &-kpis {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2rem;
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    &-kpi {
        text-align: center;

        &-number {
            display: block;
            margin-bottom: 0.25rem;
            color: #fff;
            font-size: 2rem;
            font-weight: 300;
            line-height: 1;
        }

        &-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
    }

    &-kpi-divider {
        width: 1px;
        height: 40px;
        background: rgba(255, 255, 255, 0.3);
    }
}

.animate-hero {
    opacity: 0;
    transform: translateY(40px);
    animation: heroFadeInUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    animation-delay: var(--delay, 0s);
}

@keyframes heroZoomOut {
    0% {
        transform: scale(1.1);
    }

    100% {
        transform: scale(1);
    }
}

@keyframes heroFadeInUp {
    0% {
        opacity: 0;
        transform: translateY(40px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 1000px) {
    .hero-kpis {
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .hero-kpi-divider {
        display: none;
    }

    .hero-kpi {
        flex: 1 1 30%;
    }
}

@media (max-width: 768px) {
    .section {
        padding: 4rem 0;
    }

    .hero-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 2rem;
        padding-top: 1.25rem;
    }

    .hero-kpi {
        flex: initial;
        min-width: 0;
    }

    .hero-kpi-number {
        margin-bottom: 0.2rem;
        font-size: 1.25rem;
    }

    .hero-kpi-label {
        font-size: 0.62rem;
        line-height: 1.25;
        letter-spacing: 0.06em;
    }

    .cta-btn {
        width: 100%;
        max-width: 260px;
        padding: 0.75rem 1.1rem;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
    }
}
</style>


