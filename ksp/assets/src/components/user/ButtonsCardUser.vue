<script setup>

import CustomButton from "../CustomButton.vue";

defineProps({
  userId: {
    type: Number,
  },
  isSubscribed: {
    type: Boolean,
    required: true
  },
  isUserAttente: {
    type: Boolean,
    required: true
  },
  statusCours: {
    type: String,
    required: true
  }
})

const emit = defineEmits([
  'handleSubscription',
  'handleUnsubscription'
])
</script>

<template>
  <CustomButton v-if="userId && !isSubscribed && statusCours === 'Ouvert'" @click="emit('handleSubscription', false)">
    S'inscrire
  </CustomButton>
  <CustomButton v-if="userId && isSubscribed" @click="emit('handleUnsubscription', false)">
    Se désinscrire
  </CustomButton>
  <CustomButton v-if="userId && !isSubscribed && !isUserAttente && statusCours === 'Complet'" @click="emit('handleSubscription', true)">
    Liste d'attente
  </CustomButton>
  <CustomButton v-if="userId && isUserAttente && statusCours === 'Complet'" @click="emit('handleUnsubscription', true)">
    Retirer attente
  </CustomButton>
</template>

<style scoped lang="scss">

</style>
