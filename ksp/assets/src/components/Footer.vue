<template>
  <footer>
    <div class="box_columns">
      <div class="footer_column">
        <h4>Contact</h4>
        <div>{{ store.fullName }}</div>
        <b>{{ store.address }}</b><br>
        <b>{{ store.fullAddress }}</b><br>
        <i class="fas fa-phone-alt"></i><b>{{ store.fullPhone}}</b><br>
        <a :href="`mailto:` + store.fullMail ">{{ store.fullMail }}</a>
      </div>
      <div class="footer_column">
        <h4>Suivez-moi</h4>
        <div class="social">
          <i class="fab fa-facebook"></i>
          <i class="fab fa-instagram"></i>
          <i class="fab fa-twitter"></i>
        </div>
      </div>
      <div class="footer_column">
        <h4>A propos</h4>
      </div>
    </div>
  </footer>
</template>

<script setup>
  import { infos } from "../store/index"; // Importation du store
  const store = infos(); // Utilisation du store
</script>
