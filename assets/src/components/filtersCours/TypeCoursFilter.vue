<script setup>

import {ref, watch} from "vue";
import CustomSelect from "../forms/CustomSelect.vue";

const props = defineProps({
    uniqueTypeCoursList: {
        type: Array,
        required: true,
    },
    typeCoursId: {
        type: Number,
        default: 0,
    }
});
const selectedTypeCours = ref(props.typeCoursId);
const emit = defineEmits(['update:selectedTypeCours']);

watch(() => props.typeCoursId, (newVal) => {
    selectedTypeCours.value = newVal;
}, { immediate: true });

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
