<template>
  <h1>profil</h1>
      <!-- Recap des infos ci-dessus de l'utilisateur avec tailwind -->
  <div class="flex flex-col items-center justify-center w-full">
    <div class="bg-white shadow-md rounded-lg p-8 m-4 w-full">
      <h3>Coordonnées</h3>
      <div class="flex items-center justify-center mb-10">
        <div class="flex flex-col space-y-4 w-full">
          <div class="flex">
            <label for="nom" class="font-semibold text-gray-600 w-6/12">Nom</label>
            <p class="w-6/12">{{ user.nom }}</p>
          </div>
          <div class="flex">
            <label for="prenom" class="font-semibold text-gray-600 w-6/12">Prénom</label>
            <p class="w-6/12">{{ user.prenom }}</p>
          </div>
          <div class="flex">
            <label for="email" class="font-semibold text-gray-600 w-6/12">Email</label>
            <p class="w-6/12">{{ user.email }}</p>
          </div>
          <div class="flex">
            <label for="adresse" class="font-semibold text-gray-600 w-6/12">Adresse</label>
            <p class="w-6/12">{{ user.adresse }}</p>
          </div>
        </div>
        <div class="flex flex-col space-y-4 w-full">
          <div class="flex">
            <label for="codePostal" class="font-semibold text-gray-600 w-6/12">Code postal</label>
            <p class="w-6/12">{{ user.codePostal }}</p>
          </div>
          <div class="flex">
            <label for="commune" class="font-semibold text-gray-600 w-6/12">Commune</label>
            <p class="w-6/12">{{ user.commune }}</p>
          </div>
          <div class="flex">
            <label for="telephone" class="font-semibold text-gray-600 w-6/12">Téléphone</label>
            <p class="w-6/12">{{ user.telephone }}</p>
          </div>
        </div>
        <div class="credits">
          <div class="creditsWrapper flex flex-col justify-center align-center">
            <div class="quantityCredits flex justify-center">{{ user.nombreCours }} cours</div>
            <div class="label flex justify-center">dispo{{ user.nombreCours > 1 ? "s" : "" }}</div>
          </div>
        </div>
      </div>
      <div class="buttons w-full mt-4 flex justify-center gap-2">
        <CustomButton>
          Modifier
        </CustomButton>
        <CustomButton @click="handleBuyCours">
          Acheter des cours
        </CustomButton>
      </div>
    </div>
  </div>

  <div class="flex items-start">
    <!-- Recap des cours inscrits de l'utilisateur -->
    <div class="flex flex-col items-center justify-center">
      <div class="flex flex-col items-center justify-center bg-white shadow-md rounded-lg p-8 m-4">
        <h3>Cours inscrits</h3>
        <table class="table-auto">
          <thead class="bg-gray-800 text-white">
          <tr>
            <th class="px-4 py-2">Cours</th>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Heure</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(coursArr, index) in coursFiltered" :key="coursArr.cours.id" :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
            <td class="border px-4 py-2">{{ coursArr.cours.typeCours.libelle }}</td>
            <td class="border px-4 py-2">{{ formatDateTime(coursArr.cours.dateCours)[0] }}</td>
            <td class="border px-4 py-2">{{ formatDateTime(coursArr.cours.dateCours)[1] }}</td>
            <td class="border px-4 py-2">{{ coursArr.cours.statusCours.libelle }}</td>
            <td class="border px-4 py-2">
              <CustomButton @click="handleUnsubscription(coursArr.cours.id)">
                Se désinscrire
              </CustomButton>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recap des achats de l'utilisateur -->
    <div class="flex flex-col items-center justify-center">
      <div class="flex flex-col items-center justify-center bg-white shadow-md rounded-lg p-8 m-4">
        <h3>Recap des achats</h3>
        <table class="table-auto">
          <thead class="bg-gray-800 text-white">
          <tr>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Heure</th>
            <th class="px-4 py-2">Nombre de cours</th>
            <th class="px-4 py-2">Montant</th>
            <th class="px-4 py-2">Facture</th>
          </tr>
          </thead>
          <tbody>
            <tr v-for="(paiement, index) in user.historiquePaiements" :key="paiement.id" :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
              <td class="border px-4 py-2">{{ formatDateTime(paiement.date)[0] }}</td>
              <td class="border px-4 py-2">{{ formatDateTime(paiement.date)[1] }}</td>
              <td class="border px-4 py-2">{{ paiement["pack"].nom }}</td>
              <td class="border px-4 py-2">{{ paiement["pack"].tarif / 100 }} €</td>
              <td class="border px-4 py-2"><a href=""></a></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import { useRouter } from 'vue-router';
import { useUnSubscription } from "../utils/useSubscribing";
import CustomButton from "../components/CustomButton.vue";

const user = ref({});
const coursFiltered = computed(() =>
    user.value.usersCours ? user.value.usersCours.filter(coursArr => !coursArr.isEnAttente) : []
);
const router = useRouter();

const getUser = async () => {
  const response = await fetch("/api/user", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
  });

  user.value = await response.json();

};

const handleBuyCours = () => {
  router.push("cours/acheter");
};

// Fonction de désinscription
const handleUnsubscription = async (coursId) => {
  const result = await useUnSubscription(coursId, false);

  if (result.success) {
    // Filtrer le cours désinscrit localement
    user.value.usersCours = user.value.usersCours.filter(coursArr => coursArr.cours.id !== coursId);
    user.value.nombreCours += 1;
  } else {
    console.error("Échec de la désinscription.");
  }
};

onMounted(() => {
  getUser();
});

const formatDateTime = (date) => {
  return new Date(date).toLocaleString().split(" ");
};
</script>


<style lang="scss" scoped>

h3{
  font-size: 1.5rem;
  font-weight: 900;
  font-style: italic;
  color: #5e2ca5;
  display: flex;
  justify-content: center;
  margin-bottom: 3rem;
}

.creditsWrapper{
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 2px solid #5e2ca5;
  color: #5e2ca5;
  font-style: italic;
  font-weight: bold;
}

</style>
