<template>
  <div class="coursCard_Wrapper">
    <div class="coursCard">
      <div class="card_image">
        <img :src="require(`../../images/photo${info.typeCours.id}.jpeg`)" alt="">
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
        <div class="flex justify-between mt-5">
          <router-link :to="{ name: 'CoursDetail', params: { id: info.id } }" class="w-6/12 mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            + d'infos
          </router-link>
          <!-- Si l'utilisateur n'est pas connecté -->
          <v-dialog v-model="loginDialog" max-width="500">
            <template v-slot:activator="{ props: activatorProps }">
              <button
                  v-if="!userId && statusCours !=='Complet'"
                  v-bind="activatorProps"
                  class="w-6/12 mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
              >
                S'inscrire
              </button>
            </template>

            <v-card>
              <v-card-title class="text-h5">Connexion requise</v-card-title>
              <v-card-text>
                Veuillez vous authentifier pour vous inscrire à ce cours.
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <button @click="redirectToLogin">Login</button>
                <button @click="loginDialog = false">Fermer</button>
              </v-card-actions>
            </v-card>
          </v-dialog>

          <!-- Si l'utilisateur est connecté -->
          <button v-if="userId && !isSubscribed && statusCours !=='Complet' && statusCours !== 'En création'" @click="handleSubscription" class="w-6/12 mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            S'inscrire
          </button>
          <button v-if="userId && isSubscribed" @click="handleUnsubscription" class="w-6/12 mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Se désinscrire
          </button>
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

const userStore = useUserStore();
const userId = userStore.getUserId;

const emit = defineEmits(['subscriptionResponse']);

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
  if (success.reponse) {
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
  if (success.reponse) {
    isSubscribed.value = false;
    statusCours.value = success.statusChange;
    usersCount = success.usersCount;
    emit('subscriptionResponse', {
      type: 'success',
      message: "Vous vous êtes désinscrit de ce cours",
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
