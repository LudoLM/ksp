<script setup>

import {useDateFormat} from "@vueuse/core";
import StatusCoursTag from "./StatusCoursTag.vue";
import ButtonsCardAdmin from "./admin/ButtonsCardAdmin.vue";
import {useCancelCours, useDeleteCours, useOpenCours} from "../utils/useActionCours";
import {computed, inject, ref} from "vue";
import {useRouter} from "vue-router";
import {alertStore} from "../store/alert";
import {useCounterSubscribed} from "@/utils/useCounterSubscribed.ts";
import InfosItem from "../../icons/adminActions/InfosItem.vue";
import Tooltip from "@/components/Tooltip.vue";
import AddExtraUser from "../../icons/adminActions/AddExtraUser.vue";
import ModalAddExtra from "@/components/modals/ModalAddExtra.vue";

const router = useRouter();


const props = defineProps(
    {
        item: Object
    }
)

const emit = defineEmits(['cancelCours', 'deleteCreation', 'updateCreation', 'openCreation', 'handleAddExtraResponse']);
const statusCours = computed(() => props.item.statusCours);
const addExtraDialog = ref(false);

const usersQuantity = ref(useCounterSubscribed(props.item.usersCours));

const handleAddExtraResponse = ({ type, message, statusChange, usersCount }) => {
    if (type === 'success') {
        statusCours.value = JSON.parse(statusChange);
        usersQuantity.value = usersCount;
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
            {{ usersQuantity }} / {{ item.nbInscriptionMax }}
        </div>

        <!-- Statut -->
        <div class="status">
            <StatusCoursTag class="statusCoursTag" :statusCours="statusCours" />
        </div>

        <!-- Actions -->
        <div class="actions">
            <div class="flex justify-start items-center gap-8">
                <Tooltip
                    :title="'Voir les détails du cours.'"
                >
                    <button class="hover:text buttonIcon">
                        <router-link :to="{ name: 'AdminCoursDetails', params: { id: item.id }}">
                            <InfosItem
                                size="18"/>
                        </router-link>
                    </button>
                </Tooltip>
                <ButtonsCardAdmin
                    v-model:statusCours="statusCours"
                    :coursId="item.id"
                />
                <ModalAddExtra
                    v-if="statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet'"
                    v-model:isOpen="addExtraDialog"
                    title="Ajouter un extra"
                    message="Sélectionner l'extra à ajouter."
                    :cours="item.id"
                    @subscriptionResponse="(data) =>handleAddExtraResponse(data)"
                >
                    <Tooltip
                        :title="'Ajouter un extra.'"
                    >
                        <button class="flex items-center justify-center buttonIcon">
                            <AddExtraUser size="18"/>
                        </button>
                    </Tooltip>
                </ModalAddExtra>
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
