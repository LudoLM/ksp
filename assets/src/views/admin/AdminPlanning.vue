<script setup lang="ts">

import Tabs from "@/components/tabs/Tabs.vue";
import Tab from "@/components/tabs/Tab.vue";
import AdminCalendar from "@/views/admin/AdminCalendar.vue";
import CoursListTableAdmin from "@/views/admin/CoursListTableAdmin.vue";
import {ref} from "vue";
import bannerImage from "../../../images/banners/imageBanner5.jpg";
import Banner from "@/components/Banner.vue";
import {storeToRefs} from "pinia";
import {useCalendarStore} from "@/store/calendar.ts";

const title = 'Liste des cours';
const calendarStore = useCalendarStore();
const activeTab = storeToRefs(calendarStore).activeTab;
</script>

<template>
    <Banner
        :title="title"
        :hasButton=false
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <Tabs
        v-model:activeTab="activeTab"
    >
        <Tab
            title="Par semaine"
        >
            <AdminCalendar />
        </Tab>
        <Tab
            title="Par mois"
        >
            <CoursListTableAdmin />
        </Tab>
    </Tabs>
</template>

<style scoped lang="scss">

</style>
