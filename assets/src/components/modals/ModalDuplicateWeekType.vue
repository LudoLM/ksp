<template>
    <v-dialog
        v-model="localIsOpen"
        max-width="500"
    >
        <v-card>
            <v-card-title class="text-h5">
                Choisissez la semaine type à dupliquer
            </v-card-title>

            <v-card-text>
                <CustomSelect
                    label="Semaine"
                    :options="weekTypeOptions"
                    v-model="selectedId"
                    placeholder="Sélectionnez une semaine"
                />
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <CustomButton @click="handleSelectedWeekTypeId">Dupliquer</CustomButton>
                <CustomButton @click="closeDialog">Fermer</CustomButton>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import {ref, watch} from 'vue';
import CustomButton from '../forms/CustomButton.vue';
import CustomSelect from '../forms/CustomSelect.vue';

const props = defineProps({
    isOpen: Boolean,
    weekTypeOptions: Array,
});


const emit = defineEmits(['update:isOpen', 'update:modelValue', 'select:cours']);

const localIsOpen = ref(props.isOpen);
const selectedId = ref(null);

watch(() => props.weekTypeOptions, (options) => {
    if (options.length > 0 && selectedId.value === null) {
        selectedId.value = options[0].id;
    }
}, { immediate: true });

watch(selectedId, val => {
    emit('update:modelValue', val);
});

const handleSelectedWeekTypeId = () => {
    emit('select:cours', selectedId.value);
    emit('update:isOpen', false); // Fermer la modale
};

const closeDialog = () => {
    localIsOpen.value = false;
    emit('update:isOpen', false);
};
</script>
