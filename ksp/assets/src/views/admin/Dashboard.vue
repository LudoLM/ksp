<script setup>

import ChartThree from "../../components/admin/ChartThree.vue";
import {onMounted, ref} from "vue";

const cours = ref([]);
const tauxRemplissage7Jours = ref(0);
const tauxRemplissage30Jours = ref(0);
const tauxRemplissage90Jours= ref(0);

const fetchCours = async () => {
    const response = await fetch(`/api/getCoursFilling`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
        },
    })

    cours.value = await response.json();
}

const calculateTauxRemplissage = (cours) => {
    if (typeof cours === 'string') {
        cours = JSON.parse(cours);
    }
    let totalInscrits7Jours = 0;
    let totalCapacite7Jours = 0;
    let totalInscrits30Jours = 0;
    let totalCapacite30Jours = 0;
    let totalInscrits90Jours = 0;
    let totalCapacite90Jours = 0;

    cours.forEach((cour) => {
        if (new Date(cour.dateCours) < new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000)) {
            totalInscrits7Jours += cour.nbInscrits;
            totalCapacite7Jours += cour.nbInscriptionMax;
        }
        if (new Date(cour.dateCours) < new Date(new Date().getTime() + 30 * 24 * 60 * 60 * 1000)) {
            totalInscrits30Jours += cour.nbInscrits;
            totalCapacite30Jours += cour.nbInscriptionMax;
        }
        if (new Date(cour.dateCours) < new Date(new Date().getTime() + 90 * 24 * 60 * 60 * 1000)) {
            totalInscrits90Jours += cour.nbInscrits;
            totalCapacite90Jours += cour.nbInscriptionMax;
        }
    });
    tauxRemplissage7Jours.value = totalCapacite7Jours > 0 ? (totalInscrits7Jours / totalCapacite7Jours) * 100 : 0;
    tauxRemplissage30Jours.value = totalCapacite30Jours > 0 ? (totalInscrits30Jours / totalCapacite30Jours) * 100 : 0;
    tauxRemplissage90Jours.value = totalCapacite90Jours > 0 ? (totalInscrits90Jours / totalCapacite90Jours) * 100 : 0;
};



onMounted(async () => {
    await fetchCours();
    calculateTauxRemplissage(cours.value);
});

</script>

<template>


    <div class="flex flex-col justify-space-between align-center bg-white">
        <h4 class="text-xl font-bold text-black dark:text-white text-center my-6">Remplissage des cours</h4>
        <ChartThree
            title="Sur 7 jours"
            :tauxRemplissage="tauxRemplissage7Jours"
            color='#3C50E0'
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

</template>
<style scoped lang="scss">
</style>
