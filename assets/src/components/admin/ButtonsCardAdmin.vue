<script setup>
import {inject, ref} from "vue";
import DeleteItem from "../../../icons/adminActions/DeleteItem.vue";
import CancelCours from "../../../icons/adminActions/CancelCours.vue";
import LaunchCours from "../../../icons/adminActions/LaunchCours.vue";
import EditCoursIcon from "../../../icons/adminActions/EditCoursIcon.vue";
import Tooltip from "../Tooltip.vue";
import ModalConfirm from "../modals/ModalConfirm.vue";
import {updateCours, useCancelCours, useOpenCours} from "@/utils/useActionCours.ts";
import {alertStore} from "@/store/alert.ts";

const props = defineProps({
  coursId:{
    type: Number,
    required: true
  },
})

const statusCours = defineModel('statusCours', {
    type: Object,
    required: true
})

const confirmDialog = ref(false);


const { deleteCreation } = inject('coursActions')


const onDeleteClick = async () => {
    await deleteCreation(props.coursId);
}
const cancelCours = async () => {
    const response = await useCancelCours(props.coursId);
    if (response.success) {
        statusCours.value = JSON.parse(response.statusChange);
    }
    alertStore.setAlert(response.message, response.type);
};


// A revoir
const openCreation = async () => {
    const response = await useOpenCours(props.coursId);
    if (response.success) {
        statusCours.value = JSON.parse(response.statusChange);
    }
    alertStore.setAlert(response.message, response.type);
};

</script>
<template>
<!--    DeleteCours-->
    <ModalConfirm
        v-model:isOpen="confirmDialog"
        title="Confirmation requise"
        message="Etes-vous sûr de vouloir supprimer ce cours ?"
        @confirmActions="onDeleteClick"
        v-if="statusCours.libelle === 'En création'"
    >
        <Tooltip
            :title="'Supprimer le cours.'"
        >
            <button class="hover:text buttonIcon">
                <DeleteItem size="18"/>
            </button>
        </Tooltip>
    </ModalConfirm>
<!--    EditCours-->
    <Tooltip
        title="Modifier le cours."
        v-if="statusCours.libelle === 'En création' || statusCours.libelle === 'Ouvert'"
        @click="updateCours(coursId)"
    >
        <button
            class="buttonIcon"
            @click="confirmDialog"
        >
            <EditCoursIcon size="18"/>
        </button>
    </Tooltip>
<!--    CancelCours-->
    <ModalConfirm
        v-model:isOpen="confirmDialog"
        title="Confirmation requise"
        message="Etes-vous sûr de vouloir annuler à ce cours ?"
        @confirmActions="cancelCours"
        v-if="(statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet')"
    >
        <Tooltip
            :title="'Annuler le cours.'"
        >
            <button class="buttonIcon">
                <CancelCours size="18"/>
            </button>

        </Tooltip>
    </ModalConfirm>
<!--    OpenCours-->
    <ModalConfirm
        v-model:isOpen="confirmDialog"
        title="Confirmation requise"
        message="Etes-vous sûr de vouloir vous ouvrir ce cours ?"
        @confirmActions="openCreation"
        v-if="statusCours.libelle === 'En création'"
    >
        <Tooltip
            :title="'Ouvrir le cours.'"
        >
                <button class="buttonIcon">
                    <LaunchCours size="18"/>
                </button>
        </Tooltip>
    </ModalConfirm>
</template>

