<script setup>
import CustomButton from "../components/CustomButton.vue";
import {useGetCours, useGetTypesCours} from "../utils/useActionCours";
import {onMounted, ref} from "vue";
import {useDateFormat} from "@vueuse/core";
import CoursCardCalendar from "../components/CoursCardCalendar.vue";
import TypeCoursFilter from "../components/filtersCours/TypeCoursFilter.vue";

const date = ref(new Date());
const currentDate = ref(new Date());
// Formate la date pour l'envoyer au serveur
date.value = date.value.toISOString().split('T')[0];
const days = ref([]);
const weekString = ref('');
const uniqueTypeCoursList = ref([]);

// Récupère les jours de la semaine
const updateDaysOfWeek = () => {
    const current = new Date(date.value);
    const monday = new Date(current);
    const dayOfWeek = monday.getDay();
    // Cherche le lundi de la semaine
    monday.setDate(monday.getDate() - ((dayOfWeek + 6) % 7)); // Shifts to Monday
    days.value = []; // Reset the array
    for (let i = 0; i < 6; i++) {
        const day = new Date(monday);
        day.setDate(monday.getDate() + i);
        days.value.push(day.toISOString().split('T')[0]);
    }
    weekString.value = useDateFormat(monday, 'DD MMMM YYYY').value + ' au ' + useDateFormat(monday.setDate(monday.getDate() + 6), 'DD MMMM YYYY').value;

};
const daySelected = ref(0);
// Gère le clic sur les boutons de la semaine
const handleDaySelected = (index) => {
    daySelected.value += index;
};

// Met à jour les jours de la semaine
updateDaysOfWeek();
const infos = ref([]);
const weekInfos = ref([[], [], [], [], [], [], []]); // Initialisation globale
const selectedTypeCours = ref(null);
const statusCours = ref(null);
const currentPage = ref(1);
const maxPerPage = ref(1000);
const totalPages = ref(1);
const totalItems = ref(0);

const getCoursPerWeek = async () => {
    await useGetCours(false, "getCoursCalendar", infos, currentPage, maxPerPage, totalItems, selectedTypeCours, date, statusCours, totalPages);
    // Classe les cours par date/heure
    infos.value.sort((a, b) => new Date(a.dateCours) - new Date(b.dateCours));

    // Réinitialisez et mettez à jour weekInfos
    weekInfos.value = [[], [], [], [], [], [], []];
    infos.value.forEach(info => {
        const rawDay = new Date(info.dateCours).getUTCDay();
        const day = (rawDay + 6) % 7; // Ajuste pour que Lundi = 0, Dimanche = 6
        if (weekInfos.value[day]) {
            weekInfos.value[day].push(info);
        } else {
            console.error(`Indice de jour invalide: ${day}`);
        }
    });
};

const handleGetCoursPerWeek = async (direction) => {
    const current = new Date(date.value);
    // Ajoute ou soustrait 7 jours
    current.setDate(current.getDate() + (direction === "next" ? 7 : -7));
    date.value = current.toISOString().split('T')[0];
    // Mets à jour les jours de la semaine
    updateDaysOfWeek();
    // Recharge les cours
    await getCoursPerWeek();
};

const handleUpdateSelectedTypeCours = (value) => {
    selectedTypeCours.value = value;
    getCoursPerWeek();
};

const formatDay = (day) => {
    const date = new Date(day); // Convertir en objet Date
    const daysOfWeek = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    const dayOfWeek = daysOfWeek[date.getUTCDay()]; // Obtenir le nom du jour
    const dayPart = day.split('-')[2]; // Obtenir le jour
    return `<p>${dayOfWeek.substring(0, 3)} </p><p> ${dayPart}</p>`; // Utilisation de <br> pour un saut de ligne
};

onMounted(async () => {
    await getCoursPerWeek();
    uniqueTypeCoursList.value = await useGetTypesCours();
});

</script>

<template>
    <h1>Calendrier</h1>
    <div class="flex flex-col justify-center items-center gap-4 my-8 relative">
        <div>
            <p>{{ weekString }}</p>
        </div>
        <div class="buttons flex justify-center gap-1">
            <CustomButton  :class="new Date(date) > new Date(currentDate) ? '' : 'invisible'" @click="handleGetCoursPerWeek('prev')">
                Semaine Précédente
            </CustomButton>
            <CustomButton @click="handleGetCoursPerWeek('next')">
                Semaine Suivante
            </CustomButton>
        </div>
        <TypeCoursFilter
            :uniqueTypeCoursList="uniqueTypeCoursList"
            @update:selectedTypeCours="handleUpdateSelectedTypeCours"
        />
    </div>

    <div class="grid grid-cols-6 gap-4">
        <div v-for="(day, index) in days" :key="day" class="flex justify-center" @click="daySelected = index">
            <div
                v-html="formatDay(day)"
                :class="[daySelected === index ? 'days dayActif' : 'days', weekInfos[index].length > 0 ? 'has-cours' : '']">
            </div>
        </div>
        <!-- Format desktop-->
        <div v-for="(weekInfo, index) in weekInfos" :key="index" class="desktop">
            <div v-for="info in weekInfo" :key="info.id" class="flex flex-col items-center">
                <CoursCardCalendar :info="info"/>
            </div>
        </div>
    </div>
    <div class="mobile">
        <!-- Format Mobile -->
        <div :class="daySelected !== 0 ? 'dayBefore' : 'dayBefore invisible'" @click="handleDaySelected(-1)"></div>
        <div class="active">
            <!-- Affiche les cours en fonction de la date daySelected -->
            <div v-if="weekInfos[daySelected]?.length > 0">
                <div v-for="info in weekInfos[daySelected]" :key="info.id" class="flex flex-col items-center">
                    <CoursCardCalendar :info="info" />
                </div>
            </div>
            <div v-else class="flex justify-center items-center h-70">
                <p>Aucun cours</p>
            </div>
        </div>

        <div :class="daySelected !== 5 ? 'dayAfter' : 'dayAfter invisible'" @click="handleDaySelected(+1)"></div>
    </div>
</template>


<style scoped>


.mobile {
    display: none;
}

.days{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    padding-bottom: 20px;
    width: 100%;
}

.has-cours {
    color: #5e2ca5;
    font-weight: bold;
}

.dayActif {
    border-bottom: 1px solid #5e2ca5;
}

@media (min-width: 980px) {
    .days {
        font-weight: normal;
        cursor: default;
        pointer-events: none;
    }

    .has-cours {
        width: 100%;
        border: none;
        color: #000;
    }

    .dayActif {
        border-bottom: none;
    }
}

@media (max-width: 980px) {
    .desktop {
        display: none;
    }

    .mobile {
        display: flex;
        justify-content: space-around;
        gap: 10vw;
        position: relative;
        height: auto;
        margin-top: 50px;

        .active {
            width: 40vw;
            position: relative;
            transition: all 0.5s ease;
        }
        .dayBefore, .dayAfter {
            width: clamp(50px, 10vw, 150px);
            height: clamp(50px, 10vw, 150px);
            background: url('../../../assets/icons/arrow.svg') no-repeat center;
            background-size: 50%;
            margin-top: 100px;
            cursor: pointer;

            &::after{
                position: absolute;
                content: '';
                width: clamp(50px, 10vw, 100px);
                height: clamp(50px, 10vw, 100px);
                background: rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                z-index: -10;
            }
        }

        .dayBefore {
            transform: rotate(180deg);
        }
    }
}
</style>

