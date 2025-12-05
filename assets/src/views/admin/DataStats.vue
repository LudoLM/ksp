<script setup>

    import ChartOne from "../../components/admin/ChartOne.vue";
    import TopProductsTable from "../../components/admin/TopProductsTable.vue";
    import {onMounted, ref} from "vue";
    import FillingCharts from "../../components/admin/FillingCharts.vue";
    import {apiFetch} from "../../utils/useFetchInterceptor";
    import {alertStore} from "../../store/alert";
    import bannerImage from "../../../images/banners/imageBanner5.jpg";
    import Banner from "../../components/Banner.vue";
    import Report from "../../components/admin/Report.vue";
    import UsersActionsReporting from "../../components/admin/UsersActionsReporting.vue";
    import {useIntersectionObserver} from "@vueuse/core";
    import {storeToRefs} from "pinia";
    import {useLastActivitiesStore} from "../../store/lastActivities.js";
    const title = 'Rapport d\'activité';

    const paiements = ref([]);
    const quantitiesById = ref([]);
    const totalTotalTTC = ref(0);
    const nbreTotalVentes = ref(0);
    const startDate = ref(new Date('2024-01-01T00:00:00'));
    const endDate = ref(new Date());
    const lastActivitiesStore = useLastActivitiesStore();
    const {lastActivities} = storeToRefs(lastActivitiesStore);

    const fetchPaiements = async () => {
        try {
            const startDateString = new Date(startDate.value).toISOString().slice(0, 16);
            const endDateString = new Date(endDate.value).toISOString().slice(0, 16);
            const response = await apiFetch(`/api/admin/historique-paiements?startDate=${startDateString}&endDate=${endDateString}`, {
                method: 'GET',
            })
            if (response.ok){
                paiements.value = await response.json();
                totalTotalTTC.value = paiements.value.reduce((acc, pack) => acc + pack.price * pack.quantity, 0) / 100;
                nbreTotalVentes.value = paiements.value.reduce((acc, pack) => acc + pack.quantity, 0);

                // Regrouper et compter les quantités par id
                quantitiesById.value = paiements.value.reduce((acc, pack) => {
                    // Si l'ID est déjà présent, ajouter à la quantité existante
                    if (acc[pack.id]) {
                        acc[pack.id].quantity += pack.quantity;
                    } else {
                        // Si l'ID n'existe pas encore, l'ajouter avec l'objet actuel
                        acc[pack.id] = { ...pack, quantity: pack.quantity };
                    }
                    return acc;
                }, {});
                quantitiesById.value = Object.values(quantitiesById.value).sort((a, b) => b.quantity - a.quantity);
            }
        }
        catch (error) {
            alertStore.setAlert(error.message, error.type);
        }
    }


    const handleUpdateDates = (newStartDate, newEndDate) => {
        startDate.value = newStartDate;
        endDate.value = newEndDate;
        fetchPaiements();
    };

    const resetInfos = () => {
        startDate.value = new Date('2024-01-01T00:00:00');
        endDate.value = new Date();
        fetchPaiements();
    };

    onMounted(async () => {
        await fetchPaiements();
    });
</script>

<template>
    <Banner
        :title="title"
        :hasButton=false
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <div class="flex flex-col xl:flex-row gap-4 xl:gap-8 mt-4 xl:mt-16">
        <section class="flex flex-col justify-between items-center w-full px-4 sm:px-6 md:px-8 gap-5">
            <Report/>
            <UsersActionsReporting
                id="usersActionsReporting"
            />
            <TopProductsTable
                :nbreTotalVentes="nbreTotalVentes"
                :totalTotalTTC="totalTotalTTC"
                :quantitiesById="quantitiesById"
                @updateDates="handleUpdateDates"
                @resetInfos="resetInfos"
            />
            <ChartOne
                :paiements="paiements"
            />
            <FillingCharts />
        </section>
    </div>
</template>

<style scoped>
ChartOne {
    margin-top: 100px;
}
#usersActionsReporting {
    scroll-margin-top: 80px;
}
</style>
