<script setup>

import ModalAddExtra from "../modal/ModalAddExtra.vue";
import {ref} from "vue";
import DeleteCours from "../../../icons/adminActions/DeleteCours.vue";
import CancelCours from "../../../icons/adminActions/CancelCours.vue";
import LaunchCours from "../../../icons/adminActions/LaunchCours.vue";
import InfosCours from "../../../icons/adminActions/InfosCours.vue";
import AddExtraUser from "../../../icons/adminActions/AddExtraUser.vue";
import EditCours from "../../../icons/adminActions/EditCours.vue";

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
  'deleteCreation',
  'cancelCours',
  'handleAddExtraResponse'
])

</script>
<template>
<!--    Ancienne version Cours Card Presentation-->
<!--  <CustomButton v-if="props.statusCours.libelle === 'En création'" @click="emit('openCreation')">
    Ouvrir
  </CustomButton>
  <CustomButton v-if="props.statusCours.libelle === 'En création'" @click="emit('updateCreation')">
    Modifier
  </CustomButton>
  <CustomButton v-if="props.statusCours.libelle === 'En création'" @click="emit('deleteCreation')" :color="'red'">
    Supprimer
  </CustomButton>
  <CustomButton v-if="(props.statusCours.libelle === 'Ouvert' || props.statusCours.libelle === 'Complet')" @click="emit('cancelCours')">
    Annuler
  </CustomButton>-->


    <button class="hover:text-primary">
        <router-link :to="{ name: 'AdminCoursDetails', params: { id: coursId }}">
            <InfosCours size="18"/>
        </router-link>
    </button>
    <button class="hover:text-primary" v-if="statusCours.libelle === 'En création'" @click="emit('deleteCreation')">
        <DeleteCours size="18"/>
    </button>
    <button class="hover:text-primary" v-if="statusCours.libelle === 'En création'" @click="emit('updateCreation')">
        <EditCours size="18"/>
    </button>
    <button class="hover:text-primary" v-if="(statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet')" @click="emit('cancelCours')">
        <CancelCours size="18"/>
    </button>
    <button class="hover:text-primary" v-if="statusCours.libelle === 'En création'" @click="emit('openCreation')">
        <LaunchCours size="18"/>
    </button>

  <ModalAddExtra
      v-if="statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet'"
      v-model:isOpen="addExtraDialog"
      title="Ajouter un extra"
      message="Sélectionner l'extra à ajouter."
      :cours="coursId"
      @subscriptionResponse="(data) => emit('handleAddExtraResponse', data)"
  >
      <button class="hover:text-primary flex items-center justify-center">
          <AddExtraUser size="18"/>
      </button>
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
