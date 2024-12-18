<script setup>

import CustomButton from "../CustomButton.vue";
import ModalAddExtra from "../modal/ModalAddExtra.vue";
import {ref} from "vue";

defineProps({
  statusCours:{
    type: String,
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
  'handleAddExtraResponse']
)

</script>
<template>

  <CustomButton v-if="statusCours === 'En création'" @click="emit('openCreation')">
    Ouvrir
  </CustomButton>
  <CustomButton v-if="statusCours === 'En création'" @click="emit('updateCreation')">
    Modifier
  </CustomButton>
  <CustomButton v-if="statusCours === 'En création'" @click="emit('deleteCreation')" :color="'red'">
    Supprimer
  </CustomButton>
  <CustomButton v-if="(statusCours === 'Ouvert' || statusCours === 'Complet')" @click="emit('cancelCours')">
    Annuler
  </CustomButton>

  <ModalAddExtra
      v-if="statusCours === 'Ouvert' || statusCours === 'Complet'"
      v-model:isOpen="addExtraDialog"
      title="Ajouter un extra"
      message="Sélectionner l'extra à ajouter."
      :cours="coursId"
      @subscriptionResponse="(data) => emit('handleAddExtraResponse', data)"
  >
    Ajouter extra
  </ModalAddExtra>


</template>

<style scoped lang="scss">

</style>
