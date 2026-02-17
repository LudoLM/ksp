<template>
    <section id="home-center" class="section section-center">
        <div class="container">
            <div
                v-reveal="{ delay: 0.2 }"
                class="center-image-wrapper"
            >
                <div v-reveal class="center-image-container">
                    <div class="center-image-frame">
                        <img
                            :src="centerBanner"
                            alt="Le centre"
                            class="center-image"
                        />
                    </div>
                </div>
                <div
                    class="center-content"
                >
                    <span class="section-label text-templateThirdColor">Notre espace</span>
                    <h2 class="section-title">Le Centre</h2>
                    <div class="center-body">
                        <p class="lead">
                            Aujourd'hui, douleurs et fatigue physique font
                            partie de notre quotidien. Notre mode de vie moderne
                            (sédentarisme, stress, mauvais sommeil…) est
                            directement lié à ces souffrances.
                        </p>
                        <p>
                            Nous vous proposons des
                            <span class="highlight text-templateThirdColor"
                                >méthodes simples et efficaces</span
                            >
                            pour changer vos habitudes : le
                            <strong class="text-templateThirdColor">Sport Santé</strong>.
                        </p>
                        <p>
                            Apprenez les règles de bon fonctionnement de votre
                            corps : posture, mobilité, renforcement musculaire,
                            respiration…
                        </p>
                        <a
                            href="https://maps.app.goo.gl/ApZ1E35srhDT2ynK7"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="location hover:text-templateThirdColor"
                        >
                            <svg
                                class="icon text-templateThirdColor"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"
                                />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span
                                >{{ store.address }}
                                {{ store.fullAddress }}</span
                            >
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { infos } from "@/store/index";
import centerBanner from "@/../images/banners/banner2.jpg";

const store = infos();
</script>

<style scoped lang="scss">
.section {
    padding: 6rem 0;

    &-center {
        color: #000;
    }
}

.container {
    max-width: 1250px;
    margin: 0 auto;
    padding: 0 2rem;
}

.center-image-wrapper {
    position: relative;
}

.center-image-container {
    position: relative;
    display: flex;
    justify-content: flex-end;
    width: 90%;
    padding: 20px;
}

.center-image-frame {
    position: relative;
    display: inline-block;
}

.center-image {
    display: block;
    width: 100%;
    height: auto;
}

.center-content {
    position: absolute;
    z-index: 10;
    top: 50%;
    right: 0;
    max-width: 500px;
    padding: 2rem;
    transform: translateY(-50%);
    background: #27272a;

    .section-label {
        position: relative;
        z-index: 1;
        display: inline-block;
        margin-bottom: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
    }

    .section-title {
        position: relative;
        z-index: 1;
        margin-bottom: 2rem;
        color: #fff;
        font-size: clamp(2rem, 4vw, 2.5rem);
        font-weight: 300;
    }
}

.center-body {
    p {
        position: relative;
        z-index: 1;
        margin-bottom: 1.25rem;
        color: #aba9a9;
        font-size: 0.9rem;
        line-height: 1.8;
    }

    .lead {
        font-size: 1rem;
        font-weight: 400;
    }

    .highlight,
    strong {
        font-weight: 600;
    }
}

.location {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: #aba9a9;
    font-size: 0.9rem;
    text-decoration: none;
    transition: color 0.3s ease;

    .icon {
        width: 16px;
        height: 16px;
    }
}

@media (max-width: 1000px) {
    .center-image-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2rem;
    }

    .center-image-container {
        width: 100%;
        padding: 0;
        order: -1;
    }

    .center-image-frame {
        width: 100%;
    }

    .center-content {
        position: relative;
        top: auto;
        right: auto;
        width: 100%;
        max-width: 100%;
        padding: 2rem;
        transform: none;
    }
}

@media (max-width: 768px) {
    .section {
        padding: 4rem 0;
    }
}
</style>
