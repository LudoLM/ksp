<script setup>

import {ref, watch} from "vue";
import CustomSelect from "../forms/CustomSelect.vue";

const props = defineProps({
    uniqueStatusCoursList: {
        type: Array,
        required: true,
    },
    statusCoursId: {
        type: Number,
        default: 0
    }
});

const selectedStatusCours = ref(props.statusCoursId);
const emit = defineEmits(['update:selectedStatusCours']);

watch(() => props.statusCoursId, (newVal) => {
    selectedStatusCours.value = newVal;
}, { immediate: true });

watch(selectedStatusCours, (newValue) => {
    emit('update:selectedStatusCours', newValue);
});
</script>


<template>
    <div id="form-wrapper" ref="form" class="space-y-4">
        <div class="selects">
            <!-- Sélection du statuts de cours -->
            <div class="form-item">
                <CustomSelect
                    :options="props.uniqueStatusCoursList"
                    v-model="selectedStatusCours"
                    item=""
                    id="Tous les statuts"
                />
            </div>
        </div>
    </div>
</template>
