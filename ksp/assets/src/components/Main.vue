<template>
  <main>
    <section><router-view/></section>
  </main>
</template>

<script>
export default {
  name: "Mymain",
}
</script>

<style lang="scss">

$mainColor: #A289CC;

main{
  display: flex;
  justify-content: center;
  flex-grow: 1;
  margin-top: 123px;

  section{
    flex-grow: 1;
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
