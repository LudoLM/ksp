<template>
  <div>
    <CustomButton @click="sayHello">
      Hello
    </CustomButton>
  </div>
</template>
<script setup>
import CustomButton from "../components/CustomButton.vue";

const sayHello = () => {
  alert("Hello");
};
</script>