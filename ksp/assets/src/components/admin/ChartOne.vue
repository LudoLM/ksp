<script setup>
import { ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    paiements: {
        type: Array,
        required: true,
    },
});

const series = ref([]);
const packs = ref([]);
const year = ref(new Date().getFullYear());

const calculateSeries = () => {
    if (Array.isArray(props.paiements)) {
        // Determiner les packs
        packs.value = Array.from(
            new Set(props.paiements.map((paiement) => paiement.name))
        ).sort((a, b) => {
            const getCoursCount = (packName) => {
                const match = packName.match(/\d+/);
                return match ? parseInt(match[0], 10) : 0;
            };
            return getCoursCount(a) - getCoursCount(b);
        });
        // Creer la courbe pour chaque pack
        packs.value.forEach((pack) => {
            series.value.push({
                name: pack,
                data: Array(12).fill(0)
            });
        });
        const filteredPacks = props.paiements.filter((paiement) => paiement.year === year.value);

        filteredPacks.forEach((packByMonth) => {
            const index = packs.value.indexOf(packByMonth.name);
            series.value[index].data[packByMonth.month - 1] += packByMonth.quantity;
        });
    }
}


const changeYear = (newYear) => {
    year.value = newYear;
    series.value.splice(0, series.value.length);
    calculateSeries();
};

// Recalcule les packs uniques lorsque paiements change et les classe par nombre de cours
watch(
    () => props.paiements,
    () => {
        series.value.splice(0, series.value.length);
        calculateSeries(); // Ajoute les nouvelles données
    },
    { immediate: true }
);

// Méthodes pour définir les classes dynamiquement
const getBorderClass = (index) => {
    return ['border-primary', 'border-secondary', 'border-tertiary'][index % 3];
};

const getBgClass = (index) => {
    return ['bg-primary', 'bg-secondary', 'bg-tertiary'][index % 3];
};

const getTextClass = (index) => {
    return ['text-primary', 'text-secondary', 'text-tertiary'][index % 3];
};

const chartData = {
    series: series.value,
    labels: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
};

const chart = ref(null);

const apexOptions = {
    legend: {show: false, position: 'top', horizontalAlign: 'left'},
    colors: ['#3C50E0', '#80CAEE', '#FCD34D'],
    chart: {
        fontFamily: 'Satoshi, sans-serif',
        height: 335,
        type: 'area',
        dropShadow: {
            enabled: true,
            color: '#623CEA14',
            top: 10,
            blur: 4,
            left: 0,
            opacity: 0.1,
        },
        toolbar: {show: false},
    },
    responsive: [
        {breakpoint: 1024, options: {chart: {height: 300}}},
        {breakpoint: 1366, options: {chart: {height: 350}}},
    ],
    stroke: {width: [2, 2], curve: 'straight'},
    labels: {show: false, position: 'top'},
    grid: {
        xaxis: {lines: {show: true}},
        yaxis: {lines: {show: true}},
    },
    dataLabels: {enabled: false},
    markers: {
        size: 4,
        colors: '#fff',
        strokeColors: ['#3056D3', '#80CAEE', '#FCD34D'],
        strokeWidth: 3,
        strokeOpacity: 0.9,
        strokeDashArray: 0,
        fillOpacity: 1,
        hover: {sizeOffset: 5},
    },
    xaxis: {
        type: 'category',
        categories: chartData.labels,
        axisBorder: {show: false},
        axisTicks: {show: false},
    },
    yaxis: {title: {style: {fontSize: '0px'}}, min: 0, max: 50},
};
</script>

<template>
    <div
        class="col-span-12 mt-10 rounded-sm border border-stroke bg-white px-5 pt-7.5 pb-5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:col-span-8"
    >
        <div class="flex flex-wrap items-start justify-between gap-3 sm:flex-nowrap">
            <div class="flex w-full flex-wrap">
                <!-- Liste des packs -->
                <div
                    v-for="(pack, index) in packs"
                    :key="index"
                    class="flex min-w-47.5"
                >
          <span
              :class="[
              'mt-1 mr-2 flex h-4 w-full max-w-4 items-center justify-center rounded-full border',
              getBorderClass(index)
            ]"
          >
            <span
                :class="[
                'block h-2.5 w-full max-w-2.5 rounded-full',
                getBgClass(index)
              ]"
            ></span>
          </span>
                    <div class="w-full">
                        <p :class="['font-semibold', getTextClass(index)]">{{ pack }}</p>
                    </div>
                </div>
            </div>
            <div class="flex w-full max-w-45 justify-end">
                <div class="inline-flex items-center rounded-md bg-whiter p-1.5 dark:bg-meta-4">
                    <button
                        class="rounded py-1 px-3 text-xs font-medium text-black hover:bg-white hover:shadow-card dark:text-white dark:hover:bg-boxdark"
                        @click="changeYear(year -= 1)"
                    >
                        {{ year -1}}
                    </button>
                    <button
                        class="rounded py-1 px-3 text-sm font-bold text-black hover:bg-white hover:shadow-card dark:text-white dark:hover:bg-boxdark"
                    >
                        {{ year !== new Date().getFullYear() ? year : "Actuel" }}
                    </button>
                    <button
                        class="rounded py-1 px-3 text-xs font-medium text-black hover:bg-white hover:shadow-card dark:text-white dark:hover:bg-boxdark"
                        :class="{ invisible: year === new Date().getFullYear() }"
                        @click="changeYear(year += 1)"
                    >
                        {{ year +1 !== new Date().getFullYear() ? year +1 : "Actuel" }}
                    </button>
                </div>
            </div>
        </div>
        <div>
            <div id="chartOne" class="-ml-5">
                <VueApexCharts
                    type="area"
                    height="350"
                    :options="apexOptions"
                    :series="chartData.series"
                    ref="chart"
                />
            </div>
        </div>
    </div>
</template>
