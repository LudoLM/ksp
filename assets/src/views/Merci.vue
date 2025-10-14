<script setup>

import {onMounted} from "vue";
import {useUserStore} from "../store/user";
import Banner from "../components/Banner.vue";
import {apiFetch} from "../utils/useFetchInterceptor";
import {storeToRefs} from "pinia";
import {alertStore} from "../store/alert";

const userStore = useUserStore();
const {userNombreCours} = storeToRefs(userStore);

const handleStripePayment = async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const checkoutId = urlParams.get('checkoutId');

  try {
    const response = await apiFetch(`/api/merci/` + checkoutId, {
      method: "POST",
    });

    const data = await response.json();
    if (response.ok) {
        userNombreCours.value = data.userQuantity;
        alertStore.setAlert(data.message, "success");
    } else {
      alertStore.setAlert(data.message, "error");
    }
  } catch (error) {
      alertStore.setAlert(error.message, "error");
  }
};


onMounted(async () => {
  await handleStripePayment();
});

</script>


<template>
    <Banner
        title="Merci pour votre achat"
        :backgroundColor="'rgba(30, 27, 75, .9)'"
    />
  <div class="m-20">
    <p>Vous avez maintenant {{ userNombreCours }} cours disponible{{ userNombreCours > 1 ? "s" : "" }}.</p>
  </div>
</template>

<style scoped lang="scss">

</style>
