<script setup>

import {ref, watch} from "vue";
import CustomSelect from "../forms/CustomSelect.vue";

const props = defineProps({
    uniqueStatusCoursList: {
        type: Array,
        required: true,
    },
    coursId: {
        type: Number,
    },
});

// Vérifier et utiliser une valeur par défaut locale pour éviter NaN
const selectedStatusCours = ref(props.coursId || 0);

const emit = defineEmits(['update:selectedStatusCours']);

watch(selectedStatusCours, (newValue) => {
    emit('update:selectedStatusCours', newValue);
});

</script>


<template>

    <div id="form-wrapper" ref="form" class="space-y-4">
        <div class="selects">
            <!-- Sélection du type de cours -->
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

<style scoped>

</style>
