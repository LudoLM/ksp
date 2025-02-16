<script setup>

import CustomButton from "../CustomButton.vue";
import ModalAddExtra from "../modal/ModalAddExtra.vue";
import {ref} from "vue";

const props = defineProps({
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
  'handleAddExtraResponse']
)

</script>
<template>

  <CustomButton v-if="props.statusCours.libelle === 'En création'" @click="emit('openCreation')">
      {{props.statusCours.libelle}}
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
  </CustomButton>

  <ModalAddExtra
      v-if="props.statusCours.libelle === 'Ouvert' || props.statusCours.libelle === 'Complet'"
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
