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
          <div :class="isUserOnWaitingList ? 'isUserOnWaitingList' : 'invisible'">
            En attente
          </div>
        </div>
        <div class="min-h-24 grid items-end">
          <div class="grid grid-cols-2">
            <router-link :to="{ name: 'AdminCoursDetails', params: { id: info.id }}" class="mt-3 mx-2 block px-3 py-2 text-center text-sm font-semibold text-violet-600 border-2 border-violet-600">
                + d'infos
            </router-link>


            <ButtonsCardAdmin
                :statusCours="statusCours"
                :coursId="info.id"
                @cancelCours="cancelCours"
                @deleteCreation="deleteCreation"
                @updateCreation="updateCreation"
                @openCreation="openCreation"
                @handleAddExtraResponse="handleAddExtraResponse"
            />

            <ModalConfirm
                v-if="!userId && (statusCours.libelle === 'Ouvert' || statusCours.libelle === 'Complet')"
                v-model:isOpen="loginDialog"
                title="Connexion requise"
                message="Veuillez vous authentifier pour vous inscrire à ce cours."
                @login="redirectToLogin"
            >
              {{ statusCours.libelle === "Complet" ? 'Liste d\'attente' : 'S\'inscrire' }}
            </ModalConfirm>

          </div>
        </div>
      </div>
    </div>
    <StatusCoursTag
        class="statusCoursTag"
        :statusCours="statusCours"
    />
  </div>
</template>

<script setup>
import {ref, computed} from 'vue';
import { useDateFormat } from '@vueuse/core';
import { useUserStore } from "../store/user";
import {useCancelCours, useDeleteCours, useOpenCours} from "../utils/useActionCours";
import { useRouter } from 'vue-router';
import ModalConfirm from "./modal/ModalConfirm.vue";
import ButtonsCardAdmin from "./admin/ButtonsCardAdmin.vue";
import StatusCoursTag from "./StatusCoursTag.vue";

const userStore = useUserStore();
const userId = userStore.userId;
const router = useRouter();
const emit = defineEmits(['subscriptionResponse', 'deleteCoursResponse', 'cancelCoursResponse', 'handleSubscription', 'handleUnsubscription', 'updateCoursStatus']);

const props = defineProps({
  info: {
    type: Object,
    required: true,
  },
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
    const hours = dateDebut.value.getHours();
    const minutes = String(dateDebut.value.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
});
// Initialiser le statut du cours comme réactif
const statusCours = ref(props.info.statusCours);
const usersCount = ref(props.info.usersCours.filter(cours => cours.isOnWaitingList === false).length);
// Vérifier si l'utilisateur est inscrit
const isSubscribed = ref(props.info.usersCours.some(cours => cours.user.id === userId && cours.isOnWaitingList === false));
// Vérifier si l'utilisateur est en attente
const isUserOnWaitingList = ref(props.info.usersCours.some(cours => cours.user.id === userId && cours.isOnWaitingList === true));


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

const handleUpdateStatusCours = ({ statusCoursValue, usersCountValue, isSubscribedValue, isUserOnWaitingListValue }) => {
  statusCours.value = statusCoursValue;
  usersCount.value = usersCountValue;
  isSubscribed.value = isSubscribedValue;
  isUserOnWaitingList.value = isUserOnWaitingListValue;
};

const handleSubscriptionresponse = ({ type, message }) => {
  emit('subscriptionResponse', {
    type: type,
    message: message,
  });
};

const handleUnsubscriptionresponse = ({ type, message }) => {
  emit('subscriptionResponse', {
    type: type,
    message: message,
  });
}

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

h3 {
    font-size: clamp(1.5rem, 1.5vw, 2rem);
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

.statusCoursTag {
  position: absolute;
  top: -10px;
  right: 10px;
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

.isUserOnWaitingList{
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
