<script setup>

import ModalAddExtra from "../modals/ModalAddExtra.vue";
import {ref} from "vue";
import DeleteItem from "../../../icons/adminActions/DeleteItem.vue";
import CancelCours from "../../../icons/adminActions/CancelCours.vue";
import LaunchCours from "../../../icons/adminActions/LaunchCours.vue";
import InfosItem from "../../../icons/adminActions/InfosItem.vue";
import AddExtraUser from "../../../icons/adminActions/AddExtraUser.vue";
import EditCoursIcon from "../../../icons/adminActions/EditCoursIcon.vue";
import Tooltip from "../Tooltip.vue";
import ModalConfirm from "../modals/ModalConfirm.vue";

defineProps({
  statusCours:{
    type: Object,
    required: true
  },
  coursId:{
    type: Number,
    required: true
  },

})

const addExtraDialog = ref(false);
const confirmDialog = ref(false);
const emit = defineEmits([
  'openCreation',
  'updateCreation',
  'updateCours',
  'deleteCreation',
  'cancelCours',
  'handleAddExtraResponse'
])

</script>
<template>

    <Tooltip
        :title="'Voir les détails du cours.'"
    >
        <button class="hover:text">
            <router-link :to="{ name: 'AdminCoursDetails', params: { id: coursId }}">
                <InfosItem
                    data-text="Why did you hovered?"
                    size="18"/>
            </router-link>
        </button>
    </Tooltip>
    <ModalConfirm
        v-model:isOpen="confirmDialog"
        title="Confirmation requise"
        message="Etes-vous sûr de vouloir supprimer ce cours ?"
        @confirmActions="emit('deleteCreation')"
        v-if="statusCours.libelle === 'En création'"
    >
        <Tooltip
            :title="'Supprimer le cours.'"
        >
            <button class="hover:text">
                <DeleteItem size="18"/>
            </button>
        </Tooltip>
    </ModalConfirm>
    <Tooltip
        title="Modifier le cours."
        v-if="statusCours.libelle === 'En création' || statusCours.libelle === 'Ouvert'"
        @click="emit(statusCours.libelle === 'En création' ? 'updateCreation' : 'updateCours')"
    >
        <button
            @click="confirmDialog"
        >
            <EditCoursIcon size="18"/>
        </button>
    </Tooltip>
    <ModalConfirm
        v-model:isOpen="confirmDialog"
        title="Confirmation requise"
        message="Etes-vous sûr de vouloir annuler à ce cours ?"
        @confirmActions="emit('cancelCours')"
        v-if="(statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet')"
    >
        <Tooltip
            :title="'Annuler le cours.'"
        >
            <button>
                <CancelCours size="18"/>
            </button>

        </Tooltip>
    </ModalConfirm>
    <ModalConfirm
        v-model:isOpen="confirmDialog"
        title="Confirmation requise"
        message="Etes-vous sûr de vouloir vous ouvrir ce cours ?"
        @confirmActions="emit('openCreation')"
        v-if="statusCours.libelle === 'En création'"
    >
        <Tooltip
            :title="'Ouvrir le cours.'"
        >
            <button>
                <LaunchCours size="18"/>
            </button>
        </Tooltip>
    </ModalConfirm>
  <ModalAddExtra
      v-if="statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet'"
      v-model:isOpen="addExtraDialog"
      title="Ajouter un extra"
      message="Sélectionner l'extra à ajouter."
      :cours="coursId"
      @subscriptionResponse="(data) => emit('handleAddExtraResponse', data)"
  >
      <Tooltip
            :title="'Ajouter un extra.'"
      >
          <button class="flex items-center justify-center">
              <AddExtraUser size="18"/>
          </button>
      </Tooltip>
  </ModalAddExtra>
</template>


<style scoped>

    button{
        padding: 10px;
        border: 1px solid #E5E7EB;
        border-radius: 50%;
        transition: border 0.3s ease-in-out;
        font-size: clamp(0.8rem, 1.5vw, 1rem);

        &>p{
            font-size: clamp(0.8rem, 1.5vw, 1rem);
        }
    }
</style>
