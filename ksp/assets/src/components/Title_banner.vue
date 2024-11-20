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
    bottom: 70px;
    left: 50px;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    h1 {
      border-bottom: 2px solid white;
      font-size: 5vw;
    }

    p {
      font-size: 1.7vw;
      font-weight: 100;
      margin-top: 10px;
      letter-spacing: 1px;
    }
  }

  @media (max-width: 768px) {
    div {
      left: 50%;
      transform: translateX(-50%);
    }
  }
</style>