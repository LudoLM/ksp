<script setup lang="ts">
import { onMounted, ref } from "vue";
import bannerImage from "../../../images/banners/imageBanner5.jpg";
import Banner from "../../components/Banner.vue";
import { apiFetch } from "@/utils/useFetchInterceptor.ts";
import { alertStore } from "@/store/alert.ts";
import { useGetTypesCours } from "@/utils/useActionCours.ts";
import { SEASON_PLANNING_DAYS } from "@/constants/seasonPlanningDays.ts";

interface TypeCours {
    id: number;
    libelle: string;
}

interface Slot {
    id: number;
    daySelected: number;
    timeSelected: string;
    typeCoursOptions: TypeCours[];
}

const title = "Planning de la saison";
const slots = ref<Slot[]>([]);
const typeCoursChoices = ref<TypeCours[]>([]);
const loading = ref(false);
const submitting = ref(false);

const newDaySelected = ref<string>("1");
const newTimeSelected = ref<string>("");
const newTypeCoursId = ref<string>("");

const dayName = (daySelected: number) => SEASON_PLANNING_DAYS.find((d) => d.id === daySelected)?.name ?? "?";

const load = async () => {
    loading.value = true;
    try {
        const [slotsRes, typeCoursRes] = await Promise.all([
            apiFetch("/admin/season-planning"),
            useGetTypesCours(),
        ]);

        if (!slotsRes.ok) {
            throw new Error("Erreur lors du chargement des créneaux");
        }
        // useGetTypesCours() avale ses propres erreurs et résout à `false`
        // plutôt que de rejeter : il faut vérifier la forme du résultat.
        if (!Array.isArray(typeCoursRes)) {
            throw new Error("Erreur lors du chargement des types de cours");
        }

        slots.value = await slotsRes.json();
        typeCoursChoices.value = typeCoursRes;
    } catch (error) {
        alertStore.setAlert("Erreur lors du chargement du planning", "error");
    } finally {
        loading.value = false;
    }
};

const addSlot = async () => {
    if (!newTimeSelected.value || !newTypeCoursId.value) return;

    submitting.value = true;
    try {
        const res = await apiFetch("/admin/season-planning/slots", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                daySelected: Number(newDaySelected.value),
                timeSelected: newTimeSelected.value,
                typeCoursId: Number(newTypeCoursId.value),
            }),
        });
        if (res.ok) {
            alertStore.setAlert("Cours ajouté au créneau", "success");
            newTimeSelected.value = "";
            newTypeCoursId.value = "";
            await load();
        } else {
            const data = await res.json().catch(() => null);
            alertStore.setAlert(data?.error ?? "Erreur lors de l'ajout du cours", "error");
        }
    } catch (error) {
        alertStore.setAlert("Erreur lors de l'ajout du cours", "error");
    } finally {
        submitting.value = false;
    }
};

onMounted(load);
</script>

<template>
    <Banner :title="title" :has-button="false" :background-color="'rgba(30, 27, 65, .9)'" :image="bannerImage" />

    <div class="sp-panel">
        <div class="sp-panel__header">
            <div>
                <h2 class="sp-panel__heading">Créneaux de la saison</h2>
                <p class="sp-panel__subheading">
                    {{ slots.length }} créneau{{ slots.length > 1 ? "x" : "" }} publié{{ slots.length > 1 ? "s" : "" }}
                </p>
            </div>
        </div>

        <form class="sp-add-form" @submit.prevent="addSlot">
            <div class="sp-add-form__field">
                <label for="sp-day">Jour</label>
                <select id="sp-day" v-model="newDaySelected" class="sp-input">
                    <option v-for="day in SEASON_PLANNING_DAYS" :key="day.id" :value="day.id">{{ day.name }}</option>
                </select>
            </div>
            <div class="sp-add-form__field">
                <label for="sp-time">Heure</label>
                <input id="sp-time" v-model="newTimeSelected" type="time" required class="sp-input" />
            </div>
            <div class="sp-add-form__field sp-add-form__field--grow">
                <label for="sp-type">Type de cours</label>
                <select id="sp-type" v-model="newTypeCoursId" required class="sp-input">
                    <option value="" disabled>Choisir un type de cours</option>
                    <option v-for="type in typeCoursChoices" :key="type.id" :value="type.id">{{ type.libelle }}</option>
                </select>
            </div>
            <button type="submit" class="sp-btn sp-btn--primary" :disabled="submitting">
                {{ submitting ? "Ajout…" : "Ajouter" }}
            </button>
        </form>
        <p class="sp-add-form__hint">
            Le membre s'inscrit au créneau (jour/heure), pas à un cours précis. Pour créer une alternance,
            ajoutez plusieurs cours sur le même jour et la même heure — l'un d'eux tombera selon les semaines.
        </p>

        <div v-if="loading" class="sp-state">
            <div class="sp-spinner" aria-hidden="true"></div>
            <p>Chargement du planning…</p>
        </div>

        <div v-else-if="slots.length === 0" class="sp-state sp-state--empty">
            <p class="sp-state__title">Aucun créneau publié pour cette saison</p>
            <p class="sp-state__body">Ajoutez le premier créneau ci-dessus pour ouvrir le dossier d'inscription aux membres.</p>
        </div>

        <div v-else class="sp-groups">
            <div v-for="slot in slots" :key="slot.id" class="sp-group">
                <div class="sp-group__heading">
                    <span class="sp-group__day">{{ dayName(slot.daySelected) }}</span>
                    <span class="sp-group__time">{{ slot.timeSelected.slice(0, 5) }}</span>
                    <span v-if="slot.typeCoursOptions.length > 1" class="sp-group__badge">
                        {{ slot.typeCoursOptions.length }} cours en alternance
                    </span>
                </div>

                <div v-for="typeCours in slot.typeCoursOptions" :key="typeCours.id" class="sp-option">
                    <div class="sp-option__label">{{ typeCours.libelle }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
$color-primary: #472371;
$color-primary-dark: #37195a;
$color-gold: #e2a945;
$color-border: #e5e7eb;
$color-text: #111827;
$color-text-muted: #6b7280;
$radius: 10px;

.sp-panel {
    margin: 40px 40px 24px;
    background: #fff;
    border: 1px solid $color-border;
    border-radius: $radius;
    padding: 24px 28px 12px;
}

.sp-panel__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.sp-panel__heading {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: $color-text;
}

.sp-panel__subheading {
    margin: 4px 0 0;
    font-size: 13px;
    color: $color-text-muted;
}

.sp-add-form {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border: 1px solid $color-border;
    border-radius: $radius;
    margin-bottom: 8px;
}

.sp-add-form__field {
    display: flex;
    flex-direction: column;
    gap: 5px;

    label {
        font-size: 12px;
        font-weight: 600;
        color: $color-text-muted;
    }
}

.sp-add-form__field--grow {
    flex: 1 1 220px;
}

.sp-add-form__hint {
    margin: 0 0 24px;
    font-size: 12px;
    color: $color-text-muted;
}

.sp-input {
    height: 40px;
    padding: 0 12px;
    border: 1px solid $color-border;
    border-radius: 8px;
    background: #fff;
    color: $color-text;
    font-size: 13.5px;
    font-family: inherit;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;

    &:focus {
        outline: none;
        border-color: $color-primary;
        box-shadow: 0 0 0 3px rgba(71, 35, 113, 0.12);
    }
}

.sp-btn {
    height: 40px;
    padding: 0 16px;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 0.15s ease, opacity 0.15s ease;

    &:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
}

.sp-btn--primary {
    background: $color-primary;
    color: #fff;

    &:hover:not(:disabled) {
        background: $color-primary-dark;
    }
}

.sp-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 56px 16px;
    color: $color-text-muted;
    font-size: 14px;
}

.sp-state--empty {
    gap: 4px;
}

.sp-state__title {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: $color-text;
}

.sp-state__body {
    margin: 0;
    font-size: 13px;
}

.sp-spinner {
    width: 22px;
    height: 22px;
    border: 2.5px solid $color-border;
    border-top-color: $color-primary;
    border-radius: 50%;
    animation: sp-spin 0.7s linear infinite;
}

@keyframes sp-spin {
    to { transform: rotate(360deg); }
}

.sp-groups {
    display: flex;
    flex-direction: column;
}

.sp-group {
    border-bottom: 1px solid $color-border;
    padding: 18px 0;

    &:last-child {
        border-bottom: none;
    }
}

.sp-group__heading {
    display: flex;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 10px;
}

.sp-group__day {
    font-size: 14px;
    font-weight: 700;
    color: $color-text;
}

.sp-group__time {
    font-size: 13px;
    font-weight: 600;
    color: $color-primary;
}

.sp-group__badge {
    font-size: 11px;
    font-weight: 600;
    color: $color-gold;
    background: rgba(226, 169, 69, 0.14);
    padding: 2px 8px;
    border-radius: 999px;
}

.sp-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 8px 12px;
    border-radius: 8px;

    &:hover {
        background: #f9fafb;
    }
}

.sp-option__label {
    font-size: 13.5px;
    font-weight: 600;
    color: $color-text;
}
</style>
