<script setup>
import CustomButton from "../components/forms/CustomButton.vue";
import {useCalendarStore} from "../store/calendar";
import {computed, inject, onBeforeMount, ref, watch} from "vue";
import CoursCardCalendar from "../components/CoursCardCalendar.vue";
import TypeCoursFilter from "../components/filtersCours/TypeCoursFilter.vue";
import bannerImage from "../../images/banners/imageBanner9.jpg";
import Banner from "../components/Banner.vue";
import DeleteCours from "../../icons/adminActions/DeleteCours.vue";
import Tooltip from "../components/Tooltip.vue";
import { useRouter } from "vue-router";
import {storeToRefs} from "pinia";
import {apiFetch} from "../utils/useFetchInterceptor";
import StatusCoursFilter from "../components/filtersCours/StatusCoursFilter.vue";
import {useUserStore} from "../store/user";

const calendarStore = useCalendarStore();
const dateToday = ref(new Date());
const userStore = useUserStore();
const isAdmin = storeToRefs(userStore);

const typeCoursIdFromUrl = parseInt(new URLSearchParams(window.location.search).get('typeCoursId'));
const isOpenRequiredFromUrl = ref(!! new URLSearchParams(window.location.search).get('isOpenRequired'));

const router = useRouter();
const previousRoutePath = ref(router.options.history.state.back || '');
const date = computed(() => previousRoutePath === "/coursDescriptions" ? new Date() : calendarStore.date);
const daySelected = computed(() => calendarStore.daySelected);
const weekString = computed(() => calendarStore.weekString);
const days = computed(() => calendarStore.days);
const infos = computed(() => calendarStore.infos);
const { weekInfos, selectedTypeCours, selectedStatusCours } = storeToRefs(calendarStore);
const uniqueTypeCoursList = computed(() => calendarStore.uniqueTypeCoursList);
const alertStore = inject('alertStore');

//Filtre les status accessible selon le role
const uniqueStatusCoursList = computed(() =>
    userStore.isAdmin ?
    calendarStore.uniqueStatusCoursList.filter(s => ![6, 7].includes(s.id)) :
    calendarStore.uniqueStatusCoursList.filter(s => ![4, 6, 7].includes(s.id))

);

// Gère le clic sur les boutons de navigation semaine
const handleGetCoursPerWeek = async (direction) => {
    if (isOpenRequiredFromUrl.value){
        isOpenRequiredFromUrl.value = false;
        updateUrl(false)
    }

    if (direction === "next") {
        calendarStore.nextWeek();
    } else if( direction === "prev") {
        calendarStore.prevWeek();
        calendarStore.setDaySelected(5);

    }
    else {
        calendarStore.nextCours();
    }
};



// Formate le jour pour l'affichage
const formatDay = (day) => {
    const date = new Date(day);
    const daysOfWeek = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    const dayOfWeek = daysOfWeek[date.getDay()];
    const dayPart = day.split('-')[2];
    return `<p>${dayOfWeek.substring(0, 3)} </p><p> ${dayPart}</p>`;
};

// Met à jour l'URL (peut rester local ou être une action du store si elle modifie l'état du store)
const updateUrl = (keepIsOpenRequired) => {
    const newParams = new URLSearchParams(window.location.search);
    if (newParams.has('isOpenRequired') && !keepIsOpenRequired) {
        newParams.delete('isOpenRequired');
    }
    if (calendarStore.selectedTypeCours !== 0) {
        newParams.set('typeCoursId', calendarStore.selectedTypeCours);
    } else {
        newParams.delete('typeCoursId');
    }
    if (calendarStore.selectedStatusCours !== 0) {
        newParams.set('statusCoursId', calendarStore.selectedStatusCours);
    } else {
        newParams.delete('statusCoursId');
    }
    window.history.replaceState({}, '', `${window.location.pathname}?${newParams}`);
};


onBeforeMount( async ()=> {
    // Initialisez le 'selectedTypeCours' du store avec la valeur de l'URL si elle existe
    if (!isNaN(typeCoursIdFromUrl) && typeCoursIdFromUrl !== 0) {
        calendarStore.setSelectedTypeCours(typeCoursIdFromUrl, isOpenRequiredFromUrl.value);
        await calendarStore.fetchCoursPerWeek(isOpenRequiredFromUrl.value);
    }
    else{
        await calendarStore.fetchCoursPerWeek();
    }
    // Récupérez la liste des types de cours
    await calendarStore.fetchTypesCours();
    await calendarStore.fetchStatusCours();
});


const nextDateIndex = ref(null);


// Watcher 1 : Met à jour le store et l'URL lorsque les filtres changent
watch([selectedTypeCours, selectedStatusCours], () => {
    // Met à jour les valeurs dans le store du calendrier
    calendarStore.setSelectedTypeCours(selectedTypeCours.value);
    calendarStore.setSelectedStatusCours(selectedStatusCours.value);

    // Met à jour l'URL pour refléter les nouveaux filtres
    updateUrl(false);
});

// Watcher 2 : Déclenche la récupération de données lorsque l'une des 3 valeurs importantes change
watch([date, selectedTypeCours, selectedStatusCours], async () => {
    // Réinitialise la valeur de la première leçon pour la pagination
    calendarStore.firstNextCoursInNextWeeks = null;
    // Récupère les données du calendrier avec les nouveaux filtres et la nouvelle date
    await calendarStore.fetchCoursPerWeek();
});

const displayDateNextCoursString = function(date, typeCours, statusCours) {
    return `Prochain cours ${selectedTypeCours.value !== 0 ? " de " + typeCours : ""}  ${selectedStatusCours.value !== 0 ? " " + statusCours : ""} disponible le ${new Date(date).toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })}`;
};

// Récupère la date du prochain cours dans la semaine s'il n'est pas passé
const nextDateInTheWeek = computed(() => {
    const getCoursInRestOfWeek = calendarStore.weekInfos.slice(calendarStore.daySelected).find(info => info.length > 0);

    // S'il reste des cours dans la semaine après le jour sélectionné
    if(getCoursInRestOfWeek){
        nextDateIndex.value = getCoursInRestOfWeek ? new Date(getCoursInRestOfWeek[0].dateCours).getDay() -1 : null;
        return displayDateNextCoursString(getCoursInRestOfWeek[0].dateCours, getCoursInRestOfWeek[0].typeCours.libelle, getCoursInRestOfWeek[0].statusCours.libelle);
    }
    // S'il n'y a pas de cours dans la semaine après le jour sélectionné, on prend le premier cours des semaines à venir
    else if(!getCoursInRestOfWeek && calendarStore.firstNextCoursInNextWeeks && calendarStore.firstNextCoursInNextWeeks.nextCoursDate) {
        nextDateIndex.value = null;
        return displayDateNextCoursString(calendarStore.firstNextCoursInNextWeeks.nextCoursDate.date, calendarStore.firstNextCoursInNextWeeks.typeCours, calendarStore.firstNextCoursInNextWeeks.statusCours);
    }
});

const handleLaunchAllCours = async () => {
    const firstAndLastDays = {
        "startDate" : days.value[0],
        "endDate" : days.value[5]
    }
    const response = await apiFetch("/api/week/open", {
        method: "PUT",
        body: JSON.stringify(firstAndLastDays)
    });

    const result = await response.json();

    if(response){
        alertStore.setAlert(result.message, "success");
    }
    else{
        alertStore.setAlert(result.message, "error");
    }

    calendarStore.setSelectedStatusCours(0);
    await calendarStore.fetchCoursPerWeek();
};


// Récupère la date du prochain cours dans les semaines à venir pour les semaines sans cours
const nextDateInNextWeek = computed(() => {
    if (infos.value.type === "info_next_cours") {
        return displayDateNextCoursString(infos.value.nextCoursDate.date, infos.value.typeCours, infos.value.statusCours);
    }
});
</script>

<template>
    <Banner
        title="Planning des cours"
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
        :hasButton=false
    />
    <div class="flex flex-col justify-center items-center gap-4 my-8 relative">
        <div>
            <p>{{ weekString }}</p>
        </div>
        <div class="buttons flex justify-center gap-1">
            <CustomButton
                :disabled="calendarStore.shouldPreviousWeekDisabled"
                :color="calendarStore.shouldPreviousWeekDisabled ? 'gray' : 'purple'"
                @click="handleGetCoursPerWeek('prev')">
                Semaine Précédente
            </CustomButton>
            <CustomButton
                @click="handleGetCoursPerWeek('next')"
            >
                Semaine Suivante
            </CustomButton>
        </div>
        <div class="flex flex-col justify-center items-center gap-2">
            <div class="flex justify-center items-center gap-2 mx-2 mb-2">
                <TypeCoursFilter
                    :uniqueTypeCoursList="uniqueTypeCoursList"
                    v-model:typeCoursId="selectedTypeCours"
                />
                <StatusCoursFilter
                    :uniqueStatusCoursList="uniqueStatusCoursList"
                    v-model:selectedStatusId="selectedStatusCours"
                />
                <Tooltip
                    :title="'Réinitialiser les filtres et revenir à la semaine actuelle'"
                    tooltip-pos="right"
                >
                    <button
                        class="hover:text deleteIcon"
                        @click="calendarStore.resetCalendar(); updateUrl(false)"
                    >
                        <DeleteCours size="18"/>
                    </button>
                </Tooltip>
            </div>

            <div class="flex justify-center items-center gap-2">
                <CustomButton
                    v-if="isAdmin && weekInfos.some(day =>day.some(cours => cours.statusCours.id === 4))"
                    @click="handleLaunchAllCours()"
                    class="mb-4"
                >
                    Ouvrir la semaine
                </CustomButton>
            </div>
        </div>
    </div>

    <div class="p-10 bg-white">
        <div class="grid grid-cols-6 gap-4">
            <div v-for="(day, index) in days" :key="day" class="flex justify-center" @click="calendarStore.setDaySelected(index)">
                <div
                    v-html="formatDay(day)"
                    :class="[calendarStore.daySelected === index ? 'days dayActif' : 'days', weekInfos[index]?.length > 0 ? 'has-cours' : '']"
                >
                </div>
            </div>
            <div
                v-if="weekInfos.every((info) => info.length === 0)"
                class="col-span-6 mx-auto text-center p-4 m-20 desktop">
                <a
                    v-if="infos.type === 'info_next_cours'"
                    @click="calendarStore.setDate(infos.nextCoursDate.date); handleGetCoursPerWeek()"
                >
                    {{ nextDateInNextWeek }}
                </a>
                <p v-else>
                    {{ infos.message }}
                </p>
            </div>
            <div v-for="(weekInfo, index) in weekInfos" :key="index">
                <div v-for="info in weekInfo" :key="info.id" class="flex flex-col items-center desktop">
                    <CoursCardCalendar
                        :info="info"
                    />
                </div>
            </div>
        </div>

        <div class="mobile">
            <div :class="daySelected !== 0 || new Date(date) > new Date(dateToday) ? 'dayBefore' : 'dayBefore invisible'" @click="calendarStore.daySelected !== 0 ?  calendarStore.setDaySelected(calendarStore.daySelected -1) :  handleGetCoursPerWeek('prev')"></div>
            <div class="active">
                <div
                    v-if="weekInfos[daySelected]?.length > 0">
                    <div
                        v-for="info in weekInfos[daySelected]"
                        :key="info.id"
                        class="flex flex-col items-center"
                    >
                        <CoursCardCalendar
                            :info="info"
                        />
                    </div>
                </div>
                <div v-else class="flex justify-center items-center h-70 text-center nextDateDisplayed">
                    <a
                        v-if="infos.type === 'info_next_cours'"
                        @click="
                    calendarStore.setDate(infos.nextCoursDate.date);
                    calendarStore.setDaySelected(new Date(infos.nextCoursDate.date).getDay() - 1);
                "
                    >
                        {{ nextDateInNextWeek }}
                    </a>
                    <a
                        v-else-if="nextDateInTheWeek"
                        @click="
                    calendarStore.setDate(!nextDateIndex ? calendarStore.firstNextCoursInNextWeeks.nextCoursDate.date : calendarStore.date);
                    calendarStore.setDaySelected(nextDateIndex ? nextDateIndex : new Date(calendarStore.firstNextCoursInNextWeeks.nextCoursDate.date).getDay() - 1);
                    "
                    >
                        {{ nextDateInTheWeek }}
                    </a>
                    <p v-else>
                        {{ calendarStore.firstNextCoursInNextWeeks &&  calendarStore.firstNextCoursInNextWeeks.message ? calendarStore.firstNextCoursInNextWeeks.message : infos.message }}
                    </p>
                </div>
            </div>
            <div :class="daySelected !== 5 ? 'dayAfter' : 'dayAfter'" @click="daySelected !== 5 ?  calendarStore.setDaySelected(calendarStore.daySelected +1) : handleGetCoursPerWeek('next')"></div>
        </div>

    </div>

</template>


<style scoped>


.mobile {
    display: none;
}

.desktop{
    min-height: 5vh;
}

a{
    color: #472371;
    text-decoration: underline;
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

.nextDateDisplayed{
    font-size: clamp(0.9rem, 1.4vw, 1.4rem);
}


.deleteIcon{
    padding: 10px;
    border: 1px solid #E5E7EB;
    border-radius: 5px;
    transition: border 0.3s ease-in-out;
    font-size: clamp(0.8rem, 1.5vw, 1rem);

    &>p{
        font-size: clamp(0.8rem, 1.5vw, 1rem);
    }

    &:hover {
        border-color: darkred;
    }
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
            width: clamp(40px, 9vw, 150px);
            height: clamp(40px, 9vw, 150px);
            background: url('../../../assets/icons/arrow.svg') no-repeat center;
            background-size: 50%;
            margin-top: 100px;
            cursor: pointer;

            &::after{
                position: absolute;
                content: '';
                width: clamp(40px, 9vw, 150px);
                height: clamp(40px, 9vw, 150px);
                background: rgba(0, 0, 0, 0.1);
                border-radius: 50%;
            }
        }

        .dayBefore {
            transform: rotate(180deg);
        }

    }
    .days{
        opacity: .4;
    }

    .dayActif{
        opacity: 1;
    }
}


</style>

