<script setup>
import { onMounted, ref, watch, computed } from "vue";
import { apiFetch } from "../../utils/useFetchInterceptor";

const props = defineProps({
    selectedYear: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(["update:selectedYearList"]);
const years = ref([]);
const selectedYearCours = computed({
    get() {
        return props.selectedYear;
    },
    set(value) {
        emit("update:selectedYearList", value);
    },
});

// Charger les années à partir de l'API
const fetchYears = async () => {
    try {
        const response = await apiFetch("/api/getYearsRangeForCours");
        const data = await response.json();
        years.value = JSON.parse(data);

        // Si aucune année n'est sélectionnée par défaut
        if (!props.selectedYear) {
            emit("update:selectedYearList", new Date().getFullYear());
        }
    } catch (error) {
        console.error("Erreur lors du chargement des années :", error);
    }
};

// Charger les années au montage
onMounted(() => {
    fetchYears();
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="relative z-20 bg-transparent">
                <select
                    v-model="selectedYearCours"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                >
                    <option
                        v-for="year in years"
                        :key="year"
                        :value="year"
                        class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                    >
                        {{ year }}
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
