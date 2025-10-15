<script setup>

import CoursLineProfile from "./CoursLineProfile.vue";
import {computed, ref} from "vue";
import {useUnSubscription} from "../utils/useSubscribing";
import SmartPagination from "./admin/SmartPagination.vue";

const props = defineProps({
    userCoursHistory: {
        type: Object,
        required: true
    },
    currentUser: {
        type: Object,
        required: true
    },
    isViewingOtherUser: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['page-changed']);

const handleUnsubscription = async (coursId) => {
    const result = await useUnSubscription(coursId, false);
    if (result.success) {
        props.userCoursHistory.data = props.userCoursHistory.data.filter(
            coursArr => coursArr.cours.id !== coursId
        );
        props.currentUser.nombreCours += 1;

        // Mettre à jour le store si c'est le profil de l'utilisateur connecté
        if (!props.isViewingOtherUser) {
            userStore.userNombreCours = result.userCoursQuantity;
        }

        alertStore.setAlert("Désinscription réussie", "success");
    } else {
        alertStore.setAlert(result.message, result.type);
    }
};

// Computed pour les cours filtrés
const coursFiltered = computed(() =>
    props.userCoursHistory.data?.filter(coursArr => !coursArr.isOnWaitingList) || []
);

const currentPage = ref(1);

const handlePageChange = async (newPage) => {
    currentPage.value = newPage;
    //emettre un événement pour que le parent puisse gérer le changement de page
    emit('page-changed', newPage);
};
</script>

<template>
    <!-- Cours inscrits -->
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
                    <div
                        v-for="(coursArr, index) in coursFiltered"
                        :key="coursArr.id"
                        :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'"
                    >
                        <CoursLineProfile
                            :item="coursArr"
                            @unsubscription="handleUnsubscription(coursArr.id)"
                        />
                    </div>
                    <SmartPagination
                        class="my-10"
                        :totalPages="userCoursHistory.metadata.total_pages"
                        :currentPage="userCoursHistory.metadata.current_page"
                        @page-changed="handlePageChange"
                    />
                </div>
                <div v-else class="flex justify-center items-center h-70">
                    <p>Aucun cours</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
    .theadCours{
        display: grid;
        grid-template-columns: 3fr 3fr 2fr 1fr;
    }

    @media (max-width: 850px) {
        .theadCours{
            display: none;
        }
    }
</style>


