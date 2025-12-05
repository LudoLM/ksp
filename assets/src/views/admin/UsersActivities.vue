<script setup>

import bannerImage from "../../../images/banners/imageBanner5.jpg";
import Banner from "../../components/Banner.vue";
import {onMounted, ref, watch, watchEffect} from "vue";
import {apiFetch} from "../../utils/useFetchInterceptor.js";
import {monthsOfYearOptions} from "../../constants/monthsOfYear.js";
import {yearsOptions} from "../../constants/years.js";
import CustomSelect from "../../components/forms/CustomSelect.vue";
import CustomInput from "../../components/forms/CustomInput.vue";
import DeleteItem from "../../../icons/adminActions/DeleteItem.vue";
import {useLastActivitiesPerMonth} from "../../utils/composables/useLastActivities";

const title = "Activités des utilisateurs"

const selectedMonth = ref( new Date().getMonth())
const selectedYear = ref( new Date().getFullYear())
const userName = ref('')
const {lastActivitiesPerMonth} = useLastActivitiesPerMonth(selectedMonth, selectedYear, userName);

const handleReset = () => {
    selectedMonth.value = new Date().getMonth();
    selectedYear.value = new Date().getFullYear();
    userName.value = '';
}


</script>

<template>
    <Banner
        :title="title"
        :hasButton=false
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <div class="col-10 rounded-sm border border-stroke bg-white shadow-default w-full">
        <div class="py-6 px-4 md:px-6 xl:px-7.5 flex flex-col justify-between">
            <div class="flex flex-col gap-2 mb-10">
                <h4 class="text-gray-800 text-theme-sm font-semibold">Rapport utilisateurs</h4>
                <p class ="text-xs text-gray-400">Dernières actions par mois</p>
            </div>
            <div class="flex flex-col  mb-10 sm:flex-row sm:items-center sm:justify-center gap-4">
                <CustomSelect
                    :options="monthsOfYearOptions"
                    v-model="selectedMonth"
                    class="w-1/2 sm:w-1/4"
                />
                <CustomSelect
                    :options="yearsOptions()"
                    v-model="selectedYear"
                    class="w-1/2 sm:w-1/4"
                />
                <CustomInput
                    type="text"
                    id="name"
                    placeholder="Bob ou Dylan"
                    v-model="userName"
                    class="w-1/2 sm:w-1/4 sm:mt-4"
                />
                <button
                    class="hover:text deleteIcon border p-3 rounded-lg sm:mt-2"
                    @click="handleReset"
                >
                    <DeleteItem size="18"/>
                </button>
            </div>
            <div v-if="lastActivitiesPerMonth.length > 0">
                <div
                    v-for="(activity, index) in lastActivitiesPerMonth"
                    :key="index"
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
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
