<script setup>

import {inject, onMounted, ref} from "vue";
import {useUserStore} from "../store/user";
import Banner from "../components/Banner.vue";

const nbreCours = ref();
const alertStore = inject('alertStore');
const userStore = useUserStore();

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
  userStore.setUserNombreCours(nbreCours.value);
};

onMounted(async () => {
  await handleStripePayment();
  await getUser();
});

</script>


<template>
    <Banner
        title="Merci pour votre achat"
        :backgroundColor="'rgba(30, 27, 75, .9)'"
    />
  <div class="m-20">
    <p>Vous avez maintenant {{ nbreCours }} cours disponible{{ nbreCours > 1 ? "s" : "" }}.</p>
  </div>
</template>

<style scoped lang="scss">

</style>
