<script setup lang="ts">
import { onMounted, ref } from "vue";
import { apiFetch } from "@/utils/useFetchInterceptor.ts";
import { alertStore } from "@/store/alert.ts";
import { useUserStore } from "@/store/user.ts";
import { storeToRefs } from "pinia";
import Banner from "@/components/Banner.vue";
import bannerImage from "../../images/banners/imageBanner17.jpg";
import { SEASON_PLANNING_DAYS } from "@/constants/seasonPlanningDays.ts";

interface Creneau {
    id: number;
    daySelected: number;
    timeSelected: string;
    typeCoursOptions: { libelle: string }[];
}

interface Pack {
    id: number;
    nom: string;
    tarif: number;
}

const PAYMENT_OPTIONS = [
    { value: "CbComptant", label: "Comptant en CB", hint: "Réglé en une fois sur le site" },
    { value: "Cheque2x", label: "Chèque en 2 fois", hint: "2 chèques répartis sur la saison" },
    { value: "Cheque3x", label: "Chèque en 3 fois", hint: "3 chèques répartis sur la saison" },
    { value: "Cheque10x", label: "Chèque en 10 fois", hint: "10 chèques, un par mois de cours" },
];

const { userEmail, isAuthenticated } = storeToRefs(useUserStore());

const creneaux = ref<Creneau[]>([]);
const packs = ref<Pack[]>([]);
const loading = ref(true);
const submitting = ref(false);

const email = ref(userEmail.value ?? "");
const nom = ref("");
const prenom = ref("");
const telephone = ref("");
const creneauPrimaireId = ref<string>("");
const creneauSecondaireId = ref<string>("");
const packSouhaiteId = ref<string>("");
const modeReglement = ref<string>("CbComptant");

const creneauLabel = (creneau: Creneau) => {
    const day = SEASON_PLANNING_DAYS.find((d) => d.id === creneau.daySelected)?.name ?? "?";
    return `${day} ${creneau.timeSelected.slice(0, 5)}`;
};

const coursLabel = (creneau: Creneau) => creneau.typeCoursOptions.map((t) => t.libelle).join("/");

const toggleCreneau = (creneauId: number, column: "primaire" | "secondaire") => {
    const id = String(creneauId);
    if (column === "primaire") {
        creneauPrimaireId.value = creneauPrimaireId.value === id ? "" : id;
        if (creneauSecondaireId.value === creneauPrimaireId.value) {
            creneauSecondaireId.value = "";
        }
    } else {
        creneauSecondaireId.value = creneauSecondaireId.value === id ? "" : id;
        if (creneauPrimaireId.value === creneauSecondaireId.value) {
            creneauPrimaireId.value = "";
        }
    }
};

const loadOptions = async () => {
    loading.value = true;
    try {
        const [creneauxRes, packsRes] = await Promise.all([
            apiFetch("/public/season-planning/current").then((r) => r.json()),
            apiFetch("/public/packs").then((r) => r.json()),
        ]);
        creneaux.value = creneauxRes;
        packs.value = packsRes;
    } catch (error) {
        alertStore.setAlert("Erreur lors du chargement du formulaire", "error");
    } finally {
        loading.value = false;
    }
};

const submit = async () => {
    submitting.value = true;
    try {
        const res = await apiFetch("/public/cours-wishes-form", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                email: email.value,
                nom: isAuthenticated.value ? null : nom.value,
                prenom: isAuthenticated.value ? null : prenom.value,
                telephone: isAuthenticated.value ? null : telephone.value,
                creneauPrimaireId: creneauPrimaireId.value ? Number(creneauPrimaireId.value) : null,
                creneauSecondaireId: creneauSecondaireId.value ? Number(creneauSecondaireId.value) : null,
                packSouhaiteId: Number(packSouhaiteId.value),
                modeReglement: modeReglement.value,
            }),
        });
        if (res.ok) {
            alertStore.setAlert("Votre dossier a bien été envoyé, il sera examiné prochainement.", "success");
        } else {
            const data = await res.json().catch(() => null);
            alertStore.setAlert(data?.error ?? "Erreur lors de l'envoi du dossier", "error");
        }
    } catch (error) {
        alertStore.setAlert("Erreur lors de l'envoi du dossier", "error");
    } finally {
        submitting.value = false;
    }
};

onMounted(loadOptions);
</script>

<template>
    <Banner
        title="Dossier d'inscription"
        :backgroundColor="'rgba(30, 27, 65, .75)'"
        :image="bannerImage"
        :hasButton="false"
        backgroundHeight="35vh"
    />

    <div class="wishes-form-page">
        <div class="wishes-form-card">
            <p class="wishes-form-intro">
                Indiquez le créneau que vous souhaitez suivre, le forfait envisagé et votre mode de règlement
                préféré. Notre équipe étudie votre demande selon les places disponibles et revient vers vous
                pour finaliser votre inscription.
            </p>

            <div v-if="loading" class="wishes-form-loading">
                <span class="wishes-form-spinner" aria-hidden="true"></span>
                Chargement du formulaire…
            </div>

            <form v-else class="wishes-form" @submit.prevent="submit">
                <label class="wishes-form-field">
                    <span class="wishes-form-label">Email<span class="wishes-form-required">*</span></span>
                    <input
                        v-model="email"
                        type="email"
                        name="email"
                        required
                        class="wishes-form-input"
                        placeholder="vous@exemple.fr"
                    />
                </label>

                <label v-if="!isAuthenticated" class="wishes-form-field">
                    <span class="wishes-form-label">Nom<span class="wishes-form-required">*</span></span>
                    <input
                        v-model="nom"
                        type="text"
                        name="nom"
                        required
                        class="wishes-form-input"
                        placeholder="Dupont"
                    />
                </label>

                <label v-if="!isAuthenticated" class="wishes-form-field">
                    <span class="wishes-form-label">Prénom<span class="wishes-form-required">*</span></span>
                    <input
                        v-model="prenom"
                        type="text"
                        name="prenom"
                        required
                        class="wishes-form-input"
                        placeholder="Jean"
                    />
                </label>

                <label v-if="!isAuthenticated" class="wishes-form-field">
                    <span class="wishes-form-label">Téléphone<span class="wishes-form-required">*</span></span>
                    <input
                        v-model="telephone"
                        type="tel"
                        name="telephone"
                        required
                        pattern="\d{10}"
                        class="wishes-form-input"
                        placeholder="0612345678"
                    />
                </label>

                <div class="wishes-form-field">
                    <span class="wishes-form-label">Créneaux souhaités</span>
                    <p class="wishes-form-creneaux-hint">
                        Choisissez un créneau prioritaire et, si vous le souhaitez, un créneau secondaire de repli.
                    </p>

                    <p v-if="creneaux.length === 0" class="wishes-form-creneaux-empty">
                        Les créneaux ne sont pas encore publiés, revenez bientôt.
                    </p>

                    <div v-else class="wishes-form-creneaux">
                        <div
                            v-for="creneau in creneaux"
                            :key="creneau.id"
                            class="wishes-form-creneau"
                            :class="{
                                'is-primaire': creneauPrimaireId === String(creneau.id),
                                'is-secondaire': creneauSecondaireId === String(creneau.id),
                            }"
                        >
                            <div class="wishes-form-creneau__info">
                                <div class="wishes-form-creneau__time">
                                    {{ creneauLabel(creneau) }}
                                    <span v-if="creneau.typeCoursOptions.length > 1" class="wishes-form-creneau__badge">alternance</span>
                                </div>
                                <div class="wishes-form-creneau__cours">{{ coursLabel(creneau) }}</div>
                            </div>
                            <div class="wishes-form-creneau__actions">
                                <button
                                    type="button"
                                    class="wishes-form-toggle wishes-form-toggle--primaire"
                                    :class="{ 'is-active': creneauPrimaireId === String(creneau.id) }"
                                    :aria-pressed="creneauPrimaireId === String(creneau.id)"
                                    @click="toggleCreneau(creneau.id, 'primaire')"
                                >
                                    Prioritaire
                                </button>
                                <button
                                    type="button"
                                    class="wishes-form-toggle wishes-form-toggle--secondaire"
                                    :class="{ 'is-active': creneauSecondaireId === String(creneau.id) }"
                                    :aria-pressed="creneauSecondaireId === String(creneau.id)"
                                    @click="toggleCreneau(creneau.id, 'secondaire')"
                                >
                                    Secondaire
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <label class="wishes-form-field">
                    <span class="wishes-form-label">Forfait souhaité<span class="wishes-form-required">*</span></span>
                    <select v-model="packSouhaiteId" name="packSouhaiteId" required class="wishes-form-input">
                        <option value="" disabled>Choisissez un forfait</option>
                        <option v-for="pack in packs" :key="pack.id" :value="pack.id">
                            {{ pack.nom }} — {{ (pack.tarif / 100).toFixed(2) }} €
                        </option>
                    </select>
                </label>

                <fieldset class="wishes-form-field">
                    <legend class="wishes-form-label">Mode de règlement souhaité<span class="wishes-form-required">*</span></legend>
                    <div class="wishes-form-payment-grid">
                        <label
                            v-for="option in PAYMENT_OPTIONS"
                            :key="option.value"
                            class="wishes-form-payment-option"
                            :class="{ 'is-selected': modeReglement === option.value }"
                        >
                            <input type="radio" name="modeReglement" :value="option.value" v-model="modeReglement" />
                            <span class="wishes-form-payment-label">{{ option.label }}</span>
                            <span class="wishes-form-payment-hint">{{ option.hint }}</span>
                        </label>
                    </div>
                </fieldset>

                <button
                    type="submit"
                    class="wishes-form-submit"
                    :disabled="submitting || !packSouhaiteId || !creneauPrimaireId || (!isAuthenticated && (!nom || !prenom || !telephone))"
                >
                    {{ submitting ? "Envoi en cours…" : "Envoyer mon dossier" }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped lang="scss">
$color-primary: #472371;
$color-primary-dark: #37195a;
$color-gold: #e2a945;
$color-border: #e5e7eb;
$color-text: #1f2937;
$color-text-muted: #6b7280;

.wishes-form-page {
    display: flex;
    justify-content: center;
    padding: 40px 20px 64px;
    background: #f6f5f8;
}

.wishes-form-card {
    width: 100%;
    max-width: 640px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 8px 24px rgba(30, 27, 65, 0.06);
    padding: 32px 28px;
}

.wishes-form-intro {
    margin: 0 0 28px;
    font-size: 14px;
    line-height: 1.6;
    color: $color-text-muted;
}

.wishes-form-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 48px 0;
    color: $color-text-muted;
    font-size: 14px;
}

.wishes-form-spinner {
    width: 20px;
    height: 20px;
    border: 2.5px solid $color-border;
    border-top-color: $color-primary;
    border-radius: 50%;
    animation: wishes-form-spin 0.7s linear infinite;
}

@keyframes wishes-form-spin {
    to { transform: rotate(360deg); }
}

.wishes-form {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.wishes-form-field {
    display: block;
}

.wishes-form-label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 600;
    color: $color-text;
}

.wishes-form-required {
    color: #b91c1c;
    margin-left: 2px;
}

.wishes-form-input {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1px solid $color-border;
    border-radius: 10px;
    background: #fff;
    color: $color-text;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;

    &::placeholder {
        color: #9ca3af;
    }

    &:focus {
        outline: none;
        border-color: $color-primary;
        box-shadow: 0 0 0 3px rgba(71, 35, 113, 0.12);
    }
}

.wishes-form-creneaux-hint {
    margin: 0 0 10px;
    font-size: 12.5px;
    color: $color-text-muted;
}

.wishes-form-creneaux-empty {
    margin: 0;
    padding: 20px;
    text-align: center;
    font-size: 13px;
    color: $color-text-muted;
    background: #f9fafb;
    border: 1px dashed $color-border;
    border-radius: 10px;
}

.wishes-form-creneaux {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.wishes-form-creneau {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 14px;
    border: 1.5px solid $color-border;
    border-left: 4px solid $color-border;
    border-radius: 10px;
    background: #fff;
    transition: border-color 0.15s ease, background-color 0.15s ease;

    &.is-primaire {
        border-color: $color-primary;
        border-left-color: $color-primary;
        background: rgba(71, 35, 113, 0.04);
    }

    &.is-secondaire {
        border-color: $color-gold;
        border-left-color: $color-gold;
        background: rgba(226, 169, 69, 0.08);
    }

    @media (max-width: 520px) {
        flex-direction: column;
        align-items: stretch;
    }
}

.wishes-form-creneau__info {
    min-width: 0;
}

.wishes-form-creneau__time {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: $color-text;
}

.wishes-form-creneau__badge {
    font-size: 10.5px;
    font-weight: 600;
    color: darken($color-gold, 15%);
    background: rgba(226, 169, 69, 0.16);
    padding: 2px 8px;
    border-radius: 999px;
}

.wishes-form-creneau__cours {
    margin-top: 2px;
    font-size: 12.5px;
    color: $color-text-muted;
}

.wishes-form-creneau__actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;

    @media (max-width: 520px) {
        margin-top: 4px;
    }
}

.wishes-form-toggle {
    height: 32px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1.5px solid $color-border;
    background: #fff;
    color: $color-text-muted;
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    white-space: nowrap;
    transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;

    @media (max-width: 520px) {
        flex: 1 1 0;
    }

    &:hover {
        border-color: darken($color-border, 15%);
    }
}

.wishes-form-toggle--primaire.is-active {
    background: $color-primary;
    border-color: $color-primary;
    color: #fff;
}

.wishes-form-toggle--secondaire.is-active {
    background: darken($color-gold, 8%);
    border-color: darken($color-gold, 8%);
    color: #fff;
}

.wishes-form-payment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;

    @media (max-width: 560px) {
        grid-template-columns: 1fr;
    }
}

.wishes-form-payment-option {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 12px 14px;
    border: 1.5px solid $color-border;
    border-radius: 10px;
    cursor: pointer;
    transition: border-color 0.15s ease, background-color 0.15s ease;

    input {
        position: absolute;
        top: 12px;
        right: 12px;
        accent-color: $color-primary;
    }

    &:hover {
        border-color: darken($color-border, 15%);
    }

    &.is-selected {
        border-color: $color-primary;
        background: rgba(71, 35, 113, 0.05);
    }
}

.wishes-form-payment-label {
    font-size: 13.5px;
    font-weight: 600;
    color: $color-text;
    padding-right: 20px;
}

.wishes-form-payment-hint {
    font-size: 12px;
    color: $color-text-muted;
}

.wishes-form-submit {
    height: 46px;
    border: none;
    border-radius: 10px;
    background: $color-primary;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.15s ease, opacity 0.15s ease;

    &:hover:not(:disabled) {
        background: $color-primary-dark;
    }

    &:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
}
</style>
