<script setup>

import ModalAddExtra from "../modal/ModalAddExtra.vue";
import {ref} from "vue";
import DeleteCours from "../../../icons/adminActions/DeleteCours.vue";
import CancelCours from "../../../icons/adminActions/CancelCours.vue";
import LaunchCours from "../../../icons/adminActions/LaunchCours.vue";
import InfosCours from "../../../icons/adminActions/InfosCours.vue";
import AddExtraUser from "../../../icons/adminActions/AddExtraUser.vue";
import EditCoursIcon from "../../../icons/adminActions/EditCoursIcon.vue";
import Tooltip from "../Tooltip.vue";

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
        <button class="hover:text-primary">
            <router-link :to="{ name: 'AdminCoursDetails', params: { id: coursId }}">
                <InfosCours
                    class="hover-text"
                    data-text="Why did you hovered?"
                    size="18"/>
            </router-link>
        </button>
    </Tooltip>
    <Tooltip
        :title="'Supprimer le cours.'"
        v-if="statusCours.libelle === 'En création'" @click="emit('deleteCreation')"
    >
        <button class="hover:text-primary">
            <DeleteCours size="18"/>
        </button>
    </Tooltip>
    <Tooltip
        title="Modifier le cours."
        v-if="statusCours.libelle === 'En création'" @click="emit('updateCreation')"
    >
        <button class="hover:text-primary" >
            <EditCoursIcon size="18"/>
        </button>
    </Tooltip>
    <Tooltip
        :title="'Annuler le cours.'"
        v-if="(statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet')"
        @click="emit('cancelCours')"
    >
        <button class="hover:text-primary">
            <CancelCours size="18"/>
        </button>
    </Tooltip>
    <Tooltip
        title="Modifier le cours."
        v-if="statusCours.libelle === 'Ouvert'" @click="emit('updateCours')"
    >
        <button class="hover:text-primary" >
            <EditCoursIcon
                size="18"
            />
        </button>
    </Tooltip>
    <Tooltip
        :title="'Ouvrir le cours.'"
        v-if="statusCours.libelle === 'En création'"
        @click="emit('openCreation')"
    >
        <button class="hover:text-primary">
            <LaunchCours size="18"/>
        </button>
    </Tooltip>
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
          <button class="hover:text-primary flex items-center justify-center">
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
