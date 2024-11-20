<script setup>

import {onMounted, ref} from "vue";
import {VAlert} from "vuetify/components";

const nbreCours = ref();

const handleStripePayment = async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const checkoutId = urlParams.get('checkoutId');

  try {
    const response = await fetch(`/api/merci/` + checkoutId, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.getItem('token')}`,
      },
    });

    const data = await response.json();

    if (response.ok) {
      alertMessage.value = data.message;
      alertType.value = 'success';
    } else {
      alertMessage.value = data.message;
      alertType.value = 'error';
    }
    alertVisible.value = true;

    setTimeout(() => {
      alertVisible.value = false;
    }, 3000);
  } catch (error) {
    alertMessage.value = error.message;
    alertType.value = 'error';
    alertVisible.value = true;

    setTimeout(() => {
      alertVisible.value = false;
    }, 3000);
  }
};

const alertVisible = ref(false);
const alertType = ref('success');
const alertMessage = ref('');

const getUser = async () => {
  const response = await fetch("/api/user", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
  });

  const userData = await response.json();
  nbreCours.value = userData.nombreCours;
};

onMounted(async () => {
  await handleStripePayment();
  await getUser();
});

</script>


<template>
  <v-alert v-model="alertVisible" :type="alertType" dismissible>
    {{ alertMessage }}
  </v-alert>
  <div>
    <h1>Merci</h1>
    <p>Je vous remercie pour l'achat du pack, vous avez maintenant {{ nbreCours }} cours disponible{{ nbreCours > 1 ? "s" : "" }}.</p>
  </div>
</template>

<style scoped lang="scss">

</style>