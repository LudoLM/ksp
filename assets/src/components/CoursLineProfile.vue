<script setup>

import {useDateFormat} from "@vueuse/core";
import StatusCoursTag from "./StatusCoursTag.vue";
import Unsubscribe from "../../icons/userActions/Unsubscribe.vue";
import Tooltip from "./Tooltip.vue";
import ModalConfirm from "./modals/ModalConfirm.vue";
import {ref} from "vue";

const props = defineProps(
    {
        item: Object
    }
)
const emit = defineEmits(['unsubscription']);
const confirmDialog = ref(false);

const handleUnsubscription = () => {
    emit('unsubscription', {
        id: props.item.id
    });
};

</script>


<template>
    <div class="container py-5 px-4">
        <!-- Titre du cours -->
        <div class="libelle">
            <h5 class="text-sm font-medium text-gray-800 dark:text-white/90">{{ item.typeCours.libelle }}</h5>
        </div>

        <!-- Date/Heure -->
        <div class="date">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ useDateFormat(item.dateCours, 'D/MM/YYYY - HH:mm') }}</p>
        </div>


        <!-- Statut -->
        <div class="status">
            <StatusCoursTag class="statusCoursTag" :statusCours="item.statusCours" />
        </div>

        <!-- Actions -->
        <div class="actions">
            <div class="flex justify-start items-center gap-8">
                <ModalConfirm
                    v-model:isOpen="confirmDialog"
                    title="Confirmation requise"
                    message="Etes vous sûr de vouloir vous désinscrire de ce cours ?"
                    @confirmActions="handleUnsubscription"
                    v-if="item.statusCours.id === 1 || item.statusCours.id === 2"
                >
                    <button
                        class="hover:text flex items-center justify-center">
                        <Tooltip
                            :title="'Se désinscrire du cours.'"
                            :tooltipPos="'left'"
                        >
                            <Unsubscribe size="18"/>
                        </Tooltip>
                    </button>
                </ModalConfirm>
            </div>
        </div>
    </div>
</template>

<style scoped>
.container {
    display: grid;
    grid-template-columns: 3fr 3fr 2fr 1fr;
    gap: 1rem;
    width: 100%;
    max-width: 100%;
    align-items: center;
}

button{
    padding: 10px;
    border: 1px solid #E5E7EB;
    border-radius: 50%;
    transition: border 0.3s ease-in-out;
}

.actions{
    display: flex;
    justify-content: center;
}

@media (max-width: 850px) {
    .container {
        display: grid;
        grid-template-areas:
            "libelle status"
            "date actions";
        grid-template-columns: 1fr auto;
        gap: 1rem;
    }

    .libelle {
        grid-area: libelle;
    }

    .status {
        grid-area: status;
    }

    .date {
        grid-area: date;
    }

    .actions {
        grid-area: actions;
    }
}
</style>
