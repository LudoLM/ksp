<template>
    <section class="section section-coach">
        <div class="container">
            <div class="coach-layout">
                <div v-reveal class="coach-image-wrapper">
                    <div class="coach-image-frame">
                        <img
                            src="@/../../public/images/servane.jpg"
                            alt="Portrait de Servane - Kinésithérapeute"
                            class="coach-image"
                        />
                    </div>
                </div>
                <div
                    v-reveal="{ delay: 0.2 }"
                    class="coach-content"
                >
                    <span class="section-label text-templateThirdColor">Votre accompagnante</span>
                    <h2 class="section-title">La Coach</h2>
                    <div class="coach-body">
                        <p class="lead">
                            Servane COSQUERIC - Masseur-Kinésithérapeute DE
                        </p>
                        <p class="coach-quote">
                            <span class="quote-mark text-templateThirdColor">"</span>
                            En choisissant le métier de masseur
                            kinésithérapeute, je voulais aider et soigner les
                            gens, tout en gardant comme objectif de pouvoir me
                            rapprocher du milieu sportif. Avec mes
                            spécialisations en rééducation du dos, des
                            abdominaux et du périnée, et plus récemment mes
                            formations en sport santé, je vous propose des cours
                            de gym adaptés à vos besoins.
                            <span class="quote-mark text-templateThirdColor">"</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped lang="scss">
.section {
    padding: 6rem 0;

    &-coach {
        background: #fff;
    }
}

.container {
    max-width: 1250px;
    margin: 0 auto;
    padding: 0 2rem;
}

.coach-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 4rem;
    align-items: center;
}

.coach-image-frame {
    position: relative;
    display: inline-block;
}

.coach-image {
    width: 100%;
    max-width: 400px;
    height: 500px;
    object-fit: cover;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.coach-content {
    .section-label {
        display: inline-block;
        margin-bottom: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
    }

    .section-title {
        margin-bottom: 2rem;
        color: #27272a;
        font-size: clamp(2rem, 4vw, 2.5rem);
        font-weight: 300;
    }
}

.coach-body {
    p {
        margin-bottom: 1.25rem;
        color: #52525b;
        font-size: 1rem;
        line-height: 1.8;
    }

    .lead {
        color: #27272a;
        font-size: 1.15rem;
        font-weight: 400;
    }
}

.coach-quote {
    position: relative;
    padding-left: 2rem;
    border-left: 3px solid theme('colors.templateThirdColor');
    font-style: italic;

    .quote-mark {
        font-size: 2rem;
        font-family: Georgia, serif;
        line-height: 1;

        &:first-child {
            position: absolute;
            top: -0.5rem;
            left: 0.5rem;
        }

        &:last-child {
            margin-left: 0.5rem;
        }
    }
}

@media (max-width: 1024px) {
    .coach-layout {
        grid-template-columns: 1fr;
        gap: 3rem;
    }

    .coach-image-wrapper {
        order: -1;
        display: flex;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .section {
        padding: 4rem 0;
    }

    .coach-image {
        height: 400px;
    }
}
</style>

