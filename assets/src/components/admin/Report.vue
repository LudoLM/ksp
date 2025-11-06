<script setup>
import {computed, onMounted, ref, watch} from 'vue';
import { apiFetch } from '../../utils/useFetchInterceptor.js';
import CustomSelect from "../forms/CustomSelect.vue";
import {monthsOfYearOptions} from "../../constants/monthsOfYear.js";
import {yearsOptions} from "../../constants/years.js";
import Spinner from "../Spinner.vue";

const stripeData = ref(null);
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());
const isLoading = ref(false);

const fetchStripeData = async () => {
    isLoading.value = true;

    const url = `/api/stripe-report?month=${selectedMonth.value + 1}&year=${selectedYear.value}`;

    try {
        const response = await apiFetch(url, {});
        stripeData.value = await response.json();
    } catch (error) {
        console.error("Erreur lors du chargement Stripe :", error);
        stripeData.value = null;
    } finally {
        isLoading.value = false;
    }
};


watch(
    [selectedMonth, selectedYear],

    () => {
        fetchStripeData();
    },

    { immediate: false }
);

onMounted(() => {
    fetchStripeData();
});
</script>


<template>
    <div class="col-10 rounded-sm border border-stroke bg-white shadow-default w-full">
        <div class="py-6 px-4 md:px-6 xl:px-7.5 flex flex-col justify-between gap-10">
            <div class="flex flex-col gap-2">
                <h4 class="text-gray-800 text-theme-sm font-semibold">Finance</h4>
                <p class ="text-xs text-gray-400">Encaissement par période</p>
            </div>
            <div>
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
                <input type="month"/>
                <div class="flex flex-col min-h-[160px]">
                    <div v-if="isLoading" class="flex justify-center">
                        <Spinner />
                    </div>

                    <div v-else-if="stripeData">
                        <div class="flex justify-end text-lg sm:text-xl font-semibold text-gray-800">
                            <div class="flex flex-col justify-between items-center gap-2">
                                <p class="text-xs font-semibold text-gray-800">
                                    TTC
                                </p>
                                {{ stripeData.gross }} €
                            </div>
                        </div>
                        <div class="flex justify-between sm:justify-end mt-10">
                            <ul class="flex justify-between text-xs font-thin w-full sm:w-1/2">
                                <li class="flex flex-col justify-between items-center gap-2">
                                    <p class="text-xs font-semibold text-gray-800">
                                        TVA
                                    </p>
                                    {{ stripeData.tva }} €
                                </li>
                                <li class="flex flex-col justify-between items-center gap-2">
                                    <p class="text-xs font-semibold text-gray-800">
                                        Hors Taxe
                                    </p>
                                    {{ stripeData.ht }} €
                                </li>
                                <li class="flex flex-col justify-between items-center gap-2">
                                    <p class="text-xs font-semibold text-gray-800">
                                        Frais stripe
                                    </p>
                                    {{ stripeData.fees }} €
                                </li>
                                <li class="flex flex-col justify-between items-center gap-2">
                                    <p class="text-xs font-semibold text-gray-800">
                                        Net Stripe
                                    </p>
                                    {{ stripeData.netStripe }} €
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-else class="flex justify-center">
                        Erreur dans la recupération des données Stripe.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



<style scoped>
</style>
