<script setup>
import CoursLine from "../CoursLine.vue";
import { getMondayOfSpecificDate, getSundayOfSpecificDate } from "../../store/calendar";
import CustomButton from "../forms/CustomButton.vue";
import { computed } from "vue";
import { useWeekActions } from "../../utils/useWeekActions";

const props = defineProps({
    coursData: Array,
    currentPage: Number
});

const emit = defineEmits(["deleteCreation", "weekOpened"]);


const { handleLaunchAllCours: _handleLaunchAllCours } = useWeekActions();

const launchWeekAction = async (mondayDateObject, sundayDateObject) => {
    const { success, cours } = await _handleLaunchAllCours([mondayDateObject, sundayDateObject]);
    if (success) {
        emit("weekOpened", cours); // 🔥 envoie les cours modifiés au parent
    }
};


const onDeleteCreation = (payload) => {
    emit("deleteCreation", payload);
};


// Grouper les cours par semaine (lundi)
const groups = computed(() => {
    const result = {};
    for (const cours of props.coursData) {
        // Si pas de date, on passe à l'élément suivant
        if (!cours.dateCours) continue;

        // On convertit la date de début de semaine en une chaîne ISO pour l'utiliser comme clé unique
        const monday = getMondayOfSpecificDate(new Date(cours.dateCours)).toISOString();

        if (!result[monday]) {
            result[monday] = [];
        }
        result[monday].push(cours);
    }
    return result;
});
</script>

<template>
    <div
        v-for="(group, mondayKey) in groups"
        :key="mondayKey"
        class="mt-5 relative border-b-2 border-gray-500"
    >
        <div
            class="relative flex items-baseline justify-end mr-5 min-h-15 gap-5"
        >
            <div class="flex flex-col justify-between items-center gap-2">
                <div>
                    <!-- Utilisation de la clé ISOString pour l'affichage -->
                    Semaine du {{ new Date(mondayKey).toLocaleDateString() }}
                </div>
                <CustomButton
                    v-if="group.some(c => c.statusCours.id === 4)"
                    class="mb-4"
                    @click="launchWeekAction(new Date(mondayKey), getSundayOfSpecificDate(new Date(mondayKey)))"
                >
                    Ouvrir la semaine
                </CustomButton>
            </div>
        </div>
        <div v-for="(item, index) in group" :key="item.id + currentPage" :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
            <CoursLine
                :item="item"
                @deleteCreation="onDeleteCreation"
            />
        </div>
        <div class="groupActions">

        </div>
    </div>
</template>
