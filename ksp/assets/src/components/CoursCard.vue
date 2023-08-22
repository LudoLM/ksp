<template>
  <div class="coursCard_Wrapper">
    <div class="coursCard">
      <div class="card_infos">
        <div class="card_title">
          <h3>{{ info.TypeCours.libelle }}</h3>
        </div>
        <div class="card_dateDebut">
          Le {{ info.dateCours }}
        </div>
        <div>
          Durée: {{ info.duree }} mns
        </div>
        <div class="card_ville">
          À : {{ info.description }}
        </div>
      </div>
      <div class="sortieCardBack">
        <div class="card_infos_back">


          <div class="buttons">
          </div>
        </div>
        </div>

    </div>
  </div>

</template>

<script>
export default {
  name: "CoursCard",
  props: {
    info: {
      type: Object,
      required: true,
    }
  }
}
</script>

<style lang="scss" scoped>

$mainColor: #A289CC;

.coursCard_Wrapper{
  width: 200px;
  height: 300px;
  position: relative;
}

.coursCard {
  width: 100%;
  height: 100%;
  background-image: radial-gradient(circle at 28% 51%, rgba(206, 206, 206,0.03) 0%, rgba(206, 206, 206,0.03) 17%,transparent 17%, transparent 100%),radial-gradient(circle at 45% 10%, rgba(10, 10, 10,0.03) 0%, rgba(10, 10, 10,0.03) 45%,transparent 45%, transparent 100%),radial-gradient(circle at 48% 44%, rgba(74, 74, 74,0.03) 0%, rgba(74, 74, 74,0.03) 84%,transparent 84%, transparent 100%),radial-gradient(circle at 47% 50%, rgba(186, 186, 186,0.03) 0%, rgba(186, 186, 186,0.03) 23%,transparent 23%, transparent 100%),radial-gradient(circle at 29% 70%, rgba(9, 9, 9,0.03) 0%, rgba(9, 9, 9,0.03) 32%,transparent 32%, transparent 100%),radial-gradient(circle at 2% 75%, rgba(179, 179, 179,0.03) 0%, rgba(179, 179, 179,0.03) 19%,transparent 19%, transparent 100%),radial-gradient(circle at 2% 36%, rgba(26, 26, 26,0.03) 0%, rgba(26, 26, 26,0.03) 1%,transparent 1%, transparent 100%),radial-gradient(circle at 53% 70%, rgba(90, 90, 90,0.03) 0%, rgba(90, 90, 90,0.03) 55%,transparent 55%, transparent 100%),radial-gradient(circle at 28% 92%, rgba(31, 31, 31,0.03) 0%, rgba(31, 31, 31,0.03) 35%,transparent 35%, transparent 100%),linear-gradient(90deg, rgb(255,255,255),rgb(255,255,255));
  background-size: 100%;
  transition: all .1s ease-in-out;
  position: relative;
  overflow: hidden;
  font-family: poppins;
}

.coursCard::after{
  content: "";
  position: absolute;
  width: 150px;
  height: 150px;
  background: $mainColor;
  border-radius: 50%;
  top: -20%;
  right: -30%;
  transition: all .3s ease-in-out;
  opacity: .8
}

.coursCard_Wrapper .coursCard:hover{
  background-size: 150%;
}

.coursCard:hover::after{
  width: 500px;
  height: 500px;
  top: -150%;
  right: -30%;
}


.card_infos{
  position: absolute;
  width: 100%;
  height: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  transition: all .3s ease-in-out;
  z-index: 1000;
}

.sortieCard_Wrapper:hover .card_infos{
  color: #fff;
  top: 30%;
}

.sortieCardBack{
  position: absolute;
  width: 100%;
  height: 100%;
  background: #000;
  bottom: -100%;
  opacity: .6;
  transition: all .3s ease-in-out;
}

.buttons{
  position: absolute;
  width: 100%;
  height: 200px;
  bottom: -100px;
  display: flex;
  justify-content: center;
  gap: 30px;
  align-items: center;
  transition: all .2s ease-in-out;
  transition-delay: .2s;
}

.sortieCard_Wrapper:hover .buttons{
  bottom: 0;
}

.buttons a{
  display: flex;
  justify-content: center;
  align-items: center;
  width: 35%;
  height: 50px;
  background: transparent;
  color: #fff;
  border: 2px solid #fff;
}

.card_infos_back{
  color: #fff;
}

.sortieCard_Wrapper:hover .sortieCardBack{
  bottom: 0;
}

</style>