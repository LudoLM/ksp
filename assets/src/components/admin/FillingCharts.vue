<script setup>

import ChartThree from "./ChartThree.vue";
import {computed, onMounted, ref} from "vue";
import {apiFetch} from "../../utils/useFetchInterceptor";

const cours = ref([]);

const totals = ref({
    inscrits7Jours: 0,
    capacite7Jours: 0,
    inscrits30Jours: 0,
    capacite30Jours: 0,
    inscrits90Jours: 0,
    capacite90Jours: 0,
});

const tauxRemplissage7Jours = computed(() => totals.value.capacite7Jours > 0 ? (totals.value.inscrits7Jours / totals.value.capacite7Jours) * 100 : 0);
const tauxRemplissage30Jours = computed(() => totals.value.capacite30Jours > 0 ? (totals.value.inscrits30Jours / totals.value.capacite30Jours) * 100 : 0);
const tauxRemplissage90Jours = computed(() => totals.value.capacite90Jours > 0 ? (totals.value.inscrits90Jours / totals.value.capacite90Jours) * 100 : 0);

const fetchCours = async () => {
    const response = await apiFetch(`/api/getCoursFilling`, {
        method: 'GET',
    });
    cours.value = await response.json();
};

const calculateTauxRemplissage = (cours) => {
    if (typeof cours === 'string') {
        cours = JSON.parse(cours);
    }

    cours.forEach((cour) => {
        if (new Date(cour.dateCours) < new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000)) {
            totals.value.inscrits7Jours += cour.nbInscrits;
            totals.value.capacite7Jours += cour.nbInscriptionMax;
        }
        if (new Date(cour.dateCours) < new Date(new Date().getTime() + 30 * 24 * 60 * 60 * 1000)) {
            totals.value.inscrits30Jours += cour.nbInscrits;
            totals.value.capacite30Jours += cour.nbInscriptionMax;
        }
        if (new Date(cour.dateCours) < new Date(new Date().getTime() + 90 * 24 * 60 * 60 * 1000)) {
            totals.value.inscrits90Jours += cour.nbInscrits;
            totals.value.capacite90Jours += cour.nbInscriptionMax;
        }
    });
};

onMounted(async () => {
    await fetchCours();
    calculateTauxRemplissage(cours.value);
});

</script>


<template>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark w-full">
        <div class="py-6 px-4 md:px-6 xl:px-7.5">
            <div class="flex flex-col gap-2">
                <h4 class="text-gray-800 text-theme-sm font-semibold">Remplissage</h4>
                <p class ="text-xs text-gray-400">Taux de remplissage des prochains jours</p>
            </div>
            <div class="flex justify-around items-center mt-4">
                <ChartThree
                    title="Sur 7 jours"
                    :tauxRemplissage="tauxRemplissage7Jours"
                    color='#3C50E0'
                    bgColor='#F0F2FF'
                />
                <ChartThree
                    title="Sur 30 jours"
                    :tauxRemplissage="tauxRemplissage30Jours"
                    color='#80CAEE'
                />
                <ChartThree
                    title="Sur 90 jours"
                    :tauxRemplissage="tauxRemplissage90Jours"
                    color='#FFBA00'
                />
            </div>

    </div>
    </div>

</template>
<style scoped lang="scss">
</style>
