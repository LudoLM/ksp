<template>
  <main>
    <section><router-view/></section>
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
