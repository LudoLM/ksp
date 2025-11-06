<script setup>
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    tauxRemplissage: {
        type: Number,
        required: true,
    },
    color: {
        type: String,
        default: '#3C50E0',
    },
});

const chart = ref(null);

const apexOptions = {
    chart: {
        type: 'donut',
        width: 380,
    },
    colors: [props.color, '#e1e1e1'],
    legend: {
        show: false,
        position: 'bottom',
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                background: 'transparent',
            },
            customScale: 0.8
        },
    },
    dataLabels: {
        enabled: false,
    },
    labels: ['Rempli', 'Libre'],
    responsive: [
        {
            breakpoint: 1125,
            options: {
                chart: {
                    width: 250,
                },
            },
        },
        {
            breakpoint: 860,
            options: {
                chart: {
                    width: 200,
                },
            },
        },
        {
            breakpoint: 680,
            options: {
                chart: {
                    width: 150,
                },
            },
        },
        {
            breakpoint: 520,
            options: {
                chart: {
                    width: 100,
                },
            },
        },
    ],
};

const chartData = computed(() => {
    return {
        series: [props.tauxRemplissage, 100 - props.tauxRemplissage],
    };
});

</script>

<template>
    <div class="rounded-sm bg-white">
        <div class="flex flex-col justify-center items-center gap-4">
            <div class="mb-2">
                <div id="chartThree" class="mx-auto flex justify-center">
                    <VueApexCharts
                        type="donut"
                        width="340"
                        :options="apexOptions"
                        :series="chartData.series"
                        ref="chart"
                    />
                    <div class="tauxRemplissage flex flex-col justify-center items-center">
                        <div class="text-stone-600">{{ chartData.series[0].toFixed(2) }}%</div>
                        <div class="border-t-2 border-stone-600 pt-1 text-stone-400">{{ props.title }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</template>

<style scoped>

    #chartThree {
        position: relative;
    }
    .tauxRemplissage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: clamp(0.5rem, 1.5vw, 1rem);
        color: #3C50E0;
    }

    @media (max-width: 520px) {
        .tauxRemplissage {
            top: 110%;
        }
    }
</style>
