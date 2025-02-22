<template>
  <div>
      <Banner
          title="Infos pratiques"
          :backgroundColor="'rgba(30, 27, 75, .9)'"
          :image="bannerImage"
      />
  </div>
</template>

<script setup>
import Banner from "../components/Banner.vue";
import bannerImage from "../../images/imageBanner14.jpg";
</script>
