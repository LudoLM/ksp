<script setup>

import {ref, watch} from "vue";
import CustomSelect from "../CustomSelect.vue";

const props = defineProps({
    uniqueTypeCoursList: {
        type: Array,
        required: true,
    },
    coursId: {
        type: Number,
    },
});

// Vérifier et utiliser une valeur par défaut locale pour éviter NaN
const selectedTypeCours = ref(props.coursId || 0);

const emit = defineEmits(['update:selectedTypeCours']);

watch(selectedTypeCours, (newValue) => {
    emit('update:selectedTypeCours', newValue);
});

</script>


<template>

    <div id="form-wrapper" ref="form" class="space-y-4">
        <div class="selects">
            <!-- Sélection du type de cours -->
            <div class="form-item">
                <CustomSelect
                    :options="props.uniqueTypeCoursList"
                    v-model="selectedTypeCours"
                    item=""
                    id="Tous les cours"
                />
            </div>
        </div>

    </div>
</template>

<style scoped>

</style>
