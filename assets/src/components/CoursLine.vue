<script setup>

import {useDateFormat} from "@vueuse/core";
import StatusCoursTag from "./StatusCoursTag.vue";
import ButtonsCardAdmin from "./admin/ButtonsCardAdmin.vue";
import {useCancelCours, useDeleteCours, useOpenCours} from "../utils/useActionCours";
import {inject, ref} from "vue";
import {useRouter} from "vue-router";

const alertStore = inject('alertStore');
const router = useRouter();


const props = defineProps(
    {
        item: Object
    }
)
const emit = defineEmits(['cancelCours', 'deleteCreation', 'updateCreation', 'openCreation', 'handleAddExtraResponse']);
const statusCours = ref(props.item.statusCours);
const usersCount = ref(props.item.usersCours.filter(cours => cours.isOnWaitingList === false).length);

const deleteCreation = async () => {
    const response = await useDeleteCours(props.item.id);
    alertStore.setAlert(response.message, response.type);
    if (response.success){
        emit('deleteCreation', {
            id: props.item.id
        });
    }
};

const updateCreation = () => {
    router.push({ name: 'EditCours', params: { id: props.item.id } });
};

const cancelCours = async () => {
    const response = await useCancelCours(props.item.id);
    if (response.success) {
        statusCours.value = JSON.parse(response.statusChange);
    }
    alertStore.setAlert(response.message, response.type);
};


// A revoir
const openCreation = async () => {
    const response = await useOpenCours(props.item.id);
    if (response.success) {
        statusCours.value = JSON.parse(response.statusChange);
    }
    alertStore.setAlert(response.message, response.type);
};

const handleAddExtraResponse = ({ type, message, statusChange }) => {
    if (type === 'success') {
        statusCours.value = JSON.parse(statusChange);
        usersCount.value++;
    }
    alertStore.setAlert(message, type);
};



</script>

<template>
    <div class="container py-5 px-4">
        <!-- Titre du cours -->
        <div class="libelle">
            <h5 class="font-bold text-black dark:text-white">{{ item.typeCours.libelle }}</h5>
        </div>

        <!-- Date/Heure -->
        <div class="date">
            <p class="text-black dark:text-white">{{ useDateFormat(item.dateCours, 'D/MM/YYYY - HH:mm') }}</p>
        </div>

        <!-- Inscrits -->
        <div class="users">
            {{ usersCount }} / {{ item.nbInscriptionMax }}
        </div>

        <!-- Statut -->
        <div class="status">
            <StatusCoursTag class="statusCoursTag" :statusCours="statusCours" />
        </div>

        <!-- Actions -->
        <div class="actions">
            <div class="flex justify-start items-center gap-8">
                <ButtonsCardAdmin
                    :statusCours="statusCours"
                    :coursId="item.id"
                    @cancelCours="cancelCours"
                    @deleteCreation="deleteCreation"
                    @updateCreation="updateCreation"
                    @openCreation="openCreation"
                    @handleAddExtraResponse="handleAddExtraResponse"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
    .container {
        display: grid;
        grid-template-columns: 3fr 3fr 2fr 2fr 6fr;
        gap: 1rem;
        width: 100%;
        max-width: 100%;
        align-items: center;
    }

@media (max-width: 850px) {
    .container {
        display: grid;
        grid-template-areas:
            "libelle status"
                "date users"
            "actions actions";
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

    .users {
        grid-area: users;
        display: flex;
        justify-content: center;
    }

    .actions {
        grid-area: actions;
    }
}
</style>
