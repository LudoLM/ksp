<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    selectedMonth: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["update:selectedMonthList"]);

// Liste des mois
const months = [
    "Janvier",
    "Février",
    "Mars",
    "Avril",
    "Mai",
    "Juin",
    "Juillet",
    "Août",
    "Septembre",
    "Octobre",
    "Novembre",
    "Décembre",
];

// `selectedMonthCours` est un computed lié à `props.selectedMonth`
const selectedMonthCours = computed({
    get() {
        const monthIndex = props.selectedMonth - 1;
        return months[monthIndex] || months[new Date().getMonth()];
    },
    set(value) {
        // Convertir le nom du mois en numéro et émettre l'événement
        const monthIndex = months.indexOf(value) + 1; // Trouver l'index et ajouter 1
        const monthNumber = String(monthIndex).padStart(2, "0"); // Format à deux chiffres
        emit("update:selectedMonthList", monthNumber);
    },
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="relative z-20 bg-transparent">
                <!-- Utilisation de v-model pour la synchronisation -->
                <select
                    v-model="selectedMonthCours"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                >
                    <!-- Options des mois -->
                    <option
                        v-for="month in months"
                        :key="month"
                        :value="month"
                        class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                    >
                        {{ month }}
                    </option>
                </select>
                <span
                    class="absolute z-30 text-gray-700 -translate-y-1/2 pointer-events-none right-4 top-1/2 dark:text-gray-400"
                >
          <svg
              class="stroke-current"
              width="20"
              height="20"
              viewBox="0 0 20 20"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
          >
            <path
                d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396"
                stroke=""
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
          </svg>
        </span>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
