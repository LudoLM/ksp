<template>
  <div class="coursCard_Wrapper">
    <div class="coursCard">
      <div class="card_infos">
        <div class="card_title">
          <h3>{{ info.typeCours.libelle }}</h3>
        </div>
        <div class="card_dateDebut">
          Le {{ formattedDate }} à {{ formattedHour }}
        </div>
        <div>
          Durée: {{ info.duree }} minutes
        </div>
        <div class="card_ville">
          À : {{ info.description }}
        </div>
        <div class="flex">
          <router-link :to="{ name: 'CoursDetail', params: { id: info.id } }" class="mt-3 mx-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            + d'infos
          </router-link>
          <!-- Activation de la modale si l'utilisateur n'est pas connecté -->
          <v-dialog v-model="loginDialog" max-width="500">
            <template v-slot:activator="{ props: activatorProps }">
              <v-btn
                  v-if="!userId"
                  v-bind="activatorProps"
                  color="indigo"
                  dark
                  class="mt-3 mx-2"
              >
                S'inscrire
              </v-btn>
            </template>

            <v-card>
              <v-card-title class="text-h5">Connexion requise</v-card-title>
              <v-card-text>
                  Veuillez vous authentifier pour vous inscrire à ce cours.
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="redirectToLogin">Login</v-btn>
                <v-btn color="blue darken-1" text @click="loginDialog = false">Fermer</v-btn>

              </v-card-actions>
            </v-card>
          </v-dialog>

          <!-- Si l'utilisateur est connecté -->
          <v-btn v-if="userId && !isSubscribed" @click="handleSubscription" class="mt-3 mx-2" color="indigo" dark>
            S'inscrire
          </v-btn>
          <v-btn v-if="userId && isSubscribed" @click="handleUnsubscription" class="mt-3 mx-2" color="indigo" dark>
            Se désinscrire
          </v-btn>
        </div>
      </div>
    </div>
    <div :class="['card_status', colors[info.statusCours.libelle]]">
      {{ info.statusCours.libelle }}
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

// Couleurs par statut
const colors = {
  'En cours': 'bg-lime-500',
  'Ouvert': 'bg-red-500',
  'Fermé': 'bg-blue-500',
  'Annulé': 'bg-yellow-500',
  'En création': 'bg-indigo-500',
  'Passé': 'bg-amber-500',
  'Archivé': 'bg-emerald-500',
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
const formattedDate = computed(() => useDateFormat(dateDebut.value, 'DD/MM/YYYY').value);
const formattedHour = computed(() => useDateFormat(dateDebut.value, 'HH:mm').value);

// Vérifier si l'utilisateur est inscrit
const isSubscribed = ref(props.info.users.some(user => user.id === userId));

// Gestion des abonnements
const handleSubscription = async () => {
  const success = await useSubscription(props.info.id);
  if (success) {
    isSubscribed.value = true;
  }
};

const handleUnsubscription = async () => {
  const success = await useUnSubscription(props.info.id);
  if (success) {
    isSubscribed.value = false;
  }
};
</script>

<style lang="scss" scoped>
$mainColor: #A289CC;

.coursCard_Wrapper {
  width: 200px;
  height: 300px;
  position: relative;
}

.coursCard {
  width: 100%;
  height: 100%;
  font-family: poppins;
  background: #fff;
}

.buttons {
  height: 25%;
}

.sortieCard_Wrapper:hover .card_infos {
  color: #fff;
  top: 30%;
}

.card_status {
  position: absolute;
  width: 100px;
  height: 40px;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  top: 30px;
  right: -30px;
  transition: all 0.3s ease-in-out;
}

.card_infos_back {
  color: #fff;
}

.sortieCard_Wrapper:hover .sortieCardBack {
  bottom: 0;
}
</style>
