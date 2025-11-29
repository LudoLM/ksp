<script setup>
import CustomButton from "../forms/CustomButton.vue";
import {useRouter} from "vue-router";
import {useLastActivitiesStore} from "../../store/lastActivities.js";
import {storeToRefs} from "pinia";
import {onUnmounted} from "vue";
import {apiFetch} from "../../utils/useFetchInterceptor.js";
const router = useRouter();
const lastActivitiesStore = useLastActivitiesStore();
const {lastActivities} = storeToRefs(lastActivitiesStore);

const redirectToActivitiesList = () => {
    router.push({ name: 'UsersActivities'});
}

onUnmounted(async() => {

    const response = await apiFetch("/markAllAsRead", {method: "POST"});
    if (!response.ok) {
        console.error("Erreur lors de lors de la mise à jours des données lues : ", response.statusText);
    }
    lastActivitiesStore.clearLastActivities();
})

</script>

<template>
    <div class="col-10 rounded-sm border border-stroke bg-white shadow-default w-full">
        <div class="py-6 px-4 md:px-6 xl:px-7.5 flex flex-col justify-between">
            <div class="flex flex-col gap-2 mb-10">
                <h4 class="text-gray-800 text-theme-sm font-semibold">Utilisateurs</h4>
                <p class ="text-xs text-gray-400">Dernières actions</p>
            </div>
            <div v-if="lastActivities.length > 0">
                <div
                    v-for="activity in lastActivities"
                    :key="activity.subject"
                    class="grid grid-cols-2 md:grid-cols-12 border-t border-stroke px-4 items-center"
                >
                    <div
                        class="col-span-1 md:col-span-2 flex items-center py-3"
                        :class="{
                    'text-success': activity.type === 'Inscription',
                    'text-danger': activity.type === 'Désinscription',
                    'text-primary': activity.type === 'En attente',
                    'text-warning': activity.type === 'Supp attente',
                    'text-templateMainColor': activity.type === 'Achat de cours',
                     }"
                    >
                        <p class="text-xs md:text-sm font-medium">{{ activity.type }}</p>
                    </div>

                    <div class="col-span-1 md:col-span-10">

                        <div class="flex flex-col md:grid md:grid-cols-12">

                            <div class="md:col-span-3 flex items-center justify-start py-1 md:py-3">
                                <p class="text-xs md:text-sm font-medium">{{ new Date(activity.dateAction).toLocaleString("fr-FR", {dateStyle: 'short', timeStyle: "short"}) }}</p>
                            </div>

                            <div class="md:col-span-4 flex items-center justify-start py-1 md:py-3">
                                <p class="text-xs md:text-sm font-medium">{{ activity.userName }}</p>
                            </div>

                            <div class="md:col-span-5 flex items-center justify-start py-1 md:py-3">
                                <p class="text-xs md:text-sm font-medium">
                                    {{ activity.subject }}
                                    <span class="text-[0.7rem] text-gray-500">({{ new Date(activity.dateSubject).toLocaleString("fr-FR", {dateStyle: 'short', timeStyle: "short"}) }})</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="flex justify-center items-center h-30">
                Rien de nouveau pour le moment.
            </div>
            <div class="flex justify-end">
                <CustomButton
                    class="mt-10"
                    @click="redirectToActivitiesList"
                >
                    En voir +
                </CustomButton>
            </div>
        </div>
    </div>
</template>



<style scoped>
</style>
