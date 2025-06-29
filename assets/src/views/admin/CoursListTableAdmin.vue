<template>
    <Banner
        :title="title"
        :hasButton=false
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <div
        class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:pb-1"
    >
        <div class="w-full flex justify-center items-center my-10">
            <div class="filters w-full flex justify-center gap-10">
                <MonthsFilter
                    :selectedMonth ="selectedMonth"
                    @update:selectedMonthList="updateSelectedMonthList"
                />
                <YearsFilter
                    :selectedYear ="selectedYear"
                    @update:selectedYearList="updateSelectedYearList"
                />
                <TypeCoursFilter
                    :uniqueTypeCoursList="uniqueTypeCoursList"
                    @update:selectedTypeCours="updateTypeCoursList"
                />
                <StatusCoursFilter
                    :uniqueStatusCoursList="uniqueStatusCoursList"
                    @update:selectedStatusCours="updateStatusCoursList"
                />
                <CustomButton
                    color='red'
                    @click="resetInfos"
                >Reset</CustomButton>
            </div>
        </div>
        <div class="max-w-full overflow-x-auto mb-20">
            <div class="w-full table-auto">
                <div class="thead bg-gray-800 text-white flex">
                    <div class="py-4 px-4 font-medium text-left">Cours</div>
                    <div class="py-4 px-4 font-medium text-left">Date</div>
                    <div class="py-4 px-4 font-medium text-left">Inscrits</div>
                    <div class="py-4 px-4 font-medium text-left">Statut</div>
                    <div class="py-4 px-4 font-medium text-left">Actions</div>
                </div>
                <div v-if="coursData.length > 0">
                    <div v-for="(item, index) in coursData" :key="item.id + currentPage" :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
                        <CoursLine
                            :item="item"
                            @deleteCreation="handleUpdateDeleteCreation"
                        />
                    </div>
                </div>
                <div v-else class="flex justify-center items-center h-70">
                    <p>Aucun cours</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    div{
        font-size: clamp(0.8rem, 1.5vw, 1rem);
    }
</style>

<script setup>
import {ref, onMounted, watch, inject} from 'vue';
import { useRoute } from "vue-router";
import {
    useGetCours,
    useGetStatusCours,
    useGetTypesCours,
} from "../../utils/useActionCours";
import Banner from "../../components/Banner.vue";
import bannerImage from "../../../images/banners/imageBanner5.jpg";
import CustomButton from "../../components/forms/CustomButton.vue";
import CoursLine from "../../components/CoursLine.vue";
import TypeCoursFilter from "../../components/filtersCours/TypeCoursFilter.vue";
import StatusCoursFilter from "../../components/filtersCours/StatusCoursFilter.vue";
import MonthsFilter from "../../components/filtersCours/MonthsFilter.vue";
import YearsFilter from "../../components/filtersCours/YearsFilter.vue";

const selectedCoursId = ref(null);
const coursData = ref([]);
const selectedMonth = ref(String(new Date().getMonth() + 1).padStart(2, '0'));
const selectedYear = ref(new Date().getFullYear());
const selectedDate = ref(new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-01T00:00:00Z');
const selectedStatusId = ref(null);
const selectedTypeCoursId = ref(null);
const currentPage = ref(1);
const uniqueTypeCoursList = ref([]);
const uniqueStatusCoursList = ref([]);
const route = useRoute();
const alertStore = inject('alertStore');
const title = 'Liste des cours';
const routeGetCours = ref("getCours");


// Appel de fetchData lors du montage
onMounted(async () => {
    await useGetCours(routeGetCours, coursData, selectedCoursId, selectedDate, selectedStatusId);
    uniqueTypeCoursList.value = await useGetTypesCours();
    uniqueStatusCoursList.value = await useGetStatusCours();
});


watch(() => route.query, () => {
    window.location.reload();
});


const handleUpdateDeleteCreation = ({ id }) => {
    // Supprimer le cours de la liste
    coursData.value = coursData.value.filter(info => info.id !== id);
};

// Mise à jour des filtres lorsqu'un événement est reçu
const updateTypeCoursList = async (value) => {
    selectedTypeCoursId.value = value;
    currentPage.value = 1;
    await useGetCours(routeGetCours, coursData, selectedTypeCoursId, selectedDate, selectedStatusId);
};

const updateSelectedMonthList = async (month) => {
    selectedMonth.value = month;
    selectedDate.value = selectedYear.value + '-' + month + '-01T00:00:00Z';
    currentPage.value = 1;
    await useGetCours(routeGetCours, coursData, selectedTypeCoursId, selectedDate, selectedStatusId);
};

const updateSelectedYearList = async (year) => {
    selectedYear.value = year;
    selectedDate.value = year + '-' + selectedMonth.value + '-01T00:00:00Z';
    currentPage.value = 1;
    await useGetCours(routeGetCours, coursData, selectedTypeCoursId, selectedDate, selectedStatusId);
};

const updateStatusCoursList = async (value) => {
    selectedStatusId.value = value;
    currentPage.value = 1;
    await useGetCours(routeGetCours, coursData, selectedTypeCoursId, selectedDate, selectedStatusId);
};

const resetInfos = async () => {
    selectedMonth.value = String(new Date().getMonth() + 1).padStart(2, '0');
    selectedYear.value = new Date().getFullYear();
    selectedDate.value = `${new Date().getFullYear()}-${selectedMonth.value}-01T00:00:00Z`;
    selectedTypeCoursId.value = null;
    await useGetCours(routeGetCours, coursData, selectedTypeCoursId, selectedDate, selectedStatusId);
};

</script>


<style scoped lang="scss">

    .thead {
        display: grid;
        grid-template-columns: 3fr 3fr 2fr 2fr 6fr;
        gap: 1rem;
        width: 100%;
        max-width: 100%;
        align-items: center;
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
