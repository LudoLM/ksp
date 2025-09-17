<template>
    <Banner
        title="Kiné Sport Santé"
        :image="bannerImage"
        :backgroundColor="'rgba(71,35,113,.3)'"
        alt="Salle de sport moderne avec des tapis d'exercice au sol et une grande baie vitrée offrant une vue lumineuse"
    />
    <div>
        <div class="centerPart">
            <div class="centerWrapper flex justify-center items-center">
                <div class="image">
                    <img
                        src="../../images/banners/banner2.jpg"
                        alt="Ballon de gym dans une salle au décor minimaliste, avec lumière tamisée et ambiance zen"
                    >
                </div>
                <div class="text">
                    <div class="flex flex-col justify-center items-end mr-2 mb-6">
                        <h3>Le centre</h3>
                        <span class="flex gap-2">
                            <h6>{{ store.address }} {{ store.fullAddress }}</h6>
                            <a href="https://maps.app.goo.gl/ApZ1E35srhDT2ynK7">
                                <div class="pinWrapper">
                                    <img src="../../icons/pin.svg" alt=""/>
                                </div>
                            </a>
                        </span>

                    </div>

                    <p>Aujourd’hui, douleurs et fatigue physique font malheureusement partie de notre quotidien : mal de dos, raideur articulaire, tensions musculaires... Comme pour beaucoup de problème de santé, notre mode de vie (sédentarisme, alimentation, mauvais sommeil, stress,…) est en lien direct avec ces souffrances quotidiennes.</p>
                    <p>Nous vous proposons donc des METHODES SIMPLES ET EFFICACES pour vous aider à changer les habitudes : le SPORT SANTE ! Ces cours vous permettront de comprendre et de soulager vos douleurs.</p>
                    <p>Vous y apprendrez les règles de bon fonctionnement de l’organisme : schéma corporel, posture quotidienne, mobilité articulaire, renforcement musculaire, respiration, coordination, équilibre, étirements, gestes et posture et détente… Ces séances de gymnastique ont également un but préventif et vous permettront d’empêcher les récidives.</p>
                </div>
            </div>
        </div>
        <div class="coachPart">
            <div class="coachWrapper flex justify-between items-center w-full">
                <div class="text">
                    <div class="flex flex-col justify-center items-start mb-6">
                        <h3>La coach</h3>
                        <h6 class="font-bold">{{ store.fullName }} - Masseur-Kinésithérapeute DE</h6>
                    </div>
                    <div class="image image_mobile">
                        <div class="w-1/2 flex justify-center items-center">
                            <img
                                class="rounded-full" src="../../images/servane.jpg"
                                alt="Portrait de la professeure souriante" />
                        </div>
                    </div>
                    <p>"En choisissant le métier de masseur kinésithérapeute, je voulais aider et soigner les gens, tout en gardant comme objectif de pouvoir me rapprocher du milieu sportif. Avec mes spécialisations en rééducation du dos, des abdominaux et du périnée, et plus récemment mes formations en sport santé, je vous propose des cours de gym adaptés à vos besoins."</p>
                </div>
                <div class="image image_desktop">
                    <div class="w-1/2">
                        <img class="rounded-full" src="../../images/servane.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="assetsPart">
            <div class="w-full">
                <div class="text">
                    <div class="flex flex-col justify-center items-start h-full">
                        <div class="flex flex-col justify-center items-start mb-6">
                            <h3>Les atouts</h3>
                        </div>
                        <ul class="flex flex-col justify-lg-space-around gap-8 w-full mt-10">
                            <li class="flex items-center">Encadrement par un professionnel de santé</li>
                            <li class="flex items-center">Cours en petits groupes (10 personnes max)</li>
                            <li class="flex items-center"><div class="w-3/4">Bilan sport santé possible</div><button><a target="_blank" href="https://www.doctolib.fr/masseur-kinesitherapeute/mordelles/servane-cosqueric-mordelles/booking/availabilities?specialityId=9&telehealth=false&placeId=practice-567335&motiveCategoryIds%5B%5D=338126&motiveIds%5B%5D=11667176&bookingFunnelSource=profile">Prendre RDV sur Doctolib</a></button></li>
                            <li class="flex items-center"><div class="w-3/4">Séance de coaching individuel proposé</div><button><a target="_blank" href="https://www.doctolib.fr/masseur-kinesitherapeute/mordelles/servane-cosqueric-mordelles/booking/availabilities?specialityId=9&telehealth=false&placeId=practice-567335&motiveCategoryIds%5B%5D=338126&motiveIds%5B%5D=11667177&bookingFunnelSource=profile">Prendre RDV sur Doctolib</a></button></li>
                            <li class="flex items-center">Exercices et conseils personnalisés</li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>


    </div>
</template>

<script setup>
import { infos } from "../store/index";
import bannerImage from "../../images/banners/banner4.jpg";
import Banner from "../components/Banner.vue";
import {useRoute} from "vue-router";
import {createAlertStore} from "../store/alert";
import {inject} from "vue";
const store = infos();
const route = useRoute();

if (route.query.alert === 'logout') {
    const alertStore = inject('alertStore');
    alertStore.setAlert("Vous êtes déconnecté(e).", "info");
}

</script>


<style scoped lang="scss">

.text{
    width: 50%;
    height: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: start;
    gap: 2vh;



    h3{
        font-size: clamp(1.5rem, 3.5vw, 2.5rem);
        color:#fff;
        text-align: right;
    }

    h6{
        font-size: clamp(0.6rem, 1.2vw, .8rem);
        color: #e2a945;
        text-align: right;
    }

    .pinWrapper{
        display: flex;
        justify-content: center;
        align-items: center;
        background: #e2a945;
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }

    p{
        font-size: clamp(0.8rem, 1.5vw, 1rem);
        color: #aba9a9;
    }
}


.centerPart, .coachPart, .assetsPart {
    width: 100%;
    background: #27272A;
    padding: 6vw;
    display: flex;
}

.image{
    width: 50%;
    height: 100%;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;

    img{
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
}


.coachPart{
    background: transparent;

    h3{
        color: #472371;
    }
    p{
        color: #27272A;
    }

    .image{
        max-width: 600px;
        width: 70%;
        height: 50%;
    }

    .image_mobile{
        display: none;
    }
}

.assetsPart{
    background: #fff;
    color: #2e2e2e;

    h3{
        color: #27272A;
    }

    li{
        padding-left: 20px;
        border-left: 2px solid #e2a945;
        height: 40px;
        color: #7e7e7e;
        font-size: clamp(0.8rem, 1.5vw, 1rem);
    }
    button{
        background: #107aca;
        font-size: clamp(0.6rem, 1vw, .8rem);
        padding: 10px;
        border-radius: 5px;
        color: #F3F4F6;
        margin-left: 2vw;

        &:hover{
            background: #43aaec;
        }
    }
}

@media (max-width: 760px) {

    .text{
        width: 100%;
        padding: 20px;
    }

    .centerWrapper{
        display: flex;

        .image {
            display: none;
        }
    }

    .coachWrapper {
        display: block;
        flex-direction: column;

        .image_desktop {
            display: none;
        }

        .image_mobile {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;

            img {
                width: 100%;
                height: 50%;
                object-fit: cover;
            }
        }
    }
}
</style>


