<script setup>

import InvoicesLine from "./InvoicesLine.vue";
import SmartPagination from "./admin/SmartPagination.vue";
import {computed, ref} from "vue";

const props = defineProps({
    userPaymentsHistory: {
        type: Array,
        required: true
    }
});


const currentPage = ref(1);
const itemsPerPage = 10;

// Données triées DESC
const sortedHistory = computed(() => {
    if (!props.userPaymentsHistory || !Array.isArray(props.userPaymentsHistory)) {
        return [];
    }

    return [...props.userPaymentsHistory].sort((a, b) => {
        return new Date(b.date) - new Date(a.date);
    });
});

// Total de pages (réactif)
const totalPages = computed(() => {
    return Math.ceil(sortedHistory.value.length / itemsPerPage);
});

// Données paginées (réactif)
const paginatedData = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return sortedHistory.value.slice(startIndex, endIndex);
});

const handlePageChange = async (newPage) => {
    // Charger les 10 éléments de la nouvelle page
    currentPage.value = newPage;
    const startIndex = (newPage - 1) * 10;
    const endIndex = startIndex + 10;
    paginatedData.value = props.userPaymentsHistory.slice(startIndex, endIndex);
};
</script>

<template>
    <!-- Recap des achats -->
    <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">Recap des achats</h4>
        <div class="max-w-full overflow-x-auto mb-6">
            <div class="w-full table-auto">
                <div class="theadInvoices bg-gray-800 text-white text-sm font-medium">
                    <div class="px-4 py-2">Date</div>
                    <div class="px-4 py-2">Heure</div>
                    <div class="px-4 py-2">Pack</div>
                    <div class="px-4 py-2">Montant</div>
                    <div class="px-4 py-2">Facture</div>
                </div>
                <div v-if="paginatedData?.length > 0">
                    <div
                        v-for="(paiement, index) in paginatedData"
                        :key="paiement.id"
                        :class="index % 2 === 0 ? 'bg-gray-100' : 'bg-white'"
                    >
                        <InvoicesLine :paiement="paiement" />
                    </div>
                    <SmartPagination
                        class="my-10"
                        :totalPages="totalPages"
                        :currentPage="currentPage"
                        @page-changed="handlePageChange"
                    />
                </div>
                <div v-else class="flex justify-center items-center h-70">
                    <p>Aucun achat</p>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped lang="scss">

.theadInvoices{
    display: grid;
    grid-template-columns: 2fr 2fr 2fr 2fr 1fr;
}

@media (max-width: 850px) {
    .theadInvoices{
        display: none;
    }
}

</style>
