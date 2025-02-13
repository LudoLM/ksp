<script setup>

import {ref, watch} from "vue";
import CustomSelect from "../CustomSelect.vue";


defineProps({
    uniqueTypeCoursList: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:selectedTypeCours']);
const selectedTypeCours = ref(0);

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
                    :options="uniqueTypeCoursList"
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
