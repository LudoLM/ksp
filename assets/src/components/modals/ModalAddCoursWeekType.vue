

<script setup>
import {computed, ref, watch} from 'vue';
import CustomButton from "../forms/CustomButton.vue";
import CustomInput from "../forms/CustomInput.vue";
import CustomCheckboxPriority from "../forms/CustomCheckboxPriority.vue";
import CustomTextarea from "../forms/CustomTextarea.vue";
import CustomSelect from "../forms/CustomSelect.vue";
import {daysOfWeek} from "../../constants/daysOfWeek";


const props = defineProps({
    info: {
        type: Object,
        required: true,
    },
    isEditMode:{
        type: Boolean,
    },
    isOpen: {
        type: Boolean,
        required: true,
    },
});


const emit = defineEmits(["update:isOpen", "save:cours"]);
const localIsOpen = ref(props.isOpen);
const errors = ref(
    {
        duree: null,
        nbInscriptionMax: null,
        daySelected: null,
        timeSelected: null,
        specialNote: null,
    },
);


watch(() => localIsOpen.value, (val) => {
    emit('update:isOpen', val);
});


const daySelected = computed({
    get: () => props.info.daySelected,
    set: (val) => {
        props.info.daySelected = parseInt(val);
    },
});

const handleAddCoursWeekType = async (event) => {
    event.preventDefault();
    const data = {
        uid: props.info.uid,
        typeCours: props.info.typeCours,
        duree: parseInt(props.info.duree),
        nbInscriptionMax: parseInt(props.info.nbInscriptionMax),
        specialNote: props.info.specialNote,
        hasPriority: props.info.hasPriority,
        hasLimitOfOneCoursPerWeek: props.info.hasLimitOfOneCoursPerWeek,
        daySelected: props.info.daySelected,
        timeSelected: props.info.timeSelected,
    };

    emit("save:cours", data);
    closeDialog();

};

const closeDialog = () => {
    localIsOpen.value = false;
    emit("update:isOpen", false);
};

</script>


<template>
    <v-dialog v-model="localIsOpen" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <p class="text-sm font-normal text-gray-700 dark:text-gray-400 text-right">
                <a class="text-right" v-bind="activatorProps">
                    <slot></slot>
                </a>
            </p>
        </template>

        <v-card>
            <v-card-title class="text-sm sm:text-xl">{{ isEditMode ? "Modification du cours de" : "Création du cours de " + info.typeCours.libelle}}</v-card-title>
            <v-card-actions>
                <v-spacer></v-spacer>
                <div class="flex flex-col w-full">
                    <div class="flex gap-2">
                        <div class="flex flex-col justify-center align-center">
                            <CustomInput item="Durée (minutes)" type="number" id="duree" :error="errors.duree" v-model="info.duree" required />
                            <CustomInput item="Nombre de places" type="number" id="nbInscriptionMax" :error="errors.nbInscriptionMax" v-model="info.nbInscriptionMax" required />
                            <CustomSelect
                                :options="daysOfWeek"
                                v-model="daySelected"
                                item="Jour"
                            />
                            <CustomInput item="Heure" type="time" id="time" v-model="info.timeSelected" required />
                        </div>
                        <div class="flex flex-col justify-between items-center w-full">
                            <CustomTextarea item="Note" id="specialNote" :error="errors.specialNote" v-model="info.specialNote" class="w-4/5 h-1/2 sm:h-2/3" />
                            <div class="flex flex-col sm:flex-row justify-around items-center mb-10 w-4/5 gap-4">
                                <CustomCheckboxPriority
                                    v-model="info.hasPriority"
                                    id="hasPriority"
                                    class="text-xs sm:text-sm"
                                >
                                    Prioritaire aux abonnés
                                </CustomCheckboxPriority>
                                <CustomCheckboxPriority
                                    v-model="info.hasLimitOfOneCoursPerWeek"
                                    id="hasLimitOfOneCoursPerWeek"
                                    class="text-xs sm:text-sm"
                                >
                                    Limite 2 cours/semaine
                                </CustomCheckboxPriority>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center align-center gap-2">
                        <CustomButton @click="handleAddCoursWeekType">Ajouter à la semaine</CustomButton>
                        <CustomButton @click="closeDialog">Fermer</CustomButton>
                    </div>

                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>
    a {
        color: #e2a945;
    }

    #specialNote{
        height: 75%;
    }


</style>
