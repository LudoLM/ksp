<template>
    <div>
        <Banner
            title="Découvrez Nos Cours"
            :backgroundColor="'rgba(30, 27, 75, .9)'"
            :image="bannerImage"
        />
        <!--            :backgroundColor="'rgba(30, 41, 59, .9)'"-->
        <div class="w-full flex flex-col items-center mt-20">
            <div v-for="cours in coursDescriptions" :key="cours.id" class="coursInfo ">
                <div class="descriptifWrapper">
                    <img class="imageDesktop" :src="require(`../../images/uploads/${cours.thumbnail}`)" alt="">
                    <div class="w-2/3 descriptif mx-10" >
                        <h3 class="">{{ cours.libelle }}</h3>
                        <img class="imageMobile w-1/3" :src="require(`../../images/uploads/${cours.thumbnail}`)" alt="">
                        <p class="flex items-center grow text-justify">{{ cours.descriptif }}</p>
                        <div class="flex justify-end">
                            <CustomButton
                                class="flex justify-end mt-10"
                                @click="handleRedirection(cours)">Prochains cours</CustomButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {onMounted, ref} from 'vue';
import {useGetTypesCours} from "../utils/useActionCours";
import CustomButton from "../components/forms/CustomButton.vue";
import {useRouter} from "vue-router";
import Banner from "../components/Banner.vue";
import bannerImage from "../../images/banners/imageBanner15.jpg";
import {useCalendarStore} from "../store/calendar";

const coursDescriptions = ref([]);
const router = useRouter();
const calendarStore = useCalendarStore();

onMounted(async () => {
    coursDescriptions.value = await useGetTypesCours();
});


const handleRedirection = (cours) => {
    calendarStore.$reset();
    router.push({ name: 'Calendrier', query: { typeCoursId: cours.id, isOpenRequired: true } });
}

</script>

<style scoped>

    .coursInfo{
        background-color: #fff;
        margin: 20px 10vw;
        max-width: 1250px;

        .descriptifWrapper{
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding: 20px;
        }

        .descriptif{
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }

        .imageDesktop{
            width: 33%;
            object-fit: cover;
        }

        .imageMobile{
            display: none;
        }
    }

    p {
        color: rgba(0, 0, 0, 0.6);
    }

    @media (max-width: 980px) {
        .descriptifWrapper{
            position: relative;

            .descriptif{
                width: 100%;
                margin: 5px;
            }

            .imageDesktop{
                display: none;
            }
            .imageMobile{
                display: block;
                width: 100%;
                object-fit: cover;
                margin-bottom: 10px;
            }

        }
    }

</style>
