<script setup lang="ts">
import bannerImage from "../../../images/banners/imageBanner5.jpg";
import Banner from "../../components/Banner.vue";
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import SmartPagination from "../../components/admin/SmartPagination.vue";
import { apiFetch } from "@/utils/useFetchInterceptor.ts";
import { alertStore } from "@/store/alert.ts";
import { viewCertificatePdf } from "@/utils/viewCertificatePdf.ts";

interface CertificateUser {
    id: number;
    nom: string;
    prenom: string;
    email: string;
}

interface Certificate {
    id: number;
    status: string;
    uploadedAt: string;
    validUntil: string | null;
    certificateFilename: string;
    user: CertificateUser;
}

interface Metadata {
    total_pages: number;
    current_page: number;
    total_items: number;
}

const title = "Contrôle des certificats médicaux";
const route = useRoute();
const router = useRouter();

const certificates = ref<Certificate[]>([]);
const metadata = ref<Metadata>({ total_pages: 1, current_page: 1, total_items: 0 });
const currentPage = ref(1);
const loading = ref(false);

// Id du certificat en cours de traitement (approve/reject) : permet de désactiver
// uniquement la ligne concernée plutôt que toute la page.
const processingId = ref<number | null>(null);
// Id du certificat pour lequel on demande une confirmation avant rejet.
const confirmingRejectId = ref<number | null>(null);
// Motif du rejet, saisi par l'admin avant de confirmer.
const rejectReason = ref("");
// Id du certificat pour lequel on demande la date de validité avant approbation.
const confirmingApproveId = ref<number | null>(null);
// Date de validité (YYYY-MM-DD), pré-remplie à +1 an mais modifiable par l'admin.
const approveValidUntil = ref("");

const defaultValidUntil = (): string => {
    const date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    return date.toISOString().slice(0, 10);
};

// Le backend exige une date strictement future : on l'interdit aussi côté input.
// Constante (pas un ref) : ne dépend d'aucune interaction, pas besoin d'être réinitialisée.
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const approveMinValidUntil = tomorrow.toISOString().slice(0, 10);

const fetchCertificates = async (page: number = 1) => {
    loading.value = true;
    try {
        const res = await apiFetch(`/admin/certificates/pending?page=${page}`).then((r) => r.json());
        certificates.value = res.data;
        metadata.value = res.metadata;
        currentPage.value = res.metadata.current_page;
    } catch (error) {
        console.error("Erreur lors du chargement des certificats:", error);
        alertStore.setAlert("Erreur lors du chargement des certificats", "error");
        certificates.value = [];
        metadata.value = { total_pages: 1, current_page: 1, total_items: 0 };
    } finally {
        loading.value = false;
    }
};

const handlePageChange = async (newPage: number) => {
    if (newPage === currentPage.value) return;
    await router.push({ query: { page: newPage } });
};

const viewCertificate = (certificate: Certificate) => {
    viewCertificatePdf(certificate.id);
};

const askRejectConfirmation = (certificate: Certificate) => {
    confirmingApproveId.value = null;
    approveValidUntil.value = "";
    confirmingRejectId.value = certificate.id;
    rejectReason.value = "";
};

const cancelRejectConfirmation = () => {
    confirmingRejectId.value = null;
    rejectReason.value = "";
    confirmingRejectId.value = null;
    rejectReason.value = "";
};

const askApproveConfirmation = (certificate: Certificate) => {
    confirmingApproveId.value = certificate.id;
    approveValidUntil.value = defaultValidUntil();
};

const cancelApproveConfirmation = () => {
    confirmingApproveId.value = null;
    approveValidUntil.value = "";
};

const validateCertificate = async (certificate: Certificate, action: "approve" | "reject") => {
    if (action === "reject" && !rejectReason.value.trim()) {
        return;
    }
    if (action === "approve" && !approveValidUntil.value) {
        return;
    }

    processingId.value = certificate.id;
    try {
        const formData = new FormData();
        formData.append("action", action);
        if (action === "reject") {
            formData.append("reason", rejectReason.value.trim());
        } else {
            formData.append("validUntil", approveValidUntil.value);
        }
        const res = await apiFetch(`/admin/certificate/${certificate.id}/validate`, {
            method: "POST",
            body: formData,
        });
        if (res.ok) {
            const label = action === "approve" ? "approuvé" : "rejeté";
            alertStore.setAlert(`Certificat de ${certificate.user.prenom} ${certificate.user.nom} ${label} avec succès`, "success");
            // Retrait optimiste de la ligne : évite un rechargement complet de la page
            // et donne un retour visuel immédiat sur la liste "en attente".
            certificates.value = certificates.value.filter((c) => c.id !== certificate.id);
            metadata.value.total_items -= 1;
            if (certificates.value.length === 0 && currentPage.value > 1) {
                await handlePageChange(currentPage.value - 1);
            }
        } else {
            const data = await res.json().catch(() => null);
            alertStore.setAlert(data?.error ?? "Erreur lors de la validation du certificat", "error");
        }
    } catch (error) {
        alertStore.setAlert("Erreur lors de la validation du certificat", "error");
    } finally {
        processingId.value = null;
        confirmingRejectId.value = null;
        rejectReason.value = "";
        confirmingApproveId.value = null;
        approveValidUntil.value = "";
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return new Intl.DateTimeFormat("fr-FR", { day: "2-digit", month: "short", year: "numeric" }).format(date);
};

const getInitials = (user: CertificateUser) => {
    return `${user.prenom?.[0] ?? ""}${user.nom?.[0] ?? ""}`.toUpperCase();
};

watch(() => route.query.page, async (newPageFromUrl) => {
    const requestedPage = parseInt(newPageFromUrl as string ?? "1");
    if (isNaN(requestedPage) || requestedPage < 1) {
        await router.replace({ query: { page: 1 } });
        return;
    }
    await fetchCertificates(requestedPage);
    if (metadata.value.total_pages && requestedPage > metadata.value.total_pages) {
        await router.replace({ query: { page: metadata.value.total_pages } });
    }
}, { immediate: true });
</script>

<template>
    <Banner
        :title="title"
        :has-button="false"
        :background-color="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />

    <div class="cert-panel">
        <div class="cert-panel__header">
            <div>
                <h2 class="cert-panel__heading">Certificats en attente</h2>
                <p class="cert-panel__subheading">
                    {{ metadata.total_items }} certificat{{ metadata.total_items > 1 ? "s" : "" }} à traiter
                </p>
            </div>
        </div>

        <div v-if="loading" class="cert-state">
            <div class="cert-spinner" aria-hidden="true"></div>
            <p>Chargement des certificats…</p>
        </div>

        <div v-else-if="certificates.length === 0" class="cert-state cert-state--empty">
            <p class="cert-state__title">Aucun certificat en attente</p>
            <p class="cert-state__body">Les nouveaux certificats envoyés par les utilisateurs apparaîtront ici.</p>
        </div>

        <template v-else>
            <div class="cert-table" role="table">
                <div class="cert-table__row cert-table__row--head" role="row">
                    <span role="columnheader">Utilisateur</span>
                    <span role="columnheader">Envoyé le</span>
                    <span role="columnheader">Statut</span>
                    <span role="columnheader" class="cert-table__actions-head">Actions</span>
                </div>

                <div
                    v-for="cert in certificates"
                    :key="cert.id"
                    class="cert-table__row"
                    role="row"
                >
                    <div class="cert-user" role="cell">
                        <span class="cert-user__avatar" aria-hidden="true">{{ getInitials(cert.user) }}</span>
                        <div class="cert-user__info">
                            <span class="cert-user__name">{{ cert.user.prenom }} {{ cert.user.nom }}</span>
                            <span class="cert-user__email">{{ cert.user.email }}</span>
                        </div>
                    </div>

                    <div class="cert-date" role="cell">{{ formatDate(cert.uploadedAt) }}</div>

                    <div role="cell">
                        <span class="cert-badge cert-badge--pending">En attente</span>
                    </div>

                    <div class="cert-actions" role="cell">
                        <template v-if="confirmingRejectId === cert.id">
                            <div class="cert-actions__reject">
                                <label :for="`reject-reason-${cert.id}`" class="cert-actions__confirm-label">Motif du refus (transmis à l'utilisateur)</label>
                                <textarea
                                    :id="`reject-reason-${cert.id}`"
                                    v-model="rejectReason"
                                    class="cert-actions__reason-input"
                                    rows="2"
                                    placeholder="Ex. : document illisible, date de validité manquante…"
                                ></textarea>
                                <div class="cert-actions__reject-buttons">
                                    <button
                                        type="button"
                                        class="cert-btn cert-btn--danger"
                                        :disabled="processingId === cert.id || !rejectReason.trim()"
                                        @click="validateCertificate(cert, 'reject')"
                                    >
                                        {{ processingId === cert.id ? "Rejet…" : "Confirmer le rejet" }}
                                    </button>
                                    <button
                                        type="button"
                                        class="cert-btn cert-btn--ghost"
                                        :disabled="processingId === cert.id"
                                        @click="cancelRejectConfirmation"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="confirmingApproveId === cert.id">
                            <div class="cert-actions__reject">
                                <label :for="`approve-valid-until-${cert.id}`" class="cert-actions__confirm-label">Date de validité</label>
                                <input
                                    :id="`approve-valid-until-${cert.id}`"
                                    v-model="approveValidUntil"
                                    type="date"
                                    :min="approveMinValidUntil"
                                    class="cert-actions__reason-input"
                                />
                                <div class="cert-actions__reject-buttons">
                                    <button
                                        type="button"
                                        class="cert-btn cert-btn--success"
                                        :disabled="processingId === cert.id || !approveValidUntil"
                                        @click="validateCertificate(cert, 'approve')"
                                    >
                                        {{ processingId === cert.id ? "Approbation…" : "Confirmer l'approbation" }}
                                    </button>
                                    <button
                                        type="button"
                                        class="cert-btn cert-btn--ghost"
                                        :disabled="processingId === cert.id"
                                        @click="cancelApproveConfirmation"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <button
                                type="button"
                                class="cert-btn cert-btn--ghost"
                                :disabled="processingId === cert.id"
                                @click="viewCertificate(cert)"
                            >
                                Voir le PDF
                            </button>
                            <button
                                type="button"
                                class="cert-btn cert-btn--success"
                                :disabled="processingId === cert.id"
                                @click="askApproveConfirmation(cert)"
                            >
                                Approuver
                            </button>
                            <button
                                type="button"
                                class="cert-btn cert-btn--danger-outline"
                                :disabled="processingId === cert.id"
                                @click="askRejectConfirmation(cert)"
                            >
                                Rejeter
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <SmartPagination
                class="cert-pagination"
                :current-page="currentPage"
                :total-pages="metadata.total_pages"
                @page-changed="handlePageChange"
            />
        </template>
    </div>
</template>

<style scoped lang="scss">
$color-primary: #312e81;
$color-primary-dark: #1e1b4b;
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

.cert-panel {
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

.cert-state {
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

.cert-spinner {
    width: 22px;
    height: 22px;
    border: 2.5px solid $color-border;
    border-top-color: $color-primary;
    border-radius: 50%;
    animation: cert-spin 0.7s linear infinite;
}

@keyframes cert-spin {
    to { transform: rotate(360deg); }
}

.cert-table {
    display: flex;
    flex-direction: column;
    border: 1px solid $color-border;
    border-radius: $radius;
    overflow: hidden;

    &__row {
        display: grid;
        grid-template-columns: 2.2fr 1fr 1fr 2.4fr;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid $color-border;

        &:last-child {
            border-bottom: none;
        }

        &:not(&--head):hover {
            background: #f9fafb;
        }

        &--head {
            background: $color-primary-dark;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 18px;
        }
    }

    &__actions-head {
        text-align: right;
    }

    @media (max-width: 860px) {
        &__row {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        &__row--head {
            display: none;
        }
    }
}

.cert-user {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;

    &__avatar {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #ede9fe;
        color: $color-primary;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }

    &__info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    &__name {
        font-size: 14px;
        font-weight: 500;
        color: $color-text;
    }

    &__email {
        font-size: 12px;
        color: $color-text-muted;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}

.cert-date {
    font-size: 13px;
    color: $color-text;
}

.cert-badge {
    display: inline-flex;
    align-items: center;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 999px;

    &--pending {
        background: $color-warning-bg;
        color: $color-warning-text;
    }
}

.cert-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;

    &__confirm-label {
        font-size: 12px;
        color: $color-danger;
        font-weight: 500;
        margin-right: 4px;
    }

    &__reject {
        display: flex;
        flex-direction: column;
        gap: 6px;
        width: 100%;
    }

    &__reason-input {
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

    &__reject-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
}

.cert-btn {
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

.cert-pagination {
    margin: 20px 0 8px;
}
</style>
