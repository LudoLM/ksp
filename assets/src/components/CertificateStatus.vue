<template>
    <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Certificat médical
            </h4>

            <span
                v-if="certificatMedical?.validUntil"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                :class="statusBadgeClass"
            >
                <component :is="statusIcon" :size="14" />
                {{ statusLabel }}
            </span>
        </div>

        <!-- Infos sur le certificat actuel -->
        <div v-if="certificatMedical?.status" class="mb-4">
            <p v-if="certificatMedical.status === 'Approved'" class="text-sm text-gray-500 dark:text-gray-400">
                Valide jusqu'au <span class="font-medium text-gray-700 dark:text-gray-300">{{ validUntilDate }}</span>
            </p>
            <p v-else-if="certificatMedical.status === 'Pending'" class="text-sm text-gray-500 dark:text-gray-400">
                Transmis le {{ uploadedAtDate }}, en attente de validation
            </p>
            <p v-else-if="certificatMedical.status === 'Rejected'" class="text-sm text-gray-500 dark:text-gray-400">
                Certificat refusé<span v-if="certificatMedical.rejectionReason"> : {{ certificatMedical.rejectionReason }}</span>.
                Merci d'en transmettre un nouveau.
            </p>
            <p v-else-if="certificatMedical.status === 'Expired'" class="text-sm text-gray-500 dark:text-gray-400">
                Expiré depuis le {{ validUntilDate }}
            </p>
        </div>
        <p v-else class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Aucun certificat transmis. Il est requis pour réserver certains cours.
        </p>

        <!-- Upload / re-upload, toujours disponible -->
        <CertificateUpload
            :buttonLabel="certificatMedical?.status ? 'Transmettre un nouveau certificat' : 'Transmettre mon certificat'"
            @uploaded="emit('uploaded')"
        />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDateFormat } from '@vueuse/core';
import { CheckCircle, Clock, XCircle, AlertCircle } from 'lucide-vue-next';
import CertificateUpload from '@/components/CertificateUpload.vue';
import type { UserCertificatMedical } from '@/store/user';

interface Props {
    certificatMedical: UserCertificatMedical | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    uploaded: [];
}>();

const STATUS_ICONS = {
    Approved: CheckCircle,
    Pending: Clock,
    Rejected: XCircle,
    Expired: AlertCircle,
};

const STATUS_BADGE_CLASSES = {
    Approved: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    Pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    Rejected: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    Expired: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const STATUS_LABELS = {
    Approved: 'Valide',
    Pending: 'En attente',
    Rejected: 'Refusé',
    Expired: 'Expiré',
};

const uploadedAtDate = computed(() => {
    if (!props.certificatMedical?.uploadedAt) return '';
    return useDateFormat(props.certificatMedical.uploadedAt, 'DD/MM/YYYY à HH:mm').value;
});

const validUntilDate = computed(() => {
    if (!props.certificatMedical?.validUntil) return '';
    return useDateFormat(props.certificatMedical.validUntil, 'DD/MM/YYYY').value;
});

const statusIcon = computed(() => {
    const status = props.certificatMedical?.status;
    return status ? STATUS_ICONS[status as keyof typeof STATUS_ICONS] : null;
});

const statusBadgeClass = computed(() => {
    const status = props.certificatMedical?.status;
    return status ? STATUS_BADGE_CLASSES[status as keyof typeof STATUS_BADGE_CLASSES] : '';
});

const statusLabel = computed(() => {
    const status = props.certificatMedical?.status;
    return status ? STATUS_LABELS[status as keyof typeof STATUS_LABELS] : '';
});
</script>
