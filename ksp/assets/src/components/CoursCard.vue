<template>
  <div class='coursCard_Wrapper'>
    <div class='coursCard'>
      <div class="card_image">
        <img :src="require(`../../images/uploads/${info.typeCours.thumbnail}`)" alt="">
      </div>
      <div class="card_infos">
        <div class="card_dateDebut">
          {{ capitalizedDate }}
        </div>
        <div class="card_title">
          <h3>{{ info.typeCours.libelle }}</h3>
        </div>
        <div class="card_times">
          {{ formattedHour }} - {{ info.duree }} mns
        </div>
        <div class="card_dispoRestantes">
          <div class="quantity">
            Dispo:&nbsp;<span class="infoRestante">{{ info.nbInscriptionMax - usersCount }}</span>
          </div>
<!--          <div :class="isSubscribed || isUserAttente ? 'isSubscribed' : 'invisible'">-->
<!--            {{ isSubscribed ? 'Je participe' : 'En attente' }}-->
<!--          </div>-->
        </div>
        <div class="min-h-24 grid items-end">
          <div class="grid grid-cols-2">
            <router-link :to="{ name: 'CoursDetail', params: { id: info.id }}" class="mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                + d'infos
            </router-link>
            <!-- Si l'utilisateur est admin et le cours en creation -->
            <CustomButton v-if="role === 'ROLE_ADMIN' && statusCours === 'En création'" @click="openCreation">
              Ouvrir
            </CustomButton>
            <CustomButton v-if="role === 'ROLE_ADMIN' && statusCours === 'En création'" @click="updateCreation">
              Modifier
            </CustomButton>
            <CustomButton v-if="role === 'ROLE_ADMIN' && statusCours === 'En création'" @click="deleteCreation" :color="'red'">
              Supprimer
            </CustomButton>
            <CustomButton v-if="role === 'ROLE_ADMIN' && (statusCours === 'Ouvert' || statusCours === 'Complet')" @click="cancelCours">
              Annulé
            </CustomButton>

            <!-- Si l'utilisateur n'est pas connecté -->
            <v-dialog v-model="loginDialog" max-width="500">
              <template v-slot:activator="{ props: activatorProps }">
                <CustomButton
                    v-if="!userId && statusCours ==='Ouvert'"
                    v-bind="activatorProps">
                  S'inscrire
                </CustomButton>
              </template>

              <v-card>
                <v-card-title class="text-h5">Connexion requise</v-card-title>
                <v-card-text>
                  Veuillez vous authentifier pour vous inscrire à ce cours.
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <CustomButton @click="redirectToLogin">Login</CustomButton>
                  <CustomButton @click="loginDialog = false">Fermer</CustomButton>
                </v-card-actions>
              </v-card>
            </v-dialog>

            <!-- Si l'utilisateur est connecté -->
            <CustomButton v-if="userId && !isSubscribed && role !== 'ROLE_ADMIN' && statusCours === 'Ouvert'" @click="handleSubscription(false)">
              S'inscrire
            </CustomButton>
            <CustomButton v-if="userId && isSubscribed && role !== 'ROLE_ADMIN'" @click="handleUnsubscription(false)">
              Se désinscrire
            </CustomButton>
            <CustomButton v-if="!isSubscribed && !isUserAttente && statusCours === 'Complet' && role !== 'ROLE_ADMIN'" @click="handleSubscription(true)">
              Liste d'attente
            </CustomButton>
            <CustomButton v-if="userId && isUserAttente && statusCours === 'Complet' && role !== 'ROLE_ADMIN'" @click="handleUnsubscription(true)">
              Retirer attente
            </CustomButton>
          </div>
        </div>
      </div>
    </div>
    <div :class="['card_status', colors[statusCours]]">
      {{ statusCours }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useDateFormat } from '@vueuse/core';
import { useUserStore } from "../store/user";
import {useSubscription, useUnSubscription} from "../utils/useSubscribing";
import useGetElementsToken from "../utils/useGetElementsToken";
import {useCancelCours, useDeleteCours, useOpenCours} from "../utils/useActionCours";
import { useRouter } from 'vue-router';
import CustomButton from "./CustomButton.vue";



const userStore = useUserStore();
const userId = userStore.userId;
const router = useRouter();
const role = ref(localStorage.getItem('token') ? useGetElementsToken().roles[0] : null);

const emit = defineEmits(['subscriptionResponse', 'deleteCoursResponse', 'cancelCoursResponse']);

// Couleurs par statut
const colors = {
  'En cours': 'bg-lime-300',
  'Ouvert': 'bg-emerald-300',
  'Complet': 'bg-blue-300',
  'Annulé': 'bg-yellow-300',
  'En création': 'bg-purple-300',
  'Passé': 'bg-red-300',
  'Archivé': 'bg-stone-300',
};

// Props
const props = defineProps({
  info: {
    type: Object,
    required: true,
  },
});



// État de la modale
const loginDialog = ref(false);

const redirectToLogin = () => {
  window.location.href = '/login'; // Rediriger vers la route Symfony
};

// Formattage des dates
const dateDebut = computed(() => new Date(props.info.dateCours));
const formattedDate = computed(() => useDateFormat(dateDebut.value, 'dddd D MMMM YYYY').value);
const capitalizedDate = computed(() => formattedDate.value.charAt(0).toUpperCase() + formattedDate.value.slice(1));
const formattedHour = computed(() => useDateFormat(dateDebut.value, 'H:mm').value);

// Initialiser le statut du cours comme réactif
const statusCours = ref(props.info.statusCours.libelle);
let usersCount = ref(props.info.usersCours.filter(cours => cours.isEnAttente === false).length);


// Vérifier si l'utilisateur est inscrit
const isSubscribed = ref(props.info.usersCours.some(cours => cours.user.id === userId && cours.isEnAttente === false));
const isUserAttente = ref(props.info.usersCours.some(cours => cours.user.id === userId && cours.isEnAttente === true));

// Gestion de l'inscription
const handleSubscription = async (isAttente) => {
  const result = await useSubscription(props.info.id, isAttente);
  statusCours.value = result.statusChange;
  usersCount.value = result.usersCount;

  if (result.success) {
    // Si inscription réussie
    isUserAttente.value = !!isAttente;
    isSubscribed.value = !isAttente;
    emit('subscriptionResponse', {
      type: 'success',
      message: result.response
    });
  } else {
    // Si inscription échouée
    emit('subscriptionResponse', {
      type: 'error',
      message: result.response
    });
  }
};


// Gestion de la désinscription
const handleUnsubscription = async (isAttente) => {
  const result = await useUnSubscription(props.info.id, isAttente);
  if (result.success) {
    isAttente ? isUserAttente.value = false : isSubscribed.value = false;
    statusCours.value = result.statusChange;
    usersCount.value = result.usersCount;
    emit('subscriptionResponse', {
      type: 'success',
      message: result.response
    });
  }
};


const deleteCreation = async () => {
  const response = useDeleteCours(props.info.id);

  if (response) {
    emit('deleteCoursResponse', {
      type: 'success',
      message: "Le cours a été supprimé",
      id: props.info.id
    });
  } else {
    emit('deleteCoursResponse', {
      type: 'error',
      message: "Le cours n'a pas pu être supprimé",
      id: props.info.id
    });
  }
};

const updateCreation = () => {
  router.push({ name: 'EditCours', params: { id: props.info.id } });
};


const cancelCours = async () => {
  const response = await useCancelCours(props.info.id);
  if (response) {
    statusCours.value = response.statusChange;
    emit('cancelCoursResponse', {
      type: 'success',
      message: "Le cours a été annulé",
    });
  } else {
    emit('cancelCoursResponse', {
      type: 'error',
      message: "Le cours n'a pas pu être annulé",
    });
  }
};


// A revoir
const openCreation = async () => {
  const response = await useOpenCours(props.info.id);
  if (response.response) {
    statusCours.value = response.statusChange;
    emit('subscriptionResponse', {
      type: 'success',
      message: "Le cours est désormais ouvert",
    });

  } else {
    emit('subscriptionResponse', {
      type: 'error',
      message: 'Le cours n\'a pas pu être ouvert',
    });
  }
};

</script>

<style lang="scss" scoped>
.coursCard_Wrapper {
  min-width: 300px;
  position: relative;
}


.coursCard {
  width: 100%;
  height: 100%;
  background: #fff;
}

.card_dateDebut {
  font-style: italic;
  font-weight: 900;
  color: #000;
}

.card_image {
  width: 100%;
  overflow: hidden;
  position: relative;
  img {
    width: 100%;
    min-height: 300px;
    object-fit: cover;
  }
}

.card_infos {
  height: 30%;
  padding: 20px;
}

.card_status {
  position: absolute;
  width: 100px;
  height: 40px;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  top: -10px;
  right: 10px;
  border-radius: 3px;
  transition: all 0.3s ease-in-out;
}


.card_title {
  font-size: 1.5rem;
  font-weight: 900;
  font-style: italic;
  color: #5e2ca5;
}

.card_times {
  font-weight: 100;
  margin-bottom: 20px;
}

.isSubscribed{
  //color: red;
  //font-weight: normal;
  //font-style: italic;
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.7);
  z-index: 1000;

  p, h1, h2, h3, h4, h5, h6 {
    color: white;
  }
}

.card_dispoRestantes {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  color: #838383;
  font-weight: 100;

  .infoRestante {
    font-weight: 500;
    color: #5e2ca5;

  }
}
</style>
