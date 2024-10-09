<template>
  <div class="coursCard_Wrapper">
    <div class="coursCard">
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
          <div :class="isSubscribed ? 'isSubscribed' : 'invisible'">
            Je participe
          </div>
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
                    v-if="!userId && statusCours !=='Complet'"
                    v-bind="activatorProps"

                >
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
            <CustomButton v-if="userId && !isSubscribed && role !== 'ROLE_ADMIN' && statusCours !=='Complet' && statusCours !== 'En création'" @click="handleSubscription">
              S'inscrire
            </CustomButton>
            <CustomButton v-if="userId && isSubscribed && role !== 'ROLE_ADMIN'" @click="handleUnsubscription">
              Se désinscrire
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
import { useSubscription, useUnSubscription } from "../utils/useSubscribing";
import useGetElementsToken from "../utils/useGetElementsToken";
import {useCancelCours, useDeleteCours, useOpenCours} from "../utils/useActionCours";
import { defineProps, defineEmits } from 'vue';
import { useRouter } from 'vue-router';
import CustomButton from "./CustomButton.vue";



const userStore = useUserStore();
const userId = userStore.userId;
const router = useRouter();
const role = ref(localStorage.getItem('token') ? useGetElementsToken().roles[0] : null);

const emit = defineEmits(['subscriptionResponse', 'deleteCoursResponse', 'cancelCoursResponse']);

// Couleurs par statut
const colors = {
  'En cours': 'bg-lime-500',
  'Ouvert': 'bg-emerald-500',
  'Complet': 'bg-blue-500',
  'Annulé': 'bg-yellow-500',
  'En création': 'bg-indigo-500',
  'Passé': 'bg-red-500',
  'Archivé': 'bg-amber-500',
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
let usersCount = ref(props.info.users.length);

// Vérifier si l'utilisateur est inscrit
const isSubscribed = ref(props.info.users.some(user => user.id === userId));

// Gestion de l'inscription
const handleSubscription = async () => {
  const success = await useSubscription(props.info.id);
  if (success.response) {
    isSubscribed.value = true;
    statusCours.value = success.statusChange;
    usersCount = success.usersCount;
    emit('subscriptionResponse', {
      type: 'success',
      message: "Vous êtes inscrit à ce cours",
    });
  } else {
    emit('subscriptionResponse', {
      type: 'error',
      message: "Vous n'avez pas pu être inscrit à ce cours",
    });
  }
};

// Gestion de la désinscription
const handleUnsubscription = async () => {
  const success = await useUnSubscription(props.info.id);
  if (success.response) {
    isSubscribed.value = false;
    statusCours.value = success.statusChange;
    usersCount = success.usersCount;
    emit('subscriptionResponse', {
      type: 'success',
      message: "Vous vous êtes désinscrit de ce cours",
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
    height: 300px;
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

.card_dispoRestantes {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  color: #838383;
  font-weight: 100;

  .isSubscribed{
    color: red;
    font-weight: normal;
    font-style: italic;
  }

  .infoRestante {
    font-weight: 500;
    color: #5e2ca5;

  }


}
</style>
