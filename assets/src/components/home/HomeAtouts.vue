<script>
</script>
<template>
    <section class="section section-atouts">
        <div class="container">
            <div v-reveal class="section-header">
                <span class="section-label">Pourquoi nous choisir</span>
                <h2 class="section-title">Nos atouts</h2>
                <p class="section-subtitle">
                    Une approche complète et personnalisée pour votre bien-être
                </p>
            </div>
            <div class="atouts-grid">
                <div v-reveal="{ delay: 0.1 }" class="atout-card">
                    <div class="atout-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>
                    <h3 class="atout-title">
                        Encadrement par un professionnel de santé
                    </h3>
                    <p class="atout-text">
                        Cours supervisés par une kinésithérapeute diplômée
                        d'État pour une pratique en toute sécurité.
                    </p>
                </div>

                <div v-reveal="{ delay: 0.2 }" class="atout-card">
                    <div class="atout-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>
                    </div>
                    <h3 class="atout-title">
                        Cours en petits groupes (10 personnes max)
                    </h3>
                    <p class="atout-text">
                        Maximum 10 participants pour garantir une attention
                        personnalisée à chacun.
                    </p>
                </div>

                <div v-reveal="{ delay: 0.3 }" class="atout-card">
                    <div class="atout-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            />
                        </svg>
                    </div>
                    <h3 class="atout-title">Bilan sport santé possible</h3>
                    <p class="atout-text">
                        Évaluation personnalisée de votre condition physique.
                    </p>
                    <a
                        href="https://www.doctolib.fr/masseur-kinesitherapeute/mordelles/servane-cosqueric-mordelles/booking/availabilities?specialityId=9&telehealth=false&placeId=practice-567335&motiveCategoryIds%5B%5D=338126&motiveIds%5B%5D=11667176&bookingFunnelSource=profile"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="atout-link"
                        >Prendre RDV sur Doctolib</a
                    >
                </div>

                <div v-reveal="{ delay: 0.4 }" class="atout-card">
                    <div class="atout-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <h3 class="atout-title">
                        Séance de coaching individuel proposé
                    </h3>
                    <p class="atout-text">
                        Accompagnement personnalisé en séance individuelle.
                    </p>
                    <a
                        href="https://www.doctolib.fr/masseur-kinesitherapeute/mordelles/servane-cosqueric-mordelles/booking/availabilities?specialityId=9&telehealth=false&placeId=practice-567335&motiveCategoryIds%5B%5D=338126&motiveIds%5B%5D=11667177&bookingFunnelSource=profile"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="atout-link"
                        >Prendre RDV sur Doctolib</a
                    >
                </div>

                <div v-reveal="{ delay: 0.5 }" class="atout-card">
                    <div class="atout-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                            />
                        </svg>
                    </div>
                    <h3 class="atout-title">
                        Exercices et conseils personnalisés
                    </h3>
                    <p class="atout-text">
                        Des exercices adaptés à vos besoins et conseils
                        personnalisés pour progresser.
                    </p>
                </div>

                <div v-reveal="{ delay: 0.6 }" class="atout-card">
                    <div class="atout-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>
                    </div>
                    <h3 class="atout-title">Bienveillance</h3>
                    <p class="atout-text">
                        Une atmosphère chaleureuse et bienveillante où chacun
                        progresse à son rythme.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped lang="scss">
.section {
    padding: 6rem 0;

    &-atouts {
        color: #27272a;
    }
}

.container {
    max-width: 1250px;
    margin: 0 auto;
    padding: 0 2rem;
}

.section-header {
    margin-bottom: 4rem;
    text-align: center;

    .section-label {
        display: inline-block;
        margin-bottom: 1rem;
        color: #e2a945;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
    }

    .section-title {
        margin-bottom: 1rem;
        color: #27272a;
        font-size: clamp(2rem, 4vw, 2.5rem);
        font-weight: 300;
    }
}

.section-subtitle {
    max-width: 600px;
    margin: 0 auto;
    color: #52525b;
    font-size: 1.1rem;
}

.atouts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.atout-card {
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
    border: 1px solid #f0f0f0;
    border-radius: 0;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;

    &:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
}

.atout-icon {
    width: 32px;
    height: 32px;
    margin-bottom: 1rem;
    color: #472371;

    svg {
        width: 100%;
        height: 100%;
    }
}

.atout-title {
    margin-bottom: 0.5rem;
    color: #27272a;
    font-size: 0.95rem;
    font-weight: 600;
}

.atout-text {
    margin-bottom: 0.75rem;
    color: #666;
    font-size: 0.85rem;
    line-height: 1.5;
}

.atout-link {
    display: block;
    max-width: 140px;
    margin: 0.5rem auto 0;
    padding: 5px 10px;
    border-radius: 3px;
    background: #107aca;
    color: #f3f4f6;
    font-size: 0.7rem;
    text-align: center;
    text-decoration: none;
    transition: background 0.3s ease;

    &:hover {
        background: #43aaec;
    }
}

@media (max-width: 1024px) {
    .atouts-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .section {
        padding: 4rem 0;
    }

    .atouts-grid {
        grid-template-columns: 1fr;
    }
}
</style>
