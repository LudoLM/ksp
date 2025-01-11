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


main{
  display: flex;
  justify-content: center;
  flex-grow: 1;
  margin-top: 123px;
}


</style>
