<script setup>

import Title_banner from "../Title_banner.vue";
</script>

<template>
  <div class="banner">
    <Title_banner/>
  </div>
  <div class="kspInfos bg-white">
    <div class="image">
      <img src="../../../images/banner2.jpg" alt="salle de yoga">
    </div>
    <div class="accroche">
      <h4>Soulagez vos douleurs et améliorez votre quotidien grâce à Kiné Sport Santé</h4>
      <p>Des méthodes simples pour adopter de bonnes habitudes corporelles et prévenir les récidives.</p>
    </div>
  </div>
</template>

<style scoped lang="scss">

.banner {
  background: linear-gradient(#260959, #472371);
  width: 100%;
  height: 70vh;
  object-fit: cover;
  /*display: flex;
  justify-content: space-between;*/
  position: absolute;
  top: 0;
  right: 0;
}

.kspInfos {
  position: relative;
  margin-top: 40vh;
  margin-bottom: 150px;
  left: 50%;
  transform: translateX(-50%);
  width: 90%;
  z-index: 10;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50vh;

  .image {
    width: 50%;
    height: 100%;

    img {
      height: 100%;
      width: 100%;
      object-fit: cover;
    }
  }

  .accroche {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 10%;
    align-items: center;
    width: 50%;
    height: 100%;
    padding: 10px 5vh;
    letter-spacing: .5px;
    line-height: 2;
    font-weight: 400;
    color: #515151;
    text-align: center;

    h4 {
      font-size: 1.5vw;
      font-weight: 900;
    }

    p {
      font-size: 1vw;
    }
  }
}

</style>