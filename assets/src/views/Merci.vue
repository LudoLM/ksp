<script setup>

import {inject, onMounted, ref} from "vue";
import {VAlert} from "vuetify/components";

const nbreCours = ref();
const alertStore = inject('alertStore');

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
      alertStore.setAlert(data.message, "success");
    } else {
      alertStore.setAlert(data.message, "error");
    }
  } catch (error) {
      alertStore.setAlert(error.message, "error");
  }
};


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
  <div>
    <h1>Merci</h1>
    <p>Je vous remercie pour l'achat du pack, vous avez maintenant {{ nbreCours }} cours disponible{{ nbreCours > 1 ? "s" : "" }}.</p>
  </div>
</template>

<style scoped lang="scss">

</style>
