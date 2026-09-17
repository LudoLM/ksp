
        <script setup lang="ts">
            import bannerImage from "../../../images/banners/imageBanner5.jpg";
            import Banner from "../../components/Banner.vue";
            import { ref, watch } from "vue";
            import { useRoute, useRouter } from "vue-router";
            import SmartPagination from "../../components/admin/SmartPagination.vue";
            import { apiFetch } from "@/utils/useFetchInterceptor.ts";
            import { alertStore } from "@/store/alert.ts";
            import { SEASON_PLANNING_DAYS } from "@/constants/seasonPlanningDays.ts";

            interface Creneau {
                id: number;
                daySelected: number;
                timeSelected: string;
                cours: string;
            }

            interface WishesForm {
                id: number;
                email: string;
                contactNom: string | null;
                contactPrenom: string | null;
                contactTelephone: string | null;
                saison: string;
                status: string;
                submittedAt: string;
                creneauPrimaire: Creneau | null;
                creneauSecondaire: Creneau | null;
                packSouhaite: string;
                modeReglement: string;
                user: number | null;
            }

            interface Metadata {
                total_pages: number;
                current_page: number;
                total_items: number;
            }

            const title = "Contrôle des dossiers d'inscription";
            const route = useRoute();
            const router = useRouter();

            const forms = ref<WishesForm[]>([]);
            const metadata = ref<Metadata>({ total_pages: 1, current_page: 1, total_items: 0 });
            const currentPage = ref(1);
            const loading = ref(false);
            const processingId = ref<number | null>(null);
            const confirmingCorrectionId = ref<number | null>(null);
            const correctionReason = ref("");

            const fetchForms = async (page: number = 1) => {
                loading.value = true;
                try {
                    const res = await apiFetch(`/admin/cours-wishes-form/pending?page=${page}`).then((r) => r.json());
                    forms.value = res.data;
                    metadata.value = res.metadata;
                    currentPage.value = res.metadata.current_page;
                } catch (error) {
                    alertStore.setAlert("Erreur lors du chargement des dossiers", "error");
                    forms.value = [];
                    metadata.value = { total_pages: 1, current_page: 1, total_items: 0 };
                } finally {
                    loading.value = false;
                }
            };

            const handlePageChange = async (newPage: number) => {
                if (newPage === currentPage.value) return;
                await router.push({ query: { page: newPage } });
            };

            const askCorrection = (form: WishesForm) => {
                confirmingCorrectionId.value = form.id;
                correctionReason.value = "";
            };

            const cancelCorrection = () => {
                confirmingCorrectionId.value = null;
                correctionReason.value = "";
            };

            const validateForm = async (form: WishesForm, action: "approve" | "correction") => {
                if (action === "correction" && !correctionReason.value.trim()) return;

                processingId.value = form.id;
                try {
                    const formData = new FormData();
                    formData.append("action", action);
                    if (action === "correction") {
                        formData.append("reason", correctionReason.value.trim());
                    }
                    const res = await apiFetch(`/admin/cours-wishes-form/${form.id}/validate`, {
                        method: "POST",
                        body: formData,
                    });
                    if (res.ok) {
                        const label = action === "approve" ? "validé" : "renvoyé pour correction";
                        alertStore.setAlert(`Dossier de ${form.email} ${label}`, "success");
                        forms.value = forms.value.filter((f) => f.id !== form.id);
                        metadata.value.total_items -= 1;
                        if (forms.value.length === 0 && currentPage.value > 1) {
                            await handlePageChange(currentPage.value - 1);
                        }
                    } else {
                        const data = await res.json().catch(() => null);
                        alertStore.setAlert(data?.error ?? "Erreur lors de la validation du dossier", "error");
                    }
                } catch (error) {
                    alertStore.setAlert("Erreur lors de la validation du dossier", "error");
                } finally {
                    processingId.value = null;
                    confirmingCorrectionId.value = null;
                    correctionReason.value = "";
                }
            };

            const formatDate = (dateString: string) => {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                return new Intl.DateTimeFormat("fr-FR", { day: "2-digit", month: "short", year: "numeric" }).format(date);
            };

            const STALE_THRESHOLD_DAYS = 7;

            const daysSince = (dateString: string): number => {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 0;
                return Math.floor((Date.now() - date.getTime()) / (1000 * 60 * 60 * 24));
            };

            const isStale = (form: WishesForm): boolean => daysSince(form.submittedAt) >= STALE_THRESHOLD_DAYS;

            const creneauLabel = (creneau: Creneau | null) => {
                if (!creneau) return null;
                const day = SEASON_PLANNING_DAYS.find((d) => d.id === creneau.daySelected)?.name ?? "?";
                return `${day} ${creneau.timeSelected} — ${creneau.cours}`;
            };

            watch(() => route.query.page, async (newPageFromUrl) => {
                const requestedPage = parseInt(newPageFromUrl as string ?? "1");
                if (isNaN(requestedPage) || requestedPage < 1) {
                    await router.replace({ query: { page: 1 } });
                    return;
                }
                await fetchForms(requestedPage);
                if (metadata.value.total_pages && requestedPage > metadata.value.total_pages) {
                    await router.replace({ query: { page: metadata.value.total_pages } });
                }
            }, { immediate: true });
        </script>

        <template>
            <Banner :title="title" :has-button="false" :background-color="'rgba(30, 27, 75, .9)'" :image="bannerImage" />

            <div class="wf-panel">
                <div class="wf-panel__header">
                    <div>
                        <h2 class="wf-panel__heading">Dossiers en attente</h2>
                        <p class="wf-panel__subheading">
                            {{ metadata.total_items }} dossier{{ metadata.total_items > 1 ? "s" : "" }} à traiter
                        </p>
                    </div>
                </div>

                <div v-if="loading" class="wf-state">
                    <div class="wf-spinner" aria-hidden="true"></div>
                    <p>Chargement des dossiers…</p>
                </div>

                <div v-else-if="forms.length === 0" class="wf-state wf-state--empty">
                    <p class="wf-state__title">Aucun dossier en attente</p>
                    <p class="wf-state__body">Les nouvelles demandes d'inscription apparaîtront ici.</p>
                </div>

                <div v-else>
                    <div class="wf-cards">
                        <div v-for="form in forms" :key="form.id" class="wf-card">
                            <div class="wf-card__header">
                                <div class="wf-card__identity">
                                    <span class="wf-card__email">{{ form.email }}</span>
                                    <span class="wf-card__meta">{{ form.saison }} — envoyé le {{ formatDate(form.submittedAt) }}</span>
                                </div>
                                <span class="wf-badge" :class="isStale(form) ? 'wf-badge--stale' : 'wf-badge--pending'">
                            {{ isStale(form) ? `En attente depuis ${daysSince(form.submittedAt)} j` : "En attente" }}
                        </span>
                            </div>

                            <div class="wf-card__body">
                                <div class="wf-field">
                                    <span class="wf-field__label">Créneau prioritaire</span>
                                    <span v-if="form.creneauPrimaire" class="wf-creneau wf-creneau--primaire">
                                {{ creneauLabel(form.creneauPrimaire) }}
                            </span>
                                    <span v-else class="wf-field__empty">Non renseigné</span>
                                </div>
                                <div class="wf-field">
                                    <span class="wf-field__label">Créneau secondaire</span>
                                    <span v-if="form.creneauSecondaire" class="wf-creneau wf-creneau--secondaire">
                                {{ creneauLabel(form.creneauSecondaire) }}
                            </span>
                                    <span v-else class="wf-field__empty">Non renseigné</span>
                                </div>
                                <div class="wf-field">
                                    <span class="wf-field__label">Contact</span>
                                    <span v-if="form.contactNom || form.contactPrenom">{{ form.contactPrenom }} {{ form.contactNom }}</span>
                                    <span v-else class="wf-field__empty">Non renseigné</span>
                                </div>
                                <div class="wf-field">
                                    <span class="wf-field__label">Téléphone</span>
                                    <span v-if="form.contactTelephone">{{ form.contactTelephone }}</span>
                                    <span v-else class="wf-field__empty">Non renseigné</span>
                                </div>
                                <div class="wf-field">
                                    <span class="wf-field__label">Forfait souhaité</span>
                                    <span>{{ form.packSouhaite }}</span>
                                </div>
                                <div class="wf-field">
                                    <span class="wf-field__label">Règlement</span>
                                    <span>{{ form.modeReglement }}</span>
                                </div>
                            </div>

                            <div class="wf-card__actions">
                                <template v-if="confirmingCorrectionId === form.id">
                                    <div class="wf-actions__reject">
                                        <label :for="`correction-reason-${form.id}`" class="wf-actions__confirm-label">Motif (transmis à l'utilisateur)</label>
                                        <textarea
                                            :id="`correction-reason-${form.id}`"
                                            v-model="correctionReason"
                                            class="wf-actions__reason-input"
                                            rows="2"
                                            placeholder="Ex. : créneau complet, merci d'en choisir un autre…"
                                        ></textarea>
                                        <div class="wf-actions__reject-buttons">
                                            <button
                                                type="button"
                                                class="wf-btn wf-btn--danger"
                                                :disabled="processingId === form.id || !correctionReason.trim()"
                                                @click="validateForm(form, 'correction')"
                                            >
                                                {{ processingId === form.id ? "Envoi…" : "Confirmer" }}
                                            </button>
                                            <button
                                                type="button"
                                                class="wf-btn wf-btn--ghost"
                                                :disabled="processingId === form.id"
                                                @click="cancelCorrection"
                                            >
                                                Annuler
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                            <span v-if="!form.creneauPrimaire" class="wf-actions__hint">
                                Créneau prioritaire manquant — validation impossible
                            </span>
                                    <button
                                        type="button"
                                        class="wf-btn wf-btn--success"
                                        :disabled="processingId === form.id || !form.creneauPrimaire"
                                        @click="validateForm(form, 'approve')"
                                    >
                                        {{ processingId === form.id ? "Validation…" : "Valider" }}
                                    </button>
                                    <button
                                        type="button"
                                        class="wf-btn wf-btn--danger-outline"
                                        :disabled="processingId === form.id"
                                        @click="askCorrection(form)"
                                    >
                                        Renvoyer pour correction
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <SmartPagination
                        class="wf-pagination"
                        :current-page="currentPage"
                        :total-pages="metadata.total_pages"
                        @page-changed="handlePageChange"
                    />
                </div>
            </div>
        </template>




<style scoped lang="scss">
$color-primary: #472371;
$color-primary-dark: #37195a;
$color-accent: #e2a945;
$color-success: #15803d;
$color-success-bg: #dcfce7;
$color-danger: #b91c1c;
$color-danger-bg: #fee2e2;
$color-warning-bg: #fef3c7;
$color-warning-text: #92400e;
$color-border: #e5e7eb;
$color-text: #111827;
$color-text-muted: #6b7280;
$radius: 10px;

.wf-panel {
    margin: 40px 40px 24px;
    background: #fff;
    border: 1px solid $color-border;
    border-radius: $radius;
    padding: 24px 28px 12px;

    &__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    &__heading {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: $color-text;
    }

    &__subheading {
        margin: 4px 0 0;
        font-size: 13px;
        color: $color-text-muted;
    }
}

.wf-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 56px 16px;
    color: $color-text-muted;
    font-size: 14px;

    &--empty {
        gap: 4px;
    }

    &__title {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: $color-text;
    }

    &__body {
        margin: 0;
        font-size: 13px;
    }
}

.wf-spinner {
    width: 22px;
    height: 22px;
    border: 2.5px solid $color-border;
    border-top-color: $color-primary;
    border-radius: 50%;
    animation: wf-spin 0.7s linear infinite;
}

@keyframes wf-spin {
    to { transform: rotate(360deg); }
}

.wf-cards {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 20px;
}

.wf-card {
    border: 1px solid $color-border;
    border-radius: $radius;
    overflow: hidden;

    &__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        background: $color-primary-dark;
        color: #fff;
    }

    &__identity {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    &__email {
        font-size: 14px;
        font-weight: 600;
    }

    &__meta {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.75);
    }

    &__body {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px 24px;
        padding: 18px;
    }

    &__actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        padding: 14px 18px;
        border-top: 1px solid $color-border;
        background: #f9fafb;
    }

    @media (max-width: 640px) {
        &__body {
            grid-template-columns: 1fr;
        }
    }
}

.wf-field {
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-size: 14px;
    color: $color-text;

    &__label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: $color-text-muted;
    }

    &__empty {
        color: $color-text-muted;
        font-style: italic;
    }
}

.wf-creneau {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;

    &--primaire {
        background: #ede4f7;
        color: $color-primary;
    }

    &--secondaire {
        background: #fbedd3;
        color: #8a6417;
    }
}

.wf-badge {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 999px;

    &--pending {
        background: $color-warning-bg;
        color: $color-warning-text;
    }

    &--stale {
        background: $color-danger-bg;
        color: $color-danger;
    }
}

.wf-actions__reject {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
}

.wf-actions__confirm-label {
    font-size: 12px;
    color: $color-danger;
    font-weight: 500;
}

.wf-actions__hint {
    font-size: 12px;
    color: $color-text-muted;
    font-style: italic;
    align-self: center;
}

.wf-actions__reason-input {
    width: 100%;
    font-size: 12px;
    font-family: inherit;
    padding: 6px 8px;
    border: 1px solid $color-border;
    border-radius: 6px;
    resize: vertical;

    &:focus {
        outline: none;
        border-color: $color-danger;
    }
}

.wf-actions__reject-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.wf-btn {
    font-size: 12px;
    font-weight: 500;
    padding: 7px 12px;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background-color 0.15s ease, opacity 0.15s ease;
    white-space: nowrap;

    &:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    &--ghost {
        background: #fff;
        border-color: $color-border;
        color: $color-text;

        &:hover:not(:disabled) {
            background: #f3f4f6;
        }
    }

    &--success {
        background: $color-success-bg;
        color: $color-success;

        &:hover:not(:disabled) {
            background: darken($color-success-bg, 5%);
        }
    }

    &--danger-outline {
        background: #fff;
        border-color: $color-danger-bg;
        color: $color-danger;

        &:hover:not(:disabled) {
            background: $color-danger-bg;
        }
    }

    &--danger {
        background: $color-danger;
        color: #fff;

        &:hover:not(:disabled) {
            background: darken($color-danger, 8%);
        }
    }
}

.wf-pagination {
    margin: 20px 0 8px;
}
</style>
