<template>
    <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Dossier d'inscription {{ saison }}
            </h4>

            <span
                v-if="coursWishesForm?.status"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                :class="statusBadgeClass"
            >
                {{ statusLabel }}
            </span>
        </div>

        <p v-if="coursWishesForm?.status === 'Valide'" class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Votre dossier pour la saison {{ saison }} est validé.
        </p>
        <p v-else-if="coursWishesForm?.status === 'EnAttente'" class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Votre dossier est en attente de validation.
        </p>
        <p v-else-if="coursWishesForm?.status === 'ACorriger'" class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Votre dossier doit être corrigé, merci de le renvoyer.
        </p>
        <p v-else class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Aucun dossier envoyé. Il est requis pour réserver des cours.
        </p>

        <RouterLink
            to="/demandeInscription"
            class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
        >
            {{ coursWishesForm ? 'Modifier mon dossier' : 'Remplir mon dossier' }}
        </RouterLink>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { UserCoursWishesForm } from '@/store/user';

interface Props {
    coursWishesForm: UserCoursWishesForm | null;
}

const props = defineProps<Props>();

const STATUS_BADGE_CLASSES: Record<string, string> = {
    Valide: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    EnAttente: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    ACorriger: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const STATUS_LABELS: Record<string, string> = {
    Valide: 'Validé',
    EnAttente: 'En attente',
    ACorriger: 'À corriger',
};

const saison = computed(() => props.coursWishesForm?.saison ?? '');
const statusBadgeClass = computed(() => STATUS_BADGE_CLASSES[props.coursWishesForm?.status ?? ''] ?? '');
const statusLabel = computed(() => STATUS_LABELS[props.coursWishesForm?.status ?? ''] ?? '');
</script>
