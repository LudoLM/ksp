<script setup>

import bannerImage from "../../images/banners/imageBanner17.jpg";
import Banner from "../components/Banner.vue";

</script>

<template>
    <Banner
        title="Les packs disponibles"
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />

    <div class="modalitesWrapper flex justify-center gap-5 m-20 bg-white">
        <div class="image w-1/3">
            <img src="../../images/banners/banner.jpg">
        </div>
        <div class="infos p-6 w-2/3 mb-10">
            <div class="w-full mb-10">
                <h3 class="text-right">Les modalités</h3>
            </div>
            <p class="mb-4">
                L'année comprend 36 semaines de cours (hors vacances scolaires) et s'étend du <strong>02/09/2024</strong> au <strong>05/07/2025</strong>.
            </p>
            <p class="mb-4">
                Chaque carte ou abonnement est valable uniquement sur l'année en cours (<em>année scolaire</em>).
            </p>
            <p class="mb-4">
                Les abonnés annuels sont prioritaires lors des inscriptions en ligne.
            </p>

            <ul class="list-disc pl-6 space-y-2">
                <li>Possibilité de faire un cours d'essai gratuit.</li>
                <li>Possibilité de faire un bilan kiné sport santé individuel avant de démarrer les cours.</li>
                <li>Inscriptions possibles tout au long de l'année (sous réserve de places disponibles).</li>
                <li>Réservations en ligne obligatoires pour participer aux cours.</li>
                <li>En cas de désistement, annulation gratuite au plus tard <strong>12h à l'avance</strong>.</li>
                <li>
                    Les inscriptions se font <strong>un trimestre à l'avance</strong> et sont limitées à <strong>1 cours/semaine</strong>.
                </li>
                <li>Un minimum de <strong>4 personnes</strong> est requis pour que le cours puisse avoir lieu.</li>
                <li>
                    Le matériel pour les cours est fourni, vous devez juste prévoir une serviette et votre bouteille d'eau si besoin !
                </li>
            </ul>
        </div>
    </div>

    <div class="offres">
        <div class="w-full mb-10">
            <h3 class="text-left">Les offres</h3>
        </div>
        <div class="card flex justify-between items-center bg-gray-50 p-5">
            <div class="title w-2/3">
                <h6>Séance à l'unité</h6>
            </div>
            <p class="text-lg mr-5">17€</p>
            <stripe-buy-button
            buy-button-id="buy_btn_1Q4N2pC6kEhASj1en2q72S6F"
            publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
            >
            </stripe-buy-button>
        </div>
        <div class="card flex justify-between items-center w-2/3 bg-gray-50 p-5">
            <div class="title w-2/3">
                <h6>Carte de 5 séances</h6>
                <p>Renouvelable</p>
            </div>
            <p class="text-lg mr-5">80€</p>
            <stripe-buy-button
                buy-button-id="buy_btn_1Q46sYC6kEhASj1eduGCytqN"
                publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
            >
            </stripe-buy-button>
        </div>
        <div class="card flex justify-between items-center w-2/3 bg-gray-50 p-5">
            <div class="title w-2/3">
                <h6>Carte de 10 séances</h6>
                <p>Renouvelable</p>
            </div>
           <p class="text-lg mr-5">148€</p>
           <stripe-buy-button
              buy-button-id="buy_btn_1Q4Mu7C6kEhASj1e5j6L1Tkm"
              publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
           >
           </stripe-buy-button>
      </div>
      <div class="card flex justify-between items-center w-2/3 bg-gray-50 p-5">
          <div class="title w-2/3">
              <h6>Forfait annuel 36 cours</h6>
              <p>Prioritaire</p>
          </div>
          <p class="text-lg mr-5">440€</p>
          <stripe-buy-button
              buy-button-id="buy_btn_1Q4N2pC6kEhASj1en2q72S6F"
              publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
          >
          </stripe-buy-button>
      </div>
      <div class="card flex justify-between items-center w-2/3 bg-gray-50 p-5">
          <div class="title w-2/3">
              <h6>Forfait annuel 72 cours</h6>
              <p>Prioritaire</p>
          </div>
          <p class="text-lg mr-5">525€</p>
          <stripe-buy-button
              buy-button-id="buy_btn_1Q4N2pC6kEhASj1en2q72S6F"
              publishable-key="pk_test_51Q45O6C6kEhASj1e49iRAPuqTqdAIDafacfenfCYOQcF9vYhHJAL6CP9BsnAmLo975maHjDsZJloJX1okVSMMDX200uKVPVN6r"
          >
          </stripe-buy-button>
      </div>
  </div>
</template>

<style scoped lang="scss">
    .image{

        img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    .modalitesWrapper{
        display: flex;
        flex-direction: row;
        gap: 20px;
        margin: 5vw;
        background-color: #fff;

        .infos{
            padding: 5vw;
        }
    }

    .offres{
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        margin: 5vw;
        padding: 5vw;

        background-color: #fff;
    }

    .card{
        width: 100%;
    }

    h3{
      font-size: clamp(1.5rem, 3.5vw, 2.5rem);
      color: rgb(85, 19, 96);
    }

    @media (max-width: 980px) {
        .modalitesWrapper{
            flex-direction: column;
            gap: 10px;

            img {
                width: 100%;
                height: 300px;
                object-fit: cover;
            }

            .image, .infos {
                width: 100%;
            }
        }

        .card, .infos {
            margin: 10px;
            flex-direction: column;
            gap: 10px;
        }

        .title {
            text-align: center;
            p{
                font-size: clamp(0.8rem, 1vw, 1rem);
                color: rgba(0, 0, 0, 0.6);
            }
        }
    }

</style>
