<template>
  <main>
    <section><router-view/></section>
    <aside>
      <div id="recherche" class="blocAside">
        <div>Recherche</div>
        <form action="">
          <input type="text" id="recherche" >
          <button type="submit">OK</button>
        </form>
      </div>
      <div id="infosPratiques" class="blocAside">
        <div class="titleColumn">Infos Pratiques</div>
        <p>Les cours de déroulent à : </p><br>
        <b>{{ store.address}}</b>
        <b>{{ store.fullAddress}}</b>
        <p>Votre coach <br>
          <span>{{ store.fullName }}</span><br>
          <i class="fas fa-phone-alt"></i><b>{{ store.fullPhone}}</b><br>
          <a :href="`mailto:` + store.fullMail ">{{ store.fullMail }}</a></p>
        <button>RESERVEZ VOS COURS</button>
      </div>
      <div id="atouts" class="blocAside">
        <div class="titleColumn">Nos atouts</div>
        <Myasset>
          <slot><p>Vous êtes encadré par un <strong>professionnel de santé</strong>, pour des exercices et des conseils personnalisés.</p></slot>
        </Myasset>
        <Myasset>
          <slot><p>Cours en <strong>petit groupe</strong> (10 personnes max).</p></slot>
        </Myasset>
        <Myasset>
          <slot><p><strong>Suivi personnalisé</strong> avec un bilan individuel avant et après le cycle de cours.</p></slot>
        </Myasset>
        <Myasset>
          <slot><p>Apport de données théoriques et <strong>supports envoyés par mail</strong> après les séances.</p></slot>
        </Myasset>

      </div>
    </aside>
  </main>
</template>

<script setup>
import {infos} from "../store/index";
const store = infos();
</script>
<script>

import Myasset from "./Atout";

export default {

  name: "Mymain",
  components:{
    Myasset
  },

}
</script>

<style lang="scss">

$widthContent : 80%;
$mainColor: #A289CC;

main{
  width: $widthContent;
  display: flex;
  justify-content: center;
  margin: 40px auto;
  flex-grow: 1;

  section{
    flex-grow: 1;
    margin-left: 20px;
  }

  aside{
    width: 200px;
    border-left: 1px solid #ddd;
    padding-left: 20px;

    .blocAside{
      margin: 0 0 30px;
    }

    a, span{
      color: $mainColor;
      font-weight: 900;
    }
  }

  #recherche{
    display: flex;
    flex-direction: column;

    form{
      display: flex;

      input{
        margin-right: 10px;
        border-top: none;
        border-left: none;
        border-right: none;
        border-bottom: 1px solid rgba(167, 167, 167, 0.5);
      }
    }
  }

  .titleColumn{
    text-transform: uppercase;
    font-weight: 900;
    font-size: 22px;
  }
  strong{
    color: $mainColor;
  }
}


</style>
