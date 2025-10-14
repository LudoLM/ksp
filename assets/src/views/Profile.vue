<template>
    <Banner
        :title="isViewingOtherUser ? `Profil de ${currentUser.prenom} ${currentUser.nom}` : 'Mon profil' "
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
        :has-button="!isViewingOtherUser"
    />
    <section>
        <div class="p-4 mx-auto w-full md:w-3/4 md:p-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
                <!-- Informations personnelles -->
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
                                        {{ currentUser.prenom }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Nom
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.nom }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Email
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.email }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Téléphone
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.telephone }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Ville
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.commune }}
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Code Postal
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.codePostal }}
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Adresse
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.adresse }}
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                        Priorité
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-white/90 font-bold">
                                        {{ currentUser.isPrioritized ? "Forfait" : "Standard" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
                            <button
                                @click="redirectToEditProfile"
                                class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex"
                            >
                                <EditCoursIcon size="18" class="mr-2"/>
                                Modifier
                            </button>
                            <ModalConfirm
                                v-model:isOpen="confirmDialog"
                                title="Confirmation requise"
                                :message="confirmMessage"
                                @confirmActions="handleDeleteUser(currentUser.id)"
                            >
                                <button
                                    class="flex w-full text-red-700 items-center justify-center gap-2 rounded-full border border-red-700 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-red-700 hover:text-white dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex transition-colors duration-300 ease-in-out"
                                >
                                    <DeleteItem size="18" class="mr-2"/>
                                    Supprimer
                                </button>
                            </ModalConfirm>
                        </div>
                    </div>
                </div>

                <!-- Cours disponibles -->
                <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
                    <div class="coursDispo flex gap-6  items-center">
                        <h4 class="title text-lg font-semibold text-gray-800 dark:text-white/90">
                            Cours disponible{{ currentUser.nombreCours > 1 ? "s" : "" }}
                        </h4>

                        <div class="quantity grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    {{ currentUser.nombreCours }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="button"
                            v-if="isViewingOtherUser && !isModifyingCounterCours">
                            <button
                                @click="isModifyingCounterCours = true"
                                class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                            >
                                <EditCoursIcon size="18" class="mr-2"/>
                                Modifier
                            </button>
                        </div>
                        <div v-if="isViewingOtherUser && isModifyingCounterCours" class="flex justify-center items-center gap-4">
                            <CustomInput
                                type="number"
                                id="nombreCours"
                                :placeholder="currentUserNewCount"
                                v-model="currentUserNewCount"
                                class="w-24 flex items-center"
                            />
                            <div class="flex justify-between gap-6">
                                <button
                                    @click="isModifyingCounterCours = false"
                                    class="icons">
                                        <CancelCours size="14"/>
                                </button>
                            </div>
                            <div class="flex justify-between gap-6">
                                <button
                                    @click="handleUpdateCounterCours(currentUserNewCount); isModifyingCounterCours = false"
                                    class="icons"><Checked
                                    size="14"
                                    class="mr-2"/></button>
                            </div>
                        </div>

                        <button
                            v-if="!isViewingOtherUser"
                            @click="handleBuyCours"
                            class="button flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto"
                        >
                            <BuyCours size="18" />
                            Acheter
                        </button>
                    </div>
                </div>
                <Tabs>
                    <Tab
                        title="Cours inscrits"
                    >
                        <SubscribedCours
                            :userCoursHistory="userCoursHistory"
                            :currentUser="currentUser"
                            :isViewingOtherUser="isViewingOtherUser"
                            @page-changed="handlePageChanged"
                        />
                    </Tab>
                    <Tab
                        title="Recap achats"
                    >
                        <PurchaseOverview
                            :userPaymentsHistory="userPaymentsHistory.historiquePaiements" />
                    </Tab>
                </Tabs>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useRouter, useRoute } from 'vue-router';
import bannerImage from "../../images/banners/imageBanner17.jpg";
import Banner from "../components/Banner.vue";
import BuyCours from "../../icons/userActions/BuyCours.vue";
import SubscribedCours from "../components/SubscribedCours.vue";
import PurchaseOverview from "../components/PurchaseOverview.vue";
import Tabs from "../components/tabs/Tabs.vue";
import Tab from "../components/tabs/Tab.vue";
import Checked from "../../icons/Checked.vue";
import EditCoursIcon from "../../icons/adminActions/EditCoursIcon.vue";
import CancelCours from "../../icons/adminActions/CancelCours.vue";
import CustomInput from "../components/forms/CustomInput.vue";
import DeleteItem from "../../icons/adminActions/DeleteItem.vue";
import ModalConfirm from "../components/modals/ModalConfirm.vue";
import { useActionsUser, userPaymentsHistory, userCoursHistory, currentUserNewCount, currentUser } from "../utils/composables/useActionsUser";
import {useUserStore} from "../store/user";

const router = useRouter();
const route = useRoute();
const isModifyingCounterCours = ref(false);
const { deleteUser, loadProfileData, loadUserCoursHistory, loadUserPaymentsHistory, isViewingOtherUser, handleUpdateCounterCours } = useActionsUser();
const confirmDialog = ref(false);
const confirmMessage = computed(() => {
    const cours = currentUser.value?.nombreCours ?? 0;
    return cours > 0
        ? `Êtes-vous sûr de vouloir supprimer ce compte ? ${cours} cours restant${cours > 1 ? 's' : ''} ${cours > 1 ? 'seront perdus' : 'sera perdu'}.`
        : `Êtes-vous sûr de vouloir supprimer ce compte ?`;
});




const handleDeleteUser = async (userId) => {

    const result = await deleteUser(userId);

    if (result.success) {
        if (result.isOwnProfile) {
            // Suppression de son propre profil
            const userStore = useUserStore();
            await userStore.logout();
            await router.push({name: 'Accueil'});
        } else {
            const lastPage = route.query.page;
            await router.push({name: 'ControlUser', query:{page: lastPage || 1 }});
        }
    }
};


const handlePageChanged = async (newPage) => {
    await loadUserCoursHistory(route.params.id, newPage);
};

// Surveiller les changements de route
watch(
    () => route.params.id,
    async (newId) => {
        await loadProfileData(newId);
        await loadUserCoursHistory(newId);
        await loadUserPaymentsHistory(newId);
    },
    { immediate: true }
);

const handleBuyCours = () => {
    router.push({ name: "Packs" });
};

const redirectToEditProfile = () => {
    if (route.params.id) {
        router.push({ name: 'AdminEditProfile', params: { id: route.params.id } });
    }
    else {
        router.push({ name: "EditProfile" });
    }
};
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
    border-radius: 50%;
    border: 1px solid #E5E7EB;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.line{
    height: 50px;
}


@media (max-width: 500px) {
    .coursDispo{
        display: grid;
        grid-template-areas:
            "title quantity"
            "button button";
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
        width: 100%;
    }
}

</style>
