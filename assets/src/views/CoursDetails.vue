<script setup>
import {ref, computed, onMounted } from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {useDateFormat} from '@vueuse/core';
import {useGetCoursById} from "../utils/useActionCours";
import CustomButton from "../components/forms/CustomButton.vue";
import ButtonsCardUser from "../components/user/ButtonsCardUser.vue";
import StatusCoursTag from "../components/StatusCoursTag.vue";
import ModalConnect from "../components/modals/ModalConnect.vue";
import ModalUnsubscribeUsers from "../components/modals/ModalUnsubscribeUsers.vue";
import {useUserStore} from "../store/user";
import {getImageUrl} from "../utils/useAssetHelper.js";

const route = useRoute();
const router = useRouter();
const coursId = route.params.id;
const cours = ref(null);
const isAdminPath = route.path.startsWith('/admin');
const userStore = useUserStore();
const { isAuthenticated, userId } = userStore;

// Recupoerer la liste des participants
const usersSubscribed = ref([]);
// Recuperer la liste des participants en attente
const usersOnStandby = ref([]);
const loginDialog = ref(false);
const UnsubscribeUsersDialog = ref(false);
const usersCount = ref(0);
const dateLimit = ref(null);
const isSubscribed = ref(false);
const isUserOnWaitingList = ref(false);
const dateStart = computed(() => new Date(cours.value?.dateCours));
const formattedDate = computed(() => useDateFormat(dateStart.value, 'DD/MM/YYYY').value);
const formattedHour = computed(() => {
    const hours = dateStart.value.getHours();
    const minutes = String(dateStart.value.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
});

const redirectToLogin = () => {
    router.push({ name: 'Login' });
};

const stepBack = () => {
    router.push({ name: 'Calendrier' });
};

const coursDetails = async () => {
  const result = await useGetCoursById(coursId);
  cours.value = JSON.parse(result);
  dateLimit.value = getDateLimit(cours.value.launchedAt);
  usersCount.value = cours.value.usersCours.filter(userCours => userCours.isOnWaitingList === false).length;
  usersSubscribed.value = cours.value.usersCours.filter(userCours => userCours.isOnWaitingList === false);
  usersOnStandby.value = cours.value.usersCours.filter(userCours => userCours.isOnWaitingList === true);
}


onMounted( async () => {
   await coursDetails();
   isSubscribed.value = cours.value.usersCours.some(usersCours => usersCours.user.id === userId && !usersCours.isOnWaitingList);
});

const getDateLimit = (launchedAt) => {
    const date = new Date(launchedAt);
    date.setDate(date.getDate() + 7); // Ajoute 7 jours
    return date > new Date() ? useDateFormat(date, 'DD/MM/YYYY').value : null;
};


const handleUpdateUnsubscribeUsersValue = ({ statusCoursValue, usersSubscribedValue }) => {
    cours.value.statusCours = statusCoursValue;
    usersSubscribed.value = usersSubscribedValue;
    usersCount.value = usersSubscribed.value.length;
};

const handleUpdateStatusCours = ({ statusCoursValue, usersCountValue, isSubscribedValue, isUserOnWaitingListValue }) => {
    cours.value.statusCours = statusCoursValue;
    usersCount.value = usersCountValue;
    isSubscribed.value = isSubscribedValue;
    isUserOnWaitingList.value = isUserOnWaitingListValue;
};
</script>


<template>
    <div class="coursDetails">
        <<div v-if="cours" class="details_wrapper w-full relative">
            <img class="w-full" :src="getImageUrl(cours.typeCours.thumbnail)" alt="">
            <div class="infos_wrapper">
                <div class="infos_container">
                    <div class="flex justify-between items-baseline">
                        <div class="date text-indigo-400 font-bold mb-10 flex items-center">Le {{ formattedDate }} à {{ formattedHour }}</div>
                        <div class="flex flex-col justify-center items-center">
                            <StatusCoursTag :statusCours="cours.statusCours" />
                            <div class="pt-6 h-10">
                                <div class='isSubscribedTag' v-if="isSubscribed">Je participe</div>
                                <div class='onStandby text-red-500' v-if="isUserOnWaitingList">En attente</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="constraintsInfos text-gray-400">{{ cours.hasPriority && dateLimit ? "Priorité jusqu'au " + dateLimit : "" }}</div>
                        <div class="constraintsInfos text-gray-400">{{ !cours.hasLimitOfOneCoursPerWeek ? "Ce cours n'est pas limité à 2 par semaine" : "" }}</div>
                    </div>
                    <div>
                        <h2 class="text-white mb-4">{{ cours.typeCours.libelle }}</h2>
                        <div class="flex justify-between mb-4">
                            <div class="duree text-gray-400">Durée: {{ cours.duree }} min</div>
                            <div :style="{ visibility: cours.nbInscriptionMax - usersCount <= 3 ? 'visible' : 'hidden' }" class="dispo quantity">
                                Dispo:&nbsp;<span class="infoRestante">{{ cours.nbInscriptionMax - usersCount >= 0 ? cours.nbInscriptionMax - usersCount : 0}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="descriptif">{{ cours.typeCours.descriptif }}</div>

                    <div class="specialNote mt-5" v-if="cours.specialNote !== ''">Note: {{ cours.specialNote }}</div>

                    <div class="w-full ml-auto mr-auto mt-2">
                        <!--                    Barre delimitation-->
                        <div class="w-2/3 h-0.5 bg-gray-200 mx-auto mb-3"></div>
                        <div class="button flex justify-center gap-5">
                            <ButtonsCardUser
                                 v-if="!isAdminPath"
                                 :userId="userId"
                                 :coursId="cours.id"
                                 :statusCours="cours.statusCours"
                                 :isSubscribed="isSubscribed"
                                 :isUserOnWaitingList="isUserOnWaitingList"
                                 @updateCoursStatus="handleUpdateStatusCours"
                            />
<!--                            Ouvre une modale pour désinscrire plusieurs participants-->
                            <div v-if="isAdminPath && (cours.statusCours.id === 1 || cours.statusCours.id === 2)">
                                <ModalUnsubscribeUsers
                                    :cours="cours"
                                    :usersSubscribed="usersSubscribed"
                                    :isModalUnsubscribedUsersOpen="UnsubscribeUsersDialog"
                                    :usersOnStandby="usersOnStandby"
                                    @updateUnsubscribeUsersValue="handleUpdateUnsubscribeUsersValue"
                                />
                            </div>
                            <ModalConnect
                                v-if="!isAuthenticated && (cours.statusCours.libelle === 'Ouvert' || cours.statusCours.libelle === 'Complet')"
                                v-model:isOpen="loginDialog"
                                title="Connexion requise"
                                message="Veuillez vous authentifier pour vous inscrire à ce cours."
                                @login="redirectToLogin"
                            >
                                {{ cours.statusCours.libelle === "Complet" ? 'Liste d\'attente' : 'S\'inscrire' }}
                            </ModalConnect>
                            <div>
                                <CustomButton @click="stepBack">Retour</CustomButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped lang="scss">

p{
    font-size: clamp(0.8rem, 1vw, 1.4rem);
}

.coursDetails{
    margin-top: 123px;
}

.details_wrapper {
    display: flex;
    position: relative;
    min-height: 500px;
    max-height: 80vh;


    img {
        width: 100%;
        min-height: 60vh;
        object-fit: cover;
    }
}

.infos_wrapper {
    position: absolute;
    width: 50%;
    top: 0;
    bottom: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    display: flex;
    flex-direction: column;

    .infos_container {
        height: 100%;
        margin: 4vw;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .date {
      text-transform: uppercase;
      letter-spacing: .2rem;
      font-size: clamp(0.6rem, .8vw, 1rem);
    }
}

.isSubscribedTag, .isUserOnWaitingListTag, .specialNote, .descriptif, .duree, .dispo, .isSubscribed, .onStandby, .constraintsInfos {
    font-size: clamp(0.8rem, 1vw, 1rem);
}


@media (max-width: 980px) {
  .infos_wrapper {
    width: 100%;
  }
}
</style>
