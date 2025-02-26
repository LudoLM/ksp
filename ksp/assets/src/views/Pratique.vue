<template>
    <div>
        <Banner
            title="Infos pratiques"
            :backgroundColor="'rgba(30, 27, 75, .9)'"
            :image="bannerImage"
        />
    </div>
    <div class="mt-20">
        <div class="w-full flex flex-col items-center">
            <div class="coursInfo">
                <div class="descriptifWrapper">
                    <div class="descriptif mx-10" >
                        <h3>De quoi ai je besoin?</h3>
                        <ul>
                            <li><strong>Tapis de Pilates</strong> : Un tapis antidérapant et confortable pour les exercices au sol.</li>
                            <li><strong>Bouteille d’eau</strong> : Restez hydraté pendant la séance.</li>
                            <li><strong>Serviette</strong> : Pour éponger la transpiration ou ajouter du confort sur le tapis.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full flex flex-col items-center">
            <div class="coursInfo">
                <div class="descriptifWrapper">
                    <div class="descriptif mx-10" >
                        <h3>Comment me joindre?</h3>
                        <p>Si vous avez des questions, n’hésitez pas à me contacter :</p>
                        <ul class="mt-5">
                            <li><a :href="'tel:' + store.fullPhone"><strong>Téléphone</strong> : {{ store.fullPhone }}</a></li>
                            <li><a :href="'mailto:' + store.fullMail"><strong>Email</strong> : {{ store.fullMail }}</a></li>
                            <li><strong>Réseaux sociaux</strong> :
                                <ul class="ml-5 list-disc">
                                    <li><a href="https://www.facebook.com/kinesportsante35">Facebook</a></li>
                                    <li><a href="https://instagram.com/pilates">Instagram</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full flex flex-col items-center">
            <div class="coursInfo">
                <div class="descriptifWrapper flex">
                    <div class="descriptif mx-10" >
                        <h3>Comment m'y rendre et où stationner?</h3>
                        <p>Le centre se trouve au <strong><span>{{ store.address }}</span> à <span>{{ store.fullAddress }}</span></strong>.
                        </p>
                        <br>
                        <p>La salle de Pilates est située au <strong>1er étage</strong> du bâtiment. Pour votre commodité, un <strong>parking gratuit</strong> est disponible juste à côté du bâtiment. Vous pourrez y stationner facilement avant votre séance.</p>
                        <p>Si le parking principal est complet, des places supplémentaires sont souvent disponibles à proximité. Veuillez arriver quelques minutes à l’avance pour trouver une place sans stress.</p>
                    </div>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2665.59563926403!2d-1.8350955!3d48.0794475!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x480f1e080cf37c1b%3A0x118cdf1130af9e13!2zS2luw6kgU3BvcnRzIFNhbnTDqQ!5e0!3m2!1sfr!2sfr!4v1740520589011!5m2!1sfr!2sfr" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Banner from "../components/Banner.vue";
import bannerImage from "../../images/imageBanner14.jpg";
import { infos } from "../store/index";

const store = infos();

</script>

<style scoped>
h3{
    font-size: clamp(1.5rem, 3.5vw, 2.5rem);
    color: #472371;
}

.coursInfo{
    background-color: #fff;
    margin: 20px 10vw;
    width: 80%;
    max-width: 1250px;
}
.descriptifWrapper{
    display: flex;
    justify-content: space-between;
    width: 100%;
    padding: 20px;

    @media(max-width: 1000px){
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
}
.descriptif{
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-around;


    iframe{
        width:100%;
    }
}
</style>
