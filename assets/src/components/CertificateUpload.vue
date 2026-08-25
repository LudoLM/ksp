<template>
    <div>
        <input
            ref="fileInput"
            type="file"
            accept="application/pdf"
            class="hidden"
            @change="handleFileSelected"
        />

        <button
            @click="fileInput?.click()"
            :disabled="isUploading"
            class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span v-if="isUploading">Envoi en cours...</span>
            <span v-else>{{ buttonLabel }}</span>
        </button>

        <p v-if="errorMessage" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ errorMessage }}
        </p>

        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
            Format PDF uniquement, 5 Mo maximum.
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { apiFetch } from '@/utils/useFetchInterceptor.ts';
import {alertStore} from "@/store/alert.ts";

interface Props {
    buttonLabel?: string;
}

withDefaults(defineProps<Props>(), {
    buttonLabel: 'Transmettre mon certificat',
});

const emit = defineEmits<{
    uploaded: [];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);
const errorMessage = ref<string | null>(null);

const MAX_SIZE = 5 * 1024 * 1024; // 5 Mo

const handleFileSelected = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    errorMessage.value = null;

    if (!file) return;

    // Validation côté client (rapide, mais le backend revalide de toute façon)
    if (file.type !== 'application/pdf') {
        errorMessage.value = 'Seuls les fichiers PDF sont acceptés.';
        resetInput();
        return;
    }

    if (file.size > MAX_SIZE) {
        errorMessage.value = 'Le fichier ne doit pas dépasser 5 Mo.';
        resetInput();
        return;
    }

    await uploadFile(file);
};

const uploadFile = async (file: File) => {
    isUploading.value = true;
    errorMessage.value = null;

    const formData = new FormData();
    formData.append('certificate', file);
    try {
        const response = await apiFetch('/certificate/upload', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            errorMessage.value = data?.error ? data?.error : 'Une erreur est survenue lors de l\'envoi du certificat.';
            return;
        }

        emit('uploaded');
        alertStore.setAlert("Le certificat a bien été enregistré", "success");
    } catch (error) {
        errorMessage.value = 'Une erreur est survenue lors de l\'envoi du certificat.';
    } finally {
        isUploading.value = false;
        resetInput();
    }
};

const resetInput = () => {
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>
