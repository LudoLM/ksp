<template>
  <div :class="isSubscribed ? 'isSubscribed coursCard_Wrapper' : 'coursCard_Wrapper'">
  <div class='coursCard'>
      <div class="card_image">
        <div :class="isSubscribed ? 'isSubscribedTag' : 'hidden'">Je participe</div>
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
          <div :style="{ visibility: info.nbInscriptionMax - usersCount <= 3 ? 'visible' : 'hidden' }" class="quantity">
            Dispo:&nbsp;<span class="infoRestante">{{ info.nbInscriptionMax - usersCount >= 0 ? info.nbInscriptionMax - usersCount : 0}}</span>
          </div>
          <div :class="isUserAttente ? 'isUserAttente' : 'invisible'">
            En attente
          </div>
          <div :class="debutTimer * 60 * 60 * 1000 > remainingTime && remainingTime > 0 && (statusCours === 'Ouvert' || statusCours === 'Complet') ? 'isUserAttente' : 'invisible'">
              {{ remainingTimeFormatted }}
          </div>
        </div>
        <div class="min-h-24 grid items-end">
          <div class="grid grid-cols-2">
            <router-link :to="{ name: 'CoursDetail', params: { id: info.id }}" class="mt-3 mx-2 block px-3 py-2 text-center text-sm font-semibold text-violet-600 border-2 border-violet-600">
                + d'infos
            </router-link>


            <ButtonsCardAdmin v-if="isAdminPath"
                :statusCours="statusCours"
                :coursId="info.id"
                @cancelCours="cancelCours"
                @deleteCreation="deleteCreation"
                @updateCreation="updateCreation"
                @openCreation="openCreation"
                @handleAddExtraResponse="handleAddExtraResponse"

            />

            <ButtonsCardUser v-if="!isAdminPath && remainingTime > 0"
                :userId="userId"
                :statusCours="statusCours"
                :isSubscribed="isSubscribed"
                :isUserAttente="isUserAttente"
                @handleSubscription="handleSubscription"
                @handleUnsubscription="handleUnsubscription"

            />

            <ModalConfirm
                v-if="!userId && (statusCours === 'Ouvert' || statusCours === 'Complet')"
                v-model:isOpen="loginDialog"
                title="Connexion requise"
                message="Veuillez vous authentifier pour vous inscrire à ce cours."
                @login="redirectToLogin"
            >
              {{ statusCours === "Complet" ? 'Liste d\'attente' : 'S\'inscrire' }}
            </ModalConfirm>

          </div>
        </div>
      </div>
    </div>
    <div :class="['card_status', colors[statusCours]]">
      {{ (statusCours === "Ouvert"|| statusCours === "Complet") && remainingTime < 0 ? 'Imminent' : statusCours }}
    </div>
  </div>
</template>

<script setup>
import {ref, computed, onMounted, onUnmounted} from 'vue';
import { useDateFormat } from '@vueuse/core';
import { useUserStore } from "../store/user";
import {useSubscription, useUnSubscription} from "../utils/useSubscribing";
import {useCancelCours, useDeleteCours, useOpenCours} from "../utils/useActionCours";
import { useRouter } from 'vue-router';
import ModalConfirm from "./modal/ModalConfirm.vue";
import ButtonsCardAdmin from "./admin/ButtonsCardAdmin.vue";
import ButtonsCardUser from "./user/ButtonsCardUser.vue";

const userStore = useUserStore();
const userId = userStore.userId;
const router = useRouter();
const emit = defineEmits(['subscriptionResponse', 'deleteCoursResponse', 'cancelCoursResponse', 'handleSubscription', 'handleUnsubscription']);
const delaiLimite = 30 * 60 * 1000;
const debutTimer = ref(6);

// Couleurs par statut
const colors = {
  'En cours': 'bg-lime-300',
  'Ouvert': 'bg-emerald-300',
  'Complet': 'bg-blue-300',
  'Annulé': 'bg-yellow-300',
  'En création': 'bg-purple-300',
  'Passé': 'bg-red-300',
  'Archivé': 'bg-stone-300',
  'Imminent': 'bg-orange-300',
};

// Props
const props = defineProps({
  info: {
    type: Object,
    required: true,
  },
  isAdminPath: {
    type: Boolean,
    default: false,
  }
});


// État de la modale
const loginDialog = ref(false);

const redirectToLogin = () => {
 router.push({ name: 'Login' });
};

// Formatage des dates
const dateDebut = computed(() => new Date(props.info.dateCours));

const formattedDate = computed(() =>
    useDateFormat(dateDebut.value, 'dddd D MMMM YYYY').value
);

const capitalizedDate = computed(() =>
    formattedDate.value.charAt(0).toUpperCase() + formattedDate.value.slice(1)
);

const formattedHour = computed(() => {
    const hours = dateDebut.value.getUTCHours();
    const minutes = String(dateDebut.value.getUTCMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
});

// Initialiser le statut du cours comme réactif
const statusCours = ref(props.info.statusCours.libelle);
let usersCount = ref(props.info.usersCours.filter(cours => cours.isEnAttente === false).length);
const remainingTime = ref(0);



// Vérifier si l'utilisateur est inscrit
const isSubscribed = ref(props.info.usersCours.some(cours => cours.user.id === userId && cours.isEnAttente === false));
const isUserAttente = ref(props.info.usersCours.some(cours => cours.user.id === userId && cours.isEnAttente === true));



onMounted(() => {
    if (props.info.statusCours.id === 1 || props.info.statusCours.id === 2) {
        // Capture la valeur initiale
        let initialTime = Date.now();

        const interval = setInterval(() => {
            // Calcule le temps écoulé depuis le début
            initialTime += 1000;
            remainingTime.value = new Date(dateDebut.value.getTime()).toISOString() - delaiLimite - initialTime;

            if (remainingTime.value <= 0) {
                clearInterval(interval);
            }
        }, 1000);

        // Nettoyer l'intervalle lors de la destruction du composant
        onUnmounted(() => {
            clearInterval(interval);
        });
    }
});



// Convertir le temps restant en heures et minutes
const remainingTimeFormatted = computed(() => {
    const hours = Math.abs(Math.floor(remainingTime.value / (1000 * 60 * 60)));
    const minutes = Math.abs(Math.floor((remainingTime.value % (1000 * 60 * 60)) / (1000 * 60)));
    const seconds = Math.abs(Math.floor((remainingTime.value % (1000 * 60)) / 1000));

    const formattedMinutes = String(minutes).padStart(2, '0');
    const formattedSeconds = String(seconds).padStart(2, '0');
    return `${hours}h${formattedMinutes}m${formattedSeconds}s`;
});

const handleAddExtraResponse = ({ type, message, statusChange }) => {
  if (type === 'success') {
    emit('subscriptionResponse', {
      type: type,
      message: message,
    });
    statusCours.value = statusChange;
    usersCount.value++;

  } else {
    emit('subscriptionResponse', {
      type: type,
      message: message,
    });
  }
};


// Gestion de l'inscription
const handleSubscription = async (isAttente) => {
  const result = await useSubscription(props.info.id, isAttente);
  statusCours.value = result.statusChange || statusCours.value;
  usersCount.value = result.usersCount;

  if (result.success) {
    // Si inscription réussie
    isUserAttente.value = !!isAttente;
    isSubscribed.value = !isAttente;
    emit('subscriptionResponse', {
      type: 'success',
      message: result.message
    });
  } else {
    // Si inscription échouée
    emit('subscriptionResponse', {
      type: result.type,
      message: result.message
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
      message: result.message
    });
  } else {
      // Si la desinscription a échouée
      emit('subscriptionResponse', {
          type: result.type,
          message: result.message
      });
  }
};


const deleteCreation = async () => {
  const response = await useDeleteCours(props.info.id);

  if (response.success) {
    emit('deleteCoursResponse', {
      type: 'success',
      message: response.message,
      id: props.info.id
    });
  } else {
    emit('deleteCoursResponse', {
      type: response.type ,
      message: response.message,
      id: props.info.id
    });
  }
};

const updateCreation = () => {
  router.push({ name: 'EditCours', params: { id: props.info.id } });
};

const cancelCours = async () => {
  const response = await useCancelCours(props.info.id);
  if (response.success) {
    statusCours.value = response.statusChange;
    emit('cancelCoursResponse', {
      type: 'success',
      message: response.message,
    });
  } else {
    emit('cancelCoursResponse', {
      type: response.type,
      message: response.message,
    });
  }
};


// A revoir
const openCreation = async () => {
  const response = await useOpenCours(props.info.id);
  if (response.success) {
    statusCours.value = response.statusChange;
    emit('subscriptionResponse', {
      type: 'success',
      message: response.message,
    });

  } else {
    emit('subscriptionResponse', {
      type: response.type,
      message: response.message,
    });
  }
};

</script>

<style lang="scss" scoped>
.coursCard_Wrapper {
  min-width: 300px;
  min-height: 600px;
  position: relative;
  background: #fff;

  &::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    width: 30%;
    height: 15%;
    background: #5e2ca5;
  }

  &::after {
    content: '';
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 30%;
    height: 15%;
    background: #5e2ca5;
    z-index: -1;
  }
}

.coursCard {
  width: 100%;
  height: 100%;
  position: relative;
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
    height: 300px;
    object-fit: cover;
  }
}

.card_infos {
  width: 100%;
  height: 300px;
  padding: 20px;
  top: 300px;
  position: absolute;
  z-index: 100;
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
  z-index: 11;
}


.card_title {
  font-size: 1.5rem;
  font-weight: 900;
  font-style: italic;
  color: #5e2ca5;
}

.card_times {
  font-weight: 100;
  margin-bottom: 5px;
}

.isUserAttente{
  color: red;
  font-weight: normal;
  font-style: italic;


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

.isSubscribed{
    .isSubscribedTag{
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        font-weight: 900;
        z-index: 10;
    }

    .coursCard {

        &::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 10;
        }
    }

    .card_infos {

        background: rgba(0,0,0,0.8);

        h1, h2, h3, h4, h5, h6, p, button, div, span {
            color: #fff;
        }
        .border {
            background: #fff;
        }
        a {
            border: 1px solid #fff;
            color: #fff;
        }
        CustomButton {
            background: #fff;
            color: #222;
        }

        .infoRestante {
            color: #fff;
        }
    }
}
</style>
