<script setup>

</script>

<template>
  <div>
    <h1>Kiné Sport Santé</h1>
    <p>3 route de Rennes - Mordelles</p>
  </div>

</template>

<style scoped lang="scss">
  div {
    position: absolute;
    top: 25vh;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  @media (max-width: 768px) {
    div {
      left: 50%;
      transform: translateX(-50%);
    }
  }
</style>