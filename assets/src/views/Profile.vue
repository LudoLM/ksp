<template>
    <Banner
        :title="'Mon Profil'"
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <section>
        <div class="p-4 mx-auto w-full md:w-3/4 md:p-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
                <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
                                Informations personnelles
                            </h4>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Prénom
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userPrenom }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Nom
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userNom }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Email
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userEmail }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Téléphone
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userTelephone }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Ville
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userVille }}
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Code Postal
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userCodePostal }}
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Adresse
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ userAdresse }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button
                            @click="redirectToEditProfile"
                            class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto"
                        >
                            <svg
                                class="fill-current"
                                width="18"
                                height="18"
                                viewBox="0 0 18 18"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z"
                                    fill=""
                                />
                            </svg>
                            Modifier
                        </button>
                    </div>
                </div>
                <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
                    <div class="coursDispo flex gap-6 lg:flex-row lg:items-start lg:justify-between items-center">
                        <h4 class="title text-lg font-semibold text-gray-800 dark:text-white/90">
                            Cours disponible{{ userNombreCours > 1 ? "s" : "" }}
                        </h4>

                        <div class="quantity grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white/90 text-lg">
                                    {{ userNombreCours }}
                                </p>
                            </div>
                        </div>

                        <button
                            @click="handleBuyCours"
                            class="button flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto"
                        >
                            <BuyCours size="18" />
                            Acheter
                        </button>
                    </div>
                </div>
                <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">Cours inscrits</h4>
                    <div class="max-w-full overflow-x-auto mb-6">
                        <div class="w-full table-auto">
                            <div class="theadCours bg-gray-800 text-white text-sm font-medium">
                                <div class="px-4 py-2">Cours</div>
                                <div class="px-4 py-2">Date</div>
                                <div class="px-4 py-2">Statut</div>
                                <div class="px-4 py-2">Actions</div>
                            </div>
                            <div v-if="coursFiltered.length > 0">
                                <div v-for="(coursArr, index) in coursFiltered" :key="coursArr.cours.id" :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
                                    <CoursLineProfile
                                        :item="coursArr.cours"
                                        @unsubscription="handleUnsubscription(coursArr.cours.id)"
                                    />
                                </div>
                            </div>
                            <div v-else class="flex justify-center items-center h-70">
                                <p>Aucun cours</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Recap des achats de l'utilisateur -->
                <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">Recap des achats</h4>
                    <div class="max-w-full overflow-x-auto mb-6">
                        <div class="w-full table-auto">
                            <div class="theadInvoices bg-gray-800 text-white text-sm font-medium">
                                <div class="px-4 py-2">Date</div>
                                <div class="px-4 py-2">Heure</div>
                                <div class="px-4 py-2">Pack</div>
                                <div class="px-4 py-2">Montant</div>
                                <div class="px-4 py-2">Facture</div>
                            </div>
                            <div v-if="historiquePaiementsQuantity > 0">
                                <div v-for="(paiement, index) in userHistory.historiquePaiements" :key="paiement.id" :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
                                    <InvoicesLine
                                        :paiement="paiement"
                                    />
                                </div>
                            </div>
                            <div v-else class="flex justify-center items-center h-70">
                                <p>Aucun achat</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</template>

<script setup>
import { computed, inject, onMounted, ref } from "vue";
import { useRouter } from 'vue-router';
import { useUnSubscription } from "../utils/useSubscribing";
import bannerImage from "../../images/banners/imageBanner17.jpg";
import Banner from "../components/Banner.vue";
import CoursLineProfile from "../components/CoursLineProfile.vue";
import InvoicesLine from "../components/InvoicesLine.vue";
import BuyCours from "../../icons/userActions/BuyCours.vue";
import { useUserStore } from "../store/user";
import {apiFetch} from "../utils/useFetchInterceptor";
import {storeToRefs} from "pinia";

const userHistory = ref({});
const coursFiltered = computed(() =>
    userHistory.value.usersCours ? userHistory.value.usersCours.filter(coursArr => !coursArr.isOnWaitingList) : []
);
const historiquePaiementsQuantity = ref(0);
const router = useRouter();
const userStore = useUserStore();
const alertStore = inject('alertStore');
const { userId, userPrenom, userNom, userEmail, userNombreCours, userTelephone, userCodePostal, userAdresse, userVille } = storeToRefs(userStore);

const getUserHistory = async () => {
    const response = await apiFetch("/api/userHistory", {
    });
    userHistory.value = await response.json();
    historiquePaiementsQuantity.value = userHistory.value.historiquePaiements !== undefined ? userHistory.value.historiquePaiements.length : 0;

};

const handleBuyCours = () => {
    router.push("Packs");
};


const redirectToEditProfile = () =>{
    router.push({
        name: 'EditProfile'
    });
}



// Fonction de désinscription
const handleUnsubscription = async (coursId) => {
    const result = await useUnSubscription(coursId, false);
    if (result.success) {
        // Filtrer le cours désinscrit localement
        user.value.usersCours = user.value.usersCours.filter(coursArr => coursArr.cours.id !== coursId);
        user.value.nombreCours += 1;
        userStore.userNombreCours = result.userCoursQuantity;
    } else {
        alertStore.setAlert(result.message, result.type);
    }
};

onMounted(() => {
    getUserHistory();
});

</script>


<style lang="scss" scoped>

h3{
    font-size: 1.5rem;
    font-weight: 900;
    font-style: italic;
    display: flex;
    justify-content: center;
    margin-bottom: 3rem;
}

.theadCours{
    display: grid;
    grid-template-columns: 3fr 3fr 2fr 1fr;
}

.theadInvoices{
    display: grid;
    grid-template-columns: 2fr 2fr 2fr 2fr 1fr;
}

.coursDispo{
    display: flex;
    justify-content: space-between;

    align-content: center;

    .quantity{
        display: flex;
        justify-content: center;
    }
}

.icons{
    padding: 8px;
    border-radius: 50%;
    border: 1px solid #E5E7EB;
}

.line{
    height: 50px;
}

@media(min-width: 850px){
    button{
        max-width: 200px;
    }
}

@media (max-width: 850px) {
    .theadCours, .theadInvoices{
        display: none;
    }

    .coursDispo{
        display: grid;
        grid-template-areas:
            "title quantity"
            "button button";
        grid-template-columns: 1fr auto;
        gap: 3rem;
        width: 100%;
    }
    .title{
        grid-area: title;
    }
    .quantity{
        grid-area: quantity;
    }
    .button{
        grid-area: button;
        display: flex;
        justify-content: center;
    }
}

</style>
