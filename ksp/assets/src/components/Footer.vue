<template>
  <footer>
    <div class="wrapper">
      <div class="columns">
        <div class="footer_column">
          <h4>Kiné Sport Santé</h4>
          <div>{{ store.address }}</div>
          <div>{{ store.fullAddress }}</div>
          <br>
          <a href="https://maps.app.goo.gl/ApZ1E35srhDT2ynK7">
            <div class="flex gap-3">
              <img src="../../icons/pin.svg"/>
             Localiser
            </div>
          </a>
          <a :href="'tel:' + store.fullPhone">
            <div class="flex gap-3">
              <img src="../../icons/phone.svg"/>
              <div>{{ store.fullPhone}}</div>
            </div>
          </a>
        </div>
        <div class="footer_column">
          <h4>Suivez-moi</h4>
          <div class="social">
            <a href="">
              <img src="../../icons/facebook.svg"/>
            </a>
            <a href="">
              <img src="../../icons/instagram.svg"/>
            </a>
            <a :href="'mailto:' + store.fullMail">
                <img src="../../icons/mail.svg"/>
            </a>
          </div>
        </div>
        <div class="footer_column">
          <h4>A propos</h4>
        </div>
      </div>
      <div class="mentionsLegales">
        <div>© 2024 Kiné Sport Santé </div>
        <div>Tous droits réservés</div>
        <div>Réalisé par <a href="https://www.linkedin.com/in/llemelinaire/">Ludovic Le Mélinaire</a></div>
      </div>
    </div>
  </footer>
</template>

<script setup>
  import { infos } from "../store/index";
  const store = infos();
</script>

<style scoped lang="scss">
footer{
  background: linear-gradient(#f6f5f8, #fff);
  width: 100%;
  min-height: 400px;
  padding-bottom: 20px;
  margin-top: 250px;
}

a{
  text-decoration: none;
  color: #3c3c3c;

  &:hover{

    img{
      fill: red;
    }

  }
}


.wrapper{
  margin: 0 auto;
  padding: 20px;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;

  .columns{
    width: 80%;
    display: flex;
    justify-content: space-around;
    color:#3c3c3c;
    border-bottom: 2ps solid #3c3c3c;

    h4{
      font-size: 1rem;
      margin-bottom: 20px;
      font-weight: 900;
      font-style: italic;
    }
  }
}
.footer_column{
  width: 25%;
  padding: 0 3%;
  line-height: 2rem;
  li{
    margin-bottom: 5px;
  }
}

.links{
  display: flex;
  flex-direction: column;
}

.social{
  width: 100%;
  display: flex;
  justify-content: space-around;
  align-items: center;
  a{
    padding: 10px;
    border-radius: 50%;
    border: 2px solid #000;
    margin-right: 10px;
  }

}

.mentionsLegales{
  width: 80%;
  display: flex;
  justify-content: flex-end;
  gap: 20px;
  padding-top: 50px;
  margin-top: 50px;
  font-size: .8rem;
  color: #3c3c3c;
  border-top: #b3b2b2 1px solid;
}

</style>
