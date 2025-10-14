<script setup>
import bannerImage from "../../../images/banners/imageBanner5.jpg";
import Banner from "../../components/Banner.vue";
import {ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import SmartPagination from "../../components/admin/SmartPagination.vue";
import CustomInput from "../../components/forms/CustomInput.vue";
import CustomButton from "../../components/forms/CustomButton.vue";
import ModalConfirm from "../../components/modals/ModalConfirm.vue";
import InfosItem from "../../../icons/adminActions/InfosItem.vue";
import DeleteItem from "../../../icons/adminActions/DeleteItem.vue";
import {useActionsUser} from "../../utils/composables/useActionsUser";
import {useAdminUsersManagement, users, currentPage, metadata, searchUser} from "../../utils/composables/useAdminUsersManagement";

const title = 'Gestion des utilisateurs';
const route = useRoute();
const router = useRouter();
const confirmDialog = ref(false);
const { deleteUser } = useActionsUser();
const { getUsers, resetAllUserCounterCours } = useAdminUsersManagement();


//Gère le clic sur les boutons de pagination.
const handlePageChange = async (newPage) => {
    if (newPage === currentPage.value) return;
    await router.push({ query: { page: newPage } });
};



const handleDeleteUser = async (selectedUser) => {
    if (selectedUser) {
        await deleteUser(selectedUser);
        // Recharger la page actuelle après la suppression
        if(currentPage.value > metadata.value.total_pages ) {
            currentPage.value = metadata.value.total_pages;
            await router.replace({query: {page: currentPage.value}});
            return getUsers(currentPage.value);
        }
        await getUsers(currentPage.value);
    }
};

const redirectToUserProfile = (selectedUser) => {
    if (selectedUser) {
        router.push({ name: 'AdminProfile', params: { id: selectedUser}, query: { page: currentPage.value } });
    }
};

watch(() => route.query.page, async (newPageFromUrl) => {
    // 1. Déterminer la page souhaitée (1 par défaut si l'URL est vide)
    const requestedPage = parseInt(newPageFromUrl ?? 1);
    // 2.1 Vérification essentielle : Si la page lue est un nombre valide
    if ((isNaN(requestedPage) || requestedPage < 1)) {
        // Optionnel : Corriger l'URL si elle est invalide (ex: ?page=abc)
        await router.replace({ query: { page: 1 } })
        return;
    }

    await getUsers(requestedPage);
    // 2.2 Vérification supplémentaire : Si la page demandée dépasse le nombre total de pages, rediriger vers la dernière page valide
    if (metadata.value.total_pages && requestedPage > metadata.value.total_pages){
        await router.replace({ query: { page: metadata.value.total_pages } });
    }


}, { immediate: true });

</script>

<template>
    <Banner
        :title="title"
        :hasButton=false
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <div class="p-10">
        <div>
            <CustomInput
                type="text"
                v-model="searchUser"
                @input="getUsers(1)"
                placeholder="Rechercher un utilisateur"
            />
        </div>
        <div class="flex justify-center align-items-center">
            <ModalConfirm
                v-model:isOpen="confirmDialog"
                title="Confirmation requise"
                message="Etes-vous sûr de vouloir remettre tous les nombres de cours à 0 ?"
                @confirmActions="resetAllUserCounterCours"
            >
                <CustomButton
                    color="red"
                    class="flex justify-center items-start h-full"
                >
                    Reset Nombre de Cours
                </CustomButton>
            </ModalConfirm>

        </div>
    </div>
    <div
        class="rounded-sm border border-stroke bg-white md:px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:pb-1"
    >
        <div class="w-full overflow-x-auto mb-20">
            <div class="w-full table-auto">
                <div class="hidden md:grid grid-cols-[2fr_2fr_1fr_2fr] gap-2 items-center text-sm w-full bg-gray-800 text-white">

                    <div class="py-4 px-4 font-medium text-left">Utilisateurs</div>
                    <div class="py-4 px-4 font-medium text-left">Email</div>
                    <div class="py-4 px-4 font-medium text-left">Nombre de cours</div>
                    <div class="py-4 px-4 font-medium text-center">Actions</div>
                </div>
                <div v-if="users.length > 0">
                    <div
                        v-for="(user, index) in users"
                        :key="index"
                        :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'"
                    >
                        <div
                            class="relative flex min-h-15 gap-5"
                        >
                            <div class="grid grid-cols-[2fr_2fr_1fr_2fr] gap-2 items-center text-xs md:text-sm w-full">
                                <div class="ml-4">{{ user.nom }} {{ user.prenom }}</div>
                                <div class="ml-4 max-w-xs truncate">{{ user.email }}</div>
                                <div class="text-center">{{ user.nombreCours }}</div>
                                <div class="flex justify-center items-center mr-4">

                                    <button class="userActions hover:text">
                                        <InfosItem
                                            size="18"
                                            @click="redirectToUserProfile(user.id)"
                                        />
                                    </button>
                                    <button class="userActions text-red-700 border border-red-700 bg-transparent hover:bg-red-700 hover:text-white ml-2 transition-colors duration-300 ease-in-out">
                                        <DeleteItem
                                            class=""
                                            size="18"
                                            @click="handleDeleteUser(user.id)"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <SmartPagination
                        class="my-10"
                        :currentPage="currentPage"
                        :totalPages="metadata.total_pages"
                        @page-changed="handlePageChange"
                    />
                </div>
                <div v-else class="flex justify-center items-center h-70">
                    <p>Aucun utilisateur</p>
                </div>

            </div>
        </div>
    </div>

</template>

<style scoped lang="scss">

/*.thead, .line {
    display: grid;
    grid-template-columns: 3fr 6fr 3fr 2fr;
    gap: 1rem;
    width: 100%;
    max-width: 100%;
    align-items: center;
}*/

.userActions {
    padding: 10px;
    border: 1px solid #E5E7EB;
    border-radius: 50%;
}

@media (max-width: 900px) {
    .thead{
        display: none;
    }

    .filters{
        flex-direction: column;
        gap: 1rem;
    }
}
</style>
