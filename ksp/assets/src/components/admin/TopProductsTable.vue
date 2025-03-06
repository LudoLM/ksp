<script setup>
    import { ref} from 'vue'
    import CustomButton from "../forms/CustomButton.vue";
    import CustomInput from "../forms/CustomInput.vue";


    const props = defineProps({
        quantitiesById: {
            type: Object,
            required: true
        },
        totalTotalTTC: {
            type: Number,
            required: true
        },
        nbreTotalVentes: {
            type: Number,
            required: true
        }
    });

    const tva = 20;
    const startDate = ref(new Date('Sun Jan 01 2024 00:00:00 GMT+0100 (heure normale d’Europe centrale'));
    const endDate = ref(new Date());

    const emit = defineEmits(['updateDates', 'resetInfos']);

    const resetInfos = () => {
        startDate.value = new Date('2024-01-01T00:00:00');
        endDate.value = new Date();
        emit('resetInfos');
    };

    const exportCsv = async () => {

        // En-tête
        const header = 'Pack,Quantité,PU HT,PU TTC,Total HT,Total TTC\n';

        // Contenu des lignes
        const rows = props.quantitiesById.map(pack =>
            `${pack.name},${pack.quantity},${(pack.price / (1 + tva / 100) / 100).toFixed(2)},${(pack.price / 100).toFixed(2)},${(pack.price * pack.quantity / (1 + tva / 100) / 100).toFixed(2)},${(pack.price * pack.quantity / 100).toFixed(2)}`
        ).join('\n');

        const footer = `\nTotal,${props.nbreTotalVentes},,,${(props.totalTotalTTC / (1 + tva / 100)).toFixed(2)},${(props.totalTotalTTC / (1 + tva / 100)).toFixed(2)}`;

        // Combine l'en-tête, les lignes et le pied de page
        const csv = header + rows + footer;

        // Crée un fichier Blob pour le téléchargement
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Ventes de packs du ${startDate.value.toLocaleDateString("fr")} à ${endDate.value.toLocaleDateString("fr")}\n\\`;
        a.click();
        window.URL.revokeObjectURL(url);
    };

</script>


<template>
    <div class="col-10 rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark w-full">
        <div class="py-6 px-4 md:px-6 xl:px-7.5 flex items-center justify-between">
            <h4 class="text-xl font-bold text-black dark:text-white">Top Produits</h4>
            <div class="filters flex justify-around item-baseline gap-4">
                <!-- Sélection de debut de la selection -->
                <div class="form-item">
                    <CustomInput
                        type="date"
                        item="De"
                        v-model="startDate"
                        id="dateCours"
                        @change="$emit('updateDates', startDate, endDate)"
                    />
                </div>
                <!-- Sélection de fin de la selection -->
                <div class="form-item">
                    <CustomInput
                        type="date"
                        item="A"
                        v-model="endDate"
                        id="dateCours"
                        @change="$emit('updateDates', startDate, endDate)"
                    />
                </div>
                <div class="form-item form-group mb-4 flex justify-center items-center">
                    <!-- Reset-->
                    <CustomButton
                        @click="resetInfos"
                        color="red"
                        class="self-center"
                    >
                        Reset
                    </CustomButton>
            </div>
                <div class="form-item form-group mb-4 flex justify-center items-center">
                    <!-- Reset-->
                    <CustomButton
                        @click="exportCsv"
                        class="self-center"
                    >
                        Exporter
                    </CustomButton>
                </div>
            </div>
        </div>

        <!-- Table header -->
        <div
            class="grid grid-cols-6 border-t border-stroke py-4.5 px-4 dark:border-strokedark sm:grid-cols-8 md:px-6 2xl:px-7.5"
        >
            <div class="col-span-3 flex items-center">
                <p class="font-bold">Packs</p>
            </div>
            <div class="col-span-1 hidden items-center sm:flex">
                <p class="font-bold">Ventes</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold">PU HT</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold">PU TTC</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold">Total HT</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold">Total TTC</p>
            </div>
        </div>

        <!-- Table Rows -->
        <div
            v-for="pack in quantitiesById"
            :key="pack.id"
            class="grid grid-cols-6 border-t border-stroke py-4.5 px-4 dark:border-strokedark sm:grid-cols-8 md:px-6 2xl:px-7.5"
        >
            <div class="col-span-3 flex items-center">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <p class="text-sm font-medium text-black dark:text-white">{{ pack.name }}</p>
                </div>
            </div>
            <div class="col-span-1 hidden items-center sm:flex">
                <p class="text-sm font-medium text-black dark:text-white">{{ pack.quantity }}</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="text-sm font-medium text-black dark:text-white">{{ (pack.price / (1 + tva / 100) / 100).toFixed(2) }}</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="text-sm font-medium text-black dark:text-white">{{ (pack.price / 100).toFixed(2) }}</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="text-sm font-medium text-meta-3">{{ (pack.price * pack.quantity / (1 + tva / 100) / 100).toFixed(2) }}</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="text-sm font-medium text-meta-3">{{ (pack.price *  pack.quantity / 100).toFixed(2) }}</p>
            </div>

        </div>
            <!-- Table Footer -->
        <div
            class="grid grid-cols-6 border-t border-stroke py-4.5 px-4 bg-slate-700 text-white sm:grid-cols-8 md:px-6 2xl:px-7.5"
        >
            <div class="col-span-3 flex items-center">
                <p class="font-bold">Total</p>
            </div>
            <div class="col-span-1 hidden items-center sm:flex">
                <p class="font-bold">{{ nbreTotalVentes }}</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold"></p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold"></p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold">{{ (totalTotalTTC / (1 + tva / 100)).toFixed(2) }}</p>
            </div>
            <div class="col-span-1 flex items-center">
                <p class="font-bold">{{ totalTotalTTC.toFixed(2) }}</p>
            </div>
        </div>
    </div>


</template>


<style scoped lang="scss">

</style>
