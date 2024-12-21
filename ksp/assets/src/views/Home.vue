<template>
  <div class="container">
    <!-- Affichage d'un message de validation -->
    <v-alert v-model="alertVisible" :type="alertType" dismissible>
      {{ alertMessage }}
    </v-alert>
    <div class="banner">
      <Title_banner/>
    </div>
    <div class="kspInfos bg-white">
      <div class="image">
        <img src="../../images/banner2.jpg" alt="salle de yoga">
      </div>
      <div class="accroche">
        <h4>Soulagez vos douleurs et améliorez votre quotidien grâce à Kiné Sport Santé</h4>
        <p>Des méthodes simples pour adopter de bonnes habitudes corporelles et prévenir les récidives.</p>
      </div>
    </div>
    <div class="title_wrapper">
      <h2>Les cours à venir.</h2>
    </div>


    <div class="buttonsFilters">
      <router-link to="/cours/add"><CustomButton v-if="role === 'ROLE_ADMIN'">Ajouter un cours</CustomButton></router-link>
      <CoursFilters
          :uniqueTypeCours="uniqueTypeCoursList"
          :uniqueStatusCours="uniqueStatusCoursList"
          :selectedCoursId="selectedCoursId"
          :selectedDate="selectedDate"
          :selectedStatusId="selectedStatusId"
          @update:selectedCoursId="updateSelectedCoursList"
          @update:selectedDate="updateSelectedDateList"
          @update:selectedStatusId="updateStatusCoursList"
          @resetInfos="resetInfos"
      />
    </div>

    <div class="gridCards p-12">
      <ul>
        <li v-for="info in infos" :key="info.id">
          <CoursCard
              :info="info"
              @subscriptionResponse="handleSubscriptionResponse"
              @deleteCoursResponse="handleDeleteCoursResponse"
              @cancelCoursResponse="handleCancelCoursResponse"
          />
        </li>
      </ul>
    </div>
    <div class="pagination">
      <CustomButton :class="currentPage === 1 ? 'invisible' : 'visible'" :color="'gray'" @click="prevPage" :disabled="currentPage === 1">Précédent</CustomButton>
      <span>Page {{ currentPage }} sur {{ totalPages }}</span>
      <CustomButton :class="currentPage === totalPages ? 'invisible' : 'visible'" :color="'gray'" @click="nextPage" :disabled="currentPage === totalPages">Suivant</CustomButton>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import CoursCard from "../components/CoursCard.vue";
import {VAlert} from "vuetify/components";
import CoursFilters from "../components/CoursFilters.vue";
import useGetElementsToken from "../utils/useGetElementsToken";
import { useRoute } from "vue-router";
import {useGetCours, useGetStatusCours, useGetTypesCours} from "../utils/useActionCours";
import CustomButton from "../components/CustomButton.vue";
import Title_banner from "../components/Title_banner.vue";

const infos = ref([]);
const selectedCoursId = ref(null);
const selectedDate = ref(null);
const selectedStatusId = ref(null);
const currentPage = ref(1);
const uniqueTypeCoursList = ref([]);
const uniqueStatusCoursList = ref([]);
const route = useRoute();
const role = localStorage.getItem('token') ? useGetElementsToken().roles[0] : null;
const totalItems = ref(0);
const maxPerPage = ref(window.innerWidth > 1460 ? 20 : window.innerWidth > 1110 ? 12 : 10);
const totalPages = ref(0);

// Appel de fetchData lors du montage
onMounted(async () => {
  alertMessage.value = route.query.alertMessage || '';
  alertType.value = route.query.alertType || 'success';
  alertVisible.value = route.query.alertVisible || false;

  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);


  await useGetCours(role, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  uniqueTypeCoursList.value = await useGetTypesCours();
  uniqueStatusCoursList.value = await useGetStatusCours();
  if(role !== 'ROLE_ADMIN') uniqueStatusCoursList.value =
      uniqueStatusCoursList.value.filter(
      status => status.id !== 6 && status.id !== 7 && status.id !== 4
  );



  // window.addEventListener('resize', () => {
  //   maxPerPage.value = window.innerWidth > 1460 ? 20 : window.innerWidth > 1110 ? 12 : 10;
  //   useGetCours(role, infos, currentPage, maxPerPage, totalItems);
  // });

});



// Déclaration des variables pour l'alerte
const alertVisible = ref(false);
const alertType = ref('success');
const alertMessage = ref('');

// Fonction pour gérer l'événement subscriptionResponse
const handleSubscriptionResponse = ({ type, message }) => {
  alertType.value = type;
  alertMessage.value = message;
  alertVisible.value = true;

  // Masquer l'alerte après 3 secondes
  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);
};

const handleDeleteCoursResponse = ({ type, message, id }) => {
  alertType.value = type;
  alertMessage.value = message;
  alertVisible.value = true;

  // Masquer l'alerte après 3 secondes
  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);

  // Supprimer le cours de la liste
  infos.value = infos.value.filter(info => info.id !== id);
};

const handleCancelCoursResponse = ({ type, message }) => {
  alertType.value = type;
  alertMessage.value = message;
  alertVisible.value = true;

  // Masquer l'alerte après 3 secondes
  setTimeout(() => {
    alertVisible.value = false;
  }, 3000);
};


// Mise à jour des filtres lorsqu'un événement est reçu
const updateSelectedCoursList = async (value) => {
  selectedCoursId.value = value;
  currentPage.value = 1;
  await useGetCours(role, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

const updateSelectedDateList = async (value) => {
  selectedDate.value = value;
  currentPage.value = 1;
  await useGetCours(role, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

const updateStatusCoursList = async (value) => {
  selectedStatusId.value = value;
  currentPage.value = 1;
  await useGetCours(role, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
};

/*const filteredInfos = computed(() => {
  let filtered = infos.value;

  if (selectedCoursId.value !== null && selectedCoursId.value !== "0") {
    filtered = filtered.filter(info => info.typeCours.id === selectedCoursId.value);
  }

  if (selectedDate.value) {
    filtered = filtered.filter(info => info.dateCours >= selectedDate.value);
  }

  return filtered;
});*/

/*const paginatedInfos = computed(() => {
  const start = (currentPage.value - 1) * maxPerPage.value;
  const end = start + maxPerPage.value;
  return filteredInfos.value.slice(start, end);
});*/



// Méthodes
const resetInfos = () => {
  selectedCoursId.value = null;
  selectedDate.value = '';
};
const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    await useGetCours(role, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  }
};

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    await useGetCours(role, infos, currentPage, maxPerPage, totalItems, selectedCoursId, selectedDate, selectedStatusId, totalPages);
  }
};
</script>


<style scoped>

.container {
  min-width: 100%;
}

.buttonsFilters {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 5rem;
}

.banner {
  background: linear-gradient(#260959, #472371);
  width: 100%;
  height: 70vh;
  object-fit: cover;
  /*display: flex;
  justify-content: space-between;*/
  position: absolute;
  top: 0;
  right: 0;
}

.title_wrapper {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  color: #fff;


  h2 {
    margin-left: 5rem;
    color: #2e2e2e;
    padding-bottom: 30px;
    position: relative;
    z-index: 2;

    &::before {
      content: '';
      width: 25%;
      height: 5px;
      background: #472371;
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10000;
    }
  }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 5rem;
  margin-bottom: 5rem;
  padding: 0 5rem;
    button {
      width: 200px;
    }
}

.kspInfos {
  position: relative;
  margin-top: 40vh;
  margin-bottom: 150px;
  left: 50%;
  transform: translateX(-50%);
  width: 90%;
  z-index: 10;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50vh;

  .image {
    width: 50%;
    height: 100%;

    img {
      height: 100%;
      width: 100%;
      object-fit: cover;
    }
  }

  .accroche {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 10%;
    align-items: center;
    width: 50%;
    height: 100%;
    padding: 10px 5vh;
    letter-spacing: .5px;
    line-height: 2;
    font-weight: 400;
    color: #515151;
    text-align: center;

    h4 {
      font-size: 1.5vw;
      font-weight: 900;
    }

    p {
      font-size: 1vw;
    }
  }
}


</style>
