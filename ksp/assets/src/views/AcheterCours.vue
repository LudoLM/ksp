<script setup>

import CustomButton from "../components/CustomButton.vue";

const goingBack = () => {
  window.history.back();
};

</script>

<template>
  <h1 class="mb-20">packs à acheter</h1>
  <div class="w-full flex justify-between">
    <stripe-buy-button
        buy-button-id="buy_btn_1Q4N2pC6kEhASj1en2q72S6F"
        publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
    >
    </stripe-buy-button>
    <stripe-buy-button
        buy-button-id="buy_btn_1Q46sYC6kEhASj1eduGCytqN"
        publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
    >
    </stripe-buy-button>
    <stripe-buy-button
        buy-button-id="buy_btn_1Q4Mu7C6kEhASj1e5j6L1Tkm"
        publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
    >
    </stripe-buy-button>
  </div>
  <div class="flex justify-center my-10">
    <CustomButton @click="goingBack">Retour</CustomButton>
  </div>
</template>

<style scoped lang="scss">

</style>